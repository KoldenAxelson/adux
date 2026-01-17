# [TASK-3-000] Phase 3 Overview: Scale & Monitor

## Purpose
This is a conversational task to plan and generate all Phase 3 tasks with the user. Phase 3 focuses on migrating from Lightsail to production AWS infrastructure, implementing monitoring and observability, preparing for scale, and monetizing the API.

## Phase 3 Goals
- Migrate from Lightsail to EC2 + RDS
- Implement read/write database separation
- Add comprehensive monitoring (Horizon, Pulse)
- Prepare for horizontal scaling
- Monetize API with paid tiers
- Optimize for high traffic
- Professional deployment workflow

## When to Start Phase 3

**Trigger conditions:**
- MVP is live with real users (not just you two)
- Lightsail instance hitting resource limits (CPU >70%, RAM >80%)
- Traffic growing consistently (100+ daily active users)
- API requests increasing (need better rate limiting/monitoring)
- Making money or have funding to support infrastructure costs

**Don't start too early:** If you're still at <50 users and Lightsail is handling it fine, stay in Phase 2. Premature scaling is expensive and time-consuming.

## What You're Building On

From Phase 2, you should have:
- ✅ Working game review platform with all core features
- ✅ Community features (karma, reviews, moderation)
- ✅ Octane running with response caching
- ✅ Basic API endpoints (read-only)
- ✅ Real users testing the platform

Now you're preparing for SCALE.

## Key Infrastructure Changes

### Database Architecture
**Current (Phase 2):** Single MySQL database on Lightsail

**Phase 3:** Primary + Read Replicas
- Primary DB: All writes (reviews, users, karma updates)
- Read Replica 1: Web traffic (game browsing, search)
- Read Replica 2: API traffic (rate-limited, isolated)

**Why:** Separates concerns, prevents API hammering from slowing down web users.

### Server Architecture
**Current (Phase 2):** Single Lightsail instance

**Phase 3:** Multi-server AWS setup
- EC2 Auto Scaling Group: Web/app servers (2-10 instances)
- RDS: MySQL Primary + Read Replicas
- ElastiCache: Redis for sessions, cache, queues
- S3: Static assets, user uploads
- CloudFront: CDN for images and assets
- ALB: Application Load Balancer (distributes traffic)

**Monthly cost estimate:** $100-300 depending on traffic

### Deployment Strategy
**Current (Phase 2):** Manual SSH + `git pull`

**Phase 3:** Automated CI/CD
- GitHub Actions or Laravel Forge
- Zero-downtime deployments
- Automatic rollbacks on failure
- Staging environment that mirrors production

## Key Decisions to Make

### Laravel Forge vs. Manual
- **Forge ($12/mo):** Click-button deployments, server management GUI
- **Manual:** You manage everything via CLI, more control, more work

### Monitoring Strategy
- **Laravel Horizon:** Queue monitoring (required)
- **Laravel Pulse:** Performance metrics (recommended)
- **External APM?** (New Relic, Datadog - expensive, probably overkill)
- **AWS CloudWatch:** Basic metrics (included)

### API Monetization
**Tier structure:**
- Free: 100 req/hour, attribution required
- Indie: $10/mo, 1,000 req/hour
- Pro: $50/mo, 10,000 req/hour
- Enterprise: Custom pricing

**Payment processing:**
- Stripe for subscriptions
- Laravel Cashier for Stripe integration

### Caching Strategy
**Multi-layer:**
1. **Browser Cache:** Static assets (images, CSS, JS)
2. **CloudFront CDN:** Edge caching
3. **Redis:** Application cache (game data, API responses)
4. **Octane:** In-memory PHP cache
5. **OpCache:** PHP bytecode cache

## Conversation Starters for AI

When a user starts this phase with you, have a conversation to:

1. **Assess Current State**
   - "How many users do you have now?"
   - "What's your current server resource usage?"
   - "Are you experiencing any performance issues?"
   - "Is your Lightsail instance consistently hitting limits?"

2. **Budget Planning**
   - "What's your monthly infrastructure budget?"
   - "Are you generating revenue yet?"
   - "Can you afford ~$150-300/month for AWS?"

3. **Migration Strategy**
   - "Do you want zero-downtime migration or can you have a maintenance window?"
   - "Should we migrate database first or app servers first?"
   - "Do you want to use Laravel Forge or manage manually?"

4. **API Monetization**
   - "Are people using your API?"
   - "What pricing makes sense for your user base?"
   - "Do you want to start with Stripe or wait?"

5. **Monitoring Priorities**
   - "What metrics matter most? (Response times? Error rates? Queue length?)"
   - "Do you need alerting? (Email/SMS when things break?)"
   - "How often do you want to review performance?"

## Suggested Phase 3 Tasks to Generate

After conversation with user, create tasks like:

### Infrastructure Migration
- **TASK-3-001:** AWS Account Setup (IAM roles, billing alerts)
- **TASK-3-002:** RDS MySQL Primary Database Setup
- **TASK-3-003:** RDS Read Replica Configuration
- **TASK-3-004:** EC2 Auto Scaling Group Setup
- **TASK-3-005:** ElastiCache (Redis) Setup
- **TASK-3-006:** S3 + CloudFront Configuration
- **TASK-3-007:** Application Load Balancer Setup
- **TASK-3-008:** Database Migration (Lightsail → RDS)
- **TASK-3-009:** DNS Cutover Strategy

