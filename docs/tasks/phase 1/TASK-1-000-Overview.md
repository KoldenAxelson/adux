# [TASK-1-000] Phase 1 Overview: Architecture & Foundation

## Purpose
This is a conversational task to plan and generate all Phase 1 tasks with the user. Phase 1 focuses on establishing the dual-database architecture, hierarchical platform/console/game structure, submission/voting system, and design foundation before feature development begins.

## Phase 1 Goals
- Design Main Database schema (all living data)
- Design API/Show Database schema (winning submissions only)
- Build hierarchical structure (Platforms → Consoles → Games)
- Design submission/voting system architecture
- Build design system at `/rubric`
- Create reusable Blade components
- Establish API v1 foundation with Sanctum
- Set up authentication and authorization

## Key Decisions Already Made

### Dual-Database Architecture
**Main Database:**
- All submissions for editable fields (box art, descriptions, screenshots, etc.)
- Karma-weighted votes on submissions
- Forums (hierarchical: platform/console/game)
- User activity (reviews, lists, walkthroughs, karma transactions)
- This is where you work and admin

**API/Show Database:**
- Read-only winning submissions
- Serves public pages and API requests
- Only updates when winners change
- Update frequency based on game popularity (visit tracking)

**Sync Strategy:**
- Winner-at-end-of-day (or hour/minute for popular games)
- Queue job calculates winners and syncs to Show DB
- Only sync when winner changes (efficiency)

### Hierarchical Structure
```
Platform (Nintendo) → has page, has forum
  └─ Console (Switch-2) → has page, has forum
      └─ Game (Tears of the Kingdom) → has page, has forum
```

Each level gets its own detail page and discussion forum.

### Submission/Voting System
- Every editable field can have multiple community submissions
- Users vote on submissions (vote weight = their karma)
- Winner = highest total karma
- Users can submit new OR vote for existing
- Only game **name** is immutable (set at creation)

### Design System Structure
**Location:** `/resources/views/rubric/`

**Files:**
- `index.blade.php` - Navigation hub
- `foundation.blade.php` - Static design elements
- `elements.blade.php` - Interactive components showcase

**Philosophy:**
- Dumb, simple components
- One responsibility each
- Composition over complexity
- Extract after 2-3 uses

### Static Generation Priority
- Show Database content = static generation first
- Walkthroughs/guides = static too
- SEO/AI scraping optimized
- Regenerate based on popularity:
  - High traffic games: hourly/minutely
  - Low traffic games: daily/weekly

## Conversation Starters for AI

When a user starts this phase with you, have a conversation to:

1. **Dual-Database Schema**
   - "Should Main and Show DBs be on same server or separate?"
   - "What fields are community-editable vs locked?"
   - "How do we handle submission cleanup? Archive old losers?"
   - "What's the queue job structure for winner calculation?"

2. **Hierarchical Structure**
   - "How do we handle multi-platform games? (appears under multiple consoles?)"
   - "Do platforms/consoles need approval workflow like games?"
   - "What metadata do platforms/consoles need?"

3. **Submission System**
   - "What field types exist? (box_art, description, screenshot_1, etc.)"
   - "How many screenshots per game?"
   - "Can users delete their submissions after voting starts?"
   - "How to prevent spam submissions?"

4. **Forums Architecture**
   - "Do we need categories within game forums? (General, Tips, Bugs)"
   - "Moderation tools needed in Phase 1?"
   - "Thread tagging/filtering?"

5. **Design System**
   - "What's the aesthetic? (PHP prototype has specific vibe)"
   - "What components are essential for Phase 1?"
   - "Color palette and typography decisions?"

## Suggested Phase 1 Tasks to Generate

After conversation with user, create tasks like:

- **TASK-1-001:** Dual-Database Schema Design ✅ (Already created)
- **TASK-1-002:** Laravel Models & Relationships
- **TASK-1-003:** Database Seeders (Platforms, Consoles, Sample Games)
- **TASK-1-004:** Design System Foundation (`/rubric/foundation.blade.php`)
- **TASK-1-005:** Hierarchical Navigation Component
- **TASK-1-006:** Game Card Component
- **TASK-1-007:** UX Metrics Display Component
- **TASK-1-008:** Forum Thread Component
- **TASK-1-009:** API v1 Routes & Resources Setup
- **TASK-1-010:** Winner Calculation Queue Job (stub for Phase 2)
- **TASK-1-011:** Elements Showcase Page (`/rubric/elements.blade.php`)

## AI Prompt Template
```
I'm starting Phase 1 of ADUX, a community-curated game database. We've completed environment setup. Now we need to:

1. Design dual-database schema (Main + Show)
2. Build hierarchical structure (Platforms → Consoles → Games)
3. Design submission/voting system
4. Build design system at /rubric
5. Create reusable components
6. Set up API foundation

Here are the key decisions:
- Main DB: all submissions, votes, forums, user activity
- Show DB: winning submissions only, serves public/API
- Karma-weighted voting on submissions
- Static generation based on game popularity
- Only game name is immutable

I have a PHP prototype with some design decisions:
[User will upload PHP files]

Let's start by refining the database schema. The dual-database architecture is set, but we need to finalize table structures and relationships.
```

## Important Reminders

### For Database Design:
- Plan queue jobs for winner calculation now
- Index everything that will be queried
- Use soft deletes for moderation
- Separate concerns: Main DB = work, Show DB = display

### For Hierarchical Structure:
- Platforms are few (10-20 total)
- Consoles are many (hundreds over time)
- Games are massive (millions eventually)
- Each level needs: pages, forums, navigation

### For Submission System:
- Every field type needs validation rules
- Image submissions need storage strategy (S3 later)
- Text submissions need sanitization
- Voting must prevent double-voting (unique constraint)

### For Design System:
- Use PHP prototype as aesthetic reference
- Components should work for Main DB editing UI too
- Static showcase pages help onboarding

### For API:
- Version from day one (`/api/v1/`)
- Only serve from Show DB (fast, static)
- Use API Resources for clean JSON
- Plan rate limiting structure

## Success Criteria for Phase 1

At the end of Phase 1, you should have:
- [ ] Dual-database schema designed and migrated
- [ ] Models with relationships defined
- [ ] Seeders create sample data
- [ ] `/rubric/foundation.blade.php` with design system
- [ ] 8-10 reusable components in `/resources/views/components/`
- [ ] Components showcased in `/rubric/elements.blade.php`
- [ ] API v1 routes defined with Resources
- [ ] Authentication working
- [ ] Winner calculation queue job (stubbed, implemented Phase 2)
- [ ] Clear documentation of architecture decisions

**No actual voting/forum features yet** - that's Phase 2. This phase is pure foundation.

---
**Next Phase:** TASK-2-000 (Feature Development)  
**Phase:** 1 (Architecture & Foundation)  
**Approach:** Conversational - discuss, plan, then generate specific tasks  
**Estimated Duration:** 3-4 weeks of focused work
