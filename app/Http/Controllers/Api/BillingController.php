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
use App\Models\User;
use App\Models\PaymentMode;
use App\Models\SiteSetting;
use App\Models\FinancialYear;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Exception;
use Illuminate\Support\Facades\Validator;

class BillingController extends Controller
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
                            $to_date = Carbon::parse($from_date)->addWeeks($added_weeks)->format('Y-m-d');
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
							
                        	if ($pooja['star_id'] == 28) {
                            	return response()->json([
                    				'status' => false,
                    				'data' => '',
                    				'message' => 'Please choose a valid birth star.'
                				], 500);
                            }
                        
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
                        	'is_scheduled'=> 0
                        );
                    }
                }
            }
            
        	$tem = Temple::where('punnyam_code',auth()->user()->punnyam_code)->first();
        	$bill_image = SiteSetting::first()->bill_image_pos ?? null;
        
            $response = array(
                "customer_id"   => $request->customer_id,
                "customer_name" => $request->customer_name,
            	"mobile_number" => $request->mobile_number,
            	"mode_id"		=> $request->mode_id ?? $request->payment_mode,
            	"mode" 			=> PaymentMode::find($request->mode_id)->name ?? null,
                "counter_id"    => $request->counter_id,
                "counter_name"  => $this->counter_details($request->counter_id),
            	'expected_datetime'=> $request->expected_datetime,
                "pooja_details" => $data
            );
        
        	if ($bill_image) { 
            	$response['bill_image'] = $tem->website.$bill_image ?? '';
            }
        
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


	public function getCurrentFinancialYear() {
    $date = now(); // Use Carbon to get the current date and time

    return FinancialYear::where('start_date', '<=', $date)
                        ->where('end_date', '>=', $date)
                        ->get()
                        ->first(); // Use Eloquent to query the database
}

public function getCurrentFyId() {
    $fy = $this->getCurrentFinancialYear();

    return $fy ? $fy->id : null;
}

public function getCurrentFy() {
    $fy = $this->getCurrentFinancialYear();

    return $fy ? $fy->financial_year : null;
}

public function getCurrentFyNo() {
    $fy_id = $this->getCurrentFyId();

    $last_fy_no = Billing::where('fy_id', $fy_id)
                          ->orderBy('fy_bill_no', 'desc')
                          ->value('fy_bill_no') ?? 0;

    return $last_fy_no + 1;
}

