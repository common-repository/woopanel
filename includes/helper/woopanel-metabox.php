<?php
/**
 * Output a select input box.
 *
 * @param array $field Data about the field to render.
 */
if( ! function_exists('woopanel_wp_select') ) {
    function woopanel_wp_select( $field ) {
        global $thepostid, $post;

        $thepostid = empty( $thepostid ) ? $post->ID : $thepostid;
        $field     = wp_parse_args(
            $field, array(
                'class'             => 'form-control m-input col-9',
                'style'             => '',
                'wrapper_class'     => '',
                'value'             => get_post_meta( $thepostid, $field['id'], true ),
                'name'              => $field['id'],
                'desc_tip'          => false,
                'custom_attributes' => array(),
            )
        );

        $wrapper_attributes = array(
            'class' => $field['wrapper_class'] . " form-group m-form__group row type-{$field['id']}",
        );

        $label_attributes = array(
            'for' => $field['id'],
            'class' => 'col-3 col-form-label'
        );

        $field_attributes          = (array) $field['custom_attributes'];
        $field_attributes['style'] = $field['style'];
        $field_attributes['id']    = $field['id'];
        $field_attributes['name']  = $field['name'];
        $field_attributes['class'] = $field['class'];

        $tooltip     = ! empty( $field['description'] ) && false !== $field['desc_tip'] ? $field['description'] : '';
        $description = ! empty( $field['description'] ) && false === $field['desc_tip'] ? $field['description'] : '';
        ?>

        <div <?php echo wc_implode_html_attributes( $wrapper_attributes ); // WPCS: XSS ok. ?> id="<?php echo $field_attributes['id'];?>_field" data-priority="">
            <label <?php echo wc_implode_html_attributes( $label_attributes ); // WPCS: XSS ok. ?>><?php echo wp_kses_post( $field['label'] ); ?></label>
            <?php if ( $tooltip ) : ?>
                <?php echo wc_help_tip( $tooltip ); // WPCS: XSS ok. ?>
            <?php endif; ?>
            <select <?php echo wc_implode_html_attributes( $field_attributes ); // WPCS: XSS ok. ?>>
                <?php
                foreach ( $field['options'] as $key => $value ) {
                    echo '<option value="' . esc_attr( $key ) . '"' . wc_selected( $key, $field['value'] ) . '>' . esc_html( $value ) . '</option>';
                }
                ?>
            </select>
            <?php if ( $description ) : ?>
                <span class="description"><?php echo wp_kses_post( $description ); ?></span>
            <?php endif; ?>
		</div>
        <?php
    }
}


/**
 * Output a text input box.
 *
 * @param array $field
 */
if( ! function_exists('woopanel_wp_text_input') ) {
    function woopanel_wp_text_input( $field ) {
        global $thepostid, $post;

        $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
        $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
        $field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
        $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
        $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
        $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
        $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
        $field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
        $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;
        $data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

        switch ( $data_type ) {
            case 'price':
                $field['class'] .= ' wc_input_price';
                $field['value']  = wc_format_localized_price( $field['value'] );
                break;
            case 'decimal':
                $field['class'] .= ' wc_input_decimal';
                $field['value']  = wc_format_localized_decimal( $field['value'] );
                break;
            case 'stock':
                $field['class'] .= ' wc_input_stock';
                $field['value']  = wc_stock_amount( $field['value'] );
                break;
            case 'url':
                $field['class'] .= ' wc_input_url';
                $field['value']  = esc_url( $field['value'] );
                break;

            default:
                break;
        }

        // Custom attribute handling
        $custom_attributes = array();

        if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

            foreach ( $field['custom_attributes'] as $attribute => $value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
            }
        }

        echo '<div class="form-group m-form__group row ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
            <label for="' . esc_attr( $field['id'] ) . '" class="col-3 col-form-label">' . wp_kses_post( $field['label'] ) . '</label>';

        if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
            echo wc_help_tip( $field['description'] );
        }

        echo '<input type="' . esc_attr( $field['type'] ) . '" class="form-control m-input col-9 ' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . ' /> ';

        if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
            echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
        }

        echo '</div>';
    }
}


