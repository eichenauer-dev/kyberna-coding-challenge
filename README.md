# Kyberna Coding Challenge

PHP Backend Coding Challenge – Book Lending System

## Requirements

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/) v2+

No local PHP or Composer installation is required.

## Quick Start

```bash
git clone <repository-url>
cd kyberna-coding-challenge
docker compose up --build
```

The application is available at **http://localhost:8000** once the containers are running.

> **Note:** `docker-compose up --build` (with a hyphen) works the same way on systems that use the legacy Compose CLI.

On first start, the `app` container automatically:

1. Waits for MariaDB to become healthy
2. Runs `composer install`
3. Clears and warms up the Symfony cache
4. Runs pending Doctrine migrations (if any exist)
5. Starts the PHP development server on port 8000

To run in the background, add the `-d` flag:

```bash
docker compose up --build -d
```

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd kyberna-coding-challenge
```

### 2. Start the stack

```bash
docker compose up --build
```

That is all that is required. Dependencies are installed inside the container on startup — no manual `composer install` is needed.

## Environment Setup

Environment variables are managed through Symfony `.env` files and Docker Compose.

| File | Purpose |
|------|---------|
| `.env` | Base configuration (committed) |
| `.env.dev` | Development and Docker settings (committed) |
| `.env.test` | Test environment settings (committed) |
| `.env.local` | Local overrides (not committed, optional) |

### Docker configuration

`docker-compose.yml` sets only `APP_ENV=dev` for the application container. All other variables are loaded from `.env.dev` via `env_file`.

Default database credentials (defined in `.env.dev`):

| Variable | Value |
|----------|-------|
| Database | `app` |
| User | `user` |
| Password | `!ChangeMe!` |
| Host (inside Docker) | `mariadb` |
| Port | `3306` |

To override values locally, create a `.env.local` file in the project root. This file is ignored by Git.

### Changing credentials

MariaDB initializes its users only on the **first** start (when the data volume is created). If you change `MYSQL_USER` or `MYSQL_PASSWORD` in `.env.dev`, recreate the database volume:

```bash
docker compose down -v
docker compose up --build
```

## Database Setup & Migrations

### Services

| Service | Image | Exposed port |
|---------|-------|--------------|
| `app` | Custom PHP 8.4 image | `8000` |
| `mariadb` | MariaDB 11 | `3306` |

Database data is persisted in the `mariadb_data` Docker volume.

### Automatic migrations

Migrations run automatically on every container start via `docker/start.sh`. If no migration files exist yet, this step is skipped without error.

### Manual database commands

Run Symfony console commands inside the running `app` container:

```bash
# Create a new migration from entity changes
docker compose exec app php bin/console make:migration

# Run pending migrations manually
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

# Check migration status
docker compose exec app php bin/console doctrine:migrations:status
```

### Test database

In the `test` environment, Doctrine uses a separate database with the `_test` suffix (`app_test`). Create it once before running database-related tests:

```bash
docker compose exec -e APP_ENV=test app php bin/console doctrine:database:create
```

## Running the Project

### Start

```bash
docker compose up --build
```

### Stop

```bash
docker compose down
```

### View logs

```bash
docker compose logs -f app
```

### Run a Symfony console command

```bash
docker compose exec app php bin/console <command>
```

## Running Tests

Tests use PHPUnit 13 and are located in the `tests/` directory.

```bash
docker compose exec -e APP_ENV=test app composer test
```

Run a specific test file:

```bash
docker compose exec -e APP_ENV=test app php bin/phpunit tests/Unit/ExampleTest.php
```

> **Important:** Always pass `-e APP_ENV=test` when running tests in Docker. The `app` container defaults to `APP_ENV=dev`, which would otherwise load the wrong environment.

### Test structure

| Directory | Base class | Use case |
|-------------|------------|----------|
| `tests/Unit/` | `PHPUnit\Framework\TestCase` | Pure unit tests |
| `tests/Unit/` | `KernelTestCase` | Symfony kernel / service tests |
| `tests/Controller/` | `WebTestCase` | HTTP / controller tests |

## Project Structure

```
.
├── bin/                  # Symfony console & PHPUnit binaries
├── config/               # Symfony configuration
├── docker/
│   ├── Dockerfile        # PHP 8.4 application image
│   └── start.sh          # Container startup script
├── migrations/           # Doctrine migration files
├── public/               # Web root
├── src/                  # Application source code
├── tests/                # PHPUnit tests
├── docker-compose.yml
├── .env.dev              # Docker & development environment
└── phpunit.dist.xml      # PHPUnit configuration
```


