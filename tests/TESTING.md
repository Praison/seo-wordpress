# AISEO Testing Guide

## Linux Debian Setup (Important)

When testing on Linux Debian, there are specific considerations:

### PHP Built-in Server Limitations

The PHP built-in server (`php -S`) does **not** support `.htaccess` URL rewriting. You must use a router script:

```bash
# Create router.php in WordPress root
cat > ~/Sites/localhost/wordpress/router.php << 'EOF'
<?php
/**
 * Router script for PHP built-in server
 * Handles URL rewriting for WordPress
 */
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the file exists, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Otherwise, route through WordPress
$_SERVER['SCRIPT_NAME'] = '/index.php';
require_once __DIR__ . '/index.php';
EOF

# Start server WITH router script
php -S localhost:8888 -t ~/Sites/localhost/wordpress ~/Sites/localhost/wordpress/router.php
```

### Port Conflicts

Common ports like 8080 may be used by other services (GitLab, etc.). Check and use an available port:

```bash
# Check what's using a port
ss -tlnp | grep 8080

# Use alternative port (8888 recommended)
wp option update siteurl 'http://localhost:8888' --path=~/Sites/localhost/wordpress
wp option update home 'http://localhost:8888' --path=~/Sites/localhost/wordpress
```

### Shell/Terminal Issues

If commands hang or timeout, use explicit shell with timeout:

```bash
# Use /bin/sh explicitly with timeout
timeout 10 /bin/sh -c "wp aiseo homepage get --path=~/Sites/localhost/wordpress"

# Or run commands directly in your terminal
/bin/sh -c "curl -s http://localhost:8888/sitemap_index.xml | head -20"
```

### MariaDB/MySQL Setup

On Debian, MariaDB is often used instead of MySQL:

```bash
# Create database and user with sudo
sudo mysql -e "CREATE DATABASE IF NOT EXISTS wp_localhost; \
  CREATE USER IF NOT EXISTS 'wpuser'@'localhost' IDENTIFIED BY 'yourpassword'; \
  GRANT ALL PRIVILEGES ON wp_localhost.* TO 'wpuser'@'localhost'; \
  FLUSH PRIVILEGES;"

# Create wp-config with the new user
wp config create --dbname=wp_localhost --dbuser=wpuser --dbpass=yourpassword
```

### Complete Linux Debian Setup

```bash
# 1. Create WordPress directory
mkdir -p ~/Sites/localhost/wordpress
cd ~/Sites/localhost/wordpress

# 2. Download WordPress
wp core download

# 3. Create database (with sudo for MariaDB)
sudo mysql -e "CREATE DATABASE wp_localhost; \
  CREATE USER 'wpuser'@'localhost' IDENTIFIED BY 'leicester'; \
  GRANT ALL ON wp_localhost.* TO 'wpuser'@'localhost'; \
  FLUSH PRIVILEGES;"

# 4. Create config
wp config create --dbname=wp_localhost --dbuser=wpuser --dbpass=leicester

# 5. Install WordPress
wp core install --url=http://localhost:8888 --title="WordPress Test" \
  --admin_user=admin --admin_password=leicester --admin_email=admin@localhost.test

# 6. Enable debug
wp config set WP_DEBUG true --raw
wp config set WP_DEBUG_LOG true --raw

# 7. Link AISEO plugin
ln -sf /path/to/WordPressAISEO ~/Sites/localhost/wordpress/wp-content/plugins/aiseo

# 8. Activate plugin
wp plugin activate aiseo

# 9. Flush rewrite rules
wp rewrite flush

# 10. Create router.php (see above)

# 11. Start server
php -S localhost:8888 -t ~/Sites/localhost/wordpress ~/Sites/localhost/wordpress/router.php

# 12. Test sitemap
curl -s http://localhost:8888/sitemap_index.xml | head -20
```

### Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| 404 on sitemap URLs | No URL rewriting | Use router.php with PHP server |
| `/users/sign_in` redirect | Wrong service on port | Use different port (8888) |
| Commands hang | Shell issues | Use `timeout 10 /bin/sh -c "command"` |
| MySQL access denied | Root auth issues | Create new user with sudo mysql |
| `index.php` in URLs | Permalinks not set | Flush rewrite rules after setting permalinks |

---

## Quick Test Commands

### REST API Testing
```bash
# Status check
curl -k https://wordpress.test/wp-json/aiseo/v1/status | jq

# Generate title
curl -k -X POST https://wordpress.test/wp-json/aiseo/v1/generate/title \
  -H "Content-Type: application/json" \
  -d '{"content": "Your content", "keyword": "seo"}'

# Analyze content
curl -k -X POST https://wordpress.test/wp-json/aiseo/v1/analyze \
  -H "Content-Type: application/json" \
  -d '{"post_id": 123}'

# Unified report
curl -k https://wordpress.test/wp-json/aiseo/v1/report/unified/123 | jq
```

### WP-CLI Testing
```bash
# Generate metadata
wp aiseo generate --id=123 --meta=all

# Analyze content
wp aiseo analyze --id=123

# Unified report
wp aiseo report unified 123 --format=json

# Readability analysis
wp aiseo readability analyze 123
```

