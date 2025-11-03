# AISEO - Uninstall & Testing Documentation

## Complete Uninstall Procedure

### uninstall.php Implementation

The plugin includes a comprehensive uninstall.php file that removes ALL data when the plugin is deleted through WordPress admin.

**What Gets Deleted:**
1. All plugin options (30+ settings)
2. All post meta fields (_aiseo_*)
3. All transients and cached data
4. 4 custom database tables (logs, failed_requests, usage_stats, request_queue)
5. All scheduled cron jobs
6. All user meta related to plugin
7. Rewrite rules flushed

**Multisite Support:**
- Cleans up data across all sites in network
- Removes site-specific options and meta

### Data Export Before Uninstall

Users can export their data before uninstalling:
- Settings configuration (JSON format)
- All SEO meta data for posts/pages
- Usage statistics
- Does NOT include API key (security)

## Testing Documentation

### Quick Setup for Local Testing (Valet)

#### 1. Find Your Active Valet WordPress Site

```bash
valet links
```

Example output:
```
+----------------+---------------------------+--------+
| Site           | URL                       | Secure |
+----------------+---------------------------+--------+
| christsong     | https://christsong.test   | true   |
| mywp           | http://mywp.test          | false  |
+----------------+---------------------------+--------+
```

Pick your WordPress site (e.g., `christsong`).

#### 2. Navigate to Your WordPress Directory

```bash
cd ~/Sites/christsong
```

Verify WordPress files exist:
```bash
ls -la | grep wp-config.php
ls -la wp-content/
```

#### 3. Symlink the AISEO Plugin

Create a symbolic link from your plugin folder to WordPress plugins folder:

```bash
ln -s /Users/praison/aiseo wp-content/plugins/aiseo
```

Verify the symlink:
```bash
ls -l wp-content/plugins | grep aiseo
```

You should see:
```bash
aiseo -> /Users/praison/aiseo
```

#### 4. Activate Plugin via WP-CLI

```bash
wp plugin activate aiseo
```

Confirm activation:
```bash
wp plugin list
```

✅ Should show `aiseo` as **Active**.

#### 5. Set API Key

The plugin will automatically load the API key from `.env` file if present. Alternatively, set it via WP-CLI:

```bash
wp option update aiseo_openai_api_key "your-api-key-here"
```

Or use the encrypted helper:
```bash
wp eval 'AISEO_Helpers::save_api_key("your-api-key-here"); echo "API key saved\n";'
```

### REST API Testing (Easiest Method)

REST API endpoints are the **fastest and easiest** way to test WordPress plugins:

✅ **Advantages:**
- No need to load WordPress admin
- Works from browser, curl, Postman, or any HTTP client
- Easy to automate and integrate into CI/CD
- Supports GET, POST, and authentication
- Instant feedback

#### Available REST API Endpoints

##### 1. Plugin Status Check

```bash
# Check if plugin is active and working
curl --insecure https://christsong.test/wp-json/aiseo/v1/status
```

**Expected Response:**
```json
{
  "success": true,
  "version": "1.0.0",
  "api_key_configured": true,
  "model": "gpt-4o-mini"
}
```

##### 2. Generate Meta Description

```bash
# Generate meta description for a post
curl --insecure -X POST https://christsong.test/wp-json/aiseo/v1/generate/description \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your post content here",
    "keyword": "wordpress seo"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "description": "Optimize your WordPress site with AI-powered SEO tools...",
  "length": 158
}
```

##### 3. Generate SEO Title

```bash
# Generate SEO title for a post
curl --insecure -X POST https://christsong.test/wp-json/aiseo/v1/generate/title \
  -H "Content-Type: application/json" \
  -d '{
    "content": "Your post content here",
    "keyword": "wordpress seo"
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "title": "WordPress SEO: Complete Guide to Optimization",
  "length": 52
}
```

##### 4. Analyze Content

```bash
# Analyze content for SEO quality
curl --insecure -X POST https://christsong.test/wp-json/aiseo/v1/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "post_id": 123
  }'
```

**Expected Response:**
```json
{
  "success": true,
  "score": 85,
  "analysis": {
    "keyword_density": {"score": 90, "status": "good"},
    "readability": {"score": 75, "status": "ok"},
    "content_length": {"score": 80, "status": "good"}
  }
}
```

##### 5. Validate API Key

```bash
# Test if API key is valid
curl --insecure https://christsong.test/wp-json/aiseo/v1/validate-key
```

**Expected Response:**
```json
{
  "success": true,
  "valid": true,
  "message": "API key is valid"
}
```

#### Testing with Browser

You can also test GET endpoints directly in your browser:

```
https://christsong.test/wp-json/aiseo/v1/status
https://christsong.test/wp-json/aiseo/v1/validate-key
```

#### Testing with Postman

1. Create a new request in Postman
2. Set URL: `https://christsong.test/wp-json/aiseo/v1/status`
3. Set method: `GET` or `POST`
4. For POST requests, add JSON body in the "Body" tab
5. Click "Send"

### WP-CLI Testing (For Server-Side Tasks)

WP-CLI is better for:
- Terminal-based workflows
- Batch processing
- Cron jobs and automation
- Server maintenance tasks

#### Quick WP-CLI Tests

```bash
# Test plugin is loaded
wp aiseo --help

# Generate metadata for a post
wp aiseo generate --id=123

# Analyze SEO score
wp aiseo analyze --id=123

# Check API key
wp option get aiseo_openai_api_key

# View plugin options
wp option list --search=aiseo_*
```

### Testing Strategy: REST API + WP-CLI

**For Each Feature:**

1. ✅ **Quick Test via REST API** (curl or browser)
   - Instant feedback
   - Easy debugging
   - Works from any client

2. ✅ **Comprehensive Test via WP-CLI**
   - Batch operations
   - Server-side validation
   - Integration testing

**Example Workflow:**

```bash
# 1. Quick test with REST API
curl --insecure https://christsong.test/wp-json/aiseo/v1/status

# 2. If working, test full functionality with WP-CLI
wp aiseo generate --id=123 --meta=all

# 3. Verify results
wp aiseo meta get 123 meta_title
wp aiseo analyze --id=123 --format=json
```

### Comparison: REST API vs WP-CLI

| Use Case | Best Method | Why |
|----------|-------------|-----|
| Quick test / browser | REST API | Instant feedback, no terminal needed |
| Scripted terminal task | WP-CLI | Better for automation |
| Admin actions | REST API | Can be called from admin UI |
| Local development | Both | REST for live test, CLI for maintenance |
| Batch processing | WP-CLI | Better performance for bulk operations |
| CI/CD integration | Both | REST for HTTP checks, CLI for tasks |
| Mobile/JS app | REST API | Standard HTTP interface |
| Cron jobs | WP-CLI | Direct server access |

