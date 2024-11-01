<div class="m-portlet__head">
    <div class="m-portlet__head-caption">
        <div class="m-portlet__head-title">
            <h3 class="m-portlet__head-text"><?php echo $label; ?></h3>
        </div>
    </div>
</div>

<div class="m-portlet__body m-attribute-wrapper <?php echo ! empty($this->edit) ? 'm-attribute-edit' : 'm-attribute-add';?>">
    <form class="m-form" action="" method="POST">
        <?php
		echo wp_kses(
			wpautop( __( 'Attribute terms can be assigned to products and variations.<br/><br/><b>Note</b>: Deleting a term will remove it from all products and variations to which it has been assigned. Recreating a term will not automatically assign it back to products.', 'woocommerce' ) ),
			array( 'p' => array() )
        );
        ?>
        <?php wp_nonce_field( 'add_taxonomy', 'tax_' . $this->taxonomy ); ?>
        <?php
        woopanel_form_field(
            'term_name', 
            [
                'type'			=> 'text',
                'label'	=> __('Name'),
                'id'			=> 'term_name',
                'desc_tip'    => 'true',
                'description'   =>  __( 'The name is how it appears on your site.' )
            ],
            $term->name
        );

        if ( ! global_terms_enabled() ) {
            woopanel_form_field(
                'term_slug', 
                [
                    'type'			=> 'text',
                    'label'	=> __('Slug'),
                    'id'			=> 'term_slug',
                    'desc_tip'    => 'true',
                    'description'   => __( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' )
                ],
                $term->slug
            );
        }

        woopanel_form_field(
            'term_description', 
            [
                'type'			=> 'textarea',
                'label'	=> __('Description'),
                'id'			=> 'term_description',
                'desc_tip'    => 'true',
                'description'   => __( 'The description is not prominent by default; however, some themes may show it.' )
            ],
            $term->description
        );

        if( $this->edit ) {
            do_action($this->taxonomy . '_edit_form_fields', $term, $this->taxonomy);
        }else {
            do_action($this->taxonomy . '_add_form_fields', $this->taxonomy);
        }

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