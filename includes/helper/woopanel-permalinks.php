<?php
function woopanel_get_endpoint_url( $endpoint, $value = '', $permalink = '' ) {
	if ( ! $permalink ) {
		$permalink = get_permalink();
	}

	if ( get_option( 'permalink_structure' ) ) {
		if ( strstr( $permalink, '?' ) ) {
			$query_string = '?' . wp_parse_url( $permalink, PHP_URL_QUERY );
			$permalink    = current( explode( '?', $permalink ) );
		} else {
			$query_string = '';
		}
		$url = trailingslashit( $permalink ) . trailingslashit( $endpoint );

		if ( $value ) {
			$url .= trailingslashit( $value );
		}

		$url .= $query_string;
	} else {
		$url = add_query_arg( $endpoint, $value, $permalink );
	}

	return apply_filters( 'woopanel_get_endpoint_url', $url, $endpoint, $value, $permalink );
}


/**
 * Returns the HTML of the sample permalink slug editor.
 *
 * @since 2.5.0
 *
 * @param int    $id        Post ID or post object.
 * @param string $new_title Optional. New title. Default null.
 * @param string $new_slug  Optional. New slug. Default null.
 * @return string The HTML of the sample permalink slug editor.
 */
function woopanel_get_permalink_html( $id, $new_title = null, $new_slug = null ) {
	$post = get_post( $id );
	if ( ! $post )
		return '';

	list($permalink, $post_name) = woopanel_get_permalink($post->ID, $new_title, $new_slug);

	$view_link = false;
	$preview_target = '';

	if ( current_user_can( 'read_post', $post->ID ) ) {
		if ( 'draft' === $post->post_status || empty( $post->post_name ) ) {
			$view_link = get_preview_post_link( $post );
			$preview_target = " target='wp-preview-{$post->ID}'";
		} else {
			if ( 'publish' === $post->post_status || 'attachment' === $post->post_type ) {
				$view_link = get_permalink( $post );
			} else {
				// Allow non-published (private, future) to be viewed at a pretty permalink, in case $post->post_name is set
				$view_link = str_replace( array( '%pagename%', '%postname%' ), $post->post_name, $permalink );
			}
		}
	}

	// Permalinks without a post/page name placeholder don't have anything to edit
	if ( false === strpos( $permalink, '%postname%' ) && false === strpos( $permalink, '%pagename%' ) ) {
		$return = '<strong>' . __( 'Permalink:' ) . "</strong>\n";

		if ( false !== $view_link ) {
			$display_link = urldecode( $view_link );
			$return .= '<a id="sample-permalink" href="' . esc_url( $view_link ) . '"' . $preview_target . '>' . esc_html( $display_link ) . "</a>\n";
		} else {
			$return .= '<span id="sample-permalink">' . $permalink . "</span>\n";
		}

		// Encourage a pretty permalink setting
		if ( '' == get_option( 'permalink_structure' ) && current_user_can( 'manage_options' ) && !( 'page' == get_option('show_on_front') && $id == get_option('page_on_front') ) ) {
			$return .= '<span id="change-permalinks"><a href="options-permalink.php" class="button button-small" target="_blank">' . __('Change Permalinks') . "</a></span>\n";
		}
	} else {
		if ( mb_strlen( $post_name ) > 34 ) {
			$post_name_abridged = mb_substr( $post_name, 0, 16 ) . '&hellip;' . mb_substr( $post_name, -16 );
		} else {
			$post_name_abridged = $post_name;
		}

		$post_name_html = '<span id="editable-post-name" data-title="'. $post_name .'">' . esc_html( $post_name_abridged ) . '</span>';
		$display_link = str_replace( array( '%pagename%', '%postname%' ), $post_name_html, esc_html( urldecode( $permalink ) ) );

		$return = '<strong>' . __( 'Permalink:' ) . "</strong>\n";
		$return .= '<span id="sample-permalink"><a href="' . esc_url( $view_link ) . '"' . $preview_target . '>' . $display_link . "</a></span>\n";
		$return .= '&lrm;'; // Fix bi-directional text display defect in RTL languages.
		$return .= '<span id="edit-slug-buttons"><button type="button" class="edit-slug button button-small hide-if-no-js" aria-label="' . __( 'Edit permalink' ) . '">' . __( 'Edit' ) . "</button></span>\n";
		$return .= '<span id="editable-post-name-full">' . esc_html( $post_name ) . "</span>\n";
	}

	/**
	 * Filters the sample permalink HTML markup.
	 *
	 * @since 2.9.0
	 * @since 4.4.0 Added `$post` parameter.
	 *
	 * @param string  $return    Sample permalink HTML markup.
	 * @param int     $post_id   Post ID.
	 * @param string  $new_title New sample permalink title.
	 * @param string  $new_slug  New sample permalink slug.
	 * @param WP_Post $post      Post object.
	 */
	$return = apply_filters( 'get_sample_permalink_html', $return, $post->ID, $new_title, $new_slug, $post );

	return $return;
}

