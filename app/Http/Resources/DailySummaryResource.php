<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class DailySummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     
     public function toArray($request)
     {
        return [
             'id' => $this->id,
             'pooja_name' => $this->pooja_name,
             'quantity' => $this->quantity,
             'rate' => $this->rate,
             'postal_amt' => $this->postal_amt,
             'amount' => $this->amount
          ];
     }
   
    
   
}
