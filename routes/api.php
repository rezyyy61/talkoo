<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SameIpUsersController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'is_online'])->group(function () {
    Route::get('/user', [UserProfileController::class, 'getUser']);
    Route::post('/user/profile/update', [UserProfileController::class, 'update']);
    // Friendship routes
    Route::get('/friends', [FriendshipController::class, 'listFriends']);
    Route::get('/friend/{id}', [FriendshipController::class, 'getSelectedUserWithProfile']);
    Route::get('/friends/accepted', [FriendshipController::class, 'listFriendsAccepted']);
    Route::get('/friends/search', [FriendshipController::class, 'searchUsers']);
    Route::post('/friends/request', [FriendshipController::class, 'sendRequest']);
    Route::post('/friends/accept', [FriendshipController::class, 'acceptRequest']);
    Route::post('/friends/decline', [FriendshipController::class, 'declineRequest']);
    Route::post('/friends/statuses', [FriendshipController::class, 'getFriendshipStatuses']);
    Route::post('/send-message', [FriendshipController::class, 'sendMessage']);

    // Messages routes
    Route::post('/messages/send', [MessageController::class, 'sendMessage']);
    Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'getMessages']);
    Route::get('/conversations/by-user/{receiverId}', [MessageController::class, 'getConversation']);
    Route::get('/conversations/group/same-ip', [MessageController::class, 'getSameIPConversation']);
    Route::patch('/conversations/{conversationId}/messages/read', [MessageController::class, 'markAsRead']);
    Route::post('/conversations/{conversationId}/typing', [MessageController::class, 'userTyping']);
});

// Routes accessible without authentication
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest',  'is_online')
    ->name('register');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth:sanctum', 'is_online'])
    ->name('logout');
