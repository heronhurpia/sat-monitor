<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAudioRequest;
use App\Http\Requests\UpdateAudioRequest;
use App\Models\Audio;

class AudioController extends Controller
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
     * @param  \App\Http\Requests\StoreAudioRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAudioRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Audio  $audio
     * @return \Illuminate\Http\Response
     */
    public function show(Audio $audio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Audio  $audio
     * @return \Illuminate\Http\Response
     */
    public function edit(Audio $audio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAudioRequest  $request
     * @param  \App\Models\Audio  $audio
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAudioRequest $request, Audio $audio)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Audio  $audio
     * @return \Illuminate\Http\Response
     */
    public function destroy(Audio $audio)
    {
        //
    }
}
