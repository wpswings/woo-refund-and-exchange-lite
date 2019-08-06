<?php 
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://makewebbetter.com/
 * @since      1.0.0
 *
 * @package    Mwb_Rma
 * @subpackage Mwb_Rma/admin/partials
 */

/**This class is for generating the html for the settings.
 *
 * 
 * This file use to display the function fot the html
 *
 * @package    Mwb_Rma
 * @subpackage Mwb_Rma/admin/partials
 * @author     makewebbetter <webmaster@makewebbetter.com>
 */
class mwb_rma_admin_settings {

	public function mwb_rma_generate_general_html($mwb_general_settings) {
		foreach ($mwb_general_settings as $key => $value) {
				if ($value['type'] == "title") {
					?>
					<div class="mwb_rma_general_row_wrap">
						<?php $this->mwb_rma_generate_heading($value);?>
				<?php }?>
				<?php if($value['type'] != "title" && $value['type'] != "sectionend") { ?>
				<div class="mwb_rma_general_row">
					<?php $this->mwb_rma_generate_label($value);?>
					<div class="mwb_rma_general_content">
						<?php 
						$this->mwb_rma_generate_tool_tip($value);
						if($value['type'] == "checkbox") {
							$this->mwb_rma_generate_checkbox_html($value);
						}
						if ($value['type'] == "number") {
							$this->mwb_rma_generate_number_html($value);
						}
						if ($value['type'] == "multiple_checkbox") {
							foreach ($value['multiple_checkbox'] as $k => $val) {
								$this->mwb_rma_generate_checkbox_html($val);
							}
						}
						if ($value['type'] == "text") {
							$this->mwb_rma_generate_text_html($value);
						}
						if ($value['type'] == "textarea") {
							$this->mwb_rma_generate_textarea_html($value);
						}
						if ($value['type'] == "number_text") {
							foreach ($value['number_text'] as $k => $val) {
								if ($val['type'] == 'text') {
									$this->mwb_rma_generate_text_html($val);

								}
								if ($val['type'] == 'number') {
									$this->mwb_rma_generate_number_html($val);
									echo get_woocommerce_currency_symbol();
								}
							}
						}
						?>
					</div>
				</div>
				<?php }?>
			<?php if ($value['type'] == "sectionend"):?>
				 </div>	
				<?php endif;?>
		<?php } 
	}

	/**
	*This function is for generating for the checkbox for the Settings
	*@name mwb_rma_generate_checkbox_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_generate_checkbox_html($value,$general_settings) {
		 $enable_mwb_rma = isset($general_settings[$value['id']]) ? $general_settings[$value['id']] : 0;
		?>
		<label for="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
			<input type="checkbox" name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" <?php checked($enable_mwb_rma,'on');?> id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"> <?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
			<p class="mwb-rma-cdesc-tip"><?php echo (array_key_exists('desc_tip', $value))?$value['desc_tip']:'';?></p>
		</label>
		<?php
	}

	/**
	*This function is for generating for the number for the Settings
	*@name mwb_rma_generate_number_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_generate_number_html($value,$general_settings) {
		$mwb_signup_value = isset($general_settings[$value['id']]) ? intval($general_settings[$value['id']]) : 1;
		?>
		<label for="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
			<input type="number" <?php if (array_key_exists('custom_attributes', $value)) {
					
					foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
						echo  $attribute_name ;
						echo  "=$attribute_val"; 
						
					}
				}?> value="<?php echo $mwb_signup_value;?>" name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
			class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
		</label>
		<?php
	}

	
	/**
	 * Generate Drop down menu fields
	 * @since 1.0.0
	 * @name mwb_wgm_generate_searchSelect_html()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */

	public function mwb_rma_generate_searchSelect_html( $value,$general_settings )
	{
		$selectedvalue =  isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : array();
		if($selectedvalue == ''){
			$selectedvalue = '';
		}
		?>
		<select name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>[]" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" multiple = "<?php echo (array_key_exists('multiple', $value))? $value['multiple']:''; ?>"
			<?php
			if (array_key_exists('custom_attribute', $value)) {
				foreach ($value['custom_attribute'] as $attribute_name => $attribute_val) {
					echo  $attribute_name.'='.$attribute_val ;					
				}
			}
			if(is_array($value['options']) && !empty($value['options'])){
				foreach($value['options'] as $option_key => $option_value){
					$select = 0;
					if(is_array($selectedvalue) && in_array($option_key, $selectedvalue)){
						$select = 1;
					}
					?>
					><option value="<?php echo $option_key;?>" <?php echo selected(1, $select);?> ><?php echo $option_value; ?></option>
					<?php
				}
			}	
			?>
			</select>
		</label>
	<?php	
	}
	
