# AISEO - AI-Powered SEO Plugin for WordPress

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/praisonai/aiseo)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-GPL--2.0-green.svg)](LICENSE)

AI-powered SEO optimization for WordPress. Automatically generate meta descriptions, titles, schema markup, and comprehensive SEO analysis using OpenAI's GPT-4o-mini model.

## 🚀 Features

### ✅ Implemented Features (v1.0.0 - v1.1.0)

#### 1. **AI-Powered Content Generation**
- ✅ **Meta Title Generation** - AI-generated SEO-optimized titles (50-60 characters)
- ✅ **Meta Description Generation** - Compelling descriptions (155-160 characters)
- ✅ **Multiple Suggestions** - Get 3 AI-generated options to choose from
- ✅ **Content Analysis** - AI-powered content quality assessment (11 metrics)
- ✅ **Image SEO & Alt Text** - Automatic AI-powered alt text generation (NEW in v1.1.0)
- ✅ **Advanced SEO Analysis** - Comprehensive 40+ factor analysis (NEW in v1.1.0)
- ✅ **Bulk Editing Interface** - Edit metadata for multiple posts at once (NEW in v1.2.0)
- ✅ **Import/Export Functionality** - Import from Yoast, Rank Math, AIOSEO; Export to JSON/CSV (NEW in v1.2.0)
- ✅ **Multilingual SEO Support** - WPML, Polylang, TranslatePress; hreflang tags (NEW in v1.3.0)
- ✅ **Custom Post Type Support** - Extend SEO to any custom post type; bulk operations (NEW in v1.3.0)
- ✅ **Competitor Analysis** - Analyze competitor websites; compare SEO metrics; get recommendations (NEW in v1.4.0)

#### 2. **OpenAI API Integration**
- ✅ **GPT-4o-mini Model** - Cost-efficient AI model ($0.15/1M input tokens)
- ✅ **Rate Limiting** - 10 requests/minute, 60 requests/hour
- ✅ **Circuit Breaker** - Automatic failure handling (5 failures → 5min cooldown)
- ✅ **Retry Logic** - Exponential backoff (3 retries: 2s, 4s, 8s)
- ✅ **Token Tracking** - Monitor usage and costs
- ✅ **Error Handling** - Graceful degradation on API failures

#### 3. **Security & Encryption**
- ✅ **AES-256-CBC Encryption** - API keys encrypted in database
- ✅ **Environment Variables** - Support for `.env` file configuration
- ✅ **Nonce Verification** - All AJAX requests protected
- ✅ **Capability Checks** - Proper WordPress permission handling
- ✅ **Input Sanitization** - XSS and SQL injection prevention

#### 4. **REST API Endpoints**
All endpoints tested and working! Perfect for:
- Browser testing (curl, Postman)
- Mobile apps
- JavaScript integrations
- External tools and automation
- CI/CD pipelines

**Available Endpoints:**

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/wp-json/aiseo/v1/status` | GET | Plugin status and configuration |
| `/wp-json/aiseo/v1/validate-key` | GET | Validate OpenAI API key |
| `/wp-json/aiseo/v1/generate/title` | POST | Generate SEO title |
| `/wp-json/aiseo/v1/generate/description` | POST | Generate meta description |
| `/wp-json/aiseo/v1/analyze` | POST | **Comprehensive SEO analysis** (11 metrics) |
| `/wp-json/aiseo/v1/schema/{id}` | GET | **Get schema markup** (JSON-LD) |
| `/wp-json/aiseo/v1/meta-tags/{id}` | GET | **Get all meta tags** for a post |
| `/wp-json/aiseo/v1/social-tags/{id}` | GET | **Get social media tags** (OG & Twitter) |
| `/wp-json/aiseo/v1/sitemap/stats` | GET | **Get sitemap statistics** |
| `/wp-json/aiseo/v1/generate/post/{id}` | POST | Generate metadata for post |
| `/wp-json/aiseo/v1/image/generate-alt/{id}` | POST | **Generate alt text for image** (NEW) |
| `/wp-json/aiseo/v1/image/missing-alt` | GET | **Get images missing alt text** (NEW) |
| `/wp-json/aiseo/v1/image/seo-score/{id}` | GET | **Get image SEO score** (NEW) |
| `/wp-json/aiseo/v1/analyze/advanced/{id}` | GET | **Advanced SEO analysis (40+ factors)** (NEW) |
| `/wp-json/aiseo/v1/bulk/posts` | GET | **Get posts for bulk editing** (NEW) |
| `/wp-json/aiseo/v1/bulk/update` | POST | **Bulk update multiple posts** (NEW) |
| `/wp-json/aiseo/v1/bulk/generate` | POST | **Bulk generate metadata with AI** (NEW) |
| `/wp-json/aiseo/v1/bulk/preview` | POST | **Preview bulk changes** (NEW) |
| `/wp-json/aiseo/v1/export/json` | GET | **Export metadata to JSON** (NEW) |
| `/wp-json/aiseo/v1/export/csv` | GET | **Export metadata to CSV** (NEW) |
| `/wp-json/aiseo/v1/import/json` | POST | **Import from JSON** (NEW) |
| `/wp-json/aiseo/v1/import/yoast` | POST | **Import from Yoast SEO** (NEW) |
| `/wp-json/aiseo/v1/import/rankmath` | POST | **Import from Rank Math** (NEW) |
| `/wp-json/aiseo/v1/import/aioseo` | POST | **Import from AIOSEO** (NEW) |
| `/wp-json/aiseo/v1/multilingual/plugin` | GET | **Get active multilingual plugin** (NEW) |
| `/wp-json/aiseo/v1/multilingual/languages` | GET | **Get available languages** (NEW) |
| `/wp-json/aiseo/v1/multilingual/translations/{id}` | GET | **Get post translations** (NEW) |
| `/wp-json/aiseo/v1/multilingual/hreflang/{id}` | GET | **Get hreflang tags** (NEW) |
| `/wp-json/aiseo/v1/multilingual/sync/{id}` | POST | **Sync metadata across translations** (NEW) |
| `/wp-json/aiseo/v1/cpt/list` | GET | **List all custom post types** (NEW) |
| `/wp-json/aiseo/v1/cpt/supported` | GET | **Get supported post types** (NEW) |
| `/wp-json/aiseo/v1/cpt/enable` | POST | **Enable SEO for post type** (NEW) |
| `/wp-json/aiseo/v1/cpt/disable` | POST | **Disable SEO for post type** (NEW) |
| `/wp-json/aiseo/v1/cpt/posts/{post_type}` | GET | **Get posts by type** (NEW) |
| `/wp-json/aiseo/v1/cpt/stats/{post_type}` | GET | **Get post type statistics** (NEW) |
| `/wp-json/aiseo/v1/cpt/bulk-generate` | POST | **Bulk generate metadata** (NEW) |
| `/wp-json/aiseo/v1/competitor/list` | GET | **List all competitors** (NEW) |
| `/wp-json/aiseo/v1/competitor/add` | POST | **Add competitor** (NEW) |
| `/wp-json/aiseo/v1/competitor/remove/{id}` | DELETE | **Remove competitor** (NEW) |
| `/wp-json/aiseo/v1/competitor/analyze/{id}` | POST | **Analyze competitor** (NEW) |
| `/wp-json/aiseo/v1/competitor/analysis/{id}` | GET | **Get competitor analysis** (NEW) |
| `/wp-json/aiseo/v1/competitor/compare/{id}` | GET | **Compare with site** (NEW) |
| `/wp-json/aiseo/v1/competitor/summary` | GET | **Get summary** (NEW) |

**Example Usage:**
```bash
# Check plugin status
curl https://yoursite.test/wp-json/aiseo/v1/status

