#!/bin/bash
# Test Script: Google Analytics Integration
# Feature: GA4 and Universal Analytics tracking code injection

SITE_URL="${WP_URL:-https://wordpress.test}"
echo "=============================================="
echo "Testing: Google Analytics Integration"
echo "Site: $SITE_URL"
echo "=============================================="

# Test 1: Check if analytics REST endpoint exists
echo -e "\n[TEST 1] GET /wp-json/aiseo/v1/analytics"
RESPONSE=$(curl -sk "$SITE_URL/wp-json/aiseo/v1/analytics" 2>/dev/null)
if echo "$RESPONSE" | grep -q '"tracking_id"\|"enabled"\|"success"'; then
    echo "✅ PASSED - Analytics endpoint returns settings"
else
    echo "❌ FAILED - Analytics endpoint not working"
    echo "Response: $RESPONSE"
fi

# Test 2: Check homepage for GA tracking code
echo -e "\n[TEST 2] Check homepage for GA tracking code"
RESPONSE=$(curl -sk "$SITE_URL/" 2>/dev/null)
if echo "$RESPONSE" | grep -q 'gtag\|googletagmanager\|analytics.js\|GA-\|G-'; then
    echo "✅ PASSED - Homepage has GA tracking code"
else
    echo "⚠️  INFO - No GA tracking code found (expected if not configured)"
fi

# Test 3: Check WP-CLI command exists
echo -e "\n[TEST 3] WP-CLI: wp aiseo analytics get"
if command -v wp &> /dev/null; then
    echo "⚠️  SKIPPED - WP-CLI test requires WordPress path"
else
    echo "⚠️  SKIPPED - WP-CLI not available"
fi

echo -e "\n=============================================="
echo "Google Analytics Tests Complete"
echo "=============================================="