### Unit Testing with PHPUnit

**Test Coverage:**
- Encryption/decryption functions
- Content analysis algorithms
- Keyword density calculations
- Readability score calculations
- Input sanitization
- Cache operations

**Run Tests:**
```bash
composer install
vendor/bin/phpunit
```

### Integration Testing

**Test Areas:**
- API key validation
- Rate limiting enforcement
- Circuit breaker pattern
- Cache invalidation
- Database operations

### Manual Testing Checklist

#### Critical Tests (Must Pass)
- [ ] API key encryption and storage
- [ ] Meta tag generation (3 suggestions)
- [ ] Schema markup validation (Google Rich Results Test)
- [ ] XML sitemap generation and accessibility
- [ ] Rate limiting (10 req/min, 60 req/hour)
- [ ] Security: XSS prevention, SQL injection prevention
- [ ] Uninstall: Complete data removal

#### Performance Tests
- [ ] Page load time < 2 seconds
- [ ] Admin metabox loads < 1 second
- [ ] API response time < 5 seconds
- [ ] Database queries < 50 per page
- [ ] Memory usage < 64MB

#### Compatibility Tests
- [ ] WordPress 5.0+
- [ ] PHP 7.4+
- [ ] Gutenberg editor
- [ ] Classic editor
- [ ] Popular themes (Astra, GeneratePress, Divi)
- [ ] Popular plugins (WooCommerce, Yoast, Rank Math)

### Automated Testing

**E2E Testing with Playwright:**
- User workflows (create post, generate meta, publish)
- AJAX interactions
- Error scenarios

**CI/CD with GitHub Actions:**
- Automated PHPUnit tests on push
- PHPCS WordPress coding standards
- Security scanning

### Validation Tools

- **Schema**: https://search.google.com/test/rich-results
- **Open Graph**: https://developers.facebook.com/tools/debug/
- **Twitter Cards**: https://cards-dev.twitter.com/validator
- **XML Sitemap**: https://www.xml-sitemaps.com/validate-xml-sitemap.html
- **Page Speed**: https://pagespeed.web.dev/

## Error Handling

### API Error Types

1. **Network Errors** (WP_Error)
   - Connection timeout
   - DNS resolution failure
   - SSL certificate issues
   - User message: "Unable to connect to AI service"

2. **Authentication Errors** (401, 403)
   - Invalid API key
   - Expired API key
   - User message: "Invalid API key. Please check your settings"

3. **Rate Limit Errors** (429)
   - OpenAI rate limit exceeded
   - Plugin rate limit exceeded
   - User message: "Rate limit reached. Please wait X minutes"

4. **Server Errors** (500, 502, 503, 504)
   - OpenAI service down
   - Retry with exponential backoff
   - User message: "Service temporarily unavailable. Retrying..."

5. **Validation Errors** (400)
   - Invalid request format
   - Missing required parameters
   - User message: "Invalid request. Please try again"

### Graceful Degradation

When API fails:
- Allow manual input of meta data
- Show cached suggestions if available
- Display helpful error messages
- Don't block post publishing
- Log error for admin review

### Circuit Breaker

After 5 consecutive failures:
- Stop making API requests
- 5-minute cooldown period
- Admin notification
- Automatic resume after cooldown

## Cleanup & Maintenance

### Automatic Cleanup (Cron Jobs)

**Daily (3 AM):**
- Cache warming for popular posts
- Delete logs older than 30 days

**Every 5 Minutes:**
- Process queued requests
- Retry failed requests

**Weekly:**
- Optimize database tables
- Delete completed queue items (7+ days old)
- Export usage statistics

### Manual Cleanup

**Reset Settings:**
- Admin button to reset to defaults
- Clears all caches
- Keeps post meta data

**Clear Cache:**
- Admin button to flush all caches
- Useful after major content updates

**Export Logs:**
- Download logs as CSV
- For debugging and analysis

## Security Measures

### Data Protection

1. **API Key Encryption**
   - AES-256-CBC encryption
   - Key stored in wp-config.php
   - Never exposed in frontend

2. **Input Sanitization**
   - All user inputs sanitized
   - SQL injection prevention
   - XSS prevention

3. **Output Escaping**
   - All outputs escaped
   - Context-aware escaping

4. **Nonce Verification**
   - All AJAX requests
   - All form submissions

5. **Capability Checks**
   - Admin: manage_options
   - Editor: edit_posts

### Rate Limiting

**Per User:**
- 10 requests/minute
- 60 requests/hour
- 500 requests/day

**Per Site:**
- 100 requests/hour
- 1000 requests/day

**Token Limits:**
- Configurable monthly limit
- Soft warning at 80%
- Hard stop at 100%

## Performance Optimization

### Implemented Optimizations

1. **Conditional Loading**
   - Scripts only on relevant pages
   - Deferred non-critical assets

2. **Database Optimization**
   - Indexed meta queries
   - Batch updates
   - Query caching

3. **Caching Strategy**
   - Transient cache (24 hours)
   - Object cache support (Redis/Memcached)
   - Cache warming via cron

4. **Async Processing**
   - WP-Cron for background tasks
   - Request queue system
   - Non-blocking AJAX

5. **Memory Management**
   - Chunked processing
   - Garbage collection
   - Memory limit handling

### Performance Targets

- Page load: < 2 seconds
- Admin metabox: < 1 second
- API response: < 5 seconds
- Database queries: < 50/page
- Memory usage: < 64MB

## Troubleshooting

### Common Issues

**Issue: API key not working**
- Solution: Validate key format (sk-...)
- Check API key permissions
- Verify encryption constants in wp-config.php

**Issue: Rate limit errors**
- Solution: Check usage statistics
- Increase rate limits in settings
- Use queue system for batch processing

**Issue: Slow performance**
- Solution: Enable object cache
- Check database indexes
- Review log for slow queries

**Issue: Meta tags not appearing**
- Solution: Check theme compatibility
- Verify wp_head() in theme
- Check for conflicting SEO plugins

**Issue: Schema validation errors**
- Solution: Use Google Rich Results Test
- Check required fields
- Verify JSON-LD syntax

### Debug Mode

Enable debug mode in wp-config.php:
```php
define('AISEO_DEBUG', true);
```

This enables:
- Verbose logging
- API request/response logging
- Performance profiling
- Cache operation logging

