# AISEO WordPress Plugin - Detailed Architecture

## Executive Summary

**AISEO** is an AI-powered SEO WordPress plugin that automatically generates and optimizes SEO elements using OpenAI's API. Unlike chatbot plugins, AISEO focuses on content optimization, meta tag generation, schema markup, and comprehensive SEO analysis.

---

## Plugin Information

- **Plugin Name:** AISEO
- **Version:** 1.0.0
- **Description:** AI-powered SEO optimization for WordPress. Automatically generate meta descriptions, titles, schema markup, and comprehensive SEO analysis using artificial intelligence.
- **Author:** PraisonAI
- **License:** GPL-2.0-or-later
- **Text Domain:** aiseo
- **Requires at least:** WordPress 5.0
- **Tested up to:** WordPress 6.8
- **Requires PHP:** 7.4

---

## Core Features (Based on Yoast SEO & Industry Standards)

### 1. **AI-Powered Meta Generation**
- Auto-generate SEO titles (with character limit guidance)
- Auto-generate meta descriptions (155-160 characters optimal)
- Multiple AI-generated suggestions to choose from
- Real-time preview of how content appears in search results
- Edit and customize AI suggestions

### 2. **AI Content Analysis**
- **Keyword Density Analysis** - Optimal keyword usage detection
- **Readability Score** - Flesch-Kincaid reading ease calculation
- **Paragraph Structure** - Length and structure recommendations
- **Sentence Length Analysis** - Readability optimization
- **Keyword in Subheadings** - H2/H3 keyword placement check
- **Keyword in Introduction** - First paragraph optimization
- **Content Length Analysis** - Word count recommendations

### 3. **Schema Markup Generation (JSON-LD)**
- **Article Schema** - For blog posts and articles
- **BlogPosting Schema** - Blog-specific markup
- **WebPage Schema** - General page markup
- **Organization Schema** - Site-wide organization data
- **Person/Author Schema** - Author information
- **BreadcrumbList Schema** - Navigation breadcrumbs
- **FAQ Schema** - Frequently asked questions
- **HowTo Schema** - Step-by-step guides

### 4. **Social Media Optimization**
- **Open Graph Tags** - Facebook, LinkedIn optimization
  - og:title
  - og:description
  - og:image
  - og:type
  - og:url
- **Twitter Card Tags** - Twitter-specific optimization
  - twitter:card
  - twitter:title
  - twitter:description
  - twitter:image

### 5. **XML Sitemap Generation**
- Automatic sitemap creation and updates
- Post type inclusion/exclusion settings
- Priority and frequency settings
- Automatic ping to search engines on updates
- Accessible at `/sitemap.xml`

### 6. **Permalink Optimization**
- Remove stop words from URLs
- Suggest SEO-friendly permalink structures
- Keyword inclusion in URLs

### 7. **Internal Linking Suggestions**
- AI-powered related content suggestions
- Automatic internal link recommendations
- Link anchor text optimization

### 8. **Image SEO**
- AI-generated alt text for images
- Image title optimization
- Image filename suggestions

---

## File Structure (Root Directory - No Trunk Folder)

```
/Users/praison/aiseo/
│
├── aiseo.php                           # Main plugin file (header, initialization)
├── readme.txt                          # WordPress.org readme (submission ready)
├── LICENSE                             # GPL-2.0 license file
├── .gitignore                          # Git ignore file
│
├── css/
│   ├── aiseo-admin.css                # Admin settings page styles
│   ├── aiseo-metabox.css              # Post editor metabox styles
│   └── aiseo-analysis.css             # SEO analysis panel styles
│
├── js/
│   ├── aiseo-admin.js                 # Admin settings interactions
│   ├── aiseo-metabox.js               # Metabox AJAX and UI interactions
│   └── aiseo-analysis.js              # Real-time content analysis
│
├── includes/
│   ├── class-aiseo-core.php           # Core plugin class (initialization)
│   ├── class-aiseo-api.php            # OpenAI API handler
│   ├── class-aiseo-meta.php           # Meta tags injection and management
│   ├── class-aiseo-analysis.php       # Content analysis engine
│   ├── class-aiseo-schema.php         # Schema markup generator
│   ├── class-aiseo-sitemap.php        # XML sitemap generator
│   ├── class-aiseo-social.php         # Social media tags handler
│   ├── class-aiseo-admin.php          # Admin settings page
│   ├── class-aiseo-metabox.php        # Post editor metabox
│   └── class-aiseo-helpers.php        # Helper functions and utilities
│
├── languages/
│   └── aiseo.pot                      # Translation template (generated)
│
└── assets/
    ├── icon-128x128.png               # Plugin icon (128x128)
    ├── icon-256x256.png               # Plugin icon (256x256)
    ├── banner-772x250.png             # WordPress.org banner
    └── banner-1544x500.png            # WordPress.org banner (retina)
```

---

## Detailed Component Specifications

### 1. **aiseo.php** (Main Plugin File)

**Purpose:** Plugin header, initialization, and WordPress hooks

**Key Functions:**
```php
- Plugin header with metadata
- Security check (ABSPATH)
- Define constants (AISEO_VERSION, AISEO_PLUGIN_DIR, AISEO_PLUGIN_URL)
- Autoloader for classes
- Activation/deactivation hooks
- Initialize core plugin class
```

**Hooks:**
- `register_activation_hook()` - Setup default options, flush rewrite rules
- `register_deactivation_hook()` - Cleanup temporary data
- `plugins_loaded` - Load text domain for translations

---

### 2. **class-aiseo-core.php** (Core Plugin Class)

**Purpose:** Central initialization and coordination

**Key Methods:**
```php
- __construct() - Initialize all components
- init_hooks() - Register WordPress hooks
- load_dependencies() - Load required classes
- define_admin_hooks() - Admin-specific hooks
- define_public_hooks() - Public-facing hooks
```

**Responsibilities:**
- Instantiate all other classes
- Coordinate between components
- Manage plugin lifecycle

