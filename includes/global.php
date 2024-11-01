<?php

global $woopanel_post_types, $woopanel_notices, $woopanel_order_status;

$woopanel_post_types = array(
	'product' => array(
		'slug'        => 'product',
		'plural_slug' => 'products',
    ),
	'post' => array(
		'slug'        => 'article',
		'plural_slug' => 'articles',
    ),
	'shop_order' => array(
		'slug'        => 'order',
		'plural_slug' => 'product-orders',
    ),
	'shop_coupon' => array(
		'slug'        => 'coupon',
		'plural_slug' => 'coupons',
    ),
	'wpl-faq' => array(
		'slug'        => 'faq',
		'plural_slug' => 'faqs',
    )
);

$woopanel_order_status = array(
    'wc-pending' => array(
        'color'        => 'm-badge--brand',
    ),
    'wc-processing' => array(
        'color'        => 'm-badge--info',
    ),
    'wc-on-hold' => array(
        'color'        => 'm-badge--warning',
    ),
    'wc-completed' => array(
        'color'        => 'm-badge--success',
    ),
    'wc-cancelled' => array(
        'color'        => 'm-badge--primary',
    ),
    'wc-refunded' => array(
        'color'        => 'm-badge--focus',
    ),
    'wc-failed' => array(
        'color'        => 'm-badge--danger',
    )
);

$woopanel_notices = array();