<?php
/**
 * Displays the inventory tab in the product data meta box.
 *
 * @package WooCommerce\Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="inventory_product_data" class="m-tabs-content__item">

	<div class="options_group options_hide_variable">
		<?php
		if ( wc_product_sku_enabled() ) {
			woopanel_form_field(
				'_sku',
				array(
					'id'          => '_sku',
					'type'		  => 'text',
					'label'       => '<abbr title="' . esc_attr__( 'Stock Keeping Unit', 'woocommerce' ) . '">' . esc_html__( 'SKU', 'woocommerce' ) . '</abbr>',
					'desc_tip'    => true,
					'description' => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'woocommerce' ),
					'form_inline' => true
				),
				$product_object->get_sku( 'edit' )
			);
		}

		do_action( 'woocommerce_product_options_sku' );

		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {

			woopanel_form_field(
				'_manage_stock',
				array(
					'id'            => '_manage_stock',
					'type'			=> 'checkbox',
					'wrapper_class' => 'show_if_simple show_if_variable',
					'label'         => __( 'Manage stock?', 'woocommerce' ),
					'description'   => __( 'Enable stock management at product level', 'woocommerce' ),
					'default'	  	=> 'yes',
					'form_inline' 	=> true
				),
				$product_object->get_manage_stock( 'edit' ) ? 'yes' : 'no'
			);

			do_action( 'woocommerce_product_options_stock' );

			echo '<div class="stock_fields show_if_simple show_if_variable">';

			woopanel_form_field(
				'_stock',
				array(
					'id'                => '_stock',
					'type'		  		=> 'text',
					'label'             => __( 'Stock quantity', 'woocommerce' ),
					'desc_tip'          => true,
					'default'			=> 0,
					'description'       => __( 'Stock quantity. If this is a variable product this value will be used to control stock for all variations, unless you define stock at variation level.', 'woocommerce' ),
					'custom_attributes' => array(
						'step' => 'any',
					),
					'data_type'         => 'stock',
					'form_inline' 		=> true
				),
				wc_stock_amount( $product_object->get_stock_quantity( 'edit' ) )
			);

			echo '<input type="hidden" name="_original_stock" value="' . esc_attr( wc_stock_amount( $product_object->get_stock_quantity( 'edit' ) ) ) . '" />';

			woopanel_form_field(
				'_backorders',
				array(
					'id'          => '_backorders',
					'type'		  => 'select',
					'label'       => __( 'Allow backorders?', 'woocommerce' ),
					'options'     => wc_get_product_backorder_options(),
					'desc_tip'    => true,
					'description' => __( 'If managing stock, this controls whether or not backorders are allowed. If enabled, stock quantity can go below 0.', 'woocommerce' ),
					'form_inline' => true
				),
				$product_object->get_backorders( 'edit' )
			);

			woopanel_form_field(
				'_low_stock_amount',
				array(
					'id'                => '_low_stock_amount',
					'type'				=> 'text',
					'placeholder'       => get_option( 'woocommerce_notify_low_stock_amount' ),
					'label'             => __( 'Low stock threshold', 'woocommerce' ),
					'desc_tip'          => true,
					'default'			=> 2,
					'description'       => __( 'When product stock reaches this amount you will be notified by email', 'woocommerce' ),
					'custom_attributes' => array(
						'step' => 'any',
					),
					'form_inline' 		=> true
				),
				$product_object->get_low_stock_amount( 'edit' )
			);

			do_action( 'woocommerce_product_options_stock_fields' );

			echo '</div>';
		}

		woopanel_form_field(
			'_stock_status',
			array(
				'id'            => '_stock_status',
				'type'			=> 'select',
				'wrapper_class' => 'stock_status_field hide_if_variable hide_if_external hide_if_grouped',
				'label'         => __( 'Stock status', 'woocommerce' ),
				'options'       => wc_get_product_stock_status_options(),
				'desc_tip'      => true,
				'description'   => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce' ),
				'form_inline' 	=> true
			),
			$product_object->get_stock_status( 'edit' )
		);

		do_action( 'woocommerce_product_options_stock_status' );
		?>
	</div>

	<div class="options_group show_if_simple show_if_variable">
		<?php
		woopanel_form_field(
			'_sold_individually',
			array(
				'id'            => '_sold_individually',
				'type'			=> 'checkbox',
				'wrapper_class' => 'show_if_simple show_if_variable',
				'label'         => __( 'Sold individually', 'woocommerce' ),
				'description'   => __( 'Enable this to only allow one of this item to be bought in a single order', 'woocommerce' ),
				'default'	 	=> 'yes',
				'form_inline' 	=> true
			),
			$product_object->get_sold_individually( 'edit' ) ? 'yes' : 'no'
		);

		do_action( 'woocommerce_product_options_sold_individually' );
		?>
	</div>

	<?php do_action( 'woocommerce_product_options_inventory_product_data' ); ?>
</div>
