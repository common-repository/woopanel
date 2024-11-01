<?php
class WooPanel_Template_Comment extends WooPanel_List_Table {
	public $type;
	public $type_edit;
	public $type_settings;
	public $post_type;

	private $comment_status;
	private $user_can;
	private $get_statuses;
	private $current_user;

	protected $comments;

	public function __construct( $args = array() ) {
		global $query_vars;
		$this->type = isset($args['type']) ? $args['type'] : 'comments';
		$this->type_edit = isset($args['type_edit']) ? $args['type_edit'] : 'comment';
		
		$this->post_type = isset($args['post_type']) ? $args['post_type'] : 'post';
		$this->type_settings = array(
            'name' => __('Comments'),
            'singular_name' => __('Comment'),
			'not_found' => false,
			'add_new' => false,
			'create_permission' => false,
			'search_items' => false
		);
		$this->get_statuses = array(
			'hold'	    => __('Pending'),
            'approve'	=> __('Approved'),
            'spam'	    => __('Spam'),
            'trash'     => __('Trash'),
        );
		
		parent::__construct( array(
			'type'   => 'WP_User_Query',
			'screen'         => 'customers',
			'columns'        => array(
				'avatar'      => '&nbsp;',
				'title'      => __( 'Author'),
				'comment'    => __( 'Comments' ),
				'response_to'	=> __( 'In Response To' ),
				'submitted_on'  => __( 'Submitted On' )
			),
			'primary_columns' => 'title'
		) );
	
		$this->comments = $this->get_query(array(
			'per_page' => $this->per_page,
			'offset' => ($this->paged - 1) * $this->per_page,
			'orderby'      => 'ID',
			'order'        => 'ASC'
		));

		$this->comment_status = isset( $_REQUEST['comment_status'] ) ? wp_unslash( $_REQUEST['comment_status'] ) : null;
		$this->current_user = woopanel_current_user();
		$this->hooks_table();
	}

	public function lists() {
		$this->display();
	}
	
	public function form() {
		global $wpdb;

		$comment_id = absint($_GET['id']);
		if( isset($_POST['comment_author']) ) {
			$wpdb->update( 
				$wpdb->comments, 
				array(
					'comment_author' => $_POST['comment_author'],
					'comment_author_email' => $_POST['comment_email'],
					'comment_author_url' => $_POST['comment_url'],
					'comment_content' => $_POST['comment_content'],
					'comment_approved' => $_POST['post_status']
				), 
				array( 'comment_ID' => $comment_id ), 
				array( 
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				), 
				array( '%d' ) 
			);

			if( isset($_POST['_type']) ) {
				wpl_add_notice( "comments", __(  'Comments updated.', 'woopanel' ), 'success' );
			}
			

			do_action("woopanel_{$this->type}_comment_meta", $comment_id, $_POST);
		}
		
		$query = array();
		$query['fields']  = "SELECT comment.*, post.* FROM {$wpdb->comments} as comment";
		$query['join']    = "LEFT JOIN {$wpdb->posts} AS post ON comment.comment_post_ID = post.ID";
		$query['where']	  = "WHERE comment_ID = '{$comment_id}' AND post.post_status = 'publish' ";

		$sql = implode(' ', $query);

		$comment = $wpdb->get_row( $sql );

		if($comment) {

 			if( $this->current_user['roles'] != 'administrator' && $comment->post_author != $this->current_user['ID'] ) {
				woopanel_redirect($this->get_view_url());
			}

			wpl_print_notices();

 			woopanel_get_template_part('comment/edit', '', array(
				'comment' 	 => $comment,
				'type'	  	 => $this->type,
				'statuses'	 => $this->get_statuses,
				'status'	 => $this->set_status($comment->comment_approved)
			));
		}
	}

	public function hooks_table() {
		add_filter("woopanel_{$this->type}_avatar_column", array($this, 'avatar_column'), 99, 2);
		add_filter("woopanel_{$this->type}_title_column", array($this, 'title_column'), 99, 2);
		add_filter("woopanel_{$this->type}_comment_column", array($this, 'comment_column'), 99, 2);
		add_filter("woopanel_{$this->type}_response_to_column", array($this, 'response_to_column'), 99, 2);
		add_filter("woopanel_{$this->type}_submitted_on_column", array($this, 'submitted_on_column'), 99, 2);

		add_action("woopanel_{$this->type}_filter_display", array($this, 'filter_display'), 99, 2 );
        add_action("woopanel_comments_no_item_icon", array($this, 'no_item_icon'));
	}


