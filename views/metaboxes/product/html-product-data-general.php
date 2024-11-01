<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="general_product_data" class="m-tabs-content__item">

	<div class="options_group show_if_external">
		<?php
		
		woopanel_form_field(
			'_product_url',
			array(
				'id'          => '_product_url',
				'type'		  => 'text',
				'label'       => __( 'Product URL', 'woocommerce' ),
				'placeholder' => 'http://',
				'description' => __( 'Enter the external URL to the product.', 'woocommerce' ),
			),
			is_callable( array( $product_object, 'get_product_url' ) ) ? $product_object->get_product_url( 'edit' ) : ''
		);

		woopanel_form_field(
			'_button_text',
			array(
				'id'          => '_button_text',
				'type'		  => 'text',
				'label'       => __( 'Button text', 'woocommerce' ),
				'placeholder' => _x( 'Buy product', 'placeholder', 'woocommerce' ),
				'description' => __( 'This text will be shown on the button linking to the external product.', 'woocommerce' ),
			),
			is_callable( array( $product_object, 'get_button_text' ) ) ? $product_object->get_button_text( 'edit' ) : ''
		);
		?>
	</div>

	<div class="options_group pricing show_if_simple show_if_external hidden">
		<?php

		woopanel_form_field(
			'_regular_price',
			array(
				'id'        => '_regular_price',
				'type'		=> 'text',
				'label'     => __( 'Regular price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'data_type' => 'price',
				'form_inline' => true
			),
			$product_object->get_regular_price( 'edit' )
		);

		woopanel_form_field(
			'_sale_price',
			array(
				'id'          => '_sale_price',
				'type'		  => 'text',
				'data_type'   => 'price',
				'label'       => __( 'Sale price', 'woocommerce' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'wrapper_after' => '<a href="#" data-label-cancel="'. __('Cancel', 'woocommerce') .'" data-label-text="' . __( 'Schedule', 'woocommerce' ) . '" class="sale_schedule">' . __( 'Schedule', 'woocommerce' ) . '</a>',
				'form_inline' => true
			),
			$product_object->get_sale_price( 'edit' )
		);

		$sale_price_dates_from = $product_object->get_date_on_sale_from( 'edit' ) && ( $date = $product_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
		$sale_price_dates_to   = $product_object->get_date_on_sale_to( 'edit' ) && ( $date = $product_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';

		echo '<div class="sale_price_dates_fields">';
		woopanel_form_field(
			'_sale_price_dates_from',
			array(
				'id'                => '_sale_price_dates_from',
				'type'				=> 'datepicker',
				'label'             => __( 'Sale price dates', 'woocommerce' ),
				'placeholder'       => esc_html__( _x( 'From&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD',
				'custom_attributes' => array(
					'maxlength' => '10',
					'pattern'  => esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ),
				),
				'form_inline' => true
			),
			esc_attr( $sale_price_dates_from ) 
		);

		woopanel_form_field(
			'_sale_price_dates_to',
			array(
				'id'                => '_sale_price_dates_to',
				'type'				=> 'datepicker',
				'label'             => '&nbsp;',
				'placeholder'       => esc_html__( _x( 'To&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD',
				'custom_attributes' => array(
					'maxlength' => '10',
					'pattern'  => esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) )
				),
				'form_inline' => true,
				//'wrapper_after' => '<a href="#" class="description cancel_sale_schedule">' . esc_html__( 'Cancel', 'woocommerce' ) . '</a>',
			),
			esc_attr( $sale_price_dates_to ) 
		);

		echo '</div>';
		?>
	</div>

	<div class="options_group show_if_downloadable hidden">
		<div class="form-field downloadable_files">
			<label><?php esc_html_e( 'Downloadable files', 'woocommerce' ); ?></label>
			<table class="widefat">
				<thead>
					<tr>
						<th class="sort">&nbsp;</th>
						<th><?php esc_html_e( 'Name', 'woocommerce' ); ?> <?php echo wc_help_tip( __( 'This is the name of the download shown to the customer.', 'woocommerce' ) ); ?></th>
						<th colspan="2"><?php esc_html_e( 'File URL', 'woocommerce' ); ?> <?php echo wc_help_tip( __( 'This is the URL or absolute path to the file which customers will get access to. URLs entered here should already be encoded.', 'woocommerce' ) ); ?></th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$downloadable_files = $product_object->get_downloads( 'edit' );
					if ( $downloadable_files ) {
						foreach ( $downloadable_files as $key => $file ) {
							include WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-download.php';
						}
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="5">
							<a href="#" class="button insert" data-row="
							<?php
								$key  = '';
								$file = array(
									'file' => '',
									'name' => '',
								);
								ob_start();
								require 'html-product-download.php';
								echo esc_attr( ob_get_clean() );
							?>
							"><?php esc_html_e( 'Add File', 'woocommerce' ); ?></a>
						</th>
					</tr>
				</tfoot>
			</table>
		</div>
		<?php
		woopanel_form_field(
			'_download_limit',
			array(
				'id'                => '_download_limit',
				'type'				=> 'text',
				'value'             => -1 === $product_object->get_download_limit( 'edit' ) ? '' : $product_object->get_download_limit( 'edit' ),
				'label'             => __( 'Download limit', 'woocommerce' ),
				'placeholder'       => __( 'Unlimited', 'woocommerce' ),
				'description'       => __( 'Leave blank for unlimited re-downloads.', 'woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '0',
				),
			)
		);

		woopanel_form_field(
			'_download_expiry',
			array(
				'id'                => '_download_expiry',
				'type'				=> 'text',
				'value'             => -1 === $product_object->get_download_expiry( 'edit' ) ? '' : $product_object->get_download_expiry( 'edit' ),
				'label'             => __( 'Download expiry', 'woocommerce' ),
				'placeholder'       => __( 'Never', 'woocommerce' ),
				'description'       => __( 'Enter the number of days before a download link expires, or leave blank.', 'woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'step' => '1',
					'min'  => '0',
				),
			)
		);

		do_action( 'woopanel_product_options_downloads' );
		?>
	</div>

	<?php if ( wc_tax_enabled() ) : ?>
		<div class="options_group show_if_simple show_if_external show_if_variable">
			<?php
			woopanel_form_field(
				'_tax_status',
				array(
					'id'          => '_tax_status',
					'type'		  => 'select',
					'value'       => $product_object->get_tax_status( 'edit' ),
					'label'       => __( 'Tax status', 'woocommerce' ),
					'options'     => array(
						'taxable'  => __( 'Taxable', 'woocommerce' ),
						'shipping' => __( 'Shipping only', 'woocommerce' ),
						'none'     => _x( 'None', 'Tax status', 'woocommerce' ),
					),
					'desc_tip'    => 'true',
					'description' => __( 'Define whether or not the entire product is taxable, or just the cost of shipping it.', 'woocommerce' ),
				)
			);

			woopanel_form_field(
				'_tax_class',
				array(
					'id'          => '_tax_class',
					'type'		  => 'select',
					'value'       => $product_object->get_tax_class( 'edit' ),
					'label'       => __( 'Tax class', 'woocommerce' ),
					'options'     => wc_get_product_tax_class_options(),
					'desc_tip'    => 'true',
					'description' => __( 'Choose a tax class for this product. Tax classes are used to apply different tax rates specific to certain types of product.', 'woocommerce' ),
				)
			);

			do_action( 'woocommerce_product_options_tax' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woopanel_product_options_general_product_data' ); ?>
</div>
