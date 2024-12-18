<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Billing extends Model
{
    use HasFactory;
    
    protected $connection = 'users';

    protected $table = 'billing';

    protected $guarded = [''];

    public $timestamps = false;
    
    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class,'mode','id');
    }
    
    public function billing_details()
    {
        return $this->hasMany(BillingDetail::class,'bill_id','id');
    }

	public static function hasColumn($column)
    {
        try {
        	$tableName = (new self())->getTable();
        	return Schema::hasColumn($tableName, $column);
    	} catch (\Throwable $th) {
        	// Log or print the exception for debugging
        	dd($th->getMessage());
        	return false;
    	}
    }
}
