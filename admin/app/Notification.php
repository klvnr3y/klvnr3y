<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $guarded = [];

    public function user_notification()
    {
        return $this->hasMany('App\UserNotification', 'notification_id');
    }
}