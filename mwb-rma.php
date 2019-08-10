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
 * @package           Mwb_Rma
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce RMA | Return-Refund-Exchange
 * Plugin URI:        www.makewebbetter.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            makewebbetter
 * Author URI:        www.makewebbetter.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mwb-rma
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
define( 'MWB_RMA_VERSION', '1.0.0' );
define( 'MWB_RMA_DIR_PATH', plugin_dir_path( __FILE__ ) );
define('MWB_RMA_URL', plugin_dir_url( __FILE__ ));
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mwb-rma-activator.php
 */
function activate_mwb_rma() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mwb-rma-activator.php';
	Mwb_Rma_Activator::activate();
	
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mwb-rma-deactivator.php
 */
function deactivate_mwb_rma() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mwb-rma-deactivator.php';
	Mwb_Rma_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_mwb_rma' );
register_deactivation_hook( __FILE__, 'deactivate_mwb_rma' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-mwb-rma.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mwb_rma() {

	$plugin = new Mwb_Rma();
	$plugin->run();

}
run_mwb_rma();

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
	
	function mwb_rma_create_refund_request_form_page(){
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
			$mwb_rma_return_request_form_page_id = $page_id;
		}
		update_option('mwb_rma_return_request_form_page_id',$mwb_rma_return_request_form_page_id);
	} 
	register_activation_hook( __FILE__, 'mwb_rma_create_refund_request_form_page' );

	/**
	 * The code that runs during plugin deactivation to delete Refund Request Form Post.
	 * 
	 */
	function mwb_rma_delete_refund_request_form_page() {
		
		$page_id = get_option('mwb_rma_return_request_form_page_id');
		wp_delete_post($page_id);		
		delete_option('mwb_rma_return_request_form_page_id');
	}
	register_deactivation_hook( __FILE__, 'mwb_rma_delete_refund_request_form_page' );


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

}else{

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
			<p><?php _e( 'Woocommerce is not activated, Please activate Woocommerce first to install WooCommerce Refund and Exchange Lite.', 'mwb-rma' ); ?></p>
		</div>
   		<style>
   		#message{display:none;}
   		</style>
   	<?php 
 	} 
}
