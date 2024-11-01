<div class="taxonomy-lists taxonomy-<?php echo esc_attr($this->taxonomy);?>">
    <div class="row">
        <div class="col col-4">
            <div class="m-portlet">
                <?php
                $label = '';
                if( $this->taxonomy ) {
                    if( $this->edit ) {
                        $label = sprintf( __( 'Edit %s', 'woocommerce' ), $term->name );
                    }else {
                        $label = sprintf( __( 'Add new %s', 'woocommerce' ), $attribute->attribute_label );
                    }

                    include_once WOOPANEL_VIEWS_DIR . 'attribute-child.php';	
                }else {
                    if( $this->edit ) {
                        $label = esc_html__( 'Edit attribute', 'woocommerce' );;
                    }else {
                        $label = esc_html__( 'Add new attribute', 'woocommerce' );
                    }

                    include_once WOOPANEL_VIEWS_DIR . 'attribute-parent.php';	
                }?>
            </div>
        </div>

        <div class="col col-8">
            <?php $this->display();?>
        </div>
    </div>
</div>