	/**
	*This function is for generating for the wp_editor for the Settings
	*@name mwb_rma_generate_label
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_generate_wp_editor($value,$notification_settings) {

		if(isset($value['id']) && !empty($value['id'])) {
			$defaut_text = isset($value['default'])?$value['default']:'';
			$mwb_rma_content = isset($notification_settings[$value['id']]) ?$notification_settings[$value['id']] : $defaut_text;
			$value_id = (array_key_exists('id', $value))?$value['id']:'';
			?>
			<label for="<?php echo $value_id; ?>">
				<?php 
				$content = stripcslashes($mwb_rma_content);
				$editor_id= $value_id;
				$settings = array(
					'media_buttons'    => false,
					'drag_drop_upload' => true,
					'dfw'              => true,
					'teeny'            => true,
					'editor_height'    => 200,
					'editor_class'       => 'mwb_rma_new_woo_ver_style_textarea',
					'textarea_name'    => $value_id,
					);
					wp_editor($content,$editor_id,$settings); ?>
				</label>	
				<?php
		}
	}


	/**
	*This function is for generating for the Label for the Settings
	*@name mwb_rma_generate_label
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_generate_label($value) {
		?>
		<div class="mwb_rma_general_label">
			<label for="<?php echo (array_key_exists('id', $value))?$value['id']:'';?>"><?php echo (array_key_exists('title', $value))?$value['title']:''; ?></label>
			<?php if(array_key_exists('pro',$value)) {?>
			<span class="mwb_rma_general_pro">Pro</span>
			<?php }?>
		</div>
		<?php
	}


	/**
	*This function is for generating for the heading for the Settings
	*@name mwb_rma_generate_heading
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_generate_heading($value) {
		if(array_key_exists('title',$value)) {?>
			<div class="mwb_rma_general_sign_title">
				<?php echo $value['title'];?>
			</div>
			<?php 
		}
	}

	/**
	*This function is for generating for the Tool tip for the Settings
	*@name mwb_rma_generate_tool_tip
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_generate_tool_tip($value) {
		if(array_key_exists('desc_tip',$value)) {
			echo wc_help_tip($value['desc_tip']);
		}
	}

	/**
	*This function is for generating for the text html
	*@name mwb_rma_generate_text_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_generate_text_html($value,$general_settings) {
		$mwb_signup_value = isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : '';
		if(empty($mwb_signup_value)) {
			$mwb_signup_value = array_key_exists('default',$value)?$value['default']:'';
		}
		?>
		<label for="
			<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
			<input type="text" <?php 
			if (array_key_exists('custom_attributes', $value)) {
					foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
						echo  $attribute_name ;
						echo  "=$attribute_val"; 
					}
				}?> 
				style ="<?php echo (array_key_exists('style', $value))?$value['style']:''; ?>"
				value="<?php echo $mwb_signup_value;?>" name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?><?php echo (array_key_exists('val_type', $value))?'[]':''; ?>" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
				class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
		</label>
			<?php
	}

	/**
	*This function is for generating for the text html
	*@name mwb_rma_generate_textarea_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_generate_textarea_html($value,$general_settings) {
		$mwb_signup_value = isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : '';
		if(empty($mwb_signup_value)) {
			$mwb_signup_value = array_key_exists('default',$value)?$value['default']:'';
		}
		?>
		<span class="description"><?php echo array_key_exists('desc', $value)?$value['desc']:'';?></span>	
		<label for="mwb_rma_general_text_points" class="mwb_rma_label">
			<textarea 
				<?php if (array_key_exists('custom_attributes', $value)) {
				foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
						echo  $attribute_name ;
						echo  "=$attribute_val"; 
						
					}
				}?>  name="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
				class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo (array_key_exists('desc', $value))?$value['desc']:'';?><?php echo $mwb_signup_value;?>
			</textarea>
		</label>
		<p class="description"><?php echo $value['desc2']; ?></p>
		<?php
	}

	/**
	 * Generate save button html for setting page
	 * @since 1.0.0
	 * @name mwb_wgm_save_button_html()
	 * @author makewebbetter<webmaster@makewebbetter.com>
	 * @link https://www.makewebbetter.com/
	 */
	public function mwb_rma_save_button_html($name){
		?>
		<p class="submit">
			<input type="submit" value="<?php _e('Save changes', 'mwb-rma'); ?>" class="button-primary woocommerce-save-button" name="<?php echo $name;?>" id="<?php echo $name;?>" >
		</p><?php
	}

