<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
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

// Password reset link request routes...
Route::get('password/email', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.email');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);

// Password reset routes...
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.request');
Route::post('password/reset', [ResetPasswordController::class, 'postReset'])->name('password.reset');

Route::get('/test-notification-training-mail', function () {
    $training = App\Models\Training::find(1);
    $user = App\Models\User::find(1);

    return new App\Mail\Training\NotificationTrainingMail($training, $user);
});

Route::get('/test-confirmation-notification-training-mail', function () {
    $training = App\Models\Training::find(1);
    $user = App\Models\User::find(3);

    return new App\Mail\Training\ConfirmationNotificationTrainingMail($training, $user);
});

Route::get('/test-cancellation-notification-training-mail', function () {
    $training = App\Models\Training::find(1);
    $user = App\Models\User::find(3);

    return new App\Mail\Training\CancellationNotificationTrainingMail($training, $user);
});
