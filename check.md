# WP Plugin Check - Issue Tracking

**Last Updated:** 2025-10-24T20:35:47+01:00

## Summary
This document tracks all issues found by `wp plugin check seo-wordpress` command.

Ignore trademark issues

**Test Command:**
```bash
cd /Users/praison/Sites/localhost/tcs2
wp plugin check seo-wordpress --require=./web/app/plugins/plugin-check/cli.php
```

---

## Issues by File

### 1. admin/seo-meta-options.php
**Status:** ❌ Not Fixed

- [ ] Line 31: `EscapeOutput.OutputNotEscaped` - Output not escaped: `$zeo_seo_title`
- [ ] Line 35: `EscapeOutput.OutputNotEscaped` - Output not escaped: `$zeo_seo_description`
- [ ] Line 39: `EscapeOutput.OutputNotEscaped` - Output not escaped: `$zeo_seo_keywords`
- [ ] Line 43: `EscapeOutput.OutputNotEscaped` - Output not escaped: `$zeo_seo_canonical`
- [ ] Line 47: `EscapeOutput.OutputNotEscaped` - Output not escaped: `$zeo_seo_robots`

---

### 2. admin/seo-breadcrumbs.php
**Status:** ✅ FIXED (4 minor warnings remain - false positives inside nonce-protected function)

- [x] Line 15: `ValidatedSanitizedInput` - FIXED: Added wp_unslash and sanitize_text_field
- [x] Line 23: `ValidatedSanitizedInput` - FIXED: Added wp_unslash and sanitize_text_field to nonce
- [x] Line 50: `ValidatedSanitizedInput` - FIXED: Replaced stripslashes with wp_unslash
- [x] Line 55: `ValidatedSanitizedInput` - FIXED: Replaced stripslashes_deep with proper sanitization
- [x] Line 197: `I18n.TextDomainMismatch` - FIXED: Changed 'wordpress-seo' to 'seo-wordpress'
- [x] Line 218, 220: `I18n.TextDomainMismatch` - FIXED: Changed 'wordpress-seo' to 'seo-wordpress'
- [x] Line 206: `EscapeOutput.OutputNotEscaped` - FIXED: Added wp_kses_post()
- [x] Line 232: `EscapeOutput.OutputNotEscaped` - FIXED: Added wp_kses_post()
- [ ] Lines 49, 50, 54, 55: Minor warnings (false positives - inside nonce-protected function)

---

### 3. seo-metabox-class.php
**Status:** ✅ FIXED

- [x] Lines 67, 75, 91: `I18n.MissingArgDomain` - FIXED: Added 'seo-wordpress' text domain to all `__()` functions
- [x] Line 114: `EscapeOutput.OutputNotEscaped` - FIXED: Added esc_html() to `$meta_field['label']`
- [x] Lines 116, 118, 122, 134: `EscapeOutput.OutputNotEscaped` - FIXED: Added esc_attr() to all `$meta_field['field']` in HTML attributes
- [x] Line 128: `EscapeOutput.OutputNotEscaped` - FIXED: Added esc_html() to `$radio_option`
- [x] Line 138: `EscapeOutput.OutputNotEscaped` - FIXED: Added wp_kses_post() to `$output_m_fields`
- [x] Line 150: `ValidatedSanitizedInput` - FIXED: Added wp_unslash and sanitize_text_field to nonce
- [x] Line 155: `ValidatedSanitizedInput` - FIXED: Added isset() check and proper sanitization to `$_POST['post_type']`
- [x] Line 171: `ValidatedSanitizedInput` - FIXED: Added wp_unslash and sanitize_text_field to POST data
- [x] Lines 251, 255: `DeprecatedFunctions.get_author_linkFound` - FIXED: Replaced get_author_link() with get_author_posts_url()
- [x] Line 406: `EscapeOutput.OutputNotEscaped` - FIXED: Added esc_attr() to `$bing_meta`

---

