<?php
// Add menus when woocommerce available
function woopanel_add_woo_menus(){
    if( !is_woo_available() ) return;

    global $woopanel_menus, $woopanel_submenus;

    $woopanel_menus_woo = [
        15 => [
            'id'         => 'products',
            'menu_slug'  => get_option( 'woopanel_products_endpoint', 'products' ),
            'menu_title' => __( 'Products', 'woocommerce' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-box',
            'classes'    => '',
        ],
        20 => [
            'id'         => 'product-orders',
            'menu_slug'  => get_option( 'woopanel_orders_endpoint', 'product-orders' ),
            'menu_title' => __( 'Orders', 'woocommerce' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-notepad',
            'classes'    => '',
        ],
        23 => [
            'id'         => 'coupons',
            'menu_slug'  => get_option( 'woopanel_coupons_endpoint', 'coupons' ),
            'menu_title' => __( 'Coupons', 'woocommerce' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-price-tag',
            'classes'    => '',
        ],
        25 => [
            'id'         => 'customers',
            'menu_slug'  => get_option( 'woopanel_customers_endpoint', 'customers' ),
            'menu_title' => __( 'Customers', 'woocommerce' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-users',
            'classes'    => '',
        ],
    ];
    $woopanel_submenus_woo = [
        'products' => [
            5 => [
                'id'         => 'products',
                'menu_slug'  => 'products',
                'label'      => __( 'All Products', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
            6 => [
                'id'         => 'products_publish',
                'menu_slug'  => 'products?post_status=publish',
                'label'      => _x( 'Published', 'post status' ),
                'page_title' => '',
                'capability' => '',
            ],
            7 => [
                'id'         => 'products_draft',
                'menu_slug'  => 'products?post_status=draft',
                'label'      => _x( 'Draft', 'post status' ),
                'page_title' => '',
                'capability' => '',
            ],
            8 => [
                'id'         => 'products_trash',
                'menu_slug'  => 'products?post_status=trash',
                'label'      => _x( 'Trash', 'post status' ),
                'page_title' => '',
                'capability' => '',
            ],
            10 => [
                'id'         => 'separator'
            ],
            15 => [
                'id'         => 'product_new',
                'menu_slug'  => 'product',
                'label'      => __( 'Add new', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
            20 => [
                'id'         => 'review',
                'menu_slug'  => 'reviews',
                'label'      => __( 'Reviews', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
            30 => [
                'id'         => 'separator'
            ],
            35 => [
                'id'         => 'product-categories',
                'menu_slug'  => 'product-categories',
                'label'      => __( 'Categories' ),
                'page_title' => '',
                'capability' => '',
            ],
            40 => [
                'id'         => 'product-tags',
                'menu_slug'  => 'product-tags',
                'label'      => __( 'Tags' ),
                'page_title' => '',
                'capability' => '',
            ],
            45 => [
                'id'         => 'product-attributes',
                'menu_slug'  => 'product-attributes',
                'label'      => __( 'Attributes' ),
                'page_title' => '',
                'capability' => '',
            ],
        ],
        'product-orders' => [
            5 => [
                'id'         => 'product-orders',
                'menu_slug'  => 'product-orders',
                'label'      => __( 'All Orders', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
            6 => [
                'id'         => 'orders_processing',
                'menu_slug'  => 'product-orders?status=wc-processing',
                'label'      => _x( 'Processing', 'Order status', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
            7 => [
                'id'         => 'orders_on-hold',
                'menu_slug'  => 'product-orders?status=wc-on-hold',
                'label'      => _x( 'On hold', 'Order status', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
            8 => [
                'id'         => 'orders_completed',
                'menu_slug'  => 'product-orders?status=wc-completed',
                'label'      => _x( 'Completed', 'Order status', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
            9 => [
                'id'         => 'orders_cancelled',
                'menu_slug'  => 'product-orders?status=wc-cancelled',
                'label'      => _x( 'Cancelled', 'Order status', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ]
        ],
        'coupons' => [
            5 => [
                'id'         => 'coupons',
                'menu_slug'  => 'coupons',
                'label'      => __( 'All coupons', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
            6 => [
                'id'         => 'coupon_new',
                'menu_slug'  => 'coupon',
                'label'      => __( 'Add new', 'woocommerce' ),
                'page_title' => '',
                'capability' => '',
            ],
        ]
    ];

    $woopanel_menus    = $woopanel_menus + $woopanel_menus_woo;
    $woopanel_submenus = $woopanel_submenus + $woopanel_submenus_woo;
    ksort($woopanel_menus);
}
add_action( 'woopanel_add_menu', 'woopanel_add_woo_menus' );