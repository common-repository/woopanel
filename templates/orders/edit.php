<?php
/**
 * Order Item
 * @package WooPanel/Templates
 * @version 1.1.0
 */


if ( wc_tax_enabled() ) {
	$order_taxes      = $order->get_taxes();
	$tax_classes      = WC_Tax::get_tax_classes();
	$classes_options  = wc_get_product_tax_class_options();
	$show_tax_columns = count( $order_taxes ) === 1;
}
$payment_gateway     = wc_get_payment_gateway_by_order( $order );
$gateway_name  = false !== $payment_gateway ? ( ! empty( $payment_gateway->method_title ) ? $payment_gateway->method_title : $payment_gateway->get_title() ) : __( 'None', 'woopanel' );

$order_data = $order->get_data();
$billing_fields = apply_filters(
	'woocommerce_admin_billing_fields', array(
		'first_name' => array(
			'label' => __( 'First name', 'woocommerce' ),
			'show'  => false,
		),
		'last_name'  => array(
			'label' => __( 'Last name', 'woocommerce' ),
			'show'  => false,
		),
		'company'    => array(
			'label' => __( 'Company', 'woocommerce' ),
			'show'  => false,
		),
		'address_1'  => array(
			'label' => __( 'Address line 1', 'woocommerce' ),
			'show'  => false,
		),
		'address_2'  => array(
			'label' => __( 'Address line 2', 'woocommerce' ),
			'show'  => false,
		),
		'city'       => array(
			'label' => __( 'City', 'woocommerce' ),
			'show'  => false,
		),
		'postcode'   => array(
			'label' => __( 'Postcode / ZIP', 'woocommerce' ),
			'show'  => false,
		),
		'country'    => array(
			'label'   => __( 'Country', 'woocommerce' ),
			'show'    => false,
			'class'   => 'js_field-country select short',
			'type'    => 'select',
			'options' => array( '' => __( 'Select a country&hellip;', 'woocommerce' ) ) + WC()->countries->get_allowed_countries(),
		),
		'state'      => array(
			'label' => __( 'State / County', 'woocommerce' ),
			'class' => 'js_field-state select short',
			'show'  => false,
		),
		'email'      => array(
			'label' => __( 'Email address', 'woocommerce' ),
		),
		'phone'      => array(
			'label' => __( 'Phone', 'woocommerce' ),
		),
	)
);

$shipping_fields = apply_filters(
	'woocommerce_admin_shipping_fields', array(
		'first_name' => array(
			'label' => __( 'First name', 'woocommerce' ),
			'show'  => false,
		),
		'last_name'  => array(
			'label' => __( 'Last name', 'woocommerce' ),
			'show'  => false,
		),
		'company'    => array(
			'label' => __( 'Company', 'woocommerce' ),
			'show'  => false,
		),
		'address_1'  => array(
			'label' => __( 'Address line 1', 'woocommerce' ),
			'show'  => false,
		),
		'address_2'  => array(
			'label' => __( 'Address line 2', 'woocommerce' ),
			'show'  => false,
		),
		'city'       => array(
			'label' => __( 'City', 'woocommerce' ),
			'show'  => false,
		),
		'postcode'   => array(
			'label' => __( 'Postcode / ZIP', 'woocommerce' ),
			'show'  => false,
		),
		'country'    => array(
			'label'   => __( 'Country', 'woocommerce' ),
			'show'    => false,
			'type'    => 'select',
			'class'   => 'js_field-country select short',
			'options' => array( '' => __( 'Select a country&hellip;', 'woocommerce' ) ) + WC()->countries->get_shipping_countries(),
		),
		'state'      => array(
			'label' => __( 'State / County', 'woocommerce' ),
			'class' => 'js_field-state select short',
			'show'  => false,
		),
	)
);
$order_status = $order->get_status();
$disable_edit = array('processing', 'completed');

