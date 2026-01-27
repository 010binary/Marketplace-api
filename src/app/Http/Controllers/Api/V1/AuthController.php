<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "Authentication", description: "User authentication endpoints")]
class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    #[
        OA\Post(
            path: "/api/auth/register",
            summary: "Register a new user",
            description: "Register a new user account as either a creator or customer",
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: [
                        "name",
                        "email",
                        "password",
                        "password_confirmation",
                        "role",
                    ],
                    properties: [
                        new OA\Property(
                            property: "name",
                            type: "string",
                            example: "John Doe",
                        ),
                        new OA\Property(
                            property: "email",
                            type: "string",
                            format: "email",
                            example: "john@example.com",
                        ),
                        new OA\Property(
                            property: "password",
                            type: "string",
                            format: "password",
                            example: "password123",
                        ),
                        new OA\Property(
                            property: "password_confirmation",
                            type: "string",
                            format: "password",
                            example: "password123",
                        ),
                        new OA\Property(
                            property: "role",
                            type: "string",
                            enum: ["creator", "customer"],
                            example: "creator",
                        ),
                    ],
                ),
            ),
            tags: ["Authentication"],
            responses: [
                new OA\Response(
                    response: 201,
                    description: "User registered successfully",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "User registered successfully",
                            ),
                            new OA\Property(
                                property: "data",
                                properties: [
                                    new OA\Property(
                                        property: "user",
                                        ref: "#/components/schemas/User",
                                    ),
                                    new OA\Property(
                                        property: "token",
                                        type: "string",
                                        example: "1|nJZmaKgkzaGmvPNYDIrYCOjmCVvKZy2WsNW4fruRe24c588b",
                                    ),
                                ],
                                type: "object",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 422,
                    description: "Validation error",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/ValidationError",
                    ),
                ),
            ],
        ),
    ]
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

    #[
        OA\Post(
            path: "/api/auth/login",
            summary: "Login user",
            description: "Authenticate user and return access token",
            requestBody: new OA\RequestBody(
                required: true,
                content: new OA\JsonContent(
                    required: ["email", "password"],
                    properties: [
                        new OA\Property(
                            property: "email",
                            type: "string",
                            format: "email",
                            example: "john@example.com",
                        ),
                        new OA\Property(
                            property: "password",
                            type: "string",
                            format: "password",
                            example: "password123",
                        ),
                    ],
                ),
            ),
            tags: ["Authentication"],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Login successful",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "Login successful",
                            ),
                            new OA\Property(
                                property: "data",
                                properties: [
                                    new OA\Property(
                                        property: "user",
                                        ref: "#/components/schemas/User",
                                    ),
                                    new OA\Property(
                                        property: "token",
                                        type: "string",
                                        example: "1|nJZmaKgkzaGmvPNYDIrYCOjmCVvKZy2WsNW4fruRe24c588b",
                                    ),
                                ],
                                type: "object",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 422,
                    description: "Invalid credentials",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/ValidationError",
                    ),
                ),
            ],
        ),
    ]
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

    #[
        OA\Post(
            path: "/api/auth/logout",
            summary: "Logout user from current device",
            description: "Logout user and revoke current access token",
            security: [["sanctum" => []]],
            tags: ["Authentication"],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Logged out successfully",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "Logged out successfully",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
            ],
        ),
    ]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            "message" => "Logged out successfully",
        ]);
    }

    #[
        OA\Get(
            path: "/api/auth/me",
            summary: "Get authenticated user",
            description: "Get currently authenticated user details",
            security: [["sanctum" => []]],
            tags: ["Authentication"],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Successful operation",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "data",
                                ref: "#/components/schemas/User",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
            ],
        ),
    ]
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            "message" => "User retrieved successfully",
            "data" => new UserResource($request->user()),
        ]);
    }
}
