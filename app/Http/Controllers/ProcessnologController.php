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

		/* Carrega os transponders locados e com nomes padronizados */
		$xpdrs = $this->createCollection();

		/* Atualiza tabelas e registra alterações */
		$this->create_log('process','1',"Varredura do sinal");
		$this->process($xpdrs); 

		$logs = Log::orderBy('created_at','desc')->limit(50)->get();
		$transponders = Transponder::all();

		/*
		foreach($logs as $log){
			echo '<p>' . $log->description . '<p>' ;
		}
	
		echo '<pre>'; 
		print_r($transponders); 
		echo '</pre>' ;	
		*/

		return view('processnolog', compact('logs','transponders','xpdrs'));

	}

	public function process($transponders)
	{
		/* Varre cada um dos transponders verificando se houve alteração */
		foreach($transponders as &$transponder){
			$this->updateTransponder($transponder);

			/* Varre cada um dos serviços do transponder atual verificando se houve alteração */
			foreach($transponder['services'] as $service){

				/* Verificar caso seja um serviço válido */
				if ( $service['name'] ) {
					$this->updateService($transponder,$service);

					/* Varre cada um dos audios do serviço atual verificando se houve alteração */
					foreach($service['audio_pids'] as $audio_pid){
						$this->updateAudio($service,$audio_pid);
					}
				}
			}
		}

		// Apaga canais/transponders 
		//$this->deleteChannels(json_decode($s[0]->lineup));
	}

	/* Verifica se o transponder locado está no dB.
		Incluir se não estiver e atualiar em caso de mudanças */
	public function updateTransponder(&$transponder)
	{
		/* Busca transponder atual */
		$x = Transponder::where('frequency','=',$transponder['frequency'])
									->where('symbol_rate','=',$transponder['symbol_rate'])
									->where('polarity','=',$transponder['polarity'])
									->first();

		/* Caso o transponder não tenha sido encontrada no dB, incluir */
		if ( !$x ) {
			$x = new Transponder ;
			$x->frequency = $transponder['frequency'] ;
			$x->symbol_rate = $transponder['symbol_rate'] ;
			$x->polarity = $transponder['polarity'] ;
			$x->satellite_id = 1 ;
			$x->tsid = $transponder['tsid'] ;
			$x->orbital = "70W" ;
			$x->dvb_mode = $transponder['dvb_mode'] ;
			$x->network_id = $transponder['network_id'] ;
			$x->onid = $transponder['onid'] ;
			$x->quality =  0 ;
			$x->strength = 0 ;
			$x->created_at = Carbon::now() ;
			$x->updated_at = Carbon::now() ;
			$x->save();
			$this->create_log('transponders',$x->id,'Transponder criado: ' . $x['frequency']);
		}

		/* Cria campo para uso na atualização de serviços */
		$transponder['id'] = $x->id ;

		$logs = array() ;
		if ( $transponder['tsid'] != $x->tsid ) {
			array_push($logs,"TSID mudou de " . $x->tsid . " para " . $transponder['tsid']);
			$x->tsid = $transponder['tsid'] ;
		}		

		if ( $transponder['dvb_mode'] != $x->dvb_mode ) {
			array_push($logs,"dvb_mode mudou de " . $x->dvb_mode . " para " . $transponder['dvb_mode']);
			$x->dvb_mode = $transponder['dvb_mode'] ;
		}		

		if ( $transponder['onid'] != $x->onid ) {
			array_push($logs,"ONID mudou de " . $x->onid . " para " . $transponder['onid']);
			$x->onid = $transponder['onid'] ;
		}		

		if ( $transponder['network_id'] != $x->network_id ) {
			array_push($logs,"network_id mudou de " . $x->network_id . " para " . $transponder['network_id']);
			$x->network_id = $transponder['network_id'] ;
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
						where('transponder_id','=',$transponder['id'])
						->where('svcid','=',$service['service_id'])
						->where('active','1')
						->first();

		if ( !$channel ) {
			$channel = new Service ;
			$channel->name = $service['name'] ;
			$channel->service_type = $service['service_type'] ;
			$channel->codec = $service['video_codec'] ;
			$channel->video_pid = $service['video_pid'] ;
			$channel->pcr_pid = $service['pcr_pid'] ;
			$channel->transponder_id = $transponder['id'] ;
			$channel->epg_pid = $service['epg_pid'] ;
			$channel->svcid = $service['service_id'] ;
			$channel->bouquet_id = $service['bouquet_id'] ;
			$channel->bouquet_name = $service['bouquet_name'] ;
			$channel->viewer_channel = $service['viewer_channel'] ;
			$channel->service_type_id = $service['service_type_id'] ;
			$channel->created_at = Carbon::now() ;
			$channel->updated_at = Carbon::now() ;
			$channel->save();
			array_push($logs,'Criado novo serviço: ' . $service['name']);
		}

		// Salva campo para uso na análise do áudio
		$service['id'] = $channel->id ;
		
		if ( $channel->name != $service['name'] ) {
			array_push($logs,"Nome mudou de \"" . $channel->name . "\" para \"" . $service['name'] ."\"" );
			$channel->name = $service['name'] ;
		}

		if ( $channel->viewer_channel != $service['viewer_channel'] ) {
			// Não registrar pois não é uma informação relevante para o SAC
			//array_push($logs,"viewer_channel mudou de \"" . $channel->viewer_channel . "\" para \"" . $service['viewer_channel ."\"" );
			$channel->viewer_channel = $service['viewer_channel'] ;
		}

		if ( $channel->service_type_id != $service['service_type_id'] ) {
			array_push($logs,"service_type_id mudou de \"" . $channel->service_type_id . "\" para \"" . $service['service_type_id'] ."\"" );
			$channel->service_type_id = $service['service_type_id'] ;
		}

		if ( $channel->bouquet_name != $service['bouquet_name'] ) {
			array_push($logs,"bouquet_name mudou de \"" . $channel->bouquet_name . "\" para \"" . $service['bouquet_name'] ."\"" );
			$channel->bouquet_name = $service['bouquet_name'] ;
		}

		if ( $channel->bouquet_id != $service['bouquet_id'] ) {
			// Não registrar no log pois não é uma informação relevante para o SAC
			// array_push($logs,"bouquet_id mudou de \"" . $channel->bouquet_id . "\" para \"" . $service['bouquet_id ."\"" );
			$channel->bouquet_id = $service['bouquet_id'] ;
		}

		if ( $channel->video_pid != $service['video_pid'] ) {
			array_push($logs,"video_pid mudou de " . $channel->video_pid . " para " . $service['video_pid']);
			$channel->video_pid = $service['video_pid'] ;
		}

		if ( $channel->pcr_pid != $service['pcr_pid'] ) {
			array_push($logs,"pcr_pid mudou de " . $channel->pcr_pid . " para " . $service['pcr_pid']);
			$channel->pcr_pid = $service['pcr_pid'] ;
		}

		if ( $channel->epg_pid != $service['epg_pid'] ) {
			array_push($logs,"epg_pid mudou de " . $channel->epg_pid . " para " . $service['epg_pid']);
			$channel->epg_pid = $service['epg_pid'] ;
		}

		if ( $channel->codec != $service['video_codec'] ) {
			array_push($logs,"codec mudou de " . $channel->codec . " para " . $service['video_codec']);
			$channel->codec = $service['video_codec'] ;
		}
		
		if ( $channel->service_type != $service['service_type'] ) {
			array_push($logs,"service_type mudou de " . $channel->service_type . " para " . $service['service_type']);
			$channel->service_type = $service['service_type'] ;
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

		$audio_channel = Audio::where('service_id','=',$service['id'])->where('pid','=',$audio['pid'])->first();
		if ( !$audio_channel ) {
			$audio_channel = new Audio ;
			$audio_channel->pid = $audio['pid'] ;
			$audio_channel->language = $audio['lang'] ;
			$audio_channel->service_id = $service['id'] ;
			$audio_channel->audio_type = $audio['codec'] ;
			$audio_channel->created_at = Carbon::now() ;
			$audio_channel->updated_at = Carbon::now() ;
			array_push($logs,'Criado novo áudio: ' . $audio_channel->pid);
		}

		if ( $audio_channel->audio_type != $audio['codec'] ) {
			array_push($logs,"Tipo de áudio mudou de " . $audio_channel->audio_type . " para " . $audio['codec']);
			$audio_channel->audio_type = $audio['codec'] ;
		}		

		if ( $audio_channel->language != $audio['lang'] ) {
			array_push($logs,"Idioma mudou de \"" . $audio_channel->language . "\" para \"" . $audio['lang'] . "\"");
			$audio_channel->language = $audio['lang'] ;
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
		
	/* Lê dB e converte resultado para array. 
		Somente transponders locados serão analisados
		Renomeia campos para ficarem iguais aos definidos no dB  
	*/
	public function createCollection () {

		/* Transforma o lineup recebido em collection */
		$query = "select datetime, lineup from dvb.lineup l order by id_lineup desc limit 1" ;
		$s = DB::select(DB::raw($query));
		$transponders = json_decode($s[0]->lineup) ;
		foreach ( $transponders as $transponder ) {
			$xponders = $transponder ;
		}

		/* Converte objeto em array */
		$xponders = json_decode(json_encode($xponders, true), true);

		/* Renomeia campos para ficarem iguais ao dB */
		$xponders = array_map(function($tag) {
			return array(
				'frequency' => $tag['frequency'],
				'symbol_rate' => $tag['symbol_rate'],
				'polarity' => $tag['polarity'],
				'tsid' => $tag['transport_stream_id'],
				'dvb_mode' => $tag['dvb_mode'],
				'network_id' => $tag['network_id'],
				'onid' => $tag['original_network_id'],
				'services' => $tag['services'],
			);
	  	}, $xponders);

		// Carrega resultado de sintonia
		$locks = DB::select(DB::raw('select * from dvb.frequency_lock_success_rate'));
		//$locks[0]->Last = 0 ;
		//$locks[5]->Last = 0 ;
		//$locks[8]->Last = 0 ;

		// varre cada transponder e retira da lista os não sintonizados
		foreach($xponders as $index => &$xponder){
			foreach($locks as $ndx => $lock){
				// Apaga transponder não sintonizado	
				if ( $lock->Last == 0 && $lock->frequency == $xponder['frequency'] ) {
					//echo '<p>Apagando transponder: ' . $lock->Last . '/' . $index . '</p>' ;	
					$this->create_log('process','1','Transponder ' . $xponder['frequency'] . ' não sintonizado!');
					unset($xponders[$index]);
					break;
				}

				if ( $xponder['frequency'] == '12120000' && $lock->frequency == $xponder['frequency']) {
					//$this->create_log('process','1','Alterando tsid para teste');
					//$xponders[$index]['tsid'] = 345 ;
				}
			}
		}
		return $xponders ;
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

