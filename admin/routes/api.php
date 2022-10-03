<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Ixudra\Curl\Facades\Curl;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'api'], function () {
});

Route::group(['prefix' => 'api/v1'], function () {
    Route::group(['middleware' => 'auth:api'], function () {
        // new api's
        /** user */
        Route::get('message_find_user', 'API\v1\UserController@message_find_user');
        Route::post('update_profile', 'API\v1\UserController@update_profile');
        Route::post('user_reactive', 'API\v1\UserController@user_reactive');
        Route::post('user_deactive', 'API\v1\UserController@user_deactive');
        Route::post('users_activated', 'API\v1\UserController@users_activated');
        Route::post('users_deactivated', 'API\v1\UserController@users_deactivated');
        Route::post('profile_change_password', 'API\v1\UserController@profile_change_password');
        Route::get('get_by_id', 'API\v1\UserController@get_by_id');
        Route::get('user_assigned_tickets', 'API\v1\UserController@user_assigned_tickets');
        Route::post('invite_people', 'API\v1\UserController@invite_people');
        Route::apiResource('users', 'API\v1\UserController');

        Route::post('verifypassword', 'API\v1\AuthController@verifypassword');
        Route::post('generate2faSecret', 'API\v1\AuthController@generate2faSecret');
        Route::post('enable2fa', 'API\v1\AuthController@enable2fa');
        Route::post('disable2fa', 'API\v1\AuthController@disable2fa');

        Route::apiResource('user_plan', 'API\v1\UserPlanController');

        Route::apiResource('user_payment', 'API\v1\UserPaymentController');

        /** end user */

        Route::apiResource('account_type', 'API\v1\AccountTypeController');
        Route::apiResource('account_plan', 'API\v1\AccountPlanController');
        Route::post('plan_sort', 'API\v1\AccountPlanController@plan_sort');

        Route::apiResource('faq', 'API\v1\FaqController');
        Route::apiResource('email_template', 'API\v1\EmailTemplateController');

        // Ticketing
        Route::apiResource('ticket', 'API\v1\TicketController');
        Route::apiResource('tickets_response', 'API\v1\TicketResponseController');

        // message
        Route::apiResource('message', 'API\v1\MessageController');
        Route::post('message/block', 'API\v1\MessageController@block');
        Route::apiResource('message_convo', 'API\v1\MessageConvoController');
        Route::apiResource('message_archived', 'API\v1\MessageArchivedController');
        Route::apiResource('message_blocked', 'API\v1\MessageBlockedController');

        Route::apiResource('notification', 'API\v1\NotificationController');
        Route::get('member_options', 'API\v1\UserController@member_options');
        Route::post('generate/token/viewas', 'API\v1\AuthController@viewas');

        //tooltip
        Route::post('tooltips/selector', 'API\v1\TooltipController@selector');
        Route::apiResource('tooltips', 'API\v1\TooltipController');
    });
    Route::apiResource('advert_type', 'API\v1\AdvertTypeController');

    // public
    Route::post('register', 'API\v1\AuthController@register');
    Route::post('check_auth', 'API\v1\AuthController@check_auth');
    Route::post('set_password', 'API\v1\AuthController@set_password');

    Route::post('login', 'API\v1\AuthController@login');
    Route::post('forgot_password', 'API\v1\AuthController@forgot_password');
    Route::post('change_password', 'API\v1\AuthController@change_password');
    Route::post('verify2fa', 'API\v1\AuthController@verify2fa');

    Route::post('logout', 'API\v1\AuthController@logout');
    Route::get('acc_plan', 'API\v1\AuthController@AccountPlan');
    Route::get('acc_type', 'API\v1\AuthController@AccountType');
});

function pp($data)
{
    echo '<pre>';
    print_r($data);
}

Route::get('/test', function () {
    $file_path = 'uploads/e_signature/ESignature-GJWkfWZPVQesignature.png';
    $path = storage_path('app/public/' . $file_path);
    $file_content = file_get_contents($path);
    echo ($file_content);
});