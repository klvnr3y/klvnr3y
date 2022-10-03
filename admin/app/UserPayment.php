<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserPayment extends Model
{
    protected $guarded = [];

    public function user_plan()
    {
        return $this->belongsTo('App\UserPlan', 'user_plan_id');
    }

    public function scopeUsers($query)
    {
        return $query->leftJoin('users', 'user_payments.user_id', '=', 'users.id');
    }
}