<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Version 1 of the API
|
*/

// Public routes
Route::prefix("auth")->group(function () {
    Route::post("register", [AuthController::class, "register"]);
    Route::post("login", [AuthController::class, "login"]);
});

// Protected routes
Route::middleware("auth:sanctum")->group(function () {
    // Auth routes
    Route::prefix("auth")->group(function () {
        Route::post("logout", [AuthController::class, "logout"]);
        Route::get("me", [AuthController::class, "me"]);
    });

    // admin routes
    Route::middleware("role:admin")->group(function () {
        Route::get("users", [UserController::class, "index"]);
        Route::post("users", [UserController::class, "store"]);
        Route::put("users/{user}", [UserController::class, "update"]);
        Route::delete("users/{user}", [UserController::class, "destroy"]);
    });

    // creator routes
    Route::middleware("role:creator")->group(function () {
        Route::get("posts", [PostController::class, "index"]);
        Route::post("posts", [PostController::class, "store"]);
        Route::put("posts/{post}", [PostController::class, "update"]);
        Route::delete("posts/{post}", [PostController::class, "destroy"]);
    });

    // customer routes
    Route::middleware("role:customer")->group(function () {
        Route::get("orders", [OrderController::class, "index"]);
        Route::post("orders", [OrderController::class, "store"]);
        Route::put("orders/{order}", [OrderController::class, "update"]);
        Route::delete("orders/{order}", [OrderController::class, "destroy"]);
    });
});
