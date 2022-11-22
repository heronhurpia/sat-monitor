<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreService_typeRequest;
use App\Http\Requests\UpdateService_typeRequest;
use App\Models\Service_type;

class ServiceTypeController extends Controller
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
     * @param  \App\Http\Requests\StoreService_typeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreService_typeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Service_type  $service_type
     * @return \Illuminate\Http\Response
     */
    public function show(Service_type $service_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Service_type  $service_type
     * @return \Illuminate\Http\Response
     */
    public function edit(Service_type $service_type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateService_typeRequest  $request
     * @param  \App\Models\Service_type  $service_type
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateService_typeRequest $request, Service_type $service_type)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Service_type  $service_type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Service_type $service_type)
    {
        //
    }
}
