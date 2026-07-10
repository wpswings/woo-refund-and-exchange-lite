<?php
/**
 * User restriction (blocked/unblocked) notice email template.
 *
 * Variables available: $action ('blocked'|'unblocked'), $request_type,
 *                      $email_heading, $plain_text, $email.
 *
 * @package woo-refund-and-exchange-lite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_blocked   = 'blocked' === $action;
$status_color = $is_blocked ? '#dc2626' : '#16a34a';
$status_bg    = $is_blocked ? '#fef2f2' : '#f0fdf4';
$status_label = $is_blocked
	? esc_html__( 'ACCESS RESTRICTED', 'woo-refund-and-exchange-lite' )
	: esc_html__( 'ACCESS RESTORED', 'woo-refund-and-exchange-lite' );

if ( $plain_text ) :
	echo '= ' . esc_html( $email_heading ) . " =\n\n";

	if ( $is_blocked ) :
		/* translators: %s: request type, e.g. Refund or Exchange */
		printf( esc_html__( "Your account has been restricted from raising %s requests by the store admin. If you believe this is a mistake, please contact our support team for assistance.\n", 'woo-refund-and-exchange-lite' ), esc_html( $request_type ) );
	else :
		/* translators: %s: request type, e.g. Refund or Exchange */
		printf( esc_html__( "Good news! The restriction on your account has been lifted, and you can now raise %s requests as usual.\n", 'woo-refund-and-exchange-lite' ), esc_html( $request_type ) );
	endif;

else :
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
					<?php if ( $is_blocked ) : ?>
						<?php
						printf(
							/* translators: %s: request type, e.g. Refund or Exchange */
							esc_html__( 'Your account has been restricted from raising %s requests by the store admin. If you believe this is a mistake, please contact our support team for assistance.', 'woo-refund-and-exchange-lite' ),
							esc_html( $request_type )
						);
						?>
					<?php else : ?>
						<?php
						printf(
							/* translators: %s: request type, e.g. Refund or Exchange */
							esc_html__( 'Good news! The restriction on your account has been lifted, and you can now raise %s requests as usual.', 'woo-refund-and-exchange-lite' ),
							esc_html( $request_type )
						);
						?>
					<?php endif; ?>
				</p>

			</td>
		</tr>

		<!-- Footer -->
		<tr>
			<td style="background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e2e8f0;">
				<p style="margin:0;font-size:12px;color:#94a3b8;">
					<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
				</p>
			</td>
		</tr>

	</table>

</td></tr>
</table>

</body>
</html>
<?php endif; ?>
