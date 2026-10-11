# PHP Concept Skeleton

FormSite-level starter: thin Core + opt-in Extensions via `ConceptStack`. No Database or Components by default.

## Requirements

- PHP 8.4+
- Composer 2

## Setup

```bash
composer install
cp .env.example .env
composer ci
```

Point the web server (or `public/` docroot) at `public/`.

### Git hooks (optional, local)

```bash
git config core.hooksPath .githooks
```

`pre-push` runs `composer lint` + `composer phpstan`.

## Commands

| Command | Purpose |
|---------|---------|
| `composer ci` | CS Fixer dry-run + PHPStan + boot smoke |
| `composer lint` | PHP-CS-Fixer dry-run |
| `composer lint:fix` | Apply CS Fixer |
| `composer phpstan` | Static analysis (level 10) |
| `composer boot-smoke` | Bootstrap + PathManager + Config + Router + routes |
| `composer console` | Symfony Console (`route:list`, …) |

## Manual acceptance

1. `GET /` — promo + form
2. Empty `POST /echo` — validation errors + old input
3. Valid submit — success flash on `/#try-it`
4. `GET /hello/Ada` — route param in Twig
5. `GET /missing` — 404 (Whoops when `APP_DEBUG=true`)

## Local sibling packages

Published packages install from Packagist. For day-to-day work against local clones, add path repositories (symlink) for:

| Package | Typical path |
|---------|----------------|
| `php-concept/core-2` | `../concept-core-2` |
| `php-concept/stack` | `../concept-stack` |
| `php-concept/extension-*` | `../concept-extensions/extension-*` |

See `AGENTS.md` for the short agent-oriented map.

## Layout

- `bootstrap/` — App entry + path map
- `src/App/Bootstrap/` — Early Whoops → Foundation → Stack → Runtime
- `routes/web.php` — HTTP surface + middleware
- `config/` — data-only values (glue passes them into the stack)
