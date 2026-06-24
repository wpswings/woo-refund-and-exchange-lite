<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link  https://wpswings.com/
 * @since 1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit(); // Exit if accessed directly.
}

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://makewebbetter.com
 * @since      1.0.0
 *
 * @package    One_Click_Upsell_Addon
 * @subpackage One_Click_Upsell_Addon/admin/partials
 */

$secure_nonce      = wp_create_nonce( 'wps-rma-dashboard-nonce' );
$id_nonce_verified = wp_verify_nonce( $secure_nonce, 'wps-rma-dashboard-nonce' );
if ( ! $id_nonce_verified ) {
	wp_die( esc_html__( 'Nonce Not verified', 'woo-refund-and-exchange-lite' ) );
}

global $wrael_wps_rma_obj;

$wrael_is_pro_active     = is_plugin_active( 'woocommerce-rma-for-return-refund-and-exchange/mwb-woocommerce-rma.php' );
$wrael_is_multistep_mode = ! wps_rma_standard_check_multistep() && wps_rma_pro_active();
$wrael_active_tab        = isset( $_GET['wrael_tab'] ) ? sanitize_key( $_GET['wrael_tab'] ) : 'woo-refund-and-exchange-lite-general';
$wrael_default_tabs      = $wrael_wps_rma_obj->wps_rma_plug_default_tabs();
$wrael_active_tab        = isset( $wrael_default_tabs[ $wrael_active_tab ] ) ? $wrael_active_tab : 'woo-refund-and-exchange-lite-general';
$wrael_active_tab_data   = isset( $wrael_default_tabs[ $wrael_active_tab ] ) ? $wrael_default_tabs[ $wrael_active_tab ] : array();

$wrael_wps_video_link    = $wrael_is_pro_active ? 'https://youtu.be/QyfzruqwnSM' : 'https://youtu.be/GQhXfBtzLE0';
$wrael_wps_document_link = 'https://docs.wpswings.com/rma-return-refund-exchange-for-woocommerce/?utm_source=wpswings-rma-doc&utm_medium=rma-pro-backend&utm_campaign=doc';
$wrael_support_link      = 'https://wpswings.com/submit-query/?utm_source=wpswings-rma-support&utm_medium=rma-org-backend&utm_campaign=support/';
$wrael_plugins_link      = 'https://wpswings.com/woocommerce-plugins/?utm_source=wpswings-rma&utm_medium=rma-backend&utm_campaign=more-plugins';
$wrael_hire_us_link      = 'https://wpswings.com/contact-us/?utm_source=wpswings-rma&utm_medium=rma-backend&utm_campaign=hire-us';
$wrael_services_link     = Woo_Refund_And_Exchange_Lite_Talk_To_Expert_Form::wrael_get_services_landing_url();
$wrael_version_label     = $wrael_is_pro_active && defined( 'RMA_RETURN_REFUND_EXCHANGE_FOR_WOOCOMMERCE_PRO_VERSION' ) ? 'v' . RMA_RETURN_REFUND_EXCHANGE_FOR_WOOCOMMERCE_PRO_VERSION . ' Pro' : 'v' . WOO_REFUND_AND_EXCHANGE_LITE_VERSION . ' Lite';

