<?php

function woopanel_column_title( $post, $actions ) {
	$action_count = count( $actions );
	$always_visible = false;

    $out_html = '<strong>';
    if ( 'trash' == $post->post_status ) {
        $out_html .= get_the_title($post->ID);
    } else {
        $out_html .= '<a class="row-title" href="' . woopanel_post_edit_url($post->ID) . '" aria-label="' . get_the_title($post->ID) . ' (Edit)">' . get_the_title($post->ID) . '</a>';
    }
	$out_html .= apply_filters( "woopanel_{$post->post_type}_state", false, $post );
	$out_html .= '</strong>';

	if ( $action_count ) { $i = 0;	
		$out_html .= '<div class="'. ($always_visible ? 'row-actions visible' : 'row-actions') .'">';
		foreach ( $actions as $action => $link ) { ++$i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out_html .= "<span class='$action'>$link$sep</span>";
		}
		$out_html .= '</div>';
	}
	echo $out_html;
}

function woopanel_column_taxonomy( $post, $taxonomy = 'category', $name = 'cat' ) {
	global $wp;

	$taxonomy_object = get_taxonomy( $taxonomy );
	$terms = get_the_terms( $post->ID, $taxonomy );
	$query = $_GET;

	if ( is_array( $terms ) ) {
		$out = array();
		foreach ( $terms as $t ) {
			$query[$name] = $t->term_id;
			$query_result = http_build_query($query);

			$label = esc_html( sanitize_term_field( 'name', $t->name, $t->term_id, $taxonomy, 'display' ) );
			$out[] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				home_url( add_query_arg( array(), $wp->request ) ) .'?'. $query_result,
				esc_attr( sprintf( __( 'View all post in &#8220;%s&#8221; inline' ), $label ) ),
				$label
			);
		}
		echo '<div class="woopanel-readmore" data-height="20">' . join( __( ', ' ), $out ) .'</div>';
	} else {
		echo '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . $taxonomy_object->labels->no_terms . '</span>';
	}
}

function woopanel_get_pending_comments_num( $post_id ) {
    global $wpdb;

    $single = false;
    if ( ! is_array( $post_id ) ) {
        $post_id_array = (array) $post_id;
        $single        = true;
    } else {
        $post_id_array = $post_id;
    }
    $post_id_array = array_map( 'intval', $post_id_array );
    $post_id_in    = "'" . implode( "', '", $post_id_array ) . "'";

    $pending = $wpdb->get_results( "SELECT comment_post_ID, COUNT(comment_ID) as num_comments FROM $wpdb->comments WHERE comment_post_ID IN ( $post_id_in ) AND comment_approved = '0' GROUP BY comment_post_ID", ARRAY_A );

    if ( $single ) {
        if ( empty( $pending ) ) {
            return 0;
        } else {
            return absint( $pending[0]['num_comments'] );
        }
    }

    $pending_keyed = array();

    // Default to zero pending for all posts in request
    foreach ( $post_id_array as $id ) {
        $pending_keyed[ $id ] = 0;
    }

    if ( ! empty( $pending ) ) {
        foreach ( $pending as $pend ) {
            $pending_keyed[ $pend['comment_post_ID'] ] = absint( $pend['num_comments'] );
        }
    }

    return $pending_keyed;
}

