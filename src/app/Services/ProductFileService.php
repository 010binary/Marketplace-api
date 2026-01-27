<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductFile;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductFileService
{
    public function uploadProductFile(
        Product $product,
        UploadedFile $file,
    ): ProductFile {
        // Remove old file if exists
        if ($product->file) {
            Storage::disk($product->file->disk)->delete($product->file->path);
            $product->file->delete();
        }

        $path = $file->store("products/{$product->id}/file", "private");

        return ProductFile::create([
            "product_id" => $product->id,
            "disk" => "private",
            "path" => $path,
            "original_filename" => $file->getClientOriginalName(),
            "mime_type" => $file->getMimeType(),
            "size" => $file->getSize(),
        ]);
    }

    public function uploadDisplayImage(
        Product $product,
        UploadedFile $image,
    ): ProductImage {
        if ($product->image) {
            Storage::disk($product->image->disk)->delete($product->image->path);
            $product->image->delete();
        }

        $path = $image->store("products/{$product->id}/image", "private");

        return ProductImage::create([
            "product_id" => $product->id,
            "disk" => "private",
            "path" => $path,
            "mime_type" => $image->getMimeType(),
            "size" => $image->getSize(),
        ]);
    }
}