/**
 * Get a sample permalink based off of the post name.
 *
 * @since 2.5.0
 *
 * @param int    $id    Post ID or post object.
 * @param string $title Optional. Title to override the post's current title when generating the post name. Default null.
 * @param string $name  Optional. Name to override the post name. Default null.
 * @return array Array containing the sample permalink with placeholder for the post name, and the post name.
 */
function woopanel_get_permalink($id, $title = null, $name = null) {
	$post = get_post( $id );
	if ( ! $post )
		return array( '', '' );

	$ptype = get_post_type_object($post->post_type);

	$original_status = $post->post_status;
	$original_date = $post->post_date;
	$original_name = $post->post_name;

	// Hack: get_permalink() would return ugly permalink for drafts, so we will fake that our post is published.
	if ( in_array( $post->post_status, array( 'draft', 'pending', 'future' ) ) ) {
		$post->post_status = 'publish';
		$post->post_name = sanitize_title($post->post_name ? $post->post_name : $post->post_title, $post->ID);
	}

	// If the user wants to set a new name -- override the current one
	// Note: if empty name is supplied -- use the title instead, see #6072
	if ( !is_null($name) )
		$post->post_name = sanitize_title($name ? $name : $title, $post->ID);

	$post->post_name = wp_unique_post_slug($post->post_name, $post->ID, $post->post_status, $post->post_type, $post->post_parent);

	$post->filter = 'sample';

	$permalink = get_permalink($post, true);

	// Replace custom post_type Token with generic pagename token for ease of use.
	$permalink = str_replace("%$post->post_type%", '%pagename%', $permalink);

	// Handle page hierarchy
	if ( $ptype->hierarchical ) {
		$uri = get_page_uri($post);
		if ( $uri ) {
			$uri = untrailingslashit($uri);
			$uri = strrev( stristr( strrev( $uri ), '/' ) );
			$uri = untrailingslashit($uri);
		}

		/** This filter is documented in wp-admin/edit-tag-form.php */
		$uri = apply_filters( 'editable_slug', $uri, $post );
		if ( !empty($uri) )
			$uri .= '/';
		$permalink = str_replace('%pagename%', "{$uri}%pagename%", $permalink);
	}

	/** This filter is documented in wp-admin/edit-tag-form.php */
	$permalink = array( $permalink, apply_filters( 'editable_slug', $post->post_name, $post ) );
	$post->post_status = $original_status;
	$post->post_date = $original_date;
	$post->post_name = $original_name;
	unset($post->filter);

	/**
	 * Filters the sample permalink.
	 *
	 * @since 4.4.0
	 *
	 * @param array   $permalink Array containing the sample permalink with placeholder for the post name, and the post name.
	 * @param int     $post_id   Post ID.
	 * @param string  $title     Post title.
	 * @param string  $name      Post name (slug).
	 * @param WP_Post $post      Post object.
	 */
	return apply_filters( 'get_sample_permalink', $permalink, $post->ID, $title, $name, $post );
}
