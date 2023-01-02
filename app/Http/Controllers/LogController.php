<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogRequest;
use App\Http\Requests\UpdateLogRequest;
use App\Models\Log;
use App\Models\Transponder;
use App\Models\Service;

use DB;
use Carbon\Carbon; 
use Session; 

class LogController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{

		/* Constante com limite da última alteração em dias */
		//$limit = 100 ;
		$data = Session::get('data');
		if ( isset ( $data ) ) {
			$inicio = $data ;	
		}
		else {
			$inicio = now()->subDays(7);
		}

		$logs = Log::where('created_at','>',$inicio)
			->orderBy('created_at','desc')
			->get();		

		return view('logs',compact('logs'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \App\Http\Requests\StoreLogRequest  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreLogRequest $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\Models\Log  $log
	 * @return \Illuminate\Http\Response
	 */
	public function show(Log $log)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\Models\Log  $log
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Log $log)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \App\Http\Requests\UpdateLogRequest  $request
	 * @param  \App\Models\Log  $log
	 * @return \Illuminate\Http\Response
	 */
	public function update(UpdateLogRequest $request, Log $log)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\Models\Log  $log
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Log $log)
	{
		//
	}
}
