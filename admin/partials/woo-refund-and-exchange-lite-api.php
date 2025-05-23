<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for general tab.
 *
 * @link       https://wpswings.com/
 * @since      1.0.0
 *
 * @package    woo-refund-and-exchange-lite
 * @subpackage woo-refund-and-exchange-lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wrael_wps_rma_obj;
$wrael_api_settings =
// The General Settings.
apply_filters( 'wps_rma_api_settings_array', array() );
?>
<!--  template file for admin settings. -->
<form action="" method="POST" class="wps-wrael-gen-section-form">
	<div class="wrael-secion-wrap">
		<?php
		$wrael_api_html = $wrael_wps_rma_obj->wps_rma_plug_generate_html( $wrael_api_settings );
		echo esc_html( $wrael_api_html );
		wp_nonce_field( 'admin_save_data', 'wps_tabs_nonce' );
		?>
	</div>
</form>
<style>
	.wps-rma-api-container p {
		margin-bottom: 15px;
	}

	.wps-rma-api-container code, .wps-rma-api-container pre {
		background: #f0f0f0;
		color: #505050;
		padding: 10px;
		display: block;
		border-radius: 6px;
		font-size: 14px;
		overflow-x: auto;
		margin: 15px 0;
	}
	.wps-rma-api-container pre {
		border: 1px solid #004299;
		background: transparent;
=
	}

	.wps-rma-api-container .json-response {
		background-color: #fefefe;
		padding: 12px;
		font-family: Consolas, monospace;
		font-size: 14px;
		border-radius: 5px;
		margin: 10px;
		border: 1px solid #198d00;
		border-left: 4px solid #198d00;
	}

	.wps-rma-api-container .error {
		border-color: #e53935;
		background-color: #fff5f5;
	}

	.wps-rma-api-container details {
		background: #f9f9f9;
		border: 1px solid #ddd;
		border-radius: 8px;
		margin-bottom: 15px;
		padding: 15px;
	}

	.wps-rma-api-container summary {
		font-weight: bold;
		font-size: 16px;
		cursor: pointer;
		outline: none;
	}

	.wps-rma-api-container summary::-webkit-details-marker {
		display: none;
	}
	.wps-rma-api-container span {
		color: black;
		border-radius: 5px;
		background-color: lightgrey;
		padding: 6px;
		margin-right: 10px;
		display: inline-block;
	}
	.rma-api-section > *:not(h2) {
		display: none;
	}
	.rma-api-section.open > * {
		display: block;
	}

	@media screen and (max-width: 600px) {

		.wps-rma-api-container .container {
		padding: 20px;
		}
	}
	.wps-rma-api-container h2 {
		cursor: pointer;
		background-color: #f9f9f9;
		padding: 10px 16px;
		margin: 0;
		position: relative;
		font-size: 18px;
		border: 1px solid #ddd;
		transition: background-color 0.3s;
		border-radius: 5px;
		margin-bottom: 15px;
	}

	.wps-rma-api-container h2::after {
		content: '▸'; /* Right-pointing arrow */
		position: absolute;
		right: 16px;
		top: 50%;
		transform: translateY(-50%);
		font-size: 18px;
		transition: transform 0.3s;
	}
	.wps-rma-api-container .open h2 {
		transition: background-color 0.6s;
		background-color:rgb(199, 199, 199);
	}

	.wps-rma-api-container .open h2::after {
		content: '▾';
	}

