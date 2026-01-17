# [TASK-0-002] Core Dependencies Installation

## Context
Install and configure essential Laravel packages for ADUX: authentication, debugging tools, API foundation, and permission management. These form the foundation for the karma system, community features, and API access.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] None required

**Conditions that must be met:**
- [ ] TASK-0-001 completed (Laravel project initialized)
- [ ] Sail environment running
- [ ] Database accessible

## Deliverables
- Laravel Breeze installed and configured
- Laravel Telescope installed (dev only)
- Laravel Sanctum installed and configured
- Spatie Laravel Permission installed
- Tailwind CSS configured
- All dependencies tested and working

## AI Prompt
```
I have a fresh Laravel 11 project with Sail. Install and configure these packages:

1. Laravel Breeze (authentication) - use Blade stack with Alpine
2. Laravel Telescope (debugging) - dev environment only
3. Laravel Sanctum (API authentication) - prepare for future API
4. Spatie Laravel Permission (roles/permissions) - for karma system later

Additional setup:
- Configure Tailwind CSS with custom colors (will add later)
- Set up basic user roles: admin, moderator, user
- Ensure Telescope doesn't run in production
- Configure Sanctum for both SPA and API token usage

Show installation commands and any necessary configuration changes.
```

## Implementation Notes

### Installation Order
```bash
# Authentication (Breeze with Alpine stack)
./vendor/bin/sail composer require laravel/breeze --dev
./vendor/bin/sail artisan breeze:install blade
./vendor/bin/sail npm install
./vendor/bin/sail npm run build

# Debugging (Telescope - dev only)
./vendor/bin/sail composer require laravel/telescope --dev
./vendor/bin/sail artisan telescope:install
./vendor/bin/sail artisan migrate

# API Auth (Sanctum)
./vendor/bin/sail composer require laravel/sanctum
./vendor/bin/sail artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
./vendor/bin/sail artisan migrate

# Roles & Permissions
./vendor/bin/sail composer require spatie/laravel-permission
./vendor/bin/sail artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
./vendor/bin/sail artisan migrate
```

### Configuration Changes

**config/app.php** - Register service providers (if not auto-discovered)

**app/Http/Kernel.php** - Add Sanctum middleware:
```php
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

**app/Models/User.php** - Add traits:
```php
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, HasFactory, Notifiable;
}
```

### Telescope Gate (Production Protection)
**app/Providers/TelescopeServiceProvider.php**:
```php
protected function gate()
{
    Gate::define('viewTelescope', function ($user) {
        return in_array($user->email, [
            'your-email@example.com', // Add your email
        ]);
    });
}
```

### Initial Roles Setup
Create database seeder for basic roles:
```php
// database/seeders/RoleSeeder.php
Role::create(['name' => 'admin']);
Role::create(['name' => 'moderator']);
Role::create(['name' => 'user']);
```

### Tailwind Configuration
Update `tailwind.config.js` to prepare for custom design system (will populate in Phase 1):
```js
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                // Custom colors will be added in Phase 1
            },
        },
    },
    plugins: [],
}
```

## Acceptance Criteria
- [ ] Can register/login via Breeze authentication
- [ ] Telescope accessible at `/telescope` (local only)
- [ ] Sanctum middleware configured for API routes
- [ ] User model has HasApiTokens and HasRoles traits
- [ ] Basic roles created (admin, moderator, user)
- [ ] Tailwind compiling correctly (`npm run dev`)
- [ ] All migrations run successfully
- [ ] No console errors on frontend

## Testing Commands
```bash
# Test Breeze
# Visit /register and create account

# Test Telescope
# Visit /telescope and verify it loads

# Test Sanctum (basic)
./vendor/bin/sail artisan tinker
$user = User::first();
$token = $user->createToken('test')->plainTextToken;
# Use token in API request header: Authorization: Bearer {token}

# Test Spatie Permissions
./vendor/bin/sail artisan tinker
$user = User::first();
$user->assignRole('admin');
$user->hasRole('admin'); // Should return true
```

---
**Related Tasks:** TASK-0-001 (Set Up), TASK-1-XXX (Database Schema)  
**Phase:** 0 (Environment Setup)  
**Estimated Time:** 2-3 hours  
**Priority:** Critical
