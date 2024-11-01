<?php
class WooPanel_Template_Product {
	private $post_statuses = array();
	private $classes;
	public $taxonomy = 'product_cat';
	public $tags = 'product_tag';
	public $panels = array();

	public function __construct() {
		$this->post_statuses = get_post_statuses();

		$this->classes = new WooPanel_Post_List_Table(array(
			'post_type'     	=> 'product',
			'taxonomy'			=> $this->taxonomy,
			'editor'			=> true,
			'thumbnail'			=> true,
			'preview'			=> true,
			'tags'				=> $this->tags,
			'gallery'		=> true,
			'screen'        	=> 'posts',
			'columns'       	=> array(
				'thumbnail'     => '<span class="fa fa-image" data-toggle="tooltip" data-placement="top" data-original-title="'. __('Thumbnail', 'woocommerce') .'"><span class="screen-reader-text">'. __('Thumbnail', 'woocommerce') .'</span></span>',
				'title'     	=> __( 'Title', 'woocommerce' ),
				'sku'      		=> __( 'SKU', 'woocommerce' ),
				'stock'      	=> __( 'Stock', 'woocommerce' ),
				'price'      	=> __( 'Price', 'woocommerce' ),
				'type'      	=> '<span class="flaticon-box" data-toggle="tooltip" data-placement="top" data-original-title="'. __('Type', 'woocommerce') .'"><span class="screen-reader-text">'. __('Type', 'woocommerce') .'</span></span>',
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

		$GLOBALS['product_object']    = isset($_GET['id']) ? wc_get_product( $_GET['id'] ) : new WC_Product();
		$product_image_gallery = $GLOBALS['product_object']->get_gallery_image_ids( 'edit' );

		$this->classes->form(
			array(
				'product_image_gallery' => $product_image_gallery
			)
		);
	}

	public function hooks_table() {
		// Custom column data
		add_filter( 'woopanel_product_thumbnail_column', array($this, 'thumbnail_custom'), 99, 3);
		add_filter( 'woopanel_product_sku_column', array($this, 'sku_custom'), 99, 3);
		add_filter( 'woopanel_product_stock_column', array($this, 'stock_custom'), 99, 3);
		add_filter( 'woopanel_product_price_column', array($this, 'price_custom'), 99, 3);
		add_filter( 'woopanel_product_type_column', array($this, 'type_custom'), 99, 3);
		add_filter( 'woopanel_product_categories_column', array($this, 'categories_custom'), 99, 3);
		add_filter( 'woopanel_product_tags_column', array($this, 'tags_custom'), 99, 3);
		add_filter( 'woopanel_product_comments_column', array($this, 'comments_custom'), 99, 3);

        add_action( 'woopanel_product_no_item_icon', array($this, 'no_item_icon'));

		add_action( 'woopanel_product_filter_display', array($this, 'filter_display'), 99, 2 );
		add_filter( 'posts_distinct', array($this, 'search_distinct'), 99, 1 );
		add_filter( 'woopanel_product_state', array($this, 'product_state'), 99, 2);
	}

	public function hooks_form() {
		add_filter('woopanel_product_enter_title_here', array($this, 'enter_title_here' ), 999, 1 );
		add_action('woopanel_product_form_fields', array($this, 'product_data_form_fields'), 99, 1 );
		add_action('woopanel_product_form_fields', array($this, 'product_faq_form_fields'), 99, 1 );
		add_action( 'woopanel_product_save_post', array( $this, 'save_post'), 99, 2 );
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

	public function thumbnail_custom($html, $post, $product) {
		echo '<a href="' . esc_url(woopanel_post_edit_url($post->ID)) . '">' . $product->get_image( 'thumbnail' ) .'</a>';
	}

	public function sku_custom($html, $post, $product) {
		echo ( get_post_meta($post->ID, '_sku', true) ) ? get_post_meta($post->ID, '_sku', true) : '-';
	}

	public function stock_custom($html, $post, $product) {
		$stock_html = '';
		$stock_status = $product->get_stock_status();
		$stock_options = array('instock' => __('In stock', 'wc-frontend-manager'), 'outofstock' => __('Out of stock', 'wc-frontend-manager'), 'onbackorder' => __( 'On backorder', 'wc-frontend-manager' ) );
		if ( array_key_exists( $stock_status, $stock_options ) ) {
			$stock_html .= '<mark class="m-badge m-badge--brand m-badge--wide '.$stock_status.'" data-toggle="tooltip" data-original-title="'. $stock_options[$stock_status] .'"><span>' . $stock_options[$stock_status] . '</span></mark>';
		} else {
			$stock_html .= '<mark class="m-badge m-badge--brand m-badge--wide instock" data-toggle="tooltip" data-original-title="' . __( 'In stock', 'woocommerce' ) . '"><span>' . __( 'In stock', 'woocommerce' ) . '</span>';
		}

		// If the product has children, a single stock level would be misleading as some could be -ve and some +ve, some managed/some unmanaged etc so hide stock level in this case.
		if ( $product->managing_stock() && ! sizeof( $product->get_children() ) ) {
			$stock_html .= ' (' . $product->get_stock_quantity() . ')';
		}

		echo $stock_html;
	}

	public function price_custom($html, $post, $product) {
		echo $product->get_price_html() ? $product->get_price_html() : '<span class="na">&ndash;</span>';;
	}

	public function type_custom($html, $post, $product) {
		$pro_type = '';
		if ( 'grouped' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips grouped wcicon-grouped" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Grouped', 'wc-frontend-manager' ) . '"></span>';
		} if ( 'groupby' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips grouped wcicon-grouped" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Group By', 'wc-frontend-manager-product-hub' ) . '"></span>';
		} elseif ( 'external' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips external wcicon-external" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'External/Affiliate', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'simple' == $product->get_type() ) {

			if ( $product->is_virtual() ) {
				$pro_type = '<span class="product-type tips virtual wcicon-virtual" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Virtual', 'wc-frontend-manager' ) . '"></span>';
			} elseif ( $product->is_downloadable() ) {
				$pro_type = '<span class="product-type tips downloadable wcicon-downloadable" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Downloadable', 'wc-frontend-manager' ) . '"></span>';
			} else {
				$pro_type = '<span class="product-type tips simple wcicon-simple" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Simple', 'wc-frontend-manager' ) . '"></span>';
			}

		} elseif ( 'variable' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips variable wcicon-variable" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Variable', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'subscription' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips wcicon-variable" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Subscription', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'variable-subscription' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips wcicon-variable" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Variable Subscription', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'job_package' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-briefcase" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Listings Package', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'resume_package' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-suitcase" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Resume Package', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'auction' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-gavel" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Auction', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'redq_rental' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-cab" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Rental', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'accommodation-booking' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-calendar" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Accommodation', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'appointment' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-clock-o" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Appointment', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'bundle' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-cubes" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Bundle', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'composite' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-cubes" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Composite', 'wc-frontend-manager' ) . '"></span>';
		} elseif ( 'lottery' == $product->get_type() ) {
			$pro_type = '<span class="product-type tips fa fa-dribbble" data-toggle="tooltip" data-placement="top" data-original-title="' . esc_attr__( 'Lottery', 'wc-frontend-manager' ) . '"></span>';
		} else {
			// Assuming that we have other types in future
			$pro_type = '<span class="product-type tips wcicon-' . $product->get_type() . ' text_tip ' . $product->get_type() . '" data-tip="' . ucfirst( $product->get_type() ) . '"></span>';
		}

		echo $pro_type;
	}
	

	public function categories_custom($html, $post, $product) {
		echo woopanel_column_taxonomy($post, $this->taxonomy, 'cat');
	}

	public function tags_custom($html, $post, $product) {
		echo woopanel_column_taxonomy($post, 'product_tag', 'product_tag');
	}

	public function comments_custom($html, $post, $product) {
		echo woopanel_column_comments($post, 'reviews');
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

	public function product_data_form_fields($post_id) {
		global $product_object;

		include_once WOOPANEL_VIEWS_DIR . 'metaboxes/product/panel.php'; 
	}

	public function product_faq_form_fields($post_id) {

		$data = get_post_meta($post_id, '_nbt_faq', true);
		
		include_once WOOPANEL_VIEWS_DIR . 'faqs/admin-repeater.php'; 
	}

	public function woopanel_save_shop_coupon_post_meta($post_id, $data) {
		update_post_meta($post_id, 'discount_type', $data['discount_type']);
		update_post_meta($post_id, 'coupon_amount', $data['coupon_amount']);
		update_post_meta($post_id, 'free_shipping', $data['free_shipping']);
		update_post_meta($post_id, 'expiry_date', $data['expiry_date']);
	}

	/**
	 * Return array of tabs to show.
	 *
	 * @return array
	 */
	private static function get_product_data_tabs() {
		$tabs = apply_filters(
			'woocommerce_product_data_tabs', array(
				'general'        => array(
					'label'    => __( 'General', 'woocommerce' ),
					'target'   => 'general_product_data',
					'class'    => array( 'hide_if_grouped' ),
					'priority' => 10,
				),
				'inventory'      => array(
					'label'    => __( 'Inventory', 'woocommerce' ),
					'target'   => 'inventory_product_data',
					'class'    => array( 'show_if_simple', 'show_if_variable', 'show_if_grouped', 'show_if_external' ),
					'priority' => 20,
				),
				'shipping'       => array(
					'label'    => __( 'Shipping', 'woocommerce' ),
					'target'   => 'shipping_product_data',
					'class'    => array( 'hide_if_virtual', 'hide_if_grouped', 'hide_if_external' ),
					'priority' => 30,
				),
				'linked_product' => array(
					'label'    => __( 'Linked Products', 'woocommerce' ),
					'target'   => 'linked_product_data',
					'class'    => array(),
					'priority' => 40,
				),
				'attribute'      => array(
					'label'    => __( 'Attributes', 'woocommerce' ),
					'target'   => 'product_attributes',
					'class'    => array(),
					'priority' => 50,
				),
				'variations'     => array(
					'label'    => __( 'Variations', 'woocommerce' ),
					'target'   => 'variable_product_options',
					'class'    => array( 'variations_tab', 'show_if_variable' ),
					'priority' => 60,
				)
			)
		);

		// Sort tabs based on priority.
		uasort( $tabs, array( __CLASS__, 'product_data_tabs_sort' ) );

		return $tabs;
	}

	/**
	 * Return array of product type options.
	 *
	 * @return array
	 */
	private static function get_product_type_options() {
		return apply_filters(
			'product_type_options',
			array(
				'virtual'      => array(
					'id'            => '_virtual',
					'wrapper_class' => 'show_if_simple',
					'label'         => __( 'Virtual', 'woocommerce' ),
					'description'   => __( 'Virtual products are intangible and are not shipped.', 'woocommerce' ),
					'default'       => 'no',
				)
			)
		);
	}

	/**
	 * Callback to sort product data tabs on priority.
	 *
	 * @since 3.1.0
	 * @param int $a First item.
	 * @param int $b Second item.
	 *
	 * @return bool
	 */
	private static function product_data_tabs_sort( $a, $b ) {
		if ( ! isset( $a['priority'], $b['priority'] ) ) {
			return -1;
		}

		if ( $a['priority'] == $b['priority'] ) {
			return 0;
		}

		return $a['priority'] < $b['priority'] ? -1 : 1;
	}

	/**
	 * Filter callback for finding variation attributes.
	 *
	 * @param  WC_Product_Attribute $attribute
	 * @return bool
	 */
	private static function filter_variation_attributes( $attribute ) {
		return true === $attribute->get_variation();
	}

	/**
	 * Show options for the variable product type.
	 */
	public static function output_variations() {
		global $post, $wpdb, $product_object;

		$variation_attributes   = array_filter( $product_object->get_attributes(), array( __CLASS__, 'filter_variation_attributes' ) );
		$default_attributes     = $product_object->get_default_attributes();
		$variations_count       = absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_count', $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'product_variation' AND post_status IN ('publish', 'private')", $product_object->get_id() ) ), $product_object->get_id() ) );
		$variations_per_page    = absint( apply_filters( 'woocommerce_admin_meta_boxes_variations_per_page', 15 ) );
		$variations_total_pages = ceil( $variations_count / $variations_per_page );

		include WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-data-variations.php';
	}


	/**
	 * Show tab content/settings.
	 */
	private static function output_tabs() {
		global $post, $thepostid, $product_object;

		include_once WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-data-general.php';
		include_once WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-data-inventory.php';
  		include_once WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-data-shipping.php';
		include_once WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-data-linked-products.php';
		include_once WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-data-attributes.php';
/*		include_once WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-data-advanced.php'; */
	}

	public function save_post($post_id, $data) {

		if( empty($post_id) ) {
			return;
		}

		$this->save_metabox_faq($post_id, $data);

		

		update_post_meta($post_id, '_product_image_gallery', $data['_image_gallery'] );
		
		$product = wc_get_product($post_id);
		$attributes   = WC_Meta_Box_Product_Data::prepare_attributes( $data );
		$stock        = null;

		// Handle stock changes.
		if ( isset( $_POST['_stock'] ) && ! empty($_POST['_stock']) ) {
			if ( isset( $_POST['_original_stock'] ) && wc_stock_amount( $product->get_stock_quantity( 'edit' ) ) !== wc_stock_amount( $_POST['_original_stock'] ) ) {
				/* translators: 1: product ID 2: quantity in stock */
				WC_Admin_Meta_Boxes::add_error( sprintf( __( 'The stock has not been updated because the value has changed since editing. Product %1$d has %2$d units in stock.', 'woocommerce' ), $product->get_id(), $product->get_stock_quantity( 'edit' ) ) );
			} else {
				$stock = wc_stock_amount( wp_unslash( $_POST['_stock'] ) );
			}
		}

		
		


		$errors = $product->set_props(
			array(
				'sku'                => isset( $_POST['_sku'] ) ? wc_clean( wp_unslash( $_POST['_sku'] ) ) : null,
				'purchase_note'      => isset( $_POST['_purchase_note'] ) ? wp_kses_post( wp_unslash( $_POST['_purchase_note'] ) ) : null,
				'downloadable'       => isset( $_POST['_downloadable'] ),
				'virtual'            => isset( $_POST['_virtual'] ),
				'featured'           => isset( $_POST['_featured'] ),
				'catalog_visibility' => isset( $_POST['_visibility'] ) ? wc_clean( wp_unslash( $_POST['_visibility'] ) ) : null,
				'tax_status'         => isset( $_POST['_tax_status'] ) ? wc_clean( wp_unslash( $_POST['_tax_status'] ) ) : null,
				'tax_class'          => isset( $_POST['_tax_class'] ) ? wc_clean( wp_unslash( $_POST['_tax_class'] ) ) : null,
				'weight'             => wc_clean( wp_unslash( $_POST['_weight'] ) ),
				'length'             => wc_clean( wp_unslash( $_POST['_length'] ) ),
				'width'              => wc_clean( wp_unslash( $_POST['_width'] ) ),
				'height'             => wc_clean( wp_unslash( $_POST['_height'] ) ),
				'shipping_class_id'  => absint( wp_unslash( $_POST['product_shipping_class'] ) ),
				'sold_individually'  => ! empty( $_POST['_sold_individually'] ),
				'upsell_ids'         => isset( $_POST['upsell_ids'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['upsell_ids'] ) ) : array(),
				'cross_sell_ids'     => isset( $_POST['crosssell_ids'] ) ? array_map( 'intval', (array) wp_unslash( $_POST['crosssell_ids'] ) ) : array(),
				'regular_price'      => wc_clean( wp_unslash( $_POST['_regular_price'] ) ),
				'sale_price'         => wc_clean( wp_unslash( $_POST['_sale_price'] ) ),
				'date_on_sale_from'  => wc_clean( wp_unslash( $_POST['_sale_price_dates_from'] ) ),
				'date_on_sale_to'    => wc_clean( wp_unslash( $_POST['_sale_price_dates_to'] ) ),
				'manage_stock'       => ! empty( $_POST['_manage_stock'] ),
				'backorders'         => isset( $_POST['_backorders'] ) ? wc_clean( wp_unslash( $_POST['_backorders'] ) ) : null,
				'stock_quantity'     => $stock,
				'low_stock_amount'   => wc_stock_amount( wp_unslash( $_POST['_low_stock_amount'] ) ),
				'download_limit'     => '' === $_POST['_download_limit'] ? '' : absint( wp_unslash( $_POST['_download_limit'] ) ),
				'download_expiry'    => '' === $_POST['_download_expiry'] ? '' : absint( wp_unslash( $_POST['_download_expiry'] ) ),
				'product_url'         => esc_url_raw( wp_unslash( $_POST['_product_url'] ) ),
				'button_text'         => wc_clean( wp_unslash( $_POST['_button_text'] ) ),
				'children'            => 'grouped' === $product->get_type() ? $this->grouped_products() : null,
				'reviews_allowed'     => ! empty( $_POST['comment_status'] ) && 'open' === $_POST['comment_status']
			)
		);

		update_post_meta( $post_id, '_stock_status', isset($_POST['_stock_status']) ? wc_clean( wp_unslash( $_POST['_stock_status'] ) ) : 'instock' );

		if ( is_wp_error( $errors ) ) {
			WC_Admin_Meta_Boxes::add_error( $errors->get_error_message() );
		}

		

		/**
		 * @since 3.0.0 to set props before save.
		 */
		do_action( 'woopanel_admin_process_product_object', $product );

		$product->save();

		if ( $product->is_type( 'variable' ) ) {
			$original_post_title = $_POST['post_title'];
			if( isset($_POST['original_post_title']) ) {
				$original_post_title = $_POST['original_post_title'];
			}
			$product->get_data_store()->sync_variation_names( $product, wc_clean( $original_post_title ), wc_clean( $_POST['post_title'] ) );
		}

		do_action( 'woopanel_process_product_meta_' . $data['product_type'], $post_id );

		wp_set_object_terms( $post_id, $data['product_type'], 'product_type' );
		
	}

	public function save_metabox_faq($post_id, $data) {
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
	public function grouped_products() {
		return isset( $_POST['grouped_products'] ) ? array_filter( array_map( 'intval', (array) $_POST['grouped_products'] ) ) : array();
	}
}