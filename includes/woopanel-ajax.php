<?php
if( ! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
	include_once WOOPANEL_INC_DIR . "pages/woopanel-product-ajax.php";
	include_once WOOPANEL_INC_DIR . "pages/woopanel-comment-ajax.php";

	class WooPanel_Ajax {
		public function __construct() {
			add_action('wp_ajax_get_featured', array($this, 'woopanel_featured_image_ajax') );
			add_action('wp_ajax_get_image', array($this, 'woopanel_image_ajax') );
			add_action('wp_ajax_get_gallery', array($this, 'woopanel_gallery_images_ajax') );
			add_action( 'wp_ajax_woopanel_add_category', array($this, 'woopanel_add_category') );
			add_action( 'wp_ajax_woopanel_tagcloud', array($this, 'woopanel_tagcloud') );
			add_action( 'wp_ajax_woopanel_dashboard_save_order',  array($this, 'woopanel_dashboard_save_order') );
			add_action( 'wp_ajax_woopanel_edit_item_order',  array($this, 'woopanel_edit_item_order') );
			add_action( 'wp_ajax_woopanel_edit_item_billing',  array($this, 'woopanel_edit_item_billing') );
			add_action( 'wp_ajax_woopanel_edit_item_shipping',  array($this, 'woopanel_edit_item_shipping') );
			add_action( 'wp_ajax_woopanel_delete_category',  array($this, 'woopanel_delete_category') );
			
		}

		public function woopanel_featured_image_ajax(){
			redirect_no_permission_ajax();
			woopanel_attachment_image( null, false );
			wp_die();
		}

		public function woopanel_image_ajax() {
			redirect_no_permission_ajax();
			woopanel_attachment_image( null, false, false );
			die;
		}

		public function woopanel_gallery_images_ajax() {
			redirect_no_permission_ajax();
			woopanel_gallery_images( null, false );
			die;
		}

		public function woopanel_edit_item_order() {
			check_ajax_referer( 'edit_items_order', 'security' );
			$json = array();
			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				wp_die( -1 );
			}

			$data = $_POST['data'];
			$order_id = absint($_POST['order_id']);

			$order = wc_get_order($order_id);


			
			foreach ($order->get_items() as $item_id => $item_obj) {
				if( in_array($item_id, $data['order_item_id']) ) {
					$data['line_total'][$item_id] = ($data['line_subtotal'][$item_id] * $data['order_item_qty'][$item_id]);
				}
			}

			wc_save_order_items( $order_id, $data );

			$json['complete'] = true;

			wp_send_json($json);
		}

		public function woopanel_edit_item_billing() {
			check_ajax_referer( 'edit_items_billing', 'security' );

			$json = array();
			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				wp_die( -1 );
			}

			$data = $_POST['data'];
			$order_id = absint($_POST['order_id']);

			$order = wc_get_order($order_id);

			if( $order ) {
				$order->set_billing_first_name($data['_billing_first_name']);
				$order->set_billing_last_name($data['_billing_last_name']);
				$order->set_billing_company($data['_billing_company']);
				$order->set_billing_address_1($data['_billing_address_1']);
				$order->set_billing_address_2($data['_billing_address_2']);

				$order->set_billing_city($data['_billing_city']);
				$order->set_billing_country($data['_billing_country']);
				$order->set_billing_state($data['_billing_state']);

				$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
				$order->set_payment_method( isset( $available_gateways[ $data['_payment_method'] ] ) ? $available_gateways[ $data['_payment_method'] ] : $data['_payment_method'] );
				$order->set_transaction_id( $data['_transaction_id'] );

				$order->save();
				
				$json['complete'] = true;
			}

			wp_send_json($json);
		}

		public function woopanel_edit_item_shipping() {
			check_ajax_referer( 'edit_items_shipping', 'security' );

			$json = array();
			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				wp_die( -1 );
			}

			$data = $_POST['data'];
			$order_id = absint($_POST['order_id']);

			$order = wc_get_order($order_id);

			if( $order ) {
				$order->set_shipping_first_name($data['_shipping_first_name']);
				$order->set_shipping_last_name($data['_shipping_last_name']);
				$order->set_shipping_company($data['_shipping_company']);
				$order->set_shipping_address_1($data['_shipping_address_1']);
				$order->set_shipping_address_2($data['_shipping_address_2']);

				$order->set_shipping_city($data['_shipping_city']);
				$order->set_shipping_country($data['_shipping_country']);
				$order->set_shipping_state($data['_shipping_state']);

				$order->save();
				
				$json['complete'] = true;
			}

			wp_send_json($json);
		}

		public function woopanel_delete_category() {
			check_ajax_referer( 'tax_verify_security', 'security' );

			$json = array();

			$terms = $_REQUEST['term_id'];

			if( ! empty($terms) && is_array($terms) && $_REQUEST['taxonomy'] ) {
				foreach( $terms as $term_id ) {
					wp_delete_term( $term_id, $_REQUEST['taxonomy'] );
				}

				$json['complete'] = true;
			}


			wp_send_json($json);
		}

		public function woopanel_add_category() {
			check_ajax_referer( 'category-add-tax', 'security' );

			if( ! is_shop_staff(false, false) ) {
				wp_send_json_error(array(
					'message' => __('You do not have permission for this action!', 'woopanel')
				));
			}

			$json = array();
	
			$cat_name = trim($_REQUEST['name']);
			$parent = intval($_REQUEST['parent']);
			$taxonomy = $_REQUEST['taxonomy'];
	
			$tax = get_taxonomy( $taxonomy );
	
			if ( ! current_user_can( $tax->cap->manage_terms ) ) {
				wp_send_json_error(array(
					'message' => __('Sorry, you are not allowed to access this action.', 'woopanel')
				));
			}
	
			if( empty($_REQUEST['name']) ) {
				wp_send_json_error( array(
					'message' => __('Please enter a category name.', 'woopanel')
				));
			}
	
			$slug = sanitize_title($cat_name);
	
			$cat_id = wp_insert_term( $cat_name, $taxonomy, array(
				'parent' => $parent
			) );
			if ( ! $cat_id || is_wp_error( $cat_id ) ) {
				//$result->get_error_message();
				wp_send_json_error( array(
					'message' => $cat_id->get_error_message()
				));
			} else {
				$cat_id = $cat_id['term_id'];
			}
	
			$cat_name = esc_html( $cat_name );
	
			$json['complete'] = true;
			$json['html'] = "<li id='link-category-$cat_id' class='term-item term-item-$cat_id term-item-has-children'><label for='in-link-category-$cat_id' class='m-checkbox m-checkbox--solid m-checkbox--brand'><input value='" . esc_attr($cat_id) . "' type='checkbox' checked='checked' name='link_category[]' id='in-link-category-$cat_id'/> $cat_name<span></span></label></li>";
			$json['element'] = $taxonomy . '-checklist';
	
			wp_send_json($json);
		}

	
		public function woopanel_tagcloud() {
			check_ajax_referer( 'most-used-tags', 'security' );

			if( ! is_shop_staff(false, false) ) {
				wp_send_json_error(array(
					'message' => __('You do not have permission for this action!', 'woopanel')
				));
			}
			
			$json = array();
	
	
			if ( ! isset( $_POST['tax'] ) ) {
				wp_die( 0 );
			}
	
			$taxonomy = sanitize_key( $_POST['tax'] );
			$tax = get_taxonomy( $taxonomy );
			if ( ! $tax ) {
				wp_die( 0 );
			}
	
			if ( ! current_user_can( $tax->cap->assign_terms ) ) {
				wp_die( -1 );
			}
	
			$tags = get_terms( $taxonomy, array( 'number' => 45, 'orderby' => 'count', 'order' => 'DESC' ) );
	
			if ( empty( $tags ) )
				wp_die( $tax->labels->not_found );
	
			if ( is_wp_error( $tags ) )
				wp_die( $tags->get_error_message() );
	
			foreach ( $tags as $key => $tag ) {
				$tags[ $key ]->link = '#';
				$tags[ $key ]->id = $tag->term_id;
			}
	
			// We need raw tag names here, so don't filter the output
			$return = wp_generate_tag_cloud( $tags, array( 'filter' => 0, 'format' => 'list' ) );
	
			if ( empty($return) )
				wp_die( 0 );
	
			echo $return;
			wp_die();
		}

		public function woopanel_dashboard_save_order() {
			$json = array();
	
			if( $widgets = (array)$_REQUEST['widgets'] ) {
				update_option('woopanel_dashboard_widgets', $widgets);
			}
	
			wp_send_json($json);
		}
	}

	new WooPanel_Ajax();
}