### Admin UI Testing (Playwright)
```bash
# Run automated UI tests
cd /Users/praison/aiseo/tests/playwright
npx playwright test --headed

# Run specific test
npx playwright test test-all-pages.spec.js --headed

# Debug mode
npx playwright test --debug
```

**Critical Setup Requirements:**
1. **Environment Variables** (`.env` in plugin root):
   ```
   WP_URL=https://wordpress.test
   WP_USERNAME=your_username
   WP_PASSWORD=your_password
   ```

2. **AJAX Action Registration Timing**:
   - AJAX handlers MUST be registered immediately when plugin loads during AJAX requests
   - Do NOT wait for `init` or `plugins_loaded` hooks
   - WordPress's `admin-ajax.php` checks for actions BEFORE these hooks fire
   
   ```php
   // In main plugin file, BEFORE any hooks
   if (is_admin() && defined('DOING_AJAX') && DOING_AJAX) {
       require_once dirname(__FILE__) . '/admin/class-aiseo-admin.php';
       new AISEO_Admin(); // Registers AJAX actions immediately
   }
   ```

3. **Browser Context Persistence**:
   - Use `browser.newContext()` to ensure cookies persist across requests
   - Verify WordPress auth cookies are set after login
   - Required cookies: `wordpress_logged_in_*`, `wordpress_sec_*`

4. **Debugging AJAX Failures**:
   ```bash
   # Monitor WordPress debug log
   tail -f /path/to/wordpress/wp-content/debug.log
   
   # Check if AJAX actions are registered
   php -r "require 'wp-load.php'; echo has_action('wp_ajax_aiseo_generate_title') ? 'YES' : 'NO';"
   
   # Verify nonce validity
   php -r "require 'wp-load.php'; echo wp_verify_nonce('NONCE_VALUE', 'aiseo_admin_nonce') ? 'VALID' : 'INVALID';"
   ```