### Support Resources

- Documentation: /wp-content/plugins/aiseo/README.md
- Logs: Admin → AISEO → Logs
- Export Data: Admin → AISEO → Settings → Export
- GitHub Issues: [Plugin Repository]

## WP-CLI Testing & Commands

### Overview

AISEO includes comprehensive WP-CLI support for generating and managing SEO metadata via command line. **All metadata that gets generated on page load can be generated via WP-CLI**, making it perfect for:

- Bulk metadata generation
- Automated testing and CI/CD pipelines
- Cron jobs for scheduled metadata updates
- Server-side processing without browser access
- Headless WordPress implementations

### Prerequisites

1. **WP-CLI installed** (version 2.0+)
   ```bash
   wp --version
   ```

2. **AISEO plugin activated**
   ```bash
   wp plugin list | grep aiseo
   ```

3. **OpenAI API key configured**
   ```bash
   wp option get aiseo_openai_api_key
   ```

### Command Structure

All AISEO commands follow this pattern:
```bash
wp aiseo <subcommand> [<args>] [--flags]
```

### Available Commands

#### 1. Generate Metadata (`wp aiseo generate`)

Generate SEO metadata for posts/pages by slug or ID. This command replicates **all metadata generation that occurs on page load**, including:
- Meta title (AI-generated)
- Meta description (AI-generated)
- Schema markup (JSON-LD)
- Open Graph tags
- Twitter Card tags
- SEO content analysis
- Keyword density analysis
- Readability scores

**Syntax:**
```bash
wp aiseo generate [<slug>] [--id=<id>] [--slug=<slug>] [--all] [--meta=<types>] [--force] [--dry-run]
```

**Options:**

| Option | Description | Example |
|--------|-------------|----------|
| `<slug>` | Post slug (positional argument) | `my-post-slug` |
| `--id=<id>` | Post ID(s), comma-separated | `--id=123` or `--id=1,2,3` |
| `--slug=<slug>` | Post slug(s), comma-separated | `--slug=my-post,another-post` |
| `--post-type=<type>` | Post type filter | `--post-type=post,page` |
| `--all` | Process all published posts/pages | `--all` |
| `--meta=<types>` | Specific metadata types | `--meta=title,description` |
| `--force` | Force regeneration (overwrite existing) | `--force` |
| `--dry-run` | Preview without saving | `--dry-run` |

**Metadata Types:**
- `title` - SEO title (AI-generated)
- `description` - Meta description (AI-generated)
- `schema` - JSON-LD schema markup
- `social` - Open Graph & Twitter Card tags
- `analysis` - SEO content analysis
- `all` - All of the above (default)

**Examples:**

```bash
# Generate all metadata for a post by slug
wp aiseo generate my-post-slug

# Generate all metadata for a post by ID
wp aiseo generate --id=123

# Generate only title and description for multiple posts
wp aiseo generate --id=1,2,3 --meta=title,description

# Generate metadata for all posts (bulk operation)
wp aiseo generate --all --post-type=post

# Generate metadata for all pages
wp aiseo generate --all --post-type=page

# Force regeneration even if metadata exists
wp aiseo generate --id=123 --force

# Dry run to preview what would be generated
wp aiseo generate --slug=my-post --dry-run

# Generate only SEO analysis without AI generation
wp aiseo generate --id=123 --meta=analysis

# Bulk generate for specific slugs
wp aiseo generate --slug=post-1,post-2,post-3 --meta=all
```

**Output Example:**
```
Generating metadata  100% [========================================] 0:05 / 0:05

Success: Processed 10 posts: 8 successful, 2 skipped, 0 errors
```

#### 2. Analyze SEO Score (`wp aiseo analyze`)

Analyze SEO score and content quality for posts/pages. This performs the **same analysis as the admin metabox**, including:
- Overall SEO score (0-100)
- Keyword density
- Readability (Flesch-Kincaid)
- Content structure
- Internal/external links

**Syntax:**
```bash
wp aiseo analyze [<slug>] [--id=<id>] [--slug=<slug>] [--all] [--format=<format>]
```

**Options:**

| Option | Description | Values |
|--------|-------------|--------|
| `--format=<format>` | Output format | `table`, `json`, `csv`, `yaml` |

**Examples:**

```bash
# Analyze a single post by slug
wp aiseo analyze my-post-slug

# Analyze multiple posts and output as JSON
wp aiseo analyze --id=1,2,3 --format=json

# Analyze all posts and export to CSV
wp aiseo analyze --all --format=csv > seo-scores.csv

# Analyze all pages
wp aiseo analyze --all --post-type=page --format=table
```

**Output Example (Table):**
```
+----+---------------------------+----------------+-----------+--------------------+
| ID | Title                     | Slug           | SEO Score | Status             |
+----+---------------------------+----------------+-----------+--------------------+
| 1  | Complete SEO Guide        | seo-guide      | 92        | Good               |
| 2  | WordPress Tips            | wp-tips        | 67        | Needs Improvement  |
| 3  | Getting Started           | getting-started| 45        | Poor               |
+----+---------------------------+----------------+-----------+--------------------+
```

**Output Example (JSON):**
```json
[
  {
    "ID": 1,
    "Title": "Complete SEO Guide",
    "Slug": "seo-guide",
    "SEO Score": 92,
    "Status": "Good"
  }
]
```

#### 3. Manage Metadata (`wp aiseo meta`)

Get, update, or delete specific SEO metadata fields. This provides **direct access to all post meta fields** that are generated on page load.

**Syntax:**
```bash
wp aiseo meta <action> <post-id> <meta-key> [<meta-value>]
```

**Actions:**
- `get` - Retrieve metadata value
- `update` - Update metadata value
- `delete` - Delete metadata

**Available Meta Keys:**

| Short Key | Full Meta Key | Description |
|-----------|---------------|-------------|
| `focus_keyword` | `_aiseo_focus_keyword` | Target keyword |
| `meta_title` | `_aiseo_meta_title` | SEO title |
| `meta_description` | `_aiseo_meta_description` | Meta description |
| `canonical_url` | `_aiseo_canonical_url` | Canonical URL |
| `robots_index` | `_aiseo_robots_index` | Index directive |
| `robots_follow` | `_aiseo_robots_follow` | Follow directive |
| `og_title` | `_aiseo_og_title` | Open Graph title |
| `og_description` | `_aiseo_og_description` | Open Graph description |
| `twitter_title` | `_aiseo_twitter_title` | Twitter Card title |
| `twitter_description` | `_aiseo_twitter_description` | Twitter Card description |
| `schema_type` | `_aiseo_schema_type` | Schema type |
| `seo_score` | `_aiseo_seo_score` | SEO score (0-100) |

