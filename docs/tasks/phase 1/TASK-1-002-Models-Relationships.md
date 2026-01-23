# [TASK-1-002] Laravel Models & Relationships

## Context
Create all Eloquent models for Main DB and Show DB with proper relationships, accessors, mutators, and scopes. Models serve as the interface between the database schema and application logic, making data manipulation clean and maintainable.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] TASK-1-001-Database-Schema.md (completed schema)
- [ ] README.md (project architecture overview)

**Conditions that must be met:**
- [ ] TASK-1-001 complete (all migrations run successfully)
- [ ] Both databases exist and are accessible
- [ ] Laravel 11 with proper database connections configured

## Deliverables
- All Main DB models in `app/Models/MainDb/`
- All Show DB models in `app/Models/ShowDb/`
- Proper Eloquent relationships defined
- Renown calculation logic in User model
- Field type enums for type safety
- Model documentation comments
- Scopes for common queries
- Accessors/mutators where needed

## AI Prompt
```
Create Laravel 11 Eloquent models for ADUX dual-database architecture.

**DIRECTORY STRUCTURE:**
```
app/Models/
  MainDb/
    User.php
    Platform.php
    Console.php
    Game.php
    GameSubmission.php
    SubmissionVote.php
    UxMetricVote.php
    Review.php
    UserGameList.php
    Forum.php
    ForumThread.php
    ForumPost.php
    Walkthrough.php
    RenownTransaction.php
    GameVisit.php
    NewGameSubmission.php
  ShowDb/
    GameDisplay.php
    ConsoleDisplay.php
    PlatformDisplay.php
```

**MODEL REQUIREMENTS:**

All models should:
- Use proper namespace (MainDb or ShowDb)
- Define `$fillable` or `$guarded` attributes
- Specify `$casts` for proper type conversion
- Include relationships with type hints
- Add PHPDoc comments for IDE support
- Use `SoftDeletes` trait where applicable

**ENUMS TO CREATE:**

Create in `app/Enums/`:

1. **FieldType.php**
```php
enum FieldType: string
{
    case BOX_ART = 'box_art';
    case RELEASE_DATE = 'release_date';
    case PUBLISHER = 'publisher';
    case DEVELOPER = 'developer';
    case COUNTRIES_OF_RELEASE = 'countries_of_release';
    case LANGUAGES_SUPPORTED = 'languages_supported';
}
```

2. **UxMetricVote.php**
```php
enum UxMetricVoteValue: string
{
    case EXPLOIT = 'exploit';
    case RESPECT = 'respect';
}
```

3. **GameListStatus.php**
```php
enum GameListStatus: string
{
    case PLAYING = 'playing';
    case COMPLETED = 'completed';
    case WISHLIST = 'wishlist';
    case BOUGHT = 'bought';
    case DROPPED = 'dropped';
    case CUSTOM = 'custom';
}
```

4. **SubmissionStatus.php**
```php
enum SubmissionStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
```

5. **ForumParentType.php**
```php
enum ForumParentType: string
{
    case PLATFORM = 'platform';
    case CONSOLE = 'console';
    case GAME = 'game';
}
```

6. **WalkthroughFormat.php**
```php
enum WalkthroughFormat: string
{
    case MARKDOWN = 'markdown';
    case MONOSPACED = 'monospaced';
}
```

**KEY MODELS:**

### User.php (MainDb)
```php
namespace App\Models\MainDb;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $connection = 'mysql'; // Main DB

    protected $fillable = [
        'name',
        'email',
        'password',
        'renown_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'renown_points' => 'integer',
        'password' => 'hashed',
    ];

    // Relationships
    public function gameSubmissions() { /* hasMany */ }
    public function submissionVotes() { /* hasMany */ }
    public function uxMetricVotes() { /* hasMany */ }
    public function reviews() { /* hasMany */ }
    public function gameLists() { /* hasMany */ }
    public function forumThreads() { /* hasMany */ }
    public function forumPosts() { /* hasMany */ }
    public function walkthroughs() { /* hasMany */ }
    public function renownTransactions() { /* hasMany */ }
    public function newGameSubmissions() { /* hasMany */ }

    // Methods
    public function addRenown(int $points, string $reason, $reference = null) { /* ... */ }
    public function subtractRenown(int $points, string $reason) { /* ... */ }
    public function hasVotedFor(GameSubmission $submission): bool { /* ... */ }
}
```

### Game.php (MainDb)
```php
namespace App\Models\MainDb;

