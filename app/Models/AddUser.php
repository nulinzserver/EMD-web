<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddUser extends Model
{
    protected $table = "add_user";

    protected $fillable = [
        'mc_id',
        'name',
        'role',
        'mobile_number',
        'password',
        'status',
        'permission'
    ];
}
