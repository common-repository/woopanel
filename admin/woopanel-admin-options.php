<?php

class WooPanel_Admin_Options {

    public static $options_name = 'woopanel_admin_options';

    public static $options_fields;

    public function __construct() {
        self::$options_fields = array(
            array(
                'title'    => __( 'Dashboard page', 'woopanel' ),
                'desc'     => '',
                'id'       => 'dashboard_page_id',
                'type'     => 'single_select_page',
                'default'  => '',
                'class'    => 'wc-enhanced-select-nostd',
                'css'      => 'min-width:300px;',
                'desc_tip' => true,
            ),
            array(
                'title'    => __( 'Dashboard Logo', 'woopanel' ),
                'desc'     => '',
                'id'       => 'dashboard_header_logo',
                'type'     => 'image',
                'default'  => '',
            ),
            array(
                'title'    => __( 'Dashboard Copyright', 'woopanel' ),
                'desc'     => '',
                'id'       => 'dashboard_copyright',
                'type'     => 'textarea',
                'default'  => '',
            ),
            array(
                'title'    => __( 'Shop Logo', 'woopanel' ),
                'desc'     => '',
                'id'       => 'shop_logo',
                'type'     => 'image',
                'default'  => '',
            ),
            array(
                'title'    => __( 'Woocommerce Enable', 'woopanel' ),
                'desc'     => '',
                'id'       => 'woocommerce_enable',
                'type'     => 'checkbox',
                'default'  => '',
            ),
            array(
                'title'    => __( 'Allow User to Customize Dashboard', 'woopanel' ),
                'desc'     => '',
                'id'       => 'customize_dashboard',
                'type'     => 'checkbox',
                'default'  => '',
            ),
            array(
                'title'    => __( 'Block <code>wp-admin</code> Access for Non-Admins', 'woopanel' ),
                'desc'     => '',
                'id'       => 'block_wp_admin',
                'type'     => 'checkbox',
                'default'  => '',
            ),
        );

        add_action( 'admin_menu', array( $this, 'woopanel_register_ref_page'), 99 );

        add_action('admin_enqueue_scripts', array($this, 'woopanel_admin_scripts'));
    }

    static function all_options(){
        return get_option( self::$options_name );
    }
    static function get_option( $id ){
        $admin_options = self::all_options();
        return isset($admin_options[$id]) ? $admin_options[$id] : null;
    }

    static function set_option( $id, $value ){
        $options = self::all_options();
        $options[$id] = $value;
        update_option( self::$options_name, $options );
    }
    static function set_options( $args = array() ){
        $options = self::all_options();
        $args = wp_parse_args( $args, $options );

        update_option( self::$options_name, $args );
    }

    function woopanel_register_ref_page() {
        add_submenu_page(
            'options-general.php',
            __( 'WooPanel Settings', 'woopanel' ),
            __( 'WooPanel', 'woopanel' ),
            'manage_options',
            'woopanel-settings',
            array( $this, 'woopanel_page_callback' )
        );
    }

    function get_field_html( $value ){
        $html = '';
        $html .= "<tr valign='top' class='{$value['type']}-type {$value['id']}-wrap'>";
        $html .= "<th scope='row' class='titledesc'><label>{$value['title']}</label></th>";
        $html .= '<td>';
        switch ($value['type']){
            case 'text':
            case 'password':
            case 'datetime':
            case 'month':
            case 'week':
            case 'time':
            case 'number':
            case 'file':
            case 'email':
            case 'url':
            case 'tel':
                $html .= '<input type="'. $value['type'] .'" name="'. $value['id'] .'" id="'. $value['id'] .'" value="'. $this->get_option($value['id']) .'" class="regular-text">';
                break;

            case 'textarea' :
                $html .= '<textarea name="'. $value['id'] .'" id="'. $value['id'] .'" rows="5" cols="50">'. stripslashes($this->get_option($value['id'])) .'</textarea>';
                break;

            case 'checkbox' :
                $checked = ($this->get_option($value['id']) == 'yes') ? 'checked' : '';
                $html .= '<label for="'. $value['id'] .'">';
                $html .= '<input type="hidden" name="'. $value['id'] .'" value="no">';
                $html .= '<input name="'. $value['id'] .'" type="checkbox" id="'. $value['id'] .'" value="yes" '. $checked .'>';
                if( isset( $value['label'] ) ) $html .= $value['label'];
                $html .= '</label>';
                break;

            case 'image':
                $default = array(
                    'id'      => $value['id'],
                    'echo'    => false,
                    'value' => absint( $this->get_option($value['id']) ),
                );
                $args = wp_parse_args( $value, $default );
                $html .= $this->woopanel_image_uploader( $args );
                break;

            case 'single_select_page':
                $args = array(
                    'name'             => $value['id'],
                    'id'               => $value['id'],
                    'sort_column'      => 'menu_order',
                    'sort_order'       => 'ASC',
                    'show_option_none' => ' ',
                    'class'            => $value['class'],
                    'echo'             => false,
                    'selected'         => absint( $this->get_option($value['id']) ),
                    'post_status'      => 'publish,private,draft',
                );

                if ( isset( $value['args'] ) ) {
                    $args = wp_parse_args( $value['args'], $args );
                }

                $html .= str_replace( ' id=', " data-placeholder='". esc_attr__( 'Select a page&hellip;', 'woopanel' ) . "' style='" . $value['css'] . "' class='" . $value['class'] . "' id=", wp_dropdown_pages( $args ) );
                break;

            default:
                $html .= '<input type="text" name="'. $value['id'] .'" id="'. $value['id'] .'" value="'. $this->get_option($value['id']) .'" class="regular-text">';
                break;

        }
        if( $value['desc'] ) $html .= '<p class="description">'. $value['desc'] .'</p>';
        $html .= '</td>';
        $html .= '</tr>';

        echo $html;
    }

