# [TASK-1-005] Design System Foundation (/design Page)

## Context
Create the `/design` route and page that serves as ADUX's living style guide. This page documents all design decisions (colors, typography, spacing, icons) and provides a reference for developers implementing features in Phase 2. Based on the comprehensive Design.md specification.

## Prerequisites
**Files to attach to this task prompt:**
- [ ] Design.md (complete design specification)
- [ ] README.md (project overview and tech stack)
- [ ] Prototype style.css (optional reference for current aesthetic)

**Conditions that must be met:**
- [ ] Laravel 11 with Livewire 3 and Alpine.js configured
- [ ] Tailwind CSS installed and configured
- [ ] Understanding of dark/light mode toggle architecture

## Deliverables
- `/design` route registered
- `resources/views/design.blade.php` template
- Tailwind config updated with custom colors, spacing, typography
- CSS variables for theme switching (dark/light mode)
- Interactive examples of design tokens
- Navigation component for design system
- Theme toggle component (dark/light mode)
- Documentation of how to use design tokens

## AI Prompt
```
Create a living style guide at `/design` for ADUX that documents all design tokens and provides interactive examples.

**DESIGN SPECIFICATIONS (from Design.md):**

**Color System:**
Dark mode (default):
- Backgrounds: #0f0f13 (primary), #1a1a1f (secondary), #26262e (tertiary)
- Purple brand: #9D5AAF (primary), #B76DC9 (hover), #7A4A8C (muted)
- Amber accent: #FFA726 (primary), #FFB74D (hover), #FB8C00 (muted)
- Ethics colors: #10B981 (positive), #EF4444 (negative), #9CA3AF (neutral)
- Text: #F9FAFB (primary), #D1D5DB (secondary), #9CA3AF (tertiary)

Light mode:
- Backgrounds: #FFFFFF (primary), #F9FAFB (secondary), #F3F4F6 (tertiary)
- Purple: #7A4A8C (primary), #6B3A7D (hover), #E9D5F0 (muted)
- Amber: #D97706 (primary), #B45309 (hover), #FEF3C7 (muted)
- Text: #111827 (primary), #374151 (secondary), #6B7280 (tertiary)

**Typography:**
- Primary font: Inter
- Display font: Poppins (H1, H2 only)
- Monospace: JetBrains Mono (for data/ethics percentages)
- Scale: H1 (39px), H2 (31px), H3 (25px), H4 (20px), Body (16px)

**Spacing:**
8pt grid: 4px, 8px, 12px, 16px, 24px, 32px, 48px, 64px, 96px

**Border Radius:**
- sm: 4px (buttons, tags)
- md: 8px (cards, inputs)
- lg: 12px (modals)
- xl: 16px (hero cards)

**TAILWIND CONFIGURATION:**

Update `tailwind.config.js`:
```javascript
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  darkMode: 'class', // Enable class-based dark mode
  theme: {
    extend: {
      colors: {
        // Dark mode colors (default)
        bg: {
          primary: '#0f0f13',
          secondary: '#1a1a1f',
          tertiary: '#26262e',
          input: '#1f1f25',
        },
        purple: {
          primary: '#9D5AAF',
          hover: '#B76DC9',
          muted: '#7A4A8C',
        },
        amber: {
          primary: '#FFA726',
          hover: '#FFB74D',
          muted: '#FB8C00',
        },
        ethics: {
          positive: '#10B981',
          negative: '#EF4444',
          neutral: '#9CA3AF',
          bg: '#1f2937',
        },
        text: {
          primary: '#F9FAFB',
          secondary: '#D1D5DB',
          tertiary: '#9CA3AF',
          muted: '#6B7280',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['Poppins', 'Inter', 'sans-serif'],
        mono: ['JetBrains Mono', 'Fira Code', 'monospace'],
      },
      fontSize: {
        'display-xl': ['2.441rem', { lineHeight: '1.2', letterSpacing: '-0.02em' }],
        'display-lg': ['1.953rem', { lineHeight: '1.3', letterSpacing: '-0.01em' }],
        'display-md': ['1.563rem', { lineHeight: '1.4' }],
        'display-sm': ['1.25rem', { lineHeight: '1.5' }],
      },
      spacing: {
        '18': '4.5rem',
        '88': '22rem',
      },
      borderRadius: {
        'sm': '4px',
        'md': '8px',
        'lg': '12px',
        'xl': '16px',
      },
    },
  },
  plugins: [],
}
```

**CSS VARIABLES FOR THEME SWITCHING:**

Create `resources/css/theme.css`:
```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700&family=JetBrains+Mono:wght@400;500&display=swap');

