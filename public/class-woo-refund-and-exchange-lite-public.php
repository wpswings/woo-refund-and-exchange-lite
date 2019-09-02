<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       www.makewebbetter.com
 * @since      1.0.0
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_Refund_And_Exchange_Lite
 * @subpackage Woo_Refund_And_Exchange_Lite/public
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class Woo_Refund_And_Exchange_Lite_Public {

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
		 * defined in Woo_Refund_And_Exchange_Lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Refund_And_Exchange_Lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo-refund-and-exchange-lite-public.css', array(), $this->version, 'all' );

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
		 * defined in Woo_Refund_And_Exchange_Lite_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woo_Refund_And_Exchange_Lite_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo-refund-and-exchange-lite-public.js', array( 'jquery' ), $this->version, false );
		$ajax_nonce = wp_create_nonce( "mwb-rma-ajax-security-string" );
		$user_id = get_current_user_id();

		if($user_id > 0)
		{
			$myaccount_page = get_option( 'woocommerce_myaccount_page_id' );
			$myaccount_page_url = get_permalink( $myaccount_page );
		}
		else
		{
			$myaccount_page_url='';
			$myaccount_page_url=apply_filters('myaccount_page_url',$myaccount_page_url);
		}
		$pro_active = Woo_Refund_And_Exchange_Lite_Common_Functions::mwb_rma_pro_active();
		$translation_array = array(
			'attachment_msg'		=> __( 'File should be of .png , .jpg, or .jpeg extension' , 'woo-refund-and-exchange-lite'),
			'return_subject_msg' 	=> __( 'Please enter refund subject.', 'woo-refund-and-exchange-lite' ),
			'return_reason_msg'		=> __( 'Please enter refund reason.', 'woo-refund-and-exchange-lite' ),
			'mwb_rma_nonce'			=> $ajax_nonce,
			'ajaxurl' 				=> admin_url('admin-ajax.php'),
			'myaccount_url' 		=> $myaccount_page_url,
			'pro_active'			=> $pro_active,
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
		
		$mwb_rma_pages = get_option('mwb_rma_pages');
		$page_id = $mwb_rma_pages['pages']['mwb_rma_return_form'];

		if(is_page($page_id))
		{

			$located = locate_template('woo-refund-and-exchange-lite/public/partials/mwb-rma-refund-request-form.php');
			if ( !empty( $located ) ) {

				$new_template =wc_get_template('woo-refund-and-exchange-lite/public/partials/mwb-rma-refund-request-form.php');
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
	 * @param $order
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */
	public function mwb_rma_typ_order_return_button($order){

		$mwb_rma_refund_settings = get_option( 'mwb_rma_refund_settings' ,array());
		$mwb_rma_pages = get_option('mwb_rma_pages');
		$page_id = $mwb_rma_pages['pages']['mwb_rma_return_form'];
		$return_url = get_permalink($page_id);

		if(isset($mwb_rma_refund_settings) && !empty($mwb_rma_refund_settings) && is_array($mwb_rma_refund_settings)){

			$mwb_rma_refund_enable = isset($mwb_rma_refund_settings['mwb_rma_return_enable'])?$mwb_rma_refund_settings['mwb_rma_return_enable']:'';
			$mwb_rma_refund_max_days = isset($mwb_rma_refund_settings['mwb_rma_return_days'])?$mwb_rma_refund_settings['mwb_rma_return_days']:'';

			$statuses = isset($mwb_rma_refund_settings['mwb_rma_return_order_status'])?$mwb_rma_refund_settings['mwb_rma_return_order_status']:array();


			if($mwb_rma_refund_enable == 'on'){

				$day_diff = Woo_Refund_And_Exchange_Lite_Common_Functions::mwb_rma_find_order_day_diff($order);
				$order_status ="wc-".$order->get_status();

				$order_id=$order->get_id();
				$product_datas = get_post_meta($order_id, 'mwb_rma_return_request_product', true);
				if(isset($product_datas) && !empty($product_datas))
				{
					?>
					<h2><?php _e( 'Refund Requested Product', 'woo-refund-and-exchange-lite' ); ?></h2>
					<?php 
					foreach($product_datas as $key=>$product_data)
					{
						$date=date_create($key);
						$date_format = get_option('date_format');
						$date=date_format($date,$date_format);
						?>
						<p><?php _e( 'Following product Refund request made on', 'woocommerce-refund-and-exchange-lite' ); ?> <b><?php echo $date?>.</b></p>
						<table class="shop_table order_details">
							<thead>
								<tr>
									<th class="product-name"><?php _e( 'Product', 'woo-refund-and-exchange-lite' ); ?></th>
									<th class="product-total"><?php _e( 'Total', 'woo-refund-and-exchange-lite' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								$line_items  = $order->get_items();
								$return_products = $product_data['products'];
								foreach($line_items as $item_id => $item ) 
								{
									foreach($return_products as $return_product)
									{
										if(isset($return_product['item_id']))
										{	
											if($return_product['item_id'] == $item_id)
											{
												?><tr>
													<td class="product-name">
														<?php 
														$product = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );

														$is_visible        = $product && $product->is_visible();
														$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

														echo $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink,$product->get_name() ) : $product->get_name();
														echo '<strong class="product-quantity">' . sprintf( '&times; %s', $return_product['qty'] ) . '</strong>';

														do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );wc_display_item_meta( $item );
															wc_display_item_downloads( $item );

														do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );
														?>
													</td>
													<td class="product-total"><?php 
													echo wc_price($return_product['price']*$return_product['qty']);
													?></td>
												</tr>

												<?php 
											}
										}
									}	
								}
								?>
								<tr>
									<th scope="row"><?php _e('Refund Amount', 'woo-refund-and-exchange-lite') ?></th>
									<th><?php echo wc_price($product_data['amount']); ?></th>
								</tr>
							</tbody>
						</table>
						<?php
						if($product_data['status'] == 'complete')
						{
							$appdate=date_create($product_data['approve_date']);
							$format = get_option('date_format');
							$appdate=date_format($appdate,$format);
							?>
							<p><?php _e('Above product Refund request is approved on','woo-refund-and-exchange-lite');?> <b><?php echo $appdate?>.</b></p>
							<?php 
						}

						if($product_data['status'] == 'cancel')
						{
							$appdate=date_create($product_data['cancel_date']);
							$format = get_option('date_format');
							$appdate=date_format($appdate,$format);
							?>
							<p><?php _e('Above product Refund request is cancelled on','woo-refund-and-exchange-lite');?> <b><?php echo $appdate?>.</b></p>
							<?php 
						}
					}

					if(in_array($order_status, $statuses))
					{
						if($mwb_rma_refund_max_days >= $day_diff && $mwb_rma_refund_max_days != 0){
							$return_url = add_query_arg('order_id',$order_id,$return_url);
							$return_url = wp_nonce_url($return_url,'mwb_rma_return_form_nonce','mwb_rma_return_form_nonce');
							?>
							<form action="<?php echo $return_url ?>" method="post">
								<input type="hidden" value="<?php echo $order_id?>" name="order_id">
								<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woo-refund-and-exchange-lite');?>" name="mwb_rma_new_return_request"></p>
							</form>
							<?php 
						}
					}

				}

				
			
				if(in_array($order_status, $statuses))
				{
					if($mwb_rma_refund_max_days >= $day_diff && $mwb_rma_refund_max_days != 0){
						$return_url = add_query_arg('order_id',$order_id,$return_url);
						$return_url = wp_nonce_url($return_url,'mwb_rma_return_form_nonce','mwb_rma_return_form_nonce');
						?>
						<form action="<?php echo $return_url ?>" method="post">
							<input type="hidden" value="<?php echo $order_id?>" name="order_id">
							<p><input type="submit" class="btn button" value="<?php _e('Refund Request','woo-refund-and-exchange-lite');?>" name="mwb_rma_new_return_request"></p>
						</form>
						<?php 
					}
				}
			}
		}
	}

	/**
	 * Add refund button on my-account order section.
	 * @param $actions ,$order
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
					$day_diff = Woo_Refund_And_Exchange_Lite_Common_Functions::mwb_rma_find_order_day_diff($order);
					$day_allowed = $mwb_rma_refund_max_days;

					$return_button_text = __('Refund','woo-refund-and-exchange-lite');
					
					if($day_allowed >= $day_diff && $day_allowed != 0)
					{

						$mwb_rma_pages = get_option('mwb_rma_pages');
						$page_id = $mwb_rma_pages['pages']['mwb_rma_return_form'];
						$return_url = get_permalink($page_id);
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
		wp_die();
		}
	}

	/**
	 * This function is to save return request products details when refund request is submit
	 * 
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link http://www.makewebbetter.com/
	 */

	public function mwb_rma_return_product_info_callback(){
		$check_ajax = check_ajax_referer( 'mwb-rma-ajax-security-string', 'security_check' );
		if ( $check_ajax ) 
		{
			if(current_user_can('mwb-rma-refund-request'))
			{
				$order_id = sanitize_text_field($_POST['orderid']);
				$subject = sanitize_text_field($_POST['subject']);
				$reason = sanitize_text_field($_POST['reason']);
				$mwb_rnx_products = array();
				$pending = true;
				$post_data=$_POST;
				$products = get_post_meta($order_id, 'mwb_rma_return_request_product', true);

				//update refund requested products
				$mwb_rnx_products = apply_filters('mwb_rnx_get_product_details', $mwb_rnx_products ,$order_id,$post_data);
				if(is_array($mwb_rnx_products) && !empty($mwb_rnx_products)){
					$products = $mwb_rnx_products['products'];
					$pending = $mwb_rnx_products['pending'];
				}

				if($pending)
				{
					if(!is_array($products))
					{
						$products = array();
					}
					$products = array();
					$date = date("d-m-Y");
					$products[$date] = $_POST;
					$products[$date]['status'] = 'pending';
				}	
				
				update_post_meta($order_id, "mwb_rma_return_request_made", true);
				
				update_post_meta($order_id, 'mwb_rma_return_request_product', $products);


				//Send mail to merchant
				$mwb_rma_mail_basic_settings = get_option('mwb_rma_mail_basic_settings',array());
				$mwb_rma_mail_refund_settings = get_option('mwb_rma_mail_refund_settings',array());

				$reason_subject = $subject;
					
				$mail_header = stripslashes(isset($mwb_rma_mail_basic_settings['mwb_rma_mail_header'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_header']:'');
				$mail_footer = stripslashes(isset($mwb_rma_mail_basic_settings['mwb_rma_mail_footer'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_footer']:'');

				$message_details = '<div class="header">
							<h2>'.$reason_subject.'</h2>
						</div>
						<div class="content">

							<div class="reason">
								<h4>'.__('Reason of Refund', 'woo-refund-and-exchange-lite').'</h4>
								<p>'.$reason.'</p>
							</div>
							<div class="Order">
								<h4>Order #'.$order_id.'</h4>
								<table>
									<tbody>
										<tr>
											<th>'.__('Product', 'woo-refund-and-exchange-lite').'</th>
											<th>'.__('Quantity', 'woo-refund-and-exchange-lite').'</th>
											<th>'.__('Price', 'woo-refund-and-exchange-lite').'</th>
										</tr>';
						
										$order = new WC_Order($order_id);
										$requested_products = $products[$date]['products'];

										if(isset($requested_products) && !empty($requested_products))
										{
											$total = 0;
											foreach( $order->get_items() as $item_id => $item )
											{
												foreach($requested_products as $requested_product)
												{

													if(isset($requested_product['item_id']))
													{	
														if($item_id == $requested_product['item_id'])
														{
															if(isset($requested_product['variation_id']) && $requested_product['variation_id'] > 0)
															{
																$prod = wc_get_product($requested_product['variation_id']);

															}
															else
															{
																$prod = wc_get_product($requested_product['product_id']);
															}
															$mwb_rma_actual_price = $order->get_item_total( $item );
															$subtotal = $mwb_rma_actual_price*$requested_product['qty'];
															$subtotal = apply_filters( 'mwb_rma_refund_policy_price_deduction' ,$subtotal,$order_id);
															$item_meta      = new WC_Order_Item_Product( $item);
															$item_meta_html = wc_display_item_meta($item_meta,array('echo'=> false));

															$total += $subtotal;
															$message_details .= '<tr>
															<td>'.$item['name'].'<br>';
																$message_details .= '<small>'.$item_meta_html.'</small>
																<td>'.$requested_product['qty'].'</td>
																<td>'.wc_price($subtotal).'</td>
															</tr>';
														}
													}
												}	
											}	
										}

										$message_details .= '<tr>
										<th colspan="2">'.__('Refund Total', 'woo-refund-and-exchange-lite').':</th>
										<td>'.wc_price($total).'</td>
									</tr>
								</tbody>
							</table>
						</div>';
					
				$message = Woo_Refund_And_Exchange_Lite_Common_Functions::create_mail_html($order_id,$message_details);		
			
				$headers = array();
				$headers[] = "Content-Type: text/html; charset=UTF-8";
				$to = isset($mwb_rma_mail_basic_settings['mwb_rma_mail_from_email'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_from_email']:'';
				$subject = isset($mwb_rma_mail_refund_settings['mwb_rma_mail_merchant_return_subject'])? $mwb_rma_mail_refund_settings['mwb_rma_mail_merchant_return_subject']:'';
				$subject = str_replace('[order]', "#".$order_id, $subject);	
				
				//Send mail to User that we recieved your request
				wc_mail( $to, $subject, $message, $headers );	

				$fmail =  isset($mwb_rma_mail_basic_settings['mwb_rma_mail_from_email'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_from_email']:'';
				$fname =  isset($mwb_rma_mail_basic_settings['mwb_rma_mail_from_name'])? $mwb_rma_mail_basic_settings['mwb_rma_mail_from_name']:'';

				$to = get_post_meta($order_id, '_billing_email', true);
				$headers = array();
				$headers[] = "From: $fname <$fmail>";
				$headers[] = "Content-Type: text/html; charset=UTF-8";
				$subject = isset($mwb_rma_mail_refund_settings['mwb_rma_mail_return_subject'])? $mwb_rma_mail_refund_settings['mwb_rma_mail_return_subject']:'';
				$subject = str_replace('[order]', "#".$order_id, $subject);

				$message = stripslashes(isset($mwb_rma_mail_refund_settings['mwb_rma_mail_return_message'])? $mwb_rma_mail_refund_settings['mwb_rma_mail_return_message']:'');

				$firstname = get_post_meta($order_id, '_billing_first_name', true);
				$lname = get_post_meta($order_id, '_billing_last_name', true);
				$fullname = $firstname." ".$lname;
				
				$message = str_replace('[order]', "#".$order_id, $message);
				$message = str_replace('[siteurl]', home_url(), $message);
				$message = str_replace('[username]', $fullname, $message);
				
				$mwb_rma_shortcode='';
				$mwb_rma_shortcode = $message;
				$mwb_rma_shortcode = apply_filters( 'mwb_rma_add_shortcode_refund_mail' , $mwb_rma_shortcode , $order_id);
				$message = $mwb_rma_shortcode;
				$mwb_rma_refund_template = false;
				$mwb_rma_refund_template = apply_filters('mwb_rma_refund_template',$mwb_rma_refund_template );

				if($mwb_rma_refund_template){
					$html_content = $message;
				}else{
				 	$html_content= Woo_Refund_And_Exchange_Lite_Common_Functions::mwb_rma_mail_template_html($mail_header,$subject,$message,$$mail_footer);
				}

				wc_mail($to, $subject, $html_content, $headers );
				
				$order->update_status('wc-refund-requested', 'User Request to Refund Product');
				$response['msg'] = __('Message send successfully.You have received a notification mail regarding this, Please check your mail. Soon You redirect to My Account Page. Thanks', 'woo-refund-and-exchange-lite');
				$auto_accept_day_allowed = false;
				$auto_accept_day_allowed = apply_filters( 'auto_accept_day_allowed',$auto_accept_day_allowed,$order);
				if($auto_accept_day_allowed){
					$response['auto_accept'] = true;
				}

				echo json_encode($response);
				wp_die();
			}
		}
	}

	public function mwb_rma_get_product_price(){
     	$product_id = isset( $_POST['product_id'] ) ? $_POST['product_id'] : ' ';
     	if( $product_id !==' '  && is_array( $product_id) && !empty( $product_id ) ){

     		foreach ($product_id as $key => $value) {
     			foreach ($value as $key1 => $id) {
     				
     				$product = wc_get_product( $value['productid'] );
     				if( $product->is_type( 'simple' ) ){
     					if( $product->is_on_sale() ){

     						$product_id[ $key ]['price'] = $product->get_sale_price();
     					}else{
     						$product_id[ $key ]['price'] = $product->get_price();
     					}

     				} elseif( $product->is_type( 'variable' ) ){
     					$variations = $product->get_available_variations();
     					foreach ($variations as $variation){
     						if( $variation['variation_id'] == $value['variationid'] ){
     							$product_id[ $key ]['price']= $variation['display_price'];

     						}
     					}
     				}
     			}
     		}
     		$productid = json_encode( $product_id );
     		echo $productid;
     	}else{
     		echo "fail";
     	}
     	wp_die();
     }


}
