<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideo_typeRequest;
use App\Http\Requests\UpdateVideo_typeRequest;
use App\Models\Video_type;

class VideoTypeController extends Controller
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
     * @param  \App\Http\Requests\StoreVideo_typeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVideo_typeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Video_type  $video_type
     * @return \Illuminate\Http\Response
     */
    public function show(Video_type $video_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Video_type  $video_type
     * @return \Illuminate\Http\Response
     */
    public function edit(Video_type $video_type)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateVideo_typeRequest  $request
     * @param  \App\Models\Video_type  $video_type
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVideo_typeRequest $request, Video_type $video_type)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Video_type  $video_type
     * @return \Illuminate\Http\Response
     */
    public function destroy(Video_type $video_type)
    {
        //
    }
}
