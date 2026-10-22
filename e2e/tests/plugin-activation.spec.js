// @ts-check
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

test.describe('Plugin activation', () => {
  test('WP admin is accessible', async ({ page }) => {
    await page.goto('/wp-login.php');
    await expect(page.locator('#loginform')).toBeVisible();
  });

  test('admin can log in', async ({ page }) => {
    await wpAdminLogin(page);
    await expect(page.locator('#wpadminbar')).toBeVisible();
  });

  test('plugin is active in plugin list', async ({ page }) => {
    await wpAdminLogin(page);
    await page.goto('/wp-admin/plugins.php');
    const pluginRow = page.locator('tr[data-plugin="woo-refund-and-exchange-lite/woocommerce-refund-and-exchange-lite.php"]');
    await expect(pluginRow).toBeVisible();
    await expect(pluginRow).toHaveClass(/active/);
  });
});

test.describe('RMA admin menu', () => {
  test('RMA menu item appears in WP admin', async ({ page }) => {
    await wpAdminLogin(page);
    await page.goto('/wp-admin/');
    const rmaMenu = page.locator('#adminmenu a').filter({ hasText: /refund|exchange|RMA/i }).first();
    await expect(rmaMenu).toBeVisible();
  });
});
