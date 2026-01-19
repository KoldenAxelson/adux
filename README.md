# ADUX - Against Dark User Experience

> **Stop taking advantage of your customer base. Have some ethics.**

A community-curated video game database that exposes predatory design patterns and rewards ethical contributions. Think: **Wikipedia's governance** + **MyAnimeList's tracking** + **Reddit's communities** + **GameFAQs' guides** + **Rotten Tomatoes' ratings**.

## Mission

The gaming industry weaponizes psychology through dark UX patterns: battle passes, loot boxes, time-gating, pay-to-win. ADUX is a movement to expose these practices, celebrate games that respect players, and build a comprehensive, community-governed game database.

Games should be **art and entertainment**, not Skinner boxes designed to extract engagement and money.

## What ADUX Does

### For Casual Users (Bulk Traffic)
- **Quick UX Check:** "Does Genshin Impact respect players?" → See it bleeds wallets dry
- **Game Discovery:** Browse, search, filter by platforms, genres, UX ethics
- **Ratings at a Glance:** Community consensus on game quality and ethics
- **SEO/AI Optimized:** Statically generated pages for fast loads and search engine visibility

### For Engaged Users (The Community)
- **MyAnimeList-Style Tracking:** Build game lists (Playing, Completed, Wishlist, Bought, Custom)
- **Leave Reviews:** Rate games traditionally AND on UX ethics
- **Discussion Forums:** Per-game, per-console, per-platform communities
- **Submit Walkthroughs:** Write guides in Markdown or monospaced (ASCII art friendly)
- **Earn Karma:** Contributions reward XP that increases vote weight
- **Curate Data:** Submit box art, descriptions, screenshots - community votes on winners

### The Unique Part: Democratic Data Curation

**Living Database** (Main DB):
- Users submit multiple options for game fields (box art, descriptions, screenshots, etc.)
- Other users vote on submissions (vote weight = their karma)
- Submissions accumulate karma-weighted votes over time

**Show Database** (API/Public):
- Winner-at-end-of-day/hour/minute (based on game popularity) gets pushed here
- This is what users see and what the API serves
- Only updates when winners change
- High-traffic games update frequently, low-traffic games update weekly

**Example:**
- 10 users submit different box arts for Elden Ring
- Community votes (weighted by karma)
- Highest-voted submission becomes the official box art... until a better one wins

Only the game's **name** is immutable (set at creation). Everything else is community-governed.

## Tech Stack

### Core Architecture
- **Backend:** Laravel 11+ with Laravel Octane (Swoole)
- **Frontend:** Alpine.js + Livewire + Blade components
- **Styling:** Tailwind CSS with custom design system
- **Static Generation:** Aggressive static page generation for SEO/AI scraping
- **Dual Database:**
  - **Main Database:** MySQL - All submissions, votes, forums, user activity
  - **API/Show Database:** MySQL - Read-only winners for public display
- **Cache:** Redis (Phase 3)
- **Search:** Meilisearch (self-hosted) for game search

### Key Features
- **Karma-Weighted Voting:** Higher karma = more vote weight in data curation
- **Democratic Governance:** Community votes on game data (box art, descriptions, etc.)
- **Hierarchical Structure:** Platforms → Consoles → Games (each with forums)
- **Static-First:** Winner-at-end-of-day updates pushed to Show DB for fast serving
- **Visit-Based Regeneration:** Popular games update hourly, stale games weekly

### Key Packages
- **Laravel Breeze** - Authentication scaffolding
- **Laravel Sanctum** - API token management
- **Laravel Telescope** - Debugging and monitoring
- **Laravel Scout** - Search integration
- **Spatie/laravel-permission** - Role and permission management
- **Spatie/laravel-responsecache** - ISR-like page caching

### Infrastructure
- **Phase 0-2:** AWS Lightsail ($5/month)
- **Phase 3:** AWS EC2 + RDS with auto-scaling
- **Deployment:** Laravel Forge (optional, Phase 3)

## Project Phases

### Phase 0: Environment Setup ✅ COMPLETE

Set up development environment, install dependencies, configure Lightsail, establish dual-database structure.

**Completed Tasks:**
- ✅ TASK-0-001: Environment Setup & Project Initialization
- ✅ TASK-0-002: Core Dependencies Installation  
- ✅ TASK-0-003: Laravel Octane Setup

