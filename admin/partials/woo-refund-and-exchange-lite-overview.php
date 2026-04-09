<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for overview.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

$wrael_support_link = 'https://wpswings.com/submit-query/?utm_source=wpswings-rma-support&utm_medium=rma-org-backend&utm_campaign=support/';
?>
<div class="wps-rma-overview">
	<div class="wps-rma-overview__hero">
		<div class="wps-rma-overview__icon"><?php esc_html_e( 'RMA', 'woo-refund-and-exchange-lite' ); ?></div>
		<span class="wps-rma-overview__eyebrow"><?php esc_html_e( 'Overview', 'woo-refund-and-exchange-lite' ); ?></span>
		<h2><?php esc_html_e( 'Return, refund, and exchange experience built for WooCommerce teams', 'woo-refund-and-exchange-lite' ); ?></h2>
		<p><?php esc_html_e( 'Return Refund and Exchange for WooCommerce centralizes refund requests, customer communication, exchange flows, and policy enforcement so your support team can move faster with fewer manual steps.', 'woo-refund-and-exchange-lite' ); ?></p>
	</div>

	<div class="wps-rma-overview__heading-row">
		<span><?php esc_html_e( 'Top features of this plugin', 'woo-refund-and-exchange-lite' ); ?></span>
	</div>

	<div class="wps-rma-overview__grid">
		<div class="wps-rma-overview-card">
			<div class="wps-rma-overview-card__media">
				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Connect-via-Messages.png' ); ?>" alt="<?php esc_attr_e( 'Connect via messages', 'woo-refund-and-exchange-lite' ); ?>">
			</div>
			<h3><?php esc_html_e( 'Connect via messages', 'woo-refund-and-exchange-lite' ); ?></h3>
			<p><?php esc_html_e( 'Let merchants and customers communicate directly inside the order workflow before or after a refund request.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
		<div class="wps-rma-overview-card">
			<div class="wps-rma-overview-card__media">
				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Allow-And-Set-Attachments-Limit.png' ); ?>" alt="<?php esc_attr_e( 'Attachments', 'woo-refund-and-exchange-lite' ); ?>">
			</div>
			<h3><?php esc_html_e( 'Attachment control', 'woo-refund-and-exchange-lite' ); ?></h3>
			<p><?php esc_html_e( 'Accept evidence files with configurable limits so your team gets the context needed to resolve requests faster.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
		<div class="wps-rma-overview-card">
			<div class="wps-rma-overview-card__media">
				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Set-Return-Refund-Conditions.png' ); ?>" alt="<?php esc_attr_e( 'Policy conditions', 'woo-refund-and-exchange-lite' ); ?>">
			</div>
			<h3><?php esc_html_e( 'Policy-based eligibility', 'woo-refund-and-exchange-lite' ); ?></h3>
			<p><?php esc_html_e( 'Apply timelines, order status checks, and tax rules so refund eligibility follows clear business logic.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
		<div class="wps-rma-overview-card">
			<div class="wps-rma-overview-card__media">
				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Send-Email-Notification.png' ); ?>" alt="<?php esc_attr_e( 'Email notifications', 'woo-refund-and-exchange-lite' ); ?>">
			</div>
			<h3><?php esc_html_e( 'Email notifications', 'woo-refund-and-exchange-lite' ); ?></h3>
			<p><?php esc_html_e( 'Keep merchants and customers aligned through request, approval, cancellation, and message-based email updates.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
		<div class="wps-rma-overview-card">
			<div class="wps-rma-overview-card__media">
				<img src="<?php echo esc_url( WOO_REFUND_AND_EXCHANGE_LITE_DIR_URL . 'admin/image/Manage-Product-Returns.png' ); ?>" alt="<?php esc_attr_e( 'Manage product returns', 'woo-refund-and-exchange-lite' ); ?>">
			</div>
			<h3><?php esc_html_e( 'Manage product returns', 'woo-refund-and-exchange-lite' ); ?></h3>
			<p><?php esc_html_e( 'Track amounts, quantities, and follow-up activity across return-related workflows from one place.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
	</div>

	<div class="wps-rma-overview__cta">
		<div>
			<strong><?php esc_html_e( 'Facing issues?', 'woo-refund-and-exchange-lite' ); ?></strong>
			<p><?php esc_html_e( 'We are ready to help you align refund operations, customer messaging, and advanced return workflows.', 'woo-refund-and-exchange-lite' ); ?></p>
		</div>
		<div class="wps-rma-overview__cta-actions">
			<a href="<?php echo esc_url( $wrael_support_link ); ?>" target="_blank" class="wps-rma-overview__button wps-rma-overview__button--secondary"><?php esc_html_e( 'Contact Support', 'woo-refund-and-exchange-lite' ); ?></a>
		</div>
	</div>
</div>
