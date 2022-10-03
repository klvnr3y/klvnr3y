<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccountType extends Model
{
    protected $guarded = [];

    public function account_plan()
    {
        return $this->hasMany('App\AccountPlan', 'account_type_id');
    }

    public function privacy()
    {
        return $this->hasOne('App\Privacy', 'account_type_id');
    }

    public function faq()
    {
        return $this->hasMany('App\Faq', 'account_type_id');
    }
}