# Generate SEO title
curl -X POST https://yoursite.test/wp-json/aiseo/v1/generate/title \
  -H "Content-Type: application/json" \
  -d '{"content": "Your post content", "keyword": "wordpress seo"}'

# Analyze post SEO (11 metrics)
curl -X POST https://yoursite.test/wp-json/aiseo/v1/analyze \
  -H "Content-Type: application/json" \
  -d '{"post_id": 123}'

# Get schema markup for post
curl https://yoursite.test/wp-json/aiseo/v1/schema/123

# Get images missing alt text (NEW)
curl https://yoursite.test/wp-json/aiseo/v1/image/missing-alt

# Generate alt text for image (NEW)
curl -X POST https://yoursite.test/wp-json/aiseo/v1/image/generate-alt/456

# Get image SEO score (NEW)
curl https://yoursite.test/wp-json/aiseo/v1/image/seo-score/456

# Advanced SEO analysis - 40+ factors (NEW)
curl https://yoursite.test/wp-json/aiseo/v1/analyze/advanced/123
curl https://yoursite.test/wp-json/aiseo/v1/analyze/advanced/123?keyword="wordpress seo"

# Bulk Editing (NEW)
# Get posts for bulk editing
curl "https://yoursite.test/wp-json/aiseo/v1/bulk/posts?post_type=post&limit=10"

# Bulk update multiple posts
curl -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/update \
  -H "Content-Type: application/json" \
  -d '{"updates": [{"post_id": 123, "focus_keyword": "wordpress seo"}]}'

# Bulk generate metadata with AI
curl -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/generate \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [123, 456], "meta_types": ["title", "description"]}'

# Preview changes
curl -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/preview \
  -H "Content-Type: application/json" \
  -d '{"updates": [{"post_id": 123, "meta_title": "New Title"}]}'

# Import/Export (NEW)
# Export to JSON
curl "https://yoursite.test/wp-json/aiseo/v1/export/json?post_type=post"

# Export to CSV
curl "https://yoursite.test/wp-json/aiseo/v1/export/csv?post_type=post" > export.csv

# Import from JSON
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/json \
  -H "Content-Type: application/json" \
  -d @export.json

# Import from Yoast SEO
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/yoast \
  -H "Content-Type: application/json" \
  -d '{"post_type": "post", "overwrite": false}'

# Import from Rank Math
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/rankmath \
  -H "Content-Type: application/json" \
  -d '{"post_type": "post", "overwrite": false}'

# Import from AIOSEO
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/aioseo \
  -H "Content-Type: application/json" \
  -d '{"post_type": "post", "overwrite": false}'
```

#### 5. **WP-CLI Commands**
Comprehensive command-line interface for automation and batch processing.

**Available Commands:**

| Command | Description |
|---------|-------------|
| `wp aiseo generate` | Generate SEO metadata (by slug, ID, or --all) |
| `wp aiseo analyze` | Analyze SEO scores with multiple output formats |
| `wp aiseo meta` | Get/update/delete specific metadata fields |
| `wp aiseo cache` | Clear AISEO caches |
| `wp aiseo export` | Export metadata to JSON |
| `wp aiseo image generate-alt` | **Generate alt text for image** (NEW) |
| `wp aiseo image bulk-generate` | **Bulk generate alt text** (NEW) |
| `wp aiseo image detect-missing` | **Find images without alt text** (NEW) |
| `wp aiseo image analyze` | **Analyze image SEO for post** (NEW) |
| `wp aiseo advanced analyze` | **Advanced SEO analysis (40+ factors)** (NEW) |
| `wp aiseo advanced bulk` | **Bulk advanced analysis** (NEW) |
| `wp aiseo bulk list` | **List posts for bulk editing** (NEW) |
| `wp aiseo bulk update` | **Bulk update metadata** (NEW) |
| `wp aiseo bulk generate` | **Bulk generate metadata with AI** (NEW) |
| `wp aiseo bulk preview` | **Preview bulk changes** (NEW) |
| `wp aiseo export json` | **Export to JSON** (NEW) |
| `wp aiseo export csv` | **Export to CSV** (NEW) |
| `wp aiseo import json` | **Import from JSON file** (NEW) |
| `wp aiseo import yoast` | **Import from Yoast SEO** (NEW) |
| `wp aiseo import rankmath` | **Import from Rank Math** (NEW) |
| `wp aiseo import aioseo` | **Import from AIOSEO** (NEW) |
| `wp aiseo multilingual plugin` | **Get active multilingual plugin** (NEW) |
| `wp aiseo multilingual languages` | **List available languages** (NEW) |
| `wp aiseo multilingual translations` | **Get post translations** (NEW) |
| `wp aiseo multilingual hreflang` | **Get hreflang tags** (NEW) |
| `wp aiseo multilingual sync` | **Sync metadata across translations** (NEW) |
| `wp aiseo multilingual bulk-sync` | **Bulk sync multiple posts** (NEW) |
| `wp aiseo cpt list` | **List all custom post types** (NEW) |
| `wp aiseo cpt supported` | **List supported post types** (NEW) |
| `wp aiseo cpt enable` | **Enable SEO for post type** (NEW) |
| `wp aiseo cpt disable` | **Disable SEO for post type** (NEW) |
| `wp aiseo cpt posts` | **Get posts by type** (NEW) |
| `wp aiseo cpt stats` | **Get post type statistics** (NEW) |
| `wp aiseo cpt bulk-generate` | **Bulk generate metadata** (NEW) |
| `wp aiseo cpt export` | **Export post type data** (NEW) |
| `wp aiseo competitor list` | **List all competitors** (NEW) |
| `wp aiseo competitor add` | **Add competitor** (NEW) |
| `wp aiseo competitor remove` | **Remove competitor** (NEW) |
| `wp aiseo competitor analyze` | **Analyze competitor** (NEW) |
| `wp aiseo competitor get` | **Get competitor analysis** (NEW) |
| `wp aiseo competitor compare` | **Compare with site** (NEW) |
| `wp aiseo competitor summary` | **Get summary** (NEW) |
| `wp aiseo competitor bulk-analyze` | **Bulk analyze all** (NEW) |

**Example Usage:**
```bash
# Generate metadata for a post
wp aiseo generate --id=123 --meta=title,description

