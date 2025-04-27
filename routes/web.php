<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Promotor\EventController as PromotorEventController;

Route::get('/', function () {
    return view('welcome');
});

// Public routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);
Route::get('/events/{event}/comments', [CommentController::class, 'index']);

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Event registration
    Route::post('/events/{event}/register', [EventController::class, 'register']);

    // Comments
    Route::post('/events/{event}/comments', [CommentController::class, 'store']);
    Route::put('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // Promotor routes
    Route::prefix('promotor')->middleware('promotor')->group(function () {
        // Event management
        Route::get('/events', [PromotorEventController::class, 'index']);
        Route::get('/events/{event}', [PromotorEventController::class, 'show']);
        Route::post('/events', [PromotorEventController::class, 'store']);
        Route::put('/events/{event}', [PromotorEventController::class, 'update']);
        Route::delete('/events/{event}', [PromotorEventController::class, 'destroy']);

        // Event publishing
        Route::post('/events/{event}/publish', [PromotorEventController::class, 'publish']);
        Route::post('/events/{event}/unpublish', [PromotorEventController::class, 'unpublish']);

        // Event attendees
        Route::get('/events/{event}/attendees', [PromotorEventController::class, 'attendees']);
        Route::post('/events/{event}/attendees/{attendee}/check-in', [PromotorEventController::class, 'checkIn']);
    });
});
