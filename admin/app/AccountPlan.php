<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountPlan extends Model
{
    protected $guarded = [];

    public function account_type()
    {
        return $this->belongsTo('App\AccountType', 'account_type_id');
    }

    public function user_plan()
    {
        return $this->hasMany('App\UserPlan', 'account_plan_id');
    }
}