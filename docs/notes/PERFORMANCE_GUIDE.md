# ADUX Performance Optimization Guide

**Philosophy:** Perceived performance > actual milliseconds  
**Storage:** S3 from day 1 (AI scraping = 100k+ games)  
**Inspired by:** McMaster-Carr's instant-feel catalog

---

## Core Principles

1. **Optimize for perceived performance** - Does it feel instant?
2. **Cache aggressively** - Invalidate only what changed
3. **Load only what's needed** - Page-specific JS bundles
4. **Progressive enhancement** - Works without JS, better with JS

---

## Image Optimization 📸

### Store Dimensions in Database

**Migration:**
```php
Schema::create('game_submissions', function (Blueprint $table) {
    // ... other fields
    $table->integer('image_width')->nullable();
    $table->integer('image_height')->nullable();
    $table->integer('image_filesize')->nullable();
    $table->string('image_format')->nullable(); // webp, jpg, png
});
```

**Upload Service:**
```php
public function processImageSubmission(UploadedFile $image, Game $game, string $fieldType): GameSubmission
{
    [$width, $height] = getimagesize($image->getRealPath());
    
    $path = Storage::disk('s3')->put("submissions/{$game->slug}/{$fieldType}", $image, 'public');
    
    return GameSubmission::create([
        'game_id' => $game->id,
        'field_type' => $fieldType,
        'content' => Storage::disk('s3')->url($path),
        'submitted_by' => auth()->id(),
        'image_width' => $width,
        'image_height' => $height,
        'image_filesize' => $image->getSize(),
        'image_format' => $image->extension(),
    ]);
}
```

**Blade Component:**
```blade
{{-- components/game-image.blade.php --}}
<img 
    src="{{ $submission->content }}" 
    width="{{ $submission->image_width }}"
    height="{{ $submission->image_height }}"
    alt="{{ $alt }}"
    loading="{{ $lazy ? 'lazy' : 'eager' }}"
    class="{{ $class }}"
>
```

**Why:** Prevents layout shift (perfect CLS score)

---

### Lazy Loading Strategy

```blade
{{-- Above fold: eager --}}
<x-game-image :submission="$game->boxArt" alt="{{ $game->name }}" :lazy="false" />

{{-- Below fold: lazy --}}
@foreach($game->screenshots as $screenshot)
    <x-game-image :submission="$screenshot" alt="Screenshot" :lazy="true" />
@endforeach

{{-- Grid browsing: all lazy --}}
@foreach($games as $game)
    <x-game-image :submission="$game->boxArt" alt="{{ $game->name }}" :lazy="true" />
@endforeach
```

