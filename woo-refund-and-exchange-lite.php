<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.makewebbetter.com
 * @since             1.0.0
 * @package           Woo_Refund_And_Exchange_Lite
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce RMA Return Refund Exchange Lite
 * Plugin URI:        www.makewebbetter.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           2.0.0
 * Author:            makewebbetter
 * Author URI:        www.makewebbetter.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-refund-and-exchange-lite
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOO_REFUND_AND_EXCHANGE_LITE_VERSION', '2.0.0' );
define( 'MWB_RMA_DIR_PATH', plugin_dir_path( __FILE__ ) );
define('MWB_RMA_URL', plugin_dir_url( __FILE__ ));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woo-refund-and-exchange-lite-activator.php
 */
function activate_woo_refund_and_exchange_lite() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite-activator.php';
	Woo_Refund_And_Exchange_Lite_Activator::activate();
	mwb_rma_create_pages();
	
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woo-refund-and-exchange-lite-deactivator.php
 */
function deactivate_woo_refund_and_exchange_lite() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite-deactivator.php';
	Woo_Refund_And_Exchange_Lite_Deactivator::deactivate();
	mwb_rma_delete_pages();
}



/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite.php';

$activated = true;
if (function_exists('is_multisite') && is_multisite())
{
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
	{
		$activated = false;
	}

}else{

	if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))))
	{
		$activated = false;
	}

}