	/**
	*This function is for generating the notice of the save settings
	*@name mwb_rma_generate_textarea_html
	*@param $value
	*@since 1.0.0 
	*/
	public function mwb_rma_settings_saved() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong><?php _e('Settings saved.','mwb-rma'); ?></strong></p>
			<button type="button" class="notice-dismiss">
				<span class="screen-reader-text"><?php _e('Dismiss this notices.','mwb-rma'); ?></span>
			</button>
		</div>
		<?php 
	}

	/**
	*This function is used for the saving and filtering the input.
	*@name mwb_rma_save_notification_settings
	*@param $value
	*@since 1.0.0 
	*/
	public  function mwb_rma_filter_checkbox_notification_settings($POST,$name) {
		$_POST[$name] = isset($_POST[$name]) ? 1 : 0;
	}

	/**
	*This function is used for the saving and filtering the input.
	*@name mwb_rma_save_notification_settings
	*@param $value
	*@since 1.0.0 
	*/
	public function  mwb_rma_filter_subj_email_notification_settings($POST,$name) {
		$_POST[$name] = (isset($_POST[$name])&& !empty($_POST[$name])) ? $_POST[$name] :'';
		return $_POST[$name];
	}

	public function mwb_rma_generate_label_for_membership($value) {
		?>
		<label for="mwb_rma_membership_level_name">
			<?php echo $value; ?>
		</label>

		<?php
	}

	public function mwb_rma_generate_tab_settings_html($settings_array,$setting_values){
		$mwb_settings_array = isset($settings_array) ? $settings_array : array();
		$mwb_setting_values = isset($setting_values) ? $setting_values : array();
		
		if(isset($mwb_settings_array) && !empty($mwb_settings_array) && is_array($mwb_settings_array)) {
			foreach ($mwb_settings_array as $key => $value) {
				?>
				<tr>
					<td><?php $this->mwb_rma_generate_label($value); ?></td>
					<?php if(isset($value['desc_tip']) && !empty($value['desc_tip']) && $value['type'] != 'checkbox'){ ?>
						<td><?php $this->mwb_rma_generate_tool_tip($value);?></td>
						<?php
					}else{
						?>
						<td></td>
						<?php
					}
					?>
					<?php
					if($value['type'] == 'checkbox'){
						?>
						<td><?php $this->mwb_rma_generate_checkbox_html($value,$mwb_setting_values);?></td>
						<?php
					}elseif($value['type'] == 'number'){
						?>
						<td><?php $this->mwb_rma_generate_number_html($value,$mwb_setting_values);?></td>
						<?php
					}elseif($value['type'] == 'multiselect'){
						?>
						<td><?php $this->mwb_rma_generate_searchSelect_html($value,$mwb_setting_values);?></td><?php
					}elseif($value['type'] == 'wp_editor'){
							?>
						<td><?php $this->mwb_rma_generate_wp_editor($value,$mwb_setting_values);?></td><?php
					}elseif($value['type'] == 'text'){
							?>
						<td><?php $this->mwb_rma_generate_text_html($value,$mwb_setting_values);?></td><?php
					}elseif($value['type'] == 'add_more_button'){
							?>
						<td><?php $this->mwb_rma_add_more_button_html($value,$mwb_setting_values);?></td><?php
					}elseif($value['type'] == 'display_text'){
							?>
						<td><?php $this->mwb_rma_display_text_html($value);?></td><?php
					}elseif($value['type'] == 'add_more_text'){
							?>
						<td><?php $this->mwb_rma_add_more_text_html($value,$mwb_setting_values);?></td><?php
					}
					?>
				</tr>
				<?php
			}
		}
	}

	public function mwb_rma_save_tab_settings($post,$setting_array){
		$mwb_settings_array = isset($setting_array) ? $setting_array : array();
		$mwb_setting_post = isset($post) ? $post : array();
		$mwb_setting_update_arr = [];
		foreach( $mwb_settings_array as $arr_key => $ref_val ){
			foreach ($ref_val['data'] as $key1 => $ref_val) {
				foreach($mwb_setting_post as $pd_key => $pd_val){
					// if($ref_val['id'] == $pd_key){
					if( $ref_val['type'] != 'display_text' ){
						$text_type = array_key_exists('val_type', $ref_val);
						if( $ref_val['type'] == 'text'  ){
							$mwb_setting_update_arr[$pd_key] = isset($pd_val) ? stripcslashes(sanitize_text_field($pd_val)):'';
						}else{
							$mwb_setting_update_arr[$pd_key] = isset($pd_val)? $pd_val:'';
						}
					}
				//}
				}
			}
		}
		return $mwb_setting_update_arr;
	}

	public function mwb_rma_add_more_button_html($value,$general_settings){
		$mwb_signup_value = isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : '';
		?>
		<p>
			<input type="button" value="<?php _e('ADD MORE', 'mwb-rma' ); ?>" class="button add_more_button" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
		</p>
		<?php

	}

	public function mwb_rma_display_text_html($value){
	?>
		<div>
			<p><b><?php echo (array_key_exists('str', $value))?$value['str']:''; ?></b></p>
			<p><?php  echo sprintf(((array_key_exists('ss_both', $value))?$value['ss_both']:''),'<b>','</b>','<b>','</b>','<b>','</b>','<b>','</b>'); ?></p>
			<p><b><?php echo (array_key_exists('shortcode', $value))?$value['shortcode']:''; ?></b></p>
		</div>
	<?php
	}

	public function mwb_rma_add_more_text_html($value,$general_settings){
		$mwb_signup_value = isset($general_settings[$value['id']]) ? ($general_settings[$value['id']]) : array();
	
		if(is_array($mwb_signup_value) && !empty($mwb_signup_value)){

			foreach ($mwb_signup_value as $key1 => $value1) {
				if(!empty($value1)){
						?>
					<div <?php if($key1 == 0){ ?> id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>_wrapper" <?php }else{ ?> class="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>_wrapper" <?php }?> >

						<label for="
							<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
							<input type="text" <?php 
							if (array_key_exists('custom_attributes', $value)) {
									foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
										echo  $attribute_name ;
										echo  "=$attribute_val"; 
									}
								}?> 
								style ="<?php echo (array_key_exists('style', $value))?$value['style']:''; ?>"
								value="<?php echo $value1;?>" name="<?php echo (array_key_exists('id', $value))?$value['id'].'[]':''; ?>" <?php if($key1 == 0){ ?> id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>" <?php }?>
								class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
						</label>
						<?php if($key1 != 0){ ?>
						<a href="#" class="mwb_rma_remove_button"><?php _e( 'Remove' ,'mwb-rma')?></a>
					<?php }?>
					</div>
				<?php
				}else{
				if($key1 == 0){
				?>
					<div id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>_wrapper">

						<label for="
							<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
							<input type="text" <?php 
							if (array_key_exists('custom_attributes', $value)) {
									foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
										echo  $attribute_name ;
										echo  "=$attribute_val"; 
									}
								}?> 
								style ="<?php echo (array_key_exists('style', $value))?$value['style']:''; ?>"
								value="" name="<?php echo (array_key_exists('id', $value))?$value['id'].'[]':''; ?>" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
								class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
						</label>
					</div>
					<?php
				}
			}
			}
		}else{
			?>
			<div id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>_wrapper">

				<label for="
				<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>">
				<input type="text" <?php 
				if (array_key_exists('custom_attributes', $value)) {
					foreach ($value['custom_attributes'] as $attribute_name => $attribute_val) {
						echo  $attribute_name ;
						echo  "=$attribute_val"; 
					}
				}?> 
				style ="<?php echo (array_key_exists('style', $value))?$value['style']:''; ?>"
				value="" name="<?php echo (array_key_exists('id', $value))?$value['id'].'[]':''; ?>" id="<?php echo (array_key_exists('id', $value))?$value['id']:''; ?>"
				class="<?php echo (array_key_exists('class', $value))?$value['class']:'';?>"><?php echo (array_key_exists('desc', $value))?$value['desc']:'';?>
			</label>
		</div>
		<?php
	}
	
}


}
