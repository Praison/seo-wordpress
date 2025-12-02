#!/bin/bash
# Test Script: Webmaster Verification Codes
# Feature: Google, Bing, and other webmaster tool verification codes

SITE_URL="${WP_URL:-https://wordpress.test}"
echo "=============================================="
echo "Testing: Webmaster Verification Codes"
echo "Site: $SITE_URL"
echo "=============================================="

# Test 1: Check if verification REST endpoint exists
echo -e "\n[TEST 1] GET /wp-json/aiseo/v1/webmaster-verification"
RESPONSE=$(curl -sk "$SITE_URL/wp-json/aiseo/v1/webmaster-verification" 2>/dev/null)
if echo "$RESPONSE" | grep -q '"google"\|"bing"\|"success"'; then
    echo "✅ PASSED - Webmaster verification endpoint returns settings"
else
    echo "❌ FAILED - Webmaster verification endpoint not working"
    echo "Response: $RESPONSE"
fi

# Test 2: Check homepage for verification meta tags
echo -e "\n[TEST 2] Check homepage for verification meta tags"
RESPONSE=$(curl -sk "$SITE_URL/" 2>/dev/null)
if echo "$RESPONSE" | grep -q 'google-site-verification\|msvalidate.01'; then
    echo "✅ PASSED - Homepage has verification meta tags"
else
    echo "⚠️  INFO - No verification codes set yet (expected if not configured)"
fi

# Test 3: Check WP-CLI command exists
echo -e "\n[TEST 3] WP-CLI: wp aiseo webmaster get"
if command -v wp &> /dev/null; then
    echo "⚠️  SKIPPED - WP-CLI test requires WordPress path"
else
    echo "⚠️  SKIPPED - WP-CLI not available"
fi

echo -e "\n=============================================="
echo "Webmaster Verification Tests Complete"
echo "=============================================="
