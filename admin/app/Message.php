<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $guarded = [];

    public function from()
    {
        return $this->hasOne('App\User', 'id', 'from_id');
    }

    public function to()
    {
        return $this->hasOne('App\User', 'id', 'to_id');
    }

    public function message_convos()
    {
        return $this->hasMany('App\MessageConvo', 'message_id');
    }
}