# Job Matching API

Symfony-приложение, запущенное в Docker через [symfony-docker](https://github.com/dunglas/symfony-docker) (FrankenPHP + Caddy + PostgreSQL).

## Требования

- Docker Compose v2.10+
- macOS / Linux

## Быстрый старт

### Первая установка

```bash
make setup
```

Эта команда соберёт образы, запустит контейнеры, установит зависимости и добавит HTTPS-сертификат в доверенные.

### Открыть в браузере

```
https://localhost
```

### Linux (Ubuntu/Debian)

Если `make setup` не сработал для сертификата, выполните вручную:

```bash
docker compose exec php cat /data/caddy/pki/authorities/local/root.crt | sudo tee /usr/local/share/ca-certificates/caddy-root.crt
sudo update-ca-certificates
```

## Makefile

Доступные команды (или просто `make` для справки):

| Команда | Описание |
|---|---|
| `make setup` | Первая установка: сборка, запуск, зависимости, сертификат |
| `make build` | Собрать Docker-образы |
| `make up` | Запустить контейнеры (в фоне) |
| `make down` | Остановить контейнеры |
| `make clean` | Остановить и удалить тома (данные БД) |
| `make start` | Собрать и запустить |
| `make logs` | Логи в реальном времени |
| `make sh` | Войти в контейнер PHP (sh) |
| `make bash` | Войти в контейнер PHP (bash) |
| `make sf c=about` | Выполнить Symfony-команду |
| `make cc` | Очистить кеш |
| `make composer c='require ...'` | Выполнить composer-команду |
| `make vendor` | Установить зависимости |
| `make test` | Запустить тесты |
| `make stan` | Запустить PHPStan (статический анализ) |
| `make trust-cert` | Доверять HTTPS-сертификату Caddy (macOS) |

## Структура Docker

- **php** — FrankenPHP + Caddy (HTTPS, HTTP/3, Mercure, Vulcain)
- **database** — PostgreSQL 16 (Alpine)

## Переменные окружения

Основные переменные заданы в `.env`:

| Переменная | Значение по умолчанию |
|---|---|
| `SERVER_NAME` | `localhost` |
| `HTTP_PORT` | `80` |
| `HTTPS_PORT` | `443` |
| `POSTGRES_DB` | `app` |
| `POSTGRES_USER` | `app` |
| `POSTGRES_PASSWORD` | `!ChangeMe!` |
| `POSTGRES_VERSION` | `16` |

Для переопределения создайте `.env.local` (исключён из Git).

## Остановка

```bash
make down
```

Для полного удаления данных (включая БД):

```bash
make clean
```