	public function set_status($status) {
		switch( $status ) {
			case '1':
				$status = 'hold';
				break;
			case '0':
				$status = 'approve';
				break;
		}

		return $status;
	}

	public function filter_display($post_type, $post_type_object) {
		$status = isset($_GET['status']) ? strip_tags($_GET['status']) : '';

		if( isset($_GET['post_status']) ) {
			$status = strip_tags($_GET['post_status']);
		}
		?>
		<div class="col-md-4">
			<div class="m-form__group m-form__group--inline">
				<div class="m-form__label"><label for="filter-status"><?php _e('Status', 'woocommerce');?></label></div>
				<div class="m-form__control">
					<select name="status" id="filter-status" class="form-control m-bootstrap-select">
						<option selected='selected' value="all"><?php _e( 'All status', 'woopanel' );?></option>
						<?php foreach( $this->get_statuses as $k_status => $val_status) {
							printf('<option value="%s" %s>%s</option>', $k_status, selected( $k_status, $status, false ), $val_status);
						}?>
					</select>
				</div>
			</div>
			<div class="d-md-none m--margin-bottom-10"></div>
		</div>
		<?php
	}

    public function no_item_icon() {
        echo '<i class="flaticon-chat-1"></i>';
    }

	public function avatar_column($return, $comment) {
		$this->user_can = current_user_can( 'edit_comment', $comment->comment_ID );
		echo get_avatar( $comment, 32 );
	}

	public function title_column($return, $comment) {
		$cm_status = array();
		if( isset($_GET['status']) ) {
			$cm_status['status'] = $_GET['status'];
		}
		echo '<strong><a class="row-title" href="' . $this->get_view_url() . '?post=' . $comment->comment_post_ID . '" title="'. get_comment_author( $comment ) .'">'. get_comment_author( $comment ) .'</a></strong><br />';
		if ( $this->user_can ) {
			if ( ! empty( $comment->comment_author_email ) ) {
				/** This filter is documented in wp-includes/comment-template.php */
				$email = apply_filters( 'comment_email', $comment->comment_author_email, $comment );

				if ( ! empty( $email ) && '@' !== $email ) {
					printf( '<a href="%1$s">%2$s</a><br />', esc_url( 'mailto:' . $email ), esc_html( $email ) );
				}
			}

			$author_ip = get_comment_author_IP( $comment );
			if ( $author_ip ) {
				$cm_status['search_name'] = $author_ip;
				$author_ip_url = add_query_arg(
					$cm_status,
					$this->get_view_url()
				);
				if ( 'spam' === $this->comment_status ) {
					$author_ip_url = add_query_arg( 'comment_status', 'spam', $author_ip_url );
				}
				printf( '<a href="%1$s">%2$s</a>', esc_url( $author_ip_url ), esc_html( $author_ip ) );
			}
		}
	}

