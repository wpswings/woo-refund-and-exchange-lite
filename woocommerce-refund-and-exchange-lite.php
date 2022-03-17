<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://wpswings.com/
 * @since   1.0.0
 * @package woo-refund-and-exchange-lite
 *
 * @wordpress-plugin
 * Plugin Name:       Return Refund and Exchange for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/woo-refund-and-exchange-lite/
 * Description:       Return Refund and Exchange for WooCommerce allows users to submit product refund. The plugin provides a dedicated mailing system that would help to communicate better between store owner and customers.This is lite version of Woocommerce Refund And Exchange.
 * Version:           4.0.0
 * Author:            WP Swings
 * Author URI:        https://wpswings.com/?utm_source=wpswings-official&utm_medium=rma-org-page&utm_campaign=wpswings-official
 * Text Domain:       woo-refund-and-exchange-lite
 * Domain Path:       /languages
 *
 * Requires at least: 4.6
 * Tested up to: 5.9.2
 * WC requires at least: 4.0
 * WC tested up to: 6.3.1
 *
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$activated      = true;
$active_plugins = get_option( 'active_plugins', array() );
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	$active_network_wide = get_site_option( 'active_sitewide_plugins', array() );
	if ( ! empty( $active_network_wide ) ) {
		foreach ( $active_network_wide as $key => $value ) {
			$active_plugins[] = $key;
		}
	}
	$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	if ( ! in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {
		$activated = false;
	}
} else {
	if ( ! in_array( 'woocommerce/woocommerce.php', $active_plugins, true ) ) {
		$activated = false;
	}
}
if ( $activated ) {
	/**
	 * Define plugin constants.
	 *
	 * @since 1.0.0
	 */
	function define_woo_refund_and_exchange_lite_constants() {
		woo_refund_and_exchange_lite_constants( 'WOO_REFUND_AND_EXCHANGE_LITE_VERSION', '4.0.0' );
		woo_refund_and_exchange_lite_constants( 'WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH', plugin_dir_path( __FILE__ ) );
		woo_refund_and_exchange_lite_constants( 'WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL', plugin_dir_url( __FILE__ ) );
		woo_refund_and_exchange_lite_constants( 'WOO_REFUND_AND_EXCHANGE_LITE_SERVER_URL', 'https://wpswings.com' );
		woo_refund_and_exchange_lite_constants( 'WOO_REFUND_AND_EXCHANGE_LITE_ITEM_REFERENCE', 'Woo Refund And Exchange Lite' );
	}

	/**
	 * Define wps-site update feature.
	 *
	 * @since 1.0.0
	 */
	function auto_update_woo_refund_and_exchange_lite() {
		if ( ! defined( 'WOO_REFUND_AND_EXCHANGE_LITE_SPECIAL_SECRET_KEY' ) ) {
			define( 'WOO_REFUND_AND_EXCHANGE_LITE_SPECIAL_SECRET_KEY', '59f32ad2f20102.74284991' );
		}

		if ( ! defined( 'WOO_REFUND_AND_EXCHANGE_LITE_LICENSE_SERVER_URL' ) ) {
			define( 'WOO_REFUND_AND_EXCHANGE_LITE_LICENSE_SERVER_URL', 'https://wpswings.com' );
		}

		if ( ! defined( 'WOO_REFUND_AND_EXCHANGE_LITE_ITEM_REFERENCE' ) ) {
			define( 'WOO_REFUND_AND_EXCHANGE_LITE_ITEM_REFERENCE', 'Woo Refund And Exchange Lite' );
		}
		woo_refund_and_exchange_lite_constants( 'WOO_REFUND_AND_EXCHANGE_LITE_BASE_FILE', __FILE__ );
	}

	/**
	 * Callable function for defining plugin constants.
	 *
	 * @param String $key   Key for contant.
	 * @param String $value value for contant.
	 * @since 1.0.0
	 */
	function woo_refund_and_exchange_lite_constants( $key, $value ) {
		if ( ! defined( $key ) ) {

			define( $key, $value );
		}
	}

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-woo-refund-and-exchange-lite-activator.php
	 *
	 * @param string $network_wide .
	 */
	function activate_woo_refund_and_exchange_lite( $network_wide ) {
		include_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite-activator.php';
		Woo_Refund_And_Exchange_Lite_Activator::woo_refund_and_exchange_lite_activate( $network_wide );
		$wps_rma_active_plugin = get_option( 'wps_all_plugins_active', false );
		if ( is_array( $wps_rma_active_plugin ) && ! empty( $wps_rma_active_plugin ) ) {
			$wps_rma_active_plugin['woo-refund-and-exchange-lite'] = array(
				'plugin_name' => esc_html__( 'Woo Refund And Exchange Lite', 'woo-refund-and-exchange-lite' ),
				'active'      => '1',
			);
		} else {
			$wps_rma_active_plugin                                 = array();
			$wps_rma_active_plugin['woo-refund-and-exchange-lite'] = array(
				'plugin_name' => esc_html__( 'Woo Refund And Exchange Lite', 'woo-refund-and-exchange-lite' ),
				'active'      => '1',
			);
		}
		update_option( 'wps_all_plugins_active', $wps_rma_active_plugin );
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-woo-refund-and-exchange-lite-deactivator.php
	 *
	 * @param string $network_wide .
	 */
	function deactivate_woo_refund_and_exchange_lite( $network_wide ) {
		include_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite-deactivator.php';
		Woo_Refund_And_Exchange_Lite_Deactivator::woo_refund_and_exchange_lite_deactivate( $network_wide );
		$wps_rma_deactive_plugin = get_option( 'wps_all_plugins_active', false );
		if ( is_array( $wps_rma_deactive_plugin ) && ! empty( $wps_rma_deactive_plugin ) ) {
			foreach ( $wps_rma_deactive_plugin as $wps_rma_deactive_key => $wps_rma_deactive ) {
				if ( 'woo-refund-and-exchange-lite' === $wps_rma_deactive_key ) {
					$wps_rma_deactive_plugin[ $wps_rma_deactive_key ]['active'] = '0';
				}
			}
		}
		update_option( 'wps_all_plugins_active', $wps_rma_deactive_plugin );
	}

	register_activation_hook( __FILE__, 'activate_woo_refund_and_exchange_lite' );
	register_deactivation_hook( __FILE__, 'deactivate_woo_refund_and_exchange_lite' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite.php';


	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since 1.0.0
	 */
	function run_woo_refund_and_exchange_lite() {
		define_woo_refund_and_exchange_lite_constants();
		auto_update_woo_refund_and_exchange_lite();
		$wps_rma_plugin_standard = new Woo_Refund_And_Exchange_Lite();
		$wps_rma_plugin_standard->wrael_run();
		$GLOBALS['wrael_wps_rma_obj'] = $wps_rma_plugin_standard;
		if ( function_exists( 'vc_lean_map' ) ) {
			include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'wp-bakery-widgets/class-wps-rma-vc-widgets.php';
		}
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'includes/woo-refund-and-exchange-lite-common-functions.php';

	}
	run_woo_refund_and_exchange_lite();

	// Add settings link on plugin page.
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woo_refund_and_exchange_lite_settings_link' );

	/**
	 * Settings link.
	 *
	 * @since 1.0.0
	 * @param Array $links Settings link array.
	 */
	function woo_refund_and_exchange_lite_settings_link( $links ) {
		$my_link = array(
			'<a href="' . admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ) . '">' . esc_html__( 'Settings', 'woo-refund-and-exchange-lite' ) . '</a>',
		);
		return array_merge( $my_link, $links );
	}

	/**
	 * Adding custom setting links at the plugin activation list.
	 *
	 * @param  array  $links_array      array containing the links to plugin.
	 * @param  string $plugin_file_name plugin file name.
	 * @return array
	 */
	function woo_refund_and_exchange_lite_custom_settings_at_plugin_tab( $links_array, $plugin_file_name ) {
		if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
			$links_array[] = '<a href="https://docs.wpswings.com/woo-refund-and-exchange-lite/?utm_source=wpswings-rma-doc&utm_medium=rma-org-page&utm_campaign=rma-doc/" target="_blank"><img src="' . esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) . 'admin/image/Documentation.svg" class="wps-info-img" alt="documentation image">' . esc_html__( 'Documentation', 'woo-refund-and-exchange-lite' ) . '</a>';
			$links_array[] = '<a href="https://wpswings.com/submit-query/?utm_source=wpswings-rma-support&utm_medium=rma-org-backend&utm_campaign=support/" target="_blank"><img src="' . esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) . 'admin/image/Support.svg" class="wps-info-img" alt="support image">' . esc_html__( 'Support', 'woo-refund-and-exchange-lite' ) . '</a>';
		}
		return $links_array;
	}
	add_filter( 'plugin_row_meta', 'woo_refund_and_exchange_lite_custom_settings_at_plugin_tab', 10, 2 );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wps_rma_lite_settings_link' );

	/**
	 * Settings tab of the plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_settings_link
	 * @param array $links array of the links.
	 * @since    1.0.0
	 */
	function wps_rma_lite_settings_link( $links ) {

		if ( ! is_plugin_active( 'rma-return-refund-exchange-for-woocommerce-pro/rma-return-refund-exchange-for-woocommerce-pro.php' ) ) {

			$links['goPro'] = '<a style="color:#FFF;background:linear-gradient(to right,#7a28ff 0,#00a1ff 100%);padding:5px;border-radius:6px;" target="_blank" href="https://wpswings.com/product/rma-return-refund-exchange-for-woocommerce-pro?utm_source=wpswings-rma-pro&utm_medium=rma-org-backend&utm_campaign=go-pro">' . esc_html__( 'GO PREMIUM', 'woo-refund-and-exchange-lite' ) . '</a>';
		}

		return $links;
	}

	/** Function to migrate to settings and data */
	function wps_rma_lite_migrate_settings_and_data() {
		global $wpdb;
		// Check if the plugin has been activated on the network.
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// Get all blogs in the network and activate plugins on each one.
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );

				// Setting And DB Migration Code.
				$check_return    = get_option( 'mwb_wrma_return_enable', 'not_exist' );
				if ( 'not_exist' !== $check_return ) {
					if ( ! get_option( 'wps_rma_lite_pages_migrate', false ) ) {
						$page_id = get_option( 'ced_rnx_return_request_form_page_id' );
						wp_delete_post( $page_id );
						delete_option( 'ced_rnx_return_request_form_page_id' );
						$page_id = get_option( 'ced_rnx_view_order_msg_page_id' );
						wp_delete_post( $page_id );
						delete_option( 'ced_rnx_view_order_msg_page_id' );
						$mwb_wrma_pages = get_option( 'mwb_wrma_pages' );
						if ( isset( $mwb_wrma_pages['pages']['mwb_return_from'] ) ) {
							$page_id = $mwb_wrma_pages['pages']['mwb_return_from'];
							wp_delete_post( $page_id );
						}
						if ( isset( $mwb_wrma_pages['pages']['mwb_view_order_msg'] ) ) {
							$page_id = $mwb_wrma_pages['pages']['mwb_view_order_msg'];
							wp_delete_post( $page_id );
						}
						include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'includes/class-woo-refund-and-exchange-lite-activator.php';
						$activator_class_obj = new Woo_Refund_And_Exchange_Lite_Activator();
						$activator_class_obj::wps_rma_create_pages();
						update_option( 'wps_rma_lite_pages_migrate', true );
					}

					if ( function_exists( 'wps_rma_lite_migrate_settings' ) && ! get_option( 'wps_rma_lite_settings_migrate', false ) ) {
						wps_rma_lite_migrate_settings();
						update_option( 'wps_rma_lite_settings_migrate', true );
					}
					if ( function_exists( 'wps_rma_lite_post_meta_data_migrate' ) && ! get_option( 'wps_rma_lite_post_meta_data_migrate', false ) ) {
						wps_rma_lite_post_meta_data_migrate();
						update_option( 'wps_rma_lite_post_meta_data_migrate', true );
					}
				}

				restore_current_blog();
			}
		} else {
			// Setting And DB Migration Code.
			$check_return    = get_option( 'mwb_wrma_return_enable', 'not_exist' );
			if ( 'not_exist' !== $check_return ) {
				if ( ! get_option( 'wps_rma_lite_pages_migrate', false ) ) {
					$page_id = get_option( 'ced_rnx_return_request_form_page_id' );
					wp_delete_post( $page_id );
					delete_option( 'ced_rnx_return_request_form_page_id' );
					$page_id = get_option( 'ced_rnx_view_order_msg_page_id' );
					wp_delete_post( $page_id );
					delete_option( 'ced_rnx_view_order_msg_page_id' );
					$mwb_wrma_pages = get_option( 'mwb_wrma_pages' );
					if ( isset( $mwb_wrma_pages['pages']['mwb_return_from'] ) ) {
						$page_id = $mwb_wrma_pages['pages']['mwb_return_from'];
						wp_delete_post( $page_id );
					}
					if ( isset( $mwb_wrma_pages['pages']['mwb_view_order_msg'] ) ) {
						$page_id = $mwb_wrma_pages['pages']['mwb_view_order_msg'];
						wp_delete_post( $page_id );
					}
					include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'includes/class-woo-refund-and-exchange-lite-activator.php';
					$activator_class_obj = new Woo_Refund_And_Exchange_Lite_Activator();
					$activator_class_obj::wps_rma_create_pages();
					update_option( 'wps_rma_lite_pages_migrate', true );
				}

				if ( function_exists( 'wps_rma_lite_migrate_settings' ) && ! get_option( 'wps_rma_lite_settings_migrate', false ) ) {
					wps_rma_lite_migrate_settings();
					update_option( 'wps_rma_lite_settings_migrate', true );
				}
			}
		}
		deactivate_plugins( 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php' );
	}
	add_action( 'admin_init', 'wps_rma_lite_migrate_settings_and_data', 10 );

	/**
	 * Migration to new domain notice.
	 *
	 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array  $plugin_data An array of plugin data.
	 * @param string $status Status filter currently applied to the plugin list.
	 */
	function wps_rma_lite_upgrade_notice( $plugin_file, $plugin_data, $status ) {

		?>
		<tr class="plugin-update-tr active notice-warning notice-alt">
			<td colspan="4" class="plugin-update colspanchange">
				<div class="notice notice-success inline update-message notice-alt">
					<div class='wps-notice-title wps-notice-section'>
						<p><strong><?php esc_html_e( 'IMPORTANT NOTICE', 'woo-refund-and-exchange-lite' ); ?>:</strong></p>
					</div>
					<div class='wps-notice-content wps-notice-section'>
						<p><?php esc_html_e( 'From the update', 'woo-refund-and-exchange-lite' ); ?> <strong><?php esc_html_e( 'Version', 'woo-refund-and-exchange-lite' ); ?> 3.1.4</strong> <?php esc_html_e( 'onwards, the plugin and its support will be handled by', 'woo-refund-and-exchange-lite' ); ?> <strong>WP Swings</strong>.</p><p><strong>WP Swings</strong> <?php esc_html_e( 'is just our improvised and rebranded version with all quality solutions and help being the same, so no worries at your end.', 'woo-refund-and-exchange-lite' ); ?>
						<?php esc_html_e( 'Please connect with us for all setup, support, and update related queries without hesitation', 'woo-refund-and-exchange-lite' ); ?>.</p>
					</div>
				</div>
			</td>
		</tr>
		<tr class="plugin-update-tr active notice-warning notice-alt">
			<td  colspan="4" class="plugin-update colspanchange">
				<div class="notice notice-warning inline update-message notice-alt">
					<p>
						<?php esc_html_e( 'Heads up, The latest update includes some substantial changes across different areas of the plugin.', 'woo-refund-and-exchange-lite' ); ?>
					</p>
					<p><b><?php esc_html_e( 'Please Click', 'woo-refund-and-exchange-lite' ) ?><a href="<?php echo esc_attr( admin_url( 'admin.php' ) . '?page=woo_refund_and_exchange_lite_menu&wrael_tab=woo-refund-and-exchange-lite-general' ); ?>"> here </a><?php esc_html_e( 'To Goto the Migration Page and Run the Migration Functionality.', 'woo-refund-and-exchange-lite' ); ?></b></p>
				</div>
			</td>
		</tr>
		<style>
			.wps-notice-section > p:before {
				content: none;
			}
		</style>
		<?php

	}
	add_action( 'after_plugin_row_' . plugin_basename( __FILE__ ), 'wps_rma_lite_upgrade_notice', 0, 3 );

	add_action( 'admin_notices', 'wps_rma_lite_upgrade_notice1' );

	/**
	 * Migration to new domain notice.
	 */
	function wps_rma_lite_upgrade_notice1() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		if ( 'woo_refund_and_exchange_lite_menu' === $page ) {

			?>
			<tr class="plugin-update-tr active notice-error notice-alt">
				<td colspan="4" class="plugin-update colspanchange">
					<div class="notice notice-error update-message notice-alt">
						<div class='wps-notice-title wps-notice-section'>
							<p><strong><?php esc_html_e( 'IMPORTANT NOTICE', 'woo-refund-and-exchange-lite' ); ?>:</strong></p>
						</div>
						<div class='wps-notice-content wps-notice-section'>
							<?php esc_html_e( 'Please click on the Start Migration button so that all of the data migrate. We have Made some changes in our plugin', 'woo-refund-and-exchange-lite' ); ?>.</p>
							<p><button class="button" id="wps_rma_migration_start-button"><?php esc_html_e( 'Start Migration', 'woo-refund-and-exchange-lite' ); ?></button></p>
						</div>
					</div>
				</td>
			</tr>
			<style>
				.wps-notice-section p:before {
					display:  none ! important;
				}
			</style>
			<?php
		}
	}
} else {
	/**
	 * Show warning message if woocommerce is not install
	 */
	function wps_rma_plugin_error_notice_lite() {
		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e( 'Woocommerce is not activated, Please activate Woocommerce first to install WooCommerce Refund and Exchange Lite.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
		<style>
		#message{display:none;}
		</style>
		<?php
	}
	add_action( 'admin_init', 'wps_rma_plugin_deactivate_lite' );


	/**
	 * Call Admin notices
	 *
	 * @name ced_rnx_plugin_deactivate_lite()
	 * @author Wp Swings<webmaster@wpswings.com>
	 * @link http://www.wpswings.com/
	 */
	function wps_rma_plugin_deactivate_lite() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'network_admin_notices', 'wps_rma_plugin_error_notice_lite' );
		add_action( 'admin_notices', 'wps_rma_plugin_error_notice_lite' );
	}
}

