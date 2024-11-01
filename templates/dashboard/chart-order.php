<!--begin::Portlet-->
<div id="chart-order-wrapper" class="m-portlet m-portlet--tab">
	<div class="m-portlet__head">
		<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
				<span class="m-portlet__head-icon m--hide">
					<i class="la la-gear"></i>
				</span>
				<h3 class="m-portlet__head-text">
					<?php _e( 'Chart Orders', 'woopanel' );?>
				</h3>
			</div>
		</div>
		<div class="m-portlet__head-tools">
			<ul class="m-portlet__nav nav nav-pills nav-pills--brand">
				<li class="nav-item m-tabs__item m-tabs-label m-tabs-price">
					<span class="nav-link m-tabs__link active m-chart-label">
						<?php echo $chart_orders['total'];?>
					</span>
				</li>
				<li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push m-chart-status" m-dropdown-toggle="hover">
					<a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
						<?php _e( 'Order Status', 'woopanel' );?>
					</a>
					<div class="m-dropdown__wrapper">
						<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
						<div class="m-dropdown__inner">
							<div class="m-dropdown__body">
								<div class="m-dropdown__content">
									<ul class="m-nav">
										<li class="m-nav__item active" data-value="all">
											<a href="" class="m-nav__link">
												<span class="m-nav__link-text"><?php esc_html_e('All status', 'woopanel');?></span>
											</a>
										</li>
										<?php foreach( $getAllStatus as $k_status => $status_name ) {
											$order_status_key = esc_attr(str_replace('wc-', '', $k_status));?>
										<li class="m-nav__item" data-value="<?php echo $order_status_key;?>">
											<a href="" class="m-nav__link">
												<span class="m-nav__link-text"><?php echo esc_attr($status_name);?></span>
											</a>
										</li>
										<?php }?>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</li>
				<li class="m-portlet__nav-item m-dropdown m-dropdown--inline m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push m-chart-filter" m-dropdown-toggle="hover">
					<a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill btn-secondary m-btn m-btn--label-brand">
						<?php _e( 'Filter Range', 'woopanel' );?>
					</a>
					<div class="m-dropdown__wrapper">
						<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
						<div class="m-dropdown__inner">
							<div class="m-dropdown__body">
								<div class="m-dropdown__content">
									<ul class="m-nav">
										<?php foreach( woopanel_dashboard_filter_range() as $filter_key => $filter_value) {?>
										<li class="m-nav__item<?php echo ($filter_key == $this->chart_default) ? ' active' : false;?>" data-value="<?php echo $filter_key;?>">
											<a href="" class="m-nav__link">
												<span class="m-nav__link-text"><?php echo $filter_value;?></span>
											</a>
										</li>
										<?php }?>
									</ul>
								</div>
							</div>
						</div>
					</div>

					<input type="text" name="datefilter" value="" />
				</li>
			</ul>
		</div>
	</div>
	<?php
		$data_horizontal_json = wp_json_encode($chart_orders['horizontal']);
		$data_horizontal = function_exists( 'wc_esc_json' ) ? wc_esc_json( $data_horizontal_json ) : _wp_specialchars( $data_horizontal_json, ENT_QUOTES, 'UTF-8', true );
		
		$data_vertical_json = wp_json_encode($chart_orders['vertical']);
		$data_vertical = function_exists( 'wc_esc_json' ) ? wc_esc_json( $data_vertical_json ) : _wp_specialchars( $data_vertical_json, ENT_QUOTES, 'UTF-8', true );
	?>
	<div class="m-portlet__body chartorder_body" data-horizontal="<?php echo $data_horizontal; ?>" data-vertical="<?php echo $data_vertical; ?>">
		<canvas id="chartorder-<?php echo $tab_default;?>" height="360" style="width: 100%; height: 360px;"></canvas>
	</div>
</div>
<!--end::Portlet-->