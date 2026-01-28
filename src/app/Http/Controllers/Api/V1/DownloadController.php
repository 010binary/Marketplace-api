<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DownloadService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[OA\Tag(name: "Downloads", description: "Secure file download endpoints")]
class DownloadController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService,
    ) {}

    #[
        OA\Post(
            path: "/api/v1/products/{productId}/generate-download-url",
            summary: "Generate temporary download URL",
            description: "Generate a temporary signed URL for downloading a purchased product. URL expires in 60 minutes. Customer only.",
            security: [["sanctum" => []]],
            tags: ["Downloads"],
            parameters: [
                new OA\Parameter(
                    name: "productId",
                    in: "path",
                    description: "Product ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            requestBody: new OA\RequestBody(
                required: false,
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "expiration_minutes",
                            type: "integer",
                            description: "URL expiration time in minutes (default: 60, max: 1440)",
                            example: 60,
                        ),
                    ],
                ),
            ),
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Download URL generated successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/DownloadUrlResponse",
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
                new OA\Response(
                    response: 403,
                    description: "Forbidden - Product not purchased or Customer role required",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You have not purchased this product.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Product not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
            ],
        ),
    ]
    public function generateDownloadUrl(Request $request, int $productId)
    {
        $validated = $request->validate([
            'expiration_minutes' => ['sometimes', 'integer', 'min:1', 'max:1440'], // Max 24 hours
        ]);

        $expirationMinutes = $validated['expiration_minutes'] ?? 60;

        try {
            $downloadUrl = $this->downloadService->generateDownloadUrl(
                $request->user(),
                $productId,
                $expirationMinutes
            );

            $expiresAt = now()->addMinutes($expirationMinutes);

            return response()->json([
                'download_url' => $downloadUrl,
                'expires_at' => $expiresAt->toIso8601String(),
                'expires_in_minutes' => $expirationMinutes,
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode()
            );
        } catch (\DomainException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    #[
        OA\Get(
            path: "/api/v1/download/{productId}",
            summary: "Download product file",
            description: "Download a purchased product file using a signed URL. URL must be valid and not expired. Customer only.",
            security: [["sanctum" => []]],
            tags: ["Downloads"],
            parameters: [
                new OA\Parameter(
                    name: "productId",
                    in: "path",
                    description: "Product ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
                new OA\Parameter(
                    name: "expires",
                    in: "query",
                    description: "Expiration timestamp (auto-generated by signed URL)",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1706328990),
                ),
                new OA\Parameter(
                    name: "signature",
                    in: "query",
                    description: "URL signature (auto-generated by signed URL)",
                    required: true,
                    schema: new OA\Schema(type: "string", example: "abc123..."),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "File download started",
                    content: new OA\MediaType(
                        mediaType: "application/octet-stream",
                        schema: new OA\Schema(
                            type: "string",
                            format: "binary",
                        ),
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated or invalid signature",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "Invalid signature.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 403,
                    description: "Forbidden - Product not purchased or URL expired",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You have not purchased this product.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Product or file not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
            ],
        ),
    ]
    public function download(Request $request, int $productId)
    {
        // Verify signed URL
        if (!$request->hasValidSignature()) {
            return response()->json(
                ['message' => 'Invalid or expired download URL.'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Verify user ID matches the signed URL
        $userId = $request->query('userId');
        if (!$userId || (int) $userId !== $request->user()->id) {
            return response()->json(
                ['message' => 'Invalid download URL for this user.'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $ipAddress = $request->ip();

            return $this->downloadService->downloadFile(
                $request->user(),
                $productId,
                $ipAddress
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode()
            );
        } catch (\DomainException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    #[
        OA\Get(
            path: "/api/v1/products/{productId}/download-info",
            summary: "Get download information",
            description: "Get information about a downloadable product including file details and download statistics. Customer only.",
            security: [["sanctum" => []]],
            tags: ["Downloads"],
            parameters: [
                new OA\Parameter(
                    name: "productId",
                    in: "path",
                    description: "Product ID",
                    required: true,
                    schema: new OA\Schema(type: "integer", example: 1),
                ),
            ],
            responses: [
                new OA\Response(
                    response: 200,
                    description: "Download information retrieved successfully",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/DownloadInfo",
                    ),
                ),
                new OA\Response(
                    response: 401,
                    description: "Unauthenticated",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/Unauthenticated",
                    ),
                ),
                new OA\Response(
                    response: 403,
                    description: "Forbidden - Product not purchased or Customer role required",
                    content: new OA\JsonContent(
                        properties: [
                            new OA\Property(
                                property: "message",
                                type: "string",
                                example: "You have not purchased this product.",
                            ),
                        ],
                    ),
                ),
                new OA\Response(
                    response: 404,
                    description: "Product not found",
                    content: new OA\JsonContent(
                        ref: "#/components/schemas/NotFound",
                    ),
                ),
            ],
        ),
    ]
    public function downloadInfo(Request $request, int $productId)
    {
        try {
            $info = $this->downloadService->getDownloadInfo(
                $request->user(),
                $productId
            );

            return response()->json($info);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                $e->getStatusCode()
            );
        } catch (\DomainException $e) {
            return response()->json(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}