function woopanel_column_comments( $post, $type = 'comments' ) {
	$output_html = '<div class="post-com-count-wrapper">';


	$approved_comments = $post->comment_count;
	$pending_comments = woopanel_get_pending_comments_num( array($post->ID) )[$post->ID];
	
	$approved_comments_number = number_format_i18n( $approved_comments );
	$pending_comments_number = number_format_i18n( $pending_comments );

	$approved_only_phrase = sprintf( _n( '%s comment', '%s comments', $approved_comments ), $approved_comments_number );
	$approved_phrase = sprintf( _n( '%s approved comment', '%s approved comments', $approved_comments ), $approved_comments_number );
	$pending_phrase = sprintf( _n( '%s pending comment', '%s pending comments', $pending_comments ), $pending_comments_number );

	// No comments at all.
	if ( ! $approved_comments && ! $pending_comments ) {
		$output_html .= sprintf( '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">%s</span>',
				__( 'No comments' )
		);
	// Approved comments have different display depending on some conditions.
	} elseif ( $approved_comments ) {
		$output_html .= sprintf( '<a href="%s" class="post-com-count post-com-count-approved"><span class="comment-count-approved" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></a>',
				woopanel_dashboard_url($type) .'?post=' . $post->ID,
				$approved_comments_number,
				$pending_comments ? $approved_phrase : $approved_only_phrase
		);
	} else {
		$output_html .= sprintf( '<span class="post-com-count post-com-count-no-comments"><span class="comment-count comment-count-no-comments" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
				$approved_comments_number,
				$pending_comments ? __( 'No approved comments' ) : __( 'No comments' )
		);
	}

	if ( $pending_comments ) {
		$output_html .= sprintf( '<a href="%s" class="post-com-count post-com-count-pending"><span class="comment-count-pending" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></a>',
		woopanel_dashboard_url($type) .'?post=' . $post->ID,
				$pending_comments_number,
				$pending_phrase
		);
	} else {
		 $output_html .= sprintf( '<span class="post-com-count post-com-count-pending post-com-count-no-pending"><span class="comment-count comment-count-no-pending" aria-hidden="true">%s</span><span class="screen-reader-text">%s</span></span>',
		 		$pending_comments_number,
		 		$approved_comments ? __( 'No pending comments' ) : __( 'No comments' )
		 );
	}
	$output_html .= '</div>';

	echo $output_html;
}

function woopanel_column_date( $post ) {
	global $mode;

	if ( '0000-00-00 00:00:00' === $post->post_date ) {
		$t_time = $h_time = __( 'Unpublished' );
		$time_diff = 0;
	} else {
		$t_time = get_the_time( __( 'Y/m/d g:i:s a' ) );
		$m_time = $post->post_date;
		$time = get_post_time( 'G', true, $post );

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < DAY_IN_SECONDS ) {
			$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
		} else {
			$h_time = mysql2date( __( 'Y/m/d' ), $m_time );
		}
	}

	if ( 'publish' === $post->post_status ) {
		$status = __( 'Published' );
	} elseif ( 'future' === $post->post_status ) {
		if ( $time_diff > 0 ) {
			$status = '<strong class="error-message">' . __( 'Missed schedule' ) . '</strong>';
		} else {
			$status = __( 'Scheduled' );
		}
	} else {
		$status = __( 'Last Modified' );
	}

	$status = apply_filters( 'post_date_column_status', $status, $post, 'date', $mode );

	if ( $status ) {
		echo $status . '<br />';
	}

	if ( 'excerpt' === $mode ) {
		echo apply_filters( 'post_date_column_time', $t_time, $post, 'date', $mode );
	} else {
		echo '<abbr title="' . $t_time . '">' . apply_filters( 'post_date_column_time', $h_time, $post, 'date', $mode ) . '</abbr>';
	}
}

function woopanel_pagination( $which ) {
	if ( empty( $this->_pagination_args ) ) {
		return;
	}

	$total_items = $this->_pagination_args['total_items'];
	$total_pages = $this->_pagination_args['total_pages'];
	$infinite_scroll = false;
	if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
		$infinite_scroll = $this->_pagination_args['infinite_scroll'];
	}

	if ( 'top' === $which && $total_pages > 1 ) {
		$this->screen->render_screen_reader_content( 'heading_pagination' );
	}

	$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

	$current = $this->get_pagenum();
	$removable_query_args = wp_removable_query_args();

	$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

	$current_url = remove_query_arg( $removable_query_args, $current_url );

	$page_links = array();

	$total_pages_before = '<span class="paging-input">';
	$total_pages_after  = '</span></span>';

	$disable_first = $disable_last = $disable_prev = $disable_next = false;

	if ( $current == 1 ) {
		$disable_first = true;
		$disable_prev = true;
	}
	if ( $current == 2 ) {
		$disable_first = true;
	}
	if ( $current == $total_pages ) {
		$disable_last = true;
		$disable_next = true;
	}
	if ( $current == $total_pages - 1 ) {
		$disable_last = true;
	}

	if ( $disable_first ) {
		$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
	} else {
		$page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			__( 'First page' ),
			'&laquo;'
		);
	}

	if ( $disable_prev ) {
		$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
	} else {
		$page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			__( 'Previous page' ),
			'&lsaquo;'
		);
	}

	if ( 'bottom' === $which ) {
		$html_current_page  = $current;
		$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
	} else {
		$html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
			'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
			$current,
			strlen( $total_pages )
		);
	}
	$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
	$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

	if ( $disable_next ) {
		$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
	} else {
		$page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
			esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
			__( 'Next page' ),
			'&rsaquo;'
		);
	}

	if ( $disable_last ) {
		$page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
	} else {
		$page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
			esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
			__( 'Last page' ),
			'&raquo;'
		);
	}

	$pagination_links_class = 'pagination-links';
	if ( ! empty( $infinite_scroll ) ) {
		$pagination_links_class .= ' hide-if-js';
	}
	$output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

	if ( $total_pages ) {
		$page_class = $total_pages < 2 ? ' one-page' : '';
	} else {
		$page_class = ' no-pages';
	}
	$this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

	echo $this->_pagination;
}

