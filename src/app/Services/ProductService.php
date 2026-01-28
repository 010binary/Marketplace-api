<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function paginate(
        int $perPage = 15,
        ?int $categoryId = null,
        ?string $search = null,
        ?string $sortBy = null,
        string $sortOrder = 'desc'
    ): LengthAwarePaginator {
        $query = Product::query()
            ->with(['creator', 'category', 'image', 'file']);

        // Filter by category
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        // Search by product name or date created
        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereDate('created_at', $search);
            });
        }

        // Sort by price or other fields
        if ($sortBy === 'price') {
            $query->orderBy('price', $sortOrder);
        } elseif ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortOrder);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): Product
    {
        return Product::with(['creator', 'category', 'image', 'file'])
            ->findOrFail($id);
    }

    public function create(array $data, User $creator): Product
    {
        return Product::create([
            'creator_id' => $creator->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'is_active' => false, // Default to inactive/unpublished
        ]);
    }

    public function update(Product $product, array $data, User $user): Product
    {
        $this->authorizeOwnership($product, $user);

        $product->update([
            'category_id' => $data['category_id'] ?? $product->category_id,
            'title' => $data['title'] ?? $product->title,
            'description' => $data['description'] ?? $product->description,
            'price' => $data['price'] ?? $product->price,
        ]);

        return $product->fresh(['creator', 'category', 'image', 'file']);
    }

    public function delete(Product $product, User $user): void
    {
        $this->authorizeOwnership($product, $user);

        // Check if product has purchases
        if ($product->purchases()->exists()) {
            throw new \DomainException(
                'Product cannot be deleted because it has purchases.'
            );
        }

        $product->delete();
    }

    public function publish(Product $product, User $user): Product
    {
        $this->authorizeOwnership($product, $user);

        // Ensure product has required files before publishing
        if (!$product->file || !$product->image) {
            throw new \DomainException(
                'Product must have both a file and an image before publishing.'
            );
        }

        $product->update(['is_active' => true]);

        return $product->fresh(['creator', 'category', 'image', 'file']);
    }

    public function unpublish(Product $product, User $user): Product
    {
        $this->authorizeOwnership($product, $user);

        $product->update(['is_active' => false]);

        return $product->fresh(['creator', 'category', 'image', 'file']);
    }

    public function getCreatorProducts(User $creator, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->where('creator_id', $creator->id)
            ->with(['category', 'image', 'file'])
            ->latest()
            ->paginate($perPage);
    }

    private function authorizeOwnership(Product $product, User $user): void
    {
        if ($product->creator_id !== $user->id) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403,
                'You are not authorized to perform this action on this product.'
            );
        }
    }
}
