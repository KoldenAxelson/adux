# [TASK-1-006] Core Reusable Components

## Context
Build a library of essential, reusable Blade components that will be used throughout ADUX in Phase 2. These components follow the "dumb component" philosophy: single responsibility, props in, HTML out. Components are extracted based on what the database schema dictates will be needed (game cards, UX metrics, navigation, etc.).

## Prerequisites
**Files to attach to this task prompt:**
- [ ] TASK-1-005-Design-System-Foundation.md (completed /design page)
- [ ] Design.md (component specifications)
- [ ] TASK-1-001-Database-Schema.md (understanding what data components will display)
- [ ] Prototype files (game.php, browse.php for reference)

**Conditions that must be met:**
- [ ] TASK-1-005 complete (design tokens available)
- [ ] Tailwind CSS configured with ADUX colors
- [ ] Understanding of Livewire 3 + Alpine.js architecture

## Deliverables
- Game card component
- UX metrics display component (inline and detailed)
- Button components (primary, secondary, action)
- Input field component
- Tag/chip component
- Platform/console badge component
- Rating stars component
- Navigation breadcrumb component
- Card skeleton loader component
- Empty state component
- All components in `resources/views/components/`
- Component documentation (usage examples)

## AI Prompt
```
Create reusable Blade components for ADUX following the design system established in TASK-1-005.

**COMPONENT PHILOSOPHY:**
- Dumb components: One responsibility each
- Props in, HTML out (minimal logic)
- Composition over complexity
- Extract after need is clear (database dictates usage)
- Use Tailwind classes, reference design tokens

**COMPONENTS TO CREATE:**

**1. GAME CARD (browse/discovery)**

File: `resources/views/components/game-card.blade.php`

Props:
- $game (Game model instance)
- $showConsoles (boolean, default true)
- $showEthicsBar (boolean, default true)

```blade
@props([
    'game',
    'showConsoles' => true,
    'showEthicsBar' => true,
])

<a href="{{ route('games.show', $game->slug) }}" 
   class="group block bg-bg-secondary border border-purple-primary/15 rounded-md hover:border-purple-primary hover:-translate-y-1 transition-all duration-200">
    
    {{-- Cover Art --}}
    <div class="aspect-[3/4] bg-bg-tertiary rounded-t-md overflow-hidden">
        @if($game->box_art_url)
            <img src="{{ $game->box_art_url }}" 
                 alt="{{ $game->name }} cover art" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                 loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-16 h-16 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        @endif
    </div>

    {{-- Info Section --}}
    <div class="p-3 space-y-2">
        <h3 class="text-display-sm font-semibold text-text-primary line-clamp-2 group-hover:text-purple-hover transition">
            {{ $game->name }}
        </h3>

        @if($showConsoles && $game->consoles->isNotEmpty())
            <div class="flex flex-wrap gap-1">
                @foreach($game->consoles->take(3) as $console)
                    <x-console-badge :console="$console" size="sm" />
                @endforeach
                @if($game->consoles->count() > 3)
                    <span class="text-xs text-text-tertiary">+{{ $game->consoles->count() - 3 }}</span>
                @endif
            </div>
        @endif

        {{-- Community Rating --}}
        @if($game->community_rating_avg)
            <div class="flex items-center gap-2">
                <x-rating-stars :rating="$game->community_rating_avg" size="sm" />
                <span class="text-sm text-text-secondary">{{ number_format($game->community_rating_avg, 1) }}/5</span>
            </div>
        @endif

        {{-- Ethics Bar (Inline) --}}
        @if($showEthicsBar && isset($game->ux_metrics))
            <x-ux-metrics-inline :metrics="$game->ux_metrics" />
        @endif
    </div>
</a>
```

**2. UX METRICS (Inline Bar)**

File: `resources/views/components/ux-metrics-inline.blade.php`

Props:
- $metrics (array with scores 0-100)

```blade
@props(['metrics'])

@php
    $overallScore = collect($metrics)->avg();
    $color = match(true) {
        $overallScore >= 70 => 'ethics-positive',
        $overallScore >= 40 => 'ethics-neutral',
        default => 'ethics-negative'
    };
