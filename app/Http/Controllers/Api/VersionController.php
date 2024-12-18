<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use DB;

class VersionController extends Controller
{	
	public function getVersion()
    {
        $version = Setting::orderBy('created_at', 'desc')->value('version');
        
        return response()->json(['version' => $version]);
    }

}