    function woopanel_page_callback() {
        if(isset($_POST['save'])){ $this->save_options(); } ?>
        <div class="wrap">
            <h1 id="woopanel-title"><?php _e('WooPanel Settings', 'woopanel'); ?></h1>

            <div id="woopanel-main">
                <div id="woopanel-tabs">
                    <form method="post" id="mainform" action="" enctype="multipart/form-data">
                        <table class="form-table">
                            <?php foreach (self::$options_fields as $field) {
                                $this->get_field_html($field);
                            } ?>
                        </table>

                        <p class="submit">
                            <button name="save" class="button-primary" type="submit"
                                    value="Save changes"><?php _e('Save changes'); ?></button>
                            <?php wp_nonce_field('woopanel-settings'); ?>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    function save_options() {
        $options = $_POST;

        unset($options['save']);
        unset($options['_wpnonce']);
        unset($options['_wp_http_referer']);

        update_option( self::$options_name, $options );
    }

    function woopanel_admin_scripts( $hook ) {
        if( $hook == 'settings_page_woopanel-settings' ) {
            wp_enqueue_media();
            if( is_woo_installed() ) {
                wp_register_script('wc-enhanced-select', WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select.min.js', array('jquery', 'selectWoo'), WC_VERSION);
                wp_localize_script(
                    'wc-enhanced-select',
                    'wc_enhanced_select_params',
                    array(
                        'i18n_no_matches' => _x('No matches found', 'enhanced select', 'woocommerce'),
                        'i18n_ajax_error' => _x('Loading failed', 'enhanced select', 'woocommerce'),
                        'i18n_input_too_short_1' => _x('Please enter 1 or more characters', 'enhanced select', 'woocommerce'),
                        'i18n_input_too_short_n' => _x('Please enter %qty% or more characters', 'enhanced select', 'woocommerce'),
                        'i18n_input_too_long_1' => _x('Please delete 1 character', 'enhanced select', 'woocommerce'),
                        'i18n_input_too_long_n' => _x('Please delete %qty% characters', 'enhanced select', 'woocommerce'),
                        'i18n_selection_too_long_1' => _x('You can only select 1 item', 'enhanced select', 'woocommerce'),
                        'i18n_selection_too_long_n' => _x('You can only select %qty% items', 'enhanced select', 'woocommerce'),
                        'i18n_load_more' => _x('Loading more results&hellip;', 'enhanced select', 'woocommerce'),
                        'i18n_searching' => _x('Searching&hellip;', 'enhanced select', 'woocommerce'),
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'search_products_nonce' => wp_create_nonce('search-products'),
                        'search_customers_nonce' => wp_create_nonce('search-customers'),
                    )
                );
                wp_enqueue_script('wc-enhanced-select');

                wp_enqueue_style('woocommerce_admin_styles', WC()->plugin_url() . '/assets/css/admin.css', array(), WC_VERSION);
            }
            wp_enqueue_style('woopanel_admin_styles', WOOPANEL_URL . 'admin/assets/css/admin.css', array());
            wp_enqueue_script('woopanel_admin_scripts', WOOPANEL_URL . 'admin/assets/js/scripts.js', array('jquery'), WooPanel()->version );
        }
    }

    function woopanel_image_uploader( $args = array() ) {
        $default = array(
            'id'             => 'image_id',
            'width'          => 115,
            'height'         => 115,
            'value'          => '',
            'echo'           => false,
        );
        $r = wp_parse_args( $args, $default );

        // Set variables
        $default_image = WooPanel()->plugin_url( 'assets/images/no-image.svg' );

        if ( absint( $r['value'] ) > 0 ) {
            $image_attributes = wp_get_attachment_image_src( $r['value'], array( $r['width'], $r['height'] ) );
            $src = $image_attributes[0];
            $value = $r['value'];
        } else {
            $src = $default_image;
            $value = '';
        }

        $text = __( 'Upload' );
        $html = '';

        // Print HTML field
        $html .= '<div class="upload">
            <img data-src="' . $default_image . '" src="' . $src . '" style="max-width: ' . $r['width'] . 'px; max-height: ' . $r['height'] . 'px" />
            <div>
                <input type="hidden" name="' . $r['id'] . '" id="' . $r['id'] . '" value="' . $value . '" />
                <button type="button" class="upload_image_button button">' . $text . '</button>
                <button type="button" class="remove_image_button button">&times;</button>
            </div>
        </div>';

        if( $r['echo'] ) echo $html;
        return $html;
    }
}
new WooPanel_Admin_Options();