:root {
  /* Dark mode (default) */
  --color-bg-primary: 15 15 19;
  --color-bg-secondary: 26 26 31;
  --color-bg-tertiary: 38 38 46;
  --color-bg-input: 31 31 37;
  
  --color-purple-primary: 157 90 175;
  --color-purple-hover: 183 109 201;
  --color-purple-muted: 122 74 140;
  
  --color-amber-primary: 255 167 38;
  --color-amber-hover: 255 183 77;
  --color-amber-muted: 251 140 0;
  
  --color-text-primary: 249 250 251;
  --color-text-secondary: 209 213 219;
  --color-text-tertiary: 156 163 175;
  --color-text-muted: 107 114 128;
}

.light-mode {
  --color-bg-primary: 255 255 255;
  --color-bg-secondary: 249 250 251;
  --color-bg-tertiary: 243 244 246;
  --color-bg-input: 255 255 255;
  
  --color-purple-primary: 122 74 140;
  --color-purple-hover: 107 58 125;
  --color-purple-muted: 233 213 240;
  
  --color-amber-primary: 217 119 6;
  --color-amber-hover: 180 83 9;
  --color-amber-muted: 254 243 199;
  
  --color-text-primary: 17 24 39;
  --color-text-secondary: 55 65 81;
  --color-text-tertiary: 107 114 128;
  --color-text-muted: 156 163 175;
}

