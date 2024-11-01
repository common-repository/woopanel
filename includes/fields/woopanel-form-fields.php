<?php
if ( ! function_exists( 'woopanel_form_field' ) ) {
	function woopanel_form_field( $key, $args, $value = null ) {
	
		$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
			'required'          => false,
			'autocomplete'      => false,
			'id'                => $key,
			'class'             => array(),
			'label_class'       => array(),
			'input_class'       => array(),
			'wrapper_class'		=> '',
			'return'            => false,
			'options'           => array(),
			'custom_attributes' => array(),
			'validate'          => array(),
			'default'           => '',
			'autofocus'         => '',
			'priority'          => '',
			'settings'          => '',
			'form_inline'       => false,
			'disable'           => false,
			'wrapper_after'		=> '',
			'size'				=> false,
			'value'				=> ''
		);

		$args = wp_parse_args( $args, $defaults );
		
		if( ! empty($args['value']) ) {
			$value = $args['value'];
		}
		
	
		$args = apply_filters( 'woopanel_form_field_args', $args, $key, $value );

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woopad' ) . '">*</abbr>';
		} else {
			$required = '';
		}

        $disable = $args['disable'] ? 'disabled' : '';

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}


		// Custom attribute handling.
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

		if ( $args['maxlength'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
		}

		if ( ! empty( $args['autocomplete'] ) ) {
			$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
		}

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( $args['description'] ) {
			$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
		}

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}
		if ( ! empty( $args['type'] ) ) {
			$args['class'][] = 'type-' . $args['type'] . ' ' . $args['wrapper_class'];
		}

		$field           = '';
		$label_id        = $args['id'];
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<div class="form-group m-form__group %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</div>';
		
		switch ( $args['type'] ) {
			case 'textarea':
			$field .= '<textarea '. $disable .' name="' . esc_attr( $key ) . '" class="form-control m-input ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="5"' : '' ) . ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . stripslashes($value) . '</textarea>';

			break;
			case 'text':
			case 'password':
			case 'datetime':
			case 'month':
			case 'week':
			case 'time':
			case 'file':
			case 'email':
			case 'url':
			case 'tel':
				$field .= '<input '. $disable .' type="' . esc_attr( $args['type'] ) . '" class="form-control m-input ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
			break;			
			case 'datepicker':
				$input = '<input '. $disable .' type="text" class="form-control m-input m-datepicker date-picker ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' . implode( ' ', $custom_attributes ) . ' />';
				$field .= $input . '<div class="input-group-append"><span class="input-group-text"><i class="la la-calendar-check-o"></i></span></div>';
				break;
			case 'select':
			$field   = '';
			$options = '';


			if ( isset($args['options']) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					if ( '' === $option_key ) {
							// If we have a blank option, select2 needs a placeholder.
						if ( empty( $args['placeholder'] ) ) {
							$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'woopad' );
						}
						$custom_attributes[] = 'data-allow_clear="true"';
					}
					if( is_array($value) ) {
						$options .= '<option value="' . esc_attr( $option_key ) . '" ';
						if( isset($value[$option_key]) ) {
							$options .= 'selected';
						}
						$options .= '>' . esc_attr( $option_text ) . '</option>';

					}else {
						$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
					}


				}
				


					if(empty($options)) {
						$options .= '<option></option>';
					}

				$field .= '<select '. $disable .' name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="form-control m-input ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
				' . $options . '
				</select>';
			}

			break;
			case 'checkbox_list':
				$label_id = current( array_keys( $args['options'] ) );
				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						$input = '<input type="checkbox" class="input-checkbox '. esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="'. esc_attr( $option_key ) . '" name="' . esc_attr( $key ) .'" '. implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) .'_'. esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) .' />';
						$field .= '<label for="' . esc_attr( $args['id'] ) .'_'. esc_attr( $option_key ) .'" class="checkbox ' . implode( ' ', $args['label_class'] ) .'">'. $input . $option_text . '</label>';
					}
				}
				break;
			case 'checkbox':
				$input = '<input '. $disable .' type="checkbox" class="input-checkbox '. esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="'. esc_attr( $args['default'] ) .'" name="' . esc_attr( $key ) .'" '. implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '"' . checked( $value, $args['default'], false ) .' />';
				$field .= '<label class="m-checkbox">'.  $input .' '. wp_kses_post( $args['description'] ). '<span></span></label>';
				break;
			case 'radio':
			$label_id = current( array_keys( $args['options'] ) );

			if ( ! empty( $args['options'] ) ) {
				foreach ( $args['options'] as $option_key => $option_text ) {
					$input = '<input type="radio" class="input-radio '. esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="'. esc_attr( $option_key ) . '" name="' . esc_attr( $key ) .'" '. implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) .'_'. esc_attr( $option_key ) . '"' . checked( $value, $option_key, false ) .' />';
					$field .= '<label for="' . esc_attr( $args['id'] ) .'_'. esc_attr( $option_key ) .'" class="radio ' . implode( ' ', $args['label_class'] ) .'">'. $input . $option_text . '</label>';
				}
			}

			break;
			case 'switch':
				$field .= '<span class="m-switch '. esc_attr( implode( ' ', $args['input_class'] ) ) . '">';
				$field .= '<label><input type="checkbox" name="' . esc_attr( $key ) .'" value="true" '. ($value ? 'checked="checked"' : '') .' /><span></span></label>';
				$field .= '</span>';
			break;
			
			
			case 'number':
				$attr = array();
				if( ! empty($args['custom_attributes']) ) {
					foreach( $args['custom_attributes'] as $key_attr => $val_attr) {
						$attr[] = 'min="'. $val_attr .'"';
					}
				}
				$attr = implode( ' ', $attr);
				
				$field .= '<input value="'. $value .'" name="' . esc_attr( $key ) .'" class="form-control m-input '. esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) .'" type="number"'. $attr .'>';
				break;
				
			case 'icon':
				$field .= '<div class="woopanel-icon-group"><span class="woopanel-input-group-addon"><i class="fa fa-'. $args['icon'] .'"></i></span><input value="'. $value .'" name="' . esc_attr( $key ) .'" class="form-control m-input '. esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) .'" placeholder="'. $args['placeholder'].'" type="text"></div>';
				break;
		}

		if ( ! empty( $args['type'] ) && $args['type']=='image' ) {
			if($args['form_inline']) {
				$args['label_class'][] = 'col-3 col-form-label';
				echo '<div class="form-group m-form__group type-image row" id="'. esc_attr( $label_id ) .'_field" data-priority="">';
			}else {
				echo '<div class="form-group m-form__group type-image" id="'. esc_attr( $label_id ) .'_field" data-priority="">';
			}

			echo '<label for="'. esc_attr( $label_id ) .'" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			if($args['form_inline']) {
				echo '<div class="col-9">';
			}else {
				echo '<div class="image-wrapper">';
			}

			woopanel_attachment_image( $value, true, false, esc_attr($key), $args['size'] );
			echo '</div></div>';

			//
		} elseif ( ! empty( $args['type'] ) && $args['type']=='wpeditor' ) {	
			echo '<div class="form-group m-form__group" id="'. esc_attr( $label_id ) .'_field">';		
			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				echo '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
			}
			echo '<div class="field-wrapper">';

			$defaults = array(
				'wpautop' => true,
				'media_buttons' => true,
				'textarea_name' => esc_attr( $key ),
				'textarea_rows' => get_option('default_post_edit_rows', 10),
				'tabindex' => '',
				'editor_css' => '',
				'editor_class'  => esc_attr( implode( ' ', $args['input_class'] ) ),
				'editor_height' => '',
				'teeny' => false,
				'dfw' => false,
				'tinymce' => true,
				'quicktags' => true,
				'drag_drop_upload' => false
			);
			$settings  = wp_parse_args($args['settings'], $defaults);
			wp_editor( $value, esc_attr( $args['id'] ), $settings);

			if ( $args['description'] ) {
				echo '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
			}
			echo '</div>';
			echo '</div>';
		} elseif ( ! empty( $args['type'] ) && $args['type']=='heading' ) {
		    echo '<div class="m-form__heading"><h3 class="m-form__heading-title">'. $args['label'] .'</h3></div>';
        } else {
			if ( ! empty( $field ) ) {
				$field_html = '';

				if ( $args['label'] ) {
					if($args['form_inline']) $args['label_class'][] = 'col-3 col-form-label';

					$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required . '</label>';
				}

				if($args['form_inline']) {
					if( $args['type'] == 'datepicker' ) {
						$field_html .= '<div class="input-group date col-9">';
					}else {
						if ( $args['label'] ) {
							$field_html .= '<div class="col-9">';
						}else {
							$field_html .= '<div class="col-12">';
						}
					}
				}

				$field_html .= $field;

				if ( $args['description'] && $args['type'] !== 'checkbox') {
					$field_html .= '<span class="m-form__help" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
				}

				if($args['form_inline']) $field_html .= $args['wrapper_after'] . '</div>';

				if($args['form_inline']) $args['class'][] = 'row';
				$container_class = esc_attr( implode( ' ', $args['class'] ) );
				$container_id    = esc_attr( $args['id'] ) . '_field';
				$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
			}

			/**
			 * Filter by type.
			 */
			$field = apply_filters( 'woopanel_form_field_' . $args['type'], $field, $key, $args, $value );

			/**
			 * General filter on form fields.
			 *
			 * @since 3.4.0
			 */
			$field = apply_filters( 'woopanel_form_field', $field, $key, $args, $value );

			if ( $args['return'] ) {
				return $field;
			} else {
				echo $field; // WPCS: XSS ok.
			}
		}
	}
}