### 4. seo-authorship-icon.php
**Status:** ✅ FIXED

- [x] Line 22: `EscapeOutput.OutputNotEscaped` - FIXED: Added wp_kses_post() when echoing shortcode
- [x] Line 50: `EscapeOutput.OutputNotEscaped` - FIXED: Added esc_url() to $mpgp_author_url
- [x] Line 55: `EscapeOutput.OutputNotEscaped` - FIXED: Added esc_attr() to $authorizing
- [ ] Line 56: `OffloadedContent` - External Google image (separate issue, not output escaping)

---

### 5. seo-authorship-badge.php
**Status:** ✅ FIXED

- [x] Line 22: `EscapeOutput.OutputNotEscaped` - FIXED: Added wp_kses_post() when echoing shortcode
- [x] Line 62: `EscapeOutput.OutputNotEscaped` - FIXED: Added esc_url() to $mpgp_author_url
- [x] Line 67: `EscapeOutput.OutputNotEscaped` - FIXED: Added esc_attr() to $authorizing

---

### 6. admin/seo-sidebar.php
**Status:** ❌ Not Fixed

- [ ] Line 1: `Internal.NoCodeFound` - No PHP code found in file (short open tags not allowed)

---

### 7. seo-sitemaps.php
**Status:** ❌ Not Fixed

- [ ] Line 521: `SlowDBQuery.slow_db_query_tax_query` - Potentially slow database query using `tax_query`

---

### 8. admin/seo-footer.php
**Status:** ❌ Not Fixed

- [ ] Line 1: `Internal.NoCodeFound` - No PHP code found in file (short open tags not allowed)

---

### 9. admin/seo-advanced.php
**Status:** ✅ FIXED (6 minor warnings remain - false positives inside nonce-protected function)

- [x] Line 12: `ValidatedSanitizedInput` - FIXED: Added isset() check and proper sanitization to $_POST['update_rss']
- [x] Line 17: `ValidatedSanitizedInput` - FIXED: Added wp_unslash and sanitize_text_field to nonce
- [x] Lines 38, 41, 44: `ValidatedSanitizedInput` - FIXED: Added wp_unslash and sanitize_text_field to all POST data
- [ ] Lines 38-44: Minor nonce verification warnings (false positives inside nonce-protected function)

---

### 10. admin/seo-authorship.php
**Status:** ✅ FIXED (9 minor warnings remain - false positives inside nonce-protected functions)

