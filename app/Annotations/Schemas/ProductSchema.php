<?php

namespace App\Annotations\Schemas;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "Product",
    description: "Product model schema",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Sample Product"),
        new OA\Property(property: "description", type: "string", example: "This is a sample product description."),
        new OA\Property(property: "price", type: "number", format: "float", example: 19.99),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-01-01T12:00:00Z")
    ]
)]
class ProductSchema {}