---

### 3. **class-aiseo-api.php** (OpenAI API Handler)

**Purpose:** Manage all OpenAI API communications

**Key Methods:**
```php
- generate_meta_description($content, $keyword) - Generate meta description
- generate_title($content, $keyword) - Generate SEO title
- generate_multiple_suggestions($content, $type, $count) - Multiple options
- analyze_content($content) - Content analysis
- generate_alt_text($image_context) - Image alt text
- generate_schema($content, $type) - Schema markup suggestions
- check_api_key() - Validate API key
- handle_api_error($response) - Error handling
```

**API Configuration:**
- **Model**: gpt-3.5-turbo (default), configurable to gpt-4, gpt-4-turbo
- **Timeout**: 
  - Connection timeout: 10 seconds
  - Read timeout: 30 seconds
  - Total timeout: 45 seconds
- **Max tokens**: 1000 (configurable per request type)
- **Temperature**: 0.7 (balanced creativity/consistency)
- **Rate limiting**: 
  - 60 requests per hour per site
  - 10 requests per minute per user
  - Exponential backoff on rate limit errors
- **Retry logic**: 
  - Max 3 retries
  - Exponential backoff: 2^n seconds (2s, 4s, 8s)
  - Retry on: 429 (rate limit), 500, 502, 503, 504
  - No retry on: 400, 401, 403

**Security (Enhanced):**

1. **API Key Encryption**
   - **Algorithm**: AES-256-CBC encryption
   - **Encryption key**: Stored in `wp-config.php` as `AISEO_ENCRYPTION_KEY` constant
   - **Salt**: Separate `AISEO_ENCRYPTION_SALT` constant
   - **IV (Initialization Vector)**: Generated using `openssl_random_pseudo_bytes(16)` per encryption
   - **Storage format**: `base64(encrypted_key::iv)` in database
   - **Key rotation**: Support for re-encryption with new keys
   - **Environment variables**: Support `.env` file via `wp-config.php`
   
   ```php
   // In wp-config.php (auto-generated on activation if not exists)
   define('AISEO_ENCRYPTION_KEY', 'your-64-char-random-key-here');
   define('AISEO_ENCRYPTION_SALT', 'your-64-char-random-salt-here');
   
   // Encryption implementation
   function aiseo_encrypt_api_key($key) {
       $cipher = 'AES-256-CBC';
       $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
       $encrypted = openssl_encrypt(
           $key,
           $cipher,
           AISEO_ENCRYPTION_KEY,
           OPENSSL_RAW_DATA,
           $iv
       );
       return base64_encode($encrypted . '::' . $iv);
   }
   
   function aiseo_decrypt_api_key($encrypted_key) {
       $cipher = 'AES-256-CBC';
       list($encrypted_data, $iv) = explode('::', base64_decode($encrypted_key), 2);
       return openssl_decrypt(
           $encrypted_data,
           $cipher,
           AISEO_ENCRYPTION_KEY,
           OPENSSL_RAW_DATA,
           $iv
       );
   }
   ```

2. **Request Security**
   - Use `wp_remote_post()` with SSL verification
   - Validate and sanitize all inputs before API calls
   - Never expose API key in frontend JavaScript
   - Use nonces for all AJAX requests
   - Capability checks: `edit_posts` for content generation
   - Rate limit tracking per user and per site

3. **API Key Validation**
   - Test API key on save with minimal request
   - Store validation status and last check timestamp
   - Re-validate every 24 hours
   - Clear validation on key change

**Error Handling & Fault Tolerance:**

1. **Circuit Breaker Pattern**
   ```php
   // Stop making requests after 5 consecutive failures
   // Resume after 5-minute cooldown period
   $failure_count = get_transient('aiseo_api_failures');
   if ($failure_count >= 5) {
       $cooldown = get_transient('aiseo_api_cooldown');
       if ($cooldown) {
           return new WP_Error('circuit_breaker', 'API temporarily unavailable');
       }
   }
   ```

2. **Exponential Backoff**
   ```php
   $max_retries = 3;
   $retry_count = 0;
   
   while ($retry_count < $max_retries) {
       $response = wp_remote_post($api_url, $args);
       
       if (!is_wp_error($response)) {
           $status_code = wp_remote_retrieve_response_code($response);
           
           // Success
           if ($status_code === 200) {
               return $response;
           }
           
           // Retry on specific errors
           if (in_array($status_code, [429, 500, 502, 503, 504])) {
               $retry_count++;
               $wait_time = pow(2, $retry_count); // 2, 4, 8 seconds
               sleep($wait_time);
               continue;
           }
           
           // Don't retry on client errors
           if ($status_code >= 400 && $status_code < 500) {
               return new WP_Error('api_error', 'Client error: ' . $status_code);
           }
       }
       
       $retry_count++;
   }
   
   return new WP_Error('api_timeout', 'Max retries exceeded');
   ```

3. **Dead Letter Queue**
   - Store failed requests in custom database table
   - Include: timestamp, request type, content, error message
   - Admin interface to review and retry failed requests
   - Auto-cleanup after 30 days

4. **Graceful Degradation**
   - If API fails, allow manual input
   - Show cached suggestions if available
   - Display helpful error messages
   - Don't block post publishing on API failure

**Logging & Monitoring:**

1. **Structured Logging**
   ```php
   // Log format: JSON with consistent structure
   [
       'timestamp' => '2025-11-02T06:00:00Z',
       'level' => 'ERROR',          // DEBUG, INFO, WARNING, ERROR, CRITICAL
       'category' => 'api_request',  // api_request, cache, security, performance
       'message' => 'API request failed',
       'context' => [
           'user_id' => 1,
           'post_id' => 123,
           'request_type' => 'generate_meta_description',
           'status_code' => 429,
           'retry_count' => 3,
           'duration_ms' => 1250
       ],
       'trace_id' => 'unique-request-id'
   ]
   ```

