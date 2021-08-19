<?php
/**
 * The file that defines the core plugin api class
 *
 * A class definition that includes api's endpoints and functions used across the plugin
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/package/rest-api/version1
 */

/**
 * The core plugin  api class.
 *
 * This is used to define internationalization, api-specific hooks, and
 * endpoints for plugin.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/package/rest-api/version1
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Woo_Refund_And_Exchange_Lite_Rest_Api {

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
	 * Define the core functionality of the plugin api.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the merthods, and set the hooks for the api and
	 *
	 * @since    1.0.0
	 * @param   string $plugin_name    Name of the plugin.
	 * @param   string $version        Version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}


	/**
	 * Define endpoints for the plugin.
	 *
	 * Uses the Woo_Refund_And_Exchange_Lite_Rest_Api class in order to create the endpoint
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function mwb_rma_add_endpoint() {
		register_rest_route(
			'wrael-route/v1',
			'/wrael-dummy-data/',
			array(
				// 'methods'  => 'POST',
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'mwb_rma_default_callback', '' ),
				'permission_callback' => array( $this, 'mwb_rma_default_permission_check' ),
			)
		);
		register_rest_route(
			'mwb-rma-refund-request-route/v1',
			'/mwb-rma-refund-request/',
			array(
				'methods'  => 'POST',
				// 'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'mwb_rma_default_callback', 'req' ),
				'permission_callback' => array( $this, 'mwb_rma_default_permission_check' ),
			)
		);
		register_rest_route(
			'mwb-rma-refund-request-accept-route/v1',
			'/mwb-rma-refund-request-accept/',
			array(
				'methods'  => 'POST',
				// 'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'mwb_rma_default_callback', 'req-accept' ),
				'permission_callback' => array( $this, 'mwb_rma_default_permission_check' ),
			)
		);
		register_rest_route(
			'mwb-rma-refund-request-cancel-route/v1',
			'/mwb-rma-refund-request-cancel/',
			array(
				'methods'  => 'POST',
				// 'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'mwb_rma_default_callback', 'req-cancel' ),
				'permission_callback' => array( $this, 'mwb_rma_default_permission_check' ),
			)
		);
	}


	/**
	 * Begins validation process of api endpoint.
	 *
	 * @param   Array $request    All information related with the api request containing in this array.
	 * @return  Array   $result   return rest response to server from where the endpoint hits.
	 * @since    1.0.0
	 */
	public function mwb_rma_default_permission_check( $request ) {

		// Add rest api validation for each request.
		$result = true;
		return $result;
	}


	/**
	 * Begins execution of api endpoint.
	 *
	 * @param    Array $request All information related with the api request containing in this array.
	 * @param    Array $type .
	 *  @return  Array $mwb_rma_response   return rest response to server from where the endpoint hits.
	 * @since    1.0.0
	 */
	public function mwb_rma_default_callback( $request, $type ) {
		require_once WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'package/rest-api/version1/class-woo-refund-and-exchange-lite-api-process.php';
		$mwb_rma_api_obj = new Woo_Refund_And_Exchange_Lite_Api_Process();
		if ( '' === $type ) {
			$mwb_rma_resultsdata = $mwb_rma_api_obj->mwb_rma_default_process( $request );
		} elseif ( 'req' === $type ) {
			$mwb_rma_resultsdata = $mwb_rma_api_obj->mwb_rma_refund_request_api( $request );
		} elseif ( 'req-accept' === $type ) {
			$mwb_rma_resultsdata = $mwb_rma_api_obj->mwb_rma_refund_request_accept_api( $request );
		} elseif ( 'req-cancel' === $type ) {
			$mwb_rma_resultsdata = $mwb_rma_api_obj->mwb_rma_refund_request_cancel_api( $request );
		}
		if ( is_array( $mwb_rma_resultsdata ) && isset( $mwb_rma_resultsdata['status'] ) && 200 == $mwb_rma_resultsdata['status'] ) {
			unset( $mwb_rma_resultsdata['status'] );
			$mwb_rma_response = new WP_REST_Response( $mwb_rma_resultsdata, 200 );
		} else {
			$mwb_rma_response = new WP_Error( $mwb_rma_resultsdata );
		}
		return $mwb_rma_response;
	}
}
