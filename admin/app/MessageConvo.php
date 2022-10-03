<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageConvo extends Model
{
    protected $guarded = [];

    public function message()
    {
        return $this->belongsTo('App\Message', 'message_id');
    }
    public function from()
    {
        return $this->hasOne('App\User', 'id', 'from_id');
    }
    public function to()
    {
        return $this->hasOne('App\User', 'id', 'to_id');
    }
}