use App\Enums\FieldType;

class Game extends Model
{
    protected $connection = 'mysql';

    protected $fillable = ['name', 'slug'];

    // Relationships
    public function consoles() { /* belongsToMany */ }
    public function submissions() { /* hasMany */ }
    public function uxMetricVotes() { /* hasMany */ }
    public function reviews() { /* hasMany */ }
    public function gameLists() { /* hasMany */ }
    public function forums() { /* morphMany as parent */ }
    public function walkthroughs() { /* hasMany */ }
    public function visits() { /* hasOne */ }

    // Get current winner for a field type
    public function getWinningSubmission(FieldType $fieldType): ?GameSubmission { /* ... */ }
    
    // Get all current winning submissions as array
    public function getWinningData(): array { /* ... */ }
    
    // Scopes
    public function scopePopular($query) { /* order by visit_count */ }
    public function scopeWithConsoles($query) { /* eager load consoles */ }
}
```

### GameSubmission.php (MainDb)
```php
namespace App\Models\MainDb;

use App\Enums\FieldType;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameSubmission extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql';

    protected $fillable = [
        'game_id',
        'field_type',
        'content',
        'submitted_by',
        'is_current_winner',
    ];

    protected $casts = [
        'field_type' => FieldType::class,
        'is_current_winner' => 'boolean',
    ];

    // Relationships
    public function game() { /* belongsTo */ }
    public function submitter() { /* belongsTo User */ }
    public function votes() { /* hasMany SubmissionVote */ }

    // Calculate total renown from votes
    public function calculateTotalRenown(): int {
        return $this->votes()
            ->join('users', 'submission_votes.user_id', '=', 'users.id')
            ->sum('users.renown_points');
    }

    // Check if this submission is winning
    public function isWinning(): bool {
        return $this->is_current_winner;
    }

    // Scopes
    public function scopeWinners($query) { /* where is_current_winner = true */ }
    public function scopeForGame($query, int $gameId) { /* ... */ }
    public function scopeOfType($query, FieldType $type) { /* ... */ }
}
```

### Console.php (MainDb)
```php
class Console extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'platform_id',
        'name',
        'slug',
        'description',
        'release_date',
        'image_url',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    // Relationships
    public function platform() { /* belongsTo */ }
    public function games() { /* belongsToMany */ }
    public function forums() { /* morphMany as parent */ }
}
```

### UxMetricVote.php (MainDb)
```php
use App\Enums\UxMetricVoteValue;

class UxMetricVote extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'user_id',
        'game_id',
        'time_vote',
        'money_vote',
        'psychology_vote',
        'social_vote',
        'privacy_vote',
    ];

    protected $casts = [
        'time_vote' => UxMetricVoteValue::class,
        'money_vote' => UxMetricVoteValue::class,
        'psychology_vote' => UxMetricVoteValue::class,
        'social_vote' => UxMetricVoteValue::class,
        'privacy_vote' => UxMetricVoteValue::class,
    ];

    // Relationships
    public function user() { /* belongsTo */ }
    public function game() { /* belongsTo */ }

    // Calculate percentages for a game
    public static function getMetricsForGame(int $gameId): array {
        // Returns: ['time' => 72, 'money' => 85, ...] (percentage of "respect" votes)
    }
}
```

### GameDisplay.php (ShowDb)
```php
namespace App\Models\ShowDb;

use Illuminate\Database\Eloquent\Model;

class GameDisplay extends Model
{
    protected $connection = 'show_db';
    protected $table = 'games_display';

    protected $fillable = [
        'game_id',
        'name',
        'slug',
        'box_art_url',
        'release_date',
        'publisher',
        'developer',
        'countries_of_release',
        'languages_supported',
        'console_ids',
        'community_rating_avg',
        'ux_metrics',
        'popularity_score',
        'last_synced_at',
    ];

    protected $casts = [
        'release_date' => 'date',
        'countries_of_release' => 'array',
        'languages_supported' => 'array',
        'console_ids' => 'array',
        'ux_metrics' => 'array',
        'community_rating_avg' => 'decimal:2',
        'last_synced_at' => 'datetime',
    ];