	public function comment_column($return, $comment) {
		echo '<p style="white-space: pre-line;">' . str_replace('\\', '', $comment->comment_content) .'</p>';


		if ( ! $this->user_can ) {
			return;
		}

		$the_comment_status = wp_get_comment_status( $comment );

		$out = '';

		$del_nonce     = esc_html( '_wpnonce=' . wp_create_nonce( "delete-comment_$comment->comment_ID" ) );
		$approve_nonce = esc_html( '_wpnonce=' . wp_create_nonce( "approve-comment_$comment->comment_ID" ) );

		$url = "?c=$comment->comment_ID";

		$approve_url   = esc_url( $url . "&action=approvecomment&$approve_nonce" );
		$unapprove_url = esc_url( $url . "&action=unapprovecomment&$approve_nonce" );
		$spam_url      = esc_url( $url . "&action=spamcomment&$del_nonce" );
		$unspam_url    = esc_url( $url . "&action=unspamcomment&$del_nonce" );
		$trash_url     = esc_url( $url . "&action=trashcomment&$del_nonce" );
		$untrash_url   = esc_url( $url . "&action=untrashcomment&$del_nonce" );
		$delete_url    = esc_url( $url . "&action=deletecomment&$del_nonce" );

		// Preorder it: Approve | Reply | Quick Edit | Edit | Spam | Trash.
		$actions = array(
			'approve'   => '',
			'unapproved' => '',
			'edit'      => '',
			'spam'      => '',
			'unspam'    => '',
			'trash'     => '',
			'untrash'   => '',
			'delete'    => '',
		);

		// Not looking at all comments.
		$actions['approve']   = "<a href='#' class='comment-link' data-action='approve' data-id='". $comment->comment_ID ."' aria-label='" . esc_attr__( 'Approve this comment', 'woopanel' ) . "'>" . __( 'Approve', 'woopanel' ) . '</a>';
		$actions['unapproved'] = "<a href='#' class='comment-link' data-action='unapprove' data-id='". $comment->comment_ID ."' aria-label='" . esc_attr__( 'Unapprove this comment', 'woopanel' ) . "'>" . __( 'Unapprove', 'woopanel' ) . '</a>';

		if ( 'spam' !== $the_comment_status ) {
			$actions['spam'] = "<a href='#' class='comment-link' data-action='spam' data-id='". $comment->comment_ID ."' aria-label='" . esc_attr__( 'Mark this comment as spam', 'woopanel' ) . "'>" . _x( 'Spam', 'verb' ) . '</a>';
		} elseif ( 'spam' === $the_comment_status ) {
			$actions['unspam'] = "<a href='#' class='comment-link' data-action='unspam' data-id='". $comment->comment_ID ."' aria-label='" . esc_attr__( 'Restore this comment from the spam', 'woopanel' ) . "'>" . __( 'Not Spam', 'woopanel' ) . '</a>';
			unset($actions['approve']);
            unset($actions['unapproved']);
		}

		if ( 'trash' === $the_comment_status ) {
            unset($actions['edit']);
            unset($actions['approve']);
            unset($actions['unapproved']);
			$actions['untrash'] = "<a href='#' class='comment-link' data-action='untrash' data-id='". $comment->comment_ID ."' aria-label='" . esc_attr__( 'Restore this comment from the Trash', 'woopanel' ) . "'>" . __( 'Restore' ) . '</a>';
		}

		if ( 'spam' === $the_comment_status || 'trash' === $the_comment_status || ! EMPTY_TRASH_DAYS ) {
			$actions['delete'] = "<a href='#' class='comment-link' data-action='delete' data-id='". $comment->comment_ID ."' aria-label='" . esc_attr__( 'Delete this comment permanently', 'woopanel' ) . "'>" . __( 'Delete Permanently' ) . '</a>';
		} else {
			$actions['trash'] = "<a href='#' class='comment-link' data-action='trash' data-id='". $comment->comment_ID ."' aria-label='" . esc_attr__( 'Move this comment to the Trash', 'woopanel' ) . "'>" . _x( 'Trash', 'verb' ) . '</a>';
		}

		if ( 'spam' !== $the_comment_status && 'trash' !== $the_comment_status ) {
			$actions['edit'] = "<a href='". $this->get_view_url($comment->comment_ID, true) ."' data-action='edit' data-id='". $comment->comment_ID ."'  aria-label='" . esc_attr__( 'Edit this comment', 'woopanel' ) . "'>" . __( 'Edit' ) . '</a>';
			$format = '<button type="button" data-comment-id="%d" data-post-id="%d" data-action="%s" class="%s button-link" aria-expanded="false" aria-label="%s">%s</button>';
		}

		/** This filter is documented in wp-admin/includes/dashboard.php */
		$actions = apply_filters( 'comment_row_actions', array_filter( $actions ), $comment );

		$i    = 0;

		$out .= '<div class="row-actions">';
		foreach ( $actions as $action => $link ) {
			++$i;
			( ( ( 'approve' === $action || 'unapproved' === $action ) && 2 === $i ) || 1 === $i ) ? $sep = '' : $sep = ' | ';

			// Reply and quickedit need a hide-if-no-js span when not added with ajax
			if ( ( 'reply' === $action || 'quickedit' === $action ) && ! wp_doing_ajax() ) {
                $action .= ' hide-if-no-js';
            }
            if( ($the_comment_status == 'approved' && 'approve' === $action) ||
                ($the_comment_status == 'unapproved' && 'unapproved' === $action) ) $action .= ' hidden';

			$out .= "<span class='$action'>$sep$link</span>";
		}
		$out .= '</div>';

		echo $out;
	}