if($activated){

	/**
	 * The code that runs during plugin activation to create Refund Request Form Post.
	 * 
	 */
	
	function mwb_rma_create_pages(){
		$email = get_option('admin_email');
		$admin = get_user_by('email', $email);
		$admin_id = $admin->ID;
		$mwb_rma_return_request_form_page_id = 0;
		$mwb_rma_return_request_form = array(
				'post_author'    => $admin_id,
				'post_name'      => 'refund-request-form',
				'post_title'     => 'Refund Request Form',
				'post_type'      => 'page',
				'post_status'    => 'publish',
					
		);

		$page_id = wp_insert_post($mwb_rma_return_request_form);
			
		if($page_id) {
			$mwb_rma_pages['pages']['mwb_rma_return_form']=$page_id;
		}
		update_option('mwb_rma_pages',$mwb_rma_pages);
	} 

	/**
	 * The code that runs during plugin deactivation to delete Refund Request Form Post.
	 * 
	 */
	function mwb_rma_delete_pages() {
		
		$mwb_rma_pages = get_option('mwb_rma_pages');
		$page_id = $mwb_rma_pages['pages']['mwb_rma_return_form'];
		wp_delete_post($page_id);	
		delete_option('mwb_rma_pages');
	}

	/**
	 * Add settings link on plugin page
	 * @name mwb_rma_admin_settings()
	  *@param $actions, $plugin_file
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	
	function mwb_rma_admin_settings($actions, $plugin_file) {
		static $plugin;
		if (! isset ( $plugin )) {
	
			$plugin = plugin_basename ( __FILE__ );
		}
		if ($plugin == $plugin_file) {
			$settings = array (
					'settings' => '<a href="' . admin_url ( 'admin.php?page=mwb-rma-setting' ) . '">' . __ ( 'Settings', 'woo-refund-and-exchange-lite' ) . '</a>',
			);
			$actions = array_merge ( $settings, $actions );
		}
		return $actions;
	}

	//add link for settings
	add_filter ( 'plugin_action_links','mwb_rma_admin_settings', 10, 5 );

	/**
	 * Add doc and pro link on plugin page
	 * @name mwb_rma_add_doc_and_premium_link()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	function mwb_rma_add_doc_and_premium_link( $links, $file ) {

		if ( strpos( $file, 'woo-refund-and-exchange-lite.php' ) !== false ) {

			$row_meta = array(
				'docs'    => '<a target="_blank" style="color:#FFF;background:linear-gradient(to right,#7a28ff 0,#00a1ff 100%);padding:5px;border-radius:6px;" href="https://docs.makewebbetter.com/woocommerce-refund-and-exchange-lite">'.esc_html__("Go to Docs", 'woo-refund-and-exchange-lite' ).'</a>',
				'goPro' => '<a target="_blank" style="color:#FFF;background:linear-gradient(to right,#7a28ff 0,#00a1ff 100%);padding:5px;border-radius:6px;" href="https://makewebbetter.com/product/woocommerce-rma-return-refund-exchange/"><strong>'.esc_html__("Go Premium", 'woo-refund-and-exchange-lite' ).'</strong></a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	add_filter( 'plugin_row_meta', 'mwb_rma_add_doc_and_premium_link', 10, 2 );

	/**
	 * This function is used for formatting the price seprator
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 * @param unknown $price
	 * @return price
	 */
	function mwb_rma_currency_seprator($price)
	{
		$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() ), $price, wc_get_price_decimals(), wc_get_price_decimal_separator(), wc_get_price_thousand_separator() );

		return $price;
	}

	/**
	 * Add capabilities, priority must be after the initial role 
	 * @name admin_settings_for_pmr()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function mwb_rma_role_capability()
	{
	    $mwb_rma_customer_role = get_role('customer');
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-request', true);

	    $mwb_rma_customer_role = get_role('administrator');
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-request', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-approve', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-cancel', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-manage-stock', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-amount', true);

	    $mwb_rma_customer_role = get_role('editor');
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-request', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-approve', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-cancel', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-manage-stock', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-amount', true);

	    $mwb_rma_customer_role = get_role('shop_manager');
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-request', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-approve', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-cancel', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-manage-stock', true);
	    $mwb_rma_customer_role->add_cap('mwb-rma-refund-amount', true);
	    
	}

	// add capabilities, priority must be after the initial role   
	add_action('init', 'mwb_rma_role_capability', 11);

	/**
 	 * load plugin textdomain
 	 * @name mwb_rma_load_plugin_textdomain()
 	 * @author makewebbetter<webmaster@makewebbetter.com>
 	 * @link http://www.makewebbetter.com/
 	 */

	function mwb_rma_load_plugin_textdomain()
	{
		$domain = "woo-refund-and-exchange-lite";
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
		load_textdomain( $domain, MWB_RMA_DIR_PATH .'languages/'.$domain.'-' . $locale . '.mo' );
		$var=load_plugin_textdomain( $domain, false, plugin_basename( dirname(__FILE__) ) . '/languages' );
	}
	add_action('plugins_loaded', 'mwb_rma_load_plugin_textdomain');

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_woo_refund_and_exchange_lite() {

		$plugin = new Woo_Refund_And_Exchange_Lite();
		$plugin->run();

	}
	run_woo_refund_and_exchange_lite();
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo-refund-and-exchange-lite-common-functions.php';

	register_activation_hook( __FILE__, 'activate_woo_refund_and_exchange_lite' );
	register_deactivation_hook( __FILE__, 'deactivate_woo_refund_and_exchange_lite' );

}else{

	// to deactivate plugin if woocommerce is not installed
	add_action( 'admin_init', 'mwb_rma_plugin_deactivate' ); 

	/**
 	 * Call Admin notices
 	 * @name mwb_rma_plugin_deactivate()
 	 * @author makewebbetter<webmaster@makewebbetter.com>
 	 * @link http://www.makewebbetter.com/
 	 */

	function mwb_rma_plugin_deactivate()
	{
	   	deactivate_plugins( plugin_basename( __FILE__ ) );
	   	do_action( 'woocommerce_product_options_stock_fields' );
		add_action( 'admin_notices', 'mwb_rma_plugin_error_notice' );
		
	}

 	/**
	 * Show warning message if woocommerce is not install
	 * @name mwb_rma_plugin_error_notice()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	function mwb_rma_plugin_error_notice()
 	{ 
 		
		?>
		 <div class="error notice is-dismissible">
			<p><?php _e( 'Woocommerce is not activated, Please activate Woocommerce first to install WooCommerce Refund and Exchange Lite.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
   		<style>
   		#message{display:none;}
   		</style>
   	<?php 
 	} 

}

