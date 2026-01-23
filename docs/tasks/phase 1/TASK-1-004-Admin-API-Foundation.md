# [TASK-1-004] Admin API Foundation (Bot Seeding Endpoints)

## Context
Create authenticated API endpoints that allow bot users to submit game data to the Main Database. These endpoints enable AI scraping bots (ClaudeBot, GeminiBot, etc.) to populate ADUX with game information that will flow through the democratic submission system.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] TASK-1-001-Database-Schema.md (field types and structure)
- [ ] TASK-1-002-Models-Relationships.md (model usage)
- [ ] TASK-1-003-Seeders-Initial-Data.md (bot user references)
- [ ] README.md (API architecture overview)

**Conditions that must be met:**
- [ ] TASK-1-001, 1-002, 1-003 complete
- [ ] Laravel Sanctum installed and configured
- [ ] Bot users seeded with API tokens
- [ ] Understanding of submission workflow

## Deliverables
- API routes in `routes/api.php` (v1 namespace)
- AdminGameController for game submission endpoints
- AdminSubmissionController for metadata submissions
- API request validation classes
- API resource classes for JSON responses
- Rate limiting configuration
- API documentation (inline comments)
- Postman/Insomnia collection (optional)

## AI Prompt
```
Create Laravel Sanctum-authenticated API endpoints for ADUX bot users to submit game data to the Main Database.

**ARCHITECTURE OVERVIEW:**

This is the **Admin/Seeding API** - separate from the future public API:
- Admin API: Authenticated write access to Main DB (bot users only, Phase 1)
- Public API: Rate-limited read access to Show DB (Phase 2)

**AUTHENTICATION:**

Using Laravel Sanctum with personal access tokens:
- Bot users created in TASK-1-003 have API tokens
- All endpoints require `auth:sanctum` middleware
- Tokens passed via `Authorization: Bearer {token}` header

**API ENDPOINTS TO CREATE:**

Base URL: `/api/v1/admin`

**1. Submit New Game**
```
POST /api/v1/admin/games
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "The Legend of Zelda: Breath of the Wild",
  "console_slugs": ["switch", "wii-u"],
  "initial_data": {
    "box_art": "https://example.com/box-art.jpg",
    "release_date": "2017-03-03",
    "developer": "Nintendo EPD",
    "publisher": "Nintendo",
    "countries_of_release": ["US", "JP", "EU"],
    "languages_supported": ["English", "Japanese", "French", "German", "Spanish"]
  }
}

Response 201:
{
  "success": true,
  "message": "Game created and submissions queued for approval",
  "data": {
    "game_id": 123,
    "name": "The Legend of Zelda: Breath of the Wild",
    "slug": "zelda-breath-of-the-wild",
    "submissions_created": 6
  }
}
```

**2. Submit Metadata for Existing Game**
```
POST /api/v1/admin/games/{game_id}/submissions
Authorization: Bearer {token}

{
  "field_type": "box_art",
  "content": "https://example.com/better-box-art.jpg"
}

Response 201:
{
  "success": true,
  "message": "Submission created",
  "data": {
    "submission_id": 456,
    "field_type": "box_art",
    "game_id": 123,
    "submitted_by": "ClaudeBot"
  }
}
```

**3. Get Game by Slug (for checking existence)**
```
GET /api/v1/admin/games/{slug}
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "id": 123,
    "name": "The Legend of Zelda: Breath of the Wild",
    "slug": "zelda-breath-of-the-wild",
    "consoles": [
      {"id": 5, "name": "Nintendo Switch", "slug": "switch"},
      {"id": 4, "name": "Wii U", "slug": "wii-u"}
    ],
    "current_submissions": {
      "box_art": "https://example.com/box-art.jpg",
      "developer": "Nintendo EPD",
      ...
    }
  }
}

Response 404:
{
  "success": false,
  "message": "Game not found"
}
```

**4. Bulk Console Lookup**
```
GET /api/v1/admin/consoles?slugs[]=switch&slugs[]=ps5
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": [
    {"id": 5, "name": "Nintendo Switch", "slug": "switch", "platform": "Nintendo"},
    {"id": 15, "name": "PlayStation 5", "slug": "ps5", "platform": "Sony"}
  ]
}
```

**CONTROLLER STRUCTURE:**

```php
// app/Http/Controllers/Api/V1/Admin/GameController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\Api\Admin\StoreGameRequest;
use App\Http\Resources\GameResource;
use App\Models\MainDb\Game;
use App\Models\MainDb\Console;
use App\Services\GameSubmissionService;

class GameController extends Controller
{
    public function __construct(
        private GameSubmissionService $submissionService
    ) {}

