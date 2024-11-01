<?php

if ( !function_exists( 'is_woopanel_endpoint_url' ) ) {
    function is_woopanel_endpoint_url( $endpoint = false ) {
        global $wp;
        if( isset( $wp->query_vars[ $endpoint ] ) ||
            ($endpoint=='dashboard' || $endpoint==false) && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) )
            return true;

        return false;
    }
}

if ( !function_exists( 'is_woo_installed' ) ) {
	function is_woo_installed() {
		return function_exists( 'WC' );
	}
}
if ( !function_exists( 'is_woo_available' ) ) {
    function is_woo_available() {
        return is_woo_installed() && WooPanel_Admin_Options::get_option('woocommerce_enable');
    }
}

if ( !function_exists( 'is_woopanel' ) ) {
    function is_woopanel()
    {
        global $post;
        if (isset($post->post_type) &&
            'page' === $post->post_type &&
            'publish' === $post->post_status &&
            $post->post_content &&
            has_shortcode($post->post_content, WooPanel_Shortcodes::$shortcodes['dashboard'])) {
            return true;
        }
        return false;
    }
}

if ( !function_exists( 'is_shop_staff' ) ) {
    function is_shop_staff( $user = '', $prerogative = false ) {
        if( !is_user_logged_in() ) false;
        if(!$user) $user = wp_get_current_user();

        $prerogative_role = array( 'administrator', 'shop_manager' );
        $seller_role = array('vendor', 'seller', 'wcfm_vendor', 'dc_vendor', 'wc_product_vendors_admin_vendor', 'wc_product_vendors_manager_vendor', 'shop_staff' );
        $current_role = $user->roles;

        if( $prerogative ) {
            $staff_role = $prerogative_role;
        } else {
            $staff_role = array_unique( array_merge( $prerogative_role, $seller_role ) );
        }

        return !empty( array_intersect( $staff_role, $current_role ) );
    }
}

if ( !function_exists( 'redirect_no_permission' ) ) {
    function redirect_no_permission() {
        if(!is_shop_staff()) woopanel_redirect(home_url());
    }
}
if ( !function_exists( 'redirect_no_permission_ajax' ) ) {
    function redirect_no_permission_ajax() {
        if(!is_shop_staff())
            echo '<script>alert("'. __('You need a higher level of permission.') .'"); window.location.replace("'. home_url() .'");</script>';
    }
}

if ( !function_exists( 'woopanel_is_marketplace' ) ) {
    function woopanel_is_marketplace() {
        $active_plugins = (array) get_option( 'active_plugins', array() );
        if ( is_multisite() ) {
            $active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
        }

        // WCfM Multivendor Marketplace Check
        $is_marketplace = ( in_array( 'wc-multivendor-marketplace/wc-multivendor-marketplace.php', $active_plugins ) || array_key_exists( 'wc-multivendor-marketplace/wc-multivendor-marketplace.php', $active_plugins ) || class_exists( 'WCFMmp' ) ) ? 'wcfmmarketplace' : false;

        // WC Vendors Check
        if( !$is_marketplace )
            $is_marketplace = ( in_array( 'wc-vendors/class-wc-vendors.php', $active_plugins ) || array_key_exists( 'wc-vendors/class-wc-vendors.php', $active_plugins ) || class_exists( 'WC_Vendors' ) ) ? 'wcvendors' : false;

        // WC Marketplace Check
        if( !$is_marketplace )
            $is_marketplace = ( in_array( 'dc-woocommerce-multi-vendor/dc_product_vendor.php', $active_plugins ) || array_key_exists( 'dc-woocommerce-multi-vendor/dc_product_vendor.php', $active_plugins ) || class_exists( 'WCMp' ) ) ? 'wcmarketplace' : false;

        // WC Product Vendors Check
        if( !$is_marketplace )
            $is_marketplace = ( in_array( 'woocommerce-product-vendors/woocommerce-product-vendors.php', $active_plugins ) || array_key_exists( 'woocommerce-product-vendors/woocommerce-product-vendors.php', $active_plugins ) ) ? 'wcpvendors' : false;

        // Dokan Lite Check
        if( !$is_marketplace )
            $is_marketplace = ( in_array( 'dokan-lite/dokan.php', $active_plugins ) || array_key_exists( 'dokan-lite/dokan.php', $active_plugins ) || class_exists( 'WeDevs_Dokan' ) ) ? 'dokan' : false;

        return $is_marketplace;
    }
}

