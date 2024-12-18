<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devotee extends Model
{
    use HasFactory;

    protected $connection = 'users';

    protected $table = 'user_dtl';

    protected $guarded = [''];

    public $timestamps = false;
}
