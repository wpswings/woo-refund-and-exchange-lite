<?php
if(!class_exists('MwbBasicframeworkAdminSettings')){
	class MwbBasicframeworkAdminSettings{

		protected $loader;

		public function __construct(){

			self::loadDependencies();
		}

		public function loadDependencies(){

			add_submenu_page( 'woocommerce', __('RAE Configuration','woocommerce-refund-and-exchange-lite'), __('RAE Configuration','woocommerce-refund-and-exchange-lite'), 'manage_options', 'ced-rnx-notification', array( $this, 'ced_rnx_notification_callback' ));
			add_meta_box('ced_rnx_order_refund', __('Refund Requested Products','woocommerce-refund-and-exchange-lite'), array($this, 'ced_rnx_order_return'), 'shop_order');
			
			$this->id = 'ced_rnx_setting';
			
			add_filter( 'woocommerce_settings_tabs_array', array($this,'ced_rnx_add_settings_tab'), 50 );
			add_action( 'woocommerce_settings_tabs_' . $this->id, array($this,'ced_rnx_settings_tab') );
			add_action( 'woocommerce_sections_' . $this->id, array( $this, 'ced_rnx_output_sections' ) );
			add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'ced_rnx_setting_save' ) );
		}

	 	/**
		 * Add new tab to woocommerce setting
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
	 	public static function ced_rnx_add_settings_tab( $settings_tabs ) {
	 		$settings_tabs['ced_rnx_setting'] = __( 'RAE Setting', 'woocommerce-settings-tab-demo' );
	 		return $settings_tabs;
	 	}
	 	
	 	public function ced_rnx_settings_tab() 
	 	{
	 		global $current_section;
	 		woocommerce_admin_fields( self::ced_rnx_get_settings($current_section) );
	 		if(isset($_GET['section']))
			{
				if(sanitize_text_field($_GET['section']) !='refund')
				{
					include_once MWB_REFUND_N_EXCHANGE_LITE_DIRPATH.'admin/partials/mwb-rnx-lite-pro-purchase-template.php';
					?>
					<style type="text/css">
						.button-primary.woocommerce-save-button {
						    display: none;
						}
						.ced_rnx_help_wrapper {
							margin-top: 20px;
							width: 100%;
						}
					</style>
					<?php
				}
			}
	 	}

		/**
		 * Output of section setting 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_output_sections() {

			global $current_section;
			$sections = $this->ced_rnx_get_sections();

			echo '<ul class="subsubsub">';

			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}
			echo '<li> | <a href="'.admin_url().'admin.php?page=ced-rnx-notification">'.__('Mail Configuration','woocommerce-refund-and-exchange-lite').'</a></li>';			
			echo '</ul><br class="clear ced_rnx_clear"/>';
		}

		/**
		 * Create section setting 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_get_sections() {

			$sections = array(
				'refund'             	=>  __( 'Refund Products', 'woocommerce-refund-and-exchange-lite' ),
				'exchange'              =>  __( 'Exchange Products', 'woocommerce-refund-and-exchange-lite' ),
				'other'     	=>  __( 'Common Setting', 'woocommerce-refund-and-exchange-lite' ),
				'cancel'	   	=>  __( 'Cancel Order', 'woocommerce-refund-and-exchange-lite' ),	
				'text_setting'  =>  __( 'Text Settings' , 'woocommerce-refund-and-exchange-lite' ),
				'catalog_setting'=> __('Catalog Settings', 'woocommerce-refund-and-exchange-lite'),

				);

			return apply_filters( 'ced_rnx_get_sections_' . $this->id, $sections );
		}

		/**
		 * Section setting
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_get_settings($current_section) {

			/* get woocommerce categories */

			$all_cat = get_terms('product_cat',array('hide_empty'=>0));
			$cat_name = array();
			if($all_cat){
				foreach ($all_cat as $cat){

					$cat_name[$cat->term_id] = $cat->name;

				}
			}

			$statuses = wc_get_order_statuses();
			$status=$statuses;
			if ( 'refund' == $current_section || $current_section == '') 
	    	{
				$settings = array(

					array(
						'title' => __( 'Refund Products Setting', 'woocommerce-refund-and-exchange-lite' ),
						'type' 	=> 'title',
						),

					array(
						'title'         => __( 'Enable', 'woocommerce-refund-and-exchange-lite' ),
						'desc'          => __( 'Enable Refund Request', 'woocommerce-refund-and-exchange-lite' ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'id' 		=> 'ced_rnx_return_enable'
						),

					array(
						'title'         => __( 'Include Tax', 'woocommerce-refund-and-exchange-lite' ),
						'desc'          => __( 'Include Tax with Product Refund Request.', 'woocommerce-refund-and-exchange-lite' ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'id' 		=> 'ced_rnx_return_tax_enable'
						),

					array(
						'title'         => __( 'Maximum Number of Days', 'woocommerce-refund-and-exchange-lite' ),
						'desc'          => __( 'If days exceeds from the day of order delivered then Refund Request will not be send. If value is 0 or blank then Refund button will not visible at order detail page.', 'woocommerce-refund-and-exchange-lite' ),
						'type'          => 'number',
						'custom_attributes'   => array('min'=>'0'),
						'id' 		=> 'ced_rnx_return_days'
						),
					array(
						'title'         => __( 'Enable Attachment on Request Form', 'woocommerce-refund-and-exchange-lite' ),
						'desc'          => __( 'Enable this for user to send the attachment. User can attach <i>.png, .jpg, .jpeg</i> type files.', 'woocommerce-refund-and-exchange-lite' ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'id' 		=> 'ced_rnx_return_attach_enable'
						),
					array(
						'title'         => __( 'Enable Refund Reason Description', 'woocommerce-refund-and-exchange-lite' ),
						'desc'          => __( 'Enable this for user to send the detail description of Refund request.', 'woocommerce-refund-and-exchange-lite' ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'id' 		=> 'ced_rnx_return_request_description'
						),
					array(
						'title'         => __( 'Enable Manage Stock', 'woocommerce-refund-and-exchange-lite' ),
						'desc'          => __( 'Enable this to increase product stock when Refund request is accepted.', 'woocommerce-refund-and-exchange-lite' ),
						'default'       => 'no',
						'type'          => 'checkbox',
						'id' 		=> 'ced_rnx_return_request_manage_stock'
						),
					array(
						'title'    => __( 'Select the orderstatus in which the order can be Refunded', 'woocommerce-refund-and-exchange-lite' ),
						'desc'     => __( 'Select Order status on which you want Refund request user can submit.', 'woocommerce-refund-and-exchange-lite' ),
						'class'    => 'wc-enhanced-select ',
						'css'      => 'min-width:300px;',
						'default'  => '',
						'type'     => 'multiselect',
						'options'  => $status,
						'desc_tip' =>  true,
						'id' 		=> 'ced_rnx_return_order_status'
						),
					array(
						'type' 	=> 'sectionend',
						),

					);
			}
			else
			{
				$settings = array(
					array(
						'type' 	=> 'sectionend',
						'class' => 'ced_rnx_test',
						),
					);
			}
			return apply_filters( 'ced_rnx_get_settings_exchange' . $this->id, $settings );

		}

		 /**
	     * Save setting
	     * @author makewebbetter<webmaster@makewebbetter.com>
	     * @link http://www.makewebbetter.com/
	     */
	    public function ced_rnx_setting_save() {
	    	global $current_section;
	    	$settings = $this->ced_rnx_get_settings( $current_section );
	    	WC_Admin_Settings::save_fields( $settings );
	    }

		/**
		 * Add notification submenu in woocommerce
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 */
		public function ced_rnx_notification_callback()
		{
			include_once MWB_REFUND_N_EXCHANGE_LITE_DIRPATH.'admin/partials/mwb-rnx-lite-notification.php';;
		}

		/**
		 * This function is metabox template for Refund order product
		 * 
		 * @author makewebbetter<webmaster@makewebbetter.com>
		 * @link http://www.makewebbetter.com/
		 * @param unknown $order
		 */
		public function ced_rnx_order_return()
		{
			global $post, $thepostid, $theorder;
			include_once MWB_REFUND_N_EXCHANGE_LITE_DIRPATH.'admin/partials/mwb-rnx-lite-return-product-meta.php';
		}
	}
}
new MwbBasicframeworkAdminSettings;