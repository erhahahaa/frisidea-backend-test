<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private Product $model
    ) {}

    /**
     * Get all products with optional search and pagination.
     */
    public function getAll(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query();

        // Apply search filter if provided
        if ($search) {
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        // Return paginated results
        return $query->paginate($perPage);
    }

    /**
     * Find a product by ID.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $id): Product
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing product.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Product
    {
        $product = $this->findById($id);
        $product->update($data);

        return $product->fresh();
    }

    /**
     * Delete a product (soft delete).
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        $product = $this->findById($id);

        return $product->delete();
    }
}
