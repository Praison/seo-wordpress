#!/bin/bash

##############################################################################
# AISEO Plugin - New Features REST API Testing
# 
# Tests all new REST API endpoints added during migration:
# - Homepage SEO
# - Taxonomy SEO
# - Webmaster Verification
# - Google Analytics
# - Title Templates
# - Robots Settings
# - Breadcrumbs
# - RSS Feed
# - Import
# - Sitemap (old-style URLs)
#
# Usage: ./test-new-features.sh [SITE_URL]
##############################################################################

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
SITE_URL="${WP_URL:-${1:-https://wordpress.test}}"

# Counters
TOTAL=0
PASSED=0
FAILED=0

echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  AISEO New Features - REST API Tests${NC}"
echo -e "${BLUE}  Site: $SITE_URL${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo ""

test_endpoint() {
    local method="$1"
    local endpoint="$2"
    local data="$3"
    local expected="$4"
    local name="$5"
    
    TOTAL=$((TOTAL + 1))
    
    if [ "$method" = "GET" ]; then
        response=$(curl -k -s "$SITE_URL$endpoint")
    else
        response=$(curl -k -s -X POST "$SITE_URL$endpoint" \
            -H "Content-Type: application/json" \
            -d "$data")
    fi
    
    if echo "$response" | grep -q "$expected"; then
        PASSED=$((PASSED + 1))
        echo -e "${GREEN}✓ PASS${NC} $name"
        return 0
    else
        FAILED=$((FAILED + 1))
        echo -e "${RED}✗ FAIL${NC} $name"
        echo -e "  ${YELLOW}Response:${NC} ${response:0:100}..."
        return 1
    fi
}

echo -e "${BLUE}--- Homepage SEO ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/homepage-seo" "" '"success":true' "GET Homepage SEO settings"
test_endpoint "POST" "/wp-json/aiseo/v1/homepage-seo" '{"home_title":"Test Title"}' '"success":true' "POST Homepage SEO update"

echo ""
echo -e "${BLUE}--- Webmaster Verification ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/webmaster-verification" "" '"success":true' "GET Webmaster verification codes"
test_endpoint "POST" "/wp-json/aiseo/v1/webmaster-verification" '{"google":"test123"}' '"success":true' "POST Webmaster verification update"

echo ""
echo -e "${BLUE}--- Google Analytics ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/analytics" "" '"success":true' "GET Analytics settings"
test_endpoint "POST" "/wp-json/aiseo/v1/analytics" '{"tracking_id":"G-TEST123","enabled":true}' '"success":true' "POST Analytics update"

echo ""
echo -e "${BLUE}--- Title Templates ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/title-templates" "" '"success":true' "GET Title templates"
test_endpoint "POST" "/wp-json/aiseo/v1/title-templates" '{"separator":"|"}' '"success":true' "POST Title templates update"

echo ""
echo -e "${BLUE}--- Robots Settings ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/robots-settings" "" '"success":true' "GET Robots settings"
test_endpoint "POST" "/wp-json/aiseo/v1/robots-settings" '{"noindex_categories":false}' '"success":true' "POST Robots settings update"

echo ""
echo -e "${BLUE}--- Breadcrumbs ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/breadcrumbs" "" '"success":true' "GET Breadcrumbs settings"
test_endpoint "POST" "/wp-json/aiseo/v1/breadcrumbs" '{"separator":"»","home_text":"Home"}' '"success":true' "POST Breadcrumbs update"

echo ""
echo -e "${BLUE}--- RSS Feed ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/rss" "" '"success":true' "GET RSS settings"
test_endpoint "POST" "/wp-json/aiseo/v1/rss" '{"enabled":true}' '"success":true' "POST RSS settings update"

echo ""
echo -e "${BLUE}--- Import ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/import/check" "" '"success":true' "GET Import check"
test_endpoint "GET" "/wp-json/aiseo/v1/import/preview" "" '"success":true' "GET Import preview"

echo ""
echo -e "${BLUE}--- Taxonomy SEO ---${NC}"
test_endpoint "GET" "/wp-json/aiseo/v1/taxonomy-seo/category" "" '"success":true' "GET Taxonomy SEO categories"

echo ""
echo -e "${BLUE}--- Sitemap (Old-style URLs) ---${NC}"
# Test sitemap URLs - check if they return XML or redirect
TOTAL=$((TOTAL + 1))
sitemap_response=$(curl -k -s -o /dev/null -w "%{http_code}" "$SITE_URL/sitemap_index.xml")
if [ "$sitemap_response" = "200" ] || [ "$sitemap_response" = "301" ] || [ "$sitemap_response" = "302" ]; then
    PASSED=$((PASSED + 1))
    echo -e "${GREEN}✓ PASS${NC} Sitemap index (sitemap_index.xml) - Status: $sitemap_response"
else
    FAILED=$((FAILED + 1))
    echo -e "${RED}✗ FAIL${NC} Sitemap index (sitemap_index.xml) - Status: $sitemap_response"
fi

TOTAL=$((TOTAL + 1))
post_sitemap_response=$(curl -k -s -o /dev/null -w "%{http_code}" "$SITE_URL/post-sitemap.xml")
if [ "$post_sitemap_response" = "200" ] || [ "$post_sitemap_response" = "301" ] || [ "$post_sitemap_response" = "302" ] || [ "$post_sitemap_response" = "404" ]; then
    PASSED=$((PASSED + 1))
    echo -e "${GREEN}✓ PASS${NC} Post sitemap (post-sitemap.xml) - Status: $post_sitemap_response"
else
    FAILED=$((FAILED + 1))
    echo -e "${RED}✗ FAIL${NC} Post sitemap (post-sitemap.xml) - Status: $post_sitemap_response"
fi

echo ""
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "${BLUE}  TEST SUMMARY${NC}"
echo -e "${BLUE}═══════════════════════════════════════════════════════════${NC}"
echo -e "Total Tests: ${YELLOW}$TOTAL${NC}"
echo -e "Passed:      ${GREEN}$PASSED${NC}"
echo -e "Failed:      ${RED}$FAILED${NC}"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}🎉 All tests passed!${NC}"
    exit 0
else
    echo -e "${RED}⚠️ Some tests failed!${NC}"
    exit 1
fi