/**
 * Output a text input box.
 *
 * @param array $field
 */
if( ! function_exists('woopanel_wp_datepicker') ) {
    function woopanel_wp_datepicker( $field ) {
        global $thepostid, $post;

        $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
        $field['placeholder']   = isset( $field['placeholder'] ) ? $field['placeholder'] : '';
        $field['class']         = isset( $field['class'] ) ? $field['class'] : 'short';
        $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
        $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
        $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
        $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
        $field['type']          = isset( $field['type'] ) ? $field['type'] : 'text';
        $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;
        $data_type              = empty( $field['data_type'] ) ? '' : $field['data_type'];

        switch ( $data_type ) {
            case 'price':
                $field['class'] .= ' wc_input_price';
                $field['value']  = wc_format_localized_price( $field['value'] );
                break;
            case 'decimal':
                $field['class'] .= ' wc_input_decimal';
                $field['value']  = wc_format_localized_decimal( $field['value'] );
                break;
            case 'stock':
                $field['class'] .= ' wc_input_stock';
                $field['value']  = wc_stock_amount( $field['value'] );
                break;
            case 'url':
                $field['class'] .= ' wc_input_url';
                $field['value']  = esc_url( $field['value'] );
                break;

            default:
                break;
        }

        // Custom attribute handling
        $custom_attributes = array();

        if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

            foreach ( $field['custom_attributes'] as $attribute => $value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
            }
        }

        echo '<div class="form-group m-form__group row ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
            <label for="' . esc_attr( $field['id'] ) . '" class="col-3 col-form-label">' . wp_kses_post( $field['label'] ) . '</label>';

        if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
            echo wc_help_tip( $field['description'] );
        }

        echo '<div class="input-group date col-9" style="padding: 0;">
                <input type="' . esc_attr( $field['type'] ) . '" class="form-control m-input m-datepicker ' . esc_attr( $field['class'] ) . '" readonly="" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['value'] ) . '" placeholder="' . esc_attr( $field['placeholder'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
                <div class="input-group-append">
                    <span class="input-group-text">
                        <i class="la la-calendar-check-o"></i>
                    </span>
                </div>
            </div>';

        echo '</div>';
    }
}


/**
 * Output a checkbox input box.
 *
 * @param array $field
 */
if( ! function_exists('woopanel_wp_checkbox') ) {
    function woopanel_wp_checkbox( $field ) {
        global $thepostid, $post;

        $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
        $field['class']         = isset( $field['class'] ) ? $field['class'] : 'checkbox';
        $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
        $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
        $field['value']         = isset( $field['value'] ) ? $field['value'] : get_post_meta( $thepostid, $field['id'], true );
        $field['cbvalue']       = isset( $field['cbvalue'] ) ? $field['cbvalue'] : 'yes';
        $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
        $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;

        // Custom attribute handling
        $custom_attributes = array();

        if ( ! empty( $field['custom_attributes'] ) && is_array( $field['custom_attributes'] ) ) {

            foreach ( $field['custom_attributes'] as $attribute => $value ) {
                $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $value ) . '"';
            }
        }

        echo '<div class="form-group m-form__group row ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
            <label for="' . esc_attr( $field['id'] ) . '" class="col-3 col-form-label">' . wp_kses_post( $field['label'] ) . '</label>';

        if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
            echo wc_help_tip( $field['description'] );
        }

        echo '<div class="m-checkbox-list col-9"><label class="m-checkbox"><input type="checkbox" class="' . esc_attr( $field['class'] ) . '" style="' . esc_attr( $field['style'] ) . '" name="' . esc_attr( $field['name'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="' . esc_attr( $field['cbvalue'] ) . '" ' . checked( $field['value'], $field['cbvalue'], false ) . '  ' . implode( ' ', $custom_attributes ) . '/>';
        if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
            echo wp_kses_post( $field['description'] );
        }
        echo '<span></span></label></div>';

        echo '</div>';
    }
}