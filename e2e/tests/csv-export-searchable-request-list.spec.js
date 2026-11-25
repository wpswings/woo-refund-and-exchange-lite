// @ts-check
// WPS-8385: CSV export and searchable request list.
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

async function goToRequestTab(page) {
  await page.goto('/wp-admin/admin.php?page=woo_refund_and_exchange_lite_menu&wrael_tab=woo-refund-and-exchange-lite-rma-request');
}

test.describe('WPS-8385: Request list filters and CSV export', () => {
  test('Request tab renders filter form with status dropdown', async ({ page }) => {
    await wpAdminLogin(page);
    await goToRequestTab(page);
    // Status dropdown must be present.
    const statusDropdown = page.locator('select[name="rma_request_status_filter"]');
    await expect(statusDropdown).toBeVisible();
    // Options: All Statuses, Pending, Accepted, Complete, Cancelled.
    const options = statusDropdown.locator('option');
    await expect(options).toHaveCount(5);
  });

  test('Request tab has search box accepting order ID or email input', async ({ page }) => {
    await wpAdminLogin(page);
    await goToRequestTab(page);
    const searchInput = page.locator('.wps-rma-req-search-form input[type="search"]');
    await expect(searchInput).toBeVisible();
    // Placeholder/label mentions ID or email.
    const submitBtn = page.locator('.wps-rma-req-search-form input[type="submit"]');
    await expect(submitBtn).toBeVisible();
    const label = await submitBtn.inputValue();
    expect(label).toMatch(/order id or email/i);
  });

  test('Export CSV button is visible on the Request tab', async ({ page }) => {
    await wpAdminLogin(page);
    await goToRequestTab(page);
    const exportBtn = page.locator('a.button').filter({ hasText: /export csv/i });
    await expect(exportBtn).toBeVisible();
    // The URL must contain the export query param and nonce.
    const href = await exportBtn.getAttribute('href');
    expect(href).toContain('wps_rma_export_request_log=1');
    expect(href).toContain('_wpnonce');
  });

  test('Filter form submit saves filters and reloads the tab', async ({ page }) => {
    await wpAdminLogin(page);
    await goToRequestTab(page);
    // Select "Pending" status and submit.
    await page.selectOption('select[name="rma_request_status_filter"]', 'pending');
    await page.click('input[name="wps_rma_date_submit"]');
    await page.waitForLoadState('networkidle');
    // The pending option should still be selected after reload.
    const selected = await page.locator('select[name="rma_request_status_filter"]').inputValue();
    expect(selected).toBe('pending');
    // Clear filter.
    await page.click('input[name="wps_rma_clear_filter"]');
    await page.waitForLoadState('networkidle');
    const clearedSelected = await page.locator('select[name="rma_request_status_filter"]').inputValue();
    expect(clearedSelected).toBe('');
  });
});
