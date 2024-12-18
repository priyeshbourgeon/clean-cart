<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Devotee;
use App\Models\Counter;
use App\Models\Star;
use App\Models\Deity;
use App\Models\BirthStar;
use App\Models\Pooja;
use App\Models\PaymentMode;
use App\Models\User;

class BillingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     
     public function toArray($request)
    {
        return [
             'bill_id' => $this->id,
             'bill_date' => $this->date,
             'counter' => $this->counter_details($this->counter),
        	 'staff' => $this->staff_details($this->pos_user_id),
             'devotee' => $this->customer_name ?? $this->customer_details($this->customer_id),
        	 'mobile_number' => $this->mobile_number,
        	 'vehicle_number' => $this->billing_details[0]->name ?? '',
        	 'service' => $this->pooja_details($this->billing_details[0]->pooja ?? ''),
             'payment_mode' => $this->payment_mode_details($this->mode),
        	 'expected_datetime' => $this->expected_datetime ?? null,
             'total' => (string)$this->total
          ];
    }
   
	public function staff_details($id){
        return User::find($id)->name ?? '';
    }
    
    public function customer_details($id){
        return Devotee::find($id)->name ?? 'Walk-in Devotee';
    }

    public function counter_details($id){
        return Counter::find($id)->name ?? 'NA';
    }

    public function star_details($id){
        return Star::find($id)->name_eng ?? '';
    }

    public function deity_details($id){
        return Deity::find($id)->name ?? '';
    }

    public function special_star_details($id){
        return BirthStar::where('other_code',$id)->first()->other_name ?? '';
    }

    public function pooja_details($id){
        return Pooja::find($id)->name ?? '';
    }
    
    public function payment_mode_details($id){
        return PaymentMode::find($id)->name ?? 'NA';
    }
}
