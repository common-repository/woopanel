<?php
class WooPanel_Product_Tags extends WooPanel_Taxonomy {
	public $taxonomy = 'product_tag';
	public $endpoint = 'product-tags';
	protected $columns = array();
	public $panels = array();

	public function __construct() {
		parent::__construct();
		
		$this->hooks_table();
	}


	public function form() {

	}

	public function hooks_table() {
		// Custom column data
		add_action( 'product_cat_add_form_fields', array( $this, 'add_category_fields' ), 99, 1 );
		add_action( 'woopanel_product_cat_image_column', array($this, 'woopanel_product_cat_image_column'), 99, 2);
		add_action( 'woopanel_save_taxonomy_product_cat', array($this, 'woopanel_save_taxonomy_product_cat'), 99, 2 );
		add_filter( 'woopanel_bulk_action_product_cat', array($this, 'woopanel_bulk_action_product_cat'), 99, 2);
	}

	public function hooks_form() {
		add_filter('woopanel_product_enter_title_here', array($this, 'enter_title_here' ), 999, 1 );
		add_action('woopanel_product_form_fields', array($this, 'form_fields'), 99, 1 );
		add_action( 'woopanel_product_save_post', array( $this, 'save_post'), 99, 2 );
		add_action( "woopanel_product_edit_form_after", array($this, 'edit_form_after'), 20, 2 );
	}

	/**
	 * Category thumbnail fields.
	 */
	public function add_category_fields( $taxonomy ) {
		woopanel_form_field(
			'display_type', 
			[
				'type'	=> 'select',
				'label'	=> __( 'Display type', 'woocommerce' ),
				'id'	=> 'display_type',
				'options' => array(
					''				=> __( 'Default', 'woocommerce' ),
					'products'		=> __( 'Products', 'woocommerce' ),
					'subcategories' => __( 'Subcategories', 'woocommerce' ),
					'both'			=> __( 'Both', 'woocommerce' )
				)
			],
			esc_html($post->post_title)
		);

		woopanel_form_field(
			'product_cat_thumbnail_id', 
			[
				'type'	=> 'image',
				'label'	=> __( 'Thumbnail', 'woocommerce' ),
				'id'	=> 'product_cat_thumbnail_id'
			],
			false
		);
	}

	public function woopanel_product_cat_image_column($echo, $tax) {
		$thumbnail_id = get_term_meta( $tax->term_id, 'thumbnail_id', true );

		if ( $thumbnail_id ) {
			$image = wp_get_attachment_thumb_url( $thumbnail_id );
		}

		if(empty($image)) {
			$image = wc_placeholder_img_src();
		}

		$image    = str_replace( ' ', '%20', $image );
		echo '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Thumbnail', 'woocommerce' ) . '" class="wp-post-image" height="48" width="48" />';
	}

	public function woopanel_save_taxonomy_product_cat( $term_id, $data ) {
		if ( isset( $data['display_type'] ) && 'product_cat' === $this->taxonomy ) {
			update_term_meta( $term_id, 'display_type', esc_attr( $data['display_type'] ) );
		}
		if ( isset( $data['product_cat_thumbnail_id'] ) && 'product_cat' === $this->taxonomy ) {
			update_term_meta( $term_id, 'thumbnail_id', absint( $data['product_cat_thumbnail_id'] ) );
		}
	}

	public function woopanel_bulk_action_product_cat($return, $term) {
		$default_category_id = absint( get_option( 'default_product_cat', 0 ) );

		if ( $default_category_id == $term->term_id ) {
			echo '<span class="dashicons dashicons-editor-help" data-toggle="tooltip" data-original-title="' . __( 'This is the default category and it cannot be deleted. It will be automatically assigned to products with no category.', 'woocommerce' ) .'"></span>';
		}else {
			return true;
		}
	}
}