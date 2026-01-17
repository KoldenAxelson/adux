# [TASK-0-003] Laravel Octane Setup

## Context
Install and configure Laravel Octane with Swoole to dramatically improve ADUX's performance from day one. Octane keeps the application in memory, eliminating bootstrap overhead and providing 2-5x faster response times - essential for serving static-like pages to search engines and handling high traffic efficiently.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] None required

**Conditions that must be met:**
- [ ] TASK-0-001 completed (Laravel project initialized)
- [ ] TASK-0-002 completed (Core dependencies installed)
- [ ] Sail environment running
- [ ] Basic understanding of stateful vs stateless code

## Deliverables
- Laravel Octane installed with Swoole
- Octane configuration optimized for ADUX
- Development commands documented
- State management patterns established
- Performance benchmarks recorded (before/after)
- Octane-specific gotchas documented

## AI Prompt
```
Install and configure Laravel Octane with Swoole for ADUX, a community-curated game database.

Project context:
- Dual-database architecture (Main DB + Show DB)
- Heavy read traffic expected (SEO/AI scraping)
- Static-like page generation for performance
- MacBook Air M2 for development

Setup requirements:
1. Install Laravel Octane with Swoole driver
2. Configure for optimal performance on M2 Mac
3. Set up proper worker management
4. Document state management patterns (avoid memory leaks)
5. Create commands for development vs production
6. Benchmark performance improvement

Key concerns:
- State persistence between requests (can cause bugs)
- Memory leak prevention
- Worker process management
- Development workflow (hot reload)

Show installation steps, configuration changes, and best practices for Octane development.
```

## Implementation Notes

### Installation
```bash
# Install Octane
./vendor/bin/sail composer require laravel/octane

# Install Swoole via Sail (easier than manual)
./vendor/bin/sail artisan octane:install --server=swoole

# This will:
# 1. Publish octane config
# 2. Install Swoole in the Sail container
# 3. Create octane.php config file
```

### Configuration (config/octane.php)

**Key settings to adjust:**
```php
return [
    'server' => env('OCTANE_SERVER', 'swoole'),
    
    'swoole' => [
        'options' => [
            'workers' => env('OCTANE_WORKERS', 4), // M2 has 8 cores, use 4
            'task_workers' => env('OCTANE_TASK_WORKERS', 2),
            'max_request' => 500, // Restart worker after 500 requests (memory leak prevention)
            'worker_max_request' => 500,
        ],
    ],
    
    'warm' => [
        // Warm these on boot (load into memory)
        ...Octane::defaultServicesToWarm(),
    ],
    
    'cache' => [
        'drivers' => ['array'], // Don't persist array cache between requests
    ],
    
    'tables' => [
        // Swoole tables for shared state (use sparingly)
        // 'example:1000' => [
        //     'name' => ['type' => Table::TYPE_STRING, 'size' => 1000],
        // ],
    ],
];
```

### Environment Variables (.env)
```bash
# Octane Configuration
OCTANE_SERVER=swoole
OCTANE_WORKERS=4
OCTANE_TASK_WORKERS=2
OCTANE_MAX_REQUESTS=500

# Development
OCTANE_WATCH_DIRS="app,config,routes,resources/views"
OCTANE_WATCH_EXTENSIONS="php,env,blade.php"
```

### Development Commands

**Start Octane (with hot reload):**
```bash
./vendor/bin/sail artisan octane:start --watch
# Watches files and auto-reloads on changes
# Access at http://localhost:8000
```

**Start Octane (production-like):**
```bash
./vendor/bin/sail artisan octane:start --workers=4 --task-workers=2
```

**Reload workers:**
```bash
./vendor/bin/sail artisan octane:reload
```

**Stop Octane:**
```bash
./vendor/bin/sail artisan octane:stop
```

**Check status:**
```bash
./vendor/bin/sail artisan octane:status
```

### State Management Patterns

**⚠️ CRITICAL GOTCHAS:**

