<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\UserPreferenceController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\CategorySubscriptionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\EventManagementController;
use App\Http\Controllers\User\UserDashboardController;
use App\Http\Controllers\User\EventAttendanceController;
use App\Http\Controllers\User\EventSearchController;
use App\Http\Controllers\Promotor\EventRegistrationManagementController;
use App\Http\Controllers\Promotor\EventAnalyticsController;
use App\Http\Controllers\Promotor\EventCommentManagementController;
use App\Http\Controllers\Promotor\PromotorProfileController;
use App\Http\Controllers\User\BookmarkController;
use App\Http\Controllers\Admin\EventManagementController as AdminEventController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminEventTagController;
use App\Http\Controllers\Admin\PromotorVerificationController as AdminVerificationController;
use App\Http\Controllers\Admin\AdminUserController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ==================== Public Routes ====================

// Authentication Routes
Route::prefix('auth')->group(function () {
    // Public auth routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // Protected auth routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Public Event Routes
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']); // List all events with filters
    Route::get('/{event}', [EventController::class, 'show']); // Get event details
    Route::get('/{event}/comments', [CommentController::class, 'index']); // Get all comments (including replies)
});

// Public Category Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']); // List all categories
    Route::get('/{category}', [CategoryController::class, 'show']); // Get category detail with events
});

// ======================= User Routes ======================
Route::middleware(['auth:sanctum', \App\Http\Middleware\UserMiddleware::class])->prefix('user')->group(function () {
    // User Dashboard
    // Route::prefix('dashboard')->group(function () {
    //     Route::get('/overview', [UserDashboardController::class, 'overview']);
    //     Route::get('/events', [UserDashboardController::class, 'events']);
    //     Route::get('/notifications', [UserDashboardController::class, 'notifications']);
    // });

    // Event Attendance
    Route::prefix('events')->group(function () {
        Route::get('/upcoming', [EventAttendanceController::class, 'upcomingEvents']);
        Route::get('/history', [EventAttendanceController::class, 'eventHistory']);
        Route::get('/{event}/attendance', [EventAttendanceController::class, 'attendanceDetails']);
        Route::post('/{event}/register', [EventAttendanceController::class, 'register']);
        Route::delete('/{event}/register', [EventAttendanceController::class, 'cancelRegistration']);
    });

    // Enhanced Event Search By User
    Route::prefix('search/events')->group(function () {
        Route::get('/', [EventController::class, 'search']);
        Route::get('/location/{location}', [EventController::class, 'byLocation']);
        Route::get('/date/{date}', [EventController::class, 'byDate']);
        Route::get('/nearby', [EventController::class, 'nearby']);
    });

    // User Preferences Routes
    // Route::prefix('preferences')->group(function () {
    //     Route::get('/', [UserPreferenceController::class, 'show']); // Get user preferences
    //     Route::put('/', [UserPreferenceController::class, 'update']); // Update user preferences
    // });

    // Bookmark Routes
    Route::prefix('bookmarks')->group(function () {
        Route::get('/', [BookmarkController::class, 'index']);
        Route::post('/events/{event}', [BookmarkController::class, 'store']);
        Route::delete('/events/{event}', [BookmarkController::class, 'destroy']);
        Route::get('/events/{event}/check', [BookmarkController::class, 'check']);
    });

    // Comment Routes
    Route::prefix('comments')->group(function () {
        Route::post('/events/{event}', [CommentController::class, 'store']); // Add comment or reply
        Route::put('/{comment}', [CommentController::class, 'update']); // Update own comment
        Route::delete('/{comment}', [CommentController::class, 'destroy']); // Delete own comment
        Route::get('/statistics', [CommentController::class, 'getStatistics']); // Get user's comment stats
    });

    // Subscription Routes
    Route::prefix('subscriptions')->group(function () {
        Route::post('/promotors/{promotor}', [FollowerController::class, 'store']); // Follow promotor
        Route::delete('/promotors/{promotor}', [FollowerController::class, 'destroy']); // Unfollow promotor
        Route::post('/categories/{category}', [CategorySubscriptionController::class, 'store']); // Subscribe to category
        Route::delete('/categories/{category}', [CategorySubscriptionController::class, 'destroy']); // Unsubscribe from category
    });

    // Payment Routes
    // Route::prefix('payments')->group(function () {
    //     Route::post('/events/{event}', [PaymentController::class, 'process']); // Process payment
    //     Route::get('/events/{event}/status', [PaymentController::class, 'checkStatus']); // Check payment status
    //     Route::post('/events/{event}/refund', [PaymentController::class, 'refund']); // Request refund
    // });
});

