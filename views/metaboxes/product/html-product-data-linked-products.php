<?php
/**
 * Linked product options.
 *
 * @package WooCommerce/admin
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="linked_product_data" class="m-tabs-content__item">

	<div class="options_group show_if_grouped">
		<?php
		$grouped_products_options = array();
		$product_ids = $product_object->is_type( 'grouped' ) ? $product_object->get_children( 'edit' ) : array();

		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( is_object( $product ) ) {
				$grouped_products_options[$product_id] = wp_kses_post( $product->get_formatted_name() );
			}
		}
		woopanel_form_field(
			'grouped_products[]',
			array(
				'id'                => 'grouped_products',
				'type'				=> 'select',
				'placeholder'       => __('Search for a product&hellip;', 'woocommerce'),
				'label'             => __( 'Grouped products', 'woocommerce' ),
				'desc_tip'          => true,
				'description'       => __( 'This lets you choose which products are part of this group.', 'woocommerce' ),
				'custom_attributes' => array(
					'multiple' 			=> 'multiple',
					'data-exclude'		=> intval( $post->ID )
				),
				'input_class' => array('select2-tags-ajax'),
				'options'     => $grouped_products_options,
				'form_inline' 		=> true
			),
			$grouped_products_options
		);
		?>
	</div>

	<div class="options_group">
		<?php
		$product_ids = $product_object->get_upsell_ids( 'edit' );

		$upsell_ids_selected = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( is_object( $product ) ) {
				$upsell_ids_selected[$product_id] = strip_tags( wp_kses_post( $product->get_formatted_name() ) );
			}
		}

		woopanel_form_field(
			'upsell_ids[]',
			array(
				'id'                => 'upsell_ids',
				'type'				=> 'select',
				'placeholder'       => __('Search for a product&hellip;', 'woocommerce'),
				'label'             => __( 'Upsells', 'woocommerce' ),
				'desc_tip'          => true,
				'description'       => __( 'Upsells are products which you recommend instead of the currently viewed product, for example, products that are more profitable or better quality or more expensive.', 'woocommerce' ),
				'custom_attributes' => array(
					'multiple' 			=> 'multiple',
					'data-exclude'		=> intval( $post->ID )
				),
				'input_class' => array('select2-tags-ajax'),
				'options'     => $upsell_ids_selected,
				'form_inline' 		=> true
			),
			$upsell_ids_selected
		);


		$product_ids = $product_object->get_cross_sell_ids( 'edit' );
		$crosssell_ids_selected = array();
		foreach ( $product_ids as $product_id ) {
			$product = wc_get_product( $product_id );
			if ( is_object( $product ) ) {
				$crosssell_ids_selected[$product_id] = strip_tags( wp_kses_post( $product->get_formatted_name() ) );
			}
		}


		woopanel_form_field(
			'crosssell_ids[]',
			array(
				'id'                => 'crosssell_ids',
				'type'				=> 'select',
				'placeholder'       => __('Search for a product&hellip;', 'woocommerce'),
				'label'             => __( 'Cross-sells', 'woocommerce' ),
				'desc_tip'          => true,
				'description'       => __( 'Cross-sells are products which you promote in the cart, based on the current product.', 'woocommerce' ),
				'custom_attributes' => array(
					'multiple' 			=> 'multiple',
					'data-exclude'		=> intval( $post->ID )
				),
				'wrapper_class'		=> 'hide_if_grouped hide_if_external',
				'input_class' => array('select2-tags-ajax'),
				'options'     => $crosssell_ids_selected,
				'form_inline' 		=> true
			),
			$crosssell_ids_selected
		);
		?>
	</div>

	<?php do_action( 'woocommerce_product_options_related' ); ?>
</div>
