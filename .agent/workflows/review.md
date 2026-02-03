---
description: Review code for security, quality, and performance issues
---

# /review - Code Review Workflow

Review code changes on the current branch for security vulnerabilities, bugs, and quality issues.

## Prerequisites

- Git repository with changes to review
- Current branch has commits to review against main/master

## Workflow

### Step 1: Activate Code Reviewer Agent

Activate the `@code-reviewer` agent for this review session.

### Step 2: Understand Context

Ask the user (if not provided):
1. What is the purpose of these changes?
2. Is there a related issue or ticket?
3. Any specific areas of concern?

### Step 3: Get Changes Overview

```bash
# See what changed
git diff --stat main...HEAD

# List changed files
git diff --name-only main...HEAD
```

### Step 4: Get Full Diff

```bash
# Get complete diff
git diff main...HEAD
```

If diff is large, read individual files that changed.

### Step 5: Security Review (Priority 1)

Follow the `find-bugs` skill methodology:

1. **Map Attack Surface**: For each file, identify:
   - User inputs
   - Database queries
   - Auth checks
   - File operations

2. **Security Checklist** (check every item):
   - [ ] No SQL injection (raw queries with user input)
   - [ ] No XSS (`{!! !!}` with user input)
   - [ ] No mass assignment (`$request->all()`)
   - [ ] Auth middleware on protected routes
   - [ ] Policies/Gates for authorization
   - [ ] CSRF protection on forms
   - [ ] No hardcoded secrets

3. **Search for dangerous patterns**:
```bash
grep -rn "{!!" resources/views/
grep -rn "DB::raw" app/
grep -rn '$request->all()' app/
```

### Step 6: Quality Review (Priority 2)

Check:
- [ ] Code is readable
- [ ] Functions are focused
- [ ] No code duplication
- [ ] Proper error handling
- [ ] Follows Laravel conventions

### Step 7: Performance Review (Priority 3)

Check:
- [ ] No N+1 queries
- [ ] Eager loading used
- [ ] Pagination on lists
- [ ] Caching where appropriate

### Step 8: Test Coverage (Priority 4)

Check:
- [ ] New code has tests
- [ ] Tests cover edge cases
- [ ] Tests are meaningful

```bash
# Run tests
php artisan test
```

### Step 9: Compile Findings

Organize findings by priority:

| Priority | Category |
|----------|----------|
| 🔴 Critical | Security vulnerabilities |
| 🟠 High | Bugs, logic errors |
| 🟡 Medium | Performance, maintainability |
| 🟢 Low | Minor improvements |

### Step 10: Report to User

Present findings in this format:

```markdown
## Code Review Summary

**Branch:** [branch-name]
**Files Changed:** [count]
**Commits:** [count]

### 🔴 Critical Issues (Must Fix)
[List critical security/bug issues]

### 🟠 High Priority (Should Fix)
[List bugs and important issues]

### 🟡 Medium Priority (Consider)
[List performance and maintainability issues]

### ✅ Positive Notes
[What's done well]

### Recommendation
[ ] ✅ Ready to merge
[ ] ⚠️ Ready after addressing critical/high issues
[ ] ❌ Needs significant changes
```

## Notes

- Don't nitpick formatting (Pint handles this)
- Be constructive, suggest fixes
- Explain why something is an issue
- Acknowledge what's done well