**Bandwidth savings:** 40-60% (don't load images users never see)

---

## S3 Configuration 🪣

### Laravel Setup

```php
// config/filesystems.php
'default' => env('FILESYSTEM_DISK', 's3'),

's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'bucket' => env('AWS_BUCKET', 'adux-game-images'),
    'url' => env('AWS_URL'),
    'throw' => true,
],
```

```bash
# .env
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=adux-game-images
FILESYSTEM_DISK=s3
```

### S3 Bucket Policy

```json
{
  "Version": "2012-10-17",
  "Statement": [{
    "Effect": "Allow",
    "Principal": "*",
    "Action": "s3:GetObject",
    "Resource": "arn:aws:s3:::adux-game-images/submissions/*"
  }]
}
```

**Security:** Only `submissions/` folder is public, not entire bucket.

### Cost

```
100GB images: $2.30/month
Bandwidth (10GB/mo): $0.90/month
Total: $3.20/month

With CloudFront (Phase 3):
Storage: $2.30/month
Bandwidth: $0.10/month (cached)
Total: $2.40/month + faster globally
```

---

## Prefetch on Hover ⚡

### Smart Implementation (Desktop + Fast Connections Only)

```javascript
// resources/js/prefetch.js
export function initPrefetch() {
    const canPrefetch = () => {
        // Desktop only
        if (window.matchMedia('(max-width: 768px)').matches) return false;
        
        // Fast connections only
        if (navigator.connection) {
            const type = navigator.connection.effectiveType;
            if (navigator.connection.saveData || type === 'slow-2g' || type === '2g') {
                return false;
            }
        }
        
        return true;
    };
    
    let hoverTimeout;
    
    document.addEventListener('mouseenter', (e) => {
        const link = e.target.closest('[data-prefetch]');
        if (!link || link.dataset.prefetched === 'true') return;
        
        hoverTimeout = setTimeout(() => {
            if (canPrefetch()) {
                const prefetchLink = document.createElement('link');
                prefetchLink.rel = 'prefetch';
                prefetchLink.href = link.dataset.prefetch || link.href;
                document.head.appendChild(prefetchLink);
                link.dataset.prefetched = 'true';
            }
        }, 100); // Debounce: only prefetch if hover lasts 100ms
    }, true);
    
    document.addEventListener('mouseleave', () => clearTimeout(hoverTimeout), true);
}
```

**Usage:**
```blade
<a href="{{ route('games.show', $game->slug) }}" 
   data-prefetch="{{ route('games.show', $game->slug) }}"
   class="game-card">
   {{-- Card content --}}
</a>
```

**Bandwidth impact:** +10-15% (vs +50% naive implementation)  
**Speed impact:** Clicks feel instant

---

## JavaScript Loading 📦

### Vite Bundle Splitting

```javascript
// vite.config.js
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',      // Alpine.js base
                'resources/js/voting.js',   // Voting system
                'resources/js/editor.js',   // Markdown editor
                'resources/js/charts.js',   // UX metrics charts
            ],
            refresh: true,
        }),
    ],
});
```

### Page-Specific Loading

```blade
{{-- Base layout: Alpine only --}}
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('scripts')

{{-- Game page: add voting + charts --}}
@push('scripts')
    @vite(['resources/js/voting.js', 'resources/js/charts.js'])
@endpush

{{-- Forum page: add editor --}}
@push('scripts')
    @vite('resources/js/editor.js')
@endpush
```

**Result:**
- Browse pages: 15KB JS
- Game pages: 30KB JS
- Forum pages: 40KB JS

**Bandwidth savings:** 50%+ vs loading everything on every page

---

## Response Caching 💾

### Configuration

```php
// config/responsecache.php
return [
    'enabled' => env('RESPONSE_CACHE_ENABLED', true),
    'cache_lifetime_in_seconds' => 60 * 60 * 24 * 7, // 1 week
    'cache_store' => 'redis',
    'cache_profile' => \App\Http\CacheProfiles\AduxCacheProfile::class,
];
```

### Cache Profile (Don't Cache Logged-In Users)

```php
// app/Http/CacheProfiles/AduxCacheProfile.php
class AduxCacheProfile implements CacheProfile
{
    public function shouldCacheRequest(Request $request): bool
    {
        if ($request->user()) return false;
        if ($request->isMethod('POST')) return false;
        if ($request->is('admin/*') || $request->is('telescope/*')) return false;
        
        return true;
    }
    
    public function cacheNameSuffix(Request $request): string
    {
        return md5($request->fullUrl());
    }
}
```

### Invalidate When Winners Change

```php
// app/Jobs/CalculateWinners.php
public function handle(): void
{
    $newWinner = GameSubmission::where('game_id', $this->game->id)
        ->where('field_type', $this->fieldType)
        ->withSum('votes as total_karma', 'users.karma_points')
        ->orderByDesc('total_karma')
        ->first();
    
    $currentWinner = GameSubmission::where('game_id', $this->game->id)
        ->where('field_type', $this->fieldType)
        ->where('is_current_winner', true)
        ->first();
    
    if (!$currentWinner || $newWinner->id !== $currentWinner->id) {
        if ($currentWinner) {
            $currentWinner->update(['is_current_winner' => false]);
        }
        
        $newWinner->update(['is_current_winner' => true]);
        
        // Invalidate cache
        ResponseCache::forget("games.{$this->game->slug}");
        ResponseCache::forget("api.games.{$this->game->slug}");
        
        // Sync to Show DB
        $this->syncToShowDb();
    }
}
```

**CPU savings:** 90% (first request generates, next 10,000 read from cache)

---

## Layout Stability 🎯

### Font Loading

```html
<head>
    <link rel="preload" href="/fonts/inter-var.woff2" as="font" type="font/woff2" crossorigin>
    
    <style>
        @font-face {
            font-family: 'Inter';
            src: url('/fonts/inter-var.woff2') format('woff2');
            font-weight: 100 900;
            font-display: swap; /* Show fallback, then swap */
        }
    </style>
</head>
```

**Why:** Prevents "flash of invisible text" (FOIT)

### Reserve Space for Dynamic Content

```blade
{{-- Skeleton screen while loading --}}
<div style="min-height: 200px;">
    @livewire('game-reviews', ['gameId' => $game->id])->lazy
</div>
```

---

## Livewire Optimization ⚡

### Lazy Load Components

```blade
{{-- DON'T: loads immediately --}}
@livewire('game-reviews', ['gameId' => $game->id])

{{-- DO: loads when scrolled into view --}}
@livewire('game-reviews', ['gameId' => $game->id], key('reviews'))->lazy
```

### Optimistic UI Updates

```php
// app/Livewire/VoteButton.php
public function vote()
{
    // Update UI immediately (optimistic)
    $this->voteCount++;
    $this->hasVoted = true;
    
    try {
        Vote::create([
            'user_id' => auth()->id(),
            'submission_id' => $this->submissionId,
        ]);
    } catch (\Exception $e) {
        // Rollback if failed
        $this->voteCount--;
        $this->hasVoted = false;
        session()->flash('error', 'Vote failed');
    }
}
```

```blade
<button 
    wire:click="vote"
    wire:loading.attr="disabled"
    wire:loading.class="opacity-50"
>
    <span wire:loading.remove>Vote</span>
    <span wire:loading>Voting...</span>
</button>
```

**Result:** Voting feels instant

### Smart Polling

```blade
{{-- DON'T: polls forever --}}
<div wire:poll.2s>{{ $voteCount }} votes</div>

{{-- DO: only polls when visible --}}
<div wire:poll.5s.visible>{{ $voteCount }} votes</div>

{{-- BETTER: use events --}}
#[On('vote-cast')]
public function refreshVotes($gameId) {
    if ($this->gameId === $gameId) {
        $this->voteCount = Vote::where('game_id', $gameId)->count();
    }
}
```

---

## What NOT to Do ❌

### ❌ Sprite Sheets
McMaster can do this (fixed catalog). You can't (user-submitted, 100k+ games).

### ❌ Preload Everything
Wastes bandwidth. Use hover-triggered prefetch instead.

### ❌ Cache User Pages
```php
// BAD: caches User A's dashboard, shows to User B
ResponseCache::cache(fn() => view('dashboard'));

// GOOD: only cache public pages
if (!auth()->check()) {
    ResponseCache::cache(fn() => view('games.show', $game));
}
```

### ❌ Heavy JS Frameworks
React/Vue SPA = loses server-rendering benefits. Livewire + Alpine = best of both worlds.

---

## Phase Goals 🎯

### Phase 1: Foundation (Current)
**Implement:**
- ✅ Image dimensions in database
- ✅ Lazy loading
- ✅ S3 storage
- ✅ Basic response caching
- ✅ JS bundle splitting

**Performance Target:**
- First load: < 1s
- Cached load: < 100ms

---

### Phase 2: Features
**Add:**
- ✅ Prefetch on hover (smart guards)
- ✅ Livewire lazy loading
- ✅ Optimistic UI for voting
- ✅ Cache invalidation on winner changes

**Measure:**
- Core Web Vitals (LCP < 2.5s, FID < 100ms, CLS < 0.1)
- Cache hit rate (target: 80%+)

---

### Phase 3: Scale
**Consider:**
- CloudFront CDN (global speed + saves money)
- Image processing pipeline (responsive images)
- Read replicas for Show DB
- Service workers

**Don't add unless proven necessary**

---

## Monitoring 📊

### Core Web Vitals

```javascript
// resources/js/analytics.js
import {onCLS, onFID, onLCP} from 'web-vitals';

onCLS(metric => fetch('/api/analytics/web-vitals', {
    method: 'POST',
    body: JSON.stringify(metric)
}));

onFID(metric => fetch('/api/analytics/web-vitals', {
    method: 'POST',
    body: JSON.stringify(metric)
}));

onLCP(metric => fetch('/api/analytics/web-vitals', {
    method: 'POST',
    body: JSON.stringify(metric)
}));
```

### Cache Hit Rate

```php
// Track in middleware
if (ResponseCache::hasBeenCached()) {
    Cache::increment('cache-hit-rate:' . date('Y-m-d') . ':hits');
} else {
    Cache::increment('cache-hit-rate:' . date('Y-m-d') . ':misses');
}
```

### Use Telescope

Already installed! Monitor:
- Slow queries (> 100ms)
- Cache hits/misses
- Queue job performance
- HTTP request times

**Access:** http://localhost:8000/telescope

---

## Performance Checklist ✅

### Every Image
- [ ] Width/height set in HTML
- [ ] Lazy loading for below-fold
- [ ] Alt text provided
- [ ] Dimensions stored in database

### Every Page
- [ ] Response caching enabled (if public)
- [ ] Only necessary JS loaded
- [ ] Fonts preloaded with font-display: swap
- [ ] No layout shift (CLS < 0.1)

### Every Livewire Component
- [ ] Lazy load if below fold
- [ ] No unnecessary polling
- [ ] Optimistic updates where appropriate
- [ ] Loading states shown

### Every Query
- [ ] Eager load relationships (no N+1)
- [ ] Indexed columns in WHERE/ORDER BY
- [ ] Query time < 50ms

---

## Complete Example: Game Detail Page

```blade
@extends('layouts.app')

@section('title', $game->name . ' - ADUX')

@push('scripts')
    @vite(['resources/js/voting.js', 'resources/js/charts.js'])
@endpush

@section('content')
<div class="game-detail">
    {{-- Above fold: eager load --}}
    <div class="game-header">
        <x-game-image 
            :submission="$game->boxArt" 
            alt="{{ $game->name }} box art"
            :lazy="false"
            class="box-art"
        />
        
        <div class="game-info">
            <h1>{{ $game->name }}</h1>
            <p>{{ $game->description }}</p>
            
            @livewire('vote-button', [
                'submissionId' => $game->boxArt->id,
                'currentVotes' => $game->boxArt->votes_count
            ])
        </div>
    </div>
    
    {{-- Below fold: lazy load --}}
    <div class="screenshots">
        @foreach($game->screenshots as $screenshot)
            <x-game-image 
                :submission="$screenshot" 
                alt="{{ $game->name }} screenshot"
                :lazy="true"
            />
        @endforeach
    </div>
    
    @livewire('game-reviews', ['gameId' => $game->id])->lazy
    
    {{-- Related games: prefetch on hover --}}
    <div class="related-games">
        @foreach($relatedGames as $related)
            <a href="{{ route('games.show', $related->slug) }}"
               data-prefetch="{{ route('games.show', $related->slug) }}"
               class="game-card">
               <x-game-image :submission="$related->boxArt" alt="{{ $related->name }}" :lazy="true" />
            </a>
        @endforeach
    </div>
</div>
@endsection
```

---

## Quick Reference

**McMaster principles adapted for ADUX:**
1. ✅ Fixed image dimensions (stored in DB)
2. ✅ Prefetch on hover (smart guards)
3. ✅ Aggressive caching (invalidate precisely)
4. ✅ Page-specific JS (no bloat)
5. ✅ Lazy loading (bandwidth savings)
6. ✅ Optimistic UI (instant feel)

**Key trade-off:**
- Prefetch adds 10-15% bandwidth
- But clicks feel instant
- Worth it for desktop users

**Storage:**
- S3 from day 1 (AI scraping = scale fast)
- $2-3/month for 100GB images
- Add CloudFront in Phase 3

**Remember:** Perceived performance > actual milliseconds. Make it *feel* fast.

---

**Document Version:** 2.0 (S3-optimized)  
**Last Updated:** January 19, 2026  
**For:** ADUX Phase 1+
