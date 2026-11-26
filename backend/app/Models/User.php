<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    public $timestamps = false;

    protected $authPasswordName = 'password_hash';

    protected $hidden = ['password_hash'];

    public function canManageEquipment(): bool
    {
        return $this->is_active && DB::table('roles')
            ->where('id', $this->role_id)->where('code', 'staff')->exists();
    }
}
