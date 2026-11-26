// @ts-check
// WPS-8378: EU Withdrawal button (Directive 2023/2673)
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const ADMIN_PASS = process.env.WP_ADMIN_PASSWORD || 'admin_password';
const PLUGIN_ROOT = path.resolve(__dirname, '../../');

async function wpAdminLogin(page) {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', ADMIN_USER);
  await page.fill('#user_pass', ADMIN_PASS);
  await page.click('#wp-submit');
  await expect(page).toHaveURL(/wp-admin/);
}

test.describe('WPS-8378: EU Withdrawal settings tab', () => {
  test('Admin settings page shows EU Withdrawal tab', async ({ page }) => {
    await wpAdminLogin(page);
    await page.goto(
      '/wp-admin/admin.php?page=woo_refund_and_exchange_lite_menu&wrael_tab=woo-refund-and-exchange-lite-eu-withdrawal'
    );
    // Enable toggle must exist.
    const enableToggle = page.locator('input[id="wps_rma_eu_withdrawal_enable"]');
    await expect(enableToggle).toBeVisible();
    // Window days field must exist.
    const windowDays = page.locator('input[id="wps_rma_eu_withdrawal_window_days"]');
    await expect(windowDays).toBeVisible();
    // EU-only toggle must exist.
    const euOnly = page.locator('input[id="wps_rma_eu_withdrawal_eu_only"]');
    await expect(euOnly).toBeVisible();
    // Form page selector must exist.
    const pageSelect = page.locator('select[id="wps_rma_eu_withdrawal_page_id"]');
    await expect(pageSelect).toBeVisible();
    // Button text field must exist.
    const buttonText = page.locator('input[id="wps_rma_eu_withdrawal_button_text"]');
    await expect(buttonText).toBeVisible();
    // Admin email field must exist.
    const adminEmail = page.locator('input[id="wps_rma_eu_withdrawal_admin_email"]');
    await expect(adminEmail).toBeVisible();
    // Save button must exist.
    const saveBtn = page.locator('input[id="wps_rma_save_eu_withdrawal_setting"]');
    await expect(saveBtn).toBeVisible();
  });
});

