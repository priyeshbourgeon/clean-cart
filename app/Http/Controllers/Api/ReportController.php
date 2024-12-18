<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoojaSummaryResource;
use App\Http\Resources\DailySummaryResource;
use App\Models\BillingDetail;
use App\Models\Billing;
use App\Models\PaymentMode;
use App\Models\Counter;
use App\Models\Temple;
use App\Models\Deity;
use App\Models\DeityPooja;
use App\Models\Devotee;
use App\Models\Pooja;
use App\Models\Star;
use App\Models\User;
use Illuminate\Http\Request;
use DB;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function poojaList(Request $request){
    
     $tem = Temple::where('punnyam_code',auth()->user()->punnyam_code)->first();
    
        $temple = array(
               'name' => $tem->name ?? '',
               'name_mal' => $tem->name_mal ?? '',
               'address_line_1' => $tem->address_line_1.' '.$tem->address_line_2,
               'address_line_2' => $tem->city.' '.$tem->state.' '.$tem->pincode,
               'phone' => $tem->mobile,
               'email' => $tem->email,
               'website' => $tem->website
            );
    
        $data = [];
        $deities = BillingDetail::selectRaw('diety.id,diety.name')
                                  ->distinct()
        						  ->leftJoin('billing','billing.id','billing_dtls.bill_id')
        						  ->leftJoin('diety','diety.id','billing_dtls.diety_id')
        						  ->whereDate('billing_dtls.date',today())
                                  ->where('billing.deleted','0')
                                 ->get();
        foreach($deities as $deity){
             array_push($data,array(
                                'diety_name' => $deity->name,
                                'diety_id' => $deity->id,
                                'poojas' => $this->getPoojas($deity->id),
             ));
        }
        
        
                               
        return response()->json([
           'status' => true,
           'temple' => $temple,
           'pooja_list' => $data,
           'message' => 'Fetched Pooja List'
        ]);
    }
                        
    public function getPoojas($deity_id){
         $data = BillingDetail::select('pooja.id', 'pooja.name as pooja_name')
         					->distinct()
                            ->leftJoin('billing','billing.id','billing_dtls.bill_id')
                            ->leftJoin('diety','diety.id','billing_dtls.diety_id')
                            ->leftJoin('pooja','pooja.id','billing_dtls.pooja')
                            ->whereDate('billing_dtls.date',today())
                            ->where('billing.deleted','0')
                            ->where('billing_dtls.diety_id',$deity_id)
         					->where('billing.pos_user_id', auth()->user()->id)
                            ->get();   
    
          $poojas = [];
    	  foreach($data as $pooja){
              array_push($poojas,array(
                                'pooja_name' => $pooja->pooja_name,
                                'pooja_id' => $pooja->id,
                                'bills' => $this->getBills($deity_id,$pooja->id),
              ));
          }
    
          return $poojas;
    
    }

    public function getBills($deity_id,$pooja_id){
    		$data = BillingDetail::select('billing_dtls.*','billing.id as bill_no','stars.name_mal as star_name')
         					->distinct()
                            ->leftJoin('billing','billing.id','billing_dtls.bill_id')
                            ->leftJoin('diety','diety.id','billing_dtls.diety_id')
                            ->leftJoin('pooja','pooja.id','billing_dtls.pooja')
            				->leftJoin('stars','stars.id','billing_dtls.star')
                            ->whereDate('billing_dtls.date',today())
                            ->where('billing.deleted','0')
                            ->where('billing_dtls.diety_id',$deity_id)
                            ->where('billing_dtls.pooja',$pooja_id)
            				->where('billing.pos_user_id', auth()->user()->id)
                            ->get();   
    
          $bills = [];
    	  foreach($data as $bill){
              array_push($bills,array(
                                'bill_no' => 'Bill-'.$bill->bill_no,
                                'name' => $bill->name,
                                'star' => $bill->star_name,
              					'time' => $bill->time,
                                'nos' => $bill->qlt
                                
              ));
          }
    
          return $bills;
    }
    
    public function counterWise(Request $request){
        $targetDate = $request->from_date; 
        $targetCounterId = $request->counter_id; 
        
        $counter = Counter::find($targetCounterId);
        
        if(!$counter){
             return response()->json([
                'status' => false,
                'message' => 'Counter Not Found!'
            ]);
        }
        
    	
        $paymentModes = PaymentMode::with(['billings' => function ($query) use ($targetDate, $targetCounterId) {
            $query->selectRaw('mode, SUM(recv_amt) as total_amount')
                ->where('date', $targetDate)
                ->where('counter', $targetCounterId);
        		
            	if(auth()->user()->role->slug != 'supervisor' || auth()->user()->role->slug != 'manager') {
             		$query->where('billing.payment_recorded_by', auth()->user()->id);      
            	}
            	$query->where('deleted', 0)
                ->groupBy('mode');
        }])->get();
        
        
        $collection = [];
        $total_collection = 0;
        foreach ($paymentModes as $paymentMode) {
            $billing = $paymentMode->billings->first();
        	$amount  = $billing ? $billing->total_amount : 0;
            $collection[] = [
                'payment_mode' => $paymentMode->name,
                'total_amount' => (string)$amount,
            ];
            
            $total_collection += ($amount);
        }
        
        return response()->json([
            'status' => true,
            'selected_date' => $targetDate,
            'counter' => $counter->name,
            'data' => $collection,
            'total_collection' => (string)$total_collection
        ]);
        
    } 
    
    public function dailySummary(Request $request){
        $query = BillingDetail::selectRaw('pooja.id,pooja.name as pooja_name,billing_dtls.qlt as quantity,billing_dtls.rate,billing_dtls.postal_amt,billing_dtls.amount ')
                            ->leftJoin('billing','billing.id','billing_dtls.bill_id')
                            ->leftJoin('diety','diety.id','billing_dtls.diety_id')
                            ->leftJoin('pooja','pooja.id','billing_dtls.pooja');
    	
    	$query->where('billing.pos_user_id', auth()->user()->id);
        if(!is_null($request->from_date) && !is_null($request->to_date)){
            $query->whereBetween('billing.date',[$request->from_date,$request->to_date]);
        }
        if(!is_null($request->diety_id)){
            $query->where('billing_dtls.diety_id',$request->diety_id);
        }
        
        $summaries = $query->paginate(10);
        return response()->json([
            'status' => true,
            'data' => DailySummaryResource::collection($summaries),
            'meta' => [
                'total' => $summaries->total(),
                'per_page' => $summaries->perPage(),
                'current_page' => $summaries->currentPage(),
                'last_page' => $summaries->lastPage(),
                'next_page_url' => $summaries->nextPageUrl(),
                'prev_page_url' => $summaries->previousPageUrl(),
                'from' => $summaries->firstItem(),
                'to' => $summaries->lastItem()
            ],
            'links' => [
                'self' => $summaries->url($summaries->currentPage()),
                'first' => $summaries->url(1),
                'last' => $summaries->url($summaries->lastPage()),
                'prev' => $summaries->previousPageUrl(),
                'next' => $summaries->nextPageUrl()
            ]
        ]);
        
    } 
    
    

    public function poojaSummary(Request $request)
    {
        try {
            // $query = BillingDetail::selectRaw('pooja.name as pooja_name, SUM(billing_dtls.qlt) as pooja_count,SUM(billing_dtls.rate) AS total_rate')
            //     ->leftJoin('pooja', 'pooja.id', '=', 'billing_dtls.pooja');
    
            $query = BillingDetail::selectRaw('pooja.id as pooja_id, pooja.name as pooja_name, SUM(billing_dtls.qlt) as pooja_count,SUM(billing.recv_amt) AS total_received, SUM(billing.total) AS total_rate')
                            ->leftJoin('billing','billing.id','billing_dtls.bill_id')
                            // ->leftJoin('diety','diety.id','billing_dtls.diety_id')
                            ->leftJoin('pooja','pooja.id','billing_dtls.pooja');
       
        	if(!in_array(auth()->user()->role->slug,  ['supervisor', 'manager'])) {
             	$query->where('billing.created_by', auth()->user()->id);      
            }
            if(!is_null($request->from_date) && !is_null($request->to_date)){
                $query->whereBetween('billing.date',[$request->from_date,$request->to_date]);
            }
        
        	$query->where('billing.deleted', 0);
            $gross_total = $query->get()->sum('total_rate');
            
            $poojaSummaries = $query->groupBy('billing_dtls.pooja')
                ->get();
        
        $tem = Temple::where('punnyam_code',auth()->user()->punnyam_code)->first();
    
        $temple = array(
               'name' => $tem->name ?? '',
               'name_mal' => $tem->name_mal ?? '',
               'address_line_1' => $tem->address_line_1.' '.$tem->address_line_2,
               'address_line_2' => $tem->city.' '.$tem->state.' '.$tem->pincode,
               'phone' => $tem->mobile,
               'email' => $tem->email,
               'website' => $tem->website
            );
        
            return response()->json([
                'status' => true,
                'temple' => $temple,
                'data' => PoojaSummaryResource::collection($poojaSummaries),
                'gross_total' => strval($gross_total),//(string)$poojaSummaries->sum('total_rate'),
                // 'meta' => [
                //     'total' => $poojaSummaries->total(),
                //     'per_page' => $poojaSummaries->perPage(),
                //     'current_page' => $poojaSummaries->currentPage(),
                //     'last_page' => $poojaSummaries->lastPage(),
                //     'next_page_url' => $poojaSummaries->nextPageUrl(),
                //     'prev_page_url' => $poojaSummaries->previousPageUrl(),
                //     'from' => $poojaSummaries->firstItem(),
                //     'to' => $poojaSummaries->lastItem()
                // ],
                // 'links' => [
                //     'self' => $poojaSummaries->url($poojaSummaries->currentPage()),
                //     'first' => $poojaSummaries->url(1),
                //     'last' => $poojaSummaries->url($poojaSummaries->lastPage()),
                //     'prev' => $poojaSummaries->previousPageUrl(),
                //     'next' => $poojaSummaries->nextPageUrl()
                // ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching pooja summaries.',
                'error' => $e->getMessage()
            ]);
        }
    }


	public function poojaSummaryDetail(Request $request, $service_id){
    	$authUser = auth()->user(); 

        $from_date       = $request->from_date; 
    	$to_date         = $request->to_date; 
    	$vehicle_no      = $request->vehicle_number; 

    	$staff			 = $authUser;
    	$service     	 = Pooja::find($service_id);
    	$result = [];

    	if (!empty($staff)) {
			$query = 	BillingDetail::join('billing', 'billing.id', '=', 'billing_dtls.bill_id');
            
        	if($from_date && $to_date) {
				$query->whereBetween('billing.date', [$from_date, $to_date]); 
			}
          	
        	if(!in_array($staff->role->slug, ['supervisor', 'manager'])) {
				$query->where('billing.created_by', $staff->id); 
			}
			
        	if($vehicle_no) {
				$query->where('billing_dtls.name', 'like', '%' . $vehicle_no . '%'); 
			}
        
        	$result['vehicles'] = $query->where('billing_dtls.pooja', $service_id)
										->where('billing.deleted', 0) 
										->selectRaw('billing.customer_name, billing.completed_by, billing.created_by, billing.mobile_number, billing.date, billing.created_at, billing_dtls.diety_id, billing_dtls.name as vehicle_no, billing_dtls.amount')
            							->get();
        }
    

        
        $collection = [];
        $total_count = 0;
    	$vehicles = [];
    
    	$billingDetails = $result['vehicles'];
        $billing = $billingDetails->count();
    	// dd($billingDetails->first());
        $amount  = $billing ? $billing : 0;
    	foreach ($billingDetails as $detail) {
            $vehicles[] = [
                'vehicle' 	=> $detail->vehicle_no,
            	'customer' 	=> $detail->customer_name,
            	'mobile_number' 	=> $detail->mobile_number,
                'amount'  	=> $detail->amount,
            	'bill_time' => \Carbon\Carbon::parse($detail->created_at)->format('d-m-Y h:i a'),
            	'created_by' => User::find($detail->created_by)->name ?? null,
            	'performed_by' => User::find($detail->completed_by)->name ?? null,
            ];
            
            $total_count += ($amount);
        }
        
        $collection = [
        	'user' => $staff->name,
        	'service' => $service->name,
        	'vehicles' => $vehicles
    	];

        return response()->json([
            'status' => true,
            'start_date' => $from_date,
        	'end_date' => $to_date,
            'data' => $collection
        ]);
    } 

    
    public function billReprint(Request $request){
        $query = BillingDetail::selectRaw('billing.date,billing.diety_id,billing.id as bill_no,diety.name as dietyname,billing.total,billing_dtls.postal_amt,billing.recv_amt,billing.bal_amt,billing.mode ')
                            ->leftJoin('billing','billing.id','billing_dtls.bill_id')
                            ->leftJoin('diety','diety.id','billing_dtls.diety_id');
        if(!is_null($request->from_date) && !is_null($request->to_date)){
            $query->whereBetween('billing.date',[$request->from_date,$request->to_date]);
        }
        if(!is_null($request->diety_id)){
            $query->where('billing.diety_id',$request->diety_id);
        }
        if(!is_null($request->bill_no)){
            $query->where('billing.id',$request->bill_no);
        }
        if(!is_null($request->mode)){
            $query->where('billing.mode',$request->mode);
        }
        
        $billreprint = $query->paginate(10);
        return response()->json([
            'status' => true,
            'data' => $billreprint
        ]);
        
    } 
    
        
    public function billDetailsbyId(Request $request){
        $bill = Billing::find($request->id);
        $bill_data = array(
                         'bill_id' => $bill->id,
                         'bill_date' => $bill->bill_time,
                         'counter' => $this->counter_details($bill->counter),
                         'devotee' => $this->customer_details($bill->customer_id),
                         'payment_mode' => $this->payment_mode_details($bill->mode),
                         'total' => $bill->total
                    );
        $query = BillingDetail::selectRaw(' diety.name as dietyname,
                                            diety.name_mal as dietyname_mal,
                                            pooja.name as poojaname,
                                            pooja.name_mal as poojaname_mal,
                                            billing_dtls.name,
                                            stars.name_eng as starname,
                                            stars.name_mal as starname_mal,
                                            billing_dtls.qlt as quantity,
                                            billing_dtls.rate,
                                            billing_dtls.amount as total,
                                            billing_dtls.time,
                                            billing_dtls.date as pooja_date ')
                            ->leftJoin('billing','billing.id','billing_dtls.bill_id')
                            ->leftJoin('diety','diety.id','billing_dtls.diety_id')
                            ->leftJoin('pooja','pooja.id','billing_dtls.pooja')
                            ->leftJoin('stars','stars.id','billing_dtls.star')
                            ->where('billing.id',$request->id);
                            
        $billdetails = $query->get();
        
        $tem = Temple::where('punnyam_code',auth()->user()->punnyam_code)->first();
        
        $temple = array(
               'name' => $tem->name ?? '',
               'name_mal' => $tem->name_mal ?? '',
               'address_line_1' => $tem->address_line_1.' '.$tem->address_line_2,
               'address_line_2' => $tem->city.' '.$tem->state.' '.$tem->pincode,
               'phone' => $tem->mobile,
               'email' => $tem->email,
               'website' => $tem->website
            );
        return response()->json([
            'status' => true,
            'temple' => $temple,
            'bill_summary' => $bill_data,
            'bill_details' => $billdetails
        ]);
    }
    
    
    
    
    public function dailyPoojawiseSummary(Request $request){
        
    } 
     
    public function index()
    {
        $temples = Temple::all('id','name','punnyam_code');

        return response()->json([
            'status' => true,
            'data' => $temples
        ]);
    }
    
     public function customer_details($id){
        return Devotee::find($id)->name ?? 'Walk-in Devotee';
    }

    public function counter_details($id){
        return Counter::find($id)->name ?? '';
    }

    public function payment_mode_details($id){
        return PaymentMode::find($id)->name ?? '';
    }

 
	public function staffWiseReport(Request $request){
    	$authUser = auth()->user();
    
    	if(!in_array($authUser->role->slug, ['supervisor', 'manager'])) {
        	return response()->json([
                'status' => false,
                'message' => 'Unauthorized: You do not have the required role.',
            ], 403);
        }
    
        $from_date       = $request->from_date; 
    	$to_date         = $request->to_date; 
    	
    	$staff			 = User::where('punnyam_code',$authUser->punnyam_code)->get();

    	$result = [];
    
    	if (count($staff) > 0) {
        	foreach($staff as $user) {
            	$result['user'][]	= $user;
        		$result['payment_modes'][] = PaymentMode::with(['billings' => function ($query) use ($from_date, $to_date, $user) {
            	$query->selectRaw('mode, SUM(recv_amt) as total_amount')
                ->whereBetween('date', [$from_date, $to_date])
            	->where('payment_recorded_by', $user->id)
            	->whereNotNull('mode')
                ->where('deleted', 0)
                ->groupBy('mode');
        		}])->get();
            
            	$result['cancelled'][] = Billing::whereBetween('date', [$from_date, $to_date])
            								   ->where('created_by', $user->id)
                							   ->where('deleted', 1)
                							   ->whereNotNull('mode')
                							   ->sum('recv_amt');
            
            	$result['balance'][] = Billing::whereBetween('date', [$from_date, $to_date])
            								   ->where('created_by', $user->id)
                							   ->where('deleted', 0)
                						       ->whereNull('mode')
                							   ->sum('total');
            }
        }

        $collection = [];
        $total_collection = 0;
    	foreach($result['payment_modes'] as $key =>  $paymentModes) {
        $user = $result['user'][$key];
        $modes = [];
        foreach ($paymentModes as $paymentMode) {
            $billing = $paymentMode->billings->first();
        	$amount  = $billing ? $billing->total_amount : 0;
            $modes[] = [
                'payment_mode' => $paymentMode->name,
                'total_amount' => (string)$amount,
            ];
            
            $total_collection += ($amount);
        }

        	$modes[] = [
                'payment_mode' => 'Cancelled',
                'total_amount' => (string)$result['cancelled'][$key],
            ];
        
        	$modes[] = [
                'payment_mode' => 'Balance',
                'total_amount' => (string)$result['balance'][$key],
            ];
        
        	$collection[] = [
        		'user' => $user->name,
            	'user_id' => $user->id,
        		'modes' => $modes
    		];
        }

        return response()->json([
            'status' => true,
            'start_date' => $from_date,
        	'end_date' => $to_date,
            'data' => $collection,
            'total_collection' => (string)$total_collection
        ]);
    } 

	public function staffWiseTypeWiseReport(Request $request, $user_id){
    	$authUser = auth()->user();
    
    	if(!in_array($authUser->role->slug, ['supervisor', 'manager'])) {
        	return response()->json([
                'status' => false,
                'message' => 'Unauthorized: You do not have the required role.',
            ], 403);
        }

        $from_date       = $request->from_date; 
    	$to_date         = $request->to_date; 

    	$staff			 = User::find($user_id);
    	$result = [];

    	if (!empty($staff)) {
			$result['vehicle_types'] = Deity::with(['billingDetails' => function ($query) use ($from_date, $to_date, $staff) {
											$query->join('billing', 'billing.id', '=', 'billing_dtls.bill_id') 
												->whereBetween('billing.date', [$from_date, $to_date]) 
												->where('billing.created_by', $staff->id) 
												->where('billing.deleted', 0) 
												->selectRaw('billing_dtls.diety_id, billing_dtls.amount');
										}])->get();
        }
    

        
        $collection = [];
        $total_count = 0;
    	$vehicle_types = [];
    	$vehicleTypes = $result['vehicle_types'];
        foreach ($vehicleTypes as $vehicleType) {
            $billing = $vehicleType->billingDetails->count();
        	$amount  = $billing ? $billing : 0;
            $vehicle_types[] = [
                'vehicle_type' => $vehicleType->name,
            	'vehicle_type_id' => $vehicleType->id,
                'total_count' => (string)$amount,
            ];
            
            $total_count += ($amount);
        }
        
        $collection = [
        	'user' => $staff->name,
        	'vehicle_types' => $vehicle_types
    	];

        return response()->json([
            'status' => true,
            'start_date' => $from_date,
        	'end_date' => $to_date,
            'data' => $collection,
            'total_count' => (string)$total_count
        ]);
    } 

	public function staffWiseTypeWiseDetail(Request $request, $user_id, $vehicle_type_id){
    	$authUser = auth()->user();
    
    	if($authUser->role->slug != 'supervisor') {
        	return response()->json([
                'status' => false,
                'message' => 'Unauthorized: You do not have the required role.',
            ], 403);
        }

        $from_date       = $request->from_date; 
    	$to_date         = $request->to_date; 

    	$staff			 = User::find($user_id);
    	$vehicleType     = Deity::find($vehicle_type_id);
    	$result = [];

    	if (!empty($staff)) {
			$result['vehicles'] = Deity::where('id', $vehicleType->id)->with(['billingDetails' => function ($query) use ($from_date, $to_date, $staff) {
											$query->join('billing', 'billing.id', '=', 'billing_dtls.bill_id') 
												->whereBetween('billing.date', [$from_date, $to_date]) 
												->where('billing.created_by', $staff->id) 
												->where('billing.deleted', 0) 
												->selectRaw('billing.date, billing.created_at, billing_dtls.diety_id, billing_dtls.name as vehicle_no, billing_dtls.amount');
										}])->first();
        }
    

        
        $collection = [];
        $total_count = 0;
    	$vehicles = [];
    
    	$vehicle = $result['vehicles'];
        $billing = $vehicle->billingDetails->count();
        $amount  = $billing ? $billing : 0;
    	foreach ($vehicle->billingDetails as $detail) {
            $vehicles[] = [
                'vehicle' 	=> $detail->vehicle_no,
                'amount'  	=> $detail->amount,
            	'bill_time' => \Carbon\Carbon::parse($detail->created_at)->format('d-m-Y h:i a'),
            ];
            
            $total_count += ($amount);
        }
        
        $collection = [
        	'user' => $staff->name,
        	'vehicle_type' => $vehicleType->name,
        	'vehicles' => $vehicles
    	];

        return response()->json([
            'status' => true,
            'start_date' => $from_date,
        	'end_date' => $to_date,
            'data' => $collection,
            'total_count' => (string)$total_count
        ]);
    } 

	// Created, performed, collected by
	public function staffWiseSummary(Request $request){
    	$authUser = auth()->user();
    	
        $from_date       = $request->from_date; 
    	$to_date         = $request->to_date; 
    	$staff			 = User::where('punnyam_code',$authUser->punnyam_code)->get();
    	
    	$result = [];
    	if (count($staff) > 0) {
        	foreach($staff as $user) {
            	$result['user'][]	= $user;
            
            	$createdCount       = Billing::whereBetween('date', [$from_date, $to_date])
            								 ->where('created_by', $user->id)
                							 ->where('deleted', 0)->count();
            	$performedCount     = Billing::whereBetween('date', [$from_date, $to_date])
            								 ->where('assigned_to', $user->id)
                							 ->where('deleted', 0)->count();
           	    $collectedCount     = Billing::whereBetween('date', [$from_date, $to_date])
            								 ->where('payment_recorded_by', $user->id)
                							 ->where('deleted', 0)->count();
            
            	$unpaid1  			= Billing::whereBetween('date', [$from_date, $to_date])
            								 ->where('assigned_to', $user->id)
                						     ->whereNULL('payment_recorded_by')
                							 ->where('deleted', 0)->count();
            
            	$unpaid_ids 		= Billing::whereBetween('date', [$from_date, $to_date])
            			  					 ->where('assigned_to', $user->id)
            	          				 	 ->whereNULL('payment_recorded_by')
            			  					 ->where('deleted', 0)->pluck('id');
            	// dd($unpaid1);	
            	$unpaid2  			= Billing::whereBetween('date', [$from_date, $to_date])
            								 ->where('created_by', $user->id)
                							 ->whereNotIn('id', $unpaid_ids)
                						     ->whereNULL('payment_recorded_by')
                							 ->where('deleted', 0)->count();
            
            	
            	$notCollectedCount  = $unpaid1 + $unpaid2;
                
            	$cancelledCount     = Billing::whereBetween('date', [$from_date, $to_date])
            								   ->where('created_by', $user->id)
                							   ->where('deleted', 1)->count();
            
            	
        		$result['data'][] = [
                	'created'	=> $createdCount,
                	'performed'	=> $performedCount,
                	'collected' => $collectedCount,
                	'unpaid'	=> $notCollectedCount,
                	'cancelled'	=> $cancelledCount
                ];
            }
        }
        
        $collection = [];
        $total_collection = 0;
    	foreach($result['data'] as $key =>  $data) {
        	$user = $result['user'][$key];
       	 	$modes = [];
        
        	foreach ($data as $key => $count) {
            	$modes[] = [
                	'key' => $key,
                	'count' => $count,
            	];
            
        	}
        
        	$collection[] = [
        		'user' => $user->name,
            	'user_id' => $user->id,
        		'data' => $modes
    		];
        }

        return response()->json([
            'status' => true,
            'start_date' => $from_date,
        	'end_date' => $to_date,
            'data' => $collection,
            'total_collection' => (string)$total_collection
        ]);
    } 

	// Created, performed, collected by - For Staff
	public function staffWiseTypeWiseSummary(Request $request, $staff_id){
    	$authUser = auth()->user();
    
        $from_date       = $request->from_date; 
    	$to_date         = $request->to_date;
    	$type         	 = $request->type;
    	$staff			 = User::find($staff_id);
    	
    	switch ($type) {
    		case 'created':
        		$status_key = 'created_by';
        		break;
    		case 'performed':
        		$status_key = 'assigned_to';
        		break;
        	case 'collected':
        		$status_key = 'payment_recorded_by';
        		break;
    		default:
        		$status_key = null;
       			break;
		}
    	
    	$result = [];
    	if (!empty($staff)) {
        	 $details = Deity::with(['billingDetails' => function ($query) use ($from_date, $to_date, $staff, $status_key, $type) {
											$query->join('billing', 'billing.id', '=', 'billing_dtls.bill_id') 
												->whereBetween('billing.date', [$from_date, $to_date]);
             								
             								if($status_key) {
												$query->where('billing.'.$status_key, $staff->id)->where('billing.deleted', 0);
                                            } else if ($type == 'unpaid'){
                						     		  $query->where('billing.created_by', $staff->id)
                						     		  	    ->whereNULL('billing.payment_recorded_by')->where('billing.deleted', 0);
                                            } else if($type == 'cancelled') {
                                            	$query->where('billing.created_by', $staff->id)->where('billing.deleted', 1);
                                            } 
											$query->selectRaw('billing_dtls.diety_id, billing_dtls.amount');
										}])->get();
        }
        
    	$collection = [];
        $total_count = 0;
    	$vehicle_types = [];

        foreach ($details as $vehicleType) {
            $count = $vehicleType->billingDetails->count();
        	
            $vehicle_types[] = [
                'vehicle_type' => $vehicleType->name,
            	'vehicle_type_id' => $vehicleType->id,
                'total_count' => (string)$count,
            ];
            
            $total_count += ($count);
        }
        
        $collection = [
        	'user' => $staff->name,
        	'vehicle_types' => $vehicle_types
    	];

        return response()->json([
            'status' => true,
            'start_date' => $from_date,
        	'end_date' => $to_date,
            'data' => $collection,
            'total_count' => (string)$total_count
        ]);
    } 

	public function staffWiseTypeWiseDetailSummary(Request $request, $user_id, $vehicle_type_id){
    	$authUser = auth()->user();
    
    	if(!in_array($authUser->role->slug, ['supervisor', 'manager'])) {
        	return response()->json([
                'status' => false,
                'message' => 'Unauthorized: You do not have the required role.',
            ], 403);
        }

        $from_date       = $request->from_date; 
    	$to_date         = $request->to_date;
    	$type         	 = $request->type;
    	$staff			 = User::find($user_id);
    	$vehicleType     = Deity::find($vehicle_type_id);
    	
    	switch ($type) {
    		case 'created':
        		$status_key = 'created_by';
        		break;
    		case 'performed':
        		$status_key = 'assigned_to';
        		break;
        	case 'collected':
        		$status_key = 'payment_recorded_by';
        		break;
    		default:
        		$status_key = null;
       			break;
		}

    	$result = [];

    	if (!empty($staff)) {
			$result['vehicles'] = Deity::where('id', $vehicleType->id)->with(['billingDetails' => function ($query) use ($from_date, $to_date, $staff, $status_key, $type) {
											$query->join('billing', 'billing.id', '=', 'billing_dtls.bill_id') 
												->whereBetween('billing.date', [$from_date, $to_date]); 
            
											if($status_key) {
												$query->where('billing.'.$status_key, $staff->id)->where('billing.deleted', 0);
                                            } else if ($type == 'unpaid'){
                                            	$query->where('billing.created_by', $staff->id)
                						     		  ->whereNULL('billing.payment_recorded_by')->where('billing.deleted', 0);
                                            } else if($type == 'cancelled') {
                                            	$query->where('billing.created_by', $staff->id)->where('billing.deleted', 1);
                                            } 
            								
											$query->selectRaw('billing.date, billing.created_at, billing_dtls.diety_id, billing_dtls.name as vehicle_no, billing_dtls.amount');
										}])->first();
        	
        }
    
        
        $collection = [];
        $total_count = 0;
    	$vehicles = [];
    
    	$vehicle = $result['vehicles'];
        $billing = $vehicle->billingDetails->count();
        $amount  = $billing ? $billing : 0;
    	foreach ($vehicle->billingDetails as $detail) {
            $vehicles[] = [
                'vehicle' 	=> $detail->vehicle_no,
                'amount'  	=> $detail->amount,
            	'bill_time' => \Carbon\Carbon::parse($detail->created_at)->format('d-m-Y h:i a'),
            ];
            
            $total_count += ($amount);
        }
        
        $collection = [
        	'user' => $staff->name,
        	'vehicle_type' => $vehicleType->name,
        	'vehicles' => $vehicles
    	];

        return response()->json([
            'status' => true,
            'start_date' => $from_date,
        	'end_date' => $to_date,
            'data' => $collection,
            'total_count' => (string)$total_count
        ]);
    } 
}
