# Docker setup (Apache2 + PHP 8.2 + MySQL + Redis)

The app runs on **Apache2 inside the container, listening on port 8000**, published to
`http://localhost:8000` on the host.

## Layout

| File | Purpose |
| --- | --- |
| `Dockerfile` | 3-stage build: Vite assets -> Composer vendor -> `php:8.2-apache` runtime |
| `docker-compose.yml` | `app` (Apache), `db` (MySQL 8), `redis` (Redis 7) |
| `docker/apache/ports.conf` | Makes Apache listen on 8000 instead of 80 |
| `docker/apache/000-default.conf` | VHost with `DocumentRoot` at `public/`, `AllowOverride All` for Laravel rewrites |
| `docker/php/php.ini` | memory/upload limits and OPcache settings |
| `docker/entrypoint.sh` | Seeds `.env`, generates `APP_KEY`, waits for MySQL, runs migrations, clears caches |
| `.env.docker` | Env template baked into the image (compose env vars override it) |

## Run

```bash
docker compose up -d --build     # build and start
curl http://localhost:8000/api/v1/ping
docker compose logs -f app       # follow logs
docker compose down              # stop (keeps data)
docker compose down -v           # stop and wipe MySQL/Redis volumes
```

## Ports

| Service | Container | Host | Note |
| --- | --- | --- | --- |
| Apache (app) | 8000 | **8000** | |
| MySQL | 3306 | 3307 | host 3306 is used by the local MySQL |
| Redis | 6379 | 6380 | host 6379 is used by the local Redis |

## Common commands

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan route:list
docker compose exec app php artisan tinker
docker compose exec db mysql -usvsschool -psecret svsschool
```

## Known gap: tables without migrations

`gym`, `gym_banners` and `techers` exist on the local host database but have **no migration
in this repo**, so a fresh container will not have them and `/hello` and `/all-techers`
will return 500. Until migrations are written, copy them from the host database:

```bash
mysqldump -uroot -p --no-tablespaces svsschool gym gym_banners techers \
  | docker compose exec -T db mysql -usvsschool -psecret svsschool
```
