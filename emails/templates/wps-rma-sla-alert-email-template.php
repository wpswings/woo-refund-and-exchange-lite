<?php
/**
 * SLA alert email template.
 *
 * Variables available: $order_id, $request_type, $hours_remaining, $deadline,
 *                      $email_heading, $plain_text, $email.
 *
 * @package woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $plain_text ) :
	echo "= " . esc_html( $email_heading ) . " =\n\n";

	if ( $hours_remaining <= 0 ) :
		/* translators: 1: request type, 2: order ID */
		printf( esc_html__( "OVERDUE: The %1\$s request for Order #%2\$s has passed its resolution deadline.\n\n", 'woo-refund-and-exchange-lite' ), esc_html( $request_type ), esc_html( $order_id ) );
	else :
		/* translators: 1: request type, 2: order ID, 3: hours remaining */
		printf( esc_html__( "WARNING: The %1\$s request for Order #%2\$s has %3\$s hours remaining before the resolution deadline.\n\n", 'woo-refund-and-exchange-lite' ), esc_html( $request_type ), esc_html( $order_id ), esc_html( (int) ceil( $hours_remaining ) ) );
	endif;

	/* translators: %s: formatted deadline date/time */
	printf( esc_html__( "Resolution Deadline: %s\n\n", 'woo-refund-and-exchange-lite' ), esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $deadline ) ) );

	$order_url = admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' );
	/* translators: %s: order edit URL */
	printf( esc_html__( "Review the order: %s\n", 'woo-refund-and-exchange-lite' ), esc_url( $order_url ) );

else :

	$is_overdue    = $hours_remaining <= 0;
	$status_color  = $is_overdue ? '#dc2626' : '#d97706';
	$status_bg     = $is_overdue ? '#fef2f2' : '#fffbeb';
	$status_label  = $is_overdue
		? esc_html__( 'OVERDUE', 'woo-refund-and-exchange-lite' )
		: sprintf(
			/* translators: %d: hours remaining */
			esc_html__( '%dh remaining', 'woo-refund-and-exchange-lite' ),
			(int) ceil( $hours_remaining )
		);

	$order_url     = admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' );
	$deadline_fmt  = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $deadline );
	?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo esc_html( $email_heading ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:32px 0;">
<tr><td align="center">

	<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">

		<!-- Header -->
		<tr>
			<td style="background:#1e293b;padding:28px 32px;text-align:center;">
				<h1 style="margin:0;color:#fff;font-size:20px;font-weight:700;letter-spacing:-.01em;">
					<?php echo esc_html( $email_heading ); ?>
				</h1>
			</td>
		</tr>

		<!-- Status banner -->
		<tr>
			<td style="background:<?php echo esc_attr( $status_bg ); ?>;padding:16px 32px;text-align:center;border-bottom:2px solid <?php echo esc_attr( $status_color ); ?>;">
				<span style="font-size:15px;font-weight:700;color:<?php echo esc_attr( $status_color ); ?>;">
					<?php echo esc_html( $status_label ); ?>
				</span>
			</td>
		</tr>

		<!-- Body -->
		<tr>
			<td style="padding:32px;">

				<p style="margin:0 0 20px;font-size:15px;color:#374151;line-height:1.6;">
					<?php if ( $is_overdue ) : ?>
						<?php
						printf(
							/* translators: 1: request type, 2: linked order ID */
							wp_kses(
								__( 'The <strong>%1$s</strong> request for <strong>Order %2$s</strong> has <strong style="color:#dc2626;">passed its resolution deadline</strong> and requires immediate attention.', 'woo-refund-and-exchange-lite' ),
								array( 'strong' => array( 'style' => array() ) )
							),
							esc_html( $request_type ),
							'<a href="' . esc_url( $order_url ) . '" style="color:#2563eb;">#' . esc_html( $order_id ) . '</a>'
						);
						?>
					<?php else : ?>
						<?php
						printf(
							/* translators: 1: request type, 2: linked order ID, 3: hours count */
							wp_kses(
								__( 'The <strong>%1$s</strong> request for <strong>Order %2$s</strong> will reach its resolution deadline in <strong style="color:#d97706;">%3$d hour(s)</strong>.', 'woo-refund-and-exchange-lite' ),
								array( 'strong' => array( 'style' => array() ) )
							),
							esc_html( $request_type ),
							'<a href="' . esc_url( $order_url ) . '" style="color:#2563eb;">#' . esc_html( $order_id ) . '</a>',
							(int) ceil( $hours_remaining )
						);
						?>
					<?php endif; ?>
				</p>

				<!-- Detail table -->
				<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;margin:0 0 24px;">
					<tr>
						<td style="padding:12px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e2e8f0;width:40%;">
							<?php esc_html_e( 'Order ID', 'woo-refund-and-exchange-lite' ); ?>
						</td>
						<td style="padding:12px 16px;font-size:13px;color:#1e293b;font-weight:600;border-bottom:1px solid #e2e8f0;">
							#<?php echo esc_html( $order_id ); ?>
						</td>
					</tr>
					<tr>
						<td style="padding:12px 16px;font-size:13px;color:#64748b;border-bottom:1px solid #e2e8f0;">
							<?php esc_html_e( 'Request Type', 'woo-refund-and-exchange-lite' ); ?>
						</td>
						<td style="padding:12px 16px;font-size:13px;color:#1e293b;font-weight:600;border-bottom:1px solid #e2e8f0;">
							<?php echo esc_html( $request_type ); ?>
						</td>
					</tr>
					<tr>
						<td style="padding:12px 16px;font-size:13px;color:#64748b;">
							<?php esc_html_e( 'Resolution Deadline', 'woo-refund-and-exchange-lite' ); ?>
						</td>
						<td style="padding:12px 16px;font-size:13px;color:<?php echo esc_attr( $status_color ); ?>;font-weight:600;">
							<?php echo esc_html( $deadline_fmt ); ?>
						</td>
					</tr>
				</table>

				<!-- CTA button -->
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td align="center">
							<a href="<?php echo esc_url( $order_url ); ?>"
							   style="display:inline-block;padding:12px 28px;background:#1e293b;color:#fff;font-size:14px;font-weight:600;text-decoration:none;border-radius:6px;letter-spacing:-.01em;">
								<?php esc_html_e( 'View Order', 'woo-refund-and-exchange-lite' ); ?>
							</a>
						</td>
					</tr>
				</table>

			</td>
		</tr>

		<!-- Footer -->
		<tr>
			<td style="background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e2e8f0;">
				<p style="margin:0;font-size:12px;color:#94a3b8;">
					<?php echo esc_html( get_bloginfo( 'name' ) ); ?> &mdash;
					<?php esc_html_e( 'RMA Resolution Deadline Monitoring', 'woo-refund-and-exchange-lite' ); ?>
				</p>
			</td>
		</tr>

	</table>

</td></tr>
</table>

</body>
</html>
<?php endif; ?>
