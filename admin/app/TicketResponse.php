<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TicketResponse extends Model
{
    protected $guarded = [];

    public function ticket()
    {
        return $this->belongsTo('App\Ticket', 'ticket_id');
    }

    public function user_submitted()
    {
        return $this->hasOne('App\User', 'id', 'submitted_by');
    }
}