<?php
class WooPanel_Dokan_Ajax {
	
	function __construct() {
		add_action( 'wp_ajax_woopanel_load_shipping_method', array( $this, 'load_shipping_method' ) );
		add_action( 'wp_ajax_woopanel_add_shipping_method', array( $this, 'add_shipping_method' ) );
		add_action( 'wp_ajax_woopanel_delete_shipping_method', array( $this, 'delete_shipping_method' ) );
		add_action( 'wp_ajax_woopanel_edit_shipping_method', array( $this, 'edit_shipping_method' ) );
    }
	
	public function load_shipping_method() {
		global $current_user, $wpdb;
        $zone_id = isset( $_POST['zoneID'] ) ? $_POST['zoneID'] : '';
		$instance_id = isset( $_POST['instance_id'] ) ? $_POST['instance_id'] : '';
        if ( $zone_id == '' ) {
            wp_send_json_error( __( 'Shipping zone not found', 'dokan' ) );
        }
		
        $sql = "SELECT * FROM {$wpdb->prefix}dokan_shipping_zone_methods WHERE `zone_id`={$zone_id} AND `seller_id`={$current_user->ID} AND `instance_id` = {$instance_id}";
        $result = $wpdb->get_row( $sql );
		
            $default_settings = array(
                'title'       => NBT_Solutions_Dokan_Setting_Shipping::get_method_label( $result->method_id ),
                'description' => __( 'Lets you charge a rate for shipping', 'dokan' ),
                'cost'        => '0',
                'tax_status'  => 'none',
				'calculation_type' => '',
				'no_class_cost' => ''
            );
		
		
		$settings = ! empty( $result->settings ) ? maybe_unserialize( $result->settings ) : array();
		$settings = wp_parse_args( $settings, $default_settings );
		
		$json = array();
		$json = $settings;

		wp_send_json_success( $json );
	}
	
	/**
     * Add new shipping method for a zone
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function add_shipping_method() {


        $zone_id = isset( $_POST['zoneID'] ) ? $_POST['zoneID'] : '';

        if ( $zone_id == '' ) {
            wp_send_json_error( __( 'Shipping zone not found', 'dokan' ) );
        }

        if ( empty( $_POST['method'] ) ) {
            wp_send_json_error( __( 'Please select a shipping method', 'dokan' ) );
        }

        $data = array(
            'zone_id'   => $zone_id,
            'method_id' => $_POST['method']
        );

        $result = Dokan_Shipping_Zone::add_shipping_methods( $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() , 'dokan' );
        }

        wp_send_json_success( __( 'Shipping method added successfully', 'dokan' ) );
    }
	
	public function delete_shipping_method() {
       $zone_id = isset( $_POST['zoneID'] ) ? $_POST['zoneID'] : '';

        if ( $zone_id == '' ) {
            wp_send_json_error( __( 'Shipping zone not found', 'dokan' ) );
        }

        if ( empty( $_POST['instance_id'] ) ) {
            wp_send_json_error( __( 'Shipping method not found', 'dokan' ) );
        }

        $data = array(
            'zone_id'     => $zone_id,
            'instance_id' => $_POST['instance_id']
        );

        $result = Dokan_Shipping_Zone::delete_shipping_methods( $data );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() , 'dokan' );
        }

        wp_send_json_success( __( 'Shipping method deleted', 'dokan' ) );
	}
	
	public function edit_shipping_method() {

        $zone_id = isset( $_POST['zoneID'] ) ? $_POST['zoneID'] : '';

        if ( $zone_id == '' ) {
            wp_send_json_error( __( 'Shipping zone not found', 'dokan' ) );
        }

        $defaults = array(
            'instance_id' => '',
            'method_id'   => '',
            'zone_id'     => $zone_id,
            'settings'    => array()
        );

        parse_str($_POST['data'], $data);

        $args = dokan_parse_args( $data['data'], $defaults );


        if ( empty( $args['settings']['title'] ) ) {
            wp_send_json_error( __( 'Shipping title must be required', 'dokan' ) );
        }

        $result = Dokan_Shipping_Zone::update_shipping_method( $args );

        wp_send_json_success( $args );
	}
}

new WooPanel_Dokan_Ajax();
