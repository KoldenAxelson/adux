# [TASK-1-008] Winner Calculation Queue Job (Stub)

## Context
Create the queue job structure that will calculate winning submissions and sync data from Main DB to Show DB. This task creates the job skeleton and basic logic in Phase 1, to be fully implemented and scheduled in Phase 2 when actual voting begins.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] TASK-1-001-Database-Schema.md (winner calculation logic)
- [ ] TASK-1-002-Models-Relationships.md (model methods)
- [ ] README.md (sync strategy overview)

**Conditions that must be met:**
- [ ] TASK-1-001 and 1-002 complete (database and models exist)
- [ ] Understanding of renown-weighted voting
- [ ] Laravel queues configured (database queue is fine for Phase 1)

## Deliverables
- `CalculateSubmissionWinners` queue job
- `SyncGameToShowDB` queue job
- Service class for winner calculation logic
- Service class for Main→Show sync logic
- Queue configuration
- Job dispatch examples (manual for Phase 1)
- Documentation of how jobs will be scheduled in Phase 2

## AI Prompt
```
Create Laravel queue jobs for calculating winning submissions and syncing data from Main DB to Show DB.

**WINNER CALCULATION STRATEGY:**

For each game and each field type:
1. Get all submissions for that field
2. Calculate total renown for each (sum of voter renown)
3. Submission with highest renown wins
4. Mark as current winner (is_current_winner = true)
5. If winner changed → trigger sync to Show DB

**SYNC STRATEGY:**

- Only sync when winner changes (efficiency)
- Sync frequency based on game popularity (visit_count)
- High-traffic games sync more frequently
- Batch updates to Show DB to minimize writes

**QUEUE JOBS TO CREATE:**

**1. CALCULATE SUBMISSION WINNERS JOB**

File: `app/Jobs/CalculateSubmissionWinners.php`

```php
namespace App\Jobs;

use App\Models\MainDb\Game;
use App\Services\SubmissionWinnerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateSubmissionWinners implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Game $game
    ) {}

    public function handle(SubmissionWinnerService $service): void
    {
        \Log::info("Calculating winners for game: {$this->game->name}");

        $winnersChanged = $service->calculateWinners($this->game);

        if ($winnersChanged) {
            \Log::info("Winners changed for game {$this->game->id}, dispatching sync");
            SyncGameToShowDB::dispatch($this->game);
        } else {
            \Log::info("No winner changes for game {$this->game->id}");
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error("Failed to calculate winners for game {$this->game->id}: {$exception->getMessage()}");
    }
}
```

**2. SYNC GAME TO SHOW DB JOB**

File: `app/Jobs/SyncGameToShowDB.php`

```php
namespace App\Jobs;

use App\Models\MainDb\Game;
use App\Services\ShowDBSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncGameToShowDB implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Game $game
    ) {}

    public function handle(ShowDBSyncService $service): void
    {
        \Log::info("Syncing game {$this->game->id} to Show DB");

        $service->syncGame($this->game);

        \Log::info("Successfully synced game {$this->game->id} to Show DB");
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error("Failed to sync game {$this->game->id} to Show DB: {$exception->getMessage()}");
    }
}
```

**3. SUBMISSION WINNER SERVICE**

File: `app/Services/SubmissionWinnerService.php`

```php
namespace App\Services;

use App\Enums\FieldType;
use App\Models\MainDb\Game;
use App\Models\MainDb\GameSubmission;
use Illuminate\Support\Facades\DB;

class SubmissionWinnerService
{
    /**
     * Calculate winners for all field types of a game
     * Returns true if any winners changed
     */
    public function calculateWinners(Game $game): bool
    {
        $anyChanged = false;

        foreach (FieldType::cases() as $fieldType) {
            $changed = $this->calculateWinnerForField($game, $fieldType);
            if ($changed) {
                $anyChanged = true;
            }
        }

        return $anyChanged;
    }

