<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BirthStar;
use App\Models\Deity;
use App\Models\DeityPooja;
use App\Models\PaymentMode;
use App\Models\Pooja;
use App\Models\Star;
use App\Http\Resources\DeityPoojaResource;
use Illuminate\Http\Request;

class DeityPoojaStarController extends Controller
{
    
    public function allDieties()
    {
        $deities = Deity::all('id','name','name_mal');
        return response()->json([
            'status' => true,
            'data' => $deities,
        ]);
    }

    public function deityPoojas(Request $request)
    {
        $deity_id = $request->deity;
        $poojas = DeityPooja::with('pooja')->where('temple_id',$deity_id)->get();

        $data = DeityPoojaResource::collection($poojas);
        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function allStars()
    {
        $stars = Star::all('id','name_eng','name_mal');
        return response()->json([
            'status' => true,
            'data' => $stars,
        ]);
    }

    public function allSpecialStars()
    {
        $stars = BirthStar::select('other_code','other_detail')->where('other_code','!=','')->groupBy('other_code')->get();
        return response()->json([
            'status' => true,
            'data' => $stars,
        ]);
    }

    public function allPaymentModes()
    {
        $payment_modes = PaymentMode::select('id','name')->get();
        return response()->json([
            'status' => true,
            'data' => $payment_modes,
        ]);
    }


    public function show($id)
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
