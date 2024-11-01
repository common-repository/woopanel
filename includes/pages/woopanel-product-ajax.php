<?php
class WooPanel_Product_Ajax {

	public function __construct() {
		add_action( 'wp_ajax_woopanel_load_variations', array($this, 'load_variations') );
		add_action( 'wp_ajax_woopanel_add_variation', array($this, 'add_variation') );
		add_action( 'wp_ajax_woopanel_save_variations', array($this, 'save_variations') );
		add_action( 'wp_ajax_woopanel_remove_variations', array($this, 'remove_variations') );
		
		add_action( 'wp_ajax_woopanel_bulk_edit_variations', array($this, 'bulk_edit_variations') );
		add_action( 'wp_ajax_woopanel_link_all_variations', array($this, 'link_all_variations') );
		
		add_action( 'wp_ajax_woopanel_add_attribute', array($this, 'add_attribute') );
		add_action( 'wp_ajax_woopanel_save_attributes', array($this, 'save_attributes') );
	}
	public static function load_variations() {
        redirect_no_permission_ajax();
		$json = array();
 		check_ajax_referer( 'load-variations', 'security' );

		if ( ! current_user_can( 'edit_products' ) || empty( $_POST['product_id'] ) ) {
			wp_die( -1 );
		}

		// Set $post global so its available, like within the admin screens
		global $post;

		$loop           = 0;
		$product_id     = absint( $_POST['product_id'] );
		$post           = get_post( $product_id );
		$product_object = wc_get_product( $product_id );
		$per_page       = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
		$page           = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$variations     = wc_get_products(
			array(
				'status'  => array( 'private', 'publish' ),
				'type'    => 'variation',
				'parent'  => $product_id,
				'limit'   => $per_page,
				'page'    => $page,
				'orderby' => array(
					'menu_order' => 'ASC',
					'ID'         => 'DESC',
				),
				'return'  => 'objects',
			)
		);

		if ( $variations ) {
			foreach ( $variations as $variation_object ) {
				$variation_id   = $variation_object->get_id();
				$variation      = get_post( $variation_id );
				$variation_data = array_merge( get_post_custom( $variation_id ), wc_get_product_variation_attributes( $variation_id ) ); // kept for BW compatibility.
				include WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-variation-admin.php';
				
				$loop++;
			}
		}
		
		wp_die();
	}

	/**
	 * Add variation via ajax function.
	 */
	public static function add_variation() {
        redirect_no_permission_ajax();
		check_ajax_referer( 'add-variation', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}

		global $post; // Set $post global so its available, like within the admin screens.

		$product_id       = intval( $_POST['post_id'] );
		$post             = get_post( $product_id );
		$loop             = intval( $_POST['loop'] );
		$product_object   = wc_get_product( $product_id );
		$variation_object = new WC_Product_Variation();
		$variation_object->set_parent_id( $product_id );
		$variation_object->set_attributes( array_fill_keys( array_map( 'sanitize_title', array_keys( $product_object->get_variation_attributes() ) ), '' ) );
		$variation_id   = $variation_object->save();
		$variation      = get_post( $variation_id );
		$variation_data = array_merge( get_post_custom( $variation_id ), wc_get_product_variation_attributes( $variation_id ) ); // kept for BW compatibility.
		include WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-variation-admin.php';
		wp_die();
	}

	/**
	 * Save variations via AJAX.
	 */
	public static function save_variations() {
        redirect_no_permission_ajax();
		ob_start();

		check_ajax_referer( 'save-variations', 'security' );

		// Check permissions again and make sure we have what we need
		if ( ! current_user_can( 'edit_products' ) || empty( $_POST ) || empty( $_POST['product_id'] ) ) {
			wp_die( -1 );
		}

		$product_id                           = absint( $_POST['product_id'] );
		WC_Admin_Meta_Boxes::$meta_box_errors = array();
		WC_Meta_Box_Product_Data::save_variations( $product_id, get_post( $product_id ) );

		do_action( 'woocommerce_ajax_save_product_variations', $product_id );

		if ( $errors = WC_Admin_Meta_Boxes::$meta_box_errors ) {
			echo '<div class="error notice is-dismissible">';

			foreach ( $errors as $error ) {
				echo '<p>' . wp_kses_post( $error ) . '</p>';
			}

			echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'woocommerce' ) . '</span></button>';
			echo '</div>';

			delete_option( 'woocommerce_meta_box_errors' );
		}

