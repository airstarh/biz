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

## API Documentation

### Overview
API для бронирования временных слотов с поддержкой идемпотентности, кэширования и защиты от оверсела.

---

### Endpoints

#### 1. GET /slots/availability
**Описание:** Получение списка доступных слотов с остатками.

**HTTP Method:** GET

**Response:**
```json
[
    {
        "id": 1,
        "capacity": 10,
        "remaining": 5
    },
    {
        "id": 2,
        "capacity": 20,
        "remaining": 15
    }
]
```

**Особенности:**
* Кэширование на 5-15 секунд
* Защита от cache stampede
* Автоматическая инвалидация при изменении данных

---

#### 2. POST /slots/{id}/hold
**Описание:** Создание холда на слот.

**HTTP Method:** POST

**Headers:**
```
Idempotency-Key: <UUID>
```

**Response:**
```json
{
    "id": 1,
    "slot_id": 1,
    "status": "held",
    "created_at": "2026-08-14T10:53:45"
}
```

**Возможные ошибки:**
* 404 Not Found - слот не найден
* 409 Conflict - ёмкость исчерпана
* 429 Too Many Requests - превышение лимита запросов

---

#### 3. POST /holds/{id}/confirm
**Описание:** Подтверждение холда.

**HTTP Method:** POST

**Response:**
```json
{
    "id": 1,
    "slot_id": 1,
    "status": "confirmed",
    "created_at": "2026-08-14T10:53:45"
}
```

**Особенности:**
* Атомарное уменьшение остатка
* Инвалидация кэша
* Проверка статуса холда

---

#### 4. DELETE /holds/{id}
**Описание:** Отмена холда.

**HTTP Method:** DELETE

**Response:**
```json
{
    "id": 1,
    "slot_id": 1,
    "status": "cancelled",
    "created_at": "2026-08-14T10:53:45"
}
```

**Особенности:**
* Возврат места в слот
* Инвалидация кэша
* Проверка времени жизни холда (5 минут)

---

### Общие требования

**Заголовки:**
* `Idempotency-Key` обязателен для POST-запросов
* `Content-Type: application/json`

**Статусы ответов:**
* 200 OK - успешный запрос
* 400 Bad Request - неверные параметры
* 401 Unauthorized - нет прав доступа
* 403 Forbidden - запрещенный доступ
* 404 Not Found - ресурс не найден
* 409 Conflict - конфликт при обработке
* 500 Internal Server Error - внутренняя ошибка

**Безопасность:**
* Все запросы должны быть защищены от SQL-инъекций
* Валидация всех входных параметров
* Обработка конкурентных запросов

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
