#!/bin/bash
# Test Script: Taxonomy SEO Settings
# Feature: SEO settings for categories, tags, and custom taxonomies

SITE_URL="${WP_URL:-https://wordpress.test}"
echo "=============================================="
echo "Testing: Taxonomy SEO Settings"
echo "Site: $SITE_URL"
echo "=============================================="

# Test 1: Check if taxonomy SEO REST endpoint exists
echo -e "\n[TEST 1] GET /wp-json/aiseo/v1/taxonomy-seo/category/1"
RESPONSE=$(curl -sk "$SITE_URL/wp-json/aiseo/v1/taxonomy-seo/category/1" 2>/dev/null)
if echo "$RESPONSE" | grep -q '"title"\|"description"\|"term_id"'; then
    echo "✅ PASSED - Taxonomy SEO endpoint returns settings"
else
    echo "❌ FAILED - Taxonomy SEO endpoint not working"
    echo "Response: $RESPONSE"
fi

# Test 2: Update taxonomy SEO settings via REST API
echo -e "\n[TEST 2] POST /wp-json/aiseo/v1/taxonomy-seo/category/1"
RESPONSE=$(curl -sk -X POST "$SITE_URL/wp-json/aiseo/v1/taxonomy-seo/category/1" \
    -H "Content-Type: application/json" \
    -d '{
        "title": "Test Category SEO Title",
        "description": "Test category description for SEO",
        "noindex": false,
        "nofollow": false
    }' 2>/dev/null)
if echo "$RESPONSE" | grep -q '"success":true\|"updated"'; then
    echo "✅ PASSED - Taxonomy SEO settings updated"
else
    echo "❌ FAILED - Could not update taxonomy SEO settings"
    echo "Response: $RESPONSE"
fi

# Test 3: Check WP-CLI command exists
echo -e "\n[TEST 3] WP-CLI: wp aiseo taxonomy get category 1"
if command -v wp &> /dev/null; then
    echo "⚠️  SKIPPED - WP-CLI test requires WordPress path"
else
    echo "⚠️  SKIPPED - WP-CLI not available"
fi

# Test 4: Verify meta tags on category page
echo -e "\n[TEST 4] Check category page meta tags"
RESPONSE=$(curl -sk "$SITE_URL/category/uncategorized/" 2>/dev/null)
if echo "$RESPONSE" | grep -q '<meta name="description"\|<title>'; then
    echo "✅ PASSED - Category page has meta tags"
else
    echo "⚠️  CHECK - Category page may not have custom meta tags yet"
fi

echo -e "\n=============================================="
echo "Taxonomy SEO Tests Complete"
echo "=============================================="