	public function response_to_column($return, $comment) {
		$post = get_post($comment->comment_post_ID);

		if ( ! $post ) {
			return;
		}

		if ( isset( $this->pending_count[ $post->ID ] ) ) {
			$pending_comments = $this->pending_count[ $post->ID ];
		} else {
			$_pending_count_temp = $this->get_pending_comments_num( array( $post->ID ) );
			$pending_comments    = $this->pending_count[ $post->ID ] = $_pending_count_temp[ $post->ID ];
		}

		if ( current_user_can( 'edit_post', $post->ID ) ) {
			$post_link  = "<a href='" . $this->get_view_url() . '?post=' . $comment->comment_post_ID . "' class='comments-edit-item-link'>";
			$post_link .= esc_html( get_the_title( $post->ID ) ) . '</a>';
		} else {
			$post_link = esc_html( get_the_title( $post->ID ) );
		}

		echo '<div class="response-links">';
		if ( 'attachment' === $post->post_type && ( $thumb = wp_get_attachment_image( $post->ID, array( 80, 60 ), true ) ) ) {
			echo $thumb;
		}
		echo $post_link;
		$post_type_object = get_post_type_object( $post->post_type );
		echo "<a href='" . get_permalink( $post->ID ) . "' class='comments-view-item-link' target='_blank'>" . $post_type_object->labels->view_item . '</a>';
		do_action("woopanel_{$this->type}_response_to", $comment);
		echo '</div>';
	}
	
	public function submitted_on_column($return, $comment) {
		/* translators: 1: comment date, 2: comment time */
		$submitted = sprintf(
			__( '%1$s at %2$s' ),
			/* translators: comment date format. See https://secure.php.net/date */
			get_comment_date( __( 'Y/m/d' ), $comment ),
			get_comment_date( __( 'g:i a' ), $comment )
		);

		echo '<div class="submitted-on">';
		if ( 'approved' === wp_get_comment_status( $comment ) && ! empty( $comment->comment_post_ID ) ) {
			printf(
				'<a href="%s">%s</a>',
				esc_url( get_comment_link( $comment ) ),
				$submitted
			);
		} else {
			echo $submitted;
		}
		echo '</div>';
	}

	public function get_view_url( $user_id  = null, $edit = false ) {
		$slug = isset($user_id) ? '?id='. absint($user_id) : '';
		$type = $this->type;
		if( $edit ) {
			$type = $this->type_edit;
		}
		return esc_url( woopanel_get_endpoint_url($type). $slug);
	}


