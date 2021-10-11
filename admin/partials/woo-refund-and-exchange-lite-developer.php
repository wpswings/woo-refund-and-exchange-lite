<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to list all the hooks and filter with their descriptions.
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
global $wrael_mwb_rma_obj;
$wrael_developer_admin_hooks =
// Admin Hooks.
apply_filters( 'wrael_developer_admin_hooks_array', array() );
$count_admin                  = filtered_array( $wrael_developer_admin_hooks );
$wrael_developer_public_hooks =
// Admin Hooks.
apply_filters( 'wrael_developer_public_hooks_array', array() );
$count_public = filtered_array( $wrael_developer_public_hooks );
?>
<!--  template file for admin settings. -->
<div class="wrael-section-wrap">
	<div class="mwb-col-wrap">
		<div id="admin-hooks-listing" class="table-responsive mdc-data-table">
			<table class="mwb-wrael-table mdc-data-table__table mwb-table"  id="mwb-wrael-wp">
				<thead>
				<tr><th class="mdc-data-table__header-cell"><?php esc_html_e( 'Admin Hooks', 'woo-refund-and-exchange-lite' ); ?></th></tr>
				<tr>
					<th class="mdc-data-table__header-cell"><?php esc_html_e( 'Type of Hook', 'woo-refund-and-exchange-lite' ); ?></th>
					<th class="mdc-data-table__header-cell"><?php esc_html_e( 'Hooks', 'woo-refund-and-exchange-lite' ); ?></th>
					<th class="mdc-data-table__header-cell"><?php esc_html_e( 'Hooks description', 'woo-refund-and-exchange-lite' ); ?></th>
				</tr>
				</thead>
				<tbody class="mdc-data-table__content">
				<?php
				if ( ! empty( $count_admin ) ) {
					foreach ( $count_admin as $k => $v ) {
						if ( isset( $v['action_hook'] ) ) {
							?>
						<tr class="mdc-data-table__row"><td class="mdc-data-table__cell"><?php esc_html_e( 'Action Hook', 'woo-refund-and-exchange-lite' ); ?></td><td class="mdc-data-table__cell"><?php echo esc_html__( $v['action_hook'] ); ?></td><td class="mdc-data-table__cell"><?php echo esc_html__( $v['desc'] ); ?></td></tr>
							<?php
						} else {
							?>
							<tr class="mdc-data-table__row"><td class="mdc-data-table__cell"><?php esc_html_e( 'Filter Hook', 'woo-refund-and-exchange-lite' ); ?></td><td class="mdc-data-table__cell"><?php echo esc_html__( $v['filter_hook'] ); ?></td><td class="mdc-data-table__cell"><?php echo esc_html__( $v['desc'] ); ?></td></tr>
							<?php
						}
					}
				} else {
					?>
					<tr class="mdc-data-table__row"><td><?php esc_html_e( 'No Hooks Found', 'woo-refund-and-exchange-lite' ); ?><td></tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="mwb-col-wrap">
		<div id="public-hooks-listing" class="table-responsive mdc-data-table">
			<table class="mwb-wrael-table mdc-data-table__table mwb-table" id="mwb-wrael-sys">
				<thead>
				<tr><th class="mdc-data-table__header-cell"><?php esc_html_e( 'Public Hooks', 'woo-refund-and-exchange-lite' ); ?></th></tr>
				<tr>
					<th class="mdc-data-table__header-cell"><?php esc_html_e( 'Type of Hook', 'woo-refund-and-exchange-lite' ); ?></th>
					<th class="mdc-data-table__header-cell"><?php esc_html_e( 'Hooks', 'woo-refund-and-exchange-lite' ); ?></th>
					<th class="mdc-data-table__header-cell"><?php esc_html_e( 'Hooks description', 'woo-refund-and-exchange-lite' ); ?></th>
				</tr>
				</thead>
				<tbody class="mdc-data-table__content">
				<?php
				if ( ! empty( $count_public ) ) {
					foreach ( $count_public as $k => $v ) {
						if ( isset( $v['action_hook'] ) ) {
							?>
						<tr class="mdc-data-table__row"><td class="mdc-data-table__cell"><?php esc_html_e( 'Action Hook', 'woo-refund-and-exchange-lite' ); ?></td><td class="mdc-data-table__cell"><?php echo esc_html__( $v['action_hook'] ); ?></td><td class="mdc-data-table__cell"><?php echo esc_html__( $v['desc'] ); ?></td></tr>
							<?php
						} else {
							?>
							<tr class="mdc-data-table__row"><td class="mdc-data-table__cell"><?php esc_html_e( 'Filter Hook', 'woo-refund-and-exchange-lite' ); ?></td><td class="mdc-data-table__cell"><?php echo esc_html__( $v['filter_hook'] ); ?></td><td class="mdc-data-table__cell"><?php echo esc_html__( $v['desc'] ); ?></td></tr>
							<?php
						}
					}
				} else {
					?>
					<tr class="mdc-data-table__row"><td><?php esc_html_e( 'No Hooks Found', 'woo-refund-and-exchange-lite' ); ?><td></tr>
					<?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php

/**
 * Filter array
 *
 * @param array $argu .
 */
function filtered_array( $argu ) {
	$count_admin = array();
	foreach ( $argu as $key => $value ) {
		foreach ( $value as $k => $originvalue ) {
			if ( isset( $originvalue['action_hook'] ) ) {
				$val                              = explode( "'", $originvalue['action_hook'] );
				$val                              = $val[1];
				$count_admin[ $k ]['action_hook'] = $val;
			}
			if ( isset( $originvalue['filter_hook'] ) ) {
				$val                              = explode( "'", $originvalue['filter_hook'] );
				$val                              = $val[1];
				$count_admin[ $k ]['filter_hook'] = $val;
			}
			$vale                      = str_replace( '//desc - ', '', $originvalue['desc'] );
			$count_admin[ $k ]['desc'] = $vale;
		}
	}
	return $count_admin;
}

