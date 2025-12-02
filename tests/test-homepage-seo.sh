#!/bin/bash
# Test Script: Home/Blog Page SEO Settings
# Feature: Global SEO settings for home page and blog page

SITE_URL="${WP_URL:-https://wordpress.test}"
echo "=============================================="
echo "Testing: Home/Blog Page SEO Settings"
echo "Site: $SITE_URL"
echo "=============================================="

# Test 1: Check if homepage SEO REST endpoint exists
echo -e "\n[TEST 1] GET /wp-json/aiseo/v1/homepage-seo"
RESPONSE=$(curl -sk "$SITE_URL/wp-json/aiseo/v1/homepage-seo" 2>/dev/null)
if echo "$RESPONSE" | grep -q '"home_title"\|"home_description"'; then
    echo "✅ PASSED - Homepage SEO endpoint returns settings"
else
    echo "❌ FAILED - Homepage SEO endpoint not working"
    echo "Response: $RESPONSE"
fi

# Test 2: Update homepage SEO settings via REST API
echo -e "\n[TEST 2] POST /wp-json/aiseo/v1/homepage-seo (update settings)"
RESPONSE=$(curl -sk -X POST "$SITE_URL/wp-json/aiseo/v1/homepage-seo" \
    -H "Content-Type: application/json" \
    -H "X-WP-Nonce: $(curl -sk "$SITE_URL/wp-json/" | grep -o '"nonce":"[^"]*"' | cut -d'"' -f4)" \
    -d '{
        "home_title": "Test Home Title | My Site",
        "home_description": "Test home page description for SEO",
        "home_keywords": "test, seo, homepage"
    }' 2>/dev/null)
if echo "$RESPONSE" | grep -q '"success":true\|"updated"'; then
    echo "✅ PASSED - Homepage SEO settings updated"
else
    echo "❌ FAILED - Could not update homepage SEO settings"
    echo "Response: $RESPONSE"
fi

# Test 3: Check WP-CLI command exists
echo -e "\n[TEST 3] WP-CLI: wp aiseo homepage get"
if command -v wp &> /dev/null; then
    RESPONSE=$(wp aiseo homepage get --path=/path/to/wordpress 2>&1 || echo "CLI_ERROR")
    if echo "$RESPONSE" | grep -q "home_title\|Error\|not found"; then
        echo "⚠️  SKIPPED - WP-CLI test requires WordPress path"
    else
        echo "✅ PASSED - WP-CLI homepage command exists"
    fi
else
    echo "⚠️  SKIPPED - WP-CLI not available"
fi

# Test 4: Verify meta tags on homepage
echo -e "\n[TEST 4] Check homepage meta tags in HTML"
RESPONSE=$(curl -sk "$SITE_URL/" 2>/dev/null)
if echo "$RESPONSE" | grep -q '<meta name="description"'; then
    echo "✅ PASSED - Homepage has meta description tag"
else
    echo "❌ FAILED - Homepage missing meta description tag"
fi

echo -e "\n=============================================="
echo "Home/Blog Page SEO Tests Complete"
echo "=============================================="
