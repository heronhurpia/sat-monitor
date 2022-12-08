<?php

namespace App\Http\Controllers;

use App\Models\Lista;
use App\Models\Audio;
use App\Models\Transponder;
use App\Models\Service;
use App\Models\Log;
use Illuminate\Http\Request;

use DB;
use Carbon\Carbon; 

class AdminController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$this->process();
		
		$quality = DB::select(DB::raw("select * from dvb.get_transponders_lock_grid('quality', '1 month'::interval)"));

		$query = "select date_trunc('hour', datetime) as \"datetime\",frequency,symbol_rate, " .
					"round(avg(quality))::int as \"quality\", " . 
					"round(avg(strength))::int as \"strength\", " . 
					"f.fec_rate, " . 
					"m.modulation_type " . 
					"from dvb.transponders_lock l " . 
					"join dvb.fec_rate f on l.fec_rate = f.fec_rate_id " . 
					"join dvb.modulation_type m on l.modulation_type = m.modulation_type_id " . 
					"where datetime >= (select max(date_trunc('hour', datetime)) from dvb.transponders_lock ) " .
					"group by date_trunc('hour', datetime), frequency, symbol_rate, f.fec_rate, m.modulation_type " . 
					"order by frequency" ;
		$transponders = DB::select(DB::raw($query));
 
		$query = "select datetime, lineup from dvb.lineup l order by id_lineup desc limit 1" ;
		$s = DB::select(DB::raw($query));
		$channels = $s[0];

		/** Lista de transponders */
		$xponders = Transponder::select('*')->orderBy('frequency')->get();
		foreach ( $xponders as &$x ) {
			$to = \Carbon\Carbon::parse(Carbon::now());
			$from = \Carbon\Carbon::parse($x->updated_at);
			$x->interval = $to->diffInMinutes($from) ;

			/** Carrega alterações do transponder */
			$alteracoes = LOG::select('description','created_at')
				->where('table','=','transponders')
				->where('item_id','=',$x->id)
				->whereDate('created_at',[now()->subMinutes(3000), now()])
				->get();
			$x->alteracoes = $alteracoes ;

			/** Carrega serviços associados a este transponder */
			$services = Service::where('transponder_id','=',$x->id)->get();
			$x->services = $services ;

			/** Carrega alterações do serviço */
			foreach ( $x->services as &$service ) {
				$alteracoes = LOG::select('description','created_at')
					->where('table','=','services')
					->where('item_id','=',$service->id)
					->get();
				$service->alteracoes = $alteracoes ;

				$audios = Audio::where('service_id','=',$service->id)->get();
				$service->audios = $audios ;

				foreach ( $audios as &$audio ) {
					$alteracoes = LOG::select('description','created_at')
					->where('table','=','audios')
					->where('item_id','=',$audio->id)
					->get();
					$audio->alteracoes = $alteracoes ;
				}
			}
		}
 
		/* Logs mais recentes */
		$logs = LOG::select('*')->orderBy('id','DESC')->limit(20)->get();

		return view('admin', compact('transponders','quality','channels','logs','xponders'));
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

		$logs = DB::table('public.logs')->get();
		foreach($logs as $log){
			//echo '<p>' . $log->created_at . ' - ' . $log->description . '</p>' ;
		} 
	}
	
	/**
	 * 	Verifica se o transponder existe e, caso não exista, cria um novo.
	 *		Caso o transponder já exista, registra eventuais mudanças.
	 */
	public function updateTransponder($transponder)
	{
		/* Busca transponder atual */
		$x = Transponder::select('*')
			->where('frequency','=',$transponder->frequency)
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
			$x->created_at = Carbon::now() ;
			$x->updated_at = Carbon::now() ;
			$x->orbital = "70W" ;
			$x->dvb_mode = $transponder->dvb_mode ;
			$x->network_id = $transponder->network_id ;
			$x->onid = $transponder->original_network_id ;
			$x->quality =  0 ;
			$x->strength = 0 ;
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

		$channel = Service::where('transponder_id','=',$id)->where('svcid','=',$service->service_id)->first();
		if ( !$channel ) {
			$channel = new Service ;
			$channel->name = $service->name ;
			$channel->video_type_id = 1 ;
			$channel->video_pid = $service->video_pid ;
			$channel->pcr_pid = $service->pcr_pid ;
			$channel->transponder_id = $id ;
			$channel->created_at = Carbon::now() ;
			$channel->updated_at = Carbon::now() ;
			$channel->epg_pid = $service->epg_pid ;
			$channel->svcid = $service->service_id ;
			$channel->save();

			$log = new LOG ;
			$log->table = 'services';
			$log->description = 'Criado novo serviço: ' . $service->name ;
			$log->item_id = $channel->id ;
			$log->created_at = Carbon::now() ;
			$log->updated_at = Carbon::now() ;
			$log->save();
		}
		/** Serviço já existe, registra alterações */
		else {
			$this->logService($channel,$service);
		}
		/* Varre cada um dos serviços */
		foreach($service->audio_pids as $audio){
			$this->updateAudio($channel->id,$audio);
		}
	}

	public function logService($new,$old) {
		$logs = array() ;
		if ( $old->name != $new->name ) {
			array_push($logs,"Nome mudou de " . $old->name . " para " . $new->name);
			$old->name = $new->name ;
		}		

		if ( $old->video_pid != $new->video_pid ) {
			array_push($logs,"video_pid mudou de " . $old->video_pid . " para " . $new->video_pid);
			$old->onid = $new->original_network_id ;
		}		

		if ( $old->pcr_pid != $new->pcr_pid ) {
			array_push($logs,"pcr_pid mudou de " . $old->pcr_pid . " para " . $new->pcr_pid);
			$old->pcr_pid = $new->pcr_pid ;
		}

		if ( $old->epg_pid != $new->epg_pid ) {
			array_push($logs,"epg_pid mudou de " . $old->epg_pid . " para " . $new->epg_pid);
			$old->epg_pid = $new->epg_pid ;
		}
		
		/** Em caso de alterações, atualizar db */
		if ( sizeof($logs) > 0 ) {
			$old->save();
		}

		/* Criar logs para as alterações */
		foreach ( $logs as $log ) {
			$l = new LOG ;
			$l->table = 'services';
			$l->description = $log ;
			$l->item_id = $old->id ;
			$l->created_at = Carbon::now() ;
			$l->updated_at = Carbon::now() ;
			$l->save();
		}
	}

	public function updateAudio($id,$audio){

		$audio_channel = Audio::where('service_id','=',$id)->where('pid','=',$audio->pid)->first();
		if ( !$audio_channel ) {
			$audio_channel = new Audio ;
			$audio_channel->pid = $audio->pid ;
			$audio_channel->language_id = 1 ;
			$audio_channel->service_id = $id ;
			$audio_channel->audio_type_id = $audio->codec ;
			$audio_channel->created_at = Carbon::now() ;
			$audio_channel->updated_at = Carbon::now() ;
			$audio_channel->save();

			$log = new LOG ;
			$log->table = 'audios';
			$log->description = 'Criado novo áudio: ' . $audio_channel->pid ;
			$log->item_id = $id ;
			$log->created_at = Carbon::now() ;
			$log->updated_at = Carbon::now() ;
			$log->save();
		}
		/** Audio já existe, registra alterações */
		else {
			$this->logAudio($audio,$audio_channel);
		}
	}

	public function logAudio($new,$old) {
		$logs = array() ;
		if ( $old->audio_type_id != $new->codec ) {
			array_push($logs,"Tipo de áudio mudou de " . $old->audio_type_id . " para " . $new->codec);
			$old->audio_type_id = $new->codec ;
		}		

		/*
		if ( $old->language_id != $new->language_id ) {
			array_push($logs,"language_id mudou de " . $old->language_id . " para " . $new->language_id);
			$old->language_id = $new->language_id ;
		}
		*/		

		/** Em caso de alterações, atualizar db */
		if ( sizeof($logs) > 0 ) {
			$old->save();
		}

		/* Criar logs para as alterações */
		foreach ( $logs as $log ) {
			$l = new Audio ;
			$l->table = 'audios';
			$l->description = $log ;
			$l->item_id = $old->id ;
			$l->created_at = Carbon::now() ;
			$l->updated_at = Carbon::now() ;
			$l->save();
		}
	}

}
