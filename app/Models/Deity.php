<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deity extends Model
{
    use HasFactory;

    protected $connection = 'users';

    protected $table = 'diety';

	public function billingDetails() {
    	return $this->hasMany(BillingDetail::class, 'diety_id'); 
	}

}
