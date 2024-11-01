<?php
defined( 'ABSPATH' ) || exit;

class WooPanel_Installer {

    public static $pages = array(
        'dashboard' => array(
            'post_name'    => 'sellercenter',      // Slug of page.
            'post_title'   => 'Seller Center', // Title of page
            'post_content' => '[woopanel]',    // Content of page
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ),
    );

    /**
     *
     */
    public static function do_install() {
        self::woopanel_insert_page();
        self::set_default_options();
        self::reset_permalinks();
    }

    public static function woopanel_insert_page(){
        foreach (self::$pages as $k => $page) {
            // Create post object
            $my_post = array(
                'post_name'     => $page['post_name'],
                'post_title'    => $page['post_title'],
                'post_content'  => $page['post_content'],
                'post_status'   => $page['post_status'],
                'post_author'   => get_current_user_id(),
                'post_type'     => $page['post_type'],
            );

            $page_check = get_page_by_path($page['post_name']);
            if(!isset($page_check->ID)){
                wp_insert_post( $my_post, '' );
            }
            /* else if( get_post_status( $page_check->ID ) != 'publish' ) {
                wp_update_post( array( 'ID' => $page_check->ID, 'post_status' => 'publish' ) );
            } */
        }
    }

    static function woopanel_page_url($id){
        return get_page_by_path( self::$pages[$id]['post_name'] ) ? get_permalink( get_page_by_path( self::$pages[$id]['post_name'] ) ) : null;
    }

    static function set_default_options(){
        $dashboard_page = get_page_by_path( self::$pages['dashboard']['post_name'] );

        WooPanel_Admin_Options::set_option( 'dashboard_page_id', $dashboard_page ? $dashboard_page->ID : null );
        WooPanel_Admin_Options::set_option( 'woocommerce_enable', is_woo_installed() ? 'yes' : 'no' );
    }

    static function reset_permalinks() {
        global $wp_rewrite;
        $wp_rewrite->set_permalink_structure('/%postname%/');
        $wp_rewrite->flush_rules();
    }
}