<?php

namespace Rennokki\Plans\Test\Models;

use Rennokki\Plans\Traits\HasPlans;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasPlans;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];
}
