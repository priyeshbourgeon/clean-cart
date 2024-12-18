<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeityPooja extends Model
{
    use HasFactory;

    protected $connection = 'users';

    protected $table = 'diety_pooja';

    public function pooja(){
        return $this->belongsTo(Pooja::class);
    }
}