# Analyze all posts and export to CSV
wp aiseo analyze --all --format=csv > seo-report.csv

# Get meta title
wp aiseo meta get 123 meta_title

# Update focus keyword
wp aiseo meta update 123 focus_keyword "wordpress seo"

# Export all metadata
wp aiseo export --all --file=backup.json

# Image SEO Commands (NEW in v1.1.0)
# Generate alt text for single image
wp aiseo image generate-alt 456

# Bulk generate alt text for images missing it
wp aiseo image bulk-generate --missing-only --limit=50

# Detect all images without alt text
wp aiseo image detect-missing --format=table

# Analyze image SEO for a post
wp aiseo image analyze 123 --format=json

# Dry run (preview without changes)
wp aiseo image bulk-generate --all --dry-run

# Advanced SEO Analysis Commands (NEW in v1.1.0)
# Analyze post with 40+ SEO factors
wp aiseo advanced analyze 123

# Analyze with custom keyword
wp aiseo advanced analyze 123 --keyword="wordpress seo"

# Get JSON output
wp aiseo advanced analyze 123 --format=json

# Get summary view
wp aiseo advanced analyze 123 --format=summary

# Bulk analyze posts with scores below 70%
wp aiseo advanced bulk --min-score=70 --limit=20

# Export to CSV
wp aiseo advanced bulk --min-score=80 --format=csv > seo-report.csv

# Bulk Editing Commands (NEW in v1.2.0)
# List posts for bulk editing
wp aiseo bulk list --limit=20 --format=table

# List only post IDs
wp aiseo bulk list --format=ids

# Bulk update focus keyword
wp aiseo bulk update 123,456,789 --focus-keyword="wordpress seo"

# Bulk update multiple fields
wp aiseo bulk update 123,456 --meta-title="SEO Title" --robots-index=noindex

# Bulk generate metadata for specific posts
wp aiseo bulk generate 123,456,789 --meta-types=title,description

# Bulk generate for all posts (with limit)
wp aiseo bulk generate --all --limit=10 --overwrite

# Preview changes before applying
wp aiseo bulk preview 123,456 --focus-keyword="new keyword"

# Advanced: Export IDs and pipe to bulk generate
wp aiseo bulk list --format=ids | xargs wp aiseo bulk generate

# Import/Export Commands (NEW in v1.2.0)
# Export to JSON
wp aiseo export json --output=aiseo-export.json

# Export to CSV
wp aiseo export csv --output=aiseo-export.csv

# Export specific post type
wp aiseo export json --post-type=page --output=pages-export.json

# Import from JSON file
wp aiseo import json aiseo-export.json

# Import with overwrite
wp aiseo import json aiseo-export.json --overwrite

# Import from Yoast SEO
wp aiseo import yoast

# Import from Yoast with overwrite
wp aiseo import yoast --post-type=post --overwrite

# Import from Rank Math
wp aiseo import rankmath --limit=100

# Import from AIOSEO
wp aiseo import aioseo --post-type=page
```

#### 6. **Performance Optimization**
- ✅ **Caching System** - Transient cache with 24-hour TTL
- ✅ **Object Cache Support** - Redis/Memcached compatible
- ✅ **Conditional Loading** - Scripts only on relevant pages
- ✅ **Database Optimization** - Indexed queries and batch updates
- ✅ **Async Processing** - WP-Cron for background tasks

#### 7. **Logging & Monitoring**
- ✅ **Structured Logging** - JSON format with context
- ✅ **Log Levels** - DEBUG, INFO, WARNING, ERROR, CRITICAL
- ✅ **Usage Statistics** - Track API calls, tokens, response times
- ✅ **Failed Request Queue** - Automatic retry for failed requests
- ✅ **Performance Metrics** - Response time, success rate, cache hits

#### 8. **Database Tables**
Custom tables for enhanced functionality:
- `wp_aiseo_logs` - Structured logging with trace IDs
- `wp_aiseo_failed_requests` - Failed API request queue
- `wp_aiseo_usage_stats` - Daily usage statistics
- `wp_aiseo_request_queue` - Async request processing

#### 9. **Content Analysis Engine** ✅
- ✅ **Keyword density analysis** (0.5% - 2.5% optimal)
- ✅ **Readability score** (Flesch-Kincaid with reading level)
- ✅ **Paragraph structure analysis** (max 150 words per paragraph)
- ✅ **Sentence length analysis** (max 20 words per sentence)
- ✅ **Keyword in title check**
- ✅ **Keyword in headings check** (H2/H3)
- ✅ **Keyword in introduction check** (first 150 words)
- ✅ **Content length analysis** (min 300 words recommended)
- ✅ **Internal links analysis** (2-5 optimal)
- ✅ **External links analysis** (1-3 optimal)
- ✅ **Image alt text coverage**
- ✅ **Overall SEO score** (0-100 with weighted metrics)
- ✅ **Status indicators** (good/ok/poor)
- ✅ **Actionable recommendations** for each metric

**Test the Content Analysis Engine:**

```bash
# REST API - Analyze a post
curl -X POST https://yoursite.test/wp-json/aiseo/v1/analyze \
  -H "Content-Type: application/json" \
  -d '{"post_id": 123}'

# Response includes:
# - overall_score: 0-100
# - status: good/ok/poor
# - analyses: 11 detailed metrics with scores and recommendations

# WP-CLI - Analyze single post
wp aiseo analyze --id=123

# WP-CLI - Analyze all posts with table output
wp aiseo analyze --all

# WP-CLI - Export analysis to CSV
wp aiseo analyze --all --format=csv > seo-audit.csv

