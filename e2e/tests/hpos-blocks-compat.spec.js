// @ts-check
// WPS-8387: Verify HPOS + Cart/Checkout Blocks compatibility declarations.
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

test.describe('WPS-8387: HPOS + Blocks compatibility declarations', () => {
  test('no HPOS/blocks incompatibility notice on plugins page', async ({ page }) => {
    await wpAdminLogin(page);
    await page.goto('/wp-admin/plugins.php');
    // WooCommerce shows a "feature incompatibility" notice when a plugin does not
    // declare compatibility. Verify no such notice targets our plugin slug.
    const incompatNotice = page.locator('.woocommerce-feature-incompatible-plugin-notice, .notice-warning')
      .filter({ hasText: /woo-refund-and-exchange-lite/i });
    await expect(incompatNotice).toHaveCount(0);
  });

  test('plugin page shows active RMA plugin without incompatibility warnings', async ({ page }) => {
    await wpAdminLogin(page);
    await page.goto('/wp-admin/plugins.php');
    const pluginRow = page.locator('tr[data-plugin="woo-refund-and-exchange-lite/woocommerce-refund-and-exchange-lite.php"]');
    await expect(pluginRow).toBeVisible();
    await expect(pluginRow).toHaveClass(/active/);
  });
});
