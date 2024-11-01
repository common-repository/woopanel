<?php
class WooPanel_Live_Chat {

    private static $seller_options;

    function __construct() {

        add_action('init', array($this, 'init'));
    }

    public function init() {
        global $current_user;

        self::$seller_options = get_user_meta($current_user->ID, 'seller_options', true);

        if( isset(self::$seller_options['live_chat_position']) ) {
            add_action(self::$seller_options['live_chat_position'], array($this, 'live_chat'));
        }
    }

    public function live_chat() {
        global $current_user, $wp_query, $wpdb;

        if( is_product() ) {
            $sql = $wpdb->prepare("SELECT post_author FROM {$wpdb->posts} WHERE post_name = %s AND post_type = %s", $wp_query->query['product'], $wp_query->query['post_type']);
            $product_author = $wpdb->get_var( $sql );

            if ( $product_author && $current_user->ID == $product_author && isset(self::$seller_options['live_chat_embed']) ) {
                echo self::$seller_options['live_chat_embed'];
            }
        }
        
    }
}

if( ! is_admin() ) {
    new WooPanel_Live_Chat();
}