# WP-CLI - Get JSON output for automation
wp aiseo analyze --id=123 --format=json
```

#### 10. **Schema Markup Generator** ✅
- ✅ **Article schema** (JSON-LD for news articles)
- ✅ **BlogPosting schema** (JSON-LD for blog posts)
- ✅ **WebPage schema** (JSON-LD for pages)
- ✅ **Organization schema** (publisher information)
- ✅ **Person/Author schema** (author information)
- ✅ **BreadcrumbList schema** (navigation breadcrumbs)
- ✅ **FAQ schema** (frequently asked questions)
- ✅ **HowTo schema** (step-by-step guides)
- ✅ **Auto-detection** of appropriate schema type
- ✅ **Featured image** integration
- ✅ **Word count** and metadata
- ✅ **Category/section** mapping

**Test the Schema Markup Generator:**

```bash
# REST API - Get schema for a post (auto-detect type)
curl https://yoursite.test/wp-json/aiseo/v1/schema/123

# REST API - Get specific schema type
curl https://yoursite.test/wp-json/aiseo/v1/schema/123?type=article

# Response includes:
# - main: Article/BlogPosting/WebPage schema
# - breadcrumb: BreadcrumbList schema
# - publisher: Organization schema

# WP-CLI - Generate and save schema
wp aiseo generate --id=123 --meta=schema

# WP-CLI - View generated schema
wp post meta get 123 _aiseo_schema

# Test schema in Google's Rich Results Test
# Copy the JSON-LD output and paste into:
# https://search.google.com/test/rich-results
```

**Schema Types Generated:**

| Post Type | Word Count | Schema Type |
|-----------|------------|-------------|
| Post | > 1000 words | Article |
| Post | < 1000 words | BlogPosting |
| Page | Any | WebPage |

**Example Schema Output:**

```json
{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "WordPress SEO Best Practices",
  "description": "Learn how to optimize your WordPress site...",
  "url": "https://yoursite.com/wordpress-seo-best-practices",
  "datePublished": "2024-11-03T07:00:00+00:00",
  "dateModified": "2024-11-03T07:30:00+00:00",
  "author": {
    "@type": "Person",
    "name": "John Doe",
    "url": "https://yoursite.com/author/john-doe"
  },
  "publisher": {
    "@type": "Organization",
    "name": "Your Site Name",
    "url": "https://yoursite.com",
    "logo": {
      "@type": "ImageObject",
      "url": "https://yoursite.com/logo.png"
    }
  },
  "image": {
    "@type": "ImageObject",
    "url": "https://yoursite.com/featured-image.jpg",
    "width": 1200,
    "height": 630
  },
  "wordCount": 850
}
```

#### 11. **Meta Tags Management** ✅
- ✅ **Inject meta tags in `<head>`** - Automatic injection on page load
- ✅ **Custom title tag** - Override WordPress default title
- ✅ **Meta description** - SEO-optimized descriptions
- ✅ **Canonical URL** - Prevent duplicate content issues
- ✅ **Robots meta tags** - index/noindex, follow/nofollow
- ✅ **Additional directives** - noarchive, nosnippet
- ✅ **Author meta tag** - Article author information
- ✅ **Published/Modified time** - Article timestamps
- ✅ **Keywords meta tag** - SEO keywords (optional)
- ✅ **News keywords** - Focus keyword as news_keywords

**Test the Meta Tags Handler:**

```bash
# REST API - Get all meta tags for a post
curl https://yoursite.test/wp-json/aiseo/v1/meta-tags/123

# Response includes:
{
  "success": true,
  "post_id": 123,
  "meta_tags": {
    "title": "SEO Optimized Title - Site Name",
    "description": "SEO optimized meta description...",
    "canonical": "https://yoursite.com/post-slug",
    "robots": "index, follow",
    "keywords": "wordpress, seo, optimization",
    "focus_keyword": "wordpress seo",
    "author": "John Doe",
    "published_time": "2024-11-03T07:00:00+00:00",
    "modified_time": "2024-11-03T07:30:00+00:00"
  }
}

# WP-CLI - Set robots directives
wp post meta update 123 _aiseo_noindex 0
wp post meta update 123 _aiseo_nofollow 0

# WP-CLI - Set custom canonical URL
wp post meta update 123 _aiseo_canonical_url "https://yoursite.com/custom-url"

# View page source to verify meta tags
curl https://yoursite.test/post-slug/ | grep -A 5 '<head>'

# Check specific meta tags
curl -s https://yoursite.test/post-slug/ | grep 'meta name="description"'
curl -s https://yoursite.test/post-slug/ | grep 'meta name="robots"'
curl -s https://yoursite.test/post-slug/ | grep 'link rel="canonical"'
```

**Meta Tags Injected in `<head>`:**

```html
<head>
  <title>WordPress SEO Best: Ultimate Guide to Boost Your Rankings</title>
  <meta name="description" content="Unlock the secrets to WordPress SEO...">
  <link rel="canonical" href="https://yoursite.com/wordpress-seo-best-practices">
  <meta name="robots" content="index, follow">
  <meta name="author" content="John Doe">
  <meta property="article:published_time" content="2024-11-03T07:00:00+00:00">
  <meta property="article:modified_time" content="2024-11-03T07:30:00+00:00">
  <meta name="keywords" content="wordpress, seo, optimization">
  <meta name="news_keywords" content="wordpress seo">
</head>
```

**Robots Directives:**

| Meta Field | Values | Description |
|------------|--------|-------------|
| `_aiseo_noindex` | 0/1 | Prevent search engine indexing |
| `_aiseo_nofollow` | 0/1 | Prevent following links |
| `_aiseo_noarchive` | 0/1 | Prevent cached copies |
| `_aiseo_nosnippet` | 0/1 | Prevent search result snippets |

#### 12. **Social Media Optimization** ✅
- ✅ **Open Graph tags** - Facebook, LinkedIn, WhatsApp sharing
  - og:type, og:title, og:description, og:url, og:site_name
  - og:image (with width, height, alt)
  - og:locale
  - article:published_time, article:modified_time
  - article:author, article:section, article:tag
- ✅ **Twitter Card tags** - Beautiful Twitter previews
  - twitter:card (summary / summary_large_image)
  - twitter:title, twitter:description
  - twitter:image (with alt text)
  - twitter:site, twitter:creator
- ✅ **Auto-detection** - Automatic card type based on content
- ✅ **Custom overrides** - Per-post custom social tags
- ✅ **Fallback system** - SEO meta → excerpt → content
- ✅ **Image optimization** - Featured image → content image → default

**Test the Social Media Tags:**

```bash
# REST API - Get all social media tags
curl https://yoursite.test/wp-json/aiseo/v1/social-tags/123

