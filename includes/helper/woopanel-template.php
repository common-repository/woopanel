<?php
function woopanel_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = 'woopanel/views/';;
	}

	if ( ! $default_path ) {
		$default_path = WOOPANEL_DIR . 'views/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/.
	if ( ! $template || WOOPANEL_TEMPLATE_DEBUG ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'woopanel_locate_template', $template, $template_name, $template_path );
}

/**
 * get_template_part
 */
if ( ! function_exists( 'woopanel_get_template_part' ) ) {
	function woopanel_get_template_part( $slug, $name = '', $args = array() ) {
		$defaults = array();

		$args = wp_parse_args( $args, $defaults );

		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		$template = '';
		$template_url = 'woopanel/';
		$file_name = $name ? ($slug.'-'.$name) : $slug;

		// Look in yourtheme/slug-name.php and yourtheme/woopanel/slug-name.php
		if ( $file_name && ! WOOPANEL_TEMPLATE_DEBUG ){
			$template = locate_template(array("{$file_name}.php", "{$template_url}{$file_name}.php"));
		}

		// Get default slug-name.php
		if ( !$template && $file_name && file_exists(WOOPANEL_DIR . "templates/{$file_name}.php") )
			$template = WOOPANEL_DIR . "templates/{$file_name}.php";

		// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woopanel/slug.php
		if ( !$template  && ! WOOPANEL_TEMPLATE_DEBUG )
			$template = locate_template(array("{$file_name}.php", "{$template_url}{$file_name}.php"));

		if ( $template )
			include $template;
	}
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 */
function woopanel_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = woopanel_locate_template( $template_name, $template_path, $default_path );

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'woopanel_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action('woopanel_before_template_part', $template_name, $template_path, $located, $args);

	include $located;

	do_action('woopanel_after_template_part', $template_name, $template_path, $located, $args);
}


if ( ! function_exists( 'woopanel_dashboard_content' ) ) {
    function woopanel_dashboard_content() {
        global $wp;

        if ( ! empty( $wp->query_vars ) ) {
            foreach ( $wp->query_vars as $key => $value ) {
                // Ignore pagename param.
                if ( 'pagename' === $key ) {
                    continue;
                }

                if ( has_action( ' woopanel_dashboard_' . $key . '_endpoint' ) ) {
                    do_action( ' woopanel_dashboard_' . $key . '_endpoint', $value );
                    return;
                }
            }
        }

        // No endpoint found? Default to dashboard.
        woopanel_get_template( 'myaccount/dashboard.php', array(
            'current_user' => get_user_by( 'id', get_current_user_id() ),
        ) );
    }
}


if ( ! function_exists( 'woopanel_get_dashboard_endpoint_url' ) ) {
    function woopanel_get_dashboard_endpoint_url( $endpoint ) {
        if ( 'dashboard' === $endpoint ) {
            return woopanel_dashboard_url();
        }

        if ( 'nblogout' === $endpoint ) {
            return woopanel_logout_url();
        }

        return woopanel_dashboard_url( $endpoint );
    }
}

function woopanel_article_endpoint_content() {
	$article = new WooPanel_Template_Article();
   $article->form();
}
add_action( 'woopanel_dashboard_article_endpoint', 'woopanel_article_endpoint_content' );

function woopanel_articles_endpoint_content() {
	$article = new WooPanel_Template_Article();
	$article->lists();
}
add_action( 'woopanel_dashboard_articles_endpoint', 'woopanel_articles_endpoint_content' );



function woopanel_settings_endpoint_content() {
    woopanel_get_template('settings.php');
}
add_action( 'woopanel_dashboard_settings_endpoint', 'woopanel_settings_endpoint_content' );

function woopanel_dashboard_endpoint_content() {
	$dashboard = new WooPanel_Template_Dashboard();
	$dashboard->display();
}
add_action( 'woopanel_dashboard_endpoint', 'woopanel_dashboard_endpoint_content' );

function woopanel_product_endpoint_content() {
	$article = new WooPanel_Template_Product();
	$article->form();
	
}
add_action( 'woopanel_dashboard_product_endpoint', 'woopanel_product_endpoint_content' );

