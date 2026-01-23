# [TASK-1-003] Database Seeders & Initial Data

## Context
Create comprehensive database seeders to populate ADUX with initial data: platforms, consoles, bot user accounts, sample games, and test submissions. This provides a working dataset for development and testing before the AI scraping bots populate real data.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] TASK-1-001-Database-Schema.md (schema reference)
- [ ] TASK-1-002-Models-Relationships.md (model structure)
- [ ] README.md (bot user requirements)

**Conditions that must be met:**
- [ ] TASK-1-001 complete (migrations run)
- [ ] TASK-1-002 complete (models exist)
- [ ] Both databases accessible

## Deliverables
- Platform seeder (Nintendo, Sony, Microsoft, PC, Mobile)
- Console seeder (major consoles from each platform)
- Bot user seeder (ClaudeBot, GeminiBot, GPT4Bot, etc.)
- Sample game seeder (10-20 games for testing)
- Sample submission seeder (box art, metadata for games)
- Test user seeder (regular users for testing voting)
- DatabaseSeeder orchestration
- Factory files for models
- Seeding documentation

## AI Prompt
```
Create Laravel database seeders for ADUX to establish initial platform/console hierarchy, bot users, and sample game data.

**SEEDER STRUCTURE:**
```
database/seeders/
  MainDb/
    PlatformSeeder.php
    ConsoleSeeder.php
    BotUserSeeder.php
    SampleGameSeeder.php
    SampleSubmissionSeeder.php
    TestUserSeeder.php
  ShowDb/
    SyncInitialDataSeeder.php (copies platforms/consoles to Show DB)
  DatabaseSeeder.php (orchestrates all)
