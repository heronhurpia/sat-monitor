<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\Log;

class QualityController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$quality = DB::select(DB::raw("select * from dvb.get_transponders_lock_grid('quality', '1 month'::interval)"));
		$strength = DB::select(DB::raw("select * from dvb.get_transponders_lock_grid('strength', '1 month'::interval)"));

		$query = "select date_trunc('hour', datetime) as \"datetime\",frequency,symbol_rate, " .
					"round(avg(quality))::int as \"quality\", " . 
					"round(avg(strength))::int as \"strength\", " . 
					"round(avg(snr))::int as \"snr\", " . 
					"round(avg(ber))::int as \"ber\", " . 
					"f.fec_rate, " . 
					"m.modulation_type " . 
					"from dvb.transponders_lock l " . 
					"join dvb.fec_rate f on l.fec_rate = f.fec_rate_id " . 
					"join dvb.modulation_type m on l.modulation_type = m.modulation_type_id " . 
					"where datetime >= (select max(date_trunc('hour', datetime)) from dvb.transponders_lock ) " .
					"group by date_trunc('hour', datetime), frequency, symbol_rate, f.fec_rate, m.modulation_type " . 
					"order by frequency" ;
		$transponders = DB::select(DB::raw($query));

		$log = Log::latest()->first();

		return view('quality', compact('transponders','quality','strength','log'));
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
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		//
	}
}
