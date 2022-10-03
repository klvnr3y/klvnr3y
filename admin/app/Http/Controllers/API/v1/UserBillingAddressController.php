<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;

use App\UserBillingAddress;
use Illuminate\Http\Request;

class UserBillingAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\UserBillingAddress  $userBillingAddress
     * @return \Illuminate\Http\Response
     */
    public function show(UserBillingAddress $userBillingAddress)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\UserBillingAddress  $userBillingAddress
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserBillingAddress $userBillingAddress)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\UserBillingAddress  $userBillingAddress
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserBillingAddress $userBillingAddress)
    {
        //
    }
}