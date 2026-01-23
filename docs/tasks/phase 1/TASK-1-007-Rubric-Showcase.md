# [TASK-1-007] Rubric Showcase Page (/rubric)

## Context
Create `/rubric` route and page that demonstrates all ADUX components in action with real data. This serves as both a visual regression test and documentation for developers implementing features in Phase 2. Unlike `/design` which shows raw design tokens, `/rubric` shows complete, composed UI patterns.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] TASK-1-006-Core-Components.md (all components built)
- [ ] TASK-1-005-Design-System-Foundation.md (design foundation)
- [ ] Design.md (component usage patterns)

**Conditions that must be met:**
- [ ] TASK-1-006 complete (all components exist)
- [ ] Sample games seeded (TASK-1-003)
- [ ] Design system established (TASK-1-005)

## Deliverables
- `/rubric` route registered
- `resources/views/rubric.blade.php` template
- Showcase of all components with variations
- Example layouts (game grid, game detail structure)
- Interactive examples where applicable
- Navigation between design system pages
- Component usage code examples
- Documentation of composition patterns

## AI Prompt
```
Create a component showcase page at `/rubric` that demonstrates all ADUX components with real data and usage examples.

**PURPOSE:**
- Visual regression testing (see all components at once)
- Developer reference (how to use components)
- Design validation (components match design spec)
- Composition examples (how to combine components)

**PAGE STRUCTURE:**

```blade
{{-- resources/views/rubric.blade.php --}}
<x-layout>
    <div class="min-h-screen bg-bg-primary">
        {{-- Header --}}
        <header class="bg-bg-secondary border-b border-bg-tertiary sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-display-lg font-display text-text-primary">Component Rubric</h1>
                        <p class="text-text-secondary mt-1">Interactive showcase of ADUX components</p>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <a href="{{ route('design') }}" class="text-text-secondary hover:text-purple-primary transition">
                            Design System →
                        </a>
                        <x-theme-toggle />
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-6 py-12">
            {{-- Quick Navigation --}}
            <nav class="mb-12 flex flex-wrap gap-2">
                <a href="#game-cards" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Game Cards</a>
                <a href="#ux-metrics" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">UX Metrics</a>
                <a href="#buttons" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Buttons</a>
                <a href="#forms" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Forms</a>
                <a href="#badges" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Badges & Tags</a>
                <a href="#layouts" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Layouts</a>
                <a href="#states" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">States</a>
            </nav>

            {{-- GAME CARDS SECTION --}}
            <section id="game-cards" class="mb-20">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-display-md font-display text-text-primary">Game Cards</h2>
                    <x-tag variant="purple">Used in: Browse, Search, Lists</x-tag>
                </div>

                <div class="space-y-8">
                    {{-- Standard Grid --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Standard Grid (4 columns)</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            @foreach($games->take(4) as $game)
                                <x-game-card :game="$game" />
                            @endforeach
                        </div>
                    </div>

                    {{-- Variations --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Variations</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                            {{-- With all features --}}
                            <div>
                                <p class="text-text-tertiary text-sm mb-2">With consoles + ethics bar</p>
                                <x-game-card :game="$games->first()" :showConsoles="true" :showEthicsBar="true" />
                            </div>
                            
                            {{-- No consoles --}}
                            <div>
                                <p class="text-text-tertiary text-sm mb-2">No consoles</p>
                                <x-game-card :game="$games->first()" :showConsoles="false" :showEthicsBar="true" />
                            </div>
                            
                            {{-- No ethics bar --}}
                            <div>
                                <p class="text-text-tertiary text-sm mb-2">No ethics bar</p>
                                <x-game-card :game="$games->first()" :showConsoles="true" :showEthicsBar="false" />
                            </div>
                        </div>
                    </div>

                    {{-- Usage Code --}}
                    <div class="bg-bg-secondary border border-bg-tertiary rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-text-primary mb-2">Usage</h4>
                        <pre class="text-sm text-text-secondary font-mono overflow-x-auto"><code>{{!! '<x-game-card :game="$game" :showConsoles="true" :showEthicsBar="true" />' !!}}</code></pre>
                    </div>
                </div>
            </section>

            {{-- UX METRICS SECTION --}}
            <section id="ux-metrics" class="mb-20">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-display-md font-display text-text-primary">UX Metrics</h2>
                    <x-tag variant="amber">Core Feature</x-tag>
                </div>

                <div class="space-y-8">
                    {{-- Inline (Game Card) --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Inline (Game Card)</h3>
                        <div class="max-w-sm">
                            <x-ux-metrics-inline :metrics="['time' => 72, 'money' => 85, 'psychology' => 68, 'social' => 90, 'privacy' => 45]" />
                        </div>
                    </div>

                    {{-- Detailed Breakdown --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Detailed Breakdown</h3>
                        <div class="grid md:grid-cols-2 gap-8">
                            {{-- High Score (Respects) --}}
                            <div>
                                <p class="text-text-tertiary text-sm mb-2">High Score - Respects Players</p>
                                <x-ux-metrics-detailed 
                                    :metrics="['time' => 92, 'money' => 88, 'psychology' => 85, 'social' => 78, 'privacy' => 95]" 
                                    :voteCount="1247" />
                            </div>

                            {{-- Low Score (Exploits) --}}
                            <div>
                                <p class="text-text-tertiary text-sm mb-2">Low Score - Exploits Players</p>
                                <x-ux-metrics-detailed 
                                    :metrics="['time' => 15, 'money' => 8, 'psychology' => 22, 'social' => 35, 'privacy' => 12]" 
                                    :voteCount="3456" />
                            </div>

                            {{-- Mixed --}}
                            <div>
                                <p class="text-text-tertiary text-sm mb-2">Mixed Practices</p>
                                <x-ux-metrics-detailed 
                                    :metrics="['time' => 55, 'money' => 42, 'psychology' => 68, 'social' => 58, 'privacy' => 50]" 
                                    :voteCount="892" />
                            </div>

                            {{-- No votes --}}
                            <div>
                                <p class="text-text-tertiary text-sm mb-2">No Votes Yet</p>
                                <x-ux-metrics-detailed 
                                    :metrics="['time' => 0, 'money' => 0, 'psychology' => 0, 'social' => 0, 'privacy' => 0]" 
                                    :voteCount="0" />
                            </div>
                        </div>
                    </div>

                    {{-- Usage Code --}}
                    <div class="bg-bg-secondary border border-bg-tertiary rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-text-primary mb-2">Usage</h4>
                        <pre class="text-sm text-text-secondary font-mono overflow-x-auto"><code>{{!! '<x-ux-metrics-inline :metrics="$game->ux_metrics" />
<x-ux-metrics-detailed :metrics="$game->ux_metrics" :voteCount="$game->vote_count" />' !!}}</code></pre>
                    </div>
                </div>
            </section>

            {{-- BUTTONS SECTION --}}
            <section id="buttons" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Buttons</h2>

                <div class="space-y-8">
                    {{-- Variants --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Variants</h3>
                        <div class="flex flex-wrap gap-4 items-center">
                            <x-button variant="primary">Primary Button</x-button>
                            <x-button variant="secondary">Secondary Button</x-button>
                            <x-button variant="action">Action Button</x-button>
                        </div>
                    </div>

                    {{-- Sizes --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Sizes</h3>
                        <div class="flex flex-wrap gap-4 items-center">
                            <x-button size="sm">Small</x-button>
                            <x-button size="md">Medium (default)</x-button>
                            <x-button size="lg">Large</x-button>
                        </div>
                    </div>

                    {{-- States --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">States</h3>
                        <div class="flex flex-wrap gap-4 items-center">
                            <x-button>Default</x-button>
                            <x-button disabled>Disabled</x-button>
                        </div>
                    </div>

                    {{-- As Links --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">As Links</h3>
                        <div class="flex flex-wrap gap-4 items-center">
                            <x-button href="/browse">Browse Games</x-button>
                            <x-button variant="secondary" href="/design">Design System</x-button>
                        </div>
                    </div>
                </div>
            </section>

            {{-- FORMS SECTION --}}
            <section id="forms" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Form Elements</h2>

                <div class="max-w-2xl space-y-8">
                    {{-- Input States --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Input Fields</h3>
                        <div class="space-y-4">
                            <x-input 
                                label="Game Title" 
                                placeholder="Enter game name..." 
                                helper="The name of the game as it appears on the cover"
                            />

                            <x-input 
                                label="Email Address" 
                                type="email" 
                                value="test@example.com"
                            />

                            <x-input 
                                label="Password" 
                                type="password" 
                                error="Password must be at least 8 characters"
                            />

                            <x-input 
                                label="Disabled Field" 
                                value="Cannot edit this" 
                                disabled
                            />
                        </div>
                    </div>
                </div>
            </section>

            {{-- BADGES & TAGS SECTION --}}
            <section id="badges" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Badges & Tags</h2>

                <div class="space-y-8">
                    {{-- Console Badges --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Console Badges</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($consoles->take(6) as $console)
                                <x-console-badge :console="$console" />
                            @endforeach
                        </div>

                        <div class="mt-4">
                            <p class="text-text-tertiary text-sm mb-2">Sizes</p>
                            <div class="flex flex-wrap gap-2 items-center">
                                <x-console-badge :console="$consoles->first()" size="sm" />
                                <x-console-badge :console="$consoles->first()" size="md" />
                                <x-console-badge :console="$consoles->first()" size="lg" />
                            </div>
                        </div>
                    </div>

                    {{-- Tags --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Tags</h3>
                        <div class="flex flex-wrap gap-2">
                            <x-tag>Default Tag</x-tag>
                            <x-tag variant="purple">Purple Tag</x-tag>
                            <x-tag variant="amber">Amber Tag</x-tag>
                            <x-tag variant="success">Success Tag</x-tag>
                            <x-tag variant="error">Error Tag</x-tag>
                            <x-tag variant="purple" removable>Removable Tag</x-tag>
                        </div>
                    </div>

                    {{-- Rating Stars --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Rating Stars</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-4">
                                <x-rating-stars :rating="5" />
                                <span class="text-text-secondary">5.0 - Perfect</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <x-rating-stars :rating="4.5" />
                                <span class="text-text-secondary">4.5 - Excellent</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <x-rating-stars :rating="3.2" />
                                <span class="text-text-secondary">3.2 - Good</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <x-rating-stars :rating="2.1" />
                                <span class="text-text-secondary">2.1 - Mediocre</span>
                            </div>
                            <div class="flex items-center gap-4">
                                <x-rating-stars :rating="0.5" />
                                <span class="text-text-secondary">0.5 - Poor</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- LAYOUTS SECTION --}}
            <section id="layouts" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Layout Patterns</h2>

                <div class="space-y-8">
                    {{-- Breadcrumb --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Breadcrumb Navigation</h3>
                        <x-breadcrumb :items="[
                            ['label' => 'Home', 'url' => '/'],
                            ['label' => 'Nintendo', 'url' => '/platforms/nintendo'],
                            ['label' => 'Nintendo 64', 'url' => '/consoles/n64'],
                            ['label' => 'Ocarina of Time']
                        ]" />
                    </div>

                    {{-- Game Detail Header (Example Composition) --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Game Detail Header (Example Composition)</h3>
                        <div class="bg-bg-secondary border border-bg-tertiary rounded-lg p-6">
                            <x-breadcrumb :items="[
                                ['label' => 'Home', 'url' => '/'],
                                ['label' => 'Nintendo 64', 'url' => '/consoles/n64'],
                                ['label' => 'Ocarina of Time']
                            ]" class="mb-6" />

                            <div class="flex gap-6">
                                {{-- Cover --}}
                                <div class="w-48 h-64 bg-bg-tertiary rounded-md flex-shrink-0"></div>

                                {{-- Info --}}
                                <div class="flex-1 space-y-4">
                                    <div>
                                        <h1 class="text-display-lg font-display text-text-primary">The Legend of Zelda: Ocarina of Time</h1>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <x-console-badge :console="$consoles->first()" />
                                            <x-tag>Action-Adventure</x-tag>
                                            <x-tag>1998</x-tag>
                                        </div>
                                    </div>

                                    <x-rating-stars :rating="4.8" />

                                    <p class="text-text-secondary">
                                        An epic action-adventure game that set the standard for 3D gameplay. Features time travel, dungeons, and unforgettable characters.
                                    </p>

                                    <div class="flex gap-3">
                                        <x-button variant="primary">Add to Collection</x-button>
                                        <x-button variant="secondary">Write Review</x-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- STATES SECTION --}}
            <section id="states" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Loading & Empty States</h2>

                <div class="space-y-8">
                    {{-- Skeleton Loaders --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Skeleton Loaders</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                            @for($i = 0; $i < 4; $i++)
                                <x-skeleton-card />
                            @endfor
                        </div>
                    </div>

                    {{-- Empty States --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Empty States</h3>
                        <div class="grid md:grid-cols-2 gap-8">
                            {{-- No games in collection --}}
                            <div class="bg-bg-secondary border border-bg-tertiary rounded-lg">
                                <x-empty-state 
                                    title="No games in your collection"
                                    message="Start adding games to track your progress and rate them!"
                                    :action="fn() => '<x-button variant=\"action\">Browse Games</x-button>'"
                                />
                            </div>

                            {{-- No search results --}}
                            <div class="bg-bg-secondary border border-bg-tertiary rounded-lg">
                                <x-empty-state 
                                    title="No games found"
                                    message="Try adjusting your search or filters"
                                    :action="fn() => '<x-button variant=\"secondary\">Clear Filters</x-button>'"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layout>
```

**CONTROLLER/ROUTE:**

```php
// routes/web.php
Route::get('/rubric', function () {
    $games = Game::with('consoles')->limit(12)->get();
    $consoles = Console::limit(10)->get();
    
    // Add fake UX metrics for display
    $games->each(function ($game) {
        $game->ux_metrics = [
            'time' => rand(10, 95),
            'money' => rand(10, 95),
            'psychology' => rand(10, 95),
            'social' => rand(10, 95),
            'privacy' => rand(10, 95),
        ];
        $game->community_rating_avg = rand(15, 50) / 10; // 1.5 - 5.0
    });
    
    return view('rubric', compact('games', 'consoles'));
})->name('rubric');
```

DELIVERABLES:
1. /rubric route accessible
2. All components showcased with variations
3. Layout composition examples
4. Code usage examples for each component
5. Interactive theme toggle works
6. Navigation between /design and /rubric
7. Responsive on all screen sizes
```

## Implementation Notes

**Real Data vs Fake:**
Use real games/consoles from database, but fake UX metrics since they won't exist yet in Phase 1.

**Code Examples:**
Show usage code in `<pre><code>` blocks so developers can copy-paste.

**Composition Examples:**
Game detail header shows how to combine multiple components (breadcrumb, badges, buttons, etc.) to create complex layouts.

**Visual Regression:**
This page makes it easy to see if a Tailwind/design change breaks components. Load it after any design updates.

**Navigation:**
Link to `/design` for raw tokens, and from `/design` back to `/rubric` for examples.

## Acceptance Criteria
- [ ] `/rubric` route renders successfully
- [ ] All components from TASK-1-006 are showcased
- [ ] Components display with different prop variations
- [ ] Layout composition examples work
- [ ] Code usage examples are accurate
- [ ] Theme toggle switches mode
- [ ] Navigation links work (/design ↔ /rubric)
- [ ] Page is responsive (mobile, tablet, desktop)
- [ ] Skeleton loaders animate
- [ ] Empty states display correctly
- [ ] UX metrics show all score ranges (high, medium, low)
- [ ] Buttons show all states (variants, sizes, disabled)

---
**Related Tasks:** TASK-1-005 (Design System), TASK-1-006 (Components)  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 4-6 hours  
**Priority:** Medium - Completes design foundation
