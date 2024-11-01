<?php
class WooPanel_Template_Review extends WooPanel_Template_Comment {
	public function __construct() {
		parent::__construct( array(
			'type'		=> 'reviews',
			'type_edit' => 'review',
			'post_type' => 'product'
		) );

		$this->type_settings = array(
            'name' => __( 'Reviews', 'woocommerce' ),
            'singular_name' => __( 'Review', 'woocommerce' ),
            'not_found' => false,
            'add_new' => false,
            'create_permission' => false,
            'search_items' => false
        );

		$this->hooks();
	}

	public function hooks() {
		add_action( 'woopanel_reviews_response_to', array($this, 'response_to') );
		add_action( 'woopanel_reviews_edit_form_after', array($this, 'edit_form_after'), 20, 2 );
		add_action( 'woopanel_reviews_comment_meta', array($this, 'comment_meta'), 20, 2 );
        add_action( 'woopanel_reviews_no_item_icon', array($this, 'no_item_icon'));
	}
    public function no_item_icon() {
        echo '<i class="flaticon-customer"></i>';
    }

	public function response_to($comment) {
		$current = get_comment_meta( $comment->comment_ID, 'rating', true );
		echo '<div class="comment-star">';
		for ( $rating = 1; $rating <= 5; $rating ++ ) {
			if( $rating <= $current ) {
				echo '<i class="la la-star"></i>';
			}else {
				echo '<i class="la la-star-o"></i>';
			}
		}
	}

	public function edit_form_after( $post ) {
		$star = array();
		for ( $rating = 1; $rating <= 5; $rating ++ ) {
			$star[$rating] = $rating;
		}
		woopanel_form_field( 'comment_star',
			[
					'type'      => 'select',
					'label'     => __( 'Rating', 'woocommerce' ),
					'id'		=> 'comment_star',
					'class'		=> ['comment_star'],
					'options'	=> $star
			],
		get_comment_meta( $post->comment_ID, 'rating', true ) );
	}

	public function comment_meta($comment_id, $data) {
		if( isset($data['_type']) && $data['_type'] == 'reviews' ) {
			wpl_add_notice( "reviews", __(  'Reviews updated.', 'woopanel' ), 'success' );
			update_comment_meta( $comment_id, 'rating', $data['comment_star'] );
		}
	}

}