<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Test sanctum
Route::middleware("auth:sanctum")->get("/test", function (Request $request) {
    return response()->json([
        "message" => "Sanctum is working!",
        "user" => $request->user(),
    ]);
});

// Public routes
Route::prefix("auth")->group(function () {
    Route::post("register", [AuthController::class, "register"]);
    Route::post("login", [AuthController::class, "login"]);
});

// Protected routes
Route::middleware("auth:sanctum")->group(function () {
    Route::prefix("auth")->group(function () {
        Route::post("logout", [AuthController::class, "logout"]);
        Route::post("logout-all", [AuthController::class, "logoutAll"]);
        Route::get("me", [AuthController::class, "me"]);
    });
});
