<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the html field for general tab.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/admin/partials
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$a = get_option( 'policies_setting_option', false );
if ( empty( $a ) ) {
	$a = array(
		'mwb_rma_setting' => array(
			0 => array(
				'row_policy'           => 'mwb_rma_maximum_days',
				'row_functionality'    => 'refund',
				'row_conditions1'      => 'mwb_rma_less_than',
				'row_conditions2'      => 'mwb_rma_equal_to',
				'row_value'            => '',
				'row_tax'              => 'mwb_rma_inlcude_tax',
				'incase_functionality' => 'incase',
			),
		),
	);
}

?>
<div id="add_more_rma_policies_clone">
	<input type="hidden" value="1" class="mwb_rma_get_current_i">
	<select name="mwb_rma_setting[1][row_functionality]" class="mwb_rma_on_functionality">
		<option value="refund"><?php esc_html_e( 'Refund', 'woo-refund-and-exchange-lite' ); ?></option>
		<?php
		// To extend the functionality setting on the policies tab.
		do_action( 'mwb_rma_setting_functionality_extend1' );
		?>
	</select> 

	<select name="mwb_rma_setting[1][incase_functionality]" class="mwb_rma_settings_label">
		<option value="incase"><?php esc_html_e( 'InCase: If', 'woo-refund-and-exchange-lite' ); ?></option>
		<?php
		// To extend the functionality setting on the policies tab.
		do_action( 'mwb_rma_setting_functionality_incase_extend1' );
		?>
	</select>
	<select name="mwb_rma_setting[1][row_policy]" class="mwb_rma_settings">
		<option value=""><?php esc_html_e( 'Choose Option', 'woo-refund-and-exchange-lite' ); ?></option>
		<option value="mwb_rma_maximum_days"><?php esc_html_e( 'Maximum Days', 'woo-refund-and-exchange-lite' ); ?></option>
		<option value="mwb_rma_order_status"><?php esc_html_e( 'Order Stauses', 'woo-refund-and-exchange-lite' ); ?></option>
		<option value="mwb_rma_tax_handling"><?php esc_html_e( 'Tax Handling', 'woo-refund-and-exchange-lite' ); ?></option>
		<?php
		// To extend the setting on policies tab.
		do_action( 'mwb_rma_setting_extend1' );
		?>
	</select> 

	<label class="mwb_rma_conditions_label" ><?php esc_html_e( 'Is', 'woo-refund-and-exchange-lite' ); ?></label>
	<select name="mwb_rma_setting[1][row_conditions1]" class="mwb_rma_conditions1 mwb_rma_policy_condition">
		<option value="mwb_rma_less_than"><?php esc_html_e( 'Less than', 'woo-refund-and-exchange-lite' ); ?></option>
		<option value="mwb_rma_greater_than"><?php esc_html_e( 'Greater than', 'woo-refund-and-exchange-lite' ); ?></option>
		<option value="mwb_rma_less_than_equal"><?php esc_html_e( 'Less than equal to', 'woo-refund-and-exchange-lite' ); ?></option>
		<option value="mwb_rma_greater_than_equal"><?php esc_html_e( 'Greater than equal to', 'woo-refund-and-exchange-lite' ); ?></option>
	</select>
	<select name="mwb_rma_setting[1][row_conditions2]" class="mwb_rma_conditions2 mwb_rma_policy_condition">
		<option value="mwb_rma_equal_to"><?php esc_html_e( 'Equal to', 'woo-refund-and-exchange-lite' ); ?></option>
		<option value="mwb_rma_not_equal_to"><?php esc_html_e( 'Not Equal to', 'woo-refund-and-exchange-lite' ); ?></option>
	</select>
	<input type="number" name="mwb_rma_setting[1][row_value]" class="mwb_rma_max_number_days" placeholder="<?php esc_html_e( 'Enter the max number of days for refund', 'woo-refund-and-exchange-lite' ); ?>">

	<select name="mwb_rma_setting[1][row_statuses][]" class="mwb_rma_order_statues1" multiple>
		<?php
			$statuss = wc_get_order_statuses();
			$statuss =
			// To remove the unwanted order status.
			apply_filters( 'mwb_rma_unset_unsed_statuses', $statuss );
		?>
		<?php foreach ( $statuss as $key => $statuss ) : ?>
			<option value="<?php echo esc_html( $key ); ?>" <?php echo isset( $value['row_statuses'] ) ? ( in_array( $key, $value['row_statuses'], true ) ? 'selected' : '' ) : ''; ?>><?php echo esc_html( $statuss ); ?></option>
		<?php endforeach; ?>
	</select> 
	<select name="mwb_rma_setting[1][row_tax]" class="mwb_rma_tax_handling">
		<option value="mwb_rma_inlcude_tax"><?php esc_html_e( 'Include Tax', 'woo-refund-and-exchange-lite' ); ?></option>
		<option value="mwb_rma_exclude_tax"><?php esc_html_e( 'Exclude Tax', 'woo-refund-and-exchange-lite' ); ?></option>
	</select>
	<?php
	// Add More Setting.
	do_action( 'add_more_setting_value1' );
	?>
