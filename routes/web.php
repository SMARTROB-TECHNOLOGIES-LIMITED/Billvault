<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MiscellaneousController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
use Illuminate\Support\Facades\Artisan;

Route::get('/clear-config', function () {
    // Optionally, you can check the exit code to see if the command was successful
    try {
        Artisan::call('optimize:clear');
        Artisan::call('config:clear');
        Artisan::call('config:cache');
        return 'Configuration cache cleared successfully.';
    } catch(\Exception $e) {
        return 'Error clearing configuration cache.';
    }
});

Route::get('/', function () {
    return redirect('https://billvault.app');
});

Route::get('/login', function () {
    return redirect('https://billvault.app');
});

Route::get('/home', function () {
    return redirect('https://billvault.app');
});

Route::view('/response', 'auth.passwords.password-updated-response')
    ->name('password_update_response');
Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware(['auth:sanctum','bancheck'])->group(function() {
    Route::post('/save_device_token', [MiscellaneousController::class, 'saveToken'])->name("save-token");
    Route::post('/send_notification', [MiscellaneousController::class, 'sendNotification'])->name("send.notification");
});
