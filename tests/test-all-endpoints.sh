#!/bin/bash

##############################################################################
# AISEO Plugin - Comprehensive REST API & WP-CLI Testing System
# 
# This script tests ALL REST API endpoints and WP-CLI commands
# Usage: ./test-all-endpoints.sh [--site=yoursite.test] [--verbose]
##############################################################################

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SITE_URL="${1:-https://wordpress.test}"
VERBOSE=false
WORDPRESS_PATH="/Users/praison/Sites/localhost/wordpress"

# Parse arguments
for arg in "$@"; do
    case $arg in
        --site=*)
            SITE_URL="${arg#*=}"
            ;;
        --verbose)
            VERBOSE=true
            ;;
        --help)
            echo "Usage: $0 [--site=yoursite.test] [--verbose]"
            exit 0
            ;;
    esac
done

# Test counters
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0
MISSING_ENDPOINTS=()
MISSING_CLI_COMMANDS=()

# Log file
LOG_FILE="test-results-$(date +%Y%m%d-%H%M%S).log"

##############################################################################
# Helper Functions
##############################################################################

log() {
    echo -e "$1" | tee -a "$LOG_FILE"
}

test_start() {
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
    if [ "$VERBOSE" = true ]; then
        log "${BLUE}[TEST $TOTAL_TESTS]${NC} $1"
    fi
}

test_pass() {
    PASSED_TESTS=$((PASSED_TESTS + 1))
    log "${GREEN}✓ PASS${NC} $1"
}

test_fail() {
    FAILED_TESTS=$((FAILED_TESTS + 1))
    log "${RED}✗ FAIL${NC} $1"
    if [ -n "$2" ]; then
        log "  ${RED}Error:${NC} $2"
    fi
}

test_skip() {
    log "${YELLOW}⊘ SKIP${NC} $1"
}

##############################################################################
# REST API Tests
##############################################################################

log "\n${BLUE}═══════════════════════════════════════════════════════════${NC}"
log "${BLUE}  AISEO REST API Endpoint Testing${NC}"
log "${BLUE}  Site: $SITE_URL${NC}"
log "${BLUE}═══════════════════════════════════════════════════════════${NC}\n"

# Test 1: Plugin Status
test_start "GET /wp-json/aiseo/v1/status"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/status")
if echo "$response" | grep -q '"success":true'; then
    test_pass "Plugin status endpoint"
else
    test_fail "Plugin status endpoint" "$response"
fi

# Test 2: Validate API Key
test_start "GET /wp-json/aiseo/v1/validate-key"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/validate-key")
if echo "$response" | grep -q '"success"'; then
    test_pass "API key validation endpoint"
else
    test_fail "API key validation endpoint" "$response"
fi

# Test 3: Content Analysis
test_start "POST /wp-json/aiseo/v1/analyze"
response=$(curl -k -s -X POST "$SITE_URL/wp-json/aiseo/v1/analyze" \
    -H "Content-Type: application/json" \
    -d '{"post_id": 1}')
if echo "$response" | grep -q '"overall_score"'; then
    test_pass "Content analysis endpoint"
else
    test_fail "Content analysis endpoint" "$response"
fi

# Test 4: Schema Markup
test_start "GET /wp-json/aiseo/v1/schema/1"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/schema/1")
if echo "$response" | grep -q '"@context"'; then
    test_pass "Schema markup endpoint"
else
    test_fail "Schema markup endpoint" "$response"
fi

# Test 5: Meta Tags
test_start "GET /wp-json/aiseo/v1/meta-tags/1"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/meta-tags/1")
if echo "$response" | grep -q '"meta_tags"'; then
    test_pass "Meta tags endpoint"
else
    test_fail "Meta tags endpoint" "$response"
fi

# Test 6: Social Tags
test_start "GET /wp-json/aiseo/v1/social-tags/1"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/social-tags/1")
if echo "$response" | grep -q '"social_tags"'; then
    test_pass "Social tags endpoint"
else
    test_fail "Social tags endpoint" "$response"
fi

# Test 7: Sitemap Stats
test_start "GET /wp-json/aiseo/v1/sitemap/stats"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/sitemap/stats")
if echo "$response" | grep -q '"total_urls"'; then
    test_pass "Sitemap stats endpoint"
else
    test_fail "Sitemap stats endpoint" "$response"
fi