$post = get_post($order->get_id());
?>
<form class="m-form m-form--label-align-left- m-form--state-" name="post" method="post" id="post">
	<input type="hidden" name="post_ID" id="post_ID" value="<?php echo $order->get_id();?>" />
	<div class="row">
		<div class="col col-main">
		<div class="m-portlet m-invoice-order">
				<div class="m-portlet__body m-portlet__body--no-padding">
					<div class="m-invoice-1">
						<div class="m-invoice__wrapper">
							<div class="m-invoice__head">
								<div class="m-invoice__container m-invoice__container--centered">
									<div class="m-invoice__logo">
										<a href="#">
											<h1><?php echo sprintf( __( 'Invoice #%d', 'woopanel' ), $order->get_id() );?></h1>
										</a>
										<a href="#" class="invoice-logo">
											<img src="<?php echo woopanel_logo_src();?>">
										</a>
									</div>
								<div class="m-invoice-head_detail">
									
									<div class="m-invoice__desc m-invoice-billing">
										
											<h3><?php _e('Billing', 'woocommerce');?> <div class="m-invoice-address-edit" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Edit Billing', 'woopanel');?>"><i class="la la-edit"></i></div></h3>

											
											<?php

											// Display values.
											if ( $order->get_formatted_billing_address() ) {
												echo '<p>' . wp_kses( $order->get_formatted_billing_address(), array( 'br' => array() ) ) . '</p>';
											}else {
												_e( 'No billing address set.', 'woocommerce' );
											}

											foreach ( $billing_fields as $key => $field ) {
												if ( isset( $field['show'] ) && false === $field['show'] ) {
													continue;
												}

												$field_name = 'billing_' . $key;

												if ( isset( $field['value'] ) ) {
													$field_value = $field['value'];
												} elseif ( is_callable( array( $order, 'get_' . $field_name ) ) ) {
													$field_value = $order->{"get_$field_name"}( 'edit' );
												} else {
													$field_value = $order->get_meta( '_' . $field_name );
												}

												if ( 'billing_phone' === $field_name ) {
													$field_value = wc_make_phone_clickable( $field_value );
												} else {
													$field_value = make_clickable( esc_html( $field_value ) );
												}

												if ( $field_value ) {
													echo '<p><strong>' . esc_html( $field['label'] ) . ':</strong> ' . wp_kses_post( $field_value ) . '</p>';
												}
											}
											?>
									</div>


						<div class="m-invoice__desc m-invoice-shipping">
							<h3><?php _e('Shipping', 'woocommerce');?> <div class="m-invoice-shipping-edit" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Edit Shipping', 'woopanel');?>"><i class="la la-edit"></i></div></h3>
							
							<?php

							// Display values.
							if ( $order->get_formatted_shipping_address() ) {
								echo '<p>' . wp_kses( $order->get_formatted_shipping_address(), array( 'br' => array() ) ) . '</p>';
							} else {
								echo '<p class="none_set"><strong>' . __( 'Address:', 'woocommerce' ) . '</strong> ' . __( 'No shipping address set.', 'woocommerce' ) . '</p>';
							}

							if ( ! empty( $shipping_fields ) ) {
								foreach ( $shipping_fields as $key => $field ) {
									if ( isset( $field['show'] ) && false === $field['show'] ) {
										continue;
									}

									$field_name = 'shipping_' . $key;

									if ( is_callable( array( $order, 'get_' . $field_name ) ) ) {
										$field_value = $order->{"get_$field_name"}( 'edit' );
									} else {
										$field_value = $order->get_meta( '_' . $field_name );
									}

									if ( $field_value ) {
										echo '<p><strong>' . esc_html( $field['label'] ) . ':</strong> ' . wp_kses_post( $field_value ) . '</p>';
									}
								}
							}

							if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' == get_option( 'woocommerce_enable_order_comments', 'yes' ) ) && $post->post_excerpt ) {
								echo '<p class="order_note"><strong>' . __( 'Customer provided note:', 'woocommerce' ) . '</strong> ' . nl2br( esc_html( $post->post_excerpt ) ) . '</p>';
							}
							?>
						</div>
						


									<div class="m-invoice__items">
										<div class="m-invoice__item">
											<span class="m-invoice__subtitle"><?php _e( 'Date created:', 'woocommerce' ); ?></span>
											<span class="m-invoice__text"><?php echo esc_attr( date_i18n( 'Y-m-d', $order_data['date_created']->getTimestamp() ) ); ?></span>
										</div>
										<div class="m-invoice__item">
											<span class="m-invoice__subtitle"><?php _e( 'Invoice Number:', 'woopanel' ); ?></span>
											<span class="m-invoice__text">#<?php echo $order->get_id();?></span>
										</div>
										<div class="m-invoice__item">
											<span class="m-invoice__subtitle"><?php esc_html_e( 'Payment method:', 'woocommerce' ); ?></span>
											<span class="m-invoice__text"><?php echo $gateway_name;?></span>
										</div>
									</div>
						</div>
								</div>
							</div>
							<div class="m-invoice__body m-invoice__body--centered">
								<div class="m-invoice-edit"><a href="#"><?php _e('List Items', 'woopanel');?> <i class="la la-edit" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php _e('Edit Item', 'woopanel');?>"></i></a></div>
								<div class="table-responsive">
									<table class="table">
										<thead>
											<tr>
												<th><?php esc_html_e( 'Item', 'woocommerce' ); ?></th>
												<th><?php esc_html_e( 'Cost', 'woocommerce' ); ?></th>
												<th style="width: 200px"><?php esc_html_e( 'Qty', 'woocommerce' ); ?></th>
												<th style="width: 200px"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ( $line_items as $item_id => $item ) {
												$product      = $item->get_product();
												$product_link = $product ? admin_url( 'post.php?post=' . $item->get_product_id() . '&action=edit' ) : '';
												$thumbnail    = $product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';?>
											<tr>
												<td><?php
												echo $product_link ? '<a href="' . esc_url( get_permalink($product->get_id()) ) . '" class="wc-order-item-name" target="_blank">' . wp_kses_post( $item->get_name() ) . '</a>' : '<div class="wc-order-item-name">' . wp_kses_post( $item->get_name() ) . '</div>';

												if ( $product && $product->get_sku() ) {
													echo '<div class="wc-order-item-sku"><strong>' . esc_html__( 'SKU:', 'woocommerce' ) . '</strong> ' . esc_html( $product->get_sku() ) . '</div>';
												}
										
												if ( $item->get_variation_id() ) {
													echo '<div class="wc-order-item-variation"><strong>' . esc_html__( 'Variation ID:', 'woocommerce' ) . '</strong> ';
													if ( 'product_variation' === get_post_type( $item->get_variation_id() ) ) {
														echo esc_html( $item->get_variation_id() );
													} else {
														/* translators: %s: variation id */
														printf( esc_html__( '%s (No longer exists)', 'woocommerce' ), $item->get_variation_id() );
													}
													echo '</div>';
												}
												
												?></td>
												<td>
													<?php
													echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_currency() ) );

													if ( $item->get_subtotal() !== $item->get_total() ) {
														echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $order->get_item_subtotal( $item, false, false ) - $order->get_item_total( $item, false, false ), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
													}
													?>
												</td>
												<td>
													<?php
													echo '<small class="times">&times;</small> ' . esc_html( $item->get_quantity() );

													if ( $refunded_qty = $order->get_qty_refunded_for_item( $item_id ) ) {
														echo '<small class="refunded">-' . ( $refunded_qty * -1 ) . '</small>';
													}
													?>
												</td>
												<td>
													<?php
														echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );

														if ( $item->get_subtotal() !== $item->get_total() ) {
															echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $item->get_subtotal() - $item->get_total(), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
														}

														if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
															echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
														}
													?>
												</td>
											</tr>
											<?php }?>
										</tbody>

										<tfoot>
											<tr>
												<td colspan="2"></td>	
												<td class="invoice-footer-heading" style="width: 200px"><?php esc_html_e( 'Discount:', 'woocommerce' ); ?></td>
												<td class="invoice-footer-value" style="width: 200px"><?php if ( 0 < $order->get_total_discount() ) :
													echo wc_price( $order->get_total_discount(), array( 'currency' => $order->get_currency() ) );
												else:
													echo wc_price(0);
												endif;?></td>
												</tr>

												<tr>
													<td colspan="2"></td>	
													<td class="invoice-footer-heading"><?php esc_html_e( 'Shipping:', 'woocommerce' ); ?></td>
													<td class="invoice-footer-value"><?php if ( $order->get_shipping_methods() ) :
													$refunded = $order->get_total_shipping_refunded();
													if ( $refunded > 0 ) {
														echo '<del>' . strip_tags( wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ) ) . '</del> <ins>' . wc_price( $order->get_shipping_total() - $refunded, array( 'currency' => $order->get_currency() ) ) . '</ins>'; // WPCS: XSS ok.
													} else {
														echo wc_price( $order->get_shipping_total(), array( 'currency' => $order->get_currency() ) ); // WPCS: XSS ok.
													}
												else:
													echo wc_price(0);
												endif;?></td>
											</tr>

											<tr>
												<td colspan="2"></td>	
												<td class="invoice-footer-heading"><?php esc_html_e( 'Tax', 'woocommerce' ); ?>:</td>
												<td class="invoice-footer-value"><?php if ( wc_tax_enabled() ) : 
													echo '<table>';
													foreach ( $order->get_tax_totals() as $code => $tax ) : ?>
													<tr>
														<td class="label"><?php echo esc_html( $tax->label ); ?>:</td>
														<td width="1%"></td>
														<td class="total">
															<?php
															$refunded = $order->get_total_tax_refunded_by_rate_id( $tax->rate_id );
															if ( $refunded > 0 ) {
																echo '<del>' . strip_tags( $tax->formatted_amount ) . '</del> <ins>' . wc_price( WC_Tax::round( $tax->amount, wc_get_price_decimals() ) - WC_Tax::round( $refunded, wc_get_price_decimals() ), array( 'currency' => $order->get_currency() ) ) . '</ins>'; // WPCS: XSS ok.
															} else {
																echo wp_kses_post( $tax->formatted_amount );
															}
														?>
														</td>
													</tr>
													<?php endforeach;
													echo '</table>';
												else:
													echo wc_price(0);
												endif;?></td>
											</tr>

											<tr class="subtotal">
												<td colspan="2"></td>	
												<td class="invoice-footer-heading"><?php esc_html_e( 'Total', 'woocommerce' ); ?></td>
												<td class="invoice-footer-value"><?php echo $order->get_formatted_order_total(); // WPCS: XSS ok. ?></td>
											</tr>
										</tfoot>
									</table>
								</div>
							</div>

						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-12 col-sm-12 col-sidebar">
			<div class="m-portlet m-order-notes">
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<h3 class="m-portlet__head-text"><?php _e('Order notes', 'woocommerce');?></h3>
						</div>
					</div>
				</div>
				<div class="m-portlet__body">

				<?php 
					$args = array(
						'order_id' => $order->get_id(),
					);

					$notes = wc_get_order_notes( $args );?>
						<div class="m-messenger m-messenger--message-arrow m-messenger--skin-light">
							<?php if( $notes ) { ?>
							<div class="m-messenger__messages m-scrollable">
								<?php foreach ( $notes as $note ) {
									$get_user = get_user_by('login', $note->added_by);

									$note_classes   = array( 'note' );
									$note_classes[] = $note->customer_note ? 'customer-note' : '';
									$note_classes[] = 'system' === $note->added_by ? 'system-note' : '';
									$note_classes   = apply_filters( 'woocommerce_order_note_class', array_filter( $note_classes ), $note );?>
								<?php if ( 'system' !== $note->added_by ) {?>
									<div class="m-messenger-note" rel="<?php echo $note->id;?>">

									<div class="m-messager-info m-messager-left">
										<div class="m-message-avatar">
											<img src="<?php echo esc_url( get_avatar_url( $get_user->ID ) ); ?>" alt="" />
										</div>
										<div class="m-message-content">
											<h4><?php printf( __( 'by %s', 'woocommerce' ), $note->added_by );?></h4>
											<abbr class="exact-date" title="<?php echo $note->date_created->date( 'y-m-d h:i:s' ); ?>"><?php printf( __( '%1$s @ %2$s', 'woopanel' ), $note->date_created->date_i18n( wc_date_format() ), $note->date_created->date_i18n( wc_time_format() ) ); ?></abbr>
											<a href="#" class="delete_note" role="button"><?php _e( 'Delete note', 'woocommerce' ); ?></a>
										</div>


									</div>






										<div class="m-messenger__wrapper <?php echo esc_attr( implode( ' ', $note_classes ) ); ?>">
											<div class="m-messenger__message m-messenger__message--in">
												<div class="m-messenger__message-body">
													<div class="m-messenger__message-arrow"></div>
													<div class="m-messenger__message-content">

														<div class="m-messenger__message-text">
															<?php echo wpautop( wptexturize( wp_kses_post( $note->content ) ) ); ?>
															
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								<?php } else { ?>
									<div class="m-messager-info m-messager-right">
										<div class="m-message-avatar">
											<img src="<?php echo WOOPANEL_URL;?>assets/images/robot-icon.png" alt="" />
										</div>

										<div class="m-message-content">
											<h4><?php _e('By System', 'woopanel');?></h4>
											<abbr class="exact-date" title="<?php echo $note->date_created->date( 'y-m-d h:i:s' ); ?>"><?php printf( __( '%1$s @ %2$s', 'woopanel' ), $note->date_created->date_i18n( wc_date_format() ), $note->date_created->date_i18n( wc_time_format() ) ); ?></abbr>
											<a href="#" class="delete_note" role="button"><?php _e( 'Delete note', 'woocommerce' ); ?></a>
										</div>
									</div>

									<div class="m-messenger__wrapper m-messenger__system">
										<div class="m-messenger__message m-messenger__message--out">
											<div class="m-messenger__message-body">
												<div class="m-messenger__message-arrow"></div>
												<div class="m-messenger__message-content">
													<div class="m-messenger__message-text">
														<?php echo wpautop( wptexturize( wp_kses_post( $note->content ) ) ); ?>
													</div>
												</div>
											</div>
										</div>
									</div>
								<?php }
								}?>
							</div>
							<?php } else {
								echo '<div>' . __( 'There are no notes yet.', 'woocommerce' ) . '</div>';
							}?>
							<div class="m-messenger__seperator"></div>
							<?php
							woopanel_form_field(
								'order_note_type',
								array(
									'id'                => 'order_note_type',
									'type'				=> 'select',
									'label'             => '',
									'options'     => array(
										'' => __( 'Private note', 'woocommerce' ),
										'customer' => __( 'Note to customer', 'woocommerce' )
									)
								),
								false
							);?>

							<div class="m-messenger__form">
								<div class="m-messenger__form-controls">
									<input type="text" name="order_message" placeholder="<?php _e( 'Add note', 'woocommerce' ); ?>..." class="m-messenger__form-input order_message">
								</div>
								<div class="m-messenger__form-tools">
									<button type="button" class="m-messenger__form-send">
										<i class="la la-send-o"></i>
									</button>
								</div>
							</div>
						</div>











				
				</div>
			</div>

			<div class="m-portlet">
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<h3 class="m-portlet__head-text"><?php _e('General', 'woocommerce');?></h3>
						</div>
					</div>
				</div>

				<div class="m-portlet__body">
					<div class="form-group m-form__group type-file">
						<label for="thumbnail" class=""><?php _e( 'Date created:', 'woocommerce' ); ?></label>
						<div class="form-field-wrapper">
							<div class="row">
								<div class="col-5" style="padding-right: 0px;">
									<input type="text" class="date-picker form-control m-input" name="order_date" maxlength="10" value="<?php echo $order->get_date_created()->format('Y-m-d'); ?>" pattern="<?php echo esc_attr( apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ) ); ?>" />
								</div>

								<div class="col-1" style="padding-left: 0; padding-top: 8px; padding-right: 0; text-align: center;"><span>@</span></div>
								<div class="col-6" style="padding-left: 0;">
									<input type="number" class="col-timer hour form-control m-input" placeholder="<?php esc_attr_e( 'h', 'woocommerce' ); ?>" name="order_date_hour" min="0" max="23" step="1" value="<?php echo $order->get_date_created()->format('H'); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />
									<span class="col-timer-dotma">:</span>
									<input type="number" class="col-timer minute form-control m-input" placeholder="<?php esc_attr_e( 'm', 'woocommerce' ); ?>" name="order_date_minute" min="0" max="59" step="1" value="<?php echo $order->get_date_created()->format('i'); ?>" pattern="[0-5]{1}[0-9]{1}" />
								</div>
							</div>
							<input type="hidden" name="order_date_second" value="<?php echo esc_attr( date_i18n( 's', strtotime( $post->post_date ) ) ); ?>" />
						</div>
					</div>

					<?php
					//selected( $status, 'wc-' . $order->get_status( 'edit' ), false ) 
					$new_statuses = array();
					$statuses = wc_get_order_statuses();
					foreach ( $statuses as $status => $status_name ) {
						$new_statuses[esc_attr( $status )]  = esc_html( $status_name );
					}
					woopanel_form_field(
						'order_status',
						array(
							'id'                => 'order_status',
							'type'				=> 'select',
							'placeholder'       => __('Search for a product&hellip;', 'woocommerce'),
							'label'             => __( 'Status:', 'woocommerce' ),
							'options'     => $new_statuses
						),
						'wc-' . $order->get_status( 'edit' )
					);



					$user_string = '';
					$user_id     = '';
					if ( $order->get_user_id() ) {
						$user_id = absint( $order->get_user_id() );
						$user    = get_user_by( 'id', $user_id );
						/* translators: 1: user display name 2: user ID 3: user email */
						$user_string = sprintf(
							esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'woocommerce' ),
							$user->display_name,
							absint( $user->ID ),
							$user->user_email
						);
					}
					woopanel_form_field(
						'customer_user',
						array(
							'id'                => 'customer_user',
							'type'				=> 'select',
							'placeholder'       => __('Guest', 'woocommerce'),
							'label'             => __( 'Customer:', 'woocommerce' ),
							'custom_attributes' => array(
								'abc' 			=> 'xyz'
							),
							'input_class' => array('select2-customer-ajax'),
							'options'     => array(
								esc_attr( $user_id ) => htmlspecialchars( $user_string )
							),
							'wrapper_class' => 'tags_select2'
						),
						$user_id
					);
					?>
				</div>

				<div class="m-portlet__foot">					
					<div id="publishing-actions">
						<div id="publishing-action">
							<button type="submit" name="publish" class="btn btn-primary m-btn m-loader--light m-loader--right" id="publish" onclick="if(!this.classList.contains('m-loader')) this.className+=' m-loader';"><?php _e('Update');?></button>
						</div>
						<div id="delete-action">
							<a class="btn btn-link submitdelete deletion" onclick="return showNotice.warn();" href=""><?php _e('Delete Permanently');?></a>
						</div>
					</div>
				<!--end: Form Body -->
				</div>
			</div>
		</div>
	</div>