# Response includes:
{
  "success": true,
  "post_id": 123,
  "social_tags": {
    "open_graph": {
      "og:type": "article",
      "og:title": "WordPress SEO Best Practices",
      "og:description": "Learn how to optimize your WordPress site...",
      "og:url": "https://yoursite.com/wordpress-seo-best-practices",
      "og:site_name": "Your Site Name",
      "og:image": "https://yoursite.com/featured-image.jpg",
      "og:locale": "en_US"
    },
    "twitter": {
      "twitter:card": "summary_large_image",
      "twitter:title": "WordPress SEO Best Practices",
      "twitter:description": "Learn how to optimize your WordPress site...",
      "twitter:image": "https://yoursite.com/featured-image.jpg",
      "twitter:site": "@yoursite"
    }
  }
}

# Verify Open Graph tags in page HTML
curl -s https://yoursite.test/post-slug/ | grep 'property="og:'

# Verify Twitter Card tags in page HTML
curl -s https://yoursite.test/post-slug/ | grep 'name="twitter:'

# WP-CLI - Set custom Open Graph title
wp post meta update 123 _aiseo_og_title "Custom OG Title"

# WP-CLI - Set custom Twitter card type
wp post meta update 123 _aiseo_twitter_card_type "summary_large_image"

# WP-CLI - Set site Twitter handle
wp option update aiseo_twitter_site "@yoursite"

# Test with social media validators
# Facebook Sharing Debugger: https://developers.facebook.com/tools/debug/
# Twitter Card Validator: https://cards-dev.twitter.com/validator
# LinkedIn Post Inspector: https://www.linkedin.com/post-inspector/
```

**Social Media Tags Injected in `<head>`:**

```html
<!-- Open Graph Meta Tags -->
<meta property="og:type" content="article">
<meta property="og:title" content="WordPress SEO Best: Ultimate Guide">
<meta property="og:description" content="Unlock the secrets to WordPress SEO...">
<meta property="og:url" content="https://yoursite.com/wordpress-seo-best-practices">
<meta property="og:site_name" content="Your Site Name">
<meta property="og:image" content="https://yoursite.com/featured-image.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="WordPress SEO Guide">
<meta property="og:locale" content="en_US">
<meta property="article:published_time" content="2024-11-03T07:00:00+00:00">
<meta property="article:modified_time" content="2024-11-03T07:30:00+00:00">
<meta property="article:author" content="John Doe">
<meta property="article:section" content="SEO">
<meta property="article:tag" content="wordpress">
<meta property="article:tag" content="seo">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="WordPress SEO Best: Ultimate Guide">
<meta name="twitter:description" content="Unlock the secrets to WordPress SEO...">
<meta name="twitter:image" content="https://yoursite.com/featured-image.jpg">
<meta name="twitter:image:alt" content="WordPress SEO Guide">
<meta name="twitter:site" content="@yoursite">
<meta name="twitter:creator" content="@johndoe">
```

**Custom Social Media Fields:**

| Meta Field | Description |
|------------|-------------|
| `_aiseo_og_type` | Custom Open Graph type (article/website) |
| `_aiseo_og_title` | Custom Open Graph title |
| `_aiseo_og_description` | Custom Open Graph description |
| `_aiseo_og_image` | Custom Open Graph image ID |
| `_aiseo_twitter_card_type` | Twitter card type (summary/summary_large_image) |
| `_aiseo_twitter_title` | Custom Twitter title |
| `_aiseo_twitter_description` | Custom Twitter description |
| `_aiseo_twitter_image` | Custom Twitter image ID |

**Recommended Image Sizes:**

| Platform | Recommended Size | Aspect Ratio |
|----------|------------------|-------------|
| Open Graph | 1200 x 630 px | 1.91:1 |
| Twitter Summary | 120 x 120 px | 1:1 |
| Twitter Large Image | 1200 x 628 px | 1.91:1 |

#### 13. **XML Sitemap Generator** ✅
- ✅ **Automatic sitemap.xml generation** - Dynamic XML sitemap
- ✅ **Sitemap index** - `/sitemap.xml` with post type sitemaps
- ✅ **Post type sitemaps** - `/sitemap-post.xml`, `/sitemap-page.xml`
- ✅ **Smart caching** - 12-hour cache with auto-invalidation
- ✅ **Priority calculation** - Homepage (1.0), Pages (0.8), Posts (0.6)
- ✅ **Change frequency** - Auto-calculated based on post age
- ✅ **Image sitemap** - Featured images + content images
- ✅ **Noindex exclusion** - Respects `_aiseo_noindex` meta
- ✅ **robots.txt integration** - Automatic sitemap reference
- ✅ **Search engine ping** - Google & Bing notification

**Test the XML Sitemap Generator:**

```bash
# REST API - Get sitemap statistics
curl https://yoursite.test/wp-json/aiseo/v1/sitemap/stats

# Response: 107 total URLs across post types

# IMPORTANT: Flush rewrite rules first!
wp rewrite flush

# Access sitemap index
curl https://yoursite.test/sitemap.xml

# Access post type sitemaps
curl https://yoursite.test/sitemap-post.xml
curl https://yoursite.test/sitemap-page.xml

# Check robots.txt includes sitemap
curl https://yoursite.test/robots.txt | grep Sitemap

# WP-CLI - Set custom priority
wp post meta update 123 _aiseo_sitemap_priority "0.9"

# WP-CLI - Set custom changefreq
wp post meta update 123 _aiseo_sitemap_changefreq "weekly"