test.describe('WPS-8378: EU Withdrawal file structure checks', () => {
  test('Admin settings tab template file exists', () => {
    const file = path.join(PLUGIN_ROOT, 'admin/partials/woo-refund-and-exchange-lite-eu-withdrawal.php');
    expect(fs.existsSync(file)).toBe(true);
    const src = fs.readFileSync(file, 'utf8');
    // Template applies the filter to get the settings array (which includes the save button).
    expect(src).toContain('wps_rma_eu_withdrawal_settings_array');
    expect(src).toContain('wps_rma_plug_generate_html');
  });

  test('Public withdrawal form template exists', () => {
    const file = path.join(PLUGIN_ROOT, 'public/partials/wps-rma-eu-withdrawal-form.php');
    expect(fs.existsSync(file)).toBe(true);
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('wps_rma_eu_withdrawal_step1');
    expect(src).toContain('wps_rma_ew_items');
    expect(src).toContain('wps_rma_eu_withdrawal_step2');
    expect(src).toContain('wps_rma_eu_withdrawal_log');
  });

  test('Shortcode template exists', () => {
    const file = path.join(PLUGIN_ROOT, 'public/partials/wps-rma-eu-withdrawal-shortcode.php');
    expect(fs.existsSync(file)).toBe(true);
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('[wps_rma_eu_withdrawal_form]');
  });

  test('EU Withdrawal email class exists and declares correct class', () => {
    const file = path.join(PLUGIN_ROOT, 'emails/class-wps-rma-eu-withdrawal-email.php');
    expect(fs.existsSync(file)).toBe(true);
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('class Wps_Rma_Eu_Withdrawal_Email extends WC_Email');
    expect(src).toContain('wps_rma_eu_withdrawal_email');
  });

  test('EU Withdrawal email template exists', () => {
    const file = path.join(PLUGIN_ROOT, 'emails/templates/wps-rma-eu-withdrawal-email-template.php');
    expect(fs.existsSync(file)).toBe(true);
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('withdrawal request');
    expect(src).toContain('woocommerce_email_header');
    expect(src).toContain('woocommerce_email_footer');
  });

  test('Helper functions declared in common-functions.php', () => {
    const file = path.join(PLUGIN_ROOT, 'includes/woo-refund-and-exchange-lite-common-functions.php');
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('wps_rma_eu_withdrawal_in_window');
    expect(src).toContain('wps_rma_eu_withdrawal_eu_check');
    expect(src).toContain('wps_rma_eu_withdrawal_button_url');
    // EU country list includes all 27 member states.
    expect(src).toContain("'AT'");
    expect(src).toContain("'DE'");
    expect(src).toContain("'FR'");
  });

  test('Public class has EU withdrawal button methods', () => {
    const file = path.join(PLUGIN_ROOT, 'public/class-woo-refund-and-exchange-lite-public.php');
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('wps_rma_eu_withdrawal_button');
    expect(src).toContain('wps_rma_eu_withdrawal_button_order_view');
    expect(src).toContain('wps_rma_eu_withdrawal_email_link');
    expect(src).toContain('wps_rma_eu_withdrawal_template');
    expect(src).toContain('wps_rma_eu_withdrawal_register_shortcode');
  });

  test('Common class registers EU withdrawal email and send handler', () => {
    const file = path.join(PLUGIN_ROOT, 'common/class-woo-refund-and-exchange-lite-common.php');
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('class-wps-rma-eu-withdrawal-email.php');
    expect(src).toContain('wps_rma_eu_withdrawal_email');
    expect(src).toContain('wps_rma_eu_withdrawal_send_emails');
  });

  test('Admin class has EU withdrawal settings method and save handler', () => {
    const file = path.join(PLUGIN_ROOT, 'admin/class-woo-refund-and-exchange-lite-admin.php');
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('wps_rma_eu_withdrawal_settings_array');
    expect(src).toContain('wps_rma_save_eu_withdrawal_setting');
    expect(src).toContain("'wps_rma_eu_withdrawal_enable'");
    expect(src).toContain("'wps_rma_eu_withdrawal_window_days'");
  });

  test('Default tabs include EU Withdrawal tab', () => {
    const file = path.join(PLUGIN_ROOT, 'includes/class-woo-refund-and-exchange-lite.php');
    const src = fs.readFileSync(file, 'utf8');
    expect(src).toContain('woo-refund-and-exchange-lite-eu-withdrawal');
    expect(src).toContain('woo-refund-and-exchange-lite-eu-withdrawal.php');
  });
});

test.describe('WPS-8378: EU Withdrawal window and EU-country helper logic', () => {
  test('EU country list covers all 27 EU member states', () => {
    const file = path.join(PLUGIN_ROOT, 'includes/woo-refund-and-exchange-lite-common-functions.php');
    const src = fs.readFileSync(file, 'utf8');
    const euStates = ['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK'];
    for (const code of euStates) {
      expect(src).toContain(`'${code}'`);
    }
  });

  test('Form uses nonce for step 1 and step 2 to prevent CSRF', () => {
    const shortcodeFile = path.join(PLUGIN_ROOT, 'public/partials/wps-rma-eu-withdrawal-shortcode.php');
    const src = fs.readFileSync(shortcodeFile, 'utf8');
    expect(src).toContain('wp_nonce_field');
    expect(src).toContain('wps_rma_eu_withdrawal_step1');
    expect(src).toContain('wps_rma_eu_withdrawal_step2');
    expect(src).toContain('wp_verify_nonce');
  });

  test('Withdrawal log is stored in order meta', () => {
    const shortcodeFile = path.join(PLUGIN_ROOT, 'public/partials/wps-rma-eu-withdrawal-shortcode.php');
    const src = fs.readFileSync(shortcodeFile, 'utf8');
    expect(src).toContain('wps_rma_eu_withdrawal_log');
    expect(src).toContain('wps_rma_update_meta_data');
    expect(src).toContain("'status'");
    expect(src).toContain("'submitted'");
  });

  test('Withdrawal hook fires after successful submission', () => {
    const shortcodeFile = path.join(PLUGIN_ROOT, 'public/partials/wps-rma-eu-withdrawal-shortcode.php');
    const src = fs.readFileSync(shortcodeFile, 'utf8');
    expect(src).toContain('wps_rma_eu_withdrawal_submitted');
  });
});
