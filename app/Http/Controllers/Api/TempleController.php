<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Temple;
use Illuminate\Http\Request;

class TempleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $temples = Temple::all('id','name','punnyam_code');

        return response()->json([
            'status' => true,
            'data' => $temples
        ]);
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
     * @param  \App\Models\Temple  $temple
     * @return \Illuminate\Http\Response
     */
    public function show(Temple $temple)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Temple  $temple
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Temple $temple)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Temple  $temple
     * @return \Illuminate\Http\Response
     */
    public function destroy(Temple $temple)
    {
        //
    }
}
