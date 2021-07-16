<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::middleware(['auth:sanctum', 'verified'])->get('/chat', function () {
    return Inertia::render('Chat/ChatContainer');
})->name('chat');

Route::middleware(['auth:sanctum', 'verified'])->get('/chatrooms', function () {
    return Inertia::render('Chat/ChatRoomsContainer');
})->name('chatrooms');

Route::middleware('auth:sanctum')->get('/chatrooms/chatroom/{roomId}', [ChatController::class, 'chatroom']);

Route::middleware(['auth:sanctum', 'verified'])->get('/chatrooms/newRoom', function () {
    return Inertia::render('Chat/CreateNewContainer');
})->name('newRoom');

Route::middleware('auth:sanctum')->get('/chat/rooms', [ChatController::class, 'rooms']);

Route::middleware('auth:sanctum')->get('/chat/room/{roomId}', [ChatController::class, 'currentRoom']);

Route::middleware('auth:sanctum')->get('/chat/room/{roomId}/messages', [ChatController::class, 'messages']);

Route::middleware('auth:sanctum')->post('/chat/room/{roomId}/message', [ChatController::class, 'newMessage']);

Route::middleware('auth:sanctum')->post('/chat/newRoom/create', [ChatController::class, 'newRoom']);