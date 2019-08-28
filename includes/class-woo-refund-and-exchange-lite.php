<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.makewebbetter.com
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/includes
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Woo_Refund_And_Exchange_Lite {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woo_Refund_And_Exchange_Lite_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOO_REFUND_AND_EXCHANGE_LITE_VERSION' ) ) {
			$this->version = WOO_REFUND_AND_EXCHANGE_LITE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo-refund-and-exchange-lite';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woo_Refund_And_Exchange_Lite_Loader. Orchestrates the hooks of the plugin.
	 * - Woo_Refund_And_Exchange_Lite_i18n. Defines internationalization functionality.
	 * - Woo_Refund_And_Exchange_Lite_Admin. Defines all hooks for the admin area.
	 * - Woo_Refund_And_Exchange_Lite_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-refund-and-exchange-lite-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo-refund-and-exchange-lite-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo-refund-and-exchange-lite-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo-refund-and-exchange-lite-public.php';

		$this->loader = new Woo_Refund_And_Exchange_Lite_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woo_Refund_And_Exchange_Lite_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Woo_Refund_And_Exchange_Lite_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Woo_Refund_And_Exchange_Lite_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'mwb_add_admin_menu' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'mwb_rma_add_order_edit_meta_box' );
		$this->loader->add_action( 'init', $plugin_admin, 'mwb_rma_register_custom_order_status' );
		$this->loader->add_filter( 'wc_order_statuses', $plugin_admin, 'mwb_rma_add_custom_order_status');
		$this->loader->add_action( 'wp_ajax_mwb_rma_return_req_approve' , $plugin_admin , 'mwb_rma_return_req_approve_callback');
		$this->loader->add_action( 'wp_ajax_mwb_rma_return_req_cancel' , $plugin_admin , 'mwb_rma_return_req_cancel_callback');
		$this->loader->add_action( 'woocommerce_refund_created', $plugin_admin, 'mwb_rma_action_woocommerce_order_refunded', 10, 2 );
		$this->loader->add_action('wp_ajax_mwb_rma_manage_stock' , $plugin_admin , 'mwb_rma_manage_stock' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Woo_Refund_And_Exchange_Lite_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'template_include',  $plugin_public, 'mwb_rma_product_return_template');
		$this->loader->add_filter( 'woocommerce_my_account_my_orders_actions',$plugin_public, 'mwb_rma_refund_exchange_button',10, 2 );
		$this->loader->add_action( 'woocommerce_order_details_after_order_table',$plugin_public, 'mwb_rma_typ_order_return_button');
		$this->loader->add_action( 'wp_ajax_mwb_rma_return_upload_files',$plugin_public, 'mwb_rma_order_return_attach_files');
		$this->loader->add_action( 'wp_ajax_nopriv_mwb_rma_return_upload_files',$plugin_public, 'mwb_rma_order_return_attach_files');
		$this->loader->add_action( 'wp_ajax_mwb_rma_return_product_info',$plugin_public, 'mwb_rma_return_product_info_callback');
		$this->loader->add_action( 'wp_ajax_nopriv_mwb_rma_return_product_info',$plugin_public, 'mwb_rma_return_product_info_callback');
		$this->loader->add_action( 'wp_ajax_mwb_rma_get_product_price', $plugin_public, 'mwb_rma_get_product_price' );
		$this->loader->add_action( 'wp_ajax_nopriv_mwb_rma_get_product_price', $plugin_public, 'mwb_rma_get_product_price' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woo_Refund_And_Exchange_Lite_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
