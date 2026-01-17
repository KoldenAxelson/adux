# [TASK-2-000] Phase 2 Overview: Feature Development

## Purpose
This is a conversational task to plan Phase 2 features. Phase 2 is THE LONGEST PHASE - potentially years of iterative development. It's where ADUX actually becomes functional and useful. The goal is to prioritize what's essential for MVP, then build additional features as time/interest allows.

## Phase 2 Reality Check

**This phase could take YEARS.** That's okay - it's a passion project. You'll iterate, add features as you want, polish as you go. Don't rush to Phase 3 (scaling) until you actually have traffic that justifies the cost.

**Marketing matters more than features.** You could launch with just game browsing + UX voting and be valuable. Or spend 2 years building everything and never market it. Balance building with getting users.

## Phase 2 Scope (Massive)

### Essential for MVP Launch
**Without these, ADUX doesn't work:**
- Game browsing and search
- Game detail pages showing Show DB data
- UX metrics voting (Exploit vs Respect)
- User registration and basic profiles
- Karma system (points for contributions)
- Basic moderation tools
- New game submission workflow (approval queue)
- Winner calculation queue job (Main → Show DB sync)

### MyAnimeList Features
**Track games like anime:**
- User game lists (Playing, Completed, Wishlist, Bought, Custom)
- Personal ratings (separate from public reviews)
- Started/completed dates
- Notes per game
- Profile statistics (games completed, hours played estimates)

### Community Features
**Reddit + GameFAQs aspects:**
- Discussion forums (per-game, per-console, per-platform)
- Thread creation, replies, nested comments
- Forum moderation (lock, pin, delete)
- Walkthroughs/guides (markdown or monospaced format)
- Guide voting (best guides rise to top)

### Democratic Data Curation
**The unique ADUX feature:**
- Submit data for game fields (box art, screenshots, descriptions, etc.)
- Vote on submissions (karma-weighted)
- See current winners and alternatives
- Track submission history
- Winner-at-end-of-day sync to Show DB
- Visit tracking for popularity-based sync frequency

### Additional Features (Build When Desired)
**Nice-to-haves:**
- News section (gaming news with categories)
- Shop (merch, if you want to monetize)
- User avatars and profiles customization
- Review voting (helpful/not helpful)
- Follow users
- Activity feeds
- Notifications
- Search filters (advanced)
- Top charts/leaderboards

### Performance & API
**Technical improvements:**
- Laravel Octane (Swoole) for speed
- Response caching for static pages
- API endpoints (read-only from Show DB)
- API rate limiting
- SEO optimization (meta tags, sitemaps, structured data)

## How to Prioritize

**Start with the MVP Essentials.** Get those working and LAUNCH. Get feedback from real users (even if it's just 5 people). Then iterate based on what people actually want.

**Don't build everything before launching.** You'll spend 2 years and discover people wanted something different.

## Conversation Starters for AI

When planning Phase 2 tasks, have a conversation about:

1. **MVP Definition**
   - "What's the absolute minimum for a useful ADUX?"
   - "Can we launch without forums? Without lists?"
   - "What would make you personally use this daily?"

2. **Submission System UX**
   - "How do users submit data? (Upload image, paste URL?)"
   - "How do we prevent spam/low-quality submissions?"
   - "Should submissions be moderated before going live?"

3. **Forum Structure**
   - "Do we need forum categories within games?"
   - "Moderation workflow?"
   - "Anti-spam measures?"

4. **Karma Balance**
   - "How many points for: review, helpful vote, submission, etc.?"
   - "Should karma decay over time?"
   - "How to prevent karma farming?"

5. **Show DB Sync Strategy**
   - "What triggers a sync? (Time-based, event-based, both?)"
   - "How to handle ties in voting?"
   - "Graceful degradation if sync fails?"

## Suggested Phase 2 Sub-Phases

### 2a: MVP Core (Launch This)
- Game browsing/search (from Show DB)
- Game detail pages
- UX metrics voting
- User registration/profiles
- Basic karma (points for actions)
- New game submission workflow
- Winner calculation + sync job
- **LAUNCH** with this

### 2b: Community Basics
- User game lists (MyAnimeList features)
- Per-game forums (basic)
- Review system
- Submission system (box art, descriptions)
- Karma-weighted voting on submissions

### 2c: Advanced Community
- Platform/console forums
- Walkthroughs/guides
- Forum moderation tools
- Advanced profiles
- Activity feeds
- Notifications

### 2d: Polish & Performance
- Laravel Octane
- Static page generation
- Advanced caching
- API v1 endpoints
- SEO optimization
- Analytics integration

## AI Prompt Template
```
I'm starting Phase 2 of ADUX. Phase 1 is complete:
- Dual-database schema designed (Main + Show)
- Design system at /rubric
- Reusable components ready
- API structure prepared
- Hierarchical platform/console/game structure

Phase 2 is massive - potentially years of work. Let's define the MVP first.

What's the absolute minimum ADUX needs to be useful?
- Game browsing/search?
- UX voting?
- User accounts?
- What else?

Then we can plan additional features to build over time.

Current thinking:
- Launch with MVP (2-3 months work)
- Get users and feedback
- Iterate based on real usage
- Don't spend years building in isolation

Agree with this approach?
```

## Important Reminders

### For Submission System:
- Queue all sync jobs (don't block requests)
- Validate uploads (file size, format, content)
- Store images on S3 eventually (Phase 3), filesystem for now
- Rate limit submissions (prevent spam)
- Soft delete submissions (moderation)

### For Forums:
- Use existing package or build custom?
- Spam prevention essential (rate limiting, karma thresholds)
- Moderation tools needed from day one
- Consider using Laravel's existing notification system

### For Karma:
- Log every transaction (audit trail)
- Recalculate on schedule (user karma fluctuates)
- Cap karma per action (prevent farming)
- Consider diminishing returns (first 10 reviews = 50pts each, next 10 = 25pts each)

### For Show DB Sync:
- Always background job (never synchronous)
- Handle failures gracefully (retry logic)
- Log sync events (debugging)
- Monitor sync lag (is Main → Show getting out of sync?)

### For Launch:
- Start with manual moderation
- Don't automate everything initially
- Learn what needs automation through real usage
- Better to launch simple than delayed perfect

## Success Criteria for Phase 2 (MVP)

At the end of Phase 2 MVP, you should have:
- [ ] Users can browse/search games (from Show DB)
- [ ] Users can view game details (all data from Show DB)
- [ ] Users can vote on UX metrics
- [ ] Users can submit new games (with approval)
- [ ] Karma system awards points
- [ ] Winner calculation runs on schedule
- [ ] Main → Show DB sync works
- [ ] Basic moderation tools exist
- [ ] Site is fast (Octane optional but recommended)
- [ ] **LAUNCHED** to initial users

**Then iterate.** Add lists, forums, walkthroughs, etc. based on user feedback.

## Testing Before Launch

- [ ] Create 100+ test games
- [ ] Submit 20+ box art options across games
- [ ] Test voting (multiple users with different karma)
- [ ] Verify winners calculate correctly
- [ ] Confirm Main → Show sync works
- [ ] Test new game approval workflow
- [ ] Mobile responsive
- [ ] Page load times acceptable
- [ ] Basic SEO (title tags, descriptions)

---
**Previous Phase:** TASK-1-000 (Architecture)  
**Next Phase:** TASK-3-000 (Scale & Monitor)  
**Phase:** 2 (Feature Development)  
**Approach:** Conversational - prioritize MVP, then iterate  
**Estimated Duration:** 3-6 months for MVP, then ongoing
