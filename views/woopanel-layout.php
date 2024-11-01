<?php
global $wp, $wp_query;
?>

<?php woopanel_get_template_part('layout', 'header'); ?>
	<div class="m-grid__item m-grid__item--fluid m-grid m-grid--ver-desktop m-grid--desktop m-body">

		<?php do_action( 'woopanel_dashboard_navigation' ); ?>

		<div class="m-grid__item m-grid__item--fluid m-wrapper">

            <?php // woopanel_get_template_part( 'subheader' ); ?>

			<div class="m-content">
				<?php

				wpl_print_notices();

				do_action( 'woopanel_dashboard_before' );
				if ( ! empty( $wp->query_vars ) ) {

					if( isset( $wp->query_vars['page'] ) && empty($wp->query_vars['page']) && has_action( 'woopanel_dashboard_endpoint' ) ) {
						do_action( 'woopanel_dashboard_endpoint', 'homepage' );
					} else {
						foreach ( $wp->query_vars as $key => $value ) {
							if ( 'pagename' === $key ) {
								continue;
							}
	
							if ( has_action( 'woopanel_dashboard_' . $key . '_endpoint' ) ) {
								do_action( 'woopanel_dashboard_' . $key . '_endpoint', $value );
								break;
							}
						}
					}
				}
				do_action( 'woopanel_dashboard_after' ); ?>
			
			</div>
		</div>
	</div>
<?php woopanel_get_template_part('layout', 'footer'); ?>