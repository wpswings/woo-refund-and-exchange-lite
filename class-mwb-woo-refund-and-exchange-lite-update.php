<?php
/**
 * For the updation of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Mwb_Woo_Refund_And_Exchange_Lite_Update' ) ) {
	/**
	 * Class for the updation.
	 */
	class Mwb_Woo_Refund_And_Exchange_Lite_Update {


		/**
		 * Call when maka an object.
		 */
		public function __construct() {
			register_activation_hook( WOO_REFUND_AND_EXCHANGE_LITE_BASE_FILE, array( $this, 'mwb_check_activation' ) );
			add_action( 'mwb_woo_refund_and_exchange_lite_check_event', array( $this, 'mwb_check_update' ) );
			add_filter( 'http_request_args', array( $this, 'mwb_updates_exclude' ), 5, 2 );
			register_deactivation_hook( WOO_REFUND_AND_EXCHANGE_LITE_BASE_FILE, array( $this, 'mwb_check_deactivation' ) );

			$plugin_update = get_option( 'mwb_rma_plugin_update', 'false' );
			if ( 'true' === $plugin_update ) {

				// To add view details content in plugin update notice on plugins page.
				add_action( 'install_plugins_pre_plugin-information', array( $this, 'mwb_rma_details' ) );
				// To add plugin update notice after plugin update message.
				add_action( 'in_plugin_update_message-woo-refund-and-exchange-lite/woo-refund-and-exchange-lite.php', array( $this, 'mwb_rma_in_plugin_update_notice' ), 10, 2 );
			}

		}

		/**
		 * For the check the deactivation.
		 */
		public function mwb_check_deactivation() {
			wp_clear_scheduled_hook( 'mwb_woo_refund_and_exchange_lite_check_event' );
		}

		/**
		 * For the check the activation.
		 */
		public function mwb_check_activation() {
			wp_schedule_event( time(), 'daily', 'mwb_woo_refund_and_exchange_lite_check_event' );
		}

		/**
		 * To add view details content in plugin update notice on plugins page.
		 */
		public function mwb_rma_details() {
			global $tab;
			$plugin_name = isset( $_REQUEST['plugin'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) ) : sanitize_text_field( wp_unslash( $_REQUEST['plugin'] ) );
			// change $_REQUEST['plugin] to your plugin slug name.
			if ( 'plugin-information' === $tab && 'woo-refund-and-exchange-lite' === $plugin_name ) {

				$data = $this->get_plugin_update_data();

				if ( is_wp_error( $data ) || empty( $data ) ) {

					return;
				}

				if ( ! empty( $data['body'] ) ) {

					$all_data = json_decode( $data['body'], true );

					if ( ! empty( $all_data ) && is_array( $all_data ) ) {

						$this->create_html_data( $all_data );

						wp_die();
					}
				}
			}
		}

		/**
		 * Plugin Update related data.
		 */
		public function get_plugin_update_data() {

			// replace with your plugin url.
			$url      = 'https://makewebbetter.com/pluginupdates/woo-refund-and-exchange-lite/woo-refund-and-exchange-lite.json';
			$postdata = array(
				'action' => 'check_update',
			);

			$args = array(
				'method' => 'POST',
				'body'   => $postdata,
			);

			$data = wp_remote_post( $url, $args );

			return $data;
		}

		/**
		 * Render HTML content.
		 *
		 * @param array $all_data .
		 */
		public function create_html_data( $all_data ) {
			?>
			<style>
				#TB_window{
					top : 4% !important;
				}
				.mwb_rma_banner > img {
					width: 50%;
				}
				.mwb_rma_banner > h1 {
					margin-top: 0px;
				}
				.mwb_rma_banner {
					text-align: center;
				}
				.mwb_rma_description > h4 {
					background-color: #3779B5;
					padding: 5px;
					color: #ffffff;
					border-radius: 5px;
				}
				.mwb_rma_changelog_details > h4 {
					background-color: #3779B5;
					padding: 5px;
					color: #ffffff;
					border-radius: 5px;
				}
			</style>
			<div class="mwb_rma_details_wrapper">
				<div class="mwb_rma_banner">
					<h1><?php echo esc_html( $all_data['name'] . ' ' . $all_data['version'] ); ?></h1>
					<img src="<?php echo esc_html( $all_data['banners']['logo'] ); ?>"> 
				</div>

				<div class="mwb_rma_description">
					<h4><?php esc_html_e( 'Plugin Description', 'woo-refund-and-exchange-lite' ); ?></h4>
					<span><?php echo esc_html( $all_data['sections']['description'] ); ?></span>
				</div>
				<div class="mwb_rma_changelog_details">
					<h4><?php esc_html_e( 'Plugin Change Log', 'woo-refund-and-exchange-lite' ); ?></h4>
					<span><?php echo esc_html( $all_data['sections']['changelog'] ); ?></span>
				</div> 
			</div>
			<?php
		}

		/**
		 * Show update notice.
		 */
		public function mwb_rma_in_plugin_update_notice() {
			$data = $this->get_plugin_update_data();

			if ( is_wp_error( $data ) || empty( $data ) ) {

				return;
			}

			if ( isset( $data['body'] ) ) {

				$all_data = json_decode( $data['body'], true );

				if ( is_array( $all_data ) && ! empty( $all_data['sections']['update_notice'] ) ) {

					?>

					<style type="text/css">
						#woo-refund-and-exchange-lite-update .dummy {
							display: none;
						}

						#mwb_rma_in_plugin_update_div p:before {
							content: none;
						}

						#mwb_rma_in_plugin_update_div {
							border-top: 1px solid #ffb900;
							margin-left: -13px;
							padding-left: 20px;
							padding-top: 10px;
							padding-bottom: 5px;
						}

						#mwb_rma_in_plugin_update_div ul {
							list-style-type: decimal;
							padding-left: 20px;
						}

					</style>

					<?php

					echo '</p><div id="mwb_rma_in_plugin_update_div">' . esc_html( $all_data['sections']['update_notice'] ) . '</div><p class="dummy">';
				}
			}
		}

		/**
		 * Check the update.
		 */
		public function mwb_check_update() {
			global $wp_version;
			$update_check_wrael = 'https://makewebbetter.com/pluginupdates/woo-refund-and-exchange-lite/update.php';
			$plugin_folder      = plugin_basename( dirname( WOO_REFUND_AND_EXCHANGE_LITE_BASE_FILE ) );
			$plugin_file        = basename( ( WOO_REFUND_AND_EXCHANGE_LITE_BASE_FILE ) );
			if ( defined( 'WP_INSTALLING' ) ) {
				return false;
			}
			$postdata = array(
				'action'      => 'check_update',
				'license_key' => WOO_REFUND_AND_EXCHANGE_LITE_LICENSE_KEY,
			);

			$args = array(
				'method' => 'POST',
				'body'   => $postdata,
			);

			$response = wp_remote_post( $update_check_wrael, $args );

			if ( is_wp_error( $response ) || empty( $response['body'] ) ) {

				return;
			}

			list($version, $url) = explode( '~', $response['body'] );

			if ( $this->mwb_plugin_get( 'Version' ) >= $version ) {

				update_option( 'mwb_rma_plugin_update', 'false' );

				return false;
			}

			update_option( 'mwb_rma_plugin_update', 'true' );

			$plugin_transient = get_site_transient( 'update_plugins' );
			$a                = array(
				'slug'        => $plugin_folder,
				'new_version' => $version,
				'url'         => $this->mwb_plugin_get( 'AuthorURI' ),
				'package'     => $url,
			);
			$o                = (object) $a;
			$plugin_transient->response[ $plugin_folder . '/' . $plugin_file ] = $o;
			set_site_transient( 'update_plugins', $plugin_transient );
		}

		/**
		 * Update exclude.
		 *
		 * @param array  $r .
		 * @param string $url .
		 */
		public function mwb_updates_exclude( $r, $url ) {
			if ( 0 !== strpos( $url, 'http://api.wordpress.org/plugins/update-check' ) ) {
				return $r;
			}
			$plugins = unserialize( $r['body']['plugins'] );
			if ( ! empty( $plugins->plugins ) ) {
				unset( $plugins->plugins[ plugin_basename( __FILE__ ) ] );
			}
			if ( ! empty( $plugins->active ) ) {
				unset( $plugins->active[ array_search( plugin_basename( __FILE__ ), $plugins->active ) ] );
			}
			$r['body']['plugins'] = serialize( $plugins );
			return $r;
		}

		/**
		 * Returns current plugin info.
		 *
		 * @param string $i .
		 */
		public function mwb_plugin_get( $i ) {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			$plugin_folder = get_plugins( '/' . plugin_basename( dirname( WOO_REFUND_AND_EXCHANGE_LITE_BASE_FILE ) ) );
			$plugin_file   = basename( ( WOO_REFUND_AND_EXCHANGE_LITE_BASE_FILE ) );
			return $plugin_folder[ $plugin_file ][ $i ];
		}
	}
	new Mwb_Woo_Refund_And_Exchange_Lite_Update();
}
