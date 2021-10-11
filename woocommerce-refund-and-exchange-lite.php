<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://makewebbetter.com/
 * @since   1.0.0
 * @package woo-refund-and-exchange-lite
 *
 * @wordpress-plugin
 * Plugin Name:       Return Refund and Exchange for WooCommerce
 * Plugin URI:        https://makewebbetter.com/product/woo-refund-and-exchange-lite/
 * Description:       WooCommerce Refund and Exchange lite allows users to submit product refund. The plugin provides a dedicated mailing system that would help to communicate better between store owner and customers.This is lite version of Woocommerce Refund And Exchange.
 * Version:           4.0.0
 * Author:            makewebbetter
 * Author URI:        https://makewebbetter.com/
 * Text Domain:       woo-refund-and-exchange-lite
 * Domain Path:       /languages
 *
 * Requires at least: 4.6
 * Tested up to:      4.9.5
 *
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$activated           = true;
$active_plugins      = get_option( 'active_plugins', array() );
$active_network_wide = get_site_option( 'active_sitewide_plugins', array() );
if ( ! empty( $active_network_wide ) ) {
	foreach ( $active_network_wide as $key => $value ) {
		$active_plugins[] = $key;
	}
}
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
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
		woo_refund_and_exchange_lite_constants( 'WOO_REFUND_AND_EXCHANGE_LITE_SERVER_URL', 'https://makewebbetter.com' );
		woo_refund_and_exchange_lite_constants( 'WOO_REFUND_AND_EXCHANGE_LITE_ITEM_REFERENCE', 'Woo Refund And Exchange Lite' );
	}

	/**
	 * Define mwb-site update feature.
	 *
	 * @since 1.0.0
	 */
	function auto_update_woo_refund_and_exchange_lite() {
		if ( ! defined( 'WOO_REFUND_AND_EXCHANGE_LITE_SPECIAL_SECRET_KEY' ) ) {
			define( 'WOO_REFUND_AND_EXCHANGE_LITE_SPECIAL_SECRET_KEY', '59f32ad2f20102.74284991' );
		}

		if ( ! defined( 'WOO_REFUND_AND_EXCHANGE_LITE_LICENSE_SERVER_URL' ) ) {
			define( 'WOO_REFUND_AND_EXCHANGE_LITE_LICENSE_SERVER_URL', 'https://makewebbetter.com' );
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
	 */
	function activate_woo_refund_and_exchange_lite( $network_wide ) {
		include_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite-activator.php';
		Woo_Refund_And_Exchange_Lite_Activator::woo_refund_and_exchange_lite_activate( $network_wide );
		$mwb_rma_active_plugin = get_option( 'mwb_all_plugins_active', false );
		if ( is_array( $mwb_rma_active_plugin ) && ! empty( $mwb_rma_active_plugin ) ) {
			$mwb_rma_active_plugin['woo-refund-and-exchange-lite'] = array(
				'plugin_name' => esc_html__( 'Woo Refund And Exchange Lite', 'woo-refund-and-exchange-lite' ),
				'active'      => '1',
			);
		} else {
			$mwb_rma_active_plugin                                 = array();
			$mwb_rma_active_plugin['woo-refund-and-exchange-lite'] = array(
				'plugin_name' => esc_html__( 'Woo Refund And Exchange Lite', 'woo-refund-and-exchange-lite' ),
				'active'      => '1',
			);
		}
		update_option( 'mwb_all_plugins_active', $mwb_rma_active_plugin );
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-woo-refund-and-exchange-lite-deactivator.php
	 */
	function deactivate_woo_refund_and_exchange_lite() {
		include_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite-deactivator.php';
		Woo_Refund_And_Exchange_Lite_Deactivator::woo_refund_and_exchange_lite_deactivate();
		$mwb_rma_deactive_plugin = get_option( 'mwb_all_plugins_active', false );
		if ( is_array( $mwb_rma_deactive_plugin ) && ! empty( $mwb_rma_deactive_plugin ) ) {
			foreach ( $mwb_rma_deactive_plugin as $mwb_rma_deactive_key => $mwb_rma_deactive ) {
				if ( 'woo-refund-and-exchange-lite' === $mwb_rma_deactive_key ) {
					$mwb_rma_deactive_plugin[ $mwb_rma_deactive_key ]['active'] = '0';
				}
			}
		}
		update_option( 'mwb_all_plugins_active', $mwb_rma_deactive_plugin );
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
		$wrael_plugin_standard = new Woo_Refund_And_Exchange_Lite();
		$wrael_plugin_standard->wrael_run();
		$GLOBALS['wrael_mwb_rma_obj'] = $wrael_plugin_standard;
		if ( function_exists( 'vc_lean_map' ) ) {
			include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'wp-bakery-widgets/widgets.php';
		}

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
			$links_array[] = '<a href="#" target="_blank"><img src="' . esc_html__( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) . 'admin/image/Demo.svg" class="mwb-info-img" alt="Demo image">' . esc_html__( 'Demo', 'woo-refund-and-exchange-lite' ) . '</a>';
			$links_array[] = '<a href="https://docs.makewebbetter.com/woocommerce-refund-and-exchange-lite" target="_blank"><img src="' . esc_html__( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) . 'admin/image/Documentation.svg" class="mwb-info-img" alt="documentation image">' . esc_html__( 'Documentation', 'woo-refund-and-exchange-lite' ) . '</a>';
			$links_array[] = '<a href="#" target="_blank"><img src="' . esc_html__( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ) . 'admin/image/Support.svg" class="mwb-info-img" alt="support image">' . esc_html__( 'Support', 'woo-refund-and-exchange-lite' ) . '</a>';
		}
		return $links_array;
	}
	add_filter( 'plugin_row_meta', 'woo_refund_and_exchange_lite_custom_settings_at_plugin_tab', 10, 2 );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mwb_rma_lite_settings_link' );

	/**
	 * Settings tab of the plugin.
	 *
	 * @name rewardeem_woocommerce_points_rewards_settings_link
	 * @param array $links array of the links.
	 * @since    1.0.0
	 */
	function mwb_rma_lite_settings_link( $links ) {

		if ( ! is_plugin_active( 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php' ) ) {

			$links['goPro'] = '<a style="color:#FFF;background:linear-gradient(to right,#7a28ff 0,#00a1ff 100%);padding:5px;border-radius:6px;" target="_blank" href="https://makewebbetter.com/product/woocommerce-rma-return-refund-exchange/">' . esc_html__( 'GO PREMIUM', 'woo-refund-and-exchange-lite' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Check all the condition whether to show refund buttons or not.
	 *
	 * @param string $func  is current functinality name i.e refund/exchange/cancel.
	 * @param object $order is the order object.
	 */
	function mwb_rma_show_buttons( $func, $order ) {
		$show_button = 'no';
		$condition   = get_option( 'policies_setting_option', array() );
		$check       = get_option( 'mwb_rma_' . $func . '_enable', false );
		$get_specific_setting = array();
		if ( 'on' === $check ) {
			if ( isset( $condition['mwb_rma_setting'] ) && ! empty( $condition['mwb_rma_setting'] ) ) {
				foreach ( $condition['mwb_rma_setting'] as $key => $value ) {
					if ( $func === $value['row_functionality'] ) {
						array_push( $get_specific_setting, $value );
					}
				}
			}
			$order_date = date_i18n( 'F d Y', strtotime( $order->get_date_created() ) );
			$today_date = date_i18n( 'F d Y' );
			$order_date = apply_filters( 'mwb_order_status_start_date', strtotime( $order_date ), $order );
			$today_date = strtotime( $today_date );
			$days       = $today_date - $order_date;
			$day_diff   = floor( $days / ( 60 * 60 * 24 ) );
			// Check max days exist in array.
			//$check_order_statuses = strpos( wp_json_encode( $get_specific_setting ), 'mwb_rma_order_status' ) > 0 ? true : false;
			// Check tax handing exist in array.
			$check_tax_handling = strpos( wp_json_encode( $get_specific_setting ), 'mwb_rma_tax_handling' ) > 0 ? true : false;
			if ( ! $check_tax_handling ) {
				update_option( $order->get_id() . 'check_tax', '' );
			}
			if ( empty( $condition ) || ( isset( $condition['mwb_rma_setting'] ) && empty( $condition['mwb_rma_setting'] ) ) ) {
				$show_button = 'yes';
			}
			if ( ! empty( $get_specific_setting ) ) {
				foreach ( $get_specific_setting as $key => $value ) {
					if ( isset( $value['row_policy'] ) && 'mwb_rma_tax_handling' === $value['row_policy'] ) {
						if ( isset( $value['row_tax'] ) && 'mwb_rma_inlcude_tax' === $value['row_tax'] ) {
							update_option( $order->get_id() . 'check_tax', 'mwb_rma_inlcude_tax' );
						} elseif ( isset( $value['row_tax'] ) && 'mwb_rma_exclude_tax' === $value['row_tax'] ) {
							update_option( $order->get_id() . 'check_tax', 'mwb_rma_exclude_tax' );
						}
					}
					if ( isset( $value['row_policy'] ) && 'mwb_rma_maximum_days' === $value['row_policy'] ) {
						if ( isset( $value['row_value'] ) && ! empty( $value['row_value'] ) ) {
							if ( isset( $value['row_conditions1'] ) && 'mwb_rma_less_than' === $value['row_conditions1'] ) {
								if ( isset( $value['row_value'] ) && $day_diff < floatval( $value['row_value'] ) ) {
									$show_button = 'yes';
								} else {
									$show_button = ucfirst( $func ) . esc_html__( ' days exceed must be less than ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
									break;
								}
							} elseif ( $value['row_conditions1'] && 'mwb_rma_greater_than' === $value['row_conditions1'] ) {
								if ( isset( $value['row_value'] ) && $day_diff > floatval( $value['row_value'] ) ) {
									$show_button = 'yes';
								} else {
									$show_button = ucfirst( $func ) . esc_html__( ' days must be greater than ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
									break;
								}
							} elseif ( $value['row_conditions1'] && 'mwb_rma_less_than_equal' === $value['row_conditions1'] ) {
								if ( isset( $value['row_value'] ) && $day_diff <= floatval( $value['row_value'] ) ) {
									$show_button = 'yes';
								} else {
									$show_button = ucfirst( $func ) . esc_html__( ' days must be less than equal to ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
									break;
								}
							} elseif ( $value['row_conditions1'] && 'mwb_rma_greater_than_equal' === $value['row_conditions1'] ) {
								if ( isset( $value['row_value'] ) && $day_diff >= floatval( $value['row_value'] ) ) {
									$show_button = 'yes';
								} else {
									$show_button = ucfirst( $func ) . esc_html__( ' days must be greater than equal to ', 'woo-refund-and-exchange-lite' ) . $value['row_value'];
									break;
								}
							}
						} else {
							$show_button = ucfirst( $func ) . esc_html__( ' max days is blank', 'woo-refund-and-exchange-lite' );
							break;
						}
					} elseif ( isset( $value['row_policy'] ) && 'mwb_rma_order_status' === $value['row_policy'] ) {
						if ( $value['row_conditions2'] && 'mwb_rma_equal_to' === $value['row_conditions2'] ) {
							if ( isset( $value['row_statuses'] ) && in_array( 'wc-' . $order->get_status(), $value['row_statuses'], true ) ) {
								$show_button = 'yes';
							} else {
								$show_button = ucfirst( $func ) . esc_html__( ' request can\'t make on this order.', 'woo-refund-and-exchange-lite' );
								break;
							}
						} elseif ( $value['row_conditions2'] && 'mwb_rma_not_equal_to' === $value['row_conditions2'] ) {
							if ( isset( $value['row_statuses'] ) && ! in_array( 'wc-' . $order->get_status(), $value['row_statuses'], true ) ) {
								$show_button = 'yes';
							} else {
								$show_button = ucfirst( $func ) . esc_html__( ' request can\'t make on this order.', 'woo-refund-and-exchange-lite' );
								break;
							}
						}
					}
				}
			}
		} else {
			$show_button = ucfirst( $func ) . esc_html__( ' request is disabled.', 'woo-refund-and-exchange-lite' );
		}
		$products = get_post_meta( $order->get_id(), 'mwb_rma_return_product', true );
		if ( isset( $products ) && ! empty( $products ) && ! mwb_rma_pro_active() && 'yes' === $show_button ) {
			foreach ( $products as $date => $product ) {
				if ( 'complete' === $product['status'] ) {
					$show_button = 'Refund request is already made and accepted';
				}
			}
		}
		return apply_filters( 'mwb_rma_functionality_reason', $show_button, $func, $order, $get_specific_setting );
	}

	/**
	 * Check Pro Active.
	 */
	function mwb_rma_pro_active() {
		$pro_active = false;
		$pro_active = apply_filters( 'mwb_rma_check_pro_active', $pro_active );
		return $pro_active;
	}

	/**
	 * Function to send messages.
	 *
	 * @name admin_setced_rnx_lite_send_order_msg_callback
	 * @param string $order_id order id.
	 * @param string $msg message.
	 * @param string $sender sender.
	 * @param string $to message to sent.
	 * @link http://www.makewebbetter.com/
	 */
	function mwb_rma_lite_send_order_msg_callback( $order_id, $msg, $sender, $to ) {
		$flag       = false;
		$filename   = array();
		$attachment = array();
		if ( isset( $_FILES['mwb_order_msg_attachment']['tmp_name'] ) && ! empty( $_FILES['mwb_order_msg_attachment']['tmp_name'] ) ) {
			$count         = count( $_FILES['mwb_order_msg_attachment']['tmp_name'] );
			$file_uploaded = false;
			if ( isset( $_FILES['mwb_order_msg_attachment']['tmp_name'][0] ) && ! empty( $_FILES['mwb_order_msg_attachment']['tmp_name'][0] ) ) {
				$file_uploaded = true;
			}
			if ( $file_uploaded ) {
				for ( $i = 0; $i < $count; $i++ ) {
					if ( isset( $_FILES['mwb_order_msg_attachment']['tmp_name'][ $i ] ) ) {
						$directory = ABSPATH . 'wp-content/attachment';
						if ( ! file_exists( $directory ) ) {
							mkdir( $directory, 0755, true );
						}
						$sourcepath             = sanitize_text_field( wp_unslash( $_FILES['mwb_order_msg_attachment']['tmp_name'][ $i ] ) );
						$f_name                 = isset( $_FILES['mwb_order_msg_attachment']['name'][ $i ] ) ? sanitize_text_field( wp_unslash( $_FILES['mwb_order_msg_attachment']['name'][ $i ] ) ) : '';
						$targetpath             = $directory . '/' . $order_id . '-' . $f_name;
						$attachment[ $i ]       = $targetpath;
						$filename[ $i ]['name'] = isset( $_FILES['mwb_order_msg_attachment']['name'][ $i ] ) ? sanitize_text_field( wp_unslash( $_FILES['mwb_order_msg_attachment']['name'][ $i ] ) ) : '';
						$file_type              = isset( $_FILES['mwb_order_msg_attachment']['type'][ $i ] ) ? sanitize_text_field( wp_unslash( $_FILES['mwb_order_msg_attachment']['type'][ $i ] ) ) : '';
						if ( 'image/png' === $file_type || 'image/jpeg' === $file_type || 'image/jpg' === $file_type ) {
							$filename[ $i ] ['img'] = true;
						} else {
							$filename[ $i ]['img'] = false;
						}
						move_uploaded_file( $sourcepath, $targetpath );
					}
				}
			}
		}
		$date                         = strtotime( gmdate( 'Y-m-d H:i:s' ) );
		$order_msg[ $date ]['sender'] = $sender;
		$order_msg[ $date ]['msg']    = $msg;
		$order_msg[ $date ]['files']  = $filename;
		$get_msg                      = get_option( $order_id . '-mwb_cutomer_order_msg', array() );
		$msg_count                    = get_post_meta( $order_id, 'mwb_order_msg_count', 0 );
		if ( isset( $get_msg ) && ! empty( $get_msg ) ) {
			array_push( $get_msg, $order_msg );
		} else {
			$get_msg = array();
			array_push( $get_msg, $order_msg );
		}
		update_option( $order_id . '-mwb_cutomer_order_msg', $get_msg );
		$restrict_mail =
		// Allow/Disallow Email.
		apply_filters( 'mwb_rma_restrict_order_msg_mails', false );
		if ( ! $restrict_mail ) {
			$customer_email = WC()->mailer()->emails['mwb_rma_order_messages_email'];
			$email_status   = $customer_email->trigger( $msg, $attachment, $to, $order_id );
		}
		$flag = true;
		return $flag;
	}

	/**
	 * Format the price showing on the frontend and the backend
	 *
	 * @param string $price is current showing price.
	 * @param string $currency_symbol .
	 */
	function mwb_wrma_format_price( $price, $currency_symbol ) {
		$price           = apply_filters( 'mwb_rma_price_change_everywhere', $price );
		$currency_pos    = get_option( 'woocommerce_currency_pos' );
		switch ( $currency_pos ) {
			case 'left':
				$uprice = $currency_symbol . '<span class="mwb_wrma_formatted_price">' . $price . '</span>';
				break;
			case 'right':
				$uprice = '<span class="mwb_wrma_formatted_price">' . $price . '</span>' . $currency_symbol;
				break;
			case 'left_space':
				$uprice = $currency_symbol . '&nbsp;<span class="mwb_wrma_formatted_price">' . $price . '</span>';
				break;
			case 'right_space':
				$uprice = '<span class="mwb_wrma_formatted_price">' . $price . '</span>&nbsp;' . $currency_symbol;
				break;
		}
		return $uprice;
	}
	/**
	 * Function to restore the setting
	 */
	function mwb_rma_lite_setting_restore() {
		$check_key_exist = get_option( 'mwb_rma_lite_setting_restore', false );
		if ( ! $check_key_exist ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-rma-lite-restore-settings-updation.php';
			$restore_data = new Woocommerce_Rma_Lite_Restore_Settings_Updation();
			$restore_data->mwb_rma_migrate_re_settings();
			update_option( 'mwb_rma_lite_setting_restore', true );
		}
		$check_if_refund_exist = get_option( 'mwb_wrma_return_enable', 'not_exist' );
		if ( 'not_exist' === $check_if_refund_exist ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-rma-lite-preset-settings.php';
			$restore_data = new Woocommerce_Rma_Lite_Preset_Settings();
			$restore_data->mwb_rma_lite_preset_settings();
			update_option( 'mwb_wrma_return_enable', 'no' );
		}
	}
	register_activation_hook( __FILE__, 'mwb_rma_lite_setting_restore' );
} else {
	/**
	 * Show warning message if woocommerce is not install
	 */
	function mwb_rma_plugin_error_notice_lite() {
		?>
		<div class="error notice is-dismissible">
			<p><?php esc_html_e( 'Woocommerce is not activated, Please activate Woocommerce first to install WooCommerce Refund and Exchange Lite.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
		<style>
		#message{display:none;}
		</style>
		<?php
	}
	add_action( 'admin_init', 'mwb_rma_plugin_deactivate_lite' );


	/**
	 * Call Admin notices
	 *
	 * @name ced_rnx_plugin_deactivate_lite()
	 * @author MakeWebBetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function mwb_rma_plugin_deactivate_lite() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'mwb_rma_plugin_error_notice_lite' );
	}
}