if( !function_exists( 'woopanel_is_vendor' ) ) {
    function woopanel_is_vendor( $user_id = '' ) {
        if( !$user_id ) {
            if( !is_user_logged_in() ) return false;
            $user_id = get_current_user_id();
        }

        $is_marketplace = woopanel_is_marketplace();

        if( $is_marketplace ) {
            if( 'wcvendors' == $is_marketplace ) {
                if ( WCV_Vendors::is_vendor( $user_id ) ) return true;
            } elseif( 'wcmarketplace' == $is_marketplace ) {
                if( is_user_wcmp_vendor( $user_id ) ) return true;
            } elseif( 'wcpvendors' == $is_marketplace ) {
                if( WC_Product_Vendors_Utils::is_vendor( $user_id ) && !WC_Product_Vendors_Utils::is_pending_vendor( $user_id ) ) return true;
            } elseif( in_array( $is_marketplace, array( 'dokan', 'wcfmmarketplace' ) ) ) {
                $user = get_userdata( $user_id );
                $vendor_role = array('seller', 'wcfm_vendor');

                return !empty( array_intersect( $vendor_role, (array) $user->roles ) );
            }
        }

        return apply_filters( 'woopanel_is_vendor', false );
    }
}

if ( !function_exists( 'woopanel_redirect' ) ) {
    function woopanel_redirect($url) {
        wp_safe_redirect($url);
        exit;
    }
}

if ( !function_exists( 'woopanel_current_url' ) ) {
    function woopanel_current_url() {
        if (isset($_SERVER['HTTPS']) &&
            ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
            $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        } else {
            $protocol = 'http://';
        }
        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }
}

if ( !function_exists( 'woopanel_dashboard_url' ) ) {
    function woopanel_dashboard_url($path = null)
    {
        $page_id = absint(WooPanel_Admin_Options::get_option('dashboard_page_id'));

        if ( get_post_status ( $page_id ) != 'publish' ) return null;
        $dashboard_url = get_permalink($page_id);
        return $dashboard_url . $path;
    }
}

if ( !function_exists( 'woopanel_logout_url' ) ) {
    function woopanel_logout_url($redirect = '')
    {
        $redirect = $redirect ? $redirect : home_url();
        return wp_logout_url($redirect);
    }
}

if ( !function_exists( 'woopanel_logo_src' ) ) {
    function woopanel_logo_src( $type = '' ) {
        if( $type == 'header' ) {
            $default_src = WOOPANEL_URL .'assets/images/logo_header.png';
            $imageID = WooPanel_Admin_Options::get_option( 'dashboard_header_logo' );
            if( WooPanel_Admin_Options::get_option( 'customize_dashboard' ) == 'yes' &&
                woopanel_get_option( 'dashboard_header_logo' ) != '-1' ){
                $imageID = woopanel_get_option( 'dashboard_header_logo' );
            }
        } else {
            $default_src = WOOPANEL_URL .'assets/images/logo_shop.png';
            $imageID = WooPanel_Admin_Options::get_option('shop_logo');
            if( woopanel_get_option( 'shop_logo' ) != '-1' ){
                $imageID = woopanel_get_option('shop_logo');
            }
        }
        $image_src = wp_get_attachment_image_src($imageID, 'full')[0];
        return ( $image_src ) ? $image_src : $default_src;
    }
}

if( ! function_exists('woopanel_nice_number') ) {
    function woopanel_nice_number($n) {
        // first strip any formatting;
        $n = (0+str_replace(",", "", $n));

        // is this a number?
        if (!is_numeric($n)) return false;

        // now filter it;
        if ($n > 1000000000000) return round(($n/1000000000000), 2).'T';
        elseif ($n > 1000000000) return round(($n/1000000000), 2).'G';
        elseif ($n > 1000000) return round(($n/1000000), 2).'M';
        elseif ($n > 1000) return round(($n/1000), 2).'K';

        return number_format($n);
    }
}


if( ! function_exists('woopanel_current_user') ) {
    function woopanel_current_user() {
        $user = wp_get_current_user();

        return array_merge(
            (array)$user->data,
            array(
                'roles' => $user->roles[0]
            )
        );
    }
}

if( ! function_exists('woopanel_die') ) {
    function woopanel_die( $content = '', $title = '', $args = array() ) {

    }
}

function woopanel_get_user_to_edit( $user_id ) {
    $user = get_userdata( $user_id );

    if ( $user )
        $user->filter = 'edit';

    return $user;
}

function woopanel_no_content( $args = array() ){
    $defaults = array(
        'icon'     => 'flaticon-open-box',
        'title'    => __( 'No items found.' ),
        'subtitle' => '',
        'class'    => '',
        'style'    => '',
    );
    $r = wp_parse_args($args, $defaults);

    woopanel_get_template_part('global/content', 'empty', array(
        'icon'     => $r['icon'],
        'title'    => $r['title'],
        'subtitle' => $r['subtitle'],
        'class'    => $r['class'],
        'style'    => $r['style'],
    ));
}