function woopanel_products_endpoint_content() {
	$product = new WooPanel_Template_Product();
	$product->lists();
}
add_action( 'woopanel_dashboard_products_endpoint', 'woopanel_products_endpoint_content' );

function woopanel_customer_endpoint_content() {
	$customer = new WooPanel_Template_Customer();
	$customer->form();
}
add_action( 'woopanel_dashboard_customer_endpoint', 'woopanel_customer_endpoint_content' );

function woopanel_customers_endpoint_content() {
	$customer = new WooPanel_Template_Customer();
	$customer->lists();
}
add_action( 'woopanel_dashboard_customers_endpoint', 'woopanel_customers_endpoint_content' );

function woopanel_order_endpoint_content() {
	$order = new WooPanel_Template_Order();
	$order->form();
}
add_action( 'woopanel_dashboard_order_endpoint', 'woopanel_order_endpoint_content' );

function woopanel_orders_endpoint_content() {
	$order = new WooPanel_Template_Order();
	$order->lists();
}
add_action( 'woopanel_dashboard_product-orders_endpoint', 'woopanel_orders_endpoint_content' );

 function woopanel_coupon_endpoint_content() {
	$coupon = new WooPanel_Template_Coupon();
	$coupon->form();
}
add_action( 'woopanel_dashboard_coupon_endpoint', 'woopanel_coupon_endpoint_content' );

function woopanel_coupons_endpoint_content() {
	$coupon = new WooPanel_Template_Coupon();
	$coupon->lists();
}
add_action( 'woopanel_dashboard_coupons_endpoint', 'woopanel_coupons_endpoint_content' );

function woopanel_review_endpoint_content() {
	$review = new WooPanel_Template_Review();
	$review->form();
}
add_action( 'woopanel_dashboard_review_endpoint', 'woopanel_review_endpoint_content' );


function woopanel_reviews_endpoint_content() {
	$review = new WooPanel_Template_Review();
	$review->lists();
}
add_action( 'woopanel_dashboard_reviews_endpoint', 'woopanel_reviews_endpoint_content' );

function woopanel_comment_endpoint_content() {
	$comment = new WooPanel_Template_Comment();
	$comment->form();
}
add_action( 'woopanel_dashboard_comment_endpoint', 'woopanel_comment_endpoint_content' );

function woopanel_comments_endpoint_content() {
	$comment = new WooPanel_Template_Comment();
	$comment->lists();
}
add_action( 'woopanel_dashboard_comments_endpoint', 'woopanel_comments_endpoint_content' );

function woopanel_profile_endpoint_content() {
	$comment = new WooPanel_Template_Customer();
	$comment->form(true);
}
add_action( 'woopanel_dashboard_profile_endpoint', 'woopanel_profile_endpoint_content' );

function woopanel_product_categories_endpoint_content() {
	$product_cat = new WooPanel_Product_Categories();
	$product_cat->index();
}
add_action( 'woopanel_dashboard_product-categories_endpoint', 'woopanel_product_categories_endpoint_content' );

function woopanel_product_tags_endpoint_content() {
	$product_tag = new WooPanel_Product_Tags();
	$product_tag->index();
}
add_action( 'woopanel_dashboard_product-tags_endpoint', 'woopanel_product_tags_endpoint_content' );

function woopanel_product_attributes_endpoint_content() {
	$attribute = new WooPanel_Product_Attributes();
	$attribute->index();
}
add_action( 'woopanel_dashboard_product-attributes_endpoint', 'woopanel_product_attributes_endpoint_content' );

function woopanel_faqs_endpoint_content() {
	$faq = new WooPanel_Template_FAQs();
	$faq->index();
}
add_action( 'woopanel_dashboard_faqs_endpoint', 'woopanel_faqs_endpoint_content' );

function woopanel_faq_endpoint_content() {
	$faq = new WooPanel_Template_FAQs();
	$faq->form();
}
add_action( 'woopanel_dashboard_faq_endpoint', 'woopanel_faq_endpoint_content' );

function woopanel_withdraw_endpoint_content() {
    woopanel_get_template('withdraw.php');
}
add_action( 'woopanel_dashboard_withdraw_endpoint', 'woopanel_withdraw_endpoint_content' );