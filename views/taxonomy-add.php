<div class="m-portlet__head">
    <div class="m-portlet__head-caption">
        <div class="m-portlet__head-title">
            <h3 class="m-portlet__head-text"><?php echo $this->tax->labels->add_new_item; ?></h3>
        </div>
    </div>
</div>

<div class="m-portlet__body">
    <form class="m-form" action="" method="POST">
        <?php wp_nonce_field( 'add_taxonomy', 'tax_' . $this->taxonomy ); ?>
        <?php
        woopanel_form_field(
            'tax_name', 
            [
                'type'			=> 'text',
                'label'	=> __('Name'),
                'id'			=> 'tax_name',
                'desc_tip'    => 'true',
                'description'   => __( 'The name is how it appears on your site.' )
            ]
        );

        if ( ! global_terms_enabled() ) {
            woopanel_form_field(
                'tax_slug', 
                [
                    'type'			=> 'text',
                    'label'	=> __('Slug'),
                    'id'			=> 'tax_slug',
                    'desc_tip'    => 'true',
                    'description'   => __( 'The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' )
                ]
            );
        }

        woopanel_form_field(
            'tax_description', 
            [
                'type'			=> 'textarea',
                'label'	=> __('Description'),
                'id'			=> 'tax_description',
                'desc_tip'    => 'true',
                'description'   => __( 'The description is not prominent by default; however, some themes may show it.' )
            ]
        );

        if ( is_taxonomy_hierarchical( $this->taxonomy ) ) {
            $this->dropdown_categories();
        }

        if ( ! is_taxonomy_hierarchical( $this->taxonomy ) ) {
            /**
             * Fires after the Add Tag form fields for non-hierarchical taxonomies.
             *
             * @since 3.0.0
             *
             * @param string $taxonomy The taxonomy slug.
             */
            do_action( 'add_tag_form_fields', $this->taxonomy );
        }

        /**
         * Fires after the Add Term form fields.
         *
         * The dynamic portion of the hook name, `$taxonomy`, refers to the taxonomy slug.
         *
         * @since 3.0.0
         *
         * @param string $taxonomy The taxonomy slug.
         */
        do_action( "{$this->taxonomy}_add_form_fields", $this->taxonomy );
        ?>
        <button type="submit" class="btn btn-primary m-btn m-loader--light m-loader--right" id="publish" onclick="if(!this.classList.contains('m-loader')) this.className+=' m-loader';"><?php echo $this->tax->labels->add_new_item;?></button>

    </form>
</div>