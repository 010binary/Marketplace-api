<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Digital Marketplace API",
 *      description="API documentation for Digital Marketplace - A platform for creators to sell digital products",
 *      @OA\Contact(
 *          email="support@marketplace.com"
 *      )
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="Local API Server"
 * )
 *
 * @OA\SecurityScheme(
 *      securityScheme="sanctum",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT",
 *      description="Enter your Bearer token in the format: Bearer {token}"
 * )
 */
abstract class Controller
{
    //
}