    /**
     * Calculate winner for a specific field type
     * Returns true if winner changed
     */
    protected function calculateWinnerForField(Game $game, FieldType $fieldType): bool
    {
        // Get all submissions for this field, with their total renown
        $submissions = GameSubmission::where('game_id', $game->id)
            ->where('field_type', $fieldType)
            ->get()
            ->map(function ($submission) {
                $submission->total_renown = $this->calculateTotalRenown($submission);
                return $submission;
            })
            ->sortByDesc('total_renown');

        // No submissions? No winner
        if ($submissions->isEmpty()) {
            return false;
        }

        $newWinner = $submissions->first();
        $currentWinner = GameSubmission::where('game_id', $game->id)
            ->where('field_type', $fieldType)
            ->where('is_current_winner', true)
            ->first();

        // Winner hasn't changed
        if ($currentWinner && $currentWinner->id === $newWinner->id) {
            return false;
        }

        // Update winners
        DB::transaction(function () use ($game, $fieldType, $newWinner, $currentWinner) {
            // Unmark old winner
            if ($currentWinner) {
                $currentWinner->update(['is_current_winner' => false]);
            }

            // Mark new winner
            $newWinner->update(['is_current_winner' => true]);

            \Log::info("Winner changed for game {$game->id}, field {$fieldType->value}: submission {$newWinner->id} (renown: {$newWinner->total_renown})");
        });

        return true;
    }

    /**
     * Calculate total renown for a submission
     */
    protected function calculateTotalRenown(GameSubmission $submission): int
    {
        return (int) DB::table('submission_votes')
            ->join('users', 'submission_votes.user_id', '=', 'users.id')
            ->where('submission_votes.submission_id', $submission->id)
            ->sum('users.renown_points');
    }
}
```

**4. SHOW DB SYNC SERVICE**

File: `app/Services/ShowDBSyncService.php`

```php
namespace App\Services;

use App\Enums\FieldType;
use App\Models\MainDb\Game;
use App\Models\ShowDb\GameDisplay;
use Illuminate\Support\Facades\DB;

class ShowDBSyncService
{
    /**
     * Sync a game from Main DB to Show DB
     */
    public function syncGame(Game $game): void
    {
        $game->load(['consoles', 'submissions' => fn($q) => $q->where('is_current_winner', true)]);

        // Get winning submissions
        $winningData = $this->extractWinningData($game);

        // Calculate UX metrics
        $uxMetrics = $this->calculateUxMetrics($game);

        // Get community rating
        $communityRating = $this->getCommunityRating($game);

        // Update or create in Show DB
        GameDisplay::updateOrCreate(
            ['game_id' => $game->id],
            [
                'name' => $game->name,
                'slug' => $game->slug,
                'box_art_url' => $winningData['box_art'] ?? null,
                'release_date' => $winningData['release_date'] ?? null,
                'publisher' => $winningData['publisher'] ?? null,
                'developer' => $winningData['developer'] ?? null,
                'countries_of_release' => $winningData['countries_of_release'] ?? null,
                'languages_supported' => $winningData['languages_supported'] ?? null,
                'console_ids' => $game->consoles->pluck('id')->toArray(),
                'community_rating_avg' => $communityRating,
                'ux_metrics' => $uxMetrics,
                'popularity_score' => $this->calculatePopularityScore($game),
                'last_synced_at' => now(),
            ]
        );

        \Log::info("Game {$game->id} synced to Show DB");
    }

    /**
     * Extract winning submission data
     */
    protected function extractWinningData(Game $game): array
    {
        $data = [];

        foreach ($game->submissions as $submission) {
            $fieldKey = $submission->field_type->value;
            
            // Parse content based on field type
            $data[$fieldKey] = match($submission->field_type) {
                FieldType::COUNTRIES_OF_RELEASE, 
                FieldType::LANGUAGES_SUPPORTED => json_decode($submission->content, true),
                default => $submission->content
            };
        }

        return $data;
    }

    /**
     * Calculate UX metrics percentages
     */
    protected function calculateUxMetrics(Game $game): array
    {
        $votes = DB::connection('mysql')
            ->table('ux_metric_votes')
            ->where('game_id', $game->id)
            ->get();

        if ($votes->isEmpty()) {
            return [
                'time' => 0,
                'money' => 0,
                'psychology' => 0,
                'social' => 0,
                'privacy' => 0,
            ];
        }

        $metrics = [];
        $fields = ['time', 'money', 'psychology', 'social', 'privacy'];

        foreach ($fields as $field) {
            $fieldVotes = $votes->pluck("{$field}_vote")->filter();
            $respectCount = $fieldVotes->filter(fn($v) => $v === 'respect')->count();
            $totalCount = $fieldVotes->count();

            $metrics[$field] = $totalCount > 0 
                ? round(($respectCount / $totalCount) * 100) 
                : 0;
        }

        return $metrics;
    }

    /**
     * Get community rating average
     */
    protected function getCommunityRating(Game $game): ?float
    {
        $average = DB::connection('mysql')
            ->table('reviews')
            ->where('game_id', $game->id)
            ->whereNull('deleted_at')
            ->avg('rating');

        return $average ? round($average, 2) : null;
    }

