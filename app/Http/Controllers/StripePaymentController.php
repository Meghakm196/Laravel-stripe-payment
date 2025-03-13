<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class StripePaymentController extends Controller
{
    public function index()
    {
        return view('index');
    }

    public function checkout(Request $request, $method)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $amount = (int) ($request->query('amount', 5000) * 100);
        if ($amount <= 0) {
            return back()->with('error', 'Invalid Amount');
        }

        $paymentMethods = [
            'card' => ['mode' => 'payment', 'allowed' => ['card']],
            'afterpay' => ['mode' => 'payment', 'allowed' => ['afterpay_clearpay']],
            'alipay' => ['mode' => 'payment', 'allowed' => ['alipay']],
        ];

        if (!isset($paymentMethods[$method])) {
            return back()->with('error', 'Invalid Payment Method');
        }

        try {
            $transaction = Transaction::create([
                'transaction_id' => 'INIT-' . uniqid(),
                'amount' => $amount / 100,
                'payment_method' => $method,
                'status' => 'Failed',
                'transaction_time' => Carbon::now(),
            ]);

            $session = Session::create([
                'payment_method_types' => $paymentMethods[$method]['allowed'],
                'mode' => $paymentMethods[$method]['mode'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'aud',
                        'product_data' => ['name' => ucfirst($method) . " Payment"],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => route('payment.success', [
                    'method' => $method,
                    'transaction_id' => $transaction->transaction_id
                ]) . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('payment.cancel', [
                    'method' => $method,
                    'transaction_id' => $transaction->transaction_id
                ]),
            ]);

            Log::info("Checkout session created: " . $session->id);
            return redirect()->away($session->url);
        } catch (ApiErrorException $e) {
            Log::error('Stripe API Error: ' . $e->getMessage());
            return back()->with('error', 'Payment Failed: ' . $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session_id = $request->query('session_id');
        $transaction_id = $request->query('transaction_id');

        if (!$session_id || !$transaction_id) {
            Log::error("Missing session ID or transaction ID.");
            return "Invalid request!";
        }

        try {
            $session = Session::retrieve($session_id);
            Log::info("Stripe Payment Status: " . $session->payment_status);

            if ($session->payment_status === 'paid') {
                $this->updateTransaction($transaction_id, $session_id, 'success');
                return "<h1>Payment Successful!</h1>";
            } else {
                $this->updateTransaction($transaction_id, $session_id, 'failed');
                return "<h1>Payment Failed!</h1>";
            }
        } catch (\Exception $e) {
            Log::error('Error retrieving session: ' . $e->getMessage());
            return "Error processing payment!";
        }
    }

    public function cancel(Request $request)
    {
        $transaction_id = $request->query('transaction_id');

        if (!$transaction_id) {
            Log::error("No transaction ID for cancellation.");
            return "Invalid request!";
        }

        $this->updateTransaction($transaction_id, 'CANCELED-' . uniqid(), 'canceled');
        return "<h1>Payment Canceled!</h1>";
    }

    private function updateTransaction($old_transaction_id, $new_transaction_id, $status)
    {
        $transaction = Transaction::where('transaction_id', $old_transaction_id)->first();

        if ($transaction) {
            if ($transaction->status === 'success') {
                Log::info("Transaction already marked as success. No update needed: $old_transaction_id");
                return;
            }

            $transaction->update([
                'transaction_id' => $new_transaction_id,
                'status' => $status,
                'transaction_time' => Carbon::now(),
            ]);

            Log::info("Transaction updated: $old_transaction_id -> $new_transaction_id, Status: $status");
        } else {
            Log::warning("Transaction not found: $old_transaction_id");
        }
    }
}
