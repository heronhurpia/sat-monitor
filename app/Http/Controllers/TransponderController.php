<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransponderRequest;
use App\Http\Requests\UpdateTransponderRequest;
use App\Models\Transponder;

class TransponderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreTransponderRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransponderRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transponder  $transponder
     * @return \Illuminate\Http\Response
     */
    public function show(Transponder $transponder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transponder  $transponder
     * @return \Illuminate\Http\Response
     */
    public function edit(Transponder $transponder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransponderRequest  $request
     * @param  \App\Models\Transponder  $transponder
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransponderRequest $request, Transponder $transponder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transponder  $transponder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transponder $transponder)
    {
        //
    }
}
