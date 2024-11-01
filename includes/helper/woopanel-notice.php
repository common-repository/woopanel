<?php
function woopanel_display_notice($message, $type, $dismissible = false, $important = false, $icon = false) {
    global $woopanel_notices;

    $types_color = array(
        'error' => '',
        'warning' => '',
        'success' => '',
        'info' => ''
    );

    $html = '';


        $classes = 'm-alert m-alert--air m-alert--square alert alert-'. $type;

        if($icon) $classes .= ' m-alert--icon';
        if($important) $classes .= ' m--margin-bottom-30';
        if($dismissible) $classes .= ' alert-dismissible';

        $html .= '<div class="'. $classes .'" role="alert">';

        if($dismissible)
            $html .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>';

        if($icon)
            $html .= '<div class="m-alert__icon"><i class="'. $icon .'"></i></div>';

        $html .= '<div class="m-alert__text">'. $message .'</div>';
        $html .= '</div>';

    echo $html;
}

add_action('init', function() {
    if( ! is_woo_installed() ) {
        wpl_add_notice( 'is_woo_installed', sprintf( __( '%s is inactive. The %sWooCommerce plugin%s must be active for the %s to work. Please %sinstall & activate WooCommerce%s', 'woopanel' ), '<strong>WooPanel</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<strong>WooPanel</strong>' ,'<a href="' . admin_url( 'plugin-install.php?tab=search&s=woocommerce' ) . '">', '&nbsp;&raquo;</a>' ), 'error' );
    }
});

/**
 * Get the count of notices added, either for all notices (default) or for one.
 * particular notice type specified by $notice_type.
 *
 * @since  2.1
 * @param  string $notice_type Optional. The name of the notice type - either error, success or notice.
 * @return int
 */
function wpl_notice_count( $notice_type = '' ) {
    $notice_count = 0;
    $all_notices  = WooPanel()->session->get( 'woopanel_notices', array() );

    if ( isset( $all_notices[ $notice_type ] ) ) {

        $notice_count = count( $all_notices[ $notice_type ] );

    } elseif ( empty( $notice_type ) ) {

        foreach ( $all_notices as $notices ) {
            $notice_count += count( $notices );
        }
    }
    
    return $notice_count;
}

/**
 * Add and store a notice.
 *
 * @since 2.1
 * @param string $message The text to display in the notice.
 * @param string $notice_type Optional. The name of the notice type - either error, success or notice.
 */
function wpl_add_notice( $key, $message, $notice_type = 'success' ) {

    $notices = WooPanel()->session->get( 'woopanel_notices', array() );

    // Backward compatibility.
    if ( 'success' === $notice_type ) {
        $message = apply_filters( 'woopanel_add_message', $message );
    }

    $notices[$key][$notice_type] = apply_filters( 'woopanel_add_' . $notice_type, $message );

    WooPanel()->session->set( 'woopanel_notices', $notices );
}

/**
 * Set all notices at once.
 *
 * @since 2.6.0
 * @param mixed $notices Array of notices.
 */
function wpl_set_notices( $notices ) {
    WooPanel()->session->set( 'woopanel_notices', $notices );
}


/**
 * Unset all notices.
 *
 * @since 2.1
 */
function wpl_clear_notices() {
    WooPanel()->session->set( 'woopanel_notices', null );
}

/**
 * Prints messages and errors which are stored in the session, then clears them.
 *
 * @since 2.1
 */
function wpl_print_notices() {
    $all_notices  = WooPanel()->session->get( 'woopanel_notices', array() );
    $notice_types = apply_filters( 'woopanel_notice_types', array( 'error', 'success', 'notice' ) );

    $html = '';
    $notice_array = array();


    if( ! empty($all_notices) ) {
        foreach( $all_notices as $k => $messages ) {
            if( count($messages) > 1) {
                woopanel_display_notice($messages['error'], 'error', false, true, 'flaticon-danger');
            }else {
                if( $k == 'is_woo_installed' && ! is_woo_installed() ) {
                    woopanel_display_notice($messages['error'], 'error', false, true, 'flaticon-danger');
                }else {
                    if( $k != 'is_woo_installed' ) {
                        foreach( $messages as $notice_type => $message ) {
                            woopanel_display_notice($message, $notice_type, false, true, 'flaticon-danger');
                        }
                    }
                }
            }
        }
    }

    wpl_clear_notices();
}