# Система управления слотами и бронированиями

## Описание проекта

Это веб-приложение для управления слотами и бронированиями, разработанное на PHP с использованием фреймворка Laravel. Проект включает систему пользователей, слотов и бронирований с возможностью управления через API.

## Архитектура проекта

Проект построен по модульному принципу и состоит из следующих основных компонентов:

- **Контроллеры** для обработки HTTP-запросов
- **Модели** для работы с данными
- **Сервисы** для реализации бизнес-логики
- **Миграции** для управления структурой базы данных
- **Тестирование** для обеспечения качества кода

## Структура проекта

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
├── [app]
│   ├── [Http]
│   │   └── [Controllers]
│   │       ├── AvailabilityController.php
│   │       ├── Controller.php
│   │       └── HoldController.php
│   ├── [Models]
│   │   ├── Hold.php
│   │   ├── Slot.php
│   │   └── User.php
│   ├── [Providers]
│   │   └── AppServiceProvider.php
│   └── [Services]
│       └── SlotService.php
...
├── [database]
│   ├── [factories]
│   │   ├── HoldFactory.php
│   │   ├── SlotFactory.php
│   │   └── UserFactory.php
│   ├── [migrations]
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_08_12_000000_create_slots_table.php
│   │   └── 2026_08_12_000001_create_holds_table.php
│   ├── [seeders]
│   │   └── DatabaseSeeder.php
│   └── .gitignore
├── [docker]
│   ├── [nginx]
│   │   └── default.conf
│   └── [php]
│       └── local.ini
...
├── [routes]
│   ├── console.php
│   └── web.php
├── [soft]
│   └── composer.phar
...
├── a.010.run.sh
├── a.build.log
├── docker-compose.yml
├── Dockerfile
├── .dockerignore
├── .env
├── .gitattributes
├── README.md
...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

## Установка и запуск

### Требования

- PHP 8.2+
- Docker
- Composer
- Node.js (для Vite)

### Процесс установки

1. Первый запуск:

```bash
git clone airstarh/biz biz
cd biz
bash a.010.run.sh
```

### Миграции и начальные данные

```bash
docker exec -it cont_va_stl_app php artisan migrate
docker exec -it cont_va_stl_app php artisan db:seed
```

2. Все последующие запуски через:

```bash
docker compose up -d
```

## Документация API

### Контроллеры

- **AvailabilityController** — управление доступностью слотов
- **HoldController** — управление бронированиями

### Модели данных

- **Hold** — модель бронирования
- **Slot** — модель слота
- **User** — модель пользователя

## Тестирование

Для запуска тестов выполните команду:

```bash
php artisan test
```

## Лицензия

Проект распространяется под лицензией MIT.

---

### Дополнительная информация

В проекте реализованы следующие ключевые функции:

- Управление пользователями
- Работа со слотами
- Система бронирования
- API для интеграции
- Автоматическое тестирование