</style>
  <div class="wps-rma-api-container">

	<div class="rma-api-section">
	  <h2><?php esc_html_e( 'Refund Request', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to make a refund request on an order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/refund-request' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>
<pre>
curl -X POST https://example.com/wp-json/rma/refund-request \
-u "secret_key:secret_key_val" \
-H "Content-Type: application/json" \
-d '{
  "order_id": "order_id_val",
  "reason": "reason for refund request"
}'
</pre>
	  <details open>
		<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
		<div class="json-response">
			{
			"status": "success",
			"code": 200,
			"message": "Refund request send successfully"
			}
		</div>
	  </details>
	  <details>
		<summary><?php esc_html_e( 'Error Responses', 'woo-refund-and-exchange-lite' ); ?></summary>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "Please Provide the correct order id to perform the process" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "Return Request Already has been made and accepted" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "Some problem occur while refund requesting" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "Please provide the refund reason" }
		</div>
	  </details>
	</div>
	<?php if ( function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active() ) : ?>
	<div class="rma-api-section">
	  <h2><?php esc_html_e( 'Partial Refund Request', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to make a partial refund request on an order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/refund-request' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>
<pre>
curl -X POST https://example.com/wp-json/rma/refund-request \
-u "secret_key: secret_key_val" \
-H "Content-Type: application/json" \
-d '{
"order_id": "order_id_val",
"refund_items": [
	{ "product_id": product_id_val, "qty": qty_val },
	{ "variation_id": variation_id_val, "qty": qty_val }
],
"reason": "reason for refund request",
"refund_method": <span title="wallet_method or manual_method">refund_method_val</span>
}'
</pre>

	  <details open>
		<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
		<div class="json-response">
			{
			"status": "success",
			"code": 200,
			"message": "Refund request send successfully"
			}
		</div>
	  </details>

	  <details>
		<summary><?php esc_html_e( 'Error Responses', 'woo-refund-and-exchange-lite' ); ?></summary>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "Please Provide the correct order id to perform the process" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "Refund request already has been made and accepted for the items you have given" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "Quantity given for items is greater than the order’s items quantity" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "Some problem occur while refund requesting" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "These item id does not belongs to the order" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "please give the item ids which needs to be refund" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "please give the item quantity which needs to be refund" }
		</div>
		<div class="json-response error">
			{ "status": "error", "code": 404, "message": "please give the correct products json format" }
		</div>
	  </details>
	</div>
	<?php endif; ?>
	<div class="rma-api-section">
	  <h2><?php esc_html_e( 'Refund Request Accept', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to accept pending refund requests on an order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/refund-request-accept' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>
<pre>
curl -X POST https://example.com/wp-json/rma/refund-request-accept \
-u "secret_key: secret_key_val" \
-H "Content-Type: application/json" \
-d '{
"order_id": "order_id_val"
}'
</pre>

		<details open>
			<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response">
				{
				"status": "success",
				"code": 200,
				"message": "Return Request Accepted Successfully"
				}
			</div>
		</details>

		<details>
			<summary><?php esc_html_e( 'Error Responses', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Some problem occur while refund request accepting" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "You can only perform the refund request accept when request has been made earlier" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please Provide the correct order id to perform the process" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "You have already perform the accept the request" }
			</div>
		</details>
	</div>
  	<div class="rma-api-section">
	  <h2><?php esc_html_e( 'Refund Request Cancel', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to cancel pending refund requests on an order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/refund-request-cancel' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>
<pre>
curl -X POST https://example.com/wp-json/rma/refund-request-cancel \
-u "secret_key: secret_key_val" \
-H "Content-Type: application/json" \
-d '{
"order_id": "order_id_val"
}'
</pre>

		<details open>
			<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response">
		{
		"status": "success",
		"code": 200,
		"message": "Return Request Cancel Successfully"
		}
			</div>
		</details>

		<details>
			<summary><?php esc_html_e( 'Error Responses', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response error">
		{ "status": "error", "code": 404, "message": "Some problem occur while refund request cancelling" }
			</div>
			<div class="json-response error">
		{ "status": "error", "code": 404, "message": "You can only perform the refund request cancel when request has been made earlier" }
			</div>
			<div class="json-response error">
		{ "status": "error", "code": 404, "message": "Please Provide the correct order id to perform the process" }
			</div>
			<div class="json-response error">
		{ "status": "error", "code": 404, "message": "You have already perform the cancel the request" }
			</div>
		</details>
	</div>
	<?php if ( function_exists( 'wps_rma_pro_active' ) && wps_rma_pro_active() ) : ?>

	<div class="rma-api-section">
	  <h2><?php esc_html_e( 'Exchange Request', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to make an exchange request on an order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/exchange-request' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>
<pre>
curl -X POST https://example.com/wp-json/rma/exchange-request \
-u "secret_key: secret_key_val" \
-H "Content-Type: application/json" \
-d '{
"order_id": "order_id_val",
"exchange_data": "
{
"from": [
	{"product_id": product_id_val, "qty": qty_val},
	{"variation_id": variation_id_val, "qty": qty_val}
],
"to": [
	{"variation_id": variation_id_val, "qty": qty_val},
	{"product_id": product_id_val, "qty": qty_val}
]
}",
"reason": "reason for exchange request",
"refund_method": <span title="wallet_method or manual_method">refund_method_val</span>
}'
</pre>

		<details open>
			<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response">
				{
				"status": "success",
				"code": 200,
				"message": "Exchange request send successfully"
				}
			</div>
		</details>

		<details>
			<summary>Error Responses</summary>

			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Exchange request already has been made and accepted for the items you have given" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please enter the correct variation id to continue the process" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please enter the correct product id to continue the process" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "These item id does not belongs to the order" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please give the item ids which needs to be exchange" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please give the item qty which needs to be exchange" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please Provide the reason for exchange" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Quantity given for the items is greater than the order’s items quantity" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please Enter the variations to continue the process" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "There is wrong details given in the from products exchange" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "There is wrong details given in the to products exchange" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please Enter the correct order id to perform the process" }
			</div>
		</details>
	</div>
  <div class="rma-api-section">
      <h2><?php esc_html_e( 'Exchange Request Accept', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to accept the pending exchange request on an order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/exchange-request-accept' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>

<pre>
curl -X POST https://example.com/wp-json/rma/exchange-request-accept \
-u "secret_key: secret_key_val" \
-H "Content-Type: application/json" \
-d '{
"order_id": "order_id_val"
}'
</pre>

		<details open>
			<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response">
			{
			"status": "success",
			"code": 200,
			"message": "Exchange request accept successfully"
			}
			</div>
		</details>

		<details>
			<summary><?php esc_html_e( 'Error Responses', 'woo-refund-and-exchange-lite' ); ?></summary>

			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Some problem occur while exchange request accepting" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "You can only perform the exchange request accept when the request has been made earlier" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please Provide the correct order_id to perform the process" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "You have approved the exchange request already" }
			</div>
		</details>
	</div>

	<div class="rma-api-section">
	   <h2><?php esc_html_e( 'Exchange Request Cancel', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to cancel the pending exchange request on an order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/exchange-request-cancel' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>
<pre>
curl -X POST https://example.com/wp-json/rma/exchange-request-cancel \
-u "secret_key: secret_key_val" \
-H "Content-Type: application/json" \
-d '{
"order_id": "order_id_val"
}'
</pre>

		<details open>
			<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response">
				{
				"status": "success",
				"code": 200,
				"message": "Exchange request cancel successfully"
				}
			</div>
		</details>

		<details>
			<summary><?php esc_html_e( 'Error Responses', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Some problem occur while exchange request cancelling" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "You can only perform the exchange request cancel when the request has been made earlier" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please Provide the correct order_id to perform the process" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "You have cancelled the exchange request already" }
			</div>
		</details>
	</div>
	<div class="rma-api-section">
	<h2><?php esc_html_e( 'Order Cancel', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to cancel the order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/cancel-request' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>
<pre>
curl -X POST https://example.com/wp-json/rma/cancel-request \
-u "secret_key: secret_key_val" \
-H "Content-Type: application/json" \
-d '{
"order_id": "order_id_val",
}'
</pre>

		<details open>
			<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response">
				{
				"status": "success",
				"code": 200,
				"message": "The order is cancelled"
				}
			</div>
		</details>

		<details>
			<summary><?php esc_html_e( 'Error Responses', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Some problem occur while order cancelling" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please provide the correct order id to perform the process" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "This order is already cancelled" }
			</div>
		</details>
	</div>

	<div class="rma-api-section">
	<h2><?php esc_html_e( 'Partial Order Cancel', 'woo-refund-and-exchange-lite' ); ?></h2>
	  <p><?php esc_html_e( 'This API is used to partial cancel an order', 'woo-refund-and-exchange-lite' ); ?></p>
	  <strong><?php esc_html_e( 'HTTP Request', 'woo-refund-and-exchange-lite' ); ?></strong>
	  <code><span><?php esc_html_e( 'Method', 'woo-refund-and-exchange-lite' ); ?></span>POST</code> 
	  <code> <span><?php esc_html_e( 'Base Url', 'woo-refund-and-exchange-lite' ); ?></span><?php echo site_url( '/wp-json/rma/cancel-request' ); ?></code>
	  <strong><?php esc_html_e( 'cURL Example', 'woo-refund-and-exchange-lite' ); ?></strong>
<pre>
curl -X POST https://example.com/wp-json/rma/cancel-request \
-u "secret_key: secret_key_val" \
-H "Content-Type: application/json" \
-d '{
"order_id": "order_id_val",
"cancel_data": "[
	{ "product_id": product_id_val, "qty": qty_val },
	{ "variation_id": variation_id_val, "qty": qty_val }
]"
}'
</pre>

		<details open>
			<summary><?php esc_html_e( 'Successful JSON Response', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response">
				{
				"status": "success",
				"code": 200,
				"message": "The order partially has been cancelled"
				}
			</div>
		</details>

		<details>
			<summary><?php esc_html_e( 'Error Responses', 'woo-refund-and-exchange-lite' ); ?></summary>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Some problem occur while order cancelling" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "The item quantity must be less than order’s item quantity" }
			</div>
			<div class="json-response error">
				{ "status": "error", "code": 404, "message": "Please provide the correct order id to perform the process" }
			</div>
		</details>
	</div>
	<?php endif; ?>
</div>