**Deliverables:**
- Local development environment (Laravel Sail + Docker)
- AWS Lightsail staging instance ($5/month, Ubuntu 22.04)
- Dual database configuration (adux_main + adux_show)
- Laravel 11 with Breeze (auth), Sanctum (API), Telescope (debugging)
- Spatie Permissions (roles/permissions)
- Laravel Octane with Swoole (persistent app state, 2-5x faster)
- Git workflow established (main + develop branches)

**Documentation:** See `/docs/tasks/phase 0/` for detailed completion reports

### Phase 1: Architecture & Foundation 🚧 IN PROGRESS

Design dual-database schema, build design system, create reusable components, establish hierarchical data structure.

**Current Focus:** Database schema design and model relationships

**Key Deliverables:**
- **Dual-database schema:**
  - Main DB: games, users, submissions, votes, forums, karma, walkthroughs
  - API/Show DB: winning submissions only, read-only
- **Hierarchical structure:** Platforms → Consoles → Games
- **Submission/voting system:** Karma-weighted voting on game data
- Design system at `/rubric`
- Reusable Blade components
- API v1 route structure with Sanctum
- Authentication and authorization complete

**Next Tasks:**
- TASK-1-001: Database Schema Design
- TASK-1-002: Laravel Models & Relationships
- TASK-1-003: Database Seeders
- TASK-1-004+: Design system and components

**Documentation:** See `/docs/tasks/phase 1/` for task definitions

### Phase 2: Feature Development
Build all core features. This is the longest phase - could take years of iterative development.

**Essential for MVP:**
- Game browsing, search, and detail pages
- UX metrics voting (Exploit vs Respect)
- User registration and profiles
- Basic karma system (points for contributions)
- Game submission approval workflow

**Phase 2 Extensions (build as desired):**
- MyAnimeList features: game lists (Playing, Completed, Wishlist, Bought, Custom)
- Discussion forums (per-game, per-console, per-platform)
- Walkthroughs/guides (markdown or monospaced)
- Data submission system (box art, screenshots, descriptions)
- Karma-weighted voting on submissions
- Winner-at-end-of-day sync to Show DB
- Visit tracking and popularity-based regeneration
- News section
- Shop (merch)
- API endpoints (read-only from Show DB)

### Phase 3: Scale & Monitor
Only when you have real traffic that justifies the cost.

**Key Deliverables:**
- Lightsail → EC2/RDS migration
- Separate instances for Main DB and Show DB
- Laravel Horizon for queue management
- Laravel Pulse for performance monitoring
- Advanced caching strategies
- API monetization (if desired)
- Horizontal scaling preparation

## Design Philosophy

### Component Architecture
- **Dumb, Simple Components:** One responsibility per component. Props in, HTML out.
- **DRY Without Dogma:** Extract after 2-3 uses. Duplication > wrong abstraction.
- **Composition Over Complexity:** Build complex UIs from simple, reusable parts.

### Performance First
- Swoole for persistent application state
- ISR-like caching for game pages (regenerate on new content, not every request)
- Lazy loading for below-the-fold content
- Database query optimization from day one

### Design System
Located at `/rubric`:
- `/rubric/foundation.blade.php` - Colors, typography, static design elements
- `/rubric/elements.blade.php` - Interactive components showcase
- All components used in production are showcased in the design system

## Current Status

**Phase:** 1 (Architecture & Foundation) - In Progress  
**Phase 0:** ✅ Complete (Environment Setup)  
**Next Task:** TASK-1-001 (Database Schema Design)

### Phase 0 Achievements ✅
- ✅ Local development environment (Laravel Sail + Octane)
- ✅ AWS Lightsail staging server ($5/month)
- ✅ Dual database architecture configured (adux_main + adux_show)
- ✅ Core dependencies installed (Breeze, Sanctum, Telescope, Spatie Permissions)
- ✅ Laravel Octane with Swoole (2-5x performance boost)
- ✅ Authentication system operational
- ✅ API foundation with Sanctum tokens
- ✅ Git repository established

**Prototype:** Functional PHP prototype with core UX metrics system exists as reference

Now building the Laravel rewrite with proper dual-database architecture, karma-weighted voting system, and scalable foundation.

