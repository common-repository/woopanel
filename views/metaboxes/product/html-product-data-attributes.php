<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="product_attributes" class="wc-metaboxes-wrapper m-tabs-content__item">
	<div class="m-portlet m-portlet--full-height">
		<div class="m-portlet__head m-toolbar">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<select name="attribute_taxonomy" class="attribute_taxonomy form-control m-input">
						<option value=""><?php esc_html_e( 'Custom product attribute', 'woocommerce' ); ?></option>
						<?php
						global $wc_product_attributes;

						// Array of defined attribute taxonomies.
						$attribute_taxonomies = wc_get_attribute_taxonomies();
						if ( ! empty( $attribute_taxonomies ) ) {
							foreach ( $attribute_taxonomies as $tax ) {
								$attribute_taxonomy_name = wc_attribute_taxonomy_name( $tax->attribute_name );
								$label                   = $tax->attribute_label ? $tax->attribute_label : $tax->attribute_name;
								echo '<option value="' . esc_attr( $attribute_taxonomy_name ) . '">' . esc_html( $label ) . '</option>';
							}
						}
						?>
					</select>
					<button type="button" class="btn btn-default m-btn btn-add-attribute"><?php esc_html_e( 'Add', 'woocommerce' ); ?></button>

				</div>
			</div>
		</div>
		<div class="m-portlet__body">
			<!--begin::Section-->
			<div class="product_attributes wc-metaboxes m-accordion m-accordion--bordered" id="m_accordion_2" role="tablist">
				<?php
				// Product attributes - taxonomies and custom, ordered, with visibility and variation attributes set.
				$attributes = $product_object->get_attributes( 'edit' );
				$i          = -1;

				foreach ( $attributes as $attribute ) {
					$i++;
					$metabox_class = array();

					if ( $attribute->is_taxonomy() ) {
						$metabox_class[] = 'taxonomy';
						$metabox_class[] = $attribute->get_name();
					}


					$selected_value = array();
					if ( $attribute->is_taxonomy() && ( $attribute_taxonomy = $attribute->get_taxonomy_object() ) ) {
						foreach( $attribute->get_terms() as $key => $value) {
							$selected_value[$value->term_id] = $value->name;
						}
					}

					include WOOPANEL_VIEWS_DIR . 'metaboxes/product/html-product-attribute.php';
				}
				?>
			</div>

			<!--end::Section-->
		</div>
	</div>

	<button type="button" class="btn btn-primary m-btn save_attributes button-primary btn-save-attribute">
	<?php
	if( isset($_GET['id']) ) {
		esc_html_e( 'Save attributes', 'woocommerce' );
		
	}else {
		esc_html_e( 'Save attributes & Publish', 'woopanel' );
	}
	?></button>
	<?php do_action( 'woocommerce_product_options_attributes' ); ?>
</div>
