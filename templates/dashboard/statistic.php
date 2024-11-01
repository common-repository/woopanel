<!--begin:: Widgets/Stats-->
<div class="m-portlet ">
	<div class="m-portlet__body  m-portlet__body--no-padding">
		<div class="row m-row--no-padding m-row--col-separator-xl">
			<div class="col-md-12 col-lg-6 col-xl-3">
				<!--begin::Total Profit-->
				<div class="m-widget24">
					<div class="m-widget24__item">
						<h4 class="m-widget24__title" data-toggle="tooltip" title="" data-placement="right" data-original-title="<?php _e( 'This is the sum of the order totals after any refunds and excluding shipping and taxes.', 'woocommerce' );?>">
							<?php _e('Total Revenue', 'woopanel' );?>
						</h4><br>
						<span class="m-widget24__desc">
							<?php _e('Net sales in this period', 'woopanel' );?>
						</span>
						<span class="m-widget24__stats m--font-brand">
							<?php echo woopanel_format_statistic($statistic_total_revenue);?>
						</span>
					</div>
				</div>
				<!--end::Total Profit-->
			</div>

			<div class="col-md-12 col-lg-6 col-xl-3">
				<!--begin::New Feedbacks-->
				<div class="m-widget24">
					<div class="m-widget24__item">
						<h4 class="m-widget24__title">
							<?php _e('All Products', 'woopanel' );?>
						</h4><br>
						<span class="m-widget24__desc">
							<?php _e('All Product Publish', 'woopanel' );?>
						</span>
						<span class="m-widget24__stats m--font-info">
							<?php echo woopanel_format_statistic($statistic_total_products);?>
						</span>
					</div>
				</div>
				<!--end::New Feedbacks-->
			</div>

			<div class="col-md-12 col-lg-6 col-xl-3">
				<!--begin::New Orders-->
				<div class="m-widget24">
					<div class="m-widget24__item">
						<h4 class="m-widget24__title">
							<?php _e('Total Orders', 'woopanel' );?>
						</h4><br>
						<span class="m-widget24__desc">
							<?php _e('All Order Completed', 'woopanel' );?>
						</span>
						<span class="m-widget24__stats m--font-danger">
							<?php echo woopanel_format_statistic($statistic_total_orders);?>
						</span>
					</div>
				</div>
				<!--end::New Orders-->
			</div>

			<div class="col-md-12 col-lg-6 col-xl-3">
				<!--begin::New Users-->
				<div class="m-widget24">
					<div class="m-widget24__item">
						<h4 class="m-widget24__title">
							<?php _e('Total Users', 'woopanel' );?>
						</h4><br>
						<span class="m-widget24__desc">
							<?php _e('Joined New User', 'woopanel' );?>
						</span>
						<span class="m-widget24__stats m--font-success">
							<?php echo woopanel_format_statistic($statistic_total_users);?>
						</span>
					</div>
				</div>
				<!--end::New Users-->
			</div>
			
		</div>
	</div>
</div>
<!--end:: Widgets/Stats-->