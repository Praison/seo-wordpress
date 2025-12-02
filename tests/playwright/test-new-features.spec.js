/**
 * Playwright Test: Test New AISEO Features
 * 
 * Tests for:
 * - Homepage SEO Settings
 * - Taxonomy SEO Settings
 * - Webmaster Verification
 * - Google Analytics
 * - Title Templates
 * - Robots Settings
 * - Breadcrumbs
 * - RSS Feed Customization
 * - Import from Old Plugin
 * 
 * Usage:
 *   npx playwright test test-new-features.spec.js --headed
 * 
 * Note: For PHP built-in server (single-threaded), use API-only tests.
 * For full browser tests, use Apache/Nginx or Valet.
 */

const { test, expect, request } = require('@playwright/test');
const path = require('path');
require('dotenv').config({ path: path.resolve(__dirname, '../../.env') });

// Get credentials from environment
const WP_URL = process.env.WP_URL || 'http://localhost:8888';
const USERNAME = process.env.WP_USERNAME || 'admin';
const PASSWORD = process.env.WP_PASSWORD || '';

test.describe('AISEO New Features - REST API Tests', () => {
  let apiContext;

  test.beforeAll(async ({ playwright }) => {
    // Use API context with Application Password (Basic Auth header)
    const authHeader = 'Basic ' + Buffer.from(`${USERNAME}:${PASSWORD}`).toString('base64');
    
    apiContext = await playwright.request.newContext({
      baseURL: WP_URL,
      ignoreHTTPSErrors: true,
      extraHTTPHeaders: {
        'Authorization': authHeader,
      },
    });
    
    console.log('\n========================================');
    console.log('🧪 AISEO REST API Tests (Authenticated)');
    console.log(`URL: ${WP_URL}`);
    console.log(`User: ${USERNAME}`);
    console.log('========================================\n');
  });

  test.afterAll(async () => {
    await apiContext.dispose();
  });

  test('1. Homepage SEO - GET settings', async () => {
    const response = await apiContext.get('/wp-json/aiseo/v1/homepage-seo');
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.settings).toBeDefined();
    expect(data.settings).toHaveProperty('home_title');
    expect(data.settings).toHaveProperty('home_description');
    
    console.log('✅ Homepage SEO GET - Settings retrieved');
  });

  test('2. Homepage SEO - POST update settings', async () => {
    const response = await apiContext.post(`${WP_URL}/wp-json/aiseo/v1/homepage-seo`, {
      data: {
        home_title: 'Test Homepage Title',
        home_description: 'Test homepage description for SEO testing'
      }
    });
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    
    console.log('✅ Homepage SEO POST - Settings updated');
  });

  test('3. Webmaster Verification - GET codes', async () => {
    const response = await apiContext.get(`${WP_URL}/wp-json/aiseo/v1/webmaster-verification`);
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.codes).toBeDefined();
    expect(data.codes).toHaveProperty('google');
    expect(data.codes).toHaveProperty('bing');
    
    console.log('✅ Webmaster Verification GET - Codes retrieved');
  });

  test('4. Webmaster Verification - POST update codes', async () => {
    const response = await apiContext.post(`${WP_URL}/wp-json/aiseo/v1/webmaster-verification`, {
      data: {
        google: 'test-google-verification-code',
        bing: 'test-bing-verification-code'
      }
    });
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    
    console.log('✅ Webmaster Verification POST - Codes updated');
  });

  test('5. Analytics - GET settings', async () => {
    const response = await apiContext.get(`${WP_URL}/wp-json/aiseo/v1/analytics`);
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.settings).toBeDefined();
    expect(data.settings).toHaveProperty('tracking_id');
    expect(data.settings).toHaveProperty('enabled');
    
    console.log('✅ Analytics GET - Settings retrieved');
  });

  test('6. Analytics - POST update settings', async () => {
    const response = await apiContext.post(`${WP_URL}/wp-json/aiseo/v1/analytics`, {
      data: {
        tracking_id: 'G-TEST123456',
        enabled: true
      }
    });
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    
    console.log('✅ Analytics POST - Settings updated');
  });

  test('7. Title Templates - GET templates', async () => {
    const response = await apiContext.get(`${WP_URL}/wp-json/aiseo/v1/title-templates`);
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.templates).toBeDefined();
    expect(data.templates).toHaveProperty('separator');
    
    console.log('✅ Title Templates GET - Templates retrieved');
  });

  test('8. Title Templates - POST update templates', async () => {
    const response = await apiContext.post(`${WP_URL}/wp-json/aiseo/v1/title-templates`, {
      data: {
        separator: '|',
        post_title: '%title% | %sitename%'
      }
    });
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    
    console.log('✅ Title Templates POST - Templates updated');
  });

  test('9. Robots Settings - GET settings', async () => {
    const response = await apiContext.get(`${WP_URL}/wp-json/aiseo/v1/robots-settings`);
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.settings).toBeDefined();
    
    console.log('✅ Robots Settings GET - Settings retrieved');
  });

  test('10. Robots Settings - POST update settings', async () => {
    const response = await apiContext.post(`${WP_URL}/wp-json/aiseo/v1/robots-settings`, {
      data: {
        noindex_categories: false,
        nofollow_external_links: true
      }
    });
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    
    console.log('✅ Robots Settings POST - Settings updated');
  });

  test('11. Breadcrumbs - GET settings', async () => {
    const response = await apiContext.get(`${WP_URL}/wp-json/aiseo/v1/breadcrumbs`);
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.settings).toBeDefined();
    expect(data.settings).toHaveProperty('separator');
    expect(data.settings).toHaveProperty('home_text');
    
    console.log('✅ Breadcrumbs GET - Settings retrieved');
  });

  test('12. Breadcrumbs - POST update settings', async () => {
    const response = await apiContext.post(`${WP_URL}/wp-json/aiseo/v1/breadcrumbs`, {
      data: {
        separator: '»',
        home_text: 'Home',
        show_schema: true
      }
    });
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    
    console.log('✅ Breadcrumbs POST - Settings updated');
  });

  test('13. RSS Feed - GET settings', async () => {
    const response = await apiContext.get(`${WP_URL}/wp-json/aiseo/v1/rss`);
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data.settings).toBeDefined();
    expect(data.placeholders).toBeDefined();
    
    console.log('✅ RSS Feed GET - Settings retrieved');
  });

  test('14. RSS Feed - POST update settings', async () => {
    const response = await apiContext.post(`${WP_URL}/wp-json/aiseo/v1/rss`, {
      data: {
        enabled: true,
        before_content: 'Originally posted at %site_url%',
        after_content: 'Read more at %post_url%'
      }
    });
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    
    console.log('✅ RSS Feed POST - Settings updated');
  });

  test('15. Import - Check for old plugin data', async () => {
    const response = await apiContext.get(`${WP_URL}/wp-json/aiseo/v1/import/check`);
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data).toHaveProperty('has_data');
    
    console.log('✅ Import Check - Old plugin data check completed');
  });

  test('16. Import - Get preview', async () => {
    const response = await apiContext.get(`${WP_URL}/wp-json/aiseo/v1/import/preview`);
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    expect(data).toHaveProperty('preview');
    
    console.log('✅ Import Preview - Preview data retrieved');
  });

  test('17. Sitemap - Check old-style URL (sitemap_index.xml)', async () => {
    const response = await apiContext.get(`${WP_URL}/sitemap_index.xml`, {
      failOnStatusCode: false
    });
    
    // Should return 200 or redirect to sitemap
    const status = response.status();
    expect([200, 301, 302]).toContain(status);
    
    console.log(`✅ Sitemap Index - Status: ${status}`);
  });

  test('18. Sitemap - Check old-style URL (post-sitemap.xml)', async () => {
    const response = await apiContext.get(`${WP_URL}/post-sitemap.xml`, {
      failOnStatusCode: false
    });
    
    const status = response.status();
    expect([200, 301, 302, 404]).toContain(status); // 404 is ok if no posts
    
    console.log(`✅ Post Sitemap - Status: ${status}`);
  });
});

