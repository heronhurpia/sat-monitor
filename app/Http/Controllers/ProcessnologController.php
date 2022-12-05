<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lista;
use App\Models\Audio;
use App\Models\Transponder;
use App\Models\Service;

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
		/** Atualiza tabelas e calcula alterações */
		$this->processnolog(); 

		// Totalizações para relatórios
		$tv = Service::where('video_pid','>','0')->count();
		$hevc = Service::where('codec','=','HEVC')->count();
		$radio = Service::where('video_pid','=','0')->count();
	
		$query = "select datetime, lineup from dvb.lineup l order by id_lineup desc limit 1" ;
		$s = DB::select(DB::raw($query));
		$transponders = json_decode($s[0]->lineup) ;

//		return view('processnolog', compact('tv','radio','hevc','transponders'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function processnolog()
	{
		echo	"<p>processnolog</p>" ;
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
		echo	"<p>updateTransponder</p>" ;

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
		}

		/* Varre cada um dos serviços do transponder atual */
		foreach($transponder->services as $service){
			if ( $service->name ) {
				$this->updateService($x->id,$service);
			}
		}
		return ;
	}

	public function updateService($id,$service){

		echo	"<p>updateService</p>" ;
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
			$channel->bouquet_id = $service->bouquet_id ;
			$channel->bouquet_name = $service->bouquet_name ;
			$channel->created_at = Carbon::now() ;
			$channel->updated_at = Carbon::now() ;
			$channel->save();
		}
		else {
			$channel->name = $service->name ;
			$channel->bouquet_name = $service->bouquet_name ;
			$channel->bouquet_id = $service->bouquet_id ;
			$channel->video_pid = $service->video_pid ;
			$channel->pcr_pid = $service->pcr_pid ;
			$channel->epg_pid = $service->epg_pid ;
			$channel->codec = $service->video_codec ;
			$channel->service_type = $service->service_type ;
			$channel->updated_at = Carbon::now() ;
			$channel->save();
		}

		/* Varre cada um dos serviços */
		foreach($service->audio_pids as $audio){
			$this->updateAudio($channel->id,$audio);
		}
	}


	public function updateAudio($id,$audio){

		$audio_channel = Audio::where('service_id','=',$id)->where('pid','=',$audio->pid)->first();
		if ( !$audio_channel ) {
			$audio_channel = new Audio ;
			$audio_channel->pid = $audio->pid ;
			$audio_channel->language = $audio->lang ;
			$audio_channel->service_id = $id ;
			$audio_channel->audio_type = $audio->codec ;
			$audio_channel->created_at = Carbon::now() ;
			$audio_channel->updated_at = Carbon::now() ;
			$audio_channel->save();
		}
		else {
			$audio_channel->audio_type = $audio->codec ;
			$audio_channel->language = $audio->lang ;
			$audio_channel->updated_at = Carbon::now() ;
			$audio_channel->save();
		}
	}
}

