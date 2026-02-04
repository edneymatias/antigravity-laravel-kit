---
name: find-bugs
description: Methodology for finding bugs, security vulnerabilities, and code issues in branches/PRs. Use when reviewing changes, conducting security reviews, or auditing code.
---

# Find Bugs

> Systematic approach to find bugs, security vulnerabilities, and code quality issues.

## When to Use

- Reviewing pull requests
- Conducting security reviews
- Auditing code changes before deploy
- Finding bugs in feature branches

---

## Phase 1: Input Gathering

### Get the Full Diff

```bash
# Get diff against main branch
git diff $(git symbolic-ref refs/remotes/origin/HEAD | sed 's@^refs/remotes/origin/@@')...HEAD

# Or specific branch
git diff main...HEAD

# List changed files
git diff --name-only main...HEAD
```

### Understand Context

- What problem is being solved?
- What files were changed?
- Is there a related issue/ticket?
- What's the expected behavior?

---

## Phase 2: Attack Surface Mapping

For each changed file, identify:

| Category | What to Find |
|----------|--------------|
| **User Inputs** | Request params, body, headers, URL components |
| **Database Queries** | Eloquent, raw queries, whereRaw |
| **Auth Checks** | Middleware, $this->authorize(), @can |
| **Session/State** | Session writes, cache operations |
| **External Calls** | HTTP clients, API calls, webhooks |
| **File Operations** | Uploads, downloads, path handling |

### Laravel-Specific Patterns to Flag

```php
// Flag these for review:
{!! $variable !!}           // Raw output - XSS risk
DB::raw($userInput)         // Raw SQL - injection risk
$request->all()             // Mass assignment risk
Storage::path($userInput)   // Path traversal risk
```

---

## Phase 3: Security Checklist

Check EVERY item for EVERY changed file:

### Injection

- [ ] No raw queries with user input
- [ ] No `DB::raw()` with concatenated variables
- [ ] No `whereRaw()` without bindings
- [ ] No `exec()`, `shell_exec()`, `system()` with user input

### XSS

- [ ] All user output uses `{{ }}` not `{!! !!}`
- [ ] If `{!! !!}` used, content is sanitized
- [ ] JSON output properly encoded

### Authentication

- [ ] Protected routes have `auth` middleware
- [ ] Sensitive actions require reauthentication if needed
- [ ] Password handling uses `Hash::make()`

### Authorization

- [ ] `$this->authorize()` or `@can` used
- [ ] Policies check ownership, not just auth
- [ ] No IDOR (Insecure Direct Object Reference)

### CSRF

- [ ] State-changing actions use POST/PUT/DELETE
- [ ] Forms include `@csrf`
- [ ] API endpoints use token auth (Sanctum)

### Mass Assignment

- [ ] New models have `$fillable` defined
- [ ] Using `$request->validated()` not `$request->all()`
- [ ] Sensitive fields not in `$fillable`

### Session

- [ ] Session regenerated on privilege change
- [ ] No sensitive data in session
- [ ] Session timeout appropriate

### Information Disclosure

- [ ] No stack traces in production errors
- [ ] No sensitive data in logs
- [ ] Error messages generic to users

### Business Logic

- [ ] Edge cases handled (null, empty, max values)
- [ ] Race conditions considered
- [ ] Numeric overflow/underflow checked

---

## Phase 4: Verification

For each potential issue:

1. **Check if already handled** elsewhere in the code
2. **Search for tests** covering the scenario
3. **Read surrounding context** to verify issue is real
4. **Consider false positives** - don't invent issues

### Common False Positives

- `{!! !!}` with static/trusted content (icons, etc)
- `DB::raw()` with hardcoded values
- Missing auth on intentionally public endpoints

---

## Phase 5: Pre-Conclusion Audit

Before finalizing, verify:

1. [ ] Listed every file reviewed
2. [ ] Confirmed each was read completely
3. [ ] Checked every item in checklist
4. [ ] Noted areas that couldn't be fully verified
5. [ ] Documented why (if any items unclear)

---

## Output Format

### Prioritization

```
1. Security vulnerabilities (CRITICAL)
2. Bugs that break functionality (HIGH)
3. Logic errors (MEDIUM)
4. Code quality issues (LOW)
```

### Skip

- Stylistic/formatting issues (Pint handles this)
- Minor naming preferences
- Personal code style differences

### Finding Template

```markdown
**File:Line** - Brief description

**Severity:** Critical | High | Medium | Low

**Problem:** What's wrong

**Evidence:** Why this is real (not already fixed, no test, etc.)

**Fix:** Concrete suggestion

**Reference:** OWASP, Laravel docs, etc.
```

### Example Finding

```markdown
**app/Http/Controllers/PostController.php:45** - SQL Injection

**Severity:** Critical

**Problem:** User input concatenated into raw query
```php
$posts = DB::select("SELECT * FROM posts WHERE title LIKE '%{$request->search}%'");
```

**Evidence:** No sanitization, no parameterization

**Fix:** Use bindings
```php
$posts = DB::select(
    "SELECT * FROM posts WHERE title LIKE ?",
    ['%' . $request->search . '%']
);
```

**Reference:** OWASP A03:2021 - Injection
```

---

## Quick Commands

```bash
# Search for dangerous patterns
grep -rn "{!!" resources/views/
grep -rn "DB::raw" app/
grep -rn "->whereRaw" app/
grep -rn '$request->all()' app/

# Check for missing $fillable
grep -rL "fillable" app/Models/*.php

# Check dependencies
composer audit
```

---

> **Important:** This skill is for FINDING issues, not fixing them. Report findings and let the developer decide what to address.
