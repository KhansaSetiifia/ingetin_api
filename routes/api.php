<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware(\App\Http\Middleware\CheckToken::class);

// Debug endpoint - publicly accessible
Route::get('/debug/request', function (Request $request) {
    return response()->json([
        'headers' => [
            'token' => $request->header('token'),
            'authorization' => $request->header('authorization'),
            'content_type' => $request->header('content-type'),
            'user_agent' => $request->header('user-agent'),
        ],
        'method' => $request->method(),
        'url' => $request->url(),
        'path' => $request->path(),
        'ip' => $request->ip(),
    ]);
});

Route::resource('users', \App\Http\Controllers\UserController::class)
    ->except(['create', 'edit'])
    ->middleware(\App\Http\Middleware\CheckToken::class);

Route::resource('categories', \App\Http\Controllers\CategoryController::class)
    ->except(['create', 'edit'])
    ->middleware(\App\Http\Middleware\CheckToken::class);

Route::resource('todos', \App\Http\Controllers\TodoController::class)
    ->except(['create', 'edit'])
    ->middleware(\App\Http\Middleware\CheckToken::class);
    
// Custom todo endpoints
Route::middleware(\App\Http\Middleware\CheckToken::class)->group(function() {
    Route::get('/todos/category/{categoryId}', [\App\Http\Controllers\TodoController::class, 'getByCategory']);
    Route::get('/todos/status/{status}', [\App\Http\Controllers\TodoController::class, 'getByStatus']);
    Route::get('/todos/priority/{priority}', [\App\Http\Controllers\TodoController::class, 'getByPriority']);
    Route::get('/todos/search', [\App\Http\Controllers\TodoController::class, 'search']);
    Route::get('/debug/auth', [\App\Http\Controllers\TodoController::class, 'debugAuth']);
});

// Admin routes - requires token and admin role
Route::middleware([\App\Http\Middleware\CheckToken::class, \App\Http\Middleware\CheckAdminRole::class])
    ->prefix('admin')
    ->group(function() {
        // Get todos for a specific user
        Route::get('/users/{userId}/todos', [\App\Http\Controllers\AdminController::class, 'getUserTodos']);
        
        // Get todos for a specific user filtered by status
        Route::get('/users/{userId}/todos/status/{status}', [\App\Http\Controllers\AdminController::class, 'getUserTodosByStatus']);
        
        // Get todos for a specific user filtered by priority
        Route::get('/users/{userId}/todos/priority/{priority}', [\App\Http\Controllers\AdminController::class, 'getUserTodosByPriority']);
        
        // Get todos for a specific user filtered by category
        Route::get('/users/{userId}/todos/category/{categoryId}', [\App\Http\Controllers\AdminController::class, 'getUserTodosByCategory']);
        
        // Search todos for a specific user
        Route::get('/users/{userId}/todos/search', [\App\Http\Controllers\AdminController::class, 'searchUserTodos']);
    });