$wrael_get_tab_presentation = static function( $tab_key, $tab_data ) use ( $wrael_plugins_link, $wrael_support_link, $wrael_wps_document_link ) {
	$presentation = array(
		'eyebrow'      => esc_html__( 'Configuration', 'woo-refund-and-exchange-lite' ),
		'title'        => isset( $tab_data['title'] ) ? $tab_data['title'] : esc_html__( 'Dashboard', 'woo-refund-and-exchange-lite' ),
		'description'  => esc_html__( 'Review and configure your return, refund, exchange, policy, and communication settings from one dashboard.', 'woo-refund-and-exchange-lite' ),
		'action_label' => esc_html__( 'Read Documentation', 'woo-refund-and-exchange-lite' ),
		'action_url'   => $wrael_wps_document_link,
	);

	switch ( $tab_key ) {
		case 'woo-refund-and-exchange-lite-overview':
			$presentation['eyebrow']      = esc_html__( 'Overview', 'woo-refund-and-exchange-lite' );
			$presentation['title']        = esc_html__( 'Return, refund, and exchange control center', 'woo-refund-and-exchange-lite' );
			$presentation['description']  = esc_html__( 'Build a clearer post-purchase experience with refund requests, exchanges, order messaging, and policy management from one interface.', 'woo-refund-and-exchange-lite' );
			$presentation['action_label'] = esc_html__( 'Explore More Plugins', 'woo-refund-and-exchange-lite' );
			$presentation['action_url']   = $wrael_plugins_link;
			break;
		case 'woo-refund-and-exchange-lite-rma-request':
			$presentation['eyebrow']      = esc_html__( 'Request Log', 'woo-refund-and-exchange-lite' );
			$presentation['title']        = esc_html__( 'RMA Request', 'woo-refund-and-exchange-lite' );
			$presentation['description']  = esc_html__( 'Filter and review all return, exchange, and cancellation requests. Search by order ID or narrow results by type and date range.', 'woo-refund-and-exchange-lite' );
			$presentation['action_label'] = esc_html__( 'Read Documentation', 'woo-refund-and-exchange-lite' );
			$presentation['action_url']   = $wrael_wps_document_link;
			break;
		case 'woo-refund-and-exchange-lite-general':
			$presentation['eyebrow']     = esc_html__( 'Settings', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Control the base plugin behavior, refund enablement, order messaging, and request availability windows.', 'woo-refund-and-exchange-lite' );
			$presentation['action_url']  = 'https://docs.wpswings.com/rma-return-refund-exchange-for-woocommerce/#general-settings-51';
			break;
		case 'woo-refund-and-exchange-lite-refund':
			$presentation['eyebrow']     = esc_html__( 'Refund Flow', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Configure refund request fields, attachment behavior, appearance, and related notification touchpoints.', 'woo-refund-and-exchange-lite' );
			$presentation['action_url']  = 'https://docs.wpswings.com/rma-return-refund-exchange-for-woocommerce/#woocommerce-refund-settings-tab';
			break;
		case 'woo-refund-and-exchange-lite-policies':
			$presentation['eyebrow']     = esc_html__( 'Rules Engine', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Create policy-driven eligibility rules based on timelines, statuses, taxes, and pro feature extensions.', 'woo-refund-and-exchange-lite' );
			$presentation['action_url']  = 'https://docs.wpswings.com/rma-return-refund-exchange-for-woocommerce/#rma-policies-tab';
			break;
		case 'woo-refund-and-exchange-lite-order-message':
			$presentation['eyebrow']     = esc_html__( 'Conversations', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Manage message-related options for merchant and customer communication tied to return workflows.', 'woo-refund-and-exchange-lite' );
			$presentation['action_url']  = 'https://docs.wpswings.com/rma-return-refund-exchange-for-woocommerce/#order-message-tab-2';
			break;
		case 'woo-refund-and-exchange-lite-developer':
			$presentation['eyebrow']      = esc_html__( 'Developers', 'woo-refund-and-exchange-lite' );
			$presentation['description']  = esc_html__( 'Review the available admin and public hooks before extending refund, exchange, and policy behavior.', 'woo-refund-and-exchange-lite' );
			$presentation['action_label'] = esc_html__( 'Contact Support', 'woo-refund-and-exchange-lite' );
			$presentation['action_url']   = $wrael_support_link;
			break;
		case 'woo-refund-and-exchange-lite-api':
			$presentation['eyebrow']     = esc_html__( 'API', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Generate credentials and review request formats for refund-related programmatic integrations.', 'woo-refund-and-exchange-lite' );
			break;
		case 'rma-return-refund-exchange-for-woocommerce-pro-exchange':
			$presentation['eyebrow']     = esc_html__( 'Exchange Flow', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Configure exchange request logic, pricing rules, and related exchange email flows.', 'woo-refund-and-exchange-lite' );
			break;
		case 'rma-return-refund-exchange-for-woocommerce-pro-cancel':
			$presentation['eyebrow']     = esc_html__( 'Cancellation', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Set up order cancellation options, policies, and customer-side request behavior.', 'woo-refund-and-exchange-lite' );
			break;
		case 'rma-return-refund-exchange-for-woocommerce-pro-wallet':
			$presentation['eyebrow']     = esc_html__( 'Wallet', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Control wallet credit behavior, wallet-related refund logic, and customer balance flows.', 'woo-refund-and-exchange-lite' );
			break;
		case 'rma-return-refund-exchange-for-woocommerce-pro-global-shipping':
		case 'rma-return-refund-exchange-for-woocommerce-pro-returnship-label':
			$presentation['eyebrow']     = esc_html__( 'Operations', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Configure shipping, return-label, and carrier integration settings that support advanced RMA operations.', 'woo-refund-and-exchange-lite' );
			break;
		case 'woo-refund-and-exchange-lite-sms-notification':
		case 'woo-refund-and-exchange-lite-whatsapp-notification':
			$presentation['eyebrow']     = esc_html__( 'Notifications', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Enable and fine-tune customer notification channels that extend the return and exchange lifecycle.', 'woo-refund-and-exchange-lite' );
			break;
		case 'rma-return-refund-exchange-for-woocommerce-pro-license':
			$presentation['eyebrow']     = esc_html__( 'License', 'woo-refund-and-exchange-lite' );
			$presentation['title']       = esc_html__( 'License Activation', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Validate your purchase code to unlock the pro capability set and ongoing updates.', 'woo-refund-and-exchange-lite' );
			$presentation['action_label'] = esc_html__( 'Documentation', 'woo-refund-and-exchange-lite' );
			break;
		case 'rma-return-refund-exchange-for-woocommerce-pro-system-status':
			$presentation['eyebrow']     = esc_html__( 'System Status', 'woo-refund-and-exchange-lite' );
			$presentation['description'] = esc_html__( 'Inspect WordPress and server environment details relevant to plugin compatibility and support.', 'woo-refund-and-exchange-lite' );
			break;
		default:
			break;
	}

	return $presentation;
};

$wrael_render_sidebar = static function() use ( $wrael_wps_document_link, $wrael_wps_video_link, $wrael_support_link, $wrael_plugins_link, $wrael_hire_us_link, $wrael_services_link ) {
	$wrael_marketing_services = array(
		array(
			'icon'        => 'seo',
			'title'       => esc_html__( 'SEO Services', 'woo-refund-and-exchange-lite' ),
			'description' => esc_html__( 'Improve rankings & organic traffic', 'woo-refund-and-exchange-lite' ),
		),
		array(
			'icon'        => 'ads',
			'title'       => esc_html__( 'Google Ads Setup And G4 Setup', 'woo-refund-and-exchange-lite' ),
			'description' => esc_html__( 'Run profitable ad campaigns', 'woo-refund-and-exchange-lite' ),
		),
		array(
			'icon'        => 'speed',
			'title'       => esc_html__( 'Speed Optimization', 'woo-refund-and-exchange-lite' ),
			'description' => esc_html__( 'Faster store, happier customers', 'woo-refund-and-exchange-lite' ),
		),
		array(
			'icon'        => 'dev',
			'title'       => esc_html__( 'WooCommerce Development Services', 'woo-refund-and-exchange-lite' ),
			'description' => esc_html__( 'Custom Solution For your store needs', 'woo-refund-and-exchange-lite' ),
		),
	);
	?>
	<aside class="wps-rma-shell__sidebar">
		<div class="wps-rma-sidebar-card">
			<h3><?php esc_html_e( 'Need help with this plugin?', 'woo-refund-and-exchange-lite' ); ?></h3>
			<div class="wps-rma-sidebar-card__actions">
				<a href="<?php echo esc_url( $wrael_wps_video_link ); ?>" target="_blank" class="wps-rma-sidebar-link"><?php esc_html_e( 'Watch Video', 'woo-refund-and-exchange-lite' ); ?></a>
				<a href="<?php echo esc_url( $wrael_wps_document_link ); ?>" target="_blank" class="wps-rma-sidebar-link"><?php esc_html_e( 'Documentation', 'woo-refund-and-exchange-lite' ); ?></a>
				<a href="<?php echo esc_url( $wrael_support_link ); ?>" target="_blank" class="wps-rma-sidebar-link"><?php esc_html_e( 'Support', 'woo-refund-and-exchange-lite' ); ?></a>
			</div>
		</div>
		<div class="wps-rma-sidebar-card wps-rma-sidebar-card--services">
			<div class="wps-rma-sidebar-card__header">
				<h3><?php esc_html_e( 'Grow Your Store With WP Swings', 'woo-refund-and-exchange-lite' ); ?></h3>
				<span class="wps-rma-sidebar-card__badge" aria-hidden="true"></span>
			</div>
			<p><?php esc_html_e( "Expert solutions to boost your store's performance.", 'woo-refund-and-exchange-lite' ); ?></p>
			<div class="wps-rma-service-rail">
				<?php foreach ( $wrael_marketing_services as $wrael_marketing_service ) : ?>
					<a href="<?php echo esc_url( $wrael_services_link ); ?>" target="_blank" class="wps-rma-service-rail__item">
						<span class="wps-rma-service-rail__icon wps-rma-service-rail__icon--<?php echo esc_attr( $wrael_marketing_service['icon'] ); ?>" aria-hidden="true"></span>
						<span class="wps-rma-service-rail__content">
							<span class="wps-rma-service-rail__title"><?php echo esc_html( $wrael_marketing_service['title'] ); ?></span>
							<span class="wps-rma-service-rail__description"><?php echo esc_html( $wrael_marketing_service['description'] ); ?></span>
						</span>
						<span class="wps-rma-service-rail__arrow" aria-hidden="true">&rsaquo;</span>
					</a>
				<?php endforeach; ?>
			</div>
			<button type="button" class="wps-rma-sidebar-button wps-rma-sidebar-button--full" data-wrael-open-expert-modal><?php esc_html_e( 'Talk to an Expert', 'woo-refund-and-exchange-lite' ); ?></button>
			<div class="wps-rma-service-rail__footer"><?php esc_html_e( 'Services by WP Swings', 'woo-refund-and-exchange-lite' ); ?></div>
		</div>
		<div class="wps-rma-sidebar-card wps-rma-sidebar-card--accent">
			<h3><?php esc_html_e( 'Still facing problems?', 'woo-refund-and-exchange-lite' ); ?></h3>
			<p><?php esc_html_e( 'We are ready to resolve workflow, styling, and integration issues across your store setup.', 'woo-refund-and-exchange-lite' ); ?></p>
			<a href="<?php echo esc_url( $wrael_hire_us_link ); ?>" target="_blank" class="wps-rma-sidebar-button"><?php esc_html_e( 'Contact Us', 'woo-refund-and-exchange-lite' ); ?></a>
		</div>
		<div class="wps-rma-sidebar-card">
			<h3><?php esc_html_e( 'Explore more plugins', 'woo-refund-and-exchange-lite' ); ?></h3>
			<p><?php esc_html_e( 'Discover additional commerce and automation plugins from the same product family.', 'woo-refund-and-exchange-lite' ); ?></p>
			<a href="<?php echo esc_url( $wrael_plugins_link ); ?>" target="_blank" class="wps-rma-sidebar-link"><?php esc_html_e( 'View More Plugins', 'woo-refund-and-exchange-lite' ); ?></a>
		</div>
	</aside>
	<?php
};

$wrael_active_tab_meta          = $wrael_get_tab_presentation( $wrael_active_tab, $wrael_active_tab_data );
$wrael_visible_tab_limit        = 8;
$wrael_visible_tabs             = array_slice( $wrael_default_tabs, 0, $wrael_visible_tab_limit, true );
$wrael_overflow_tabs            = array_slice( $wrael_default_tabs, $wrael_visible_tab_limit, null, true );
$wrael_is_overflow_active       = isset( $wrael_overflow_tabs[ $wrael_active_tab ] );
$wrael_layout_notice_meta_key   = 'wrael_aurora_layout_notice_dismissed';
$wrael_can_manage_layout_notice = current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' );
$wrael_show_layout_notice       = ! $wrael_is_multistep_mode && $wrael_can_manage_layout_notice && 'yes' !== get_user_meta( get_current_user_id(), $wrael_layout_notice_meta_key, true );
$wrael_layout_notice_dismiss_url = wp_nonce_url(
	add_query_arg(
		array(
			'wrael_hide_layout_notice' => '1',
			'wrael_tab'                => $wrael_active_tab,
		),
		admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' )
	),
	'wrael_hide_layout_notice'
);

	?>
<div class="wps-rma-shell<?php echo $wrael_is_multistep_mode ? ' wps-rma-shell--multistep' : ''; ?>">
	<?php
	// Used to get the settings during saving.
	do_action( 'wps_rma_settings_saved_notice' );
	?>
	<?php if ( $wrael_show_layout_notice ) : ?>
		<div class="wps-rma-layout-notice">
			<div class="wps-rma-layout-notice__badge"><?php esc_html_e( 'New Layout', 'woo-refund-and-exchange-lite' ); ?></div>
			<div class="wps-rma-layout-notice__content">
				<h3><?php esc_html_e( 'Aurora Luxe is now available across your RMA flows', 'woo-refund-and-exchange-lite' ); ?></h3>
				<p><?php esc_html_e( 'You can now enable the Aurora Luxe template for Refund, Exchange, Cancel, and Order Message, and fine-tune the visual style with the new customization fields.', 'woo-refund-and-exchange-lite' ); ?></p>
			</div>
			<a href="<?php echo esc_url( $wrael_layout_notice_dismiss_url ); ?>" class="wps-rma-layout-notice__dismiss"><?php esc_html_e( 'Dismiss', 'woo-refund-and-exchange-lite' ); ?></a>
		</div>
	<?php endif; ?>

		<div class="wps-rma-shell__promo">
			<div class="wps-rma-shell__promo-text">
			<?php if ( function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active() ) : ?>
				<span class="wps-rma-shell__promo-badge"><?php esc_html_e( 'Pro Active', 'woo-refund-and-exchange-lite' ); ?></span>
				<?php esc_html_e( 'RMA Return Refund & Exchange for WooCommerce Pro', 'woo-refund-and-exchange-lite' ); ?>
			<?php else : ?>
				<span class="wps-rma-shell__promo-badge"><?php esc_html_e( 'Free Active', 'woo-refund-and-exchange-lite' ); ?></span>
				<?php esc_html_e( 'Return Refund and Exchange for WooCommerce', 'woo-refund-and-exchange-lite' ); ?>
			<?php endif; ?>
		</div>
		</div>

		<?php do_action( 'wps_rma_show_license_info' ); ?>

		<div class="wps-rma-shell__frame">
		<div class="wps-rma-shell__topbar">
			<div class="wps-rma-shell__version"><?php echo esc_html( $wrael_version_label ); ?></div>
			<?php if ( ! $wrael_is_multistep_mode ) : ?>
				<nav class="wps-rma-shell__nav">
					<ul class="wps-navbar__items wps-rma-shell__tabs">
						<?php foreach ( $wrael_visible_tabs as $wrael_tab_key => $wrael_tab_data ) : ?>
							<?php
							$wrael_tab_classes = 'wps-link wps-rma-shell__tab-link';
							if ( isset( $wrael_tab_data['class'] ) ) {
								$wrael_tab_classes .= ' ' . $wrael_tab_data['class'];
							}
							if ( ! empty( $wrael_active_tab ) && $wrael_active_tab === $wrael_tab_key ) {
								$wrael_tab_classes .= ' active';
							}
							?>
							<li>
								<a id="<?php echo esc_attr( $wrael_tab_key ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ) . '&wrael_tab=' . esc_attr( $wrael_tab_key ) ); ?>" class="<?php echo esc_attr( $wrael_tab_classes ); ?>"><?php echo esc_html( $wrael_tab_data['title'] ); ?></a>
							</li>
						<?php endforeach; ?>

						<?php if ( ! empty( $wrael_overflow_tabs ) ) : ?>
							<li class="wps-rma-shell__tab-overflow-item">
								<details class="wps-rma-shell__tab-overflow<?php echo $wrael_is_overflow_active ? ' is-active' : ''; ?>">
									<summary class="wps-rma-shell__tab-link wps-rma-shell__tab-summary<?php echo $wrael_is_overflow_active ? ' active' : ''; ?>">
										<span><?php esc_html_e( 'More', 'woo-refund-and-exchange-lite' ); ?></span>
									</summary>
									<ul class="wps-rma-shell__tab-overflow-menu">
										<?php foreach ( $wrael_overflow_tabs as $wrael_tab_key => $wrael_tab_data ) : ?>
											<?php
											$wrael_tab_classes = 'wps-link wps-rma-shell__overflow-link';
											if ( isset( $wrael_tab_data['class'] ) ) {
												$wrael_tab_classes .= ' ' . $wrael_tab_data['class'];
											}
											if ( ! empty( $wrael_active_tab ) && $wrael_active_tab === $wrael_tab_key ) {
												$wrael_tab_classes .= ' active';
											}
											?>
											<li>
												<a id="<?php echo esc_attr( $wrael_tab_key ); ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=woo_refund_and_exchange_lite_menu' ) . '&wrael_tab=' . esc_attr( $wrael_tab_key ) ); ?>" class="<?php echo esc_attr( $wrael_tab_classes ); ?>"><?php echo esc_html( $wrael_tab_data['title'] ); ?></a>
											</li>
										<?php endforeach; ?>
									</ul>
								</details>
							</li>
						<?php endif; ?>
					</ul>
				</nav>
			<?php else : ?>
				<div class="wps-rma-shell__setup-flag"><?php esc_html_e( 'Setup Assistant', 'woo-refund-and-exchange-lite' ); ?></div>
			<?php endif; ?>

		</div>

		<?php $wrael_is_full_layout = in_array( $wrael_active_tab, array( 'woo-refund-and-exchange-lite-policies', 'woo-refund-and-exchange-lite-rma-request' ), true ); ?>
		<div class="wps-rma-shell__layout<?php echo $wrael_is_full_layout ? ' wps-rma-shell__layout--full' : ''; ?>">
			<div class="wps-rma-shell__main">
				<?php if ( $wrael_is_multistep_mode ) : ?>
					<section class="wps-rma-shell__surface wps-rma-shell__surface--setup">
						<div class="wps-rma-setup-modal">
							<div class="wps-rma-setup-modal__header">
								<span class="wps-rma-shell__eyebrow"><?php esc_html_e( 'Guided Setup', 'woo-refund-and-exchange-lite' ); ?></span>
								<h1><?php esc_html_e( 'Set up your return workflow in a few focused steps', 'woo-refund-and-exchange-lite' ); ?></h1>
								<p><?php esc_html_e( 'This setup flow keeps the existing configuration logic intact while bringing the experience in line with the redesigned dashboard.', 'woo-refund-and-exchange-lite' ); ?></p>
							</div>
							<div class="wps-rma-shell__multistep-app">
								<div id="react-app"></div>
							</div>
						</div>
					</section>
				<?php else : ?>
					<?php if ( 'woo-refund-and-exchange-lite-overview' !== $wrael_active_tab ) : ?>
						<section class="wps-rma-shell__hero<?php echo 'rma-return-refund-exchange-for-woocommerce-pro-license' === $wrael_active_tab ? ' wps-rma-shell__hero--license' : ''; ?>">
							<div>
								<span class="wps-rma-shell__eyebrow"><?php echo esc_html( $wrael_active_tab_meta['eyebrow'] ); ?></span>
								<h1><?php echo esc_html( $wrael_active_tab_meta['title'] ); ?></h1>
								<p><?php echo esc_html( $wrael_active_tab_meta['description'] ); ?></p>
							</div>
							<a class="wps-rma-shell__hero-action" href="<?php echo esc_url( $wrael_active_tab_meta['action_url'] ); ?>" target="_blank"><?php echo esc_html( $wrael_active_tab_meta['action_label'] ); ?></a>
						</section>
					<?php endif; ?>

					<section class="wps-rma-shell__surface<?php echo 'woo-refund-and-exchange-lite-overview' === $wrael_active_tab ? ' wps-rma-shell__surface--overview' : ''; ?>">
						<?php
						// desc - This hook is used for trial.
						do_action( 'wps_rma_before_general_settings_form' );

						if ( empty( $wrael_active_tab ) ) {
							$wrael_active_tab = 'wps_rma_plug_general';
						}

						$wrael_default_tabs     = $wrael_wps_rma_obj->wps_rma_plug_default_tabs();
						$wrael_tab_content_path = isset( $wrael_default_tabs[ $wrael_active_tab ]['file_path'] ) ? $wrael_default_tabs[ $wrael_active_tab ]['file_path'] : '';
						$wrael_wps_rma_obj->wps_rma_plug_load_template( $wrael_tab_content_path );

						// desc - This hook is used for trial.
						do_action( 'wps_rma_after_general_settings_form' );
						?>
					</section>
				<?php endif; ?>
			</div>

			<?php if ( ! $wrael_is_multistep_mode && ! $wrael_is_full_layout ) : ?>
				<?php $wrael_render_sidebar(); ?>
			<?php endif; ?>
		</div>

		<?php if ( ! $wrael_is_multistep_mode ) : ?>
			<?php Woo_Refund_And_Exchange_Lite_Talk_To_Expert_Form::wrael_render_modal(); ?>
		<?php endif; ?>
	</div>
</div>
