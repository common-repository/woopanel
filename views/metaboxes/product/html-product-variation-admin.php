<?php
/**
 * Outputs a variation for editing.
 *
 * @var int $variation_id
 * @var WP_POST $variation
 * @var WC_Product_Variation $variation_object
 * @var array $variation_data array of variation data @deprecated.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woocommerce_variation m-accordion__item">
	<div class="m-accordion__item-head collapsed" data-id="m_accordion_<?php echo $variation_id; ?>_body">

		<div class="m-accordion__item-title m-accordion_select">
			<div class="row">
				<span class="col-1 m-accordion__variation_id">#<?php echo esc_html( $variation_id ); ?></span>
				<?php
				$attribute_values = $variation_object->get_attributes( 'edit' );
				foreach ( $product_object->get_attributes( 'edit' ) as $attribute ) {
					if ( ! $attribute->get_variation() ) {
						continue;
					}
					$selected_value = isset( $attribute_values[ sanitize_title( $attribute->get_name() ) ] ) ? $attribute_values[ sanitize_title( $attribute->get_name() ) ] : '';
					?>
					<div class="col-3">
						<select class="form-control m-input" name="attribute_<?php echo sanitize_title( $attribute->get_name() ) . "[{$loop}]"; ?>">
							<option value="">
								<?php
								/* translators: %s: attribute label */
								printf( esc_html__( 'Any %s&hellip;', 'woocommerce' ), wc_attribute_label( $attribute->get_name() ) );
								?>
							</option>
							<?php if ( $attribute->is_taxonomy() ) : ?>
								<?php foreach ( $attribute->get_terms() as $option ) : ?>
									<option <?php selected( $selected_value, $option->slug ); ?> value="<?php echo esc_attr( $option->slug ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option->name ) ); ?></option>
								<?php endforeach; ?>
							<?php else : ?>
								<?php foreach ( $attribute->get_options() as $option ) : ?>
									<option <?php selected( $selected_value, $option ); ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ); ?></option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</div>
					<?php
				}
				?>
				<input type="hidden" name="variable_post_id[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_id ); ?>" />
				<input type="hidden" class="variation_menu_order" name="variation_menu_order[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $variation_object->get_menu_order( 'edit' ) ); ?>" />
			</div>
		</div>

		<a href="#" class="remove_variation delete" rel="<?php echo esc_attr( $variation_id ); ?>"><?php esc_html_e( 'Remove', 'woocommerce' ); ?></a>
		<span class="m-accordion__item-mode"></span>
	</div>
	<div class="m-accordion__item-body collapse" id="m_accordion_<?php echo $variation_id; ?>_body" role="tabpanel" aria-labelledby="m_accordion_<?php echo $variation_id; ?>_head" data-parent="#m_accordion_2" style="">
		<div class="m-accordion__item-content">
			<div class="row">
				<div class="col variation-thumbnail">
					<?php woopanel_attachment_image( $variation_object->get_image_id( 'edit' ), true, false, 'upload_image_id['.$loop.']' );?>
				</div>

				<div class="col">
					<div class="row">
						<div class="col-6">
							<?php
							woopanel_form_field(
								'variable_enabled['.$loop.']',
								array(
									'id'                => 'variable_enabled_' . $loop,
									'type'				=> 'checkbox',
									'description'		=> esc_html__( 'Enabled', 'woocommerce' ),
									'form_inline' 		=> true,
									'wrapper_class'		=> 'wpl_checkbox_variation',
									'default'			=> true,
									'full'				=> true
								),
								in_array( $variation_object->get_status( 'edit' ), array( 'publish', false ), true )
							);

							woopanel_form_field(
								'variable_is_downloadable['.$loop.']',
								array(
									'id'                => 'variable_is_downloadable' . $loop,
									'type'				=> 'checkbox',
									'description'		=> esc_html__( 'xDownloadable', 'woocommerce' ),
									'form_inline' 		=> true,
									'wrapper_class'		=> 'wpl_checkbox_variation',
									'input_class'		=> array('variable_is_downloadable'),
									'default'			=> true,
									'full'				=> true
								),
								$variation_object->get_downloadable( 'edit' )
							);

							woopanel_form_field(
								"variable_regular_price[{$loop}]",
								array(
									'id'                => "variable_regular_price{$loop}",
									'type'				=> 'text',
									'placeholder'       => __('Variation price (required)', 'woocommerce' ),
									'label'             => sprintf(
										__( 'Regular price (%s)', 'woocommerce' ),
										get_woocommerce_currency_symbol()
									)
								),
								wc_format_localized_price( $variation_object->get_regular_price( 'edit' ) )
							);
							?>
						</div>
						<div class="col-6">
						<?php
							woopanel_form_field(
								'variable_is_virtual['.$loop.']',
								array(
									'id'                => 'variable_is_virtual' . $loop,
									'type'				=> 'checkbox',
									'description'		=> esc_html__( 'Virtual', 'woocommerce' ),
									'form_inline' 		=> true,
									'wrapper_class'		=> 'wpl_checkbox_variation',
									'input_class'		=> array('variable_is_virtual'),
									'default'			=> true,
									'full'				=> true
								),
								$variation_object->get_virtual( 'edit' )
							);

							woopanel_form_field(
								'variable_manage_stock['.$loop.']',
								array(
									'id'                => 'variable_manage_stock' . $loop,
									'type'				=> 'checkbox',
									'description'		=> esc_html__( 'Manage stock?', 'woocommerce' ),
									'form_inline' 		=> true,
									'wrapper_class'		=> 'wpl_checkbox_variation',
									'input_class'		=> array('variable_manage_stock'),
									'default'			=> true,
									'full'				=> true
								),
								$variation_object->get_manage_stock()
							);

							woopanel_form_field(
								"variable_sale_price[{$loop}]",
								array(
									'id'                => "variable_sale_price{$loop}",
									'type'				=> 'text',
									'label'             => sprintf(
										__( 'Sale price (%s)', 'woocommerce' ) . '<a href="#" data-label-cancel="'. __('Cancel schedule', 'woocommerce') .'" data-label-text="' . __( 'Schedule', 'woocommerce' ) . '" class="sale_schedule_variation" style="display: inline;">'. __( 'Schedule', 'woocommerce' ) .'</a>',
										get_woocommerce_currency_symbol()
									),
									'wrapper_class'		=> 'variable_sale_price'
								),
								wc_format_localized_price( $variation_object->get_sale_price( 'edit' ) )
							);
							?>
						</div>
					</div>
				</div>
			</div>

			<?php
				$sale_price_dates_from = $product_object->get_date_on_sale_from( 'edit' ) && ( $date = $product_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
				$sale_price_dates_to   = $product_object->get_date_on_sale_to( 'edit' ) && ( $date = $product_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';

			echo '<div class="row sale_price_dates_fields"><div class="col-6">';
				woopanel_form_field(
					'variable_sale_price_dates_from['.$loop.']',
					array(
						'id'                => 'variable_sale_price_dates_from',
						'type'				=> 'datepicker',
						'label'             => __( 'Sale start date', 'woocommerce' ),
						'placeholder'       => esc_html__( _x( 'From&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD',
						'custom_attributes' => array(
							'maxlength' => '10',
							'pattern'  => esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ),
						),
						'input_class'		=> array('m-datepicker', 'date-picker'),
					),
					esc_attr( $sale_price_dates_from ) 
				);
			echo '</div>
			<div class="col-6">';

				woopanel_form_field(
					'variable_sale_price_dates_to['.$loop.']',
					array(
						'id'                => 'variable_sale_price_dates_to',
						'type'				=> 'datepicker',
						'label'             => __( 'Sale end date', 'woocommerce' ),
						'placeholder'       => esc_html__( _x( 'To&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD',
						'custom_attributes' => array(
							'maxlength' => '10',
							'pattern'  => esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) )
						),
						'input_class'		=> array('m-datepicker', 'date-picker'),
						//'wrapper_after' => '<a href="#" class="description cancel_sale_schedule">' . esc_html__( 'Cancel', 'woocommerce' ) . '</a>',
					),
					esc_attr( $sale_price_dates_to ) 
				);

			echo '</div></div>';
			?>
			<div class="row" style="margin-top: 15px;">
				<div class="col-6">
					<?php
					if ( wc_product_sku_enabled() ) {
						woopanel_form_field(
							"variable_sku[{$loop}]",
							array(
								'id'                => "variable_sku{$loop}",
								'type'				=> 'text',
								'label'             => __('SKU', 'woocommerce' ),
							),
							$variation_object->get_sku( 'edit' )
						);
					}
					?>
				</div>

				<div class="col-6">
					<?php
					woopanel_form_field(
						"variable_stock_status[{$loop}]",
						array(
							'id'                => "variable_stock_status{$loop}",
							'type'				=> 'select',
							'label'             => __('Stock status', 'woocommerce' ),
							'options'       => wc_get_product_stock_status_options(),
							'wrapper_class'	=> 'hide_if_variation_manage_stock'
						),
						$variation_object->get_stock_status( 'edit' )
					);
					?>
				</div>
			</div>


			<?php if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) : ?>
				<div class="row show_if_variation_manage_stock" style="display: none;margin-top: 15px;margin-bottom: -15px;">
					<div class="col-6">
						<?php
						woopanel_form_field(
							"variable_stock[{$loop}]",
							array(
								'id'                => "variable_stock{$loop}",
								'label'             => __( 'Stock quantity', 'woocommerce' ),
								'type'              => 'number',
								'custom_attributes' => array(
									'step' => 'any',
								),
								'data_type'         => 'stock',
							),
							wc_stock_amount( $variation_object->get_stock_quantity( 'edit' ) )
						);

						echo '<input type="hidden" name="variable_original_stock[' . esc_attr( $loop ) . ']" value="' . esc_attr( wc_stock_amount( $variation_object->get_stock_quantity( 'edit' ) ) ) . '" />';?>
					</div>
					<div class="col-6">
						<?php

						woopanel_form_field(
							"variable_backorders[{$loop}]",
							array(
								'id'            => "variable_backorders{$loop}",
								'type'			=> 'select',
								'label'         => __( 'Allow backorders?', 'woocommerce' ),
								'options'       => wc_get_product_backorder_options(),
							),
							$variation_object->get_backorders( 'edit' )
						);

						/**
						 * woocommerce_variation_options_inventory action.
						 *
						 * @since 2.5.0
						 *
						 * @param int     $loop
						 * @param array   $variation_data
						 * @param WP_Post $variation
						 */
						do_action( 'woocommerce_variation_options_inventory', $loop, $variation_data, $variation );
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="row product_shipping_class-row hide_if_variation_virtual">
				<div class="col-12">
					<?php
					$args = array(
						'taxonomy'         => 'product_shipping_class',
						'hide_empty'       => 0,
						'show_option_none' => __( 'Same as parent', 'woocommerce' ),
						'name'             => 'product_shipping_class',
						'id'               => 'product_shipping_class',
						'selected'         => $product_object->get_shipping_class_id( 'edit' ),
						'class'            => 'select short',
						'echo'             => 0,
					);

					$select  = wp_dropdown_categories( $args );


					$options = array();
					$shipping_class_selected = '';
					if( preg_match_all("/<option value='(.*?)'\s*>(.*?)<\/option>/", $select, $matches, PREG_SET_ORDER) ) {
						foreach($matches as $match) {
							$value = str_replace("' selected='selected", '', $match[1]);
							$options[$value] = $match[2];

							if(preg_match('/\' selected=\'selected/', $match[1])) {
								$shipping_class_selected = $value;
							}
						}
					}

					woopanel_form_field(
						"variable_shipping_class[{$loop}]",
						array(
							'id'          => 'product_shipping_class',
							'type'        => 'select',
							'label'       => __( 'Shipping class', 'woocommerce' ),
							'options'	  => $options
						),
						$variation_object->get_shipping_class_id( 'edit' )
					);
					?>
				</div>
			</div>


			<div class="row m-margin15">
				<div class="col-12">
					<?php
					woopanel_form_field(
						"variable_description[{$loop}]",
						array(
							'id'                => "variable_description{$loop}",
							'type'				=> 'textarea',
							'label'             => __('Description', 'woocommerce' )
						),
						$variation_object->get_description( 'edit' )
					);
					?>
				</div>
			</div>


			<div class="row show_if_variation_downloadable" style="display: none; margin-top: 15px;">
				<div class="col-6">
					<?php
					woopanel_form_field(
						"variable_download_limit[{$loop}]",
						array(
							'id'                => "variable_download_limit{$loop}",
							'label'             => __( 'Download limit', 'woocommerce' ),
							'placeholder'       => __( 'Unlimited', 'woocommerce' ),
							'type'              => 'number',
							'desc_tip'          => true,
							'custom_attributes' => array(
								'step' => '1',
								'min'  => '0',
							),
						),
						$variation_object->get_download_limit( 'edit' ) < 0 ? '' : $variation_object->get_download_limit( 'edit' )
					);
					?>
				</div>

				<div class="col-6">
					<?php
					woopanel_form_field(
						"variable_download_expiry[{$loop}]",
						array(
							'id'                => "variable_download_expiry{$loop}",
							'label'             => __( 'Download expiry', 'woocommerce' ),
							'placeholder'       => __( 'Never', 'woocommerce' ),
							'type'              => 'number',
							'desc_tip'          => true,
							'custom_attributes' => array(
								'step' => '1',
								'min'  => '0',
							),
						),
						$variation_object->get_download_expiry( 'edit' ) < 0 ? '' : $variation_object->get_download_expiry( 'edit' )
					);

					/**
					 * woocommerce_variation_options_download action.
					 *
					 * @since 2.5.0
					 *
					 * @param int     $loop
					 * @param array   $variation_data
					 * @param WP_Post $variation
					 */
					do_action( 'woopanel_variation_options_download', $loop, $variation_data, $variation );
					?>
				</div>
			</div>


		</div>
	</div>
</div>

<div class="woocommerce_variation wc-metabox closed">

	<div class="woocommerce_variable_attributes wc-metabox-content" style="display: none;">
		<div class="data">
			<p class="form-row form-row-first upload_image">
				<a href="#" class="upload_image_button tips <?php echo $variation_object->get_image_id( 'edit' ) ? 'remove' : ''; ?>" data-tip="<?php echo $variation_object->get_image_id( 'edit' ) ? esc_attr__( 'Remove this image', 'woocommerce' ) : esc_attr__( 'Upload an image', 'woocommerce' ); ?>" rel="<?php echo esc_attr( $variation_id ); ?>">
					<img src="<?php echo $variation_object->get_image_id( 'edit' ) ? esc_url( wp_get_attachment_thumb_url( $variation_object->get_image_id( 'edit' ) ) ) : esc_url( wc_placeholder_img_src() ); ?>" /><input type="hidden" name="upload_image_id[<?php echo esc_attr( $loop ); ?>]" class="upload_image_id" value="<?php echo esc_attr( $variation_object->get_image_id( 'edit' ) ); ?>" />
				</a>
			</p>
			<?php
			if ( wc_product_sku_enabled() ) {
				woocommerce_wp_text_input(
					array(
						'id'            => "variable_sku{$loop}",
						'name'          => "variable_sku[{$loop}]",
						'value'         => $variation_object->get_sku( 'edit' ),
						'placeholder'   => $variation_object->get_sku(),
						'label'         => '<abbr title="' . esc_attr__( 'Stock Keeping Unit', 'woocommerce' ) . '">' . esc_html__( 'SKU', 'woocommerce' ) . '</abbr>',
						'desc_tip'      => true,
						'description'   => __( 'SKU refers to a Stock-keeping unit, a unique identifier for each distinct product and service that can be purchased.', 'woocommerce' ),
						'wrapper_class' => 'form-row form-row-last',
					)
				);
			}
			?>
			<p class="form-row form-row-full options">
				<label>
					<?php esc_html_e( 'Enabled', 'woocommerce' ); ?>:
					<input type="checkbox" class="checkbox" name="variable_enabled[<?php echo $loop; ?>]" <?php checked( in_array( $variation_object->get_status( 'edit' ), array( 'publish', false ), true ), true ); ?> />
				</label>
				<label class="tips" data-tip="<?php esc_html_e( 'Enable this option if access is given to a downloadable file upon purchase of a product', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Downloadable', 'woocommerce' ); ?>:
					<input type="checkbox" class="checkbox variable_is_downloadable" name="variable_is_downloadable[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_downloadable( 'edit' ), true ); ?> />
				</label>
				<label class="tips" data-tip="<?php esc_html_e( 'Enable this option if a product is not shipped or there is no shipping cost', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Virtual', 'woocommerce' ); ?>:
					<input type="checkbox" class="checkbox variable_is_virtual" name="variable_is_virtual[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_virtual( 'edit' ), true ); ?> />
				</label>

				<?php if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) : ?>
					<label class="tips" data-tip="<?php esc_html_e( 'Enable this option to enable stock management at variation level', 'woocommerce' ); ?>">
						<?php esc_html_e( 'Manage stock?', 'woocommerce' ); ?>
						<input type="checkbox" class="checkbox variable_manage_stock" name="variable_manage_stock[<?php echo esc_attr( $loop ); ?>]" <?php checked( $variation_object->get_manage_stock(), true ); // Use view context so 'parent' is considered. ?> />
					</label>
				<?php endif; ?>

				<?php do_action( 'woocommerce_variation_options', $loop, $variation_data, $variation ); ?>
			</p>

			<div class="variable_pricing">
				<?php
				$label = sprintf(
					/* translators: %s: currency symbol */
					__( 'Regular price (%s)', 'woocommerce' ),
					get_woocommerce_currency_symbol()
				);

				woocommerce_wp_text_input(
					array(
						'id'            => "variable_regular_price_{$loop}",
						'name'          => "variable_regular_price[{$loop}]",
						'value'         => wc_format_localized_price( $variation_object->get_regular_price( 'edit' ) ),
						'label'         => $label,
						'data_type'     => 'price',
						'wrapper_class' => 'form-row form-row-first',
						'placeholder'   => __( 'Variation price (required)', 'woocommerce' ),
					)
				);

				$label = sprintf(
					/* translators: %s: currency symbol */
					__( 'Sale price (%s)', 'woocommerce' ),
					get_woocommerce_currency_symbol()
				);

				woocommerce_wp_text_input(
					array(
						'id'            => "variable_sale_price{$loop}",
						'name'          => "variable_sale_price[{$loop}]",
						'value'         => wc_format_localized_price( $variation_object->get_sale_price( 'edit' ) ),
						'data_type'     => 'price',
						'label'         => $label . ' <a href="#" class="sale_schedule">' . esc_html__( 'Schedule', 'woocommerce' ) . '</a><a href="#" class="cancel_sale_schedule hidden">' . esc_html__( 'Cancel schedule', 'woocommerce' ) . '</a>',
						'wrapper_class' => 'form-row form-row-last',
					)
				);

				$sale_price_dates_from = $variation_object->get_date_on_sale_from( 'edit' ) && ( $date = $variation_object->get_date_on_sale_from( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';
				$sale_price_dates_to   = $variation_object->get_date_on_sale_to( 'edit' ) && ( $date = $variation_object->get_date_on_sale_to( 'edit' )->getOffsetTimestamp() ) ? date_i18n( 'Y-m-d', $date ) : '';

				echo '<div class="form-field sale_price_dates_fields hidden">
					<p class="form-row form-row-first">
						<label>' . __( 'Sale start date', 'woocommerce' ) . '</label>
						<input type="text" class="sale_price_dates_from" name="variable_sale_price_dates_from[' . $loop . ']" value="' . esc_attr( $sale_price_dates_from ) . '" placeholder="' . _x( 'From&hellip;', 'placeholder', 'woocommerce' ) . ' YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
					</p>
					<p class="form-row form-row-last">
						<label>' . __( 'Sale end date', 'woocommerce' ) . '</label>
						<input type="text" class="sale_price_dates_to" name="variable_sale_price_dates_to[' . esc_attr( $loop ) . ']" value="' . esc_attr( $sale_price_dates_to ) . '" placeholder="' . esc_html_x( 'To&hellip;', 'placeholder', 'woocommerce' ) . '  YYYY-MM-DD" maxlength="10" pattern="' . esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ) . '" />
					</p>
				</div>';

				/**
				 * woocommerce_variation_options_pricing action.
				 *
				 * @since 2.5.0
				 *
				 * @param int     $loop
				 * @param array   $variation_data
				 * @param WP_Post $variation
				 */
				do_action( 'woocommerce_variation_options_pricing', $loop, $variation_data, $variation );
				?>
			</div>


			<div>
				<?php
				woocommerce_wp_select(
					array(
						'id'            => "variable_stock_status{$loop}",
						'name'          => "variable_stock_status[{$loop}]",
						'value'         => $variation_object->get_stock_status( 'edit' ),
						'label'         => __( 'Stock status', 'woocommerce' ),
						'options'       => wc_get_product_stock_status_options(),
						'desc_tip'      => true,
						'description'   => __( 'Controls whether or not the product is listed as "in stock" or "out of stock" on the frontend.', 'woocommerce' ),
						'wrapper_class' => 'form-row form-row-full hide_if_variation_manage_stock',
					)
				);

				if ( wc_product_weight_enabled() ) {
					$label = sprintf(
						/* translators: %s: weight unit */
						__( 'Weight (%s)', 'woocommerce' ),
						esc_html( get_option( 'woocommerce_weight_unit' ) )
					);

					woocommerce_wp_text_input(
						array(
							'id'            => "variable_weight{$loop}",
							'name'          => "variable_weight[{$loop}]",
							'value'         => wc_format_localized_decimal( $variation_object->get_weight( 'edit' ) ),
							'placeholder'   => wc_format_localized_decimal( $product_object->get_weight() ),
							'label'         => $label,
							'desc_tip'      => true,
							'description'   => __( 'Weight in decimal form', 'woocommerce' ),
							'type'          => 'text',
							'data_type'     => 'decimal',
							'wrapper_class' => 'form-row form-row-first hide_if_variation_virtual',
						)
					);
				}

				if ( wc_product_dimensions_enabled() ) {
					$parent_length = wc_format_localized_decimal( $product_object->get_length() );
					$parent_width  = wc_format_localized_decimal( $product_object->get_width() );
					$parent_height = wc_format_localized_decimal( $product_object->get_height() );

					?>
					<p class="form-field form-row dimensions_field hide_if_variation_virtual form-row-last">
						<label for="product_length">
							<?php
							printf(
								/* translators: %s: dimension unit */
								esc_html__( 'Dimensions (L&times;W&times;H) (%s)', 'woocommerce' ),
								get_option( 'woocommerce_dimension_unit' )
							);
							?>
						</label>
						<?php echo wc_help_tip( __( 'Length x width x height in decimal form', 'woocommerce' ) ); ?>
						<span class="wrap">
							<input id="product_length" placeholder="<?php echo $parent_length ? esc_attr( $parent_length ) : esc_attr__( 'Length', 'woocommerce' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="variable_length[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_length( 'edit' ) ) ); ?>" />
							<input placeholder="<?php echo $parent_width ? esc_attr( $parent_width ) : esc_attr__( 'Width', 'woocommerce' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="variable_width[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_width( 'edit' ) ) ); ?>" />
							<input placeholder="<?php echo $parent_height ? esc_attr( $parent_height ) : esc_attr__( 'Height', 'woocommerce' ); ?>" class="input-text wc_input_decimal last" size="6" type="text" name="variable_height[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( wc_format_localized_decimal( $variation_object->get_height( 'edit' ) ) ); ?>" />
						</span>
					</p>
					<?php
				}

				/**
				 * woocommerce_variation_options_dimensions action.
				 *
				 * @since 2.5.0
				 *
				 * @param int     $loop
				 * @param array   $variation_data
				 * @param WP_Post $variation
				 */
				do_action( 'woocommerce_variation_options_dimensions', $loop, $variation_data, $variation );
				?>
			</div>

			<div>


				<?php
				if ( wc_tax_enabled() ) {
					woocommerce_wp_select(
						array(
							'id'            => "variable_tax_class{$loop}",
							'name'          => "variable_tax_class[{$loop}]",
							'value'         => $variation_object->get_tax_class( 'edit' ),
							'label'         => __( 'Tax class', 'woocommerce' ),
							'options'       => array( 'parent' => __( 'Same as parent', 'woocommerce' ) ) + wc_get_product_tax_class_options(),
							'desc_tip'      => 'true',
							'description'   => __( 'Choose a tax class for this product. Tax classes are used to apply different tax rates specific to certain types of product.', 'woocommerce' ),
							'wrapper_class' => 'form-row form-row-full',
						)
					);

					/**
					 * woocommerce_variation_options_tax action.
					 *
					 * @since 2.5.0
					 *
					 * @param int     $loop
					 * @param array   $variation_data
					 * @param WP_Post $variation
					 */
					do_action( 'woocommerce_variation_options_tax', $loop, $variation_data, $variation );
				}
				?>
			</div>
			<div>
				<?php
				woocommerce_wp_textarea_input(
					array(
						'id'            => "variable_description{$loop}",
						'name'          => "variable_description[{$loop}]",
						'value'         => $variation_object->get_description( 'edit' ),
						'label'         => __( 'Description', 'woocommerce' ),
						'desc_tip'      => true,
						'description'   => __( 'Enter an optional description for this variation.', 'woocommerce' ),
						'wrapper_class' => 'form-row form-row-full',
					)
				);
				?>
			</div>
			<div class="show_if_variation_downloadable" style="display: none;">
				<div class="form-row form-row-full downloadable_files">
					<label><?php esc_html_e( 'Downloadable files', 'woocommerce' ); ?></label>
					<table class="widefat">
						<thead>
							<div>
								<th><?php esc_html_e( 'Name', 'woocommerce' ); ?> <?php echo wc_help_tip( __( 'This is the name of the download shown to the customer.', 'woocommerce' ) ); ?></th>
								<th colspan="2"><?php esc_html_e( 'File URL', 'woocommerce' ); ?> <?php echo wc_help_tip( __( 'This is the URL or absolute path to the file which customers will get access to. URLs entered here should already be encoded.', 'woocommerce' ) ); ?></th>
								<th>&nbsp;</th>
							</div>
						</thead>
						<tbody>
							<?php
							if ( $downloads = $variation_object->get_downloads( 'edit' ) ) {
								foreach ( $downloads as $key => $file ) {
									include 'html-product-variation-download.php';
								}
							}
							?>
						</tbody>
						<tfoot>
							<div>
								<th colspan="4">
									<a href="#" class="button insert" data-row="
									<?php
									$key  = '';
									$file = array(
										'file' => '',
										'name' => '',
									);
									ob_start();
									require WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-variation-download.php';
									echo esc_attr( ob_get_clean() );
									?>
									"><?php esc_html_e( 'Add file', 'woocommerce' ); ?></a>
								</th>
							</div>
						</tfoot>
					</table>
				</div>

		</div>
	</div>
</div>
