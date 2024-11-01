<!--begin:: Widgets/Authors Profit-->
<div class="m-portlet m-portlet--bordered-semi m-portlet--full-height" id="dashboard-new-customer-wrapper">
	<div class="m-portlet__head">
		<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
				<h3 class="m-portlet__head-text">
					<?php _e( 'New Customers', 'woopanel' );?>
				</h3>
			</div>
		</div>
	</div>
	<div class="m-portlet__body">
		<div class="m-widget4">
			<?php
			if( $new_customers ) {
				foreach( $new_customers as $k => $user ) {?>
				<div class="m-widget4__item">
					<div class="m-widget4__img m-widget4__img--logo">
						<img src="<?php echo esc_url( get_avatar_url( $user['ID'] ) ); ?>" alt="<?php echo $user['display_name'];?>">
					</div>
					<div class="m-widget4__info">
						<span class="m-widget4__title">
							<?php echo $user['display_name'];?>
						</span><br>
						<span class="m-widget4__sub">
							<?php echo $user['user_email'];?>
						</span>
					</div>
					<span class="m-widget4__ext dnc-date">
						<span class="m-widget4__number m--font-brand m-date-format"><?php echo date_i18n( get_option( 'date_format' ), strtotime($user['user_registered']) ); ?></span>
					</span>
				</div>
				<?php }
			}else {?>
				<div class="dashboard-block-empty">
					<i class="fa flaticon-user"></i>
                    <h3><?php _e( 'Your Customer List Is Empty', 'woopanel' );?></h3>
                    <p><?php _e( 'No customers matching your search criteria.', 'woopanel' );?></p>
				</div>
			<?php }?>
		</div>
	</div>
</div>

<!--end:: Widgets/Authors Profit-->