test.describe('AISEO New Features - Taxonomy SEO Tests', () => {
  let apiContext;

  test.beforeAll(async ({ playwright }) => {
    const authHeader = 'Basic ' + Buffer.from(`${USERNAME}:${PASSWORD}`).toString('base64');
    
    apiContext = await playwright.request.newContext({
      baseURL: WP_URL,
      ignoreHTTPSErrors: true,
      extraHTTPHeaders: {
        'Authorization': authHeader,
      },
    });
  });

  test.afterAll(async () => {
    await apiContext.dispose();
  });

  test('19. Taxonomy SEO - GET all categories', async () => {
    const response = await apiContext.get('/wp-json/aiseo/v1/taxonomy-seo/category');
    expect(response.ok()).toBeTruthy();
    
    const data = await response.json();
    expect(data.success).toBe(true);
    
    console.log('✅ Taxonomy SEO - Categories list retrieved');
  });

  test('20. Taxonomy SEO - GET term meta (category 1)', async () => {
    const response = await apiContext.get('/wp-json/aiseo/v1/taxonomy-seo/category/1', {
      failOnStatusCode: false
    });
    
    // May return 404 if category 1 doesn't exist
    const status = response.status();
    expect([200, 404]).toContain(status);
    
    if (status === 200) {
      const data = await response.json();
      expect(data.success).toBe(true);
      console.log('✅ Taxonomy SEO - Category 1 meta retrieved');
    } else {
      console.log('⚠️ Taxonomy SEO - Category 1 not found (expected if no categories)');
    }
  });
});
