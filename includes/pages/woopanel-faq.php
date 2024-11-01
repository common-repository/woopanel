<?php
class WooPanel_Template_FAQs {
	private $post_statuses = array();
	private $classes;
	public $panels = array();

	public function __construct() {
		$this->post_statuses = get_post_statuses();

		$this->classes = new WooPanel_Post_List_Table(array(
			'post_type'     	=> 'wpl-faq',
			'taxonomy'			=> false,
			'editor'			=> true,
			'thumbnail'			=> true,
			'preview'			=> true,
			'tags'				=> false,
			'gallery'		=> true,
			'screen'        	=> 'posts',
			'columns'       	=> array(
				'title'     	=> __( 'Title', 'woocommerce' ),
				'shortcode'     => __( 'Shortcode', 'woocommerce' ),
				'date'   		=> __( 'Date', 'woocommerce' )
			),
			'primary_columns' 	=> 'title',
			'post_statuses' 	=> $this->post_statuses,
		));

		$this->hooks_table();
		$this->hooks_form();

		
	}

	public function index() {
		$this->classes->prepare_items();
		$this->classes->display();
	}

	public function form() {
		$this->classes->form(
			array(

			)
		);
	}

	public function hooks_table() {
		// Custom column data
		add_filter( 'woopanel_wpl-faq_shortcode_column', array($this, 'shortcode_custom'), 99, 3);


        add_action( 'woopanel_product_no_item_icon', array($this, 'no_item_icon'));

		add_action( 'woopanel_product_filter_display', array($this, 'filter_display'), 99, 2 );
		add_filter( 'posts_distinct', array($this, 'search_distinct'), 99, 1 );
		add_filter( 'woopanel_product_state', array($this, 'product_state'), 99, 2);
	}

	public function hooks_form() {
		add_filter('woopanel_wpl-faq_enter_title_here', array($this, 'enter_title_here' ), 999, 1 );
		add_action('woopanel_wpl-faq_form_fields', array($this, 'form_fields'), 99, 1 );
		add_action( 'woopanel_wpl-faq_save_post', array( $this, 'save_post'), 99, 2 );
		add_action( "woopanel_product_edit_form_after", array($this, 'edit_form_after'), 20, 2 );
	}



	public function edit_form_after($action, $post) {
		woopanel_form_field(
			'comment_status',
			array(
				'type'		  => 'checkbox',
				'id'          => 'comment_status',
				'label'       => '&nbsp;',
				'description' => __( 'Allow Reviews', 'woopanel' ),
				'default'	  => 'open'
			),
			$post->comment_status
		);
	}

	public function no_item_icon() {
		echo '<i class="flaticon-box"></i>';
	}

	public function product_state($return, $post) {
		if( $post->post_status != 'publish') {
			return '  — <span class="post-state">'. $this->post_statuses[$post->post_status] .'</span>';
		}
	}

	public function shortcode_custom($html, $post, $product) {
		echo '<span class="shortcode"><input type="text" onfocus="this.select();" name="shortcode_faq" value="[wpl_faq id=&quot;'. $post->ID .'&quot;]"></span>';
	}


	/**
	 * Change title boxes in admin.
	 *
	 * @param string  $text Text to shown.
	 * @param WP_Post $post Current post object.
	 * @return string
	 */
	public function enter_title_here( $text ) {
		return esc_html__( 'Product name', 'woocommerce' );
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

	public function form_fields($post_id) {

		$data = get_post_meta($post_id, '_nbt_faq', true);
		
		include_once WOOPANEL_VIEWS_DIR . 'faqs/admin-repeater.php'; 
	}


	public function save_post($post_id, $data) {
	    /*
	     * In production code, $slug should be set only once in the plugin,
	     * preferably as a class property, rather than in each function that needs it.
	     */
	    $post_type = get_post_type($post_id);

	    // If this isn't a 'book' post, don't update it.
	    if ( "wpl-faq" == $post_type ) {
			if(isset($_POST['faq_heading']) && !empty($_POST['faq_heading'])){
				$new = array();
				foreach ($_POST['faq_heading'] as $k => $h):
					$e = array();
					if( isset($_POST['faq_title'][$k]) ) {
						foreach ($_POST['faq_title'][$k] as $ke => $ve):
							$e[$ke] = array(
								'faq_title' => $ve,
								'faq_content' => $_POST['faq_content'][$k][$ke]
							);
						endforeach;
					}



					$new[$k] = array(
						'heading' => $h,
						'lists' => $e
					);
				endforeach;
				update_post_meta( $post_id, '_nbt_faq', $new );
			}
		}
	}
}

include_once WOOPANEL_INC_DIR . "pages/woopanel-faq-admin.php";
include_once WOOPANEL_INC_DIR . "pages/woopanel-faq-frontend.php";