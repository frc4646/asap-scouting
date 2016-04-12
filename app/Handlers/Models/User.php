<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = "users";

    protected $fillable = [
        "email",
        "username",
        "password",
        "first_name",
        "last_name",
        "active",
        "active_hash",
        "recover_hash",
        "remember_identifier",
        "remember_token",
        "update_email",
        "update_token",
    ];

    public function hasPermission($permission)
    {
        return (bool) $this->permissions->{$permission};
    }

    public function isAdmin()
    {
        return $this->hasPermission("is_admin");
    }

    public function permissions()
    {
        return $this->hasOne("App\Models\UserPermission", "user_id");
    }
}
