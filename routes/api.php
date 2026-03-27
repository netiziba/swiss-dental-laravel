<?php

use Illuminate\Support\Facades\Route;

Route::get('/health', fn() => response()->json(['status' => 'ok']));

use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\TreatmentNoteController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);
Route::get('/availability', [AvailabilityController::class, 'index']);
Route::get('/dentists', [AvailabilityController::class, 'dentists']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

    // Services (admin/dentist management)
    Route::post('/services', [ServiceController::class, 'store']);
    Route::put('/services/{service}', [ServiceController::class, 'update']);
    Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

    // Appointments
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']);

    // Documents
    Route::get('/documents', [DocumentController::class, 'index']);
    Route::post('/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{document}', [DocumentController::class, 'show']);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::patch('/messages/{message}/read', [MessageController::class, 'markRead']);

    // Dentist availability
    Route::post('/availability', [AvailabilityController::class, 'store']);
    Route::put('/availability/{availability}', [AvailabilityController::class, 'update']);
    Route::delete('/availability/{availability}', [AvailabilityController::class, 'destroy']);

    // Users (admin)
    Route::get('/users', [UserController::class, 'index']);
    Route::put('/users/{user}/role', [UserController::class, 'updateRole']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Treatment notes
    Route::get('/treatment-notes', [TreatmentNoteController::class, 'index']);
    Route::post('/treatment-notes', [TreatmentNoteController::class, 'store']);
    Route::get('/treatment-notes/{treatmentNote}', [TreatmentNoteController::class, 'show']);
    Route::put('/treatment-notes/{treatmentNote}', [TreatmentNoteController::class, 'update']);
    Route::delete('/treatment-notes/{treatmentNote}', [TreatmentNoteController::class, 'destroy']);
});
