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

### Fixtures (sample data)

Doctrine Fixtures load sample books and members into the development database. Fixture classes live in `src/DataFixtures/`:

| Fixture | Data |
|---------|------|
| `BookFixtures` | 5 sample books |
| `MemberFixtures` | 3 sample members |

Load fixtures after migrations have been applied:

```bash
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction
```

> **Warning:** This command **purges all existing data** in the database before loading the fixtures.

To reset your local environment to a clean state with sample data:

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec app php bin/console doctrine:fixtures:load --no-interaction
```

Fixtures are intended for local development only and are not loaded automatically on container start.

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

## Overdue Loan Reminders

The application includes a daily reminder process for overdue loans. Instead of sending real emails, reminders are stored in the `reminder` database table and can be viewed via the API.

### Business logic

A loan is considered **overdue** when:

- `returned_at` is `NULL` (the book has not been returned yet)
- `due_at` is in the past

When the reminder process runs, it:

1. Finds all overdue loans
2. Creates one `reminder` record per overdue loan
3. Skips loans that already received a reminder on the current day (no duplicate reminders per loan per day)

Each reminder stores:

| Field | Description |
|-------|-------------|
| `loan` | Reference to the overdue loan |
| `message` | Human-readable reminder text (member name, book title, due date) |
| `created_at` | Timestamp when the reminder was created |

The processing is implemented with **Symfony Messenger**:

```
app:reminders:process (Command)
  └── dispatches ProcessOverdueLoanReminders (Message)
        └── ProcessOverdueLoanRemindersHandler
              └── creates Reminder records in the database
```

### Console command

Run the reminder process manually:

```bash
docker compose exec app php bin/console app:reminders:process
```

This command dispatches the reminder message to Symfony Messenger. The handler runs synchronously and writes any new reminders to the database.

### Cron setup

Schedule the command to run once per day, for example at 8:00 AM:

```cron
0 8 * * * cd /path/to/kyberna-coding-challenge && docker compose exec -T app php bin/console app:reminders:process
```

> **Note:** Use the `-T` flag in cron to disable TTY allocation.

## Project Structure

```
.
├── bin/                          # Symfony console & PHPUnit binaries
├── config/
│   ├── packages/                 # Bundle configuration (doctrine, messenger, validator, ...)
│   ├── routes.yaml               # Route definitions
│   └── services.yaml             # Service container configuration
├── docker/
│   ├── Dockerfile                # PHP 8.4 application image
│   └── start.sh                  # Container startup script
├── migrations/                   # Doctrine migration files
├── public/
│   └── index.php                 # Application front controller
├── src/
│   ├── Command/                  # Console commands (e.g. overdue reminders)
│   ├── Controller/
│   │   └── ApiController.php     # REST API endpoints
│   ├── DataFixtures/             # Sample data for local development
│   ├── Dto/                      # Request/query DTOs with validation
│   ├── Entity/                   # Doctrine entities (Book, Member, Loan, Reminder)
│   ├── Exception/                # Domain-specific exceptions
│   ├── Message/                  # Symfony Messenger messages
│   ├── MessageHandler/           # Symfony Messenger handlers
│   ├── Repository/               # Doctrine repositories
│   ├── Service/
│   │   └── LoanService.php       # Loan business logic
│   └── Kernel.php
├── tests/
│   ├── Unit/                     # Unit tests (e.g. LoanServiceTest)
│   └── bootstrap.php
├── docker-compose.yml
├── .env                          # Base environment configuration
├── .env.dev                      # Docker & development environment
├── .env.test                     # Test environment configuration
└── phpunit.dist.xml              # PHPUnit configuration
```


