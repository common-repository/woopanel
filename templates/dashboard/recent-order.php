<!--begin:: Widgets/Sale Reports-->
<div class="m-portlet m-portlet--full-height recent-order-wrapper">
	<div class="m-portlet__head">
		<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
				<h3 class="m-portlet__head-text">
					<?php _e( 'Recent Orders', 'woopanel' );?>
				</h3>
				<span class="m-portlet__head-desc">Total invoices <?php echo empty($total_orders) ? 0 : $total_orders;?>, unpaid <?php echo empty($total_unpaid_order) ? 0 : $total_unpaid_order;?></span>
			</div>
		</div>
		<div class="m-portlet__head-tools">
			<ul class="nav nav-pills nav-pills--brand m-nav-pills--align-right m-nav-pills--btn-pill m-nav-pills--btn-sm" role="tablist">
				<li class="nav-item m-tabs__item">
					<a class="nav-link m-tabs__link active" href="<?php echo woopanel_dashboard_url('orders/');?>">
						<?php _e( 'View all', 'woopanel' );?>
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div class="m-portlet__body">

		<!--Begin::Tab Content-->
		<div class="tab-content">

			<!--begin::tab 1 content-->
			<div class="tab-pane active" id="m_widget11_tab1_content">

				<!--begin::Widget 11-->
				<div class="m-widget11">
					<div class="table-responsive">
						<?php if( ! empty($recent_orders) ) {?>
						<!--begin::Table-->
						<table class="table">

							<!--begin::Thead-->
							<thead>
								<tr>
									<td class="m-widget11__label dashboard-col-order"><?php _e( 'Order', 'woocommerce' );?></td>
									<td class="m-widget11__app"><?php _e( 'Date', 'woocommerce' );?></td>
									<td class="m-widget11__price"><?php _e( 'Status', 'woocommerce' );?></td>
									<td class="m-widget11__total m--align-right"><?php _e( 'Amount', 'woopanel' );?></td>
								</tr>
							</thead>

							<!--end::Thead-->

							<!--begin::Tbody-->
							<tbody>
								<?php
								global $post;
								foreach ( $recent_orders as $k => $order ) {
									$order = wc_get_order($order['ID']);
									$order_data = $order->get_data();
									?>
								<tr>
									<td class="dashboard-col-order">
                                        <?php
                                        if( method_exists($order,'get_formatted_billing_full_name') ) {
                                            $buyer = $order->get_formatted_billing_full_name();
                                            if( strlen($buyer) <= 1 ) {
                                                $buyer = __( 'Guest', 'woocommerce' );
                                            }
                                        }

                                        echo '<a href="' . esc_url( woopanel_post_edit_url( $order->get_id()) ) . '" class="order-view">';
                                        echo '<strong>#' . esc_attr( $order->get_order_number() ) . sprintf( ' ' . __( 'by %s', 'woocommerce' ), esc_html( $buyer ) ) . '</strong>';
                                        echo '</a>';
                                        ?>
                                    </td>
									<td>
                                        <?php echo date_i18n( get_option( 'date_format' ), strtotime($order->get_date_created()));?>
                                    </td>
									<td>
                                        <span class="m-badge m-badge--brand m-badge--wide <?php echo $woopanel_order_status["wc-{$order_data['status']}"]['color'];?>">
                                            <?php echo woopanel_get_order_status($order_data['status']);?>
                                        </span>
                                    </td>
									<td class="m--align-right m--font-brand"><?php echo $order->get_formatted_order_total();?></td>
								</tr>
								<?php }?>
							</tbody>
							<!--end::Tbody-->
						</table>
						<!--end::Table-->
						<?php } else {?>
							<div class="dashboard-block-empty">
								<i class="fa flaticon-clipboard"></i>
								<h3>Your Order List Is Empty</h3>
								<p>No orders matching your search criteria.</p>
							</div>
							<?php
						}?>
					</div>
				</div>

				<!--end::Widget 11-->
			</div>

			<!--end::tab 1 content-->

			<!--begin::tab 2 content-->
			<div class="tab-pane" id="m_widget11_tab2_content">

				<!--begin::Widget 11-->
				<div class="m-widget11">
					<div class="table-responsive">

						<!--begin::Table-->
						<table class="table">

							<!--begin::Thead-->
							<thead>
								<tr>
									<td class="m-widget11__label">#</td>
									<td class="m-widget11__app">Application</td>
									<td class="m-widget11__sales">Sales</td>
									<td class="m-widget11__change">Change</td>
									<td class="m-widget11__price">Avg Price</td>
									<td class="m-widget11__total m--align-right">Total</td>
								</tr>
							</thead>

							<!--end::Thead-->

							<!--begin::Tbody-->
							<tbody>
								<tr>
									<td>
										<label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand">
											<input type="checkbox"><span></span>
										</label>
									</td>
									<td>
										<span class="m-widget11__title">Loop</span>
										<span class="m-widget11__sub">CRM System</span>
									</td>
									<td>19,200</td>
									<td>$63</td>
									<td>$11,300</td>
									<td class="m--align-right m--font-brand">$34,740</td>
								</tr>
								<tr>
									<td>
										<label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand"><input type="checkbox"><span></span></label>
									</td>
									<td>
										<span class="m-widget11__title">Selto</span>
										<span class="m-widget11__sub">Powerful Website Builder</span>
									</td>
									<td>24,310</td>
									<td>$39</td>
									<td>$14,700</td>
									<td class="m--align-right m--font-brand">$46,010</td>
								</tr>
								<tr>
									<td>
										<label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand"><input type="checkbox"><span></span></label>
									</td>
									<td>
										<span class="m-widget11__title">Jippo</span>
										<span class="m-widget11__sub">The Best Selling App</span>
									</td>
									<td>9,076</td>
									<td>$105</td>
									<td>$8,400</td>
									<td class="m--align-right m--font-brand">$67,800</td>
								</tr>
								<tr>
									<td>
										<label class="m-checkbox m-checkbox--solid m-checkbox--single m-checkbox--brand"><input type="checkbox"><span></span></label>
									</td>
									<td>
										<span class="m-widget11__title">Verto</span>
										<span class="m-widget11__sub">Web Development Tool</span>
									</td>
									<td>11,094</td>
									<td>$16</td>
									<td>$12,500</td>
									<td class="m--align-right m--font-brand">$18,520</td>
								</tr>
							</tbody>

							<!--end::Tbody-->
						</table>

						<!--end::Table-->
					</div>
					<div class="m-widget11__action m--align-right">
						<button type="button" class="btn m-btn--pill btn-outline-brand m-btn m-btn--custom">Generate Report</button>
					</div>
				</div>

				<!--end::Widget 11-->
			</div>

			<!--end::tab 2 content-->

			<!--begin::tab 3 content-->
			<div class="tab-pane" id="m_widget11_tab3_content">
			</div>

			<!--end::tab 3 content-->
		</div>

		<!--End::Tab Content-->
	</div>
</div>

<!--end:: Widgets/Sale Reports-->