**Examples:**

```bash
# Get focus keyword for post 123
wp aiseo meta get 123 focus_keyword

# Update meta title
wp aiseo meta update 123 meta_title "New SEO Optimized Title"

# Update meta description
wp aiseo meta update 123 meta_description "This is a compelling meta description."

# Set focus keyword
wp aiseo meta update 123 focus_keyword "wordpress seo"

# Delete meta description
wp aiseo meta delete 123 meta_description

# Get SEO score
wp aiseo meta get 123 seo_score

# Update Open Graph title
wp aiseo meta update 123 og_title "Share-worthy Title"
```

#### 4. Cache Management (`wp aiseo cache`)

Clear AISEO caches. Useful after bulk operations or when troubleshooting.

**Syntax:**
```bash
wp aiseo cache clear [--post-id=<id>] [--all]
```

**Examples:**

```bash
# Clear cache for specific post
wp aiseo cache clear --post-id=123

# Clear all AISEO caches
wp aiseo cache clear --all
```

#### 5. Export Metadata (`wp aiseo export`)

Export SEO metadata to JSON file for backup or migration.

**Syntax:**
```bash
wp aiseo export [--post-id=<id>] [--all] [--file=<path>]
```

**Examples:**

```bash
# Export specific posts
wp aiseo export --post-id=1,2,3

# Export all posts with SEO metadata
wp aiseo export --all

# Export to custom file location
wp aiseo export --all --file=/path/to/backup/aiseo-export.json

# Export only posts (not pages)
wp aiseo export --all --post-type=post --file=posts-seo.json
```

**Export Format:**
```json
{
  "version": "1.0.0",
  "exported_at": "2025-11-03 07:00:00",
  "posts": [
    {
      "id": 123,
      "title": "My Post Title",
      "slug": "my-post-slug",
      "post_type": "post",
      "metadata": {
        "_aiseo_focus_keyword": "wordpress seo",
        "_aiseo_meta_title": "SEO Optimized Title",
        "_aiseo_meta_description": "Compelling description...",
        "_aiseo_seo_score": 85
      }
    }
  ]
}
```

### Testing Workflows

#### Test 1: Single Post Metadata Generation

**Objective:** Verify that WP-CLI generates the same metadata as page load.

```bash
# Step 1: Create a test post
wp post create --post_title="Test SEO Post" --post_content="This is test content for SEO analysis. It contains multiple paragraphs and keywords." --post_status=publish

# Step 2: Generate metadata via CLI
wp aiseo generate --id=<post-id> --meta=all

# Step 3: Verify metadata was created
wp aiseo meta get <post-id> meta_title
wp aiseo meta get <post-id> meta_description
wp aiseo meta get <post-id> seo_score

# Step 4: Analyze the post
wp aiseo analyze --id=<post-id> --format=json

# Step 5: Compare with browser-generated metadata
# Load the post in browser and compare meta tags in <head>
curl -s https://yoursite.com/test-seo-post/ | grep -E '(meta name="description"|og:title|og:description)'
```

**Expected Result:**
- Meta title generated (50-60 characters)
- Meta description generated (155-160 characters)
- SEO score calculated (0-100)
- Schema markup present in HTML
- Open Graph tags present

#### Test 2: Bulk Metadata Generation

**Objective:** Generate metadata for multiple posts efficiently.

```bash
# Generate metadata for all posts without existing metadata
wp aiseo generate --all --post-type=post

# Force regeneration for all posts (overwrite existing)
wp aiseo generate --all --post-type=post --force

# Generate only for specific posts
wp aiseo generate --id=1,2,3,4,5 --meta=all

# Dry run to preview (no changes)
wp aiseo generate --all --dry-run
```

#### Test 3: Metadata Consistency Test

**Objective:** Verify CLI-generated metadata matches page load metadata.

```bash
#!/bin/bash
# Test script: test-metadata-consistency.sh

POST_ID=123

# Generate via CLI
wp aiseo generate --id=$POST_ID --force

# Get CLI-generated metadata
CLI_TITLE=$(wp aiseo meta get $POST_ID meta_title)
CLI_DESC=$(wp aiseo meta get $POST_ID meta_description)
CLI_SCORE=$(wp aiseo meta get $POST_ID seo_score)

echo "CLI Generated:"
echo "Title: $CLI_TITLE"
echo "Description: $CLI_DESC"
echo "Score: $CLI_SCORE"

# Fetch page and extract meta tags
POST_URL=$(wp post list --post__in=$POST_ID --field=url)
curl -s "$POST_URL" > /tmp/page.html

BROWSER_TITLE=$(grep -oP '(?<=<meta name="title" content=")[^"]*' /tmp/page.html)
BROWSER_DESC=$(grep -oP '(?<=<meta name="description" content=")[^"]*' /tmp/page.html)

echo ""
echo "Browser Generated:"
echo "Title: $BROWSER_TITLE"
echo "Description: $BROWSER_DESC"

# Compare
if [ "$CLI_TITLE" = "$BROWSER_TITLE" ]; then
    echo "✓ Title matches"
else
    echo "✗ Title mismatch"
fi

if [ "$CLI_DESC" = "$BROWSER_DESC" ]; then
    echo "✓ Description matches"
else
    echo "✗ Description mismatch"
fi
```

#### Test 4: Schema Markup Validation

**Objective:** Verify schema markup is generated correctly via CLI.

```bash
# Generate schema for a post
wp aiseo generate --id=123 --meta=schema

# Fetch the page HTML
POST_URL=$(wp post list --post__in=123 --field=url)
curl -s "$POST_URL" > /tmp/page-with-schema.html

# Extract JSON-LD schema
grep -oP '(?<=<script type="application/ld\+json">).*(?=</script>)' /tmp/page-with-schema.html > /tmp/schema.json

# Validate schema using Google's validator (requires jq)
cat /tmp/schema.json | jq .

# Or use online validator
echo "Validate at: https://search.google.com/test/rich-results"
```

#### Test 5: Performance Testing

**Objective:** Measure CLI performance for bulk operations.

```bash
# Time bulk generation
time wp aiseo generate --all --post-type=post

# Generate for 100 posts and measure
time wp aiseo generate --id=$(wp post list --post_type=post --posts_per_page=100 --format=ids)

# Monitor memory usage
/usr/bin/time -v wp aiseo generate --all 2>&1 | grep "Maximum resident set size"
```

