# Frisidea Backend Test - Product Management API

A RESTful API for product management built with Laravel 12, featuring JWT authentication, repository pattern, comprehensive testing, and full Docker support.

## Features

- ✅ **JWT Authentication** - Secure API access with token-based authentication
- ✅ **Product CRUD** - Complete Create, Read, Update, Delete operations
- ✅ **Repository Pattern** - Clean architecture with interface-based design
- ✅ **Search & Pagination** - Efficient product filtering and data pagination
- ✅ **Request Validation** - Strong validation for create and update operations
- ✅ **Soft Delete** - Products are marked as deleted, not removed
- ✅ **Rate Limiting** - 60 requests per minute per IP address
- ✅ **Global Exception Handling** - Consistent JSON error responses
- ✅ **Comprehensive Testing** - Full test coverage with Pest PHP
- ✅ **Docker Support** - Containerized with FrankenPHP + PostgreSQL
- ✅ **API Documentation** - Postman collection included

## Tech Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 12.x | PHP Framework |
| PHP | 8.2+ | Programming Language |
| PostgreSQL | 18 | Database |
| JWT Auth | 2.2 | Authentication |
| Pest PHP | 4.x | Testing Framework |
| FrankenPHP | Latest | Application Server |
| Docker | Latest | Containerization |

## Quick Start

### Option 1: Docker (Recommended)

```bash
# Clone the repository
git clone <repository-url>
cd frisidea-backend-test

# Copy environment file
cp .env.example .env

# Start Docker containers
docker-compose up -d

# Run migrations and seed database
docker-compose exec laravel php artisan migrate:fresh --seed

# Application ready at http://localhost
```

### Option 2: Local Development

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=frisidea_backend_test
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Run migrations and seed
php artisan migrate:fresh --seed

# Start development server
php artisan serve
```

## API Documentation

### Base URL

- **Docker**: `http://localhost/api`
- **Local**: `http://localhost:8000/api`

### Authentication

All product endpoints require JWT authentication. First, obtain a token:

#### Login

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com"
    }
  }
}
```

Use the token in subsequent requests:
```
Authorization: Bearer {token}
```

### Product Endpoints

#### Get All Products

```http
GET /api/products
Authorization: Bearer {token}

Query Parameters:
  - search (optional): Filter by name (case-insensitive)
  - page (optional): Page number (default: 1)
  - per_page (optional): Items per page (default: 10)

Example: /api/products?search=phone&page=1&per_page=20
```

**Response:**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": {
    "data": [...],
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50
  }
}
```

#### Get Single Product

```http
GET /api/products/{id}
Authorization: Bearer {token}
```

#### Create Product

```http
POST /api/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "iPhone 15 Pro",
  "description": "Latest Apple smartphone",
  "price": 999.99
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `description`: optional, string
- `price`: required, numeric, min 0, max 2 decimal places

#### Update Product (Full)

```http
PUT /api/products/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "iPhone 15 Pro Max",
  "description": "Updated description",
  "price": 1199.99
}
```

#### Update Product (Partial)

```http
PATCH /api/products/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "price": 899.99
}
```

#### Delete Product

```http
DELETE /api/products/{id}
Authorization: Bearer {token}
```

**Note:** This is a soft delete. The product is marked as deleted but remains in the database.

### Response Format

All API responses follow this structure:

**Success:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {...}
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": {...}
}
```

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 401 | Unauthorized |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests |
| 500 | Server Error |

## Testing

The project includes comprehensive tests using Pest PHP:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test --filter=ProductTest

# Run with coverage
php artisan test --coverage

# Run in Docker
docker-compose exec laravel php artisan test
```

**Test Coverage:**
- ✅ Authentication (login, JWT tokens, protected routes)
- ✅ Product CRUD (create, read, update, delete)
- ✅ Validation (required fields, data types, constraints)
- ✅ Search functionality
- ✅ Pagination
- ✅ Soft delete
- ✅ Error handling

## Database Seeding

```bash
# Seed database with sample data
php artisan db:seed

# Seed only products
php artisan db:seed --class=ProductSeeder

# Fresh migration with seed
php artisan migrate:fresh --seed
```

This creates:
- 1 test user (`test@example.com` / `password`)
- 50 sample products

## Rate Limiting

API endpoints are rate-limited to **60 requests per minute per IP address**.

When the limit is exceeded, you'll receive:
```json
{
  "success": false,
  "message": "Too many requests. Please try again later."
}
```

Response headers:
- `X-RateLimit-Limit`: 60
- `X-RateLimit-Remaining`: [count]
- `Retry-After`: [seconds] (when throttled)

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php          # JWT authentication
│   │   ├── Controller.php              # Base controller with ApiResponse trait
│   │   └── ProductController.php       # Product CRUD operations
│   ├── Requests/
│   │   ├── StoreProductRequest.php     # Create validation
│   │   └── UpdateProductRequest.php    # Update validation
│   └── Traits/
│       └── ApiResponse.php             # Consistent JSON responses
├── Models/
│   ├── Product.php                     # Product Eloquent model
│   └── User.php                        # User model with JWTSubject
├── Providers/
│   └── AppServiceProvider.php          # Service bindings & rate limiting
└── Repositories/
    ├── Contracts/
    │   └── ProductRepositoryInterface.php
    └── EloquentProductRepository.php

database/
├── factories/
│   └── ProductFactory.php              # Product test data factory
├── migrations/
│   └── *_create_products_table.php     # Products table schema
└── seeders/
    ├── DatabaseSeeder.php
    └── ProductSeeder.php               # Sample product seeder

tests/
└── Feature/
    ├── AuthTest.php                    # Authentication tests
    └── ProductTest.php                 # Product CRUD tests
```

## Docker

See [DOCKER.md](DOCKER.md) for comprehensive Docker documentation.

**Quick Commands:**
```bash
# Start services
docker-compose up -d

# View logs
docker-compose logs -f

# Run artisan commands
docker-compose exec laravel php artisan {command}

# Access PostgreSQL
docker-compose exec pgsql psql -U sail frisidea_backend_test

# Stop services
docker-compose down
```

## Postman Collection

Import `postman_collection.json` into Postman for ready-to-use API requests.

The collection includes:
- Environment variables (base_url, token)
- Auto-save token from login
- All product endpoints
- Search & pagination examples
- Automated test scripts

## Environment Variables

Key variables in `.env`:

```env
# Application
APP_NAME=PTMMS_REST_API
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=frisidea_backend_test
DB_USERNAME=sail
DB_PASSWORD=password

# JWT
JWT_SECRET=<generated-secret>
JWT_TTL=60
```

## Architecture Patterns

### Repository Pattern
- **Interface**: `ProductRepositoryInterface` defines the contract
- **Implementation**: `EloquentProductRepository` uses Eloquent ORM
- **Benefits**: Testability, flexibility, separation of concerns

### Dependency Injection
- Controllers type-hint interfaces
- Laravel service container resolves dependencies
- Easy to swap implementations

### Request Validation
- Form Request classes for validation logic
- Centralized validation rules
- Custom error messages

### Soft Delete
- Products aren't permanently removed
- `deleted_at` timestamp tracks deletion
- Can be restored if needed

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Write/update tests
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues, questions, or contributions, please open an issue on GitHub.
