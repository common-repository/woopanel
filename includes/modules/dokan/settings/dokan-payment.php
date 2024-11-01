<?php
class NBT_Solutions_Dokan_Setting_Payment {
	function __construct() {
		add_filter('woopanel_options', array($this, 'get_settings'), 99, 1);
		add_action( 'woopanel_init', array($this, 'save_settings') );
		add_filter( 'woopanel_form_field_paypal', array($this, 'show_field_payment_paypal'), 99, 4);
		add_filter( 'woopanel_form_field_skrill', array($this, 'show_field_payment_skrill'), 99, 4);
		add_filter( 'woopanel_form_field_bank', array($this, 'show_field_payment_bank'), 99, 4);
	}
	
	function get_settings( $fields = array() ) {
		global $current_user;
		
		$dokan_settings = get_user_meta($current_user->ID, 'dokan_profile_settings', true);
		
		$fields['dokan_payment'] = [
			'menu_title' => __( 'Dokan Payment', 'woopanel' ),
			'title'      => __( 'Dokan Payment Settings', 'woopanel' ),
			'desc'       => '',
			'parent'     => '',
			'icon'       => '',
			'type'       => 'user_meta',
			'fields'     => array()
		];
		
		$methods      = dokan_withdraw_get_active_methods();
		
		foreach ( $methods as $method_key ) {
			$method = dokan_withdraw_get_method( $method_key );

			$fields['dokan_payment']['fields'][$method_key] = array(
				'id' => 'dokan_payment['.$method_key.']',
				'type' => $method_key,
				'title' => $method['title']
			);
		}
		
		// echo '<pre>';
		// print_r($dokan_settings);
		// echo '</pre>';

		return $fields;
	}
	
	function save_settings() {
		global $current_user;

		$dokan_profile_settings = get_user_meta($current_user->ID, 'dokan_profile_settings', true);
		if( empty($dokan_profile_settings) ) {
			$dokan_profile_settings = array();
		}
		
		if ( is_woopanel_endpoint_url('settings') && ( isset($_POST['save']) || isset($_POST['save1']) ) ) {
			$dokan_profile_settings['payment'] = $_POST['dokan_payment']['payment'];
			update_user_meta($current_user->ID, 'dokan_profile_settings', $dokan_profile_settings );
		}
	}
	

	function show_field_payment_paypal($field = false, $key, $args, $value ) {
		global $current_user;
		
		$profile_info = dokan_get_store_info( dokan_get_current_user_id() );
		
		$email = isset( $profile_info['payment']['paypal']['email'] ) ? esc_attr( $profile_info['payment']['paypal']['email'] ) : $current_user->user_email ;
		?>
		<div class="form-group m-form__group type-checkbox row" id="setting_show_more_ptab_field" data-priority="">
			<label for="setting_dokan_payment_paypal" class="col-3 col-form-label"><?php esc_html_e('Paypal', 'dokan-lite');?></label>
			
			<div class="col-9">
				<input type="text" class="form-control m-input " name="dokan_payment[payment][paypal][email]" id="setting_dokan_payment_paypal" placeholder="" value="<?php echo $email;?>">
			</div>
		</div>
		<?php
	}
	

	function show_field_payment_skrill($field = false, $key, $args, $value ) {
		global $current_user;
		
		$profile_info = dokan_get_store_info( dokan_get_current_user_id() );
		
		$email = isset( $profile_info['payment']['skrill']['email'] ) ? esc_attr( $profile_info['payment']['skrill']['email'] ) : $current_user->user_email ;
		?>
		<div class="form-group m-form__group type-checkbox row" id="setting_show_more_ptab_field" data-priority="">
			<label for="setting_dokan_payment_skrill" class="col-3 col-form-label"><?php esc_html_e('Skrill', 'dokan-lite');?></label>
			
			<div class="col-9">
				<input type="text" class="form-control m-input " name="dokan_payment[payment][skrill][email]" id="setting_dokan_payment_skrill" placeholder="" value="<?php echo $email;?>">
			</div>
		</div>
		<?php
	}
	
	function show_field_payment_bank($field = false, $key, $args, $value ) {
		$profile_info = dokan_get_store_info( dokan_get_current_user_id() );
		
		$fields = array(
			'ac_name' => array(
				'label' => esc_attr__( 'Your bank account name', 'dokan-lite' ),
				'type' => 'text'
			),
			'ac_number' => array(
				'label' => esc_attr__( 'Your bank account number', 'dokan-lite' ),
				'type' => 'text'
			),
			'bank_name' => array(
				'label' => esc_attr__( 'Name of bank', 'dokan-lite' ),
				'type' => 'text'
			),
			'bank_addr' => array(
				'label' => esc_attr__( 'Address of your bank', 'dokan-lite' ),
				'type' => 'textarea'
			),
			'routing_number' => array(
				'label' => esc_attr__( 'Routing number', 'dokan-lite' ),
				'type' => 'text'
			),
			'iban' => array(
				'label' => esc_attr__( 'IBAN', 'dokan-lite' ),
				'type' => 'text'
			),
			'swift' => array(
				'label' => esc_attr__( 'Swift code', 'dokan-lite' ),
				'type' => 'text'
			),
		);
		
		$account_name   = isset( $profile_info['payment']['bank']['ac_name'] ) ? $profile_info['payment']['bank']['ac_name'] : '';
		$account_number = isset( $profile_info['payment']['bank']['ac_number'] ) ? $profile_info['payment']['bank']['ac_number'] : '';
		$bank_name      = isset( $profile_info['payment']['bank']['bank_name'] ) ? $profile_info['payment']['bank']['bank_name'] : '';
		$bank_addr      = isset( $profile_info['payment']['bank']['bank_addr'] ) ? $profile_info['payment']['bank']['bank_addr'] : '';
		$routing_number = isset( $profile_info['payment']['bank']['routing_number'] ) ? $profile_info['payment']['bank']['routing_number'] : '';
		$iban           = isset( $profile_info['payment']['bank']['iban'] ) ? $profile_info['payment']['bank']['iban'] : '';
		$swift_code     = isset( $profile_info['payment']['bank']['swift'] ) ? $profile_info['payment']['bank']['swift'] : '';
		?>
		<div class="form-group m-form__group type-checkbox row" id="setting_show_more_ptab_field" data-priority="">
			<label for="setting_show_more_ptab" class="col-3 col-form-label"><?php echo esc_attr($args['label']);?></label>
			
			<div class="col-9">
				<?php foreach( $fields as $name => $field) {
					$value = isset( $profile_info['payment']['bank'][$name] ) ? $profile_info['payment']['bank'][$name] : '';?>
				<div class="form-field-address">
					<label><?php echo esc_attr($field['label']);?></label>
					<?php
					if( $field['type'] == 'text' ) {?>
						<input type="text" class="form-control m-input" name="dokan_payment[payment][bank][<?php echo esc_attr($name);?>]" value="<?php echo esc_attr($value);?>" placeholder="">
					
					<?php }elseif( $field['type'] == 'textarea' ) {?>
						<textarea class="form-control m-input" rows="4" name="dokan_payment[payment][bank][<?php echo esc_attr($name);?>]"><?php echo esc_attr($value);?></textarea>
					<?php
					}
				echo '</div>';
				}?>
			</div>
		</div>
		<?php
	}
}

new NBT_Solutions_Dokan_Setting_Payment();