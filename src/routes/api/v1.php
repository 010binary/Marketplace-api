<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\DownloadController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductFileController;
use App\Http\Controllers\Api\V1\PurchaseController;
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

    Route::get("/products", [ProductController::class, "index"]); // paginated with filters

    // creator routes
    Route::middleware("role:creator")->group(function () {
        // Categories
        Route::post("/categories", [CategoryController::class, "store"]);
        Route::put("/categories/{category}", [
            CategoryController::class,
            "update",
        ]);
        Route::delete("/categories/{category}", [
            CategoryController::class,
            "destroy",
        ]);

        // Products - my-products must come before {product} wildcard
        Route::get("/products/my-products", [
            ProductController::class,
            "myProducts",
        ]);
        Route::post("/products", [ProductController::class, "store"]);
        Route::put("/products/{product}", [ProductController::class, "update"]);
        Route::delete("/products/{product}", [
            ProductController::class,
            "destroy",
        ]);
        Route::post("/products/{product}/publish", [
            ProductController::class,
            "publish",
        ]);
        Route::post("/products/{product}/unpublish", [
            ProductController::class,
            "unpublish",
        ]);

        // Product File Uploads
        Route::post("/products/{product}/upload-file", [
            ProductFileController::class,
            "uploadFile",
        ]);
        Route::post("/products/{product}/upload-image", [
            ProductFileController::class,
            "uploadImage",
        ]);
    });

    // Product detail route - must come after /products/my-products
    Route::get("/products/{id}", [ProductController::class, "show"]);

    // customer routes
    Route::middleware("role:customer")->group(function () {
        // Checkout & Purchase
        Route::post("/checkout/{product}", [
            PurchaseController::class,
            "checkout",
        ]);

        // Library (Purchased Products)
        Route::get("/library", [PurchaseController::class, "library"]);
        Route::get("/library/{purchase}", [PurchaseController::class, "show"]);

        // Downloads
        Route::post("/products/{productId}/generate-download-url", [
            DownloadController::class,
            "generateDownloadUrl",
        ]);
        Route::get("/products/{productId}/download-info", [
            DownloadController::class,
            "downloadInfo",
        ]);
    });

    // Secure download route (requires valid signed URL)
    Route::get("/download/{productId}", [
        DownloadController::class,
        "download",
    ])->name("download.file");

    // Creator sales and revenue routes
    Route::middleware("role:creator")->group(function () {
        Route::get("/sales", [PurchaseController::class, "sales"]);
        Route::get("/revenue", [PurchaseController::class, "revenue"]);
    });
});
