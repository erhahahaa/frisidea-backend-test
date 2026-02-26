<?php

use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = auth('api')->login($this->user);
});

// Test: Fetch All Products
test('can fetch all products with pagination', function () {
    Product::factory()->count(15)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'data',
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ])
        ->assertJson([
            'success' => true,
            'message' => 'Products retrieved successfully',
        ]);

    expect($response->json('data.per_page'))->toBe(10);
    expect($response->json('data.total'))->toBe(15);
});

// Test: Fetch Single Product
test('can fetch single product by id', function () {
    $product = Product::factory()->create([
        'name' => 'Test Product',
        'price' => 99.99,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data' => [
                'id' => $product->id,
                'name' => 'Test Product',
                'price' => '99.99',
            ],
        ]);
});

test('returns 404 for non-existent product', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/products/99999');

    $response->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Resource not found',
        ]);
});

// Test: Create Product
test('can create product with valid data', function () {
    $productData = [
        'name' => 'New Product',
        'description' => 'Product description',
        'price' => 149.99,
    ];

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/products', $productData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Product created successfully',
        ]);

    $this->assertDatabaseHas('products', [
        'name' => 'New Product',
        'price' => 149.99,
    ]);
});

test('cannot create product without required fields', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/products', []);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => ['name', 'price'],
        ]);
});

test('cannot create product with invalid price', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => -10,
        ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => ['price'],
        ]);
});

test('cannot create product with price having more than 2 decimals', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => 99.999,
        ]);

    $response->assertStatus(422)
        ->assertJsonStructure([
            'success',
            'message',
            'errors' => ['price'],
        ]);
});

// Test: Update Product
test('can update product with valid data', function () {
    $product = Product::factory()->create([
        'name' => 'Original Name',
        'price' => 50.00,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 75.00,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Product updated successfully',
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
        'price' => 75.00,
    ]);
});

test('can partially update product', function () {
    $product = Product::factory()->create([
        'name' => 'Original Name',
        'price' => 50.00,
    ]);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->patchJson("/api/products/{$product->id}", [
            'name' => 'Partial Update',
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Partial Update',
        'price' => 50.00, // Price unchanged
    ]);
});

test('returns 404 when updating non-existent product', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->putJson('/api/products/99999', [
            'name' => 'Updated Name',
        ]);

    $response->assertStatus(404);
});

// Test: Delete Product
test('can soft delete product', function () {
    $product = Product::factory()->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

test('returns 404 when deleting non-existent product', function () {
    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->deleteJson('/api/products/99999');

    $response->assertStatus(404);
});

// Test: Search Functionality
test('can search products by name', function () {
    Product::factory()->create(['name' => 'iPhone 15 Pro']);
    Product::factory()->create(['name' => 'Samsung Galaxy S24']);
    Product::factory()->create(['name' => 'iPhone 14']);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/products?search=iphone');

    $response->assertStatus(200);

    $products = $response->json('data.data');
    expect(count($products))->toBe(2);
});

test('search is case insensitive', function () {
    Product::factory()->create(['name' => 'Laptop Computer']);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/products?search=LAPTOP');

    $response->assertStatus(200);

    $products = $response->json('data.data');
    expect(count($products))->toBeGreaterThanOrEqual(1);
});

test('search returns empty array when no matches', function () {
    Product::factory()->create(['name' => 'Product One']);

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/products?search=nonexistent');

    $response->assertStatus(200);

    $products = $response->json('data.data');
    expect(count($products))->toBe(0);
});

// Test: Pagination
test('pagination works correctly', function () {
    Product::factory()->count(25)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/products?per_page=10&page=1');

    $response->assertStatus(200)
        ->assertJsonPath('data.per_page', 10)
        ->assertJsonPath('data.current_page', 1);

    $products = $response->json('data.data');
    expect(count($products))->toBe(10);
});

test('can navigate to second page', function () {
    Product::factory()->count(25)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/products?page=2');

    $response->assertStatus(200)
        ->assertJsonPath('data.current_page', 2);
});

test('can customize items per page', function () {
    Product::factory()->count(25)->create();

    $response = $this->withHeader('Authorization', "Bearer {$this->token}")
        ->getJson('/api/products?per_page=5');

    $response->assertStatus(200)
        ->assertJsonPath('data.per_page', 5);

    $products = $response->json('data.data');
    expect(count($products))->toBe(5);
});

// Test: Authentication Requirements
test('all product endpoints require authentication', function () {
    $product = Product::factory()->create();

    $endpoints = [
        ['method' => 'get', 'url' => '/api/products'],
        ['method' => 'get', 'url' => "/api/products/{$product->id}"],
        ['method' => 'post', 'url' => '/api/products'],
        ['method' => 'put', 'url' => "/api/products/{$product->id}"],
        ['method' => 'delete', 'url' => "/api/products/{$product->id}"],
    ];

    foreach ($endpoints as $endpoint) {
        $method = $endpoint['method'].'Json';
        $response = $this->$method($endpoint['url']);
        $response->assertStatus(401);
    }
});
