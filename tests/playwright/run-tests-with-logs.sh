#!/bin/bash

# AISEO Playwright Tests with Log Monitoring
# This script runs Playwright tests and monitors WordPress debug.log simultaneously

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Paths
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
WP_LOG="/Users/praison/Sites/localhost/wordpress/wp-content/debug.log"
TEST_LOG="$SCRIPT_DIR/../logs/test-run-$(date +%Y%m%d-%H%M%S).log"

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}🧪 AISEO PLAYWRIGHT TESTS${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Check if WordPress log exists
if [ ! -f "$WP_LOG" ]; then
    echo -e "${RED}❌ WordPress debug.log not found at: $WP_LOG${NC}"
    echo -e "${YELLOW}Make sure WP_DEBUG_LOG is enabled in wp-config.php${NC}"
    exit 1
fi

# Create logs directory
mkdir -p "$SCRIPT_DIR/../logs"

# Clear old WordPress log entries (optional)
echo -e "${YELLOW}📝 Marking log position...${NC}"
LOG_START_LINE=$(wc -l < "$WP_LOG")
echo "Starting from line: $LOG_START_LINE"
echo ""

# Start log monitoring in background
echo -e "${BLUE}📊 Starting log monitor...${NC}"
tail -f "$WP_LOG" | grep --line-buffered -E "🔵|🔴|AISEO|403|500|Error|Nonce" > "$TEST_LOG" &
LOG_PID=$!

# Give it a moment to start
sleep 1

echo -e "${GREEN}✅ Log monitor started (PID: $LOG_PID)${NC}"
echo -e "${BLUE}📝 Logs being saved to: $TEST_LOG${NC}"
echo ""

# Function to cleanup on exit
cleanup() {
    echo ""
    echo -e "${YELLOW}🛑 Stopping log monitor...${NC}"
    kill $LOG_PID 2>/dev/null || true
    
    echo ""
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}📊 TEST SUMMARY${NC}"
    echo -e "${BLUE}========================================${NC}"
    
    # Count log entries
    if [ -f "$TEST_LOG" ]; then
        TOTAL_LINES=$(wc -l < "$TEST_LOG")
        ERRORS=$(grep -c "Error\|403\|500" "$TEST_LOG" || true)
        AJAX_REQUESTS=$(grep -c "🔵 GLOBAL AJAX LOGGER" "$TEST_LOG" || true)
        
        echo -e "Total log entries: ${YELLOW}$TOTAL_LINES${NC}"
        echo -e "AJAX requests: ${BLUE}$AJAX_REQUESTS${NC}"
        echo -e "Errors: ${RED}$ERRORS${NC}"
        echo ""
        
        if [ $ERRORS -gt 0 ]; then
            echo -e "${RED}❌ ERRORS FOUND IN LOGS:${NC}"
            grep -E "Error|403|500" "$TEST_LOG" | head -10
            echo ""
        fi
        
        echo -e "${GREEN}Full logs saved to:${NC} $TEST_LOG"
    fi
    
    echo -e "${BLUE}========================================${NC}"
}

trap cleanup EXIT INT TERM

# Run Playwright tests
echo -e "${GREEN}🚀 Starting Playwright tests...${NC}"
echo ""

cd "$SCRIPT_DIR"

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo -e "${YELLOW}📦 Installing dependencies...${NC}"
    npm install
    npx playwright install chromium
    echo ""
fi

# Run tests
npx playwright test --headed

echo ""
echo -e "${GREEN}✅ Tests completed!${NC}"
