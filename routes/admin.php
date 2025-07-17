<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\AirtimeToCashController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\MiscellaneousController;
use App\Http\Controllers\Admin\AdminGiftCardController;

/*
|--------------------------------------------------------------------------
| admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "admin" middleware group. Make something great!
|
*/


// Route::post('/', [AuthController::class, 'login'])->name('login');
Route::match(['get', 'post'], '/', [AuthController::class, 'login'])->name('.login');
Route::get('/storage/app/public/banners/{filename}', function ($filename) {
    $path = storage_path('app/public/banners/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('filename', '.*');

Route::middleware('auth:admin')->group(function () {
    Route::get('dashboard', [HomeController::class, 'dashboard'])->name('.dashboard');
    Route::get('smtp-settings', [HomeController::class, 'smtpSet'])->name('.smtp-settings');
    Route::post('setting-submit-smtp', [HomeController::class, 'smtpSetSubmit'])->name('.setting-submit-smtp');
    Route::get('thirdparty-settings', [HomeController::class, 'thirdPartySet'])->name('.thirdparty-settings');
    Route::get('broadcast-settings', [HomeController::class, 'broadCastSet'])->name('.broadcast-settings');
    Route::get('transaction-settings', [HomeController::class, 'transactionSet'])->name('.transaction-settings');
    Route::get('utility-settings', [HomeController::class, 'utilitySet'])->name('.utility-settings');
    Route::post('setting-submit-utility', [HomeController::class, 'utilitySetSubmit'])->name('.setting-submit-utility');
    Route::post('setting-submit-transfer', [HomeController::class, 'transferSetSubmit'])->name('.setting-submit-transfer');
    Route::post('setting-submit-deposit', [HomeController::class, 'depositSetSubmit'])->name('.setting-submit-deposit');

    Route::post('setting-submit-card-charges', [HomeController::class, 'cardSetSubmit'])->name('.setting-submit-card-charges');

    Route::get('referral-settings', [HomeController::class, 'referralSettings'])->name('.referral-settings');
    Route::post('setting-referral-bonus', [HomeController::class, 'referralBonus'])->name('.setting-referral-bonus');

    Route::post('setting-submit-exchange-rate', [HomeController::class, 'exchangeRateSubmit'])->name('.setting-submit-exchange-rate');

    Route::post('setting-submit-paystack', [HomeController::class, 'paystackSetSubmit'])->name('.setting-submit-paystack');
    Route::post('setting-submit-vtpass', [HomeController::class, 'vtpassSetSubmit'])->name('.setting-submit-vtpass');
    Route::post('setting-submit-youverify', [HomeController::class, 'youverifySetSubmit'])->name('.setting-submit-youverify');
    Route::post('setting-submit-firebase', [HomeController::class, 'firebaseSetSubmit'])->name('.setting-submit-firebase');
    Route::post('setting-submit-broadcast', [HomeController::class, 'broadcastSetSubmit'])->name('.setting-submit-broadcast');
    Route::post('setting-submit-notification', [HomeController::class, 'notificationSetSubmit'])->name('.setting-submit-notification');
    Route::get('banner-settings', [HomeController::class, 'bannerSet'])->name('.banner-settings');
    Route::post('setting-banner', [HomeController::class, 'uploadBanners'])->name('.setting-banner');
    Route::get('banner-toggle/{bid}', [HomeController::class, 'toogleBanner'])->name('.banner-toogle');
    Route::delete('banners/{banner}', [HomeController::class, 'deleteBanner'])->name('.banners.destroy');

    Route::get('login-history', [HomeController::class, 'loginHistory'])->name('.login-history');

    // KYC Routes

    Route::get('manage-kyc', [KycController::class, 'manageKyc'])->name('.manage-kyc');

    Route::prefix('kyc')->name('.kyc')->group(function () {
        Route::get('level-two', [KycController::class, 'levelTwoKycRequest'])->name('.level-two');
        Route::post('reject-level-two', [KycController::class, 'rejectleveltwo'])->name('.reject-level-two');
        Route::post('approve-level-two', [KycController::class, 'approveleveltwo'])->name('.approve-level-two');
        Route::get('level-three', [KycController::class, 'levelThreeKycRequest'])->name('.level-three');
        Route::post('reject-level-three', [KycController::class, 'rejectlevelthree'])->name('.reject-level-three');
        Route::post('approve-level-three', [KycController::class, 'approvelevelthree'])->name('.approve-level-three');
        Route::post('level-update', [KycController::class, 'updateLevelDetails'])->name('.level-update');

        Route::get('manual-level-two-kyc', [KycController::class, 'manualLevelTwo'])->name('.manual-level-two-kyc');
        Route::post('/manual-level-two-kyc', [KycController::class, 'submitManualLevelTwo'])->name('submitManualLevelTwo');
        Route::post('/manual-level-three-kyc', [KycController::class, 'submitManualLevelThree'])->name('submitManualLevelThree');

    });


    Route::prefix('customer')->name('.customer')->group(function () {
        Route::get('list', [HomeController::class, 'customerList'])->name('.list');
        Route::get('topup', [HomeController::class, 'customerTopup'])->name('.topup');
        Route::post('/admin/topup', [AdminController::class, 'topUpUser'])->name('admin_topup');
        Route::get('view/{uid}', [HomeController::class, 'viewDetails'])->name('.view');
        Route::get('toggle/{uid}', [HomeController::class, 'toogleUser'])->name('.toggle');
        Route::get('restrict/{uid}', [HomeController::class, 'toogleUserRestriction'])->name('.restrict');
        Route::get('toggle-login/{uid}', [HomeController::class, 'toogleUserLogin'])->name('.toggle_login');
        Route::get('delete/{uid}', [HomeController::class, 'deleteUser'])->name('.delete');
        Route::put('update/{id}', [HomeController::class, 'update'])->name('.update');





    });

    Route::get('airtime-cash', [AirtimeToCashController::class, 'index'])->name('airtime-cash');
    Route::get('airtime-cash-transactions', [AirtimeToCashController::class, 'transactions'])->name('airtime-cash-transactions');
    Route::post('airtime-to-cash', [AirtimeToCashController::class, 'store'])->name('airtime-to-cash.store');
    Route::patch('airtime-to-cash/{AirtimeToCash}', [AirtimeToCashController::class, 'update'])->name('airtime-to-cash.update');
    Route::patch('airtime-to-cash/{AirtimeToCash}/toggle-status', [AirtimeToCashController::class, 'toggleStatus'])->name('airtime-to-cash.toggle-status');

    Route::patch('/transaction-log/{transactionLog}/decline', [AirtimeToCashController::class, 'decline'])->name('transaction-log.decline');
    Route::patch('/airtime-to-cash-process/{transaction}', [AirtimeToCashController::class, 'approveTransaction'])->name('airtime-to-cash.approve');


    Route::patch('gift-cards/{giftCard}/update', [AdminGiftCardController::class, 'updateGiftCard'])->name('gift-cards.update');
    Route::get('gift-cards', [AdminGiftCardController::class, 'index'])->name('gift-cards');
    Route::post('add-gift-card', [AdminGiftCardController::class, 'store'])->name('add-gift-card');

    Route::get('/gift-card/{giftCard}/rates', [AdminGiftCardController::class, 'rates']);
    Route::post('/gift-card/{giftCard}/rates', [AdminGiftCardController::class, 'addRate']);


    Route::patch('gift-card/{GiftCard}/toggle-status', [AdminGiftCardController::class, 'toggleStatus']);

    Route::patch('/transaction-log/{transactionLog}/decline', [AdminGiftCardController::class, 'decline'])->name('transaction-log.decline');
    Route::patch('/airtime-to-cash-process/{transaction}', [AdminGiftCardController::class, 'approveTransaction'])->name('airtime-to-cash.approve');
    Route::get('/gift-card-transactions', [AdminGiftCardController::class, 'transactions'])->name('gift-card-transactions');

    Route::delete('/rates/delete/{id}', [AdminGiftCardController::class, 'destroy'])->name('rates.destroy');

    // Route::post('/gift-card/calculate', [GiftCardSaleController::class, 'calculatePayable']);






    Route::get('notification', [MiscellaneousController::class, 'notification'])->name('.notification');
    Route::post('send-notification', [MiscellaneousController::class, 'sendNotification'])->name('.send-notification');

    Route::get('change-password', [AuthController::class, 'showChangePasswordForm'])->name('.change-password');
    Route::post('change-password', [AuthController::class, 'changePassword'])->name('.submit-password');

        Route::post('/admin/topup', [HomeController::class, 'topUpUser'])->name('admin_topup');
    Route::post('logout', [AuthController::class,'logout'])->name('.logout');





});
