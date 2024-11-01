<?php
class WooPanel_Comment_Ajax {
	public function __construct() {
		add_action( 'wp_ajax_woopanel_comment_link', array($this, 'comment_action') );
	}
	
	public static function comment_action() {
		global $wpdb;
		
		$json = array();
		
		$method = sanitize_text_field($_POST['method']);
		$comment_id = absint($_POST['id']);
		$status_message = $status_class = $html_tr = '';

		$query = array();
		$query['fields']  = "SELECT comment.*, post.* FROM {$wpdb->comments} as comment";
		$query['join']    = "LEFT JOIN {$wpdb->posts} AS post ON comment.comment_post_ID = post.ID";
		$query['where']	  = "WHERE comment_ID = '{$comment_id}' AND post.post_status = 'publish'";

		$sql = implode(' ', $query);
		$get_comment = $wpdb->get_row( $sql );


		if( $get_comment ) {
			if( ! is_shop_staff() ) return;

			if( $method ) {
				switch( $method ) {
					case 'unapprove':
                        wp_set_comment_status( $comment_id, 0 );
						$status_message = __('Approve');
						$status_class = 'approved';
						break;
					case 'approve':
                        wp_set_comment_status( $comment_id, 1 );
						$status_message = __('Unapprove');
						$status_class = 'unapproved';
						break;
					case 'spam':
                        wp_spam_comment( $comment_id );
						$json['template'] = '<tr id="cm-hide-'. $comment_id .'" class="wpl-datatable__row"><td class="wpl-datatable__cell--center wpl-datatable__cell wpl-datatable__cell--check">
						<span><label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand" for="cb-select-">
								<input id="cb-select-" type="checkbox" name="user[]" value="">
								<span></span>
							</label>
						</span>
					</td><td class="wpl-datatable__cell column-avatar" data-colname="&nbsp;"><img alt="" src="http://2.gravatar.com/avatar/88de7e0be9f793ed162ffa78b9cd4a12?s=32&amp;d=mm&amp;r=g" srcset="http://2.gravatar.com/avatar/88de7e0be9f793ed162ffa78b9cd4a12?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"></td><td colspan="4" class="wpl-datatable__cell"><div class="spam-undo-inside">'. sprintf( __( 'Comment by %s marked as spam.' ), '<strong>' . $get_comment->comment_author .'</strong>' ).' <span class="undo unspam"><a href="" class="cm-destructive" data-id="'. $comment_id .'">'. __( 'Undo' ) .'</a></span></div></td></tr>';
						break;
                    case 'unspam':
                        wp_unspam_comment( $comment_id );
                        break;
					case 'trash':
                        wp_trash_comment( $comment_id );
						$json['template'] = '<tr id="cm-hide-'. $comment_id .'" class="wpl-datatable__row"><td class="wpl-datatable__cell--center wpl-datatable__cell wpl-datatable__cell--check">
						<span><label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand" for="cb-select-">
								<input id="cb-select-" type="checkbox" name="user[]" value="">
								<span></span>
							</label>
						</span>
					</td><td class="wpl-datatable__cell column-avatar" data-colname="&nbsp;"><img alt="" src="http://2.gravatar.com/avatar/88de7e0be9f793ed162ffa78b9cd4a12?s=32&amp;d=mm&amp;r=g" srcset="http://2.gravatar.com/avatar/88de7e0be9f793ed162ffa78b9cd4a12?s=64&amp;d=mm&amp;r=g 2x" class="avatar avatar-32 photo" height="32" width="32"></td><td colspan="4" class="wpl-datatable__cell">
					<div class="trash-undo-inside">'. sprintf( __( 'Comment by %s moved to the trash.' ), '<strong>' . $get_comment->comment_author .'</strong>' ).' <span class="undo untrash"><a href="" class="cm-destructive" data-id="'. $comment_id .'">'. __( 'Undo' ) .'</a></span></div>
					</td></tr>';
						break;
                    case 'untrash':
                        wp_untrash_comment( $comment_id );
                        break;
                    case 'delete':
                        wp_delete_comment( $comment_id );
                        break;
					default:
						break;
				}
				
				$json['complete'] = true;
				$json['status_msg'] = $status_message;
				$json['status_class'] = $status_class;
			}
		}

		wp_send_json($json);
	}
}

new WooPanel_Comment_Ajax();