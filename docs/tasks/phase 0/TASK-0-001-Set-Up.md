# [TASK-0-001] Environment Setup & Project Initialization

## Context
Set up local development environment using Laravel Sail (Docker), initialize ADUX Laravel project, configure AWS Lightsail instance for staging/testing, establish Git workflow, and prepare for dual-database architecture (Main DB + Show DB).

## Prerequisites
**Files to attach to this task prompt:**
- [ ] None (this is the starting task)

**Conditions that must be met:**
- [ ] MacBook Air M2 (confirmed)
- [ ] Git installed
- [ ] Docker Desktop installed
- [ ] AWS account created
- [ ] GitHub account ready

## Deliverables
- Laravel 11+ project initialized with Sail
- Lightsail instance running ($5/month tier)
- Git repository created and pushed to GitHub
- `.env` configured for local and staging (with dual-database support)
- Basic deployment workflow documented

## AI Prompt
```
I'm starting ADUX, a community-curated game database with Wikipedia-style governance. Help me:

1. Initialize a fresh Laravel 11 project with Sail
2. Configure Sail for MySQL (will need 2 databases eventually), Redis, and Meilisearch
3. Set up a basic Git workflow (main + develop branches)
4. Create an AWS Lightsail instance and configure it for Laravel
5. Document the deployment process from local → Lightsail

Project context:
- ADUX = Against Dark User Experience (fighting predatory game design)
- MacBook Air M2 for local development
- Using Laravel Sail for Docker-based local dev
- AWS Lightsail for staging/testing (will migrate to EC2 in Phase 3)
- Dual-database architecture planned: Main DB (all data) + Show DB (public display)
- Planning for API in addition to web interface

Show me the exact commands and configuration files.
```

## Implementation Notes

### Local Development (Sail)
```bash
# Install Laravel with Sail
curl -s "https://laravel.build/adux?with=mysql,redis,meilisearch" | bash
cd adux
./vendor/bin/sail up -d

# Set up Git
git init
git add .
git commit -m "Initial ADUX Laravel setup with Sail"
git branch -M main
git remote add origin <your-repo-url>
git push -u origin main
```

### AWS Lightsail Setup
- Use $5/month tier (1 GB RAM, 1 vCPU, 40 GB SSD)
- Ubuntu 22.04 LTS
- Open ports: 22 (SSH), 80 (HTTP), 443 (HTTPS)
- Reserve static IP address
- Install: PHP 8.2+, Composer, MySQL, Nginx

### Initial Configuration
- Set `APP_ENV=local` for development
- Set `APP_ENV=staging` for Lightsail
- Configure database credentials for both environments
- Set up basic Nginx config for Laravel
- Prepare `.env` for eventual dual-database support:
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_DATABASE=adux_main
  
  # Show DB (Phase 1+)
  SHOW_DB_HOST=127.0.0.1
  SHOW_DB_DATABASE=adux_show
  ```

### Git Workflow
```
main (production, Phase 3)
  ↑
develop (active development, Phase 0-2)
  ↑
feature/task-x-xxx (individual tasks)
```

## Acceptance Criteria
- [ ] Laravel project runs locally via Sail (`sail up`)
- [ ] Can access app at `http://localhost`
- [ ] Database migrations run successfully
- [ ] Git repository pushed to GitHub
- [ ] Lightsail instance accessible via SSH
- [ ] Can deploy to Lightsail manually
- [ ] Documentation written for deployment process
- [ ] `.env.example` updated with all required variables

## Deployment Script (Basic)
Create `deploy.sh` for manual deployment to Lightsail:
```bash
#!/bin/bash
# Simple deploy script for Phase 0-2
git pull origin develop
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---
**Related Tasks:** TASK-0-002 (Core Dependencies)  
**Phase:** 0 (Environment Setup)  
**Estimated Time:** 3-4 hours  
**Priority:** Critical
