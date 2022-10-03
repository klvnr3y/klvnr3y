<?php

use App\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Ticket;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('admin');
// });

// // Route::get('/', 'DashboardController@index');
// Route::get('/', function() {
//     return view('admin.app');
// });

Route::get('/purechat-email', function () {
    return view('admin.emails.panticket', ['link' => 'test', 'full_name' => 'Promise Network', 'email' => 'support@promise.network', 'button_link' => 'test']);
});


// Route::get( '/{any}', function( ){
//         return view('admin.app');
// });

Route::get('{all?}/{all1?}/{all2?}/{all3?}/{all4?}/{all5?}/{all6?}/{all7?}/{all8?}/{all9?}/{all10?}/{all11?}/{all12?}/{all13?}/{all14?}/{all15?}', function (Request $request) {
    if ($request->all == 'doc') {
        return view('apidoc.index');
    } else {
        return view('admin.app');
    }
});


Route::get('/share-media-content/{id}', function ($id) {
    $data = new Event;
    $data = $data->select([
        'events.*',
        DB::raw("(SELECT DATE_FORMAT(date , '%m/%d/%Y') FROM `event_schedules` WHERE event_schedules.event_id = events.id AND DATE_FORMAT(date , '%m/%d/%Y') >= DATE_FORMAT(NOW(), '%m/%d/%Y') LIMIT 1) as `date_sort`"),
        DB::raw("(SELECT time_start FROM `event_schedules` WHERE event_schedules.event_id = events.id AND DATE_FORMAT(date , '%m/%d/%Y') >= DATE_FORMAT(NOW(), '%m/%d/%Y') LIMIT 1) as `date_time_start`"),
        DB::raw("(SELECT time_end FROM `event_schedules` WHERE event_schedules.event_id = events.id AND DATE_FORMAT(date , '%m/%d/%Y') >= DATE_FORMAT(NOW(), '%m/%d/%Y') LIMIT 1) as `date_time_end`"),
    ]);
    $data = $data->with(['schedule', 'category', 'locations', 'tag']);
    $data = $data->where('status', '<>', 1);
    // $data = $data->where(DB::raw("(SELECT DATE_FORMAT(date , '%m/%d/%Y') FROM `event_schedules` WHERE event_schedules.event_id = events.id AND DATE_FORMAT(date , '%m/%d/%Y') >= DATE_FORMAT(NOW(), '%m/%d/%Y') LIMIT 1)"), '>=', $date_now);
    $data = $data->orderBy(DB::raw("(SELECT DATE_FORMAT(date , '%m/%d/%Y') FROM `event_schedules` WHERE event_schedules.event_id = events.id AND DATE_FORMAT(date , '%m/%d/%Y') >= DATE_FORMAT(NOW(), '%m/%d/%Y') LIMIT 1)"), 'asc');


    $data = $data->get();

    // dd($cities);

    return view('admin.share-media-content', ['data' => $data]);
});