@endphp

<div class="space-y-1">
    <div class="flex items-center justify-between">
        <span class="text-xs text-text-tertiary uppercase tracking-wide">Ethics</span>
        <span class="font-mono text-xs text-text-primary">{{ round($overallScore) }}%</span>
    </div>
    <div class="w-full h-2 bg-bg-tertiary rounded-full overflow-hidden">
        <div class="h-full bg-{{ $color }} transition-all duration-300" 
             style="width: {{ $overallScore }}%"></div>
    </div>
</div>
```

**3. UX METRICS (Detailed Breakdown)**

File: `resources/views/components/ux-metrics-detailed.blade.php`

Props:
- $metrics (array: ['time' => 72, 'money' => 85, ...])
- $voteCount (integer)

```blade
@props(['metrics', 'voteCount' => 0])

@php
    $categories = [
        'time' => ['icon' => '⏱️', 'label' => 'Player Time'],
        'money' => ['icon' => '💰', 'label' => 'Monetization'],
        'psychology' => ['icon' => '🧠', 'label' => 'Psychology'],
        'social' => ['icon' => '👥', 'label' => 'Social'],
        'privacy' => ['icon' => '🔒', 'label' => 'Data/Privacy'],
    ];
    
    $overallScore = collect($metrics)->avg();
@endphp

<div class="bg-ethics-bg border border-bg-tertiary rounded-lg p-6 space-y-6">
    {{-- Header --}}
    <div class="text-center pb-4 border-b border-bg-tertiary">
        <p class="text-display-xl font-mono text-text-primary">{{ round($overallScore) }}</p>
        <p class="text-sm text-text-tertiary mt-1">
            @if($overallScore >= 70)
                Respects Players
            @elseif($overallScore >= 40)
                Mixed Practices
            @else
                Exploits Players
            @endif
        </p>
        @if($voteCount > 0)
            <p class="text-xs text-text-muted mt-2">Based on {{ number_format($voteCount) }} votes</p>
        @endif
    </div>

    {{-- Breakdown --}}
    <div class="space-y-4">
        @foreach($categories as $key => $category)
            @php
                $score = $metrics[$key] ?? 0;
                $color = match(true) {
                    $score >= 70 => 'ethics-positive',
                    $score >= 40 => 'ethics-neutral',
                    default => 'ethics-negative'
                };
                $label = match(true) {
                    $score >= 70 => 'Respects',
                    $score >= 40 => 'Mixed',
                    default => 'Exploits'
                };
            @endphp
            
            <div class="flex items-center gap-3">
                <span class="text-xl">{{ $category['icon'] }}</span>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-text-secondary">{{ $category['label'] }}</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sm text-text-primary">{{ round($score) }}%</span>
                            <span class="text-xs px-2 py-0.5 rounded bg-{{ $color }}/20 text-{{ $color }}">{{ $label }}</span>
                        </div>
                    </div>
                    <div class="w-full h-2 bg-bg-tertiary rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $color }} transition-all duration-300" 
                             style="width: {{ $score }}%"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
```

**4. BUTTON COMPONENTS**

File: `resources/views/components/button.blade.php`

Props:
- $variant (primary|secondary|action)
- $size (sm|md|lg)
- $href (optional, makes it a link)

```blade
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-sm rounded-sm',
        'md' => 'px-6 py-3 text-base rounded-sm',
        'lg' => 'px-8 py-4 text-lg rounded-md',
        default => 'px-6 py-3 text-base rounded-sm'
    };
    
    $variantClasses = match($variant) {
        'primary' => 'bg-purple-primary text-text-primary hover:bg-purple-hover active:scale-98',
        'secondary' => 'border border-purple-primary text-purple-primary hover:bg-purple-primary/10',
        'action' => 'bg-amber-primary text-bg-primary hover:bg-amber-hover active:scale-98',
        default => 'bg-purple-primary text-text-primary hover:bg-purple-hover'
    };
    
    $classes = $baseClasses . ' ' . $sizeClasses . ' ' . $variantClasses;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
