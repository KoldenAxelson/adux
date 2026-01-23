# [TASK-1-001] Dual-Database Schema Design

## Context
Design and implement the complete database architecture for ADUX: a Main Database for all living data (submissions, votes, forums, renown) and a Show Database that serves only winning submissions to the public. This dual-database approach enables democratic data curation while maintaining fast, static-friendly public pages.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] README.md (project overview and architecture decisions)
- [ ] TASK-1-000-Overview.md (Phase 1 goals and context)
- [ ] Design.md (for understanding the UX metrics system)

**Conditions that must be met:**
- [ ] Laravel 11 installed with Sail + Octane configured (Phase 0 complete)
- [ ] Understanding of renown-weighted voting system
- [ ] Clear understanding of Main DB vs Show DB responsibilities
- [ ] AWS Lightsail environment running

## Deliverables
- Complete database schema documentation (ER diagram or detailed description)
- Laravel migration files for all Main DB tables
- Laravel migration files for all Show DB tables
- Database connection configuration in `config/database.php`
- Seeder structure planning (to be implemented in TASK-1-003)
- Documentation of data sync strategy (Main → Show)
- Queue job structure planning for winner calculation

## AI Prompt
```
Design and implement a dual-database Laravel schema for ADUX, a community-curated game database with an ethics rating system.

**PROJECT CONTEXT:**
ADUX fights dark UX patterns in gaming by allowing communities to democratically curate game information and rate games on 5 ethical metrics (Time, Money, Psychology, Social, Privacy). All game data except the title is editable through community submissions weighted by user renown points.

**DUAL-DATABASE ARCHITECTURE:**

Two separate MySQL databases on same server (separate in Phase 3):
1. **Main Database** - All living data, submissions, votes, forums, user activity
2. **Show Database** - Read-only winning submissions for public display and API

**CRITICAL DESIGN DECISIONS:**

1. **Renown System (NOT Karma)**
   - Users earn "renown points" for contributions
   - Submission votes are weighted by voter's renown
   - Winner = submission with highest total renown from voters

2. **Multi-Platform Games**
   - Games can appear on multiple consoles
   - Use many-to-many relationship via pivot table
   - Example: Ocarina of Time appears on N64, GameCube, Wii, 3DS

3. **Immutable Title Only**
   - Game name/title is set at creation and NEVER changes (URL identifier)
   - Everything else is community-editable via submissions

4. **Editable Fields (via game_submissions table):**
   - box_art
   - release_date
   - publisher
   - developer
   - countries_of_release (JSON array)
   - languages_supported (JSON array)
   
   NOT editable: Price, Description, Screenshots (excluded for specific reasons)

5. **Bot Users**
   - LLM scraper bots are regular user accounts (ClaudeBot, GeminiBot, etc.)
   - High starting renown (10,000+ points)
   - Submit via authenticated API endpoints
   - Compete with each other - democracy decides best data

**MAIN DATABASE TABLES:**

1. **users** - Standard auth + renown
   - id (bigint, primary key)
   - name (string)
   - email (string, unique)
   - email_verified_at (timestamp, nullable)
   - password (string)
   - renown_points (integer, default 0)
   - created_at, updated_at
   - remember_token
   - Indexes: email

2. **platforms** - Top-level (Nintendo, Sony, Microsoft, PC, Mobile)
   - id (bigint, primary key)
   - name (string, unique)
   - slug (string, unique)
   - description (text, nullable)
   - logo_url (string, nullable)
   - created_at, updated_at
   - Indexes: slug

3. **consoles** - Mid-level (Switch, PS5, Xbox Series X, N64, etc.)
   - id (bigint, primary key)
   - platform_id (foreign key to platforms)
   - name (string)
   - slug (string, unique)
   - description (text, nullable)
   - release_date (date, nullable)
   - image_url (string, nullable)
   - created_at, updated_at
   - Indexes: slug, platform_id
   - Foreign keys: platform_id → platforms.id (cascade on delete)

4. **games** - Bottom-level (actual games)
   - id (bigint, primary key)
   - name (string) - IMMUTABLE, set at creation only
   - slug (string, unique) - Generated from name, used in URLs
   - created_at, updated_at
   - Indexes: slug, name
   - Note: All other data comes from game_submissions

5. **console_game** - Pivot table for many-to-many
   - id (bigint, primary key)
   - console_id (foreign key to consoles)
   - game_id (foreign key to games)
   - created_at, updated_at
   - Unique constraint: (console_id, game_id)
   - Indexes: console_id, game_id
   - Foreign keys: Both cascade on delete

6. **game_submissions** - CORE TABLE for democratic curation
   - id (bigint, primary key)
   - game_id (foreign key to games)
   - field_type (enum: 'box_art', 'release_date', 'publisher', 'developer', 'countries_of_release', 'languages_supported')
   - content (text) - Stores URL for images or JSON/text for other fields
   - submitted_by (foreign key to users)
   - is_current_winner (boolean, default false)
   - created_at, updated_at
   - soft_deletes (deleted_at for moderation)
   - Indexes: (game_id, field_type, is_current_winner), submitted_by
   - Foreign keys: game_id → games.id, submitted_by → users.id

7. **submission_votes** - Renown-weighted voting
   - id (bigint, primary key)
   - submission_id (foreign key to game_submissions)
   - user_id (foreign key to users)
   - created_at, updated_at
   - Unique constraint: (submission_id, user_id) - No double voting
   - Indexes: submission_id, user_id
   - Foreign keys: Both cascade on delete
   - Note: Renown is calculated from users.renown_points at vote time, not stored

8. **ux_metric_votes** - The 5 ethical rating metrics
   - id (bigint, primary key)
   - user_id (foreign key to users)
   - game_id (foreign key to games)
   - time_vote (enum: 'exploit', 'respect', nullable)
   - money_vote (enum: 'exploit', 'respect', nullable)
   - psychology_vote (enum: 'exploit', 'respect', nullable)
   - social_vote (enum: 'exploit', 'respect', nullable)
   - privacy_vote (enum: 'exploit', 'respect', nullable)
   - created_at, updated_at
   - Unique constraint: (user_id, game_id) - One vote per user per game
   - Indexes: game_id, user_id
   - Foreign keys: Both cascade on delete

9. **reviews**
   - id (bigint, primary key)
   - user_id (foreign key to users)
   - game_id (foreign key to games)
   - rating (tinyint: 1-5)
   - review_text (text)
   - helpful_votes (integer, default 0)
   - created_at, updated_at
   - soft_deletes
   - Unique constraint: (user_id, game_id)
   - Indexes: game_id, user_id
   - Foreign keys: Both cascade on delete

10. **user_game_lists** - MyAnimeList-style tracking
    - id (bigint, primary key)
    - user_id (foreign key to users)
    - game_id (foreign key to games)
    - status (enum: 'playing', 'completed', 'wishlist', 'bought', 'dropped', 'custom')
    - personal_rating (tinyint: 1-5, nullable)
    - started_at (date, nullable)
    - completed_at (date, nullable)
    - notes (text, nullable)
    - created_at, updated_at
    - Unique constraint: (user_id, game_id)
    - Indexes: user_id, (user_id, status)
    - Foreign keys: Both cascade on delete

11. **forums** - Hierarchical discussion forums
    - id (bigint, primary key)
    - parent_type (enum: 'platform', 'console', 'game')
    - parent_id (bigint) - References platform/console/game id
    - name (string)
    - description (text, nullable)
    - created_at, updated_at
    - Indexes: (parent_type, parent_id)

12. **forum_threads**
    - id (bigint, primary key)
    - forum_id (foreign key to forums)
    - user_id (foreign key to users)
    - title (string)
    - pinned (boolean, default false)
    - locked (boolean, default false)
    - created_at, updated_at
    - soft_deletes
    - Indexes: forum_id, user_id, created_at
    - Foreign keys: Both cascade on delete

13. **forum_posts**
    - id (bigint, primary key)
    - thread_id (foreign key to forum_threads)
    - user_id (foreign key to users)
    - content (text)
    - created_at, updated_at
    - soft_deletes
    - Indexes: thread_id, user_id, created_at
    - Foreign keys: Both cascade on delete

14. **walkthroughs**
    - id (bigint, primary key)
    - game_id (foreign key to games)
    - user_id (foreign key to users)
    - title (string)
    - content (longtext)
    - format (enum: 'markdown', 'monospaced')
    - helpful_votes (integer, default 0)
    - created_at, updated_at
    - soft_deletes
    - Indexes: game_id, user_id
    - Foreign keys: Both cascade on delete

15. **renown_transactions** - Audit log of all renown changes
    - id (bigint, primary key)
    - user_id (foreign key to users)
    - points (integer) - Can be positive or negative
    - reason (string) - e.g., "Submission accepted", "Review helpful vote"
    - reference_type (string, nullable) - e.g., "App\Models\GameSubmission"
    - reference_id (bigint, nullable)
    - created_at
    - Indexes: user_id, created_at
    - Foreign key: user_id → users.id

16. **game_visits** - Track popularity for sync frequency
    - id (bigint, primary key)
    - game_id (foreign key to games, unique)
    - visit_count (bigint, default 0)
    - last_visited_at (timestamp)
    - created_at, updated_at
    - Indexes: game_id, visit_count
    - Foreign key: game_id → games.id (cascade on delete)

17. **new_game_submissions** - Approval queue for new games
    - id (bigint, primary key)
    - submitted_by (foreign key to users)
    - name (string) - Proposed game name
    - console_ids (json) - Array of console IDs
    - initial_data (json) - Box art URL, developer, etc.
    - status (enum: 'pending', 'approved', 'rejected')
    - reviewed_by (foreign key to users, nullable)
    - reviewed_at (timestamp, nullable)
    - rejection_reason (text, nullable)
    - created_at, updated_at
    - Indexes: submitted_by, status, created_at
    - Foreign keys: submitted_by and reviewed_by → users.id

**SHOW DATABASE TABLES:**

1. **games_display** - Winning submissions only
   - id (bigint, primary key)
   - game_id (bigint) - References Main DB games.id (NOT a foreign key)
   - name (string)
   - slug (string, unique)
   - box_art_url (string, nullable)
   - release_date (date, nullable)
   - publisher (string, nullable)
   - developer (string, nullable)
   - countries_of_release (json, nullable)
   - languages_supported (json, nullable)
   - console_ids (json) - Array of console IDs for display
   - community_rating_avg (decimal(3,2), nullable) - Average of reviews
   - ux_metrics (json) - Percentages for each metric
   - popularity_score (integer, default 0)
   - last_synced_at (timestamp)
   - created_at, updated_at
   - Indexes: slug, game_id, popularity_score

2. **consoles_display** - Copy of consoles for Show DB
   - id (bigint, primary key)
   - console_id (bigint) - References Main DB consoles.id
   - platform_id (bigint)
   - name (string)
   - slug (string, unique)
   - description (text, nullable)
   - release_date (date, nullable)
   - image_url (string, nullable)
   - created_at, updated_at
   - Indexes: slug, console_id

3. **platforms_display** - Copy of platforms for Show DB
   - id (bigint, primary key)
   - platform_id (bigint) - References Main DB platforms.id
   - name (string)
   - slug (string, unique)
   - description (text, nullable)
   - logo_url (string, nullable)
   - created_at, updated_at
   - Indexes: slug, platform_id

**DATA SYNC STRATEGY:**

Main DB → Queue Job (calculate winners) → Show DB

**Winner Calculation Logic:**
```php
// For each game and each field_type
$winner = GameSubmission::where('game_id', $game->id)
    ->where('field_type', 'box_art')
    ->withCount(['votes as total_renown' => function($query) {
        $query->join('users', 'submission_votes.user_id', '=', 'users.id')
              ->select(DB::raw('SUM(users.renown_points)'));
    }])
    ->orderByDesc('total_renown')
    ->first();