### CI/CD Integration

#### GitHub Actions Example

```yaml
name: AISEO Metadata Generation

on:
  schedule:
    - cron: '0 2 * * *'  # Daily at 2 AM
  workflow_dispatch:

jobs:
  generate-metadata:
    runs-on: ubuntu-latest
    steps:
      - name: Setup WP-CLI
        run: |
          curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
          chmod +x wp-cli.phar
          sudo mv wp-cli.phar /usr/local/bin/wp

      - name: Generate SEO Metadata
        run: |
          wp aiseo generate --all --force
          wp aiseo analyze --all --format=csv > seo-report.csv

      - name: Upload Report
        uses: actions/upload-artifact@v2
        with:
          name: seo-report
          path: seo-report.csv
```

#### Cron Job Example

```bash
# Add to crontab: crontab -e

# Generate metadata for new posts daily at 3 AM
0 3 * * * cd /var/www/html && wp aiseo generate --all >> /var/log/aiseo-cron.log 2>&1

# Weekly SEO analysis report
0 9 * * 1 cd /var/www/html && wp aiseo analyze --all --format=csv > /var/www/reports/seo-$(date +\%Y\%m\%d).csv
```

### Advanced Usage

#### Batch Processing with Shell Script

```bash
#!/bin/bash
# batch-generate-metadata.sh

# Get all post IDs
POST_IDS=$(wp post list --post_type=post --post_status=publish --format=ids)

# Process in batches of 10
for id in $POST_IDS; do
    echo "Processing post $id..."
    wp aiseo generate --id=$id --meta=all
    
    # Rate limiting (avoid API throttling)
    sleep 2
done

echo "Batch processing complete!"
```

#### Selective Metadata Update

```bash
# Update only posts with low SEO scores
LOW_SCORE_POSTS=$(wp aiseo analyze --all --format=csv | awk -F',' '$4 < 50 {print $1}' | tail -n +2)

for id in $LOW_SCORE_POSTS; do
    wp aiseo generate --id=$id --force
done
```

#### Metadata Migration Script

```bash
#!/bin/bash
# migrate-from-yoast.sh - Migrate from Yoast to AISEO

# Get all posts with Yoast metadata
POSTS=$(wp post list --post_type=post --format=ids)

for id in $POSTS; do
    # Get Yoast metadata
    YOAST_TITLE=$(wp post meta get $id _yoast_wpseo_title)
    YOAST_DESC=$(wp post meta get $id _yoast_wpseo_metadesc)
    YOAST_KEYWORD=$(wp post meta get $id _yoast_wpseo_focuskw)
    
    # Set AISEO metadata
    if [ -n "$YOAST_TITLE" ]; then
        wp aiseo meta update $id meta_title "$YOAST_TITLE"
    fi
    
    if [ -n "$YOAST_DESC" ]; then
        wp aiseo meta update $id meta_description "$YOAST_DESC"
    fi
    
    if [ -n "$YOAST_KEYWORD" ]; then
        wp aiseo meta update $id focus_keyword "$YOAST_KEYWORD"
    fi
    
    # Generate missing metadata with AI
    wp aiseo generate --id=$id
    
    echo "Migrated post $id"
done
```

### Testing Checklist for WP-CLI

#### Functional Tests
- [ ] `wp aiseo generate` creates meta title
- [ ] `wp aiseo generate` creates meta description
- [ ] `wp aiseo generate` generates schema markup
- [ ] `wp aiseo generate` creates social media tags
- [ ] `wp aiseo generate` performs SEO analysis
- [ ] `wp aiseo generate --force` overwrites existing metadata
- [ ] `wp aiseo generate --dry-run` doesn't save data
- [ ] `wp aiseo analyze` calculates SEO score
- [ ] `wp aiseo meta get` retrieves metadata
- [ ] `wp aiseo meta update` saves metadata
- [ ] `wp aiseo meta delete` removes metadata
- [ ] `wp aiseo cache clear` removes cached data
- [ ] `wp aiseo export` creates JSON file

#### Consistency Tests
- [ ] CLI-generated title matches page load title
- [ ] CLI-generated description matches page load description
- [ ] CLI-generated schema matches page load schema
- [ ] CLI SEO score matches admin metabox score
- [ ] CLI analysis results match admin analysis

#### Performance Tests
- [ ] Single post generation < 5 seconds
- [ ] Bulk generation (100 posts) < 10 minutes
- [ ] Memory usage < 128MB
- [ ] No timeout errors
- [ ] Rate limiting works correctly

#### Error Handling Tests
- [ ] Invalid post ID shows error
- [ ] Invalid slug shows error
- [ ] Missing API key shows error
- [ ] API timeout handled gracefully
- [ ] Rate limit errors handled
- [ ] Network errors handled

### Troubleshooting WP-CLI

#### Command Not Found

```bash
# Check if WP-CLI is installed
wp --version

# Check if AISEO is activated
wp plugin list | grep aiseo

# Verify command registration
wp cli has-command aiseo
```

#### API Key Issues

```bash
# Check if API key is set
wp option get aiseo_openai_api_key

# Test API key validity
wp aiseo generate --id=1 --dry-run
```

#### Permission Issues

```bash
# Run as web server user
sudo -u www-data wp aiseo generate --id=123

# Or specify WordPress path
wp aiseo generate --id=123 --path=/var/www/html
```

#### Debug Mode

```bash
# Enable debug output
wp aiseo generate --id=123 --debug

# Verbose output
wp aiseo generate --id=123 --debug --verbose
```

### Best Practices

1. **Rate Limiting**: Add delays between bulk operations to avoid API throttling
   ```bash
   for id in $(wp post list --format=ids); do
       wp aiseo generate --id=$id
       sleep 2  # 2-second delay
   done
   ```

2. **Dry Run First**: Always test with `--dry-run` before bulk operations
   ```bash
   wp aiseo generate --all --dry-run
   ```

3. **Backup Before Bulk Updates**: Export metadata before force regeneration
   ```bash
   wp aiseo export --all --file=backup-$(date +%Y%m%d).json
   wp aiseo generate --all --force
   ```

4. **Monitor Progress**: Use progress bars for long operations
   ```bash
   wp aiseo generate --all  # Built-in progress bar
   ```

5. **Error Logging**: Redirect errors to log file
   ```bash
   wp aiseo generate --all 2>> /var/log/aiseo-errors.log
   ```

6. **Validate Output**: Always verify generated metadata
   ```bash
   wp aiseo generate --id=123
   wp aiseo meta get 123 meta_title
   wp aiseo analyze --id=123
   ```

