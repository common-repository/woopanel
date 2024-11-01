<!--begin:: Widgets/Best Sellers-->
<div class="m-portlet m-portlet--full-height dashboard-best-products">
	<div class="m-portlet__head">
		<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
				<h3 class="m-portlet__head-text">
					<?php _e( 'Best Products', 'woopanel' );?>
				</h3>
			</div>
		</div>
	</div>
	<div class="m-portlet__body">

		<!--begin::Content-->
		<div class="tab-content">
			<div class="tab-pane active" id="m_widget5_tab1_content" aria-expanded="true">

				<!--begin::m-widget5-->
				<div class="m-widget5">
					<?php
					global $post;
					if ( $best_products->have_posts() ) {
					while ( $best_products->have_posts() ) {
						$best_products->the_post();
						$product = wc_get_product($post->ID);
						?>
					<div class="m-widget5__item">
						<div class="m-widget5__content">
							<div class="m-widget5__pic">
								<?php echo $product->get_image( 'thumbnail' );?>
							</div>
							<div class="m-widget5__section">
								<a href="<?php echo get_permalink($product->get_id());?>" title="<?php echo $product->get_title();?>" target="_blank"><h4 class="m-widget5__title">
									<?php echo $product->get_title();?>
								</h4></a>
								<span class="m-widget5__desc">
									<?php echo wp_trim_words(get_the_excerpt($product->get_id()), 25, '...');?>
								</span>
							</div>
						</div>
						<div class="m-widget5__content">
							<div class="m-widget5__stats1">
								<?php printf(
								        _n( '%s %ssale%s', '%s %ssales%s', get_post_meta( $product->get_id(), 'total_sales', true ), 'woopanel' ),
                                        '<span class="m-widget5__number">'. number_format_i18n( get_post_meta( $product->get_id(), 'total_sales', true ) ) .'</span><br>',
                                        '<span class="m-widget5__sales">',
                                        '</span>' ); ?>
							</div>
						</div>
					</div><?php }
					wp_reset_postdata();
					}else {?>
						<div class="dashboard-block-empty">
							<i class="fa flaticon-bag"></i>
							<h3><?php _e( 'Your Products List Is Empty', 'woopanel' );?></h3>
							<p><?php _e( 'No products matching your search criteria.', 'woopanel' );?></p>
						</div>
					<?php }?>
				</div>

				<!--end::m-widget5-->
			</div>
		</div>

		<!--end::Content-->
	</div>
</div>