**1. Container bindings persist between requests**
```php
// ❌ BAD - This persists across requests
app()->singleton(GameService::class, function () {
    return new GameService($someStateFromPreviousRequest);
});

// ✅ GOOD - Use scoped bindings
app()->scoped(GameService::class, function () {
    return new GameService();
});
```

**2. Static properties persist**
```php
// ❌ BAD - Static properties leak between requests
class GameRepository {
    public static $cache = [];
}

// ✅ GOOD - Use instance properties
class GameRepository {
    public $cache = [];
}
```

**3. Global state persists**
```php
// ❌ BAD - File handles, DB connections can leak
global $connection;

// ✅ GOOD - Use dependency injection
public function __construct(
    protected ConnectionInterface $connection
) {}
```

**4. Middleware and service providers**
```php
// ✅ GOOD - These are automatically handled by Octane
// Just be aware of what happens on each request vs boot
```

### Octane Middleware (Auto-configured)

Laravel Octane automatically adds middleware to flush state:
- `Illuminate\Http\Middleware\HandleCors`
- `Octane\Middleware\FlushSessionState`
- `Octane\Middleware\FlushViews`
- etc.

### Testing State Leaks

Create a test route to verify no state leakage:
```php
// routes/web.php (temporary, remove later)
Route::get('/octane-test', function () {
    static $counter = 0;
    $counter++;
    
    return response()->json([
        'counter' => $counter, // Should always be 1 if state is flushed
        'request_id' => request()->id(),
    ]);
});
```

Visit `/octane-test` multiple times. If counter > 1, you have state leakage.

### Performance Benchmarking

**Before Octane:**
```bash
# Using Apache Bench
ab -n 1000 -c 10 http://localhost/
# Record: requests/sec, time per request
```

**After Octane:**
```bash
ab -n 1000 -c 10 http://localhost:8000/
# Compare results
```

Expected improvement: **2-5x faster** response times.

### Production Configuration (Phase 3)

For Lightsail/EC2 deployment:
```bash
# Use Supervisor to manage Octane
# /etc/supervisor/conf.d/octane.conf
[program:octane]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/adux/artisan octane:start --server=swoole --host=0.0.0.0 --port=8000 --workers=4 --task-workers=2
autostart=true
autorestart=true
user=forge
redirect_stderr=true
stdout_logfile=/path/to/adux/storage/logs/octane.log
```

### Nginx Configuration (Phase 3)

Proxy to Octane instead of PHP-FPM:
```nginx
server {
    listen 80;
    server_name adux.com;
    
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

## Acceptance Criteria
- [ ] Octane installed and running with Swoole
- [ ] Can start Octane with `sail artisan octane:start`
- [ ] Hot reload works in development (`--watch` flag)
- [ ] No state leakage detected (test route returns counter=1)
- [ ] Performance benchmark shows 2x+ improvement
- [ ] All tests pass with Octane running
- [ ] Documentation created for common Octane patterns
- [ ] Team understands state management gotchas

## Common Issues & Solutions

**Issue:** `swoole extension not installed`
**Solution:** Run `sail artisan octane:install --server=swoole` again

**Issue:** Worker crashes frequently
**Solution:** Lower `max_request` in config to restart workers more often

**Issue:** Changes not reflecting
**Solution:** Use `--watch` flag or manually reload with `octane:reload`

**Issue:** Memory leaks
**Solution:** Check for static properties, global state, singleton bindings

**Issue:** Port 8000 already in use
**Solution:** `sail artisan octane:stop` or change port with `--port=8001`

## Next Steps

After Octane is configured:
1. Update development workflow docs
2. Train team on state management
3. Move to Phase 1 (database schema)
4. Consider RoadRunner as alternative to Swoole (optional)

## Additional Resources

- [Laravel Octane Docs](https://laravel.com/docs/11.x/octane)
- [Swoole Docs](https://www.swoole.co.uk/)
- [Octane State Management](https://laravel.com/docs/11.x/octane#managing-memory-leaks)

---
**Related Tasks:** TASK-0-002 (Core Dependencies), TASK-1-001 (Database Schema)  
**Phase:** 0 (Environment Setup)  
**Estimated Time:** 2-3 hours  
**Priority:** High (sets foundation for performance)