### Monitoring & Observability
- **TASK-3-010:** Laravel Horizon Installation & Configuration
- **TASK-3-011:** Laravel Pulse Installation & Configuration
- **TASK-3-012:** CloudWatch Alarms Setup
- **TASK-3-013:** Error Tracking (Sentry or Flare)
- **TASK-3-014:** Uptime Monitoring (Oh Dear or Pingdom)

### Performance Optimization
- **TASK-3-015:** Read/Write DB Connection Configuration
- **TASK-3-016:** Redis Queue Configuration
- **TASK-3-017:** Asset Optimization Pipeline (S3 + CloudFront)
- **TASK-3-018:** Database Query Optimization Audit
- **TASK-3-019:** Cache Warming Strategies

### API Monetization
- **TASK-3-020:** API Rate Limiting Tiers Implementation
- **TASK-3-021:** Stripe Integration (Laravel Cashier)
- **TASK-3-022:** API Key Management UI
- **TASK-3-023:** Usage Analytics Dashboard
- **TASK-3-024:** API Documentation (Scramble/Scribe)

### Deployment & DevOps
- **TASK-3-025:** CI/CD Pipeline (GitHub Actions)
- **TASK-3-026:** Zero-Downtime Deployment Strategy
- **TASK-3-027:** Staging Environment Setup
- **TASK-3-028:** Automated Backup Strategy
- **TASK-3-029:** Disaster Recovery Plan

### Optional (Laravel Forge Path)
- **TASK-3-030:** Laravel Forge Setup
- **TASK-3-031:** Server Provisioning via Forge
- **TASK-3-032:** Deployment Scripts via Forge

## AI Prompt Template
```
I'm starting Phase 3 of my Laravel game review platform. Phase 2 is complete with:
- Live MVP with [X] active users
- Working features (browse, search, reviews, karma)
- Octane + caching implemented
- Basic API endpoints

Current situation:
- Lightsail instance usage: [CPU%, RAM%]
- Traffic: [daily active users, requests/day]
- Budget: $[amount]/month for infrastructure

I need to migrate to production AWS infrastructure and prepare for scale.

Goals:
1. Migrate from Lightsail to EC2 + RDS
2. Set up read/write database separation
3. Implement monitoring (Horizon, Pulse)
4. Prepare for horizontal scaling
5. Monetize API

Let's start by discussing: Should I use Laravel Forge for server management or handle it manually? What's my migration strategy?
```

## Important Reminders

### For Database Migration:
- **Test extensively** in staging before production cutover
- Use database replication, not dump/restore (minimize downtime)
- Have rollback plan ready
- Schedule during low-traffic period
- Communicate with users (status page)

### For Read/Write Separation:
- Configure in `config/database.php`:
```php
'mysql' => [
    'read' => [
        'host' => env('DB_READ_HOST'),
    ],
    'write' => [
        'host' => env('DB_WRITE_HOST'),
    ],
    // ... other config
],
```
- Use `DB::table()->useWritePdo()` for critical writes
- Monitor replication lag

### For Auto Scaling:
- Start with 2 instances minimum (high availability)
- Scale based on CPU/RAM metrics
- Ensure sessions stored in Redis (not local filesystem)
- Test with one server down (failover)

### For Monitoring:
- Set up alerts BEFORE issues happen
- Monitor: response times, error rates, queue depth, DB connections
- Use Horizon for queue visibility
- Use Pulse for application performance

### For API Monetization:
- Start with generous free tier (build adoption)
- Clearly communicate rate limits
- Provide usage dashboard
- Consider grace period before hard rate limiting

### For Deployment:
- Never deploy on Fridays
- Always deploy to staging first
- Have rollback script ready
- Monitor error rates post-deployment

## Success Criteria for Phase 3

At the end of Phase 3, you should have:
- [ ] All services on AWS (no Lightsail dependencies)
- [ ] Read replica(s) handling most read traffic
- [ ] Auto scaling working (can handle 10x traffic spike)
- [ ] Comprehensive monitoring with alerts
- [ ] Zero-downtime deployments working
- [ ] API monetization live (if applicable)
- [ ] Disaster recovery plan tested
- [ ] Infrastructure costs predictable and sustainable

**You're production-ready** - Can handle significant traffic growth without scrambling.

## Cost Monitoring

Set up billing alerts for:
- $50/month (warning)
- $100/month (review usage)
- $200/month (optimization needed)
- $500/month (serious review required)

Track costs by service:
- EC2: ~$50-150/month (depending on instance count/size)
- RDS: ~$30-100/month (primary + replicas)
- ElastiCache: ~$15-30/month
- S3 + CloudFront: ~$5-20/month
- Data transfer: ~$10-50/month

**Total: $110-350/month** for professional production infrastructure

---
**Previous Phase:** TASK-2-000 (Feature Development)  
**Phase:** 3 (Scale & Monitor)  
**Approach:** Conversational - assess current state, plan based on actual needs  
**Estimated Duration:** 2-4 weeks of focused work  
**Note:** Only start when you actually need to scale