</div>
<form action="" method="post" id="save_policies_setting_form">
	<input type="hidden" name="get_nonce" value="<?php echo esc_html( wp_create_nonce( 'create_form_nonce' ) ); ?>">
	<div id="div_add_more_rma_policies">
		<?php
		$count = 1;
		foreach ( $a['mwb_rma_setting'] as $key => $value ) :
			if ( ! mwb_rma_pro_active() ) {
				if ( 'refund' !== $value['row_functionality'] ) {
					continue;
				}
			}
			?>
			<div class="add_more_rma_policies">
				<select name="mwb_rma_setting[<?php echo esc_html( $count ); ?>][row_functionality]" class="mwb_rma_on_functionality">
					<option value="refund" <?php selected( 'refund', $value['row_functionality'] ); ?>><?php esc_html_e( 'Refund', 'woo-refund-and-exchange-lite' ); ?></option>
					<?php
					// To extend the functionality setting on the policies tab.
					do_action( 'mwb_rma_setting_functionality_extend2', $value['row_functionality'] );
					?>
				</select>
				<input type="hidden" value="<?php echo esc_html( $count ); ?>" class="mwb_rma_get_current_i">

				<select name="mwb_rma_setting[<?php echo esc_html( $count ); ?>][incase_functionality]" class="mwb_rma_settings_label">
					<option value="incase" <?php selected( 'incase', $value['incase_functionality'] ); ?>><?php esc_html_e( 'InCase: If', 'woo-refund-and-exchange-lite' ); ?></option>
					<?php
					// To extend the functionality setting on the policies tab.
					do_action( 'mwb_rma_setting_functionality_incase_extend2', $value['incase_functionality'] );
					?>
				</select> 
				<select name="mwb_rma_setting[<?php echo esc_html( $count ); ?>][row_policy]" class="mwb_rma_settings">
					<option value=""><?php esc_html_e( 'Choose Option', 'woo-refund-and-exchange-lite' ); ?></option>	
					<option value="mwb_rma_maximum_days" <?php selected( 'mwb_rma_maximum_days', $value['row_policy'] ); ?>><?php esc_html_e( 'Maximum Days', 'woo-refund-and-exchange-lite' ); ?></option>
					<option value="mwb_rma_order_status" <?php selected( 'mwb_rma_order_status', $value['row_policy'] ); ?>><?php esc_html_e( 'Order Stauses', 'woo-refund-and-exchange-lite' ); ?></option>
					<option value="mwb_rma_tax_handling" <?php selected( 'mwb_rma_tax_handling', $value['row_policy'] ); ?>><?php esc_html_e( 'Tax Handling', 'woo-refund-and-exchange-lite' ); ?></option>
					<?php
					// To extend the setting on policies tab.
					do_action( 'mwb_rma_setting_extend2', $value['row_policy'] );
					?>
				</select>

				<label class="mwb_rma_conditions_label" ><?php esc_html_e( 'is', 'woo-refund-and-exchange-lite' ); ?></label>
				<select name="mwb_rma_setting[<?php echo esc_html( $count ); ?>][row_conditions1]" class="mwb_rma_conditions1 mwb_rma_policy_condition">
					<option value="mwb_rma_less_than" <?php selected( 'mwb_rma_less_than', $value['row_conditions1'] ); ?>><?php esc_html_e( 'Less than', 'woo-refund-and-exchange-lite' ); ?></option>
					<option value="mwb_rma_greater_than" <?php selected( 'mwb_rma_greater_than', $value['row_conditions1'] ); ?>><?php esc_html_e( 'Greater than', 'woo-refund-and-exchange-lite' ); ?></option>
					<option value="mwb_rma_less_than_equal" <?php selected( 'mwb_rma_less_than_equal', $value['row_conditions1'] ); ?>><?php esc_html_e( 'Less than equal to', 'woo-refund-and-exchange-lite' ); ?></option>
					<option value="mwb_rma_greater_than_equal" <?php selected( 'mwb_rma_greater_than_equal', $value['row_conditions1'] ); ?>><?php esc_html_e( 'Greater than equal to', 'woo-refund-and-exchange-lite' ); ?></option>
				</select>
				<select name="mwb_rma_setting[<?php echo esc_html( $count ); ?>][row_conditions2]" class="mwb_rma_conditions2 mwb_rma_policy_condition">
					<option value="mwb_rma_equal_to" <?php selected( 'mwb_rma_equal_to', $value['row_conditions2'] ); ?>><?php esc_html_e( 'Equal to', 'woo-refund-and-exchange-lite' ); ?></option>
					<option value="mwb_rma_not_equal_to" <?php selected( 'mwb_rma_not_equal_to', $value['row_conditions2'] ); ?>><?php esc_html_e( 'Not Equal to', 'woo-refund-and-exchange-lite' ); ?></option>
				</select>
				<input type="number" name="mwb_rma_setting[<?php echo esc_html( $count ); ?>][row_value]" class="mwb_rma_max_number_days" placeholder="<?php esc_html_e( 'Enter the max number of days for refund', 'woo-refund-and-exchange-lite' ); ?>" value="<?php echo isset( $value['row_value'] ) ? esc_html( $value['row_value'] ) : ''; ?>">
				<?php
				// Add More Setting.
				do_action( 'add_more_setting_value2', $value, $count );
				?>
				<select name="mwb_rma_setting[<?php echo esc_html( $count ); ?>][row_statuses][]" class="mwb_rma_order_statues" multiple>
					<?php
					$statuss = wc_get_order_statuses();
					$statuss =
					// To remove the unwanted order status.
					apply_filters( 'mwb_rma_unset_unsed_statuses', $statuss );
					?>
					<?php foreach ( $statuss as $key => $statuss ) : ?>
						<option value="<?php echo esc_html( $key ); ?>" <?php echo isset( $value['row_statuses'] ) ? ( in_array( $key, $value['row_statuses'], true ) ? 'selected' : '' ) : ''; ?>><?php echo esc_html( $statuss ); ?></option>
					<?php endforeach; ?>
				</select>
				<select name="mwb_rma_setting[<?php echo esc_html( $count++ ); ?>][row_tax]" class="mwb_rma_tax_handling">
					<option value="mwb_rma_inlcude_tax" <?php selected( 'mwb_rma_inlcude_tax', $value['row_tax'] ); ?>><?php esc_html_e( 'Include Tax', 'woo-refund-and-exchange-lite' ); ?></option>
					<option value="mwb_rma_exclude_tax" <?php selected( 'mwb_rma_exclude_tax', $value['row_tax'] ); ?>><?php esc_html_e( 'Exclude Tax', 'woo-refund-and-exchange-lite' ); ?></option>
				</select>
				<?php if ( 2 !== $count ) : ?>
				<input type="button" value="X" class="rma_policy_delete">
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<br><br>
<input type="button" value="Add More" id="mwb_rma_add_more">
<input type="submit" name="save_policies_setting" value="Save Setting" class="mwb_rma_save_settings">
</form>