<?php
/**
 * Product data variations
 *
 * @package WooCommerce\Admin\Metaboxes\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="variable_product_options" class="m-tabs-content__item">
	<?php if ( ! count( $variation_attributes ) ) { ?>
		<div class="m-alert m-alert--outline alert alert-success alert-dismissible fade show" role="alert">
			<p><?php echo wp_kses_post( __( 'Before you can add a variation you need to add some variation attributes on the <strong>Attributes</strong> tab.', 'woocommerce' ) ); ?></p>
			<p><a class="btn btn-success btn-sm" href="<?php echo esc_url( apply_filters( 'woocommerce_docs_url', 'https://docs.woocommerce.com/document/variable-product/', 'product-variations' ) ); ?>" target="_blank"><?php esc_html_e( 'Learn more', 'woocommerce' ); ?></a></p>
		</div>
	<?php } else { ?>
	<div id="variable_product_options_inner" class="m-portlet m-portlet--full-height">
		<div class="m-portlet__head m-toolbar<?php if( count($variation_attributes) > 3 ) { echo ' m-variation-break-select';}?>">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<div class="row variations-defaults">
						<div class="col-variation col-variation-label">
							<strong><?php esc_html_e( 'Default Form Values', 'woocommerce' ); ?>: <?php echo wc_help_tip( __( 'These are the attributes that will be pre-selected on the frontend.', 'woocommerce' ) ); ?></strong>
						</div>
						
						<?php
						foreach ( $variation_attributes as $attribute ) {
							$selected_value = isset( $default_attributes[ sanitize_title( $attribute->get_name() ) ] ) ? $default_attributes[ sanitize_title( $attribute->get_name() ) ] : '';
							?>
							<div class="col-variation">
								<select class="form-control m-input" name="default_attribute_<?php echo esc_attr( sanitize_title( $attribute->get_name() ) ); ?>" data-current="<?php echo esc_attr( $selected_value ); ?>">
									<?php /* translators: WooCommerce attribute label */ ?>
									<option value=""><?php echo esc_html( sprintf( __( 'No default %s&hellip;', 'woocommerce' ), wc_attribute_label( $attribute->get_name() ) ) ); ?></option>
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
					</div>
				</div>
			</div>
		</div>

		<div class="m-portlet__head m-toolbar toolbar-top">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<select id="field_to_edit" class="variation_actions form-control m-input">
						<option data-global="true" value="add_variation" selected="selected"><?php esc_html_e( 'Add variation', 'woocommerce' ); ?></option>
						<option data-global="true" value="link_all_variations"><?php esc_html_e( 'Create variations from all attributes', 'woocommerce' ); ?></option>
						<option value="delete_all"><?php esc_html_e( 'Delete all variations', 'woocommerce' ); ?></option>
					</select>
					<a class="btn btn-default m-btn bulk_edit do_variation_action"><?php esc_html_e( 'Go', 'woocommerce' ); ?></a>
				</div>
			</div>
		</div>
		<div class="m-portlet__body">
			<?php $attributes_data = htmlspecialchars( wp_json_encode( wc_list_pluck( $variation_attributes, 'get_data' ) ) );?>
			<div class="woocommerce_variations wc-metaboxes m-accordion m-accordion--bordered<?php if( count($variation_attributes) > 3 ) { echo ' m-variation-break-select';}?>" data-attributes="<?php echo $attributes_data; // WPCS: XSS ok. ?>" data-total="<?php echo esc_attr( $variations_count ); ?>" data-total_pages="<?php echo esc_attr( $variations_total_pages ); ?>" data-page="1" data-edited="false">
			</div>
		</div>

		<div class="m-portlet__head m-toolbar toolbar-bottom">
			<button type="button" class="btn btn-primary m-btn save_attributes button-primary save-variation-changes" disabled="disabled"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
			<div class="variations-pagenav">
				<?php /* translators: variations count */ ?>
				<span class="displaying-num"><?php echo esc_html( sprintf( _n( '%s item', '%s items', $variations_count, 'woocommerce' ), $variations_count ) ); ?></span>
				<span class="pagination-links">
					<a class="prev-page disabled" title="<?php esc_attr_e( 'Go to the previous page', 'woocommerce' ); ?>" href="#"><i class="la la-angle-double-left"></i></a>
					<span class="paging-select">
						<label for="current-page-selector-1" class="screen-reader-text"><?php esc_html_e( 'Select Page', 'woocommerce' ); ?></label>
						<select class="page-selector form-control m-input" id="current-page-selector-1" title="<?php esc_attr_e( 'Current page', 'woocommerce' ); ?>">
							<?php for ( $i = 1; $i <= $variations_total_pages; $i++ ) : ?>
								<option value="<?php echo $i; // WPCS: XSS ok. ?>"><?php echo $i; // WPCS: XSS ok. ?></option>
							<?php endfor; ?>
						</select>
						<?php echo esc_html_x( 'of', 'number of pages', 'woocommerce' ); ?> <span class="total-pages"><?php echo esc_html( $variations_total_pages ); ?></span>
					</span>
					<a class="next-page" title="<?php esc_attr_e( 'Go to the next page', 'woocommerce' ); ?>" href="#"><i class="la la-angle-double-right"></i></a>
				</span>
			</div>
		</div>
	</div>

	<?php }?>


</div>
