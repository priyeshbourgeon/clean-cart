<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DeityPoojaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'deity_pooja_id' => $this->id,
            'pooja_id' => $this->pooja->id,
            'name' => $this->code." | ".$this->pooja->name,
            'name_mal' => $this->pooja->name_mal,
            'rate' => (string)$this->pooja->rate,
        	'row_count'=> $this->pooja->rowcount
        ];
    }
}
