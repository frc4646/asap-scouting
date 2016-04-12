<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    protected $table = "permissions";

    protected $fillable = [
        "is_admin",
    ];

    public static $defaults = [
        "is_admin" => false,
    ];
}
