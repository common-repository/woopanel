<?php
/**
 * Layout Header
 * @package WooPanel/Templates
 * @version 1.1.0
 */

$my_info = wp_get_current_user();
?>
<!-- BEGIN: Header -->
		<header id="m_header" class="m-grid__item m-header " m-minimize-offset="200" m-minimize-mobile-offset="200">
			<div class="m-container m-container--fluid m-container--full-height">
				<div class="m-stack m-stack--ver m-stack--desktop">

					<!-- BEGIN: Brand -->
					<div class="m-stack__item m-brand  m-brand--skin-dark ">
						<div class="m-stack m-stack--ver m-stack--general">
							<div class="m-stack__item m-stack__item--middle m-brand__logo">
								<a href="<?php echo woopanel_dashboard_url();?>" class="m-brand__logo-wrapper">
									<img alt="" src="<?php echo woopanel_logo_src( 'header' ); ?>" />
								</a>
							</div>
							<div class="m-stack__item m-stack__item--middle m-brand__tools">

								<!-- BEGIN: Left Aside Minimize Toggle -->
								<a href="javascript:;" id="m_aside_left_minimize_toggle" class="m-brand__icon m-brand__toggler m-brand__toggler--left m--visible-desktop-inline-block  ">
									<span></span>
								</a>

								<!-- END -->

								<!-- BEGIN: Responsive Aside Left Menu Toggler -->
								<a href="javascript:;" id="m_aside_left_offcanvas_toggle" class="m-brand__icon m-brand__toggler m-brand__toggler--left m--visible-tablet-and-mobile-inline-block">
									<span></span>
								</a>

								<!-- END -->

								<!-- BEGIN: Responsive Header Menu Toggler -->
								<a id="m_aside_header_menu_mobile_toggle" href="javascript:;" class="m-brand__icon m-brand__toggler m--visible-tablet-and-mobile-inline-block">
									<span></span>
								</a>

								<!-- END -->

								<!-- BEGIN: Topbar Toggler -->
								<a id="m_aside_header_topbar_mobile_toggle" href="javascript:;" class="m-brand__icon m--visible-tablet-and-mobile-inline-block">
									<i class="flaticon-more"></i>
								</a>

								<!-- BEGIN: Topbar Toggler -->
							</div>
						</div>
					</div>

					<!-- END: Brand -->
					<div class="m-stack__item m-stack__item--fluid m-header-head" id="m_header_nav">

						<!-- BEGIN: Horizontal Menu -->
						<button class="m-aside-header-menu-mobile-close  m-aside-header-menu-mobile-close--skin-dark " id="m_aside_header_menu_mobile_close_btn"><i class="la la-close"></i></button>
						<div id="m_header_menu" class="m-header-menu m-aside-header-menu-mobile m-aside-header-menu-mobile--offcanvas  m-header-menu--skin-light m-header-menu--submenu-skin-light m-aside-header-menu-mobile--skin-dark m-aside-header-menu-mobile--submenu-skin-dark ">
							<ul class="m-menu__nav">
								<li class="m-menu__item  m-menu__item--submenu m-menu__item--rel" m-menu-submenu-toggle="click" m-menu-link-redirect="1" aria-haspopup="true">
                                    <a href="javascript:;" class="m-menu__link m-menu__toggle">
                                        <i class="m-menu__link-icon flaticon-add"></i>
                                        <span class="m-menu__link-text"><?php echo _x( 'New', 'admin bar menu group label' );?></span>
                                        <i class="m-menu__hor-arrow la la-angle-down"></i>
                                        <i class="m-menu__ver-arrow la la-angle-right"></i>
                                    </a>
                                    <div class="m-menu__submenu m-menu__submenu--classic m-menu__submenu--left">
                                        <ul class="m-menu__subnav">
                                            <?php if( current_user_can('publish_posts') ) { ?>
                                            <li class="m-menu__item " aria-haspopup="true">
                                                <a href="<?php echo woopanel_post_new_url('post'); ?>" class="m-menu__link ">
                                                    <i class="m-menu__link-icon flaticon-book"></i>
                                                    <span class="m-menu__link-text"><?php _e('Add New Post' );?></span>
                                                </a>
                                            </li>
                                            <?php } ?>
                                            <?php if( is_woo_available() ) { ?>
                                                <?php if( current_user_can('publish_products') ) { ?>
                                                    <li class="m-menu__item " aria-haspopup="true">
                                                        <a href="<?php echo woopanel_post_new_url('product'); ?>" class="m-menu__link ">
                                                            <i class="m-menu__link-icon flaticon-box"></i>
                                                            <span class="m-menu__link-text"><?php _e('Add new product', 'woocommerce' );?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                                <?php if( current_user_can('publish_shop_coupons') ) { ?>
                                                    <li class="m-menu__item " aria-haspopup="true">
                                                        <a href="<?php echo woopanel_post_new_url('shop_coupon'); ?>" class="m-menu__link ">
                                                            <i class="m-menu__link-icon flaticon-price-tag"></i>
                                                            <span class="m-menu__link-text"><?php _e('Add new coupon', 'woocommerce' );?></span>
                                                        </a>
                                                    </li>
                                                <?php } ?>
                                            <?php } ?>
                                        </ul>
									</div>
								</li>
							</ul>
						</div>
						<!-- END: Horizontal Menu -->

						<!-- BEGIN: Topbar -->
						<div id="m_header_topbar" class="m-topbar  m-stack m-stack--ver m-stack--general m-stack--fluid">
							<div class="m-stack__item m-topbar__nav-wrapper">
								<ul class="m-topbar__nav m-nav m-nav--inline">
									<?php if( class_exists('WeDevs_Dokan') ) :
									$store_url = dokan_get_store_url( $my_info->ID ); ?>
                                    <li class="m-nav__item">
                                        <a href="<?php echo $store_url; ?>" target="_blank" class="m-nav__link" data-container="body" data-toggle="m-tooltip" data-placement="bottom" title="<?php _e( 'Visit Store', 'woopanel' ); ?>">
                                            <span class="m-nav__link-icon"><i class="flaticon-bag"></i></span>
                                        </a>
                                    </li>
									<?php endif;?>
                                    <li class="m-nav__item">
                                        <a href="<?php echo home_url(); ?>" target="_blank" class="m-nav__link" data-container="body" data-toggle="m-tooltip" data-placement="bottom" title="<?php _e( 'Go to the homepage', 'woopanel' ); ?>">
                                            <span class="m-nav__link-icon"><i class="flaticon-home"></i></span>
                                        </a>
                                    </li>
                                    <li class="m-nav__item">
                                        <a href="<?php echo admin_url(); ?>" target="_blank" class="m-nav__link" data-container="body" data-toggle="m-tooltip" data-placement="bottom" title="<?php _e( 'The URL to the admin area' ); ?>">
                                            <span class="m-nav__link-icon"><i class="fab fa-wordpress-simple"></i></span>
                                        </a>
                                    </li>
									<li class="m-nav__item m-topbar__user-profile  m-dropdown m-dropdown--medium m-dropdown--arrow  m-dropdown--align-right m-dropdown--mobile-full-width m-dropdown--skin-light"
									m-dropdown-toggle="click">
										<a href="#" class="m-nav__link m-dropdown__toggle">
											<span class="m-topbar__userpic">
												<img src="<?php echo esc_url( get_avatar_url( $my_info->ID ) ); ?>" class="m--img-rounded m--marginless" alt="" />
											</span>
											<span class="m-topbar__username m--hide"><?php echo $my_info->display_name; ?></span>
										</a>
										<div class="m-dropdown__wrapper">
											<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust"></span>
											<div class="m-dropdown__inner">
												<div class="m-dropdown__header m--align-center">
													<div class="m-card-user m-card-user--skin-light">
														<div class="m-card-user__pic">
															<img src="<?php echo esc_url( get_avatar_url( $my_info->ID ) ); ?>" class="m--img-rounded m--marginless" alt="<?php echo $my_info->display_name; ?>" />
														</div>
														<div class="m-card-user__details">
															<span class="m-card-user__name m--font-weight-500"><?php echo sprintf( __( 'Howdy, %s' ), '<span class="display-name">' . $my_info->display_name . '</span>' ); ?></span>
															<a href="javascript:void(0);" class="m-card-user__email m--font-weight-300 m-link"><?php echo $my_info->user_email; ?></a>
														</div>
													</div>
												</div>
												<div class="m-dropdown__body">
													<div class="m-dropdown__content">
                                                        <ul class="m-nav m-nav--skin-light">
                                                            <li class="m-nav__item">
                                                                <a href="<?php echo woopanel_dashboard_url('profile');?>" class="m-nav__link">
                                                                    <i class="m-nav__link-icon flaticon-profile-1"></i>
                                                                    <span class="m-nav__link-title">
                                                                        <span class="m-nav__link-wrap">
                                                                            <span class="m-nav__link-text"><?php esc_html_e('My Profiles', 'woopanel');?></span>
                                                                        </span>
                                                                    </span>
                                                                </a>
                                                            </li>
                                                            <li class="m-nav__separator m-nav__separator--fit"></li>
                                                            <li class="m-nav__item">
																<a href="<?php echo woopanel_logout_url();?>" class="btn m-btn--pill btn-secondary m-btn m-btn--custom m-btn--label-brand m-btn--bolder"><?php esc_html_e('Log Out');?></a>
															</li>
														</ul>
													</div>
												</div>
											</div>
										</div>
									</li>
								</ul>
							</div>
						</div>
						<!-- END: Topbar -->
					</div>
				</div>
			</div>
		</header>
		<!-- end::Header -->