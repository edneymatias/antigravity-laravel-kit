# Antigravity Laravel Kit Architecture

> AI Agent templates for Laravel development with Skills, Agents, and Workflows

---

## 📋 Overview

Antigravity Laravel Kit is a modular system for AI-assisted Laravel development:

- **18 Specialist Agents** - Role-based AI personas for Laravel
- **33 Skills** - Domain-specific knowledge modules (Laravel-focused)
- **10 Workflows** - Slash command procedures

Based on the original [Antigravity Kit](https://github.com/...) adapted for the Laravel ecosystem using context from [Laravel Boost](https://laravel.com/docs/boost).

---

## 🏗️ Directory Structure

```plaintext
.agent/
├── ARCHITECTURE.md          # This file
├── agents/                  # 18 Specialist Agents
├── skills/                  # 33 Skills (adapting...)
├── workflows/               # 10 Slash Commands
├── rules/                   # Global Rules
└── scripts/                 # Validation Scripts
```

---

## 🤖 Agents (18 Active, 2 Deferred)

Specialist AI personas adapted for Laravel development.

| Agent | Focus | Adapted For |
| ----- | ----- | ----------- |
| `backend-specialist` | Laravel backend | Controllers, Eloquent, Jobs |
| `frontend-specialist` | Laravel frontend | Blade, Livewire, Alpine, Tailwind |
| `database-architect` | Database design | Eloquent, Migrations, Factories |
| `test-engineer` | Testing | Pest, PHPUnit, Dusk |
| `security-auditor` | Security | Sanctum, Policies, Gates |
| `devops-engineer` | Deployment | Forge, Vapor, Sail |
| `debugger` | Debugging | Telescope, Debugbar, Logs |
| `performance-optimizer` | Performance | N+1, Caching, Queries |
| `seo-specialist` | SEO | Meta tags, Sitemaps |
| `documentation-writer` | Documentation | Scribe, README |
| `project-planner` | Planning | Agnóstico |
| `orchestrator` | Coordination | Agnóstico |
| `product-manager` | Requirements | Agnóstico |
| `product-owner` | Strategy | Agnóstico |
| `explorer-agent` | Codebase analysis | Agnóstico |
| `code-archaeologist` | Refactoring | Agnóstico |
| `qa-automation-engineer` | QA | Agnóstico |
| `mobile-developer` | Mobile apps | RN/Flutter + Laravel API |

### Deferred Agents
- `game-developer` - Niche use case
- `penetration-tester` - Requires specialized tools

---

## 🔄 Workflows (10 Active, 1 Deferred)

Slash command procedures. Invoke with `/command`.

| Command | Description | Status |
| ------- | ----------- | ------ |
| `/create` | Create new Laravel features | Adapted |
| `/test` | Run Pest/PHPUnit tests | Adapted |
| `/deploy` | Deploy with Forge/manual | Adapted |
| `/debug` | Debug with Telescope/Logs | Adapted |
| `/preview` | Start dev server | Adapted |
| `/enhance` | Improve existing code | Adapted |
| `/status` | Check app health | Adapted |
| `/brainstorm` | Socratic discovery | Kept |
| `/plan` | Task breakdown | Kept |
| `/orchestrate` | Multi-agent coordination | Kept |

### Deferred Workflows
- `/ui-ux-pro-max` - Advanced design workflow (future)

---

## 🧩 Skills (33 Active, 3 Deferred)

Modular knowledge domains. Skills are being adapted from the original kit.

### Laravel Core
- `laravel-best-practices` - Service Providers, DI, Facades
- `eloquent-expert` - Models, Relationships, Factories
- `artisan-mastery` - Artisan commands
- `laravel-api-patterns` - API Resources, Sanctum

### Laravel Frontend
- `livewire-expert` - Livewire 3 components
- `blade-mastery` - Blade components, slots
- `inertia-expert` - Inertia + React/Vue
- `tailwind-laravel` - Tailwind with Laravel

### Testing
- `pest-testing` - Pest syntax, assertions
- `tdd-workflow` - TDD methodology

### Security & DevOps
- `laravel-security` - Sanctum, Policies
- `laravel-deployment` - Forge, Vapor
- `laravel-sail` - Docker development

### Preserved from Original
- `clean-code` - Coding standards
- `frontend-design` - UI/UX principles
- `architecture` - System design
- `brainstorming` - Socratic questioning
- And more...

---

## 📊 Statistics

| Metric | Value |
| ------ | ----- |
| **Active Agents** | 18 |
| **Deferred Agents** | 2 |
| **Active Workflows** | 10 |
| **Deferred Workflows** | 1 |
| **Skills** | 33 active, 3 deferred |
| **Focus** | Laravel/PHP development |

---

## 🔗 Quick Reference

| Need | Agent | Skills |
| ---- | ----- | ------ |
| Laravel API | `backend-specialist` | eloquent-expert, laravel-api-patterns |
| Livewire UI | `frontend-specialist` | livewire-expert, blade-mastery |
| Database | `database-architect` | eloquent-expert |
| Testing | `test-engineer` | pest-testing, tdd-workflow |
| Security | `security-auditor` | laravel-security |
| Deploy | `devops-engineer` | laravel-deployment |
| Debug | `debugger` | systematic-debugging |
| Plan | `project-planner` | brainstorming, plan-writing |

---

## 📖 About This Kit

This kit is **fully self-contained**. All Laravel knowledge is already incorporated in the agents and skills.

**Created using:**
- Laravel Boost documentation (as reference during creation)
- Official Laravel documentation
- Community best practices

**No external dependencies required.** When in doubt, use `search-docs` tool for version-specific Laravel documentation.