/* Smooth transitions on theme change */
* {
  transition: background-color 200ms ease, color 200ms ease, border-color 200ms ease;
}
```

Import in `resources/css/app.css`:
```css
@import 'theme.css';
@tailwind base;
@tailwind components;
@tailwind utilities;
```

**DESIGN PAGE STRUCTURE:**

```blade
{{-- resources/views/design.blade.php --}}
<x-layout>
    <div class="min-h-screen bg-bg-primary">
        {{-- Header --}}
        <header class="bg-bg-secondary border-b border-bg-tertiary sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-display-lg font-display text-text-primary">ADUX Design System</h1>
                        <p class="text-text-secondary mt-1">Foundation for building consistent, accessible interfaces</p>
                    </div>
                    
                    {{-- Theme Toggle --}}
                    <x-theme-toggle />
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-6 py-12">
            {{-- Navigation --}}
            <nav class="mb-12 flex flex-wrap gap-2">
                <a href="#colors" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Colors</a>
                <a href="#typography" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Typography</a>
                <a href="#spacing" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Spacing</a>
                <a href="#borders" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Borders</a>
                <a href="#icons" class="px-4 py-2 bg-bg-secondary hover:bg-purple-primary/10 rounded-md text-text-secondary hover:text-purple-primary transition">Icons</a>
            </nav>

            {{-- Colors Section --}}
            <section id="colors" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Color System</h2>
                
                <div class="space-y-8">
                    {{-- Backgrounds --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Backgrounds</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-4 bg-bg-primary border border-bg-tertiary rounded-lg">
                                <div class="w-full h-20 bg-bg-primary rounded mb-2 border border-text-muted"></div>
                                <p class="text-text-primary font-mono text-sm">bg-primary</p>
                                <p class="text-text-tertiary text-xs">#0f0f13</p>
                            </div>
                            <div class="p-4 bg-bg-primary border border-bg-tertiary rounded-lg">
                                <div class="w-full h-20 bg-bg-secondary rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">bg-secondary</p>
                                <p class="text-text-tertiary text-xs">#1a1a1f</p>
                            </div>
                            <div class="p-4 bg-bg-primary border border-bg-tertiary rounded-lg">
                                <div class="w-full h-20 bg-bg-tertiary rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">bg-tertiary</p>
                                <p class="text-text-tertiary text-xs">#26262e</p>
                            </div>
                            <div class="p-4 bg-bg-primary border border-bg-tertiary rounded-lg">
                                <div class="w-full h-20 bg-bg-input rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">bg-input</p>
                                <p class="text-text-tertiary text-xs">#1f1f25</p>
                            </div>
                        </div>
                    </div>

                    {{-- Brand Colors --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Brand Colors</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            {{-- Purple --}}
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-purple-primary rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">purple-primary</p>
                                <p class="text-text-tertiary text-xs">#9D5AAF</p>
                            </div>
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-purple-hover rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">purple-hover</p>
                                <p class="text-text-tertiary text-xs">#B76DC9</p>
                            </div>
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-purple-muted rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">purple-muted</p>
                                <p class="text-text-tertiary text-xs">#7A4A8C</p>
                            </div>
                            {{-- Amber --}}
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-amber-primary rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">amber-primary</p>
                                <p class="text-text-tertiary text-xs">#FFA726</p>
                            </div>
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-amber-hover rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">amber-hover</p>
                                <p class="text-text-tertiary text-xs">#FFB74D</p>
                            </div>
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-amber-muted rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">amber-muted</p>
                                <p class="text-text-tertiary text-xs">#FB8C00</p>
                            </div>
                        </div>
                    </div>

                    {{-- Ethics Colors --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Ethics Rating Colors</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-ethics-positive rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">ethics-positive</p>
                                <p class="text-text-tertiary text-xs">Respects Players</p>
                            </div>
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-ethics-negative rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">ethics-negative</p>
                                <p class="text-text-tertiary text-xs">Exploits Players</p>
                            </div>
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-ethics-neutral rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">ethics-neutral</p>
                                <p class="text-text-tertiary text-xs">Neutral/Unrated</p>
                            </div>
                            <div class="p-4 bg-bg-secondary rounded-lg">
                                <div class="w-full h-20 bg-ethics-bg rounded mb-2"></div>
                                <p class="text-text-primary font-mono text-sm">ethics-bg</p>
                                <p class="text-text-tertiary text-xs">Background</p>
                            </div>
                        </div>
                    </div>

                    {{-- Text Colors --}}
                    <div>
                        <h3 class="text-display-sm text-text-primary mb-4">Text Colors</h3>
                        <div class="space-y-2 bg-bg-secondary p-6 rounded-lg">
                            <p class="text-text-primary text-lg">Primary text - Headings and important content</p>
                            <p class="text-text-secondary">Secondary text - Body copy and descriptions</p>
                            <p class="text-text-tertiary text-sm">Tertiary text - Metadata and supporting info</p>
                            <p class="text-text-muted text-xs">Muted text - Disabled or de-emphasized</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Typography Section --}}
            <section id="typography" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Typography</h2>
                
                <div class="space-y-6 bg-bg-secondary p-8 rounded-lg">
                    <div>
                        <h1 class="text-display-xl font-display text-text-primary">Display XL - Poppins Bold</h1>
                        <code class="text-text-tertiary text-sm">text-display-xl font-display</code>
                    </div>
                    <div>
                        <h2 class="text-display-lg font-display text-text-primary">Display LG - Poppins SemiBold</h2>
                        <code class="text-text-tertiary text-sm">text-display-lg font-display</code>
                    </div>
                    <div>
                        <h3 class="text-display-md font-semibold text-text-primary">Display MD - Inter SemiBold</h3>
                        <code class="text-text-tertiary text-sm">text-display-md font-semibold</code>
                    </div>
                    <div>
                        <h4 class="text-display-sm font-semibold text-text-primary">Display SM - Inter SemiBold</h4>
                        <code class="text-text-tertiary text-sm">text-display-sm font-semibold</code>
                    </div>
                    <div>
                        <p class="text-base text-text-secondary">Body text - Inter Regular. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                        <code class="text-text-tertiary text-sm">text-base</code>
                    </div>
                    <div>
                        <p class="text-sm text-text-secondary">Small text - Inter Regular. Supporting information and captions.</p>
                        <code class="text-text-tertiary text-sm">text-sm</code>
                    </div>
                    <div>
                        <p class="text-xs text-text-tertiary uppercase tracking-wider">Label Text - Inter Medium</p>
                        <code class="text-text-tertiary text-sm">text-xs uppercase tracking-wider</code>
                    </div>
                    <div>
                        <p class="font-mono text-sm text-text-primary">82% - JetBrains Mono (Ethics Data)</p>
                        <code class="text-text-tertiary text-sm">font-mono text-sm</code>
                    </div>
                </div>
            </section>

            {{-- Spacing Section --}}
            <section id="spacing" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Spacing Scale (8pt Grid)</h2>
                
                <div class="space-y-4">
                    @foreach([1 => '4px', 2 => '8px', 3 => '12px', 4 => '16px', 6 => '24px', 8 => '32px', 12 => '48px', 16 => '64px', 24 => '96px'] as $class => $px)
                        <div class="flex items-center gap-4">
                            <code class="text-text-primary font-mono w-20">space-{{ $class }}</code>
                            <div class="h-8 bg-purple-primary" style="width: {{ $px }}"></div>
                            <span class="text-text-tertiary">{{ $px }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Border Radius Section --}}
            <section id="borders" class="mb-20">
                <h2 class="text-display-md font-display text-text-primary mb-6">Border Radius</h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="text-center">
                        <div class="w-32 h-32 bg-purple-primary rounded-sm mx-auto mb-2"></div>
                        <code class="text-text-primary font-mono">rounded-sm</code>
                        <p class="text-text-tertiary text-sm">4px</p>
                    </div>
                    <div class="text-center">
                        <div class="w-32 h-32 bg-purple-primary rounded-md mx-auto mb-2"></div>
                        <code class="text-text-primary font-mono">rounded-md</code>
                        <p class="text-text-tertiary text-sm">8px</p>
                    </div>
                    <div class="text-center">
                        <div class="w-32 h-32 bg-purple-primary rounded-lg mx-auto mb-2"></div>
                        <code class="text-text-primary font-mono">rounded-lg</code>
                        <p class="text-text-tertiary text-sm">12px</p>
                    </div>
                    <div class="text-center">
                        <div class="w-32 h-32 bg-purple-primary rounded-xl mx-auto mb-2"></div>
                        <code class="text-text-primary font-mono">rounded-xl</code>
                        <p class="text-text-tertiary text-sm">16px</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-layout>
```

**THEME TOGGLE COMPONENT:**

```blade
{{-- resources/views/components/theme-toggle.blade.php --}}
<button 
    x-data="{ theme: localStorage.getItem('theme') || 'dark' }"
    x-init="
        $watch('theme', value => {
            localStorage.setItem('theme', value);
            if (value === 'light') {
                document.documentElement.classList.add('light-mode');
            } else {
                document.documentElement.classList.remove('light-mode');
            }
        });
        // Apply on load
        if (theme === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    "
    @click="theme = theme === 'dark' ? 'light' : 'dark'"
    class="p-2 rounded-md bg-bg-secondary hover:bg-bg-tertiary transition"
    :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
>
    <svg x-show="theme === 'dark'" class="w-6 h-6 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
    </svg>
    <svg x-show="theme === 'light'" class="w-6 h-6 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
    </svg>
</button>
```

**ROUTE:**

```php
// routes/web.php
Route::get('/design', function () {
    return view('design');
})->name('design');
```

DELIVERABLES:
1. `/design` route accessible
2. Tailwind config extended with ADUX colors and tokens
3. CSS theme variables for dark/light mode
4. Theme toggle working
5. All design tokens displayed with examples
6. Google Fonts imported
7. Smooth transitions between themes
```

## Implementation Notes

**Font Loading:**
Use Google Fonts CDN for development. In production (Phase 3), self-host fonts for performance.

**Dark Mode Default:**
The system defaults to dark mode. Light mode is activated via class on `<html>`. Alpine.js handles the toggle and localStorage persistence.

**Color Usage:**
- Purple: Navigation, primary CTAs, brand moments
- Amber: Secondary CTAs, hover states, community features
- Ethics colors: ONLY for rating system (maintain objectivity)

**Accessibility:**
All color combinations verified for WCAG AA contrast. Text colors meet minimum 4.5:1 ratio on their backgrounds.

**CSS Variable Approach:**
Using CSS variables (not just Tailwind classes) allows dynamic theming without recompiling. Important for theme toggle.

## Acceptance Criteria
- [ ] `/design` route renders successfully
- [ ] All color swatches display correctly
- [ ] Typography examples show correct fonts and sizes
- [ ] Spacing examples demonstrate 8pt grid
- [ ] Border radius examples show all sizes
- [ ] Theme toggle switches between dark/light mode
- [ ] Theme preference persists in localStorage
- [ ] All text remains readable in both modes
- [ ] Transitions are smooth (not jarring)
- [ ] Google Fonts load correctly
- [ ] Tailwind config includes all custom tokens
- [ ] Page is responsive (mobile-friendly)

---
**Related Tasks:** TASK-1-006 (Components), TASK-1-007 (Rubric Showcase)  
**Phase:** 1 (Architecture & Foundation)  
**Estimated Time:** 4-6 hours  
**Priority:** Medium - Foundation for all UI work