```

**5. INPUT FIELD**

File: `resources/views/components/input.blade.php`

```blade
@props([
    'label' => null,
    'error' => null,
    'helper' => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label class="block text-sm font-medium text-text-secondary mb-2">
            {{ $label }}
        </label>
    @endif

    <input 
        {{ $attributes->except(['class', 'label', 'error', 'helper'])->class([
            'w-full px-4 py-3 bg-bg-input border rounded-sm text-text-primary placeholder-text-muted transition-colors',
            'border-bg-tertiary focus:border-purple-primary focus:ring-2 focus:ring-purple-primary/20' => !$error,
            'border-red-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20' => $error,
        ]) }}
    >

    @if($error)
        <p class="mt-1 text-sm text-red-500">{{ $error }}</p>
    @endif

    @if($helper && !$error)
        <p class="mt-1 text-xs text-text-muted">{{ $helper }}</p>
    @endif
</div>
```

**6. TAG/CHIP**

File: `resources/views/components/tag.blade.php`

```blade
@props([
    'variant' => 'default',
    'removable' => false,
])

@php
    $variantClasses = match($variant) {
        'purple' => 'bg-purple-muted/20 text-purple-primary',
        'amber' => 'bg-amber-muted/20 text-amber-primary',
        'success' => 'bg-ethics-positive/20 text-ethics-positive',
        'error' => 'bg-ethics-negative/20 text-ethics-negative',
        default => 'bg-bg-tertiary text-text-secondary'
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-sm {$variantClasses}"]) }}>
    {{ $slot }}
    
    @if($removable)
        <button type="button" class="hover:text-text-primary transition" aria-label="Remove">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    @endif
</span>
```

**7. CONSOLE BADGE**

File: `resources/views/components/console-badge.blade.php`

```blade
@props([
    'console',
    'size' => 'md',
])

@php
    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-base',
        default => 'px-3 py-1 text-sm'
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 bg-bg-tertiary text-text-secondary rounded-sm font-medium {$sizeClasses}"]) }}>
    {{ $console->name }}
</span>
```

**8. RATING STARS**

File: `resources/views/components/rating-stars.blade.php`

```blade
@props([
    'rating', // 0-5, can be decimal
    'size' => 'md',
])

@php
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
    
    $sizeClass = match($size) {
        'sm' => 'w-4 h-4',
        'md' => 'w-5 h-5',
        'lg' => 'w-6 h-6',
        default => 'w-5 h-5'
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-0.5']) }}>
    @for($i = 0; $i < $fullStars; $i++)
        <svg class="{{ $sizeClass }} text-amber-primary" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
        </svg>
    @endfor

    @if($hasHalfStar)
        <svg class="{{ $sizeClass }} text-amber-primary" fill="currentColor" viewBox="0 0 20 20">
            <defs>
                <linearGradient id="half-fill">
                    <stop offset="50%" stop-color="currentColor"/>
                    <stop offset="50%" stop-color="transparent"/>
                </linearGradient>
            </defs>
            <path fill="url(#half-fill)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
        </svg>
    @endif

    @for($i = 0; $i < $emptyStars; $i++)
        <svg class="{{ $sizeClass }} text-text-muted" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
        </svg>
    @endfor
</div>
```

**9. BREADCRUMB NAVIGATION**

File: `resources/views/components/breadcrumb.blade.php`

Props:
- $items (array: [['label' => 'Home', 'url' => '/'], ...])

```blade
@props(['items'])

<nav {{ $attributes->merge(['class' => 'flex items-center gap-2 text-sm']) }} aria-label="Breadcrumb">
    @foreach($items as $index => $item)
        @if($index > 0)
            <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        @endif

        @if(isset($item['url']) && $index < count($items) - 1)
            <a href="{{ $item['url'] }}" class="text-text-tertiary hover:text-purple-primary transition">
                {{ $item['label'] }}
            </a>
        @else
            <span class="text-text-primary font-medium">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
```

**10. SKELETON LOADER (for game card)**

File: `resources/views/components/skeleton-card.blade.php`

```blade
<div class="bg-bg-secondary border border-bg-tertiary rounded-md overflow-hidden animate-pulse">
    <div class="aspect-[3/4] bg-bg-tertiary"></div>
    <div class="p-3 space-y-3">
        <div class="h-5 bg-bg-tertiary rounded w-3/4"></div>
        <div class="h-4 bg-bg-tertiary rounded w-1/2"></div>
        <div class="h-3 bg-bg-tertiary rounded w-full"></div>
    </div>
</div>
```

**11. EMPTY STATE**

File: `resources/views/components/empty-state.blade.php`

Props:
- $icon (optional SVG)
- $title
- $message
- $action (optional)

```blade
@props([
    'icon' => null,
    'title',
    'message' => null,
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-16 px-4 text-center']) }}>
    @if($icon)
        <div class="w-16 h-16 text-text-muted mb-4">
            {!! $icon !!}
        </div>
    @endif

    <h3 class="text-display-sm font-semibold text-text-primary mb-2">{{ $title }}</h3>
    
    @if($message)
        <p class="text-text-secondary max-w-md mb-6">{{ $message }}</p>
    @endif

    @if($action)
        {{ $action }}
    @endif
</div>
```

DELIVERABLES:
1. All 11 components created and tested
2. Components use design tokens from TASK-1-005
3. Components are responsive (mobile-friendly)
4. Each component has clear prop definitions
5. Usage examples documented (see TASK-1-007)
```

## Implementation Notes

**Component Organization:**
All components go in `resources/views/components/`. Laravel auto-discovers them as `<x-component-name>`.

**Prop Validation:**
Use `@props()` directive to define accepted props with defaults. Makes components self-documenting.

**Tailwind Safelist:**
For dynamic colors (ethics bars), add to `tailwind.config.js`:
```javascript
safelist: [
  'bg-ethics-positive',
  'bg-ethics-neutral',
  'bg-ethics-negative',
  'text-ethics-positive',
  'text-ethics-neutral',
  'text-ethics-negative',
]
```

**Alpine.js Integration:**
Components can use Alpine directives when needed (theme toggle does). Keep it minimal - most components are static.

**Accessibility:**
- All interactive elements have proper ARIA labels
- Semantic HTML (nav, button, input)
- Focus states visible
- Color not sole indicator (icons + text for ethics)

**Performance:**
- Use `loading="lazy"` for images
- Skeleton loaders prevent layout shift
- Keep components simple (fast rendering)

## Acceptance Criteria
- [ ] All 11 components created
- [ ] Components render without errors
- [ ] Props work as expected (test with different values)
- [ ] Components use design tokens correctly
- [ ] Responsive on mobile, tablet, desktop
- [ ] Accessible (keyboard navigation, screen readers)
- [ ] Game card displays correctly with/without data
- [ ] UX metrics calculate colors correctly
- [ ] Buttons have all states (hover, active, disabled)
- [ ] Input fields show error states
- [ ] Stars render half-stars correctly
- [ ] Skeleton loader animates
- [ ] Empty state displays cleanly

## Testing Each Component

```blade
{{-- Create test route: routes/web.php --}}
Route::get('/test-components', function () {
    $game = Game::with('consoles')->first();
    $metrics = ['time' => 72, 'money' => 85, 'psychology' => 68, 'social' => 90, 'privacy' => 45];
    return view('test-components', compact('game', 'metrics'));
});

{{-- resources/views/test-components.blade.php --}}
<div class="p-8 space-y-8 bg-bg-primary min-h-screen">
    <x-game-card :game="$game" />
    <x-ux-metrics-detailed :metrics="$metrics" :voteCount="1247" />
    <x-button variant="primary">Primary Button</x-button>
    <x-button variant="secondary">Secondary Button</x-button>
    <x-rating-stars :rating="4.5" />
</div>
```

---
**Related Tasks:** TASK-1-005 (Design System), TASK-1-007 (Rubric Showcase)  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 8-12 hours  
**Priority:** High - Essential for Phase 2 UI
