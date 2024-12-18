<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthStar extends Model
{
    use HasFactory;
    
    protected $connection = 'users';

    protected $table = 'birth_star';

}
