<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->check() && !empty( auth()->user()->temple->user_db )){
             $db_name = auth()->user()->temple->user_db;
             $config = \Config::get('database.connections.users');
             $config['database'] = $db_name;
             config()->set('database.connections.users', $config);
             \DB::purge('mysql');
             return $next($request);
        }
        else{
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated!'
            ], 500);
        }
       
    }
}
