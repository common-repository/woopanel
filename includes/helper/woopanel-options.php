<?php
global $woopanel_options;

$woopanel_options = array();

$woopanel_options = array(
    'general' => [
        'menu_title' => __( 'General', 'woopanel' ),
        'title'      => __( 'General Settings', 'woopanel' ),
        'desc'       => '',
        'parent'     => '',
        'icon'       => '',
        'type'       => 'personal',
        'fields'     => array(
            array(
                'id'       => 'shop_name',
                'type'     => 'text',
                'title'    => __( 'Shop name', 'woopanel'  ),
            ),
            array(
                'id'       => 'shop_slogan',
                'type'     => 'textarea',
                'title'    => __( 'Shop slogan', 'woopanel'  ),
            ),
            array(
                'id'       => 'shop_logo',
                'type'     => 'image',
                'title'    => __( 'Shop Logo', 'woopanel'  ),
            ),
        )
    ],
    'live_chat' => [
        'menu_title' => __( 'Live Chat', 'woopanel' ),
        'title'      => __( 'Live Chat Settings', 'woopanel' ),
        'desc'       => '',
        'parent'     => '',
        'icon'       => '',
        'type'       => 'personal',
        'fields'     => array(
            array(
                'id'       => 'live_chat_position',
                'type'     => 'select',
                'title'    => __( 'Position', 'woopanel'  ),
                'options'  => array(
                    'wp_head'   => __('Header (before 	&lt;/head&gt;)', 'woopanel'),
                    'wp_footer' => __('Footer (before 	&lt;/body&gt;)', 'woopanel')
                ),
                'default' => 'c'
            ),
            array(
                'id'       => 'live_chat_embed',
                'type'     => 'textarea',
                'title'    => __( 'Embed Code', 'woopanel'  ),
                'description' => __('Paste embed code here.', 'woopanel')
            )
        )
    ],
);

if( WooPanel_Admin_Options::get_option('customize_dashboard') == 'yes' ){
    $woopanel_options['shop_customizer'] = array(
        'menu_title' => __( 'Customize' ),
        'title'      => __( 'Shop Customizer', 'woopanel' ),
        'desc'       => '',
        'parent'     => '',
        'icon'       => '',
        'type'       => 'personal',
        'fields'     => array(
            array(
                'id'       => 'dashboard_header_logo',
                'type'     => 'image',
                'title'    => __( 'Dashboard Header Logo', 'woopanel'  ),
            ),
        )
    );
}

function woopanel_all_options(){
    global $woopanel_options;

    $shop_options = get_option( 'woopanel_options' );
    $seller_options = get_user_meta( get_current_user_id(), 'seller_options' );

    $options_return = array();

    foreach ( $woopanel_options as $section ) {
        if($section['type'] == 'personal') {
            foreach ($section['fields'] as $option) {
                $options_return[$option['id']] = isset($seller_options[0][$option['id']]) ? $seller_options[0][$option['id']] : null;
            }
        }
    }

    return $options_return;
}

function woopanel_get_option( $id ){
	if( isset(woopanel_all_options()[$id]) ) {
		return woopanel_all_options()[$id];
	}
	return false;
}

function woopanel_set_options( $args = array() ){
    $options_name = 'woopanel_options';
    $options = get_option( $options_name );
    $args = wp_parse_args( $args, $options );
    update_option( $options_name, $args );
}

function woopanel_get_option_type( $id ){
	global $woopanel_options;

	foreach ( $woopanel_options as $section ) {
		foreach ( $section['fields'] as $fields ) {
			if( $fields['id'] === $id ) return $fields['type'];
		}
	}
	return null;
}

function woopanel_save_options() {
    global $woopanel_options;

    if ( is_woopanel_endpoint_url('settings') && ( isset($_POST['save']) || isset($_POST['save1']) ) ) {
        $user_ID = get_current_user_id();
        $options = $_POST;
        $seller_options = array();

        if ($user_ID !== absint($options['user_ID'])) return false;

        unset($options['user_ID']);
        unset($options['save']);
        unset($options['save1']);
        unset($options['_wpnonce']);
        unset($options['_wp_http_referer']);

        foreach ( $woopanel_options as $section ) {
            if($section['type'] == 'personal') {
                foreach ( $section['fields'] as $field ){
                    $seller_options[$field['id']] = $options[$field['id']];
                }
            }
        }

        update_user_meta( $user_ID, 'seller_options', $seller_options );

        wpl_add_notice( "settings", __('Settings saved.'), 'success' );
    }
}

add_action('woopanel_init', 'woopanel_save_options');