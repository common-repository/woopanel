<?php
if ( ! defined( 'ABSPATH' ) ) exit;
do_action('woopanel_init');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<!-- begin::Body -->
<body <?php // body_class(); ?> class="m-page--fluid m--skin- m-content--skin-light2 m-header--fixed m-header--fixed-mobile m-aside-left--enabled m-aside-left--skin-dark m-aside-left--offcanvas m-footer--push m-aside--offcanvas-default">

	<!-- begin:: Page -->
	<div id="woopanel_main" class="m-grid m-grid--hor m-grid--root m-page">
		<?php
		global $post;
		echo do_shortcode($post->post_content);?>
	</div>

	<!-- begin::Scroll Top -->
	<div id="m_scroll_top" class="m-scroll-top">
		<i class="la la-arrow-up"></i>
	</div>

	<?php wp_footer(); ?>
</body>
</html>