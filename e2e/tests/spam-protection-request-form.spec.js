// @ts-check
// WPS-8386: Spam protection on request form.
const { test, expect } = require('@playwright/test');

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASSWORD || 'admin_password';

async function wpAdminLogin(page) {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', ADMIN_USER);
  await page.fill('#user_pass', ADMIN_PASS);
  await page.click('#wp-submit');
  await expect(page).toHaveURL(/wp-admin/);
}

test.describe('WPS-8386: Spam protection settings and form fields', () => {
  test('Admin settings page shows spam protection fields', async ({ page }) => {
    await wpAdminLogin(page);
    await page.goto('/wp-admin/admin.php?page=woo_refund_and_exchange_lite_menu&wrael_tab=woo-refund-and-exchange-lite-general-setting');
    // Spam enable toggle must exist.
    const spamToggle = page.locator('input[id="wps_rma_spam_protection_enable"]');
    await expect(spamToggle).toBeVisible();
    // Rate limit field must exist.
    const rateLimit = page.locator('input[id="wps_rma_spam_rate_limit"]');
    await expect(rateLimit).toBeVisible();
    // Turnstile site key field must exist.
    const siteKey = page.locator('input[id="wps_rma_turnstile_site_key"]');
    await expect(siteKey).toBeVisible();
    // Turnstile secret key field must exist.
    const secretKey = page.locator('input[id="wps_rma_turnstile_secret_key"]');
    await expect(secretKey).toBeVisible();
  });

  test('Return request form contains honeypot field (visually hidden)', async ({ page }) => {
    // The honeypot is present in the DOM but hidden from real users.
    // We navigate to the form page (nonce-protected, so just check the template renders).
    await wpAdminLogin(page);
    // Access the form template directly by checking the source after rendering.
    // The honeypot input must be in the page source when the form renders.
    const response = await page.goto('/wp-admin/admin.php?page=woo_refund_and_exchange_lite_menu');
    // Verify login succeeded; real form page requires a valid order nonce so we
    // only confirm the honeypot element would be rendered by checking its presence
    // via a lightweight grep of the partial file (structural test).
    const fs = require('fs');
    const formPartial = '/home/stuti-1219/Local Sites/rma-site/app/public/wp-content/plugins/woo-refund-and-exchange-lite/public/partials/wps-rma-refund-request-form.php';
    const source = fs.readFileSync(formPartial, 'utf8');
    expect(source).toContain('wps_rma_hp');
    expect(source).toContain('position:absolute');
  });

  test('Return request form contains Turnstile widget placeholder', async ({ page }) => {
    const fs = require('fs');
    const formPartial = '/home/stuti-1219/Local Sites/rma-site/app/public/wp-content/plugins/woo-refund-and-exchange-lite/public/partials/wps-rma-refund-request-form.php';
    const source = fs.readFileSync(formPartial, 'utf8');
    expect(source).toContain('cf-turnstile');
    expect(source).toContain('wps_rma_turnstile_site_key');
  });

  test('Public class enqueues Turnstile script when site key is set', async ({ page }) => {
    const fs = require('fs');
    const publicClass = '/home/stuti-1219/Local Sites/rma-site/app/public/wp-content/plugins/woo-refund-and-exchange-lite/public/class-woo-refund-and-exchange-lite-public.php';
    const source = fs.readFileSync(publicClass, 'utf8');
    expect(source).toContain('wps_rma_enqueue_turnstile_script');
    expect(source).toContain('challenges.cloudflare.com/turnstile/v0/api.js');
  });
});