	/**
	 * Get the number of pending comments on a post or posts
	 *
	 * @since 2.3.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param int|array $post_id Either a single Post ID or an array of Post IDs
	 * @return int|array Either a single Posts pending comments as an int or an array of ints keyed on the Post IDs
	 */
	public function get_pending_comments_num( $post_id ) {
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

	/**
	 *
	 * @global array    $avail_post_stati
	 * @global WP_Query $wp_query
	 * @global int      $per_page
	 * @global string   $mode
	 */
	public function get_query($args = null) {
		global $wpdb;

		$defaults = array(
			'post_type'   => $this->post_type,
			'post_status' => 'publish'
		);

		$defaults['status'] = 'all';
		if( isset($_GET['status']) && isset($this->get_statuses[$_GET['status']]) ) {
			$defaults['status'] = $_GET['status'];
		}

		if( isset($_GET['search_name']) ) {
			$defaults['search'] = $_GET['search_name'];
		}

		if( isset($_GET['post']) ) {
			$defaults['post_id'] = $_GET['post'];
		}



		$user = wp_get_current_user();
		$query = array();



		if( ! empty($args['count']) ) {
			$query['fields']  = "SELECT COUNT(DISTINCT comments.comment_ID) as total_comments FROM {$wpdb->comments} as comments";
		}else {
			// Custom select field here
			$query['fields']  = "SELECT DISTINCT comments.comment_ID, comments.* FROM {$wpdb->comments} as comments";
		}

		$query['join']   = "INNER JOIN {$wpdb->posts} AS posts ON comments.comment_post_ID = posts.ID";
		$query['where']	   = "WHERE 1=1 ";
		$query['where']	  .= "AND posts.post_type = '". $this->post_type ."' ";


		if( in_array($user->roles[0], NBWooPanel::$permission)) {
			$query['where']	  .= "AND posts.post_author = '". $user->ID ."' ";
		}

		$query['where'] .= "AND posts.post_status != 'trash' ";

		if( isset($_GET['post']) && is_numeric($_GET['post']) ) {
			$query['where']	  .= "AND comments.comment_post_ID = '". $_GET['post'] ."' ";
		}
		
		if( isset($_GET['status']) && isset($this->get_statuses[$_GET['status']]) ) {
			$query['where'] .=  "AND comments.comment_approved = '" . $this->exchange_status($_GET['status']) . "' ";
		} else if(!isset($_GET['status']) || (isset($_GET['status']) && ($_GET['status']=='all' || $_GET['status']=='') ) ) {
            $query['where'] .=  "AND (comments.comment_approved = '0' OR comments.comment_approved = '1') ";
        }

		if( isset($_GET['search_name']) ) {
			$search_name = strip_tags($_GET['search_name']);
			$search_like = '%'.$wpdb->esc_like( $search_name ).'%';
			$query['where'] .=  $wpdb->prepare("AND (((comments.comment_content LIKE %s) OR (comments.comment_author_email LIKE %s) OR (comments.comment_author LIKE %s)))", $search_like, $search_like, $search_like);
		}

		$query['orderby'] = "ORDER BY comment_date DESC";

		if( empty($args['count']) ) {
			$query['limit']   = "LIMIT {$args["offset"]}, {$args["per_page"]}";
		}

		$sql = implode(' ', $query);

  		if( ! empty($args['count']) ) {
			$results = $wpdb->get_row( $sql );
			return $results->total_comments;
		}else {
			return $wpdb->get_results($sql);
		}

	}

	public function exchange_status($status) {
		if( $status == 'approve' ) {
			$status = 1;
		}

		if( $status == 'hold' ) {
			$status = 0;
		}

		return $status;
	}

	public function has_items() {
		$query = $this->get_query(array(
			'count' => true
		));

		if( ! empty( $query ) ) {
			return true;
		}
	}

	/**
	 * Display the table
	 *
	 * @since 1.0.0
	 */
	public function display_rows_or_placeholder() {

		if ( $this->has_items() ) {
			foreach ( $this->comments as $index => $user ) {
				?>
				<tr id="cm-hide-<?php echo $user->comment_ID;?>" class="wpl-datatable__row wpl-datatable__row_cmhide" style="display: none">
					<td class="wpl-datatable__cell--center wpl-datatable__cell wpl-datatable__cell--check">
						<span><label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand" for="cb-select-">
								<input id="cb-select-" type="checkbox" name="user[]" value="">
								<span></span>
							</label>
						</span>
					</td>
					<td class="wpl-datatable__cell column-avatar" data-colname="&nbsp;">
						<?php echo get_avatar( $user, 32 );?>
					</td>
					<td colspan="4" class="wpl-datatable__cell">
						<div class="spam-undo-inside" style="display: none"><?php printf( __( 'Comment by %s marked as spam.' ), '<strong>' . $user->comment_author .'</strong>' );?> <span class="undo unspam"><a href="" class="cm-destructive" data-id="<?php echo $user->comment_ID;?>"><?php _e( 'Undo' );?></a></span></div>
						<div class="trash-undo-inside" style="display: none"><?php printf( __( 'Comment by %s moved to the trash.' ), '<strong>' . $user->comment_author .'</strong>' );?> <span class="undo untrash"><a href="" class="cm-destructive" data-id="<?php echo $user->comment_ID;?>"><?php _e( 'Undo' );?></a></span></div>
					</td>
				</tr>
				<tr id="user-<?php echo $user->comment_ID;?>" class="wpl-datatable__row wpl-datatable_edit author-self level-0 user-<?php echo $user->comment_ID;?> format-standard has-post-thumbnail tr-status-<?php echo wp_get_comment_status($user);?> x<?php echo $user->comment_approved;?>">
					<td class="wpl-datatable__cell--center wpl-datatable__cell wpl-datatable__cell--check">
						<span>
							<label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand" for="cb-select-<?php echo $user->comment_ID;?>">
								<input id="cb-select-<?php echo $user->comment_ID;?>" type="checkbox" name="user[]" value="<?php echo $user->comment_ID;?>">
								<span></span>
							</label>
						</span>
					</td>

					<?php
					if( empty($this->template) ) {
						foreach( $this->columns as $key_column => $column ) {?>
							<td class="wpl-datatable__cell column-<?php echo $key_column;?>" data-colname="<?php echo $column;?>">
								<?php
								$action_name = "woopanel_{$this->type}_{$key_column}_column";
								if( ! has_filter($action_name) ) {
									echo '-';
								}else {
									echo apply_filters($action_name, '', $user, false);
								}
								?>
							</td>
						<?php }
					}?>
				</tr>
				<?php
			}
		}
	}

	public function comment_status($comment) {
		if( $comment == '0' ) {
			$comment = 'approve';
		}else if( $comment == '1' ) {
			$comment = 'unapprove';
		}

		return $comment;
	}

	public function display() {
		$total_items = $this->get_query(
			array('count' => true)
		);
		?>
		<form id="posts-filter" method="get">
			<div id="list-<?php echo $this->type;?>-table" class="woopanel-list-post-table m-portlet m-portlet--mobile">
				<!--begin: Head -->
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<h3 class="m-portlet__head-text">
								<?php echo $this->type_settings['name'];?>
								<small><span class="displaying-num"><?php echo sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) );?></span></small>
							</h3>
						</div>
					</div>
					<div class="m-portlet__head-tools">
						<?php if ( apply_filters("woopanel_{$this->type}_user_can_create", true) && current_user_can( $this->type_settings['create_permission'] ) ) { ?>
							<a href="<?php echo esc_html( woopanel_post_new_url($this->type) );?>" class="btn btn-secondary m-btn m-btn--icon m-btn--md">
								<span>
									<i class="flaticon-add"></i>
									<span><?php echo esc_html( $this->type_settings['add_new']); ?></span>
								</span>
							</a>
						<?php } ?>
					</div>
				</div>
				<!--end: Head -->