// ==================== Promotor Routes ====================
Route::middleware(['auth:sanctum', \App\Http\Middleware\PromotorMiddleware::class])->prefix('promotor')->group(function () {
    // Event Management
    Route::apiResource('events', EventController::class);
    // Route::post('events/{event}/publish', [EventController::class, 'publish']);
    // Route::post('events/{event}/unpublish', [EventController::class, 'unpublish']);
    // Route::get('events/{event}/statistics', [EventController::class, 'getStatistics']);
    // Route::get('events/{event}/attendees', [EventController::class, 'getAttendees']);
    // Route::get('events/{event}/payments', [EventController::class, 'getPayments']);

    // Event Image Management
    // Route::prefix('events/{event}/images')->group(function () {
    //     Route::post('/', [PromotorEventImageController::class, 'store']);
    //     Route::put('/{image}', [PromotorEventImageController::class, 'update']);
    //     Route::delete('/{image}', [PromotorEventImageController::class, 'destroy']);
    //     Route::post('/{image}/reorder', [PromotorEventImageController::class, 'reorder']);
    // });

    // Event Comment Promotor
    Route::prefix('comments')->group(function () {
        Route::post('/events/{event}', [CommentController::class, 'store']); // Add comment or reply
        Route::put('/{comment}', [CommentController::class, 'update']); // Update own comment
        Route::delete('/{comment}', [CommentController::class, 'destroy']); // Delete own comment
        Route::get('/statistics', [CommentController::class, 'getStatistics']); // Get user's comment stats
    });

    // Promotor Profile
    // Route::get('/profile', [PromotorProfileController::class, 'show']);
    // Route::put('/profile', [PromotorProfileController::class, 'update']);
    // Route::get('/profile/followers', [PromotorProfileController::class, 'getFollowers']);
    // Route::get('/profile/statistics', [PromotorProfileController::class, 'getStatistics']);
});

// ==================== Admin Routes ====================
Route::middleware(['auth:sanctum', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);

    // Admin Mangement Verification for Promotor
    Route::get('/verifications', [AdminVerificationController::class, 'index']);
    Route::get('/verifications/{promotor}', [AdminVerificationController::class, 'show']);
    Route::put('/verifications/{promotor}/approve', [AdminVerificationController::class, 'approve']);
    Route::put('/verifications/{promotor}/reject', [AdminVerificationController::class, 'reject']);

    // Admin Management Category
    Route::apiResource('categories', AdminCategoryController::class);

    // Admin Management Event Tags
    Route::apiResource('event-tags', AdminEventTagController::class);

    // Admin Delete Event
    Route::delete('/events/{event}', [EventController::class, 'destroy']);

    // Admin Management User
    Route::apiResource('users', AdminUserController::class);
    Route::put('/users/{user}/active', [AdminUserController::class, 'updateActive']);
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole']);

    // Admin Comment Management
    Route::prefix('comments')->group(function () {
        Route::get('/statistics', [CommentController::class, 'getAdminStatistics']); // Get admin-level comment stats
        Route::get('/events/{event}/statistics', [CommentController::class, 'getEventCommentStatistics']); // Get event-specific comment stats
        Route::delete('/{comment}', [CommentController::class, 'deleteComment']); // Admin delete comment
    });
});
