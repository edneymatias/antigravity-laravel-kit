# Antigravity Laravel Kit 🚀

> AI Agent templates for Laravel development - Skills, Agents, and Workflows optimized for the Laravel ecosystem.

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat)](https://livewire.laravel.com)
[![Pest](https://img.shields.io/badge/Pest-2.x-00C49B?style=flat)](https://pestphp.com)
[![Packagist](https://img.shields.io/packagist/v/edneymatias/antigravity-laravel-kit)](https://packagist.org/packages/edneymatias/antigravity-laravel-kit)

---

## 🚀 Installation

```bash
composer require edneymatias/antigravity-laravel-kit --dev
```

Then, run the installation command:

```bash
php artisan antigravity:install
```

The `.agent` folder will be created in your project root.

### Reinstall / Update

To reinstall or update the agent files:

```bash
php artisan antigravity:install --force
```



---

### Agents (20)

| Agent | Focus |
|-------|-------|
| `backend-specialist` | Controllers, Eloquent, Jobs, APIs |
| `frontend-specialist` | Blade, Livewire, Alpine, Tailwind |
| `database-architect` | Eloquent, Migrations, Factories |
| `test-engineer` | Pest, PHPUnit, Dusk |
| `security-auditor` | Sanctum, Policies, Gates |
| `devops-engineer` | Forge, Vapor, Sail |
| `debugger` | Telescope, Debugbar, Logs |
| `code-reviewer` | Security, quality, performance reviews |
| `ai-engineer` | OpenAI, Anthropic, Gemini, RAG |
| And 11 more... | See `.agent/ARCHITECTURE.md` |

### Workflows (11)

| Command | Description |
|---------|-------------|
| `/create` | Create new Laravel features with Artisan |
| `/test` | Run Pest/PHPUnit tests |
| `/deploy` | Deploy with Forge or manually |
| `/debug` | Debug with Telescope/Debugbar |
| `/preview` | Start dev server |
| `/review` | Code review (security, quality) |
| `/brainstorm` | Socratic discovery |
| `/plan` | Task breakdown |

### Skills (50)

- **Laravel Core**: laravel-best-practices, eloquent-expert, filament-expert
- **CLI & Debug**: artisan-mastery, tinker-usage *(v1.2)*
- **Frontend**: livewire-expert, blade-mastery, tailwind-patterns
- **Testing**: pest-testing, tdd-workflow, testing-patterns
- **Security**: secure-coding-patterns, api-security, pre-deploy-security
- **Queues/Jobs**: laravel-queues, job-patterns
- **AI/LLM**: openai-client, anthropic-client, gemini-client, pgvector-search
- **Refactoring**: code-refactoring, codebase-cleanup, legacy-modernization

---

## 🚀 Quick Start

1. **Clone/Copy** the `.agent/` folder to your Laravel project
2. **Configure** your AI assistant to use the agents and skills
3. **Use workflows** with slash commands like `/create`, `/test`, `/deploy`

---

## 📁 Directory Structure

```
.agent/
├── ARCHITECTURE.md     # System overview
├── agents/             # 20 specialist agents
├── skills/             # 50 knowledge modules
├── workflows/          # 11 slash commands
├── rules/              # Global rules
└── scripts/            # Validation scripts
```

---

## 📝 Agent Usage

Invoke agents by name:

```
@backend-specialist Help me create a REST API for posts
@frontend-specialist Build a Livewire component for comments
@database-architect Design the schema for a blog
@test-engineer Write Pest tests for PostController
```

## 📚 Documentation

- **[Example: Building a CRM with Filament](docs/guide/examples/filament-crm.md)**
  Step-by-step guide to building a Customer Relationship Management system using Antigravity agents and workflows.

## 🙏 Credits & Acknowledgments

Este projeto **não existiria** sem o trabalho excepcional de dois projetos fundamentais. Todo o crédito vai para eles:

### [Antigravity Kit](https://github.com/vudovn/antigravity-kit)

O **Antigravity Kit** é a base completa deste projeto. A arquitetura modular de agents, skills e workflows, o sistema de routing inteligente, e toda a filosofia de desenvolvimento assistido por IA foram criados por este projeto incrível.

**O que utilizamos:**
- 🏗️ Arquitetura completa de `.agent/` (agents, skills, workflows)
- 🤖 8 agents mantidos integralmente (project-planner, orchestrator, etc.)
- 📚 24 skills preservados (clean-code, brainstorming, architecture, etc.)
- 🔄 3 workflows mantidos (brainstorm, plan, orchestrate)
- 📜 Scripts de validação (checklist.py, verify_all.py)
- ✨ Princípios de Design UI/UX (Deep Design Thinking, Purple Ban, etc.)

> **Sem o Antigravity Kit, este projeto simplesmente não existiria.**

---

### [Laravel Boost](https://github.com/laravel/boost)

O **Laravel Boost** forneceu todo o contexto Laravel-específico que tornou possível adaptar o Antigravity Kit para o ecossistema Laravel. As práticas, padrões e convenções do "Laravel Way" vieram diretamente deste projeto.

**O que utilizamos:**
- 📖 Contexto do "Laravel Way" (Eloquent, Form Requests, Policies)
- ⚡ Padrões Livewire 3 (wire:model.live, eventos, testing)
- 🎨 Convenções Blade (components, slots, layouts)
- 🧪 Práticas de testing com Pest
- 🔐 Padrões de segurança (Sanctum, Gates, Policies)
- 🚀 Práticas de deployment (Forge, Vapor, Sail)

> **Sem o Laravel Boost, não teríamos o conhecimento Laravel necessário para esta adaptação.**

---

### Um Trabalho de Combinação

O **Antigravity Laravel Kit** é essencialmente uma **combinação** desses dois projetos extraordinários:

```
┌─────────────────────┐     ┌─────────────────────┐
│   Antigravity Kit   │     │    Laravel Boost    │
│   ───────────────   │     │   ───────────────   │
│  • Arquitetura      │  +  │  • Laravel Way      │
│  • Agents/Skills    │     │  • Livewire/Blade   │
│  • Workflows        │     │  • Pest Testing     │
│  • Design Principles│     │  • Security/Deploy  │
└─────────────────────┘     └─────────────────────┘
            │                         │
            └────────────┬────────────┘
                         ▼
        ┌─────────────────────────────────┐
        │   Antigravity Laravel Kit       │
        │   ─────────────────────────     │
        │   AI Agent Templates for        │
        │   Laravel Development           │
        └─────────────────────────────────┘
```

**Agradecemos imensamente aos mantenedores e contribuidores de ambos os projetos.** 🎉

---

## 📄 License

MIT License - See [LICENSE](LICENSE) for details.
