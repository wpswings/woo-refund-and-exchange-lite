<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       www.makewebbetter.com
 * @since      1.0.0
 *
 * @package    Mwb_Rma
 * @subpackage Mwb_Rma/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mwb_Rma
 * @subpackage Mwb_Rma/public
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Mwb_Rma_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mwb_Rma_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mwb_Rma_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mwb-rma-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mwb_Rma_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mwb_Rma_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mwb-rma-public.js', array( 'jquery' ), $this->version, false );
		$ajax_nonce = wp_create_nonce( "mwb-rma-ajax-security-string" );
		$translation_array = array(
			'attachment_msg'		=> __( 'File should be of .png , .jpg, or .jpeg extension' , 'mwb-rma'),
			'return_subject_msg' 	=> __( 'Please enter refund subject.', 'mwb-rma' ),
			'return_reason_msg'		=> __( 'Please enter refund reason.', 'mwb-rma' ),
			'mwb_rma_nonce'			=>	$ajax_nonce,
			'ajaxurl' 				=> admin_url('admin-ajax.php'),
		);
		wp_localize_script(  $this->plugin_name, 'global_mwb_rma', $translation_array );

	}

	/**
	 *  Add template for refund request form.
	 * @param $template
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	public function mwb_rma_product_return_template($template){
		
		$mwb_rma_return_request_form_page_id = get_option('mwb_rma_return_request_form_page_id');
		if(is_page($mwb_rma_return_request_form_page_id))
		{

			$located = locate_template('mwb-rma/public/partials/mwb-rma-refund-request-form.php');
			if ( !empty( $located ) ) {

				$new_template =wc_get_template('mwb-rma/public/partials/mwb-rma-refund-request-form.php');
			}
			else
			{
				$new_template = MWB_RMA_DIR_PATH. 'public/partials/mwb-rma-refund-request-form.php';
			}
			$template =  $new_template;
		}
		return $template;
	}

	/**
	 * This function is to add Return button on thankyou page after order details and show Return Product details
	 * 
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function mwb_rma_typ_order_return_button($order){

		$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings' ,array());
		$mwb_rma_return_request_form_page_id =  get_option('mwb_rma_return_request_form_page_id',true);

		if(isset($mwb_rma_refund_settings) && !empty($mwb_rma_refund_settings) && is_array($mwb_rma_refund_settings)){

			$mwb_rma_refund_enable = isset($mwb_rma_refund_settings['mwb_rma_return_enable'])?$mwb_rma_refund_settings['mwb_rma_return_enable']:'';
			$mwb_rma_refund_max_days=isset($mwb_rma_refund_settings['mwb_rma_return_days'])?$mwb_rma_refund_settings['mwb_rma_return_days']:'';

			if($mwb_rma_refund_enable == 'on'){

				$order_id=$order->get_id();
				$order_date = date_i18n( 'd-m-Y', strtotime( $order->get_date_created()  ) );
				$statuses = isset($mwb_rma_refund_settings['mwb_rma_return_order_status'])?$mwb_rma_refund_settings['mwb_rma_return_order_status']:array();
				$order_status ="wc-".$order->get_status();

				if(in_array($order_status, $statuses))
				{
					$today_date = date_i18n( 'd-m-Y' );
					$order_date = strtotime($order_date);
					$today_date = strtotime($today_date);
					$days = $today_date - $order_date;
					$day_diff = floor($days/(60*60*24));
					$page_id=$mwb_rma_return_request_form_page_id;
					$return_url = get_permalink($page_id);
					if($mwb_rma_refund_max_days >= $day_diff && $mwb_rma_refund_max_days != 0){
						$return_url = add_query_arg('order_id',$order_id,$return_url);
						$return_url = wp_nonce_url($return_url,'mwb_rma_return_form_nonce','mwb_rma_return_form_nonce');
						?>
						<form action="<?php echo $return_url ?>" method="post">
							<input type="hidden" value="<?php echo $order_id?>" name="order_id">
							<p><input type="submit" class="btn button" value="<?php _e('Refund Request','mwb-rma');?>" name="mwb_rma_new_return_request"></p>
						</form>
						<?php 
					}
				}
			}
		}
	}

	/**
	 * Add refund button on my-account order section.
	 *
	 * @since    1.0.0
	 */
	public function mwb_rma_refund_exchange_button($actions, $order)
	{
		$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings' ,array());
		$order = new WC_Order($order);		
		$mwb_rma_next_return = true;
		$order_id = $order->get_id();
		$mwb_rma_made = get_post_meta($order_id, "mwb_rma_request_made", true);
		if(isset($mwb_rma_made) && !empty($mwb_rma_made))
		{
			$mwb_rma_next_return = false;
		}

		if($mwb_rma_next_return)
		{				
			//Return Request at order detail page
			$mwb_rma_refund_enable = isset($mwb_rma_refund_settings['mwb_rma_return_enable'])?$mwb_rma_refund_settings['mwb_rma_return_enable']:'';
			$mwb_rma_refund_max_days=isset($mwb_rma_refund_settings['mwb_rma_return_days'])?$mwb_rma_refund_settings['mwb_rma_return_days']:'';

			if($mwb_rma_refund_enable == 'on')
			{

				$statuses = isset($mwb_rma_refund_settings['mwb_rma_return_order_status'])?$mwb_rma_refund_settings['mwb_rma_return_order_status']:array();
				$order_status ="wc-".$order->get_status();

				if(in_array($order_status, $statuses))
				{
					
					$order_date = date_i18n( 'd-m-Y', strtotime( $order->get_date_created() ) );

					$today_date = date_i18n( 'd-m-Y' );
					$order_date = strtotime($order_date);
					$today_date = strtotime($today_date);
					$days = $today_date - $order_date;
					$day_diff = floor($days/(60*60*24));

					$day_allowed = $mwb_rma_refund_max_days;

					$return_button_text = __('Refund','mwb-rma');
					
					if($day_allowed >= $day_diff && $day_allowed != 0)
					{

						$mwb_rma_return_request_form_page_id = get_option('mwb_rma_return_request_form_page_id');
						$return_url = get_permalink($mwb_rma_return_request_form_page_id);
						$order_id = $order->get_id();
						$return_url = add_query_arg('order_id',$order_id,$return_url);
						$return_url = wp_nonce_url($return_url,'mwb_rma_return_form_nonce','mwb_rma_return_form_nonce');
						$actions['return']['url'] = $return_url;
						$actions['return']['name'] = $return_button_text;

					}	

				}
			}
		}
		return $actions;
	}

	public function test_func_callback(){
		$set_qty = 1;
		if(isset($product_data) && !empty($product_data))
		{
			foreach($product_data as $key=>$data)
			{
				if($item['product_id'] == $data['product_id'] && $item['variation_id'] == $data['variation_id'])
				{
					$checked = 'checked="checked"';
					$set_qty = $data['qty'];
					break;
				}
			}
		}
	}

	/**
	 * This function is to save return request Attachment
	 * 
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function mwb_rma_order_return_attach_files()
	{
		$check_ajax = check_ajax_referer( 'mwb-rma-ajax-security-string', 'security_check' );
		if ( $check_ajax ) 
		{
			if(current_user_can('mwb-rma-refund-request'))
			{
				if(isset($_FILES['mwb_rma_return_request_files']))
				{
					if(isset($_FILES['mwb_rma_return_request_files']['tmp_name']))
					{
						$filename = array();
						$order_id = sanitize_text_field($_POST['mwb_rma_return_request_order']);
						$count = sizeof($_FILES['mwb_rma_return_request_files']['tmp_name']);
						for($i=0;$i<$count;$i++)
						{
							if(isset($_FILES['mwb_rma_return_request_files']['tmp_name'][$i]))
							{	
								$directory = ABSPATH.'wp-content/attachment';
								if (!file_exists($directory)) 
								{
									mkdir($directory, 0755, true);
								}

								$sourcePath = sanitize_text_field($_FILES['mwb_rma_return_request_files']['tmp_name'][$i]);
								$targetPath = $directory.'/'.$order_id.'-'.sanitize_text_field($_FILES['mwb_rma_return_request_files']['name'][$i]);

								$filename[] = $order_id.'-'.sanitize_text_field($_FILES['mwb_rma_return_request_files']['name'][$i]);
								move_uploaded_file($sourcePath,$targetPath) ;
							}
						}

						$request_files = get_post_meta($order_id, 'mwb_rma_return_attachment', true);

						$pending = true;
						if(isset($request_files) && !empty($request_files))
						{
							foreach($request_files as $date=>$request_file)
							{
								if($request_file['status'] == 'pending')
								{
									unset($request_files[$date][0]);
									$request_files[$date]['files'] = $filename;
									$request_files[$date]['status'] = 'pending';
									$pending = false;
									break;
								}
							}
						}

						if($pending)
						{	
							$request_files = array();
							$date = date("d-m-Y");
							$request_files[$date]['files'] = $filename;
							$request_files[$date]['status'] = 'pending';
						}

						update_post_meta($order_id, 'mwb_rma_return_attachment', $request_files);
						echo 'success';
					}
				}
			}
		die();
		}
	}
}
