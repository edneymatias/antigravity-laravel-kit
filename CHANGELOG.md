# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.2.0] - 2026-01-28

### Ecosystem Alignment & Clean-up
This release aligns the kit structure with the Laravel ecosystem, removing legacy Python dependencies in favor of native PHP tools and standard packages.

### Added
- **Native i18n Checker**: New PHP script (`.agent/skills/i18n-localization/scripts/i18n_checker.php`) that scans Blade templates AND Filament Resources (PHP) for hardcoded strings. Also validates consistency between locale files (e.g., `en.json` vs `pt_BR.json`).
- **Documentation Translations**: Fully translated `.agent/rules/GEMINI.md` to Portuguese.

### Changed
- **Script Consolidation**: Updated all Skill documentation (`clean-code`, `seo-fundamentals`, etc.) to reference `checklist.php` (Pest/Pint/Audit) instead of individual Python scripts.
- **SEO Strategy**: Updated `seo-fundamentals` skill to recommend `spatie/laravel-seo` and Lighthouse auditing instead of regex scripts.
- **Frontend Audits**: Updated `frontend-design` to recommend browser-based tools (Lighthouse/DevTools) for UX/A11y instead of backend scripts.

### Removed
- **Legacy Python Scripts**: Deleted 9 unmaintained Python scripts (`security_scan.py`, `api_validator.py`, etc.) that required external dependencies (pip/node) and duplicated functionality already present in `checklist.php`.

## [1.1.0] - 2026-01-28

### Added
- **Filament 4 Support**: New `filament-expert` skill covering Resources, Forms, Tables, Widgets, and v4-specific changes.
- **Example Guide**: New documentation [Building a CRM with Filament](docs/guide/examples/filament-crm.md).
- **Workflow Updates**:
  - `/create`: Added `php artisan make:filament-resource` command.
  - `/test`: Added Pest and Filament testing patterns (Forms, Actions, Tables).
- **Agent Updates**:
  - `backend-specialist`: Updated to recommend Filament as primary admin panel.
  - `frontend-specialist`: Added Filament recognition.
- **Skill Improvements**:
  - `laravel-best-practices`: Added Filament commands to standard workflow.
  - `laravel-security`: Added `FilamentUser` check for admin access.
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
