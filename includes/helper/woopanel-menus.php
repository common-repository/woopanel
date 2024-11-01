<?php
/**
 * Build WooPanel Menu.
 *
 * @package WooPanel
 */

function woopanel_add_menu($menu){
	global $woopanel_menus;

	$position = $menu['position'];
	unset($menu['position']);

	$woopanel_menus[$position] = $menu;
	
	ksort($woopanel_menus);
}

function woopanel_default_menus(){
    global $woopanel_menus, $woopanel_submenus;

    $woopanel_menus = array(
        5 => array(
            'id'         => 'dashboard',
            'menu_slug'  => 'dashboard',
            'menu_title' => __( 'Dashboard' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-line-graph',
            'classes'    => '',
        ),
        19 => array(
            'id'         => 'articles',
            'menu_slug'  => 'articles',
            'menu_title' => __( 'Articles', 'woopanel' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-book',
            'classes'    => '',
		),
        24 => array(
            'id'         => 'faqs',
            'menu_slug'  => 'faqs',
            'menu_title' => __( 'Faqs', 'woopanel' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-technology-2',
            'classes'    => '',
        ),
        29 => array(
            'id'         => 'withdraw',
            'menu_slug'  => 'withdraw',
            'menu_title' => __( 'Withdraw' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-coins',
            'classes'    => '',
        ),
        30 => array(
            'id'         => 'settings',
            'menu_slug'  => 'settings',
            'menu_title' => __( 'Settings' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-settings-1',
            'classes'    => '',
        ),
        40 => array(
            'id'         => 'logout',
            'menu_slug'  => 'nblogout',
            'menu_title' => __( 'Log Out' ),
            'capability' => '',
            'page_title' => '',
            'icon'       => 'flaticon-logout',
            'classes'    => '',
        ),
	);
	
	if( ! class_exists('WeDevs_Dokan') ) {
		unset($woopanel_menus[29]);
	}
	
    $woopanel_submenus = array(
        'articles' => array(
            5 => array(
                'id'         => 'articles',
                'menu_slug'  => 'articles',
                'label'      => __( 'All Articles' ),
                'page_title' => '',
                'capability' => '',
            ),
            6 => array(
                'id'         => 'articles_publish',
                'menu_slug'  => 'articles?post_status=publish',
                'label'      => _x( 'Published', 'post status' ),
                'page_title' => '',
                'capability' => '',
            ),
            7 => array(
                'id'         => 'articles_draft',
                'menu_slug'  => 'articles?post_status=draft',
                'label'      => _x( 'Draft', 'post status' ),
                'page_title' => '',
                'capability' => '',
            ),
            8 => array(
                'id'         => 'articles_trash',
                'menu_slug'  => 'articles?post_status=trash',
                'label'      => _x( 'Trash', 'post status' ),
                'page_title' => '',
                'capability' => '',
            ),
            10 => array(
                'id'         => 'separator'
            ),
            15 => array(
                'id'         => 'article_new',
                'menu_slug'  => 'article',
                'label'      => _x('Add New', 'post'),
                'page_title' => '',
                'capability' => '',
            ),
            20 => array(
                'id'         => 'comment',
                'menu_slug'  => 'comments',
                'label'      => __( 'Comments' ),
                'page_title' => '',
                'capability' => '',
            ),
		),
        'faqs' => array(
            5 => array(
                'id'         => 'faqs',
                'menu_slug'  => 'faqs',
                'label'      => __( 'All FAQs' ),
                'page_title' => '',
                'capability' => '',
            ),
            6 => array(
                'id'         => 'faq_new',
                'menu_slug'  => 'faq',
                'label'      => _x( 'Add New', 'post status' ),
                'page_title' => '',
                'capability' => '',
			)
		)
    );
}
add_action( 'init', 'woopanel_default_menus' );


add_action( 'woopanel_dashboard_navigation', 'woopanel_navigation' );
if ( ! function_exists( 'woopanel_navigation' ) ) {
	function woopanel_navigation() {
		woopanel_get_template( 'navigation.php' );
	}
}

function woopanel_get_navigation_items() {
	global $woopanel_menus, $woopanel_submenus, $woopanel_menus_woo;

	$output_menus = array();

	// set submenu to menu
	foreach ($woopanel_menus as $key => $item) {
		ksort($item);
		if(!isset($woopanel_submenus[$item['id']])) $woopanel_submenus[$item['id']] = array();
		$item['submenu'] = $woopanel_submenus[$item['id']];
		$output_menus[$key] = $item;
	}

	return apply_filters( 'woopanel_navigation_items', $output_menus );
}

function woopanel_get_navigation_item_classes( $id, $parent_id = '', array $arg_class=array() ) {
	global $wp, $woopanel_menus, $woopanel_submenus, $woopanel_post_types;

	$current_url = woopanel_current_url();
	$current_url_noquery = home_url( add_query_arg( array(), $wp->request ) );
	parse_str($_SERVER['QUERY_STRING'], $current_query);

	$current = false;
	$is_parent = false;
	$has_child_active = false;

	$classes = array(
		'm-menu__item',
		$id .'_item',
	);
	$classes = wp_parse_args($arg_class, $classes);

	// Get post type
	$post_type = '';

	foreach ($woopanel_post_types as $type => $item) {
		if($item['plural_slug'] == $id) {
			$post_type = $type;
			break;
		}
	}

	// Check if edit page
	if (!empty($post_type) && isset( $wp->query_vars[$woopanel_post_types[$post_type]['slug']]) && isset($_GET['id']) ) {
		$current = true;
	}
    // Check if customer page
    if( ! $current && $id=='customers' && preg_match('/\/'. rtrim($id, 's') .'/', $current_url_noquery) ) {
        $current = true;
    }

	if( $parent_id == '' ) {
		if (isset( $wp->query_vars[ $id ] ) || 'dashboard' === $id && ( isset( $wp->query_vars['page'] ) || empty( $wp->query_vars ) ) ) {
			$current = true;
		}
		if( count($woopanel_submenus[$id]) > 0 ) $is_parent = true;
		if( $current && $is_parent ) $has_child_active = true;
	}


	if( !empty($parent_id) ) {
	    $menu_slug = '';
		foreach ($woopanel_submenus[$parent_id] as $submenu_item) {
			if($id == $submenu_item['id']) {
				$item_url = woopanel_dashboard_url( $submenu_item['menu_slug'] );
                $menu_slug = $submenu_item['menu_slug'];
				break;
			}
		}
		$item_parts = parse_url( $item_url );

		// Check if menu_slug has query_var
		if( isset($item_parts['query']) && (strtok($item_url, '?') == $current_url_noquery) ){
			parse_str($item_parts['query'], $item_query);

			if(count(array_diff_assoc($item_query, $current_query)) == 0) $current = true;
		} else 

		// Check if menu_slug not query_var
		if (trailingslashit($item_url) == trailingslashit($current_url)) $current = true;

		// Check if filter, pagenum, post_status
		if ( empty($item_parts['query']) && (!isset($_GET['post_status']) || $_GET['post_status']=='all' ) && (isset($_GET['pagenum']) || isset($_GET['filter_action'])) && !$current && ($item_url == $current_url_noquery) ) {
			$current = true;
		}

		// Check if comment, review
		if ( ( $id == 'comment' || $id == 'review' ) && ( isset($wp->query_vars[ $id ]) || isset($wp->query_vars[ $menu_slug ]) ) ) {
			$current = true;
		}

		if( ( $id == 'product-attributes' || $id == 'product-categories' || $id == 'product-tags' ) && isset($wp->query_vars[ $id ]) ) {
			$current = true;
		}

	}

	if( $current && !in_array('m-menu__item--active', $classes) )
		$classes[] = 'm-menu__item--active';
	if( $is_parent && !in_array('m-menu__item--submenu', $classes) )
		$classes[] = 'm-menu__item--submenu';
	if( $has_child_active && !in_array('m-menu__item--open', $classes) )
		$classes[] = 'm-menu__item--open';

	$classes = apply_filters( 'woopanel_navigation_item_classes', $classes, $id );

	return implode( ' ', array_map( 'sanitize_html_class', $classes ) );
}

function woopanel_menu_output($args = array()) {
	do_action('woopanel_add_menu');

	$default = array(
		'' => '',
	);
	$options = wp_parse_args($args, $default);

	$output_html = '<ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow ">';

			foreach ( woopanel_get_navigation_items() as $menu_item ) :
				$submenu_item_html = '';
				$classes = array();
				if(empty($menu_item['icon']))
					$menu_item['icon'] = 'flaticon-interface-11';
				
				if( count($menu_item['submenu']) > 0 ) :
					$submenu_item_html = '<ul class="m-menu__subnav">';
					foreach ( $menu_item['submenu'] as $submenu_item ) :
						if($submenu_item['id'] == 'separator'):
							$submenu_item_html .= '<li class="m-menu__item separator_item"></li>';
						elseif(!empty($submenu_item['label']) && !empty($submenu_item['id'])):
							$submenu_item_html .= '<li class="'. woopanel_get_navigation_item_classes( $submenu_item['id'], $menu_item['id'] ) .'">';
							$submenu_item_html .= '<a href="'. esc_url( woopanel_dashboard_url( $submenu_item['menu_slug'] ) ) .'" class="m-menu__link ">';
							$submenu_item_html .= '<span class="m-menu__link-text">'. esc_html( $submenu_item['label'] ) .'</span>';
							$submenu_item_html .= '</a></li>';

							if(strpos(woopanel_get_navigation_item_classes( $submenu_item['id'], $menu_item['id'] ), 'm-menu__item--active') !== false) {
								$classes[] = 'm-menu__item--open';
								$classes[] = 'm-menu__item--active';
							}
						endif;
					endforeach;
					$submenu_item_html .= '</ul>';

					$output_html .= '<li class="'. woopanel_get_navigation_item_classes( $menu_item['id'], '', $classes ) .'" aria-haspopup="true" m-menu-submenu-toggle="hover">';
					$output_html .= '<a href="'. esc_url( woopanel_get_dashboard_endpoint_url( $menu_item['menu_slug'] ) ) .'" class="m-menu__link m-menu__toggle">';
						
					$output_html .= '<i class="m-menu__link-icon '. esc_html( $menu_item['icon'] ) .'"></i>';

					$output_html .= '<span class="m-menu__link-text">'. esc_html( $menu_item['menu_title'] ) .'</span>';
					$output_html .= '<i class="m-menu__ver-arrow la la-angle-right"></i>';
					$output_html .= '</a>';
					$output_html .= '<div class="m-menu__submenu "><span class="m-menu__arrow"></span>';
					$output_html .= $submenu_item_html;
					$output_html .= '</div>';
					$output_html .= '</li>';

				else:					
					$output_html .= '<li class="'. woopanel_get_navigation_item_classes( $menu_item['id'], '', $classes ) .'" aria-haspopup="true">';
					$output_html .= '<a href="'. esc_url( woopanel_get_dashboard_endpoint_url( $menu_item['menu_slug'] ) ) .'" class="m-menu__link ">';
					$output_html .= '<i class="m-menu__link-icon '. esc_html( $menu_item['icon'] ) .'"></i>';
					$output_html .= '<span class="m-menu__link-text">'. esc_html( $menu_item['menu_title'] ) .'</span>';
					$output_html .= '</a>';
					$output_html .= $submenu_item_html;
					$output_html .= '</li>';
				endif;
			endforeach;

	$output_html .= '</ul></nav>';

	return $output_html;
}


/**
 * Add menu demo
 *
woopanel_add_menu(
	array(
		'id'         => 'test_menu',
		'menu_slug'  => 'test_menu',
		'menu_title' => __( 'Test Menu', 'woopad' ),
		'capability' => '',
		'page_title' => '',
		'icon'       => '',
		'classes'    => '',
		'position'   => 6,
	));
*/