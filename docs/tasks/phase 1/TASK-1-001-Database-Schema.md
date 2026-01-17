# [TASK-1-001] Dual-Database Schema Design

## Context
Design the core architecture of ADUX: a Main Database for all living data (submissions, votes, forums, karma) and an API/Show Database that serves only winning submissions to the public. This dual-database approach enables democratic data curation while maintaining fast, static-friendly public pages.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] PHP prototype database structure queries (if available)
- [ ] List of game fields that should be community-editable

**Conditions that must be met:**
- [ ] TASK-0-002 completed (Laravel with migrations ready)
- [ ] Understanding of karma-weighted voting system
- [ ] Clear picture of Main DB vs Show DB responsibilities

## Deliverables
- Complete ER diagram or schema documentation
- Laravel migration files for all Main DB tables
- Laravel migration files for all Show DB tables  
- Model relationships defined
- Seeders for initial data (platforms, consoles, sample games)
- Documentation of data sync strategy (Main → Show)
- Queue job structure for winner calculation and syncing

## AI Prompt
```
Design a dual-database Laravel schema for ADUX, a community-curated game database.

**ARCHITECTURE:**
Two separate MySQL databases:
1. **Main Database** - All living data, submissions, votes, forums, user activity
2. **API/Show Database** - Read-only winners for public display and API

**MAIN DATABASE TABLES:**

1. **users** - Standard auth + karma
   - id, name, email, password, karma_points, role, created_at, updated_at

2. **platforms** - Top-level (Nintendo, Sony, Microsoft, PC, Mobile)
   - id, name, slug, description, logo_url, created_at

3. **consoles** - Mid-level (Switch-2, PS5, Xbox Series X)
   - id, platform_id, name, slug, description, release_date, image_url

4. **games** - Bottom-level (actual games)
   - id, name (IMMUTABLE), slug, created_at, updated_at
   - Name is set at creation and never changes
   - All other fields come from submissions (see below)

5. **game_submissions** - CORE TABLE for democratic curation
   - id, game_id, field_type (box_art, description, developer, publisher, genre, release_date, screenshot_1, screenshot_2, etc.)
   - submitted_by (user_id), content (URL or text)
   - is_current_winner (boolean)
   - created_at, updated_at
   - Tracks all submissions for all editable fields

6. **submission_votes** - Karma-weighted voting
   - id, submission_id, user_id, created_at
   - Karma is calculated from user's current karma (not stored here)
   - Total karma per submission = SUM(voters.karma_points)

7. **ux_metric_votes** - The ethical rating system
   - id, user_id, game_id
   - time_vote (exploit/respect/null)
   - money_vote (exploit/respect/null)
   - psychology_vote (exploit/respect/null)
   - social_vote (exploit/respect/null)
   - privacy_vote (exploit/respect/null)
   - created_at, updated_at

8. **reviews**
   - id, user_id, game_id, rating (1-5), review_text, helpful_votes, created_at, updated_at
   - Soft deletes for moderation

9. **user_game_lists** - MyAnimeList feature
   - id, user_id, game_id
   - status (playing, completed, wishlist, bought, custom)
   - personal_rating, started_at, completed_at, notes

10. **forums** - Hierarchical forums
    - id, parent_type (platform/console/game), parent_id
    - name, description, created_at

11. **forum_threads**
    - id, forum_id, user_id, title, pinned, locked, created_at

12. **forum_posts**
    - id, thread_id, user_id, content, created_at, updated_at

13. **walkthroughs**
    - id, game_id, user_id, title, content
    - format (markdown/monospaced), karma_votes, created_at, updated_at

14. **karma_transactions** - Audit log of all karma changes
    - id, user_id, points, reason, reference_type, reference_id, created_at

15. **game_visits** - Track popularity for regeneration frequency
    - id, game_id, visit_count, last_visited_at

16. **new_game_submissions** - Approval queue
    - id, submitted_by, name, platform_id, console_id, initial_data (JSON), status (pending/approved/rejected), created_at

**API/SHOW DATABASE TABLES:**

1. **games_display** - Winning submissions only
   - id, game_id (from Main DB), name, slug
   - box_art_url, description, developer, publisher, genre, release_date
   - screenshot_1_url, screenshot_2_url, etc.
   - community_rating (avg), ux_metrics (JSON with percentages)
   - popularity_score, last_synced_at

2. **walkthroughs_display** - Top-voted walkthroughs
   - Copy of top-voted walkthroughs for each game
   - Synced when new walkthrough wins

**DATA FLOW:**
Main DB → Queue Job (calculate winners) → API/Show DB
- High-traffic games: Sync hourly/minutely
- Low-traffic games: Sync daily/weekly
- Only sync when winner changes

Create all migrations with proper indexes, foreign keys, and soft deletes where needed.
```

## Implementation Notes

### Karma-Weighted Voting
When calculating winner for a submission:
```php
$submission->total_karma = DB::table('submission_votes')
    ->join('users', 'submission_votes.user_id', '=', 'users.id')
    ->where('submission_id', $submission->id)
    ->sum('users.karma_points');
```

### Winner Calculation (Queue Job)
Run daily/hourly/minutely based on game popularity:
1. For each game field type (box_art, description, etc.)
2. Calculate total karma for each submission
3. If winner changed → mark new winner → sync to Show DB
4. Update `last_synced_at` on games_display

### Popularity-Based Sync Frequency
```php
// In queue job
if ($game->visit_count > 10000) {
    // High traffic: sync every 15 minutes
} elseif ($game->visit_count > 1000) {
    // Medium traffic: sync hourly
} else {
    // Low traffic: sync daily
}
```

### Game Submission Approval
High-karma users submit new games → manual mod approval initially → creates entry in Main DB `games` table with immutable name.

### Critical Indexes
- `game_submissions`: (game_id, field_type, is_current_winner)
- `submission_votes`: (submission_id, user_id) unique
- `ux_metric_votes`: (game_id, user_id) unique
- `user_game_lists`: (user_id, game_id, status)
- `game_visits`: (game_id, visit_count)

### Database Connection Config
```php
// config/database.php
'connections' => [
    'mysql' => [...], // Main DB
    'show_db' => [
        'driver' => 'mysql',
        'host' => env('SHOW_DB_HOST'),
        'database' => env('SHOW_DB_DATABASE'),
        // ... read-only user
    ],
]
```

## Acceptance Criteria
- [ ] All Main DB migrations run successfully
- [ ] All Show DB migrations run successfully  
- [ ] Can create test submissions and vote on them
- [ ] Winner calculation works correctly
- [ ] Data syncs from Main → Show DB
- [ ] Hierarchical structure (platforms → consoles → games) works
- [ ] Forums attach to correct parents
- [ ] Models and relationships defined
- [ ] Schema documented with ER diagram

## Questions to Resolve
1. Should Show DB be completely separate server or same server, different database?
2. Do we need real-time sync for anything or is scheduled always fine?
3. What karma thresholds for privileges? (TBD in Phase 2)
4. How to handle submission cleanup? (Archive old losing submissions?)

---
**Related Tasks:** TASK-1-002 (Models & Relationships), TASK-2-XXX (Submission UI), TASK-2-XXX (Winner Calculation Job)  
**Phase:** 1 (Architecture)  
**Estimated Time:** 8-12 hours (complex schema)  
**Priority:** Critical
