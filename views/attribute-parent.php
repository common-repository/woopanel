<div class="m-portlet__head">
    <div class="m-portlet__head-caption">
        <div class="m-portlet__head-title">
            <h3 class="m-portlet__head-text"><?php echo $label; ?></h3>
        </div>
    </div>
</div>

<div class="m-portlet__body m-attribute-wrapper">
    <form class="m-form" action="" method="POST">
        <p><?php esc_html_e( 'Attributes let you define extra product data, such as size or color. You can use these attributes in the shop sidebar using the "layered nav" widgets.', 'woocommerce' ); ?></p>
        <?php wp_nonce_field( 'add_taxonomy', 'tax_' . $this->taxonomy ); ?>
        <?php
        woopanel_form_field(
            'attribute_name', 
            [
                'type'			=> 'text',
                'label'	=> __('Name'),
                'id'			=> 'attribute_name',
                'desc_tip'    => 'true',
                'description'   => __( 'Name for the attribute (shown on the front-end).' )
            ],
            $attribute->attribute_label
        );

        if ( ! global_terms_enabled() ) {
            woopanel_form_field(
                'attribute_slug', 
                [
                    'type'			=> 'text',
                    'label'	=> __('Slug'),
                    'id'			=> 'attribute_slug',
                    'desc_tip'    => 'true',
                    'description'   => __( 'Unique slug/reference for the attribute; must be no more than 28 characters.' )
                ],
                $attribute->attribute_name
            );
        }

        if ( wc_has_custom_attribute_types() ) {
            woopanel_form_field(
                'attribute_type',
                array(
                    'type'	  => 'select',
                    'id'      => 'attribute_type',
                    'label'   => __( 'Type', 'woocommerce' ),
                    'options' => wc_get_attribute_types(),
                    'description' => esc_html__( "Determines how this attribute's values are displayed.", 'woocommerce' )
                ),
                $attribute->attribute_type
            );
        }


        if( $this->edit ) {
            do_action('woocommerce_after_edit_attribute_fields');
        }else {
            do_action('woocommerce_after_add_attribute_fields');
        }
        
        woopanel_form_field(
            'attribute_orderby',
            array(
                'type'	  => 'select',
                'id'      => 'attribute_orderby',
                'label'   => __( 'Default sort order', 'woocommerce' ),
                'options' => array(
                    'menu_order'=> esc_html__( 'Custom ordering', 'woocommerce' ),
                    'name'      => esc_html__( 'Name', 'woocommerce' ),
                    'name_num'  => esc_html__( 'Name (numeric)', 'woocommerce' ),
                    'id'        => esc_html__( 'Term ID', 'woocommerce' )
                ),
                'description' => esc_html__( 'Determines the sort order of the terms on the frontend shop product pages. If using custom ordering, you can drag and drop the terms in this attribute.', 'woocommerce' )
            ),
            $attribute->attribute_orderby
        );
        ?>
        <div class="btn-attribute-wrapper">
            <button type="submit" name="submit" class="btn btn-primary m-btn m-loader--light m-loader--right" id="publish" onclick="if(!this.classList.contains('m-loader')) this.className+=' m-loader';"><?php
            if( $this->edit ) {
                _e('Update');
            }else {
                esc_html_e( 'Add attribute', 'woocommerce' );
            }?></button>
        </div>

    </form>
</div>