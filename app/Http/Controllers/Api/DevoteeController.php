<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DevoteeRequest;
use Illuminate\Http\Request;
use App\Models\Devotee;
use App\Models\Ledger;
use DB;

class DevoteeController extends Controller
{
    public function devotees(Request $request) 
    {
        $mobile = $request->mobile;
        $devotees = Devotee::where('mobile','like',$mobile.'%')->get();
        return response()->json([
            'status' => true,
            'data' => $devotees,
        ]);
    }

    public function storeDevotee(DevoteeRequest $request){

        DB::beginTransaction();
        try {
            $count = Devotee::count() + 1; 
            $devotee = Devotee::create([
                'customer_id' => date('dmyhis').'-'.$count,
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'house' => $request->house,
                'street' => $request->street,
                'post' => $request->post,
                'district' => $request->district,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'status' => 1,
                'led_id' => 0
            ]);

            $ledger = Ledger::create([
                'name' => $devotee->name.'-'.$devotee->mobile,
                'name_mal' => '',
                'opening_bal' => 0,
                'group' => 15,
                'balance' => 0,
                'is_delete' => 1,
                'created' => now()
            ]);

            $devotee->update(['led_id' => $ledger->id]);

            DB::commit();
            return response()->json([
                'status' => true,
                'data' => $devotee,
                'message' => 'Devotee Created!'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return response()->json([
                'status' => false,
                'data' => [],
                'message' => 'Something went wrong!'
            ]);  
        }
        
    }
}
