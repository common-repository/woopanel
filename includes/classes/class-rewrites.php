<?php

class WooPanel_Rewrites {
    public $query_vars = array();

    public function __construct() {
        $this->init_query_vars();
        add_action( 'init', array( $this, 'woopanel_register_rule' ) );
    }

    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WooPanel_Rewrites();
        }

        return $instance;
    }

    public function init_query_vars() {
        $this->query_vars = array(
            'dashboard',
            'articles',
            'article',
            'categories',
            'products',
            'product',
            'product-categories',
            'product-tags',
            'product-attributes',
            'product-orders',
            'order',
            'coupons',
            'coupon',
            'customers',
            'customer',
            'faqs',
            'faq',
            'comments',
            'comment',
            'reviews',
            'review',
            'settings',
            'edit-account',
            'nblogout',
            'profile'
        );
    }

    public function get_query_vars() {
        return apply_filters( 'woopanel_query_var_filter', $this->query_vars );
    }

    function woopanel_register_rule() {
        $woopanel_dashboard_url = woopanel_dashboard_url();
        $woopanel_query_vars = $this->query_vars;

        foreach ( $woopanel_query_vars as $var ) {
            add_rewrite_endpoint( $var, EP_PAGES );
        }

        do_action( 'woopanel_rewrite_rules_loaded', $woopanel_dashboard_url );
        flush_rewrite_rules();
    }
}