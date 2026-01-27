<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class CategoryService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Category::query()->latest()->paginate($perPage);
    }

    public function findById(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function create(string $name): Category
    {
        $slug = $this->generateSlug($name);

        return Category::create([
            "name" => $name,
            "slug" => $slug,
        ]);
    }

    public function update(Category $category, string $name): Category
    {
        $category->update([
            "name" => $name,
            "slug" => $this->generateSlug($name),
        ]);

        return $category;
    }

    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new \DomainException(
                "Category cannot be deleted because it has products attached.",
            );
        }

        $category->delete();
    }

    private function generateSlug(string $name): string
    {
        // Replace spaces with underscores as requested
        return Str::lower(str_replace(" ", "_", trim($name)));
    }
}