2. **Log Levels**
   - **DEBUG**: Detailed information for troubleshooting (disabled in production)
   - **INFO**: General informational messages (API success, cache hits)
   - **WARNING**: Warning messages (slow API response, cache miss)
   - **ERROR**: Error conditions (API failure, validation errors)
   - **CRITICAL**: Critical conditions (API key invalid, encryption failure)

3. **Log Storage**
   - Store in custom database table: `{prefix}_aiseo_logs`
   - Fields: id, timestamp, level, category, message, context (JSON), user_id, post_id
   - Index on: timestamp, level, category, user_id
   - Automatic rotation: Keep last 10,000 entries or 30 days
   - Export to CSV functionality for analysis

4. **Performance Metrics**
   ```php
   // Track and store metrics
   - API response time (avg, min, max)
   - Success rate (percentage)
   - Token usage per request type
   - Cache hit rate
   - Error rate by type
   - Requests per hour/day
   ```

5. **Debug Mode**
   - Enable via: `define('AISEO_DEBUG', true);` in `wp-config.php`
   - Verbose logging of all API requests/responses
   - Display debug info in admin metabox
   - Log all cache operations
   - Performance profiling

**Caching Strategy:**

1. **Transient Cache (WordPress Transients)**
   ```php
   // Cache structure
   'aiseo_meta_desc_{post_id}_{hash}' => [
       'suggestions' => ['option1', 'option2', 'option3'],
       'generated_at' => timestamp,
       'keyword' => 'focus keyword'
   ]
   
   // TTL (Time To Live)
   - Generated suggestions: 24 hours
   - Content analysis: 1 hour
   - API validation: 24 hours
   - Schema markup: Until post update
   ```

2. **Object Cache Support**
   - Compatible with Redis, Memcached
   - Automatic detection of persistent object cache
   - Fallback to transients if not available
   - Cache groups: 'aiseo_api', 'aiseo_analysis', 'aiseo_schema'

3. **Cache Invalidation**
   ```php
   // Clear cache on:
   - Post update (clear post-specific cache)
   - Settings change (clear all cache)
   - Manual cache flush (admin button)
   - API key change (clear all cache)
   - Plugin update (clear all cache)
   ```

4. **Cache Keys**
   ```php
   // Naming convention
   'aiseo_{type}_{post_id}_{hash}'
   
   // Hash includes:
   - Content hash (first 1000 chars)
   - Focus keyword
   - Settings version
   
   // Example
   'aiseo_meta_desc_123_a1b2c3d4'
   'aiseo_analysis_123_e5f6g7h8'
   'aiseo_schema_123_i9j0k1l2'
   ```

5. **Cache Warming**
   - Pre-generate common requests during off-peak hours
   - WordPress cron job: daily at 3 AM
   - Warm cache for: recent posts, popular posts, scheduled posts

**Rate Limiting & Cost Management:**

1. **Request Throttling**
   ```php
   // Per-user limits
   - 10 requests per minute
   - 60 requests per hour
   - 500 requests per day
   
   // Per-site limits
   - 100 requests per hour
   - 1000 requests per day
   
   // Implementation
   $user_requests = get_transient('aiseo_user_requests_' . $user_id);
   if ($user_requests >= 10) {
       return new WP_Error('rate_limit', 'Too many requests. Please wait.');
   }
   set_transient('aiseo_user_requests_' . $user_id, $user_requests + 1, 60);
   ```

2. **Token Usage Tracking**
   ```php
   // Store in database
   - Total tokens used (lifetime)
   - Tokens used today/this month
   - Tokens per request type
   - Estimated cost (tokens * price per token)
   
   // Display in admin dashboard
   - Usage statistics widget
   - Cost estimation
   - Usage trends graph
   - Alerts when approaching limits
   ```

3. **Cost Estimation**
   ```php
   // OpenAI pricing (as of 2025)
   gpt-3.5-turbo: $0.0015 per 1K tokens (input), $0.002 per 1K tokens (output)
   gpt-4: $0.03 per 1K tokens (input), $0.06 per 1K tokens (output)
   
   // Show before generation
   "Estimated cost: $0.005 (approximately 2,500 tokens)"
   ```

4. **Usage Quotas**
   - Admin can set monthly token limit
   - Soft limit: Warning at 80%
   - Hard limit: Stop requests at 100%
   - Email notifications to admin
   - Per-user quotas (optional)

5. **Queue System**
   ```php
   // Batch processing for non-urgent requests
   - Queue requests during peak hours
   - Process via WP-Cron during off-peak (2-6 AM)
   - Priority queue: urgent (real-time) vs normal (batched)
   - Status tracking: queued, processing, completed, failed
   ```

---

### 4. **class-aiseo-meta.php** (Meta Tags Management)

**Purpose:** Inject and manage all meta tags in `<head>`

**Key Methods:**
```php
- inject_meta_tags() - Add meta tags to wp_head
- get_meta_title($post_id) - Retrieve custom title
- get_meta_description($post_id) - Retrieve custom description
- generate_canonical_url($post_id) - Canonical URL
- add_robots_meta() - Robots meta tag
- add_og_tags() - Open Graph tags
- add_twitter_tags() - Twitter Card tags
```

**Meta Tags Generated:**
- `<title>` - SEO-optimized title
- `<meta name="description">` - Meta description
- `<meta name="robots">` - Index/follow directives
- `<link rel="canonical">` - Canonical URL
- Open Graph tags (og:*)
- Twitter Card tags (twitter:*)

**Hooks:**
- `wp_head` (priority 1) - Inject meta tags early
- `wp_title` - Filter page title

---

### 5. **class-aiseo-analysis.php** (Content Analysis Engine)

**Purpose:** Real-time SEO analysis of content

**Key Methods:**
```php
- analyze_keyword_density($content, $keyword) - Calculate density
- analyze_readability($content) - Flesch-Kincaid score
- analyze_paragraph_structure($content) - Paragraph analysis
- analyze_sentence_length($content) - Sentence analysis
- check_keyword_in_headings($content, $keyword) - Heading analysis
- check_keyword_in_intro($content, $keyword) - Introduction check
- analyze_content_length($content) - Word count analysis
- check_internal_links($content) - Link analysis
- check_external_links($content) - External link check
- generate_seo_score($analyses) - Overall SEO score (0-100)
```

