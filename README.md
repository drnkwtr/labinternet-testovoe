# Landing Contact API

Backend-сервис формы обратной связи лендинга: приём заявки → валидация → очередь → email-уведомления (владельцу и отправителю) с AI-цитатой по смыслу обращения.

- API-документация (Swagger UI): `http://localhost:8080/api/documentation`
- OpenAPI JSON: `http://localhost:8080/docs`

---

## 1. Как запустить проект

### Требования
- Docker + Docker Compose (всё крутится в контейнерах, локальные PHP/Composer не нужны).

### Быстрый старт
```bash
cp .env.example .env          # значения по умолчанию сразу рабочие
docker compose up -d --wait   # nginx + postgres + redis + app
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan l5-swagger:generate
```

Либо через Makefile (после `cp .env.example .env`):
```bash
make setup
```

API поднимется на `http://localhost:8080`.

### Фронтенд (лендинг)

Лендинг-презентация с формой обратной связи собран на **Vue 3 + Vite** и встроен прямо в приложение (`resources/js`, отдаётся blade-шаблоном `landing`). Он ходит в API с того же origin (`/api/v1`), поэтому CORS для него не нужен.

Тулинг Vite выполняется **на хосте** (нужен Node.js 20+): каталог проекта монтируется в контейнер как volume, а нативные бинарники Vite несовместимы между Alpine (musl) и хостом (glibc), поэтому node-команды не запускаем внутри контейнера.

```bash
make front-install    # npm ci — поставить зависимости (один раз)
make front-build      # npm run build — собрать ассеты в public/build
```

После `make front-build` лендинг открывается на `http://localhost:8080/`.

**Dev-режим с hot-reload** (Vite dev-сервер + HMR):
```bash
make front-dev        # npm run dev, слушает http://localhost:5173
```
HTML по-прежнему отдаёт nginx на `:8080`, а ассеты — dev-сервер Vite на `:5173` (laravel-vite-plugin создаёт файл `hot` и переключает `@vite` на него). Держите обе вкладки процесса открытыми: `docker compose` для API и `make front-dev` для фронта. Останов dev-сервера — `Ctrl+C`, после чего снова работает собранная версия из `public/build`.

### Настройка переменных окружения (`.env`)
| Переменная | Назначение | Дефолт |
|---|---|---|
| `APP_PORT` | HTTP-порт nginx на хосте | `8080` |
| `DB_HOST_PORT` / `REDIS_HOST_PORT` | host-порты БД/Redis (сменить, если 5432/6379 заняты) | `5432` / `6379` |
| `QUEUE_CONNECTION` | драйвер очереди | `sync` |
| `CACHE_STORE` | хранилище кеша (rate limit) | `redis` |
| `MAIL_MAILER` | почтовый драйвер (`log` — письма пишутся в файл) | `log` |
| `CONTACT_OWNER_EMAIL` | адрес владельца для уведомлений | `owner@example.com` |
| `CONTACT_RATE_LIMIT_MAX` / `CONTACT_RATE_LIMIT_DECAY` | лимит: запросов / за сколько секунд | `5` / `60` |
| `OPENAI_API_KEY` | ключ OpenAI (пустой → сразу heuristic fallback) | пусто |
| `OPENAI_MODEL` | модель | `gpt-4o-mini` |
| `CORS_ALLOWED_ORIGINS` | разрешённые origin фронта (через запятую) | `http://localhost:3000,http://localhost:5173` |

### Команды (Makefile)
`make up` / `down` / `restart` · `make migrate` · `make swagger` · `make test` · `make pint` · `make logs` · `make exec` (шелл в контейнере).
Фронтенд: `make front-install` · `make front-dev` · `make front-build`.

---

## 2. Стек технологий

**Backend**
- PHP 8.4, Laravel 13
- PostgreSQL 16 — хранение обращений
- Redis 7 — кеш и счётчик rate limiting
- Nginx (Brotli) + PHP-FPM
- Composer

**Frontend**
- Vue 3 (`<script setup>`) + Vite 8 — лендинг с формой обратной связи.
- Tailwind v4 подключён, но лендинг свёрстан на собственных CSS-токенах (scoped-стили в SFC).
- Шрифты self-hosted через `laravel-vite-plugin/fonts` (Space Grotesk, IBM Plex Mono, Instrument Sans).

**AI**
- OpenAI Chat Completions API (`gpt-4o-mini`) — генерация тематической цитаты по комментарию.

**Библиотеки**
- `darkaonline/l5-swagger` (+ `zircote/swagger-php`) — OpenAPI/Swagger из PHP-атрибутов.
- HTTP-клиент Laravel (Guzzle) — вызов OpenAI.

---

## 3. Архитектура

