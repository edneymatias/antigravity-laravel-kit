# Update Workflow

Este documento descreve o processo para atualizar o antigravity-laravel-kit quando os projetos pai recebem atualizações.

---

## 🔄 Workflow de Atualização

### 1. Verificar Mudanças nos Pais

```bash
# Antigravity Kit
cd ../antigravity-kit
git fetch origin
git log --oneline HEAD..origin/main

# Laravel Boost
cd ../laravel-boost
git fetch origin
git log --oneline HEAD..origin/main
```

### 2. Consultar UPSTREAM.md

Antes de importar qualquer mudança, **SEMPRE verifique**:
- Seção "Excluded Components" - NÃO importar estes itens
- Seção "Adaptations Made" - Estes precisam de adaptação, não cópia direta

### 3. Categorizar as Mudanças

| Tipo de Mudança | Ação |
|-----------------|------|
| Novo agent genérico (ex: code-archaeologist) | Copiar diretamente |
| Novo agent específico Node/Python | Avaliar se adaptar ou excluir |
| Correção em agent "Keep" | Copiar diretamente |
| Correção em agent "Adapt" | Adaptar manualmente |
| Novo skill genérico | Copiar diretamente |
| Novo skill Node/Python específico | NÃO importar |
| Novo workflow genérico | Copiar ou adaptar |
| Mudança em script Python | Adaptar para PHP |

### 4. Aplicar Mudanças

```bash
# Para componentes "Keep" (cópia direta)
cp ../antigravity-kit/.agent/agents/novo-agent.md .agent/agents/

# Para componentes "Adapt" (manual)
# Abrir o original, adaptar para Laravel, criar novo arquivo
```

### 5. Atualizar Documentação

1. **UPSTREAM.md** - Atualizar commits e versão
2. **CHANGELOG.md** - Adicionar entrada
3. **docs/map.md** - Se houver novos componentes
4. **composer.json** - Bump de versão

### 6. Testar

```bash
# Validar sintaxe PHP
php -l src/*.php
php -l src/Console/*.php

# Verificar estrutura
ls -la .agent/agents/ | wc -l  # Deve ter X agents
ls -la .agent/skills/ | wc -l  # Deve ter X skills
```

### 7. Commit e Release

```bash
git add .
git commit -m "chore: sync with upstream (antigravity-kit abc123, laravel-boost def456)"
git tag v1.0.1
git push origin main --tags
```

---

## 📋 Checklist de Atualização

- [ ] Verificou mudanças em antigravity-kit?
- [ ] Verificou mudanças em laravel-boost?
- [ ] Consultou UPSTREAM.md para exclusões?
- [ ] Aplicou apenas mudanças permitidas?
- [ ] Atualizou UPSTREAM.md com novos commits?
- [ ] Atualizou CHANGELOG.md?
- [ ] Bump de versão em composer.json?
- [ ] Testou a estrutura?
- [ ] Commit e tag criados?

---

## ⚠️ Armadilhas Comuns

1. **NÃO importar** game-developer, penetration-tester
2. **NÃO copiar** scripts Python - adaptar para PHP
3. **NÃO sobrescrever** agents adaptados sem verificar mudanças
4. **SEMPRE consultar** UPSTREAM.md antes de importar
