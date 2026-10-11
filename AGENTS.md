# Concept Skeleton — agent notes

Short map for this repo. Sandbox (`concept-skeleton-dev-2`) is the full reference; this tree is the FormSite clone-starter.

## Role

| Layer | Here |
|-------|------|
| Glue | `bootstrap/`, `src/App/Bootstrap/`, `config/`, `routes/` |
| App surface | Controllers, FormRequests, app middleware, Twig |
| Platform | `php-concept/core-2`, `php-concept/stack`, `php-concept/extension-*` (Composer) |

**Canonical stack:** Logging, Casting, Validation, Session+CSRF, Http+FormRequests, View(Twig), Console, ErrorHandling.  
**Not in default stack:** Database, Components, Events, Telemetry, Masking, Cache.

## Bootstrap order

```
EarlyErrorHandlingBootstrap
  → FoundationBootstrap          # PathManager + Config
  → ApplicationStackBootstrap    # ConceptStack recipe
  → ApplicationRuntimeBootstrap  # timezone
```

Capability on/off = `ApplicationStackBootstrap`. Values = `config/`. URLs/middleware = `routes/web.php`.

## Sibling repos (local path installs)

| Project | Path |
|---------|------|
| Core | `/var/www/concept-core-2` |
| Stack | `/var/www/concept-stack` |
| Extensions | `/var/www/concept-extensions/extension-*` |
| Sandbox reference | `/var/www/concept-skeleton-dev-2` |

Example path repository:

```json
{
  "type": "path",
  "url": "../concept-core-2",
  "options": { "symlink": true, "versions": { "php-concept/core-2": "1.0.0" } }
}
```

CI uses Packagist/`composer.lock` — no sibling checkouts required.

## Quality gate

```bash
composer install
cp .env.example .env   # if missing
composer ci            # lint + phpstan + boot-smoke
```

- `.editorconfig` — indentation / charset
- PHP-CS-Fixer (`.php-cs-fixer.dist.php`, `@PER-CS2.0`) — `composer lint` / `lint:fix`
- PHPStan level 10 (`phpstan.neon`)
- Boot smoke: container resolves Whoops, PathManager, Config, Router; named routes `home`, `echo`, `hello`
- GitHub Actions: `.github/workflows/ci.yml` on push/PR
- Optional local pre-push: `git config core.hooksPath .githooks` (lint + phpstan)

## Conventions

- PHP 8.4, `declare(strict_types=1);`
- App namespace: `Concept\App\`
- Extensions do not read `ConfigInterface` — glue passes constructor params
- Middleware lists stay in `routes/web.php`, not in the stack
- Do not commit unless the user asks
