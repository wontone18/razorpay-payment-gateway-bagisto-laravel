<?php

use Illuminate\Support\Facades\Route;
use Wontonee\Razorpay\Http\Controllers\RazorpayController;

Route::group(['middleware' => ['web']], function () {
    Route::get('razorpay-redirect', [RazorpayController::class, 'redirect'])->name('razorpay.process');
    Route::post('razorpaycheck', [RazorpayController::class, 'verify'])->name('razorpay.callback');
});
