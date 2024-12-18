<?php

namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingDetail extends Model
{
    use HasFactory;
    
    protected $connection = 'users';

    protected $table = 'billing_dtls';

    protected $guarded = [''];

    public $timestamps = false;


    public function pooja()
    {
        return $this->belongsTo(Pooja::class,'pooja','id');
    }
    
    public function bill()
    {
        return $this->belongsTo(Billing::class,'bill_id','id');
    }
}
