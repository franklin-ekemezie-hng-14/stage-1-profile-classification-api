# Stage 1 Profile Classification API

A Laravel API that accepts a name, queries multiple public classification services, persists the aggregated result, and exposes endpoints for retrieving, filtering, and deleting stored profiles.

This project was built for the HNG Backend Stage 1 task and focuses on three things:

- multi-API integration
- durable persistence
- predictable, testable API responses

## Overview

Given a name, the application calls the following upstream services:

- Genderize: predicts gender and probability
- Agify: predicts age
- Nationalize: predicts likely nationality

The application then:

- derives `age_group` from the returned age
- selects the most likely country from the Nationalize response
- stores the profile in the database
- prevents duplicate records for the same normalized name
- serves the stored data through RESTful endpoints

## Tech Stack

- PHP 8.3
- Laravel 13
- SQLite for local development
- Pest for automated testing
- GitHub Actions for CI

## API Endpoints

### Health Check

`GET /`

Returns a small JSON payload confirming the API is running.

### Create Profile

`POST /api/profiles`

Request body:

```json
{
  "name": "ella"
}
```

Successful response:

```json
{
  "status": "success",
  "data": {
    "id": "0196354c-c51f-7b79-b5d0-72245f52f001",
    "name": "ella",
    "gender": "female",
    "gender_probability": 0.99,
    "sample_size": 1234,
    "age": 46,
    "age_group": "adult",
    "country_id": "DRC",
    "country_probability": 0.85,
    "created_at": "2026-04-15T12:00:00Z"
  }
}
```

If the same name already exists, the API returns the stored record instead of creating a duplicate.

### Get Single Profile

`GET /api/profiles/{id}`

Returns a single stored profile by UUID.

### Get All Profiles

`GET /api/profiles`

Supports optional case-insensitive filters:

- `gender`
- `country_id`
- `age_group`

Example:

```text
/api/profiles?gender=male&country_id=ng&age_group=adult
```

### Delete Profile

`DELETE /api/profiles/{id}`

Deletes a stored profile and returns `204 No Content` on success.

## Classification Rules

### Age Group

- `0-12` => `child`
- `13-19` => `teenager`
- `20-59` => `adult`
- `60+` => `senior`

### Nationality

The API selects the country with the highest probability from the Nationalize response.

## Error Handling

All error responses use the same top-level format:

```json
{
  "status": "error",
  "message": "..."
}
```

Supported error conditions include:

- `400 Bad Request` for missing or empty `name`
- `422 Unprocessable Entity` for invalid request types
- `404 Not Found` for unknown profiles
- `502 Bad Gateway` for invalid or failed upstream API responses

## Idempotency

Profile creation is idempotent by name. If a profile already exists for the submitted name, the API returns the existing record rather than inserting a second one.

## Local Setup

### Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- SQLite

### Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Database

This project is configured to use SQLite by default for local development.

Create the SQLite database file if it does not already exist:

```bash
touch database/database.sqlite
```

Run migrations:

```bash
php artisan migrate
```

### Start the Application

Run the API server:

```bash
php artisan serve
```

Or use the Laravel development workflow:

```bash
composer run dev
```

## Environment Variables

Important environment values in `.env.example`:

- `DB_CONNECTION=sqlite`
- `DB_DATABASE=database/database.sqlite`
- `SESSION_DRIVER=file`
- `CACHE_STORE=file`
- `QUEUE_CONNECTION=sync`
- `GENDERIZE_API_ENDPOINT=https://api.genderize.io`
- `AGIFY_API_ENDPOINT=https://api.agify.io`
- `NATIONALIZE_API_ENDPOINT=https://api.nationalize.io`

## Testing

The project uses Pest for feature and unit tests.

Run the full test suite:

```bash
php artisan test
```

Run only the profile API tests:

```bash
php artisan test tests/Feature/ProfileApiTest.php
```

The test suite mocks all external HTTP requests. No real upstream API calls are made during automated tests.

## Continuous Integration

GitHub Actions runs the test suite automatically on every push and pull request.

The workflow is defined in:

```text
.github/workflows/ci.yml
```

## Project Structure

Key application areas:

- `app/Actions` - orchestration for profile creation
- `app/DTOs` - structured transfer objects for upstream and internal data
- `app/Enums` - domain enum values such as age groups
- `app/Exceptions` - upstream API failure and invalid data exceptions
- `app/Http/Controllers` - API controllers
- `app/Http/Resources` - response shaping
- `app/Repositories` - profile persistence abstraction
- `app/Services` - external API integrations and aggregation logic
- `database/migrations` - schema definition
- `tests/Feature/ProfileApiTest.php` - end-to-end contract coverage for the API

## Notes

- IDs are exposed as UUIDs
- timestamps are returned in ISO 8601 UTC format
- the root endpoint returns a JSON status response instead of an HTML welcome page
- local runtime defaults favor simplicity: file sessions, file cache, and sync queue processing
