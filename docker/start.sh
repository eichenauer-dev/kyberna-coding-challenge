#!/bin/sh
set -e

cd /app

wait_for_db() {
    host="${DB_HOST:-mariadb}"
    port="${DB_PORT:-3306}"
    user="${MYSQL_USER:-user}"
    password="${MYSQL_PASSWORD:-!ChangeMe!}"
    max_attempts=30
    attempt=0

    echo "Waiting for database at ${host}:${port}..."
    while [ "$attempt" -lt "$max_attempts" ]; do
        if php -r "
            try {
                new PDO('mysql:host=${host};port=${port}', '${user}', '${password}');
                exit(0);
            } catch (PDOException \$e) {
                exit(str_contains(\$e->getMessage(), 'Access denied') ? 2 : 1);
            }
        " 2>/dev/null; then
            echo "Database is ready."
            return 0
        fi

        if [ "$?" -eq 2 ]; then
            echo "Database authentication failed for user '${user}'."
            echo "If you changed credentials, recreate the volume: docker compose down -v"
            return 1
        fi

        attempt=$((attempt + 1))
        sleep 2
    done

    echo "Database not reachable after ${max_attempts} attempts."
    return 1
}

wait_for_db

composer install --no-interaction --prefer-dist

php bin/console cache:clear --no-warmup
php bin/console cache:warmup

if php bin/console list doctrine:migrations:migrate >/dev/null 2>&1; then
    php bin/console doctrine:migrations:migrate --no-interaction
else
    echo "Doctrine Migrations not installed, skipping migrations."
fi

echo "Starting Symfony on http://localhost:8000"
exec php -S 0.0.0.0:8000 -t public
