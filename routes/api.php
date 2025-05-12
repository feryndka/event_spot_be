<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Promotor\EventController as PromotorEventController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventAttendeeController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\EventRatingController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\CategorySubscriptionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ==================== Public Routes ====================

// Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
});

// Public Event Routes
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{event}', [EventController::class, 'show']);
    Route::get('/{event}/comments', [CommentController::class, 'index']);
    Route::get('/{event}/ratings', [EventRatingController::class, 'index']);
});

// Public Category Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{category}', [CategoryController::class, 'show']);
    Route::get('/{category}/events', [CategoryController::class, 'events']);
});

// ==================== Protected Routes ====================
Route::middleware('auth:sanctum')->group(function () {
    // User Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'profile']);
        Route::put('/', [UserController::class, 'updateProfile']);
        Route::put('/password', [UserController::class, 'updatePassword']);
        Route::post('/avatar', [UserController::class, 'updateAvatar']);
    });

    // User Preferences Routes
    Route::prefix('preferences')->group(function () {
        Route::get('/', [UserPreferenceController::class, 'show']);
        Route::put('/', [UserPreferenceController::class, 'update']);
    });

    // Event Interaction Routes
    Route::prefix('events')->group(function () {
        Route::post('/{event}/register', [EventController::class, 'register']);
        Route::post('/{event}/attend', [EventAttendeeController::class, 'store']);
        Route::post('/{event}/bookmark', [BookmarkController::class, 'store']);
        Route::delete('/{event}/bookmark', [BookmarkController::class, 'destroy']);
        Route::post('/{event}/rating', [EventRatingController::class, 'store']);
        Route::put('/{event}/rating/{rating}', [EventRatingController::class, 'update']);
        Route::delete('/{event}/rating/{rating}', [EventRatingController::class, 'destroy']);
    });

    // Comment Routes
    Route::prefix('comments')->group(function () {
        Route::post('/events/{event}', [CommentController::class, 'store']);
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
    });

    // Subscription Routes
    Route::prefix('subscriptions')->group(function () {
        Route::post('/promotors/{promotor}', [FollowerController::class, 'store']);
        Route::delete('/promotors/{promotor}', [FollowerController::class, 'destroy']);
        Route::post('/categories/{category}', [CategorySubscriptionController::class, 'store']);
        Route::delete('/categories/{category}', [CategorySubscriptionController::class, 'destroy']);
    });

    // Payment Routes
    Route::prefix('payments')->group(function () {
        Route::post('/events/{event}', [PaymentController::class, 'store']);
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/{payment}', [PaymentController::class, 'show']);
        Route::get('/events/{event}/history', [PaymentController::class, 'eventPaymentHistory']);
    });

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{notification}', [NotificationController::class, 'destroy']);
    });

    // ==================== Promotor Routes ====================
    Route::middleware('promotor')->prefix('promotor')->group(function () {
        // Event Management
        Route::apiResource('events', PromotorEventController::class);
        Route::post('/events/{event}/images', [PromotorEventController::class, 'uploadImages']);
        Route::delete('/events/{event}/images/{image}', [PromotorEventController::class, 'deleteImage']);
        Route::put('/events/{event}/publish', [PromotorEventController::class, 'publish']);
        Route::put('/events/{event}/unpublish', [PromotorEventController::class, 'unpublish']);
        Route::post('/events/{event}/generate-description', [PromotorEventController::class, 'generateDescription']); // AI Generate Description

        // Attendee Management
        Route::prefix('events/{event}/attendees')->group(function () {
            Route::get('/', [PromotorEventController::class, 'getAttendees']);
            Route::put('/{attendee}/check-in', [PromotorEventController::class, 'checkInAttendee']);
            Route::get('/export', [PromotorEventController::class, 'exportAttendees']);
        });

        // Comment Management
        Route::prefix('events/{event}/comments')->group(function () {
            Route::get('/', [PromotorEventController::class, 'getComments']);
            Route::post('/{comment}/reply', [PromotorEventController::class, 'replyComment']);
            Route::delete('/{comment}', [PromotorEventController::class, 'deleteComment']);
        });

        // Statistics & Analytics
        Route::prefix('statistics')->group(function () {
            Route::get('/overview', [PromotorEventController::class, 'getOverviewStatistics']);
            Route::get('/events/{event}', [PromotorEventController::class, 'getStatistics']);
            Route::get('/revenue', [PromotorEventController::class, 'getRevenue']);
            Route::get('/events/{event}/revenue', [PromotorEventController::class, 'getEventRevenue']);
        });
    });

    // ==================== Admin Routes ====================
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Dashboard & Statistics
        Route::prefix('statistics')->group(function () {
            Route::get('/overview', [AdminController::class, 'getPlatformStatistics']);
            Route::get('/events', [AdminController::class, 'getEventStatistics']);
            Route::get('/users', [AdminController::class, 'getUserStatistics']);
            Route::get('/transactions', [AdminController::class, 'getTransactionStatistics']);
        });

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'getAllUsers']);
            Route::get('/{user}', [AdminController::class, 'getUserDetails']);
            Route::put('/{user}/verify', [AdminController::class, 'verifyUser']);
            Route::put('/{user}/suspend', [AdminController::class, 'suspendUser']);
            Route::delete('/{user}', [AdminController::class, 'deleteUser']);
            Route::post('/{user}/reset-password', [AdminController::class, 'resetUserPassword']);
        });

        // Promotor Verification
        Route::prefix('promotor-verifications')->group(function () {
            Route::get('/', [AdminController::class, 'getPromotorVerifications']);
            Route::put('/{promotor}/approve', [AdminController::class, 'approvePromotor']);
            Route::put('/{promotor}/reject', [AdminController::class, 'rejectPromotor']);
        });

        // Event Management
        Route::prefix('events')->group(function () {
            Route::get('/', [AdminController::class, 'getAllEvents']);
            Route::put('/{event}/approve', [AdminController::class, 'approveEvent']);
            Route::put('/{event}/reject', [AdminController::class, 'rejectEvent']);
            Route::delete('/{event}', [AdminController::class, 'deleteEvent']);
        });

        // Category Management
        Route::apiResource('categories', CategoryController::class);

        // Transaction Management
        Route::prefix('transactions')->group(function () {
            Route::get('/', [AdminController::class, 'getAllTransactions']);
            Route::get('/{transaction}', [AdminController::class, 'getTransactionDetails']);
            Route::get('/export', [AdminController::class, 'exportTransactions']);
        });

        // Comment Moderation
        Route::prefix('comments')->group(function () {
            Route::get('/reported', [AdminController::class, 'reportedComments']);
            Route::put('/{comment}/approve', [AdminController::class, 'approveComment']);
            Route::delete('/{comment}', [AdminController::class, 'deleteComment']);
        });
    });
});
