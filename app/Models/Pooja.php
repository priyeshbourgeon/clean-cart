<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pooja extends Model
{
    use HasFactory;

    protected $connection = 'users';

    protected $table = 'pooja';

	public function billingDetails() {
    	return $this->hasMany(BillingDetail::class, 'pooja');
	}

    public function deities()
    {
        return $this->belongsToMany(Deity::class, 'diety_pooja', 'pooja_id', 'temple_id');
    }
}
