# Antigravity Laravel Kit Architecture

> AI Agent templates for Laravel development with Skills, Agents, and Workflows

---

## 📋 Overview

Antigravity Laravel Kit is a modular system for AI-assisted Laravel development:

- **20 Specialist Agents** - Role-based AI personas for Laravel
- **50 Skills** - Domain-specific knowledge modules (Laravel-focused)
- **11 Workflows** - Slash command procedures

Based on the original [Antigravity Kit](https://github.com/vudovn/antigravity-kit) adapted for the Laravel ecosystem.

---

## 🏗️ Directory Structure

```plaintext
.agent/
├── ARCHITECTURE.md          # This file
├── agents/                  # 20 Specialist Agents
├── skills/                  # 50 Skills
├── workflows/               # 11 Slash Commands
├── rules/                   # Global Rules
└── scripts/                 # Validation Scripts (PHP)
```

---

## 🤖 Agents (20 Active)

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
| `project-planner` | Planning | Agnostic |
| `orchestrator` | Coordination | Agnostic |
| `product-manager` | Requirements | Agnostic |
| `product-owner` | Strategy | Agnostic |
| `explorer-agent` | Codebase analysis | Agnostic |
| `code-archaeologist` | Refactoring | Agnostic |
| `qa-automation-engineer` | QA | Agnostic |
| `mobile-developer` | Mobile apps | RN/Flutter + Laravel API |
| `code-reviewer` | Code review | Security, quality, performance |
| `ai-engineer` | AI/LLM | OpenAI, Anthropic, Gemini, RAG |

---

## 🔄 Workflows (11 Active)

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
| `/review` | Code review (security, quality) | **New v1.1** |
| `/brainstorm` | Socratic discovery | Kept |
| `/plan` | Task breakdown | Kept |
| `/orchestrate` | Multi-agent coordination | Kept |

---

## 🧩 Skills (50 Active)

Modular knowledge domains organized by category.

### Laravel Core
- `laravel-best-practices` - Service Providers, DI, Facades, L11/12 structure
- `eloquent-expert` - Models, Relationships, Factories, L12 patterns
- `filament-expert` - Filament 4 admin panels
- `laravel-queues` - Jobs, Workers, Horizon

### CLI & Debugging (v1.2)
- `artisan-mastery` - Make commands, flags, AI-friendly usage
- `tinker-usage` - Safe debugging, snippets, alternatives

### Laravel Frontend
- `livewire-expert` - Livewire 3 components
- `blade-mastery` - Blade components, slots
- `tailwind-patterns` - Tailwind CSS v4

### Testing
- `pest-testing` - Pest syntax, assertions
- `tdd-workflow` - TDD methodology
- `testing-patterns` - Unit, integration, mocking

### Security (v1.1)
- `secure-coding-patterns` - Input validation, output encoding
- `find-bugs` - Bug hunting methodology
- `pre-deploy-security` - Pre-deployment checklist
- `api-security` - Rate limiting, CORS, Sanctum
- `laravel-security` - Policies, Gates, built-in protections
- `vulnerability-scanner` - OWASP, supply chain security

### Queues/Jobs (v1.1)
- `laravel-queues` - Jobs, Workers, Horizon, batching
- `job-patterns` - Saga, compensation, pipelines

### AI/LLM Integration (v1.1)
- `openai-client` - OpenAI with Laravel HTTP client
- `anthropic-client` - Anthropic Claude integration
- `gemini-client` - Google Gemini integration
- `pgvector-search` - PostgreSQL vector search
- `document-chunking` - Chunking strategies for RAG
- `prompt-templates` - Prompt engineering patterns

### Refactoring (v1.1)
- `code-refactoring` - SOLID, code smells, safe patterns
- `codebase-cleanup` - Tech debt, dead code removal
- `legacy-modernization` - Strangler fig, upgrades

### DevOps
- `laravel-deployment` - Forge, Vapor
- `laravel-sail` - Docker development
- `deployment-procedures` - Safe deployment workflows

### Architecture & Planning
- `architecture` - System design decisions
- `api-patterns` - REST, response formats
- `database-design` - Schema, indexing
- `brainstorming` - Socratic questioning
- `plan-writing` - Task breakdown

### General Development
- `clean-code` - Coding standards
- `frontend-design` - UI/UX principles
- `documentation-templates` - README, API docs
- And more...

---

## 📊 Statistics

| Metric | v1.0 | v1.1 | v1.2 |
| ------ | ---- | ---- | ---- |
| **Agents** | 18 | 20 | **20** |
| **Skills** | 33 | 48 | **50** |
| **Workflows** | 10 | 11 | **11** |
| **Focus** | Laravel/PHP | Laravel/PHP + AI | Laravel 12 + CLI |

### What's New in v1.2

| Category | New Skills | Enhanced |
|----------|------------|----------|
| CLI & Debug | 2 (`artisan-mastery`, `tinker-usage`) | - |
| Laravel Core | - | 2 (`eloquent-expert`, `laravel-best-practices`) |
| **Total** | **+2** | **2 enhanced** |

---

## 🔗 Quick Reference

| Need | Agent | Skills |
| ---- | ----- | ------ |
| Laravel API | `backend-specialist` | eloquent-expert, api-patterns |
| Livewire UI | `frontend-specialist` | livewire-expert, blade-mastery |
| Database | `database-architect` | eloquent-expert, database-design |
| Testing | `test-engineer` | pest-testing, tdd-workflow |
| Security | `security-auditor` | secure-coding-patterns, api-security |
| Code Review | `code-reviewer` | find-bugs, pre-deploy-security |
| AI Features | `ai-engineer` | openai-client, pgvector-search |
| Queues | `backend-specialist` | laravel-queues, job-patterns |
| Deploy | `devops-engineer` | laravel-deployment |
| Debug | `debugger` | systematic-debugging |
| Plan | `project-planner` | brainstorming, plan-writing |

---

## 🛠️ Scripts

Native PHP scripts for automation and validation.

| Script | Description |
| ------ | ----------- |
| `checklist.php` | Full validation (Security, Lint, Test, DB) |
| `verify_all.php` | Pre-deploy verification |
| `session_manager.php` | Session context management |
| `auto_preview.php` | Automatic preview server |
| `i18n_checker.php` | Hardcoded string scanner |

---

## 📖 About This Kit

This kit is **fully self-contained**. All Laravel knowledge is already incorporated in the agents and skills.

**Created using:**
- Laravel Boost documentation (as reference during creation)
- Official Laravel documentation
- Community best practices

**No external dependencies required.** When in doubt, use `search-docs` tool for version-specific Laravel documentation.