```

**Sync Frequency (based on game_visits):**
- High traffic (10k+ visits): Sync every 15 minutes
- Medium traffic (1k-10k): Sync hourly
- Low traffic (<1k): Sync daily

**Queue Jobs Structure (to implement in TASK-1-008):**
- `CalculateSubmissionWinners` - Runs periodically based on game popularity
- `SyncGameToShowDB` - Syncs winning data from Main → Show DB
- Only syncs when winner has changed (efficiency)

**DATABASE CONNECTION CONFIG:**

Update `config/database.php`:
```php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'adux_main'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        // ... other config
    ],
    
    'show_db' => [
        'driver' => 'mysql',
        'host' => env('SHOW_DB_HOST', '127.0.0.1'),
        'port' => env('SHOW_DB_PORT', '3306'),
        'database' => env('SHOW_DB_DATABASE', 'adux_show'),
        'username' => env('SHOW_DB_USERNAME', 'forge'),
        'password' => env('SHOW_DB_PASSWORD', ''),
        // ... other config
        'read' => [
            'host' => [env('SHOW_DB_HOST', '127.0.0.1')],
        ],
    ],
],
```

Add to `.env`:
```
# Main Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adux_main
DB_USERNAME=sail
DB_PASSWORD=password

# Show Database
SHOW_DB_HOST=127.0.0.1
SHOW_DB_PORT=3306
SHOW_DB_DATABASE=adux_show
SHOW_DB_USERNAME=sail
SHOW_DB_PASSWORD=password
```

**MIGRATION STRUCTURE:**

Organize migrations in subdirectories:
```
database/migrations/
  main_db/
    2024_01_01_000001_create_users_table.php
    2024_01_01_000002_create_platforms_table.php
    ... (all Main DB tables)
  show_db/
    2024_01_01_100001_create_games_display_table.php
    ... (all Show DB tables)