# Validate with Google Search Console
# https://search.google.com/search-console
```

**Priority & Change Frequency:**

| Content | Priority | Changefreq (age-based) |
|---------|----------|------------------------|
| Homepage | 1.0 | weekly |
| Pages | 0.8 | monthly |
| Posts | 0.6 | <7d: daily, <30d: weekly, <365d: monthly, >365d: yearly |

#### 14. **Admin Interface** ✅
- ✅ **Settings Page** - WordPress Admin → AISEO → Settings
  - OpenAI API key configuration
  - AI model selection (GPT-4o-mini recommended)
  - Twitter site handle
  - Sitemap post types selection
- ✅ **Statistics Page** - WordPress Admin → AISEO → Statistics
  - Total API requests
  - Failed requests tracking
  - Token usage stats
  - Sitemap breakdown by post type
- ✅ **Dashboard Widget** - Quick 7-day stats on WP Dashboard
- ✅ **Post Editor Metabox** - SEO controls in post/page editor
  - Real-time SEO score (0-100) with color coding
  - Focus keyword input
  - SEO title with character counter (50-60 chars)
  - Meta description with character counter (150-160 chars)
  - AI-powered generation buttons
  - Advanced settings (canonical URL, robots meta)
  - Content analysis button

**Access Admin Interface:**
- Settings: `/wp-admin/admin.php?page=aiseo-settings`
- Statistics: `/wp-admin/admin.php?page=aiseo-stats`
- Metabox: Edit any post/page → "AISEO - AI SEO Optimization" box

### 🚧 In Progress / Coming Soon

#### 15. **Advanced Features**
- ⏳ Permalink optimization (remove stop words)
- ⏳ Internal linking suggestions
- ⏳ Image SEO optimization
- ⏳ Reading time calculation
- ⏳ Content suggestions
- ⏳ Competitor analysis

## 📦 Installation

### Requirements
- WordPress 5.0 or higher
- PHP 7.4 or higher
- OpenAI API key ([Get one here](https://platform.openai.com/api-keys))

### Quick Install

1. **Download the plugin**
   ```bash
   cd wp-content/plugins
   git clone https://github.com/praisonai/aiseo.git
   ```

2. **Activate the plugin**
   ```bash
   wp plugin activate aiseo
   ```

3. **Configure API key**
   
   Option A: Using `.env` file (recommended for development)
   ```bash
   cd wp-content/plugins/aiseo
   echo "OPENAI_API_KEY=your-api-key-here" > .env
   ```
   
   Option B: Using WP-CLI
   ```bash
   wp eval 'AISEO_Helpers::save_api_key("your-api-key-here");'
   ```
   
   Option C: Via WordPress admin (when admin interface is ready)
   - Go to Settings → AISEO
   - Enter your OpenAI API key
   - Click Save

### Development Setup (Valet)

1. **Find your Valet site**
   ```bash
   valet links
   ```

2. **Symlink the plugin**
   ```bash
   cd ~/Sites/yoursite
   ln -s /path/to/aiseo wp-content/plugins/aiseo
   ```

3. **Activate and test**
   ```bash
   wp plugin activate aiseo
   wp aiseo --help
   curl https://yoursite.test/wp-json/aiseo/v1/status
   ```

## 🧪 Testing

### REST API Testing

**Quick Status Check:**
```bash
curl https://yoursite.test/wp-json/aiseo/v1/status
```

**Generate SEO Title:**
```bash
curl -X POST https://yoursite.test/wp-json/aiseo/v1/generate/title \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your post content here",
    "keyword": "wordpress seo"
  }'
```

**Validate API Key:**
```bash
curl https://yoursite.test/wp-json/aiseo/v1/validate-key
```

### WP-CLI Testing

**Generate Metadata:**
```bash
# Single post
wp aiseo generate --id=123

# Multiple posts
wp aiseo generate --id=1,2,3 --meta=title,description

# All posts
wp aiseo generate --all --post-type=post

# Dry run (preview without saving)
wp aiseo generate --id=123 --dry-run
```

**Analyze SEO:**
```bash
# Single post
wp aiseo analyze --id=123

# All posts with JSON output
wp aiseo analyze --all --format=json

# Export to CSV
wp aiseo analyze --all --format=csv > seo-scores.csv
```

**Manage Metadata:**
```bash
# Get metadata
wp aiseo meta get 123 meta_title

# Update metadata
wp aiseo meta update 123 meta_title "New SEO Title"

# Delete metadata
wp aiseo meta delete 123 meta_description
```

For comprehensive testing documentation, see [UNINSTALL-AND-TESTING.md](UNINSTALL-AND-TESTING.md).

## 📊 Usage Statistics

Track your API usage and costs:

```bash
# View usage statistics
wp option get aiseo_token_usage_month
wp option get aiseo_token_usage_total

# Export usage data
wp db query "SELECT * FROM wp_aiseo_usage_stats ORDER BY date DESC LIMIT 30"
```

**Cost Estimation:**
- GPT-4o-mini: $0.15 per 1M input tokens, $0.60 per 1M output tokens
- Average meta description: ~100 tokens = $0.00006
- Average SEO title: ~50 tokens = $0.00003

## 🔧 Configuration

### Plugin Options

| Option | Default | Description |
|--------|---------|-------------|
| `aiseo_api_model` | `gpt-4o-mini` | OpenAI model to use |
| `aiseo_api_timeout` | `45` | API timeout in seconds |
| `aiseo_api_max_tokens` | `1000` | Max tokens per request |
| `aiseo_api_temperature` | `0.7` | Temperature (0.0-1.0) |
| `aiseo_rate_limit_per_minute` | `10` | Requests per minute |
| `aiseo_rate_limit_per_hour` | `60` | Requests per hour |
| `aiseo_monthly_token_limit` | `0` | Monthly token limit (0 = unlimited) |

### Update Configuration

```bash
# Change API model
wp option update aiseo_api_model "gpt-4o"

# Adjust rate limits
wp option update aiseo_rate_limit_per_minute 20
wp option update aiseo_rate_limit_per_hour 100

# Set monthly token limit
wp option update aiseo_monthly_token_limit 1000000
```

## 🐛 Debugging

### Enable Debug Mode

Add to `wp-config.php`:
```php
define('AISEO_DEBUG', true);
```

This enables:
- Verbose logging of all API requests/responses
- Performance profiling
- Cache operation logging
- Detailed error messages

### View Logs

```bash
# View recent logs
wp db query "SELECT * FROM wp_aiseo_logs ORDER BY timestamp DESC LIMIT 50"

# View errors only
wp db query "SELECT * FROM wp_aiseo_logs WHERE level='ERROR' ORDER BY timestamp DESC LIMIT 20"

# Export logs to CSV
wp db query "SELECT * FROM wp_aiseo_logs" --format=csv > aiseo-logs.csv
```

### Common Issues

**Issue: API key not working**
```bash
# Validate API key
wp eval 'echo AISEO_Helpers::get_api_key() . "\n";'

# Test API connection
curl https://yoursite.test/wp-json/aiseo/v1/validate-key
```

**Issue: Rate limit errors**
```bash
# Check current usage
wp transient get aiseo_user_requests_1

# Increase rate limits
wp option update aiseo_rate_limit_per_minute 20
```

**Issue: Slow performance**
```bash
# Clear all caches
wp aiseo cache clear --all

# Check database indexes
wp db query "SHOW INDEX FROM wp_aiseo_logs"
```

## 🧪 Testing Guide

### Quick Testing Methods

AISEO provides **three ways to test** every feature:

#### 1. **REST API Testing** (Easiest - Use curl or browser)

```bash
# Test 1: Check plugin status
curl https://yoursite.test/wp-json/aiseo/v1/status

# Test 2: Analyze post SEO (11 metrics)
curl -X POST https://yoursite.test/wp-json/aiseo/v1/analyze \
  -H "Content-Type: application/json" \
  -d '{"post_id": 123}'

