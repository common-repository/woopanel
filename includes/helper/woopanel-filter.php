<?php

function woopanel_filter_months_dropdown( $post_type, $param_name = 'm' ) {
	global $wpdb, $wp_locale;

	if ( apply_filters( 'disable_months_dropdown', false, $post_type ) ) {
		return;
	}

	$extra_checks = "AND post_status != 'auto-draft'";
	if ( ! isset( $_GET['post_status'] ) || 'trash' !== $_GET['post_status'] ) {
		$extra_checks .= " AND post_status != 'trash'";
	} elseif ( isset( $_GET['post_status'] ) ) {
		$extra_checks = $wpdb->prepare( ' AND post_status = %s', $_GET['post_status'] );
	}

	$months = $wpdb->get_results( $wpdb->prepare( "
		SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
		FROM $wpdb->posts
		WHERE post_type = %s
		$extra_checks
		ORDER BY post_date DESC
		", $post_type ) );

	$months = apply_filters( 'months_dropdown_results', $months, $post_type );

	$month_count = count( $months );

	if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
		return;

	$m = isset( $_GET[$param_name] ) ? (int) $_GET[$param_name] : 0; ?>
	<div class="m-form__label"><label><?php _e( 'Date' ); ?></label></div>
	<div class="m-form__control">
		<select name="<?php echo $param_name;?>" id="filter-by-date" class="form-control m-bootstrap-select">
			<option<?php selected( $m, 0 ); ?> value="0"><?php _e( 'All dates' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year )
					continue;

				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;

				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					/* translators: 1: month name, 2: 4-digit year */
					sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
				);
			} ?>
		</select>
	</div>
<?php }

function woopanel_filter_taxonomies_dropdown( $post_type, $taxonomy, $param_name = 'cat' ) {
	global $wp_query;

	$taxonomy_object = get_taxonomy( $taxonomy );

	if ( is_object_in_taxonomy( $post_type, $taxonomy ) ) {
		$dropdown_options = array(
			'pad_counts'         => 1,
			'show_count'         => 0,
			'hierarchical'       => 1,
			'hide_empty'         => 1,
			'show_uncategorized' => 1,
			'orderby'            => 'name',
			'selected'           => isset( $wp_query->query_vars[$param_name] ) ? $wp_query->query_vars[$param_name] : '',
			'menu_order'         => false,
			'show_option_none'   => $taxonomy_object->labels->all_items,
			'option_none_value'  => '',
			// 'value_field'        => 'slug',
			'taxonomy'           => $taxonomy,
			'name'               => $param_name,
			'class'              => 'form-control m-bootstrap-select dropdown_'. $param_name,
		);

		echo '<div class="m-form__label"><label>'. $taxonomy_object->labels->singular_name . '</label></div>';
		echo '<div class="m-form__control">';
		wp_dropdown_categories( $dropdown_options );
		echo '</div>';
	}
}
