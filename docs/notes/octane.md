# Octane State Management Rules for ADUX

## ⚠️ CRITICAL: State Persists Between Requests

Octane keeps your application in memory. This means:

1. **Static properties persist** across all requests
2. **Singleton bindings persist** if not scoped properly
3. **Global variables persist** (don't use them)
4. **File handles, DB connections can leak** if not closed

## ✅ Safe Patterns

- Use `app()->scoped()` instead of `app()->singleton()`
- Use instance properties, not static
- Rely on dependency injection
- Use Laravel's cache (Redis) for cross-request state

## ❌ Dangerous Patterns

- Static properties in services
- Global variables
- Singleton bindings with request-specific data
- Manually managed database connections

## Testing for Leaks

Run `/octane-test` in dev to check for state leakage.
Remove this route before production.

## Memory Monitoring

Watch for memory growth:
```bash
# Check worker memory
./vendor/bin/sail artisan octane:metrics

# Restart workers if memory grows
./vendor/bin/sail artisan octane:reload
```

## Configuration

- 1 worker (1 vCPU limit)
- 250 requests max before worker restart
- Aggressive garbage collection (every 50 requests)