# Test 3: Get schema markup
curl https://yoursite.test/wp-json/aiseo/v1/schema/123

# Test 4: Get meta tags
curl https://yoursite.test/wp-json/aiseo/v1/meta-tags/123

# Test 5: Get social media tags
curl https://yoursite.test/wp-json/aiseo/v1/social-tags/123

# Test 6: Get sitemap stats
curl https://yoursite.test/wp-json/aiseo/v1/sitemap/stats

# Image SEO Tests (NEW)
# Test 7: Get images missing alt text
curl https://yoursite.test/wp-json/aiseo/v1/image/missing-alt

# Test 8: Generate alt text for image
curl -X POST https://yoursite.test/wp-json/aiseo/v1/image/generate-alt/456

# Test 9: Get image SEO score
curl https://yoursite.test/wp-json/aiseo/v1/image/seo-score/456

# Advanced SEO Analysis Tests (NEW)
# Test 10: Comprehensive analysis (40+ factors)
curl https://yoursite.test/wp-json/aiseo/v1/analyze/advanced/123

# Test 11: Analysis with custom keyword
curl https://yoursite.test/wp-json/aiseo/v1/analyze/advanced/123?keyword="wordpress seo"

# Bulk Editing Tests (NEW)
# Test 12: Get posts for bulk editing
curl "https://yoursite.test/wp-json/aiseo/v1/bulk/posts?post_type=post&limit=10"

# Test 13: Bulk update posts
curl -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/update \
  -H "Content-Type: application/json" \
  -d '{"updates": [{"post_id": 123, "focus_keyword": "wordpress seo"}]}'

# Test 14: Bulk generate metadata
curl -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/generate \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [123, 456], "meta_types": ["title", "description"]}'

# Test 15: Preview bulk changes
curl -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/preview \
  -H "Content-Type: application/json" \
  -d '{"updates": [{"post_id": 123, "meta_title": "New Title"}]}'

# Import/Export Tests (NEW)
# Test 16: Export to JSON
curl "https://yoursite.test/wp-json/aiseo/v1/export/json?post_type=post" > export.json

# Test 17: Export to CSV
curl "https://yoursite.test/wp-json/aiseo/v1/export/csv?post_type=post" > export.csv

# Test 18: Import from JSON
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/json \
  -H "Content-Type: application/json" \
  -d @export.json

# Test 19: Import from Yoast SEO
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/yoast \
  -H "Content-Type: application/json" \
  -d '{"post_type": "post", "overwrite": false}'

# Test 20: Import from Rank Math
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/rankmath \
  -H "Content-Type: application/json" \
  -d '{"post_type": "post", "overwrite": true}'

# Test 21: Import from AIOSEO
curl -X POST https://yoursite.test/wp-json/aiseo/v1/import/aioseo \
  -H "Content-Type: application/json" \
  -d '{"post_type": "page"}'
```

#### 2. **WP-CLI Testing** (Best for automation)

```bash
# Test 1: Generate metadata for post
wp aiseo generate --id=123 --meta=all

# Test 2: Analyze post SEO
wp aiseo analyze --id=123 --format=json

# Test 3: Get meta title
wp aiseo meta get 123 meta_title

# Test 4: Update focus keyword
wp aiseo meta update 123 focus_keyword "wordpress seo"

# Test 5: Export all metadata
wp aiseo export --all --file=backup.json

# Image SEO Tests (NEW)
# Test 6: Generate alt text for image
wp aiseo image generate-alt 456

# Test 7: Detect images missing alt text
wp aiseo image detect-missing --format=table

# Test 8: Bulk generate alt text
wp aiseo image bulk-generate --missing-only --limit=10

# Test 9: Analyze image SEO for post
wp aiseo image analyze 123 --format=json

# Test 10: Dry run (preview without changes)
wp aiseo image bulk-generate --all --dry-run

# Advanced SEO Analysis Tests (NEW)
# Test 11: Comprehensive analysis (40+ factors)
wp aiseo advanced analyze 123

# Test 12: Analysis with custom keyword
wp aiseo advanced analyze 123 --keyword="wordpress seo"

# Test 13: Get summary view
wp aiseo advanced analyze 123 --format=summary

# Test 14: Bulk analyze posts below 70% score
wp aiseo advanced bulk --min-score=70 --limit=20

# Test 15: Export analysis to CSV
wp aiseo advanced bulk --min-score=80 --format=csv > seo-report.csv

# Bulk Editing Tests (NEW)
# Test 16: List posts for bulk editing
wp aiseo bulk list --limit=20 --format=table

# Test 17: List post IDs only
wp aiseo bulk list --format=ids

# Test 18: Bulk update focus keyword
wp aiseo bulk update 123,456,789 --focus-keyword="wordpress seo"

# Test 19: Bulk update multiple fields
wp aiseo bulk update 123,456 --meta-title="SEO Title" --robots-index=noindex

# Test 20: Bulk generate metadata
wp aiseo bulk generate 123,456,789 --meta-types=title,description

# Test 21: Bulk generate for all posts
wp aiseo bulk generate --all --limit=10 --overwrite

# Test 22: Preview bulk changes
wp aiseo bulk preview 123,456 --focus-keyword="new keyword"

# Import/Export Tests (NEW)
# Test 23: Export to JSON
wp aiseo export json --output=aiseo-export.json

# Test 24: Export to CSV
wp aiseo export csv --output=aiseo-export.csv

# Test 25: Export specific post type
wp aiseo export json --post-type=page --output=pages.json

# Test 26: Import from JSON
wp aiseo import json aiseo-export.json

# Test 27: Import with overwrite
wp aiseo import json aiseo-export.json --overwrite

# Test 28: Import from Yoast SEO
wp aiseo import yoast --post-type=post

# Test 29: Import from Rank Math with overwrite
wp aiseo import rankmath --overwrite --limit=100

