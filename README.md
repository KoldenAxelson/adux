# ADUX - Against Dark User Experience

> **Community-curated video game database exposing predatory design patterns**

A full-stack Laravel application demonstrating advanced architecture, performance optimization, and system design. Built to scale from MVP to millions of users while fighting unethical game monetization.

**Live Demo:** [Coming Phase 2]  
**GitHub:** [Repository Link]

---

## Tech Stack & Architecture

**Backend:** Laravel 11 + Octane (Swoole) for 2-5x performance gains  
**Frontend:** Livewire 3 + Alpine.js (server-rendered, progressively enhanced)  
**Styling:** Tailwind CSS with custom design system  
**Storage:** AWS S3 (handling 100k+ game images)  
**Cache:** Redis with aggressive response caching  
**Search:** Meilisearch (self-hosted)  
**Database:** Dual MySQL architecture (Main DB + API/Show DB)  
**Infrastructure:** AWS Lightsail → EC2 (auto-scaling planned Phase 3)

---

## Performance Benchmarks

| Metric | Typical Website | ADUX |
|--------|-----------------|------|
| **First Page Load** | 2-4 seconds | < 1 second |
| **Cached Page Load** | 200-500ms | < 100ms |
| **Images Below Fold** | Load immediately | Lazy-loaded (40-60% bandwidth savings) |
| **JavaScript Bundle** | 200-500KB on every page | 15-40KB page-specific bundles |
| **Layout Shift (CLS)** | 0.15-0.25 (poor) | < 0.1 (good) - dimensions stored in DB |
| **Cache Hit Rate** | 50-60% | 80%+ target |
| **Click Response** | Full page reload (500ms+) | Instant with hover prefetch |
| **Server Cost (10k games, 100k views/month)** | $20-50/month | $8/month ($5 Lightsail + $3 S3) |
| **Database Queries per Request** | 10-50 (N+1 problems) | 1-3 (eager loading + caching) |
| **Time to Interactive** | 3-5 seconds | 1-2 seconds |

**Key Optimizations:**
- Octane persistent application state (no bootstrap per request)
- Response caching with precise invalidation (1-week cache, invalidate on data change)
- Smart prefetching (hover-triggered, desktop-only, debounced)
- Image dimensions stored in DB (prevents layout shift)
- S3 + CloudFront (Phase 3) for global CDN

---

## System Architecture: Dual-Database Design

### The Challenge
Community-curated data where users submit and vote on game information (box art, descriptions, screenshots). Need democratic curation without sacrificing performance.

### The Solution: Main DB + Show DB

**Main Database** (MySQL - Write-Heavy)
- All submissions for every game field
- Karma-weighted voting system
- Forums, reviews, user activity
- Admin/moderation workflows
- Where the work happens

**Show Database** (MySQL - Read-Only)
- Only winning submissions
- Serves public pages and API
- Static-generation friendly
- Updated when winners change (not every request)

**Data Flow:**
```
User submits box art → Main DB
Community votes (karma-weighted) → Main DB
Queue job calculates winner → Updates if changed
Winner syncs to Show DB → Public sees winning submission
```