**Analysis Criteria:**
- **Keyword Density:** 0.5% - 2.5% optimal
- **Readability:** Flesch-Kincaid 60+ (easy to read)
- **Paragraph Length:** Max 150 words
- **Sentence Length:** Max 20 words
- **Content Length:** Min 300 words (blog posts)
- **Internal Links:** Min 2-3 per post
- **External Links:** Min 1-2 authoritative sources

**Output Format:**
```php
[
    'overall_score' => 85,
    'keyword_density' => ['score' => 90, 'status' => 'good', 'message' => '...'],
    'readability' => ['score' => 75, 'status' => 'ok', 'message' => '...'],
    'paragraph_structure' => ['score' => 80, 'status' => 'good', 'message' => '...'],
    // ... more analyses
]
```

---

### 6. **class-aiseo-schema.php** (Schema Markup Generator)

**Purpose:** Generate and inject JSON-LD structured data

**Key Methods:**
```php
- generate_article_schema($post) - Article schema
- generate_blogposting_schema($post) - BlogPosting schema
- generate_webpage_schema($post) - WebPage schema
- generate_organization_schema() - Organization schema
- generate_person_schema($author_id) - Person/Author schema
- generate_breadcrumb_schema($post) - Breadcrumb schema
- inject_schema() - Add schema to wp_footer
- validate_schema($schema) - Validate JSON-LD
```

**Schema Types Supported:**
- Article
- BlogPosting
- WebPage
- Organization
- Person
- BreadcrumbList
- FAQPage
- HowTo

**Implementation:**
- JSON-LD format (Google recommended)
- Inject via `wp_footer` hook
- Validate against Schema.org standards
- Conditional loading based on content type

---

### 7. **class-aiseo-sitemap.php** (XML Sitemap Generator)

**Purpose:** Generate and manage XML sitemap

**Key Methods:**
```php
- generate_sitemap() - Create XML sitemap
- update_sitemap() - Update on content changes
- get_posts_for_sitemap() - Retrieve posts
- get_pages_for_sitemap() - Retrieve pages
- get_custom_post_types() - Custom post types
- add_rewrite_rules() - Add sitemap URL rewrite
- serve_sitemap() - Output XML
- ping_search_engines() - Notify Google/Bing
```

**Features:**
- Automatic generation on content publish/update
- Post type inclusion/exclusion
- Priority settings (0.0 - 1.0)
- Change frequency settings
- Image sitemap support
- Accessible at `/sitemap.xml`

**Hooks:**
- `init` - Add rewrite rules
- `save_post` - Update sitemap
- `delete_post` - Update sitemap

---

### 8. **class-aiseo-social.php** (Social Media Tags)

**Purpose:** Manage Open Graph and Twitter Card tags

**Key Methods:**
```php
- generate_og_tags($post) - Open Graph tags
- generate_twitter_tags($post) - Twitter Card tags
- get_og_image($post) - Featured image for OG
- get_twitter_image($post) - Featured image for Twitter
- ai_generate_social_description($content) - AI social descriptions
```

**Tags Generated:**
- og:title, og:description, og:image, og:url, og:type, og:site_name
- twitter:card, twitter:title, twitter:description, twitter:image

---

### 9. **class-aiseo-admin.php** (Admin Settings Page)

**Purpose:** Plugin settings interface

**Settings Sections:**
1. **API Settings**
   - OpenAI API Key (password field with show/hide toggle)
   - API Model selection (gpt-3.5-turbo / gpt-4)
   - API timeout setting

2. **General Settings**
   - Enable/disable features (sitemap, schema, social tags)
   - Default focus keyword behavior
   - Auto-generate on publish

3. **Schema Settings**
   - Organization name
   - Organization logo
   - Social profile URLs

4. **Sitemap Settings**
   - Include/exclude post types
   - Priority settings
   - Change frequency

**Key Methods:**
```php
- add_admin_menu() - Add settings page
- render_settings_page() - Display settings
- register_settings() - Register options
- sanitize_settings($input) - Sanitize inputs
- enqueue_admin_scripts() - Load admin JS/CSS
```

---

### 10. **class-aiseo-metabox.php** (Post Editor Metabox)

**Purpose:** SEO metabox in post/page editor

**Metabox Sections:**

1. **Focus Keyword**
   - Input field for target keyword
   - Keyword suggestions

2. **SEO Title**
   - Custom title input
   - Character counter (50-60 optimal)
   - "Generate with AI" button
   - Preview in search results

3. **Meta Description**
   - Custom description textarea
   - Character counter (155-160 optimal)
   - "Generate with AI" button
   - Preview in search results

4. **SEO Analysis**
   - Real-time content analysis
   - Color-coded indicators (red/orange/green)
   - Overall SEO score (0-100)
   - Actionable recommendations

5. **Social Preview**
   - Facebook preview
   - Twitter preview
   - Custom social titles/descriptions

6. **Advanced**
   - Canonical URL
   - Robots meta (index/noindex, follow/nofollow)
   - Schema type selection

**Key Methods:**
```php
- add_metabox() - Register metabox
- render_metabox($post) - Display metabox
- save_metabox_data($post_id) - Save custom fields
- ajax_generate_title() - AJAX title generation
- ajax_generate_description() - AJAX description generation
- ajax_analyze_content() - AJAX content analysis
```

**AJAX Actions:**
- `aiseo_generate_title`
- `aiseo_generate_description`
- `aiseo_analyze_content`
- `aiseo_generate_social_tags`

---

### 11. **class-aiseo-helpers.php** (Helper Functions)

**Purpose:** Utility functions used across the plugin

