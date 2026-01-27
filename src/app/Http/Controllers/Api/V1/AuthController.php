<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    /**
     * Register a new user
     *
     * @OA\Post(
     *      path="/api/auth/register",
     *      tags={"Authentication"},
     *      summary="Register a new user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name","email","password","password_confirmation","role"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", example="john@example.com"),
     *              @OA\Property(property="password", type="string", example="password123"),
     *              @OA\Property(property="password_confirmation", type="string", example="password123"),
     *              @OA\Property(property="role", type="string", enum={"creator", "customer"}, example="creator")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="User registered successfully"
     *      )
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json(
            [
                "message" => "User registered successfully",
                "data" => [
                    "user" => new UserResource($result["user"]),
                    "token" => $result["token"],
                ],
            ],
            201,
        );
    }

    /**
     * Login user
     *
     * @OA\Post(
     *      path="/api/auth/login",
     *      tags={"Authentication"},
     *      summary="Login user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", example="john@example.com"),
     *              @OA\Property(property="password", type="string", example="password123")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Login successful"
     *      )
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            "message" => "Login successful",
            "data" => [
                "user" => new UserResource($result["user"]),
                "token" => $result["token"],
            ],
        ]);
    }

    /**
     * Logout user
     *
     * @OA\Post(
     *      path="/api/auth/logout",
     *      tags={"Authentication"},
     *      summary="Logout user",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Logged out successfully"
     *      )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            "message" => "Logged out successfully",
        ]);
    }

    /**
     * Get authenticated user
     *
     * @OA\Get(
     *      path="/api/auth/me",
     *      tags={"Authentication"},
     *      summary="Get authenticated user",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation"
     *      )
     * )
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            "message" => "User retrieved successfully",
            "data" => new UserResource($request->user()),
        ]);
    }
}