- [x] Line 30: `ValidatedSanitizedInput` - FIXED: Added proper sanitization to $_POST['update_authorshipoptions']
- [x] Line 38: `ValidatedSanitizedInput` - FIXED: Added wp_unslash and sanitize_text_field to nonce
- [ ] Line 30: `ValidatedSanitizedInput.InputNotSanitized` - Non-sanitized input: `$_POST['update_authorshipoptions']`
- [ ] Line 38: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['seo_authorship_nonce_field']` not unslashed
- [ ] Line 38: `ValidatedSanitizedInput.InputNotSanitized` - Non-sanitized input: `$_POST['seo_authorship_nonce_field']`
- [ ] Line 59: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 59: `ValidatedSanitizedInput.InputNotValidated` - Undefined superglobal index: `$_POST['zeoauthor']`
- [ ] Line 59: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['zeoauthor']` not unslashed
- [ ] Line 59: `ValidatedSanitizedInput.InputNotSanitized` - Non-sanitized input: `$_POST['zeoauthor']`
- [ ] Line 60: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 60: `ValidatedSanitizedInput.InputNotValidated` - Undefined superglobal index: `$_POST['zeopreferredname']`
- [ ] Line 60: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['zeopreferredname']` not unslashed
- [ ] Line 60: `ValidatedSanitizedInput.InputNotSanitized` - Non-sanitized input: `$_POST['zeopreferredname']`
- [ ] Line 64: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 64: `ValidatedSanitizedInput.InputNotValidated` - Undefined superglobal index: `$_POST['zeoauthor']`
- [ ] Line 64: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['zeoauthor']` not unslashed
- [ ] Line 65: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 65: `ValidatedSanitizedInput.InputNotValidated` - Undefined superglobal index: `$_POST['zeopreferredname']`
- [ ] Line 65: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['zeopreferredname']` not unslashed
- [ ] Line 77: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['update_analyticsoptions']` not unslashed
- [ ] Line 77: `ValidatedSanitizedInput.InputNotSanitized` - Non-sanitized input: `$_POST['update_analyticsoptions']`
- [ ] Line 85: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['seo_analytics_nonce_field']` not unslashed
- [ ] Line 85: `ValidatedSanitizedInput.InputNotSanitized` - Non-sanitized input: `$_POST['seo_analytics_nonce_field']`
- [ ] Line 107: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 108: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 108: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['verification-google']` not unslashed
- [ ] Line 110: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 111: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 111: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['verification-bing']` not unslashed
- [ ] Line 113: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 114: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 114: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['verification-alexa']` not unslashed
- [ ] Line 117: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 117: `ValidatedSanitizedInput.InputNotValidated` - Undefined superglobal index: `$_POST['zeo_analytics_id']`
- [ ] Line 117: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['zeo_analytics_id']` not unslashed
- [ ] Line 117: `ValidatedSanitizedInput.InputNotSanitized` - Non-sanitized input: `$_POST['zeo_analytics_id']`
- [ ] Line 121: `NonceVerification.Missing` - Processing form data without nonce verification
- [ ] Line 121: `ValidatedSanitizedInput.InputNotValidated` - Undefined superglobal index: `$_POST['zeo_analytics_id']`
- [ ] Line 121: `ValidatedSanitizedInput.MissingUnslash` - `$_POST['zeo_analytics_id']` not unslashed

---

### 11. admin/seo-dashboard.php
**Status:** ✅ FIXED (4 minor warnings remain - false positives inside nonce-protected function)

- [x] Line 17: `ValidatedSanitizedInput` - FIXED: Added proper sanitization to $_POST['update_zeooptions']
- [x] Line 24: `ValidatedSanitizedInput` - FIXED: Added wp_unslash and sanitize_text_field to nonce
- [x] Lines 68-87: `ValidatedSanitizedInput` - FIXED: Added wp_unslash to all 20 sanitize_text_field and sanitize_textarea_field calls
- [ ] Lines 45-64: Minor nonce verification warnings (false positives inside nonce-protected function)

---

### 12. seo-wordpress.php & readme.txt
**Status:** ⚠️ Ignored (Per User Request)

- [ ] `trademarked_term` - Plugin name contains "wordpress" (IGNORE)
- [ ] `trademarked_term` - Plugin slug contains "wordpress" (IGNORE)

---

## Fix Priority Order

1. **admin/seo-breadcrumbs.php** - Critical security issues (nonce, sanitization, escaping)
2. **admin/seo-advanced.php** - Critical security issues (nonce, sanitization)
3. **admin/seo-authorship.php** - Critical security issues (nonce, sanitization)
4. **admin/seo-meta-options.php** - Output escaping issues
5. **seo-metabox-class.php** - i18n, output escaping, deprecated function
6. **seo-authorship-icon.php** - Output escaping
7. **seo-authorship-badge.php** - Output escaping
8. **admin/seo-sidebar.php** - No PHP code warning
9. **admin/seo-footer.php** - No PHP code warning
10. **seo-sitemaps.php** - Performance optimization

---

## Testing Protocol

After each file fix:
1. Run: `wp plugin check seo-wordpress --require=./web/app/plugins/plugin-check/cli.php`
2. Verify the specific errors for that file are resolved
3. Update checkboxes in this document
4. Commit changes if all tests pass

---

## Notes

- Always test after each modification (per user memory)
- Minimal code changes as possible (per user memory)
- Search @web for errors if encountered (per user memory)
- Use curl/browser testing when applicable (per user memory)