### Слои
```
Controller → (Request → DTO) → Service / Job (Handler) → Repository → Model
                                        ↘ Resource ← DTO
```
- **Controller** — тонкий, только приём запроса и формирование ответа.
- **FormRequest** — валидация + санитизация, `toDto()` собирает immutable DTO.
- **Service** — бизнес-логика (`ContactQuoteService`, `ContactNotifier`, `MetricsService`, `HealthService`).
- **Job** — `ProcessContactSubmissionJob` оркестрирует поток заявки в очереди.
- **Repository** — доступ к данным поверх Eloquent, наружу отдаёт DTO.
- **DTO** — неизменяемые объекты передачи данных; `Resource` рендерит их в JSON.

### Структура проекта
```
app/
├── Core/                      # переиспользуемое ядро (по образцу core-модуля)
│   ├── DTO/Models/            # AbstractModelDTO, ModelDTOInterface
│   ├── Http/Requests/         # AbstractRequest, RequestInterface, RequestDTOInterface
│   ├── Http/Resources/        # AbstractResource
│   ├── Http/Controllers/      # ApiController
│   └── Repositories/          # AbstractRepository, MappableInterface
├── DTO/                       # Contact / Ai / Metrics / Health
├── Http/
│   ├── Controllers/Api/V1/    # Contact, Health, Metrics
│   ├── Requests/              # StoreContactRequest, ...
│   ├── Resources/             # ContactResource, ContactMetricsResource
│   └── Middleware/ApiLogger   # логирование запросов в файл
├── Jobs/ProcessContactSubmissionJob
├── Mail/                      # ContactReceivedOwnerMail, ContactCopyUserMail
├── Repositories/ContactRepository
├── Services/                  # Ai / Contact / Metrics / Health
└── Types/QuoteSource
```

### Паттерны
- **Repository** — изоляция доступа к данным за интерфейсом `AbstractRepository`.
- **DTO + Mapper** — данные ходят между слоями неизменяемыми объектами (`MappableInterface::map()`), а не массивами/моделями.
- **Service layer / Handler** — бизнес-логика вне контроллеров; тяжёлая обработка вынесена в Job.
- **Strategy + graceful degradation** — `QuoteGenerator` (OpenAI / heuristic) за общим интерфейсом, `ContactQuoteService` переключает стратегии.

### Обоснование выбора
- **Laravel** — быстрый REST, встроенные валидация/очереди/почта/rate limiting, зрелая экосистема.
- **Ядро `App\Core`** — общие базовые классы (Repository/DTO/Request/Resource) для единообразия (в более крупном проекте круто сочетается с модульным монолитом).
- **Очередь `sync`** — по ТЗ достаточно; код при этом уже «queue-ready» — смена драйвера включает фоновую обработку без изменения логики.
- **Postgres + Redis** — показать работу с БД (обращения) и in-memory-кешем (rate limit); при этом логи пишутся в файл.

---

## 4. Реализация API

Базовый префикс — `/api`, версия — `/v1`.

### `POST /api/v1/contact`
Приём заявки. Валидация → постановка в очередь (`204 No Content`). Обработка (сохранение, AI, письма) идёт в Job.

**Тело запроса**
| Поле | Правила |
|---|---|
| `name` | required, string, 2–100 |
| `phone` | required, string, 5–20, `^\+?[0-9\s\-()]+$` |
| `email` | required, email(rfc), ≤255 |
| `comment` | required, string, 5–2000 |

**Запрос**
```bash
curl -i -X POST http://localhost:8080/api/v1/contact \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{
    "name": "Иван Иванов",
    "phone": "+7 900 123-45-67",
    "email": "ivan@example.com",
    "comment": "Здравствуйте, хочу обсудить проект."
  }'
```

**204 No Content** — успех, тело ответа пустое.

### `GET /api/v1/health`
Статус сервиса и зависимостей (БД, кеш). `200` — всё ок, `503` — деградация.
```bash
curl http://localhost:8080/api/v1/health -H "Accept: application/json"
```
```json
{ "status": "ok", "checks": { "database": true, "cache": true }, "timestamp": "2026-07-16T16:01:49+00:00" }
```

### `GET /api/v1/metrics`
Агрегированная статистика обращений (из БД).
```bash
curl http://localhost:8080/api/v1/metrics -H "Accept: application/json"
```
```json
{ "total": 28, "today": 28, "last_7_days": 28, "last_contact_at": "2026-07-16T15:50:01.000000Z" }
```

### Валидация и обработка ошибок
Глобальный обработчик (`bootstrap/app.php`) для `api/*` всегда отдаёт JSON.

| Статус | Когда | Тело |
|---|---|---|
| `422` | ошибка валидации | `{ "message": "...", "errors": { "email": ["..."] } }` |
| `429` | превышен лимит (анти-спам) | `{ "message": "Too Many Attempts." }` + заголовки `Retry-After`, `X-RateLimit-*` |
| `404` | нет роута/ресурса | `{ "message": "Запрашиваемый ресурс не найден." }` |
| `405` | неверный метод | `{ "message": "..." }` |

