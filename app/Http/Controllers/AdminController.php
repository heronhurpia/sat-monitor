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
		/* Logs mais recentes */
		$logs = LOG::orderBy('id','DESC')->limit(200)->get();
		foreach ( $logs as &$log ) {
			if ( $log->table == 'services') {
				$service_name = Service::where('services.id',$log->item_id)->first();
				$log->name = $service_name ? $service_name->name : "" ;
			}
			else if ( $log->table == 'audios') {
				$service_name = Audio::where('audio.id',$log->item_id)->join('services as s','audio.service_id','s.id')->first();
				$log->name = $service_name ? $service_name->name : "" ;
			}
		}

		$services = Service::orderBy('name','DESC')->get();

		return view('admin', compact('services','logs'));
	}		

}