if( ! function_exists('woopanel_sanitize') ) {
    function woopanel_sanitize($array) {
        foreach( (array) $array as $k => $v) {
            if ( is_array( $v ) ) {
                $array[$k] = woopanel_sanitize( $v );
            } else {
                $array[$k] = sanitize_text_field( $v );
            }
        }
   
        return $array;
    }
}


if( ! function_exists('woopanel_clean') ) {
    function woopanel_clean( $var ) {
        if ( is_array( $var ) ) {
            return array_map( 'wc_clean', $var );
        } else {
            return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
        }
    }
}

function woopanel_wc_orderby($name = false) {
    $array = array(
        'menu_order'=> esc_html__( 'Custom ordering', 'woocommerce' ),
        'name'      => esc_html__( 'Name', 'woocommerce' ),
        'name_num'  => esc_html__( 'Name (numeric)', 'woocommerce' ),
        'id'        => esc_html__( 'Term ID', 'woocommerce' )
    );

    if( isset($array[$name]) ) {
        return $array[$name];
    }else {
        return $name;
    }
}

function woopanel_withdraw_request($section_id) {
	global $current_user;
	
	if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
		return;
	}
	
	$post_data = wp_unslash( $_POST );
	$amount          = '';
	$withdraw_method = '';
	
	
	if( isset($post_data['witdraw_amount']) && isset($post_data['withdraw_method']) ) {
		$amount          = sanitize_text_field( $post_data['witdraw_amount'] );
		$withdraw_method = sanitize_text_field( $post_data['withdraw_method'] );

        $errors           = new WP_Error();
        $limit           = (new Dokan_Withdraw)->get_withdraw_limit();
        $balance         = round( dokan_get_seller_balance( dokan_get_current_user_id(), false ), 2 );
        $withdraw_amount = (float) $post_data['witdraw_amount'];

        if ( empty( $withdraw_amount ) ) {
            $errors->add( 'errors', __( 'Withdraw amount required ', 'dokan-lite' ) );
        } elseif ( $withdraw_amount > $balance ) {
            $errors->add( 'errors', __( 'You don\'t have enough balance for this request', 'dokan-lite' ) );
        } elseif ( $withdraw_amount < $limit ) {
            $errors->add( 'errors', sprintf( __( 'Withdraw amount must be greater than %d', 'dokan-lite' ), (new Dokan_Withdraw)->get_withdraw_limit() ) );
        }

        if ( empty( sanitize_text_field( $post_data['withdraw_method'] ) ) ) {
            $errors->add( 'errors', __( 'withdraw method required', 'dokan-lite' ) );
        }
	
		if ( $errors->get_error_codes() ) {
			$error_msg = '';
			foreach( $errors->errors['errors'] as $k => $error ) {
				$error_msg .= $error;		
			}

			if( ! empty($error_msg) ) {
				echo woopanel_render_alert($error_msg, 'error');
			}
		}else {
			$data_info = array(
				'user_id' => $current_user->ID,
				'amount'  => $amount,
				'status'  => 0,
				'method'  => $withdraw_method,
				'ip'      => dokan_get_client_ip(),
				'notes'   => '',
			);

			$update = (new Dokan_Withdraw)->insert_withdraw( $data_info );
			echo woopanel_render_alert( __('Your request has been received successfully and being reviewed!', 'dokan-lite' ), 'success');
			
			return;
		}
		
	}	
	
	
	if ( (new Dokan_Withdraw)->has_pending_request( $current_user->ID ) ) {
		
		if( ! isset($post_data['witdraw_amount']) && ! isset($post_data['withdraw_method']) ) {
			echo woopanel_render_alert( sprintf( '<p>%s</p><p>%s</p>', __( 'You already have pending withdraw request(s).', 'dokan-lite' ), __( 'Please submit your request after approval or cancellation of your previous request.', 'dokan-lite' ) ), 'error' );
		}
		
		$withdraw_requests = (new Dokan_Withdraw)->get_withdraw_requests( $current_user->ID );
			
		if ( $withdraw_requests ) {
		?>
			<table class="dokan-table dokan-table-striped">
				<tr>
					<th><?php esc_html_e( 'Amount', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Method', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Date', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Cancel', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Status', 'dokan-lite' ); ?></th>
				</tr>

				<?php foreach ( $withdraw_requests as $request ) { ?>

					<tr>
						<td><?php echo wc_price( $request->amount ); ?></td>
						<td><?php echo esc_html( dokan_withdraw_get_method_title( $request->method ) ); ?></td>
						<td><?php echo esc_html( dokan_format_time( $request->date ) ); ?></td>
						<td>
							<?php
							$url = add_query_arg( array(
								'action' => 'dokan_cancel_withdrow',
								'id'     => $request->id
							), dokan_get_navigation_url( 'withdraw' ) );
							?>
							<a href="<?php echo esc_url( wp_nonce_url( $url, 'dokan_cancel_withdrow' ) ); ?>">
								<?php esc_html_e( 'Cancel', 'dokan-lite' ); ?>
							</a>
						</td>
						<td>
							<?php
								if ( $request->status == 0 ) {
									echo '<span class="label label-danger">' . esc_html__( 'Pending Review', 'dokan-lite' ) . '</span>';
								} elseif ( $request->status == 1 ) {
									echo '<span class="label label-warning">' . esc_html__( 'Accepted', 'dokan-lite' ) . '</span>';
								}
							?>
						</td>
					</tr>

				<?php } ?>

			</table>
		<?php
		}
		
		return;
	}else if( ! (new Dokan_Withdraw)->has_withdraw_balance( $current_user->ID ) ) {
		echo woopanel_render_alert( __('You don\'t have sufficient balance for a withdraw request!', 'dokan-lite' ), 'error');
		
		return;
	}
	

	

	

	// $withdraw_requests = (new Dokan_Withdraw)->get_withdraw_requests( $current_user->ID );

			
	$payment_methods = array_intersect( dokan_get_seller_active_withdraw_methods(), dokan_withdraw_get_active_methods() );
	
	woopanel_form_field(
		'witdraw_amount',
		array(
			'id'          => 'witdraw_amount',
			'type'		  => 'number',
			'label'       => esc_html__( 'Withdraw Amount', 'dokan-lite' ),
			'form_inline' => true,
			'custom_attributes' => array(
				'min'		  => esc_attr( dokan_get_option( 'withdraw_limit', 'dokan_withdraw', 0 ) ),
				'placeholder' => '0.00'
			)
		),
		$amount
	);
	
	$payment_options = array();
	foreach ( $payment_methods as $method_name ) {
		$payment_options[$method_name] = dokan_withdraw_get_method_title( $method_name );
	}

	woopanel_form_field(
		'withdraw_method',
		array(
			'id'          => 'withdraw_method',
			'type'		  => 'select',
			'label'       => esc_html__( 'Payment Method', 'dokan-lite' ),
			'form_inline' => true,
			'options' => $payment_options

		),
		$withdraw_method
	);
}

