<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


// Route::group([

//     'middleware' => 'api',
//     'prefix' => 'auth'

// ], function ($router) {

//     Route::get('login', 'API/UserController@login');
//     Route::post('logout', 'AuthController@logout');
//     Route::post('refresh', 'AuthController@refresh');
//     Route::post('me', 'AuthController@me');

// });
Route::apiResources([
    'sign-up' => 'API\SignUpController',
    'sign-in' => 'API\AuthController',
    'temp-upload' => 'API\TempUploadController',
    'auth-user' => 'API\UserController',
    'event' => 'API\EventController',
    'bookings' => 'API\BookingsController'
]);
Route::post('/check-in-kid', [
    'uses' => 'API\BookingsController@CheckInKid'
]);
Route::post('/check-out-kid', [
    'uses' => 'API\BookingsController@CheckOutKid'
]);
Route::post('/accept-this-attendee', [
    'uses' => 'API\BookingsController@AcceptThisAttendee'
]);
Route::get('/parent-fetch-registered-event', [
    'uses' => 'API\BookingsController@ParentFetchRegisteredEvents'
]);
Route::get('/parent-fetch-attendees', [
    'uses' => 'API\BookingsController@ParentFetchAttendees'
]);
Route::get('/village-fetch-attendees', [
    'uses' => 'API\BookingsController@VillageFetchAttendees'
]);
Route::get('/fetch-this-kid/{id}', [
    'uses' => 'API\UserController@FetchThisKid'
]);
Route::post('/fetch-this-parent', [
    'uses' => 'API\BookingsController@FetchThisParent'
]);
Route::post('/fetch-this-kid-and-parent', [
    'uses' => 'API\BookingsController@FetchThisKidAndParent'
]);
Route::get('/fetch-this-registered-event/{id}', [
    'uses' => 'API\EventController@FetchThisRegisteredEvent'
]);
Route::post('/place-booking', [
    'uses' => 'API\BookingsController@PlaceBooking'
]);
Route::post('/get-nearby-events' , [
    'uses' => 'API\EventController@getNearByEvents',
]);
Route::post('/add-to-gallery/{event}', [
    'uses' => 'API\EventController@addToGallery'
]);
Route::delete('/del-this-image/{id}' , [
    'uses' => 'API\EventController@delThisImage',
]);
Route::post('/fetch-this-event/{id}', [
    'uses' => 'API\EventController@fetchThisEvent'
]);
Route::post('/fetch-kids', [
    'uses' => 'API\UserController@fetchKids'
]);


// Route::post('/add-new-event', [
//     'uses' => 
// ])
Route::post('/change-password', [
    'uses' => 'API\AuthController@changePass',
]);
Route::delete('/logout', [
    'uses' => 'API\AuthController@destroy',
]);
Route::post('/register-village' , [
    'uses' => 'API\SignUpController@registerVillage',
]);
Route::post('/parent-details' , [
    'uses' => 'API\SignUpController@parentDetails',
]);
Route::post('/signup-village' , [
    'uses' => 'API\SignUpController@createVillage',
]);
Route::delete('/del-temp-upload/{id}' , [
    'uses' => 'API\TempUploadController@delStoreTemp',
]);
Route::post('/set-temp-update' , [
    'uses' => 'API\TempUploadController@setTempUpdate',
]);
Route::post('/kid-details' , [
    'uses' => 'API\SignUpController@kidDetails',
]);