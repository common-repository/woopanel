<?php

/**
 * Tempalte shortcode class file
 *
 * @load all shortcode for template  rendering
 */
class WooPanel_Shortcodes {

    private static $user_can = 'edit_posts';
    public static $shortcodes = array(
        'dashboard' => 'woopanel',
    );

    function __construct()
    {
        add_action( 'wp', array($this, 'unhook_wp_head_footer') );
        add_shortcode( self::$shortcodes['dashboard'], array($this, 'load_template_files') );
        add_action( 'wp_enqueue_scripts', array($this, 'load_page_scripts'), 99999, 1 );
        add_filter( 'document_title_parts', array($this, 'woopanel_document_title') );
    }

    function load_template_files()
    {
        ob_start();
        if ( is_user_logged_in() ) {
            //do_action('woopanel_notices');
            woopanel_get_template('woopanel-layout.php');
        } else {
            if( is_woo_installed() ){
                woocommerce_login_form();
            } else {
                woopanel_redirect( wp_login_url( woopanel_current_url() ) );
                // wp_login_form();
            }
        }
        return ob_get_clean();
    }




    function load_page_scripts() {
        global $wp_scripts, $wp_styles, $post;
        if( is_woopanel() ) {

            $wp_scripts->queue = array();
            $wp_styles->queue = array();

            remove_action('wp_head', '_admin_bar_bump_cb');

            wp_enqueue_style( 'dashicons' );
            wp_enqueue_style('scrollbar', WOOPANEL_URL .'vendors/perfect-scrollbar/css/perfect-scrollbar.css', false, '1.4.0', 'all' );
            wp_enqueue_style('bootstrap-select', WOOPANEL_URL .'vendors/bootstrap-select/dist/css/bootstrap-select.css', false, '1.13.0-beta', 'all' );
            wp_enqueue_style('metronic', WOOPANEL_URL .'vendors/metronic/css/styles.css', false, WooPanel()->version, 'all' );

            wp_enqueue_style('flaticon', WOOPANEL_URL .'vendors/flaticon/css/flaticon.css', false, WooPanel()->version, 'all' );
            wp_enqueue_style('line-awesome', WOOPANEL_URL .'vendors/line-awesome/css/line-awesome.css', false, '1.1.0', 'all' );
            wp_enqueue_style('fontawesome5', WOOPANEL_URL .'vendors/fontawesome5/css/all.min.css', false, '5.2.0', 'all' );
            wp_enqueue_style('toastr', WOOPANEL_URL .'vendors/toastr/toastr.css', false, WooPanel()->version, 'all' );

            wp_enqueue_style('metronic-styles', WOOPANEL_URL .'themes/default/style.css', false, WooPanel()->version, 'all' );
            wp_enqueue_style('woopanel', WOOPANEL_URL . 'assets/css/style.css', false, WooPanel()->version, 'all' );

            wp_enqueue_script('popper', WOOPANEL_URL .'vendors/popper.js/dist/umd/popper.js', array('jquery'), '1.14.4' , true );
            wp_enqueue_script('bootstrap', WOOPANEL_URL .'vendors/bootstrap/dist/js/bootstrap.min.js', array('jquery'), '4.1.3' , true );
            wp_enqueue_script('bootstrap-select', WOOPANEL_URL .'vendors/bootstrap-select/dist/js/bootstrap-select.js', array('jquery'), '1.13.0-beta' , true );
            wp_enqueue_script('cookie', WOOPANEL_URL .'vendors/js-cookie/src/js.cookie.js', array('jquery'), '2.2.0' , true );
            wp_enqueue_script('moment', WOOPANEL_URL .'vendors/moment/min/moment.min.js', false, WooPanel()->version , true );
            wp_enqueue_script('tooltip', WOOPANEL_URL .'vendors/tooltip.js/dist/umd/tooltip.min.js', array('jquery'), '1.3.0' , true );
            wp_enqueue_script('scrollbar', WOOPANEL_URL .'vendors/perfect-scrollbar/dist/perfect-scrollbar.js', array('jquery'), '1.4.0' , true );
            wp_enqueue_script('wnumb', WOOPANEL_URL .'vendors/wnumb/wNumb.js', array('jquery'), WooPanel()->version , true );

            wp_enqueue_script('metronic-scripts', WOOPANEL_URL .'vendors/metronic/scripts.bundle.js', array('jquery'), WooPanel()->version , true );

            wp_enqueue_script('nb-tags-box', WOOPANEL_URL .'assets/js/nb-tags-box.js', array('jquery'), WooPanel()->version , true );
            wp_enqueue_script('nb-media-uploader', WOOPANEL_URL .'assets/js/nb-media-uploader.js', array('jquery'), WooPanel()->version , true );


            add_filter('show_admin_bar', '__return_false');

            wp_enqueue_script('jquery-blockui', WOOPANEL_URL . 'vendors/blockUI/jquery.blockUI.js', array(), '2.70.0', true);
            wp_enqueue_script('toastr', WOOPANEL_URL .'vendors/toastr/toastr.min.js', array('jquery'), '2.1.4', true );

            wp_register_style( 'wpl-bootstrap-datepicker', WOOPANEL_URL . 'vendors/bootstrap-datepicker/bootstrap-datepicker3.min.css', WooPanel()->version, 'all' );
            wp_register_script('wpl-bootstrap-datepicker', WOOPANEL_URL . 'vendors/bootstrap-datepicker/bootstrap-datepicker.min.js', array(), false, true);

	        if ( is_woo_installed() ) {
				$price = str_replace('%2$s', 'number', get_woocommerce_price_format());
				$price = str_replace('%1$s', get_woocommerce_currency_symbol(), $price);
				$decimals = wc_get_price_decimals();
				$decimal_separator = wc_get_price_decimal_separator();
                $thousand_separator = wc_get_price_thousand_separator();
			}else {
				$price = '%1$s';
				$decimals = 2;
				$decimal_separator = '.';
				$thousand_separator = ',';
			}
			
			$extra = array(
				'format_money' => $price,
				'decimals' => $decimals,
				'decimal_separator' => $decimal_separator,
				'thousand_separator' => $thousand_separator
			);

            $myvars = array_merge(
                $extra,
                array( 
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'json_url' => home_url() . '/wp-json/woopanel/v2/%action%',
                'label' => array(
                    'item' => __('order', 'nb-dashboard'),
                    'items' => __('orders', 'nb-dashboard'),
                    'i18n_deny'            => esc_js( __( 'You do not have permission for this action!', 'woopanel' ) ),
                ),
            ));

            //Dashboard
            if( is_woopanel_endpoint_url('dashboard') ) {
                wp_enqueue_style( 'admin-daterangepicker', WOOPANEL_URL . 'vendors/daterangepicker/daterangepicker.css', false, '3.0.3', 'all' );
                wp_enqueue_script('moment.daterangepicker', WOOPANEL_URL . 'vendors/daterangepicker/moment.min.js', array(), false, true);
                wp_enqueue_script('daterangepicker', WOOPANEL_URL . 'vendors/daterangepicker/daterangepicker.js', array(), '3.0.3', true);
                wp_enqueue_script('Chart', 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.js', array(), '2.7.2', true);

                if ( is_woo_installed() ) {
                    wp_enqueue_script('dashboard-woocommerce', WOOPANEL_URL . 'assets/js/dashboard-woocommerce.js', array(), WooPanel()->version, true);
                }

                wp_enqueue_script('jquery-ui-sortable', home_url() . 'wp-includes/js/jquery/ui/sortable.min.js', array(), '1.11.4', true);
            }

            // Order
            if( is_woopanel_endpoint_url('orders') || is_woopanel_endpoint_url('order') ) {
                wp_enqueue_style('select2', WOOPANEL_URL .'vendors/select2/select2.min.css', false, '4.0.3','all');
                wp_enqueue_style('select2-bootstrap', WOOPANEL_URL .'vendors/select2/select2-bootstrap.min.css', false, '0.1.0-beta.4', 'all');

                wp_enqueue_script('select2', WOOPANEL_URL . 'vendors/select2/select2.full.min.js', array(), '4.0.3', true );

                wp_enqueue_script( 'wc-backbone-modal', WC()->plugin_url() . '/assets/js/admin/backbone-modal.min.js', array( 'underscore', 'backbone', 'wp-util' ), WC_VERSION );
                wp_enqueue_script('wpel-orders', WOOPANEL_URL . 'assets/js/orders.js', array(), false, true);
                wp_localize_script(
                    'wpel-orders',
                    'wpel_orders_params',
                    array(
                        'ajax_url'      => admin_url( 'admin-ajax.php' ),
                        'preview_nonce' => wp_create_nonce( 'woocommerce-preview-order' ),
                        'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
                        'add_order_note_nonce'          => wp_create_nonce( 'add-order-note' ),
                        'delete_order_note_nonce'       => wp_create_nonce( 'delete-order-note' ),
                        'i18n_delete_note'              => __( 'Are you sure you wish to delete this note? This action cannot be undone.', 'woocommerce' ),
                        'i18n_add_note'              => __( 'Please enter your note!', 'woopanel' ),
                    )
                );
            }


            if( is_woopanel_endpoint_url('coupon') ) {
                wp_enqueue_style('wpl-bootstrap-datepicker');
                wp_enqueue_script('wpl-bootstrap-datepicker');
            }

            if( is_woopanel_endpoint_url('profile') ) {
                wp_enqueue_style('wpl-bootstrap-datepicker');
                wp_enqueue_script('wpl-bootstrap-datepicker');
            }

            if( is_woopanel_endpoint_url('article') || is_woopanel_endpoint_url('product') ) {
                wp_enqueue_script('posts', WOOPANEL_URL . 'assets/js/posts.js', array(), WooPanel()->version, true );
            }
            if( is_woopanel_endpoint_url('settings') || is_woopanel_endpoint_url('product-categories') ) {
                wp_enqueue_media();
				wp_enqueue_style( 'jquery.timepicker', WOOPANEL_URL . 'vendors/timepicker/jquery.timepicker.min.css', false, '3.0.3', 'all' );
				wp_enqueue_script('jquery.timepicker', WOOPANEL_URL . 'vendors/timepicker/jquery.timepicker.min.js', array(), WooPanel()->version, true );
				
				wp_enqueue_style( 'jquery.magnific-popup', WOOPANEL_URL . 'includes/modules/dokan/assets/css/magnific-popup.css', false, '3.0.3', 'all' );
				wp_enqueue_script('jquery.magnific-popup', WOOPANEL_URL . 'includes/modules/dokan/assets/js/jquery.magnific-popup.min.js', array(), WooPanel()->version, true );
				
                wp_enqueue_script('settings', WOOPANEL_URL . 'assets/js/settings.js', array(), WooPanel()->version, true );
            }

            if( is_woopanel_endpoint_url('product-attributes') ) {
                do_action('woopanel_product_attribute_enqueue_scripts');
            }

            if( is_woopanel_endpoint_url('product') ) {
                wp_enqueue_style('wpl-bootstrap-datepicker');
                wp_enqueue_script('wpl-bootstrap-datepicker');
                wp_enqueue_script('jquery-ui-sortable');


                wp_enqueue_style('select2', WOOPANEL_URL .'vendors/select2/select2.min.css', false, '4.0.3','all');
                wp_enqueue_style('select2-bootstrap', WOOPANEL_URL .'vendors/select2/select2-bootstrap.min.css', false, '0.1.0-beta.4', 'all');

                wp_enqueue_script('select2', WOOPANEL_URL . 'vendors/select2/select2.full.min.js', array(), '4.0.3', true );

                wp_enqueue_script('woopanel-serializejson', WOOPANEL_URL . 'vendors/serializeJSON/jquery.serializejson.min.js', array(), '2.9.0', true );
                wp_enqueue_script('woopanel-meta-boxes-product', WOOPANEL_URL . 'assets/js/meta-boxes-product.js', array(), WooPanel()->version, true );

                wp_enqueue_script('woopanel-meta-boxes-product', WOOPANEL_URL . 'assets/js/meta-boxes-product.js', array(), WooPanel()->version, true );
                $myvars['product'] = array(
                    'product_types' => array_unique( array_merge( array( 'simple', 'grouped', 'variable', 'external' ), array_keys( wc_get_product_types() ) ) ),
                    'search_products_nonce'     => wp_create_nonce( 'search-products' ),
                    'load_variations_nonce'     => wp_create_nonce( 'load-variations' ),
                    'add_attribute_nonce'           => wp_create_nonce( 'add-attribute' ),
                    'save_attributes_nonce'         => wp_create_nonce( 'save-attributes' ),
                    'save_variations_nonce'               => wp_create_nonce( 'save-variations' ),
                    'add_variation_nonce'                 => wp_create_nonce( 'add-variation' ),
                    'link_variation_nonce'                => wp_create_nonce( 'link-variations' ),
                    'delete_variations_nonce'             => wp_create_nonce( 'delete-variations' ),
                    'search_customers_nonce'    => wp_create_nonce( 'search-customers' ),
                    'input_price_nonce'       => wp_create_nonce( 'input-price' ),
                    'save_price_nonce'        => wp_create_nonce( 'save-price' ),

                    'post_id'                   => isset( $_GET['id'] ) ? $_GET['id'] : '',
                    'label_remove_attribute'    => __('Remove this attribute?', 'woocommerce'),
                    'variations_per_page'                 => absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_per_page', 15 ) ),
                    'i18n_delete_all_variations'          => esc_js( __( 'Are you sure you want to delete all variations? This cannot be undone.', 'woocommerce' ) ),
                    'i18n_last_warning'                   => esc_js( __( 'Last warning, are you sure?', 'woocommerce' ) ),
                    'i18n_variation_count_single'         => esc_js( __( '%qty% variation', 'woocommerce' ) ),
                    'i18n_variation_count_plural'         => esc_js( __( '%qty% variations', 'woocommerce' ) ),
                    'bulk_edit_variations_nonce'          => wp_create_nonce( 'bulk-edit-variations' ),
                    'i18n_edited_variations'              => esc_js( __( 'Save changes before changing page?', 'woocommerce' ) ),
                    'i18n_link_all_variations'            => esc_js( sprintf( __( 'Are you sure you want to link all variations? This will create a new variation for each and every possible combination of variation attributes (max %d per run).', 'woocommerce' ), defined( 'WC_MAX_LINKED_VARIATIONS' ) ? WC_MAX_LINKED_VARIATIONS : 50 ) ),
                    'i18n_variation_added'                => esc_js( __( 'variation added', 'woocommerce' ) ),
                    'i18n_variations_added'               => esc_js( __( 'variations added', 'woocommerce' ) ),
                    'i18n_no_variations_added'            => esc_js( __( 'No variations added', 'woocommerce' ) ),
                    'i18n_first_page'            => esc_js( __( 'This is first page', 'woopanel' ) ),
                    'i18n_last_page'            => esc_js( __( 'This is last page', 'woopanel' ) ),
                    'i18n_save_attribute'            => esc_js( __( 'Save attribute', 'woocommerce' ) ),
                    'i18n_update'            => esc_js( __( 'Update', 'woocommerce' ) ),
                    'i18n_remove_variation'               => esc_js( __( 'Are you sure you want to remove this variation?', 'woocommerce' ) ),
                    
                    'variations_per_page'                 => absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_per_page', 15 ) ),
                );

                do_action('woopanel_product_enqueue_scripts');
            }

            if( is_woopanel_endpoint_url('faq') || is_woopanel_endpoint_url('product') ) {
                wp_enqueue_editor();
                wp_enqueue_media();
                wp_enqueue_script('faqs', WOOPANEL_URL . 'admin/assets/js/faqs.js',  array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-resizable'), '4.0.3', true );
            }

            if ( is_woo_installed() ) {
                wp_enqueue_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', array('jquery'), '2.70', true);
            }

            wp_enqueue_script('navigation', WOOPANEL_URL .'assets/js/navigation.js', array('jquery'), WooPanel()->version, true );
            wp_enqueue_script('woopanel-admin-modules', WOOPANEL_URL .'assets/js/admin-modules.js', array('jquery'), WooPanel()->version, true );
            wp_enqueue_script('woopanel', WOOPANEL_URL .'assets/js/main.js', array('jquery'), WooPanel()->version, true );
            wp_localize_script( 'woopanel', 'WooPanel', $myvars );
        }
    }

    function unhook_wp_head_footer(){
        global $wp_filter;

        $args_head_action = array(
            '_wp_render_title_tag',
            'wp_enqueue_scripts',
            'wp_resource_hints',
            'feed_links',
            'feed_links_extra',
            'rsd_link',
            'wlwmanifest_link',
            'adjacent_posts_rel_link_wp_head',
            'locale_stylesheet',
            'noindex',
            'print_emoji_detection_script',
            'wp_print_styles',
            'wp_print_head_scripts',
            'wp_generator',
            'rel_canonical',
            'wp_shortlink_wp_head',
            'wp_custom_css_cb',
            'wp_site_icon'
        );

        $args_fooder_action = array(
            'wp_print_footer_scripts',
            'wp_admin_bar_render'
        );

        if( is_woopanel() ) {
            foreach ( $wp_filter['wp_head'] as $priority => $wp_head_hooks ) {
                if( is_array( $wp_head_hooks ) ){
                    foreach ( $wp_head_hooks as $wp_head_hook ) {
                        if( in_array( $wp_head_hook['function'], $args_head_action) ) continue;
                        remove_action( 'wp_head', $wp_head_hook['function'], $priority );
                    }
                }
            }
            foreach ($wp_filter['wp_footer'] as $priority => $wp_footer_hooks ) {
                if( is_array( $wp_footer_hooks ) ){
                    foreach ( $wp_footer_hooks as $wp_footer_hook ) {
                        if( in_array( $wp_footer_hook['function'], $args_fooder_action) ) continue;
                        remove_action( 'wp_footer', $wp_footer_hook['function'], $priority );
                    }
                }
            }
        }
    }

    function woopanel_document_title( $title ){
        if ( is_woopanel() ) {
            if (is_woopanel_endpoint_url('dashboard')) $title['title'] = __( 'Dashboard' );

            if (is_woopanel_endpoint_url('articles')) $title['title'] = __('Articles', 'woopanel');
            if (is_woopanel_endpoint_url('article')) {
                $title['title'] = isset($_GET['id']) ? sprintf( __( 'Edit article %s', 'woopanel' ), '#'.$_GET['id'] ) : __('Add new article', 'woopanel');
            }

            if (is_woopanel_endpoint_url('products')) $title['title'] = __('Products', 'woocommerce');
            if (is_woopanel_endpoint_url('product')) {
                $title['title'] = isset($_GET['id']) ? sprintf( __( 'Edit product', 'woopanel' ), '#'.$_GET['id'] ) : __('Add new product', 'woocommerce');
            }

            if (is_woopanel_endpoint_url('orders')) $title['title'] = __('Orders', 'woocommerce');
            if (is_woopanel_endpoint_url('order')) {
                $title['title'] = sprintf( __( 'View order: %s', 'woocommerce' ), '#'.$_GET['id'] );
            }

            if (is_woopanel_endpoint_url('coupons')) $title['title'] = __('Coupons', 'woopanel');
            if (is_woopanel_endpoint_url('coupon')) {
                $title['title'] = isset($_GET['id']) ? sprintf( __( 'Edit coupon %s', 'woopanel' ), '#'.$_GET['id'] ) : __('Add new coupon', 'woocommerce');
            }

            if (is_woopanel_endpoint_url('customers')) $title['title'] = __('Customers', 'woopanel');
            if (is_woopanel_endpoint_url('customer')) {
                $title['title'] = __('View Customer', 'woopanel');
            }

            if (is_woopanel_endpoint_url('comments')) $title['title'] = __( 'Comments' );
            if (is_woopanel_endpoint_url('comment')) $title['title'] = __( 'Edit Comment' );

            if (is_woopanel_endpoint_url('reviews')) $title['title'] = __( 'Reviews', 'woocommerce' );
            if (is_woopanel_endpoint_url('review')) $title['title'] = __( 'Edit Review', 'woopanel' );

            if (is_woopanel_endpoint_url('profile')) $title['title'] = __( 'My Profile', 'woopanel' );

            if (is_woopanel_endpoint_url('settings')) $title['title'] = __( 'Settings' );

            $title['site'] = __('WooPanel', 'woopanel');

            if( WooPanel_Admin_Options::get_option( 'customize_dashboard' ) == 'yes' &&
                woopanel_get_option( 'shop_name' ) ){
                $title['site'] = woopanel_get_option( 'shop_name' );
            }

        }
        return $title;
    }
}