# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-02-05

### Added

#### CLI & Debugging Skills (2 new)
- `artisan-mastery` - PHP Artisan CLI patterns (make commands, flags, AI-friendly usage)
- `tinker-usage` - Laravel Tinker best practices (safe debugging, snippets, alternatives)

### Changed

#### Enhanced Skills (2 updated)
- `eloquent-expert` - Added Laravel 12 patterns:
  - Prohibition of `DB::` facade (use `Model::query()`)
  - `casts()` method (preferred over `$casts` property)
  - Eager loading with native `limit()` (Laravel 12)
  - Modern accessors/mutators (`Attribute::make()`)

- `laravel-best-practices` - Added:
  - `--no-interaction` flag requirement for AI agents
  - Laravel 11/12 vs Laravel 10 structure detection

### Statistics
- Skills: 48 → **50** (+2)
- Agents: 20 (unchanged)
- Workflows: 11 (unchanged)

---

## [1.1.0] - 2026-02-03

### Added

#### Security Skills (4 new)
- `secure-coding-patterns` - Secure coding for PHP/Laravel (input validation, output encoding, database security)
- `find-bugs` - Methodology for finding bugs and vulnerabilities in branches/PRs
- `pre-deploy-security` - Pre-deployment security checklist
- `api-security` - API security patterns (rate limiting, CORS, Sanctum tokens)

#### Refactoring Skills (3 new)
- `code-refactoring` - Refactoring techniques (SOLID, code smells, safe patterns)
- `codebase-cleanup` - Technical debt identification, dead code removal
- `legacy-modernization` - Strangler fig pattern, incremental upgrades

#### Queue/Jobs Skills (2 new)
- `laravel-queues` - Jobs, Workers, Horizon, batching, retry
- `job-patterns` - Saga pattern, compensation, pipelines, state machines

#### AI/LLM Skills (6 new)
- `openai-client` - OpenAI integration with Laravel HTTP client
- `anthropic-client` - Anthropic Claude integration
- `gemini-client` - Google Gemini integration
- `pgvector-search` - PostgreSQL vector search with pgvector
- `document-chunking` - Document chunking strategies for RAG
- `prompt-templates` - Prompt template patterns (few-shot, chains)

#### New Agents (2 new)
- `code-reviewer` - Code review specialist (security, quality, performance)
- `ai-engineer` - AI/LLM integration specialist (OpenAI, Anthropic, Gemini)

#### New Workflow
- `/review` - Code review workflow orchestrated by code-reviewer agent

### Changed
- Updated ARCHITECTURE.md with new statistics
- Updated README.md with accurate counts

### Statistics
- Skills: 33 → **48** (+15)
- Agents: 18 → **20** (+2)
- Workflows: 10 → **11** (+1)

---

## [1.0.0] - 2026-02-01

### Official Release
- **Initial Release**: Comprehensive AI Agent templates for Laravel development.
- **Packagist Integration**: Published as `edneymatias/antigravity-laravel-kit`.
- **Manual Installation**: Standardized via `php artisan antigravity:install`.

### Core Features
- **18 Specialist Agents**: Adapted for Laravel (Backend, Frontend, Tests, etc.).
- **33 Skills**: Including Laravel specific skills (Best Practices, Eloquent, Livewire, Pest, etc.).
- **10 Workflows**: Slash commands for creating features, testing, and deploying.
- **4 PHP Scripts**: Validation tools (`checklist.php`, `verify_all.php`).

### Laravel Ecosystem Support
- **Full Compatibility**: Laravel 11.x, Livewire 3.x, Pest 2.x/3.x.
- **Filament 4 Support**: Dedicated agents and skills for Filament Admin.
- **Localization Checker**: Native PHP script to scan for hardcoded strings.

### Key Components
- **Native Tools**: Replaced legacy Python scripts with native PHP implementations.
- **SEO Strategy**: Integrated Spatie SEO and Lighthouse auditing.
- **Frontend Audits**: Browser-based tools recommendations.
- **Documentation**: Comprehensive guides and examples (e.g., Building a CRM).

### Credits
- Based on [antigravity-kit](https://github.com/vudovn/antigravity-kit)
- Laravel context from [laravel-boost](https://github.com/laravel/boost)