public function getCurrentFyBillNo() {
    $current_fy_no = $this->getCurrentFyNo();
    $fy_prefix = $this->getCurrentFy();

    return $fy_prefix.'/'.$current_fy_no;
}

	// Update Bill No
    public function update_bill_no($bill_id) {
        $new_bill_no 	= $this->getCurrentFyBillNo();
    	$fy_no 			= $this->getCurrentFyNo();
        $current_fy_id	= $this->getCurrentFyId();
        try {
   			DB::beginTransaction();

    		Billing::where('id', $bill_id)
        			->update([
            			'bill_no' => $new_bill_no,
            			'fy_bill_no' => $fy_no,
            			'fy_id' => $current_fy_id
        		]);

    		DB::commit();
		} catch (Exception $e) {
   			 DB::rollBack();

    		if ($e->getCode() == 23000) { // MySQL error code for duplicate entry
        
        		return $this->update_bill_no($bill_id); 
    		} else {
        		// Handle other types of exceptions
        		error_log('Database update error: ' . $e->getMessage());
    		}
		}
    }

	public function saveBill(Request $request) {
    	DB::beginTransaction();
        try {
                $pooja_details  = $request->pooja_details;
                $payment_status = ((double) $request->bill_amount == (double) $request->paid_amount && $request->mode_id) ? 'paid' : 'unpaid';
        		$payment_recorded_by = ((double) $request->bill_amount == (double) $request->paid_amount && $request->mode_id) ? auth()->user()->id : null;
        
                $billing = Billing::create([
                    'diety_id'  => $pooja_details[0]['deity_id'], 
                	'customer_name'=> $request->customer_name ?? '', 
                	'mobile_number'=> $request->mobile_number ?? '', 
                    'date'  => date('Y-m-d'),
                    'pos_user_id'   => auth()->user()->id,
                	'created_by'   => auth()->user()->id,
                    'status'    => 1,
                    'mode'  => $request->mode_id ?? null,
                    'bill_time' => date('Y-m-d h:i:s'),
                    'counter'   => $request->counter_id, 
                    'total' => (double) $request->bill_amount,
                    'recv_amt' => (double) $request->paid_amount,
                    'customer_id' => $request->customer_id ?? 1,
                	'completion_status'=> 1,
                	'payment_status'=> $payment_status,
                	'payment_recorded_by'=> $payment_recorded_by,
                	'expected_datetime' => $request->expected_datetime ?? null
                ]);
        
        		
        		if (SiteSetting::first()->store_transaction_id == 1) {
    				Billing::where('id', $billing->id)->update([
                    	'bank_transaction_id'=> $request->transaction_id ?? ''
                    ]);
				}

        		if(SiteSetting::first()->financial_year_settings == 1) {
        			$this->update_bill_no($billing->id);
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
                            'rate' => $pooja['qty'] > 0 ? $pooja['rate']/$pooja['qty'] : 0,
                            'amount' => $pooja['qty'] * $pooja['rate'],
                            'date' => Carbon::parse($pooja['from_date'])->format('Y-m-d'),
                            'status' => 1
                        ));
                    }
                    else{
                      	$pooja_data = Pooja::find($pooja['pooja_id']);

                    	if ( $pooja_data->rate > 0 ) {
                        	$poojarate=$pooja_data->rate;
                        } else {
                        	$poojarate = $pooja['rate'];
                        }
                        // $poojarate=$pooja_data->rate; // added by priyesh
                        $billing_details[] = BillingDetail::create(array(
                      
                        
                            'bill_id' => $billing->id,
                            'name' => $pooja['name'],
                            'address' => $pooja['address'] ?? '',
                            'diety_id' => $pooja['deity_id'],
                            'star' => $pooja['star_id'],
                            'pooja' => $pooja['pooja_id'],
                            'qlt' => $pooja['qty'],
                            'time' => 'M',
                            'rate' => $pooja_data->rate,
                            'amount' => $pooja['qty'] * $poojarate, // cahnegd from pooja[rate] 
                            'date' => Carbon::parse($pooja['date'])->format('Y-m-d'),
                            'status' => 1
                        ));
                    }
                }

                DB::commit();
                
                $data = array(
                    'id' => $billing->id,
                    'bill_date' => $billing->bill_time,
                    'mode' => $this->payment_mode_details($billing->mode),
                    'counter' => $this->counter_details($billing->counter),
                    'devotee' => $billing->customer_name ?? $this->customer_details($billing->user_id),
                	'mobile_number' => $billing->mobile_number ?? '',
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
                             'qty' => strval($bill->qlt),
                             'rate' => strval($bill->rate),
                             'time' => $bill->time,
                             'date' => $bill->date,
                             'amount' => strval($bill->amount)
                        );
                }
			
        		$bill_id = $billing->id;
        		$details = array();
        		$normal_details   = BillingDetail::where('bill_id', $bill_id)->where('type', '!=', 'S')->get()->map(function($query) {
							
                				$amount = $query->rate * $query->qlt;
        						return [
                                		 'name'=> $query->name,
                                		 'pooja_id'=> $query->pooja,
                                		 'qty'=> strval($query->qlt),
                                		 'rate'=> strval($query->rate),
                                		 'amount'=> strval($amount),
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
								$amount = $query->rate * $query->qlt;
                
                				$date_string = '';
							
								foreach($dates as $date) {
    								$date_string .= Carbon::parse($date)->format('d-m-Y') . ', ';
								}
								
                				$date_string = rtrim($date_string, ', ');

        						return [
                                		 'name'=> $query->name,
                                		 'pooja_id'=> $query->pooja,
                                		 'qty'=> strval($p_query->sum('qlt')),
                                		 'rate'=> strval($query->rate),
                                		 'amount'=> strval($amount),
                                		 'address' => $query->address,
                             			 'star' => $this->star_details($query->star),
                             	         'star_mal' => $this->star_details_mal($query->star),
                                         'deity' => $this->deity_details($query->diety_id),
                                		 'deity_mal' => $this->deity_details_mal($query->diety_id),
                             		 	 'pooja' => $this->pooja_details($query->pooja),
                             		 	 'pooja_mal' => $this->pooja_details_mal($query->pooja),
                             			 'time' => $query->time,
                             			 'date' => '['.$date_string.']' //Carbon::parse(min($dates))->format('d-m-Y')." to ".Carbon::parse(max($dates))->format('d-m-Y'),
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
     	 $query->where('created_by', auth()->user()->id);
         if(!is_null($request->date)){
            $query->where('date',$request->date);
         }
         if(!is_null($request->bill_no)){
            $query->where('id',$request->bill_no);
         }
		 
     	 $query->where('deleted', '0');
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

	public function changeCompletionStatusToActive(Request $request) {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|integer'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            $id      = $request->bill_id;
            $billing = Billing::find($id);
	
        	if (!$billing) {
            	return response()->json([
                	'status' => false,
                	'message' => 'Booking not found',
            	], 404);
        	}
            $billing->update(['completion_status'=> 2, 'assigned_to'=> auth()->user()->id, 'started_by'=> auth()->user()->id]);
    
            DB::commit();

            $response = [
                'status' => true,
                'bill'   => new BillingResource($billing),
                'message' => 'Successfully updated!'
            ];
    
            return response()->json($response, 200);
    
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

	public function changeCompletionStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|integer'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            $id      = $request->bill_id;
            $billing = Billing::find($id);
			
        	if (!$billing) {
            	return response()->json([
                	'status' => false,
                	'message' => 'Booking not found',
            	], 404);
        	}
        
            $billing->update(['completion_status'=> 3, 'completed_by'=> auth()->user()->id]);
    
            DB::commit();

            $response = [
                'status' => true,
                'bill'   => new BillingResource($billing),
                'message' => 'Successfully updated!'
            ];
    
            return response()->json($response, 200);
    
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pendingList(Request $request){
        $bill_details = [];
        $query = Billing::query();
      
        $query->where('completion_status', 1);

        if (!is_null($request->from_date) && !is_null($request->to_date)) {
        	$from_date = Carbon::parse($request->from_date)->startOfDay()->format('Y-m-d');
        	$to_date = Carbon::parse($request->to_date)->endOfDay()->format('Y-m-d');
        	$query->whereBetween('date', [$from_date, $to_date]);
    	}
    
        if(!is_null($request->bill_no)){
        	$query->where('id',$request->bill_no);
        }
    
    	if (!is_null($request->vehicle_number)) {
    		$query->whereHas('billing_details', function ($query) use ($request) {
        		$query->where('name', 'like', '%' . $request->input('vehicle_number') . '%');
    		});
		}
        
        $query->where('deleted', 0);
    	
        if (!in_array(auth()->user()->role->slug, ['supervisor', 'manager'])) {
        	$query->where(function($query) {
            	$query->whereNull('assigned_to')->orWhere('assigned_to', auth()->user()->id);
        	});
    	}
    
    	$query->orderByRaw('CASE WHEN created_by = ? THEN 1 ELSE 2 END', [auth()->user()->id])
          ->orderBy('created_at', 'desc');
    
        $gross_total = (string)$query->sum('total');
        $bills = $query->get();
    	// DD($bills);
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
            ]);
        }
        
        
    }

	public function activeBillList(Request $request){
        $bill_details = [];
        $query = Billing::query();
    
        // if(!in_array(auth()->user()->role->slug, ['manager'])) {
        // 	$query->whereNull('started_by');
        // 	$query->orWhere('started_by', auth()->user()->id);
        // }
        $query->where('completion_status', 2);
    
    	if (!is_null($request->from_date) && !is_null($request->to_date)) {
        	$from_date = Carbon::parse($request->from_date)->startOfDay()->format('Y-m-d');
        	$to_date = Carbon::parse($request->to_date)->endOfDay()->format('Y-m-d');
        	$query->whereBetween('date', [$from_date, $to_date]);
    	}
        if(!is_null($request->bill_no)){
        	$query->where('id',$request->bill_no);
        }
    
    	if (!is_null($request->vehicle_number)) {
    		$query->whereHas('billing_details', function ($query) use ($request) {
        		$query->where('name', 'like', '%' . $request->input('vehicle_number') . '%');
    		});
		}
        
    	if (!in_array(auth()->user()->role->slug, ['manager'])) {
        	$query->where(function($query) {
            	$query->whereNull('started_by')->orWhere('started_by', auth()->user()->id);
        	});
    	}
    
        $query->where('deleted', '0');
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
            ]);
        }
    }

    public function completedBillList(Request $request){
        $bill_details = [];
        $query = Billing::query();
        if(auth()->user()->role->slug != 'manager') {
        	$query->where('completed_by', auth()->user()->id);
        }
        $query->where('completion_status', 3);

        if(!is_null($request->from_date) && !is_null($request->to_date)){
        	$query->whereBetween('date',[$request->from_date, $request->to_date]);
        }
        if(!is_null($request->bill_no)){
        	$query->where('id',$request->bill_no);
        }
    
    	if (!is_null($request->vehicle_number)) {
    		$query->whereHas('billing_details', function ($query) use ($request) {
        		$query->where('name', 'like', '%' . $request->input('vehicle_number') . '%');
    		});
		}
        
        $query->where('deleted', '0');
        $gross_total = (string)$query->sum('total');
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
		$page = $request->input('page', 1); // Default to page 1

		// Use paginate
		$bills = $query->paginate($perPage, ['*'], 'page', $page);
    
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
            	'pagination' => [
            		'total' => $bills->total(),
            		'current_page' => $bills->currentPage(),
            		'last_page' => $bills->lastPage(),
            		'per_page' => $bills->perPage(),
            		'from' => $bills->firstItem(),
            		'to' => $bills->lastItem(),
        		],
            ]);
        }
    }

	public function staffList(Request $request) {
    $authUser = auth()->user();
//         $validator = Validator::make($request->all(), [
//             'bill_id' => 'required|integer',
//         	'staff_id' => 'required|integer'
//         ]);
    
//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Validation error',
//                 'errors' => $validator->errors()
//             ], 422);
//         }
    
        try {
            $staff = User::whereHas('role', function ($query) {
   							 $query->where('slug', 'staff'); 
                          //  $query-> where('punnyam_code',$authUser->punnyam_code)
						   })->where('punnyam_code',$authUser->punnyam_code)->get();
			
        	if (!$staff) {
            	return response()->json([
                	'status' => false,
                	'message' => 'No staff found',
            	], 404);
        	}
        
            $response = [
                'status' => true,
                'staff'   => $staff,
                'message' => 'List of Staff!'
            ];
    
            return response()->json($response, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

	public function assignStaffToBooking(Request $request) {
    	$authUser = auth()->user();
    
    	if($authUser->role->slug == 'staff') {
        	return response()->json([
                'status' => false,
                'message' => 'Unauthorized: You do not have the required role.',
            ], 403);
        }
    
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|integer',
        	'staff_id' => 'required|integer'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
        	$staff_id = $request->staff_id;
            $bill_id  = $request->bill_id;
            $billing  = Billing::find($bill_id);
			
        	if (!$billing) {
            	return response()->json([
                	'status' => false,
                	'message' => 'Booking not found',
            	], 404);
        	}
        
            $billing->update(['assigned_to'=> $staff_id, 'started_by'=> $staff_id, 'completion_status'=> 2]);
    
            DB::commit();

            $response = [
                'status' => true,
                'bill'   => new BillingResource($billing),
                'message' => 'Successfully updated!'
            ];
    
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

	public function cancelBooking(Request $request) {
    	$authUser = auth()->user();
    
    	if($authUser->role->slug != 'manager') {
        	return response()->json([
                'status' => false,
                'message' => 'Unauthorized: You do not have the required role.',
            ], 403);
        }
    
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|integer',
        	'reason' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
    	$bill_id  = $request->bill_id;
        $billing  = Billing::find($bill_id);
    	
    	if(!$billing) {
        	return response()->json([
                'status' => false,
                'message' => 'Booking not found!'
            ], 404);
        }

    	if ($billing->completion_status != 1) {
        	return response()->json([
                'status' => false,
                'message' => 'Booking not found in the pending list!'
            ], 404);
        }
    	
        DB::beginTransaction();
        try {
        	$reason = $request->reason;

            $billing->update(['deleted'=> 1, 'dl_date'=> date('Y-m-d'), 'dl_reason'=> $reason, 'dl_user'=> auth()->user()->id ]);
    
            DB::commit();

            $response = [
                'status' => true,
                'bill'   => new BillingResource($billing),
                'message' => 'Booking cancelled successfully!'
            ];
    
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to cancel the booking.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

	public function updatePayment(Request $request) {
        $validator = Validator::make($request->all(), [
            'bill_id' => 'required|integer',
        	'mode_id' => 'required|integer',
        	'amount'  => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
        	$amount   = $request->amount;
        	$mode_id  = $request->mode_id;
            $bill_id  = $request->bill_id;
            $billing  = Billing::find($bill_id);

        	if (!$billing) {
            	return response()->json([
                	'status' => false,
                	'message' => 'Booking not found',
            	], 404);
        	}
        
        	if ($billing->total !=  $amount) {
            	return response()->json([
                	'status' => false,
                	'message' => 'Amount mismatch',
            	], 404);
        	}
        
            $billing->update(['recv_amt'=> (double) $amount, 'mode'=> $mode_id, 'payment_recorded_by'=> auth()->user()->id, 'payment_status'=> 'paid']);
    
            DB::commit();

            $response = [
                'status' => true,
                'bill'   => new BillingResource($billing),
                'message' => 'Payment Successful!'
            ];
    
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

	public function quickBill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_mode' => 'required',
            'bill_amount' => 'required|numeric',
            'paid_amount' => 'required|numeric',
        	'counter_id' => 'required|numeric',
            'pooja_details' => 'required|array',
            'pooja_details.*.pooja_id' => 'required|integer',
            'pooja_details.*.qty' => 'required|integer|min:1',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
    
        DB::beginTransaction();
        try {
            $billing = Billing::create([
                'date' => date('Y-m-d'),
                'customer_id' => $request->customer_id ?? 1,
            	'pos_user_id' => auth()->user()->id,
                'status' => 1,
                'mode' => $request->payment_mode,
                'bill_time' => date('Y-m-d H:i:s'),
                'total' => (double) $request->bill_amount,
                'recv_amt' => (double) $request->paid_amount,
                'user_id' => auth()->user()->id,
                'diety_id' => 1,
                'place' => "---",
                'amount' => "0",
                'count' => "0",
                'number' => "0",
                'bal_amt' => "0",
            	'counter' => $request->counter_id,
                'deleted' => "0",
                'dl_reason'  => "",
                'dl_user' => "0",
                'book_issue_id' => "0",
            ]);
    
            $billing_details = [];
            foreach ($request->pooja_details as $pooja) {
                $pooja_data = Pooja::find($pooja['pooja_id']);
                $pooja_rate = $pooja_data ? $pooja_data->rate : 0;
    
                $billing_details[] = BillingDetail::create([
                    'bill_id' => $billing->id,
                    'pooja' => $pooja['pooja_id'],
                    'name' => $pooja['name'],
                    'star' => $pooja['star_id'],
                    'qlt' => $pooja['qty'],
                    'rate' => $pooja_rate,
                    'amount' => $pooja['qty'] * $pooja_rate,
                    'date' => date('Y-m-d'),
                    'status' => 1,
                    'diety_id' => 1,
                    'time' => " ",
                    'type' => " ",
                    'postal_yes' => '0',
                    'postal_amt' => '0',
                ]);
            }
    
            DB::commit();
    
            $response_details = [];
            foreach ($billing_details as $bill) {
                $response_details[] = [
                    'pooja' => $bill->pooja,
                    'qty' => strval($bill->qlt),
                    'rate' => strval($bill->rate),
                    'amount' => strval($bill->amount)
                ];
            }
        
        
        	$tem = Temple::where('punnyam_code', auth()->user()->punnyam_code)->first();
        	$temple = [
            	'name' => $tem->name ?? '',
            	'name_mal' => $tem->name_mal ?? '',
            	'address_line_1' => $tem->address_line_1 . ' ' . $tem->address_line_2,
            	'address_line_2' => $tem->city . ' ' . $tem->state . ' ' . $tem->pincode,
            	'phone' => $tem->mobile,
            	'email' => $tem->email,
            	'website' => $tem->website ?? ''
        	];

        	$bill_image = SiteSetting::first()->bill_image_pos ?? null;
    
            $response = [
                'status' => true,
            	'temple' => $temple,
            	'bill_image' => $bill_image,
                'summary' => [
                    'id' => $billing->id,
                    'name' => $pooja['name'],
                    'bill_date' => $billing->bill_time,
                    'mode' => $this->payment_mode_details($billing->mode),
                    'total' => strval($billing->total),
                    'recv_amt' => strval($billing->recv_amt)
                ],
                'details' => $response_details,
                'message' => 'Quick Bill Saved!'
            ];
    
            return response()->json($response, 200);
    
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to save quick bill.',
                'error' => $e->getMessage()
            ], 500);
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
