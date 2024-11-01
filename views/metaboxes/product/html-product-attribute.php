<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!--begin::Item-->
<div data-taxonomy="<?php echo esc_attr( $attribute->get_taxonomy() ); ?>" class="woocommerce_attribute m-accordion__item <?php echo esc_attr( implode( ' ', $metabox_class ) ); ?>" rel="<?php echo esc_attr( $attribute->get_position() ); ?>">
	<div class="m-accordion__item-head collapsed" role="tab" id="m_accordion_<?php echo md5($attribute->get_name()) . esc_attr( $attribute->get_position() ); ?>_head" data-toggle="collapse" href="#m_accordion_<?php echo md5($attribute->get_name()) . esc_attr( $attribute->get_position() ); ?>_body" aria-expanded="false">
		<span class="m-accordion__item-title"><?php echo wc_attribute_label( $attribute->get_name() ); ?></span>
		<a href="#" class="remove_row delete">Remove</a>
		<span class="m-accordion__item-mode"></span>
	</div>
	<div class="m-accordion__item-body collapse" id="m_accordion_<?php echo md5($attribute->get_name()) . esc_attr( $attribute->get_position() ); ?>_body" role="tabpanel" aria-labelledby="m_accordion_<?php echo md5($attribute->get_name()) . esc_attr( $attribute->get_position() ); ?>_head" data-parent="#m_accordion_2" style="">
		<div class="m-accordion__item-content">
			<?php

			if ( $attribute->is_taxonomy() && $attribute_taxonomy = $attribute->get_taxonomy_object() ) {
				$attribute_types = wc_get_attribute_types();

				if ( ! array_key_exists( $attribute_taxonomy->attribute_type, $attribute_types ) ) {
					$attribute_taxonomy->attribute_type = 'select';
				}

				?>
				<div class="form-group m-form__group type-text row" id="attribute_name_<?php echo $i;?>_field" data-priority="">
					<label for="attribute_name_<?php echo $i;?>" class="col-3 col-form-label"><?php echo esc_html( 'Name', 'woocommerce' );?></label>
					<div class="col-9">
						<input type="text" class="form-control m-input m-exclude-input readonly" name="hidden" value="<?php echo wc_attribute_label($attribute->get_name());?>" readonly="readonly">
						<input type="hidden" name="attribute_names[<?php echo esc_attr($i);?>]" value="<?php echo esc_attr( $attribute->get_name() );?>" />
					</div>
				</div>
				<?php
				//if ( 'select' === $attribute_taxonomy->attribute_type ) {

					
					
					$args      = array(
						'orderby'    => 'name',
						'hide_empty' => 0,
					);
					$all_terms = get_terms( $attribute->get_taxonomy(), apply_filters( 'woocommerce_product_attribute_terms', $args ) );

					$value_options = array();
					if ( $all_terms ) {
						foreach ( $all_terms as $term ) {
							$options = $attribute->get_options();
							$options = ! empty( $options ) ? $options : array();
							$value_options[$term->term_id] = apply_filters( 'woocommerce_product_attribute_term_name', $term->name, $term );
						}
					}

					if( ! isset($selected_value) ) {
						$selected_value = $value_options;
					}

					woopanel_form_field(
						'attribute_values['.$i.'][]',
						array(
							'id'                => 'attribute_value_' . $i,
							'type'				=> 'select',
							'label'             => __( 'Value(s)', 'woocommerce' ),
							'custom_attributes' => array(
								'multiple' 			=> 'multiple',
								'data-exclude'		=> intval( $post->ID )
							),
							'input_class' => array('select2-tags'),
							'options'     => $value_options,
							'form_inline' 		=> true
						),
						$selected_value
					);
				//}

				do_action( 'woopanel_product_option_terms', $attribute_taxonomy, $i );

			}else {
				woopanel_form_field(
					'attribute_names[' . $i . ']',
					array(
						'id'                => 'attribute_name_' . $i,
						'type'				=> 'text',
						'placeholder'       => __( 'Enter name attribute&hellip;', 'woopanel' ),
						'label'             => esc_html__( 'Name', 'woocommerce' ),
						'input_class'		=> array('attribute_name'),
						'form_inline' 		=> true,
					),
					esc_attr( $attribute->get_name() )
				);

				woopanel_form_field(
					'attribute_values['.$i.']',
					array(
						'id'                => 'attribute_value_' . $i,
						'type'				=> 'textarea',
						'label'             => __( 'Value(s)', 'woocommerce' ),
						'placeholder'		=> sprintf( esc_attr__( 'Enter some text, or some attributes by "%s" separating values.', 'woocommerce' ), WC_DELIMITER ),
						'form_inline' 		=> true
					),
					esc_textarea( wc_implode_text_attributes( $attribute->get_options() ) )
				);
			}

			woopanel_form_field(
				'attribute_visibility['.$i.']',
				array(
					'id'                => 'attribute_visibility_' . $i,
					'type'				=> 'checkbox',
					'label'             => '&nbsp;',
					'description'		=> esc_html__( 'Visible on the product page', 'woocommerce' ),
					'form_inline' 		=> true,
					'wrapper_class'		=> 'attribute_variation',
					'default'				=> true
				),
				$attribute->get_visible() ? true : false
			);

			$attribute_variation_class = 'attribute_variation show_if_variable';
			
			if(isset($type) && $type == 'variable' ) {
				$attribute_variation_class .= ' enable_variation';
			}

			woopanel_form_field(
				'attribute_variation['.$i.']',
				array(
					'id'                => 'attribute_variation_' . $i,
					'type'				=> 'checkbox',
					'label'             => '&nbsp;',
					'description'		=> esc_html__( 'Used for variations', 'woocommerce' ),
					'form_inline' 		=> true,
					'default'				=> true,
					'wrapper_class' => $attribute_variation_class,
				),
				$attribute->get_variation() ? true : false
			);
			?>
			<input type="hidden" name="attribute_position[<?php echo esc_attr( $i ); ?>]" class="attribute_position" value="<?php echo esc_attr( $attribute->get_position() ); ?>" />

		</div>
	</div>
</div>
<!--end::Item-->