<?php

use App\Http\Controllers\StripePaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeWebhookController;

Route::get('/', [StripePaymentController::class, 'index']);
Route::get('/checkout/{method}', [StripePaymentController::class, 'checkout']);
Route::get('/success', [StripePaymentController::class, 'success'])->name('payment.success');
Route::get('/cancel', [StripePaymentController::class, 'cancel'])->name('payment.cancel');

// Add Webhook Route for Stripe
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);
