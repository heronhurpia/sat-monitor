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

class ListaController extends Controller
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
		$to = \Carbon\Carbon::parse(Carbon::now());

		/* Constante com limite da última alteração em minutos */
		//$limit = 100 ;
		$data = Session::get('data');
		if ( isset ( $data ) ) {
			$inicio = $data ;	
		}
		else {
			$inicio = now()->subDays(1);
		}


		/* Busca todos os transponders */
		$transponders = Transponder::orderBy('frequency')->get();

		/** Busca o serviços de cada um dos transponders */
		foreach ( $transponders as &$transponder ) {

			/** Carrega alterações do transponder */
			$alteracoes = LOG::where('table','=','transponders')
				->where('item_id','=',$transponder->id)
				->where('created_at','>',$inicio)
				->orderBy('created_at','desc')
				->get();
			$transponder->count_logs = 0 ;

			/* Verifica se existem alterações para este serviço */
			if ( count($alteracoes) > 0 ) {

				/** Indica que serviço teve alteração */
				$transponder->alteracoes = $alteracoes ;
				$transponder->has_logs = true ;
				$transponder->count_logs += count($alteracoes) ;
			
				/* Calcula quando foi feita a última alteração */
				foreach ( $alteracoes as &$alteracao ) {
					$from = \Carbon\Carbon::parse($alteracao->created_at);
					$alteracao->last = $to->diffInMinutes($from) ;
				}
			}

			/** Carrega serviços associados a este transponder */
			$services = Service::where('transponder_id','=',$transponder->id)->get();
			$transponder->services = $services ;

			/* Busca os serviços de cada um dos transponders */
			foreach ( $services as &$service ) {
				/** Carrega audios associados a este serviço */
				$audios = Audio::where('service_id','=',$service->id)->get();
				$service->audios = $audios ;

				/** Carrega alterações do canal */
				$alteracoes = LOG::where('table','=','services')
					->where('item_id','=',$service->id)
					->where('created_at','>',$inicio)
					->orderBy('created_at','desc')
					->get();
				$service->count_logs = 0 ;
				/* Verifica se existem alterações para este serviço */
				if ( count($alteracoes) > 0 ) {

					/* Calcula quando foi feita a última alteração */
					foreach ( $alteracoes as &$alteracao ) {
						$from = \Carbon\Carbon::parse($alteracao->created_at);
						$alteracao->last = $to->diffInMinutes($from) ;
					}

					/** Indica que serviço teve alteração */
					$service->alteracoes = $alteracoes ;
					$service->has_logs = true ;
					$transponder->has_logs = true ;
					$transponder->count_logs += count($alteracoes) ;
					$service->count_logs += count($alteracoes) ;
				}

				/* Busca alterações dos serviços de áudio */
				foreach ( $audios as &$audio ) {

					/** Carrega alterações do áudio */
					$alteracoes = LOG::where('table','=','audios')
						->where('item_id','=',$audio->id)
						->where('created_at','>',$inicio)
						->orderBy('created_at','desc')
						->get();
					$audio->count_logs = 0 ;
					
					/* Verifica se existem alterações para este áudio */
					if ( count($alteracoes) > 0 ) {
					
						/* Calcula quando foi feita a última alteração */
						foreach ( $alteracoes as &$alteracao ) {
							$from = \Carbon\Carbon::parse($alteracao->created_at);
							$alteracao->last = $to->diffInMinutes($from) ;
						}
						$audio->alteracoes = $alteracoes ;

						/** Indica que serviço teve alteração */
						$service->has_logs = true ;
						$transponder->has_logs = true ;
						$transponder->count_logs += count($alteracoes) ;
						$service->count_logs += count($alteracoes) ;
						$audio->count_logs += count($alteracoes) ;
					}
				}
			}
		}

		$logs = Log::where('created_at','>',$inicio)
			->orderBy('created_at','desc')
			->get();

		// $query = "select datetime, lineup from dvb.lineup l order by id_lineup desc limit 1" ;
		// $list = DB::select(DB::raw($query));
	
		// Totalizações para relatórios
		$tv = Service::where('video_pid','>','0')->count();
		$hevc = Service::where('codec','=','HEVC')->count();
		$radio = Service::where('video_pid','=','0')->count();

		return view('lista', compact('transponders','logs','tv','radio','hevc','inicio'));
	}

	public function find( Request $request)
	{
		$data = $request->input('inicio');

		return redirect('lista')->with(['data' => $data]);
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

/*
{"line_up": [
	{	"dvb_mode": "DVB2", 
		"polarity": "Vertical", 
		"services": [
			{	
				"name": "TESTE-X", 
				"epg_pid": 38896, 
				"pcr_pid": 32, 
				"video_pid": 32, 
				"audio_pids": [
					{
						"pid": 33, 
						"lang": "", 
						"codec": "AAC"
					}
				], 
				"service_id": 300, 
				"video_codec": "HEVC", 
				"service_type": "0x1f", 
				"regionalizacao": 0, 
				"viewer_channel": 0
			}
		], 
		"frequency": 11720000, 
		"network_id": 45626, 
		"symbol_rate": 34300, 
		"original_network_id": 45626, 
		"transport_stream_id": 110
	}, 
		{"dvb_mode": "DVB2", "polarity": "Horizontal", "services": [{"name": "SBT Belem", "epg_pid": 38896, "pcr_pid": 1801, "video_pid": 1801, "audio_pids": [{"pid": 1811, "lang": "por", "codec": "AAC"}, {"pid": 1812, "lang": "AD", "codec": "AAC"}], "service_id": 1018, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 0, "viewer_channel": 0}, {"name": "TV Cultura", "epg_pid": 38896, "pcr_pid": 1401, "video_pid": 1401, "audio_pids": [{"pid": 1411, "lang": "por", "codec": "AAC"}, {"pid": 1412, "lang": "AD", "codec": "AAC"}], "service_id": 1014, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 2}, {"name": "REDETV", "epg_pid": 38896, "pcr_pid": 101, "video_pid": 101, "audio_pids": [{"pid": 111, "lang": "por", "codec": "AAC"}, {"pid": 112, "lang": "AD", "codec": "AAC"}], "service_id": 1002, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 9}, {"name": "TV APARECIDA", "epg_pid": 38896, "pcr_pid": 201, "video_pid": 201, "audio_pids": [{"pid": 211, "lang": "por", "codec": "AAC"}, {"pid": 212, "lang": "AD", "codec": "AAC"}], "service_id": 1003, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 41}, {"name": "TV CULTURA", "epg_pid": 38896, "pcr_pid": 301, "video_pid": 301, "audio_pids": [{"pid": 311, "lang": "por", "codec": "AAC"}, {"pid": 312, "lang": "AD", "codec": "AAC"}], "service_id": 1001, "video_codec": "HEVC", "service_type": "0x1f", "regionalizacao": 2, "viewer_channel": 302}, {"name": "REDE VIDA EDU", "epg_pid": 38896, "pcr_pid": 1201, "video_pid": 1201, "audio_pids": [{"pid": 1211, "lang": "por", "codec": "AAC"}, {"pid": 1212, "lang": "AD", "codec": "AAC"}], "service_id": 1012, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 312}, {"name": "REDE VIDA EDU 2", "epg_pid": 38896, "pcr_pid": 1301, "video_pid": 1301, "audio_pids": [{"pid": 1311, "lang": "por", "codec": "AAC"}, {"pid": 1312, "lang": "AD", "codec": "AAC"}], "service_id": 1013, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 317}, {"name": "ISTV", "epg_pid": 38896, "pcr_pid": 401, "video_pid": 401, "audio_pids": [{"pid": 411, "lang": "por", "codec": "AAC"}, {"pid": 412, "lang": "AD", "codec": "AAC"}], "service_id": 1004, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 1004}, {"name": "SAUDADE FM", "epg_pid": 38896, "pcr_pid": 511, "video_pid": 0, "audio_pids": [{"pid": 511, "lang": "por", "codec": "AAC"}], "service_id": 1005, "video_codec": "Nenhum", "service_type": "Digital Radio Sound Service", "regionalizacao": 2, "viewer_channel": 1005}, {"name": "APARECIDA FM", "epg_pid": 38896, "pcr_pid": 611, "video_pid": 0, "audio_pids": [{"pid": 611, "lang": "por", "codec": "AAC"}], "service_id": 1006, "video_codec": "Nenhum", "service_type": "Digital Radio Sound Service", "regionalizacao": 2, "viewer_channel": 1006}, {"name": "REDE VIDA FM", "epg_pid": 38896, "pcr_pid": 711, "video_pid": 0, "audio_pids": [{"pid": 711, "lang": "por", "codec": "AAC"}], "service_id": 1007, "video_codec": "Nenhum", "service_type": "Digital Radio Sound Service", "regionalizacao": 2, "viewer_channel": 1007}, {"name": "REDE BRASIL", "epg_pid": 38896, "pcr_pid": 801, "video_pid": 801, "audio_pids": [{"pid": 811, "lang": "por", "codec": "AAC"}, {"pid": 812, "lang": "AD", "codec": "AAC"}], "service_id": 1008, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 1008}, {"name": "RBTV", "epg_pid": 38896, "pcr_pid": 901, "video_pid": 901, "audio_pids": [{"pid": 911, "lang": "por", "codec": "AAC"}, {"pid": 912, "lang": "AD", "codec": "AAC"}], "service_id": 1009, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 1009}, {"name": "TV TEMPLO", "epg_pid": 38896, "pcr_pid": 1001, "video_pid": 1001, "audio_pids": [{"pid": 1011, "lang": "por", "codec": "AAC"}, {"pid": 1012, "lang": "AD", "codec": "AAC"}], "service_id": 1010, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 1010}, {"name": "TV PADRE CICERO", "epg_pid": 38896, "pcr_pid": 1101, "video_pid": 1101, "audio_pids": [{"pid": 1111, "lang": "por", "codec": "AAC"}, {"pid": 1112, "lang": "AD", "codec": "AAC"}], "service_id": 1011, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 1011}], "frequency": 11740000, "network_id": 45626, "symbol_rate": 34300, "original_network_id": 45626, "transport_stream_id": 111}, {"dvb_mode": "DVB2", "polarity": "Vertical", "services": [{"name": "Band Porto Alegre", "epg_pid": 38896, "pcr_pid": 3973, "video_pid": 3973, "audio_pids": [{"pid": 3971, "lang": "por", "codec": "MP2"}, {"pid": 3972, "lang": "aud", "codec": "MP2"}], "service_id": 355, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "Band Paraná", "epg_pid": 38896, "pcr_pid": 3983, "video_pid": 3983, "audio_pids": [{"pid": 3981, "lang": "por", "codec": "MP2"}, {"pid": 3982, "lang": "aud", "codec": "MP2"}], "service_id": 356, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "Band Campinas", "epg_pid": 38896, "pcr_pid": 3993, "video_pid": 3993, "audio_pids": [{"pid": 3991, "lang": "por", "codec": "MP2"}, {"pid": 3992, "lang": "aud", "codec": "MP2"}], "service_id": 357, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "Band Taubaté", "epg_pid": 38896, "pcr_pid": 4003, "video_pid": 4003, "audio_pids": [{"pid": 4001, "lang": "por", "codec": "MP2"}, {"pid": 4002, "lang": "aud", "codec": "MP2"}], "service_id": 358, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "Band Pres. Prudente", "epg_pid": 38896, "pcr_pid": 4013, "video_pid": 4013, "audio_pids": [{"pid": 4011, "lang": "por", "codec": "MP2"}, {"pid": 4012, "lang": "aud", "codec": "MP2"}], "service_id": 359, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "Band Manaus", "epg_pid": 38896, "pcr_pid": 4023, "video_pid": 4023, "audio_pids": [{"pid": 4021, "lang": "por", "codec": "MP2"}, {"pid": 4022, "lang": "aud", "codec": "MP2"}], "service_id": 360, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "Rede Super", "epg_pid": 38896, "pcr_pid": 3893, "video_pid": 3893, "audio_pids": [{"pid": 3891, "lang": "por", "codec": "MP2"}], "service_id": 347, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 23}, {"name": "Rede Gospel", "epg_pid": 38896, "pcr_pid": 3913, "video_pid": 3913, "audio_pids": [{"pid": 3911, "lang": "por", "codec": "MP2"}], "service_id": 349, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 123}, {"name": "Religião de Deus", "epg_pid": 38896, "pcr_pid": 3873, "video_pid": 3873, "audio_pids": [{"pid": 3871, "lang": "", "codec": "MP2"}, {"pid": 3872, "lang": "", "codec": "MP2"}], "service_id": 345, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 345}, {"name": "Canal Rural", "epg_pid": 38896, "pcr_pid": 3883, "video_pid": 3883, "audio_pids": [{"pid": 3881, "lang": "", "codec": "MP2"}], "service_id": 346, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 346}, {"name": "Band Ceará", "epg_pid": 38896, "pcr_pid": 4033, "video_pid": 4033, "audio_pids": [{"pid": 4031, "lang": "por", "codec": "MP2"}, {"pid": 4032, "lang": "aud", "codec": "MP2"}], "service_id": 361, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 361}, {"name": "Radio Gospel", "epg_pid": 38896, "pcr_pid": 4091, "video_pid": 0, "audio_pids": [{"pid": 4091, "lang": "", "codec": "MP1"}], "service_id": 368, "video_codec": "Nenhum", "service_type": "Digital Radio Sound Service", "regionalizacao": 2, "viewer_channel": 368}, {"name": "TESTE 11", "epg_pid": 38896, "pcr_pid": 0, "video_pid": 0, "audio_pids": [], "service_id": 801, "video_codec": "Nenhum", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 0}, {"name": "TESTE 12", "epg_pid": 38896, "pcr_pid": 0, "video_pid": 0, "audio_pids": [], "service_id": 812, "video_codec": "Nenhum", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 0, "viewer_channel": 0}, {"name": "TESTE 13", "epg_pid": 38896, "pcr_pid": 0, "video_pid": 0, "audio_pids": [], "service_id": 803, "video_codec": "Nenhum", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 0}, {"name": "TESTE 14", "epg_pid": 38896, "pcr_pid": 3953, "video_pid": 3953, "audio_pids": [{"pid": 3951, "lang": "", "codec": "MP2"}, {"pid": 3952, "lang": "", "codec": "MP2"}], "service_id": 804, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 0}, {"name": "TESTE 15", "epg_pid": 38896, "pcr_pid": 3963, "video_pid": 3963, "audio_pids": [{"pid": 3961, "lang": "", "codec": "MP2"}, {"pid": 3962, "lang": "", "codec": "MP2"}], "service_id": 805, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 0}], "frequency": 12000000, "network_id": 45626, "symbol_rate": 29892, "original_network_id": 45626, "transport_stream_id": 103}, {"dvb_mode": "DVB2", "polarity": "Vertical", "services": [{"name": "", "epg_pid": 38896, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 7001, "video_codec": "Nenhum", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 38896, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 6021, "video_codec": "Nenhum", "service_type": "0x90", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 38896, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 6011, "video_codec": "Nenhum", "service_type": "0x90", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 38896, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 6000, "video_codec": "Nenhum", "service_type": "0x90", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 38896, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 1794, "video_codec": "Nenhum", "service_type": "0x90", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 38896, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 1792, "video_codec": "Nenhum", "service_type": "0x90", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 38896, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 1790, "video_codec": "Nenhum", "service_type": "0x90", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 38896, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 1788, "video_codec": "Nenhum", "service_type": "0x90", "regionalizacao": 0, "viewer_channel": 0}, {"name": "SBT RIO", "epg_pid": 38896, "pcr_pid": 5183, "video_pid": 5183, "audio_pids": [{"pid": 5181, "lang": "por", "codec": "MP2"}, {"pid": 5182, "lang": "sap", "codec": "MP2"}], "service_id": 309, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 4}, {"name": "TV Centro América", "epg_pid": 38896, "pcr_pid": 5133, "video_pid": 5133, "audio_pids": [{"pid": 5131, "lang": "dub", "codec": "AC3"}, {"pid": 5132, "lang": "aud", "codec": "AC3"}], "service_id": 304, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "TV Mirante", "epg_pid": 38896, "pcr_pid": 5143, "video_pid": 5143, "audio_pids": [{"pid": 5141, "lang": "dub", "codec": "MP2"}, {"pid": 5142, "lang": "aud", "codec": "MP2"}], "service_id": 305, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "EPTV Varginha", "epg_pid": 38896, "pcr_pid": 5123, "video_pid": 5123, "audio_pids": [{"pid": 5121, "lang": "dub", "codec": "MP2"}, {"pid": 5122, "lang": "aud", "codec": "MP2"}], "service_id": 303, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "TV RECORD NACIONAL ", "epg_pid": 38896, "pcr_pid": 5233, "video_pid": 5233, "audio_pids": [{"pid": 5231, "lang": "por", "codec": "MP2"}, {"pid": 5232, "lang": "aud", "codec": "MP2"}], "service_id": 314, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 7}, {"name": "IDEAL TV", "epg_pid": 38896, "pcr_pid": 5193, "video_pid": 5193, "audio_pids": [{"pid": 5191, "lang": "", "codec": "MP2"}, {"pid": 5192, "lang": "", "codec": "MP2"}], "service_id": 310, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 32}, {"name": "REDE VIDA", "epg_pid": 38896, "pcr_pid": 5253, "video_pid": 5253, "audio_pids": [{"pid": 5251, "lang": "", "codec": "MP2"}, {"pid": 5252, "lang": "", "codec": "MP2"}], "service_id": 316, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 34}, {"name": "RECORD NEWS", "epg_pid": 38896, "pcr_pid": 5203, "video_pid": 5203, "audio_pids": [{"pid": 5201, "lang": "por", "codec": "MP2"}, {"pid": 5202, "lang": "aud", "codec": "MP2"}], "service_id": 311, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 42}, {"name": "TV Novo Tempo", "epg_pid": 38896, "pcr_pid": 5163, "video_pid": 5163, "audio_pids": [{"pid": 5161, "lang": "por", "codec": "MP2"}, {"pid": 5162, "lang": "aud", "codec": "MP2"}], "service_id": 307, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 46}, {"name": "TV SÉCULO 21", "epg_pid": 38896, "pcr_pid": 5243, "video_pid": 5243, "audio_pids": [{"pid": 5241, "lang": "", "codec": "MP2"}, {"pid": 5242, "lang": "", "codec": "MP2"}], "service_id": 315, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 58}, {"name": "TV CANÇÃO NOVA", "epg_pid": 38896, "pcr_pid": 5273, "video_pid": 5273, "audio_pids": [{"pid": 5271, "lang": "por", "codec": "MP2"}, {"pid": 5272, "lang": "aud", "codec": "MP2"}], "service_id": 318, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 59}, {"name": "REDE AMERICA", "epg_pid": 38896, "pcr_pid": 5173, "video_pid": 5173, "audio_pids": [{"pid": 5171, "lang": "", "codec": "MP2"}], "service_id": 308, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 121}, {"name": "TV Morena", "epg_pid": 38896, "pcr_pid": 5153, "video_pid": 5153, "audio_pids": [{"pid": 5151, "lang": "", "codec": "MP2"}, {"pid": 5152, "lang": "", "codec": "MP2"}], "service_id": 306, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 306}, {"name": "SBT BSB", "epg_pid": 38896, "pcr_pid": 5223, "video_pid": 5223, "audio_pids": [{"pid": 5221, "lang": "por", "codec": "MP2"}, {"pid": 5222, "lang": "sap", "codec": "MP2"}], "service_id": 313, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 313}, {"name": "TESTE TVRO", "epg_pid": 38896, "pcr_pid": 5283, "video_pid": 5283, "audio_pids": [{"pid": 5281, "lang": "", "codec": "MP2"}, {"pid": 5282, "lang": "", "codec": "MP2"}], "service_id": 319, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 319}, {"name": "TV HORIZONTE", "epg_pid": 38896, "pcr_pid": 5293, "video_pid": 5293, "audio_pids": [{"pid": 5291, "lang": "", "codec": "MP2"}], "service_id": 320, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 320}], "frequency": 12120000, "network_id": 45626, "symbol_rate": 29892, "original_network_id": 45626, "transport_stream_id": 101}, {"dvb_mode": "DVB2", "polarity": "Vertical", "services": [{"name": "SBT RS", "epg_pid": 38896, "pcr_pid": 3473, "video_pid": 3473, "audio_pids": [{"pid": 3471, "lang": "por", "codec": "MP2"}, {"pid": 3472, "lang": "sap", "codec": "MP2"}], "service_id": 340, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 4}, {"name": "SBT ", "epg_pid": 38896, "pcr_pid": 3343, "video_pid": 3343, "audio_pids": [{"pid": 3341, "lang": "por", "codec": "MP2"}, {"pid": 3342, "lang": "sap", "codec": "MP2"}], "service_id": 325, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 4}, {"name": "Rede Gazeta SP", "epg_pid": 38896, "pcr_pid": 3433, "video_pid": 3433, "audio_pids": [{"pid": 3431, "lang": "por", "codec": "MP2"}, {"pid": 3432, "lang": "aud", "codec": "MP2"}], "service_id": 334, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 11}, {"name": "BAND SP", "epg_pid": 38896, "pcr_pid": 3313, "video_pid": 3313, "audio_pids": [{"pid": 3311, "lang": "por", "codec": "MP2"}, {"pid": 3312, "lang": "aud", "codec": "MP2"}], "service_id": 322, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "BAND BA", "epg_pid": 38896, "pcr_pid": 3463, "video_pid": 3463, "audio_pids": [{"pid": 3461, "lang": "por", "codec": "MP2"}, {"pid": 3462, "lang": "aud", "codec": "MP2"}], "service_id": 337, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "BAND MG", "epg_pid": 38896, "pcr_pid": 3453, "video_pid": 3453, "audio_pids": [{"pid": 3451, "lang": "por", "codec": "MP2"}, {"pid": 3452, "lang": "aud", "codec": "MP2"}], "service_id": 336, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "BAND SAT", "epg_pid": 38896, "pcr_pid": 3863, "video_pid": 3863, "audio_pids": [{"pid": 3861, "lang": "por", "codec": "MP2"}, {"pid": 3862, "lang": "sap", "codec": "MP2"}], "service_id": 321, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 13}, {"name": "Rede 21", "epg_pid": 38896, "pcr_pid": 3325, "video_pid": 3325, "audio_pids": [{"pid": 3321, "lang": "por", "codec": "MP2"}, {"pid": 3322, "lang": "por", "codec": "MP2"}], "service_id": 323, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 21}, {"name": "PAI ETERNO", "epg_pid": 38896, "pcr_pid": 3403, "video_pid": 3403, "audio_pids": [{"pid": 3401, "lang": "por", "codec": "MP2"}], "service_id": 331, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 25}, {"name": "Terra Viva", "epg_pid": 38896, "pcr_pid": 3335, "video_pid": 3335, "audio_pids": [{"pid": 3331, "lang": "", "codec": "MP2"}], "service_id": 324, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 49}, {"name": "TESTE TVRO 2", "epg_pid": 38896, "pcr_pid": 0, "video_pid": 0, "audio_pids": [], "service_id": 326, "video_codec": "Nenhum", "service_type": "0x1f", "regionalizacao": 2, "viewer_channel": 326}, {"name": "REDE FAMILIA", "epg_pid": 38896, "pcr_pid": 3365, "video_pid": 3365, "audio_pids": [{"pid": 3361, "lang": "", "codec": "MP2"}, {"pid": 3362, "lang": "", "codec": "MP2"}], "service_id": 327, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 327}, {"name": "SBN", "epg_pid": 38896, "pcr_pid": 2460, "video_pid": 2460, "audio_pids": [{"pid": 2420, "lang": "por", "codec": "MP2"}, {"pid": 2430, "lang": "spa", "codec": "MP2"}], "service_id": 328, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 328}, {"name": "EVANGELIZAR", "epg_pid": 38896, "pcr_pid": 3385, "video_pid": 3385, "audio_pids": [{"pid": 3381, "lang": "por", "codec": "MP2"}], "service_id": 329, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 329}, {"name": "TV VERDADE", "epg_pid": 38896, "pcr_pid": 3395, "video_pid": 3395, "audio_pids": [{"pid": 3391, "lang": "por", "codec": "MP2"}, {"pid": 3392, "lang": "eng", "codec": "MP2"}], "service_id": 330, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 330}, {"name": "REDE CNT", "epg_pid": 38896, "pcr_pid": 3415, "video_pid": 3415, "audio_pids": [{"pid": 3411, "lang": "", "codec": "MP2"}, {"pid": 3412, "lang": "", "codec": "MP2"}], "service_id": 332, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 332}, {"name": "CNT RJ", "epg_pid": 38896, "pcr_pid": 3425, "video_pid": 3425, "audio_pids": [{"pid": 3421, "lang": "", "codec": "MP2"}, {"pid": 3422, "lang": "", "codec": "MP2"}], "service_id": 333, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 333}, {"name": "JOVEM PAN NEWS", "epg_pid": 38896, "pcr_pid": 3445, "video_pid": 3445, "audio_pids": [{"pid": 3441, "lang": "", "codec": "MP2"}, {"pid": 3442, "lang": "", "codec": "MP2"}], "service_id": 335, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 335}, {"name": "BAND RJ", "epg_pid": 38896, "pcr_pid": 3483, "video_pid": 3483, "audio_pids": [{"pid": 3481, "lang": "por", "codec": "MP2"}, {"pid": 3482, "lang": "aud", "codec": "MP2"}], "service_id": 343, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 343}, {"name": "SBT SP", "epg_pid": 38896, "pcr_pid": 3493, "video_pid": 3493, "audio_pids": [{"pid": 3491, "lang": "por", "codec": "MP2"}, {"pid": 3492, "lang": "sap", "codec": "MP2"}], "service_id": 344, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 0, "viewer_channel": 0}], "frequency": 12160000, "network_id": 45626, "symbol_rate": 29892, "original_network_id": 45626, "transport_stream_id": 102}, {"dvb_mode": "DVB2", "polarity": "Horizontal", "services": [{"name": "Globo HD Minas", "epg_pid": 38896, "pcr_pid": 5703, "video_pid": 5703, "audio_pids": [{"pid": 5701, "lang": "por", "codec": "AAC"}, {"pid": 5702, "lang": "SAP", "codec": "AAC"}, {"pid": 458, "lang": "dub", "codec": "MP1"}, {"pid": 5708, "lang": "aud", "codec": "MP1"}], "service_id": 458, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "Globo Nordeste HD", "epg_pid": 38896, "pcr_pid": 5713, "video_pid": 5713, "audio_pids": [{"pid": 5711, "lang": "por", "codec": "AAC"}, {"pid": 5712, "lang": "SAP", "codec": "AAC"}, {"pid": 459, "lang": "dub", "codec": "MP1"}, {"pid": 5718, "lang": "aud", "codec": "MP1"}], "service_id": 459, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "TV Liberal HD Belém", "epg_pid": 38896, "pcr_pid": 5723, "video_pid": 5723, "audio_pids": [{"pid": 5721, "lang": "por", "codec": "AAC"}, {"pid": 5722, "lang": "SAP", "codec": "AAC"}, {"pid": 465, "lang": "dub", "codec": "MP1"}, {"pid": 5728, "lang": "aud", "codec": "MP1"}], "service_id": 465, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 0, "viewer_channel": 0}], "frequency": 12420000, "network_id": 45626, "symbol_rate": 29892, "original_network_id": 45626, "transport_stream_id": 164}, {"dvb_mode": "DVB2", "polarity": "Horizontal", "services": [{"name": "", "epg_pid": 0, "pcr_pid": 8191, "video_pid": 0, "audio_pids": [], "service_id": 906, "video_codec": "Nenhum", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "Globo RJ HD", "epg_pid": 38896, "pcr_pid": 5913, "video_pid": 5913, "audio_pids": [{"pid": 5911, "lang": "por", "codec": "AAC"}, {"pid": 5912, "lang": "SAP", "codec": "AAC"}, {"pid": 454, "lang": "dub", "codec": "MP1"}, {"pid": 5918, "lang": "aud", "codec": "MP1"}], "service_id": 454, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "Globo SP HD", "epg_pid": 38896, "pcr_pid": 5903, "video_pid": 5903, "audio_pids": [{"pid": 5901, "lang": "por", "codec": "AAC"}, {"pid": 5902, "lang": "SAP", "codec": "AAC"}, {"pid": 455, "lang": "dub", "codec": "MP1"}, {"pid": 5908, "lang": "aud", "codec": "MP1"}], "service_id": 455, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "TV Bahia HD", "epg_pid": 38896, "pcr_pid": 5923, "video_pid": 5923, "audio_pids": [{"pid": 5921, "lang": "por", "codec": "AAC"}, {"pid": 5922, "lang": "SAP", "codec": "AAC"}, {"pid": 457, "lang": "dub", "codec": "MP1"}, {"pid": 5928, "lang": "aud", "codec": "MP1"}], "service_id": 457, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 0, "viewer_channel": 0}, {"name": "TV Verdes Mares HD Fortaleza", "epg_pid": 38896, "pcr_pid": 5933, "video_pid": 5933, "audio_pids": [{"pid": 5931, "lang": "por", "codec": "AAC"}, {"pid": 5932, "lang": "SAP", "codec": "AAC"}, {"pid": 456, "lang": "dub", "codec": "MP1"}, {"pid": 5938, "lang": "aud", "codec": "MP1"}], "service_id": 456, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}], "frequency": 12460000, "network_id": 45626, "symbol_rate": 29892, "original_network_id": 45626, "transport_stream_id": 166}, {"dvb_mode": "DVB2", "polarity": "Vertical", "services": [{"name": "", "epg_pid": 0, "pcr_pid": 6543, "video_pid": 6543, "audio_pids": [{"pid": 6541, "lang": "por", "codec": "MP2"}, {"pid": 6542, "lang": "au2", "codec": "MP2"}], "service_id": 802, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "Futura HD", "epg_pid": 38896, "pcr_pid": 6553, "video_pid": 6553, "audio_pids": [{"pid": 6551, "lang": "por", "codec": "MP2"}, {"pid": 6552, "lang": "eng", "codec": "MP1"}], "service_id": 687, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 0, "viewer_channel": 0}, {"name": "TV Anhanguera Goiania HD", "epg_pid": 38896, "pcr_pid": 6503, "video_pid": 6503, "audio_pids": [{"pid": 6501, "lang": "por", "codec": "AAC"}, {"pid": 6502, "lang": "sap", "codec": "AAC"}, {"pid": 460, "lang": "dub", "codec": "MP1"}, {"pid": 6508, "lang": "aud", "codec": "MP1"}], "service_id": 460, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "RPC TV Curitiba HD", "epg_pid": 38896, "pcr_pid": 6513, "video_pid": 6513, "audio_pids": [{"pid": 6511, "lang": "por", "codec": "AAC"}, {"pid": 6512, "lang": "sap", "codec": "AAC"}, {"pid": 464, "lang": "dub", "codec": "MP1"}, {"pid": 6518, "lang": "aud", "codec": "MP1"}], "service_id": 464, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}, {"name": "RBS TV HD Porto Alegre", "epg_pid": 38896, "pcr_pid": 6523, "video_pid": 6523, "audio_pids": [{"pid": 6521, "lang": "por", "codec": "AAC"}, {"pid": 6522, "lang": "sap", "codec": "AAC"}, {"pid": 466, "lang": "dub", "codec": "MP1"}, {"pid": 6528, "lang": "aud", "codec": "MP1"}], "service_id": 466, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 2, "viewer_channel": 466}], "frequency": 12480000, "network_id": 45626, "symbol_rate": 29892, "original_network_id": 45626, "transport_stream_id": 167}, {"dvb_mode": "DVB2", "polarity": "Vertical", "services": [{"name": "", "epg_pid": 0, "pcr_pid": 6903, "video_pid": 6903, "audio_pids": [{"pid": 6901, "lang": "por", "codec": "AC3"}, {"pid": 6902, "lang": "sap", "codec": "AC3"}, {"pid": 6908, "lang": "aud", "codec": "AC3"}], "service_id": 472, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 6933, "video_pid": 6933, "audio_pids": [{"pid": 6931, "lang": "por", "codec": "AC3"}, {"pid": 6932, "lang": "sap", "codec": "AC3"}, {"pid": 6938, "lang": "aud", "codec": "AC3"}], "service_id": 480, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "EPTV HD Campinas", "epg_pid": 38896, "pcr_pid": 6913, "video_pid": 6913, "audio_pids": [{"pid": 6911, "lang": "por", "codec": "AC3"}, {"pid": 6912, "lang": "sap", "codec": "AC3"}, {"pid": 479, "lang": "dub", "codec": "AC3"}, {"pid": 6918, "lang": "aud", "codec": "AC3"}], "service_id": 479, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 0, "viewer_channel": 0}, {"name": "Rede Amazônica Manaus HD", "epg_pid": 38896, "pcr_pid": 6923, "video_pid": 6923, "audio_pids": [{"pid": 6921, "lang": "por", "codec": "AC3"}, {"pid": 6922, "lang": "sap", "codec": "AC3"}, {"pid": 467, "lang": "dub", "codec": "AC3"}, {"pid": 6928, "lang": "aud", "codec": "AC3"}], "service_id": 467, "video_codec": "H264", "service_type": "Advanced Codec HD Digital Television Service", "regionalizacao": 1, "viewer_channel": 5}], "frequency": 12560000, "network_id": 45626, "symbol_rate": 29892, "original_network_id": 45626, "transport_stream_id": 169}, {"dvb_mode": "DVB2", "polarity": "Horizontal", "services": [{"name": "", "epg_pid": 0, "pcr_pid": 1823, "video_pid": 1823, "audio_pids": [{"pid": 1821, "lang": "por", "codec": "MP2"}, {"pid": 1822, "lang": "eng", "codec": "MP2"}], "service_id": 80, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1763, "video_pid": 1763, "audio_pids": [{"pid": 1761, "lang": "por", "codec": "MP2"}], "service_id": 852, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1883, "video_pid": 1883, "audio_pids": [{"pid": 1881, "lang": "por", "codec": "MP2"}], "service_id": 401, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1813, "video_pid": 1813, "audio_pids": [{"pid": 1811, "lang": "por", "codec": "MP2"}, {"pid": 1812, "lang": "eng", "codec": "MP2"}], "service_id": 395, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1893, "video_pid": 1893, "audio_pids": [{"pid": 1891, "lang": "por", "codec": "MP2"}], "service_id": 234, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1703, "video_pid": 1703, "audio_pids": [{"pid": 1701, "lang": "por", "codec": "MP2"}], "service_id": 233, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1873, "video_pid": 1873, "audio_pids": [{"pid": 1871, "lang": "por", "codec": "MP2"}], "service_id": 159, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1833, "video_pid": 1833, "audio_pids": [{"pid": 1831, "lang": "eng", "codec": "MP2"}, {"pid": 1832, "lang": "por", "codec": "MP2"}], "service_id": 142, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1753, "video_pid": 1753, "audio_pids": [{"pid": 1751, "lang": "por", "codec": "MP2"}, {"pid": 1752, "lang": "eng", "codec": "MP2"}], "service_id": 122, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1733, "video_pid": 1733, "audio_pids": [{"pid": 1731, "lang": "por", "codec": "MP2"}, {"pid": 1732, "lang": "eng", "codec": "MP2"}], "service_id": 92, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1793, "video_pid": 1793, "audio_pids": [{"pid": 1791, "lang": "por", "codec": "MP2"}, {"pid": 1792, "lang": "eng", "codec": "MP2"}], "service_id": 81, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1743, "video_pid": 1743, "audio_pids": [{"pid": 1741, "lang": "eng", "codec": "MP2"}, {"pid": 1742, "lang": "dub", "codec": "MP2"}], "service_id": 77, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1843, "video_pid": 1843, "audio_pids": [{"pid": 1841, "lang": "por", "codec": "MP2"}, {"pid": 1842, "lang": "eng", "codec": "MP2"}], "service_id": 64, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1773, "video_pid": 1773, "audio_pids": [{"pid": 1771, "lang": "por", "codec": "MP2"}, {"pid": 1772, "lang": "eng", "codec": "MP2"}], "service_id": 58, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1803, "video_pid": 1803, "audio_pids": [{"pid": 1801, "lang": "por", "codec": "MP2"}, {"pid": 1802, "lang": "eng", "codec": "MP2"}], "service_id": 54, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "", "epg_pid": 0, "pcr_pid": 1853, "video_pid": 1853, "audio_pids": [{"pid": 1851, "lang": "por", "codec": "MP2"}, {"pid": 1852, "lang": "eng", "codec": "MP2"}], "service_id": 42, "video_codec": "H264", "service_type": "0x0", "regionalizacao": 0, "viewer_channel": 0}, {"name": "TV Brasil 2", "epg_pid": 38896, "pcr_pid": 1903, "video_pid": 1903, "audio_pids": [{"pid": 1901, "lang": "por", "codec": "MP2"}], "service_id": 425, "video_codec": "H264", "service_type": "Digital Television Service", "regionalizacao": 0, "viewer_channel": 0}, {"name": "TV Câmara", "epg_pid": 38896, "pcr_pid": 1783, "video_pid": 1783, "audio_pids": [{"pid": 1781, "lang": "por", "codec": "MP2"}], "service_id": 162, "video_codec": "H264", "service_type": "Digital Television Service", "regionalizacao": 2, "viewer_channel": 3}, {"name": "TV Justiça", "epg_pid": 38896, "pcr_pid": 34, "video_pid": 34, "audio_pids": [{"pid": 33, "lang": "por", "codec": "MP2"}], "service_id": 160, "video_codec": "H264", "service_type": "Digital Television Service", "regionalizacao": 2, "viewer_channel": 6}, {"name": "TV Senado", "epg_pid": 38896, "pcr_pid": 38, "video_pid": 38, "audio_pids": [{"pid": 32, "lang": "por", "codec": "MP2"}], "service_id": 161, "video_codec": "H264", "service_type": "Digital Television Service", "regionalizacao": 2, "viewer_channel": 8}, {"name": "TV Brasil", "epg_pid": 38896, "pcr_pid": 43, "video_pid": 43, "audio_pids": [{"pid": 40, "lang": "por", "codec": "MP2"}], "service_id": 164, "video_codec": "H264", "service_type": "Digital Television Service", "regionalizacao": 2, "viewer_channel": 44}], "frequency": 12580000, "network_id": 45626, "symbol_rate": 29892, "original_network_id": 45626, "transport_stream_id": 150}]}
)
*/