# Test 30: Import from AIOSEO
wp aiseo import aioseo --post-type=page
```

#### 3. **Admin Interface Testing** (Visual)

1. **Content Analysis**
   - Navigate to any post/page editor
   - Scroll to "AISEO - SEO Optimization" metabox
   - Click "Analyze Content" button
   - View 11 SEO metrics with traffic light indicators

2. **Meta Generation**
   - In the AISEO metabox, click "Generate Title"
   - Review 3 AI-generated suggestions
   - Click "Use This" to apply
   - Repeat for meta description

3. **Image SEO** (NEW)
   - Navigate to `wp-admin/admin.php?page=aiseo-image-seo`
   - View statistics dashboard
   - See missing alt text table
   - Click "Generate Alt Text" for individual images
   - Select multiple images and bulk generate
   - Watch progress bar

4. **Advanced SEO Analysis** (NEW)
   - In post editor, click "Analyze Content" in AISEO metabox
   - View 40+ SEO factors with traffic light indicators
   - See overall score and status (Good/OK/Poor)
   - Review prioritized recommendations
   - Check detailed factor breakdown by category

5. **Bulk Editing Interface** (NEW)
   - Use WP-CLI or REST API for bulk operations
   - List posts with current metadata status
   - Update multiple posts simultaneously
   - Generate AI metadata in bulk with progress tracking
   - Preview changes before applying

6. **Import/Export Functionality** (NEW)
   - Export AISEO metadata to JSON or CSV
   - Import from JSON files
   - Import from Yoast SEO plugin
   - Import from Rank Math plugin
   - Import from AIOSEO plugin
   - Backup and restore functionality

7. **Settings Page**
   - Navigate to `wp-admin/admin.php?page=aiseo-settings`
   - Configure OpenAI API key
   - Test API connection
   - View usage statistics

8. **Dashboard Widget**
   - View WordPress dashboard
   - Check "AISEO Overview" widget
   - See total posts analyzed, average score, API usage

### Complete Testing Workflow

```bash
# Step 1: Create test post
POST_ID=$(wp post create --post_title="Test SEO Post" --post_content="Lorem ipsum dolor sit amet, consectetur adipiscing elit. This is a test post for WordPress SEO optimization. We will analyze the content and generate metadata using AI." --post_status=publish --porcelain)

echo "Created post ID: $POST_ID"

# Step 2: Set focus keyword
wp aiseo meta update $POST_ID focus_keyword "wordpress seo"

# Step 3: Generate all metadata
wp aiseo generate --id=$POST_ID --meta=all

# Step 4: Analyze content (11 metrics)
wp aiseo analyze --id=$POST_ID --format=json

# Step 5: Verify metadata via REST API
curl https://yoursite.test/wp-json/aiseo/v1/meta-tags/$POST_ID

# Step 6: Check schema markup
curl https://yoursite.test/wp-json/aiseo/v1/schema/$POST_ID

# Step 7: Verify social media tags
curl https://yoursite.test/wp-json/aiseo/v1/social-tags/$POST_ID

# Step 8: Test image SEO (if post has images)
wp aiseo image analyze $POST_ID

# Step 9: View in browser
wp post url $POST_ID

# Step 10: Check frontend output
curl -s $(wp post url $POST_ID) | grep -A 5 "<meta name=\"description\""
```

### Integration Testing

```bash
# Test 1: Complete SEO workflow
wp aiseo generate --id=123 --meta=all
wp aiseo analyze --id=123
wp aiseo image bulk-generate --post-id=123

# Test 2: Bulk optimization
wp aiseo analyze --all --score-below=50 --format=ids > low-score-posts.txt
cat low-score-posts.txt | xargs -I {} wp aiseo generate --id={} --meta=all

# Test 3: Export and import
wp aiseo export --all --file=backup.json
wp aiseo import --file=backup.json

# Test 4: Performance testing
time wp aiseo analyze --all --format=count
```

### Expected Results

✅ **Content Analysis** should return:
- 11 SEO metrics (keyword density, readability, etc.)
- Overall score (0-100)
- Status indicators (good/ok/poor)
- Actionable recommendations

✅ **Meta Generation** should produce:
- Title: 50-60 characters with keyword
- Description: 155-160 characters, compelling
- 3 variations to choose from

✅ **Schema Markup** should include:
- Valid JSON-LD format
- Article/BlogPosting type
- Author, publisher, dates
- Images and word count

✅ **Image SEO** should provide:
- Alt text: 50-125 characters
- Focus keyword included naturally
- SEO score: 0-100 with breakdown
- Bulk processing with progress tracking

✅ **Advanced SEO Analysis** should return:
- 40+ SEO factors analyzed
- Overall score: 0-400 points (0-100%)
- Status: Good (80%+), OK (50-79%), Poor (<50%)
- Categorized results: Content Quality, Readability, Technical SEO, UX
- Prioritized recommendations (high/medium/low)
- Actionable insights for each factor

✅ **Bulk Editing** should provide:
- List posts with metadata status
- Update multiple posts at once
- Bulk generate with AI (titles, descriptions)
- Preview changes before applying
- Progress tracking for bulk operations
- Success/failure reporting per post

✅ **Import/Export** should provide:
- Export to JSON with all metadata fields
- Export to CSV for spreadsheet editing
- Import from JSON files
- Import from Yoast SEO (meta title, description, keywords, canonical, robots)
- Import from Rank Math (all SEO fields)
- Import from AIOSEO (title, description, keywords)
- Overwrite or skip existing metadata options
- Success/skip/failure reporting

## 📚 Documentation

- [Architecture Documentation](ARCHITECTURE.md) - Detailed technical architecture
- [Feature Specifications](FEATURE-SPECIFICATIONS.md) - Detailed specs for all features
- [Testing & Uninstall Guide](UNINSTALL-AND-TESTING.md) - Comprehensive testing workflows
- [WP-CLI Commands](UNINSTALL-AND-TESTING.md#wp-cli-testing--commands) - Complete CLI reference
- [REST API Reference](UNINSTALL-AND-TESTING.md#rest-api-testing-easiest-method) - API endpoints documentation

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the GPL-2.0-or-later License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- OpenAI for the GPT-4o-mini API
- WordPress community for excellent documentation
- Yoast SEO and SEOPress for inspiration

## 📧 Support

- Documentation: This README and linked docs
- Issues: [GitHub Issues](https://github.com/praisonai/aiseo/issues)
- Website: [https://praisonai.com](https://praisonai.com)

## 🗺️ Roadmap

### Version 1.0.0 (Current - In Development)
- ✅ Core plugin infrastructure
- ✅ OpenAI API integration
- ✅ REST API endpoints
- ✅ WP-CLI commands
- ⏳ Content analysis engine
- ⏳ Schema markup generator
- ⏳ Meta tags management
- ⏳ Social media optimization
- ⏳ XML sitemap generator
- ⏳ Admin interface

### Version 1.1.0 (Planned)
- Advanced SEO analysis
- Bulk editing interface
- Import/export functionality
- Multilingual support
- Custom post type support

### Version 2.0.0 (Future)
- Competitor analysis
- Keyword research tool
- Backlink monitoring
- Rank tracking
- Content suggestions
- Integration with Google Search Console
- Integration with Google Analytics

---

**Made with ❤️ by [PraisonAI](https://praison.ai)**
