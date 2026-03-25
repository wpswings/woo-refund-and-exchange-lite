# Codex Change Handoff

Captured on: 2026-03-25
Repository: /home/cedcoss/Local Sites/plugin-notice-app/app/public/wp-content/plugins/woo-refund-and-exchange-lite
Branch: master

## Changed Files

 M admin/class-woo-refund-and-exchange-lite-admin.php
 M admin/partials/woo-refund-and-exchange-lite-admin-dashboard.php
 M admin/partials/woo-refund-and-exchange-lite-overview.php
 M multistep-form/build/index.js
 M public/partials/wps-rma-refund-request-form.php
 M public/partials/wps-rma-view-order-msg.php
?? admin/css/wps-rma-redesign.css
?? public/css/wps-rma-public-redesign.css

## Unified Diff

```diff
diff --git a/admin/class-woo-refund-and-exchange-lite-admin.php b/admin/class-woo-refund-and-exchange-lite-admin.php
index 7a9652b..81361f1 100755
--- a/admin/class-woo-refund-and-exchange-lite-admin.php
+++ b/admin/class-woo-refund-and-exchange-lite-admin.php
@@ -63,6 +63,117 @@ class Woo_Refund_And_Exchange_Lite_Admin {
 		$this->version     = $version;
 	}
 
+	/**
+	 * Get the current admin screen id.
+	 *
+	 * @return string
+	 */
+	private function wrael_get_screen_id() {
+		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
+
+		return ( ! empty( $screen ) && isset( $screen->id ) ) ? $screen->id : '';
+	}
+
+	/**
+	 * Determine whether the current screen is the plugin settings screen.
+	 *
+	 * @param string $screen_id Current screen id.
+	 * @return bool
+	 */
+	private function wrael_is_settings_screen( $screen_id ) {
+		return in_array(
+			$screen_id,
+			array(
+				'wp-swings_page_woo_refund_and_exchange_lite_menu',
+				'wpswings_page_woo_refund_and_exchange_lite_menu',
+			),
+			true
+		);
+	}
+
+	/**
+	 * Determine whether the current screen is one of the plugin admin screens.
+	 *
+	 * @param string $screen_id Current screen id.
+	 * @return bool
+	 */
+	private function wrael_is_plugin_screen( $screen_id ) {
+		return in_array(
+			$screen_id,
+			array(
+				'wp-swings_page_woo_refund_and_exchange_lite_menu',
+				'wpswings_page_woo_refund_and_exchange_lite_menu',
+				'wp-swings_page_home',
+				'wpswings_page_home',
+			),
+			true
+		);
+	}
+
+	/**
+	 * Determine whether the current screen is an order screen.
+	 *
+	 * @param string $screen_id Current screen id.
+	 * @return bool
+	 */
+	private function wrael_is_order_screen( $screen_id ) {
+		return in_array(
+			$screen_id,
+			array(
+				'shop_order',
+				'woocommerce_page_wc-orders',
+			),
+			true
+		);
+	}
+
+	/**
+	 * Determine whether banner assets should load on the current screen.
+	 *
+	 * @param string $screen_id Current screen id.
+	 * @return bool
+	 */
+	private function wrael_is_banner_screen( $screen_id ) {
+		return in_array(
+			$screen_id,
+			array(
+				'plugins',
+				'wp-swings_page_woo_refund_and_exchange_lite_menu',
+				'wpswings_page_woo_refund_and_exchange_lite_menu',
+				'wp-swings_page_home',
+				'wpswings_page_home',
+			),
+			true
+		);
+	}
+
+	/**
+	 * Determine whether multistep assets should load on the current screen.
+	 *
+	 * @param string $screen_id Current screen id.
+	 * @return bool
+	 */
+	private function wrael_is_multistep_screen( $screen_id ) {
+		return $this->wrael_is_settings_screen( $screen_id ) && ! wps_rma_standard_check_multistep() && wps_rma_pro_active();
+	}
+
+	/**
+	 * Resolve a stable asset version based on file modification time.
+	 *
+	 * @param string $relative_path Asset path relative to the lite plugin root.
+	 * @return string
+	 */
+	private function wrael_asset_version( $relative_path ) {
+		static $version_cache = array();
+
+		$asset_path = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . ltrim( $relative_path, '/' );
+		if ( ! isset( $version_cache[ $asset_path ] ) ) {
+			$version_cache[ $asset_path ] = file_exists( $asset_path ) ? (string) filemtime( $asset_path ) : (string) $this->version;
+		}
+
+		return $version_cache[ $asset_path ];
+	}
+
 	/**
 	 * Register the stylesheets for the admin area.
 	 *
@@ -70,39 +181,48 @@ class Woo_Refund_And_Exchange_Lite_Admin {
 	 * @param string $hook The plugin page slug.
 	 */
 	public function wrael_admin_enqueue_styles( $hook ) {
-		$screen = get_current_screen();
-		// multistep form css.
-		if ( ! wps_rma_standard_check_multistep() && wps_rma_pro_active() ) {
-			$style_url        = WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'multistep-form/build/style-index.css';
+		$screen_id = $this->wrael_get_screen_id();
+
+		if ( $this->wrael_is_multistep_screen( $screen_id ) ) {
 			wp_enqueue_style(
 				'wps-admin-react-styles',
-				$style_url,
+				WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'multistep-form/build/style-index.css',
 				array(),
-				time(),
-				false
+				$this->wrael_asset_version( 'multistep-form/build/style-index.css' ),
+				'all'
+			);
+			wp_enqueue_style(
+				'wps-rma-admin-redesign',
+				WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/wps-rma-redesign.css',
+				array( 'wps-admin-react-styles' ),
+				$this->wrael_asset_version( 'admin/css/wps-rma-redesign.css' ),
+				'all'
 			);
 			return;
 		}
-		if ( ! empty( $screen ) && isset( $screen->id ) && ( 'wp-swings_page_woo_refund_and_exchange_lite_menu' === $screen->id || 'wp-swings_page_home' === $screen->id ) ) {
-
-			wp_enqueue_style( 'wps-wrael-select2-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/select-2/woo-refund-and-exchange-lite-select2.css', array(), time(), 'all' );
 
-			wp_enqueue_style( 'wps-wrael-meterial-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-web.min.css', array(), time(), 'all' );
-			wp_enqueue_style( 'wps-wrael-meterial-css2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.css', array(), time(), 'all' );
-			wp_enqueue_style( 'wps-wrael-meterial-lite', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-lite.min.css', array(), time(), 'all' );
-
-			wp_enqueue_style( 'wps-wrael-meterial-icons-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/icon.css', array(), time(), 'all' );
+		if ( $this->wrael_is_plugin_screen( $screen_id ) ) {
+			wp_enqueue_style( 'wps-wrael-select2-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/select-2/woo-refund-and-exchange-lite-select2.css', array(), $this->wrael_asset_version( 'package/lib/select-2/woo-refund-and-exchange-lite-select2.css' ), 'all' );
+			wp_enqueue_style( 'wps-wrael-meterial-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-web.min.css', array(), $this->wrael_asset_version( 'package/lib/material-design/material-components-web.min.css' ), 'all' );
+			wp_enqueue_style( 'wps-wrael-meterial-css2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.css', array(), $this->wrael_asset_version( 'package/lib/material-design/material-components-v5.0-web.min.css' ), 'all' );
+			wp_enqueue_style( 'wps-wrael-meterial-lite', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-lite.min.css', array(), $this->wrael_asset_version( 'package/lib/material-design/material-lite.min.css' ), 'all' );
+			wp_enqueue_style( 'wps-wrael-meterial-icons-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/icon.css', array(), $this->wrael_asset_version( 'package/lib/material-design/icon.css' ), 'all' );
+			wp_enqueue_style( 'wps-admin-min-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/woo-refund-and-exchange-lite-admin.min.css', array(), $this->wrael_asset_version( 'admin/css/woo-refund-and-exchange-lite-admin.min.css' ), 'all' );
+			wp_enqueue_style( 'wps-datatable-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables/media/css/jquery.dataTables.min.css', array(), $this->wrael_asset_version( 'package/lib/datatables/media/css/jquery.dataTables.min.css' ), 'all' );
+			wp_enqueue_style( 'wps-rma-admin-redesign', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/wps-rma-redesign.css', array( 'wps-admin-min-css' ), $this->wrael_asset_version( 'admin/css/wps-rma-redesign.css' ), 'all' );
+		}
 
-			wp_enqueue_style( 'wps-admin-min-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/woo-refund-and-exchange-lite-admin.min.css', array(), $this->version, 'all' );
-			wp_enqueue_style( 'wps-datatable-css', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables/media/css/jquery.dataTables.min.css', array(), $this->version, 'all' );
+		if ( $this->wrael_is_order_screen( $screen_id ) ) {
+			wp_enqueue_style( $this->plugin_name, WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/wps-order-edit-page-lite.scss.min.css', array(), $this->wrael_asset_version( 'admin/css/wps-order-edit-page-lite.scss.min.css' ), 'all' );
 		}
-		if ( ! empty( $screen ) && isset( $screen->id ) && ( 'shop_order' === $screen->id || 'woocommerce_page_wc-orders' === $screen->id ) ) {
-			wp_enqueue_style( $this->plugin_name, WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/wps-order-edit-page-lite.scss.min.css', array(), $this->version, 'all' );
+
+		if ( $this->wrael_is_settings_screen( $screen_id ) ) {
+			wp_enqueue_style( 'wps-rma-style-jqueru-ui', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/jquery-ui.css', array(), $this->wrael_asset_version( 'admin/css/jquery-ui.css' ), 'all' );
 		}
-		if ( ! empty( $screen ) && isset( $screen->id ) && 'wp-swings_page_woo_refund_and_exchange_lite_menu' === $screen->id ) {
-			wp_enqueue_style( 'wps-rma-style-jqueru-ui', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/jquery-ui.css', array(), $this->version, 'all' );
+
+		if ( $this->wrael_is_banner_screen( $screen_id ) ) {
+			wp_enqueue_style( 'wps-rma-promotional-banner', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/woo-refund-and-exchange-lite-banner.css', array(), $this->wrael_asset_version( 'admin/css/woo-refund-and-exchange-lite-banner.css' ), 'all' );
 		}
-		wp_enqueue_style( 'wps-rma-promotional-banner', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/css/woo-refund-and-exchange-lite-banner.css', array(), $this->version, 'all' );
 	}
 
 	/**
@@ -112,54 +232,52 @@ class Woo_Refund_And_Exchange_Lite_Admin {
 	 * @param string $hook The plugin page slug.
 	 */
 	public function wrael_admin_enqueue_scripts( $hook ) {
-		$screen     = get_current_screen();
+		$screen_id  = $this->wrael_get_screen_id();
 		$pro_active = wps_rma_pro_active();
-		if ( ! empty( $screen ) && isset( $screen->id ) && 'wp-swings_page_woo_refund_and_exchange_lite_menu' === $screen->id ) {
-			if ( ! wps_rma_standard_check_multistep() && wps_rma_pro_active() ) {
-				// js for the multistep from.
-				$script_path       = '../../multistep-form/build/index.js';
-				$script_asset_path = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'multistep-form/build/index-asset.php';
-				$script_asset      = file_exists( $script_asset_path )
-					? require $script_asset_path
-					: array(
-						'dependencies' => array(
-							'wp-hooks',
-							'wp-element',
-							'wp-i18n',
-							'wc-components',
-						),
-						'version'      => filemtime( $script_path ),
-					);
-				$script_url = WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'multistep-form/build/index.js';
-				wp_register_script(
-					'react-app-block',
-					$script_url,
-					$script_asset['dependencies'],
-					$script_asset['version'],
-					true
-				);
-				wp_enqueue_script( 'react-app-block' );
-				wp_localize_script(
-					'react-app-block',
-					'frontend_ajax_object',
-					array(
-						'ajaxurl'            => admin_url( 'admin-ajax.php' ),
-						'wps_standard_nonce' => wp_create_nonce( 'ajax-nonce' ),
-						'redirect_url'       => admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ),
-					)
+
+		if ( $this->wrael_is_multistep_screen( $screen_id ) ) {
+			$script_asset_path = WOO_REFUND_AND_EXCHANGE_LITE_DIR_PATH . 'multistep-form/build/index-asset.php';
+			$script_asset      = file_exists( $script_asset_path )
+				? require $script_asset_path
+				: array(
+					'dependencies' => array(
+						'wp-hooks',
+						'wp-element',
+						'wp-i18n',
+						'wc-components',
+					),
+					'version'      => $this->wrael_asset_version( 'multistep-form/build/index.js' ),
 				);
-				return;
-			}
+
+			wp_register_script(
+				'react-app-block',
+				WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'multistep-form/build/index.js',
+				$script_asset['dependencies'],
+				$script_asset['version'],
+				true
+			);
+			wp_localize_script(
+				'react-app-block',
+				'frontend_ajax_object',
+				array(
+					'ajaxurl'            => admin_url( 'admin-ajax.php' ),
+					'wps_standard_nonce' => wp_create_nonce( 'ajax-nonce' ),
+					'redirect_url'       => admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ),
+				)
+			);
+			wp_enqueue_script( 'react-app-block' );
+			return;
 		}
-		if ( ! empty( $screen ) && isset( $screen->id ) && ( 'wp-swings_page_woo_refund_and_exchange_lite_menu' === $screen->id || 'shop_order' === $screen->id || 'plugins' === $screen->id || 'wp-swings_page_home' === $screen->id || 'woocommerce_page_wc-orders' === $screen->id ) ) {
-			wp_enqueue_script( 'wps-wrael-select2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/select-2/woo-refund-and-exchange-lite-select2.js', array( 'jquery' ), time(), false );
-			wp_enqueue_script( 'wps-wrael-metarial-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-web.min.js', array(), time(), false );
-			wp_enqueue_script( 'wps-wrael-metarial-js2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.js', array(), time(), false );
-			wp_enqueue_script( 'wps-wrael-metarial-lite', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-lite.min.js', array(), time(), false );
-			wp_enqueue_script( 'wps-wrael-datatable', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/js/jquery.dataTables.min.js', array(), time(), false );
-			wp_enqueue_script( 'wps-wrael-datatable-btn', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/buttons/dataTables.buttons.min.js', array(), time(), false );
-			wp_enqueue_script( 'wps-wrael-datatable-btn-2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/buttons/buttons.html5.min.js', array(), time(), false );
-			wp_register_script( $this->plugin_name . 'admin-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/woo-refund-and-exchange-lite-admin.min.js', array( 'jquery', 'wps-wrael-select2', 'wps-wrael-metarial-js', 'wps-wrael-metarial-js2', 'wps-wrael-metarial-lite' ), $this->version, false );
+
+		if ( in_array( $screen_id, array( 'wp-swings_page_woo_refund_and_exchange_lite_menu', 'wpswings_page_woo_refund_and_exchange_lite_menu', 'shop_order', 'plugins', 'wp-swings_page_home', 'wpswings_page_home', 'woocommerce_page_wc-orders' ), true ) ) {
+			wp_enqueue_script( 'wps-wrael-select2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/select-2/woo-refund-and-exchange-lite-select2.js', array( 'jquery' ), $this->wrael_asset_version( 'package/lib/select-2/woo-refund-and-exchange-lite-select2.js' ), false );
+			wp_enqueue_script( 'wps-wrael-metarial-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-web.min.js', array(), $this->wrael_asset_version( 'package/lib/material-design/material-components-web.min.js' ), false );
+			wp_enqueue_script( 'wps-wrael-metarial-js2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-components-v5.0-web.min.js', array(), $this->wrael_asset_version( 'package/lib/material-design/material-components-v5.0-web.min.js' ), false );
+			wp_enqueue_script( 'wps-wrael-metarial-lite', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/material-design/material-lite.min.js', array(), $this->wrael_asset_version( 'package/lib/material-design/material-lite.min.js' ), false );
+			wp_enqueue_script( 'wps-wrael-datatable', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/js/jquery.dataTables.min.js', array(), $this->wrael_asset_version( 'package/lib/datatables.net/js/jquery.dataTables.min.js' ), false );
+			wp_enqueue_script( 'wps-wrael-datatable-btn', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/buttons/dataTables.buttons.min.js', array(), $this->wrael_asset_version( 'package/lib/datatables.net/buttons/dataTables.buttons.min.js' ), false );
+			wp_enqueue_script( 'wps-wrael-datatable-btn-2', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'package/lib/datatables.net/buttons/buttons.html5.min.js', array(), $this->wrael_asset_version( 'package/lib/datatables.net/buttons/buttons.html5.min.js' ), false );
+			wp_register_script( $this->plugin_name . 'admin-js', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/woo-refund-and-exchange-lite-admin.min.js', array( 'jquery', 'wps-wrael-select2', 'wps-wrael-metarial-js', 'wps-wrael-metarial-js2', 'wps-wrael-metarial-lite' ), $this->wrael_asset_version( 'admin/js/woo-refund-and-exchange-lite-admin.min.js' ), false );
 			wp_localize_script(
 				$this->plugin_name . 'admin-js',
 				'wrael_admin_param',
@@ -174,18 +292,22 @@ class Woo_Refund_And_Exchange_Lite_Admin {
 			);
 			wp_enqueue_script( $this->plugin_name . 'admin-js' );
 		}
-		wp_enqueue_script( 'wps-rma-promotional-banner', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/woo-refund-and-exchange-lite-banner.js', array( 'jquery' ), time(), false );
-		wp_register_script( 'wps-rma-promotional-banner', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/woo-refund-and-exchange-lite-banner.js', array( 'jquery' ), $this->version, false );
-		wp_localize_script(
-			'wps-rma-promotional-banner',
-			'wrael_banner_param',
-			array(
-				'ajaxurl'       => admin_url( 'admin-ajax.php' ),
-				'wps_rma_nonce' => wp_create_nonce( 'wps_rma_ajax_seurity' ),
-			)
-		);
-		if ( ! empty( $screen ) && isset( $screen->id ) && 'wp-swings_page_woo_refund_and_exchange_lite_menu' === $screen->id ) {
-			wp_enqueue_script( 'wps-rma-script-timepicker', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/jquery.ui.timepicker.js', array( 'jquery' ), $this->version, true );
+
+		if ( $this->wrael_is_banner_screen( $screen_id ) ) {
+			wp_register_script( 'wps-rma-promotional-banner', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/woo-refund-and-exchange-lite-banner.js', array( 'jquery' ), $this->wrael_asset_version( 'admin/js/woo-refund-and-exchange-lite-banner.js' ), false );
+			wp_localize_script(
+				'wps-rma-promotional-banner',
+				'wrael_banner_param',
+				array(
+					'ajaxurl'       => admin_url( 'admin-ajax.php' ),
+					'wps_rma_nonce' => wp_create_nonce( 'wps_rma_ajax_seurity' ),
+				)
+			);
+			wp_enqueue_script( 'wps-rma-promotional-banner' );
+		}
+
+		if ( $this->wrael_is_settings_screen( $screen_id ) ) {
+			wp_enqueue_script( 'wps-rma-script-timepicker', WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/js/jquery.ui.timepicker.js', array( 'jquery' ), $this->wrael_asset_version( 'admin/js/jquery.ui.timepicker.js' ), true );
 		}
 	}
 
diff --git a/admin/partials/woo-refund-and-exchange-lite-admin-dashboard.php b/admin/partials/woo-refund-and-exchange-lite-admin-dashboard.php
index 94890d7..7d5fbd2 100755
--- a/admin/partials/woo-refund-and-exchange-lite-admin-dashboard.php
+++ b/admin/partials/woo-refund-and-exchange-lite-admin-dashboard.php
@@ -28,101 +28,283 @@ if ( ! defined( 'ABSPATH' ) ) {
  * @subpackage One_Click_Upsell_Addon/admin/partials
  */
 
-?>
-
-<?php
-if ( ! wps_rma_standard_check_multistep() && wps_rma_pro_active() ) {
-	?>
-	<div id="react-app"></div>
-	<?php
-	return;
-}
 $secure_nonce      = wp_create_nonce( 'wps-rma-dashboard-nonce' );
 $id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-rma-dashboard-nonce' );
 if ( ! $id_nonce_verified ) {
 	wp_die( esc_html__( 'Nonce Not verified', 'woo-refund-and-exchange-lite' ) );
 }
+
 global $wrael_wps_rma_obj;
-$wrael_active_tab   = isset( $_GET['wrael_tab'] ) ? sanitize_key( $_GET['wrael_tab'] ) : 'woo-refund-and-exchange-lite-general';
-$wrael_default_tabs = $wrael_wps_rma_obj->wps_rma_plug_default_tabs();
-if( is_plugin_active( 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php' ) ){
-	$wrael_wps_video_link = "https://youtu.be/QyfzruqwnSM";
-} else {
-	$wrael_wps_video_link = "https://youtu.be/GQhXfBtzLE0";
-}
-$wrael_wps_document_link = "https://docs.wpswings.com/rma-return-refund-exchange-for-woocommerce/?utm_source=wpswings-rma-doc&utm_medium=rma-pro-backend&utm_campaign=doc" ;
+
+$wrael_is_pro_active     = is_plugin_active( 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php' );
+$wrael_is_multistep_mode = ! wps_rma_standard_check_multistep() && wps_rma_pro_active();
+$wrael_active_tab        = isset( $_GET['wrael_tab'] ) ? sanitize_key( $_GET['wrael_tab'] ) : 'woo-refund-and-exchange-lite-general';
+$wrael_default_tabs      = $wrael_wps_rma_obj->wps_rma_plug_default_tabs();
+$wrael_active_tab        = isset( $wrael_default_tabs[ $wrael_active_tab ] ) ? $wrael_active_tab : 'woo-refund-and-exchange-lite-general';
+$wrael_active_tab_data   = isset( $wrael_default_tabs[ $wrael_active_tab ] ) ? $wrael_default_tabs[ $wrael_active_tab ] : array();
+
+$wrael_wps_video_link    = $wrael_is_pro_active ? 'https://youtu.be/QyfzruqwnSM' : 'https://youtu.be/GQhXfBtzLE0';
+$wrael_wps_document_link = 'https://docs.wpswings.com/rma-return-refund-exchange-for-woocommerce/?utm_source=wpswings-rma-doc&utm_medium=rma-pro-backend&utm_campaign=doc';
+$wrael_support_link      = 'https://wpswings.com/submit-query/?utm_source=wpswings-rma-support&utm_medium=rma-org-backend&utm_campaign=support/';
+$wrael_upgrade_link      = 'https://wpswings.com/product/rma-return-refund-exchange-for-woocommerce-pro/?utm_source=wpswings-rma&utm_medium=rma-org-page&utm_campaign=go-pro';
+$wrael_plugins_link      = 'https://wpswings.com/plugins/?utm_source=wpswings-rma&utm_medium=rma-backend&utm_campaign=more-plugins';
+$wrael_hire_us_link      = 'https://wpswings.com/hire-us/?utm_source=wpswings-rma&utm_medium=rma-backend&utm_campaign=hire-us';
+$wrael_version_label     = $wrael_is_pro_active && defined( 'RMA_RETURN_REFUND_EXCHANGE_FOR_WOOCOMMERCE_PRO_VERSION' ) ? 'v' . RMA_RETURN_REFUND_EXCHANGE_FOR_WOOCOMMERCE_PRO_VERSION . ' Pro' : 'v' . WOO_REFUND_AND_EXCHANGE_LITE_VERSION . ' Lite';
+
+$wrael_get_tab_presentation = static function( $tab_key, $tab_data ) use ( $wrael_plugins_link, $wrael_support_link, $wrael_wps_document_link ) {
+	$presentation = array(
+		'eyebrow'      => esc_html__( 'Configuration', 'woo-refund-and-exchange-lite' ),
+		'title'        => isset( $tab_data['title'] ) ? $tab_data['title'] : esc_html__( 'Dashboard', 'woo-refund-and-exchange-lite' ),
+		'description'  => esc_html__( 'Review and configure your return, refund, exchange, policy, and communication settings from one dashboard.', 'woo-refund-and-exchange-lite' ),
+		'action_label' => esc_html__( 'Read Documentation', 'woo-refund-and-exchange-lite' ),
+		'action_url'   => $wrael_wps_document_link,
+	);
+
+	switch ( $tab_key ) {
+		case 'woo-refund-and-exchange-lite-overview':
+			$presentation['eyebrow']      = esc_html__( 'Overview', 'woo-refund-and-exchange-lite' );
+			$presentation['title']        = esc_html__( 'Return, refund, and exchange control center', 'woo-refund-and-exchange-lite' );
+			$presentation['description']  = esc_html__( 'Build a clearer post-purchase experience with refund requests, exchanges, order messaging, and policy management from one interface.', 'woo-refund-and-exchange-lite' );
+			$presentation['action_label'] = esc_html__( 'Explore More Plugins', 'woo-refund-and-exchange-lite' );
+			$presentation['action_url']   = $wrael_plugins_link;
+			break;
+		case 'woo-refund-and-exchange-lite-general':
+			$presentation['eyebrow']     = esc_html__( 'Settings', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Control the base plugin behavior, refund enablement, order messaging, and request availability windows.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'woo-refund-and-exchange-lite-refund':
+			$presentation['eyebrow']     = esc_html__( 'Refund Flow', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Configure refund request fields, attachment behavior, appearance, and related notification touchpoints.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'woo-refund-and-exchange-lite-policies':
+			$presentation['eyebrow']     = esc_html__( 'Rules Engine', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Create policy-driven eligibility rules based on timelines, statuses, taxes, and pro feature extensions.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'woo-refund-and-exchange-lite-order-message':
+			$presentation['eyebrow']     = esc_html__( 'Conversations', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Manage message-related options for merchant and customer communication tied to return workflows.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'woo-refund-and-exchange-lite-developer':
+			$presentation['eyebrow']      = esc_html__( 'Developers', 'woo-refund-and-exchange-lite' );
+			$presentation['description']  = esc_html__( 'Review the available admin and public hooks before extending refund, exchange, and policy behavior.', 'woo-refund-and-exchange-lite' );
+			$presentation['action_label'] = esc_html__( 'Contact Support', 'woo-refund-and-exchange-lite' );
+			$presentation['action_url']   = $wrael_support_link;
+			break;
+		case 'woo-refund-and-exchange-lite-api':
+			$presentation['eyebrow']     = esc_html__( 'API', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Generate credentials and review request formats for refund-related programmatic integrations.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'rma-return-refund-exchange-for-woocommerce-pro-exchange':
+			$presentation['eyebrow']     = esc_html__( 'Exchange Flow', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Configure exchange request logic, pricing rules, and related exchange email flows.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'rma-return-refund-exchange-for-woocommerce-pro-cancel':
+			$presentation['eyebrow']     = esc_html__( 'Cancellation', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Set up order cancellation options, policies, and customer-side request behavior.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'rma-return-refund-exchange-for-woocommerce-pro-wallet':
+			$presentation['eyebrow']     = esc_html__( 'Wallet', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Control wallet credit behavior, wallet-related refund logic, and customer balance flows.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'rma-return-refund-exchange-for-woocommerce-pro-global-shipping':
+		case 'rma-return-refund-exchange-for-woocommerce-pro-returnship-label':
+			$presentation['eyebrow']     = esc_html__( 'Operations', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Configure shipping, return-label, and carrier integration settings that support advanced RMA operations.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'woo-refund-and-exchange-lite-sms-notification':
+		case 'woo-refund-and-exchange-lite-whatsapp-notification':
+			$presentation['eyebrow']     = esc_html__( 'Notifications', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Enable and fine-tune customer notification channels that extend the return and exchange lifecycle.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'rma-return-refund-exchange-for-woocommerce-pro-license':
+			$presentation['eyebrow']     = esc_html__( 'License', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Validate your purchase code to unlock the licensed pro feature set and maintain update eligibility.', 'woo-refund-and-exchange-lite' );
+			break;
+		case 'rma-return-refund-exchange-for-woocommerce-pro-system-status':
+			$presentation['eyebrow']     = esc_html__( 'System Status', 'woo-refund-and-exchange-lite' );
+			$presentation['description'] = esc_html__( 'Inspect WordPress and server environment details relevant to plugin compatibility and support.', 'woo-refund-and-exchange-lite' );
+			break;
+		default:
+			break;
+	}
+
+	return $presentation;
+};
+
+$wrael_render_sidebar = static function() use ( $wrael_wps_document_link, $wrael_wps_video_link, $wrael_support_link, $wrael_plugins_link, $wrael_hire_us_link ) {
+	?>
+	<aside class="wps-rma-shell__sidebar">
+		<div class="wps-rma-sidebar-card">
+			<h3><?php esc_html_e( 'Need help with this plugin?', 'woo-refund-and-exchange-lite' ); ?></h3>
+			<div class="wps-rma-sidebar-card__actions">
+				<a href="<?php echo esc_url( $wrael_wps_video_link ); ?>" target="_blank" class="wps-rma-sidebar-link"><?php esc_html_e( 'Watch Video', 'woo-refund-and-exchange-lite' ); ?></a>
+				<a href="<?php echo esc_url( $wrael_wps_document_link ); ?>" target="_blank" class="wps-rma-sidebar-link"><?php esc_html_e( 'Documentation', 'woo-refund-and-exchange-lite' ); ?></a>
+				<a href="<?php echo esc_url( $wrael_support_link ); ?>" target="_blank" class="wps-rma-sidebar-link"><?php esc_html_e( 'Support', 'woo-refund-and-exchange-lite' ); ?></a>
+			</div>
+		</div>
+		<div class="wps-rma-sidebar-card wps-rma-sidebar-card--accent">
+			<h3><?php esc_html_e( 'Still facing problems?', 'woo-refund-and-exchange-lite' ); ?></h3>
+			<p><?php esc_html_e( 'We are ready to resolve workflow, styling, and integration issues across your store setup.', 'woo-refund-and-exchange-lite' ); ?></p>
+			<a href="<?php echo esc_url( $wrael_hire_us_link ); ?>" target="_blank" class="wps-rma-sidebar-button"><?php esc_html_e( 'Hire Us', 'woo-refund-and-exchange-lite' ); ?></a>
+		</div>
+		<div class="wps-rma-sidebar-card">
+			<h3><?php esc_html_e( 'Explore more plugins', 'woo-refund-and-exchange-lite' ); ?></h3>
+			<p><?php esc_html_e( 'Discover additional commerce and automation plugins from the same product family.', 'woo-refund-and-exchange-lite' ); ?></p>
+			<a href="<?php echo esc_url( $wrael_plugins_link ); ?>" target="_blank" class="wps-rma-sidebar-link"><?php esc_html_e( 'View More Plugins', 'woo-refund-and-exchange-lite' ); ?></a>
+		</div>
+	</aside>
+	<?php
+};
+
+$wrael_active_tab_meta     = $wrael_get_tab_presentation( $wrael_active_tab, $wrael_active_tab_data );
+$wrael_visible_tab_limit = 8;
+$wrael_visible_tabs      = array_slice( $wrael_default_tabs, 0, $wrael_visible_tab_limit, true );
+$wrael_overflow_tabs     = array_slice( $wrael_default_tabs, $wrael_visible_tab_limit, null, true );
+$wrael_is_overflow_active = isset( $wrael_overflow_tabs[ $wrael_active_tab ] );
+
 do_action( 'wps_rma_show_license_info' );
 ?>
-<header>
+<div class="wps-rma-shell<?php echo $wrael_is_multistep_mode ? ' wps-rma-shell--multistep' : ''; ?>">
 	<?php
-		// Used to get the settings during saving.
-		do_action( 'wps_rma_settings_saved_notice' );
+	// Used to get the settings during saving.
+	do_action( 'wps_rma_settings_saved_notice' );
 	?>
-	<div class="wps-header-container wps-bg-white wps-r-8">
-		<h1 class="wps-header-title"><?php echo esc_html( 'RETURN REFUND AND EXCHANGE FOR WOOCOMMERCE' ); ?></h1>
-		<?php
-		if ( ! is_plugin_active( 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php' ) ) {
-			?>
-			<a class="wps_go_pro_link" style="background: #0aa000;color: white;font-weight: 700;padding: 2px 5px;border: 1px solid #139d09;border-radius: 5px;" target="_blank" href=""><?php esc_html_e( 'GO PRO', 'woo-refund-and-exchange-lite' ); ?></a>
-		<?php } ?>
-		<a href="<?php echo esc_attr( $wrael_wps_document_link ) ;?>"  target="_blank" class="wps-link"><?php esc_html_e( 'Documentation', 'woo-refund-and-exchange-lite' ); ?></a>
-		<span>|</span>
-		<a href="<?php echo esc_attr( $wrael_wps_video_link ); ?>" target="_blank" class="wps-link"><?php esc_html_e( 'Video', 'woo-refund-and-exchange-lite' ); ?></a>
-		<span>|</span>
-		<a href="https://wpswings.com/submit-query/?utm_source=wpswings-rma-support&utm_medium=rma-org-backend&utm_campaign=support/" target="_blank" class="wps-link"><?php esc_html_e( 'Support', 'woo-refund-and-exchange-lite' ); ?></a>
+	<div class="wps-rma-shell__promo">
+		<div class="wps-rma-shell__promo-text">
+			<?php if ( function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active() ) : ?>
+				<span class="wps-rma-shell__promo-badge"><?php esc_html_e( 'Pro Active', 'woo-refund-and-exchange-lite' ); ?></span>
+				<?php esc_html_e( 'RMA Return Refund & Exchange for WooCommerce Pro', 'woo-refund-and-exchange-lite' ); ?>
+			<?php else : ?>
+				<span class="wps-rma-shell__promo-badge"><?php esc_html_e( 'Limited Offer', 'woo-refund-and-exchange-lite' ); ?></span>
+				<?php esc_html_e( 'Create a cleaner return experience with better customer communication and workflow control.', 'woo-refund-and-exchange-lite' ); ?>
+			<?php endif; ?>
+		</div>
+		<?php if ( ! function_exists( 'wps_rma_pro_active' ) || ! wps_rma_pro_active() ) : ?>
+			<a href="<?php echo esc_url( $wrael_upgrade_link ); ?>" target="_blank" class="wps-rma-shell__promo-link"><?php esc_html_e( 'Upgrade Now', 'woo-refund-and-exchange-lite' ); ?></a>
+		<?php endif; ?>
 	</div>
-</header>
-<main class="wps-main wps-bg-white wps-r-8">
-	<nav class="wps-navbar">
-		<ul class="wps-navbar__items">
-			<?php
-			if ( is_array( $wrael_default_tabs ) && ! empty( $wrael_default_tabs ) ) {
-				foreach ( $wrael_default_tabs as $wrael_tab_key => $wrael_default_tabs ) {
-
-					$wrael_tab_classes = 'wps-link ';
-					if ( isset( $wrael_default_tabs['class'] ) ) {
-						$wrael_tab_classes .= $wrael_default_tabs['class'] . ' ';
-					}
-					if ( ! empty( $wrael_active_tab ) && $wrael_active_tab === $wrael_tab_key ) {
-						$wrael_tab_classes .= 'active';
-					}
-					?>
-					<li>
-						<a id="<?php echo esc_attr( $wrael_tab_key ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ) . '&wrael_tab=' . esc_attr( $wrael_tab_key ) ); ?>" class="<?php echo esc_attr( $wrael_tab_classes ); ?>"><?php echo esc_html( $wrael_default_tabs['title'] ); ?></a>
-					</li>
-					<?php
-				}
-			}
-			?>
-		</ul>
-	</nav>
-	<section class="wps-section">
-		<div class="wps-rma__popup-for-pro-wrap">
-			<div class="wps-rma__popup-for-pro-shadow"></div>
-			<div class="wps-rma__popup-for-pro">
-				<span class="wps-rma__popup-for-pro-close">+</span>
-				<h2 class="wps-rma__popup-for-pro-title"><?php esc_html_e( 'Want More ?? Go Pro !!', 'woo-refund-and-exchange-lite' ); ?></h2>
-				<p class="wps-rma__popup-for-pro-content"><i><?php echo esc_html__( 'The Pro Version will unlock all of the feature', 'woo-refund-and-exchange-lite' ) . '<br/>' . esc_html__( 'This will easily process returns, refunds, exchange, and cancellation requests with outstanding auto re-stocking, global shipping, wallet integration, and email notifications feature making it the perfect return management system', 'woo-refund-and-exchange-lite' ); ?></i></p>
-				<div class="wps-rma__popup-for-pro-link-wrap">
-					<a target="_blank" href="https://wpswings.com/product/rma-return-refund-exchange-for-woocommerce-pro/?utm_source=wpswings-rma&utm_medium=rma-org-page&utm_campaign=go-pro" class="wps-rma__popup-for-pro-link"><?php esc_html_e( 'Go pro now', 'woo-refund-and-exchange-lite' ); ?></a>
-				</div>
-			</div>
+
+	<div class="wps-rma-shell__frame">
+		<div class="wps-rma-shell__topbar">
+			<div class="wps-rma-shell__version"><?php echo esc_html( $wrael_version_label ); ?></div>
+			<?php if ( ! $wrael_is_multistep_mode ) : ?>
+				<nav class="wps-rma-shell__nav">
+					<ul class="wps-navbar__items wps-rma-shell__tabs">
+						<?php foreach ( $wrael_visible_tabs as $wrael_tab_key => $wrael_tab_data ) : ?>
+							<?php
+							$wrael_tab_classes = 'wps-link wps-rma-shell__tab-link';
+							if ( isset( $wrael_tab_data['class'] ) ) {
+								$wrael_tab_classes .= ' ' . $wrael_tab_data['class'];
+							}
+							if ( ! empty( $wrael_active_tab ) && $wrael_active_tab === $wrael_tab_key ) {
+								$wrael_tab_classes .= ' active';
+							}
+							?>
+							<li>
+								<a id="<?php echo esc_attr( $wrael_tab_key ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ) . '&wrael_tab=' . esc_attr( $wrael_tab_key ) ); ?>" class="<?php echo esc_attr( $wrael_tab_classes ); ?>"><?php echo esc_html( $wrael_tab_data['title'] ); ?></a>
+							</li>
+						<?php endforeach; ?>
+
+						<?php if ( ! empty( $wrael_overflow_tabs ) ) : ?>
+							<li class="wps-rma-shell__tab-overflow-item">
+								<details class="wps-rma-shell__tab-overflow<?php echo $wrael_is_overflow_active ? ' is-active' : ''; ?>">
+									<summary class="wps-rma-shell__tab-link wps-rma-shell__tab-summary<?php echo $wrael_is_overflow_active ? ' active' : ''; ?>">
+										<span><?php esc_html_e( 'More', 'woo-refund-and-exchange-lite' ); ?></span>
+									</summary>
+									<ul class="wps-rma-shell__tab-overflow-menu">
+										<?php foreach ( $wrael_overflow_tabs as $wrael_tab_key => $wrael_tab_data ) : ?>
+											<?php
+											$wrael_tab_classes = 'wps-link wps-rma-shell__overflow-link';
+											if ( isset( $wrael_tab_data['class'] ) ) {
+												$wrael_tab_classes .= ' ' . $wrael_tab_data['class'];
+											}
+											if ( ! empty( $wrael_active_tab ) && $wrael_active_tab === $wrael_tab_key ) {
+												$wrael_tab_classes .= ' active';
+											}
+											?>
+											<li>
+												<a id="<?php echo esc_attr( $wrael_tab_key ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ) . '&wrael_tab=' . esc_attr( $wrael_tab_key ) ); ?>" class="<?php echo esc_attr( $wrael_tab_classes ); ?>"><?php echo esc_html( $wrael_tab_data['title'] ); ?></a>
+											</li>
+										<?php endforeach; ?>
+									</ul>
+								</details>
+							</li>
+						<?php endif; ?>
+					</ul>
+				</nav>
+			<?php else : ?>
+				<div class="wps-rma-shell__setup-flag"><?php esc_html_e( 'Setup Assistant', 'woo-refund-and-exchange-lite' ); ?></div>
+			<?php endif; ?>
+
+			<?php if ( ! $wrael_is_pro_active ) : ?>
+				<a class="wps_go_pro_link wps-rma-shell__upgrade" target="_blank" href="<?php echo esc_url( $wrael_upgrade_link ); ?>"><?php esc_html_e( 'Upgrade to Pro', 'woo-refund-and-exchange-lite' ); ?></a>
+			<?php endif; ?>
 		</div>
-		<div>
-			<?php
-				// desc - This hook is used for trial.
-				do_action( 'wps_rma_before_general_settings_form' );
-				// if submenu is directly clicked on woocommerce.
-			if ( empty( $wrael_active_tab ) ) {
-				$wrael_active_tab = 'wps_rma_plug_general';
-			}
-
-				// look for the path based on the tab id in the admin templates.
-				$wrael_default_tabs     = $wrael_wps_rma_obj->wps_rma_plug_default_tabs();
-				$wrael_tab_content_path = $wrael_default_tabs[ $wrael_active_tab ]['file_path'];
-				$wrael_wps_rma_obj->wps_rma_plug_load_template( $wrael_tab_content_path );
-				// desc - This hook is used for trial.
-				do_action( 'wps_rma_after_general_settings_form' );
-			?>
+
+		<div class="wps-rma-shell__layout">
+			<div class="wps-rma-shell__main">
+				<?php if ( ! $wrael_is_multistep_mode ) : ?>
+					<div class="wps-rma__popup-for-pro-wrap">
+						<div class="wps-rma__popup-for-pro-shadow"></div>
+						<div class="wps-rma__popup-for-pro">
+							<span class="wps-rma__popup-for-pro-close">+</span>
+							<h2 class="wps-rma__popup-for-pro-title"><?php esc_html_e( 'Want More ?? Go Pro !!', 'woo-refund-and-exchange-lite' ); ?></h2>
+							<p class="wps-rma__popup-for-pro-content"><i><?php echo esc_html__( 'The Pro Version will unlock all of the feature', 'woo-refund-and-exchange-lite' ) . '<br/>' . esc_html__( 'This will easily process returns, refunds, exchange, and cancellation requests with outstanding auto re-stocking, global shipping, wallet integration, and email notifications feature making it the perfect return management system', 'woo-refund-and-exchange-lite' ); ?></i></p>
+							<div class="wps-rma__popup-for-pro-link-wrap">
+								<a target="_blank" href="<?php echo esc_url( $wrael_upgrade_link ); ?>" class="wps-rma__popup-for-pro-link"><?php esc_html_e( 'Go pro now', 'woo-refund-and-exchange-lite' ); ?></a>
+							</div>
+						</div>
+					</div>
+				<?php endif; ?>
+
+				<?php if ( $wrael_is_multistep_mode ) : ?>
+					<section class="wps-rma-shell__surface wps-rma-shell__surface--setup">
+						<div class="wps-rma-setup-modal">
+							<div class="wps-rma-setup-modal__header">
+								<span class="wps-rma-shell__eyebrow"><?php esc_html_e( 'Guided Setup', 'woo-refund-and-exchange-lite' ); ?></span>
+								<h1><?php esc_html_e( 'Set up your return workflow in a few focused steps', 'woo-refund-and-exchange-lite' ); ?></h1>
+								<p><?php esc_html_e( 'This setup flow keeps the existing configuration logic intact while bringing the experience in line with the redesigned dashboard.', 'woo-refund-and-exchange-lite' ); ?></p>
+							</div>
+							<div class="wps-rma-shell__multistep-app">
+								<div id="react-app"></div>
+							</div>
+						</div>
+					</section>
+				<?php else : ?>
+					<?php if ( 'woo-refund-and-exchange-lite-overview' !== $wrael_active_tab ) : ?>
+						<section class="wps-rma-shell__hero">
+							<div>
+								<span class="wps-rma-shell__eyebrow"><?php echo esc_html( $wrael_active_tab_meta['eyebrow'] ); ?></span>
+								<h1><?php echo esc_html( $wrael_active_tab_meta['title'] ); ?></h1>
+								<p><?php echo esc_html( $wrael_active_tab_meta['description'] ); ?></p>
+							</div>
+							<a class="wps-rma-shell__hero-action" href="<?php echo esc_url( $wrael_active_tab_meta['action_url'] ); ?>" target="_blank"><?php echo esc_html( $wrael_active_tab_meta['action_label'] ); ?></a>
+						</section>
+					<?php endif; ?>
+
+					<section class="wps-rma-shell__surface<?php echo 'woo-refund-and-exchange-lite-overview' === $wrael_active_tab ? ' wps-rma-shell__surface--overview' : ''; ?>">
+						<?php
+						// desc - This hook is used for trial.
+						do_action( 'wps_rma_before_general_settings_form' );
+
+						if ( empty( $wrael_active_tab ) ) {
+							$wrael_active_tab = 'wps_rma_plug_general';
+						}
+
+						$wrael_default_tabs     = $wrael_wps_rma_obj->wps_rma_plug_default_tabs();
+						$wrael_tab_content_path = isset( $wrael_default_tabs[ $wrael_active_tab ]['file_path'] ) ? $wrael_default_tabs[ $wrael_active_tab ]['file_path'] : '';
+						$wrael_wps_rma_obj->wps_rma_plug_load_template( $wrael_tab_content_path );
+
+						// desc - This hook is used for trial.
+						do_action( 'wps_rma_after_general_settings_form' );
+						?>
+					</section>
+				<?php endif; ?>
+			</div>
+
+			<?php if ( ! $wrael_is_multistep_mode ) : ?>
+				<?php $wrael_render_sidebar(); ?>
+			<?php endif; ?>
 		</div>
-	</section>
+	</div>
+</div>
diff --git a/admin/partials/woo-refund-and-exchange-lite-overview.php b/admin/partials/woo-refund-and-exchange-lite-overview.php
index e9d0278..06b6b48 100755
--- a/admin/partials/woo-refund-and-exchange-lite-overview.php
+++ b/admin/partials/woo-refund-and-exchange-lite-overview.php
@@ -11,108 +11,69 @@
  * @subpackage woo-refund-and-exchange-lite/admin/partials
  */
 
+$wrael_support_link = 'https://wpswings.com/submit-query/?utm_source=wpswings-rma-support&utm_medium=rma-org-backend&utm_campaign=support/';
+$wrael_upgrade_link = 'https://wpswings.com/product/rma-return-refund-exchange-for-woocommerce-pro/?utm_source=wpswings-rma&utm_medium=rma-org-page&utm_campaign=go-pro';
 ?>
+<div class="wps-rma-overview">
+	<div class="wps-rma-overview__hero">
+		<div class="wps-rma-overview__icon"><?php esc_html_e( 'RMA', 'woo-refund-and-exchange-lite' ); ?></div>
+		<span class="wps-rma-overview__eyebrow"><?php esc_html_e( 'Overview', 'woo-refund-and-exchange-lite' ); ?></span>
+		<h2><?php esc_html_e( 'Return, refund, and exchange experience built for WooCommerce teams', 'woo-refund-and-exchange-lite' ); ?></h2>
+		<p><?php esc_html_e( 'Return Refund and Exchange for WooCommerce centralizes refund requests, customer communication, exchange flows, and policy enforcement so your support team can move faster with fewer manual steps.', 'woo-refund-and-exchange-lite' ); ?></p>
+	</div>
 
-<div class="wps-overview__wrapper">
-	<div class="wps-overview__banner">
-		<img src="<?php echo esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL ); ?>admin/image/banner.webp" alt="Overview banner image">
+	<div class="wps-rma-overview__heading-row">
+		<span><?php esc_html_e( 'Top features of this plugin', 'woo-refund-and-exchange-lite' ); ?></span>
 	</div>
-	<div class="wps-overview__content">
-		<div class="wps-overview__content-description">
-			<h2><?php echo esc_html_e( 'What Is Woo Refund And Exchange Lite?', 'woo-refund-and-exchange-lite' ); ?></h2>
-			<p>
-				<?php
-				esc_html_e( 'Return Refund and Exchange for WooCommerce is a one-stop solution for complete refund management plugin for your WooCommerce store. This FREE plugin allows the admin to show a "Refund" button on the desired page of your store, that the customers can use to send you a refund request for their purchased product with which they are unsatisfied.', 'woo-refund-and-exchange-lite' );
-				?>
-			</p>
-			<p>
-				<?php
-				esc_html_e( 'Further, this plugin has a message feature that allows merchants and customers to connect with direct messages to solve refund related issues. Admin can set refund button text, allows customers to send reasons for refund request, set predefined reason, allow attachments along with refund request, set limits to number of attachments, set condition on products if and how long it is eligible for refund, etc.', 'woo-refund-and-exchange-lite' );
-				?>
-			</p>
-			<p>
-				<?php
-				esc_html_e( 'The whole process goes under a dedicated email based notification system which would keep both the parties on the same note. With WPML, the plugin can be translated into different languages, to engage multilingual buyers across the globe. ', 'woo-refund-and-exchange-lite' );
-				?>
-			</p>
-		</div>
-		<h2> <?php esc_html_e( 'The Free Plugin Benefits', 'woo-refund-and-exchange-lite' ); ?></h2>
-		<div class="wps-overview__keywords">
-			<div class="wps-overview__keywords-item">
-				<div class="wps-overview__keywords-card">
-					<div class="wps-overview__keywords-image">
-						<img src="<?php echo esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Connect-via-Messages.png' ); ?>" alt="AConnect-via-Messages image">
-					</div>
-					<div class="wps-overview__keywords-text">
-						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Connect via Messages ', 'woo-refund-and-exchange-lite' ); ?></h3>
-						<p class="wps-overview__keywords-description">
-							<?php
-							esc_html_e( 'Allow customers to send you messages in the refund panel.', 'woo-refund-and-exchange-lite' );
-							?>
-						</p>
-					</div>
-				</div>
+
+	<div class="wps-rma-overview__grid">
+		<div class="wps-rma-overview-card">
+			<div class="wps-rma-overview-card__media">
+				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Connect-via-Messages.png' ); ?>" alt="<?php esc_attr_e( 'Connect via messages', 'woo-refund-and-exchange-lite' ); ?>">
 			</div>
-			<div class="wps-overview__keywords-item">
-				<div class="wps-overview__keywords-card">
-					<div class="wps-overview__keywords-image">
-						<img src="<?php echo esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Allow-And-Set-Attachments-Limit.png' ); ?>" alt="Allow-And-Set-Attachments-Limit image">
-					</div>
-					<div class="wps-overview__keywords-text">
-						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Allow And Set Attachments Limit ', 'woo-refund-and-exchange-lite' ); ?></h3>
-						<p class="wps-overview__keywords-description">
-							<?php
-							esc_html_e( 'Admin can allow and set a limit to the number of attachments on the refund request form.', 'woo-refund-and-exchange-lite' );
-							?>
-						</p>
-					</div>
-				</div>
+			<h3><?php esc_html_e( 'Connect via messages', 'woo-refund-and-exchange-lite' ); ?></h3>
+			<p><?php esc_html_e( 'Let merchants and customers communicate directly inside the order workflow before or after a refund request.', 'woo-refund-and-exchange-lite' ); ?></p>
+		</div>
+		<div class="wps-rma-overview-card">
+			<div class="wps-rma-overview-card__media">
+				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Allow-And-Set-Attachments-Limit.png' ); ?>" alt="<?php esc_attr_e( 'Attachments', 'woo-refund-and-exchange-lite' ); ?>">
 			</div>
-			<div class="wps-overview__keywords-item">
-				<div class="wps-overview__keywords-card">
-					<div class="wps-overview__keywords-image">
-						<img src="<?php echo esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Set-Return-Refund-Conditions.png' ); ?>" alt="Set-Return-Refund-Conditions image">
-					</div>
-					<div class="wps-overview__keywords-text">
-						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Set Return Refund Conditions ', 'woo-refund-and-exchange-lite' ); ?></h3>
-						<p class="wps-overview__keywords-description">
-							<?php
-							esc_html_e( 'Admin can set conditions for the refund process.', 'woo-refund-and-exchange-lite' );
-							?>
-						</p>
-					</div>
-				</div>
+			<h3><?php esc_html_e( 'Attachment control', 'woo-refund-and-exchange-lite' ); ?></h3>
+			<p><?php esc_html_e( 'Accept evidence files with configurable limits so your team gets the context needed to resolve requests faster.', 'woo-refund-and-exchange-lite' ); ?></p>
+		</div>
+		<div class="wps-rma-overview-card">
+			<div class="wps-rma-overview-card__media">
+				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Set-Return-Refund-Conditions.png' ); ?>" alt="<?php esc_attr_e( 'Policy conditions', 'woo-refund-and-exchange-lite' ); ?>">
 			</div>
-			<div class="wps-overview__keywords-item">
-				<div class="wps-overview__keywords-card">
-					<div class="wps-overview__keywords-image">
-						<img src="<?php echo esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Send-Email-Notification.png' ); ?>" alt="Send-Email-Notification image">
-					</div>
-					<div class="wps-overview__keywords-text">
-						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( 'Send Email Notification', 'woo-refund-and-exchange-lite' ); ?></h3>
-						<p class="wps-overview__keywords-description">
-							<?php
-							esc_html_e( 'Set emails to notify each performing step during the refund process.', 'woo-refund-and-exchange-lite' );
-							?>
-						</p>
-					</div>
-				</div>
+			<h3><?php esc_html_e( 'Policy-based eligibility', 'woo-refund-and-exchange-lite' ); ?></h3>
+			<p><?php esc_html_e( 'Apply timelines, order status checks, and tax rules so refund eligibility follows clear business logic.', 'woo-refund-and-exchange-lite' ); ?></p>
+		</div>
+		<div class="wps-rma-overview-card">
+			<div class="wps-rma-overview-card__media">
+				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Send-Email-Notification.png' ); ?>" alt="<?php esc_attr_e( 'Email notifications', 'woo-refund-and-exchange-lite' ); ?>">
 			</div>
-			<div class="wps-overview__keywords-item">
-				<div class="wps-overview__keywords-card">
-					<div class="wps-overview__keywords-image">
-						<img src="<?php echo esc_html( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Manage-Product-Returns.png' ); ?>" alt="Manage-Product-Returns image">
-					</div>
-					<div class="wps-overview__keywords-text">
-						<h3 class="wps-overview__keywords-heading"><?php echo esc_html_e( ' Manage Product Returns ', 'woo-refund-and-exchange-lite' ); ?></h3>
-						<p class="wps-overview__keywords-description">
-							<?php
-							esc_html_e( 'Provide a complete refund system with the manage stock and the refund amount', 'woo-refund-and-exchange-lite' );
-							?>
-						</p>
-					</div>
-				</div>
+			<h3><?php esc_html_e( 'Email notifications', 'woo-refund-and-exchange-lite' ); ?></h3>
+			<p><?php esc_html_e( 'Keep merchants and customers aligned through request, approval, cancellation, and message-based email updates.', 'woo-refund-and-exchange-lite' ); ?></p>
+		</div>
+		<div class="wps-rma-overview-card">
+			<div class="wps-rma-overview-card__media">
+				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Manage-Product-Returns.png' ); ?>" alt="<?php esc_attr_e( 'Manage product returns', 'woo-refund-and-exchange-lite' ); ?>">
 			</div>
+			<h3><?php esc_html_e( 'Manage product returns', 'woo-refund-and-exchange-lite' ); ?></h3>
+			<p><?php esc_html_e( 'Track amounts, quantities, and follow-up activity across return-related workflows from one place.', 'woo-refund-and-exchange-lite' ); ?></p>
+		</div>
+	</div>
+
+	<div class="wps-rma-overview__cta">
+		<div>
+			<strong><?php esc_html_e( 'Facing issues?', 'woo-refund-and-exchange-lite' ); ?></strong>
+			<p><?php esc_html_e( 'We are ready to help you align refund operations, customer messaging, and advanced return workflows.', 'woo-refund-and-exchange-lite' ); ?></p>
+		</div>
+		<div class="wps-rma-overview__cta-actions">
+			<a href="<?php echo esc_url( $wrael_support_link ); ?>" target="_blank" class="wps-rma-overview__button wps-rma-overview__button--secondary"><?php esc_html_e( 'Contact Support', 'woo-refund-and-exchange-lite' ); ?></a>
+			<?php if ( ! function_exists( 'wps_rma_pro_active' ) || ! wps_rma_pro_active() ) : ?>
+				<a href="<?php echo esc_url( $wrael_upgrade_link ); ?>" target="_blank" class="wps-rma-overview__button"><?php esc_html_e( 'Unlock Pro Features', 'woo-refund-and-exchange-lite' ); ?></a>
+			<?php endif; ?>
 		</div>
 	</div>
 </div>
diff --git a/multistep-form/build/index.js b/multistep-form/build/index.js
index 86de614..b3e9735 100755
--- a/multistep-form/build/index.js
+++ b/multistep-form/build/index.js
@@ -55717,6 +55717,7 @@ function FinalStep(props) {
       name: "consetCheck",
       color: "primary"
     }),
+    label: Object(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_5__["__"])('Enable tracking', 'woo-refund-and-exchange-lite'),
     className: classes.margin
   })), Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_material_ui_core__WEBPACK_IMPORTED_MODULE_3__["FormControlLabel"], {
     control: Object(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__["createElement"])(_material_ui_core__WEBPACK_IMPORTED_MODULE_3__["Switch"], {
diff --git a/public/partials/wps-rma-refund-request-form.php b/public/partials/wps-rma-refund-request-form.php
index fb041d7..b9a35a7 100755
--- a/public/partials/wps-rma-refund-request-form.php
+++ b/public/partials/wps-rma-refund-request-form.php
@@ -100,47 +100,47 @@ if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp
 							if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) {
 								$shipping_price += $order_obj->get_shipping_tax();
 							}
-							foreach ( $order_obj->get_items() as $item_id => $item ) {
-								$item_quantity = $item->get_quantity();
-								$refund_qty    = $order_obj->get_qty_refunded_for_item( $item_id );
-								$item_qty      = $item->get_quantity() + $refund_qty;
-								// manage the return quantity, if there is refund reuqest has been made and approved.
-								if ( ! empty( $refund_items_details ) && isset( $refund_items_details[ $item_id ] ) ) {
-									$return_item_qty = $refund_items_details[ $item_id ];
-									$item_qty        = $item->get_quantity() - $return_item_qty;
-								}
-								if ( $item_qty > 0 ) {
-									if ( ! empty( $item->get_product_id() ) ) {
-										$product_id   = $item->get_product_id();
-									} else {
-										$product_id = $item->get_product_id();
+								foreach ( $order_obj->get_items() as $item_id => $item ) {
+									$item_quantity = $item->get_quantity();
+									$refund_qty    = abs( (int) $order_obj->get_qty_refunded_for_item( $item_id ) );
+									$item_qty      = max( 0, $item_quantity - $refund_qty );
+									// Manage the return quantity if a refund has already been approved.
+									if ( ! empty( $refund_items_details ) && isset( $refund_items_details[ $item_id ] ) ) {
+										$return_item_qty = abs( (int) $refund_items_details[ $item_id ] );
+										$item_qty        = max( 0, $item_quantity - $return_item_qty );
 									}
-									$product = wc_get_product( $product_id );
+									$product_id = $item->get_product_id();
+									$product    = wc_get_product( $product_id );
 
 									$coupon_discount = get_option( 'wps_rma_refund_deduct_coupon', 'no' );
 									if ( 'on' === $coupon_discount ) {
 										$item_price_inc_tax = $item->get_total() + $item->get_total_tax();
 										$item_price_exc_tax = $item->get_total() - $item->get_total_tax();
-										$item_price = $item->get_total();
+										$item_price         = $item->get_total();
 									} else {
 										$item_price_inc_tax = $item->get_subtotal() + $item->get_subtotal_tax();
 										$item_price_exc_tax = $item->get_subtotal() - $item->get_subtotal_tax();
-										$item_price = $item->get_subtotal();
+										$item_price         = $item->get_subtotal();
 									}
 									if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) {
 										$item_price = $item_price_inc_tax;
 									} elseif ( 'wps_rma_exclude_tax' === $wps_rma_check_tax ) {
 										$item_price = $item_price_exc_tax;
 									}
-									$total_items_price += $item_price;
+
+									$per_item_price = $item_price / max( 1, $item_quantity );
+									$line_total     = $per_item_price * $item_qty;
+									if ( $item_qty > 0 ) {
+										$total_items_price += $line_total;
+									}
 									?>
-									<tr class="wps_rma_return_column" data-productid="<?php echo esc_html( $product_id ); ?>" data-variationid="<?php echo esc_html( $item['variation_id'] ); ?>" data-item_id="<?php echo esc_html( $item_id ); ?>">
+									<tr class="wps_rma_return_column<?php echo $item_qty <= 0 ? ' wps_rma_return_column--disabled' : ''; ?>" data-productid="<?php echo esc_html( $product_id ); ?>" data-variationid="<?php echo esc_html( $item['variation_id'] ); ?>" data-item_id="<?php echo esc_html( $item_id ); ?>">
 										<?php
 										// To show extra column field value in the tbody.
 										do_action( 'wps_rma_add_extra_column_field_value', $item_id, $product_id, $order_obj );
 										?>
 										<td class="product-name">
-											<input type="hidden" name="wps_rma_product_amount" class="wps_rma_product_amount" data-item_id="<?php echo esc_html( $item_id ); ?>" value="<?php echo esc_html( $item_price / $item->get_quantity() ); ?>">
+											<input type="hidden" name="wps_rma_product_amount" class="wps_rma_product_amount" data-item_id="<?php echo esc_html( $item_id ); ?>" value="<?php echo esc_html( $per_item_price ); ?>">
 											<div class="wps-rma-product__wrap">
 												<?php
 												$product_permalink = ( $product && $product->is_visible() ) ? $product->get_permalink( $item ) : '';
@@ -152,23 +152,22 @@ if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp
 													<img alt="Placeholder" width="150" height="150" class="attachment-thumbnail size-thumbnail wp-post-image" src="<?php echo esc_html( plugins_url() ); ?>/woocommerce/assets/images/placeholder.png">
 													<?php
 												}
-													?>
+												?>
 												<div class="wps_rma_product_title wps-rma__product-title">
 													<?php
 													echo wp_kses_post( $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name() );
 													echo wp_kses_post( '<strong class="product-quantity">' . sprintf( '&times; %s', $item->get_quantity() ) . '</strong>' );
 													?>
 													<p>
-														<b><?php esc_html_e( 'Price', 'woo-refund-and-exchange-lite' ); ?> :</b> 
-														<?php
-															echo wp_kses_post( wps_wrma_format_price( $item->get_total() / $item->get_quantity(), $get_order_currency ) );
-														?>
+														<b><?php esc_html_e( 'Price', 'woo-refund-and-exchange-lite' ); ?> :</b>
+														<?php echo wp_kses_post( wps_wrma_format_price( $per_item_price, $get_order_currency ) ); ?>
 													</p>
 												</div>
 											</div>
 										</td>
 										<td class="product-quantity">
 										<?php
+										$qty_value  = $item_qty > 0 ? $item_qty : 0;
 										$allow_html = array(
 											'input' => array(
 												'type'     => array(),
@@ -176,36 +175,28 @@ if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp
 												'class'    => array(),
 												'name'     => array(),
 												'disabled' => array(),
-												'min'      => 1,
-												'max'      => $item_qty,
+												'min'      => 0,
+												'max'      => $qty_value,
 											),
 										);
-										$qty_html   = '<input type="number" max="'. esc_html( $item_qty ) .'" min="1" disabled value="' . esc_html( $item_qty ) . '" class="wps_rma_return_product_qty" name="wps_rma_return_product_qty">';
-										echo // Refund form Quantity html.
-										wp_kses( apply_filters( 'wps_rma_change_quanity', $qty_html, $item_qty ), $allow_html ); // phpcs:ignore
+										$qty_html   = '<input type="number" max="'. esc_html( $qty_value ) .'" min="0" disabled value="' . esc_html( $qty_value ) . '" class="wps_rma_return_product_qty" name="wps_rma_return_product_qty">';
+										echo wp_kses( apply_filters( 'wps_rma_change_quanity', $qty_html, $item_qty ), $allow_html ); // phpcs:ignore
+										if ( $item_qty <= 0 ) {
+											echo '<p class="wps-rma-item-note">' . esc_html__( 'This product has no refundable quantity left.', 'woo-refund-and-exchange-lite' ) . '</p>';
+										}
 										?>
 										</td>
 										<td class="product-total">
-											<?php
-											echo wp_kses_post( wps_wrma_format_price( $item_price / $item->get_quantity(), $get_order_currency ) );
-
-											if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) {
-												?>
+											<?php echo wp_kses_post( wps_wrma_format_price( $line_total, $get_order_currency ) ); ?>
+											<?php if ( 'wps_rma_inlcude_tax' === $wps_rma_check_tax ) : ?>
 												<small class="tax_label"><?php esc_html_e( '(incl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
-												<?php
-											} elseif ( 'wps_rma_exclude_tax' === $wps_rma_check_tax ) {
-												?>
-													<small class="tax_label"><?php esc_html_e( '(excl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
-													<?php
-											}
-											?>
+											<?php elseif ( 'wps_rma_exclude_tax' === $wps_rma_check_tax ) : ?>
+												<small class="tax_label"><?php esc_html_e( '(excl. tax)', 'woo-refund-and-exchange-lite' ); ?></small>
+											<?php endif; ?>
 										</td>
 									</tr>
-										<?php
-									?>
 									<?php
 								}
-							}
 							$wps_rma_allow_refund_shipping_charge = get_option( 'wps_rma_allow_refund_shipping_charge' );
 							if ( 'on' == $wps_rma_allow_refund_shipping_charge && $shipping_price && ! $shipping_already_requested ) { // add the shipping charges and avoid duplicate entry.
 								$total_items_price += $shipping_price;
diff --git a/public/partials/wps-rma-view-order-msg.php b/public/partials/wps-rma-view-order-msg.php
index c0c972d..d2cf656 100755
--- a/public/partials/wps-rma-view-order-msg.php
+++ b/public/partials/wps-rma-view-order-msg.php
@@ -18,13 +18,13 @@ if ( apply_filters( 'wps_rma_refund_form_sidebar', true ) ) {
 	do_action( 'woocommerce_before_main_content' );
 }
 if ( isset( $_GET['wps_rma_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wps_rma_nonce'] ) ), 'wps_rma_nonce' ) && isset( $_GET['order_id'] ) ) {
-	$order_id = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
-	$order_obj    = wc_get_order( $order_id );
+	$order_id   = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
+	$order_obj  = wc_get_order( $order_id );
 	if ( ! empty( $order_id ) && ! empty( $order_obj ) ) {
 		$user_id = $order_obj->get_user_id();
 		if ( function_exists( 'get_current_user_id' ) && ! empty( get_current_user_id() ) && ( 1 === get_current_user_id() || get_current_user_id() === $user_id ) ) {
 			?>
-				<div id="wps_rma_order_msg_react" class="wps_rma_order_msg_react_wrapper" data-order_id="<?php echo esc_attr( $order_id ); ?>"></div>
+			<div id="wps_rma_order_msg_react" class="wps_rma_order_msg_react_wrapper" data-order_id="<?php echo esc_attr( $order_id ); ?>"></div>
 			<?php
 		}
 	}
diff --git a/admin/css/wps-rma-redesign.css b/admin/css/wps-rma-redesign.css
new file mode 100644
index 0000000..37ab516
--- /dev/null
+++ b/admin/css/wps-rma-redesign.css
@@ -0,0 +1,1563 @@
+:root {
+  --wps-rma-ink: #21133b;
+  --wps-rma-ink-soft: #6d6582;
+  --wps-rma-accent: #ffb13b;
+  --wps-rma-accent-soft: #fff3d9;
+  --wps-rma-primary: #1d1237;
+  --wps-rma-border: #ece7f5;
+  --wps-rma-surface: #ffffff;
+  --wps-rma-surface-soft: #fbf9ff;
+  --wps-rma-shadow: 0 24px 60px rgba(29, 18, 55, 0.08);
+  --wps-rma-radius-xl: 28px;
+  --wps-rma-radius-lg: 22px;
+  --wps-rma-radius-md: 16px;
+}
+
+body.wp-admin[class*="wp-swings_page_woo_refund_and_exchange_lite_menu"],
+body.wp-admin[class*="wp-swings_page_home"] {
+  background: linear-gradient(180deg, #f5f1ff 0%, #fcfbff 100%);
+}
+
+.wp-swings_page_woo_refund_and_exchange_lite_menu #wpcontent,
+.wp-swings_page_home #wpcontent {
+  padding-left: 0;
+}
+
+.wp-swings_page_woo_refund_and_exchange_lite_menu #wpbody-content,
+.wp-swings_page_home #wpbody-content {
+  padding-bottom: 24px;
+}
+
+.wps-rma-shell {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-Regular, sans-serif;
+  padding: 18px 20px 28px;
+}
+
+.wps-rma-shell a {
+  text-decoration: none;
+}
+
+.wps-rma-shell__promo {
+  background: linear-gradient(90deg, #1f1238 0%, #2f174f 100%);
+  border-radius: var(--wps-rma-radius-lg);
+  color: #fff;
+  display: flex;
+  align-items: center;
+  justify-content: space-between;
+  gap: 16px;
+  padding: 14px 22px;
+  box-shadow: var(--wps-rma-shadow);
+}
+
+.wps-rma-shell__promo-text {
+  font-size: 14px;
+  line-height: 1.5;
+}
+
+.wps-rma-shell__promo-badge {
+  display: inline-flex;
+  align-items: center;
+  background: rgba(255, 255, 255, 0.14);
+  border: 1px solid rgba(255, 255, 255, 0.16);
+  border-radius: 999px;
+  font-size: 11px;
+  font-family: NunitoSans-Bold, sans-serif;
+  letter-spacing: 0.08em;
+  margin-right: 12px;
+  padding: 5px 10px;
+  text-transform: uppercase;
+}
+
+.wps-rma-shell__promo-link {
+  background: #fff;
+  border-radius: 999px;
+  color: var(--wps-rma-primary);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 13px;
+  padding: 10px 16px;
+  white-space: nowrap;
+}
+
+.wps-rma-shell__frame {
+  background: rgba(255, 255, 255, 0.72);
+  border: 1px solid rgba(236, 231, 245, 0.85);
+  border-radius: 30px;
+  box-shadow: var(--wps-rma-shadow);
+  margin-top: 18px;
+  overflow: visible;
+}
+
+.wps-rma-shell__topbar {
+  background: rgba(255, 255, 255, 0.94);
+  border-bottom: 1px solid var(--wps-rma-border);
+  display: flex;
+  align-items: center;
+  gap: 16px;
+  justify-content: space-between;
+  padding: 18px 28px;
+  position: relative;
+  z-index: 30;
+}
+
+.wps-rma-shell__version,
+.wps-rma-shell__setup-flag {
+  color: var(--wps-rma-ink-soft);
+  font-family: NunitoSans-Bold, sans-serif;
+  font-size: 13px;
+  min-width: max-content;
+}
+
+.wps-rma-shell__nav {
+  flex: 1 1 auto;
+  min-width: 0;
+  overflow: visible;
+}
+
+.wps-rma-shell__tabs {
+  display: flex;
+  align-items: center;
+  gap: 10px;
+  flex-wrap: nowrap;
+  margin: 0;
+  overflow: visible;
+  padding: 0;
+  position: relative;
+  scrollbar-width: none;
+}
+
+.wps-rma-shell__tabs::-webkit-scrollbar {
+  display: none;
+}
+
+.wps-rma-shell__tabs li {
+  margin: 0;
+}
+
+.wps-rma-shell__tab-link {
+  border-radius: 999px;
+  color: var(--wps-rma-ink-soft) !important;
+  font-family: NunitoSans-Bold, sans-serif;
+  font-size: 13px;
+  line-height: 1;
+  padding: 10px 14px;
+  transition: all 0.2s ease;
+  white-space: nowrap;
+}
+
+.wps-rma-shell__tab-link:hover,
+.wps-rma-shell__tab-link.active {
+  background: var(--wps-rma-accent-soft);
+  color: var(--wps-rma-primary) !important;
+}
+
+.wps-rma-shell__tab-overflow-item {
+  position: relative;
+  z-index: 45;
+}
+
+.wps-rma-shell__tab-overflow {
+  position: relative;
+}
+
+.wps-rma-shell__tab-summary {
+  align-items: center;
+  cursor: pointer;
+  display: inline-flex;
+  gap: 8px;
+  list-style: none;
+}
+
+.wps-rma-shell__tab-summary::-webkit-details-marker {
+  display: none;
+}
+
+.wps-rma-shell__tab-summary::after {
+  content: '\25be';
+  font-size: 11px;
+}
+
+.wps-rma-shell__tab-overflow-menu {
+  background: #fff;
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 18px;
+  box-shadow: 0 18px 40px rgba(29, 18, 55, 0.12);
+  display: grid;
+  gap: 6px;
+  list-style: none;
+  margin: 10px 0 0;
+  min-width: 220px;
+  padding: 10px;
+  position: absolute;
+  right: 0;
+  top: calc(100% + 8px);
+  z-index: 60;
+}
+
+.wps-rma-shell__tab-overflow-menu li {
+  margin: 0;
+}
+
+.wps-rma-shell__overflow-link {
+  border-radius: 12px;
+  color: var(--wps-rma-ink-soft) !important;
+  display: flex;
+  font-family: NunitoSans-Bold, sans-serif;
+  font-size: 13px;
+  line-height: 1.35;
+  padding: 10px 12px;
+}
+
+.wps-rma-shell__overflow-link:hover,
+.wps-rma-shell__overflow-link.active {
+  background: var(--wps-rma-accent-soft);
+  color: var(--wps-rma-primary) !important;
+}
+
+.wps-rma-shell__upgrade,
+.wps_go_pro_link.wps-rma-shell__upgrade {
+  background: #22c55e !important;
+  border: 0 !important;
+  border-radius: 999px !important;
+  color: #fff !important;
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 13px;
+  padding: 12px 18px !important;
+}
+
+.wps-rma-shell__layout {
+  display: grid;
+  gap: 24px;
+  grid-template-columns: minmax(0, 1fr) 300px;
+  padding: 28px;
+}
+
+.wps-rma-shell__main {
+  min-width: 0;
+}
+
+.wps-rma-shell__hero {
+  align-items: flex-start;
+  background: linear-gradient(180deg, rgba(255, 255, 255, 0.96) 0%, rgba(248, 245, 255, 0.96) 100%);
+  border: 1px solid var(--wps-rma-border);
+  border-radius: var(--wps-rma-radius-xl);
+  display: flex;
+  justify-content: space-between;
+  gap: 20px;
+  margin-bottom: 20px;
+  padding: 28px 32px;
+}
+
+.wps-rma-shell__eyebrow {
+  color: var(--wps-rma-accent);
+  display: inline-block;
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 12px;
+  letter-spacing: 0.08em;
+  margin-bottom: 10px;
+  text-transform: uppercase;
+}
+
+.wps-rma-shell__hero h1 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 42px;
+  line-height: 1.05;
+  margin: 0 0 12px;
+  max-width: 620px;
+}
+
+.wps-rma-shell__hero p {
+  color: var(--wps-rma-ink-soft);
+  font-size: 16px;
+  line-height: 1.7;
+  margin: 0;
+  max-width: 760px;
+}
+
+.wps-rma-shell__hero-action,
+.wps-rma-sidebar-button,
+.wps-rma-overview__button,
+.wps-rma-license-panel__button {
+  background: var(--wps-rma-primary) !important;
+  border: 0 !important;
+  border-radius: 14px !important;
+  color: #fff !important;
+  display: inline-flex;
+  align-items: center;
+  justify-content: center;
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 14px;
+  gap: 8px;
+  min-height: 48px;
+  padding: 12px 18px !important;
+}
+
+.wps-rma-overview__button--secondary {
+  background: transparent !important;
+  border: 1px solid var(--wps-rma-border) !important;
+  color: var(--wps-rma-primary) !important;
+}
+
+.wps-rma-shell__surface {
+  background: var(--wps-rma-surface);
+  border: 1px solid var(--wps-rma-border);
+  border-radius: var(--wps-rma-radius-xl);
+  box-shadow: 0 18px 45px rgba(29, 18, 55, 0.05);
+  padding: 30px 32px;
+}
+
+.wps-rma-shell__surface--overview {
+  background: linear-gradient(180deg, #fff 0%, #fcfbff 100%);
+}
+
+.wps-rma-shell__sidebar {
+  display: flex;
+  flex-direction: column;
+  gap: 16px;
+}
+
+.wps-rma-sidebar-card {
+  background: rgba(255, 255, 255, 0.96);
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 20px;
+  padding: 20px;
+}
+
+.wps-rma-sidebar-card h3 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 19px;
+  line-height: 1.2;
+  margin: 0 0 10px;
+}
+
+.wps-rma-sidebar-card p {
+  color: var(--wps-rma-ink-soft);
+  font-size: 14px;
+  line-height: 1.6;
+  margin: 0 0 14px;
+}
+
+.wps-rma-sidebar-card__actions {
+  display: flex;
+  flex-direction: column;
+  gap: 10px;
+}
+
+.wps-rma-sidebar-link {
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 14px;
+  color: var(--wps-rma-primary);
+  display: inline-flex;
+  font-family: NunitoSans-Bold, sans-serif;
+  justify-content: space-between;
+  padding: 12px 14px;
+}
+
+.wps-rma-sidebar-card--accent {
+  background: linear-gradient(180deg, #fff7ea 0%, #ffffff 100%);
+}
+
+.wps-rma-overview {
+  display: flex;
+  flex-direction: column;
+  gap: 28px;
+}
+
+.wps-rma-overview__hero {
+  align-items: center;
+  display: flex;
+  flex-direction: column;
+  gap: 14px;
+  margin: 0 auto;
+  max-width: 760px;
+  text-align: center;
+}
+
+.wps-rma-overview__icon {
+  align-items: center;
+  background: radial-gradient(circle at top, #fff4d6, #ffcc74);
+  border-radius: 22px;
+  color: var(--wps-rma-primary);
+  display: inline-flex;
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 20px;
+  height: 84px;
+  justify-content: center;
+  letter-spacing: 0.08em;
+  width: 84px;
+}
+
+.wps-rma-overview__eyebrow {
+  color: var(--wps-rma-accent);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 12px;
+  letter-spacing: 0.08em;
+  text-transform: uppercase;
+}
+
+.wps-rma-overview__hero h2 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 46px;
+  line-height: 1.08;
+  margin: 0;
+}
+
+.wps-rma-overview__hero p {
+  color: var(--wps-rma-ink-soft);
+  font-size: 17px;
+  line-height: 1.75;
+  margin: 0;
+}
+
+.wps-rma-overview__heading-row {
+  align-items: center;
+  color: var(--wps-rma-primary);
+  display: flex;
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 20px;
+  gap: 16px;
+  justify-content: center;
+}
+
+.wps-rma-overview__heading-row::before,
+.wps-rma-overview__heading-row::after {
+  background: linear-gradient(90deg, transparent, var(--wps-rma-border), transparent);
+  content: "";
+  flex: 1 1 auto;
+  height: 1px;
+  max-width: 180px;
+}
+
+.wps-rma-overview__grid {
+  display: grid;
+  gap: 18px;
+  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
+}
+
+.wps-rma-overview-card {
+  background: var(--wps-rma-surface);
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 20px;
+  display: flex;
+  flex-direction: column;
+  gap: 14px;
+  padding: 22px;
+}
+
+.wps-rma-overview-card__media {
+  align-items: center;
+  background: var(--wps-rma-surface-soft);
+  border-radius: 16px;
+  display: flex;
+  height: 74px;
+  justify-content: center;
+  width: 74px;
+}
+
+.wps-rma-overview-card__media img {
+  max-height: 46px;
+  max-width: 46px;
+}
+
+.wps-rma-overview-card h3 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 22px;
+  line-height: 1.2;
+  margin: 0;
+}
+
+.wps-rma-overview-card p {
+  color: var(--wps-rma-ink-soft);
+  font-size: 14px;
+  line-height: 1.7;
+  margin: 0;
+}
+
+.wps-rma-overview__cta {
+  align-items: center;
+  background: linear-gradient(90deg, #f9f5ff 0%, #ffffff 100%);
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 18px;
+  display: flex;
+  gap: 18px;
+  justify-content: space-between;
+  padding: 22px 24px;
+}
+
+.wps-rma-overview__cta strong {
+  color: var(--wps-rma-ink);
+  display: block;
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 20px;
+  margin-bottom: 6px;
+}
+
+.wps-rma-overview__cta p {
+  color: var(--wps-rma-ink-soft);
+  margin: 0;
+}
+
+.wps-rma-overview__cta-actions {
+  display: flex;
+  gap: 12px;
+}
+
+.wps-form-group {
+  border-top: 1px solid var(--wps-rma-border);
+  margin: 0;
+  padding: 24px 0;
+}
+
+.wps-form-group:first-child {
+  border-top: 0;
+  padding-top: 0;
+}
+
+.wps-form-group__label {
+  max-width: 260px;
+}
+
+.wps-form-label {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 15px;
+  line-height: 1.5;
+}
+
+.wps-form-group__control,
+.wps-form-group .wps-form-group__control {
+  max-width: calc(100% - 260px);
+}
+
+.wps-helper-text,
+.mdc-text-field-helper-text--persistent {
+  color: #5a5570 !important;
+  font-size: 13px;
+  line-height: 1.65;
+  margin-top: 10px;
+}
+
+.mdc-text-field-helper-line {
+  padding: 0 8px;
+}
+
+.wps-form-select select,
+.wps-time-form-group input,
+.wps-rma-license-panel__input-wrap input,
+.wps-rma-api-container input,
+.wps-rma-api-container select,
+.wps-rma-api-container textarea {
+  background: #fff !important;
+  border: 1px solid var(--wps-rma-border) !important;
+  border-radius: 14px !important;
+  box-shadow: none !important;
+  color: var(--wps-rma-ink) !important;
+  min-height: 52px;
+}
+
+.wps-rma-shell .select2.select2-container,
+.wps-rma-shell .select2-container {
+  max-width: 100% !important;
+  min-width: 0 !important;
+  width: 100% !important;
+}
+
+.wps-rma-shell .select2-container .select2-selection--single,
+.wps-rma-shell .select2-container .select2-selection--multiple {
+  align-items: center;
+  background: #fff !important;
+  border: 1px solid var(--wps-rma-border) !important;
+  border-radius: 14px !important;
+  box-shadow: none !important;
+  color: var(--wps-rma-ink) !important;
+  display: flex;
+  min-height: 52px;
+  padding: 6px 12px !important;
+}
+
+.wps-rma-shell .select2-container .select2-selection--multiple {
+  align-items: flex-start;
+  flex-wrap: wrap;
+  gap: 6px;
+}
+
+.wps-rma-shell .select2-container .select2-search--inline,
+.wps-rma-shell .select2-container .select2-search--inline .select2-search__field {
+  margin: 0 !important;
+  width: 100% !important;
+}
+
+.wps-rma-shell .select2-container .select2-search--inline .select2-search__field {
+  border: 0 !important;
+  box-shadow: none !important;
+  min-height: 32px;
+  padding: 0 !important;
+}
+
+.wps-rma-shell .select2-container--default .select2-selection--multiple .select2-selection__choice {
+  background: #f4ecff !important;
+  border: 1px solid #e4d7fb !important;
+  border-radius: 999px !important;
+  color: var(--wps-rma-primary) !important;
+  margin: 0 !important;
+  padding: 4px 10px 4px 24px !important;
+}
+
+.wps-rma-shell .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
+  border-right: 0 !important;
+  color: var(--wps-rma-primary) !important;
+  left: 8px;
+  top: 3px;
+}
+
+.wps-rma-shell .select2-container--default.select2-container--focus .select2-selection--multiple,
+.wps-rma-shell .select2-container--default .select2-selection--single:focus,
+.wps-rma-shell .select2-container--default .select2-selection--multiple:focus {
+  border-color: #cdbde8 !important;
+  box-shadow: 0 0 0 4px rgba(67, 43, 107, 0.08) !important;
+}
+
+.wps-rma-shell .select2-dropdown {
+  border: 1px solid var(--wps-rma-border) !important;
+  border-radius: 14px !important;
+  box-shadow: 0 18px 40px rgba(29, 18, 55, 0.12);
+  overflow: hidden;
+}
+
+.mdc-text-field,
+.wps-form-select {
+  width: 100%;
+}
+
+.mdc-text-field.mdc-text-field--outlined {
+  align-items: center;
+  background: #fff !important;
+  border: 1px solid var(--wps-rma-border) !important;
+  border-radius: 14px !important;
+  box-shadow: none !important;
+  display: flex;
+  overflow: visible;
+  transition: border-color 0.2s ease, box-shadow 0.2s ease;
+}
+
+.mdc-text-field.mdc-text-field--outlined .mdc-notched-outline {
+  display: none;
+}
+
+.mdc-text-field.mdc-text-field--outlined .mdc-text-field__input {
+  background: transparent !important;
+  border: 0 !important;
+  border-radius: 0 !important;
+  box-shadow: none !important;
+  color: var(--wps-rma-ink) !important;
+  min-height: 52px;
+  padding: 16px 18px !important;
+}
+
+.mdc-text-field.mdc-text-field--outlined.mdc-text-field--textarea {
+  align-items: stretch;
+}
+
+.mdc-text-field.mdc-text-field--outlined.mdc-text-field--textarea .mdc-text-field__resizer {
+  width: 100%;
+}
+
+.mdc-text-field.mdc-text-field--outlined.mdc-text-field--textarea .mdc-text-field__input {
+  min-height: 140px;
+  padding: 18px !important;
+}
+
+.mdc-text-field.mdc-text-field--outlined .mdc-floating-label,
+.mdc-text-field.mdc-text-field--outlined .mdc-floating-label--float-above,
+.mdc-text-field.mdc-text-field--outlined .mdc-floating-label--shake {
+  display: none !important;
+}
+
+.mdc-text-field.mdc-text-field--outlined .mdc-text-field__input::placeholder,
+.wps-rma-license-panel__input-wrap input::placeholder,
+.wps-rma-api-container input::placeholder,
+.wps-rma-api-container textarea::placeholder {
+  color: #8a849d !important;
+  opacity: 1;
+}
+
+.mdc-text-field.mdc-text-field--outlined .mdc-text-field__icon {
+  color: var(--wps-rma-ink-soft) !important;
+  margin-right: 16px;
+}
+
+.mdc-text-field.mdc-text-field--outlined:hover,
+.mdc-text-field.mdc-text-field--outlined:focus-within {
+  border-color: #cdbde8 !important;
+  box-shadow: 0 0 0 4px rgba(67, 43, 107, 0.08) !important;
+}
+
+.mdc-floating-label {
+  color: var(--wps-rma-ink-soft) !important;
+}
+
+.mdc-button,
+input.wps_rma_save_settings,
+#wps_rma_add_more,
+.wps-rma-shell input[type="submit"],
+.wps-rma-shell button.button,
+.wps-rma-shell button.button-primary {
+  background: var(--wps-rma-primary) !important;
+  border: 0 !important;
+  border-radius: 14px !important;
+  color: #fff !important;
+  cursor: pointer;
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 14px;
+  min-height: 48px;
+  padding: 12px 20px !important;
+}
+
+.wps-rma-shell h6 {
+  color: var(--wps-rma-ink-soft);
+  font-size: 14px;
+  margin: 16px 0 0;
+}
+
+.wps-rma-shell h6 a {
+  color: var(--wps-rma-primary);
+  font-family: NunitoSans-Bold, sans-serif;
+}
+
+.wps-rma-license-panel {
+  display: flex;
+  justify-content: center;
+  padding: 14px 0;
+}
+
+.wps-rma-license-panel__content {
+  max-width: 520px;
+  text-align: center;
+  width: 100%;
+}
+
+.wps-rma-license-panel__eyebrow {
+  color: var(--wps-rma-accent);
+  display: inline-block;
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 12px;
+  letter-spacing: 0.08em;
+  margin-bottom: 10px;
+  text-transform: uppercase;
+}
+
+.wps-rma-license-panel__content h2 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 42px;
+  margin: 0 0 14px;
+}
+
+.wps-rma-license-panel__content p {
+  color: var(--wps-rma-ink-soft);
+  font-size: 15px;
+  line-height: 1.7;
+  margin: 0 auto 18px;
+  max-width: 460px;
+}
+
+.wps-rma-license-panel__form {
+  margin-top: 18px;
+}
+
+.wps-rma-license-panel__field {
+  color: var(--wps-rma-ink);
+  display: block;
+  font-family: NunitoSans-Bold, sans-serif;
+  margin-bottom: 10px;
+  text-align: left;
+}
+
+.wps-rma-license-panel__input-wrap {
+  position: relative;
+}
+
+#wps_license_ajax_loader {
+  position: absolute;
+  right: 16px;
+  top: 50%;
+  transform: translateY(-50%);
+}
+
+.wps-rma-license-panel__form .submit {
+  margin: 18px 0 0;
+}
+
+#save_policies_setting_form {
+  margin-top: 24px;
+}
+
+.add_more_rma_policies {
+  align-items: center;
+  background: var(--wps-rma-surface-soft);
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 18px;
+  display: flex;
+  flex-wrap: wrap;
+  gap: 12px;
+  margin-bottom: 14px;
+  padding: 16px;
+}
+
+.add_more_rma_policies select,
+.add_more_rma_policies input[type="number"],
+#add_more_rma_policies_clone select,
+#add_more_rma_policies_clone input[type="number"] {
+  background: #fff;
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 12px;
+  min-height: 44px;
+  padding: 8px 12px;
+}
+
+.rma_policy_delete {
+  background: #fff !important;
+  border: 1px solid #ffd2d2 !important;
+  color: #d63d3d !important;
+  min-height: 44px;
+  min-width: 44px;
+  padding: 0 !important;
+}
+
+.wps-rma-api-container {
+  color: var(--wps-rma-ink);
+  margin-top: 28px;
+}
+
+.wps-rma-api-container h2,
+.wps-rma-api-container summary {
+  color: var(--wps-rma-primary);
+}
+
+.wps-rma-api-container code,
+.wps-rma-api-container pre,
+.wps-rma-api-container .json-response,
+.wps-rma-api-container details {
+  border-radius: 14px;
+}
+
+.table-responsive,
+.wps-rma-shell .table-responsive {
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 18px;
+  box-shadow: none;
+}
+
+.wps-rma-shell .dataTable thead th,
+.wps-rma-shell .mdc-data-table__header-cell {
+  background: var(--wps-rma-surface-soft);
+  color: var(--wps-rma-primary) !important;
+  font-family: NunitoSans-ExtraBold, sans-serif !important;
+}
+
+.wps-rma-shell .mdc-data-table__cell,
+.wps-rma-shell table td,
+.wps-rma-shell table th {
+  color: var(--wps-rma-ink);
+}
+
+.wps_rma_pro_class_wrap .wps-form-group__control,
+.wps_rma_pro_div .wps_rma_shipping_label_setting td,
+.wps_rma_pro_div .wps_rma_shipping_setting .wps_wrma_ship_validate_form_wrapper,
+.wps_rma_pro_div .wps_rma_shipping_setting .wps_wrma_shipstation_details_wrapper,
+.wps_rma_pro_div .wps_rma_shipping_setting .wps_wrma_validate_form_wrapper,
+.wps_rma_pro_div .wps_rma_shiprocket_setting .wps_wrma_shipstation_details_wrapper,
+.wps_rma_pro_div .wps_rma_shiprocket_setting .wps_wrma_validate_form_wrapper {
+  background: linear-gradient(180deg, rgba(255, 255, 255, 0.88) 0%, rgba(255, 245, 219, 0.88) 100%) !important;
+}
+
+.wps-rma-shell__multistep-app .wpsMsfWrapper {
+  min-height: 0;
+}
+
+.wps-rma-shell__multistep-app .wpsStepper {
+  background: transparent;
+  border: 0;
+  height: auto;
+  justify-content: flex-start;
+  padding: 0;
+}
+
+.wps-rma-shell__multistep-app .MuiStepper-root {
+  padding: 0 0 10px;
+}
+
+.wps-rma-shell__multistep-app .MuiStepIcon-root.MuiStepIcon-active,
+.wps-rma-shell__multistep-app .MuiStepIcon-root.MuiStepIcon-completed {
+  color: var(--wps-rma-accent);
+}
+
+.wps-rma-shell__multistep-app .wpsHeadingWrap {
+  padding: 0 0 10px;
+  text-align: left;
+}
+
+.wps-rma-shell__multistep-app .wpsHeadingWrap h2 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 34px;
+}
+
+.wps-rma-shell__multistep-app .wpsHeadingWrap p {
+  color: var(--wps-rma-ink-soft);
+  font-size: 15px;
+}
+
+.wps-rma-shell__multistep-app .wpsMsf {
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 24px;
+  box-shadow: none;
+  margin: 16px 0 0;
+  max-width: none;
+  padding: 28px;
+}
+
+.wps-rma-shell__multistep-app .MuiFormControlLabel-root {
+  align-items: flex-start;
+  border-top: 1px solid var(--wps-rma-border);
+  margin: 0;
+  padding: 16px 0;
+}
+
+.wps-rma-shell__multistep-app .MuiFormControlLabel-root:first-of-type {
+  border-top: 0;
+  padding-top: 0;
+}
+
+.wps-rma-shell__multistep-app .MuiButton-containedPrimary {
+  background: var(--wps-rma-primary);
+}
+
+.wps-rma-shell__multistep-app .MuiButton-contained {
+  border-radius: 14px;
+  box-shadow: none;
+  min-height: 46px;
+  text-transform: none;
+}
+
+@media (max-width: 1200px) {
+  .wps-rma-shell__layout {
+    grid-template-columns: minmax(0, 1fr);
+  }
+
+  .wps-rma-shell__sidebar {
+    display: grid;
+    grid-template-columns: repeat(3, minmax(0, 1fr));
+  }
+}
+
+@media (max-width: 900px) {
+  .wps-rma-shell {
+    padding: 16px 12px 24px;
+  }
+
+  .wps-rma-shell__promo,
+  .wps-rma-shell__topbar,
+  .wps-rma-shell__hero,
+  .wps-rma-overview__cta {
+    flex-direction: column;
+    align-items: flex-start;
+  }
+
+  .wps-rma-shell__hero h1,
+  .wps-rma-overview__hero h2,
+  .wps-rma-license-panel__content h2 {
+    font-size: 32px;
+  }
+
+  .wps-form-group__label,
+  .wps-form-group__control,
+  .wps-form-group .wps-form-group__control {
+    max-width: 100%;
+  }
+
+  .wps-rma-sidebar-card__actions,
+  .wps-rma-overview__cta-actions,
+  .wps-rma-shell__sidebar {
+    width: 100%;
+  }
+
+  .wps-rma-shell__sidebar {
+    grid-template-columns: 1fr;
+  }
+
+  .wps-rma-shell__tab-overflow-menu {
+    left: 0;
+    min-width: 200px;
+    right: auto;
+  }
+}
+
+@media (max-width: 640px) {
+  .wps-rma-shell__frame {
+    border-radius: 22px;
+  }
+
+  .wps-rma-shell__topbar,
+  .wps-rma-shell__layout,
+  .wps-rma-shell__surface,
+  .wps-rma-shell__hero {
+    padding: 20px;
+  }
+
+  .wps-rma-overview__grid {
+    grid-template-columns: 1fr;
+  }
+}
+
+
+.wps-rma-report-shell {
+  color: var(--wps-rma-ink);
+  margin: 24px 20px 0 0;
+}
+
+.wps-rma-report-shell__hero,
+.wps-rma-report-shell__card {
+  background: rgba(255, 255, 255, 0.92);
+  border: 1px solid rgba(236, 231, 245, 0.9);
+  border-radius: 28px;
+  box-shadow: var(--wps-rma-shadow);
+}
+
+.wps-rma-report-shell__hero {
+  margin-bottom: 18px;
+  padding: 30px 34px;
+}
+
+.wps-rma-report-shell__eyebrow {
+  color: var(--wps-rma-accent);
+  display: inline-block;
+  font-family: NunitoSans-Bold, sans-serif;
+  font-size: 12px;
+  letter-spacing: 0.08em;
+  margin-bottom: 12px;
+  text-transform: uppercase;
+}
+
+.wps-rma-report-shell__hero h1,
+.wps-rma-report-shell__card-head h2 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  margin: 0;
+}
+
+.wps-rma-report-shell__hero h1 {
+  font-size: 34px;
+  line-height: 1.15;
+}
+
+.wps-rma-report-shell__hero p,
+.wps-rma-report-shell__card-head p {
+  color: var(--wps-rma-ink-soft);
+  font-size: 15px;
+  line-height: 1.7;
+  margin: 10px 0 0;
+  max-width: 860px;
+}
+
+.wps-rma-report-shell__switcher {
+  display: inline-flex;
+  gap: 10px;
+  margin-bottom: 18px;
+}
+
+.wps-rma-report-shell__switch {
+  background: rgba(255, 255, 255, 0.9);
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 999px;
+  color: var(--wps-rma-ink-soft) !important;
+  font-family: NunitoSans-Bold, sans-serif;
+  font-size: 13px;
+  padding: 11px 18px;
+  text-decoration: none;
+  transition: all 0.2s ease;
+}
+
+.wps-rma-report-shell__switch:hover,
+.wps-rma-report-shell__switch.active {
+  background: var(--wps-rma-primary);
+  border-color: var(--wps-rma-primary);
+  color: #fff !important;
+}
+
+.wps-rma-report-shell__card {
+  margin-bottom: 22px;
+  padding: 28px;
+}
+
+.wps-rma-report-shell__card-head {
+  align-items: flex-start;
+  display: flex;
+  justify-content: space-between;
+  gap: 16px;
+  margin-bottom: 22px;
+}
+
+.wps-rma-report-shell__filters,
+.wps-rma-report-shell__table-controls {
+  align-items: center;
+  display: flex;
+  flex-wrap: wrap;
+  gap: 12px;
+  margin-bottom: 22px;
+}
+
+.wps-rma-report-shell__filters select,
+.wps-rma-report-shell__filters input[type="text"],
+.wps-rma-report-shell__filters input[type="date"],
+.wps-rma-report-shell__table-controls select,
+.wps-rma-report-shell__table-controls input[type="text"],
+.wps-rma-report-shell__table-controls input[type="date"] {
+  background: #fff;
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 14px;
+  box-shadow: none;
+  color: var(--wps-rma-ink);
+  min-height: 46px;
+  min-width: 170px;
+  padding: 10px 14px;
+}
+
+.wps-rma-report-shell__filters .button,
+.wps-rma-report-shell__table-controls .button,
+.wps-rma-report-shell__table-form .button,
+.wps-rma-report-shell .tablenav .button,
+.wps-rma-report-shell .search-box .button {
+  background: var(--wps-rma-primary) !important;
+  border: 0 !important;
+  border-radius: 14px !important;
+  box-shadow: none !important;
+  color: #fff !important;
+  min-height: 46px;
+  padding: 10px 16px !important;
+}
+
+.wps-rma-report-shell__filters .button.button-secondary,
+.wps-rma-report-shell .tablenav .button.button-secondary {
+  background: #f4ecff !important;
+  color: var(--wps-rma-primary) !important;
+}
+
+.wps-rma-report-shell__to-label,
+.wps-rma-report-shell__table-controls label {
+  color: var(--wps-rma-ink-soft);
+  font-family: NunitoSans-Bold, sans-serif;
+  font-size: 13px;
+}
+
+.wps-rma-report-shell__table-wrap {
+  background: #fff;
+  border: 1px solid rgba(236, 231, 245, 0.92);
+  border-radius: 22px;
+  overflow: auto;
+  padding: 16px;
+}
+
+.wps-rma-report-shell__table-form .search-box {
+  float: none;
+  margin: 0 0 14px;
+}
+
+.wps-rma-report-shell__table-form .search-box input[type="search"] {
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 14px;
+  min-height: 46px;
+  padding: 10px 14px;
+}
+
+.wps-rma-report-shell table.widefat,
+.wps-rma-report-shell .wps-rma-table-wrapper table {
+  border: 0;
+  border-collapse: separate;
+  border-spacing: 0;
+  box-shadow: none;
+  margin: 0;
+  width: 100%;
+}
+
+.wps-rma-report-shell table.widefat thead th,
+.wps-rma-report-shell .wps-rma-table-wrapper table thead th {
+  background: #f7f3fd;
+  border-bottom: 1px solid rgba(236, 231, 245, 0.95);
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  padding: 16px 14px;
+}
+
+.wps-rma-report-shell table.widefat td,
+.wps-rma-report-shell table.widefat th,
+.wps-rma-report-shell .wps-rma-table-wrapper table td,
+.wps-rma-report-shell .wps-rma-table-wrapper table th {
+  border-left: 0;
+  border-right: 0;
+  padding: 14px;
+  vertical-align: top;
+}
+
+.wps-rma-report-shell table.widefat tbody tr:nth-child(even),
+.wps-rma-report-shell .wps-rma-table-wrapper table tbody tr:nth-child(even) {
+  background: #fcfbfe;
+}
+
+.wps-rma-report-shell table.widefat tbody tr:hover,
+.wps-rma-report-shell .wps-rma-table-wrapper table tbody tr:hover {
+  background: #f7f3fd;
+}
+
+.wps-rma-report-shell__metrics {
+  display: grid;
+  gap: 18px;
+  grid-template-columns: repeat(5, minmax(0, 1fr));
+  margin-bottom: 22px;
+}
+
+.wps-rma-report-shell__metric-card {
+  background: rgba(255, 255, 255, 0.92);
+  border: 1px solid rgba(236, 231, 245, 0.9);
+  border-radius: 24px;
+  box-shadow: var(--wps-rma-shadow);
+  padding: 22px;
+}
+
+.wps-rma-report-shell__metric-card h3 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 18px;
+  margin: 0 0 16px;
+}
+
+.wps-rma-report-shell__metric-row {
+  align-items: center;
+  display: flex;
+  gap: 12px;
+  justify-content: space-between;
+  margin-top: 12px;
+}
+
+.wps-rma-report-shell__metric-row span,
+.wps-rma-report-shell__metric-list li {
+  color: var(--wps-rma-ink-soft);
+  font-size: 14px;
+}
+
+.wps-rma-report-shell__metric-row strong,
+.wps-rma-report-shell__metric-value {
+  color: var(--wps-rma-primary);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+}
+
+.wps-rma-report-shell__metric-value {
+  font-size: 36px;
+  line-height: 1.1;
+  margin: 0;
+}
+
+.wps-rma-report-shell__metric-list {
+  margin: 0;
+}
+
+.wps-rma-report-shell__metric-list li + li {
+  margin-top: 10px;
+}
+
+.wps-rma-report-shell__metric-list a {
+  color: var(--wps-rma-primary);
+  text-decoration: none;
+}
+
+.wps-rma-report-shell #woocommerce_complex_analytics_chart {
+  max-width: 100%;
+}
+
+.wps-rma-report-shell .tablenav {
+  height: auto;
+  margin: 0 0 12px;
+}
+
+.wps-rma-report-shell .tablenav.top,
+.wps-rma-report-shell .tablenav.bottom {
+  align-items: center;
+  display: flex;
+  flex-wrap: wrap;
+  gap: 12px;
+  justify-content: space-between;
+}
+
+.wps-rma-report-shell .tablenav-pages,
+.wps-rma-report-shell .displaying-num,
+.wps-rma-report-shell .pagination-links {
+  color: var(--wps-rma-ink-soft);
+}
+
+.wps-rma-report-shell .tablenav-pages .current-page,
+.wps-rma-report-shell .tablenav-pages input,
+.wps-rma-report-shell .tablenav-pages a,
+.wps-rma-report-shell .tablenav-pages span {
+  border-radius: 10px !important;
+}
+
+.wps-rma-report-shell .column-cb,
+.wps-rma-report-shell .check-column {
+  width: 42px;
+}
+
+@media (max-width: 1280px) {
+  .wps-rma-report-shell__metrics {
+    grid-template-columns: repeat(3, minmax(0, 1fr));
+  }
+}
+
+@media (max-width: 900px) {
+  .wps-rma-report-shell {
+    margin-right: 0;
+  }
+
+  .wps-rma-report-shell__hero,
+  .wps-rma-report-shell__card {
+    padding: 22px;
+  }
+
+  .wps-rma-report-shell__metrics {
+    grid-template-columns: repeat(2, minmax(0, 1fr));
+  }
+}
+
+@media (max-width: 640px) {
+  .wps-rma-report-shell__switcher {
+    display: flex;
+  }
+
+  .wps-rma-report-shell__switch {
+    flex: 1 1 0;
+    text-align: center;
+  }
+
+  .wps-rma-report-shell__metrics {
+    grid-template-columns: 1fr;
+  }
+
+  .wps-rma-report-shell__filters,
+  .wps-rma-report-shell__table-controls,
+  .wps-rma-report-shell .tablenav.top,
+  .wps-rma-report-shell .tablenav.bottom {
+    align-items: stretch;
+    flex-direction: column;
+  }
+
+  .wps-rma-report-shell__filters select,
+  .wps-rma-report-shell__filters input[type="text"],
+  .wps-rma-report-shell__filters input[type="date"],
+  .wps-rma-report-shell__table-controls select,
+  .wps-rma-report-shell__table-controls input[type="text"],
+  .wps-rma-report-shell__table-controls input[type="date"],
+  .wps-rma-report-shell__filters .button,
+  .wps-rma-report-shell__table-controls .button,
+  .wps-rma-report-shell__table-form .button {
+    width: 100%;
+  }
+}
+
+
+body.woocommerce_page_wc-reports #wpfooter {
+  display: none;
+}
+
+body.woocommerce_page_wc-reports #wpbody-content {
+  padding-bottom: 24px;
+}
+
+
+.wps-rma-shell--multistep .wps-rma-shell__layout {
+  grid-template-columns: minmax(0, 1fr);
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__surface--setup {
+  background: transparent;
+  border: 0;
+  box-shadow: none;
+  padding: 0;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app {
+  max-width: 920px;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .wpsMsfWrapper {
+  height: auto;
+  min-height: 0;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .MuiContainer-root,
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .MuiContainer-maxWidthSm {
+  max-width: 100% !important;
+  padding-left: 0 !important;
+  padding-right: 0 !important;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .MuiTypography-root {
+  display: block;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .MuiFormControl-root,
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .fieldsetWrapper {
+  width: 100%;
+}
+
+body.wp-admin[class*="woo_refund_and_exchange_lite_menu"] #wpfooter,
+.wp-swings_page_woo_refund_and_exchange_lite_menu #wpfooter,
+.wpswings_page_woo_refund_and_exchange_lite_menu #wpfooter {
+  display: none;
+}
+
+
+.wps-rma-shell--multistep .wps-rma-shell__topbar,
+.wps-rma-shell--multistep .wps-rma-shell__promo {
+  display: none;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__frame {
+  background: transparent;
+  border: 0;
+  box-shadow: none;
+  margin-top: 0;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__layout {
+  padding: 0;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__main {
+  align-items: center;
+  display: flex;
+  justify-content: center;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__surface--setup {
+  align-items: center;
+  background: radial-gradient(circle at top, rgba(255, 241, 209, 0.42), rgba(252, 251, 255, 0) 42%), linear-gradient(180deg, rgba(255, 255, 255, 0.18) 0%, rgba(248, 244, 255, 0.38) 100%);
+  display: flex;
+  justify-content: center;
+  min-height: calc(100vh - 80px);
+  width: 100%;
+}
+
+.wps-rma-setup-modal {
+  background: rgba(255, 255, 255, 0.96);
+  border: 1px solid rgba(236, 231, 245, 0.92);
+  border-radius: 32px;
+  box-shadow: 0 30px 80px rgba(29, 18, 55, 0.14);
+  max-width: 980px;
+  padding: 30px;
+  position: relative;
+  width: 100%;
+}
+
+.wps-rma-setup-modal__header {
+  margin: 0 auto 20px;
+  max-width: 720px;
+  text-align: center;
+}
+
+.wps-rma-setup-modal__header h1 {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-ExtraBold, sans-serif;
+  font-size: 38px;
+  line-height: 1.1;
+  margin: 0 0 12px;
+}
+
+.wps-rma-setup-modal__header p {
+  color: var(--wps-rma-ink-soft);
+  font-size: 15px;
+  line-height: 1.7;
+  margin: 0;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app {
+  margin: 0 auto;
+  max-width: 820px;
+  width: 100%;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .wpsStepper {
+  border-bottom: 0;
+  justify-content: center;
+  margin-bottom: 6px;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .MuiStepper-root {
+  justify-content: center;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .wpsHeadingWrap {
+  display: none;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .wpsMsf {
+  background: #fff;
+  border: 1px solid var(--wps-rma-border);
+  border-radius: 28px;
+  box-shadow: 0 18px 44px rgba(29, 18, 55, 0.08);
+  margin-top: 8px;
+  padding: 30px;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .MuiFormControlLabel-label {
+  color: var(--wps-rma-ink);
+  font-family: NunitoSans-Bold, sans-serif;
+  line-height: 1.55;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .MuiButton-contained {
+  min-width: 110px;
+}
+
+.wps-rma-shell--multistep .wps-rma-shell__multistep-app .MuiButton-contained.Mui-disabled {
+  background: #efedf4 !important;
+  color: #b6b0c6 !important;
+}
+
+@media (max-width: 900px) {
+  .wps-rma-shell--multistep .wps-rma-shell {
+    padding: 12px;
+  }
+
+  .wps-rma-shell--multistep .wps-rma-shell__surface--setup {
+    min-height: auto;
+  }
+
+  .wps-rma-setup-modal {
+    border-radius: 24px;
+    padding: 22px;
+  }
+
+  .wps-rma-setup-modal__header h1 {
+    font-size: 30px;
+  }
+}
+
+@media (max-width: 640px) {
+  .wps-rma-setup-modal {
+    padding: 18px;
+  }
+
+  .wps-rma-shell--multistep .wps-rma-shell__multistep-app .wpsMsf {
+    padding: 20px;
+  }
+}
+
+
+.wps-rma-shell--multistep .wps-rma__popup-for-pro-wrap,
+.wps-rma-shell--multistep .wps-rma__popup-for-pro,
+.wps-rma-shell--multistep .wps-rma__popup-for-pro-shadow {
+  display: none !important;
+}
diff --git a/public/css/wps-rma-public-redesign.css b/public/css/wps-rma-public-redesign.css
new file mode 100644
index 0000000..325c034
--- /dev/null
+++ b/public/css/wps-rma-public-redesign.css
@@ -0,0 +1,802 @@
+:root {
+  --wps-rma-public-ink: #21133b;
+  --wps-rma-public-ink-soft: #6d6582;
+  --wps-rma-public-accent: #ffb13b;
+  --wps-rma-public-primary: #1d1237;
+  --wps-rma-public-border: #ece7f5;
+  --wps-rma-public-surface: #ffffff;
+  --wps-rma-public-surface-soft: #fbf9ff;
+  --wps-rma-public-shadow: 0 28px 70px rgba(29, 18, 55, 0.08);
+}
+
+.wps-rma-public-shell,
+.wps-rma-form__wrapper,
+.wps_rma_refund_form_wrapper {
+  background: linear-gradient(180deg, #ffffff 0%, #fcfbff 100%);
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 28px;
+  box-shadow: var(--wps-rma-public-shadow);
+}
+
+.wps-rma-public-shell {
+  margin: 30px auto;
+  max-width: 1180px;
+  padding: 28px;
+}
+
+.wps-rma-public-shell__header {
+  margin: 0 auto 24px;
+  max-width: 760px;
+  text-align: center;
+}
+
+.wps-rma-public-shell__eyebrow {
+  color: var(--wps-rma-public-accent);
+  display: inline-block;
+  font-size: 12px;
+  font-weight: 700;
+  letter-spacing: 0.08em;
+  margin-bottom: 10px;
+  text-transform: uppercase;
+}
+
+.wps-rma-public-shell__header h1,
+.wps-rma-form__heading {
+  color: var(--wps-rma-public-ink);
+  font-size: 42px;
+  font-weight: 800;
+  line-height: 1.08;
+  margin: 0 0 12px;
+}
+
+.wps-rma-public-shell__header p {
+  color: var(--wps-rma-public-ink-soft);
+  font-size: 16px;
+  line-height: 1.75;
+  margin: 0;
+}
+
+.wps-rma-form__wrapper,
+.wps_rma_refund_form_wrapper {
+  margin: 30px auto;
+  max-width: 1180px;
+  padding: 30px;
+}
+
+.wps-rma-form__header {
+  margin-bottom: 24px;
+  text-align: center;
+}
+
+.wps-rma-product__table-wrapper,
+.wps_rma_exchange_form_wrapper {
+  background: var(--wps-rma-public-surface-soft);
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 22px;
+  overflow: hidden;
+  padding: 16px;
+}
+
+.wps-rma-product__table,
+.wps_wrma_product_table {
+  background: transparent;
+  border-collapse: collapse;
+  width: 100%;
+}
+
+.wps-rma-product__table thead th,
+.wps_wrma_product_table thead th {
+  background: #fff;
+  color: var(--wps-rma-public-primary);
+  font-size: 14px;
+  font-weight: 800;
+  padding: 18px 16px;
+}
+
+.wps-rma-product__table tbody tr,
+.wps_wrma_product_table tbody tr {
+  background: transparent;
+  border-top: 1px solid var(--wps-rma-public-border);
+}
+
+.wps-rma-product__table td,
+.wps-rma-product__table th,
+.wps_wrma_product_table td,
+.wps_wrma_product_table th {
+  color: var(--wps-rma-public-ink);
+  padding: 18px 16px;
+  vertical-align: top;
+}
+
+.wps-rma-product__wrap {
+  align-items: center;
+  display: flex;
+  gap: 16px;
+}
+
+.wps-rma-product__wrap img {
+  background: #fff;
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 18px;
+  height: 84px;
+  object-fit: contain;
+  padding: 10px;
+  width: 84px;
+}
+
+.wps-rma__product-title a,
+.wps_wrma_product_title a {
+  color: var(--wps-rma-public-primary);
+  font-size: 17px;
+  font-weight: 800;
+}
+
+.wps-rma__product-title p,
+.wps_wrma_product_title p {
+  color: var(--wps-rma-public-ink-soft);
+  margin: 8px 0 0;
+}
+
+.wps-rma-form__wrapper input[type="text"],
+.wps-rma-form__wrapper input[type="number"],
+.wps-rma-form__wrapper input[type="tel"],
+.wps-rma-form__wrapper select,
+.wps-rma-form__wrapper textarea,
+.wps-rma-public-shell input[type="text"],
+.wps-rma-public-shell input[type="number"],
+.wps-rma-public-shell input[type="tel"],
+.wps-rma-public-shell textarea,
+.wps-rma-public-shell select {
+  background: #fff;
+  border: 1px solid var(--wps-rma-public-border) !important;
+  border-radius: 14px !important;
+  box-shadow: none !important;
+  color: var(--wps-rma-public-ink) !important;
+}
+
+.wps-rma-form__wrapper textarea,
+.wps-rma-public-shell textarea {
+  min-height: 132px;
+  padding: 14px 16px;
+}
+
+.wps-rma-form__wrapper input[type="submit"],
+.wps-rma-form__wrapper button,
+.wps-rma-public-shell input[type="submit"] {
+  background: var(--wps-rma-public-primary) !important;
+  border: 0 !important;
+  border-radius: 14px !important;
+  color: #fff !important;
+  font-size: 14px;
+  font-weight: 800;
+  min-height: 48px;
+  padding: 12px 20px !important;
+}
+
+.wps_rma_return_notification_checkbox,
+.wps_rma_exchange_notification_checkbox {
+  display: flex;
+  justify-content: center;
+  margin-top: 12px;
+}
+
+.wps_rma_subject_dropdown,
+.wps_rma_other_subject,
+.wps_rma_reason_description,
+#bank_details,
+.wps_wrma_exchange_note,
+.ship_show_info,
+.ship_show,
+.wps_rma_section {
+  background: var(--wps-rma-public-surface-soft);
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 18px;
+  margin-top: 18px;
+  padding: 18px;
+}
+
+.wps_rma_subject_dropdown label,
+.wps_rma_reason_description label,
+#bank_details label,
+.wps_wrma_exchange_note label,
+.wps_rma_section label {
+  color: var(--wps-rma-public-primary);
+  font-size: 15px;
+  font-weight: 800;
+}
+
+.wps-rma-order-msg-wrapper {
+  background: var(--wps-rma-public-surface-soft);
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 18px;
+  padding: 12px;
+}
+
+#wps_rma_order_msg_react .wps_order_msg_container {
+  max-width: 100%;
+}
+
+.wps-order-msg-back {
+  background: var(--wps-rma-public-primary);
+  border-radius: 14px;
+  color: #fff;
+  display: inline-flex;
+  font-size: 14px;
+  font-weight: 800;
+  margin-bottom: 14px;
+  padding: 12px 16px;
+}
+
+.wps-order-msg_column {
+  background: linear-gradient(90deg, #f8f4ff 0%, #fff8eb 100%);
+  border: 1px solid var(--wps-rma-public-border);
+  border-bottom: 0;
+  border-radius: 18px 18px 0 0;
+  color: var(--wps-rma-public-primary);
+  font-size: 15px;
+  font-weight: 800;
+  padding: 14px 18px;
+}
+
+.wps_order_msg_sub_container {
+  background: #fff;
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 0 0 18px 18px;
+  max-height: 460px;
+  padding: 24px;
+}
+
+.wps-order-msg_row_shopmanager .wps_order_msg_detail_container,
+.wps-order-msg_row_customer .wps_order_msg_detail_container {
+  border-radius: 18px;
+  box-shadow: none;
+  line-height: 1.6;
+  max-width: 70%;
+  padding: 14px 16px;
+}
+
+.wps-order-msg_row_shopmanager .wps_order_msg_detail_container {
+  background: #edf3ff;
+  color: var(--wps-rma-public-primary);
+}
+
+.wps-order-msg_row_customer .wps_order_msg_detail_container {
+  background: var(--wps-rma-public-primary);
+  color: #fff;
+}
+
+.wps-order-msg-btn {
+  background: var(--wps-rma-public-primary);
+  border-radius: 14px;
+  min-width: 54px;
+  padding: 12px;
+}
+
+.wps_order_msg_att-wrap,
+.wps-order-msg-attachment-wrapper {
+  min-width: 42px;
+}
+
+.wps_o_m-label-wrap label,
+#wps_rma_notification_div .wps_rma_notification_label,
+.wps_order_msg_sender_details .wps_order_msg_date {
+  color: var(--wps-rma-public-ink-soft);
+}
+
+@media (max-width: 900px) {
+  .wps-rma-public-shell,
+  .wps-rma-form__wrapper,
+  .wps_rma_refund_form_wrapper {
+    padding: 20px;
+  }
+
+  .wps-rma-public-shell__header h1,
+  .wps-rma-form__heading {
+    font-size: 32px;
+  }
+
+  .wps-rma-product__wrap {
+    align-items: flex-start;
+    flex-direction: column;
+  }
+}
+
+@media (max-width: 640px) {
+  .wps-rma-public-shell,
+  .wps-rma-form__wrapper,
+  .wps_rma_refund_form_wrapper {
+    border-radius: 20px;
+    padding: 16px;
+  }
+
+  .wps-rma-product__table-wrapper,
+  .wps_rma_exchange_form_wrapper,
+  .wps_order_msg_sub_container {
+    padding: 12px;
+  }
+
+  .wps-order-msg_row_shopmanager .wps_order_msg_detail_container,
+  .wps-order-msg_row_customer .wps_order_msg_detail_container {
+    max-width: 88%;
+  }
+}
+
+
+.woocommerce-order form:has(.wps_rma_view_order),
+.woocommerce-order div:has(.wps_rma_cancel_order) {
+  display: inline-flex;
+  margin: 16px 12px 0 0;
+}
+
+.woocommerce-order form:has(.wps_rma_view_order) p,
+.woocommerce-order div:has(.wps_rma_cancel_order) p {
+  margin: 0;
+}
+
+.woocommerce-order input[type="submit"].wps_rma_view_order,
+.woocommerce-order button.wps_rma_cancel_order {
+  appearance: none;
+  -webkit-appearance: none;
+  align-items: center;
+  background: var(--wps-rma-public-primary) !important;
+  border: 0 !important;
+  border-radius: 14px !important;
+  box-shadow: 0 16px 35px rgba(29, 18, 55, 0.12);
+  color: #fff !important;
+  cursor: pointer;
+  display: inline-flex !important;
+  font-size: 14px;
+  font-weight: 800;
+  justify-content: center;
+  line-height: 1.2;
+  margin: 0 !important;
+  min-height: 48px;
+  min-width: 180px;
+  padding: 12px 20px !important;
+  text-align: center;
+  text-decoration: none !important;
+  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
+}
+
+.woocommerce-order button.wps_rma_cancel_order {
+  background: linear-gradient(135deg, #fff3d9 0%, #ffe6b0 100%) !important;
+  color: var(--wps-rma-public-primary) !important;
+}
+
+.woocommerce-order input[type="submit"].wps_rma_view_order:hover,
+.woocommerce-order button.wps_rma_cancel_order:hover {
+  box-shadow: 0 20px 40px rgba(29, 18, 55, 0.16);
+  transform: translateY(-1px);
+}
+
+.woocommerce-order input[type="submit"].wps_rma_view_order:focus,
+.woocommerce-order button.wps_rma_cancel_order:focus {
+  outline: none;
+}
+
+@media (max-width: 640px) {
+  .woocommerce-order form:has(.wps_rma_view_order),
+  .woocommerce-order div:has(.wps_rma_cancel_order) {
+    display: flex;
+    margin-right: 0;
+    width: 100%;
+  }
+
+  .woocommerce-order input[type="submit"].wps_rma_view_order,
+  .woocommerce-order button.wps_rma_cancel_order {
+    min-width: 0;
+    width: 100%;
+  }
+}
+
+
+.wps-rma-public-shell--messages {
+  max-width: 1240px;
+  overflow: hidden;
+  padding: 34px;
+  position: relative;
+}
+
+.wps-rma-public-shell--messages::before {
+  background: radial-gradient(circle at top left, rgba(255, 177, 59, 0.16), transparent 34%), radial-gradient(circle at top right, rgba(139, 92, 246, 0.08), transparent 38%);
+  content: "";
+  inset: 0;
+  pointer-events: none;
+  position: absolute;
+}
+
+.wps-rma-public-shell--messages > * {
+  position: relative;
+  z-index: 1;
+}
+
+.wps-rma-public-shell__header--messages {
+  margin: 0;
+  max-width: none;
+  text-align: left;
+}
+
+.wps-rma-public-shell__eyebrow-wrap {
+  align-items: center;
+  display: flex;
+  flex-wrap: wrap;
+  gap: 12px;
+  margin-bottom: 12px;
+}
+
+.wps-rma-public-shell__status {
+  background: rgba(29, 18, 55, 0.08);
+  border: 1px solid rgba(29, 18, 55, 0.08);
+  border-radius: 999px;
+  color: var(--wps-rma-public-primary);
+  display: inline-flex;
+  font-size: 12px;
+  font-weight: 800;
+  letter-spacing: 0.02em;
+  padding: 8px 12px;
+  text-transform: capitalize;
+}
+
+.wps-rma-public-shell__header--messages h1 {
+  font-size: clamp(34px, 4vw, 56px);
+  margin-bottom: 14px;
+  max-width: 760px;
+}
+
+.wps-rma-public-shell__header--messages p {
+  max-width: 760px;
+}
+
+.wps-rma-public-shell__summary {
+  display: grid;
+  gap: 16px;
+  grid-template-columns: repeat(3, minmax(0, 1fr));
+  margin: 28px 0 32px;
+}
+
+.wps-rma-public-shell__summary-card {
+  background: rgba(255, 255, 255, 0.86);
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 20px;
+  box-shadow: 0 16px 40px rgba(29, 18, 55, 0.06);
+  display: flex;
+  flex-direction: column;
+  gap: 8px;
+  min-height: 112px;
+  padding: 20px 22px;
+}
+
+.wps-rma-public-shell__summary-label {
+  color: var(--wps-rma-public-ink-soft);
+  font-size: 12px;
+  font-weight: 700;
+  letter-spacing: 0.08em;
+  text-transform: uppercase;
+}
+
+.wps-rma-public-shell__summary-card strong {
+  color: var(--wps-rma-public-primary);
+  font-size: 18px;
+  line-height: 1.45;
+}
+
+#wps_rma_order_msg_react .wps_order_msg_container {
+  max-width: 100%;
+}
+
+.wps-rma-public-shell--messages .wps-order-msg-back {
+  align-items: center;
+  background: linear-gradient(135deg, var(--wps-rma-public-primary) 0%, #342058 100%);
+  border: 0;
+  border-radius: 999px;
+  box-shadow: 0 18px 35px rgba(29, 18, 55, 0.12);
+  color: #fff;
+  display: inline-flex;
+  font-size: 14px;
+  font-weight: 800;
+  margin: 0 0 18px;
+  min-height: 44px;
+  padding: 0 18px;
+  text-decoration: none;
+}
+
+.wps-rma-public-shell--messages .wps-order-msg_column {
+  align-items: center;
+  background: linear-gradient(90deg, #f8f4ff 0%, #fff9ef 100%);
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 20px 20px 0 0;
+  display: grid;
+  gap: 16px;
+  grid-template-columns: repeat(2, minmax(0, 1fr));
+  padding: 18px 24px;
+}
+
+.wps-rma-public-shell--messages .wps-order-msg_column_name {
+  color: var(--wps-rma-public-primary);
+  font-size: 14px;
+  font-weight: 800;
+}
+
+.wps-rma-public-shell--messages .wps-order-msg_column_name:last-child {
+  text-align: right;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_sub_container {
+  background: rgba(255, 255, 255, 0.94);
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 0 0 24px 24px;
+  border-top: 0;
+  display: flex;
+  flex-direction: column;
+  gap: 18px;
+  margin: 0;
+  max-height: 540px;
+  min-height: 380px;
+  overflow: auto;
+  padding: 28px;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_sub_container:empty::before {
+  color: var(--wps-rma-public-ink-soft);
+  content: "No messages yet. Start the conversation with the store here.";
+  display: block;
+  font-size: 15px;
+  margin: auto;
+  max-width: 360px;
+  text-align: center;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_sub_container .wps-order-msg_row {
+  display: flex;
+  flex-direction: column;
+  gap: 10px;
+  margin: 0;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_sub_container .wps-order-msg_row_customer {
+  align-items: flex-end;
+  text-align: right;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_main_container {
+  margin: 0;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_sender_details {
+  color: var(--wps-rma-public-ink-soft);
+  font-size: 12px;
+  font-weight: 700;
+  gap: 4px;
+  letter-spacing: 0.02em;
+}
+
+.wps-rma-public-shell--messages .wmb-order-customer__msg-container .wps_order_msg_sender_details {
+  align-items: flex-end;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_sender_details .wps_order_msg_date {
+  color: inherit;
+  font-size: 12px;
+  margin: 0;
+}
+
+.wps-rma-public-shell--messages .wps-order-msg_row_shopmanager .wps_order_msg_detail_container,
+.wps-rma-public-shell--messages .wps-order-msg_row_customer .wps_order_msg_detail_container {
+  border-radius: 24px;
+  box-shadow: 0 18px 45px rgba(29, 18, 55, 0.08);
+  display: inline-block;
+  font-size: 15px;
+  line-height: 1.75;
+  max-width: min(74%, 720px);
+  padding: 16px 18px;
+}
+
+.wps-rma-public-shell--messages .wps-order-msg_row_shopmanager .wps_order_msg_detail_container {
+  background: #eef4ff;
+  border-bottom-left-radius: 8px;
+  color: var(--wps-rma-public-primary);
+}
+
+.wps-rma-public-shell--messages .wps-order-msg_row_customer .wps_order_msg_detail_container {
+  background: linear-gradient(135deg, var(--wps-rma-public-primary) 0%, #39205e 100%);
+  border-bottom-right-radius: 8px;
+  color: #fff;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_attach_container {
+  display: flex;
+  flex-wrap: wrap;
+  gap: 12px;
+  margin-top: 4px;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_single_attachment a {
+  background: #fff;
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 18px;
+  display: block;
+  padding: 8px;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_single_attachment img {
+  border: 0;
+  border-radius: 12px;
+  display: block;
+  height: 84px;
+  object-fit: cover;
+  width: 84px;
+}
+
+.wps-rma-public-shell--messages .wps-rma-order-msg-wrapper {
+  align-items: stretch;
+  background: rgba(255, 255, 255, 0.92);
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 24px;
+  box-shadow: 0 20px 45px rgba(29, 18, 55, 0.06);
+  display: grid;
+  gap: 16px;
+  grid-template-columns: minmax(0, 1fr) auto;
+  margin: 22px 0 0;
+  padding: 18px;
+}
+
+.wps-rma-public-shell--messages .wps-rma-order-msg-wrapper #wps_order_new_msg {
+  background: #fff;
+  border: 1px solid var(--wps-rma-public-border);
+  border-radius: 18px;
+  color: var(--wps-rma-public-primary);
+  font-size: 15px;
+  min-height: 136px;
+  padding: 16px 18px;
+}
+
+.wps-rma-public-shell--messages .wps-rma-order-msg-wrapper .wps-order-msg-attachment-wrapper {
+  align-items: center;
+  display: flex;
+  flex-direction: column;
+  gap: 12px;
+  justify-content: space-between;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_att-wrap,
+.wps-rma-public-shell--messages .wps-order-msg-btn {
+  align-items: center;
+  background: #fff8eb;
+  border: 1px solid rgba(255, 177, 59, 0.24);
+  border-radius: 18px;
+  display: inline-flex;
+  height: 56px;
+  justify-content: center;
+  min-width: 56px;
+  padding: 0;
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_att-wrap {
+  background: #fff;
+  border-color: var(--wps-rma-public-border);
+}
+
+.wps-rma-public-shell--messages .wps_order_msg_att-wrap svg,
+.wps-rma-public-shell--messages .wps-order-msg-btn svg {
+  height: 24px;
+  width: 24px;
+}
+
+.wps-rma-public-shell--messages .wps-order-msg-btn {
+  background: linear-gradient(135deg, var(--wps-rma-public-primary) 0%, #39205e 100%);
+  border-color: transparent;
+  box-shadow: 0 18px 35px rgba(29, 18, 55, 0.18);
+}
+
+.wps-rma-public-shell--messages .wps-order-msg-btn svg path {
+  stroke: #fff;
+}
+
+.wps-rma-public-shell--messages .wps_o_m-label-wrap {
+  margin-top: 12px;
+}
+
+.wps-rma-public-shell--messages .wps_o_m-label-wrap label {
+  font-size: 12px;
+  margin: 0;
+  text-align: right;
+}
+
+.wps-rma-public-shell--messages #wps_rma_notification_div {
+  margin: 18px 0 0 auto;
+}
+
+.wps-rma-public-shell--messages #wps_rma_notification_div label {
+  align-items: flex-start;
+  text-align: left;
+}
+
+.wps-rma-public-shell--messages #wps_rma_notification_div label input[type=tel] {
+  width: 100%;
+}
+
+@media (max-width: 900px) {
+  .wps-rma-public-shell--messages {
+    padding: 24px;
+  }
+
+  .wps-rma-public-shell__summary {
+    grid-template-columns: 1fr;
+  }
+
+  .wps-rma-public-shell--messages .wps-rma-order-msg-wrapper {
+    grid-template-columns: 1fr;
+  }
+
+  .wps-rma-public-shell--messages .wps-rma-order-msg-wrapper .wps-order-msg-attachment-wrapper {
+    align-items: stretch;
+    flex-direction: row;
+    justify-content: flex-end;
+  }
+}
+
+@media (max-width: 640px) {
+  .wps-rma-public-shell--messages {
+    padding: 18px;
+  }
+
+  .wps-rma-public-shell--messages .wps-order-msg_column {
+    gap: 10px;
+    grid-template-columns: 1fr;
+    padding: 16px 18px;
+  }
+
+  .wps-rma-public-shell--messages .wps-order-msg_column_name:last-child {
+    text-align: left;
+  }
+
+  .wps-rma-public-shell--messages .wps_order_msg_sub_container {
+    min-height: 320px;
+    padding: 18px;
+  }
+
+  .wps-rma-public-shell--messages .wps-order-msg_row_shopmanager .wps_order_msg_detail_container,
+  .wps-rma-public-shell--messages .wps-order-msg_row_customer .wps_order_msg_detail_container {
+    max-width: 100%;
+  }
+
+  .wps-rma-public-shell--messages .wps-rma-order-msg-wrapper #wps_order_new_msg {
+    min-height: 112px;
+  }
+}
+
+
+body:has(.wps_rma_refund_form_wrapper) #primary,
+body:has(.wps_rma_refund_form_wrapper) #main,
+body:has(.wps_rma_refund_form_wrapper) .content-area,
+body:has(.wps_rma_refund_form_wrapper) .site-main,
+body:has(.wps_rma_refund_form_wrapper) .woocommerce,
+body:has(.wps_rma_refund_form_wrapper) .woocommerce-page {
+  max-width: none !important;
+  width: 100% !important;
+}
+
+body:has(.wps_rma_refund_form_wrapper) #secondary,
+body:has(.wps_rma_refund_form_wrapper) .widget-area,
+body:has(.wps_rma_refund_form_wrapper) aside.widget {
+  display: none !important;
+}
+
+.wps_rma_refund_form_wrapper.wps-rma-form__wrapper {
+  margin-left: 0;
+  margin-right: 0;
+  max-width: none;
+  width: 100%;
+}
+
+
+.wps-rma-item-note {
+  color: var(--wps-rma-public-ink-soft);
+  font-size: 12px;
+  line-height: 1.5;
+  margin: 8px 0 0;
+}
+
+.wps_rma_return_column--disabled {
+  opacity: 0.82;
+}
```
