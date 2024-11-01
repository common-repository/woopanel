<?php

class WooPanel_Template {

 	public function __construct() {
 		add_action( 'page_template', array( &$this, 'woopanel_page_template' ) );
 		// add_filter( 'the_title', array( &$this, 'woopanel_page_title' ), 9 );
 	}

 	function woopanel_page_template( $page_template ) {
		global $post;

        if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, WooPanel_Shortcodes::$shortcodes['dashboard']) ) {
            if( is_user_logged_in() && !is_shop_staff() )
                wp_die( __( 'You have no permission to view this page', 'woopanel' ), __( 'No permission', 'woopanel' ), array('response'=>403) );

            $page_template = woopanel_locate_template( 'page-fullscreen.php' );
		}

		return $page_template;
	}

	function woopanel_page_title( $title ) {
		global $wp, $woopanel_menus, $woopanel_submenus, $woopanel_post_types;

		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$current_url_noquery = home_url( add_query_arg( array(), $wp->request ) );
		parse_str($_SERVER['QUERY_STRING'], $current_query);
	
		return $title;
	}
}
new WooPanel_Template();