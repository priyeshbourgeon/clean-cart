<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PoojaSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     
     public function toArray($request)
    {
        return [
        	 'pooja_id' => $this->pooja_id,
             'pooja_name' => $this->pooja_name,
             'pooja_count' => (int)$this->pooja_count,
             'total_rate' => $this->total_rate,
        	 'total_received' => $this->total_received
          ];
    }
   
    
   
}