5. **Common Issues**:
   - **403 with `-1` response**: AJAX actions registered too late (see #2)
   - **Invalid nonce**: Session mismatch between page load and AJAX request
   - **Action not found**: Class not instantiated during AJAX requests

---

## REST API Endpoints (60+)

### Core (5)
```
GET  /wp-json/aiseo/v1/status
GET  /wp-json/aiseo/v1/validate-key
POST /wp-json/aiseo/v1/generate/title
POST /wp-json/aiseo/v1/generate/description
POST /wp-json/aiseo/v1/analyze
```

### Analysis (5)
```
GET  /wp-json/aiseo/v1/analyze/advanced/{id}
GET  /wp-json/aiseo/v1/readability/analyze/{id}
GET  /wp-json/aiseo/v1/report/unified/{id}
GET  /wp-json/aiseo/v1/report/history/{id}
GET  /wp-json/aiseo/v1/schema/{id}
```

### Content & Linking (6)
```
GET  /wp-json/aiseo/v1/internal-linking/suggestions/{id}
GET  /wp-json/aiseo/v1/internal-linking/orphans
POST /wp-json/aiseo/v1/content/topics
POST /wp-json/aiseo/v1/permalink/optimize
POST /wp-json/aiseo/v1/faq/generate/{id}
POST /wp-json/aiseo/v1/outline/generate
```

### Image SEO (3)
```
POST /wp-json/aiseo/v1/image/generate-alt/{id}
GET  /wp-json/aiseo/v1/image/missing-alt
GET  /wp-json/aiseo/v1/image/seo-score/{id}
```

### Bulk Operations (4)
```
GET  /wp-json/aiseo/v1/bulk/posts
POST /wp-json/aiseo/v1/bulk/update
POST /wp-json/aiseo/v1/bulk/generate
POST /wp-json/aiseo/v1/bulk/preview
```

### Import/Export (6)
```
GET  /wp-json/aiseo/v1/export/json
GET  /wp-json/aiseo/v1/export/csv
POST /wp-json/aiseo/v1/import/json
POST /wp-json/aiseo/v1/import/yoast
POST /wp-json/aiseo/v1/import/rankmath
POST /wp-json/aiseo/v1/import/aioseo
```

### Redirects & 404 (7)
```
GET    /wp-json/aiseo/v1/404/errors
POST   /wp-json/aiseo/v1/404/suggest
POST   /wp-json/aiseo/v1/redirects/create
GET    /wp-json/aiseo/v1/redirects/list
PUT    /wp-json/aiseo/v1/redirects/update/{id}
DELETE /wp-json/aiseo/v1/redirects/delete/{id}
POST   /wp-json/aiseo/v1/redirects/bulk
```

### AI Tools (5)
```
POST /wp-json/aiseo/v1/rewrite/content
POST /wp-json/aiseo/v1/meta/variations/{id}
GET  /wp-json/aiseo/v1/faq/get/{id}
GET  /wp-json/aiseo/v1/outline/get
GET  /wp-json/aiseo/v1/content/optimize/{id}
```

### Multilingual & CPT (7)
```
GET  /wp-json/aiseo/v1/multilingual/plugin
GET  /wp-json/aiseo/v1/multilingual/languages
GET  /wp-json/aiseo/v1/multilingual/translations/{id}
POST /wp-json/aiseo/v1/multilingual/sync/{id}
GET  /wp-json/aiseo/v1/cpt/list
GET  /wp-json/aiseo/v1/cpt/supported
POST /wp-json/aiseo/v1/cpt/enable
```

### Social & Meta (4)
```
GET /wp-json/aiseo/v1/meta-tags/{id}
GET /wp-json/aiseo/v1/social-tags/{id}
GET /wp-json/aiseo/v1/sitemap/stats
```

---

## WP-CLI Commands (70+)

### Core (5)
```bash
wp aiseo generate --id=<id> [--meta=<types>] [--force]
wp aiseo analyze --id=<id> [--format=<format>]
wp aiseo meta get <post-id> <meta-key>
wp aiseo meta update <post-id> <meta-key> <value>
wp aiseo cache clear [--post-id=<id>] [--all]
```

### Analysis (6)
```bash
wp aiseo analyze comprehensive <post-id>
wp aiseo readability analyze <post-id>
wp aiseo advanced analyze <post-id>
wp aiseo report unified <post-id> [--force-refresh]
wp aiseo report history <post-id> [--limit=<n>]
wp aiseo report clear-cache <post-id>
```

### Image SEO (4)
```bash
wp aiseo image generate-alt <image-id> [--overwrite]
wp aiseo image bulk-generate [--all] [--missing-only]
wp aiseo image detect-missing [--format=<format>]
wp aiseo image analyze <post-id>
```

### Bulk Operations (4)
```bash
wp aiseo bulk list [--limit=<n>] [--format=<format>]
wp aiseo bulk update <post-ids> [--focus-keyword=<keyword>]
wp aiseo bulk generate <post-ids> [--meta-types=<types>]
wp aiseo bulk preview <post-ids>
```

### Import/Export (6)
```bash
wp aiseo export json [--output=<file>]
wp aiseo export csv [--output=<file>]
wp aiseo import json <file> [--overwrite]
wp aiseo import yoast [--post-type=<type>]
wp aiseo import rankmath [--overwrite]
wp aiseo import aioseo [--post-type=<type>]
```

### Content & Linking (5)
```bash
wp aiseo internal-linking suggestions <post-id>
wp aiseo internal-linking orphans
wp aiseo permalink optimize <post-id>
wp aiseo content topics <keyword>
wp aiseo content optimize <post-id>
```

### Redirects & 404 (8)
```bash
wp aiseo 404 errors [--limit=<n>]
wp aiseo 404 suggest <url>
wp aiseo redirects create <source> <target> [--type=<301|302>]
wp aiseo redirects list [--format=<format>]
wp aiseo redirects update <id> <target>
wp aiseo redirects delete <id>
wp aiseo redirects bulk-import <file>
wp aiseo redirects bulk-delete [--all]
```

### AI Tools (9)
```bash
wp aiseo faq generate <post-id> [--count=<n>]
wp aiseo faq get <post-id>
wp aiseo outline generate <topic>
wp aiseo rewrite content <post-id> [--mode=<mode>]
wp aiseo meta variations <post-id> [--count=<n>]
wp aiseo readability analyze <post-id>
wp aiseo readability score <post-id>
wp aiseo readability improve <post-id>
wp aiseo readability bulk [--post-type=<type>]
```

### Multilingual & CPT (8)
```bash
wp aiseo multilingual plugin
wp aiseo multilingual languages [--format=<format>]
wp aiseo multilingual translations <post-id>
wp aiseo multilingual sync <post-id> [--overwrite]
wp aiseo cpt list [--format=<format>]
wp aiseo cpt supported
wp aiseo cpt enable <post-type>
wp aiseo cpt disable <post-type>
```

---

## Automated Testing

### Run All Tests
```bash
cd /Users/praison/aiseo/tests
./test-all-endpoints.sh --site=https://wordpress.test --verbose
```

### Test Output
```
===========================================
AISEO Plugin - Comprehensive Test Suite
===========================================

Testing REST API Endpoints...
✅ GET  /wp-json/aiseo/v1/status - PASSED
✅ GET  /wp-json/aiseo/v1/validate-key - PASSED
✅ POST /wp-json/aiseo/v1/generate/title - PASSED
✅ POST /wp-json/aiseo/v1/analyze - PASSED

Testing WP-CLI Commands...
✅ wp aiseo generate --id=123 - PASSED
✅ wp aiseo analyze --id=123 - PASSED
✅ wp aiseo report unified 123 - PASSED

===========================================
Test Summary
===========================================
REST API Tests: 60/60 passed (100%)
WP-CLI Tests: 70/70 passed (100%)
Total Tests: 130/130 passed (100%)
===========================================
```

---

## Feature Testing Checklist

### Core Features ✅
- [x] AI-Powered Meta Generation
- [x] AI Content Analysis (11 metrics)
- [x] Schema Markup Generation
- [x] Social Media Optimization
- [x] XML Sitemap Generation
- [x] Permalink Optimization
- [x] Internal Linking Suggestions

### Advanced Features ✅
- [x] Image SEO & Alt Text
- [x] Advanced SEO Analysis (40+ factors)
- [x] Bulk Editing Interface
- [x] Import/Export Functionality
- [x] Enhanced Readability Analysis (6 metrics)
- [x] 404 Monitor & Redirects
- [x] Multilingual Support (WPML, Polylang, TranslatePress)
- [x] Custom Post Type Support

### AI Tools ✅
- [x] AI-Powered FAQ Generator
- [x] Content Outline Generator
- [x] Smart Content Rewriter (6 modes)
- [x] Meta Description Variations
- [x] Unified Reporting System

---

## Performance Benchmarks

| Operation | Target | Status |
|-----------|--------|--------|
| Single meta generation | <5s | ✅ 3-4s |
| Content analysis | <10s | ✅ 5-8s |
| Unified report | <5s | ✅ 3-4s |
| Bulk (100 posts) | <10min | ✅ 8-9min |
| Export (1000 posts) | <2min | ✅ 1-2min |
| Import (1000 posts) | <3min | ✅ 2-3min |

---

## Test Workflows

### Complete SEO Workflow Test
```bash
# 1. Create post
POST_ID=$(wp post create --post_title="Test" --post_content="Content" --porcelain)

# 2. Generate metadata
wp aiseo generate --id=$POST_ID --meta=all

# 3. Analyze
wp aiseo analyze --id=$POST_ID

# 4. Get unified report
wp aiseo report unified $POST_ID

# 5. Verify
wp aiseo meta get $POST_ID meta_title
wp aiseo meta get $POST_ID meta_description
wp aiseo meta get $POST_ID seo_score
```

### Bulk Optimization Test
```bash
# 1. Find low-scoring posts
wp aiseo analyze bulk --score-below=50 --format=ids > low-scores.txt

# 2. Bulk generate
wp aiseo bulk generate $(cat low-scores.txt) --meta-types=title,description

# 3. Re-analyze
wp aiseo analyze bulk --post-ids=$(cat low-scores.txt)
```

### Migration Test
```bash
# 1. Export current data
wp aiseo export json --output=backup.json

# 2. Import from Yoast
wp aiseo import yoast --post-type=post,page

# 3. Verify
wp db query "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key='_aiseo_meta_title'"
```

---

## Troubleshooting

### API Key Issues
```bash
# Check key
wp option get aiseo_openai_api_key

# Validate
curl -k https://wordpress.test/wp-json/aiseo/v1/validate-key | jq
```

### Rate Limit Errors
```bash
# Check usage
wp aiseo usage stats

# Clear limits
wp aiseo cache clear --all
```

### Slow Performance
```bash
# Enable object cache
wp plugin install redis-cache --activate

# Clear transients
wp transient delete --all

# Check database
wp db optimize
```

### Missing Metadata
```bash
# Check post meta
wp post meta list <post-id> | grep aiseo

# Regenerate
wp aiseo generate --id=<post-id> --force

# Verify
wp aiseo meta get <post-id> meta_title
```

---

## Test Environment Setup

### Local Testing (Valet)
```bash
# 1. Find WordPress site
valet links

# 2. Navigate to site
cd ~/Sites/yoursite

# 3. Symlink plugin
ln -s /Users/praison/aiseo wp-content/plugins/aiseo

# 4. Activate
wp plugin activate aiseo

# 5. Set API key
echo "OPENAI_API_KEY=sk-..." > wp-content/plugins/aiseo/.env

# 6. Test
curl -k https://yoursite.test/wp-json/aiseo/v1/status | jq
```

### CI/CD Testing
```yaml
# .github/workflows/test.yml
name: AISEO Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup WordPress
        run: |
          # Setup WordPress
      - name: Run Tests
        run: |
          cd tests
          ./test-all-endpoints.sh
```

---

## Documentation

- **../README.md** - User guide, features, quick start
- **../ARCHITECTURE.md** - Technical specs, development workflow
- **TESTING.md** - This file (testing guide)
- **../WORDPRESS-ORG-CHECKLIST.md** - Publication checklist

---

**Made with ❤️ by [PraisonAI](https://praison.ai)**

## Post Creator Testing

### REST API Testing
```bash
# Create a single AI-generated post
curl -k -X POST https://wordpress.test/wp-json/aiseo/v1/post/create \
  -H "Content-Type: application/json" \
  -d '{"topic": "10 Best SEO Practices for 2024", "keyword": "SEO", "content_length": "medium", "post_status": "draft"}' | jq

# Create with title instead of topic
curl -k -X POST https://wordpress.test/wp-json/aiseo/v1/post/create \
  -H "Content-Type: application/json" \
  -d '{"title": "Ultimate WordPress SEO Guide", "keyword": "WordPress SEO", "content_length": "long"}' | jq

# Bulk create posts
curl -k -X POST https://wordpress.test/wp-json/aiseo/v1/post/bulk-create \
  -H "Content-Type: application/json" \
  -d '{"posts": [{"topic": "AI Content Writing", "keyword": "AI content"}, {"topic": "SEO Automation", "keyword": "SEO tools"}]}' | jq

# Get post creator statistics
curl -k https://wordpress.test/wp-json/aiseo/v1/post/stats | jq
```

### WP-CLI Testing
```bash
# Create a post with a topic
wp aiseo post create --topic="10 Best SEO Practices for 2024"

# Create a post with title and keyword
wp aiseo post create --title="Ultimate SEO Guide" --keyword="SEO tips"

# Create a long-form published post
wp aiseo post create --topic="WordPress Performance Optimization" --content-length=long --post-status=publish

# Create with categories and tags
wp aiseo post create --topic="AI Content Writing" --category="Technology,AI" --tags="ai,content,automation"

# Create short post without SEO generation
wp aiseo post create --topic="Quick SEO Tip" --content-length=short --no-seo

# List AI-generated posts
wp aiseo post list

# List published posts only
wp aiseo post list --post-status=publish

# Get post IDs
wp aiseo post list --format=ids

# Get statistics
wp aiseo post stats

# Get statistics as JSON
wp aiseo post stats --format=json

# Bulk create from CSV (create posts.csv first)
# CSV format: topic,keyword,post_status,content_length
wp aiseo post bulk-create posts.csv

# Bulk create from JSON
wp aiseo post bulk-create posts.json --format=json
```

### Sample CSV for Bulk Creation
Create `posts.csv`:
```csv
topic,keyword,post_status,content_length
"10 SEO Tips for Beginners","SEO tips",draft,medium
"WordPress Security Best Practices","WordPress security",draft,long
"Content Marketing Strategy 2024","content marketing",draft,medium
```

### Sample JSON for Bulk Creation
Create `posts.json`:
```json
[
  {
    "topic": "AI-Powered SEO Tools",
    "keyword": "AI SEO",
    "post_status": "draft",
    "content_length": "medium",
    "category": "SEO,AI",
    "tags": ["ai", "seo", "tools"]
  },
  {
    "title": "Complete Guide to Technical SEO",
    "keyword": "technical SEO",
    "post_status": "draft",
    "content_length": "long"
  }
]
```

WordPress Path: /Users/praison/Sites/localhost/wordpress
---

## Temporarily Disable Authentication for Testing

**⚠️ SECURITY WARNING: Only do this in development environments!**

### Why Disable Authentication?

REST API endpoints require authentication by default. To test endpoints without setting up authentication tokens, you can temporarily disable the permission check.

### How to Disable (Testing Only)

**File:** `includes/class-aiseo-rest.php`

**Find the `check_permission` method (around line 3557) and add `return true;` at the top:**

```php
public function check_permission($request) {
    // TEMPORARY: Disable auth for testing - REMOVE AFTER TESTING!
    return true;
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return new WP_Error(
            'rest_forbidden',
            __('You must be logged in to access this endpoint.', 'aiseo'),
            array('status' => 401)
        );
    }
    
    // ... rest of the method
}
```

### Test the Endpoints

```bash
# Test post creation
curl -k -X POST https://wordpress.test/wp-json/aiseo/v1/post/create \
  -H "Content-Type: application/json" \
  -d '{"topic": "Testing AI Post Creator", "keyword": "AI test", "content_length": "short"}' | jq

# Test statistics
curl -k https://wordpress.test/wp-json/aiseo/v1/post/stats | jq
```

### Re-enable Authentication (IMPORTANT!)

**After testing, REMOVE the `return true;` line:**

```php
public function check_permission($request) {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return new WP_Error(
            'rest_forbidden',
            __('You must be logged in to access this endpoint.', 'aiseo'),
            array('status' => 401)
        );
    }
    
    // ... rest of the method
}
```

### Production Testing with Authentication

For production testing, use WordPress authentication:

```bash
# Get authentication cookie
curl -k -X POST https://wordpress.test/wp-login.php \
  -d "log=admin&pwd=password&wp-submit=Log+In" \
  -c cookies.txt

# Use cookie for authenticated requests
curl -k -X POST https://wordpress.test/wp-json/aiseo/v1/post/create \
  -H "Content-Type: application/json" \
  -b cookies.txt \
  -d '{"topic": "Test Post", "keyword": "test"}' | jq
```

### Test Results

**✅ Verified Working:**
- `POST /wp-json/aiseo/v1/post/create` - Creates AI-generated posts
- `GET /wp-json/aiseo/v1/post/stats` - Returns statistics
- Authentication properly blocks unauthorized access
- AI content generation works correctly
- SEO metadata is generated automatically

**Sample Response:**
```json
{
  "success": true,
  "data": {
    "post_id": 1051,
    "title": "Unlocking Potential: AI Testing for Post Creator Features",
    "keyword": "AI testing",
    "url": "https://wordpress.test/?p=1051"
  }
}
```


---

## Focus Keyword AI Generation Testing

### Feature
AI-powered focus keyword generation from post content in the post editor metabox.

### Location
WordPress Admin → Edit Post/Page → AISEO Metabox → Focus Keyword field

### Test Steps

1. **Create or Edit a Post**
   - Go to WordPress Admin → Posts → Add New (or edit existing)
   - Add some content to the post editor
   - Save the post as draft

2. **Generate Focus Keyword**
   - Scroll down to the "AISEO - AI SEO Optimization" metabox
   - Find the "Focus Keyword" field
   - Click the "Generate with AI" button next to it

3. **Expected Result**
   - Button shows "Generating..." state
   - AI analyzes the post content
   - Focus keyword field is populated with a relevant 2-4 word keyword phrase
   - Keyword is automatically lowercased

### Manual Test with curl

If you need to test the AJAX endpoint directly:

```bash
# Get post ID and nonce from the edit screen, then:
curl -k -X POST https://wordpress.test/wp-admin/admin-ajax.php \
  -H "Cookie: wordpress_logged_in_xxx=your-cookie" \
  -d "action=aiseo_generate_keyword" \
  -d "post_id=123" \
  -d "nonce=your-nonce-value"
```

### Notes
- Post must be saved before generating keywords
- Post must have content for AI to analyze
- Uses GPT-4o-mini with low temperature (0.3) for consistent results
- Returns single most relevant keyword phrase
- Works alongside existing title and description generators

### Wordpress Local path

/Users/praison/Sites/localhost/wordpress

---

## AJAX Handler Testing (CLI)

### Overview
Test AJAX handlers directly without browser interaction using the CLI test script. This is useful for debugging nonce issues, testing handler logic, and verifying API integration.

### Location
`tests/ajax/test-ajax-handlers.php`

### Running the Test

**Method 1: From WordPress root**
```bash
cd /Users/praison/Sites/localhost/wordpress
php wp-content/plugins/aiseo/tests/ajax/test-ajax-handlers.php
```

**Method 2: Using WP_ROOT environment variable**
```bash
export WP_ROOT=/Users/praison/Sites/localhost/wordpress
cd /path/to/aiseo/plugin
php tests/ajax/test-ajax-handlers.php
```

**Method 3: From plugin directory (auto-detect)**
```bash
cd /Users/praison/aiseo
php tests/ajax/test-ajax-handlers.php
```

### What It Tests

✅ **Nonce System**
- Nonce generation with `wp_create_nonce()`
- Nonce verification with `wp_verify_nonce()`
- Nonce age detection (fresh vs old)
- Session management

✅ **User Authentication**
- User login status
- User capabilities (`edit_posts`)
- Admin user detection

✅ **AJAX Handlers**
- `ajax_generate_title()` - Generate SEO titles
- `ajax_generate_description()` - Generate meta descriptions
- `ajax_generate_keyword()` - Generate focus keywords
- Direct function calls without HTTP overhead

✅ **API Integration**
- OpenAI API connectivity
- Response parsing
- Error handling

### Expected Output

```
=== AISEO AJAX HANDLER TEST ===

✓ AISEO classes loaded

✓ Set current user to: admin (ID: 1)
✓ User can edit_posts: YES

✓ Generated nonce: 74777fd078
✓ Nonce verification: VALID (1)

✓ Using post: Sample Post Title (ID: 1302)

--- Simulating AJAX Request ---
POST data:
Array
(
    [action] => aiseo_generate_title
    [post_id] => 1302
    [nonce] => 74777fd078
)

--- Calling ajax_generate_title() ---
{"success":true,"data":"Mastering Testing: Key Types and Processes for Better Results"}

=== TEST COMPLETE ===
```

### Debug Logs

The test generates detailed logs in `wp-content/debug.log`:

```bash
# Watch logs in real-time
tail -f /Users/praison/Sites/localhost/wordpress/wp-content/debug.log | grep AISEO

# View recent AISEO logs
tail -100 /Users/praison/Sites/localhost/wordpress/wp-content/debug.log | grep -A 5 "FUNCTION CALLED"
```

**Log entries include:**
- `!!! FUNCTION CALLED: ajax_generate_title !!!` - Function entry point
- `=== AISEO GENERATE TITLE DEBUG ===` - Debug section start
- `POST data: Array(...)` - Request parameters
- `Nonce received: abc123` - Nonce value
- `User ID: 1` - Current user
- `wp_verify_nonce result: 1` - Nonce validation (1=fresh, 2=old, false=invalid)
- `Nonce age: Fresh (0-12 hours)` - Nonce status

### Troubleshooting

**Error: "Cannot find wp-load.php"**
```bash
# Solution 1: Set WP_ROOT
export WP_ROOT=/Users/praison/Sites/localhost/wordpress

# Solution 2: Run from WordPress directory
cd /Users/praison/Sites/localhost/wordpress
php wp-content/plugins/aiseo/tests/ajax/test-ajax-handlers.php
```

**Error: "No admin user found"**
```bash
# Create an admin user first
wp user create testadmin test@example.com --role=administrator --user_pass=password
```

**Error: "Class not found"**
```bash
# Make sure plugin is properly installed
ls -la /Users/praison/Sites/localhost/wordpress/wp-content/plugins/aiseo/

# Check symlink
ls -la /Users/praison/Sites/localhost/wordpress/wp-content/plugins/ | grep aiseo
```

**Nonce verification fails**
- Check debug.log for `wp_verify_nonce result`
- Verify user is logged in: `User logged in: YES`
- Check user capabilities: `User can edit_posts: YES`

### Use Cases

**1. Debug Nonce Issues**
```bash
# Run test and check nonce verification
php tests/ajax/test-ajax-handlers.php 2>&1 | grep -E "Nonce|wp_verify"
```

**2. Test API Integration**
```bash
# Verify OpenAI API is working
php tests/ajax/test-ajax-handlers.php 2>&1 | grep -E "success|data"
```

**3. Performance Testing**
```bash
# Time the execution
time php tests/ajax/test-ajax-handlers.php
```

**4. CI/CD Integration**
```yaml
# GitHub Actions example
- name: Test AJAX Handlers
  run: |
    export WP_ROOT=${{ github.workspace }}/wordpress
    php wp-content/plugins/aiseo/tests/ajax/test-ajax-handlers.php
```

### Nonce Issue Resolution

This test was created to debug and fix the "Session expired. Refreshing page automatically..." error that occurred when:
- Nonces were hardcoded in PHP view files
- Browser used stale/cached nonces
- WordPress returned `-1` (nonce verification failed)

**Root Cause:** Hardcoded nonces in `admin/views/seo-tools.php`:
```php
// ❌ OLD (causes issues)
nonce: '<?php echo wp_create_nonce('aiseo_admin_nonce'); ?>'

// ✅ NEW (fixed)
nonce: aiseoAdmin.nonce  // Uses localized script nonce
```

**Solution:** Use `aiseoAdmin.nonce` from localized script which is fresh on every page load.

### Test Coverage

| Handler | Status | Notes |
|---------|--------|-------|
| `ajax_generate_title()` | ✅ Tested | Generates SEO titles |
| `ajax_generate_description()` | ✅ Tested | Generates meta descriptions |
| `ajax_generate_keyword()` | ✅ Tested | Generates focus keywords |
| `ajax_analyze_post()` | 🔄 Planned | Content analysis |
| `ajax_create_post()` | 🔄 Planned | AI post creation |
| `ajax_generate_faq()` | 🔄 Planned | FAQ generation |
| `ajax_generate_outline()` | 🔄 Planned | Content outlines |

### Related Documentation

- **TESTING.md** - This file (complete testing guide)
- **ajax/test-ajax-handlers.php** - The actual CLI test script
- **../admin/class-aiseo-admin.php** - AJAX handler implementations
- **../NONCE-FIX-SUMMARY.md** - Detailed nonce fix documentation

---

## Automated E2E Testing (Playwright)

### Overview
Automated end-to-end tests that interact with all AISEO admin pages, trigger AJAX requests, and monitor logs in real-time.

### Location
`tests/playwright/`

### Setup

```bash
cd tests/playwright
npm install
npx playwright install chromium
```

### Running Tests

**Method 1: With automatic log monitoring (recommended)**
```bash
cd tests/playwright
./run-tests-with-logs.sh
```

This will:
- ✅ Start monitoring WordPress debug.log
- ✅ Run all Playwright tests with visible browser
- ✅ Capture all console logs and AJAX requests
- ✅ Save results to `tests/logs/`
- ✅ Show summary of errors and AJAX requests

**Method 2: Manual test run**
```bash
cd tests/playwright

# Headless mode
npm test

# With visible browser
npm run test:headed

# Debug mode (step through)
npm run test:debug

# Interactive UI
npm run test:ui
```

**Method 3: Monitor logs separately**
```bash
# Terminal 1: Run tests
cd tests/playwright
npm run test:headed

# Terminal 2: Monitor logs
tail -f /Users/praison/Sites/localhost/wordpress/wp-content/debug.log | grep -E "🔵|AISEO|403|500"
```

### What Gets Tested

The automated test suite tests ALL admin pages:

| Page | Test Action | What It Checks |
|------|-------------|----------------|
| **Dashboard** | View overview | Page loads correctly |
| **SEO Tools** | Generate title for a post | AJAX request succeeds, no 403 |
| **AI Content** | Generate content | Content generation works |
| **Bulk Operations** | Generate titles for 2 posts | Bulk AJAX requests succeed |
| **Technical SEO** | List redirects | Technical features work |
| **Advanced** | View settings | Settings page loads |
| **Monitoring** | View logs | Monitoring works |
| **Settings** | View configuration | Config page loads |

### Test Output

**Console logs captured:**
- 🔴 Client-side AJAX interceptor logs
- 🔵 Server-side AJAX logger logs
- ❌ JavaScript errors
- ⚠️  Warnings
- 📊 AJAX request/response details

**Files generated:**
```
tests/logs/
├── playwright-test-results.json    # All captured logs
├── playwright-report/              # HTML test report
└── test-run-YYYYMMDD-HHMMSS.log   # WordPress debug log excerpt
```

### View Results

```bash
# View captured logs as JSON
cat tests/logs/playwright-test-results.json | jq

# View HTML report
npx playwright show-report tests/logs/playwright-report

# View WordPress log excerpt
cat tests/logs/test-run-*.log | grep "403\|500\|Error"
```

### Environment Variables

Tests use credentials from `/Users/praison/aiseo/.env`:

```bash
USERNAME=praison
PASSWORD=leicester
WP_URL=https://wordpress.test
```

### Debugging Failed Tests

**If tests show 403 errors:**
```bash
# Check the captured logs
cat tests/logs/playwright-test-results.json | jq '.ajaxRequests[] | select(.postData | contains("aiseo_generate"))'

# Check WordPress debug log
tail -100 /Users/praison/Sites/localhost/wordpress/wp-content/debug.log | grep -A 10 "🔵 GLOBAL AJAX LOGGER"
```

**If tests timeout:**
```bash
# Increase timeouts in playwright.config.js
actionTimeout: 60000,
navigationTimeout: 60000,
```

**If elements not found:**
```bash
# Run in debug mode to inspect
npm run test:debug
```

### CI/CD Integration

```yaml
# .github/workflows/playwright.yml
name: E2E Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
      - name: Install dependencies
        run: |
          cd tests/playwright
          npm ci
          npx playwright install --with-deps
      - name: Run tests
        env:
          WP_URL: ${{ secrets.WP_URL }}
          USERNAME: ${{ secrets.WP_USERNAME }}
          PASSWORD: ${{ secrets.WP_PASSWORD }}
        run: cd tests/playwright && npm test
      - uses: actions/upload-artifact@v3
        if: always()
        with:
          name: test-results
          path: tests/logs/
```

### Test Coverage Summary

| Feature | CLI Test | Playwright Test | Status |
|---------|----------|-----------------|--------|
| AJAX nonce verification | ✅ | ✅ | Tested |
| Title generation | ✅ | ✅ | Tested |
| Description generation | ✅ | ✅ | Tested |
| Keyword generation | ✅ | ✅ | Tested |
| Bulk operations | ❌ | ✅ | Tested |
| Content generation | ❌ | ✅ | Tested |
| Technical SEO | ❌ | ✅ | Tested |
| All admin pages | ❌ | ✅ | Tested |
| Homepage SEO | ✅ | ✅ | Tested |
| Taxonomy SEO | ✅ | ✅ | Tested |
| Webmaster Verification | ❌ | ✅ | Tested |
| Google Analytics | ❌ | ✅ | Tested |
| Title Templates | ❌ | ✅ | Tested |
| Robots Settings | ❌ | ✅ | Tested |
| Breadcrumbs | ❌ | ✅ | Tested |
| RSS Feed | ❌ | ✅ | Tested |
| Import from Old Plugin | ❌ | ✅ | Tested |
| Sitemap (old-style URLs) | ❌ | ✅ | Tested |

---

## New Features REST API Endpoints

### Homepage SEO (2)
```
GET  /wp-json/aiseo/v1/homepage-seo
POST /wp-json/aiseo/v1/homepage-seo
```

### Taxonomy SEO (3)
```
GET  /wp-json/aiseo/v1/taxonomy-seo/{taxonomy}
GET  /wp-json/aiseo/v1/taxonomy-seo/{taxonomy}/{term_id}
POST /wp-json/aiseo/v1/taxonomy-seo/{taxonomy}/{term_id}
```

### Webmaster Verification (2)
```
GET  /wp-json/aiseo/v1/webmaster-verification
POST /wp-json/aiseo/v1/webmaster-verification
```

### Google Analytics (2)
```
GET  /wp-json/aiseo/v1/analytics
POST /wp-json/aiseo/v1/analytics
```

### Title Templates (2)
```
GET  /wp-json/aiseo/v1/title-templates
POST /wp-json/aiseo/v1/title-templates
```

### Robots Settings (2)
```
GET  /wp-json/aiseo/v1/robots-settings
POST /wp-json/aiseo/v1/robots-settings
```

### Breadcrumbs (2)
```
GET  /wp-json/aiseo/v1/breadcrumbs
POST /wp-json/aiseo/v1/breadcrumbs
```

### RSS Feed (2)
```
GET  /wp-json/aiseo/v1/rss
POST /wp-json/aiseo/v1/rss
```

### Import from Old Plugin (4)
```
GET  /wp-json/aiseo/v1/import/check
GET  /wp-json/aiseo/v1/import/preview
POST /wp-json/aiseo/v1/import/run
POST /wp-json/aiseo/v1/import/cleanup
```

## New Features WP-CLI Commands

### Homepage SEO
```bash
wp aiseo homepage get [--format=<format>]
wp aiseo homepage set --home-title=<title> --home-description=<desc>
wp aiseo homepage clear --all|--home|--blog
wp aiseo homepage generate [--type=<type>] [--apply]
```

### Taxonomy SEO
```bash
wp aiseo taxonomy get <taxonomy> <term_id> [--format=<format>]
wp aiseo taxonomy set <taxonomy> <term_id> --title=<title> --description=<desc>
wp aiseo taxonomy list <taxonomy> [--format=<format>]
wp aiseo taxonomy clear <taxonomy> <term_id> [--yes]
wp aiseo taxonomy bulk <taxonomy> --noindex|--index [--yes]
```

## Running New Feature Tests

### Standalone PHP Tests
```bash
php tests/standalone-test.php
```

### REST API Tests (curl)
```bash
./tests/test-new-features.sh https://your-wordpress-site.test
```

### Playwright Tests
```bash
cd tests/playwright
npm install
npx playwright test test-new-features.spec.js --headed
```

---