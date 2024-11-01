<?php
global $woopanel_options;

$user_ID = get_current_user_id();
$nonce_action = 'update_options';
?>
<form name="post" method="post" id="post" class="m-form">
	<input type="hidden" name="user_ID" value="<?php echo (int) $user_ID ?>" />
	<?php wp_nonce_field($nonce_action); ?>
	<div class="m-portlet" id="main_portlet">
		<div class="m-portlet__head">
			<div class="m-portlet__head-caption">
				<div class="m-portlet__head-title">
					<span class="m-portlet__head-icon">
						<i class="flaticon-cogwheel-2"></i>
					</span>
					<h3 class="m-portlet__head-text">
    					<?php _e('WooPanel Settings', 'woopanel');?>
					</h3>
				</div>
			</div>
			<div class="m-portlet__head-tools">
				<button type="submit" name="save1" class="btn btn-accent m-btn m-btn--icon m-btn--wide m-btn--md m-loader--light m-loader--right m--margin-right-10" onclick="if(!this.classList.contains('m-loader')) this.className+=' m-loader';">
					<?php _e( 'Update' );?>
				</button>
			</div>
		</div>

		<div class="m-portlet__body">
			<div class="row">
				<div class="col-xl-3">
					<?php
					$woopanel_options = apply_filters('woopanel_options', $woopanel_options);?>
					<div class="m-tabs" data-tabs="true" data-tabs-contents="#m_sections">
						<ul class="m-nav m-nav--active-bg m-nav--active-bg-padding-lg m-nav--font-lg m-nav--font-bold m--margin-bottom-20 m--margin-top-10 m--margin-right-40" id="m_nav" role="tablist">
							<?php $i=0; foreach ($woopanel_options as $section_id => $section) { ?>
								<li class="m-nav__item">
									<a class="m-nav__link m-tabs__item <?php if( $i == 0 && empty(get_query_var('settings')) || get_query_var('settings') == 'regular-shipping' && $section_id == 'dokan_shipping' ) echo 'm-tabs__item--active';?>" data-tab-target="<?php echo '#'. $section_id;?>" href="<?php echo '#'. $section_id;?>">
										<span class="m-nav__link-text"><?php echo $section['menu_title'];?></span>
									</a>
								</li>
							<?php $i++;} ?>
						</ul>
					</div>
				</div>

				<div class="col-xl-9">
					<div class="m-tabs-content" id="m_sections">

						<?php $i=0; foreach ($woopanel_options as $section_id => $section) { ?>
						<div class="m-tabs-content__item <?php if( $i == 0 && empty(get_query_var('settings')) || get_query_var('settings') == 'regular-shipping' && $section_id == 'dokan_shipping' ) echo 'm-tabs-content__item--active';?>" id="<?php echo $section_id;?>">

							<h4 class="m--font-bold m--margin-top-15 m--margin-bottom-20"><?php echo $section['title'];?></h4>
							
							<?php if( is_array($section['fields']) && count($section['fields']) > 0) :
								foreach ( $section['fields'] as $field ) :
									$field = array_merge($field, array(
										'form_inline' => true,
										'label' => isset($field['title']) ? $field['title'] : ''
									));
								?>
								<?php
								woopanel_form_field(
									$field['id'], 
									$field,
                                    woopanel_get_option($field['id']) ); ?>
								<?php endforeach;
							endif; ?>

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

<?php do_action('woopanel_setting_footer');?>