    /**
     * Calculate popularity score (for sorting)
     */
    protected function calculatePopularityScore(Game $game): int
    {
        $visit = DB::connection('mysql')
            ->table('game_visits')
            ->where('game_id', $game->id)
            ->first();

        return $visit ? $visit->visit_count : 0;
    }
}
```

**QUEUE CONFIGURATION:**

Update `config/queue.php`:
```php
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],
],
```

Create jobs table migration:
```bash
php artisan queue:table
php artisan migrate
```

**MANUAL DISPATCH EXAMPLES (Phase 1 Testing):**

```php
// In tinker or controller
use App\Jobs\CalculateSubmissionWinners;
use App\Models\MainDb\Game;

// Calculate winners for a specific game
$game = Game::find(1);
CalculateSubmissionWinners::dispatch($game);

// Process queue
php artisan queue:work

// Or run synchronously for testing
CalculateSubmissionWinners::dispatchSync($game);
```

**FUTURE SCHEDULING (Phase 2):**

In `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    // High-traffic games (10k+ visits): every 15 minutes
    $schedule->call(function () {
        Game::whereHas('visits', fn($q) => $q->where('visit_count', '>=', 10000))
            ->chunk(100, function ($games) {
                foreach ($games as $game) {
                    CalculateSubmissionWinners::dispatch($game);
                }
            });
    })->everyFifteenMinutes();

    // Medium-traffic games (1k-10k visits): hourly
    $schedule->call(function () {
        Game::whereHas('visits', fn($q) => $q->whereBetween('visit_count', [1000, 9999]))
            ->chunk(100, function ($games) {
                foreach ($games as $game) {
                    CalculateSubmissionWinners::dispatch($game);
                }
            });
    })->hourly();

    // Low-traffic games (<1k visits): daily
    $schedule->call(function () {
        Game::whereHas('visits', fn($q) => $q->where('visit_count', '<', 1000))
            ->chunk(100, function ($games) {
                foreach ($games as $game) {
                    CalculateSubmissionWinners::dispatch($game);
                }
            });
    })->daily();
}
```

DELIVERABLES:
1. Both queue jobs created
2. Both service classes implemented
3. Jobs table migration run
4. Can manually dispatch jobs successfully
5. Jobs log activity properly
6. Winner calculation logic works
7. Sync to Show DB works
8. Documentation of scheduling strategy
```

## Implementation Notes

**Why Separate Jobs:**
- `CalculateSubmissionWinners` can run frequently without syncing
- `SyncGameToShowDB` only runs when needed (winner changed)
- Separation allows different retry policies

**Renown Calculation:**
Uses JOIN to get current renown at calculation time. If user's renown changes, recalculation reflects the new weight.

**Transaction Safety:**
Winner updates wrapped in DB transaction to ensure consistency.

**Logging:**
Extensive logging helps debug winner calculation issues in Phase 2.

**Queue Choice:**
Database queue is fine for Phase 1/2. Switch to Redis in Phase 3 for better performance at scale.

**Error Handling:**
Jobs have `failed()` methods to log failures. In Phase 2, add alerting (email, Slack).

**Testing Winner Calculation:**

```php
// Create test scenario in tinker
$game = Game::first();
$user1 = User::find(1); // High renown
$user2 = User::find(2); // Low renown

$submission1 = GameSubmission::create([...]);
$submission2 = GameSubmission::create([...]);

SubmissionVote::create(['submission_id' => $submission1->id, 'user_id' => $user1->id]);
SubmissionVote::create(['submission_id' => $submission2->id, 'user_id' => $user2->id]);

CalculateSubmissionWinners::dispatchSync($game);

// Check: submission1 should win (higher voter renown)
$submission1->refresh();
$submission1->is_current_winner; // Should be true
```

## Acceptance Criteria
- [ ] Jobs created in `app/Jobs/`
- [ ] Services created in `app/Services/`
- [ ] Jobs table migration run
- [ ] Can dispatch jobs manually via tinker
- [ ] Winner calculation correctly identifies highest renown
- [ ] Only syncs when winner changes
- [ ] Show DB receives correct data
- [ ] UX metrics calculate correctly
- [ ] Jobs log activity
- [ ] Failed jobs are logged
- [ ] Can process queue with `php artisan queue:work`
- [ ] Documentation of Phase 2 scheduling strategy complete

---
**Related Tasks:** TASK-1-001 (Schema), TASK-1-002 (Models), TASK-1-004 (Admin API)  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 6-8 hours  
**Priority:** High - Core functionality
