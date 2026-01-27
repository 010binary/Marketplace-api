<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
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
    
    //general routes
    Route::get("/categories", [CategoryController::class, "index"]); // paginated
    Route::get("/categories/{id}", [CategoryController::class, "show"]);

    // creator routes
    Route::middleware("role:creator")->group(function () {
        Route::post("/categories", [CategoryController::class, "store"]);
        Route::put("/categories/{category}", [
            CategoryController::class,
            "update",
        ]);
        Route::delete("/categories/{category}", [
            CategoryController::class,
            "destroy",
        ]);
    });

    // customer routes
    Route::middleware("role:customer")->group(function () {
        Route::get("/categories", [CategoryController::class, "index"]); // paginated
        Route::get("/categories/{id}", [CategoryController::class, "show"]);
    });
});
