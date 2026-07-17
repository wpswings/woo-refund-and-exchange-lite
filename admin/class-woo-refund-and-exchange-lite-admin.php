<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://wpswings.com/
 * @since 1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin
 */

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin
 */
class Woo_Refund_And_Exchange_Lite_Admin {


	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @var   string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @var   string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$pro_version = null;
		$pro_slug = 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php';
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins   = get_plugins();
		if ( isset( $all_plugins[ $pro_slug ] ) ) {
			$pro_version = $all_plugins[ $pro_slug ]['Version'];
		}
		if ( ( is_null( $pro_version ) || ( $pro_version > '5.0.9' || ( ! is_plugin_active( $pro_slug ) && $pro_version <= '5.0.9' ) ) ) ) {
			require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/pro_setting_templates/admin_setting/class-wps-rma-policies-settings.php';
			require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/pro_setting_templates/admin_setting/class-wps-rma-settings-extend.php';
		}
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Get the current admin screen id.
	 *
	 * @return string
	 */
	private function wrael_get_screen_id() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		return ( ! empty( $screen ) && isset( $screen->id ) ) ? $screen->id : '';
	}

	/**
	 * Determine whether the current screen is the plugin settings screen.
	 *
	 * @param string $screen_id Current screen id.
	 * @return bool
	 */
	private function wrael_is_settings_screen( $screen_id ) {
		return in_array(
			$screen_id,
			array(
				'wp-swings_page_woo_refund_and_exchange_lite_menu',
				'wpswings_page_woo_refund_and_exchange_lite_menu',
			),
			true
		);
	}

	/**
	 * Determine whether the current screen is one of the plugin admin screens.
	 *
	 * @param string $screen_id Current screen id.
	 * @return bool
	 */
	private function wrael_is_plugin_screen( $screen_id ) {
		return in_array(
			$screen_id,
			array(
				'wp-swings_page_woo_refund_and_exchange_lite_menu',
				'wpswings_page_woo_refund_and_exchange_lite_menu',
				'wp-swings_page_home',
				'wpswings_page_home',
			),
			true
		);
	}

	/**
	 * Determine whether the current screen is an order screen.
	 *
	 * @param string $screen_id Current screen id.
	 * @return bool
	 */
	private function wrael_is_order_screen( $screen_id ) {
		return in_array(
			$screen_id,
			array(
				'shop_order',
				'woocommerce_page_wc-orders',
			),
			true
		);
	}

	/**
	 * Determine whether banner assets should load on the current screen.
	 *
	 * @param string $screen_id Current screen id.
	 * @return bool
	 */
	private function wrael_is_banner_screen( $screen_id ) {
		return in_array(
			$screen_id,
			array(
				'plugins',
				'wp-swings_page_woo_refund_and_exchange_lite_menu',
				'wpswings_page_woo_refund_and_exchange_lite_menu',
				'wp-swings_page_home',
				'wpswings_page_home',
			),
			true
		);
	}

	/**
	 * Determine whether multistep assets should load on the current screen.
	 *
	 * @param string $screen_id Current screen id.
	 * @return bool
	 */
	private function wrael_is_multistep_screen( $screen_id ) {
		return $this->wrael_is_settings_screen( $screen_id ) && ! wps_rma_standard_check_multistep() && wps_rma_pro_active();
	}

	/**
	 * Resolve a stable asset version based on file modification time.
	 *
	 * @param string $relative_path Asset path relative to the lite plugin root.
	 * @return string
	 */
	private function wrael_asset_version( $relative_path ) {
		static $version_cache = array();

		$asset_path = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . ltrim( $relative_path, '/' );
		if ( ! isset( $version_cache[ $asset_path ] ) ) {
			$version_cache[ $asset_path ] = file_exists( $asset_path ) ? (string) filemtime( $asset_path ) : (string) $this->version;
		}

		return $version_cache[ $asset_path ];
	}

	/**
	 * Get the user meta key used for the Aurora layout introduction notice.
	 *
	 * @return string
	 */
	private function wrael_layout_notice_meta_key() {
		return 'wrael_aurora_layout_notice_dismissed';
	}

	/**
	 * Dismiss the Aurora layout notice for the current admin user.
	 *
	 * @return void
	 */
	public function wrael_maybe_dismiss_layout_notice() {
		if ( ! isset( $_GET['wrael_hide_layout_notice'] ) || '1' !== sanitize_text_field( wp_unslash( $_GET['wrael_hide_layout_notice'] ) ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'wrael_hide_layout_notice' );

		$user_id = get_current_user_id();
		if ( ! empty( $user_id ) ) {
			update_user_meta( $user_id, $this->wrael_layout_notice_meta_key(), 'yes' );
		}

		$redirect_url = admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' );
		if ( isset( $_GET['wrael_tab'] ) ) {
			$redirect_url = add_query_arg( 'wrael_tab', sanitize_key( wp_unslash( $_GET['wrael_tab'] ) ), $redirect_url );
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 * @param string $hook The plugin page slug.
	 */
	public function wrael_admin_enqueue_styles( $hook ) {
		$screen_id = $this->wrael_get_screen_id();

		if ( $this->wrael_is_multistep_screen( $screen_id ) ) {
			wp_enqueue_style(
				'wps-admin-react-styles',
				WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'multistep-form/build/style-index.css',
				array(),
				$this->wrael_asset_version( 'multistep-form/build/style-index.css' ),
				'all'
			);
			wp_enqueue_style(
				'wps-rma-admin-redesign',
				WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/wps-rma-redesign.css',
				array( 'wps-admin-react-styles' ),
				$this->wrael_asset_version( 'admin/css/wps-rma-redesign.css' ),
				'all'
			);
			return;
		}

		if ( $this->wrael_is_plugin_screen( $screen_id ) ) {
			wp_enqueue_style( 'wps-wrael-select2-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/select-2/woo-refund-and-exchange-lite-select2.css', array(), $this->wrael_asset_version( 'package/lib/select-2/woo-refund-and-exchange-lite-select2.css' ), 'all' );
			wp_enqueue_style( 'wps-wrael-meterial-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-web.min.css', array(), $this->wrael_asset_version( 'package/lib/material-design/material-components-web.min.css' ), 'all' );
			wp_enqueue_style( 'wps-wrael-meterial-css2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.css', array(), $this->wrael_asset_version( 'package/lib/material-design/material-components-v5.0-web.min.css' ), 'all' );
			wp_enqueue_style( 'wps-wrael-meterial-lite', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-lite.min.css', array(), $this->wrael_asset_version( 'package/lib/material-design/material-lite.min.css' ), 'all' );
			wp_enqueue_style( 'wps-wrael-meterial-icons-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/icon.css', array(), $this->wrael_asset_version( 'package/lib/material-design/icon.css' ), 'all' );
			wp_enqueue_style( 'wps-admin-min-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/woo-refund-and-exchange-lite-admin.min.css', array(), $this->wrael_asset_version( 'admin/css/woo-refund-and-exchange-lite-admin.min.css' ), 'all' );
			wp_enqueue_style( 'wps-datatable-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables/media/css/jquery.dataTables.min.css', array(), $this->wrael_asset_version( 'package/lib/datatables/media/css/jquery.dataTables.min.css' ), 'all' );
			wp_enqueue_style( 'wps-rma-admin-redesign', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/wps-rma-redesign.css', array( 'wps-admin-min-css' ), $this->wrael_asset_version( 'admin/css/wps-rma-redesign.css' ), 'all' );
		}

		if ( $this->wrael_is_order_screen( $screen_id ) ) {
			wp_enqueue_style( $this->plugin_name, WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/wps-order-edit-page-lite.scss.min.css', array(), $this->wrael_asset_version( 'admin/css/wps-order-edit-page-lite.scss.min.css' ), 'all' );
		}

		if ( $this->wrael_is_settings_screen( $screen_id ) ) {
			wp_enqueue_style( 'wps-rma-style-jqueru-ui', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/jquery-ui.css', array(), $this->wrael_asset_version( 'admin/css/jquery-ui.css' ), 'all' );
		}

		if ( $this->wrael_is_banner_screen( $screen_id ) ) {
			wp_enqueue_style( 'wps-rma-promotional-banner', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/woo-refund-and-exchange-lite-banner.css', array(), $this->wrael_asset_version( 'admin/css/woo-refund-and-exchange-lite-banner.css' ), 'all' );
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 * @param string $hook The plugin page slug.
	 */
	public function wrael_admin_enqueue_scripts( $hook ) {
		$screen_id  = $this->wrael_get_screen_id();
		$pro_active = wps_rma_pro_active();

		if ( $this->wrael_is_multistep_screen( $screen_id ) ) {
			$script_asset_path = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'multistep-form/build/index-asset.php';
			$script_asset      = file_exists( $script_asset_path )
				? require $script_asset_path
				: array(
					'dependencies' => array(
						'wp-hooks',
						'wp-element',
						'wp-i18n',
						'wc-components',
					),
					'version'      => $this->wrael_asset_version( 'multistep-form/build/index.js' ),
				);

			wp_register_script(
				'react-app-block',
				WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'multistep-form/build/index.js',
				$script_asset['dependencies'],
				$script_asset['version'],
				true
			);
			wp_localize_script(
				'react-app-block',
				'frontend_ajax_object',
				array(
					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
					'wps_standard_nonce' => wp_create_nonce( 'ajax-nonce' ),
					'redirect_url'       => admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ),
				)
			);
			wp_enqueue_script( 'react-app-block' );
			return;
		}

		if ( in_array( $screen_id, array( 'wp-swings_page_woo_refund_and_exchange_lite_menu', 'wpswings_page_woo_refund_and_exchange_lite_menu', 'shop_order', 'plugins', 'wp-swings_page_home', 'wpswings_page_home', 'woocommerce_page_wc-orders' ), true ) ) {
			wp_enqueue_script( 'wps-wrael-select2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/select-2/woo-refund-and-exchange-lite-select2.js', array( 'jquery' ), $this->wrael_asset_version( 'package/lib/select-2/woo-refund-and-exchange-lite-select2.js' ), false );
			wp_enqueue_script( 'wps-wrael-metarial-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-web.min.js', array(), $this->wrael_asset_version( 'package/lib/material-design/material-components-web.min.js' ), false );
			wp_enqueue_script( 'wps-wrael-metarial-js2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.js', array(), $this->wrael_asset_version( 'package/lib/material-design/material-components-v5.0-web.min.js' ), false );
			wp_enqueue_script( 'wps-wrael-metarial-lite', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-lite.min.js', array(), $this->wrael_asset_version( 'package/lib/material-design/material-lite.min.js' ), false );
			wp_enqueue_script( 'wps-wrael-datatable', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/js/jquery.dataTables.min.js', array(), $this->wrael_asset_version( 'package/lib/datatables.net/js/jquery.dataTables.min.js' ), false );
			wp_enqueue_script( 'wps-wrael-datatable-btn', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/buttons/dataTables.buttons.min.js', array(), $this->wrael_asset_version( 'package/lib/datatables.net/buttons/dataTables.buttons.min.js' ), false );
			wp_enqueue_script( 'wps-wrael-datatable-btn-2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/buttons/buttons.html5.min.js', array(), $this->wrael_asset_version( 'package/lib/datatables.net/buttons/buttons.html5.min.js' ), false );
			wp_register_script( $this->plugin_name . 'admin-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/woo-refund-and-exchange-lite-admin.js', array( 'jquery', 'wps-wrael-select2', 'wps-wrael-metarial-js', 'wps-wrael-metarial-js2', 'wps-wrael-metarial-lite' ), $this->wrael_asset_version( 'admin/js/woo-refund-and-exchange-lite-admin.js' ), false );
			wp_localize_script(
				$this->plugin_name . 'admin-js',
				'wrael_admin_param',
				array(
					'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
					'reloadurl'                  => admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ),
					'wps_rma_nonce'              => wp_create_nonce( 'wps_rma_ajax_seurity' ),
					'wrael_expert_action'        => Woo_Refund_And_Exchange_Lite_Talk_To_Expert_Form::AJAX_ACTION,
					'wrael_expert_nonce'         => wp_create_nonce( Woo_Refund_And_Exchange_Lite_Talk_To_Expert_Form::NONCE_ACTION ),
					'wrael_admin_param_location' => admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu&wrael_tab=woo-refund-and-exchange-lite-general' ),
					'check_pro_active'           => esc_html( $pro_active ),
					'wps_policy_already_exist'   => esc_html__( 'Policy already exists', 'woo-refund-and-exchange-lite' ),
					'floating_save_label'        => esc_html__( 'Unsaved changes?', 'woo-refund-and-exchange-lite' ),
					'floating_save_btn'          => esc_html__( 'Save Setting', 'woo-refund-and-exchange-lite' ),
				)
			);
			wp_enqueue_script( $this->plugin_name . 'admin-js' );
		}

		if ( $this->wrael_is_banner_screen( $screen_id ) ) {
			wp_register_script( 'wps-rma-promotional-banner', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/woo-refund-and-exchange-lite-banner.js', array( 'jquery' ), $this->wrael_asset_version( 'admin/js/woo-refund-and-exchange-lite-banner.js' ), false );
			wp_localize_script(
				'wps-rma-promotional-banner',
				'wrael_banner_param',
				array(
					'ajaxurl'       => admin_url( 'admin-ajax.php' ),
					'wps_rma_nonce' => wp_create_nonce( 'wps_rma_ajax_seurity' ),
				)
			);
			wp_enqueue_script( 'wps-rma-promotional-banner' );
		}

		if ( $this->wrael_is_settings_screen( $screen_id ) ) {
			wp_enqueue_script( 'wps-rma-script-timepicker', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/jquery.ui.timepicker.js', array( 'jquery' ), $this->wrael_asset_version( 'admin/js/jquery.ui.timepicker.js' ), true );
		}
	}


	/**
	 * Adding settings menu for Woo Refund And Exchange Lite.
	 *
	 * @since 1.0.0
	 */
	public function wrael_options_page() {
		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['wps-plugins'] ) ) {
			add_menu_page( esc_html( 'WP Swings' ), esc_html( 'WP Swings' ), 'manage_options', 'wps-plugins', array( $this, 'wps_plugins_listing_page' ), WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/WPS_Grey.png', 15 );
			add_submenu_page( 'wps-plugins', 'Home', 'Home', 'manage_options', 'home', array( $this, 'wps_rma_welcome_callback_function' ) );
			$wrael_menus =
			// Add Sub Menu.
			apply_filters( 'wps_add_plugins_menus_array', array() );

			if ( is_array( $wrael_menus ) && ! empty( $wrael_menus ) ) {
				foreach ( $wrael_menus as $wrael_key => $wrael_value ) {
					add_submenu_page( 'wps-plugins', $wrael_value['name'], $wrael_value['name'], 'manage_options', $wrael_value['menu_link'], array( $wrael_value['instance'], $wrael_value['function'] ) );
				}
			}
		} else {
			$is_home = false;
			if ( ! empty( $submenu['wps-plugins'] ) ) {
				foreach ( $submenu['wps-plugins'] as $key => $value ) {
					if ( 'Home' === $value[0] ) {
						$is_home = true;
					}
				}
				if ( ! $is_home ) {
					add_submenu_page( 'wps-plugins', 'Home', 'Home', 'manage_options', 'home', array( $this, 'wps_rma_welcome_callback_function' ), 1 );
				}
			}
		}
	}

	/**
	 *
	 * Adding the default menu into the WordPress menu
	 *
	 * @name wpswings_callback_function
	 * @since 4.0.3
	 */
	public function wps_rma_welcome_callback_function() {
		include WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-welcome.php';
	}

	/**
	 * Removing default submenu of parent menu in backend dashboard
	 *
	 * @since 1.0.0
	 */
	public function wps_rma_remove_default_submenu() {
		global $submenu;
		if ( is_array( $submenu ) && array_key_exists( 'wps-plugins', $submenu ) ) {
			if ( isset( $submenu['wps-plugins'][0] ) ) {
				unset( $submenu['wps-plugins'][0] );
			}
		}
	}


	/**
	 * Woo Refund And Exchange Lite wrael_admin_submenu_page.
	 *
	 * @since 1.0.0
	 * @param array $menus Marketplace menus.
	 */
	public function wrael_admin_submenu_page( $menus = array() ) {
		$menus[] = array(
			'name'      => 'Return Refund and Exchange for WooCommerce',
			'slug'      => 'woo_refund_and_exchange_lite_menu',
			'menu_link' => 'woo_refund_and_exchange_lite_menu',
			'instance'  => $this,
			'function'  => 'wrael_options_menu_html',
		);
		return $menus;
	}

	/**
	 * Woo Refund And Exchange Lite wps_plugins_listing_page.
	 *
	 * @since 1.0.0
	 */
	public function wps_plugins_listing_page() {
		$active_marketplaces =
		// Add Menu.
		apply_filters( 'wps_add_plugins_menus_array', array() );
		if ( is_array( $active_marketplaces ) && ! empty( $active_marketplaces ) ) {
			include WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/welcome.php';
		}
	}

	/**
	 * Woo Refund And Exchange Lite admin menu page.
	 *
	 * @since 1.0.0
	 */
	public function wrael_options_menu_html() {
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-admin-dashboard.php';
	}

	/**
	 * Wps_developer_admin_hooks_listing.
	 */
	public function wps_developer_admin_hooks_listing() {
		$admin_hooks = array();
		$val         = $this->wps_developer_hooks_function( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/' );
		if ( ! empty( $val['hooks'] ) ) {
			$admin_hooks[] = $val['hooks'];
			unset( $val['hooks'] );
		}
		$data = array();
		foreach ( $val['files'] as $v ) {
			if ( 'css' !== $v && 'js' !== $v && 'images' !== $v ) {
				$helo = $this->wps_developer_hooks_function( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/' . $v . '/' );
				if ( ! empty( $helo['hooks'] ) ) {
					$admin_hooks[] = $helo['hooks'];
					unset( $helo['hooks'] );
				}
				if ( ! empty( $helo ) ) {
					$data[] = $helo;
				}
			}
		}
		return $admin_hooks;
	}

	/**
	 * Wps_developer_public_hooks_listing.
	 */
	public function wps_developer_public_hooks_listing() {
		$public_hooks = array();
		$val          = $this->wps_developer_hooks_function( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'public/' );

		if ( ! empty( $val['hooks'] ) ) {
			$public_hooks[] = $val['hooks'];
			unset( $val['hooks'] );
		}
		$data = array();
		foreach ( $val['files'] as $v ) {
			if ( 'css' !== $v && 'js' !== $v && 'images' !== $v ) {
				$helo = $this->wps_developer_hooks_function( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'public/' . $v . '/' );
				if ( ! empty( $helo['hooks'] ) ) {
					$public_hooks[] = $helo['hooks'];
					unset( $helo['hooks'] );
				}
				if ( ! empty( $helo ) ) {
					$data[] = $helo;
				}
			}
		}
		return $public_hooks;
	}
	/**
	 * Wps_developer_hooks_function
	 *
	 * @param string $path .
	 */
	public function wps_developer_hooks_function( $path ) {
		$all_hooks = array();
		$scan      = scandir( $path );
		$response  = array();
		foreach ( $scan as $file ) {
			if ( strpos( $file, '.php' ) ) {
				$myfile = file( $path . $file );
				foreach ( $myfile as $key => $lines ) {
					if ( preg_match( '/do_action/i', $lines ) && ! strpos( $lines, 'str_replace' ) && ! strpos( $lines, 'preg_match' ) ) {
						$all_hooks[ $key ]['action_hook'] = $lines;
						$all_hooks[ $key ]['desc']        = $myfile[ $key - 1 ];
					}
					if ( preg_match( '/apply_filters/i', $lines ) && ! strpos( $lines, 'str_replace' ) && ! strpos( $lines, 'preg_match' ) ) {
						$all_hooks[ $key ]['filter_hook'] = $lines;
						$all_hooks[ $key ]['desc']        = $myfile[ $key - 1 ];
					}
				}
			} elseif ( strpos( $file, '.' ) == '' && strpos( $file, '.' ) !== 0 ) {
				$response['files'][] = $file;
			}
		}
		if ( ! empty( $all_hooks ) ) {
			$response['hooks'] = $all_hooks;
		}
		return $response;
	}

	/**
	 * Woo Refund And Exchange Lite admin menu page.
	 *
	 * @since 1.0.0
	 * @param array $wrael_settings_general Settings fields.
	 */
	public function wrael_admin_general_settings_page( $wrael_settings_general ) {
		$wrael_settings_general = array(
			array(
				'title'   => esc_html__( 'Enable Refund', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_refund_enable',
				'value'   => get_option( 'wps_rma_refund_enable' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'   => esc_html__( 'Enable Order Messages', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_general_om',
				'value'   => get_option( 'wps_rma_general_om' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
		);
		$wrael_settings_general[] = array(
			'title'   => esc_html__( 'Enable to Show Bank Details Field For Manual Refund', 'woo-refund-and-exchange-lite' ),
			'type'    => 'radio-switch',
			'id'      => 'wps_rma_refund_manually_de',
			'value'   => get_option( 'wps_rma_refund_manually_de' ),
			'class'   => 'wrael-radio-switch-class',
			'options' => array(
				'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
				'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
			),
		);
		$wrael_settings_general[] = array(
			'title'   => esc_html__( 'Enable Time Based Policy for Refund, Exchange and Cancellation', 'woo-refund-and-exchange-lite' ),
			'type'    => 'radio-switch',
			'id'      => 'wps_rma_return_time_policy',
			'value'   => get_option( 'wps_rma_return_time_policy' ),
			'class'   => 'wrael-number-class wrael-number-class-time',
			'options' => array(
				'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
				'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
			),
		);
		$wrael_settings_general[] = array(
			'title' => esc_html__( 'Allow the Requests Between', 'woo-refund-and-exchange-lite' ),
			'type'  => 'time',
			'id'    => 'wps_rma_time_duration',
			'to'    => 'wps_rma_time_duration_to',
			'from'  => 'wps_rma_time_duration_from',
			'class' => 'wrael-number-class',
			'description' => esc_html__( 'Enter a valid time period, For Example:', 'woo-refund-and-exchange-lite' ) . ' 4:00 AM - 8:30 AM, 8:00 AM - 12 PM, 2:30 PM - 6:30 PM',
		);
		$wrael_settings_general   =
		// To extend the general setting.
		apply_filters( 'wps_rma_general_setting_extend', $wrael_settings_general );
		$wrael_settings_general[] = array(
			'type'        => 'button',
			'id'          => 'wps_rma_save_general_setting',
			'button_text' => esc_html__( 'Save Setting', 'woo-refund-and-exchange-lite' ),
			'class'       => 'wrael-button-class',
		);
		return $wrael_settings_general;
	}

	/**
	 * Woo Refund And Exchange Lite save tab settings.
	 *
	 * @since 1.0.0
	 */
	public function wrael_admin_save_tab_settings() {
		global $wrael_wps_rma_obj;
		if ( ( isset( $_POST['wps_rma_save_general_setting'] ) || isset( $_POST['wps_rma_save_refund_setting'] ) || isset( $_POST['wps_rma_save_text_setting'] ) || isset( $_POST['wps_rma_save_api_setting'] ) )
			&& ( ! empty( $_POST['wps_tabs_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wps_tabs_nonce'] ) ), 'admin_save_data' ) )
		) {
			$wps_rma_gen_flag = false;
			if ( isset( $_POST['wps_rma_save_general_setting'] ) ) {
				$wrael_genaral_settings =
				// The general tab settings.
				apply_filters( 'wrael_general_settings_array', array() );
			} elseif ( isset( $_POST['wps_rma_save_refund_setting'] ) ) {
				$wrael_genaral_settings =
				// The refund tab settings.
				apply_filters( 'wps_rma_refund_settings_array', array() );
			} elseif ( isset( $_POST['wps_rma_save_text_setting'] ) ) {
				$wrael_genaral_settings =
				// The Order Message tab settings.
				apply_filters( 'wps_rma_order_message_settings_array', array() );
			} elseif ( isset( $_POST['wps_rma_save_api_setting'] ) ) {
				$wrael_genaral_settings =
				// The Order Message tab settings.
				apply_filters( 'wps_rma_api_settings_array', array() );
			}
			$wrael_button_index = array_search( 'submit', array_column( $wrael_genaral_settings, 'type' ), true );
			if ( isset( $wrael_button_index ) && ( null == $wrael_button_index || '' == $wrael_button_index ) ) {
				$wrael_button_index = array_search( 'button', array_column( $wrael_genaral_settings, 'type' ), true );
			}
			if ( isset( $wrael_button_index ) && '' !== $wrael_button_index ) {
				unset( $wrael_genaral_settings[ $wrael_button_index ] );
				if ( is_array( $wrael_genaral_settings ) && ! empty( $wrael_genaral_settings ) ) {
					foreach ( $wrael_genaral_settings as $wrael_genaral_setting ) {
						if ( isset( $wrael_genaral_setting['id'] ) && '' !== $wrael_genaral_setting['id'] ) {
							if ( isset( $_POST[ $wrael_genaral_setting['id'] ] ) ) {
								if ( 'textarea' === $wrael_genaral_setting['type'] || 'text' === $wrael_genaral_setting['type'] ) {
									$setting = sanitize_text_field( wp_unslash( $_POST[ $wrael_genaral_setting['id'] ] ) );
									$setting = trim( preg_replace( '/\s\s+/', ' ', $setting ) );
								}
								if ( 'wps_rma_refund_rules_editor' === $wrael_genaral_setting['id'] ) {
									update_option( 'wps_rma_refund_rules_editor', wp_kses_post( wp_unslash( $_POST[ $wrael_genaral_setting['id'] ] ) ) );
								} elseif ( 'wps_rma_refund_form_css' === $wrael_genaral_setting['id'] ) {
									update_option( 'wps_rma_refund_form_css', wp_kses_post( wp_unslash( $_POST[ $wrael_genaral_setting['id'] ] ) ) );
								} else {
									update_option( sanitize_text_field( wp_unslash( $wrael_genaral_setting['id'] ) ), is_array( $_POST[ $wrael_genaral_setting['id'] ] ) ? map_deep( wp_unslash( $_POST[ $wrael_genaral_setting['id'] ] ), 'sanitize_text_field' ) : stripslashes( sanitize_text_field( wp_unslash( $_POST[ $wrael_genaral_setting['id'] ] ) ) ) );
								}
							} else {
								update_option( sanitize_text_field( wp_unslash( $wrael_genaral_setting['id'] ) ), '' );
							}
						} else {
							$wps_rma_gen_flag = true;
						}
					}
				}
				if ( $wps_rma_gen_flag ) {
					$wps_rma_error_text = esc_html__( 'Id of some field is missing', 'woo-refund-and-exchange-lite' );
					$wrael_wps_rma_obj->wps_rma_plug_admin_notice( $wps_rma_error_text, 'error' );
				} else {
					$wps_rma_error_text = esc_html__( 'Settings saved !', 'woo-refund-and-exchange-lite' );
					$wrael_wps_rma_obj->wps_rma_plug_admin_notice( $wps_rma_error_text, 'success' );
				}
			}
			if ( isset( $_POST['wps_rma_return_from_time'] ) && isset( $_POST['wps_rma_return_to_time'] ) ) {
				update_option( 'wps_rma_time_duration_from', sanitize_text_field( wp_unslash( $_POST['wps_rma_return_from_time'] ) ) );
				update_option( 'wps_rma_time_duration_to', sanitize_text_field( wp_unslash( $_POST['wps_rma_return_to_time'] ) ) );
			}
		}
	}

	/**
	 * Register Refund section setting.
	 *
	 * @param array $wps_rma_settings_refund .
	 */
	public function wps_rma_refund_settings_page( $wps_rma_settings_refund ) {
		$button_view = array(
			'order-page' => esc_html__( 'Order Page', 'woo-refund-and-exchange-lite' ),
			'My account' => esc_html__( 'Order View Page', 'woo-refund-and-exchange-lite' ),
			'Checkout'   => esc_html__( 'Thank You Page', 'woo-refund-and-exchange-lite' ),
		);

		$woocommerce_roles = array(
			'customer'      => esc_html__( 'Customer', 'woo-refund-and-exchange-lite' ),
			'shop_manager'  => esc_html__( 'Shop Manager', 'woo-refund-and-exchange-lite' ),
			'subscriber'    => esc_html__( 'Subscriber', 'woo-refund-and-exchange-lite' ),
			'contributor'   => esc_html__( 'Contributor', 'woo-refund-and-exchange-lite' ),
			'author'        => esc_html__( 'Author', 'woo-refund-and-exchange-lite' ),
			'editor'        => esc_html__( 'Editor', 'woo-refund-and-exchange-lite' ),
			'administrator' => esc_html__( 'Administrator', 'woo-refund-and-exchange-lite' ),
		);

		$woocommerce_roles = apply_filters( 'wps_rma_add_extra_user_role', $woocommerce_roles );

		$pages       = get_pages();
		$get_pages   = array( '' => esc_html__( 'Default', 'woo-refund-and-exchange-lite' ) );
		foreach ( $pages as $page ) {
			$get_pages[ $page->ID ] = $page->post_title;
		}

		$woocommerce_user_emails = array();

		/**
		 * Get all users (you can limit roles if needed)
		 */
		$users = get_users(
			array(
				'fields' => array( 'ID', 'user_email', 'display_name' ),
			)
		);

		if ( ! empty( $users ) ) {
			foreach ( $users as $user ) {
				// Key = email, Value = readable label.
				$woocommerce_user_emails[ $user->user_email ] =
					$user->display_name . ' (' . $user->user_email . ')';
			}
		}

		/**
		 * Allow developers to add/remove users
		 */
		$woocommerce_user_emails = apply_filters(
			'wps_rma_add_specific_users_email',
			$woocommerce_user_emails
		);
		$wps_rma_settings_refund = array(
			array(
				'title'       => esc_html__( 'Select Pages To Hide Refund Button', 'woo-refund-and-exchange-lite' ),
				'type'        => 'multiselect',
				'description' => '',
				'id'          => 'wps_rma_refund_button_pages',
				'value'       => get_option( 'wps_rma_refund_button_pages' ),
				'class'       => 'wrael-multiselect-class wps-defaut-multiselect',
				'placeholder' => '',
				'options'     => $button_view,
			),
			array(
				'title'   => esc_html__( 'Enable To Show Manage Stock Button', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_refund_manage_stock',
				'value'   => get_option( 'wps_rma_refund_manage_stock' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'       => esc_html__( 'Enable Refund Without Return (customer keeps item)', 'woo-refund-and-exchange-lite' ),
				'type'        => 'radio-switch',
				'description' => esc_html__( 'When enabled, admins can approve a return request as refund-only and let the customer keep the item. Such requests are never restocked.', 'woo-refund-and-exchange-lite' ),
				'id'          => 'wps_rma_refund_without_return',
				'value'       => get_option( 'wps_rma_refund_without_return' ),
				'class'       => 'wrael-radio-switch-class',
				'options'     => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'   => esc_html__( 'Enable Attachment', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_refund_attachment',
				'value'   => get_option( 'wps_rma_refund_attachment' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'       => esc_html__( 'Attachment Limit', 'woo-refund-and-exchange-lite' ),
				'type'        => 'number',
				'description' => esc_html__( 'By default, It will take 5. If not given any.', 'woo-refund-and-exchange-lite' ),
				'id'          => 'wps_rma_attachment_limit',
				'value'       => get_option( 'wps_rma_attachment_limit' ),
				'class'       => 'wrael-number-class',
				'min'         => '0',
				'max'         => '15',
				'placeholder' => 'Enter the attachment limit',
			),
			array(
				'title'       => esc_html__( 'Mandatory Attachment', 'woo-refund-and-exchange-lite' ),
				'type'        => 'radio-switch',
				'id'          => 'wps_rma_refund_attachment_mandatory',
				'value'       => get_option( 'wps_rma_refund_attachment_mandatory' ),
				'description' => esc_html__( 'When enabled, the customer must attach a file to submit the refund request. Only applies when Enable Attachment is on.', 'woo-refund-and-exchange-lite' ),
				'class'       => 'wrael-radio-switch-class',
				'options'     => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'   => esc_html__( 'Enable To Refund Shipping Charge', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_allow_refund_shipping_charge',
				'value'   => get_option( 'wps_rma_allow_refund_shipping_charge' ),
				'description' => esc_html__( 'This feature will only work with a full refund order, not a partial refund.', 'woo-refund-and-exchange-lite' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'   => esc_html__( 'Enable To Allow the Refund Request Cancellation by User', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_refund_cancellation',
				'value'   => get_option( 'wps_rma_refund_cancellation' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),

			array(
				'title'   => esc_html__( 'Enable/Disable Refund Functionality for Specific User Roles', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_disable_refund_user_role',
				'value'   => get_option( 'wps_rma_disable_refund_user_role' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),

			array(
				'title'       => esc_html__( 'Select User Roles to Restrict Refund Access', 'woo-refund-and-exchange-lite' ),
				'type'        => 'multiselect',
				'description' => esc_html__( 'If no user role is selected, the refund feature will be available for all user roles', 'woo-refund-and-exchange-lite' ),
				'id'          => 'wps_rma_refund_disable_user_roles',
				'value'       => get_option( 'wps_rma_refund_disable_user_roles' ),
				'class'       => 'wrael-multiselect-class wps-defaut-multiselect',
				'placeholder' => '',
				'options'     => $woocommerce_roles,
			),
			array(
				'title'   => esc_html__( 'Enable/Disable Refund Functionality based on User Count', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_disable_refund_user_count',
				'value'   => get_option( 'wps_rma_disable_refund_user_count' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),

			array(
				'title'       => esc_html__( 'Refund Limit for User Count', 'woo-refund-and-exchange-lite' ),
				'type'        => 'number',
				'description' => esc_html__( 'Need to enter a value for Refund Limit for User Count.', 'woo-refund-and-exchange-lite' ),
				'id'          => 'wps_rma_refund_limit',
				'value'       => get_option( 'wps_rma_refund_limit' ),
				'class'       => 'wrael-number-class',
				'min'         => '0',
				'max'         => '15',
				'placeholder' => 'Enter the refund limit',
			),

			array(
				'title'   => esc_html__( 'Enable/Disable Refund Functionality For Particular User To Prevent Fraud', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_disable_refund_specific_user',
				'value'   => get_option( 'wps_rma_disable_refund_specific_user' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),

			array(
				'title'       => esc_html__( 'Enable To Notify Customer When Blocked/Unblocked From Refund', 'woo-refund-and-exchange-lite' ),
				'type'        => 'radio-switch',
				'id'          => 'wps_rma_refund_block_user_notify_mail',
				'value'       => get_option( 'wps_rma_refund_block_user_notify_mail' ),
				'description' => esc_html__( 'If enabled, an email will be sent to the customer whenever their email is added to or removed from the restricted list below.', 'woo-refund-and-exchange-lite' ),
				'class'       => 'wrael-radio-switch-class',
				'options'     => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),

			array(
				'title'       => esc_html__( 'Enter Particular User Email to Restrict From Refund Functionality', 'woo-refund-and-exchange-lite' ),
				'type'        => 'multiselect',
				'description' => esc_html__( 'If no user email is selected, the refund feature will be available for all user and multiple email can be enter', 'woo-refund-and-exchange-lite' ),
				'id'          => 'wps_rma_refund_disable_specific_users',
				'value'       => get_option( 'wps_rma_refund_disable_specific_users' ),
				'class'       => 'wrael-multiselect-class wps-defaut-multiselect',
				'placeholder' => '',
				'options'     => $woocommerce_user_emails,
			),
		);
		$wps_rma_settings_refund =
		// To extend the refund setting.
		apply_filters( 'wps_rma_refund_setting_extend', $wps_rma_settings_refund );
		$wps_rma_settings_refund[] = array(
			'type' => 'breaker',
			'id'   => 'Appearance',
			'name' => 'Appearance',
		);
		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Refund Button Text', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'wps_rma_refund_button_text',
			'value'       => get_option( 'wps_rma_refund_button_text' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( 'Write the Refund Button Text', 'woo-refund-and-exchange-lite' ),
		);
		$wps_rma_settings_refund[] = array(
			'title'   => esc_html__( 'Enable Refund Reason Description', 'woo-refund-and-exchange-lite' ),
			'type'    => 'radio-switch',
			'id'      => 'wps_rma_refund_description',
			'value'   => get_option( 'wps_rma_refund_description' ),
			'class'   => 'wrael-radio-switch-class',
			'options' => array(
				'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
				'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
			),
		);
		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Predefined Refund Reason', 'woo-refund-and-exchange-lite' ),
			'type'        => 'textarea',
			'id'          => 'wps_rma_refund_reasons',
			'value'       => get_option( 'wps_rma_refund_reasons' ),
			'class'       => 'wrael-textarea-class',
			'rows'        => '2',
			'cols'        => '80',
			'placeholder' => esc_html__( 'Write Multiple Refund Reason Separated by Comma', 'woo-refund-and-exchange-lite' ),
		);
		$wps_rma_settings_refund[] = array(
			'title'   => esc_html__( 'Enable Refund Rules', 'woo-refund-and-exchange-lite' ),
			'type'    => 'radio-switch',
			'id'      => 'wps_rma_refund_rules',
			'value'   => get_option( 'wps_rma_refund_rules' ),
			'class'   => 'wrael-radio-switch-class',
			'options' => array(
				'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
				'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
			),
		);
		$wps_rma_settings_refund[] = array(
			'title' => esc_html__( 'Refund Rules Editor', 'woo-refund-and-exchange-lite' ),
			'type'  => 'wp_editor',
			'id'    => 'wps_rma_refund_rules_editor',
			'value' => get_option( 'wps_rma_refund_rules_editor' ),
			'class' => 'wrael-text-class',
		);
		if ( function_exists( 'vc_lean_map' ) ) {
			$wps_rma_settings_refund[] = array(
				'title'       => esc_html__( 'Select The Page To Redirect', 'woo-refund-and-exchange-lite' ),
				'type'        => 'select',
				'id'          => 'wps_rma_refund_page',
				'description' => '',
				'value'       => get_option( 'wps_rma_refund_page' ),
				'class'       => 'wrael-textarea-class',
				'options'     => $get_pages,
			);
		}
		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Refund Form Wrapper Class', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'wps_wrma_refund_form_wrapper_class',
			'value'       => get_option( 'wps_wrma_refund_form_wrapper_class' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( 'Enter Refund Form Wrapper Class', 'woo-refund-and-exchange-lite' ),
		);
		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Refund Form Custom CSS', 'woo-refund-and-exchange-lite' ),
			'type'        => 'textarea',
			'id'          => 'wps_rma_refund_form_css',
			'value'       => get_option( 'wps_rma_refund_form_css' ),
			'class'       => 'wrael-text-class',
			'rows'        => '5',
			'cols'        => '80',
			'placeholder' => esc_html__( 'Write the Refund Form CSS', 'woo-refund-and-exchange-lite' ),
		);
		$pro_slug = 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php';
		if ( function_exists( 'is_plugin_active' ) && ! is_plugin_active( $pro_slug ) ) {
			$wps_rma_settings_refund[] = array(
				'title' => esc_html__( 'Choose Template', 'woo-refund-and-exchange-lite' ),
				'type'  => 'radio',
				'id'    => 'wps_rma_return_template_css',
				'value' => get_option( 'wps_rma_return_template_css' ),
				'class' => 'mwr-radio-class',
				'options' => array(
					'' => esc_html__( 'Default', 'woo-refund-and-exchange-lite' ),
					'template1' => esc_html__( 'Clean Slate', 'woo-refund-and-exchange-lite' ),
					'template2' => esc_html__( 'Aurora Luxe', 'woo-refund-and-exchange-lite' ),
				),
			);

		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Background Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'wps_rma_refund_background_color',
			'value'       => get_option( 'wps_rma_refund_background_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#fffdf7', 'woo-refund-and-exchange-lite' ),
		);

		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Surface Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'wps_rma_refund_surface_color',
			'value'       => get_option( 'wps_rma_refund_surface_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#ffffff', 'woo-refund-and-exchange-lite' ),
		);

		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Accent Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'wps_rma_refund_accent_color',
			'value'       => get_option( 'wps_rma_refund_accent_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#ff9800', 'woo-refund-and-exchange-lite' ),
		);

		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Text Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'wps_rma_refund_text_color',
			'value'       => get_option( 'wps_rma_refund_text_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#18120b', 'woo-refund-and-exchange-lite' ),
		);

		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Button Text Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'wps_rma_refund_button_text_color',
			'value'       => get_option( 'wps_rma_refund_button_text_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#18120b', 'woo-refund-and-exchange-lite' ),
		);
		}
		$wps_rma_settings_refund   =
		// To extend Refund Apperance setting.
		apply_filters( 'wps_rma_refund_appearance_setting_extend', $wps_rma_settings_refund );

		$wps_rma_settings_refund[] = array(
			'type' => 'breaker',
			'id'   => 'Resolution Deadline Settings',
			'name' => 'Resolution Deadline Settings',
		);
		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Return Resolution Hours', 'woo-refund-and-exchange-lite' ),
			'type'        => 'number',
			'id'          => 'wps_rma_return_sla_hours',
			'value'       => get_option( 'wps_rma_return_sla_hours', 48 ),
			'class'       => 'wrael-number-class',
			'description' => esc_html__( 'Hours allowed to resolve a return request before the resolution deadline. Set 0 to disable.', 'woo-refund-and-exchange-lite' ),
			'placeholder' => '48',
		);
		$wps_rma_settings_refund[] = array(
			'title'       => esc_html__( 'Return Reminder Hours', 'woo-refund-and-exchange-lite' ),
			'type'        => 'number',
			'id'          => 'wps_rma_return_sla_reminder_hours',
			'value'       => get_option( 'wps_rma_return_sla_reminder_hours', 6 ),
			'class'       => 'wrael-number-class',
			'description' => esc_html__( 'Send admin email alert this many hours before the return resolution deadline.', 'woo-refund-and-exchange-lite' ),
			'placeholder' => '6',
		);

		$wps_rma_settings_refund[] = array(
			'type'        => 'button',
			'id'          => 'wps_rma_save_refund_setting',
			'button_text' => esc_html__( 'Save Setting', 'woo-refund-and-exchange-lite' ),
			'class'       => 'wrael-button-class',
		);
		return $wps_rma_settings_refund;
	}

	/**
	 * To add order message tab setting.
	 *
	 * @param array $wps_rma_settings_order_message .
	 */
	public function wps_rma_order_message_settings_page( $wps_rma_settings_order_message ) {
		$pages = get_pages();
		$get_pages = array( '' => esc_html__( 'Default', 'woo-refund-and-exchange-lite' ) );
		foreach ( $pages as $page ) {
			$get_pages[ $page->ID ] = $page->post_title;
		}

		$woocommerce_roles = array(
			'customer'      => esc_html__( 'Customer', 'woo-refund-and-exchange-lite' ),
			'shop_manager'  => esc_html__( 'Shop Manager', 'woo-refund-and-exchange-lite' ),
			'subscriber'    => esc_html__( 'Subscriber', 'woo-refund-and-exchange-lite' ),
			'contributor'   => esc_html__( 'Contributor', 'woo-refund-and-exchange-lite' ),
			'author'        => esc_html__( 'Author', 'woo-refund-and-exchange-lite' ),
			'editor'        => esc_html__( 'Editor', 'woo-refund-and-exchange-lite' ),
			'administrator' => esc_html__( 'Administrator', 'woo-refund-and-exchange-lite' ),
		);

		$woocommerce_roles = apply_filters( 'wps_rma_add_extra_user_role', $woocommerce_roles );

		$wps_rma_settings_order_message = array(
			array(
				'title'   => esc_html__( 'Enable Attachment', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_general_enable_om_attachment',
				'value'   => get_option( 'wps_rma_general_enable_om_attachment' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'       => esc_html__( 'Mandatory Attachment', 'woo-refund-and-exchange-lite' ),
				'type'        => 'radio-switch',
				'id'          => 'wps_rma_general_om_attachment_mandatory',
				'value'       => get_option( 'wps_rma_general_om_attachment_mandatory' ),
				'description' => esc_html__( 'When enabled, a file attachment is required to send an order message. Only applies when Enable Attachment is on.', 'woo-refund-and-exchange-lite' ),
				'class'       => 'wrael-radio-switch-class',
				'options'     => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'   => esc_html__( 'Enable/Disable Order Message Functionality for Specific User Roles', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_disable_order_message_user_role',
				'value'   => get_option( 'wps_rma_disable_order_message_user_role' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'       => esc_html__( 'Select User Roles to Restrict Order Message Access', 'woo-refund-and-exchange-lite' ),
				'type'        => 'multiselect',
				'description' => esc_html__( 'If no user role is selected, the order message feature will be available for all user roles', 'woo-refund-and-exchange-lite' ),
				'id'          => 'wps_rma_order_message_disable_user_roles',
				'value'       => get_option( 'wps_rma_order_message_disable_user_roles' ),
				'class'       => 'wrael-multiselect-class wps-defaut-multiselect',
				'placeholder' => '',
				'options'     => $woocommerce_roles,
			),
		);
		$wps_rma_settings_order_message =
		// To Extend Order Message Setting.
		apply_filters( 'wps_rma_order_message_setting_extend', $wps_rma_settings_order_message );
		$wps_rma_settings_order_message[] = array(
			'type' => 'breaker',
			'id'   => 'Appearance',
			'name' => 'Appearance',
		);
		$wps_rma_settings_order_message[] = array(
			'title'       => esc_html__( 'Order Message Button Text', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'wps_rma_order_message_button_text',
			'value'       => get_option( 'wps_rma_order_message_button_text' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( 'Enter Order Message Button Text', 'woo-refund-and-exchange-lite' ),
		);
		if ( function_exists( 'vc_lean_map' ) ) {
			$wps_rma_settings_order_message[] = array(
				'title'   => esc_html__( 'Select the Page to Redirect', 'woo-refund-and-exchange-lite' ),
				'type'    => 'select',
				'id'      => 'wps_rma_order_msg_page',
				'value'   => get_option( 'wps_rma_order_msg_page' ),
				'class'   => 'wrael-textarea-class',
				'options' => $get_pages,
			);
		}
		$wps_rma_settings_order_message[] = array(
			'title' => esc_html__( 'Choose Template', 'woo-refund-and-exchange-lite' ),
			'type'  => 'radio',
			'id'    => 'wps_rma_order_msg_template_css',
			'value' => get_option( 'wps_rma_order_msg_template_css' ),
			'class' => 'mwr-radio-class',
			'options' => array(
				'' => esc_html__( 'Default', 'woo-refund-and-exchange-lite' ),
				'template2' => esc_html__( 'Aurora Luxe', 'woo-refund-and-exchange-lite' ),
			),
		);

		$wps_rma_settings_order_message[] = array(
			'title'       => esc_html__( 'Background Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'color',
			'id'          => 'wps_rma_order_msg_background_color',
			'value'       => get_option( 'wps_rma_order_msg_background_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#fffdf7', 'woo-refund-and-exchange-lite' ),
		);

		$wps_rma_settings_order_message[] = array(
			'title'       => esc_html__( 'Surface Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'color',
			'id'          => 'wps_rma_order_msg_surface_color',
			'value'       => get_option( 'wps_rma_order_msg_surface_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#ffffff', 'woo-refund-and-exchange-lite' ),
		);

		$wps_rma_settings_order_message[] = array(
			'title'       => esc_html__( 'Accent Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'color',
			'id'          => 'wps_rma_order_msg_accent_color',
			'value'       => get_option( 'wps_rma_order_msg_accent_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#ff9800', 'woo-refund-and-exchange-lite' ),
		);

		$wps_rma_settings_order_message[] = array(
			'title'       => esc_html__( 'Text Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'color',
			'id'          => 'wps_rma_order_msg_text_color',
			'value'       => get_option( 'wps_rma_order_msg_text_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#18120b', 'woo-refund-and-exchange-lite' ),
		);

		$wps_rma_settings_order_message[] = array(
			'title'       => esc_html__( 'Button Text Color', 'woo-refund-and-exchange-lite' ),
			'type'        => 'color',
			'id'          => 'wps_rma_order_msg_button_text_color',
			'value'       => get_option( 'wps_rma_order_msg_button_text_color' ),
			'class'       => 'wrael-text-class',
			'placeholder' => esc_html__( '#18120b', 'woo-refund-and-exchange-lite' ),
		);
		$wps_rma_settings_order_message =
		// To Extend Order Message Appearance Setting.
		apply_filters( 'wps_rma_order_message_appearance_setting_extend', $wps_rma_settings_order_message );
		$wps_rma_settings_order_message[] = array(
			'type'        => 'button',
			'id'          => 'wps_rma_save_text_setting',
			'button_text' => esc_html__( 'Save Setting', 'woo-refund-and-exchange-lite' ),
			'class'       => 'wrael-button-class',
		);
		return $wps_rma_settings_order_message;
	}
	/**
	 * To add api tab setting .
	 *
	 * @param array $wps_rma_settings_api .
	 */
	public function wps_rma_api_settings_page( $wps_rma_settings_api ) {
		$wps_rma_settings_api = array(
			array(
				'title'   => esc_html__( 'Enable API', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'wps_rma_enable_api',
				'value'   => get_option( 'wps_rma_enable_api' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => esc_html__( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => esc_html__( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'       => esc_html__( 'Secret Key', 'woo-refund-and-exchange-lite' ),
				'type'        => 'text',
				'id'          => 'wps_rma_secret_key',
				'attr'        => 'readonly',
				'value'       => get_option( 'wps_rma_secret_key' ),
				'class'       => 'wrael-text-class',
				'placeholder' => esc_html__( 'Please Generate the Secret Key', 'woo-refund-and-exchange-lite' ),
			),
			array(
				'type'        => 'button',
				'id'          => 'wps_rma_generate_key_setting',
				'button_text' => esc_html__( 'Generate Key', 'woo-refund-and-exchange-lite' ),
				'class'       => 'wrael-button-class',
			),
			array(
				'type'        => 'button',
				'id'          => 'wps_rma_save_api_setting',
				'button_text' => esc_html__( 'Save Setting', 'woo-refund-and-exchange-lite' ),
				'class'       => 'wrael-button-class',
			),
		);
		return $wps_rma_settings_api;
	}


	/**
	 * Function to add metabox on the order edit page
	 *
	 * @return void
	 */
	public function wps_wrma_add_metaboxes() {
		$screen = wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';

		$wps_rma_return_enable = get_option( 'wps_rma_refund_enable', 'no' );
		if ( isset( $wps_rma_return_enable ) && 'on' === $wps_rma_return_enable ) {
			add_meta_box(
				'wps_rma_order_refund',
				esc_html__( 'Refund Requested Products', 'woo-refund-and-exchange-lite' ),
				array( $this, 'wps_rma_order_return' ),
				'shop_order'
			);

			add_meta_box(
				'wps_rma_order_refund',
				esc_html__( 'Refund Requested Products', 'woo-refund-and-exchange-lite' ),
				array( $this, 'wps_rma_order_return_hpos' ),
				$screen,
				'advanced',
				'high'
			);
		}
		$wps_rma_om_enable = get_option( 'wps_rma_general_om', 'no' );
		if ( 'on' === $wps_rma_om_enable ) {
			add_meta_box(
				'wps_rma_order_msg_history',
				esc_html__( 'Order Message History', 'woo-refund-and-exchange-lite' ),
				array( $this, 'wps_rma_order_msg_history' ),
				'shop_order'
			);

			add_meta_box(
				'wps_rma_order_msg_history',
				esc_html__( 'Order Message History', 'woo-refund-and-exchange-lite' ),
				array( $this, 'wps_rma_order_msg_history_hpos' ),
				$screen,
				'advanced',
				'high'
			);
		}
	}
	/**
	 * This function is metabox template for order msg history.
	 *
	 * @name wps_rma_order_msg_history.
	 */
	public function wps_rma_order_msg_history() {
		global $post, $thepostid, $theorder;
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-order-message-meta.php';
	}

	/**
	 * This function is metabox template for order msg history.
	 */
	public function wps_rma_order_return() {
		global $post, $thepostid, $theorder;
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-return-meta.php';
	}

	/**
	 * This function is metabox template for order msg history.
	 *
	 * @param object $order .
	 * @name wps_rma_order_msg_history_hpos.
	 */
	public function wps_rma_order_msg_history_hpos( $order ) {
		global $post, $thepostid, $theorder;
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-order-message-meta.php';
	}

	/**
	 * This function is metabox template for order msg history.
	 *
	 * @param object $order .
	 */
	public function wps_rma_order_return_hpos( $order ) {
		global $post, $thepostid, $theorder;
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-return-meta.php';
	}

	/**
	 * Accept return request approve.
	 */
	public function wps_rma_return_req_approve() {
		$check_ajax = check_ajax_referer( 'wps_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			if ( current_user_can( 'wps-rma-refund-approve' ) ) {
				$orderid  = isset( $_POST['orderid'] ) ? sanitize_text_field( wp_unslash( $_POST['orderid'] ) ) : '';
				$products = wps_rma_get_meta_data( $orderid, 'wps_rma_return_product', true );

				// Refund without return: flag the order so the refund is not restocked and no return is expected.
				$keep_item = isset( $_POST['keep_item'] ) ? sanitize_text_field( wp_unslash( $_POST['keep_item'] ) ) : 'no';
				if ( 'on' === get_option( 'wps_rma_refund_without_return' ) && 'yes' === $keep_item ) {
					wps_rma_update_meta_data( $orderid, 'wps_rma_keep_item', 'yes' );
					// Suppress the Manage Stock button so the item is never restocked.
					wps_rma_update_meta_data( $orderid, 'wps_rma_manage_stock_for_return', 'no' );
				}

				$response = wps_rma_return_req_approve_callback( $orderid, $products );

				if ( 'yes' === wps_rma_get_meta_data( $orderid, 'wps_rma_keep_item', true ) ) {
					$order_obj = wc_get_order( $orderid );
					if ( $order_obj ) {
						$order_obj->add_order_note( esc_html__( 'Refund without return — customer keeps the item (not restocked).', 'woo-refund-and-exchange-lite' ), false );
					}
				}

				echo wp_json_encode( $response );
			}
		}
		wp_die();
	}

	/**
	 * Cancel return request cancel.
	 */
	public function wps_rma_return_req_cancel() {
		$check_ajax = check_ajax_referer( 'wps_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			if ( current_user_can( 'wps-rma-refund-cancel' ) ) {
				$orderid  = isset( $_POST['orderid'] ) ? sanitize_text_field( wp_unslash( $_POST['orderid'] ) ) : '';
				$products = wps_rma_get_meta_data( $orderid, 'wps_rma_return_product', true );
				$response = wps_rma_return_req_cancel_callback( $orderid, $products, false );
				echo wp_json_encode( $response );
			}
		}
		wp_die();
	}

	/**
	 * Force restock OFF for "refund without return" (keep the item) requests.
	 *
	 * Hooked to `wps_rma_auto_restock_item_refund` at a priority later than the pro
	 * implementation so it wins. When the order is flagged keep-item, the refund
	 * payload's `restock_items` is set to false, so wc_create_refund() will not
	 * return the item to stock.
	 *
	 * @param bool   $restock Whether to restock on refund.
	 * @param string $orderid Order ID.
	 * @return bool
	 */
	public function wps_rma_keep_item_no_restock( $restock, $orderid ) {
		if ( 'yes' === wps_rma_get_meta_data( $orderid, 'wps_rma_keep_item', true ) ) {
			return false;
		}
		return $restock;
	}

	/**
	 * Add bulk Approve / Reject options to the RMA request list table.
	 *
	 * Hooked to the `wps_rma_lite_request_bulk_option` filter exposed by
	 * Woo_Refund_And_Exchange_Lite_Rma_Request_Table::get_bulk_actions().
	 *
	 * @param array $actions Existing bulk actions.
	 * @return array
	 */
	public function wps_rma_lite_request_bulk_option( $actions ) {
		$actions['wps_rma_bulk_approve'] = esc_html__( 'Approve', 'woo-refund-and-exchange-lite' );
		$actions['wps_rma_bulk_reject']  = esc_html__( 'Reject', 'woo-refund-and-exchange-lite' );
		return $actions;
	}

	/**
	 * Process a bulk Approve / Reject action on selected return requests.
	 *
	 * Hooked to the `wps_rma_lite_process_bulk_request_action` action fired by
	 * Woo_Refund_And_Exchange_Lite_Rma_Request_Table::process_bulk_action() during
	 * prepare_items(). Headers are already sent at this point, so results are shown
	 * via an inline admin notice rather than a redirect. For each selected order the
	 * pending Return request is processed, and — when the pro plugin is active — its
	 * pending Exchange request as well, reusing the same status callbacks as the
	 * single-request AJAX handlers.
	 *
	 * @param string $action Current bulk action slug.
	 * @param array  $post   Raw $_POST from the list table form.
	 */
	public function wps_rma_lite_process_bulk_request_action( $action, $post ) {
		if ( 'wps_rma_bulk_approve' !== $action && 'wps_rma_bulk_reject' !== $action ) {
			return;
		}

		$nonce = isset( $post['wps_rma_request_table_lite'] ) ? sanitize_text_field( wp_unslash( $post['wps_rma_request_table_lite'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wps_rma_request_table_lite' ) ) {
			return;
		}

		$is_approve = 'wps_rma_bulk_approve' === $action;
		$capability = $is_approve ? 'wps-rma-refund-approve' : 'wps-rma-refund-cancel';
		if ( ! current_user_can( $capability ) ) {
			$this->wps_rma_bulk_admin_notice(
				esc_html__( 'You do not have permission to perform this action.', 'woo-refund-and-exchange-lite' ),
				'error'
			);
			return;
		}

		$order_ids = isset( $post['wps_rma_order_ids'] ) ? array_map( 'absint', (array) $post['wps_rma_order_ids'] ) : array();
		$order_ids = array_filter( array_unique( $order_ids ) );
		if ( empty( $order_ids ) ) {
			$this->wps_rma_bulk_admin_notice(
				esc_html__( 'No return requests were selected.', 'woo-refund-and-exchange-lite' ),
				'warning'
			);
			return;
		}

		// Exchange is only available when the pro plugin (and its callbacks) is active.
		$exchange_supported = $is_approve
			? function_exists( 'wps_exchange_req_approve_callback' )
			: function_exists( 'wps_wrma_exchange_req_cancel_callback' );

		$processed = 0;
		$skipped   = 0;
		foreach ( $order_ids as $order_id ) {
			$acted = false;

			// Return request.
			$products = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
			if ( $this->wps_rma_bulk_has_pending( $products ) ) {
				if ( $is_approve ) {
					wps_rma_return_req_approve_callback( $order_id, $products );
				} else {
					wps_rma_return_req_cancel_callback( $order_id, $products, false );
				}
				$acted = true;
			}

			// Exchange request (pro).
			if ( $exchange_supported ) {
				$exchange_products = wps_rma_get_meta_data( $order_id, 'wps_wrma_exchange_product', true );
				if ( $this->wps_rma_bulk_has_pending( $exchange_products ) ) {
					if ( $is_approve ) {
						wps_exchange_req_approve_callback( $order_id );
					} else {
						wps_wrma_exchange_req_cancel_callback( $order_id, false );
					}
					$acted = true;
				}
			}

			if ( $acted ) {
				++$processed;
			} else {
				++$skipped;
			}
		}

		$message = $is_approve
			/* translators: %d: number of requests approved. */
			? sprintf( _n( '%d request approved.', '%d requests approved.', $processed, 'woo-refund-and-exchange-lite' ), $processed )
			/* translators: %d: number of requests rejected. */
			: sprintf( _n( '%d request rejected.', '%d requests rejected.', $processed, 'woo-refund-and-exchange-lite' ), $processed );

		if ( $skipped ) {
			/* translators: %d: number of selected requests skipped because they were not pending. */
			$message .= ' ' . sprintf( _n( '%d request skipped (not pending).', '%d requests skipped (not pending).', $skipped, 'woo-refund-and-exchange-lite' ), $skipped );
		}

		$this->wps_rma_bulk_admin_notice( $message, $processed ? 'success' : 'warning' );
	}

	/**
	 * Whether a request meta payload has at least one pending entry.
	 *
	 * Both return (`wps_rma_return_product`) and exchange (`wps_wrma_exchange_product`)
	 * meta share the same shape: an array keyed by request timestamp, each entry
	 * carrying a `status`.
	 *
	 * @param mixed $products Request meta payload.
	 * @return bool
	 */
	private function wps_rma_bulk_has_pending( $products ) {
		if ( ! is_array( $products ) ) {
			return false;
		}
		foreach ( $products as $product ) {
			if ( isset( $product['status'] ) && 'pending' === $product['status'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Render an inline admin notice for a bulk action result.
	 *
	 * @param string $message Notice text (already translated/escaped).
	 * @param string $type    One of success|warning|error.
	 */
	private function wps_rma_bulk_admin_notice( $message, $type = 'success' ) {
		$class = 'notice notice-' . ( in_array( $type, array( 'success', 'warning', 'error' ), true ) ? $type : 'success' ) . ' is-dismissible';
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
	}

	/**
	 * Refund Amount.
	 */
	public function wps_rma_refund_amount() {
		$check_ajax = check_ajax_referer( 'wps_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			$refund_method = isset( $_POST['refund_method'] ) ? sanitize_text_field( wp_unslash( $_POST['refund_method'] ) ) : '';
			$order_id      = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : '';
			$response      = array();
			if ( '' == $refund_method || 'manual_method' === $refund_method ) {
				$refund_method = 'manual_method';
				wps_rma_update_meta_data( $order_id, 'refundable_amount', 0 );
			} else {
				do_action( 'wps_rma_refund_price', $_POST );
			}
			$response['refund_method'] = $refund_method;
			wps_rma_update_meta_data( $order_id, 'wps_rma_left_amount_done', 'yes' );

			$order = wc_get_order( $order_id );
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $item->get_product();
				if ( 'mwb_booking' === $product->get_type() ) {
					$order->update_status( 'wc-cancelled' );
					break;
				}
			}
		}
		echo wp_json_encode( $response );
		wp_die();
	}


	/**
	 * Restock the refund items
	 */
	public function wps_rma_manage_stock() {
		$check_ajax = check_ajax_referer( 'wps_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			if ( current_user_can( 'wps-rma-refund-manage-stock' ) ) {
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
				if ( $order_id > 0 ) {
					$wps_rma_type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
					if ( '' !== $wps_rma_type && 'wps_rma_return' === $wps_rma_type ) {
						// Check already restock the items.
						$manage_stock = get_option( 'wps_rma_manage_stock_for_return' );
						if ( 'yes' !== $manage_stock ) {
							$wps_rma_return_data = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );
							if ( is_array( $wps_rma_return_data ) && ! empty( $wps_rma_return_data ) ) {
								foreach ( $wps_rma_return_data as $date => $requested_data ) {
									$wps_rma_returned_products = $requested_data['products'];
									if ( is_array( $wps_rma_returned_products ) && ! empty( $wps_rma_returned_products ) ) {
										foreach ( $wps_rma_returned_products as $key => $product_data ) {
											if ( $product_data['variation_id'] > 0 ) {
												$product = wc_get_product( $product_data['variation_id'] );
											} else {
												$product = wc_get_product( $product_data['product_id'] );
											}

											if ( $product->managing_stock() ) {
												$avaliable_qty = $product_data['qty'];
												if ( $product_data['variation_id'] > 0 ) {
													$total_stock = $product->get_stock_quantity();
													$total_stock = $total_stock + $avaliable_qty;
													$product->set_stock_quantity( $total_stock );
												} else {
													$total_stock = $product->get_stock_quantity();
													$total_stock = $total_stock + $avaliable_qty;
													$product->set_stock_quantity( $total_stock );
												}
												$product->save();
												wps_rma_update_meta_data( $order_id, 'wps_rma_manage_stock_for_return', 'no' );
												$response['result'] = 'success';
												$response['msg']    = esc_html__( 'Product Stock is updated Successfully.', 'woo-refund-and-exchange-lite' );
												/* translators: %s: search term */
												wc_get_order( $order_id )->add_order_note( sprintf( esc_html__( '%s Product Stock is updated Successfully.', 'woo-refund-and-exchange-lite' ), $product->get_name() ), false, true );
											} else {
												$response['result'] = false;
												$response['msg']    = esc_html__( 'Product Stock is not updated as manage stock setting of product is disable.', 'woo-refund-and-exchange-lite' );
												/* translators: %s: search term */
												wc_get_order( $order_id )->add_order_note( sprintf( esc_html__( '%s Product Stock is not updated as manage stock setting of product is disable.', 'woo-refund-and-exchange-lite' ), $product->get_name() ), false, true );
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Save policies setting.
	 */
	public function wps_rma_save_policies_setting() {
		global $wrael_wps_rma_obj;
		if ( isset( $_POST['save_policies_setting'] ) && isset( $_POST['get_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['get_nonce'] ) ), 'create_form_nonce' ) ) {
			unset( $_POST['save_policies_setting'] );
			unset( $_POST['get_nonce'] );
			$value = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
			if ( ! empty( $value ) ) {
				foreach ( $value as $setting_index => $setting_value ) {
					if ( isset( $setting_value['row_policy'] ) && 'wps_rma_maximum_days' === $setting_value['row_policy'] && empty( $setting_value['row_value'] ) ) {
						unset( $value[ $setting_index ] );
					}
					if ( isset( $setting_value['row_policy'] ) && 'wps_rma_order_status' === $setting_value['row_policy'] && empty( $setting_value['row_statuses'] ) ) {
						unset( $value[ $setting_index ] );
					}
				}
				// Policies Setting Saving.
				$value = apply_filters( 'wps_rma_policies_setting', $value );
				update_option( 'policies_setting_option', $value );
			}
			$wps_rma_error_text = esc_html__( 'Settings saved !', 'woo-refund-and-exchange-lite' );
			$wrael_wps_rma_obj->wps_rma_plug_admin_notice( $wps_rma_error_text, 'success2' );
		}
	}

	/**
	 * Generate the secret key
	 */
	public function wps_rma_api_secret_key() {
		$check_ajax = check_ajax_referer( 'wps_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			$value = 'wps_' . wc_rand_hash();
			update_option( 'wps_rma_secret_key', $value );
			return 'success';
		}
	}

	/** Add submenu in woocommerce setting */
	public function wps_rma_lite_admin_menus() {
		// phpcs:disable
		$active_plugins          = get_option( 'active_plugins', array() );
		$setting_name = '';
		if ( in_array( 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php', $active_plugins, true ) ) {
			$setting_name = __( 'RMA Configuration', 'woo-refund-and-exchange-lite' );
		} else {
			$setting_name = __( 'Refund-Exchange Lite', 'woo-refund-and-exchange-lite' );
		}
		add_submenu_page( 'woocommerce', $setting_name, $setting_name, 'manage_options', 'woo-refund-and-exchange-lite', array( $this, 'wps_rma_addsubmenu_woocommerce' ) );
		// phpcs:enable
	}

	/**
	 * This function is used to add submenu of subscription inside woocommerce.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function wps_rma_addsubmenu_woocommerce() {
		$permalink = admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' );
		wp_safe_redirect( $permalink );
		exit;
	}

	/**
	 * Get Count
	 *
	 * @param string  $status .
	 * @param string  $action .
	 * @param boolean $type .
	 * @return $result .
	 */
	public function wps_rma_get_count( $status = 'all', $action = 'count', $type = false ) {
		return 0;
	}

	/**
	 * Plugin org setting tab addon
	 *
	 * @param array $mwr_default_tabs .
	 */
	public function wps_rma_plugin_admin_settings_tabs_addon_before( $mwr_default_tabs ) {
		$rma_pro_activate = 'wps_rma_pro_class';
		if ( function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active() ) {
			$rma_pro_activate = null;
		}
		$mwr_default_tabs['rma-return-refund-exchange-for-woocommerce-pro-exchange'] = array(
			'title'     => esc_html__( 'Exchange', 'woo-refund-and-exchange-lite' ),
			'name'      => 'rma-return-refund-exchange-for-woocommerce-pro-exchange',
			'class'     => $rma_pro_activate,
			'file_path' => WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/pro_setting_templates/rma-return-refund-exchange-for-woocommerce-pro-exchange.php',
		);
		$mwr_default_tabs['rma-return-refund-exchange-for-woocommerce-pro-cancel']   = array(
			'title'     => esc_html__( 'Cancel', 'woo-refund-and-exchange-lite' ),
			'name'      => 'rma-return-refund-exchange-for-woocommerce-pro-cancel',
			'class'     => $rma_pro_activate,
			'file_path' => WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/pro_setting_templates/rma-return-refund-exchange-for-woocommerce-pro-cancel.php',
		);
		return $mwr_default_tabs;
	}

	/**
	 * Plugin org setting tab addon
	 *
	 * @param array $mwr_default_tabs .
	 */
	public function wps_rma_plugin_admin_settings_tabs_addon_after( $mwr_default_tabs ) {
		$rma_pro_activate = 'wps_rma_pro_class';
		if ( function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active() ) {
			$rma_pro_activate = null;
		}
		$mwr_default_tabs['rma-return-refund-exchange-for-woocommerce-pro-wallet']           = array(
			'title'     => esc_html__( 'Wallet', 'woo-refund-and-exchange-lite' ),
			'name'      => 'rma-return-refund-exchange-for-woocommerce-pro-wallet',
			'class'     => $rma_pro_activate,
			'file_path' => WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/pro_setting_templates/rma-return-refund-exchange-for-woocommerce-pro-wallet.php',
		);
		$mwr_default_tabs['rma-return-refund-exchange-for-woocommerce-pro-global-shipping']  = array(
			'title'     => esc_html__( 'Global Shipping', 'woo-refund-and-exchange-lite' ),
			'name'      => 'rma-return-refund-exchange-for-woocommerce-pro-global-shipping',
			'class'     => $rma_pro_activate,
			'file_path' => WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/pro_setting_templates/rma-return-refund-exchange-for-woocommerce-pro-global-shipping.php',
		);
		$mwr_default_tabs['rma-return-refund-exchange-for-woocommerce-pro-returnship-label'] = array(
			'title'     => esc_html__( 'Integration', 'woo-refund-and-exchange-lite' ),
			'name'      => 'rma-return-refund-exchange-for-woocommerce-pro-returnship-label',
			'class'     => $rma_pro_activate,
			'file_path' => WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/pro_setting_templates/rma-return-refund-exchange-for-woocommerce-pro-returnship-label.php',
		);
		return $mwr_default_tabs;
	}

	/**
	 * General setting extend
	 *
	 * @param array $wps_rma_settings_general .
	 */
	public function wps_rma_general_setting_extend( $wps_rma_settings_general ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_general_setting_extend_set( $wps_rma_settings_general );
	}

	/**
	 * Refund setting extend
	 *
	 * @param array $wps_rma_settings_refund .
	 */
	public function wps_rma_refund_setting_extend( $wps_rma_settings_refund ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_refund_setting_extend_set( $wps_rma_settings_refund );
	}

	/**
	 * Refund appearance setting extend
	 *
	 * @param array $refund_app_setting_extend .
	 */
	public function wps_rma_refund_appearance_setting_extend( $refund_app_setting_extend ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_refund_appearance_setting_extend_set( $refund_app_setting_extend );
	}

	/**
	 * Exchange setting register.
	 *
	 * @param array $wps_rma_settings_exchange .
	 */
	public function wps_rma_exchange_settings_array( $wps_rma_settings_exchange ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_exchange_settings_array_set( $wps_rma_settings_exchange );
	}

	/**
	 * Cancel setting register.
	 *
	 * @param array $wps_rma_settings_cancel .
	 */
	public function wps_rma_cancel_settings_array( $wps_rma_settings_cancel ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_cancel_settings_array_set( $wps_rma_settings_cancel );
	}

	/**
	 * Wallet setting register.
	 *
	 * @param array $wps_rma_settings_wallet .
	 */
	public function wps_rma_wallet_settings_array( $wps_rma_settings_wallet ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_wallet_settings_array_set( $wps_rma_settings_wallet );
	}

	/**
	 * SMS notifcaiton settings register.
	 *
	 * @param array $wps_rma_settings_wallet .
	 */
	public function wps_rma_sms_notification_settings_array( $wps_rma_settings_wallet ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_sms_notification_settings_array_set( $wps_rma_settings_wallet );
	}

	/**
	 * Whatsapp notifcaiton settings register.
	 *
	 * @param array $wps_rma_settings_wallet .
	 */
	public function wps_rma_whatsapp_notification_settings_array( $wps_rma_settings_wallet ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_whatsapp_notification_settings_array_set( $wps_rma_settings_wallet );
	}

	/**
	 * Order message seting extend.
	 *
	 * @param array $cancel_setting_array .
	 */
	public function wps_rma_order_message_setting_extend( $cancel_setting_array ) {
		$setting_obj = new Wps_Rma_Settings_Extend();
		return $setting_obj->wps_rma_order_message_setting_extend_set( $cancel_setting_array );
	}

	/**
	 * Policy Setting column1 extend.
	 */
	public function wps_rma_setting_extend_column1() {
		$setting_obj = new Wps_Rma_Policies_Settings();
		$setting_obj->wps_rma_setting_extend_column1_set();
	}

	/**
	 * Policy Setting column1 extend.
	 *
	 * @param string $value .
	 * @return void
	 */
	public function wps_rma_setting_extend_show_column1( $value ) {
		$setting_obj = new Wps_Rma_Policies_Settings();
		$setting_obj->wps_rma_setting_extend_show_column1_set( $value );
	}


	/** Policy Setting column3 extend. */
	public function wps_rma_setting_extend_column3() {
		$setting_obj = new Wps_Rma_Policies_Settings();
		$setting_obj->wps_rma_setting_extend_column3_set();
	}

	/**
	 * Policy Setting column3 extend.
	 *
	 * @param array $value .
	 */
	public function wps_rma_setting_extend_show_column3( $value ) {
		$setting_obj = new Wps_Rma_Policies_Settings();
		$setting_obj->wps_rma_setting_extend_show_column3_set( $value );
	}

	/** Policy Setting column5 extend */
	public function wps_rma_setting_extend_column5() {
		$setting_obj = new Wps_Rma_Policies_Settings();
		$setting_obj->wps_rma_setting_extend_column5_set();
	}

	/**
	 * Policy Setting column5 extend.
	 *
	 * @param string $value .
	 * @param string $count .
	 * @return void
	 */
	public function wps_rma_setting_extend_show_column5( $value, $count ) {
		$setting_obj = new Wps_Rma_Policies_Settings();
		$setting_obj->wps_rma_setting_extend_show_column5_set( $value, $count );
	}

	/**
	 * Schedule the cron to get the banner info from the server.
	 */
	public function wps_rma_set_cron_for_plugin_notification() {
		$wps_sfw_offset = get_option( 'gmt_offset' );
		$wps_sfw_time   = time() + $wps_sfw_offset * 60 * 60;
		if ( ! wp_next_scheduled( 'wps_wgm_check_for_notification_update' ) ) {
			wp_schedule_event( $wps_sfw_time, 'daily', 'wps_wgm_check_for_notification_update' );
		}
	}

	/**
	 * Save the promotional banner info.
	 */
	public function wps_rma_save_banner_info() {
		$wps_notification_data = $this->wps_rma_get_update_notification_data();
		if ( is_array( $wps_notification_data ) && ! empty( $wps_notification_data ) ) {
			$banner_id    = array_key_exists( 'notification_id', $wps_notification_data[0] ) ? $wps_notification_data[0]['wps_banner_id'] : '';
			$banner_image = array_key_exists( 'notification_message', $wps_notification_data[0] ) ? $wps_notification_data[0]['wps_banner_image'] : '';
			$banner_url   = array_key_exists( 'notification_message', $wps_notification_data[0] ) ? $wps_notification_data[0]['wps_banner_url'] : '';
			$banner_type  = array_key_exists( 'notification_message', $wps_notification_data[0] ) ? $wps_notification_data[0]['wps_banner_type'] : '';
			update_option( 'wps_wgm_notify_new_banner_id', $banner_id );
			update_option( 'wps_wgm_notify_new_banner_image', $banner_image );
			update_option( 'wps_wgm_notify_new_banner_url', $banner_url );
			if ( 'regular' === $banner_type ) {
				update_option( 'wps_wgm_notify_hide_baneer_notification', 0 );
			}
		}
	}

	/** Fetch the banner info from server throug api call */
	public function wps_rma_get_update_notification_data() {
		$wps_notification_data = array();
		$url                   = 'https://demo.wpswings.com/client-notification/woo-gift-cards-lite/wps-client-notify.php';
		$attr                  = array(
			'action'         => 'wps_notification_fetch',
			'plugin_version' => WOO_REFUND_AND_EXCHANGE_LITE_VERSION,
		);
		$query                 = esc_url_raw( add_query_arg( $attr, $url ) );
		$response              = wp_remote_get(
			$query,
			array(
				'timeout'   => 20,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			echo '<p><strong>Something went wrong: ' . esc_html( stripslashes( $error_message ) ) . '</strong></p>';
		} else {
			$wps_notification_data = json_decode( wp_remote_retrieve_body( $response ), true );
		}
		return $wps_notification_data;
	}

	/** Dismiss the banner */
	public function wps_rma_dismiss_notice_banner_callback() {
		if ( isset( $_REQUEST['wps_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wps_nonce'] ) ), 'wps_rma_ajax_seurity' ) ) {

			$banner_id = get_option( 'wps_wgm_notify_new_banner_id', false );
			if ( isset( $banner_id ) && '' != $banner_id ) {
				update_option( 'wps_wgm_notify_hide_baneer_notification', $banner_id );
			}
			wp_send_json_success();
		}
	}

	/**
	 * Add "Export Refund Orders" button to the WooCommerce orders list page.
	 *
	 * Fires on both the legacy shop_order list (restrict_manage_posts) and the
	 * HPOS order list (woocommerce_order_list_table_restrict_manage_orders).
	 *
	 * @param string $post_type Post type passed by restrict_manage_posts, empty for HPOS hook.
	 */
	public function wps_rma_add_export_refund_button( $post_type = '' ) {
		if ( ! empty( $post_type ) && 'shop_order' !== $post_type ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$has_orders = wc_get_orders(
			array(
				'status'  => array( 'return-requested', 'return-approved', 'return-cancelled', 'refunded' ),
				'limit'   => 1,
				'return'  => 'ids',
			)
		);
		if ( empty( $has_orders ) ) {
			return;
		}
		$export_url = wp_nonce_url(
			add_query_arg( 'wps_rma_export_refund_orders', '1' ),
			'wps_rma_export_refund_orders'
		);
		echo '<a href="' . esc_url( $export_url ) . '" class="button button-secondary" style="margin-left:4px;">'
			. esc_html__( 'Export Refund Orders', 'woo-refund-and-exchange-lite' )
			. '</a>';
	}

	/**
	 * Stream a CSV of all refund-related orders when the export button is clicked.
	 *
	 * Statuses included: Refund Requested, Refund Approved, Refund Cancelled, Refunded.
	 */
	public function wps_rma_handle_export_refund_orders() {
		if ( ! isset( $_GET['wps_rma_export_refund_orders'] ) || '1' !== $_GET['wps_rma_export_refund_orders'] ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) && ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Unauthorized', 'woo-refund-and-exchange-lite' ) );
		}
		check_admin_referer( 'wps_rma_export_refund_orders' );

		$orders = wc_get_orders(
			array(
				'status'  => array( 'return-requested', 'return-approved', 'return-cancelled', 'refunded' ),
				'limit'   => -1,
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		$filename = 'refund-orders-' . date_i18n( 'Y-m-d' ) . '.csv';
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen

		// UTF-8 BOM so Excel opens the file correctly.
		fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite

		fputcsv(
			$out,
			array(
				__( 'Order ID', 'woo-refund-and-exchange-lite' ),
				__( 'Order Date', 'woo-refund-and-exchange-lite' ),
				__( 'Order Status', 'woo-refund-and-exchange-lite' ),
				__( 'Customer Name', 'woo-refund-and-exchange-lite' ),
				__( 'Customer Email', 'woo-refund-and-exchange-lite' ),
				__( 'Billing Phone', 'woo-refund-and-exchange-lite' ),
				__( 'Order Total', 'woo-refund-and-exchange-lite' ),
				__( 'Request Date', 'woo-refund-and-exchange-lite' ),
				__( 'Refund Reason', 'woo-refund-and-exchange-lite' ),
				__( 'Refund Method', 'woo-refund-and-exchange-lite' ),
				__( 'Products (Name | Qty | Price)', 'woo-refund-and-exchange-lite' ),
				__( 'Refund Amount', 'woo-refund-and-exchange-lite' ),
			)
		);

		foreach ( $orders as $order ) {
			$order_id    = $order->get_id();
			$status_name = wc_get_order_status_name( $order->get_status() );
			$order_date  = $order->get_date_created() ? date_i18n( wc_date_format(), $order->get_date_created()->getTimestamp() ) : '';
			$return_data = wps_rma_get_meta_data( $order_id, 'wps_rma_return_product', true );

			if ( ! empty( $return_data ) && is_array( $return_data ) ) {
				foreach ( $return_data as $timestamp => $data ) {
					$products_str = '';
					if ( isset( $data['products'] ) && is_array( $data['products'] ) ) {
						$parts = array();
						foreach ( $data['products'] as $item ) {
							$pid      = isset( $item['variation_id'] ) && ! empty( $item['variation_id'] ) ? $item['variation_id'] : ( isset( $item['product_id'] ) ? $item['product_id'] : 0 );
							$prod_obj = $pid ? wc_get_product( $pid ) : null;
							$name     = $prod_obj ? $prod_obj->get_name() : ( 'Product #' . $pid );
							$parts[]  = $name . ' x' . ( isset( $item['qty'] ) ? $item['qty'] : 1 ) . ' @ ' . wc_format_decimal( isset( $item['price'] ) ? $item['price'] : 0, 2 );
						}
						$products_str = implode( '; ', $parts );
					}

					fputcsv(
						$out,
						array(
							$order_id,
							$order_date,
							$status_name,
							trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
							$order->get_billing_email(),
							$order->get_billing_phone(),
							wc_format_decimal( $order->get_total(), 2 ),
							is_numeric( $timestamp ) ? date_i18n( wc_date_format(), (int) $timestamp ) : $timestamp,
							isset( $data['subject'] ) ? $data['subject'] : '',
							isset( $data['refund_method'] ) ? $data['refund_method'] : '',
							$products_str,
							isset( $data['amount'] ) ? wc_format_decimal( $data['amount'], 2 ) : '',
						)
					);
				}
			} else {
				fputcsv(
					$out,
					array(
						$order_id,
						$order_date,
						$status_name,
						trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
						$order->get_billing_email(),
						$order->get_billing_phone(),
						wc_format_decimal( $order->get_total(), 2 ),
						'', '', '', '', '',
					)
				);
			}
		}

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	// -------------------------------------------------------------------------
	// SLA: Cron registration
	// -------------------------------------------------------------------------

	/**
	 * Schedule the hourly SLA cron event (runs on admin_init so it registers
	 * after the plugin is fully loaded).
	 */
	public function wps_rma_register_sla_cron() {
		if ( ! wp_next_scheduled( 'wps_rma_sla_hourly_check' ) ) {
			wp_schedule_event( time(), 'hourly', 'wps_rma_sla_hourly_check' );
		}
	}

	// -------------------------------------------------------------------------
	// SLA: Hourly cron callback
	// -------------------------------------------------------------------------

	/**
	 * Scan all active RMA requests, send admin email when the request enters
	 * the reminder window, and again (once) when it becomes overdue.
	 */
	public function wps_rma_sla_cron_callback() {
		global $wpdb;

		$return_sla_hours        = (int) get_option( 'wps_rma_return_sla_hours', 0 );
		$return_reminder_hours   = (int) get_option( 'wps_rma_return_sla_reminder_hours', 6 );
		$exchange_sla_hours      = (int) get_option( 'wps_rma_exchange_sla_hours', 0 );
		$exchange_reminder_hours = (int) get_option( 'wps_rma_exchange_sla_reminder_hours', 6 );
		$is_pro                  = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();

		if ( ! $return_sla_hours && ! ( $is_pro && $exchange_sla_hours ) ) {
			return;
		}

		$query_keys = array( 'wps_rma_return_product' );
		if ( $is_pro && $exchange_sla_hours ) {
			$query_keys[] = 'wps_rma_exchange_req_date';
		}

		$placeholders = implode( ', ', array_fill( 0, count( $query_keys ), '%s' ) );

		if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled() ) {
			$sql = $wpdb->prepare(
				"SELECT DISTINCT p.id FROM {$wpdb->prefix}wc_orders AS p
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm ON p.id = pm.order_id
				WHERE pm.meta_key IN ($placeholders)",
				$query_keys
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT DISTINCT p.ID FROM {$wpdb->prefix}posts AS p
				INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
				WHERE p.post_type = 'shop_order'
				AND pm.meta_key IN ($placeholders)",
				$query_keys
			);
		}

		$order_ids = $wpdb->get_col( $sql );
		if ( empty( $order_ids ) ) {
			return;
		}

		$terminal = array( 'accepted', 'cancel', 'cancelled' );

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}

			// --- Return requests ---
			if ( $return_sla_hours ) {
				$return_data = $order->get_meta( 'wps_rma_return_product' );
				if ( is_array( $return_data ) ) {
					foreach ( $return_data as $timestamp => $req ) {
						if ( in_array( strtolower( $req['status'] ?? '' ), $terminal, true ) ) {
							continue;
						}
						$this->wps_rma_maybe_send_sla_alert(
							$order,
							(int) $timestamp,
							'Return',
							$return_sla_hours,
							$return_reminder_hours
						);
					}
				}
			}

			// --- Exchange requests (pro only) ---
			if ( $is_pro && $exchange_sla_hours ) {
				$exchange_data = $order->get_meta( 'wps_wrma_exchange_product' );
				if ( is_array( $exchange_data ) ) {
					foreach ( $exchange_data as $timestamp => $req ) {
						if ( in_array( strtolower( $req['status'] ?? '' ), $terminal, true ) ) {
							continue;
						}
						$this->wps_rma_maybe_send_sla_alert(
							$order,
							(int) $timestamp,
							'Exchange',
							$exchange_sla_hours,
							$exchange_reminder_hours
						);
					}
				}
			}
		}
	}

	/**
	 * Check a single request's SLA and fire the alert email if thresholds are met.
	 * Uses per-request order meta flags to avoid duplicate sends.
	 *
	 * @param WC_Order $order           The order object.
	 * @param int      $timestamp       Unix timestamp when the request was created.
	 * @param string   $type            'Return' or 'Exchange'.
	 * @param int      $sla_hours       Total SLA hours configured.
	 * @param int      $reminder_hours  Reminder window hours configured.
	 */
	private function wps_rma_maybe_send_sla_alert( $order, $timestamp, $type, $sla_hours, $reminder_hours ) {
		$type_key        = strtolower( $type );
		$deadline        = $timestamp + ( $sla_hours * HOUR_IN_SECONDS );
		$now             = current_time( 'timestamp' );
		$hours_remaining = ( $deadline - $now ) / HOUR_IN_SECONDS;

		if ( $hours_remaining > $reminder_hours ) {
			return;
		}

		$meta_key = $hours_remaining <= 0
			? "wps_rma_sla_{$type_key}_overdue_sent_{$timestamp}"
			: "wps_rma_sla_{$type_key}_reminder_sent_{$timestamp}";

		if ( $order->get_meta( $meta_key ) ) {
			return;
		}

		$email_classes = WC()->mailer()->get_emails();
		if ( isset( $email_classes['wps_rma_sla_alert_email'] ) ) {
			$email_classes['wps_rma_sla_alert_email']->trigger(
				$order->get_id(),
				$type,
				$hours_remaining,
				$deadline
			);
		}

		$order->update_meta_data( $meta_key, $now );
		$order->save();
	}

	// -------------------------------------------------------------------------
	// SLA: Dashboard widget
	// -------------------------------------------------------------------------

	/**
	 * Register the SLA overdue dashboard widget.
	 */
	public function wps_rma_sla_dashboard_widget_setup() {
		wp_add_dashboard_widget(
			'wps_rma_sla_overdue',
			esc_html__( 'RMA Deadline Overview', 'woo-refund-and-exchange-lite' ),
			array( $this, 'wps_rma_sla_dashboard_widget_display' )
		);
	}

	/**
	 * Render the SLA dashboard widget.
	 * Shows pending request counts broken down by SLA status (on_track / warning / overdue).
	 * Results are cached in a 1-hour transient to keep the dashboard fast.
	 */
	public function wps_rma_sla_dashboard_widget_display() {
		$counts = get_transient( 'wps_rma_sla_status_counts' );

		if ( false === $counts ) {
			$counts = $this->wps_rma_count_requests_by_sla_status();
			set_transient( 'wps_rma_sla_status_counts', $counts, HOUR_IN_SECONDS );
		}

		$total_pending = $counts['on_track'] + $counts['warning'] + $counts['overdue'];
		$tab_url       = admin_url( 'admin.php?page=wps_wra_menu_slug&active_tab=woo-refund-and-exchange-lite-rma-request' );

		$rows = array(
			array(
				'key'   => 'on_track',
				'color' => '#16a34a',
				'bg'    => '#f0fdf4',
				/* translators: %d: count */
				'label' => sprintf( esc_html__( '%d On Track', 'woo-refund-and-exchange-lite' ), $counts['on_track'] ),
			),
			array(
				'key'   => 'warning',
				'color' => '#d97706',
				'bg'    => '#fffbeb',
				/* translators: %d: count */
				'label' => sprintf( esc_html__( '%d Warning', 'woo-refund-and-exchange-lite' ), $counts['warning'] ),
			),
			array(
				'key'   => 'overdue',
				'color' => '#dc2626',
				'bg'    => '#fef2f2',
				/* translators: %d: count */
				'label' => sprintf( esc_html__( '%d Overdue', 'woo-refund-and-exchange-lite' ), $counts['overdue'] ),
			),
		);
		?>
		<div style="padding:4px 0;">

			<?php if ( 0 === $total_pending ) : ?>
				<p style="color:#16a34a;font-weight:600;margin:0 0 10px;">
					&#10003; <?php esc_html_e( 'No pending RMA requests.', 'woo-refund-and-exchange-lite' ); ?>
				</p>
			<?php else : ?>
				<p style="font-size:13px;color:#475569;margin:0 0 10px;">
					<?php
					printf(
						/* translators: %d: total pending count */
						esc_html__( '%d pending request(s) by deadline status:', 'woo-refund-and-exchange-lite' ),
						esc_html( $total_pending )
					);
					?>
				</p>
				<div style="display:flex;flex-direction:column;gap:6px;margin-bottom:12px;">
					<?php foreach ( $rows as $row ) : ?>
					<div style="display:flex;align-items:center;justify-content:space-between;padding:7px 12px;background:<?php echo esc_attr( $row['bg'] ); ?>;border-radius:6px;">
						<span style="font-size:13px;font-weight:600;color:<?php echo esc_attr( $row['color'] ); ?>;">
							<?php echo esc_html( $row['label'] ); ?>
						</span>
						<?php if ( $counts[ $row['key'] ] > 0 ) : ?>
						<a href="<?php echo esc_url( add_query_arg( array(
							'page'       => 'wps_wra_menu_slug',
							'active_tab' => 'woo-refund-and-exchange-lite-rma-request',
						), admin_url( 'admin.php' ) ) ); ?>"
						   style="font-size:11px;color:<?php echo esc_attr( $row['color'] ); ?>;text-decoration:underline;">
							<?php esc_html_e( 'View', 'woo-refund-and-exchange-lite' ); ?>
						</a>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<a href="<?php echo esc_url( $tab_url ); ?>" style="font-size:13px;">
				<?php esc_html_e( 'View all RMA Requests &rarr;', 'woo-refund-and-exchange-lite' ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Count active (non-terminal) requests grouped by SLA status.
	 * Terminal statuses (complete / accepted / cancel / cancelled) are excluded.
	 *
	 * @return array { on_track: int, warning: int, overdue: int }
	 */
	private function wps_rma_count_requests_by_sla_status() {
		global $wpdb;

		$counts   = array( 'on_track' => 0, 'warning' => 0, 'overdue' => 0 );
		$is_pro   = function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active();
		$terminal = array( 'accepted', 'cancel', 'cancelled', 'complete' );

		$return_sla_hours        = (int) get_option( 'wps_rma_return_sla_hours', 0 );
		$return_reminder_hours   = (int) get_option( 'wps_rma_return_sla_reminder_hours', 6 );
		$exchange_sla_hours      = (int) get_option( 'wps_rma_exchange_sla_hours', 0 );
		$exchange_reminder_hours = (int) get_option( 'wps_rma_exchange_sla_reminder_hours', 6 );

		if ( ! $return_sla_hours && ! ( $is_pro && $exchange_sla_hours ) ) {
			return $counts;
		}

		$query_keys = array();
		if ( $return_sla_hours ) {
			$query_keys[] = 'wps_rma_return_product';
		}
		if ( $is_pro && $exchange_sla_hours ) {
			$query_keys[] = 'wps_rma_exchange_req_date';
		}

		$placeholders = implode( ', ', array_fill( 0, count( $query_keys ), '%s' ) );
		$hpos         = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' )
			&& \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();

		if ( $hpos ) {
			$sql = $wpdb->prepare(
				"SELECT DISTINCT p.id FROM {$wpdb->prefix}wc_orders AS p
				INNER JOIN {$wpdb->prefix}wc_orders_meta AS pm ON p.id = pm.order_id
				WHERE pm.meta_key IN ($placeholders)",
				$query_keys
			);
		} else {
			$sql = $wpdb->prepare(
				"SELECT DISTINCT p.ID FROM {$wpdb->prefix}posts AS p
				INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
				WHERE p.post_type = 'shop_order'
				AND pm.meta_key IN ($placeholders)",
				$query_keys
			);
		}

		$order_ids = $wpdb->get_col( $sql );
		$now       = current_time( 'timestamp' );

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				continue;
			}

			// Return requests.
			if ( $return_sla_hours ) {
				$return_data = $order->get_meta( 'wps_rma_return_product' );
				if ( is_array( $return_data ) ) {
					foreach ( $return_data as $ts => $req ) {
						if ( in_array( strtolower( $req['status'] ?? '' ), $terminal, true ) ) {
							continue;
						}
						$deadline        = (int) $ts + ( $return_sla_hours * HOUR_IN_SECONDS );
						$hours_remaining = ( $deadline - $now ) / HOUR_IN_SECONDS;

						if ( $hours_remaining <= 0 ) {
							++$counts['overdue'];
						} elseif ( $hours_remaining <= $return_reminder_hours ) {
							++$counts['warning'];
						} else {
							++$counts['on_track'];
						}
					}
				}
			}

			// Exchange requests (pro only).
			if ( $is_pro && $exchange_sla_hours ) {
				$exchange_data = $order->get_meta( 'wps_wrma_exchange_product' );
				if ( is_array( $exchange_data ) ) {
					foreach ( $exchange_data as $ts => $req ) {
						if ( in_array( strtolower( $req['status'] ?? '' ), $terminal, true ) ) {
							continue;
						}
						$deadline        = (int) $ts + ( $exchange_sla_hours * HOUR_IN_SECONDS );
						$hours_remaining = ( $deadline - $now ) / HOUR_IN_SECONDS;

						if ( $hours_remaining <= 0 ) {
							++$counts['overdue'];
						} elseif ( $hours_remaining <= $exchange_reminder_hours ) {
							++$counts['warning'];
						} else {
							++$counts['on_track'];
						}
					}
				}
			}
		}

		return $counts;
	}
}