```

**1. PLATFORM SEEDER (MainDb)**

Seed the major gaming platforms:
```php
namespace Database\Seeders\MainDb;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            [
                'name' => 'Nintendo',
                'slug' => 'nintendo',
                'description' => 'Japanese gaming company known for Mario, Zelda, and Pokemon',
                'logo_url' => null, // Add later in Phase 2
            ],
            [
                'name' => 'Sony',
                'slug' => 'sony',
                'description' => 'PlayStation platform creator',
                'logo_url' => null,
            ],
            [
                'name' => 'Microsoft',
                'slug' => 'microsoft',
                'description' => 'Xbox platform creator',
                'logo_url' => null,
            ],
            [
                'name' => 'PC',
                'slug' => 'pc',
                'description' => 'Personal computer gaming',
                'logo_url' => null,
            ],
            [
                'name' => 'Mobile',
                'slug' => 'mobile',
                'description' => 'iOS and Android gaming',
                'logo_url' => null,
            ],
            [
                'name' => 'Sega',
                'slug' => 'sega',
                'description' => 'Former hardware manufacturer, now software developer',
                'logo_url' => null,
            ],
        ];

        foreach ($platforms as $platform) {
            Platform::create($platform);
        }

        $this->command->info('Platforms seeded successfully.');
    }
}
```

**2. CONSOLE SEEDER (MainDb)**

Seed major consoles for each platform:
```php
class ConsoleSeeder extends Seeder
{
    public function run(): void
    {
        $consoles = [
            // Nintendo
            ['platform' => 'nintendo', 'name' => 'Nintendo 64', 'slug' => 'n64', 'release_date' => '1996-06-23'],
            ['platform' => 'nintendo', 'name' => 'GameCube', 'slug' => 'gamecube', 'release_date' => '2001-09-14'],
            ['platform' => 'nintendo', 'name' => 'Wii', 'slug' => 'wii', 'release_date' => '2006-11-19'],
            ['platform' => 'nintendo', 'name' => 'Wii U', 'slug' => 'wii-u', 'release_date' => '2012-11-18'],
            ['platform' => 'nintendo', 'name' => 'Nintendo Switch', 'slug' => 'switch', 'release_date' => '2017-03-03'],
            ['platform' => 'nintendo', 'name' => 'Nintendo Switch 2', 'slug' => 'switch-2', 'release_date' => '2025-03-01'],
            ['platform' => 'nintendo', 'name' => 'Game Boy', 'slug' => 'gameboy', 'release_date' => '1989-04-21'],
            ['platform' => 'nintendo', 'name' => 'Game Boy Advance', 'slug' => 'gba', 'release_date' => '2001-03-21'],
            ['platform' => 'nintendo', 'name' => 'Nintendo DS', 'slug' => 'ds', 'release_date' => '2004-11-21'],
            ['platform' => 'nintendo', 'name' => 'Nintendo 3DS', 'slug' => '3ds', 'release_date' => '2011-02-26'],

            // Sony
            ['platform' => 'sony', 'name' => 'PlayStation', 'slug' => 'ps1', 'release_date' => '1994-12-03'],
            ['platform' => 'sony', 'name' => 'PlayStation 2', 'slug' => 'ps2', 'release_date' => '2000-03-04'],
            ['platform' => 'sony', 'name' => 'PlayStation 3', 'slug' => 'ps3', 'release_date' => '2006-11-11'],
            ['platform' => 'sony', 'name' => 'PlayStation 4', 'slug' => 'ps4', 'release_date' => '2013-11-15'],
            ['platform' => 'sony', 'name' => 'PlayStation 5', 'slug' => 'ps5', 'release_date' => '2020-11-12'],
            ['platform' => 'sony', 'name' => 'PlayStation Portable', 'slug' => 'psp', 'release_date' => '2004-12-12'],
            ['platform' => 'sony', 'name' => 'PlayStation Vita', 'slug' => 'vita', 'release_date' => '2011-12-17'],

            // Microsoft
            ['platform' => 'microsoft', 'name' => 'Xbox', 'slug' => 'xbox', 'release_date' => '2001-11-15'],
            ['platform' => 'microsoft', 'name' => 'Xbox 360', 'slug' => 'xbox-360', 'release_date' => '2005-11-22'],
            ['platform' => 'microsoft', 'name' => 'Xbox One', 'slug' => 'xbox-one', 'release_date' => '2013-11-22'],
            ['platform' => 'microsoft', 'name' => 'Xbox Series X', 'slug' => 'xbox-series-x', 'release_date' => '2020-11-10'],
            ['platform' => 'microsoft', 'name' => 'Xbox Series S', 'slug' => 'xbox-series-s', 'release_date' => '2020-11-10'],

            // PC (versions/eras)
            ['platform' => 'pc', 'name' => 'Windows', 'slug' => 'windows', 'release_date' => null],
            ['platform' => 'pc', 'name' => 'macOS', 'slug' => 'macos', 'release_date' => null],
            ['platform' => 'pc', 'name' => 'Linux', 'slug' => 'linux', 'release_date' => null],

            // Mobile
            ['platform' => 'mobile', 'name' => 'iOS', 'slug' => 'ios', 'release_date' => null],
            ['platform' => 'mobile', 'name' => 'Android', 'slug' => 'android', 'release_date' => null],

            // Sega (legacy)
            ['platform' => 'sega', 'name' => 'Genesis', 'slug' => 'genesis', 'release_date' => '1988-10-29'],
            ['platform' => 'sega', 'name' => 'Saturn', 'slug' => 'saturn', 'release_date' => '1994-11-22'],
            ['platform' => 'sega', 'name' => 'Dreamcast', 'slug' => 'dreamcast', 'release_date' => '1998-11-27'],
        ];

        foreach ($consoles as $consoleData) {
            $platform = Platform::where('slug', $consoleData['platform'])->first();
            
            Console::create([
                'platform_id' => $platform->id,
                'name' => $consoleData['name'],
                'slug' => $consoleData['slug'],
                'release_date' => $consoleData['release_date'],
                'description' => null,
                'image_url' => null,
            ]);
        }

        $this->command->info('Consoles seeded successfully.');
    }
}
```

**3. BOT USER SEEDER (MainDb)**

Create bot accounts with high renown:
```php
class BotUserSeeder extends Seeder
{
    public function run(): void
    {
        $bots = [
            [
                'name' => 'ClaudeBot',
                'email' => 'claude@adux-bots.internal',
                'renown_points' => 10000,
                'description' => 'Anthropic Claude AI scraper',
            ],
            [
                'name' => 'GeminiBot',
                'email' => 'gemini@adux-bots.internal',
                'renown_points' => 10000,
                'description' => 'Google Gemini AI scraper',
            ],
            [
                'name' => 'GPT4Bot',
                'email' => 'gpt4@adux-bots.internal',
                'renown_points' => 10000,
                'description' => 'OpenAI GPT-4 scraper',
            ],
            [
                'name' => 'PerplexityBot',
                'email' => 'perplexity@adux-bots.internal',
                'renown_points' => 10000,
                'description' => 'Perplexity AI scraper',
            ],
        ];

        foreach ($bots as $bot) {
            $user = User::create([
                'name' => $bot['name'],
                'email' => $bot['email'],
                'password' => Hash::make(Str::random(32)), // Random password
                'renown_points' => $bot['renown_points'],
                'email_verified_at' => now(),
            ]);

            // Create API token for each bot
            $token = $user->createToken($bot['name'] . ' API Token')->plainTextToken;
            
            $this->command->info("Created {$bot['name']} with token: {$token}");
            $this->command->warn("SAVE THIS TOKEN - it won't be shown again!");
        }

        $this->command->info('Bot users seeded successfully.');
    }
}
```

**4. TEST USER SEEDER (MainDb)**

Create regular test users with varying renown:
```php
class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create main test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@adux.local',
            'password' => Hash::make('password'),
            'renown_points' => 500,
            'email_verified_at' => now(),
        ]);

        // Create users with different renown levels
        User::create([
            'name' => 'High Renown User',
            'email' => 'highrenown@adux.local',
            'password' => Hash::make('password'),
            'renown_points' => 5000,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Low Renown User',
            'email' => 'lowrenown@adux.local',
            'password' => Hash::make('password'),
            'renown_points' => 50,
            'email_verified_at' => now(),
        ]);

        // Create 10 random users for testing voting
        User::factory(10)->create();

        $this->command->info('Test users seeded successfully.');
    }
}
```

**5. SAMPLE GAME SEEDER (MainDb)**

Create iconic games for testing:
```php
class SampleGameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            // Multi-platform games
            [
                'name' => 'The Legend of Zelda: Ocarina of Time',
                'slug' => 'zelda-ocarina-of-time',
                'consoles' => ['n64', '3ds', 'gamecube', 'wii'], // Virtual Console
            ],
            [
                'name' => 'The Legend of Zelda: Majora\'s Mask',
                'slug' => 'zelda-majoras-mask',
                'consoles' => ['n64', '3ds', 'gamecube', 'wii'],
            ],
            [
                'name' => 'Super Mario 64',
                'slug' => 'super-mario-64',
                'consoles' => ['n64', 'ds', 'wii'],
            ],
            [
                'name' => 'GoldenEye 007',
                'slug' => 'goldeneye-007',
                'consoles' => ['n64'],
            ],
            [
                'name' => 'Perfect Dark',
                'slug' => 'perfect-dark',
                'consoles' => ['n64', 'xbox-360'],
            ],
            [
                'name' => 'Banjo-Kazooie',
                'slug' => 'banjo-kazooie',
                'consoles' => ['n64', 'xbox-360'],
            ],
            [
                'name' => 'Conker\'s Bad Fur Day',
                'slug' => 'conkers-bad-fur-day',
                'consoles' => ['n64'],
            ],
            [
                'name' => 'Super Smash Bros.',
                'slug' => 'super-smash-bros',
                'consoles' => ['n64'],
            ],
            [
                'name' => 'Mario Kart 64',
                'slug' => 'mario-kart-64',
                'consoles' => ['n64', 'wii'],
            ],
            
            // Modern games for ethics testing
            [
                'name' => 'Fortnite',
                'slug' => 'fortnite',
                'consoles' => ['ps4', 'ps5', 'xbox-one', 'xbox-series-x', 'switch', 'windows', 'ios', 'android'],
            ],
            [
                'name' => 'Elden Ring',
                'slug' => 'elden-ring',
                'consoles' => ['ps4', 'ps5', 'xbox-one', 'xbox-series-x', 'windows'],
            ],
            [
                'name' => 'Baldur\'s Gate 3',
                'slug' => 'baldurs-gate-3',
                'consoles' => ['ps5', 'xbox-series-x', 'windows', 'macos'],
            ],
        ];

        foreach ($games as $gameData) {
            $game = Game::create([
                'name' => $gameData['name'],
                'slug' => $gameData['slug'],
            ]);

            // Attach consoles
            $consoleIds = Console::whereIn('slug', $gameData['consoles'])->pluck('id');
            $game->consoles()->attach($consoleIds);

            $this->command->info("Created game: {$gameData['name']}");
        }

        $this->command->info('Sample games seeded successfully.');
    }
}
```

**6. SAMPLE SUBMISSION SEEDER (MainDb)**

Create initial submissions for sample games:
```php
class SampleSubmissionSeeder extends Seeder
{
    public function run(): void
    {
        $claudeBot = User::where('email', 'claude@adux-bots.internal')->first();
        $geminiBot = User::where('email', 'gemini@adux-bots.internal')->first();

        $games = Game::all();

        foreach ($games as $game) {
            // Box art submission (competing bots)
            GameSubmission::create([
                'game_id' => $game->id,
                'field_type' => FieldType::BOX_ART,
                'content' => 'https://via.placeholder.com/300x400?text=' . urlencode($game->name) . '+Claude',
                'submitted_by' => $claudeBot->id,
                'is_current_winner' => false, // Will be calculated
            ]);

            GameSubmission::create([
                'game_id' => $game->id,
                'field_type' => FieldType::BOX_ART,
                'content' => 'https://via.placeholder.com/300x400?text=' . urlencode($game->name) . '+Gemini',
                'submitted_by' => $geminiBot->id,
                'is_current_winner' => false,
            ]);

            // Release date
            GameSubmission::create([
                'game_id' => $game->id,
                'field_type' => FieldType::RELEASE_DATE,
                'content' => '1998-11-21', // Placeholder
                'submitted_by' => $claudeBot->id,
                'is_current_winner' => false,
            ]);

            // Developer
            GameSubmission::create([
                'game_id' => $game->id,
                'field_type' => FieldType::DEVELOPER,
                'content' => 'Nintendo EAD', // Placeholder
                'submitted_by' => $claudeBot->id,
                'is_current_winner' => false,
            ]);

            // Publisher
            GameSubmission::create([
                'game_id' => $game->id,
                'field_type' => FieldType::PUBLISHER,
                'content' => 'Nintendo',
                'submitted_by' => $geminiBot->id,
                'is_current_winner' => false,
            ]);
        }

        // Create some votes (high renown users vote for Claude's submissions)
        $highRenownUser = User::where('email', 'highrenown@adux.local')->first();
        $claudeSubmissions = GameSubmission::where('submitted_by', $claudeBot->id)->get();

        foreach ($claudeSubmissions as $submission) {
            SubmissionVote::create([
                'submission_id' => $submission->id,
                'user_id' => $highRenownUser->id,
            ]);
        }

        $this->command->info('Sample submissions and votes seeded successfully.');
    }
}
```

**7. SYNC INITIAL DATA TO SHOW DB**

Copy platforms and consoles to Show DB:
```php
namespace Database\Seeders\ShowDb;

