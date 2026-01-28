# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-01-28

### Added
- **Filament 4 Support**: New `filament-expert` skill covering Resources, Forms, Tables, Widgets, and v4-specific changes.
- Updated `backend-specialist` agent to include Filament expertise.
- Comprehensive guidelines for Filament 4 namespace migration (`Forms` -> `Schemas`).

## [1.0.1] - 2026-01-28

### Changed
- Synced with laravel-boost v2.0.4 (from 20c246b)
- No content changes in this release (upstream change was internal config only)

## [1.0.0] - 2026-01-28

### Added
- Initial release of Antigravity Laravel Kit
- 18 specialist agents (10 adapted for Laravel, 8 kept from original)
- 32 skills (8 new Laravel-specific, 24 preserved from original)
- 10 workflows (7 adapted for Laravel, 3 kept from original)
- 4 PHP scripts (rewritten from Python)
- Composer package with auto-install
- `php artisan antigravity:install` command

### Adapted Agents
- `backend-specialist` - Now uses Eloquent, Controllers, Jobs
- `frontend-specialist` - Now uses Blade, Livewire, Alpine
- `database-architect` - Now uses Migrations, Factories
- `test-engineer` - Now uses Pest, PHPUnit
- `security-auditor` - Now uses Sanctum, Policies
- `devops-engineer` - Now uses Forge, Vapor, Sail
- `debugger` - Now uses Telescope, Debugbar
- And 3 more...

### New Laravel Skills
- `laravel-best-practices`
- `eloquent-expert`
- `livewire-expert`
- `blade-mastery`
- `pest-testing`
- `laravel-security`
- `laravel-deployment`
- `laravel-sail`

### Excluded from Original
- `game-developer` agent (niche)
- `penetration-tester` agent (specialized tools)
- `/ui-ux-pro-max` workflow (deferred)
- `.shared/` folder (Node.js specific)
- Node.js/Python/React specific skills

### Credits
- Based on [antigravity-kit](https://github.com/vudovn/antigravity-kit) (commit dc4f3ef)
- Laravel context from [laravel-boost](https://github.com/laravel/boost) (commit 20c246b)
