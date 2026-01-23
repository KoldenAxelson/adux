# [TASK-1-009] Architecture Documentation

## Context
Document the Phase 1 architecture decisions, database schema, API structure, and component system. This documentation ensures Phase 2 developers (or future you) understand the system design and can build features correctly on top of the foundation.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] All Phase 1 task files (TASK-1-001 through TASK-1-008)
- [ ] README.md (current project overview)

**Conditions that must be met:**
- [ ] All other Phase 1 tasks complete
- [ ] Database schema finalized
- [ ] Components built and tested
- [ ] Queue jobs working

## Deliverables
- Architecture overview document
- Database schema documentation (ER diagram or detailed description)
- API documentation for admin endpoints
- Component usage guide
- Queue job flow documentation
- README.md updates
- Development workflow guide
- Phase 2 preparation notes

## AI Prompt
```
Create comprehensive documentation for ADUX Phase 1 architecture.

**DOCUMENTS TO CREATE:**

**1. ARCHITECTURE.md**

```markdown
# ADUX Architecture Documentation

## Overview
ADUX is a community-curated video game database with an ethics rating system. The architecture is designed to scale from a $5/month server to millions of users while maintaining democratic data curation through renown-weighted voting.

## Core Principles
1. **Dual-Database Architecture** - Separate work (Main DB) from display (Show DB)
2. **Democratic Curation** - Community submissions weighted by contributor renown
3. **Performance First** - Octane + caching + queue jobs for speed
4. **Progressive Enhancement** - Server-rendered HTML with Livewire/Alpine enhancements
5. **Ethical Focus** - 5-metric rating system evaluates player respect vs exploitation

---

## System Architecture

### Dual-Database Design

**Main Database (MySQL)**
- Purpose: All living data, work happens here
- Contains: Submissions, votes, forums, user activity, renown transactions
- Access: Write-heavy, admin/user interactions
- Optimized for: Flexibility, democratic voting, data integrity

**Show Database (MySQL)**
- Purpose: Read-only winning submissions for public display
- Contains: Only current winners, denormalized for speed
- Access: Read-only, public pages and API
- Optimized for: Speed, static generation, caching

**Data Flow:**
```
User submits data → Main DB
Community votes (renown-weighted) → Main DB
Queue job calculates winner → Updates Main DB
Winner changed? → Sync to Show DB → Public sees new data
```

**Sync Strategy:**
- High-traffic games (10k+ visits): Every 15 minutes
- Medium-traffic games (1k-10k): Hourly
- Low-traffic games (<1k): Daily
- Only sync when winner actually changes (efficiency)

**Why This Works:**
- Public pages hit fast read-only Show DB
- Admin/voting doesn't slow public traffic
- Show DB can be cached aggressively
- Enables static generation for high-traffic pages
- Databases can scale independently (Phase 3)

---

## Hierarchical Structure

```
Platform (Nintendo, Sony, PC) → has page, has forum
  └─ Console (Switch, N64, PS5) → has page, has forum
      └─ Game (Zelda BOTW) → has page, has forum
```

**Multi-Platform Games:**
Games can appear on multiple consoles via many-to-many relationship.
Example: Ocarina of Time appears on N64, GameCube, Wii, 3DS.

**Forums:**
Polymorphic relationship - each platform/console/game can have a forum.

---

## Submission & Voting System

### Editable Fields (via game_submissions)
Only game **title** is immutable. Everything else is community-editable:
- Box art (URL)
- Release date
- Publisher
- Developer
- Countries of release (JSON array)
- Languages supported (JSON array)

NOT editable: Price, Description, Screenshots (excluded by design)

### Voting Mechanism
- Users vote on submissions (one vote per submission)
- Votes are weighted by voter's renown points
- Winner = submission with highest total renown
- Renown calculation: `SUM(voters.renown_points)`

### Renown System
Users earn renown points for contributions:
- Submission accepted: +50 points
- Review helpful vote: +5 points
- Forum post helpful: +2 points
- (Exact values TBD in Phase 2)

Renown is stored in `users.renown_points` and logged in `renown_transactions`.

---

## API Architecture

### Admin API (Phase 1)
- Base URL: `/api/v1/admin`
- Authentication: Laravel Sanctum (bearer tokens)
- Purpose: Bot users submit game data
- Rate limit: 100 requests/minute per user
- Database: Writes to Main DB

**Endpoints:**
- `POST /api/v1/admin/games` - Create new game
- `POST /api/v1/admin/games/{game}/submissions` - Submit metadata
- `GET /api/v1/admin/games/{slug}` - Get game details
- `GET /api/v1/admin/consoles` - Lookup consoles