**Key Functions:**
```php
- sanitize_api_key($key) - Sanitize API key
- encrypt_api_key($key) - Encrypt for storage
- decrypt_api_key($encrypted) - Decrypt for use
- calculate_reading_time($content) - Reading time estimate
- truncate_text($text, $length) - Smart text truncation
- strip_shortcodes_and_tags($content) - Clean content
- get_focus_keyword($post_id) - Retrieve focus keyword
- calculate_flesch_kincaid($content) - Readability score
- count_syllables($word) - Syllable counter for readability
```

---

## Database Schema (Enhanced)

### Post Meta Fields

```sql
-- Core SEO Fields
_aiseo_focus_keyword          - varchar(255) - Target keyword
_aiseo_meta_title             - varchar(255) - Custom SEO title
_aiseo_meta_description       - text         - Custom meta description
_aiseo_canonical_url          - varchar(255) - Custom canonical URL
_aiseo_robots_index           - varchar(20)  - index/noindex
_aiseo_robots_follow          - varchar(20)  - follow/nofollow

-- Social Media Fields
_aiseo_og_title               - varchar(255) - Open Graph title
_aiseo_og_description         - text         - Open Graph description
_aiseo_og_image               - varchar(255) - Open Graph image URL
_aiseo_twitter_title          - varchar(255) - Twitter Card title
_aiseo_twitter_description    - text         - Twitter Card description
_aiseo_twitter_image          - varchar(255) - Twitter Card image URL

-- Schema & Analysis Fields
_aiseo_schema_type            - varchar(50)  - Schema type selection
_aiseo_seo_score              - int(3)       - Overall SEO score (0-100)
_aiseo_analysis_data          - longtext     - Serialized analysis results
_aiseo_last_analyzed          - datetime     - Last analysis timestamp

-- AI Generation Tracking
_aiseo_ai_generated_title     - boolean      - Whether title was AI-generated
_aiseo_ai_generated_desc      - boolean      - Whether description was AI-generated
_aiseo_generation_timestamp   - datetime     - When AI content was generated
_aiseo_generation_cost        - decimal(10,6)- Estimated cost of generation

-- Performance & Cache
_aiseo_content_hash           - varchar(32)  - MD5 hash of content for cache invalidation
_aiseo_cache_version          - int          - Cache version for invalidation
```

### Options Table

```sql
-- API Configuration
aiseo_openai_api_key          - text         - Encrypted OpenAI API key
aiseo_api_model               - varchar(50)  - gpt-3.5-turbo, gpt-4, gpt-4-turbo
aiseo_api_timeout             - int          - API timeout in seconds (default: 45)
aiseo_api_max_tokens          - int          - Max tokens per request (default: 1000)
aiseo_api_temperature         - decimal(2,1) - Temperature setting (default: 0.7)

-- Feature Toggles
aiseo_enable_sitemap          - boolean      - Enable XML sitemap
aiseo_enable_schema           - boolean      - Enable schema markup
aiseo_enable_social_tags      - boolean      - Enable social media tags
aiseo_auto_generate           - boolean      - Auto-generate on publish
aiseo_enable_image_alt        - boolean      - Enable AI alt text generation

-- Organization Data
aiseo_organization_name       - varchar(255) - Organization name
aiseo_organization_logo       - varchar(255) - Organization logo URL
aiseo_social_profiles         - text         - JSON array of social URLs

-- Sitemap Configuration
aiseo_sitemap_post_types      - text         - JSON array of included post types
aiseo_sitemap_priority        - text         - JSON object of priorities
aiseo_sitemap_last_generated  - datetime     - Last sitemap generation time

-- Rate Limiting & Usage
aiseo_rate_limit_per_minute   - int          - Requests per minute (default: 10)
aiseo_rate_limit_per_hour     - int          - Requests per hour (default: 60)
aiseo_monthly_token_limit     - int          - Monthly token limit (0 = unlimited)
aiseo_token_usage_month       - int          - Tokens used this month
aiseo_token_usage_total       - int          - Total tokens used (lifetime)

-- System
aiseo_version                 - varchar(20)  - Plugin version
aiseo_encryption_version      - int          - Encryption version for key rotation
aiseo_last_api_validation     - datetime     - Last API key validation
aiseo_api_validation_status   - varchar(20)  - valid/invalid/pending
aiseo_install_date            - datetime     - Plugin installation date
```

### Custom Tables

#### 1. Logs Table: `{prefix}_aiseo_logs`

