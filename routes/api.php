<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EbookController;
use App\Http\Controllers\Admin\AdminEbookController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

Route::prefix('v1')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [RegisterController::class, 'register']);
        Route::post('login', [LoginController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [LoginController::class, 'logout']);
            Route::get('me', [LoginController::class, 'me']);
        });
    });

    // Public ebook routes — anyone can browse
    Route::prefix('ebooks')->group(function () {
        Route::get('/', [EbookController::class, 'index']);
        Route::get('{slug}', [EbookController::class, 'show']);
    });

    // Admin ebook routes — must be logged in AND be an admin
    Route::prefix('admin')->middleware(['auth:sanctum', 'auth.admin'])->group(function () {
        Route::apiResource('ebooks', AdminEbookController::class);
        Route::patch('ebooks/{id}/toggle-publish', [AdminEbookController::class, 'togglePublish']);
        Route::apiResource('orders', AdminOrderController::class);
    });


    Route::middleware('auth:sanctum')->group(function () {
        Route::post('orders', [OrderController::class, 'store']);
        Route::get('orders/{id}', [OrderController::class, 'show']);



        Route::post('payments/initiate', [PaymentController::class, 'initiate']);

        Route::post('downloads/generate/{order_id}', [DownloadController::class, 'generate']);
        Route::get('downloads/{token}', [DownloadController::class, 'stream']);


        Route::get('dashboard/purchases', [DashboardController::class, 'purchases']);

        // Email verification routes
        Route::get('auth/verify-email/{id}/{hash}', function (Request $request) {
            $user = User::findOrFail($request->route('id'));

            if (!hash_equals(
                (string) $request->route('hash'),
                sha1($user->getEmailForVerification())
            )) {
                return response()->json(['message' => 'Invalid verification link.'], 403);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified.']);
            }

            $user->markEmailAsVerified();
            event(new Verified($user));

            // Redirect to frontend verified page
            return redirect(env('FRONTEND_URL') . '/auth/verified');
        })->middleware(['signed'])->name('verification.verify');

        Route::post('auth/resend-verification', function (Request $request) {
            if ($request->user()->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified.']);
            }

            $request->user()->sendEmailVerificationNotification();

            return response()->json(['message' => 'Verification email sent.']);
        });

        Route::get('/messages', [ContactMessageController::class, 'index']);
        Route::delete('/messages/{contactMessage}', [ContactMessageController::class, 'destroy']);
    });
    Route::post('/messages', [ContactMessageController::class, 'store']);

    Route::post('webhooks/paymob', [WebhookController::class, 'handlePaymob'])
        ->middleware('verify.paymob.webhook');
});