## Getting Started

### Prerequisites
- Docker Desktop (for Laravel Sail)
- Git
- Composer (optional, Sail handles this)

### Quick Start

```bash
# Clone the repository
git clone <repo-url>
cd adux

# Copy environment file
cp .env.example .env

# Start Docker containers (includes MySQL, Redis, Meilisearch)
./vendor/bin/sail up -d

# Generate application key
./vendor/bin/sail artisan key:generate

# Run migrations
./vendor/bin/sail artisan migrate

# Seed roles
./vendor/bin/sail artisan db:seed --class=RoleSeeder

# Start Laravel Octane (application server)
./vendor/bin/sail artisan octane:start --watch
```

**Application will be available at:** http://localhost:8000  
**Telescope (debugging):** http://localhost:8000/telescope  
**Mailpit (email testing):** http://localhost:8025

### Detailed Setup Documentation

For complete environment setup instructions, see:
- **Local Setup:** `/docs/tasks/phase 0/VIEW-0-001-Set-Up.md`
- **Dependencies:** `/docs/tasks/phase 0/VIEW-0-002-Core-Dependencies.md`
- **Octane Setup:** `/docs/tasks/phase 0/VIEW-0-003-Octane-Setup.md`

### Development Workflow

```bash
# Start environment
./vendor/bin/sail up -d
./vendor/bin/sail artisan octane:start --watch

# Code changes auto-reload with --watch flag
# No need to manually restart during development

# Run migrations
./vendor/bin/sail artisan migrate

# Access MySQL
./vendor/bin/sail mysql

# Run tests
./vendor/bin/sail test

# Stop environment
./vendor/bin/sail artisan octane:stop
./vendor/bin/sail down
```

## Project Structure

```
/app
  /Http
    /Controllers
      /Api/v1               # API controllers (read from Show DB)
      /Admin                # Admin/moderation controllers
      /Community            # Forums, walkthroughs controllers
    /Resources              # API response resources
  /Models
    /MainDb                 # Models for Main Database
    /ShowDb                 # Models for Show Database (read-only)
  /Jobs                     # Queue jobs (winner calculation, sync)
  /Services                 # Business logic (karma calculation, voting)
/resources
  /views
    /components             # Reusable Blade components
    /rubric                 # Design system showcase pages
    /layouts                # Page layouts
    /games                  # Game-related views
    /forums                 # Forum views
    /admin                  # Admin/moderation views
/routes
  api.php                   # API routes (versioned)
  web.php                   # Web routes
  admin.php                 # Admin routes
/database
  /migrations
    /main_db                # Main Database migrations
    /show_db                # Show Database migrations
  /seeders                  # Data seeders
/docs
  /tasks                    # Task files organized by phase
```

## Key Directories

**`/app/Models/MainDb`** - All living data (submissions, votes, forums, users)  
**`/app/Models/ShowDb`** - Read-only display data (winners only)  
**`/app/Jobs`** - Winner calculation, Main → Show sync, karma recalculation  
**`/resources/views/components`** - DRY components used throughout  
**`/resources/views/rubric`** - Living design system documentation

## API (Future)

Public API providing access to game database with freemium tiers:
- **Free Tier:** 100 req/hour, basic game data
- **Indie Tier:** $10/mo, 1,000 req/hour
- **Pro Tier:** $50/mo, 10,000 req/hour
- **Enterprise:** Custom pricing, unlimited requests

Documentation at `/api/docs` (Scramble auto-generated).

## Contributing

This is a passion project fighting for ethical game design. We welcome feedback, suggestions, and contributions that align with our mission of exposing dark UX patterns and celebrating player-respecting games.

## Future Considerations

Technologies and optimizations planned for when traffic demands them:
- **Incremental Static Regeneration (ISR)** - Cache pages, regenerate on content changes
- **Read Replicas** - Separate read/write databases for high-traffic scenarios
- **Laravel Vapor** - Serverless deployment (if AWS Lambda makes sense)
- **Advanced Caching** - Multi-layer caching strategy (Redis, CDN, browser)
- **Webhooks** - Real-time notifications for API users (enterprise tier)

## License

MIT

---

**Fight dark patterns. Respect players. Build better games.**

*"Triple A, and the A's stand for Trash!"* - ADUX Community