		wp_die();
	}

	/**
	 * Delete variations via ajax function.
	 */
	public static function remove_variations() {
        redirect_no_permission_ajax();
		$json = array();
		check_ajax_referer( 'delete-variations', 'security' );

		if ( current_user_can( 'edit_products' ) ) {
			$variation_ids = (array) $_POST['variation_ids'];

			foreach ( $variation_ids as $variation_id ) {
				if ( 'product_variation' === get_post_type( $variation_id ) ) {
					$variation = wc_get_product( $variation_id );
					$variation->delete( true );
				}
			}

			$json['complete'] = true;
		}

		wp_send_json( $json );
	}

	/**
	 * Link all variations via ajax function.
	 */
	public static function link_all_variations() {
        redirect_no_permission_ajax();
		check_ajax_referer( 'link-variations', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}

		wc_maybe_define_constant( 'WC_MAX_LINKED_VARIATIONS', 49 );
		wc_set_time_limit( 0 );

		$post_id = intval( $_POST['post_id'] );

		if ( ! $post_id ) {
			wp_die();
		}

		$product    = wc_get_product( $post_id );
		$attributes = wc_list_pluck( array_filter( $product->get_attributes(), 'wc_attributes_array_filter_variation' ), 'get_slugs' );

		if ( ! empty( $attributes ) ) {
			// Get existing variations so we don't create duplicates.
			$existing_variations = array_map( 'wc_get_product', $product->get_children() );
			$existing_attributes = array();

			foreach ( $existing_variations as $existing_variation ) {
				$existing_attributes[] = $existing_variation->get_attributes();
			}

			$added               = 0;
			$possible_attributes = array_reverse( wc_array_cartesian( $attributes ) );

			foreach ( $possible_attributes as $possible_attribute ) {
				if ( in_array( $possible_attribute, $existing_attributes ) ) {
					continue;
				}
				$variation = new WC_Product_Variation();
				$variation->set_parent_id( $post_id );
				$variation->set_attributes( $possible_attribute );

				do_action( 'product_variation_linked', $variation->save() );

				if ( ( $added ++ ) > WC_MAX_LINKED_VARIATIONS ) {
					break;
				}
			}

			echo $added;
		}

		$data_store = $product->get_data_store();
		$data_store->sort_all_product_variations( $product->get_id() );
		wp_die();
	}
	
	public static function bulk_edit_variations() {
        redirect_no_permission_ajax();
		ob_start();

		check_ajax_referer( 'bulk-edit-variations', 'security' );

		// Check permissions again and make sure we have what we need
		if ( ! current_user_can( 'edit_products' ) || empty( $_POST['product_id'] ) || empty( $_POST['bulk_action'] ) ) {
			wp_die( -1 );
		}

		$product_id  = absint( $_POST['product_id'] );
		$bulk_action = wc_clean( $_POST['bulk_action'] );
		$data        = ! empty( $_POST['data'] ) ? array_map( 'wc_clean', $_POST['data'] ) : array();
		$variations  = array();

		if ( apply_filters( 'woocommerce_bulk_edit_variations_need_children', true ) ) {
			$variations = get_posts(
				array(
					'post_parent'    => $product_id,
					'posts_per_page' => -1,
					'post_type'      => 'product_variation',
					'fields'         => 'ids',
					'post_status'    => array( 'publish', 'private' ),
				)
			);
		}

		

		if ( method_exists( __CLASS__, "variation_bulk_action_$bulk_action" ) ) {
			call_user_func( array( __CLASS__, "variation_bulk_action_$bulk_action" ), $variations, $data );
		} else {
			do_action( 'woopanel_bulk_edit_variations_default', $bulk_action, $data, $product_id, $variations );
		}

		do_action( 'woopanel_bulk_edit_variations', $bulk_action, $data, $product_id, $variations );
		WC_Product_Variable::sync( $product_id );
		wc_delete_product_transients( $product_id );
		wp_die();
	}
	
	/**
	 * Add an attribute row.
	 */
	public static function add_attribute() {
        redirect_no_permission_ajax();
		ob_start();

		check_ajax_referer( 'add-attribute', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}

		$i             = absint( $_POST['i'] );
		$metabox_class = array();
		$attribute     = new WC_Product_Attribute();
		$type = sanitize_text_field($_POST['type']);

		$attribute->set_id( wc_attribute_taxonomy_id_by_name( sanitize_text_field( $_POST['taxonomy'] ) ) );
		$attribute->set_name( sanitize_text_field( $_POST['taxonomy'] ) );
		$attribute->set_visible( apply_filters( 'woocommerce_attribute_default_visibility', 1 ) );
		$attribute->set_variation( apply_filters( 'woocommerce_attribute_default_is_variation', 0 ) );

		if ( $attribute->is_taxonomy() ) {
			$metabox_class[] = 'taxonomy';
			$metabox_class[] = $attribute->get_name();
		}

		include WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-attribute.php';
		wp_die();
	}

	/**
	 * Save attributes via ajax.
	 */
	public static function save_attributes() {
        redirect_no_permission_ajax();
		check_ajax_referer( 'save-attributes', 'security' );

		if ( ! current_user_can( 'edit_products' ) ) {
			wp_die( -1 );
		}

		if( empty($_POST['data']) ) {
			wp_send_json(
				array(
					'error' => __('Not found for attribute, please add attribute!', 'woopanel')
				)
			);
		}

		try {
			parse_str( $_POST['data'], $data );

			if( ! isset($_POST['post_id']) ) {

				if( empty($data['post_title']) ) {
					$data['post_title'] = 'AUTO-DRAFT';
				}
		
 				$post_id = woopanel_write_post(array(
					'taxonomy'  => 'product_cat',
					'tags'		=> 'product_tag',
					'data'		=> $data
				));

				$product_type = $data['product_type'];
			}else {
				$post_id = $_POST['post_id'];
				$product_type = $_POST['product_type'];
			}

			if ( is_wp_error( $post_id ) ) {
				wp_send_json_error( array( 'error' => $post_id->get_error_message() ) );
			}else {
				$attributes   = WC_Meta_Box_Product_Data::prepare_attributes( $data );
				$product_id   = absint( $post_id );
				$product_type = ! empty( $_POST['product_type'] ) ? wc_clean( $_POST['product_type'] ) : 'simple';
				$classname    = WC_Product_Factory::get_product_classname( $product_id, $product_type );
				$product      = new $classname( $product_id );
	
				$product->set_attributes( $attributes );
				$product->save();
	
				$response = array();
	
				ob_start();
				$attributes = $product->get_attributes( 'edit' );
				$i          = -1;
	
				foreach ( $data['attribute_names'] as $attribute_name ) {
					$attribute = isset( $attributes[ sanitize_title( $attribute_name ) ] ) ? $attributes[ sanitize_title( $attribute_name ) ] : false;
					if ( ! $attribute ) {
						continue;
					}
					$i++;
					$metabox_class = array();
	
					if ( $attribute->is_taxonomy() ) {
						$metabox_class[] = 'taxonomy';
						$metabox_class[] = $attribute->get_name();
					}
	
					include WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-attribute.php';
				}
	
				$response['html'] = ob_get_clean();
				$response['post_id'] = $post_id;

				wp_send_json_success( $response );
			}

			
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'error' => $e->getMessage() ) );
		}
		wp_die();
	}

	/**
	 * Bulk action - Delete all.
	 *
	 * @access private
	 * @used-by bulk_edit_variations
	 * @param  array $variations
	 * @param  array $data
	 */
	private static function variation_bulk_action_delete_all( $variations, $data ) {
        redirect_no_permission_ajax();
		if ( isset( $data['allowed'] ) && 'true' === $data['allowed'] ) {
			foreach ( $variations as $variation_id ) {
				$variation = wc_get_product( $variation_id );
				$variation->delete( true );
			}
		}
	}
}

new WooPanel_Product_Ajax();