				<div class="m-portlet__body">
				    <?php // if( $this->has_items() || isset($_GET['search_name']) ) { ?>
					<!--begin: Search Form -->
					<div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
						<div class="row align-items-center">
							<div class="col-xl-9 order-2 order-xl-1">
								<div class="form-group m-form__group row align-items-center">
									<?php do_action( "woopanel_{$this->type}_filter_display", $this->type, $this->type_settings );?>

									<div class="col-md-4">
										<div class="m-input-icon m-input-icon--left">
											<input type="text" class="form-control m-input" name="search_name" placeholder="<?php _e( 'Search comment', 'woopanel' );?>" id="generalSearch" value="<?php echo isset($_GET['search_name']) ? strip_tags($_GET['search_name']) : '';?>">
											<span class="m-input-icon__icon m-input-icon__icon--left">
												<span><i class="la la-search"></i></span>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-3 order-1 order-xl-2 m--align-right">
								<button type="submit" name="filter_action" id="post-query-submit" class="btn btn-accent m-btn m-btn--custom m-btn--icon">
									<span>
										<i class="la la-filter"></i>
										<span><?php _e('Filter', 'woopanel'); ?></span>
									</span>
								</button>
								<div class="m-separator m-separator--dashed d-xl-none"></div>
							</div>
						</div>
					</div>
					<!--end: Search Form -->
					<?php // } ?>

					<div class="m-datatable m-datatable--default wpl_datatable<?php if( ! $this->has_items() ) { echo ' m-datatable-empty';}?>">
						<div class="table-responsive">
							<table class="wpl-datatable__table table m-table">
								<thead class="wpl-datatable__head">
									<tr class="wpl-datatable__row">
										<?php $this->print_column_headers(); ?>
									</tr>
								</thead>

								<tbody class="wpl-datatable__body">
									<?php
									if ( $this->has_items() ) {
										$this->display_rows_or_placeholder();
									}else {
										$this->no_items();
									}?>
								</tbody>
							</table>
						</div>

						<?php
						if ( $this->has_items() ) {
							$max_num_pages = ceil($total_items / $this->per_page);
							$this->display_paginate($total_items, $max_num_pages);
						}?>
	
					</div>
				</div>
			</div>
		</form>
		<?php
	}

}