# Test 8: Advanced Analysis
test_start "GET /wp-json/aiseo/v1/analyze/advanced/1"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/analyze/advanced/1")
if echo "$response" | grep -q '"success"'; then
    test_pass "Advanced analysis endpoint"
else
    test_fail "Advanced analysis endpoint" "$response"
fi

# Test 9: Internal Linking Suggestions
test_start "GET /wp-json/aiseo/v1/internal-linking/suggestions/1"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/internal-linking/suggestions/1")
if echo "$response" | grep -q '"success"'; then
    test_pass "Internal linking suggestions endpoint"
else
    test_fail "Internal linking suggestions endpoint" "$response"
fi

# Test 10: Orphan Pages Detection
test_start "GET /wp-json/aiseo/v1/internal-linking/orphans"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/internal-linking/orphans")
if echo "$response" | grep -q '"success"'; then
    test_pass "Orphan pages detection endpoint"
else
    test_fail "Orphan pages detection endpoint" "$response"
fi

# Test 11: Content Topics
test_start "POST /wp-json/aiseo/v1/content/topics"
response=$(curl -k -s -X POST "$SITE_URL/wp-json/aiseo/v1/content/topics" \
    -H "Content-Type: application/json" \
    -d '{"niche": "WordPress SEO", "count": 5}')
if echo "$response" | grep -q '"success"'; then
    test_pass "Content topics endpoint"
else
    test_fail "Content topics endpoint" "$response"
fi

# Test 12: 404 Errors Log
test_start "GET /wp-json/aiseo/v1/404/errors"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/404/errors")
if echo "$response" | grep -q '"success"'; then
    test_pass "404 errors log endpoint"
else
    test_fail "404 errors log endpoint" "$response"
fi

# Test 13: Redirects List
test_start "GET /wp-json/aiseo/v1/redirects/list"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/redirects/list")
if echo "$response" | grep -q '"success"'; then
    test_pass "Redirects list endpoint"
else
    test_fail "Redirects list endpoint" "$response"
fi

# Test 14: Permalink Optimization
test_start "POST /wp-json/aiseo/v1/permalink/optimize"
response=$(curl -k -s -X POST "$SITE_URL/wp-json/aiseo/v1/permalink/optimize" \
    -H "Content-Type: application/json" \
    -d '{"post_id": 1}')
if echo "$response" | grep -q '"success"'; then
    test_pass "Permalink optimization endpoint"
else
    test_fail "Permalink optimization endpoint" "$response"
fi

# Test 15: Readability Analysis
test_start "GET /wp-json/aiseo/v1/readability/analyze/1"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/readability/analyze/1")
if echo "$response" | grep -q '"flesch_reading_ease"'; then
    test_pass "Readability analysis endpoint"
else
    test_fail "Readability analysis endpoint" "$response"
fi

# Test 16: FAQ Generation
test_start "POST /wp-json/aiseo/v1/faq/generate/1"
response=$(curl -k -s -X POST "$SITE_URL/wp-json/aiseo/v1/faq/generate/1" \
    -H "Content-Type: application/json" \
    -d '{"count": 5}')
if echo "$response" | grep -q '"success"'; then
    test_pass "FAQ generation endpoint"
else
    test_fail "FAQ generation endpoint" "$response"
fi

# Test 17: FAQ Get
test_start "GET /wp-json/aiseo/v1/faq/get/1"
response=$(curl -k -s "$SITE_URL/wp-json/aiseo/v1/faq/get/1")
if echo "$response" | grep -q '"success"'; then
    test_pass "FAQ get endpoint"
else
    test_fail "FAQ get endpoint" "$response"
fi

# Test 18: Outline Generation
test_start "POST /wp-json/aiseo/v1/outline/generate"
response=$(curl -k -s -X POST "$SITE_URL/wp-json/aiseo/v1/outline/generate" \
    -H "Content-Type: application/json" \
    -d '{"topic": "WordPress SEO Best Practices"}')
if echo "$response" | grep -q '"success"'; then
    test_pass "Outline generation endpoint"
else
    test_fail "Outline generation endpoint" "$response"
fi

# Test 19: Content Rewriter
test_start "POST /wp-json/aiseo/v1/rewrite/content"
response=$(curl -k -s -X POST "$SITE_URL/wp-json/aiseo/v1/rewrite/content" \
    -H "Content-Type: application/json" \
    -d '{"content": "Test content", "mode": "improve"}')
if echo "$response" | grep -q '"success"'; then
    test_pass "Content rewriter endpoint"
else
    test_fail "Content rewriter endpoint" "$response"