    /**
     * Store a new game with initial submissions
     */
    public function store(StoreGameRequest $request)
    {
        // Create game
        $game = Game::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        // Attach consoles
        $consoleIds = Console::whereIn('slug', $request->console_slugs)->pluck('id');
        $game->consoles()->attach($consoleIds);

        // Create initial submissions
        $submissionsCreated = 0;
        foreach ($request->initial_data as $fieldType => $content) {
            $this->submissionService->createSubmission(
                game: $game,
                fieldType: FieldType::from($fieldType),
                content: $content,
                userId: auth()->id()
            );
            $submissionsCreated++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Game created and submissions queued',
            'data' => [
                'game_id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'submissions_created' => $submissionsCreated,
            ],
        ], 201);
    }

    /**
     * Get game by slug
     */
    public function show(string $slug)
    {
        $game = Game::with('consoles')
            ->where('slug', $slug)
            ->firstOrFail();

        return new GameResource($game);
    }
}
```

```php
// app/Http/Controllers/Api/V1/Admin/SubmissionController.php
class SubmissionController extends Controller
{
    public function store(int $gameId, StoreSubmissionRequest $request)
    {
        $game = Game::findOrFail($gameId);

        $submission = $this->submissionService->createSubmission(
            game: $game,
            fieldType: FieldType::from($request->field_type),
            content: $request->content,
            userId: auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Submission created',
            'data' => [
                'submission_id' => $submission->id,
                'field_type' => $submission->field_type->value,
                'game_id' => $game->id,
                'submitted_by' => auth()->user()->name,
            ],
        ], 201);
    }
}
```

**VALIDATION REQUESTS:**

```php
// app/Http/Requests/Api/Admin/StoreGameRequest.php
class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // Must be authenticated
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:games,name'],
            'console_slugs' => ['required', 'array', 'min:1'],
            'console_slugs.*' => ['required', 'string', 'exists:consoles,slug'],
            'initial_data' => ['sometimes', 'array'],
            'initial_data.box_art' => ['sometimes', 'url'],
            'initial_data.release_date' => ['sometimes', 'date'],
            'initial_data.developer' => ['sometimes', 'string', 'max:255'],
            'initial_data.publisher' => ['sometimes', 'string', 'max:255'],
            'initial_data.countries_of_release' => ['sometimes', 'array'],
            'initial_data.countries_of_release.*' => ['string', 'size:2'], // Country codes
            'initial_data.languages_supported' => ['sometimes', 'array'],
            'initial_data.languages_supported.*' => ['string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A game with this name already exists',
            'console_slugs.*.exists' => 'One or more console slugs are invalid',
        ];
    }
}
```

```php
// app/Http/Requests/Api/Admin/StoreSubmissionRequest.php
class StoreSubmissionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'field_type' => ['required', 'string', Rule::enum(FieldType::class)],
            'content' => ['required', 'string'], // Validated further based on field_type
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateContentByFieldType($validator);
        });
    }

    private function validateContentByFieldType($validator)
    {
        $fieldType = FieldType::tryFrom($this->field_type);
        
        match($fieldType) {
            FieldType::BOX_ART => $this->validateUrl($validator, 'content'),
            FieldType::RELEASE_DATE => $this->validateDate($validator, 'content'),
            FieldType::PUBLISHER, FieldType::DEVELOPER => $this->validateString($validator, 'content', 255),
            FieldType::COUNTRIES_OF_RELEASE, FieldType::LANGUAGES_SUPPORTED => $this->validateJson($validator, 'content'),
            default => null,
        };
    }

    private function validateUrl($validator, $field)
    {
        if (!filter_var($this->input($field), FILTER_VALIDATE_URL)) {
            $validator->errors()->add($field, 'The content must be a valid URL for this field type');
        }
    }

    private function validateDate($validator, $field)
    {
        if (!strtotime($this->input($field))) {
            $validator->errors()->add($field, 'The content must be a valid date');
        }
    }

    private function validateString($validator, $field, $maxLength)
    {
        if (strlen($this->input($field)) > $maxLength) {
            $validator->errors()->add($field, "The content must not exceed {$maxLength} characters");
        }
    }

    private function validateJson($validator, $field)
    {
        json_decode($this->input($field));
        if (json_last_error() !== JSON_ERROR_NONE) {
            $validator->errors()->add($field, 'The content must be valid JSON for this field type');
        }
    }
}
```

**API RESOURCES:**

```php
// app/Http/Resources/GameResource.php
class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'consoles' => ConsoleResource::collection($this->whenLoaded('consoles')),
            'current_submissions' => $this->when(
                $this->relationLoaded('submissions'),
                fn() => $this->getWinningData()
            ),
            'created_at' => $this->created_at,
        ];
    }
}
```

**SERVICE CLASS:**

```php
// app/Services/GameSubmissionService.php
namespace App\Services;

use App\Enums\FieldType;
use App\Models\MainDb\Game;
use App\Models\MainDb\GameSubmission;

class GameSubmissionService
{
    public function createSubmission(
        Game $game,
        FieldType $fieldType,
        string|array $content,
        int $userId
    ): GameSubmission {
        // Convert arrays to JSON for storage
        if (is_array($content)) {
            $content = json_encode($content);
        }

        return GameSubmission::create([
            'game_id' => $game->id,
            'field_type' => $fieldType,
            'content' => $content,
            'submitted_by' => $userId,
            'is_current_winner' => false, // Will be calculated by queue job
        ]);
    }

