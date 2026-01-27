<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductFileService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductFileController extends Controller
{
    public function __construct(
        private readonly ProductFileService $fileService
    ) {}

    public function uploadFile(Request $request, Product $product)
    {
        $this->authorizeOwnership($product, $request);

        $request->validate([
            'file' => ['required', 'file', 'max:512000'], // ~500MB
        ]);

        return response()->json(
            $this->fileService->uploadProductFile(
                $product,
                $request->file('file')
            ),
            Response::HTTP_CREATED
        );
    }

    public function uploadImage(Request $request, Product $product)
    {
        $this->authorizeOwnership($product, $request);

        $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5MB
        ]);

        return response()->json(
            $this->fileService->uploadDisplayImage(
                $product,
                $request->file('image')
            ),
            Response::HTTP_CREATED
        );
    }

    private function authorizeOwnership(Product $product, Request $request): void
    {
        if ($product->creator_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }
    }
}