### Integration with Testing Tools

#### PHPUnit Integration

```php
// tests/test-cli-commands.php
class Test_AISEO_CLI extends WP_UnitTestCase {
    
    public function test_generate_metadata_via_cli() {
        $post_id = $this->factory->post->create();
        
        // Run CLI command
        $result = WP_CLI::runcommand(
            "aiseo generate --id={$post_id}",
            ['return' => true]
        );
        
        // Verify metadata was created
        $title = get_post_meta($post_id, '_aiseo_meta_title', true);
        $this->assertNotEmpty($title);
    }
}
```

#### Behat Integration

```gherkin
# features/aiseo-cli.feature
Feature: AISEO CLI Commands
  
  Scenario: Generate metadata for a post
    Given I have a WordPress installation
    And a post exists with slug "test-post"
    When I run `wp aiseo generate test-post`
    Then STDOUT should contain "Success"
    And the post should have SEO metadata
```

### Summary

The AISEO WP-CLI integration provides complete parity with page load metadata generation:

✅ **All metadata generated on page load is available via CLI**
✅ **Supports bulk operations for efficiency**
✅ **Includes dry-run mode for safe testing**
✅ **Provides multiple output formats (table, JSON, CSV)**
✅ **Handles errors gracefully with proper logging**
✅ **Integrates with CI/CD pipelines**
✅ **Supports cron jobs for automation**
✅ **Compatible with WordPress multisite**
✅ **Includes comprehensive testing workflows**

For detailed implementation, see `/includes/class-aiseo-cli.php`.

---

## Testing Workflows for New Features (v1.1.0 - v2.2.0)

### Feature Testing Overview

This section provides comprehensive testing workflows for all roadmap features based on FEATURE-SPECIFICATIONS.md. Each feature includes:
- REST API testing (curl/browser)
- WP-CLI testing (terminal)
- Admin interface testing (manual)
- Integration testing (automated)

---

## 1. Image SEO & Alt Text Optimization Testing

### Quick Test (REST API)

```bash
# 1. Get images missing alt text
curl --insecure https://yoursite.test/wp-json/aiseo/v1/image/missing-alt

# 2. Generate alt text for a single image
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/image/generate-alt/123 \
  -H "Content-Type: application/json"

# 3. Get image SEO score
curl --insecure https://yoursite.test/wp-json/aiseo/v1/image/seo-score/123

# 4. Bulk generate alt text
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/image/bulk-generate \
  -H "Content-Type: application/json" \
  -d '{"image_ids": [123, 124, 125], "overwrite": false}'
```

### WP-CLI Testing

```bash
# Test 1: Generate alt text for single image
wp aiseo image generate-alt 123

# Test 2: Detect images missing alt text
wp aiseo image detect-missing --format=table

# Test 3: Bulk generate for all missing alt text
wp aiseo image bulk-generate --missing-only --limit=10

# Test 4: Analyze image SEO for a post
wp aiseo image analyze 456

# Test 5: Dry run (preview without changes)
wp aiseo image bulk-generate --all --dry-run
```

### Admin Interface Testing

1. Navigate to `wp-admin/admin.php?page=aiseo-image-seo`
2. Verify statistics dashboard displays correctly
3. Check missing alt text table loads
4. Select multiple images and click "Generate Alt Text"
5. Verify progress bar updates
6. Check generated alt text appears in table
7. Verify estimated cost calculation

### Integration Testing

```bash
# Test workflow: Upload image → Generate alt → Verify
wp media import test-image.jpg --post_id=123
IMAGE_ID=$(wp db query "SELECT ID FROM wp_posts WHERE post_type='attachment' ORDER BY ID DESC LIMIT 1" --skip-column-names)
wp aiseo image generate-alt $IMAGE_ID
wp post meta get $IMAGE_ID _wp_attachment_image_alt
```

### Expected Results

- ✅ Alt text generated within 5 seconds
- ✅ Alt text length between 50-125 characters
- ✅ Focus keyword included naturally
- ✅ Image SEO score calculated correctly
- ✅ Bulk operations complete without errors
- ✅ Progress tracking accurate

---

## 2. Advanced SEO Analysis (40+ Factors) Testing

### Quick Test (REST API)

```bash
# 1. Comprehensive analysis (all 40+ factors)
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/analyze/comprehensive/123

# 2. Readability analysis only
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/analyze/readability/123

# 3. Technical SEO analysis
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/analyze/technical/123

# 4. Get available analysis factors
curl --insecure https://yoursite.test/wp-json/aiseo/v1/analyze/factors
```

### WP-CLI Testing

```bash
# Test 1: Comprehensive analysis
wp aiseo analyze comprehensive 123 --format=json

# Test 2: Readability check
wp aiseo analyze readability 123

# Test 3: Technical SEO check
wp aiseo analyze technical 123

# Test 4: Bulk analysis for low-scoring posts
wp aiseo analyze bulk --post-type=post --score-below=50

# Test 5: Export analysis results
wp aiseo analyze comprehensive 123 --format=json > analysis-report.json
```

### Testing Individual Factors

```bash
# Test passive voice detection
wp aiseo analyze readability 123 | grep "Passive voice"

# Test transition words
wp aiseo analyze readability 123 | grep "Transition words"

# Test keyword in URL
wp aiseo analyze technical 123 | grep "Keyword in URL"

# Test image alt text check
wp aiseo analyze technical 123 | grep "Image alt text"
```

### Expected Results

- ✅ All 40+ factors analyzed
- ✅ Score calculated correctly (0-100)
- ✅ Status indicators accurate (good/ok/poor)
- ✅ Recommendations actionable
- ✅ Analysis completes in <10 seconds

---

## 3. Bulk Editing Interface Testing

### Quick Test (REST API)

```bash
# 1. Get posts for bulk editing
curl --insecure "https://yoursite.test/wp-json/aiseo/v1/bulk/posts?post_type=post&score_below=50"

# 2. Bulk generate titles
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/generate-titles \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [1, 2, 3]}'

# 3. Bulk generate descriptions
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/bulk/generate-descriptions \
  -H "Content-Type: application/json" \
  -d '{"post_ids": [1, 2, 3]}'

# 4. Check progress
curl --insecure https://yoursite.test/wp-json/aiseo/v1/bulk/progress/abc123
```

### WP-CLI Testing

