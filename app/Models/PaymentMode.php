<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    use HasFactory;
    
    protected $connection = 'users';

    protected $table = 'payment_modes';
    
     public function billings()
    {
        return $this->hasMany(Billing::class,'mode','id');
    }

}
