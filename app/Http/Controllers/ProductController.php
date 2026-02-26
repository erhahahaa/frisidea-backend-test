<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    #[OA\Get(
        path: "/api/products",
        summary: "Get list of products",
        description: "Retrieve a paginated list of products with optional search functionality.",
        operationId: "getProducts",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters:[ 
            new OA\Parameter(
                name: "search",
                in: "query",
                description: "Search term to filter products by name or description.",
                required:false,
                schema:new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                description: "Number of products to return per page.",
                required:false,
                schema:new OA\Schema(type: "integer", default:10)
            )
        ],
        responses:[
            new OA\Response(
                response:200,
                description: "Successful retrieval of products",
                content:new OA\JsonContent(
                    properties:[
                        new OA\Property(
                            property: "success",
                            type: "boolean",
                            example:true
                        ),
                        new OA\Property(
                            property: "message",
                            type: "string",
                            example: "Products retrieved successfully"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items:new OA\Items(ref: "#/components/schemas/ProductSchema")
                        )
                    ]
                )
            ),
            
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $products = $this->productRepository->getAll($search, $perPage);

        return $this->successResponse($products, 'Products retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    #[OA\Post(
        path: "/api/products",
        summary: "Create a new product",
        description: "Create a new product with the provided details.",
        operationId: "createProduct",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "price"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Sample Product"),
                    new OA\Property(property: "description", type: "string", example: "This is a sample product."),
                    new OA\Property(property: "price", type: "number", format: "float", example: 19.99),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Product created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Product created successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            ref: "#/components/schemas/ProductSchema"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Validation failed"),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "name",
                                    type: "array",
                                    items: new OA\Items(type: "string", example: "The name field is required.")
                                ),
                                new OA\Property(
                                    property: "price",
                                    type: "array",
                                    items: new OA\Items(type: "string", example: "The price field is required.")
                                ),
                            ]
                        )
                    ],
                )
            ),
        ]
    )]
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productRepository->create($request->validated());

        return $this->successResponse($product, 'Product created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    #[OA\Get(
        path: "/api/products/{id}",
        summary: "Get product details",
        description: "Retrieve detailed information about a specific product by its ID.",
        operationId: "getProductById",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID of the product to retrieve",
                required: true,
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Product retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Product retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            ref: "#/components/schemas/ProductSchema",
                        ),
                    ]
                )
            ),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $product = $this->productRepository->findById($id);

        return $this->successResponse($product, 'Product retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     */
    #[OA\Put(
        path: "/api/products/{id}",
        summary: "Update a product",
        description: "Update the details of an existing product by its ID.",
        operationId: "updateProduct",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID of the product to update",
                required: true,
                schema: new OA\Schema(type: "string") 
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Product updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Product updated successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            ref: "#/components/schemas/ProductSchema"
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Validation error",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Validation failed"),
                        new OA\Property(
                            property: "errors",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "name",
                                    type: "array",
                                    items: new OA\Items(type: "string", example: "The name field is required.")
                                ),
                                new OA\Property(
                                    property: "price",
                                    type: "array",
                                    items: new OA\Items(type: "string", example: "The price field is required.")
                                ),
                            ]
                        )
                    ],
                )
             ),
        ]
    )]
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = $this->productRepository->update($id, $request->validated());

        return $this->successResponse($product, 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    #[OA\Delete(
        path: "/api/products/{id}",
        summary: "Delete a product",
        description: "Delete an existing product by its ID.",
        operationId: "deleteProduct",
        tags: ["Products"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID of the product to delete",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Product deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Product deleted successfully"),
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Product not found",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Product not found"),
                    ]
                )
            ),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        $this->productRepository->delete($id);

        return $this->successResponse(null, 'Product deleted successfully');
    }
}