function woopanel_withdraw_approved($section_id) {
	global $current_user;
	
	if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
		return;
	}
	
	$requests = (new Dokan_Withdraw)->get_withdraw_requests( $current_user->ID, 1, 100 );
	if ( $requests ) {
		?>
		<table class="dokan-table dokan-table-striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Amount', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Method', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Date', 'dokan-lite' ); ?></th>
				</tr>
			</thead>
			<tbody>

			<?php foreach ( $requests as $row ) { ?>
				<tr>
					<td><?php echo wc_price( $row->amount ); ?></td>
					<td><?php echo esc_html( dokan_withdraw_get_method_title( $row->method ) ); ?></td>
					<td><?php echo esc_html( date_i18n( 'M j, Y g:ia', strtotime( $row->date ) ) ); ?></td>
				</tr>
			<?php } ?>

			</tbody>
		</table>
		<?php
	}else {
		_e( 'Sorry, no transactions were found!', 'dokan-lite' );
	}
}

function woopanel_withdraw_cancelled($section_id) {
	global $current_user;
	
	if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
		return;
	}
	
	$requests = (new Dokan_Withdraw)->get_withdraw_requests( $current_user->ID, 2, 100 );

	if ( $requests ) {
		?>
		<table class="dokan-table dokan-table-striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Amount', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Method', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Date', 'dokan-lite' ); ?></th>
					<th><?php esc_html_e( 'Note', 'dokan-lite' ); ?></th>
				</tr>
			</thead>
			<tbody>

			<?php foreach ( $requests as $row ) { ?>
				<tr>
					<td><?php echo wc_price( $row->amount ); ?></td>
					<td><?php echo esc_html( dokan_withdraw_get_method_title( $row->method ) ); ?></td>
					<td><?php echo esc_html( date_i18n( 'M j, Y g:ia', strtotime( $row->date ) ) ); ?></td>
					<td><?php echo wp_kses_post( $row->note ); ?></td>
				</tr>
			<?php } ?>

			</tbody>
		</table>
		<?php
	}else {
		_e( 'Sorry, no transactions were found!', 'dokan-lite' );
	}

}

function woopanel_render_alert( $msg, $type = 'warrning') {
	return '<div class="m-alert m-alert--air m-alert--square alert alert-success m-alert--icon m-alert-alt m-alert-'. $type .'">'. $msg .'</div>';
}