```bash
# Test 1: Bulk generate titles
wp aiseo bulk generate-titles --post-type=post --limit=10

# Test 2: Bulk generate descriptions for low-scoring posts
wp aiseo bulk generate-descriptions --score-below=50

# Test 3: Bulk update schema type
wp aiseo bulk update-schema --schema-type=Article --post-type=post

# Test 4: Export bulk data to CSV
wp aiseo bulk export --format=csv > bulk-export.csv

# Test 5: Dry run (preview changes)
wp aiseo bulk generate-titles --post-type=post --dry-run
```

### Admin Interface Testing

1. Navigate to `wp-admin/admin.php?page=aiseo-bulk-editor`
2. Apply filters (post type, SEO score, date range)
3. Select multiple posts via checkboxes
4. Choose bulk action: "Generate Titles (AI)"
5. Click "Apply" and verify progress bar
6. Check that titles are generated correctly
7. Test inline editing of meta fields
8. Verify save functionality

### Performance Testing

```bash
# Test bulk operation performance
time wp aiseo bulk generate-titles --post-type=post --limit=100

# Expected: <10 minutes for 100 posts (with 2-second rate limiting)
```

### Expected Results

- ✅ Bulk operations complete without timeout
- ✅ Progress tracking accurate
- ✅ Rate limiting prevents API throttling
- ✅ Error handling graceful
- ✅ UI responsive during operations

---

## 4. Import/Export Functionality Testing

### Quick Test (REST API)

```bash
# 1. Import from Yoast SEO
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/import/yoast \
  -H "Content-Type: application/json" \
  -d '{"post_types": ["post", "page"], "overwrite": false}'

# 2. Import from Rank Math
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/import/rankmath

# 3. Export to JSON
curl --insecure https://yoursite.test/wp-json/aiseo/v1/export/json > aiseo-export.json

# 4. Export to CSV
curl --insecure https://yoursite.test/wp-json/aiseo/v1/export/csv > aiseo-export.csv
```

### WP-CLI Testing

```bash
# Test 1: Import from Yoast SEO
wp aiseo import yoast --post-type=post,page

# Test 2: Import from Rank Math (dry run)
wp aiseo import rankmath --dry-run

# Test 3: Import from All in One SEO
wp aiseo import aioseo --overwrite

# Test 4: Export all data to JSON
wp aiseo export --file=aiseo-backup-$(date +%Y%m%d).json

# Test 5: Import from JSON backup
wp aiseo import --file=aiseo-backup-20250103.json

# Test 6: Verify imported data
wp aiseo meta get 123 meta_title
wp aiseo meta get 123 meta_description
```

### Migration Testing Workflow

```bash
# Complete migration from Yoast to AISEO

# Step 1: Backup current data
wp aiseo export --file=aiseo-pre-migration.json

# Step 2: Import from Yoast (dry run first)
wp aiseo import yoast --dry-run

# Step 3: Review dry run results, then import
wp aiseo import yoast --post-type=post,page

# Step 4: Verify import
wp db query "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key LIKE '_aiseo_%'"

# Step 5: Compare with Yoast data
wp db query "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key LIKE '_yoast_wpseo_%'"
```

### Expected Results

- ✅ All meta fields mapped correctly
- ✅ No data loss during import
- ✅ Export file format valid JSON/CSV
- ✅ Import handles duplicates gracefully
- ✅ Rollback option available

---

## 5. Google Search Console Integration Testing

### Quick Test (REST API)

```bash
# 1. Check authentication status
curl --insecure https://yoursite.test/wp-json/aiseo/v1/gsc/status

# 2. Get search analytics
curl --insecure "https://yoursite.test/wp-json/aiseo/v1/gsc/analytics?date_range=last-30-days"

# 3. Get top keywords
curl --insecure "https://yoursite.test/wp-json/aiseo/v1/gsc/top-keywords?limit=100"

# 4. Get crawl errors
curl --insecure https://yoursite.test/wp-json/aiseo/v1/gsc/crawl-errors

# 5. Trigger sync
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/gsc/sync
```

### WP-CLI Testing

```bash
# Test 1: Authenticate with Google
wp aiseo gsc authenticate
# Follow OAuth flow in browser

# Test 2: Sync data from GSC
wp aiseo gsc sync --date-range=last-30-days

# Test 3: Get top keywords
wp aiseo gsc top-keywords --limit=100 --format=table

# Test 4: Check crawl errors
wp aiseo gsc crawl-errors --format=json

# Test 5: Get index status for URL
wp aiseo gsc index-status https://yoursite.com/sample-post/
```

### Expected Results

- ✅ OAuth authentication successful
- ✅ Data synced from GSC
- ✅ Top keywords displayed correctly
- ✅ Crawl errors detected
- ✅ Index status accurate

---

## 6. Google Analytics 4 Integration Testing

### Quick Test (REST API)

```bash
# 1. Check authentication status
curl --insecure https://yoursite.test/wp-json/aiseo/v1/ga4/status

# 2. Get analytics for post
curl --insecure https://yoursite.test/wp-json/aiseo/v1/ga4/analytics/123

# 3. Get top pages
curl --insecure "https://yoursite.test/wp-json/aiseo/v1/ga4/top-pages?limit=50"

# 4. Get traffic sources
curl --insecure https://yoursite.test/wp-json/aiseo/v1/ga4/traffic-sources/123
```

### WP-CLI Testing

```bash
# Test 1: Authenticate with GA4
wp aiseo ga4 authenticate

# Test 2: Sync analytics data
wp aiseo ga4 sync --date-range=last-7-days

# Test 3: Get top pages
wp aiseo ga4 top-pages --limit=50 --format=table

# Test 4: Get stats for specific post
wp aiseo ga4 stats 123
```

### Expected Results

- ✅ OAuth authentication successful
- ✅ Page views data accurate
- ✅ Traffic sources breakdown correct
- ✅ Real-time data available

---

## 7. Rank Tracking Testing

### Quick Test (REST API)

```bash
# 1. Track keyword ranking
curl --insecure -X POST https://yoursite.test/wp-json/aiseo/v1/rank/track \
  -H "Content-Type: application/json" \
  -d '{"keyword": "wordpress seo", "post_id": 123, "location": "US"}'

# 2. Get position history
curl --insecure "https://yoursite.test/wp-json/aiseo/v1/rank/history/wordpress-seo?days=30"

# 3. Get ranking keywords for post
curl --insecure https://yoursite.test/wp-json/aiseo/v1/rank/keywords/123

# 4. Compare with competitor
curl --insecure "https://yoursite.test/wp-json/aiseo/v1/rank/compare?keyword=wordpress-seo&competitor=example.com"
```

