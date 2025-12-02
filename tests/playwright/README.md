# AISEO Playwright Tests

Automated end-to-end tests for the AISEO WordPress plugin using Playwright.

## Setup

1. **Install dependencies:**
   ```bash
   cd tests/playwright
   npm install
   npx playwright install chromium
   ```

2. **Configure environment:**
   The tests use credentials from `/Users/praison/aiseo/.env`:
   ```
   USERNAME=praison
   PASSWORD=your_password
   WP_URL=https://wordpress.test
   ```

## Running Tests

### Run all tests (headless):
```bash
npm test
```

### Run with browser visible:
```bash
npm run test:headed
```

### Debug mode (step through):
```bash
npm run test:debug
```

### Interactive UI mode:
```bash
npm run test:ui
```

### Monitor WordPress logs while testing:
```bash
# In a separate terminal, run:
tail -f /Users/praison/Sites/localhost/wordpress/wp-content/debug.log | grep -E "🔵|AISEO|403|500|Error"
```

## What the Tests Do

The test suite (`test-all-pages.spec.js`) performs the following:

1. **Login** - Authenticates using credentials from .env
2. **Dashboard** - Views the overview page
3. **SEO Tools** - Selects a post and generates a title
4. **AI Content** - Enters a topic and generates content
5. **Bulk Operations** - Selects posts and generates titles in bulk
6. **Technical SEO** - Lists redirects
7. **Advanced** - Views settings
8. **Monitoring** - Views logs
9. **Settings** - Views configuration

## Monitoring

The tests automatically capture:

- ✅ **Console logs** - All browser console messages
- ✅ **AJAX requests** - All admin-ajax.php requests
- ✅ **AJAX responses** - Status codes and response bodies
- ✅ **Errors** - 403, 500, and JavaScript errors

### View Test Results:

```bash
# View captured logs
cat tests/logs/playwright-test-results.json | jq

# View HTML report
npx playwright show-report tests/logs/playwright-report
```

### Monitor WordPress Debug Log:

```bash
# Watch in real-time
tail -f /Users/praison/Sites/localhost/wordpress/wp-content/debug.log

# Filter for AISEO only
tail -f /Users/praison/Sites/localhost/wordpress/wp-content/debug.log | grep AISEO

# Filter for errors
tail -f /Users/praison/Sites/localhost/wordpress/wp-content/debug.log | grep -E "403|500|Error|🔵"
```

## Troubleshooting

### Tests fail with "net::ERR_CERT_AUTHORITY_INVALID"
The config has `ignoreHTTPSErrors: true` for local development. If still failing, check your Valet setup.

### Tests timeout
Increase timeouts in `playwright.config.js`:
```javascript
actionTimeout: 60000,
navigationTimeout: 60000,
```

### Can't find elements
The page might be loading slowly. Add waits:
```javascript
await page.waitForSelector('#element-id', { timeout: 10000 });
```

### 403 Forbidden errors
Check the WordPress debug.log for nonce issues:
```bash
tail -100 /Users/praison/Sites/localhost/wordpress/wp-content/debug.log | grep -A 5 "🔵 GLOBAL AJAX LOGGER"
```

## CI/CD Integration

### GitHub Actions Example:
```yaml
name: Playwright Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: actions/setup-node@v3
        with:
          node-version: 18
      - name: Install dependencies
        run: |
          cd tests/playwright
          npm ci
          npx playwright install --with-deps chromium
      - name: Run tests
        env:
          WP_URL: ${{ secrets.WP_URL }}
          USERNAME: ${{ secrets.WP_USERNAME }}
          PASSWORD: ${{ secrets.WP_PASSWORD }}
        run: |
          cd tests/playwright
          npm test
      - uses: actions/upload-artifact@v3
        if: always()
        with:
          name: playwright-report
          path: tests/logs/playwright-report/
```

## File Structure

```
tests/playwright/
├── package.json              # Dependencies
├── playwright.config.js      # Playwright configuration
├── test-all-pages.spec.js    # Main test suite
├── README.md                 # This file
└── ../logs/                  # Test results (generated)
    ├── playwright-test-results.json
    └── playwright-report/
```

## Next Steps

1. Run the tests: `npm run test:headed`
2. Monitor logs: `tail -f wp-content/debug.log`
3. Check results: `cat tests/logs/playwright-test-results.json`
4. Fix any 403 errors found
5. Add more test cases as needed
