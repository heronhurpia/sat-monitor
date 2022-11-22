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

class ProcessController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		/** Atualiza tabelas e calcula alterações */
		$this->process();

		$log = new LOG ;
		$log->table = 'process';
		$log->item_id = 1 ;
		$log->description = 'Última varredura' ;
		$log->created_at = Carbon::now() ;
		$log->updated_at = Carbon::now() ;
		$log->save();

		// Totalizações para relatórios
		$tv = Service::where('video_pid','>','0')->count();
		$hevc = Service::where('codec','=','HEVC')->count();
		$radio = Service::where('video_pid','=','0')->count();
		$logs = Log::orderBy('created_at','desc')
		->limit(100)
		->get();
		
		return view('process', compact('tv','radio','hevc','logs'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function process()
	{
		$query = "select datetime, lineup from dvb.lineup l order by id_lineup desc limit 1" ;
		$s = DB::select(DB::raw($query));

		/* Varre cada um dos transponders */
		foreach(json_decode($s[0]->lineup) as $transponders){
			foreach($transponders as $transponder){
				$this->updateTransponder($transponder);
			}
		}
	}
	
	/**
	 * 	Verifica se o transponder existe e, caso não exista, cria um novo.
	 *		Caso o transponder já exista, registra eventuais mudanças.
	 */
	public function updateTransponder($transponder)
	{
		/* Busca transponder atual */
		$x = Transponder::where('frequency','=',$transponder->frequency)
									->where('symbol_rate','=',$transponder->symbol_rate)
									->where('polarity','=',$transponder->polarity)
									->first();

		/* Caso o transponder não tenha sido encontrada no dB, incluir */
		if ( !$x ) {
			//echo '<p>Criar transponder</p>' ;
			/* Insere nova frequencia no dB */
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

			$log = new LOG ;
			$log->table = 'transponders';
			$log->description = 'Criado novo transponder: ' . $x->frequency ;
			$log->item_id = $x->id ;
			$log->created_at = Carbon::now() ;
			$log->updated_at = Carbon::now() ;
			$log->save();
		}
		/** Transponder já existe, registra alterações */
		else {
			$this->logTransponder($transponder,$x);
		}

		/* Varre cada um dos serviços do transponder atual */
		foreach($transponder->services as $service){
			if ( $service->name ) {
				$this->updateService($x->id,$service);
			}
		}
		return ;
	}

	public function logTransponder($new,$old) {
		$logs = array() ;
	
		if ( $old->tsid != $new->transport_stream_id ) {
			array_push($logs,"TSID mudou de " . $old->tsid . " para " . $new->transport_stream_id);
			$old->tsid = $new->transport_stream_id ;
		}		

		if ( $old->dvb_mode != $new->dvb_mode ) {
			array_push($logs,"dvb_mode mudou de " . $old->dvb_mode . " para " . $new->dvb_mode);
			$old->dvb_mode = $new->dvb_mode ;
		}		

		if ( $old->onid != $new->original_network_id ) {
			array_push($logs,"ONID mudou de " . $old->onid . " para " . $new->original_network_id);
			$old->onid = $new->original_network_id ;
		}		

		if ( $old->network_id != $new->network_id ) {
			array_push($logs,"network_id mudou de " . $old->network_id . " para " . $new->network_id);
			$old->network_id = $new->network_id ;
		}
		
		/** Em caso de alterações, atualizar db */
		if ( sizeof($logs) > 0 ) {
			$old->save() ;
		}

		/* Criar logs para as alterações */
		foreach ( $logs as $log ) {
			$l = new LOG ;
			$l->table = 'transponders';
			$l->description = $log ;
			$l->item_id = $old->id ;
			$l->created_at = Carbon::now() ;
			$l->updated_at = Carbon::now() ;
			$l->save();
		}
	}

	public function updateService($id,$service){

		$logs = array() ;
		$channel = Service::where('transponder_id','=',$id)->where('svcid','=',$service->service_id)->first();
		if ( !$channel ) {
			$channel = new Service ;
			$channel->name = $service->name ;
			$channel->service_type = $service->service_type ;
			$channel->codec = $service->video_codec ;
			$channel->video_pid = $service->video_pid ;
			$channel->pcr_pid = $service->pcr_pid ;
			$channel->transponder_id = $id ;
			$channel->epg_pid = $service->epg_pid ;
			$channel->svcid = $service->service_id ;
			$channel->created_at = Carbon::now() ;
			$channel->updated_at = Carbon::now() ;
			array_push($logs,'Criado novo serviço: ' . $service->name);
		}

		if ( $channel->name != $service->name ) {
			array_push($logs,"Nome mudou de \"" . $channel->name . "\" para \"" . $service->name ."\"" );
			$channel->name = $service->name ;
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
			$channel->save();
		}

		/* Criar logs para as alterações */
		foreach ( $logs as $log ) {
			$l = new LOG ;
			$l->table = 'services';
			$l->description = $log ;
			$l->item_id = $channel->id ;
			$l->created_at = Carbon::now() ;
			$l->updated_at = Carbon::now() ;
			$l->save();
		}

		/* Varre cada um dos serviços */
		foreach($service->audio_pids as $audio){
			$this->updateAudio($channel->id,$audio);
		}
	}



	public function updateAudio($id,$audio){

		/** Lista de alterações */
		$logs = array() ;

		$audio_channel = Audio::where('service_id','=',$id)->where('pid','=',$audio->pid)->first();
		if ( !$audio_channel ) {
			$audio_channel = new Audio ;
			$audio_channel->pid = $audio->pid ;
			$audio_channel->language = $audio->lang ;
			$audio_channel->service_id = $id ;
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
		}

		/* Criar logs para as alterações */
		foreach ( $logs as $log ) {
			$l = new Log ;
			$l->table = 'audios';
			$l->description = $log ;
			$l->item_id = $audio_channel->id ;
			$l->created_at = Carbon::now() ;
			$l->updated_at = Carbon::now() ;
			$l->save();
		}
	}
}
