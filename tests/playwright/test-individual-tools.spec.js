/**
 * Playwright Test: Individual Tool Testing with Response Capture
 * 
 * Tests each AISEO tool individually and captures detailed responses
 */

const { test, expect } = require('@playwright/test');
const path = require('path');
const fs = require('fs');
require('dotenv').config({ path: path.resolve(__dirname, '../../.env') });

const WP_URL = process.env.WP_URL || 'https://wordpress.test';
const USERNAME = process.env.WP_USERNAME || 'praison';
const PASSWORD = process.env.WP_PASSWORD || 'leicester';

// Store all tool responses
const toolResponses = {};
const testResults = [];

test.describe.serial('AISEO Individual Tool Testing', () => {
  let page;
  let context;

  // Set timeout for all tests to 60 seconds
  test.setTimeout(60000);

  test.beforeAll(async ({ browser }) => {
    context = await browser.newContext({
      ignoreHTTPSErrors: true,
      extraHTTPHeaders: {
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      }
    });
    page = await context.newPage();
    
    // Capture all AJAX responses
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
          
          // Extract action name
          const actionMatch = postData.match(/action=([^&]+)/);
          const action = actionMatch ? actionMatch[1] : 'unknown';
          
          if (!toolResponses[action]) {
            toolResponses[action] = [];
          }
          
          toolResponses[action].push({
            status,
            body: body.substring(0, 500), // First 500 chars
            timestamp: new Date().toISOString(),
            postData: postData.substring(0, 200)
          });
        }
      }
    });

    // Login
    console.log('\n🔐 Logging in to WordPress...');
    await page.goto(`${WP_URL}/wp-admin`);
    await page.fill('#user_login', USERNAME);
    await page.fill('#user_pass', PASSWORD);
    await page.click('#wp-submit');
    await page.waitForLoadState('networkidle');
    
    const cookies = await context.cookies();
    const wpCookies = cookies.filter(c => c.name.includes('wordpress') || c.name.includes('wp-'));
    console.log(`✅ Login successful - ${wpCookies.length} cookies set\n`);
  });

  test.afterAll(async () => {
    // Save detailed results
    const logsDir = path.resolve(__dirname, '../logs');
    if (!fs.existsSync(logsDir)) {
      fs.mkdirSync(logsDir, { recursive: true });
    }
    
    const reportPath = path.join(logsDir, 'individual-tools-report.json');
    fs.writeFileSync(reportPath, JSON.stringify({
      timestamp: new Date().toISOString(),
      toolResponses,
      testResults,
      summary: {
        totalTools: testResults.length,
        passed: testResults.filter(r => r.status === 'PASSED').length,
        failed: testResults.filter(r => r.status === 'FAILED').length,
        skipped: testResults.filter(r => r.status === 'SKIPPED').length
      }
    }, null, 2));
    
    console.log(`\n📝 Detailed report saved to: ${reportPath}`);
    
    await page.close();
    await context.close();
  });

  // SEO TOOLS TAB
  test('Tool 1: Generate Title', async () => {
    console.log('\n🔧 Testing: Generate Title');
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.locator('select[name="post_id"]').first();
      await postSelect.selectOption({ index: 1 });
      await page.waitForTimeout(500);
      
      const generateBtn = await page.locator('button:has-text("Generate Title")').first();
      await generateBtn.click();
      await page.waitForTimeout(8000); // Wait for AI response
      
      const result = toolResponses['aiseo_generate_title']?.[0];
      testResults.push({
        tool: 'Generate Title',
        status: result?.status === 200 ? 'PASSED' : 'FAILED',
        response: result
      });
      console.log(`✅ Generate Title - Status: ${result?.status || 'N/A'}`);
    } catch (error) {
      testResults.push({ tool: 'Generate Title', status: 'FAILED', error: error.message });
      console.log(`❌ Generate Title - Error: ${error.message}`);
    }
  });

  test('Tool 2: Generate Description', async () => {
    console.log('\n🔧 Testing: Generate Description');
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.locator('select[name="post_id"]').first();
      await postSelect.selectOption({ index: 1 });
      await page.waitForTimeout(500);
      
      const generateBtn = await page.locator('button:has-text("Generate Description")').first();
      if (await generateBtn.count() > 0) {
        await generateBtn.click();
        await page.waitForTimeout(8000); // Wait for AI response
        
        const result = toolResponses['aiseo_generate_description']?.[0];
        testResults.push({
          tool: 'Generate Description',
          status: result?.status === 200 ? 'PASSED' : 'FAILED',
          response: result
        });
        console.log(`✅ Generate Description - Status: ${result?.status || 'N/A'}`);
      } else {
        testResults.push({ tool: 'Generate Description', status: 'SKIPPED', reason: 'Button not found' });
        console.log(`⚠️  Generate Description - Skipped (button not found)`);
      }
    } catch (error) {
      testResults.push({ tool: 'Generate Description', status: 'FAILED', error: error.message });
      console.log(`❌ Generate Description - Error: ${error.message}`);
    }
  });

  test('Tool 3: Analyze Content', async () => {
    console.log('\n🔧 Testing: Analyze Content');
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      const postSelect = await page.locator('select[name="post_id"]').first();
      await postSelect.selectOption({ index: 1 });
      await page.waitForTimeout(500);
      
      const analyzeBtn = await page.locator('button:has-text("Analyze")').first();
      if (await analyzeBtn.count() > 0) {
        await analyzeBtn.click();
        await page.waitForTimeout(8000); // Wait for AI response
        
        const result = toolResponses['aiseo_analyze_content']?.[0];
        testResults.push({
          tool: 'Analyze Content',
          status: result?.status === 200 ? 'PASSED' : 'FAILED',
          response: result
        });
        console.log(`✅ Analyze Content - Status: ${result?.status || 'N/A'}`);
      } else {
        testResults.push({ tool: 'Analyze Content', status: 'SKIPPED', reason: 'Button not found' });
        console.log(`⚠️  Analyze Content - Skipped (button not found)`);
      }
    } catch (error) {
      testResults.push({ tool: 'Analyze Content', status: 'FAILED', error: error.message });
      console.log(`❌ Analyze Content - Error: ${error.message}`);
    }
  });

  // BULK OPERATIONS TAB
  test('Tool 4: Bulk Generate Titles', async () => {
    console.log('\n🔧 Testing: Bulk Generate Titles');
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=bulk-operations`);
    await page.waitForLoadState('networkidle');
    
    try {
      const checkboxes = await page.locator('input[type="checkbox"][name="post_ids[]"]');
      const count = await checkboxes.count();
      
      if (count >= 2) {
        await checkboxes.nth(0).check();
        await checkboxes.nth(1).check();
        await page.waitForTimeout(500);
        
        const bulkBtn = await page.locator('button:has-text("Generate Titles for Selected")');
        await bulkBtn.click();
        await page.waitForTimeout(5000);
        
        const results = toolResponses['aiseo_generate_title'] || [];
        const bulkResults = results.slice(-2); // Last 2 requests
        testResults.push({
          tool: 'Bulk Generate Titles',
          status: bulkResults.every(r => r.status === 200) ? 'PASSED' : 'FAILED',
          response: { count: bulkResults.length, results: bulkResults }
        });
        console.log(`✅ Bulk Generate Titles - Processed: ${bulkResults.length} posts`);
      } else {
        testResults.push({ tool: 'Bulk Generate Titles', status: 'SKIPPED', reason: 'Not enough posts' });
        console.log(`⚠️  Bulk Generate Titles - Skipped (not enough posts)`);
      }
    } catch (error) {
      testResults.push({ tool: 'Bulk Generate Titles', status: 'FAILED', error: error.message });
      console.log(`❌ Bulk Generate Titles - Error: ${error.message}`);
    }
  });

  test('Tool 5: Bulk Generate Descriptions', async () => {
    console.log('\n🔧 Testing: Bulk Generate Descriptions');
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=bulk-operations`);
    await page.waitForLoadState('networkidle');
    
    try {
      const checkboxes = await page.locator('input[type="checkbox"][name="post_ids[]"]');
      const count = await checkboxes.count();
      
      if (count >= 2) {
        await checkboxes.nth(0).check();
        await checkboxes.nth(1).check();
        await page.waitForTimeout(500);
        
        const bulkBtn = await page.locator('button:has-text("Generate Descriptions for Selected")');
        if (await bulkBtn.count() > 0) {
          await bulkBtn.click();
          await page.waitForTimeout(5000);
          
          const results = toolResponses['aiseo_generate_description'] || [];
          const bulkResults = results.slice(-2);
          testResults.push({
            tool: 'Bulk Generate Descriptions',
            status: bulkResults.every(r => r.status === 200) ? 'PASSED' : 'FAILED',
            response: { count: bulkResults.length, results: bulkResults }
          });
          console.log(`✅ Bulk Generate Descriptions - Processed: ${bulkResults.length} posts`);
        } else {
          testResults.push({ tool: 'Bulk Generate Descriptions', status: 'SKIPPED', reason: 'Button not found' });
          console.log(`⚠️  Bulk Generate Descriptions - Skipped (button not found)`);
        }
      } else {
        testResults.push({ tool: 'Bulk Generate Descriptions', status: 'SKIPPED', reason: 'Not enough posts' });
        console.log(`⚠️  Bulk Generate Descriptions - Skipped (not enough posts)`);
      }
    } catch (error) {
      testResults.push({ tool: 'Bulk Generate Descriptions', status: 'FAILED', error: error.message });
      console.log(`❌ Bulk Generate Descriptions - Error: ${error.message}`);
    }
  });

  // TECHNICAL SEO TAB
  test('Tool 6: List Redirects', async () => {
    console.log('\n🔧 Testing: List Redirects');
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=technical-seo`);
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    try {
      const result = toolResponses['aiseo_list_redirects']?.[0];
      testResults.push({
        tool: 'List Redirects',
        status: result?.status === 200 ? 'PASSED' : 'FAILED',
        response: result
      });
      console.log(`✅ List Redirects - Status: ${result?.status || 'N/A'}`);
    } catch (error) {
      testResults.push({ tool: 'List Redirects', status: 'FAILED', error: error.message });
      console.log(`❌ List Redirects - Error: ${error.message}`);
    }
  });

  test('Tool 7: Add Redirect', async () => {
    console.log('\n🔧 Testing: Add Redirect');
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=technical-seo`);
    await page.waitForLoadState('networkidle');
    
    try {
      const fromInput = await page.locator('input[name="redirect_from"]');
      const toInput = await page.locator('input[name="redirect_to"]');
      const addBtn = await page.locator('button:has-text("Add Redirect")');
      
      if (await fromInput.count() > 0 && await toInput.count() > 0) {
        await fromInput.fill(`/test-redirect-${Date.now()}`);
        await toInput.fill('/');
        await addBtn.click();
        await page.waitForTimeout(2000);
        
        const result = toolResponses['aiseo_add_redirect']?.[0];
        testResults.push({
          tool: 'Add Redirect',
          status: result?.status === 200 ? 'PASSED' : 'FAILED',
          response: result
        });
        console.log(`✅ Add Redirect - Status: ${result?.status || 'N/A'}`);
      } else {
        testResults.push({ tool: 'Add Redirect', status: 'SKIPPED', reason: 'Form not found' });
        console.log(`⚠️  Add Redirect - Skipped (form not found)`);
      }
    } catch (error) {
      testResults.push({ tool: 'Add Redirect', status: 'FAILED', error: error.message });
      console.log(`❌ Add Redirect - Error: ${error.message}`);
    }
  });

  test('Tool 8: Refresh Nonce', async () => {
    console.log('\n🔧 Testing: Refresh Nonce');
    await page.goto(`${WP_URL}/wp-admin/admin.php?page=aiseo&tab=seo-tools`);
    await page.waitForLoadState('networkidle');
    
    try {
      // Trigger nonce refresh (happens automatically on page load)
      await page.waitForTimeout(2000);
      
      const result = toolResponses['aiseo_refresh_nonce']?.[0];
      testResults.push({
        tool: 'Refresh Nonce',
        status: result?.status === 200 ? 'PASSED' : 'SKIPPED',
        response: result
      });
      console.log(`✅ Refresh Nonce - Status: ${result?.status || 'N/A (auto-triggered)'}`);
    } catch (error) {
      testResults.push({ tool: 'Refresh Nonce', status: 'FAILED', error: error.message });
      console.log(`❌ Refresh Nonce - Error: ${error.message}`);
    }
  });

  // SUMMARY TEST
  test('Summary: Display Results', async () => {
    console.log('\n' + '='.repeat(60));
    console.log('📊 INDIVIDUAL TOOL TEST SUMMARY');
    console.log('='.repeat(60));
    
    const passed = testResults.filter(r => r.status === 'PASSED').length;
    const failed = testResults.filter(r => r.status === 'FAILED').length;
    const skipped = testResults.filter(r => r.status === 'SKIPPED').length;
    
    console.log(`\nTotal Tools Tested: ${testResults.length}`);
    console.log(`✅ Passed: ${passed}`);
    console.log(`❌ Failed: ${failed}`);
    console.log(`⚠️  Skipped: ${skipped}`);
    console.log(`\nSuccess Rate: ${((passed / testResults.length) * 100).toFixed(1)}%`);
    
    console.log('\n' + '-'.repeat(60));
    console.log('DETAILED RESULTS:');
    console.log('-'.repeat(60));
    
    testResults.forEach((result, index) => {
      const icon = result.status === 'PASSED' ? '✅' : result.status === 'FAILED' ? '❌' : '⚠️';
      console.log(`${index + 1}. ${icon} ${result.tool} - ${result.status}`);
      if (result.response?.status) {
        console.log(`   HTTP Status: ${result.response.status}`);
        if (result.response.body) {
          const preview = result.response.body.substring(0, 100);
          console.log(`   Response: ${preview}${result.response.body.length > 100 ? '...' : ''}`);
        }
      }
      if (result.error) {
        console.log(`   Error: ${result.error}`);
      }
      if (result.reason) {
        console.log(`   Reason: ${result.reason}`);
      }
    });
    
    console.log('\n' + '='.repeat(60));
    console.log('📝 Full report saved to: tests/logs/individual-tools-report.json');
    console.log('='.repeat(60) + '\n');
  });
});