class SyncInitialDataSeeder extends Seeder
{
    public function run(): void
    {
        // Copy platforms
        $platforms = \App\Models\MainDb\Platform::all();
        foreach ($platforms as $platform) {
            \App\Models\ShowDb\PlatformDisplay::create([
                'platform_id' => $platform->id,
                'name' => $platform->name,
                'slug' => $platform->slug,
                'description' => $platform->description,
                'logo_url' => $platform->logo_url,
            ]);
        }

        // Copy consoles
        $consoles = \App\Models\MainDb\Console::all();
        foreach ($consoles as $console) {
            \App\Models\ShowDb\ConsoleDisplay::create([
                'console_id' => $console->id,
                'platform_id' => $console->platform_id,
                'name' => $console->name,
                'slug' => $console->slug,
                'description' => $console->description,
                'release_date' => $console->release_date,
                'image_url' => $console->image_url,
            ]);
        }

        $this->command->info('Initial data synced to Show DB successfully.');
    }
}
```

**8. DATABASE SEEDER ORCHESTRATION**

```php
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting ADUX database seeding...');

        // Main DB seeders
        $this->call([
            MainDb\PlatformSeeder::class,
            MainDb\ConsoleSeeder::class,
            MainDb\BotUserSeeder::class,
            MainDb\TestUserSeeder::class,
            MainDb\SampleGameSeeder::class,
            MainDb\SampleSubmissionSeeder::class,
        ]);

        // Show DB seeders
        $this->call([
            ShowDb\SyncInitialDataSeeder::class,
        ]);

        $this->command->info('✅ Database seeding completed!');
        $this->command->warn('🔑 Bot API tokens were displayed above - save them!');
    }
}
```

**FACTORIES:**

Create factories for testing:
```php
// database/factories/UserFactory.php
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'renown_points' => fake()->numberBetween(0, 1000),
            'remember_token' => Str::random(10),
        ];
    }
}

