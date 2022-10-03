<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $guarded = [];

    public function requeter_user()
    {
        return $this->hasOne('App\User', 'id', 'requester');
    }

    public function assigned_user()
    {
        return $this->hasOne('App\User', 'id', 'asssigned');
    }

    public function ticket_response()
    {
        return $this->hasMany('App\TicketResponse', 'ticket_id');
    }
}