</form>


<form action="" method="POST" class="frm_edit_item">
    <input type="hidden" name="action" value="woopanel_edit_item_order" />
    <input type="hidden" name="order_id" value="<?php echo $order->get_id();?>" />
    <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'edit_items_order' );?>" />
    <div id="edit_order_modal" class="modal modal-order fade">
        <div class="modal-dialog">
            <div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title"><?php echo sprintf( __( 'Invoice #%d', 'woopanel' ), $order->get_id() );?></h4>
				</div>
				<div class="modal-body">
					<table cellpadding="0" cellspacing="0" class="woocommerce_order_items">
						<thead>
							<tr>
								<th class="item sortable" colspan="2" data-sort="string-ins"><?php esc_html_e( 'Item', 'woocommerce' ); ?></th>
								<th class="item_cost sortable" data-sort="float"><?php esc_html_e( 'Cost', 'woocommerce' ); ?></th>
								<th class="quantity sortable" data-sort="int"><?php esc_html_e( 'Qty', 'woocommerce' ); ?></th>
								<th class="line_cost sortable" data-sort="float"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
								<th class="wc-order-edit-line-item" width="1%">&nbsp;</th>
							</tr>
						</thead>
						<tbody id="order_line_items" data-price-format="<?php echo htmlspecialchars(wc_price(0));?>" data-price="<?php echo woopanel_price(0);?>">
						<?php
						$i = 0;
						foreach ( $line_items as $item_id => $item ) {
							$product      = $item->get_product();
							$product_link = $product ? admin_url( 'post.php?post=' . $item->get_product_id() . '&action=edit' ) : '';
							$thumbnail    = $product ? apply_filters( 'woocommerce_admin_order_item_thumbnail', $product->get_image( 'thumbnail', array( 'title' => '' ), false ), $item_id, $item ) : '';
							?>
							<tr class="item " data-order_item_id="1">
								<td class="thumb">
									<div class="wc-order-item-thumbnail"><?php echo $product->get_image( 'thumbnail', array( 'title' => '' ), false );?></div>
								</td>
								<td class="name" data-sort-value="WordPress Pennant">
									<?php
									echo $product_link ? '<a href="' . esc_url( get_permalink($product->get_id()) ) . '" class="wc-order-item-name" target="_blank">' . wp_kses_post( $item->get_name() ) . '</a>' : '<div class="wc-order-item-name">' . wp_kses_post( $item->get_name() ) . '</div>';

									if ( $product && $product->get_sku() ) {
										echo '<div class="wc-order-item-sku"><strong>' . esc_html__( 'SKU:', 'woocommerce' ) . '</strong> ' . esc_html( $product->get_sku() ) . '</div>';
									}
							
									if ( $item->get_variation_id() ) {
										echo '<div class="wc-order-item-variation"><strong>' . esc_html__( 'Variation ID:', 'woocommerce' ) . '</strong> ';
										if ( 'product_variation' === get_post_type( $item->get_variation_id() ) ) {
											echo esc_html( $item->get_variation_id() );
										} else {
											/* translators: %s: variation id */
											printf( esc_html__( '%s (No longer exists)', 'woocommerce' ), $item->get_variation_id() );
										}
										echo '</div>';
									}
									
									?>
								</td>
								<td class="item_cost" width="1%">
									<input type="hidden" name="data[order_item_id][<?php echo $i;?>]" value="<?php echo $item_id;?>" />
									<input type="hidden" name="data[order_item_tax_class][<?php echo $item_id;?>]" value="" />
									<input type="hidden" name="data[refund_order_item_qty][<?php echo $item_id;?>]" value="" />
									<input type="hidden" name="data[refund_line_total][<?php echo $item_id;?>]" value="" />
									<input type="hidden" class="line_total" name="data[line_total][<?php echo $item_id;?>]" value="<?php echo $item->get_total();?>" />
									<input type="hidden" class="line_subtotal" name="data[line_subtotal][<?php echo $item_id;?>]" value="<?php echo $order->get_item_total( $item, false, true );?>" />
									

									
									<?php
										echo wc_price( $order->get_item_total( $item, false, true ), array( 'currency' => $order->get_currency() ) );

										if ( $item->get_subtotal() !== $item->get_total() ) {
											echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $order->get_item_subtotal( $item, false, false ) - $order->get_item_total( $item, false, false ), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
										}?>
								</td>
								<td class="quantity" width="1%">
									<?php
									if( ! in_array($order_status, $disable_edit) ) {?>								
									<div class="edit">
										<input type="number" step="1" min="0" autocomplete="off" name="data[order_item_qty][<?php echo $item_id;?>]" placeholder="0" value="<?php echo $item->get_quantity();?>" data-qty="1" size="4" class="quantity edit_order_input">
									</div>
									<?php }else {
										echo '<small class="times">&times;</small> ' . esc_html( $item->get_quantity() );
									}?>
								</td>
								<td class="line_cost" width="1%" data-sort-value="11.05">
									<?php

										echo wc_price( $item->get_total(), array( 'currency' => $order->get_currency() ) );

										if ( $item->get_subtotal() !== $item->get_total() ) {
											echo '<span class="wc-order-item-discount">-' . wc_price( wc_format_decimal( $item->get_subtotal() - $item->get_total(), '' ), array( 'currency' => $order->get_currency() ) ) . '</span>';
										}

										if ( $refunded = $order->get_total_refunded_for_item( $item_id ) ) {
											echo '<small class="refunded">-' . wc_price( $refunded, array( 'currency' => $order->get_currency() ) ) . '</small>';
										}
									?>
								</td>
								<td class="wc-order-edit-line-item" width="1%">
									<div class="wc-order-edit-line-item-actions">
									</div>
								</td>
							</tr>
							<?php $i++;
						}?>
						</tbody>
						<tbody id="order_shipping_line_items">
						</tbody>
						<tbody id="order_fee_line_items">
						</tbody>
						<tbody id="order_refunds">
						</tbody>
					</table>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary btn-sm m-btn--widee"><?php _e('Save changes');?></button>
				</div>
            </div>
        </div>
    </div>
