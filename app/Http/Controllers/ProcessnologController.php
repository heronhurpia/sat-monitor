<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lista;
use App\Models\Audio;
use App\Models\Transponder;
use App\Models\Service;
use App\Models\Log;

use DB;
use Carbon\Carbon; 
use Session; 

class ProcessnologController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		// Apaga todos os logs
		//Log::truncate();
		//Audio::truncate();
		//Service::truncate();
		//Transponder::truncate();

		/* Carrega os transponders locados */
		list($transponders,$locks) = $this->createCollections();
	
		/* Atualiza tabelas e registra alterações */
		$this->create_log('process','1',"Varredura do sinal");
		$this->process($transponders); 

		$logs = Log::orderBy('created_at','desc')->limit(150)->get();

		//echo '<pre>'; 
		//print_r($locks); 
		//echo '</pre>' ;

		return view('processnolog', compact('logs','transponders','locks'));
	}

	public function process($transponders)
	{
		/* Varre cada um dos transponders verificando se houve alteração */
		foreach($transponders as &$transponder){
			$this->updateTransponder($transponder);

			/* Varre cada um dos serviços do transponder atual verificando se houve alteração */
			foreach($transponder->services as $service){

				/* Verificar caso seja um serviço válido */
				if ( $service->name ) {
					$this->updateService($transponder,$service);

					/* Varre cada um dos audios do serviço atual verificando se houve alteração */
					foreach($service->audio_pids as $audio_pid){
						$this->updateAudio($service,$audio_pid);
					}
				}
			}
		}
	}

	/* Lê dB e converte resultado para array. 
		Somente transponders locados serão analisados
	*/
	public function createCollections () {

		// Carrega resultado da última sintonia
		$locks = DB::select(DB::raw('select * from dvb.frequency_lock_success_rate where "Last" = 1 '));

		// Carrega do banco de dados todos os transponders sintonizados
		$frequencies = array_map(function($l) {
			return $l->frequency ;
		},$locks) ;
		
		/* Carrega o lineup recebido */
		$query = "select datetime, lineup as col_lineup from dvb.lineup l order by id_lineup desc limit 1" ;
		$s = DB::select(DB::raw($query));
		$transponders = json_decode($s[0]->col_lineup)->line_up;

		// varre cada transponder e retira da lista os não sintonizados
		foreach($transponders as $index => &$transponder) {
			if ( !in_array($transponder->frequency,$frequencies) ){
				$this->create_log('process','1','Frequencia ' . $transponder->frequency . ' não sintonizada!');
				unset($transponders[$index]);
			}
		}

		/*
		**		Apaga serviços não encontrados.
		**	Somente transponders sintonizados na última varredura serão analisados
		*/
		$this->deleteChannels($transponders,$frequencies);

		return [$transponders,$locks];
	}

	// Analisa dados para pagar canais escluídos
	public function deleteChannels($transponders,$frequencies)
	{
		/* Processo para apagar canais */
		/* Cria uma lista de serviços baseada na varredura atual */
		$canais = array() ;
		foreach($transponders as $transponder){
			foreach($transponder->services as $service){
				if ( $service->name ) {
					array_push($canais,$service);
				}
			}
		}
		
		$services = Service::select('services.*','tp.frequency as frequency')
			->join('transponders as tp','services.transponder_id','tp.id')
			->whereIn('tp.frequency',$frequencies)
			->where('active','1')
			->get();

		/* Apaga canais */
		foreach($services as $index => $service){
			foreach($canais as $ndx => $canal){
				if ( $service->name == $canal->name ) {
					unset($services[$index]);
					break;
				}
			}
		}

		// Apaga os serviços que estavam no dB mas não estão na varredura atual
		foreach($services as $service){
			$this->create_log('services',$service->id,"Apagar serviço: " . $service->name);
		}
	}

	/* Verifica se o transponder locado está no dB. Incluir se não estiver e atualiar em caso de mudanças */
	public function updateTransponder($transponder)
	{

		//echo '<pre>'; 
		//print_r($transponder); 
		//echo '</pre>' ;
		//return ;

		// Variável de logs
		$logs = array() ;

		/* Busca transponder atual */
		$x = Transponder::where('frequency','=',$transponder->frequency)
				->where('symbol_rate','=',$transponder->symbol_rate)
				->where('polarity','=',$transponder->polarity)
				->first();

		/* Caso o transponder não tenha sido encontrada no dB, incluir */
		if ( !$x ) {
			$x = new Transponder ;
			$x->frequency = $transponder->frequency ;
			$x->symbol_rate = $transponder->symbol_rate ;
			$x->polarity = $transponder->polarity ;
			$x->satellite_id = 1 ;
			$x->tsid = $transponder->transport_stream_id ;
			$x->orbital = "70W" ;
			$x->dvb_mode = $transponder->dvb_mode ;
			$x->network_id = $transponder->network_id ;
			$x->onid = $transponder->original_network_id ;
			$x->quality =  0 ;
			$x->strength = 0 ;
			$x->created_at = Carbon::now() ;
			$x->updated_at = Carbon::now() ;
			$x->save();
			$this->create_log('transponders',$x->id,'Transponder criado: ' . $x->frequency);
		}

		/* Cria campo para uso na atualização de serviços */
		$transponder->id = $x->id ;

		//if ( $transponder->symbol_rate != $x->symbol_rate ) {
		//	array_push($logs,"Symbol rate mudou de " . $x->symbol_rate . " para " . $transponder->symbol_rate);
		//	$x->symbol_rate = $transponder->symbol_rate ;
		//}		

		if ( $transponder->transport_stream_id != $x->tsid ) {
			array_push($logs,"TSID mudou de " . $x->tsid . " para " . $transponder->transport_stream_id);
			$x->tsid = $transponder->transport_stream_id ;
		}		

		if ( $transponder->dvb_mode != $x->dvb_mode ) {
			array_push($logs,"dvb_mode mudou de " . $x->dvb_mode . " para " . $transponder->dvb_mode);
			$x->dvb_mode = $transponder->dvb_mode ;
		}		

		if ( $transponder->original_network_id != $x->onid ) {
			array_push($logs,"ONID mudou de " . $x->onid . " para " . $transponder->original_network_id);
			$x->onid = $transponder->original_network_id ;
		}		

		if ( $transponder->network_id != $x->network_id ) {
			array_push($logs,"network_id mudou de " . $x->network_id . " para " . $transponder->network_id);
			$x->network_id = $transponder->network_id ;
		}
		
		/** Em caso de alterações, atualizar db */
		if ( sizeof($logs) > 0 ) {
			$x->updated_at = Carbon::now() ;
			$x->save() ;

			/* Criar logs para as alterações */
			foreach ( $logs as $log ) {
				$this->create_log('transponders',$x->id,$log);
			}		
		}
	}

	public function updateService($transponder,&$service)
	{
		$logs = array() ;
		$channel = Service::
						where('transponder_id','=',$transponder->id)
						->where('svcid','=',$service->service_id)
						->where('active','1')
						->first();

		if ( !$channel ) {
			$channel = new Service ;
			$channel->name = $service->name ;
			$channel->service_type = $service->service_type ;
			$channel->codec = $service->video_codec ;
			$channel->video_pid = $service->video_pid ;
			$channel->pcr_pid = $service->pcr_pid ;
			$channel->transponder_id = $transponder->id ;
			$channel->epg_pid = $service->epg_pid ;
			$channel->svcid = $service->service_id ;
			$channel->bouquet_id = $service->bouquet_id ;
			$channel->bouquet_name = $service->bouquet_name ;
			$channel->viewer_channel = $service->viewer_channel ;
			$channel->service_type_id = $service->service_type_id ;
			$channel->created_at = Carbon::now() ;
			$channel->updated_at = Carbon::now() ;
			$channel->save();
			array_push($logs,'Criado novo serviço: ' . $service->name);
		}

		// Salva campo para uso na análise do áudio
		$service->id = $channel->id ;
		
		if ( $channel->name != $service->name ) {
			array_push($logs,"Nome mudou de \"" . $channel->name . "\" para \"" . $service->name ."\"" );
			$channel->name = $service->name ;
		}

		if ( $channel->viewer_channel != $service->viewer_channel ) {
			// Não registrar pois não é uma informação relevante para o SAC
			//array_push($logs,"viewer_channel mudou de \"" . $channel->viewer_channel . "\" para \"" . $service->viewer_channel ."\"" );
			$channel->viewer_channel = $service->viewer_channel ;
		}

		if ( $channel->service_type_id != $service->service_type_id ) {
			array_push($logs,"service_type_id mudou de \"" . $channel->service_type_id . "\" para \"" . $service->service_type_i ."\"" );
			$channel->service_type_id = $service->service_type_id ;
		}

		if ( $channel->bouquet_name != $service->bouquet_name ) {
			array_push($logs,"bouquet_name mudou de \"" . $channel->bouquet_name . "\" para \"" . $service->bouquet_name ."\"" );
			$channel->bouquet_name = $service->bouquet_name ;
		}

		if ( $channel->bouquet_id != $service->bouquet_id ) {
			// Não registrar no log pois não é uma informação relevante para o SAC
			// array_push($logs,"bouquet_id mudou de \"" . $channel->bouquet_id . "\" para \"" . $service->bouquet_id ."\"" );
			$channel->bouquet_id = $service->bouquet_id ;
		}

		if ( $channel->video_pid != $service->video_pid ) {
			array_push($logs,"video_pid mudou de " . $channel->video_pid . " para " . $service->video_pid);
			$channel->video_pid = $service->video_pid ;
		}

		if ( $channel->pcr_pid != $service->pcr_pid ) {
			array_push($logs,"pcr_pid mudou de " . $channel->pcr_pid . " para " . $service->pcr_pid);
			$channel->pcr_pid = $service->pcr_pid ;
		}

		if ( $channel->epg_pid != $service->epg_pid ) {
			array_push($logs,"epg_pid mudou de " . $channel->epg_pid . " para " . $service->epg_pid);
			$channel->epg_pid = $service->epg_pid ;
		}

		if ( $channel->codec != $service->video_codec ) {
			array_push($logs,"codec mudou de " . $channel->codec . " para " . $service->video_codec);
			$channel->codec = $service->video_codec ;
		}
		
		if ( $channel->service_type != $service->service_type ) {
			array_push($logs,"service_type mudou de " . $channel->service_type . " para " . $service->service_type);
			$channel->service_type = $service->service_type ;
		}

		/** Em caso de alterações, atualizar db */
		if ( sizeof($logs) > 0 ) {
			$channel->updated_at = Carbon::now() ;
			$channel->save() ;

			/* Criar logs para as alterações */
			foreach ( $logs as $log ) {
				$this->create_log('services',$channel->id,$log);
			}		
		}
	}

	public function updateAudio($service,$audio)
	{
		/** Lista de alterações */
		$logs = array() ;

		$audio_channel = Audio::where('service_id','=',$service->id)->where('pid','=',$audio->pid)->first();
		if ( !$audio_channel ) {
			$audio_channel = new Audio ;
			$audio_channel->pid = $audio->pid ;
			$audio_channel->language = $audio->lang ;
			$audio_channel->service_id = $service->id ;
			$audio_channel->audio_type = $audio->codec ;
			$audio_channel->created_at = Carbon::now() ;
			$audio_channel->updated_at = Carbon::now() ;
			array_push($logs,'Criado novo áudio: ' . $audio_channel->pid);
		}

		if ( $audio_channel->audio_type != $audio->codec ) {
			array_push($logs,"Tipo de áudio mudou de " . $audio_channel->audio_type . " para " . $audio->codec);
			$audio_channel->audio_type = $audio->codec ;
		}		

		if ( $audio_channel->language != $audio->lang ) {
			array_push($logs,"Idioma mudou de \"" . $audio_channel->language . "\" para \"" . $audio->lang . "\"");
			$audio_channel->language = $audio->lang ;
		}

		/** Em caso de alterações, atualizar db */
		if ( sizeof($logs) > 0 ) {
			$audio_channel->save();
		
			/* Criar logs para as alterações */
			foreach ( $logs as $log ) {
				$this->create_log('audios',$audio_channel->id,$log);
			}
		}
	}
		
	// Cria arquivo de log padronizado
	public function create_log ( $table, $item_id, $message ) {
		$log = new LOG ;
		$log->table = $table;
		$log->item_id = $item_id ;
		$log->description = $message ;
		$log->created_at = Carbon::now() ;
		$log->updated_at = Carbon::now() ;
		$log->save();
	}
}