### WP-CLI Testing

```bash
# Test 1: Track keyword
wp aiseo rank track "wordpress seo" --post-id=123 --location=US

# Test 2: Get position history
wp aiseo rank history "wordpress seo" --days=30 --format=table

# Test 3: Get all ranking keywords for post
wp aiseo rank keywords 123

# Test 4: Bulk track keywords
wp aiseo rank bulk-track --file=keywords.csv
```

### Expected Results

- ✅ Keyword positions tracked accurately
- ✅ Historical data stored correctly
- ✅ Graphs display position changes
- ✅ SERP features detected

---

## Integration Testing Scenarios

### Scenario 1: Complete SEO Workflow

```bash
# 1. Create new post
POST_ID=$(wp post create --post_title="Test SEO Post" --post_content="Lorem ipsum..." --post_status=publish --porcelain)

# 2. Set focus keyword
wp aiseo meta update $POST_ID focus_keyword "wordpress seo"

# 3. Generate SEO metadata
wp aiseo generate --id=$POST_ID --meta=all

# 4. Analyze content
wp aiseo analyze comprehensive $POST_ID

# 5. Generate alt text for images
wp aiseo image bulk-generate --post-id=$POST_ID

# 6. Track keyword ranking
wp aiseo rank track "wordpress seo" --post-id=$POST_ID

# 7. Verify all metadata
wp aiseo meta get $POST_ID meta_title
wp aiseo meta get $POST_ID meta_description
wp aiseo meta get $POST_ID seo_score
```

### Scenario 2: Bulk Optimization

```bash
# 1. Find low-scoring posts
wp aiseo analyze bulk --score-below=50 --format=ids > low-score-posts.txt

# 2. Bulk generate titles and descriptions
wp aiseo bulk generate-titles --post-ids=$(cat low-score-posts.txt)
wp aiseo bulk generate-descriptions --post-ids=$(cat low-score-posts.txt)

# 3. Re-analyze
wp aiseo analyze bulk --post-ids=$(cat low-score-posts.txt)

# 4. Generate report
wp aiseo analyze bulk --post-ids=$(cat low-score-posts.txt) --format=csv > improvement-report.csv
```

### Scenario 3: Migration & Import

```bash
# 1. Export current AISEO data (backup)
wp aiseo export --file=aiseo-backup.json

# 2. Import from Yoast
wp aiseo import yoast --post-type=post,page

# 3. Verify import
wp db query "SELECT COUNT(*) as aiseo_posts FROM wp_postmeta WHERE meta_key='_aiseo_meta_title'"

# 4. Generate missing metadata
wp aiseo generate --all --missing-only

# 5. Bulk analyze all posts
wp aiseo analyze bulk --all
```

---

## Performance Benchmarks

### Expected Performance Targets

| Operation | Target Time | Notes |
|-----------|-------------|-------|
| Single alt text generation | <5 seconds | With API call |
| Bulk alt text (100 images) | <10 minutes | With 2s rate limiting |
| Comprehensive analysis | <10 seconds | All 40+ factors |
| Bulk title generation (50 posts) | <5 minutes | With rate limiting |
| Import from Yoast (1000 posts) | <2 minutes | Database operations |
| GSC data sync | <30 seconds | API dependent |
| Rank tracking check | <5 seconds | Per keyword |

### Performance Testing Commands

```bash
# Test 1: Single operation timing
time wp aiseo image generate-alt 123

# Test 2: Bulk operation timing
time wp aiseo bulk generate-titles --limit=50

# Test 3: Analysis performance
time wp aiseo analyze comprehensive 123

# Test 4: Import performance
time wp aiseo import yoast --post-type=post
```

---

## Automated Testing Suite

### PHPUnit Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite=image-seo
vendor/bin/phpunit --testsuite=advanced-analysis
vendor/bin/phpunit --testsuite=bulk-editor

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Integration Tests

```bash
# Run integration tests
vendor/bin/phpunit --group=integration

# Run API tests
vendor/bin/phpunit --group=api

# Run CLI tests
vendor/bin/phpunit --group=cli
```

---

## Troubleshooting Common Issues

### Issue: Alt text generation fails

```bash
# Check API key
wp option get aiseo_openai_api_key

# Test API connection
wp aiseo generate --id=123 --meta=title --dry-run

# Check logs
wp aiseo logs --category=image_seo --level=ERROR
```

### Issue: Bulk operations timeout

```bash
# Increase PHP timeout
wp config set WP_MAX_EXECUTION_TIME 300

# Process in smaller batches
wp aiseo bulk generate-titles --limit=10

# Use WP-Cron for background processing
wp aiseo bulk generate-titles --async
```

### Issue: Import fails

```bash
# Check source plugin data
wp db query "SELECT COUNT(*) FROM wp_postmeta WHERE meta_key LIKE '_yoast_%'"

# Dry run first
wp aiseo import yoast --dry-run

# Import with verbose output
wp aiseo import yoast --verbose
```

---

## Final Testing Checklist

### Phase 1 Features (v1.1.0)
- [ ] Image SEO: Generate alt text
- [ ] Image SEO: Bulk operations
- [ ] Image SEO: SEO scoring
- [ ] Advanced Analysis: All 40+ factors
- [ ] Advanced Analysis: Readability checks
- [ ] Advanced Analysis: Technical SEO
- [ ] Bulk Editor: Filter and select
- [ ] Bulk Editor: Bulk generate
- [ ] Bulk Editor: Progress tracking
- [ ] Import/Export: Yoast import
- [ ] Import/Export: Rank Math import
- [ ] Import/Export: JSON export/import

### Phase 2 Features (v1.2.0)
- [ ] GSC: OAuth authentication
- [ ] GSC: Data sync
- [ ] GSC: Top keywords
- [ ] GSC: Crawl errors
- [ ] GA4: OAuth authentication
- [ ] GA4: Analytics dashboard
- [ ] GA4: Traffic sources
- [ ] Rank Tracking: Track keywords
- [ ] Rank Tracking: Position history
- [ ] Rank Tracking: Competitor comparison

### Phase 3 Features (v2.0.0)
- [ ] 404 Monitor: Log errors
- [ ] 404 Monitor: Suggest redirects
- [ ] Redirects: Create/manage
- [ ] Internal Links: AI suggestions
- [ ] Internal Links: Orphan detection
- [ ] Permalink: Stop word removal
- [ ] Permalink: Optimization suggestions

---

**End of Testing Workflows**

For detailed feature specifications, see `FEATURE-SPECIFICATIONS.md`.