// database/factories/GameFactory.php
class GameFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->slug(),
        ];
    }
}
```

DELIVERABLES:
1. All seeders created and organized
2. Run `php artisan db:seed` successfully
3. Both databases populated with test data
4. Bot API tokens generated and saved
5. Factories created for models
```

## Implementation Notes

**Seeding Order:**
1. Platforms (independent)
2. Consoles (depends on platforms)
3. Users (bots and test users)
4. Games (independent)
5. Submissions (depends on games and users)
6. Show DB sync (depends on Main DB data)

**Bot Tokens:**
When seeding, the tokens are displayed once. In production, you'll manually create these and store in password manager.

**Sample Data Choice:**
N64 games chosen because they're iconic, have clean metadata, and represent different genres. Mix of old (respects players) and new (some exploit) for ethics testing.

**Idempotency:**
Seeders should handle re-running. Use `updateOrCreate()` or check existence before creating if needed.

**Console Slugs:**
Slugs should be unique and URL-friendly. Used for routing: `/browse/n64`

## Acceptance Criteria
- [ ] `php artisan db:seed` runs without errors
- [ ] Platforms table has 5-6 entries
- [ ] Consoles table has 30+ entries
- [ ] 4 bot users created with high renown
- [ ] 10+ test users created
- [ ] 10+ sample games created
- [ ] Games attached to correct consoles (multi-platform works)
- [ ] Sample submissions exist for each game
- [ ] Show DB has platforms and consoles copied
- [ ] Bot tokens displayed and saved
- [ ] Can browse data in database client
- [ ] Relationships load correctly (test in tinker)

## Testing After Completion

```bash
php artisan db:seed

# Verify in tinker
$game = Game::with('consoles')->first();
$game->consoles; // Should show attached consoles

$bot = User::where('name', 'ClaudeBot')->first();
$bot->renown_points; // Should be 10000

$submission = GameSubmission::with('submitter', 'votes')->first();
$submission->submitter->name; // Should show bot name
```

---
**Related Tasks:** TASK-1-001 (Schema), TASK-1-002 (Models), TASK-1-004 (Admin API)  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 4-6 hours  
**Priority:** High
