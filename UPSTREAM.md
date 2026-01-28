# Antigravity Laravel Kit v1.2.0

---

## Current Version: 1.1.0

| Source | Branch | Commit/Tag | Date |
|--------|--------|------------|------|
| [antigravity-kit](https://github.com/vudovn/antigravity-kit) | main | `dc4f3ef` | 2026-01-26 |
| [laravel-boost](https://github.com/laravel/boost) | main | `v2.0.4` (6f7a9f7) | 2026-01-28 |

> **Note:** Antigravity Kit não usa tags de release.

---

## Excluded Components

> ⚠️ **IMPORTANTE:** Estes itens foram **intencionalmente excluídos** e **NÃO devem ser importados** em atualizações futuras.

### Agents (NÃO importar)
- `game-developer` - Nicho específico
- `penetration-tester` - Requer ferramentas especializadas

### Skills (NÃO importar)
- `nodejs-best-practices` - Node.js específico
- `python-patterns` - Python específico
- `nextjs-react-expert` - React/Next.js específico
- `powershell-windows` - Windows específico
- `game-development` - Nicho específico

### Workflows (NÃO importar)
- `/ui-ux-pro-max` - Workflow avançado de design (deferido)

### Folders (NÃO importar)
- `.shared/` - Contém padrões UI de Node.js/React

### Scripts (NÃO importar)
- `*.py` - Foram substituídos por versões PHP

---

## Adaptations Made

Os seguintes componentes foram **adaptados** (não copiados diretamente):

### Agents Adaptados
- backend-specialist → Eloquent, Controllers, Jobs
- frontend-specialist → Blade, Livewire, Alpine
- database-architect → Migrations, Factories
- test-engineer → Pest, PHPUnit
- security-auditor → Sanctum, Policies
- devops-engineer → Forge, Vapor, Sail
- debugger → Telescope, Debugbar
- performance-optimizer → N+1, caching
- seo-specialist → Laravel meta, Spatie SEO
- documentation-writer → PHPDoc, Scribe

### New Laravel Skills
- laravel-best-practices
- eloquent-expert
- livewire-expert
- blade-mastery
- **filament-expert** (NEW in v1.1.0)
- pest-testing
- laravel-security
- laravel-deployment
- laravel-sail

### Scripts Reescritos (Python → PHP)
- checklist.py → checklist.php
- verify_all.py → verify_all.php
- auto_preview.py → auto_preview.php
- session_manager.py → session_manager.php

---

## Version History

| Version | Laravel Boost | Changes |
|---------|---------------|---------|
| **v1.2.0** | v2.0.4 | Ecosystem Alignment (PHP Scripts, i18n restore, Doc cleanup) |
| v1.1.0 | v2.0.4 | Filament 4 Support + Example Guide |
| 1.0.1 | v2.0.4 | Sync with laravel-boost v2.0.4 (no content changes) |
| 1.0.0 | 20c246b | Initial release |
