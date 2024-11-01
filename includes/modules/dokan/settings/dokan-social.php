<?php
class NBT_Solutions_Dokan_Setting_Social {
	function __construct() {
		add_filter('woopanel_options', array($this, 'get_settings'), 99, 1);
		add_action( 'woopanel_init', array($this, 'save_settings') );
	}
	
	function get_settings( $fields = array() ) {
		global $current_user;
		
		$dokan_settings = get_user_meta($current_user->ID, 'dokan_profile_settings', true);

		$fields['dokan_social'] = [
			'menu_title' => __( 'Dokan Social', 'woopanel' ),
			'title'      => __( 'Dokan Social Settings', 'woopanel' ),
			'desc'       => '',
			'parent'     => '',
			'icon'       => '',
			'type'       => 'user_meta',
			'fields'     => array()
		];
		
		if( $dokan_get_social = dokan_get_social_profile_fields() ) {
			foreach( $dokan_get_social as $key => $social) {
				$fields['dokan_social']['fields'][] = array(
					'id'       => 'dokan_social['. $key .']',
					'type'     => 'icon',
					'title'    => $social['title'],
					'icon'		=> $social['icon'],
					'placeholder' => 'http://',
					'value' => isset($dokan_settings['social'][$key]) ? $dokan_settings['social'][$key] : 0
				);
			}
		}

		return $fields;
	}
	

	function save_settings() {
		global $current_user;
		
		$dokan_profile_settings = get_user_meta($current_user->ID, 'dokan_profile_settings', true);
		if( empty($dokan_profile_settings) ) {
			$dokan_profile_settings = array();
		}
		


		if ( is_woopanel_endpoint_url('settings') && ( isset($_POST['save']) || isset($_POST['save1']) ) ) {
			
			
			$dokan_profile_settings['social'] = $_POST['dokan_social'];
			
			update_user_meta($current_user->ID, 'dokan_profile_settings', $dokan_profile_settings );


		}
	}
}

new NBT_Solutions_Dokan_Setting_Social();