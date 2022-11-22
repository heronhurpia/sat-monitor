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
}