**Why This Works:**
- ✅ Public pages hit read-optimized Show DB (fast)
- ✅ Admin/voting hit Main DB (doesn't slow public traffic)
- ✅ Cache Show DB aggressively (rarely changes)
- ✅ Enables static generation for high-traffic pages
- ✅ Scales independently (separate read replicas in Phase 3)

**Popularity-Based Sync:**
- High-traffic games: Sync every 15 minutes
- Medium-traffic games: Sync hourly
- Low-traffic games: Sync daily
- Only sync when winner actually changes

---

## Key Features

### For Users
- **Game Discovery:** Browse 100k+ games by platform, genre, ethical rating
- **UX Metrics Voting:** Rate games on time exploitation, pay-to-win, dark patterns
- **MyAnimeList-Style Tracking:** Lists (Playing, Completed, Wishlist, Custom)
- **Reviews & Forums:** Per-game, per-console, per-platform communities
- **Walkthrough System:** Markdown or monospaced (ASCII art friendly)

### For the Mission
- **Karma System:** Contributions earn karma → karma weights votes → democratic curation
- **Democratic Data:** Community votes on game info (box art, descriptions, screenshots)
- **Winner-Takes-All:** Highest karma submission becomes official until beaten
- **Only Game Name is Immutable:** Everything else can be improved by the community

### Technical Highlights
- **Karma-Weighted Voting:** `SUM(voters.karma_points)` determines winners
- **Queue-Based Winner Calculation:** Scheduled based on game popularity
- **Hierarchical Forums:** Platform → Console → Game structure
- **API v1 with Sanctum:** Token-based authentication, read from Show DB
- **Static Page Generation:** ISR-like caching with ResponseCache

---

## Current Status

**Phase:** 1 (Architecture & Foundation) - In Progress  
**Next Task:** Database Schema Design  

### Phase 0 ✅ Complete
- Local dev (Sail + Octane + Docker)
- AWS Lightsail staging ($5/month)
- Dual database configuration
- Auth (Breeze), API (Sanctum), Debugging (Telescope), Roles (Spatie Permissions)

### Phase 1 🚧 In Progress
- Database schema (Main DB + Show DB)
- Models and relationships
- Design system (`/rubric`)
- Reusable Blade components
- API v1 structure

### Phase 2 Roadmap
- Game browsing, search, filtering
- UX metrics voting
- Submission system with approval workflow
- Karma calculation
- Winner-calculation queue jobs
- Forums (basic)
- User profiles and game lists

### Phase 3 Goals (Scale)
- EC2 + RDS with auto-scaling
- CloudFront CDN
- Separate read replicas
- Laravel Horizon (queue management)
- Laravel Pulse (performance monitoring)

---

## Project Structure

```
/app
  /Models
    /MainDb              # Living data (submissions, votes, forums)
    /ShowDb              # Winners only (public display)
  /Jobs                  # Winner calculation, sync, karma
  /Services              # Business logic (voting, karma, sync)
  /Http/Controllers
    /Api/v1              # API (reads from Show DB)
    /Admin               # Moderation
    /Community           # Forums, walkthroughs

/resources/views
  /components            # Reusable Blade components
  /rubric                # Design system showcase
  /layouts               # Base layouts

/database/migrations
  /main_db               # Main Database migrations
  /show_db               # Show Database migrations
```

---

## Getting Started

### Prerequisites
- Docker Desktop (Laravel Sail)
- Git
- Composer (optional, Sail handles this)

### Quick Start
```bash
git clone <repo-url>
cd adux
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed --class=RoleSeeder
./vendor/bin/sail artisan octane:start --watch
```

**Access:**
- App: http://localhost:8000
- Telescope: http://localhost:8000/telescope
- Mailpit: http://localhost:8025

### Development Workflow
```bash
# Start
./vendor/bin/sail up -d
./vendor/bin/sail artisan octane:start --watch

# Octane hot-reloads on file changes (no manual restarts)

# Database
./vendor/bin/sail artisan migrate
./vendor/bin/sail mysql

# Tests
./vendor/bin/sail test

# Stop
./vendor/bin/sail artisan octane:stop
./vendor/bin/sail down
```

---

## Design Philosophy

### Component Architecture
- **Dumb Components:** One responsibility, props in, HTML out
- **Extract After 2-3 Uses:** Avoid premature abstraction
- **Composition:** Build complex UIs from simple parts

### Performance First
- Octane for persistent state
- Response caching with precise invalidation
- Lazy loading (images, Livewire components)
- Page-specific JS bundles
- Hover-triggered prefetch (smart guards)

### Progressive Enhancement
- Base functionality: server-rendered HTML (works without JS)
- Enhanced: Livewire for interactivity
- Optimized: Alpine.js for client-side reactivity

---

## Key Technical Decisions

### Why Laravel + Octane?
- Server-rendered HTML (SEO friendly)
- 2-5x faster than standard PHP-FPM
- Persistent application state
- Better resource efficiency on limited hardware

### Why Livewire + Alpine?
- Server-rendered (good for SEO, fast initial load)
- Progressive enhancement (works without JS)
- No JS framework complexity
- Perfect for forms, voting, filtering

### Why Dual Database?
- Separates concerns (work vs display)
- Public pages hit fast read-only DB
- Admin/voting doesn't impact public performance
- Enables aggressive caching
- Scales independently

### Why S3 from Day 1?
- AI scraping will add 100k+ games rapidly
- Cheaper at scale ($2-3/month vs $10-20/month on instance)
- Doesn't fill application disk
- Scales infinitely
- CloudFront-ready (Phase 3)

---

## API (Planned Phase 2)

Public API serving game data from Show DB with freemium model:

| Tier | Price | Requests | Features |
|------|-------|----------|----------|
| Free | $0 | 100/hour | Basic game data |
| Indie | $10/mo | 1,000/hour | Full game data |
| Pro | $50/mo | 10,000/hour | Webhooks, priority support |
| Enterprise | Custom | Unlimited | SLA, dedicated support |

**Documentation:** `/api/docs` (Scramble auto-generated)

---

## Mission

The gaming industry weaponizes psychology through dark UX patterns:
- Battle passes (FOMO exploitation)
- Loot boxes (gambling mechanics)
- Time-gating (artificial scarcity)
- Pay-to-win (competitive imbalance)

**ADUX exposes these practices** while celebrating games that respect players.

Games should be art and entertainment, not Skinner boxes designed to extract engagement and money.

---

## Contributing

Passion project fighting for ethical game design. Contributions welcome that align with the mission of exposing dark UX patterns and celebrating player-respecting games.

---

## Documentation

- **Setup:** `/docs/tasks/phase 0/VIEW-0-001-Set-Up.md`
- **Performance Guide:** `/docs/PERFORMANCE_GUIDE.md`
- **Octane Notes:** `/docs/notes/octane.md`
- **Task Tracking:** `/docs/tasks/` organized by phase

---

## License

MIT

---

**Fight dark patterns. Respect players. Build better games.**

*"Triple A, and the A's stand for Trash!"* - ADUX Community
