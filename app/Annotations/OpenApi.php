<?php

namespace App\Annotations;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Frisidea Backend API',
    description: 'REST API for Frisidea Backend Test with JWT Authentication'
)]
#[OA\Server(url: 'http://localhost:8000')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    name: 'Authorization',
    in: 'header',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
class OpenApi {}