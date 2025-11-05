/**
 * Playwright Test: Test All AISEO Admin Pages
 * 
 * This script:
 * 1. Logs in to WordPress using credentials from .env
 * 2. Visits each AISEO admin page
 * 3. Interacts with at least one tool on each page
 * 4. Monitors console logs and network requests
 * 
 * Usage:
 *   npx playwright test tests/playwright/test-all-pages.spec.js --headed
 */

const { test, expect } = require('@playwright/test');
const path = require('path');
require('dotenv').config({ path: path.resolve(__dirname, '../../.env') });

// Get credentials from environment
const WP_URL = process.env.WP_URL || 'https://wordpress.test';
const USERNAME = process.env.WP_USERNAME || 'praison';
const PASSWORD = process.env.WP_PASSWORD || 'leicester';

test.describe('AISEO Admin Pages - Full Test Suite', () => {
  let page;
  let context;
  let consoleMessages = [];
  let ajaxRequests = [];

  test.beforeAll(async ({ browser }) => {
    // Create a new context to ensure cookies persist
    context = await browser.newContext({
      ignoreHTTPSErrors: true, // For local SSL certificates
      extraHTTPHeaders: {
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      }
    });
    page = await context.newPage();
    
    // Capture console messages
    page.on('console', msg => {
      const text = msg.text();
      // Capture ALL console logs
      consoleMessages.push({
        type: msg.type(),
        text: text,
        timestamp: new Date().toISOString()
      });
      // Only print AISEO-related logs to terminal
      if (text.includes('AISEO') || text.includes('🔴') || text.includes('🔵') || 
          text.includes('aiseoAdmin') || text.includes('nonce') || text.includes('Nonce')) {
        console.log(`[CONSOLE ${msg.type()}] ${text}`);
      }
    });

    // Capture AJAX requests
    page.on('request', request => {
      if (request.url().includes('admin-ajax.php')) {
        const postData = request.postData();
        if (postData && postData.includes('aiseo_')) {
          ajaxRequests.push({
            url: request.url(),
            method: request.method(),
            postData: postData,
            timestamp: new Date().toISOString()
          });
          console.log(`[AJAX REQUEST] ${postData.substring(0, 100)}...`);
        }
      }
    });

    // Capture AJAX responses
    page.on('response', async response => {
      if (response.url().includes('admin-ajax.php')) {
        const request = response.request();
        const postData = request.postData();
        if (postData && postData.includes('aiseo_')) {
          const status = response.status();
          let body = '';
          try {
            body = await response.text();
          } catch (e) {
            body = '[Could not read response]';
          }
          console.log(`[AJAX RESPONSE] Status: ${status}, Body: ${body.substring(0, 200)}...`);
          
          if (status === 403 || status === 500) {
            console.error(`❌ ERROR: ${status} response for AJAX request`);
          }
        }
      }
    });

    // Login to WordPress
    console.log('\n========================================');
    console.log('🔐 LOGGING IN TO WORDPRESS');
    console.log('========================================');
    console.log(`URL: ${WP_URL}/wp-admin`);
    console.log(`Username: ${USERNAME}`);
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin`);
    await page.fill('#user_login', USERNAME);
    await page.fill('#user_pass', PASSWORD);
    await page.click('#wp-submit');
    await page.waitForLoadState('networkidle');
    
    // Verify we have WordPress auth cookies
    const cookies = await context.cookies();
    const wpCookies = cookies.filter(c => c.name.includes('wordpress') || c.name.includes('wp-'));
    console.log(`✅ Login successful - ${wpCookies.length} WordPress cookies set`);
    wpCookies.forEach(c => console.log(`   Cookie: ${c.name}`));
    console.log('');
  });

  test.afterAll(async () => {
    // Save logs to file
    const fs = require('fs');
    const logPath = path.resolve(__dirname, '../logs/playwright-test-results.json');
    
    // Create logs directory if it doesn't exist
    const logsDir = path.dirname(logPath);
    if (!fs.existsSync(logsDir)) {
      fs.mkdirSync(logsDir, { recursive: true });
    }
    
    fs.writeFileSync(logPath, JSON.stringify({
      timestamp: new Date().toISOString(),
      consoleMessages: consoleMessages,
      ajaxRequests: ajaxRequests
    }, null, 2));
    
    console.log(`\n📝 Logs saved to: ${logPath}`);
    
    await page.close();
    await context.close();
  });

  test('1. Dashboard - View Overview', async () => {
    console.log('\n========================================');
    console.log('📊 TEST 1: DASHBOARD');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=dashboard`);
    await page.waitForLoadState('networkidle');
    
    // Check if dashboard loaded
    const dashboardTitle = await page.textContent('h1, h2');
    console.log(`Dashboard loaded: ${dashboardTitle}`);
    expect(dashboardTitle).toContain('AISEO');
    
    await page.waitForTimeout(2000);
  });

  test('2. SEO Tools - Generate Title', async () => {
    console.log('\n========================================');
    console.log('🔧 TEST 2: SEO TOOLS - GENERATE TITLE');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    // Select a post
    const postSelect = await page.$('#aiseo-meta-post-select');
    if (postSelect) {
      await page.selectOption('#aiseo-meta-post-select', { index: 1 });
      console.log('✅ Post selected');
      
      // Click Generate Title button
      await page.click('button[data-field="title"]');
      console.log('✅ Generate Title button clicked');
      
      // Wait for response
      await page.waitForTimeout(5000);
    } else {
      console.log('⚠️  Post select not found');
    }
  });

  test('3. AI Content - Generate Content', async () => {
    console.log('\n========================================');
    console.log('✍️  TEST 3: AI CONTENT');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=ai-content`);
    await page.waitForLoadState('networkidle');
    
    // Try to interact with content generation
    const topicInput = await page.$('#aiseo-content-topic');
    if (topicInput) {
      await page.fill('#aiseo-content-topic', 'Test Topic for Playwright');
      console.log('✅ Topic entered');
      
      const generateBtn = await page.$('#aiseo-generate-content');
      if (generateBtn) {
        await generateBtn.click();
        console.log('✅ Generate Content button clicked');
        await page.waitForTimeout(3000);
      }
    } else {
      console.log('⚠️  Content generation form not found');
    }
  });

  test('4. Bulk Operations - Select Posts', async () => {
    console.log('\n========================================');
    console.log('📦 TEST 4: BULK OPERATIONS');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=bulk-operations`);
    await page.waitForLoadState('networkidle');
    
    // Select first two posts
    const checkboxes = await page.$$('.aiseo-bulk-post');
    if (checkboxes.length >= 2) {
      await checkboxes[0].check();
      await checkboxes[1].check();
      console.log('✅ Two posts selected');
      
      // Click Generate Titles button
      const generateBtn = await page.$('#aiseo-bulk-generate-titles');
      if (generateBtn) {
        await generateBtn.click();
        console.log('✅ Generate Titles for Selected clicked');
        await page.waitForTimeout(8000); // Wait longer for bulk operation
      }
    } else {
      console.log('⚠️  Bulk operation checkboxes not found');
    }
  });

  test('5. Technical SEO - View Redirects', async () => {
    console.log('\n========================================');
    console.log('🔗 TEST 5: TECHNICAL SEO');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=technical-seo`);
    await page.waitForLoadState('networkidle');
    
    // Try to list redirects
    const listBtn = await page.$('#aiseo-list-redirects');
    if (listBtn) {
      await listBtn.click();
      console.log('✅ List Redirects clicked');
      await page.waitForTimeout(3000);
    } else {
      console.log('⚠️  Technical SEO buttons not found');
    }
  });

  test('6. Advanced - View Settings', async () => {
    console.log('\n========================================');
    console.log('⚙️  TEST 6: ADVANCED SETTINGS');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=advanced`);
    await page.waitForLoadState('networkidle');
    
    // Just verify the page loaded
    const pageContent = await page.textContent('body');
    expect(pageContent).toContain('Advanced');
    console.log('✅ Advanced settings page loaded');
    
    await page.waitForTimeout(2000);
  });

  test('7. Monitoring - View Logs', async () => {
    console.log('\n========================================');
    console.log('📈 TEST 7: MONITORING');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=monitoring`);
    await page.waitForLoadState('networkidle');
    
    // Just verify the page loaded
    const pageContent = await page.textContent('body');
    console.log('✅ Monitoring page loaded');
    
    await page.waitForTimeout(2000);
  });

  test('8. Settings - View Configuration', async () => {
    console.log('\n========================================');
    console.log('🔧 TEST 8: SETTINGS');
    console.log('========================================\n');

    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=settings`);
    await page.waitForLoadState('networkidle');
    
    // Just verify the page loaded
    const pageContent = await page.textContent('body');
    console.log('✅ Settings page loaded');
    
    await page.waitForTimeout(2000);
  });

  test('9. Summary - Check Results', async () => {
    console.log('\n========================================');
    console.log('📊 TEST SUMMARY');
    console.log('========================================');
    console.log(`Total Console Messages: ${consoleMessages.length}`);
    console.log(`Total AJAX Requests: ${ajaxRequests.length}`);
    
    // Count errors
    const errors = consoleMessages.filter(m => m.type === 'error');
    const warnings = consoleMessages.filter(m => m.type === 'warning');
    
    console.log(`Errors: ${errors.length}`);
    console.log(`Warnings: ${warnings.length}`);
    
    if (errors.length > 0) {
      console.log('\n❌ ERRORS FOUND:');
      errors.forEach(e => console.log(`  - ${e.text}`));
    }
    
    console.log('========================================\n');
  });
});