</form>

<form action="" method="POST" class="frm_edit_billing">
    <div id="edit_order_address_modal" class="modal modal-order fade">
        <input type="hidden" name="action" value="woopanel_edit_item_billing" />
        <input type="hidden" name="order_id" value="<?php echo $order->get_id();?>" />
        <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'edit_items_billing' );?>" />
        <div class="modal-dialog">
            <div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title"><?php echo sprintf( __( 'Billing', 'woocommerce' ), $order->get_id() );?></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<?php
							woopanel_form_field(
								'data[_billing_first_name]',
								array(
									'id'                => '_billing_first_name',
									'type'				=> 'text',
									'label'             => __( 'First name', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_billing_first_name()
							);

							woopanel_form_field(
								'data[_billing_last_name]',
								array(
									'id'                => '_billing_last_name',
									'type'				=> 'text',
									'label'             => __( 'Last name', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_billing_last_name()
							);
						?>
					</div>

					<?php
						woopanel_form_field(
							'data[_billing_company]',
							array(
								'id'                => '_billing_company',
								'type'				=> 'text',
								'label'             => __( 'Company', 'woocommerce' ),
							),
							$order->get_billing_company()
						);
					?>
					
					<div class="row">
						<?php
							woopanel_form_field(
								'data[_billing_address_1]',
								array(
									'id'                => '_billing_address_1',
									'type'				=> 'text',
									'label'             => __( 'Address line 1', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_billing_address_1()
							);

							woopanel_form_field(
								'data[_billing_address_2]',
								array(
									'id'                => '_billing_address_2',
									'type'				=> 'text',
									'label'             => __( 'Address line 2', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_billing_address_2()
							);
						?>
					</div>

					<div class="row">
						<?php
							woopanel_form_field(
								'data[_billing_city]',
								array(
									'id'                => '_billing_city',
									'type'				=> 'text',
									'label'             => __( 'City', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_billing_city()
							);

							woopanel_form_field(
								'data[_billing_postcode]',
								array(
									'id'                => '_billing_postcode',
									'type'				=> 'text',
									'label'             => __( 'Postcode / ZIP', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_billing_postcode()
							);
						?>
					</div>

					<div class="row">
						<?php
							woopanel_form_field(
								'data[_billing_country]',
								array(
									'id'                => '_billing_country',
									'type'				=> 'select',
									'label'             => __( 'Country', 'woocommerce' ),
									'options'			=> WC()->countries->get_countries(),
									'wrapper_class'		=> 'col-6 single-select2',
									'input_class'		=> array('wpl-select2')
								),
								$order->get_billing_country()
							);

							woopanel_form_field(
								'data[_billing_state]',
								array(
									'id'                => '_billing_state',
									'type'				=> 'text',
									'label'             => __( 'State / County', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_billing_state()
							);
						?>
					</div>

					<?php
						if ( WC()->payment_gateways() ) {
							$payment_gateways = WC()->payment_gateways->payment_gateways();
						} else {
							$payment_gateways = array();
						}
						$payment_method = $order->get_payment_method();

						$found_method = false;
						$_payment_method = array();
						foreach ( $payment_gateways as $gateway ) {
							if ( 'yes' === $gateway->enabled ) {
								$_payment_method[$gateway->id] = esc_html( $gateway->get_title() );
								if ( $payment_method == $gateway->id ) {
									$found_method = true;
								}
							}
						}

						if ( ! $found_method && ! empty( $payment_method ) ) {
							$_payment_method[$payment_method] = __( 'Other', 'woocommerce' );
						} else {
							$_payment_method['other'] = __( 'Other', 'woocommerce' );
						}
						woopanel_form_field(
							'data[_payment_method]',
							array(
								'id'                => '_payment_method',
								'type'				=> 'select',
								'label'             => __( 'Payment method:', 'woocommerce' ),
								'options'			=> array_merge(array(
									'' => esc_html__( 'N/A', 'woocommerce' )
								), $_payment_method)
							),
							$order->get_payment_method()
						);
					?>
					<?php
						woopanel_form_field(
							'data[_transaction_id]',
							array(
								'id'                => '_transaction_id',
								'type'				=> 'text',
								'label'             => __( 'Transaction ID', 'woocommerce' ),
							),
							$order->get_transaction_id( 'edit' )
						);
					?>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary btn-sm m-btn--widee"><?php _e('Save changes');?></button>
				</div>
            </div>
        </div>
    </div>
</form>

<form action="" method="POST" class="frm_edit_shipping">
    <div id="edit_order_shipping_modal" class="modal modal-order fade">
        <div class="modal-dialog">
            <div class="modal-content">
				<input type="hidden" name="action" value="woopanel_edit_item_shipping" />
				<input type="hidden" name="order_id" value="<?php echo $order->get_id();?>" />
				<input type="hidden" name="security" value="<?php echo wp_create_nonce( 'edit_items_shipping' );?>" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title"><?php echo sprintf( __( 'Shipping', 'woocommerce' ), $order->get_id() );?></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<?php
							woopanel_form_field(
								'data[_shipping_first_name]',
								array(
									'id'                => '_shipping_first_name',
									'type'				=> 'text',
									'label'             => __( 'First name', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_shipping_first_name()
							);

							woopanel_form_field(
								'data[_shipping_last_name]',
								array(
									'id'                => '_shipping_last_name',
									'type'				=> 'text',
									'label'             => __( 'Last name', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_shipping_last_name()
							);
						?>
					</div>

					<?php
						woopanel_form_field(
							'data[_shipping_company]',
							array(
								'id'                => '_shipping_company',
								'type'				=> 'text',
								'label'             => __( 'Company', 'woocommerce' ),
							),
							$order->get_shipping_company()
						);
					?>
					
					<div class="row">
						<?php
							woopanel_form_field(
								'data[_shipping_address_1]',
								array(
									'id'                => '_shipping_address_1',
									'type'				=> 'text',
									'label'             => __( 'Address line 1', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_shipping_address_1()
							);

							woopanel_form_field(
								'data[_shipping_address_2]',
								array(
									'id'                => '_shipping_address_2',
									'type'				=> 'text',
									'label'             => __( 'Address line 2', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_shipping_address_2()
							);
						?>
					</div>

					<div class="row">
						<?php
							woopanel_form_field(
								'data[_shipping_city]',
								array(
									'id'                => '_shipping_city',
									'type'				=> 'text',
									'label'             => __( 'City', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_shipping_city()
							);

							woopanel_form_field(
								'data[_shipping_postcode]',
								array(
									'id'                => '_shipping_postcode',
									'type'				=> 'text',
									'label'             => __( 'Postcode / ZIP', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_shipping_postcode()
							);
						?>
					</div>

					<div class="row">
						<?php
							woopanel_form_field(
								'data[_shipping_country]',
								array(
									'id'                => '_shipping_country',
									'type'				=> 'select',
									'label'             => __( 'Country', 'woocommerce' ),
									'options'			=> WC()->countries->get_countries(),
									'wrapper_class'		=> 'col-6 single-select2',
									'input_class'		=> array('wpl-select2')
								),
								$order->get_shipping_country()
							);

							woopanel_form_field(
								'data[_shipping_state]',
								array(
									'id'                => '_shipping_state',
									'type'				=> 'text',
									'label'             => __( 'State / County', 'woocommerce' ),
									'wrapper_class'		=> 'col-6'
								),
								$order->get_shipping_state()
							);
						?>
					</div>

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary btn-sm m-btn--widee"><?php _e('Save changes');?></button>
				</div>
            </div>
        </div>
    </div>
</form>