```sql
CREATE TABLE {prefix}_aiseo_logs (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    level VARCHAR(20) NOT NULL,           -- DEBUG, INFO, WARNING, ERROR, CRITICAL
    category VARCHAR(50) NOT NULL,        -- api_request, cache, security, performance
    message TEXT NOT NULL,
    context LONGTEXT,                     -- JSON encoded context data
    user_id BIGINT(20) UNSIGNED,
    post_id BIGINT(20) UNSIGNED,
    trace_id VARCHAR(36),                 -- UUID for request tracing
    PRIMARY KEY (id),
    KEY timestamp (timestamp),
    KEY level (level),
    KEY category (category),
    KEY user_id (user_id),
    KEY trace_id (trace_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 2. Failed Requests Table: `{prefix}_aiseo_failed_requests`

```sql
CREATE TABLE {prefix}_aiseo_failed_requests (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    timestamp DATETIME NOT NULL,
    request_type VARCHAR(50) NOT NULL,    -- generate_title, generate_description, etc.
    post_id BIGINT(20) UNSIGNED,
    user_id BIGINT(20) UNSIGNED,
    content LONGTEXT,                     -- The content that was being processed
    error_message TEXT,
    error_code VARCHAR(20),
    retry_count INT DEFAULT 0,
    status VARCHAR(20) DEFAULT 'pending', -- pending, retrying, failed, resolved
    last_retry_at DATETIME,
    PRIMARY KEY (id),
    KEY timestamp (timestamp),
    KEY status (status),
    KEY post_id (post_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 3. Usage Statistics Table: `{prefix}_aiseo_usage_stats`

```sql
CREATE TABLE {prefix}_aiseo_usage_stats (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    date DATE NOT NULL,
    request_type VARCHAR(50) NOT NULL,
    requests_count INT DEFAULT 0,
    tokens_used INT DEFAULT 0,
    avg_response_time INT,                -- milliseconds
    success_count INT DEFAULT 0,
    error_count INT DEFAULT 0,
    cache_hits INT DEFAULT 0,
    cache_misses INT DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY date_type (date, request_type),
    KEY date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 4. Request Queue Table: `{prefix}_aiseo_request_queue`

```sql
CREATE TABLE {prefix}_aiseo_request_queue (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    created_at DATETIME NOT NULL,
    scheduled_at DATETIME NOT NULL,
    priority VARCHAR(20) DEFAULT 'normal', -- urgent, normal, low
    request_type VARCHAR(50) NOT NULL,
    post_id BIGINT(20) UNSIGNED,
    user_id BIGINT(20) UNSIGNED,
    request_data LONGTEXT,                 -- JSON encoded request parameters
    status VARCHAR(20) DEFAULT 'queued',   -- queued, processing, completed, failed
    processed_at DATETIME,
    result LONGTEXT,                       -- JSON encoded result
    PRIMARY KEY (id),
    KEY scheduled_at (scheduled_at),
    KEY status (status),
    KEY priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Database Indexes (Performance Optimization)

```sql
-- Add indexes to postmeta for faster queries
ALTER TABLE {prefix}_postmeta 
    ADD INDEX aiseo_focus_keyword (meta_key(191), meta_value(191)) 
    WHERE meta_key LIKE '_aiseo_%';

-- Composite indexes for common queries
ALTER TABLE {prefix}_aiseo_logs 
    ADD INDEX level_timestamp (level, timestamp);

ALTER TABLE {prefix}_aiseo_usage_stats 
    ADD INDEX date_request_type (date, request_type);
```

---

## WordPress Hooks & Filters

### Actions
```php
add_action('admin_menu', 'aiseo_add_admin_menu');
add_action('admin_enqueue_scripts', 'aiseo_enqueue_admin_scripts');
add_action('add_meta_boxes', 'aiseo_add_metabox');
add_action('save_post', 'aiseo_save_metabox_data');
add_action('wp_head', 'aiseo_inject_meta_tags', 1);
add_action('wp_footer', 'aiseo_inject_schema');
add_action('init', 'aiseo_add_sitemap_rewrite');
add_action('wp_ajax_aiseo_generate_title', 'aiseo_ajax_generate_title');
add_action('wp_ajax_aiseo_generate_description', 'aiseo_ajax_generate_description');
add_action('wp_ajax_aiseo_analyze_content', 'aiseo_ajax_analyze_content');
```

### Filters
```php
add_filter('wp_title', 'aiseo_filter_title', 10, 2);
add_filter('document_title_parts', 'aiseo_filter_document_title');
add_filter('the_content', 'aiseo_add_schema_to_content', 20);
```

---

## Security Measures

1. **API Key Protection**
   - Encrypt API key in database
   - Never expose in frontend code
   - Use nonces for all AJAX requests
   - Capability checks (manage_options)

2. **Input Sanitization**
   - `sanitize_text_field()` for text inputs
   - `sanitize_textarea_field()` for textareas
   - `esc_url()` for URLs
   - `wp_kses_post()` for HTML content

3. **Output Escaping**
   - `esc_html()` for HTML output
   - `esc_attr()` for attributes
   - `esc_url()` for URLs
   - `wp_json_encode()` for JSON

4. **Nonce Verification**
   - All AJAX requests require nonce
   - Settings form uses nonce
   - Metabox save uses nonce

5. **Capability Checks**
   - Admin pages: `manage_options`
   - Metabox save: `edit_post`
   - AJAX actions: appropriate capabilities

---

## WordPress.org Submission Requirements

### Required Files
1. **readme.txt** - Properly formatted with:
   - Plugin name and description
   - Installation instructions
   - Frequently Asked Questions
   - Changelog
   - Screenshots description
   - External services disclosure (OpenAI API)

2. **LICENSE** - GPL-2.0-or-later full text

3. **Plugin header** in main file with all metadata

### Guidelines Compliance
- ✅ GPL-compatible license
- ✅ No obfuscated code
- ✅ External service documented (OpenAI)
- ✅ User consent for API usage (opt-in via API key)
- ✅ No tracking without consent
- ✅ Use WordPress default libraries
- ✅ Proper sanitization and escaping
- ✅ No executable code from external sources
- ✅ Proper version numbering

### External Services Documentation
Must include in readme.txt:
```
== External Services ==

This plugin uses the OpenAI API to provide AI-powered SEO features.

Service Used: OpenAI API
Purpose: Generate SEO titles, meta descriptions, content analysis, and schema markup
Data Sent: Post content, titles, and user-specified keywords
Privacy Policy: https://openai.com/policies/privacy-policy
Terms of Use: https://openai.com/policies/terms-of-use

The plugin only connects to OpenAI when you provide an API key and use AI generation features.
No data is sent without your explicit action.
```

---

## User Interface & Experience

### Admin Settings Page
- Clean, modern design matching WordPress admin UI
- Tabbed interface for different settings sections
- Inline help text and tooltips
- Save success/error notifications
- API key validation on save

### Post Editor Metabox
- Collapsible sections for better organization
- Real-time character counters
- Color-coded SEO analysis (traffic light system)
- Loading states for AI generation
- Preview panels for search results and social media
- Clear call-to-action buttons

### Visual Indicators
- 🔴 Red: Critical issues (score 0-49)
- 🟠 Orange: Needs improvement (score 50-79)
- 🟢 Green: Good/Excellent (score 80-100)

---

## Performance Optimization (Detailed)

### 1. **Asset Loading Optimization**

```php
// Conditional script loading
function aiseo_enqueue_admin_scripts($hook) {
    // Only load on AISEO settings page
    if ($hook === 'settings_page_aiseo') {
        wp_enqueue_script('aiseo-admin', AISEO_PLUGIN_URL . 'js/aiseo-admin.min.js', ['jquery'], AISEO_VERSION, true);
        wp_enqueue_style('aiseo-admin', AISEO_PLUGIN_URL . 'css/aiseo-admin.min.css', [], AISEO_VERSION);
    }
    
    // Only load on post edit screens
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        wp_enqueue_script('aiseo-metabox', AISEO_PLUGIN_URL . 'js/aiseo-metabox.min.js', ['jquery'], AISEO_VERSION, true);
        wp_enqueue_style('aiseo-metabox', AISEO_PLUGIN_URL . 'css/aiseo-metabox.min.css', [], AISEO_VERSION);
        
        // Localize script with only necessary data
        wp_localize_script('aiseo-metabox', 'aiseoData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aiseo_metabox_nonce'),
            'postId' => get_the_ID(),
            'i18n' => [
                'generating' => __('Generating...', 'aiseo'),
                'error' => __('Error occurred', 'aiseo')
            ]
        ]);
    }
}

// Defer non-critical scripts
function aiseo_defer_scripts($tag, $handle) {
    if (strpos($handle, 'aiseo-') !== false && !is_admin()) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'aiseo_defer_scripts', 10, 2);
```

### 2. **Database Query Optimization**

```php
// Efficient meta query with caching
function aiseo_get_post_meta_cached($post_id, $key) {
    $cache_key = 'aiseo_meta_' . $post_id . '_' . $key;
    $value = wp_cache_get($cache_key, 'aiseo_meta');
    
    if (false === $value) {
        $value = get_post_meta($post_id, $key, true);
        wp_cache_set($cache_key, $value, 'aiseo_meta', 3600);
    }
    
    return $value;
}

// Batch meta updates
function aiseo_update_post_meta_batch($post_id, $meta_data) {
    global $wpdb;
    
    // Use single query for multiple meta updates
    $values = [];
    foreach ($meta_data as $key => $value) {
        $values[] = $wpdb->prepare(
            "(%d, %s, %s)",
            $post_id,
            $key,
            maybe_serialize($value)
        );
    }
    
    if (!empty($values)) {
        $query = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) 
                  VALUES " . implode(',', $values) . "
                  ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";
        $wpdb->query($query);
        
        // Clear cache
        foreach ($meta_data as $key => $value) {
            wp_cache_delete('aiseo_meta_' . $post_id . '_' . $key, 'aiseo_meta');
        }
    }
}

// Optimized query for posts needing SEO analysis
function aiseo_get_posts_needing_analysis($limit = 10) {
    global $wpdb;
    
    $query = $wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
             AND pm.meta_key = '_aiseo_last_analyzed'
         WHERE p.post_status = 'publish'
         AND p.post_type IN ('post', 'page')
         AND (pm.meta_value IS NULL OR pm.meta_value < DATE_SUB(NOW(), INTERVAL 7 DAY))
         ORDER BY p.post_modified DESC
         LIMIT %d",
        $limit
    );
    
    return $wpdb->get_col($query);
}
```

### 3. **Async Processing with WP-Cron**

```php
// Register cron jobs
function aiseo_schedule_cron_jobs() {
    // Cache warming - daily at 3 AM
    if (!wp_next_scheduled('aiseo_cache_warming')) {
        wp_schedule_event(strtotime('03:00:00'), 'daily', 'aiseo_cache_warming');
    }
    
    // Process request queue - every 5 minutes
    if (!wp_next_scheduled('aiseo_process_queue')) {
        wp_schedule_event(time(), 'aiseo_five_minutes', 'aiseo_process_queue');
    }
    
    // Cleanup old logs - weekly
    if (!wp_next_scheduled('aiseo_cleanup_logs')) {
        wp_schedule_event(time(), 'weekly', 'aiseo_cleanup_logs');
    }
}

// Add custom cron interval
function aiseo_cron_intervals($schedules) {
    $schedules['aiseo_five_minutes'] = [
        'interval' => 300,
        'display' => __('Every 5 Minutes', 'aiseo')
    ];
    return $schedules;
}
add_filter('cron_schedules', 'aiseo_cron_intervals');

// Process queue in background
function aiseo_process_request_queue() {
    global $wpdb;
    
    // Get next 10 queued requests
    $requests = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}aiseo_request_queue
         WHERE status = 'queued'
         AND scheduled_at <= NOW()
         ORDER BY priority DESC, scheduled_at ASC
         LIMIT 10"
    );
    
    foreach ($requests as $request) {
        // Mark as processing
        $wpdb->update(
            $wpdb->prefix . 'aiseo_request_queue',
            ['status' => 'processing'],
            ['id' => $request->id]
        );
        
        // Process request
        $result = aiseo_process_queued_request($request);
        
        // Update status
        $wpdb->update(
            $wpdb->prefix . 'aiseo_request_queue',
            [
                'status' => $result['success'] ? 'completed' : 'failed',
                'processed_at' => current_time('mysql'),
                'result' => json_encode($result)
            ],
            ['id' => $request->id]
        );
    }
}
add_action('aiseo_process_queue', 'aiseo_process_request_queue');
```

### 4. **Debouncing & Throttling**

```javascript
// JavaScript debouncing for real-time analysis
let analysisTimeout;

function debounceAnalysis(content, delay = 1000) {
    clearTimeout(analysisTimeout);
    
    analysisTimeout = setTimeout(() => {
        performContentAnalysis(content);
    }, delay);
}

// Throttle API requests
let lastApiCall = 0;
const API_THROTTLE_MS = 2000; // Minimum 2 seconds between calls

function throttledApiCall(apiFunction, ...args) {
    const now = Date.now();
    const timeSinceLastCall = now - lastApiCall;
    
    if (timeSinceLastCall < API_THROTTLE_MS) {
        const waitTime = API_THROTTLE_MS - timeSinceLastCall;
        return new Promise(resolve => {
            setTimeout(() => {
                lastApiCall = Date.now();
                resolve(apiFunction(...args));
            }, waitTime);
        });
    }
    
    lastApiCall = now;
    return apiFunction(...args);
}
```

### 5. **Image Optimization**

```php
// Lazy load images in admin
function aiseo_lazy_load_images($content) {
    if (is_admin()) {
        return $content;
    }
    
    // Add loading="lazy" to images
    $content = preg_replace(
        '/<img((?![^>]*loading=)[^>]*)>/i',
        '<img$1 loading="lazy">',
        $content
    );
    
    return $content;
}

// Optimize featured images for social sharing
function aiseo_optimize_og_image($image_url) {
    // Use WordPress image sizes
    $image_id = attachment_url_to_postid($image_url);
    
    if ($image_id) {
        // Get optimized size (1200x630 for OG)
        $image = wp_get_attachment_image_src($image_id, 'large');
        return $image ? $image[0] : $image_url;
    }
    
    return $image_url;
}
```

### 6. **Memory Management**

```php
// Increase memory limit for large operations
function aiseo_increase_memory_limit() {
    if (function_exists('wp_raise_memory_limit')) {
        wp_raise_memory_limit('aiseo');
    }
}

// Clean up large variables
function aiseo_cleanup_memory($large_variable) {
    unset($large_variable);
    if (function_exists('gc_collect_cycles')) {
        gc_collect_cycles();
    }
}

// Process large datasets in chunks
function aiseo_process_posts_in_chunks($post_ids, $callback, $chunk_size = 50) {
    $chunks = array_chunk($post_ids, $chunk_size);
    
    foreach ($chunks as $chunk) {
        foreach ($chunk as $post_id) {
            call_user_func($callback, $post_id);
        }
        
        // Free memory between chunks
        aiseo_cleanup_memory($chunk);
        
        // Prevent timeout
        if (function_exists('set_time_limit')) {
            set_time_limit(30);
        }
    }
}
```

### 7. **CDN Integration**

```php
// Support for CDN URLs
function aiseo_get_asset_url($asset_path) {
    $base_url = AISEO_PLUGIN_URL;
    
    // Check if CDN is configured
    $cdn_url = apply_filters('aiseo_cdn_url', '');
    
    if (!empty($cdn_url)) {
        $base_url = trailingslashit($cdn_url) . 'wp-content/plugins/aiseo/';
    }
    
    return $base_url . ltrim($asset_path, '/');
}
```

### 8. **Performance Monitoring**

```php
// Track execution time
function aiseo_track_performance($operation_name, $callback) {
    $start_time = microtime(true);
    $start_memory = memory_get_usage();
    
    $result = call_user_func($callback);
    
    $end_time = microtime(true);
    $end_memory = memory_get_usage();
    
    $duration = ($end_time - $start_time) * 1000; // milliseconds
    $memory_used = $end_memory - $start_memory;
    
    // Log if slow
    if ($duration > 1000) { // > 1 second
        aiseo_log('WARNING', 'performance', "Slow operation: {$operation_name}", [
            'duration_ms' => $duration,
            'memory_bytes' => $memory_used
        ]);
    }
    
    return $result;
}
```

---

## Error Handling

1. **API Errors**
   - Graceful degradation if API fails
   - User-friendly error messages
   - Fallback to manual input
   - Log errors for debugging

2. **Validation Errors**
   - Inline validation messages
   - Prevent invalid data storage
   - Clear error descriptions

3. **Edge Cases**
   - Handle missing API key
   - Handle empty content
   - Handle network timeouts
   - Handle rate limits

---

## Testing Strategy

### Manual Testing
- [ ] API key validation
- [ ] Meta tag generation
- [ ] Schema markup validation
- [ ] Sitemap generation
- [ ] Content analysis accuracy
- [ ] Social media preview
- [ ] Cross-browser compatibility
- [ ] Mobile responsiveness

### Automated Testing (Future)
- Unit tests for helper functions
- Integration tests for API calls
- E2E tests for user workflows

---

## Internationalization (i18n)

- All strings wrapped in translation functions
- Text domain: `aiseo`
- POT file generation for translators
- RTL support consideration

**Translation Functions:**
```php
__('Text', 'aiseo')           // Translate and return
_e('Text', 'aiseo')           // Translate and echo
esc_html__('Text', 'aiseo')   // Translate, escape, return
esc_html_e('Text', 'aiseo')   // Translate, escape, echo
```

---

## Future Enhancements (v2.0+)

1. **Advanced Features**
   - Competitor analysis
   - Keyword research tool
   - Backlink monitoring
   - Rank tracking
   - Content suggestions

2. **AI Improvements**
   - Support for multiple AI providers (Anthropic, Cohere)
   - Fine-tuned models for SEO
   - Multilingual support

3. **Integrations**
   - Google Search Console integration
   - Google Analytics integration
   - WooCommerce product SEO

4. **Premium Features**
   - Advanced schema types
   - Video SEO
   - Local SEO
   - Multiple focus keywords

---

## Development Workflow

### Version Control
```bash
git init
git add .
git commit -m "Initial commit"
```

### Coding Standards
- Follow WordPress Coding Standards
- Use PHPCS for validation
- Comment complex logic
- Document all functions

### Release Process
1. Update version numbers
2. Update changelog in readme.txt
3. Test all features
4. Create git tag
5. Submit to WordPress.org SVN

---

## Support & Documentation

### User Documentation
- Installation guide
- Configuration guide
- Feature tutorials
- FAQ section
- Troubleshooting guide

### Developer Documentation
- Code comments
- Function documentation
- Hook reference
- Filter reference
- API documentation

---

## Conclusion

This architecture provides a comprehensive, scalable, and WordPress.org submission-ready foundation for the AISEO plugin. It follows WordPress best practices, implements robust security measures, and delivers a user-friendly experience while leveraging AI to automate and optimize SEO tasks.

The modular structure allows for easy maintenance, testing, and future enhancements while keeping the codebase clean and organized.
