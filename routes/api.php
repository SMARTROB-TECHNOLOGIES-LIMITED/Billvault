<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaystackController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\Utility;
use App\Http\Controllers\Api\SafeHaven;
use App\Http\Controllers\Api\NombaController;
use App\Http\Controllers\Api\ReloadlyGiftCard;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\DojahVerificationController;
use App\Http\Controllers\MiscellaneousController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\FlightBookingController;
use App\Http\Controllers\Api\VtpassController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Auth::routes();

Route::middleware(['auth:sanctum','bancheck'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/vtpass/generate-request-id', [VtpassController::class, 'vtpass_generate_request_id']);


Route::prefix('auth')->group(function () {


    Route::post('/user-details', [AuthController::class, 'getUserDetails']);
    Route::prefix('register')->group(function () {
        Route::post('/verification', [SafeHaven::class, 'newCustomerRegistration']);
        Route::post('/email-confirmation', [AuthController::class, 'emailConfirm']);
        Route::post('/email-otp', [AuthController::class, 'emailConfirmOtp']);

        Route::post('/step-one', [SafeHaven::class, 'Registration']);
        Route::post('/create_nomba_account', [NombaController::class,'Registration']);


        Route::middleware(['auth:sanctum','bancheck'])->group(function() {
            // Profile & Kyc Related
            Route::post('/profile_level_1', [ProfileController::class, 'profileLevel1']);
            Route::post('/create_account', [ProfileController::class, 'createAccount']);
            Route::post('/set_transaction_pin', [ProfileController::class, 'setTransactionPin']);
            Route::post('/confirm_transaction_pin', [ProfileController::class, 'confirmTransactionPin']);



            Route::post('/complete_and_confirm_transaction_pin', [ProfileController::class, 'completeSetUpAndconfirmTransactionPin']); // SafeHaven
            Route::post('/bvn_verification', [ProfileController::class, 'bvnVerification']);
            Route::post('/profile_level_2', [ProfileController::class, 'profileLevel2']);
            Route::post('/create_tier_two', [ProfileController::class, 'createTier2']);
            Route::post('/create_tier_three', [ProfileController::class, 'createTier3']);


            // Virtual Card Routes

            Route::post('/create_virtual_card_account', [ProfileController::class, 'createVirtualCardAccount']);
            Route::post('/create_virtual_card', [ProfileController::class, 'createdVirtualCard']);
            Route::post('/fund_card', [ProfileController::class, 'fundCard']);
            Route::post('/withdraw_from_card', [ProfileController::class, 'withdrawFromCard']);
            Route::post('/freeze', [ProfileController::class, 'freezeCard']);
            Route::get('/card_transactions', [ProfileController::class, 'cardTransactions']);
            Route::get('/card_details', [ProfileController::class, 'cardDetails']);

        });
    });
    //Password Reset
    Route::middleware(['auth:sanctum','bancheck'])->post('/reset_password', [AuthController::class, 'resetPassword']);

    //Transaction Pin Reset

    Route::middleware(['auth:sanctum','bancheck'])->group(function () {
        Route::get('/test-user', function () {
            return 'User details';
        })->middleware('throttle:2,1');
        Route::post('/pin_login', [ProfileController::class, 'pinLogin']);
        Route::post('/forgot_transaction_pin', [ProfileController::class, 'forgotTransactionPin']);
        Route::post('/confirm-otp-transaction-pin', [ProfileController::class, 'confirmPinOtp']);
        Route::post('/forgot-pin-otp-confirmation', [ProfileController::class, 'forgotPinOTPConfirm']);
        Route::post('/logout', [AuthController::class, 'logout'])->name('.logout');
    });

    Route::post('/forgot_password', [AuthController::class, 'forgotPassword']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/login', function () {
        return response()->json(['status'=>'false','data' => ['message' => "Unauthorized",]],401);
    })->name('api.login');
});

Route::get('/all_transactions_test/{page?}', [MiscellaneousController::class, 'allTransactionsTest']);
Route::get('/banners/active', [MiscellaneousController::class, 'getActiveBanners'])->name('banners.active');
Route::get('/broadcast-message', [MiscellaneousController::class, 'broadcastMessage']);
Route::get('/transfer-message', [MiscellaneousController::class, 'transferMessage']);

Route::middleware(['auth:sanctum','bancheck'])->group(function() {
    //Profile Related
    Route::post('/profile_image', [ProfileController::class, 'profileImage']);
    Route::post('/save-capture', [ProfileController::class, 'saveCapture']);
    Route::get('/is_restricted', [AuthController::class, 'isRestricted']);
    Route::get('/is_ban', [AuthController::class, 'isBan']);
    Route::get('/kyc_levels', [ProfileController::class, 'getActiveLevels']);
    Route::get('/level_two_status', [ProfileController::class, 'level_two_status']);
    Route::get('/level_three_status', [ProfileController::class, 'level_three_status']);
    Route::get('/card_charges', [ProfileController::class, 'cardCharges']);
    Route::get('/transaction-summary', [WalletController::class, 'getUserTransactionSummary']);

    // Refeffal
    Route::get('/referral', [ProfileController::class, 'getUsersAndReferralCount']);

    // VTU Related
    Route::get('/get_airtime_list', [Utility::class, 'getAirtimeList']);
    Route::middleware(['kyc'])->post('/purchase_airtime', [Utility::class, 'purchaseAirtime']);
    Route::get('/data_variation_list/{serviceId}', [Utility::class, 'dataVariationList']);
    Route::middleware(['kyc'])->post('/purchase_data', [Utility::class, 'purchaseData']);

    Route::get('/get_tv_subscription_list', [Utility::class, 'getTvSubscriptionList']);
    Route::get('/tv_provider_variation_list/{serviceId}', [Utility::class, 'tvProviderVariationList']);
    Route::middleware(['kyc'])->post('/purchase_tv_subscription', [Utility::class,'purchaseTVSubscription']);

    Route::get('/airtime-to-cash-networks', [Utility::class, 'getNetworkList']);
    Route::get('/airtime-to-cash-settings/{id}', [Utility::class, 'getNetworkSettings']);
    Route::post('/airtime-to-cash', [Utility::class, 'processAirtimeToCash']);

    // Safe Haven APIS
    Route::get('/refresh_token', [SafeHaven::class,'refreshToken']);
    Route::post('/verify_bvn', [SafeHaven::class,'verifyBVN']);
    Route::post('/auth/create_safehaven_account', [SafeHaven::class, 'createSafeHavenAccount']);
    Route::post('/create_safehaven_account', [SafeHaven::class, 'createSafeHavenAccountAndComplete']);
    Route::get('safe_haven_bank_list', [SafeHaven::class,'getBankList']);
    Route::post('/get_customer_name', [SafeHaven::class, 'getAccountDetails']);
    Route::post('/safehaven_initiate_transfer/{ref?}', [SafeHaven::class, 'safeHavenTransfer']);

    // Nomba APIS
    Route::get('/refresh_nomba_token', [NombaController::class,'refreshToken']);
    Route::post('/create_only_nomba_account', [NombaController::class,'createNombaAccount']);

    Route::get('/nomba_bank_list', [NombaController::class,'getBankList']);
    Route::post('/nomba_account_details', [NombaController::class,'getAccountDetails']);
    Route::post('/nomba_transfer', [NombaController::class, 'nombaTransfer']);

    // Flight Booking APIS
    Route::get('/search_flight', [FlightBookingController::class,'searchFlights']);
    Route::post('/flight_final_price', [FlightBookingController::class,'getFlightFinalPrice']);
    Route::post('/book_flight', [FlightBookingController::class,'bookFlight']);
    Route::get('/airport-search', [FlightBookingController::class, 'searchAirportAndCity']);

    // Reloadly
    Route::get('get_card_countries', [ReloadlyGiftCard::class,'getCardCountries']);
    Route::get('get_card_country/{iso}', [ReloadlyGiftCard::class,'getCardCountry']);
    Route::get('get_product_categories', [ReloadlyGiftCard::class,'getProductCategories']);
    Route::get('get_card_products', [ReloadlyGiftCard::class,'getCardProducts']);
    Route::get('get_country_products/{iso}', [ReloadlyGiftCard::class,'getCountryProducts']);
    Route::get('get_product_details/{pid}', [ReloadlyGiftCard::class,'getProductDetails']);
    Route::get('get_product_redeem_instructions/{pid}', [ReloadlyGiftCard::class,'getProductRedeemInstructions']);
    Route::get('get_fx_rate/{currency}/{amount}', [ReloadlyGiftCard::class,'getFXRate']);
    Route::post('/order_gift_card', [ReloadlyGiftCard::class, 'orderGiftCard']);
    Route::get('get_redeem_code/{tid}', [ReloadlyGiftCard::class,'getRedeemCode']);

    // Sell GiftCard

    Route::get('get_products', [ReloadlyGiftCard::class,'getProducts']);
    Route::get('get_products_countries/{pid}', [ReloadlyGiftCard::class,'getProductsCountries']);
    Route::post('calculate_rate', [ReloadlyGiftCard::class,'calculateRate']);
    Route::post('sell_gift_card', [ReloadlyGiftCard::class,'sellGiftCard']);
    Route::get('gift_card_transaction_history', [ReloadlyGiftCard::class,'transactionLog']);


    Route::get('/get_electricity_list', [Utility::class, 'getElectricityList']);
    Route::middleware(['kyc'])->post('/verify_metre_number', [Utility::class,'verifyMetreNumber']);
    Route::middleware(['kyc'])->post('/verify_cable_tv_number', [Utility::class,'verifyCableTVNumber']);

    Route::middleware(['kyc'])->post('/purchase_electricity', [Utility::class,'purchaseElectricity']);
    Route::get('/recent-transactions/{type}', [Utility::class,'getRecentTransactions']);

    //Transfer
    Route::get('/transaction_detail/{tid}', [MiscellaneousController::class,'transactionDetails']);
    Route::get('/download_receipt/{format}/{tid}', [MiscellaneousController::class,'downloadReceipt']);
    Route::middleware(['kyc'])->get('/charges/{amt}', [WalletController::class, 'tfCharges']);
    Route::middleware(['kyc'])->post('/paypoint_user_details', [WalletController::class,'paypointTfUDet']);
    Route::middleware(['kyc'])->post('/paypoint_2_paypoint', [WalletController::class,'p2pTf']);
    // Route::middleware(['kyc'])->post('/resolve_account', [PaystackController::class,'resolveAccount']);
    Route::middleware(['kyc'])->post('/create_recipient', [PaystackController::class,'createRecipient']);
    // Route::middleware(['kyc'])->post('/initiate_transfer/{ref?}', [PaystackController::class,'initiateTransfer']);

    //Strowallet TransfergetActiveBanners
    Route::get('/bank_list', [PaystackController::class, 'getBankList']);
    Route::middleware(['kyc'])->post('/resolve_account', [PaystackController::class,'getAccountDetails']);
    Route::middleware(['kyc'])->post('/initiate_transfer/{ref?}', [PaystackController::class,'initiateTransfer']);

    // Educational API
    Route::get('/waec_services', [Utility::class, 'waecServices']);
    // Route::get('/waec_variations/{serviceId}', [Utility::class, 'weacVariation']);
    Route::post('/purchase_waec', [Utility::class,'purchaseWaec']);

    Route::get('/education_variations/{serviceId}', [Utility::class, 'educationVariation']);
    Route::post('/verify_jamb_profile', [Utility::class,'verifyProfile']);
    Route::post('/purchase_jamb', [Utility::class,'purchaseJamb']);

    // Recent Transfers
    Route::get('/recent_transfers_users/{page}', [MiscellaneousController::class, 'recentTransfer']);
    Route::get('/all_transactions/{page?}', [MiscellaneousController::class, 'allTransactions']);
    Route::post('/save_device_token', [MiscellaneousController::class, 'saveToken']);
    Route::post('/bank_statement', [MiscellaneousController::class, 'bankStatement']);
    Route::get('/user/notifications/{page?}', [MiscellaneousController::class, 'notificationsList']);
    Route::get('/beneficiaries/{type}', [MiscellaneousController::class, 'getBeneficiariesByType']);
    Route::post('/delete_beneficiaries/{id}', [MiscellaneousController::class, 'destroy']);


    // New utility routes
    Route::get('/get_data_list', [Utility::class, 'getDataList']);
    Route::middleware(['kyc'])->post('/verify_smile_email', [Utility::class,'verifySmileEmail']);
    Route::middleware(['kyc'])->get('/get-international-airtime-countries', [Utility::class,'internationalCountries']);
    Route::get('/international-product-type/{countryCode}', [Utility::class, 'productType']);
    Route::get('/international-airtime-operator/{countryCode}/{productType}', [Utility::class, 'internationalAirtimeOperator']);
    Route::get('/international-airtime-variation-code/{operatorID}/{productID}', [Utility::class, 'internationalAirtimeVariationCode']);
    Route::middleware(['kyc'])->post('/purchase_international_airtime', [Utility::class, 'purchaseInternationalAirtime']);
    // Ends Here

    // Betting Endpoints

    Route::get('/betting-platforms', [Utility::class,'bettingPlatform']);
    Route::post('/verify-betting-id', [Utility::class,'verifyBettingID']);
    Route::post('/fund-betting-account', [Utility::class,'fundBettingAccount']);

});


// WEB HOOK / CALLBACK Related
Route::post('/confirmvtustatus', [Utility::class, 'confirmVTUStatus']);
Route::get('/withdrawal-status', [ProfileController::class, 'withdrawStatus']);
Route::post('/nombaWebhook', [WalletController::class,'nombaDepositWebhook']);



Route::prefix('kyc')->middleware('auth:sanctum')->group(function () {
    Route::post('/verify-bvn', [DojahVerificationController::class, 'verifyBvn']);
    Route::post('/verify-nin', [DojahVerificationController::class, 'verifyNin']);
    Route::post('/verify-dl', [DojahVerificationController::class, 'verifyDriverLicense']);
});