fi

# Test 20: Meta Variations
test_start "POST /wp-json/aiseo/v1/meta/variations/1"
response=$(curl -k -s -X POST "$SITE_URL/wp-json/aiseo/v1/meta/variations/1" \
    -H "Content-Type: application/json" \
    -d '{"count": 5}')
if echo "$response" | grep -q '"success"'; then
    test_pass "Meta variations endpoint"
else
    test_fail "Meta variations endpoint" "$response"
fi

##############################################################################
# WP-CLI Tests
##############################################################################

log "\n${BLUE}═══════════════════════════════════════════════════════════${NC}"
log "${BLUE}  AISEO WP-CLI Command Testing${NC}"
log "${BLUE}═══════════════════════════════════════════════════════════${NC}\n"

# Test 21: WP-CLI - Analyze
test_start "wp aiseo analyze --id=1"
output=$(wp aiseo analyze --id=1 --path="$WORDPRESS_PATH" 2>&1)
if echo "$output" | grep -q "overall_score\|SEO Score"; then
    test_pass "WP-CLI analyze command"
else
    test_fail "WP-CLI analyze command" "$output"
fi

# Test 22: WP-CLI - Readability Analyze
test_start "wp aiseo readability analyze 1"
output=$(wp aiseo readability analyze 1 --path="$WORDPRESS_PATH" 2>&1)
if echo "$output" | grep -q "flesch_reading_ease\|Readability"; then
    test_pass "WP-CLI readability analyze command"
else
    test_fail "WP-CLI readability analyze command" "$output"
fi

# Test 23: WP-CLI - Permalink Optimize
test_start "wp aiseo permalink optimize 1"
output=$(wp aiseo permalink optimize 1 --path="$WORDPRESS_PATH" 2>&1)
if echo "$output" | grep -q "Success\|optimized"; then
    test_pass "WP-CLI permalink optimize command"
else
    test_fail "WP-CLI permalink optimize command" "$output"
fi

# Test 24: WP-CLI - FAQ Generate
test_start "wp aiseo faq generate 1"
output=$(wp aiseo faq generate 1 --count=3 --path="$WORDPRESS_PATH" 2>&1)
if echo "$output" | grep -q "Success\|FAQ"; then
    test_pass "WP-CLI FAQ generate command"
else
    test_fail "WP-CLI FAQ generate command" "$output"
fi

# Test 25: WP-CLI - Internal Linking Suggestions
test_start "wp aiseo internal-linking suggestions 1"
output=$(wp aiseo internal-linking suggestions 1 --path="$WORDPRESS_PATH" 2>&1)
if echo "$output" | grep -q "Success\|suggestions"; then
    test_pass "WP-CLI internal linking suggestions command"
else
    test_fail "WP-CLI internal linking suggestions command" "$output"
fi

# Test 26: WP-CLI - 404 Errors
test_start "wp aiseo 404 errors"
output=$(wp aiseo 404 errors --path="$WORDPRESS_PATH" 2>&1)
if echo "$output" | grep -q "Success\|404\|No errors"; then
    test_pass "WP-CLI 404 errors command"
else
    test_fail "WP-CLI 404 errors command" "$output"
fi

# Test 27: WP-CLI - Redirects List
test_start "wp aiseo redirects list"
output=$(wp aiseo redirects list --path="$WORDPRESS_PATH" 2>&1)
if echo "$output" | grep -q "Success\|redirects\|No redirects"; then
    test_pass "WP-CLI redirects list command"
else
    test_fail "WP-CLI redirects list command" "$output"
fi

##############################################################################
# Summary Report
##############################################################################

log "\n${BLUE}═══════════════════════════════════════════════════════════${NC}"
log "${BLUE}  Test Summary${NC}"
log "${BLUE}═══════════════════════════════════════════════════════════${NC}\n"

log "Total Tests:  $TOTAL_TESTS"
log "${GREEN}Passed:       $PASSED_TESTS${NC}"
log "${RED}Failed:       $FAILED_TESTS${NC}"

PASS_RATE=$((PASSED_TESTS * 100 / TOTAL_TESTS))
log "\nPass Rate:    ${PASS_RATE}%"

if [ $FAILED_TESTS -eq 0 ]; then
    log "\n${GREEN}✓ All tests passed!${NC}"
    exit 0
else
    log "\n${RED}✗ Some tests failed. Check log: $LOG_FILE${NC}"
    exit 1
fi