function woopanel_current_action() {
	if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
		return false;

	if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
		return $_REQUEST['action'];

	if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
		return $_REQUEST['action2'];

	return false;
}

/**
 * @param  $which: top/bottom
 */
function woopanel_bulk_actions( $which = 'top' ) {
	$_actions = [
		// 'edit' => 'Edit',
		'trash' => 'Move to Trash',
	];

	$two = ($which == 'top') ? '' : 2;

	if ( empty( $_actions ) )
		return;

	echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
	echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
	echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";

	foreach ( $_actions as $name => $title ) {
		$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

		echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
	}

	echo "</select>\n";
	echo '<input type="submit" id="doaction'.$two.'" class="button action" value="'.__( 'Apply' ).'" />';
	echo "\n";
}

// Table Post
function woopanel_post_actions($post){
	$post_type_object = get_post_type_object( $post->post_type );
	$can_edit_post = current_user_can( 'edit_post', $post->ID );
	$actions = array();
	$title = get_the_title($post->ID);
	$endpoint = ($post->post_type == 'post') ? 'article' : $post->post_type;

	if ( $can_edit_post && 'trash' != $post->post_status ) {
		$actions['edit'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			woopanel_post_edit_url( $post->ID ),
			esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $title ) ),
			__( 'Edit' )
		);
	}


	if ( current_user_can( 'delete_post', $post->ID ) ) {
		if ( 'trash' === $post->post_status ) {
			$actions['untrash'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				wp_nonce_url( add_query_arg( 'action', 'untrash', woopanel_dashboard_url( $endpoint ) .'/?id='. $post->ID ), 'untrash-post_' . $post->ID ),
				esc_attr( sprintf( __( 'Restore &#8220;%s&#8221; from the Trash' ), $title ) ),
				__( 'Restore' )
			);
		} elseif ( EMPTY_TRASH_DAYS ) {
			$actions['trash'] = sprintf(
				'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				woopanel_post_delete_url( $post->ID ),
				esc_attr( sprintf( __( 'Move &#8220;%s&#8221; to the Trash' ), $title ) ),
				_x( 'Trash', 'verb' )
			);
		}
		if ( 'trash' === $post->post_status || ! EMPTY_TRASH_DAYS ) {
			$actions['delete'] = sprintf(
				'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				woopanel_post_delete_url( $post->ID, '', true ),
				esc_attr( sprintf( __( 'Delete &#8220;%s&#8221; permanently' ), $title ) ),
				__( 'Delete Permanently' )
			);
		}
	}

	if ( is_post_type_viewable( $post_type_object ) ) {
		if ( in_array( $post->post_status, array( 'pending', 'draft', 'future' ) ) ) {
			if ( $can_edit_post ) {
				$preview_link = get_preview_post_link( $post );
				$actions['view'] = sprintf(
					'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
					esc_url( $preview_link ),
					esc_attr( sprintf( __( 'Preview &#8220;%s&#8221;' ), $title ) ),
					__( 'Preview' )
				);
			}
		} elseif ( 'trash' != $post->post_status ) {
			$actions['view'] = sprintf(
				'<a href="%s" rel="bookmark" aria-label="%s">%s</a>',
				get_permalink( $post->ID ),
				esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), $title ) ),
				__( 'View' )
			);
		}
	}
	return $actions;
}