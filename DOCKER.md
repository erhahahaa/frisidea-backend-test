# Docker Setup Guide

This project is fully containerized using Docker and Docker Compose with FrankenPHP as the application server.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose V2+

## Quick Start

```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f laravel

# Stop services
docker-compose down

# Stop and remove volumes (clean slate)
docker-compose down -v
```

## Services

### Laravel Application (FrankenPHP)
- **Container**: `frisidea-laravel`
- **Port**: 80
- **URL**: http://localhost
- **Server**: FrankenPHP (modern PHP application server)

### PostgreSQL Database
- **Container**: `ptmms-pgsql`
- **Port**: 5432
- **Database**: `frisidea_backend_test`
- **Username**: `sail`
- **Password**: `password`

## Environment Configuration

The application uses `.env` file for configuration. Key variables:

```env
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=frisidea_backend_test
DB_USERNAME=sail
DB_PASSWORD=password

JWT_SECRET=<generated-secret>
```

## Database Operations

### Run Migrations

```bash
docker-compose exec laravel php artisan migrate
```

### Seed Database

```bash
docker-compose exec laravel php artisan db:seed
```

### Fresh Migration with Seed

```bash
docker-compose exec laravel php artisan migrate:fresh --seed
```

### Create Test User

```bash
docker-compose exec laravel php artisan tinker
>>> User::factory()->create(['email' => 'admin@example.com', 'password' => bcrypt('password')])
```

## Running Tests

```bash
# Run all tests
docker-compose exec laravel php artisan test

# Run specific test file
docker-compose exec laravel php artisan test --filter=ProductTest

# Run with coverage
docker-compose exec laravel php artisan test --coverage
```

## Accessing the Database

### From Host Machine

```bash
psql -h localhost -p 5432 -U sail -d frisidea_backend_test
# Password: password
```

### From Laravel Container

```bash
docker-compose exec laravel php artisan tinker
>>> DB::connection()->getPdo();
```

## Troubleshooting

### Container Won't Start

```bash
# View logs
docker-compose logs laravel

# Restart services
docker-compose restart
```

### Database Connection Issues

```bash
# Check if PostgreSQL is healthy
docker-compose ps

# Should show 'healthy' status for pgsql service
```

### Clear Application Cache

```bash
docker-compose exec laravel php artisan config:clear
docker-compose exec laravel php artisan cache:clear
docker-compose exec laravel php artisan route:clear
```

### Reset Everything

```bash
# Stop containers and remove volumes
docker-compose down -v

# Rebuild and start fresh
docker-compose up -d --build

# Run migrations and seed
docker-compose exec laravel php artisan migrate:fresh --seed
```

## Production Deployment

For production, update `docker-compose.yml` to use the `prod` target:

```yaml
build:
  target: prod
```

Then rebuild:

```bash
docker-compose build --no-cache
docker-compose up -d
```

## Health Checks

- **Application**: http://localhost/up
- **Database**: Automatic health check configured in compose.yaml

Both services have built-in health checks that ensure they're running properly before accepting traffic.