    public function getWinningSubmissions(Game $game): array
    {
        $winners = [];
        
        foreach (FieldType::cases() as $fieldType) {
            $winner = GameSubmission::where('game_id', $game->id)
                ->where('field_type', $fieldType)
                ->where('is_current_winner', true)
                ->first();

            if ($winner) {
                $winners[$fieldType->value] = $winner->content;
            }
        }

        return $winners;
    }
}
```

**ROUTES:**

```php
// routes/api.php
use App\Http\Controllers\Api\V1\Admin\GameController;
use App\Http\Controllers\Api\V1\Admin\SubmissionController;
use App\Http\Controllers\Api\V1\Admin\ConsoleController;

Route::prefix('v1/admin')->middleware(['auth:sanctum', 'throttle:admin-api'])->group(function () {
    // Games
    Route::post('games', [GameController::class, 'store']);
    Route::get('games/{slug}', [GameController::class, 'show']);
    
    // Submissions
    Route::post('games/{game}/submissions', [SubmissionController::class, 'store']);
    
    // Consoles (lookup)
    Route::get('consoles', [ConsoleController::class, 'index']);
});
```

**RATE LIMITING:**

```php
// app/Providers/AppServiceProvider.php (or RouteServiceProvider)
RateLimiter::for('admin-api', function (Request $request) {
    return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
});
```

**ERROR HANDLING:**

```php
// app/Exceptions/Handler.php
public function render($request, Throwable $e)
{
    if ($request->is('api/*')) {
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found',
            ], 404);
        }

        if ($e instanceof AuthenticationException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        // Generic error
        return response()->json([
            'success' => false,
            'message' => 'An error occurred',
            'error' => config('app.debug') ? $e->getMessage() : null,
        ], 500);
    }

    return parent::render($request, $e);
}
```

DELIVERABLES:
1. All routes registered and tested
2. Controllers handle requests properly
3. Validation catches invalid data
4. API resources format responses correctly
5. Rate limiting configured
6. Error handling returns JSON
7. Documentation (inline + README update)
```

## Implementation Notes

**Bot Token Usage:**
Bots created in TASK-1-003 have tokens. To use:
```bash
curl -X POST http://localhost/api/v1/admin/games \
  -H "Authorization: Bearer {token_from_seeder}" \
  -H "Content-Type: application/json" \
  -d '{"name": "Test Game", "console_slugs": ["switch"]}'
```

**Content Type Handling:**
Different field types require different content formats:
- `box_art`: URL string
- `release_date`: Date string (YYYY-MM-DD)
- `developer/publisher`: Plain string
- `countries_of_release/languages_supported`: JSON array

**Slug Generation:**
Auto-generate slugs from game names using `Str::slug()`. Check for uniqueness and append number if needed.

**Security:**
- Only authenticated users (bots) can access admin API
- Rate limit prevents abuse (100 req/min per user)
- Validation prevents malformed data
- Sanctum tokens are revocable

**Future Enhancement (Phase 2):**
Manual approval workflow for new games. For now, games are auto-approved when submitted by trusted bot accounts.

## Acceptance Criteria
- [ ] Routes accessible at `/api/v1/admin/*`
- [ ] `auth:sanctum` middleware protects all endpoints
- [ ] Can create new game with valid token
- [ ] Can submit metadata for existing game
- [ ] Validation rejects invalid data with proper errors
- [ ] Can query game by slug
- [ ] Can lookup consoles by slug
- [ ] Rate limiting works (test with 101 requests)
- [ ] Errors return JSON (not HTML)
- [ ] Postman/Insomnia collection created (optional)
- [ ] README updated with API usage examples

## Testing Commands

```bash
# Create .env tokens from seeder output
CLAUDE_TOKEN="..."
GEMINI_TOKEN="..."

# Test game creation
curl -X POST http://localhost/api/v1/admin/games \
  -H "Authorization: Bearer $CLAUDE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Halo Infinite",
    "console_slugs": ["xbox-series-x", "windows"],
    "initial_data": {
      "box_art": "https://via.placeholder.com/300x400",
      "release_date": "2021-12-08",
      "developer": "343 Industries",
      "publisher": "Xbox Game Studios"
    }
  }'

# Test submission
curl -X POST http://localhost/api/v1/admin/games/1/submissions \
  -H "Authorization: Bearer $GEMINI_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "field_type": "box_art",
    "content": "https://via.placeholder.com/300x400/competing-image"
  }'

# Test get game
curl http://localhost/api/v1/admin/games/halo-infinite \
  -H "Authorization: Bearer $CLAUDE_TOKEN"

# Test without auth (should fail)
curl -X POST http://localhost/api/v1/admin/games \
  -H "Content-Type: application/json" \
  -d '{"name": "Test"}'
```

---
**Related Tasks:** TASK-1-002 (Models), TASK-1-003 (Seeders), TASK-1-008 (Winner Calculation)  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 6-8 hours  
**Priority:** High - Enables bot data population
