<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageBlocked extends Model
{
    protected $guarded = [];

    public function blocked()
    {
        return $this->hasOne('App\User', 'id', 'blocked_id');
    }
}