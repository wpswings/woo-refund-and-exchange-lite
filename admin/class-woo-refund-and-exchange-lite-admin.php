<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://makewebbetter.com/
 * @since 1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/admin
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
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 * @param string $hook The plugin page slug.
	 */
	public function wrael_admin_enqueue_styles( $hook ) {
		$screen = get_current_screen();
		if ( isset( $screen->id ) && 'makewebbetter_page_woo_refund_and_exchange_lite_menu' === $screen->id ) {

			wp_enqueue_style( 'mwb-wrael-select2-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/select-2/woo-refund-and-exchange-lite-select2.css', array(), time(), 'all' );

			wp_enqueue_style( 'mwb-wrael-meterial-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-web.min.css', array(), time(), 'all' );
			wp_enqueue_style( 'mwb-wrael-meterial-css2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.css', array(), time(), 'all' );
			wp_enqueue_style( 'mwb-wrael-meterial-lite', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-lite.min.css', array(), time(), 'all' );

			wp_enqueue_style( 'mwb-wrael-meterial-icons-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/icon.css', array(), time(), 'all' );

			wp_enqueue_style( $this->plugin_name . '-admin-global', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/woo-refund-and-exchange-lite-admin-global.css', array( 'mwb-wrael-meterial-icons-css' ), time(), 'all' );

			wp_enqueue_style( 'mwb-admin-min-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/mwb-admin.min.css', array(), $this->version, 'all' );
			wp_enqueue_style( 'mwb-datatable-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables/media/css/jquery.dataTables.min.css', array(), $this->version, 'all' );
		}
		if ( isset( $screen->id ) && 'shop_order' === $screen->id ) {
			wp_enqueue_style( $this->plugin_name, WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/woo-refund-and-exchange-lite-admin.scss', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 * @param string $hook The plugin page slug.
	 */
	public function wrael_admin_enqueue_scripts( $hook ) {
		$screen     = get_current_screen();
		$pro_active = mwb_rma_pro_active();
		if ( isset( $screen->id ) && 'makewebbetter_page_woo_refund_and_exchange_lite_menu' === $screen->id || 'shop_order' === $screen->id ) {
			wp_enqueue_script( 'mwb-wrael-select2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/select-2/woo-refund-and-exchange-lite-select2.js', array( 'jquery' ), time(), false );
			wp_enqueue_script( 'mwb-wrael-metarial-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-web.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-wrael-metarial-js2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-wrael-metarial-lite', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-lite.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-wrael-datatable', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/js/jquery.dataTables.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-wrael-datatable-btn', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/buttons/dataTables.buttons.min.js', array(), time(), false );
			wp_enqueue_script( 'mwb-wrael-datatable-btn-2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/buttons/buttons.html5.min.js', array(), time(), false );
			wp_register_script( $this->plugin_name . 'admin-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/woo-refund-and-exchange-lite-admin.js', array( 'jquery', 'mwb-wrael-select2', 'mwb-wrael-metarial-js', 'mwb-wrael-metarial-js2', 'mwb-wrael-metarial-lite' ), $this->version, false );
			wp_localize_script(
				$this->plugin_name . 'admin-js',
				'wrael_admin_param',
				array(
					'ajaxurl'                    => admin_url( 'admin-ajax.php' ),
					'reloadurl'                  => admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ),
					'wrael_gen_tab_enable'       => get_option( 'wrael_radio_switch_demo' ),
					'mwb_rma_nonce'              => wp_create_nonce( 'mwb_rma_ajax_seurity' ),
					'wrael_admin_param_location' => ( admin_url( 'admin.php' ) . '?page=woo_refund_and_exchange_lite_menu&wrael_tab=woo-refund-and-exchange-lite-general' ),
					'message_sent'               => __( 'The message has been sent successfully.', 'woo-refund-and-exchange-lite' ),
					'message_empty'              => __( 'Please enter a message.', 'woo-refund-and-exchange-lite' ),
					'check_pro_active'           => esc_html( $pro_active ),
				)
			);
			wp_enqueue_script( $this->plugin_name . 'admin-js' );
			wp_enqueue_script( 'mwb-admin-min-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/mwb-admin.min.js', array(), time(), false );
		}
	}

	/**
	 * Adding settings menu for Woo Refund And Exchange Lite.
	 *
	 * @since 1.0.0
	 */
	public function wrael_options_page() {
		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['mwb-plugins'] ) ) {
			add_menu_page( __( 'MakeWebBetter', 'woo-refund-and-exchange-lite' ), __( 'MakeWebBetter', 'woo-refund-and-exchange-lite' ), 'manage_options', 'mwb-plugins', array( $this, 'mwb_plugins_listing_page' ), WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/MWB_Grey-01.svg', 15 );
			$wrael_menus =
			// desc - filter for trial.
			apply_filters( 'mwb_add_plugins_menus_array', array() );
			if ( is_array( $wrael_menus ) && ! empty( $wrael_menus ) ) {
				foreach ( $wrael_menus as $wrael_key => $wrael_value ) {
					add_submenu_page( 'mwb-plugins', $wrael_value['name'], $wrael_value['name'], 'manage_options', $wrael_value['menu_link'], array( $wrael_value['instance'], $wrael_value['function'] ) );
				}
			}
		}
	}

	/**
	 * Removing default submenu of parent menu in backend dashboard
	 *
	 * @since 1.0.0
	 */
	public function mwb_rma_remove_default_submenu() {
		global $submenu;
		if ( is_array( $submenu ) && array_key_exists( 'mwb-plugins', $submenu ) ) {
			if ( isset( $submenu['mwb-plugins'][0] ) ) {
				unset( $submenu['mwb-plugins'][0] );
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
			'name'      => __( 'Return Refund and Exchange for WooCommerce', 'woo-refund-and-exchange-lite' ),
			'slug'      => 'woo_refund_and_exchange_lite_menu',
			'menu_link' => 'woo_refund_and_exchange_lite_menu',
			'instance'  => $this,
			'function'  => 'wrael_options_menu_html',
		);
		return $menus;
	}

	/**
	 * Woo Refund And Exchange Lite mwb_plugins_listing_page.
	 *
	 * @since 1.0.0
	 */
	public function mwb_plugins_listing_page() {
		$active_marketplaces =
		// desc - filter for trial.
		apply_filters( 'mwb_add_plugins_menus_array', array() );
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
	 * Mwb_developer_admin_hooks_listing.
	 */
	public function mwb_developer_admin_hooks_listing() {
		$admin_hooks = array();
		$val         = $this->mwb_developer_hooks_function( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/' );
		if ( ! empty( $val['hooks'] ) ) {
			$admin_hooks[] = $val['hooks'];
			unset( $val['hooks'] );
		}
		$data = array();
		foreach ( $val['files'] as $v ) {
			if ( 'css' !== $v && 'js' !== $v && 'images' !== $v ) {
				$helo = $this->mwb_developer_hooks_function( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/' . $v . '/' );
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
	 * Mwb_developer_public_hooks_listing.
	 */
	public function mwb_developer_public_hooks_listing() {
		$public_hooks = array();
		$val          = $this->mwb_developer_hooks_function( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'public/' );

		if ( ! empty( $val['hooks'] ) ) {
			$public_hooks[] = $val['hooks'];
			unset( $val['hooks'] );
		}
		$data = array();
		foreach ( $val['files'] as $v ) {
			if ( 'css' !== $v && 'js' !== $v && 'images' !== $v ) {
				$helo = $this->mwb_developer_hooks_function( WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'public/' . $v . '/' );
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
	 * Mwb_developer_hooks_function
	 *
	 * @param string $path .
	 */
	public function mwb_developer_hooks_function( $path ) {
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
				'title'   => __( 'Enable Refund', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'mwb_rma_refund_enable',
				'value'   => get_option( 'mwb_rma_refund_enable' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => __( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'   => __( 'Enable Order Messages', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'mwb_rma_general_om',
				'value'   => get_option( 'mwb_rma_general_om' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => __( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
		);
		$wrael_settings_general =
		// To extend the general setting.
		apply_filters( 'mwb_rma_general_setting_extend', $wrael_settings_general );
		$wrael_settings_general[] = array(
			'type'        => 'button',
			'id'          => 'mwb_rma_save_general_setting',
			'button_text' => __( 'Save Setting', 'woo-refund-and-exchange-lite' ),
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
		global $wrael_mwb_rma_obj;
		if ( ( isset( $_POST['mwb_rma_save_general_setting'] ) || isset( $_POST['mwb_rma_save_refund_setting'] ) || isset( $_POST['mwb_rma_save_text_setting'] ) || isset( $_POST['mwb_rma_save_policies_setting'] ) )
			&& ( ! empty( $_POST['mwb_tabs_nonce'] )
			&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mwb_tabs_nonce'] ) ), 'admin_save_data' ) )
		) {
			$mwb_rma_gen_flag = false;
			if ( isset( $_POST['mwb_rma_save_general_setting'] ) ) {
				$wrael_genaral_settings =
				// The general tab settings.
				apply_filters( 'wrael_general_settings_array', array() );
			} elseif ( isset( $_POST['mwb_rma_save_refund_setting'] ) ) {
				$wrael_genaral_settings =
				// The refund tab settings.
				apply_filters( 'mwb_rma_refund_settings_array', array() );
			} elseif ( isset( $_POST['mwb_rma_save_text_setting'] ) ) {
				$wrael_genaral_settings =
				// The Order Message tab settings.
				apply_filters( 'mwb_rma_order_message_settings_array', array() );
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
								if ( 'mwb_rma_refund_rules_editor' === $wrael_genaral_setting['id'] ) {
									update_option( 'mwb_rma_refund_rules_editor', wp_kses_post( wp_unslash( $_POST[ $wrael_genaral_setting['id'] ] ) ) );
								} else {
									update_option( $wrael_genaral_setting['id'], is_array( $_POST[ $wrael_genaral_setting['id'] ] ) ? $this->mwb_sanitize_array( $_POST[ $wrael_genaral_setting['id'] ] ) : $_POST[ $wrael_genaral_setting['id'] ] );
								}
							} else {
								update_option( $wrael_genaral_setting['id'], '' );
							}
						} else {
							$mwb_rma_gen_flag = true;
						}
					}
				}
				if ( $mwb_rma_gen_flag ) {
					$mwb_rma_error_text = esc_html__( 'Id of some field is missing', 'woo-refund-and-exchange-lite' );
					$wrael_mwb_rma_obj->mwb_rma_plug_admin_notice( $mwb_rma_error_text, 'error' );
				} else {
					$mwb_rma_error_text = esc_html__( 'Settings saved !', 'woo-refund-and-exchange-lite' );
					$wrael_mwb_rma_obj->mwb_rma_plug_admin_notice( $mwb_rma_error_text, 'success' );
				}
			}
		}
	}

	/**
	 * Sanitation for an array
	 *
	 * @param array $mwb_input_array .
	 *
	 * @return array
	 */
	public function mwb_sanitize_array( $mwb_input_array ) {
		foreach ( $mwb_input_array as $key => $value ) {
			$key   = sanitize_text_field( wp_unslash( $key ) );
			$value = sanitize_text_field( wp_unslash( $value ) );
		}
		return $mwb_input_array;
	}

	/**
	 * Register Refund section setting.
	 *
	 * @param array $mwb_rma_settings_refund .
	 */
	public function mwb_rma_refund_settings_page( $mwb_rma_settings_refund ) {
		$button_view             = array(
			'order-page' => __( 'Order Page', 'woo-refund-and-exchange-lite' ),
			'My account' => __( 'Order View Page', 'woo-refund-and-exchange-lite' ),
			'Checkout'   => __( 'Thank You Page', 'woo-refund-and-exchange-lite' ),
		);
		$mwb_rma_settings_refund = array(
			array(
				'title'       => __( 'Select pages to hide refund Button', 'woo-refund-and-exchange-lite' ),
				'type'        => 'multiselect',
				'description' => __( 'Select the pages to hide refund button.', 'woo-refund-and-exchange-lite' ),
				'id'          => 'mwb_rma_refund_button_pages',
				'value'       => get_option( 'mwb_rma_refund_button_pages' ),
				'class'       => 'wrael-multiselect-class mwb-defaut-multiselect',
				'placeholder' => '',
				'options'     => $button_view,
			),
			array(
				'title'   => __( 'Enable to Manage Stock button', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'mwb_rma_refund_manage_stock',
				'value'   => get_option( 'mwb_rma_refund_manage_stock' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => __( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'   => __( 'Enable Attachment', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'mwb_rma_refund_attachment',
				'value'   => get_option( 'mwb_rma_refund_attachment' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => __( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
			array(
				'title'       => __( 'Attachement Limit', 'woo-refund-and-exchange-lite' ),
				'type'        => 'number',
				'description' => __( 'By default, It will take 5. If not given any.', 'woo-refund-and-exchange-lite' ),
				'id'          => 'mwb_rma_attachment_limit',
				'value'       => get_option( 'mwb_rma_attachment_limit' ),
				'class'       => 'wrael-number-class',
				'min'         => '0',
				'max'         => '15',
				'placeholder' => 'Enter the attachment limit',
			),
		);
		$mwb_rma_settings_refund =
		// To extend the refund setting.
		apply_filters( 'mwb_rma_refund_setting_extend', $mwb_rma_settings_refund );
		$mwb_rma_settings_refund[] = array(
			'type' => 'breaker',
			'id'   => 'Appearance',
			'name' => 'Appearance',
		);
		$mwb_rma_settings_refund[] = array(
			'title'       => __( 'Refund Button Text', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'mwb_rma_refund_button_text',
			'value'       => get_option( 'mwb_rma_refund_button_text' ),
			'class'       => 'wrael-text-class',
			'placeholder' => __( 'Enter Refund Button Text', 'woo-refund-and-exchange-lite' ),
		);
		$mwb_rma_settings_refund[] = array(
			'title'   => __( 'Enable Refund Reason Description', 'woo-refund-and-exchange-lite' ),
			'type'    => 'radio-switch',
			'id'      => 'mwb_rma_refund_description',
			'value'   => get_option( 'mwb_rma_refund_description' ),
			'class'   => 'wrael-radio-switch-class',
			'options' => array(
				'yes' => __( 'YES', 'woo-refund-and-exchange-lite' ),
				'no'  => __( 'NO', 'woo-refund-and-exchange-lite' ),
			),
		);
		$mwb_rma_settings_refund[] = array(
			'title'       => __( 'Predefined Refund Reason', 'woo-refund-and-exchange-lite' ),
			'type'        => 'textarea',
			'id'          => 'mwb_rma_refund_reasons',
			'value'       => get_option( 'mwb_rma_refund_reasons' ),
			'class'       => 'wrael-textarea-class',
			'rows'        => '2',
			'cols'        => '80',
			'placeholder' => __( 'Enter the multiple reason separated by comma', 'woo-refund-and-exchange-lite' ),
		);
		$mwb_rma_settings_refund[] = array(
			'title'   => __( 'Enable Refund Rules', 'woo-refund-and-exchange-lite' ),
			'type'    => 'radio-switch',
			'id'      => 'mwb_rma_refund_rules',
			'value'   => get_option( 'mwb_rma_refund_rules' ),
			'class'   => 'wrael-radio-switch-class',
			'options' => array(
				'yes' => __( 'YES', 'woo-refund-and-exchange-lite' ),
				'no'  => __( 'NO', 'woo-refund-and-exchange-lite' ),
			),
		);
		$mwb_rma_settings_refund[] = array(
			'title'       => __( 'Refund Rules Editor', 'woo-refund-and-exchange-lite' ),
			'type'        => 'textarea',
			'id'          => 'mwb_rma_refund_rules_editor',
			'value'       => get_option( 'mwb_rma_refund_rules_editor' ),
			'class'       => 'wrael-textarea-class',
			'rows'        => '5',
			'cols'        => '80',
			'placeholder' => __( 'Enter the Refund Rules( HTML + CSS )', 'woo-refund-and-exchange-lite' ),
		);
		$mwb_rma_settings_refund   =
		// To extend Refund Apperance setting.
		apply_filters( 'mwb_rma_refund_appearance_setting_extend', $mwb_rma_settings_refund );
		$mwb_rma_settings_refund[] = array(
			'type'        => 'button',
			'id'          => 'mwb_rma_save_refund_setting',
			'button_text' => __( 'Save Setting', 'woo-refund-and-exchange-lite' ),
			'class'       => 'wrael-button-class',
		);
		return $mwb_rma_settings_refund;
	}

	/**
	 * Register Policies section setting.
	 *
	 * @param array $mwb_rma_settings_policies .
	 */
	public function mwb_rma_policies_settings_page( $mwb_rma_settings_policies ) {
		$mwb_rma_settings_policies[] = array(
			'type'        => 'button',
			'id'          => 'mwb_rma_save_policies_setting',
			'button_text' => __( 'Save Setting', 'woo-refund-and-exchange-lite' ),
			'class'       => 'wrael-button-class',
		);
		return $mwb_rma_settings_policies;
	}

	/**
	 * To add order message tab setting.
	 *
	 * @param array $mwb_rma_settings_order_message .
	 */
	public function mwb_rma_order_message_settings_page( $mwb_rma_settings_order_message ) {
		$mwb_rma_settings_order_message = array(
			array(
				'title'   => __( 'Enable Attachment upload for order messages', 'woo-refund-and-exchange-lite' ),
				'type'    => 'radio-switch',
				'id'      => 'mwb_rma_general_enable_om_attachment',
				'value'   => get_option( 'mwb_rma_general_enable_om_attachment' ),
				'class'   => 'wrael-radio-switch-class',
				'options' => array(
					'yes' => __( 'YES', 'woo-refund-and-exchange-lite' ),
					'no'  => __( 'NO', 'woo-refund-and-exchange-lite' ),
				),
			),
		);
		$mwb_rma_settings_order_message =
		// To Extend Order Message Setting.
		apply_filters( 'mwb_rma_order_message_setting_extend', $mwb_rma_settings_order_message );
		$mwb_rma_settings_order_message[] = array(
			'type' => 'breaker',
			'id'   => 'Appearance',
			'name' => 'Appearance',
		);
		$mwb_rma_settings_order_message[] = array(
			'title'       => __( 'Order Message Button Text', 'woo-refund-and-exchange-lite' ),
			'type'        => 'text',
			'id'          => 'mwb_rma_order_message_button_text',
			'value'       => get_option( 'mwb_rma_order_message_button_text' ),
			'class'       => 'wrael-text-class',
			'placeholder' => __( 'Enter Order Message Button Text', 'woo-refund-and-exchange-lite' ),
		);
		$mwb_rma_settings_order_message   =
		// To Extend Order Message Appearance Setting.
		apply_filters( 'mwb_rma_order_message_appearance_setting_extend', $mwb_rma_settings_order_message );
		$mwb_rma_settings_order_message[] = array(
			'type'        => 'button',
			'id'          => 'mwb_rma_save_text_setting',
			'button_text' => __( 'Save Setting', 'woo-refund-and-exchange-lite' ),
			'class'       => 'wrael-button-class',
		);
		return $mwb_rma_settings_order_message;
	}
	/**
	 * This function is metabox template for order msg history.
	 *
	 * @name ced_rnx_order_msg_history.
	 */
	public function mwb_rma_order_msg_history() {
		global $post, $thepostid, $theorder;
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-order-message-meta.php';
	}

	/**
	 * This function is metabox template for order msg history.
	 */
	public function mwb_rma_order_return() {
		global $post, $thepostid, $theorder;
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-return-meta.php';
	}

	/**
	 * Function to add metabox on the order edit page
	 *
	 * @return void
	 */
	public function mwb_wrma_add_metaboxes() {
		$mwb_rma_return_enable = get_option( 'mwb_rma_refund_enable', 'no' );
		if ( isset( $mwb_rma_return_enable ) && 'on' === $mwb_rma_return_enable ) {
			add_meta_box(
				'mwb_rma_order_refund',
				__( 'Refund Requested Products', 'woo-refund-and-exchange-lite' ),
				array( $this, 'mwb_rma_order_return' ),
				'shop_order'
			);
		}
		add_meta_box(
			'mwb_rma_order_msg_history',
			__( 'Order Message History', 'woo-refund-and-exchange-lite' ),
			array( $this, 'mwb_rma_order_msg_history' ),
			'shop_order'
		);
	}

	/**
	 * Save order message from admin side.
	 */
	public function mwb_rma_order_messages_save() {
		$check_ajax = check_ajax_referer( 'mwb_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			$msg      = isset( $_POST['msg'] ) ? filter_input( INPUT_POST, 'msg' ) : '';
			$order_id = isset( $_POST['order_id'] ) ? filter_input( INPUT_POST, 'order_id' ) : '';
			$order    = wc_get_order( $order_id );
			$to       = $order->get_billing_email();
			$sender   = 'Shop Manager';
			$flag     = mwb_rma_lite_send_order_msg_callback( $order_id, $msg, $sender, $to );
			echo esc_html( $flag );
			wp_die();
		}
	}

	/**
	 * Accept return request.
	 */
	public function mwb_rma_return_req_approve() {
		$check_ajax = check_ajax_referer( 'mwb_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			if ( current_user_can( 'mwb-rma-refund-approve' ) ) {
				$orderid  = isset( $_POST['orderid'] ) ? sanitize_text_field( wp_unslash( $_POST['orderid'] ) ) : '';
				$date     = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
				$products = get_post_meta( $orderid, 'mwb_rma_return_product', true );

			}
			// Fetch and update the return request product.
			if ( isset( $products ) && ! empty( $products ) ) {
				foreach ( $products as $date => $product ) {
					if ( 'pending' === $product['status'] ) {
						$product_datas                     = $product['products'];
						$products[ $date ]['status']       = 'complete';
						$approvdate                        = date_i18n( wc_date_format(), time() );
						$products[ $date ]['approve_date'] = $approvdate;
						break;
					}
				}
			}

			// Update the status.
			update_post_meta( $orderid, 'mwb_rma_return_product', $products );

			$request_files = get_post_meta( $orderid, 'mwb_rma_return_attachment', true );
			if ( isset( $request_files ) && ! empty( $request_files ) ) {
				foreach ( $request_files as $date => $request_file ) {
					if ( 'pending' === $request_file['status'] ) {
						$request_files[ $date ]['status'] = 'complete';
						break;
					}
				}
			}
			// Update the status.
			update_post_meta( $orderid, 'mwb_rma_return_attachment', $request_files );

			$order_obj    = wc_get_order( $orderid );
			$final_stotal = 0;
			$last_element = end( $order_obj->get_items() );
			foreach ( $order_obj->get_items() as $item_id => $item ) {
				if ( $item !== $last_element ) {
					$final_stotal += $item['subtotal'];
				}
			}

			$update_item_status    = get_post_meta( $orderid, 'mwb_rma_request_made', true );
			$update_item_status[0] = 'completed';
			update_post_meta( $orderid, 'mwb_rma_request_made', $update_item_status );
			// Send refund request accept email to customer.

			$restrict_mail =
			// Allow/Disallow Email.
			apply_filters( 'mwb_rma_restrict_refund_app_mails', false );
			if ( ! $restrict_mail ) {
				// To Send Refund Request Accept Email.
				do_action( 'mwb_rma_refund_req_accept_email', $orderid );
			}

			$order_obj->update_status( 'wc-refund-approved', __( 'User Request of Refund Product is approved', 'woo-refund-and-exchange-lite' ) );
			$response['response'] = 'success';
			echo wp_json_encode( $response );
		}
		wp_die();
	}

	/**
	 * Cancel return request.
	 */
	public function mwb_rma_return_req_cancel() {
		$check_ajax = check_ajax_referer( 'mwb_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			if ( current_user_can( 'ced-rnx-refund-cancel' ) ) {
				$orderid  = isset( $_POST['orderid'] ) ? sanitize_text_field( wp_unslash( $_POST['orderid'] ) ) : '';
				$date     = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
				$products = get_post_meta( $orderid, 'mwb_rma_return_product', true );

				// Fetch the return request product.
				if ( isset( $products ) && ! empty( $products ) ) {
					foreach ( $products as $date => $product ) {
						if ( 'pending' === $product['status'] ) {
							$product_datas                    = $product['products'];
							$products[ $date ]['status']      = 'cancel';
							$approvdate                       = date_i18n( wc_date_format(), time() );
							$products[ $date ]['cancel_date'] = $approvdate;
							break;
						}
					}
				}
				// Update the status.
				update_post_meta( $orderid, 'mwb_rma_return_product', $products );

				$request_files = get_post_meta( $orderid, 'mwb_rma_return_attachment', true );
				if ( isset( $request_files ) && ! empty( $request_files ) ) {
					foreach ( $request_files as $date => $request_file ) {
						if ( 'pending' === $request_file['status'] ) {
							$request_files[ $date ]['status'] = 'cancel';
						}
					}
				}
				// Update the status.
				update_post_meta( $orderid, 'ced_rnx_return_attachment', $request_files );

				// Send the cancel refund request email to customer.

				$restrict_mail =
				// Allow/Disallow Email.
				apply_filters( 'mwb_rma_restrict_refund_cancel_mails', false );
				if ( ! $restrict_mail ) {
					// To Send Refund Request Cancel Email.
					do_action( 'mwb_rma_refund_req_cancel_email', $orderid );
				}
				$order_obj = wc_get_order( $orderid );
				$order_obj->update_status( 'wc-refund-cancelled', __( 'User Request of Refund Product is approved', 'woo-refund-and-exchange-lite' ) );
				$response['response'] = 'success';
				echo wp_json_encode( $response );

			}
		}
		wp_die();
	}

	/**
	 * Update left amount because amount is refunded.
	 *
	 * @param int $order_get_id order id.
	 * @param int $refund_get_id refund order id.
	 */
	public function mwb_rma_action_woocommerce_order_refunded( $order_get_id, $refund_get_id ) {
		update_post_meta( $refund_get_id['order_id'], 'mwb_rma_left_amount_done', 'yes' );
	}


	/**
	 * Restock the refund items
	 */
	public function mwb_rma_manage_stock() {
		$check_ajax = check_ajax_referer( 'mwb_rma_ajax_seurity', 'security_check' );
		if ( $check_ajax ) {
			if ( current_user_can( 'mwb-rma-refund-manage-stock' ) ) {
				$order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
				if ( $order_id > 0 ) {
					$mwb_rma_type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
					if ( '' !== $mwb_rma_type && 'mwb_rma_return' === $mwb_rma_type ) {
						// Check already restock the items.
						$manage_stock = get_option( 'mwb_rma_manage_stock_for_return' );
						if ( 'yes' !== $manage_stock ) {
							$mwb_rma_return_data = get_post_meta( $order_id, 'mwb_rma_return_product', true );
							if ( is_array( $mwb_rma_return_data ) && ! empty( $mwb_rma_return_data ) ) {
								foreach ( $mwb_rma_return_data as $date => $requested_data ) {
									$mwb_rma_returned_products = $requested_data['products'];
									if ( is_array( $mwb_rma_returned_products ) && ! empty( $mwb_rma_returned_products ) ) {
										foreach ( $mwb_rma_returned_products as $key => $product_data ) {
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
												update_post_meta( $order_id, 'mwb_rma_manage_stock_for_return', 'no' );
												$response['result'] = 'success';
												$response['msg']    = __( 'Product Stock is updated Successfully.', 'woo-refund-and-exchange-lite' );
												/* translators: %s: search term */
												wc_get_order( $order_id )->add_order_note( sprintf( __( '%s Product Stock is updated Successfully.', 'mwb-woocommerce-rma' ), $product->get_name() ), false, true );
											} else {
												$response['result'] = false;
												$response['msg']    = __( 'Product Stock is not updated as manage stock setting of product is disable.', 'woo-refund-and-exchange-lite' );
												/* translators: %s: search term */
												wc_get_order( $order_id )->add_order_note( sprintf( __( '%s Product Stock is not updated as manage stock setting of product is disable.', 'mwb-woocommerce-rma' ), $product->get_name() ), false, true );
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
	 * Send refund request accept email to customer
	 *
	 * @param string $order_id .
	 */
	public function mwb_rma_refund_req_accept_email( $order_id ) {
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-refund-request-accept-email.php';
	}

	/**
	 * Send refund request cancel email to customer.
	 *
	 * @param string $order_id .
	 */
	public function mwb_rma_refund_req_cancel_email( $order_id ) {
		include_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'admin/partials/woo-refund-and-exchange-lite-refund-request-cancel-email.php';
	}

	/**
	 * Save policies setting.
	 */
	public function mwb_rma_save_policies_setting() {
		if ( isset( $_POST['save_policies_setting'] ) && isset( $_POST['get_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['get_nonce'] ) ), 'create_form_nonce' ) ) {
			unset( $_POST['save_policies_setting'] );
			unset( $_POST['get_nonce'] );
			$count1 = 0;
			$count2 = 0;
			$count3 = 0;
			// foreach ( $_POST as $key => $value ) {
			// 	foreach ( $value as $key => $value ) {
			// 		if ( 'refund' === $value['row_functionality'] && 'mwb_rma_maximum_days' === $value['row_policy'] ) {
			// 			$count1++;
			// 			if ( empty( $value['row_value'] ) ) {
			// 				unset( $_POST['mwb_rma_setting'][ $key ] );
			// 			}
			// 			if ( 2 <= $count1 ) {
			// 				unset( $_POST['mwb_rma_setting'][ $key ] );
			// 			}
			// 		}
			// 		if ( 'refund' === $value['row_functionality'] && 'mwb_rma_order_status' === $value['row_policy'] ) {
			// 			$count2++;
			// 			if ( 2 <= $count2 ) {
			// 				unset( $_POST['mwb_rma_setting'][ $key ] );
			// 			}
			// 			if ( empty( $value['row_statuses'] ) ) {
			// 				unset( $_POST['mwb_rma_setting'][ $key ] );
			// 			}
			// 		}
			// 		if ( 'refund' === $value['row_functionality'] && 'mwb_rma_tax_handling' === $value['row_policy'] ) {
			// 			$count3++;
			// 			if ( 2 <= $count3 ) {
			// 				unset( $_POST['mwb_rma_setting'][ $key ] );
			// 			}
			// 		}
			// 	}
			// }
			update_option( 'policies_setting_option', $_POST );
			$url = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			update_option( 'mwb_rma_save_policiies_setting', true );
			wp_safe_redirect( $url );
		}
	}

	/**
	 * Show notices after rma policies setting saved.
	 */
	public function show_notices() {
		global $wrael_mwb_rma_obj;
		if( get_option( 'mwb_rma_save_policiies_setting' ) ) {
			$mwb_rma_error_text = esc_html__( 'Settings saved !', 'woo-refund-and-exchange-lite' );
			$wrael_mwb_rma_obj->mwb_rma_plug_admin_notice( $mwb_rma_error_text, 'success' );
			update_option( 'mwb_rma_save_policiies_setting', false );

		}
	}

}
