# SVS Schools — Open REST API (v1)

A public, **unauthenticated** REST API. Every endpoint is scoped by a `user_id`
and every read is served through **Redis**.

## Base URL

```
http://127.0.0.1:8000/api/v1
```

The dev server is started with `php artisan serve`. If you serve the app through
Apache/nginx instead, the base URL is `<your-host>/api/v1` (document root must be
`svsbackend/public`).

## Endpoints

| Method | Path | Purpose |
|--------|------|---------|
| `GET` | `/ping` | Health check |
| `GET` | `/users/{user_id}/records` | The long dataset, paginated + filterable |
| `GET` | `/users/{user_id}/records/summary` | Aggregates for dashboards |
| `DELETE` | `/users/{user_id}/cache` | Drop all cached Redis entries for that user |

No API key, token or login is required. Requests are throttled to
**120 per minute per IP** (`X-RateLimit-*` headers are returned).

---

## `GET /users/{user_id}/records`

Returns demo student records belonging to `user_id`.

### Query parameters

| Param | Type | Default | Notes |
|-------|------|---------|-------|
| `page` | int | `1` | Page number |
| `per_page` | int | `50` | Max `1000` |
| `standard` | string | – | e.g. `10` |
| `division` | string | – | `A`–`D` |
| `status` | enum | – | `active` \| `inactive` \| `alumni` |
| `grade` | string | – | `A+`, `A`, `B+`, `B`, `C`, `D`, `F` |
| `gender` | enum | – | `male` \| `female` \| `other` |
| `min_percentage` | float | – | `0`–`100` |
| `search` | string | – | Matches name, roll no or email |
| `sort_by` | enum | `id` | `id`, `student_name`, `percentage`, `attendance_percent`, `fees_due`, `admission_date` |
| `sort_dir` | enum | `asc` | `asc` \| `desc` |

### Examples

```bash
# First 50 records for user 1
curl "http://127.0.0.1:8000/api/v1/users/1/records"

# Full 1000-row page (~519 KB)
curl "http://127.0.0.1:8000/api/v1/users/1/records?per_page=1000"

# Top scorers in standard 10 who are still active
curl "http://127.0.0.1:8000/api/v1/users/1/records?standard=10&status=active&sort_by=percentage&sort_dir=desc&per_page=10"

# Search
curl "http://127.0.0.1:8000/api/v1/users/1/records?search=Nilima"
```

### Response

```json
{
  "success": true,
  "user_id": 1,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "roll_no": "SVS-1-00001",
      "student_name": "Nilima Nagarajan",
      "email": "nilima1@svsdemo.test",
      "phone": "9874461119",
      "standard": "10",
      "division": "C",
      "gender": "male",
      "dob": "2009-10-15",
      "city": "Indore",
      "attendance_percent": 91,
      "marks_math": 79,
      "marks_science": 38,
      "marks_english": 53,
      "total_marks": 170,
      "percentage": 56.67,
      "grade": "C",
      "fees_total": 32000,
      "fees_paid": 16000,
      "fees_due": 16000,
      "status": "active",
      "admission_date": "2024-06-23",
      "created_at": "2026-09-21T14:18:17.000000Z",
      "updated_at": "2026-09-21T14:18:17.000000Z"
    }
  ],
  "pagination": {
    "total": 1000,
    "per_page": 50,
    "current_page": 1,
    "last_page": 20,
    "from": 1,
    "to": 50,
    "has_more": true
  },
  "meta": {
    "cache": {
      "store": "redis",
      "hit": false,
      "key": "svs:v1:user:1:records:9274f6055a5f130f974b63c98f13491a",
      "ttl_seconds": 300
    },
    "filters": { "page": 1, "per_page": 50, "sort_by": "id", "sort_dir": "asc" },
    "response_time_ms": 56.49,
    "generated_at": "2026-09-21T14:18:17+00:00"
  }
}
```

`meta.cache.hit` is `false` when the page was built from MySQL and `true` when it
came straight out of Redis.

---

## `GET /users/{user_id}/records/summary`

```bash
curl "http://127.0.0.1:8000/api/v1/users/1/records/summary"
```

```json
{
  "success": true,
  "user_id": 1,
  "totals": {
    "total_records": 1000,
    "avg_percentage": 63.71,
    "avg_attendance": 72.08,
    "fees_total": 40049000,
    "fees_paid": 27710250,
    "fees_due": 12338750
  },
  "by_standard": { "1": 83, "2": 99, "...": 0 },
  "by_status":   { "active": 585, "inactive": 201, "alumni": 214 },
  "by_grade":    { "A+": 14, "A": 96, "B+": 220, "B": 281, "C": 235, "D": 126, "F": 28 },
  "by_gender":   { "male": 373, "female": 297, "other": 330 },
  "meta": { "...": "same shape as above" }
}
```

---

## `DELETE /users/{user_id}/cache`

```bash
curl -X DELETE "http://127.0.0.1:8000/api/v1/users/1/cache"
```

```json
{ "success": true, "message": "Redis cache cleared for user #1.", "user_id": 1 }
```

Only that user's entries are removed — other users keep their cached pages.

---

## Errors

All errors return JSON (never an HTML error page).

| Status | When | Body |
|--------|------|------|
| `404` | Unknown or non-numeric `user_id` | `{"success":false,"error":"user_not_found","message":"..."}` |
| `422` | Invalid query parameter | `{"message":"...","errors":{"per_page":["..."]}}` |
| `429` | More than 120 requests/minute | Standard Laravel throttle body |

---

## How the Redis layer works

* The API calls `Cache::store('redis')` **explicitly**, so it does not change the
  app's default cache store (still `database` — existing pages are unaffected).
* Entries are **tagged per user** (`svs:user:{id}`), which is what makes
  `DELETE /users/{id}/cache` able to clear one user without touching others.
* Cache keys are deterministic — the same user with the same filters always maps
  to the same key: `svs:v1:user:{id}:records:{md5-of-filters}`.
* TTL: **300s** for record pages, **600s** for the summary.
* Redis connection: the `cache` connection from `config/database.php`
  (`REDIS_CACHE_DB`, database **1** by default).

Inspect the live keys:

```bash
redis-cli -n 1 --scan --pattern '*svs*'
redis-cli -n 1 TTL '<full-key-name>'
```

---

## Setup / regenerating the demo data

```bash
php artisan migrate
php artisan db:seed --class=StudentRecordSeeder
```

The seeder creates **1000 records per existing user** (2000 rows for the two
current users). It deletes that user's old rows first, so it is safe to re-run.
Change the volume with `DEMO_RECORDS_PER_USER` in `.env`:

```env
DEMO_RECORDS_PER_USER=5000
```

Requirements: a running Redis (`redis-cli ping` → `PONG`) and the `phpredis`
PHP extension, both already present on this machine.

---

## Files

| File | Role |
|------|------|
| `routes/api.php` | Route definitions |
| `bootstrap/app.php` | Registers `api.php` + forces JSON errors on `api/*` |
| `app/Http/Controllers/Api/DatasetController.php` | Endpoints + Redis caching |
| `app/Models/StudentRecord.php` | Eloquent model |
| `database/migrations/2026_09_21_120000_create_student_records_table.php` | Schema |
| `database/seeders/StudentRecordSeeder.php` | Demo data generator |