### Public API (Phase 2)
- Base URL: `/api/v1/public`
- Authentication: Optional (rate limits differ)
- Purpose: Read game data for apps/sites
- Database: Reads from Show DB only
- Freemium model: Free (100/hr), Indie ($10, 1k/hr), Pro ($50, 10k/hr)

---

## Queue Job Architecture

### Jobs
1. **CalculateSubmissionWinners** - Determines winning submissions per field
2. **SyncGameToShowDB** - Copies winning data to Show DB

### Flow
```
Scheduled trigger (based on game popularity)
  → CalculateSubmissionWinners dispatched
  → Calculates renown totals for all submissions
  → Updates is_current_winner flags
  → If winner changed:
    → SyncGameToShowDB dispatched
    → Copies winning data to Show DB
  → If no change: Skip sync (efficiency)
```

### Scheduling (Phase 2)
- Jobs run via Laravel scheduler
- Frequency based on `game_visits.visit_count`
- Uses database queue (Phase 1/2), Redis (Phase 3)

---

## Design System

### Color Palette
- **Purple** (#9D5AAF): Primary brand, navigation, CTAs
- **Amber** (#FFA726): Secondary CTAs, community features
- **Ethics Colors**: Green (respect), Red (exploit), Gray (neutral)
- **Backgrounds**: Dark mode default, light mode toggle

### Typography
- **Primary**: Inter (UI, body text)
- **Display**: Poppins (H1, H2 only)
- **Monospace**: JetBrains Mono (ethics data, code)

### Components
11 core reusable Blade components:
- Game card, UX metrics (inline + detailed), Buttons, Input fields
- Tags/badges, Console badges, Rating stars, Breadcrumb
- Skeleton loaders, Empty states

### Pages
- `/design` - Raw design tokens and colors
- `/rubric` - Component showcase with examples

---

## Technology Stack

**Backend:**
- Laravel 11 + Octane (Swoole) for 2-5x speed boost
- MySQL for both Main and Show databases
- Laravel Sanctum for API authentication
- Laravel Queue for background jobs

**Frontend:**
- Livewire 3 for server-rendered interactivity
- Alpine.js for client-side reactivity
- Tailwind CSS with custom design tokens
- No JS framework needed (progressive enhancement)

**Infrastructure (Phase 1):**
- AWS Lightsail ($5/month)
- Local storage for images (S3 in Phase 2+)
- Database queue (Redis in Phase 3)

**Infrastructure (Phase 3):**
- EC2 Auto Scaling + RDS + ElastiCache + S3 + CloudFront
- Budget: ~$100-300/month for production scale

---

## File Structure

```
app/
  Models/
    MainDb/           # Main database models
    ShowDb/           # Show database models
  Jobs/               # Queue jobs
  Services/           # Business logic (voting, sync, renown)
  Http/
    Controllers/
      Api/V1/Admin/   # Admin API controllers
    Requests/         # Validation classes
    Resources/        # API resources
  Enums/              # Field types, statuses, etc.

resources/views/
  components/         # Reusable Blade components
  design.blade.php    # Design system page
  rubric.blade.php    # Component showcase

database/
  migrations/
    main_db/          # Main DB migrations
    show_db/          # Show DB migrations
  seeders/
    MainDb/           # Main DB seeders
    ShowDb/           # Show DB seeders
```

---

## Performance Considerations

### Caching Strategy
- Show DB data cached aggressively (rarely changes)
- Octane in-memory cache for hot data
- Response caching for static pages
- Redis cache in Phase 3

### Database Optimization
- All foreign keys indexed
- Compound indexes for common queries
- Eager loading to prevent N+1 queries
- Chunk large updates to prevent memory issues

### Queue Optimization
- Jobs only run when needed (winner changed)
- Batch processing for bulk updates
- Failed job retry logic with backoff
- Monitoring via Horizon (Phase 3)

---

## Security

### Authentication
- Laravel Breeze for web auth
- Sanctum for API tokens
- Bot users have unique tokens (revocable)

### Authorization
- Spatie Permissions for roles
- Middleware protects admin routes
- Rate limiting prevents abuse

### Data Validation
- Form requests validate all input
- API requests have strict validation
- SQL injection prevented by Eloquent
- XSS protection via Blade escaping

---

## Scaling Plan

**Phase 1 (Current):**
- Single Lightsail instance
- Both databases on same server
- Good for <10k users, <100k games

**Phase 2:**
- Same infrastructure
- Add Redis for sessions/cache
- Optimize queries as traffic grows

**Phase 3 (When needed):**
- Separate Main and Show DB to different servers
- Read replicas for Show DB
- EC2 auto-scaling (2-10 instances)
- CloudFront CDN for assets
- Migrate to managed RDS

**Trigger for Phase 3:**
- Lightsail CPU >70% sustained
- 100+ daily active users
- Making money or have funding
- Cost justifies upgrade ($100-300/mo)

---

## Development Workflow

### Local Setup
```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan octane:start --watch
```

### Running Queue Jobs
```bash
# Development
php artisan queue:work

# Production (via supervisor)
php artisan queue:work --queue=default --sleep=3 --tries=3
```

### Testing
```bash
./vendor/bin/sail test
```

### Deployment (Phase 1)
```bash
ssh lightsail
cd /var/www/adux
git pull
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan octane:reload
```

---

## Phase 2 Preparation

**What's Ready:**
- ✅ Database schema for all features
- ✅ Models and relationships
- ✅ Component library
- ✅ Design system
- ✅ Admin API for bot data seeding
- ✅ Winner calculation logic

**What Phase 2 Will Add:**
- Game browsing UI (from Show DB)
- Game detail pages
- UX metrics voting UI
- User registration/profiles
- Review system
- Forum basic functionality
- Winner calculation scheduling
- Public API v1 endpoints

**Estimated Phase 2 Timeline:** 3-6 months for MVP

---

## Troubleshooting

### Winner Not Updating
- Check queue is running: `php artisan queue:work`
- Check logs: `storage/logs/laravel.log`
- Manually dispatch: `CalculateSubmissionWinners::dispatch($game)`

### Sync Not Working
- Verify winner changed (only syncs on change)
- Check Show DB connection in `.env`
- Check `last_synced_at` in `games_display`

### Renown Not Calculating
- Verify `submission_votes` exist
- Check users have renown_points > 0
- Review `SubmissionWinnerService::calculateTotalRenown()`

---

## Further Reading

- [Laravel Octane Docs](https://laravel.com/docs/octane)
- [Livewire 3 Docs](https://livewire.laravel.com)
- [Tailwind CSS Docs](https://tailwindcss.com)
- [AWS Lightsail Guide](https://aws.amazon.com/lightsail/)

---

**Last Updated:** January 2026  
**Version:** 1.0 (Phase 1 Complete)
```

**2. DATABASE_SCHEMA.md** (or create ER diagram)

Use dbdiagram.io or similar to generate visual schema, or create detailed table documentation with all columns, types, indexes, and relationships.

**3. API_DOCUMENTATION.md**

Document all admin API endpoints with request/response examples (can use what's in TASK-1-004 as base).

**4. COMPONENT_GUIDE.md**

Document all 11 components with usage examples, props, and when to use each.

**5. README.md Updates**

Add Phase 1 completion status, link to new docs, update getting started guide.

DELIVERABLES:
1. ARCHITECTURE.md created
2. DATABASE_SCHEMA.md or ER diagram
3. API_DOCUMENTATION.md
4. COMPONENT_GUIDE.md
5. README.md updated
6. All docs cross-reference each other
7. Docs include code examples
8. Docs explain "why" not just "what"
```

## Implementation Notes

**Documentation Philosophy:**
- Explain architecture decisions (why dual DB, why renown, etc.)
- Include examples (code snippets, flows, diagrams)
- Write for future developers (or future you in 6 months)
- Keep updated as system evolves

**ER Diagram:**
Use dbdiagram.io with this syntax:
```
Table users {
  id bigint [pk]
  name varchar
  email varchar [unique]
  renown_points int [default: 0]
}

Table games {
  id bigint [pk]
  name varchar
  slug varchar [unique]
}

Ref: game_submissions.game_id > games.id
Ref: game_submissions.submitted_by > users.id
```

**Version Control:**
Tag Phase 1 completion in git:
```bash
git tag -a v1.0-phase1 -m "Phase 1 Architecture Complete"
git push origin v1.0-phase1
```

## Acceptance Criteria
- [ ] ARCHITECTURE.md created and comprehensive
- [ ] Database schema documented (diagram or detailed description)
- [ ] API endpoints documented with examples
- [ ] All components documented with usage
- [ ] README.md updated with Phase 1 status
- [ ] Docs explain "why" behind decisions
- [ ] Code examples are accurate and tested
- [ ] Cross-references work (links between docs)
- [ ] Troubleshooting section helpful
- [ ] Phase 2 preparation notes clear
- [ ] Git tagged with Phase 1 version

---
**Related Tasks:** All Phase 1 tasks  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 4-6 hours  
**Priority:** High - Essential for Phase 2 handoff