Пример `422`:
```json
{
  "message": "The email field must be a valid email address. (and 1 more error)",
  "errors": {
    "email": ["The email field must be a valid email address."],
    "comment": ["The comment field must be at least 5 characters."]
  }
}
```

Примеры запросов также лежат в Postman-коллекции: [`docs/postman_collection.json`](docs/postman_collection.json).

---

## 5. AI-интеграция

**Что делает.** По тексту `comment` подбирается короткая тематическая цитата, которая добавляется в оба письма (владельцу и отправителю).

**Провайдер.** OpenAI Chat Completions (`gpt-4o-mini`) — `App\Services\Ai\OpenAiQuoteGenerator`.

**Промпт (system).**
```
Ты подбираешь одну короткую вдохновляющую цитату на русском языке,
уместную по смыслу комментария пользователя из формы обратной связи.
Ответь только текстом цитаты и автором в скобках, без кавычек и пояснений.
Не более 200 символов.
```
User-сообщение — сам текст комментария.

**Graceful fallback.** `ContactQuoteService` вызывает OpenAI, и при **любой** недоступности (нет ключа, `401`, отсутствие квоты, rate limit, сетевая ошибка, пустой ответ) ловит исключение, пишет `WARNING` в лог и переключается на офлайн-эвристику `HeuristicQuoteGenerator`. Сервис всегда возвращает цитату — заявка не ломается.

**Эвристика (3 варианта)** — по ключевым словам в комментарии:
1. проблема/жалоба (`проблем`, `помощ`, `не работает`, `баг`, …) — цитата про преодоление;
2. сотрудничество/работа (`проект`, `сотруднич`, `вакан`, …) — про действие;
3. иначе — общая вдохновляющая цитата.

Источник цитаты (`openai` / `fallback`) фиксируется в DTO и выводится в письме владельцу.

---

## 6. Что сделано с помощью AI

Проект написан в паре с AI-ассистентом (Claude Code, Opus).

**Что генерировалось с помощью AI**
- Архитектура, слои и концепция целиком на разработчике. 
- Слой-ядро `App\Core` (AbstractRepository / DTO / Request / Resource) по образцу существующего core-модуля в проекте стартапа (модульный монолит).
- Большая часть кода по указанной архитектуре: Request → DTO → Job → Repository → Mailable, rate limiting, логирование, глобальный error handler.
- AI-слой: OpenAI-генератор, эвристический fallback, оркестратор.
- Весь фронтенд

**Примеры промптов**
- «Перенеси конфиги докера, nginx и Makefile в текущий проект, возьми из проекта стартапа как референс, лишнее вырежи».
- «Реализуй эндпоинт по образцу core-модуля: Request→toDto→DTO→Repository→Resource, отправку формы в очередь через Job».
- «AI как OpenAI, при недоступности — эвристический fallback по ключевым словам, цитату в письмо».

**Что правилось вручную (после проверки)**
- Правки по желаемой архитектуре, упрощение перемудренного кода и т.д.
- Rate limiter: у `Limit` в Laravel 13 параметр `decaySeconds`, а не `decayMinutes`.
- Эвристика: ключ `работ` ложно матчил «не рабо**тает**» → issue-ветку вынесли выше collab.
- Health-check кеша: Redis отдаёт числа строкой (`"1"`), строгий `=== 1` давал ложный false.
- Обработка ошибки доставки письма: под очередью `try/catch` в диспатчере не ловит сбой доставки → перенесли в хук `Mailable::failed()`. Может быть перенесено в кастомную Джобу, но исходя из ТЗ, смысла мало.
---

## 7. Хранение данных

| Что | Где | Детали |
|---|---|---|
| **Обращения** | PostgreSQL, таблица `contacts` | через `ContactRepository`; поля `name/phone/email/comment` + метаданные `ip_address/user_agent` |
| **Логи запросов** | файл `storage/logs/requests.log` | канал `requests` + middleware `ApiLogger` (method, path, status, ip, user-agent) |
| **Логи приложения / письма** | файл `storage/logs/laravel.log` | при `MAIL_MAILER=log` письма пишутся сюда |
| **Rate limiting** | Redis | счётчик по IP через `RateLimiter` (`throttle:contact`), лимит из `config/contact.php` |
| **Статистика** | PostgreSQL (агрегация) | `MetricsService` считает `total / today / last_7_days / last_contact_at` из `contacts` |

По ТЗ достаточно файлового хранилища — здесь дополнительно показана работа с БД (обращения, статистика) и Redis (rate limit), при этом логирование остаётся файловым.