```

Create custom migration command to run specific database migrations:
```bash
php artisan migrate --path=database/migrations/main_db --database=mysql
php artisan migrate --path=database/migrations/show_db --database=show_db
```

**DELIVERABLES:**
1. All migration files created in proper directory structure
2. Database connections configured
3. Schema documentation (this file serves as the documentation)
4. Test that migrations run successfully on both databases
5. Create database diagram (optional but recommended - use dbdiagram.io or similar)
```

## Implementation Notes

**Migration Best Practices:**
- Use `$table->foreign()` for all foreign keys
- Add indexes on all foreign keys automatically
- Use `onDelete('cascade')` where appropriate
- Use `softDeletes()` for user-generated content (reviews, posts, submissions)
- Use `timestamps()` on all tables except pure pivot tables

**Enum Considerations:**
Laravel 11 supports native enum casting. For field_type, ux_metric votes, etc., use string enums initially, then migrate to PHP enums in models later for type safety.

**JSON Columns:**
- `countries_of_release`: Store as JSON array: `["US", "JP", "EU"]`
- `languages_supported`: Store as JSON array: `["English", "Japanese"]`
- `ux_metrics` in Show DB: Store as JSON object: `{"time": 72, "money": 85, ...}`

**Renown Calculation:**
Total renown for a submission = SUM(renown_points) of all users who voted for it. This is calculated on-the-fly during winner determination, not stored.

