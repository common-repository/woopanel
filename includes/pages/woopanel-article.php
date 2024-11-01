<?php
class WooPanel_Template_Article {
	private $post_statuses = array();
	private $classes;
	public $taxonomy = 'category';
	public $tags = 'post_tag';

	public function __construct() {
		$this->post_statuses = get_post_statuses();

		$this->classes = new WooPanel_Post_List_Table(array(
			'post_type'     	=> 'post',
			'taxonomy'			=> $this->taxonomy,
			'editor'			=> true,
			'thumbnail'			=> true,
			'preview'			=> true,
			'tags'			=> $this->tags,
			'screen'        	=> 'posts',
			'columns'       	=> array(
				'title'     	=> __( 'Title', 'woocommerce' ),
				'author'      	=> __( 'Author', 'woocommerce' ),
				'categories'    => __( 'Categories', 'woocommerce' ),
				'tags'   		=> __( 'Tags', 'woocommerce' ),
				'comments'     	=> '<span class="vers comment-grey-bubble" data-toggle="tooltip" data-placement="top" data-original-title="'. __('Comments') .'"><span class="screen-reader-text">'. __('Comments') .'</span></span>',
				'date'   		=> __( 'Date', 'woocommerce' )
			),
			'primary_columns' 	=> 'title',
			'post_statuses' 	=> $this->post_statuses,
		));

		$this->hooks_table();
		$this->hooks_form();
	}

	public function lists() {
		$this->classes->prepare_items();
		$this->classes->display();
	}

	public function form() {
		$this->classes->form();
	}

	public function hooks_table() {
		// Custom column data
		add_filter( 'woopanel_post_author_column', array($this, 'author_custom'), 99, 3);
		add_filter( 'woopanel_post_categories_column', array($this, 'categories_custom'), 99, 3);
		add_filter( 'woopanel_post_tags_column', array($this, 'tags_custom'), 99, 3);
		add_filter( 'woopanel_post_comments_column', array($this, 'comments_custom'), 99, 3);

		add_action( 'woopanel_post_filter_display', array($this, 'filter_display'), 99, 2 );
		add_filter( 'posts_distinct', array($this, 'search_distinct'), 99, 1 );
		add_action( 'woopanel_post_no_item_icon', array($this, 'no_item_icon'));
	}

	public function hooks_form() {
		add_action( "woopanel_post_edit_form_after", array($this, 'edit_form_after'), 20, 2 );
	}

	public function edit_form_after($action, $post) {
		woopanel_form_field(
			'comment_status',
			array(
				'type'		  => 'checkbox',
				'id'          => 'comment_status',
				'label'       => '&nbsp;',
				'description' => __( 'Allow Comments' ),
				'default'	  => 'open'
			),
			$post->comment_status
		);
	}

	public function no_item_icon() {
		echo '<i class="flaticon-book"></i>';
	}

	public function author_custom($html, $post, $data) {
		echo '<a href="#">'. get_the_author() .'</a>';
	}

	public function categories_custom($html, $post, $data) {
		echo woopanel_column_taxonomy($post, $this->taxonomy, 'cat');
	}

	public function tags_custom($html, $post, $data) {
		echo woopanel_column_taxonomy($post, $this->tags, $this->tags);
	}

	public function comments_custom($html, $post, $data) {
		echo woopanel_column_comments($post);
	}

	public function filter_display($post_type, $post_type_object) {

		$status = isset($_GET['status']) ? strip_tags($_GET['status']) : '';
		?>
		<div class="col-md-4">
			<div class="m-form__group m-form__group--inline">
				<?php woopanel_filter_taxonomies_dropdown($post_type, $this->taxonomy, 'cat');?>
			</div>
			<div class="d-md-none m--margin-bottom-10"></div>
		</div>
		<?php
	}

	public function search_distinct( $where ) {
		return "DISTINCT";
	}
}