    // No relationships - this is read-only display data
    // Access consoles via console_ids array and ConsoleDisplay lookups

    // Scopes
    public function scopePopular($query) { /* order by popularity_score */ }
    public function scopeByPlatform($query, int $platformId) { /* ... */ }
}
```

**POLYMORPHIC RELATIONSHIPS:**

Forums are polymorphic - they can belong to Platform, Console, or Game:
```php
// In Forum.php
public function parent()
{
    return $this->morphTo('parent', 'parent_type', 'parent_id');
}

// In Platform.php, Console.php, Game.php
public function forum()
{
    return $this->morphOne(Forum::class, 'parent', 'parent_type', 'parent_id');
}
```

**PIVOT TABLE MODELS:**

Create explicit pivot model for better control:
```php
// app/Models/MainDb/ConsoleGame.php
class ConsoleGame extends Pivot
{
    protected $table = 'console_game';
    public $incrementing = true;
    public $timestamps = true;
}

// Then in Game.php
public function consoles()
{
    return $this->belongsToMany(Console::class, 'console_game')
                ->using(ConsoleGame::class)
                ->withTimestamps();
}
```

**TRAIT FOR RENOWN (optional):**
```php
// app/Traits/HasRenown.php
trait HasRenown
{
    public function addRenown(int $points, string $reason, $reference = null): void
    {
        $this->increment('renown_points', $points);
        
        RenownTransaction::create([
            'user_id' => $this->id,
            'points' => $points,
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);
    }
    
    // Similar for subtractRenown
}
```

**TESTING MODELS:**

After creating, test in tinker:
```php
$user = User::factory()->create(['renown_points' => 5000]);
$platform = Platform::create(['name' => 'Nintendo', 'slug' => 'nintendo']);
$console = Console::create(['platform_id' => $platform->id, 'name' => 'Switch', 'slug' => 'switch']);
$game = Game::create(['name' => 'Zelda TOTK', 'slug' => 'zelda-totk']);
$game->consoles()->attach($console->id);

// Test relationships
$game->consoles; // Should return collection with Switch
$console->platform; // Should return Nintendo
```

DELIVERABLES:
1. All models created with proper namespaces
2. Enums created for type safety
3. All relationships defined and tested
4. PHPDoc comments for IDE autocomplete
5. Common scopes implemented
6. Renown transaction methods working
7. Polymorphic relationships working
```

## Implementation Notes

**Namespace Organization:**
Separating Main and Show DB models into subdirectories makes the architecture clear and prevents accidentally querying the wrong database.

**Connection Property:**
Always explicitly set `protected $connection` in each model to avoid confusion. Main DB uses 'mysql', Show DB uses 'show_db'.

**Enum Benefits:**
Using PHP 8.1+ enums provides type safety and IDE autocomplete. Better than magic strings.

**Soft Deletes:**
User-generated content (reviews, submissions, posts) should use `SoftDeletes` for moderation and potential restoration.

**Renown Calculation:**
The `calculateTotalRenown()` method uses a JOIN to sum voter renown in real-time. This is calculated, not stored, ensuring it updates when user renown changes.

**Show DB Models:**
Show DB models are simpler - no relationships since it's denormalized read-only data. Console and platform info is stored as IDs/arrays for fast retrieval.

**Accessor/Mutator Examples:**
```php
// In GameSubmission - parse JSON content
protected function content(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $this->field_type->requiresJson() ? json_decode($value, true) : $value,
        set: fn ($value) => is_array($value) ? json_encode($value) : $value,
    );
}
```

## Acceptance Criteria
- [ ] All models created in correct namespaces
- [ ] All enums created and imported
- [ ] Relationships defined and return correct types
- [ ] Can eager load relationships without errors
- [ ] Scopes work as expected
- [ ] Renown transaction creation works
- [ ] Polymorphic forum relationships work
- [ ] Models use correct database connections
- [ ] `$fillable` or `$guarded` defined on all models
- [ ] `$casts` used for dates, booleans, enums, JSON
- [ ] PHPDoc comments added for relationships
- [ ] Test in tinker successfully

---
**Related Tasks:** TASK-1-001 (Database Schema), TASK-1-003 (Seeders), TASK-1-004 (Admin API)  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 6-10 hours  
**Priority:** Critical
