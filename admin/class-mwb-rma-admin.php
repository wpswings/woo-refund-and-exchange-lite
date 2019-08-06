<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Rma
 * @subpackage Mwb_Rma/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mwb_Rma
 * @subpackage Mwb_Rma/admin
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Mwb_Rma_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mwb_Rma_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mwb_Rma_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mwb-rma-admin.css', array(), $this->version, 'all' );
		wp_register_style( 'woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION );
		wp_enqueue_style( 'woocommerce_admin_menu_styles' );
		wp_enqueue_style( 'woocommerce_admin_styles' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mwb_Rma_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mwb_Rma_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		

		wp_register_script( 'woocommerce_admin', WC()->plugin_url() . '/assets/js/admin/woocommerce_admin.js', array( 'jquery', 'jquery-blockui', 'jquery-ui-sortable', 'jquery-ui-widget', 'jquery-ui-core', 'jquery-tiptip' ), WC_VERSION );
		$locale = localeconv();
		$decimal = isset( $locale['decimal_point'] ) ? $locale['decimal_point'] : '.';

		$params = array(
		/* translators: %s: decimal */
		'i18n_decimal_error' => sprintf( __( 'Please enter in decimal (%s) format without thousand separators.', 'woocommerce' ), $decimal ),
		/* translators: %s: price decimal separator */
		'i18n_mon_decimal_error' => sprintf( __( 'Please enter in monetary decimal (%s) format without thousand separators and currency symbols.', 'woocommerce' ), wc_get_price_decimal_separator() ),
		'i18n_country_iso_error' => __( 'Please enter in country code with two capital letters.', 'woocommerce' ),
		'i18_sale_less_than_regular_error' => __( 'Please enter in a value less than the regular price.', 'woocommerce' ),
		'decimal_point' => $decimal,
		'mon_decimal_point' => wc_get_price_decimal_separator(),
		'strings' => array(
		'import_products' => __( 'Import', 'woocommerce' ),
		'export_products' => __( 'Export', 'woocommerce' ),
		),
		'urls' => array(
		'import_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_importer' ) ),
		'export_products' => esc_url_raw( admin_url( 'edit.php?post_type=product&page=product_exporter' ) ),
		),
		);

		wp_localize_script( 'woocommerce_admin', 'woocommerce_admin', $params );
		wp_enqueue_script( 'woocommerce_admin' );
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mwb-rma-admin.js', array( 'jquery' ), $this->version, false );
		$translation_array = array(
			'remove'	=>	__( 'Remove' , 'mwb-rma'),
		);
		wp_localize_script(  $this->plugin_name, 'global_mwb_rma', $translation_array );
	}

	/**
	 * Add new admin menu under woocommerce
	 * @param $array 
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public static function mwb_add_admin_menu( $array ) {
		add_submenu_page( 'woocommerce',
			__( 'RMA Setting' , 'mwb-rma'), 
			__( 'RMA Setting' , 'mwb-rma'),
			'manage_options',
			__( 'mwb-rma-setting','mwb-rma'),
			array( $this , 'mwb_rma_setting_page_callback')
		);

	}

	/**
	 * Include RMA setting page
	 * @param $array 
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function mwb_rma_setting_page_callback(){
		include_once MWB_RMA_DIR_PATH.'admin/partials/mwb-rma-admin-display.php';
	}

	/**
	 * Add new return meta box on order edit page
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function mwb_rma_add_order_edit_meta_box(){
		$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings', array() );
		if(isset($mwb_rma_refund_settings) && !empty($mwb_rma_refund_settings) && is_array($mwb_rma_refund_settings)){
			$mwb_rma_return_enable = $mwb_rma_refund_settings['mwb_rma_return_enable'];
			$mwb_rma_max_return_days =  $mwb_rma_refund_settings['mwb_rma_return_days'];
			$mwb_rma_return_status =  $mwb_rma_refund_settings['mwb_rma_return_order_status'];
			//$order = wc_get_order( $order_id );
			// $order_status ="wc-".$order->get_status();
			if(isset($mwb_rma_return_enable) && $mwb_rma_return_enable == 'on'){
				if(isset($mwb_rma_max_return_days) && $mwb_rma_max_return_days != 0 ){
					//if(isset($mwb_rma_return_status) && is_array($mwb_rma_return_status) && in_array($order_status, $mwb_rma_return_status) ){

						add_meta_box('mwb_rma_order_refund', 
							__('Refund Requested Products','mwb-rma'),
							array($this, 'mwb_rma_order_return'),
							'shop_order');
					//}
				}
			}
		}
	}

	/**
	 * This function is metabox template for Refund order product
	 * 
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 * @param unknown $order
	*/
	public function mwb_rma_order_return()
	{
		global $post, $thepostid, $theorder;
		include_once MWB_RMA_DIR_PATH.'admin/partials/templates/mwb-rma-return-product-meta.php';
	}

	/**
	 * This function is to add custom order status for return 
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	public function mwb_rma_register_custom_order_status()
	{
		register_post_status( 'wc-refund-requested', array(
			'label'                     => 'Refund Requested',
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Refund Requested <span class="count">(%s)</span>', 'Refund Requested <span class="count">(%s)</span>' )
			) );

		register_post_status( 'wc-refund-approved', array(
			'label'                     => 'Refund Approved',
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Refund Approved <span class="count">(%s)</span>', 'Refund Approved <span class="count">(%s)</span>' )
			) );

		register_post_status( 'wc-refund-cancelled', array(
			'label'                     => 'Refund Cancelled',
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Refund Cancelled <span class="count">(%s)</span>', 'Refund Cancelled <span class="count">(%s)</span>' )
			) );
	}

	/**
	 * This function is to register custom order status
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 * @param unknown $mwb_rma_order_statuses
	 * @return multitype:string unknown
	 */

	public function mwb_rma_add_custom_order_status($mwb_rma_order_statuses)
	{
		$mwb_rma_new_order_statuses = array();
		foreach ( $mwb_rma_order_statuses as $mwb_rma_key => $mwb_rma_status ) {

			$mwb_rma_new_order_statuses[ $mwb_rma_key ] = $mwb_rma_status;

			if ( 'wc-completed' === $mwb_rma_key ) {
				$mwb_rma_new_order_statuses['wc-refund-requested'] = __('Refund Requested','mwb-rma');
				$mwb_rma_new_order_statuses['wc-refund-approved']  = __('Refund Approved','mwb-rma');
				$mwb_rma_new_order_statuses['wc-refund-cancelled'] = __('Refund Cancelled','mwb-rma');
			}
		}
		return $mwb_rma_new_order_statuses;	
	}

}
