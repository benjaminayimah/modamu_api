<?php

use App\Http\Controllers\MeController;
use App\Http\Controllers\pdfController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});
// Route::get('/reset-password', function () {
//     return view('emails.passwordReset');
// });
// Route::get('/welcome-email', function () {
//     return view('emails.welcomeEmail');
// });
// Route::get('/payment-recieved', function () {
//     return view('emails.paymentRecieved');
// });
// Route::get('/event-booked', function () {
//     return view('emails.eventBooked');
// });
// Route::get('/kid-accepted', function () {
//     return view('emails.kidAccepted');
// });
// Route::get('/dropoff-pickup', function () {
//     return view('emails.dropOffPickUp');
// });
Route::get('/bulk-message', function () {
    return view('emails.bulkMessage');
});
// Route::get('/view-pdf', [MeController::class, 'me']);