**Index Strategy:**
- Index all foreign keys
- Index commonly queried columns (slug, status, created_at)
- Compound indexes for common query patterns: (user_id, game_id), (game_id, field_type)
- Add `->index()` in migrations for these

**Database Size Estimates (for planning):**
- With 100k games, Main DB ~50-100GB
- Show DB much smaller ~10-20GB (only winners)
- Separation becomes critical at scale (Phase 3)

**Performance Considerations:**
- Main DB writes are isolated from Show DB reads
- Show DB can be cached aggressively (data rarely changes)
- Game visits tracking uses simple counter increment (fast)
- Winner calculation runs in background (doesn't block requests)

## Acceptance Criteria
- [ ] All Main DB migrations run successfully: `php artisan migrate --path=database/migrations/main_db`
- [ ] All Show DB migrations run successfully: `php artisan migrate --path=database/migrations/show_db --database=show_db`
- [ ] Can create test data manually and verify relationships work
- [ ] Foreign key constraints enforce data integrity
- [ ] Indexes exist on all commonly queried columns
- [ ] Both databases visible in database client (TablePlus, phpMyAdmin, etc.)
- [ ] `.env` configured with both database connections
- [ ] `config/database.php` has both connections defined
- [ ] Schema documented (either in separate doc or this task file)
- [ ] No migration errors or warnings

## Testing After Completion

Create a simple test script or use tinker:
```php
// Test Main DB relationships
$platform = Platform::create(['name' => 'Nintendo', 'slug' => 'nintendo']);
$console = Console::create(['platform_id' => $platform->id, 'name' => 'N64', 'slug' => 'n64']);
$game = Game::create(['name' => 'Ocarina of Time', 'slug' => 'ocarina-of-time']);
$game->consoles()->attach($console->id);

// Test user and renown
$user = User::create(['name' => 'ClaudeBot', 'email' => 'claude@bot.com', 'password' => bcrypt('password'), 'renown_points' => 10000]);

// Test submission
$submission = GameSubmission::create([
    'game_id' => $game->id,
    'field_type' => 'box_art',
    'content' => 'https://example.com/box-art.jpg',
    'submitted_by' => $user->id
]);

// Test vote
SubmissionVote::create(['submission_id' => $submission->id, 'user_id' => $user->id]);

// Verify relationships load
$game->load('consoles', 'submissions');
```

---
**Related Tasks:** TASK-1-002 (Models & Relationships), TASK-1-003 (Seeders), TASK-1-008 (Winner Calculation Job)  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 10-16 hours (complex schema with two databases)  
**Priority:** Critical - Everything else depends on this
