<?php
$user_ID = get_current_user_id();
$nonce_action = 'update_options';

$tabs = array(
	'requests' => array(
		'label' => esc_html__( 'Withdraw Request', 'dokan-lite' ),
		'callback' => 'woopanel_withdraw_request'
	),
	'approved' => array(
		'label' => esc_html__( 'Approved Requests', 'dokan-lite' ),
		'callback' => 'woopanel_withdraw_approved'
	),
	'cancelled' => array(
		'label' => esc_html__( 'Cancelled Requests', 'dokan-lite' ),
		'callback' => 'woopanel_withdraw_cancelled'
	),
);
?>
<form name="post" method="post" id="post" class="m-form">
	<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
	<?php wp_nonce_field($nonce_action); ?>
	<div class="m-portlet" id="main_portlet">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<span class="m-portlet__head-icon">
						<i class="flaticon-coins"></i>
					</span>
					<h3 class="m-portlet__head-text">
    					<?php _e('Withdraw', 'woopanel');?>
					</h3>
				</div>
			</div>
		</div>

		<div class="m-portlet__body withdraw-wrapper">
		
<?php
        $balance        = dokan_get_seller_balance( dokan_get_current_user_id(), true );
        $withdraw_limit = dokan_get_option( 'withdraw_limit', 'dokan_withdraw', -1 );
		?>
			<div class="m-alert m-alert--air m-alert--square alert alert-success m-alert--icon m--margin-bottom-30" role="alert">
				<div class="m-alert__text">
					<ul>
						<li><?php printf( __( 'Current Balance: %s ', 'dokan-lite' ), $balance );?></li>
						<?php
						 if ( $withdraw_limit != -1 ) {
							echo '<li>'. sprintf( __( 'Minimum Withdraw amount: %s ', 'woopanel' ), wc_price( $withdraw_limit ) ).'</li>';
						 }?>
					</ul>
				</div>
			</div>

			<div class="row">
				<div class="col-xl-3">
					<div class="m-tabs" data-tabs="true" data-tabs-contents="#m_sections">
						<ul class="m-nav m-nav--active-bg m-nav--active-bg-padding-lg m-nav--font-lg m-nav--font-bold m--margin-bottom-20 m--margin-top-10 m--margin-right-40" id="m_nav" role="tablist">
							<?php $i=0; foreach ($tabs as $section_id => $section) { ?>
								<li class="m-nav__item">
									<a class="m-nav__link m-tabs__item <?php if($i == 0) echo 'm-tabs__item--active';?>" data-tab-target="<?php echo '#'. $section_id;?>" href="#">
										<span class="m-nav__link-text"><?php echo $section['label'];?></span>
									</a>
								</li>
							<?php $i++;} ?>
						</ul>
					</div>
				</div>

				<div class="col-xl-9">
					<div class="m-tabs-content" id="m_sections">

						<?php $i=0; foreach ($tabs as $section_id => $section) { ?>
						<div class="m-tabs-content__item <?php if($i == 0) echo 'm-tabs-content__item--active';?>" id="<?php echo $section_id;?>">

							<h4 class="m--font-bold m--margin-top-15 m--margin-bottom-20"><?php echo $section['label'];?></h4>
							
							<?php 
							if( ! empty($section['callback']) && function_exists($section['callback']) ) {
								call_user_func($section['callback'], $section_id);
							}?>

						</div>
						<?php $i++;} ?>

					</div>
				</div>
			</div>
		</div>
		
		<div class="m-portlet__foot">
			<div class="row">
				<div class="col-xl-9 offset-xl-3">
					<button type="submit" name="save" class="btn btn-accent m-btn m-btn--wide m-btn--md m-loader--light m-loader--right" onclick="if(!this.classList.contains('m-loader')) this.className+=' m-loader';">
						<?php _e( 'Update' );?>
					</button>
				</div>
			</div>
		</div>
	</div>
</form>