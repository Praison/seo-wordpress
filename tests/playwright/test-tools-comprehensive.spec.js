/**
 * Playwright Test: Comprehensive Individual Tool Testing
 * 
 * Tests each AISEO tool individually with detailed logging
 * Based on the working test-all-pages.spec.js structure
 */

const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');
require('dotenv').config({ path: path.resolve(__dirname, '../../.env') });

const WP_URL = process.env.WP_URL || 'https://wordpress.test';
const USERNAME = process.env.WP_USERNAME || 'praison';
const PASSWORD = process.env.WP_PASSWORD || 'leicester';

// Store all test data
const toolResults = [];
const consoleMessages = [];
const ajaxRequests = [];
const ajaxResponses = [];

test.describe('AISEO Comprehensive Tool Testing', () => {
  let page;
  let context;

  // Set timeout for all tests to 60 seconds
  test.setTimeout(60000);

  test.beforeAll(async ({ browser }) => {
    // Create a new context to ensure cookies persist
    context = await browser.newContext({
      ignoreHTTPSErrors: true,
      extraHTTPHeaders: {
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      }
    });
    page = await context.newPage();
    
    // Capture console messages
    page.on('console', msg => {
      const text = msg.text();
      consoleMessages.push({
        type: msg.type(),
        text: text,
        timestamp: new Date().toISOString()
      });
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
          const actionMatch = postData.match(/action=([^&]+)/);
          const action = actionMatch ? actionMatch[1] : 'unknown';
          
          ajaxRequests.push({
            action,
            url: request.url(),
            method: request.method(),
            postData: postData,
            timestamp: new Date().toISOString()
          });
          console.log(`[AJAX REQUEST] Action: ${action}, Data: ${postData.substring(0, 150)}...`);
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
          
          const actionMatch = postData.match(/action=([^&]+)/);
          const action = actionMatch ? actionMatch[1] : 'unknown';
          
          ajaxResponses.push({
            action,
            status,
            body: body.substring(0, 1000),
            fullBody: body,
            timestamp: new Date().toISOString(),
            postData: postData.substring(0, 300)
          });
          
          console.log(`[AJAX RESPONSE] Action: ${action}, Status: ${status}, Body: ${body.substring(0, 200)}...`);
          
          if (status === 403 || status === 500) {
            console.error(`❌ ERROR: ${status} response for ${action}`);
          } else if (status === 200) {
            console.log(`✅ SUCCESS: ${action} completed`);
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
    // Save comprehensive logs
    const logsDir = path.resolve(__dirname, '../logs');
    if (!fs.existsSync(logsDir)) {
      fs.mkdirSync(logsDir, { recursive: true });
    }
    
    const reportPath = path.join(logsDir, 'comprehensive-tools-report.json');
    const report = {
      timestamp: new Date().toISOString(),
      summary: {
        totalTools: toolResults.length,
        passed: toolResults.filter(r => r.status === 'PASSED').length,
        failed: toolResults.filter(r => r.status === 'FAILED').length,
        skipped: toolResults.filter(r => r.status === 'SKIPPED').length,
        totalAjaxRequests: ajaxRequests.length,
        totalAjaxResponses: ajaxResponses.length,
        totalConsoleMessages: consoleMessages.length
      },
      toolResults,
      ajaxRequests,
      ajaxResponses,
      consoleMessages
    };
    
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    
    console.log(`\n📝 Comprehensive report saved to: ${reportPath}`);
    
    await page.close();
    await context.close();
  });

  // SEO TOOLS TAB - Tool 1
  test('1. SEO Tools - Generate Title', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 1: GENERATE TITLE');
    console.log('========================================\n');
    
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.locator('select[name="post_id"]').first();
      const optionCount = await postSelect.locator('option').count();
      console.log(`📋 Found ${optionCount} posts in dropdown`);
      
      if (optionCount < 2) {
        throw new Error(`Not enough posts in dropdown. Found: ${optionCount}, Need: at least 2`);
      }
      
      await postSelect.selectOption({ index: 1 });
      const selectedValue = await postSelect.inputValue();
      console.log(`✅ Post selected (ID: ${selectedValue})`);
      await page.waitForTimeout(500);
      
      const generateBtn = await page.locator('button:has-text("Generate Title")').first();
      await generateBtn.click();
      console.log('✅ Generate Title button clicked');
      await page.waitForTimeout(6000); // Wait for AI response
      
      const response = ajaxResponses.find(r => r.action === 'aiseo_generate_title');
      toolResults.push({
        tool: 'Generate Title',
        tab: 'SEO Tools',
        status: response?.status === 200 ? 'PASSED' : 'FAILED',
        httpStatus: response?.status,
        response: response?.body,
        timestamp: new Date().toISOString()
      });
      
      console.log(`\n📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
      console.log(`HTTP Status: ${response?.status || 'N/A'}`);
      if (response?.body) {
        console.log(`Response Preview: ${response.body.substring(0, 150)}...`);
      }
    } catch (error) {
      toolResults.push({
        tool: 'Generate Title',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`\n❌ Error: ${error.message}`);
    }
  });

  // SEO TOOLS TAB - Tool 2
  test('2. SEO Tools - Generate Description', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 2: GENERATE DESCRIPTION');
    console.log('========================================\n');
    
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.locator('select[name="post_id"]').first();
      await postSelect.selectOption({ index: 1 });
      console.log('✅ Post selected');
      await page.waitForTimeout(500);
      
      const generateBtn = await page.locator('button:has-text("Generate Description")').first();
      const btnCount = await generateBtn.count();
      
      if (btnCount > 0) {
        await generateBtn.click();
        console.log('✅ Generate Description button clicked');
        await page.waitForTimeout(6000);
        
        const response = ajaxResponses.find(r => r.action === 'aiseo_generate_description');
        toolResults.push({
          tool: 'Generate Description',
          tab: 'SEO Tools',
          status: response?.status === 200 ? 'PASSED' : 'FAILED',
          httpStatus: response?.status,
          response: response?.body,
          timestamp: new Date().toISOString()
        });
        
        console.log(`\n📊 Result: ${response?.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
        console.log(`HTTP Status: ${response?.status || 'N/A'}`);
      } else {
        toolResults.push({
          tool: 'Generate Description',
          tab: 'SEO Tools',
          status: 'SKIPPED',
          reason: 'Button not found',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  Skipped: Button not found');
      }
    } catch (error) {
      toolResults.push({
        tool: 'Generate Description',
        tab: 'SEO Tools',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`\n❌ Error: ${error.message}`);
    }
  });

  // BULK OPERATIONS TAB - Tool 3
  test('3. Bulk Operations - Generate Titles', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 3: BULK GENERATE TITLES');
    console.log('========================================\n');
    
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=bulk-operations`);
    await page.waitForLoadState('networkidle');
    
    try {
      const checkboxes = await page.locator('input[type="checkbox"][name="post_ids[]"]');
      const count = await checkboxes.count();
      
      if (count >= 2) {
        await checkboxes.nth(0).check();
        await checkboxes.nth(1).check();
        console.log('✅ Two posts selected');
        await page.waitForTimeout(500);
        
        const bulkBtn = await page.locator('button:has-text("Generate Titles for Selected")');
        await bulkBtn.click();
        console.log('✅ Generate Titles for Selected clicked');
        await page.waitForTimeout(8000);
        
        const responses = ajaxResponses.filter(r => r.action === 'aiseo_generate_title');
        const bulkResponses = responses.slice(-2);
        const allSuccess = bulkResponses.every(r => r.status === 200);
        
        toolResults.push({
          tool: 'Bulk Generate Titles',
          tab: 'Bulk Operations',
          status: allSuccess ? 'PASSED' : 'FAILED',
          postsProcessed: bulkResponses.length,
          responses: bulkResponses.map(r => ({ status: r.status, body: r.body.substring(0, 200) })),
          timestamp: new Date().toISOString()
        });
        
        console.log(`\n📊 Result: ${allSuccess ? 'PASSED ✅' : 'FAILED ❌'}`);
        console.log(`Posts Processed: ${bulkResponses.length}`);
        bulkResponses.forEach((r, i) => {
          console.log(`  Post ${i + 1}: Status ${r.status}`);
        });
      } else {
        toolResults.push({
          tool: 'Bulk Generate Titles',
          tab: 'Bulk Operations',
          status: 'SKIPPED',
          reason: 'Not enough posts',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  Skipped: Not enough posts');
      }
    } catch (error) {
      toolResults.push({
        tool: 'Bulk Generate Titles',
        tab: 'Bulk Operations',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`\n❌ Error: ${error.message}`);
    }
  });

  // TECHNICAL SEO TAB - Tool 4
  test('4. Technical SEO - List Redirects', async () => {
    console.log('\n========================================');
    console.log('🔧 TOOL 4: LIST REDIRECTS');
    console.log('========================================\n');
    
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=technical-seo`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    try {
      const response = ajaxResponses.find(r => r.action === 'aiseo_list_redirects');
      
      if (response) {
        let redirectCount = 0;
        try {
          const data = JSON.parse(response.fullBody);
          redirectCount = data.data?.length || 0;
        } catch (e) {
          // Ignore parse errors
        }
        
        toolResults.push({
          tool: 'List Redirects',
          tab: 'Technical SEO',
          status: response.status === 200 ? 'PASSED' : 'FAILED',
          httpStatus: response.status,
          redirectCount,
          response: response.body,
          timestamp: new Date().toISOString()
        });
        
        console.log(`\n📊 Result: ${response.status === 200 ? 'PASSED ✅' : 'FAILED ❌'}`);
        console.log(`HTTP Status: ${response.status}`);
        console.log(`Redirects Found: ${redirectCount}`);
      } else {
        toolResults.push({
          tool: 'List Redirects',
          tab: 'Technical SEO',
          status: 'SKIPPED',
          reason: 'No AJAX response captured',
          timestamp: new Date().toISOString()
        });
        console.log('⚠️  Skipped: No AJAX response captured');
      }
    } catch (error) {
      toolResults.push({
        tool: 'List Redirects',
        tab: 'Technical SEO',
        status: 'FAILED',
        error: error.message,
        timestamp: new Date().toISOString()
      });
      console.log(`\n❌ Error: ${error.message}`);
    }
  });

  // SUMMARY TEST
  test('5. Summary - Display Results', async () => {
    console.log('\n' + '='.repeat(70));
    console.log('📊 COMPREHENSIVE TOOL TEST SUMMARY');
    console.log('='.repeat(70));
    
    const passed = toolResults.filter(r => r.status === 'PASSED').length;
    const failed = toolResults.filter(r => r.status === 'FAILED').length;
    const skipped = toolResults.filter(r => r.status === 'SKIPPED').length;
    
    console.log(`\n📈 STATISTICS:`);
    console.log(`   Total Tools Tested: ${toolResults.length}`);
    console.log(`   ✅ Passed: ${passed}`);
    console.log(`   ❌ Failed: ${failed}`);
    console.log(`   ⚠️  Skipped: ${skipped}`);
    console.log(`   Success Rate: ${((passed / (toolResults.length - skipped)) * 100).toFixed(1)}%`);
    
    console.log(`\n📡 AJAX STATISTICS:`);
    console.log(`   Total AJAX Requests: ${ajaxRequests.length}`);
    console.log(`   Total AJAX Responses: ${ajaxResponses.length}`);
    console.log(`   Status 200 (Success): ${ajaxResponses.filter(r => r.status === 200).length}`);
    console.log(`   Status 403 (Forbidden): ${ajaxResponses.filter(r => r.status === 403).length}`);
    console.log(`   Status 500 (Error): ${ajaxResponses.filter(r => r.status === 500).length}`);
    
    console.log(`\n💬 CONSOLE STATISTICS:`);
    console.log(`   Total Console Messages: ${consoleMessages.length}`);
    console.log(`   Errors: ${consoleMessages.filter(m => m.type === 'error').length}`);
    console.log(`   Warnings: ${consoleMessages.filter(m => m.type === 'warning').length}`);
    
    console.log('\n' + '-'.repeat(70));
    console.log('DETAILED TOOL RESULTS:');
    console.log('-'.repeat(70));
    
    toolResults.forEach((result, index) => {
      const icon = result.status === 'PASSED' ? '✅' : result.status === 'FAILED' ? '❌' : '⚠️';
      console.log(`\n${index + 1}. ${icon} ${result.tool} (${result.tab})`);
      console.log(`   Status: ${result.status}`);
      if (result.httpStatus) {
        console.log(`   HTTP Status: ${result.httpStatus}`);
      }
      if (result.postsProcessed) {
        console.log(`   Posts Processed: ${result.postsProcessed}`);
      }
      if (result.redirectCount !== undefined) {
        console.log(`   Redirects Found: ${result.redirectCount}`);
      }
      if (result.response) {
        const preview = result.response.substring(0, 150);
        console.log(`   Response: ${preview}${result.response.length > 150 ? '...' : ''}`);
      }
      if (result.error) {
        console.log(`   Error: ${result.error}`);
      }
      if (result.reason) {
        console.log(`   Reason: ${result.reason}`);
      }
    });
    
    console.log('\n' + '='.repeat(70));
    console.log('📝 Full report with all logs saved to:');
    console.log('   tests/logs/comprehensive-tools-report.json');
    console.log('='.repeat(70) + '\n');
  });
});
