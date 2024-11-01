<div class="m-portlet" id="product_data_portlet">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<h3 class="m-portlet__head-text">
    					<?php _e( 'Product data', 'woocommerce' ); ?>
					</h3>
				</div>
				<div class="m-portlet__head-tools">
					<ul class="m-portlet__nav">
						<li class="m-product_type m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-left m-dropdown--align-push" m-dropdown-toggle="hover">
							<a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn-sm  btn-metal m-btn m-btn--pill">
								<?php
								$wc_get_product_types = wc_get_product_types();
								if( ! empty($product_object) ) {
									echo $wc_get_product_types[$product_object->get_type()];
								}else {
									esc_attr_e( 'Product Type', 'woocommerce' );
								}?>
							</a>
							<input type="hidden" id="input_product_type" name="product_type" value="<?php echo $product_object->get_type();?>" />
							<div class="m-dropdown__wrapper">
								<span class="m-dropdown__arrow m-dropdown__arrow--left m-dropdown__arrow--adjust"></span>
								<div class="m-dropdown__inner">
									<div class="m-dropdown__body">
										<div class="m-dropdown__content">
											<ul class="m-nav" id="m-dropdown-product_type">
												<?php foreach ( $wc_get_product_types as $value => $label ) : ?>
												<li class="m-nav__item <?php if( ! empty($product_object) && $product_object->get_type() == $value) { echo ' m-nav__item--active';}?>" data-value="<?php echo esc_attr( $value ); ?>">
													<a href="#woopanel-product-<?php echo esc_attr($value);?>" class="m-nav__link">
														<i class="m-nav__link-icon wcicon-<?php echo esc_attr($value);?>"></i>
														<span class="m-nav__link-text"><?php echo esc_html( $label ); ?></span>
													</a>
												</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</li>
					</ul>


					<div class="panel-options">
						<?php
						foreach ( self::get_product_type_options() as $key => $option ) :
							if ( isset($post_id) && metadata_exists( 'post', $post_id, '_' . $key ) ) {
								$selected_value = is_callable( array( $product_object, "is_$key" ) ) ? $product_object->{"is_$key"}() : 'yes' === get_post_meta( $post_id, '_' . $key, true );
							} else {
								$selected_value = 'yes' === ( isset( $option['default'] ) ? $option['default'] : 'no' );
							}
							?>
							<label for="<?php echo esc_attr( $option['id'] ); ?>" class="m-checkbox <?php echo esc_attr( $option['wrapper_class'] ); ?>" data-toggle="tooltip" data-original-title="<?php echo esc_attr( $option['description'] ); ?>">
								<input type="checkbox" name="<?php echo esc_attr( $option['id'] ); ?>" id="<?php echo esc_attr( $option['id'] ); ?>" class="input-checkbox" style="display: none" value="yes" <?php echo checked( $selected_value, true, false ); ?> />
								<?php echo esc_html( $option['label'] ); ?><span style="background: #fff;"></span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>

			</div>
		</div>
		<div class="m-portlet__body">
			<div class="row">
				<div id="m-portlet__tableft" class="col-xl-3">
					<div class="m-tabs" data-tabs="true" data-tabs-contents="#m_sections">
						<ul class="m-nav m-nav--active-bg m-nav--active-bg-padding-lg m-nav--font-lg m-nav--font-bold m--margin-bottom-20 m--margin-top-10 m--margin-right-40" id="product_data_tabs" role="tablist">
							<?php foreach( self::get_product_data_tabs() as $key => $tab) {?>
							<li class="m-nav__item <?php echo esc_attr( $key ); ?>_options <?php echo esc_attr( $key ); ?>_tab <?php echo esc_attr( isset( $tab['class'] ) ? implode( ' ', (array) $tab['class'] ) : '' ); ?>">
								<a class="m-nav__link m-tabs__item" data-tab-target="#<?php echo esc_attr( $tab['target'] ); ?>" href="#<?php echo esc_attr( $tab['target'] ); ?>">
									<span class="m-nav__link-text"><?php echo esc_html( $tab['label'] ); ?></span>
								</a>
							</li>
							<?php }?>
							<?php do_action( 'woopanel_product_write_panel_tabs' ); ?>
						</ul>
					</div>
				</div>
				<div id="m-portlet__tabright" class="col-xl-9">
					<div class="m-tabs-content" id="m_sections">
						<?php
						self::output_tabs();
						self::output_variations();
						do_action( 'woocommerce_product_data_panels' );?>
					</div>
				</div>
			</div>
		</div>
	</div>