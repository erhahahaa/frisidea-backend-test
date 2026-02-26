<?php

namespace App\Annotations\Schemas;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "User",
    description: "User model schema",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "John Doe"),
        new OA\Property(property: "email", type: "string", format: "email", example: "john@example.com"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-01-01T12:00:00Z")
    ]
)]
class UserSchema {}