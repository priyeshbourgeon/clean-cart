<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillingResource;
use App\Models\BirthStar;
use App\Models\Billing;
use App\Models\BillingDetail;
use App\Models\Counter;
use App\Models\Temple;
use App\Models\Devotee;
use App\Models\Deity;
use App\Models\Pooja;
use App\Models\Star;
use App\Models\PaymentMode;
use App\Models\SiteSetting;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;


class TestController extends Controller
{

    public function counters()
    {
        $counters = Counter::all('id','name');
        return response()->json([
            'status' => true,
            'data' => $counters,
        ]);
    }

    public function previewBill(Request $request)
    {
        try {
            $data = [];
            if($request->pooja_details) {
                foreach($request->pooja_details as $key => $pooja)
                {
                    $from_date = $pooja['from_date'];
                    if($pooja['is_scheduled'] == 1){
                        if($pooja['dwmo'] == 'D'){
                            $added_days = $pooja['no_of_days'];
                            $to_date = Carbon::parse($from_date)->addDays($added_days - 1)->format('Y-m-d');
                            $schedules = BirthStar::whereBetween('birth_date',[$from_date,$to_date])->groupBy('birth_date')->get();
                            foreach($schedules as $key => $schedule){
                                $data[] = array(
                                    'name' => $pooja['name'],
                                    'address' => $pooja['address'],
                                    'star' => $this->star_details($pooja['star_id']), 
                                    'star_id' => $pooja['star_id'],
                                    'deity' => $this->deity_details($pooja['deity_id']),
                                    'deity_id' => $pooja['deity_id'],
                                    'pooja' => $this->pooja_details($pooja['pooja_id']),
                                    'pooja_id' => $pooja['pooja_id'],
                                    'qty' => $pooja['qty'],
                                    'rate' => $pooja['rate'],
                                    'from_date' => $schedule->birth_date,
                                    'date_star' => $schedule->name_eng,
                                	'is_scheduled'=> 1,
                                );
                            }
                        }
                        elseif($pooja['dwmo'] == 'W'){
                            $added_weeks = $pooja['no_of_weeks'];
                            $weekdays = $pooja['week_days'];
                            $to_date = Carbon::parse($from_date)->addWeeks($added_weeks - 1)->format('Y-m-d');
                            $schedules = BirthStar::whereBetween('birth_date',[$from_date,$to_date])->where('day_of_day',$weekdays)->groupBy('birth_date')->get();
                            foreach($schedules as $key => $schedule){
                                $data[] = array(
                                    'name' => $pooja['name'],
                                    'star' => $this->star_details($pooja['star_id']), 
                                    'star_id' => $pooja['star_id'],
                                    'deity' => $this->deity_details($pooja['deity_id']),
                                    'deity_id' => $pooja['deity_id'],
                                    'pooja' => $this->pooja_details($pooja['pooja_id']),
                                    'pooja_id' => $pooja['pooja_id'],
                                    'qty' => $pooja['qty'],
                                    'rate' => $pooja['rate'],
                                    'from_date' => $schedule->birth_date,
                                    'date_star' => $schedule->name_eng,
                                	'is_scheduled'=> 1,
                                );
                            }
                           
                        }
                        elseif($pooja['dwmo'] == 'M'){
                            $added_months = $pooja['no_of_months'];
                            $to_date = Carbon::parse($from_date)->addMonths($added_months - 1)->format('Y-m-d');
                            $month_star = $pooja['month_star'];
                            $schedules = BirthStar::whereBetween('birth_date',[$from_date,$to_date])->where('name_eng',$month_star)->groupBy('birth_date')->get();
                            
                        	foreach($schedules as $key => $schedule){
                                $data[] = array(
                                    'name' => $pooja['name'],
                                    'address' => $pooja['address'],
                                    'star' => $this->star_details($pooja['star_id']), 
                                    'star_id' => $pooja['star_id'],
                                    'deity' => $this->deity_details($pooja['deity_id']),
                                    'deity_id' => $pooja['deity_id'],
                                    'pooja' => $this->pooja_details($pooja['pooja_id']),
                                    'pooja_id' => $pooja['pooja_id'],
                                    'qty' => $pooja['qty'],
                                    'rate' => $pooja['rate'],
                                    'from_date' => $schedule->birth_date,
                                    'date_star' => $schedule->name_eng,
                                	'is_scheduled'=> 1,
                                );
                            }
                        }
                        elseif($pooja['dwmo'] == 'O'){
                            $added_months = $pooja['no_of_months'];
                            $to_date = Carbon::parse($from_date)->addMonths($added_months - 1)->format('Y-m-d');
                            $special_star_id = $pooja['special_star_id'];
                            $schedules = BirthStar::whereBetween('birth_date',[$from_date,$to_date])->where('other_code',$special_star_id)->groupBy('birth_date')->get();
                            foreach($schedules as $key => $schedule){
                                $data[] = array(
                                    'name' => $pooja['name'],
                                    'address' => $pooja['address'],
                                    'star' => $this->star_details($pooja['star_id']), 
                                    'star_id' => $pooja['star_id'],
                                    'deity' => $this->deity_details($pooja['deity_id']),
                                    'deity_id' => $pooja['deity_id'],
                                    'pooja' => $this->pooja_details($pooja['pooja_id']),
                                    'pooja_id' => $pooja['pooja_id'],
                                    'qty' => $pooja['qty'],
                                    'rate' => $pooja['rate'],
                                    'from_date' => $schedule->birth_date,
                                    'date_star' => $schedule->name_eng,
                                	'is_scheduled'=> 1,
                                );
                            }
                        }
                    }
                    else{
                        $data[] = array(
                            'name' => $pooja['name'],
                            'address' => $pooja['address'],
                            'star' => $this->star_details($pooja['star_id']), 
                            'star_id' => $pooja['star_id'],
                            'deity' => $this->deity_details($pooja['deity_id']),
                            'deity_id' => $pooja['deity_id'],
                            'pooja' => $this->pooja_details($pooja['pooja_id']),
                            'pooja_id' => $pooja['pooja_id'],
                            'qty' => $pooja['qty'],
                            'rate' => $pooja['rate'],
                            'from_date' => $from_date,
                            'date_star' => '',
                        	'is_scheduled'=> 0,
                        );
                    }
                }
            }
            
            $response = array(
                "customer_id" => $request->customer_id,
                "customer_name" => $this->customer_details($request->customer_id),
                "counter_id" => $request->counter_id,
                "counter_name" => $this->counter_details($request->counter_id),
                "pooja_details" => $data
            );
            if($data){
                return response()->json([
                    'status' => true,
                    'data' => $response,
                    'message' => 'Generated Bill Preview!'
                ]);
            }
            else{
                return response()->json([
                    'status' => false,
                    'data' => '',
                    'message' => 'Something went wrong! Try Again'
                ]);
            }
        }
        catch (\Throwable $th) {

            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);

        }
        
    }

    public function customer_details($id){
        return Devotee::find($id)->name ?? 'Walk-in Devotee';
    }

    public function counter_details($id){
        return Counter::find($id)->name ?? '';
    }

    public function star_details($id){
        return Star::find($id)->name_eng ?? '';
    }
    
    public function star_details_mal($id){
        return Star::find($id)->name_mal ?? '';
    }

    public function deity_details($id){
        return Deity::find($id)->name ?? '';
    }
    
    public function deity_details_mal($id){
        return Deity::find($id)->name_mal ?? '';
    }

    public function special_star_details($id){
        return BirthStar::where('other_code',$id)->first()->other_name ?? '';
    }

    public function pooja_details($id){
        return Pooja::find($id)->name ?? '';
    }
    
    public function pooja_details_mal($id){
        return Pooja::find($id)->name_mal ?? '';
    }
    
    public function payment_mode_details($id){
        return PaymentMode::find($id)->name ?? '';
    }

    public function saveBill(Request $request)
    {
        DB::beginTransaction();
        try {
        		
                $pooja_details = $request->pooja_details;
                
                $billing = Billing::create([
                    'diety_id'  => $pooja_details[0]['deity_id'], 
                    'date'  => date('Y-m-d'),
                    'place' => '---', //
                    'user_id'   => $request->customer_id,
                    'status'    => 1,
                    'mode'  => $request->payment_mode,
                    'bill_time' => date('Y-m-d h:i:s'),
                    'counter'   => $request->counter_id, 
                    'book_issue_id' => 1,
                    'total' => $request->bill_amount,
                    'recv_amt' => $request->paid_amount,
                    'customer_id' => $request->customer_id ?? 1
                ]);
        
        		
        		if (SiteSetting::first()->store_transaction_id == 1) {
    				Billing::where('id', $billing->id)->update([
                    	'bank_transaction_id'=> $request->transaction_id ?? ''
                    ]);
				}
        
                foreach($request->pooja_details as $key => $pooja)
                {
                    if($pooja->is_scheduled == 1){
                        $billing_details[] = BillingDetail::create(array(
                            'bill_id' => $billing->id,
                            'name' => $pooja['name'],
                            'address' => $pooja['address'] ?? '',
                            'diety_id' => $pooja['deity_id'],
                            'star' => $pooja['star_id'],
                            'pooja' => $pooja['pooja_id'],
                            'qlt' => $pooja['qty'],
                            'time' => 'M',
                            'type' => 'S',
                            'rate' => $pooja['rate'],
                            'amount' => $pooja['qty'] * $pooja['rate'],
                            'date' => Carbon::parse($pooja['from_date'])->format('Y-m-d'),
                            'status' => 1
                        ));
                    }
                    else{
                        $billing_details[] = BillingDetail::create(array(
                            'bill_id' => $billing->id,
                            'name' => $pooja['name'],
                            'address' => $pooja['address'] ?? '',
                            'diety_id' => $pooja['deity_id'],
                            'star' => $pooja['star_id'],
                            'pooja' => $pooja['pooja_id'],
                            'qlt' => $pooja['qty'],
                            'time' => 'M',
                            'rate' => $pooja['rate'],
                            'amount' => $pooja['qty'] * $pooja['rate'],
                            'date' => Carbon::parse($pooja['date'])->format('Y-m-d'),
                            'status' => 1
                        ));
                    }
                }
                // foreach($request->pooja_details as $key => $pooja)
                // {
                //     $billing_details[] = BillingDetail::create([
                //         'bill_id' => $billing->id,
                //         'name' => $pooja['name'],
                //         'address' => $pooja['address'],
                //         'diety_id' => $pooja['deity_id'],
                //         'star' => $pooja['star_id'],
                //         'pooja' => $pooja['pooja_id'],
                //         'qlt' => $pooja['qty'],
                //         'time' => 'M',
                //         'rate' => $pooja['rate'],
                //         'amount' => $pooja['qty'] * $pooja['rate'],
                //         'date' => Carbon::parse($pooja['date'])->format('Y-m-d'),
                //         'status' => 1
                //     ]);

                // }

                DB::commit();
                
                $data = array(
                    'id' => $billing->id,
                    'bill_date' => $billing->bill_time,
                    'mode' => $this->payment_mode_details($billing->mode),
                    'counter' => $this->counter_details($billing->counter),
                    'devotee' => $this->customer_details($billing->user_id),
                    'total' => strval($billing->total), //added by priyesh
                    'recv_amt' => strval($billing->recv_amt) // added by priyesh
                    
                );
        
        		if (SiteSetting::first()->store_transaction_id == 1) {
    				$data['transaction_id'] = Billing::find($billing->id)->bank_transaction_id;
				}
                
                foreach($billing_details as $bill){
                    $bill_details[] = array(
                             'name' => $bill->name,
                             'address' => $bill->address,
                             'star' => $this->star_details($bill->star),
                             'star_mal' => $this->star_details_mal($bill->star),
                             'deity' => $this->deity_details($bill->diety_id),
                             'deity_mal' => $this->deity_details_mal($bill->diety_id),
                             'pooja' => $this->pooja_details($bill->pooja),
                             'pooja_mal' => $this->pooja_details_mal($bill->pooja),
                             'qty' => $bill->qlt,
                             'rate' => $bill->rate,
                             'time' => $bill->time,
                             'date' => $bill->date,
                             'amount' => $bill->amount
                        );
                }
			
        		$bill_id = $billing->id;
        		$details = array();
        		$normal_details   = BillingDetail::where('bill_id', $bill_id)->where('type', '!=', 'S')->get()->map(function($query) {
							
        						return [
                                		 'name'=> $query->name,
                                		 'pooja_id'=> $query->pooja,
                                		 'qty'=> $query->qlt,
                                		 'rate'=> $query->rate,
                                		 'amount'=> $query->rate * $query->qlt,
                                		 'address' => $query->address,
                             			 'star' => $this->star_details($query->star),
                             	         'star_mal' => $this->star_details_mal($query->star),
                                         'deity' => $this->deity_details($query->diety_id),
                                		 'deity_mal' => $this->deity_details_mal($query->diety_id),
                             		 	 'pooja' => $this->pooja_details($query->pooja),
                             		 	 'pooja_mal' => $this->pooja_details_mal($query->pooja),
                             			 'time' => $query->time,
                             			 'date' => $query->date,
                                		];
        					});
        		$schedule_details = BillingDetail::where('bill_id', $bill_id)->where('type', 'S')->groupBy('name', 'pooja')->get()->map(function($query) {
        						$p_query = BillingDetail::where('bill_id', $query->bill_id)->where('type', 'S')->where('name', $query->name)->where('pooja', $query->pooja);
        						$dates = $p_query->pluck('date')->toArray();
							
        						return [
                                		 'name'=> $query->name,
                                		 'pooja_id'=> $query->pooja,
                                		 'qty'=> $p_query->sum('qlt'),
                                		 'rate'=> $query->rate,
                                		 'amount'=> $query->rate * $p_query->sum('qlt'),
                                		 'address' => $query->address,
                             			 'star' => $this->star_details($query->star),
                             	         'star_mal' => $this->star_details_mal($query->star),
                                         'deity' => $this->deity_details($query->diety_id),
                                		 'deity_mal' => $this->deity_details_mal($query->diety_id),
                             		 	 'pooja' => $this->pooja_details($query->pooja),
                             		 	 'pooja_mal' => $this->pooja_details_mal($query->pooja),
                             			 'time' => $query->time,
                             			 'date' => min($dates)." to ".max($dates),
                                		];
        					});
    
    			foreach($schedule_details as $detail) {
        			$details[] = $detail;
        		} 
        		
        		foreach($normal_details as $detail) {
        			$details[] = $detail;
        		}
                
                $tem = Temple::where('punnyam_code',auth()->user()->punnyam_code)->first();
        
                $temple = array(
                       'name' => $tem->name ?? '',
                       'name_mal' => $tem->name_mal ?? '',
                       'address_line_1' => $tem->address_line_1.' '.$tem->address_line_2,
                       'address_line_2' => $tem->city.' '.$tem->state.' '.$tem->pincode,
                       'phone' => $tem->mobile,
                       'email' => $tem->email,
                       'website' => $tem->website ?? ''
                    );
                
            $bill_image = SiteSetting::first()->bill_image_pos ?? null;
        
            
            $response = [
                    'status'     => true,
                    'temple'     => $temple,
                    'summary'    => $data,
                    'details'    => $details,
                    'message'    => 'Bill Saved!'
                ];
        
            if ($bill_image) { 
            	$response['bill_image'] = $tem->website.$bill_image ?? '';
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
            $response = [
                'status' => false,
                'message' => 'Something went wrong!'
            ];
        }
        
        return response()->json($response);
    }

	public function testBill(Request $request) {
    	DB::beginTransaction();
        try {
        		
                $pooja_details = $request->pooja_details;
                
                $billing = Billing::create([
                    'diety_id'  => $pooja_details[0]['deity_id'], 
                    'date'  => date('Y-m-d'),
                    'place' => '---', //
                    'user_id'   => $request->customer_id,
                    'status'    => 1,
                    'mode'  => $request->payment_mode,
                    'bill_time' => date('Y-m-d h:i:s'),
                    'counter'   => $request->counter_id, 
                    'book_issue_id' => 1,
                    'total' => $request->bill_amount,
                    'recv_amt' => $request->paid_amount,
                    'customer_id' => $request->customer_id ?? 1
                ]);
        
        		
        		if (SiteSetting::first()->store_transaction_id == 1) {
    				Billing::where('id', $billing->id)->update([
                    	'bank_transaction_id'=> $request->transaction_id ?? ''
                    ]);
				}
        
                foreach($request->pooja_details as $key => $pooja)
                {
                    if($pooja['is_scheduled'] == 1){
                        $billing_details[] = BillingDetail::create(array(
                            'bill_id' => $billing->id,
                            'name' => $pooja['name'],
                            'address' => $pooja['address'] ?? '',
                            'diety_id' => $pooja['deity_id'],
                            'star' => $pooja['star_id'],
                            'pooja' => $pooja['pooja_id'],
                            'qlt' => $pooja['qty'],
                            'time' => 'M',
                            'type' => 'S',
                            'rate' => $pooja['rate'],
                            'amount' => $pooja['qty'] * $pooja['rate'],
                            'date' => Carbon::parse($pooja['from_date'])->format('Y-m-d'),
                            'status' => 1
                        ));
                    }
                    else{
                        $billing_details[] = BillingDetail::create(array(
                            'bill_id' => $billing->id,
                            'name' => $pooja['name'],
                            'address' => $pooja['address'] ?? '',
                            'diety_id' => $pooja['deity_id'],
                            'star' => $pooja['star_id'],
                            'pooja' => $pooja['pooja_id'],
                            'qlt' => $pooja['qty'],
                            'time' => 'M',
                            'rate' => $pooja['rate'],
                            'amount' => $pooja['qty'] * $pooja['rate'],
                            'date' => Carbon::parse($pooja['date'])->format('Y-m-d'),
                            'status' => 1
                        ));
                    }
                }
                // foreach($request->pooja_details as $key => $pooja)
                // {
                //     $billing_details[] = BillingDetail::create([
                //         'bill_id' => $billing->id,
                //         'name' => $pooja['name'],
                //         'address' => $pooja['address'],
                //         'diety_id' => $pooja['deity_id'],
                //         'star' => $pooja['star_id'],
                //         'pooja' => $pooja['pooja_id'],
                //         'qlt' => $pooja['qty'],
                //         'time' => 'M',
                //         'rate' => $pooja['rate'],
                //         'amount' => $pooja['qty'] * $pooja['rate'],
                //         'date' => Carbon::parse($pooja['date'])->format('Y-m-d'),
                //         'status' => 1
                //     ]);

                // }

                DB::commit();
                
                $data = array(
                    'id' => $billing->id,
                    'bill_date' => $billing->bill_time,
                    'mode' => $this->payment_mode_details($billing->mode),
                    'counter' => $this->counter_details($billing->counter),
                    'devotee' => $this->customer_details($billing->user_id),
                    'total' => strval($billing->total), //added by priyesh
                    'recv_amt' => strval($billing->recv_amt) // added by priyesh
                    
                );
        
        		if (SiteSetting::first()->store_transaction_id == 1) {
    				$data['transaction_id'] = Billing::find($billing->id)->bank_transaction_id;
				}
                
                foreach($billing_details as $bill){
                    $bill_details[] = array(
                             'name' => $bill->name,
                             'address' => $bill->address,
                             'star' => $this->star_details($bill->star),
                             'star_mal' => $this->star_details_mal($bill->star),
                             'deity' => $this->deity_details($bill->diety_id),
                             'deity_mal' => $this->deity_details_mal($bill->diety_id),
                             'pooja' => $this->pooja_details($bill->pooja),
                             'pooja_mal' => $this->pooja_details_mal($bill->pooja),
                             'qty' => $bill->qlt,
                             'rate' => $bill->rate,
                             'time' => $bill->time,
                             'date' => $bill->date,
                             'amount' => $bill->amount
                        );
                }
			
        		$bill_id = $billing->id;
        		$details = array();
        		$normal_details   = BillingDetail::where('bill_id', $bill_id)->where('type', '!=', 'S')->get()->map(function($query) {
							
        						return [
                                		 'name'=> $query->name,
                                		 'pooja_id'=> $query->pooja,
                                		 'qty'=> $query->qlt,
                                		 'rate'=> $query->rate,
                                		 'amount'=> $query->rate * $query->qlt,
                                		 'address' => $query->address,
                             			 'star' => $this->star_details($query->star),
                             	         'star_mal' => $this->star_details_mal($query->star),
                                         'deity' => $this->deity_details($query->diety_id),
                                		 'deity_mal' => $this->deity_details_mal($query->diety_id),
                             		 	 'pooja' => $this->pooja_details($query->pooja),
                             		 	 'pooja_mal' => $this->pooja_details_mal($query->pooja),
                             			 'time' => $query->time,
                             			 'date' => Carbon::parse($query->date)->format('d-m-Y'),
                                		];
        					});
        		$schedule_details = BillingDetail::where('bill_id', $bill_id)->where('type', 'S')->groupBy('name', 'pooja')->get()->map(function($query) {
        						$p_query = BillingDetail::where('bill_id', $query->bill_id)->where('type', 'S')->where('name', $query->name)->where('pooja', $query->pooja);
        						$dates = $p_query->pluck('date')->toArray();
							
        						return [
                                		 'name'=> $query->name,
                                		 'pooja_id'=> $query->pooja,
                                		 'qty'=> $p_query->sum('qlt'),
                                		 'rate'=> $query->rate,
                                		 'amount'=> $query->rate * $p_query->sum('qlt'),
                                		 'address' => $query->address,
                             			 'star' => $this->star_details($query->star),
                             	         'star_mal' => $this->star_details_mal($query->star),
                                         'deity' => $this->deity_details($query->diety_id),
                                		 'deity_mal' => $this->deity_details_mal($query->diety_id),
                             		 	 'pooja' => $this->pooja_details($query->pooja),
                             		 	 'pooja_mal' => $this->pooja_details_mal($query->pooja),
                             			 'time' => $query->time,
                             			 'date' => Carbon::parse(min($dates))->format('d-m-Y')." to ".Carbon::parse(max($dates))->format('d-m-Y'),
                                		];
        					});
    
    			foreach($schedule_details as $detail) {
        			$details[] = $detail;
        		} 
        		
        		foreach($normal_details as $detail) {
        			$details[] = $detail;
        		}
                
                $tem = Temple::where('punnyam_code',auth()->user()->punnyam_code)->first();
        
                $temple = array(
                       'name' => $tem->name ?? '',
                       'name_mal' => $tem->name_mal ?? '',
                       'address_line_1' => $tem->address_line_1.' '.$tem->address_line_2,
                       'address_line_2' => $tem->city.' '.$tem->state.' '.$tem->pincode,
                       'phone' => $tem->mobile,
                       'email' => $tem->email,
                       'website' => $tem->website ?? ''
                    );
                
            $bill_image = SiteSetting::first()->bill_image_pos ?? null;
        
            
            $response = [
                    'status'     => true,
                    'temple'     => $temple,
                    'summary'    => $data,
                    'details'    => $details,
                    'message'    => 'Bill Saved!'
                ];
        
            if ($bill_image) { 
            	$response['bill_image'] = $tem->website.$bill_image ?? '';
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
            $response = [
                'status' => false,
                'message' => 'Something went wrong!'
            ];
        }
        
        return response()->json($response);
    }
    
     public function billList(Request $request){
         $bill_details = [];
         $query = Billing::query();
         if(!is_null($request->date)){
            $query->where('date',$request->date);
         }
         if(!is_null($request->bill_no)){
            $query->where('id',$request->bill_no);
         }

         $gross_total = (string)$query->sum('total');
         $bills = $query->get();
        
        $tem = Temple::where('punnyam_code',auth()->user()->punnyam_code)->first();
        
        $temple = array(
               'name' => $tem->name ?? '',
               'name_mal' => $tem->name_mal ?? '',
               'address_line_1' => $tem->address_line_1.' '.$tem->address_line_2,
               'address_line_2' => $tem->city.' '.$tem->state.' '.$tem->pincode,
               'phone' => $tem->mobile,
               'email' => $tem->email,
               'website' => $tem->website,
            );
     
         if(!$bills){
             return response()->json([
                'status' => false,
                'list' => "No Data Found"
             ]);
         }
         else{
             $data = BillingResource::collection($bills);
             return response()->json([
                'status' => true,
                'temple' => $temple,
                'list' => $data,
                'gross_total' => $gross_total,
                // 'meta' => [
                //     'total' => $bills->total(),
                //     'per_page' => $bills->perPage(),
                //     'current_page' => $bills->currentPage(),
                //     'last_page' => $bills->lastPage(),
                //     'next_page_url' => $bills->nextPageUrl(),
                //     'prev_page_url' => $bills->previousPageUrl(),
                //     'from' => $bills->firstItem(),
                //     'to' => $bills->lastItem()
                // ],
                // 'links' => [
                //     'self' => $bills->url($bills->currentPage()),
                //     'first' => $bills->url(1),
                //     'last' => $bills->url($bills->lastPage()),
                //     'prev' => $bills->previousPageUrl(),
                //     'next' => $bills->nextPageUrl()
                // ],
             ]);
         }
         
         
     }
     
    public function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function quickBillPooja() {
    	$pooja = Pooja::where('quick_bill', 1)->select('id', 'name', 'rate')->get();
    	// $site_settings = 
    	return response()->json([
        	'status' => true,
        	'deity_id' => 1,
            'data' => $pooja,
        ]);
    }
}
