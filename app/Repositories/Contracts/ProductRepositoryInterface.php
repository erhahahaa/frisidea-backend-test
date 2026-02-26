<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface
{
    /**
     * Get all products with optional search and pagination.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAll(?string $search = null, int $perPage = 10);

    /**
     * Find a product by ID.
     *
     * @return \App\Models\Product
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $id);

    /**
     * Create a new product.
     *
     * @return \App\Models\Product
     */
    public function create(array $data);

    /**
     * Update an existing product.
     *
     * @return \App\Models\Product
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data);

    /**
     * Delete a product (soft delete).
     *
     * @return bool
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id);
}
