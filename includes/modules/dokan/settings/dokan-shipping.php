<?php
class NBT_Solutions_Dokan_Setting_Shipping {
	function __construct() {
		add_filter('woopanel_options', array($this, 'get_settings'), 99, 1);
		add_action( 'woopanel_init', array($this, 'save_settings') );
		add_filter( 'woopanel_form_field_shipping', array($this, 'show_field_payment_shipping'), 99, 4);
		add_action( 'woopanel_setting_footer', array($this, 'edit_html_shipping') );
	}
	
	function get_settings( $fields = array() ) {
		global $current_user;
		
		$dokan_settings = get_user_meta($current_user->ID, 'dokan_profile_settings', true);
		
		$fields['dokan_shipping'] = [
			'menu_title' => __( 'Dokan Shipping', 'woopanel' ),
			'title'      => __( 'Dokan Shipping Settings', 'woopanel' ),
			'desc'       => '',
			'parent'     => '',
			'icon'       => '',
			'type'       => 'user_meta',
			'fields'     => array(
				array(
					'id' => '',
					'type' => 'shipping'
				)
			)
		];
	
		return $fields;
	}
	
	function save_settings() {
		global $current_user;
		
		
		if( isset($_POST['dps_shipping_type_price']) ) {
			$dps_enable_shipping = 'no';
			if( isset($_POST['dps_enable_shipping']) ) {
				$dps_enable_shipping = 'yes';
			}

			update_user_meta( $current_user->ID, '_dps_shipping_enable', $dps_enable_shipping );
			update_user_meta( $current_user->ID, '_dps_shipping_type_price', $_POST['dps_shipping_type_price'] );
			
			update_user_meta( $current_user->ID, '_dps_additional_product', $_POST['dps_additional_product'] );
			update_user_meta( $current_user->ID, '_dps_additional_qty', $_POST['dps_additional_qty'] );
			update_user_meta( $current_user->ID, '_dps_pt', $_POST['dps_pt'] );
			update_user_meta( $current_user->ID, '_dps_ship_policy', $_POST['dps_shipping_policy'] );
			update_user_meta( $current_user->ID, '_dps_refund_policy', $_POST['dps_refund_policy'] );
			update_user_meta( $current_user->ID, '_dps_form_location', $_POST['dps_form_location'] );
            if ( isset( $_POST['dps_country_to'] ) ) {

                foreach ($_POST['dps_country_to'] as $key => $value) {
                    $country = $value;
                    $c_price = floatval( $_POST['dps_country_to_price'][$key] );

                    if( !$c_price && empty( $c_price ) ) {
                        $c_price = 0;
                    }

                    if ( !empty( $value ) ) {
                        $rates[$country] = $c_price;
                    }
                }
				
				update_user_meta( $current_user->ID, '_dps_country_rates', $rates );
            }
			
            if ( isset( $_POST['dps_state_to'] ) ) {
                foreach ( $_POST['dps_state_to'] as $country_code => $states ) {

                    foreach ( $states as $key_val => $name ) {
                        $country_c = $country_code;
                        $state_code = $name;
                        $s_price = floatval( $_POST['dps_state_to_price'][$country_c][$key_val] );

                        if ( !$s_price || empty( $s_price ) ) {
                            $s_price = 0;
                        }

                        if ( !empty( $name ) ) {
                            $s_rates[$country_c][$state_code] = $s_price;
                        }
                    }
                }
				
				update_user_meta( $current_user->ID, '_dps_state_rates', $s_rates );
            }

            
		}else {
			if( isset($_POST['data_country']['zoneID']) ) {
				$zoneID = $_POST['data_country']['zoneID'];
				
				unset($_POST['data_country']['zoneID']);
				if( !isset($_POST['data_country']['enable']) ) {
					unset($_POST['data_country']);
				}else {
					unset($_POST['data_country']['enable']);
				}
				
				$data_country = empty($_POST['data_country']) ? array() : $_POST['data_country'];
				
				self::save_location($data_country, $zoneID);
			}
		}


		$dokan_profile_settings = get_user_meta($current_user->ID, 'dokan_profile_settings', true);
		if( empty($dokan_profile_settings) ) {
			$dokan_profile_settings = array();
		}
		
		// if ( is_woopanel_endpoint_url('settings') && ( isset($_POST['save']) || isset($_POST['save1']) ) ) {
			// $fields = woopanel_dokan_payment_settings();
			
			// foreach( $fields['dokan_payment']['fields'] as $f) {
				// $name = str_replace( array('dokan_payment[', ']'), '', $f['id']);
				
				// if( isset($_POST['dokan_payment']['payment'][$name]) ) {
					// $dokan_profile_settings['payment'][$name] = $_POST['dokan_payment']['payment'][$name];
				// }
			// }
			
			// update_user_meta($current_user->ID, 'dokan_profile_settings', $dokan_profile_settings );
		// }
	}
	

	function show_field_payment_shipping($field = false, $key, $args, $value ) {
		
		global $current_user;

        $dokan_shipping_option = get_option( 'woocommerce_dokan_product_shipping_settings' );
        $enable_shipping       = ( isset( $dokan_shipping_option['enabled'] ) ) ? $dokan_shipping_option['enabled'] : 'yes';
		
		$help_text = sprintf ( '<p>%s</p>',
			__( 'A shipping zone is a geographic region where a certain set of shipping methods are offered. We will match a customer to a single zone using their shipping address and present the shipping methods within that zone to them.', 'dokan' ),
			__( 'If you want to use the previous shipping system then', 'dokan' ),
			esc_url( woopanel_dashboard_url() . 'settings/regular-shipping' ),
			__( 'Click Here', 'dokan' )
		);

		if ( 'yes' == $enable_shipping ) {
			$help_text .= sprintf ( '<p>%s <a href="%s">%s</a></p>',
				__( 'If you want to use the previous shipping system then', 'dokan' ),
				esc_url( woopanel_dashboard_url() . 'settings/regular-shipping' ),
				__( 'Click Here', 'dokan' )
			);
		}
		
		$load_shipping_methods = WC()->shipping()->load_shipping_methods();
		
		echo sprintf( '<div class="m-alert m-alert--air m-alert--square alert alert-success m-alert--icon m-alert-alt m-alert-error" style="padding: 15px 20px 10px 20px;">%s</div>', $help_text);
		
		
		if( get_query_var('settings') == 'regular-shipping' ) {
			$this->show_section_regular_shipping();
			return;
		}
		
		
		
        $data_store = WC_Data_Store::load( 'shipping-zone' );
        $raw_zones  = $data_store->get_zones();
        $zones      = array();
        $seller_id  = dokan_get_current_user_id();

        foreach ( $raw_zones as $raw_zone ) {
            $zone             = new WC_Shipping_Zone( $raw_zone );
            $enabled_methods  = $zone->get_shipping_methods( true );
            $methods_id = wp_list_pluck( $enabled_methods, 'id' );

            if ( in_array( 'dokan_vendor_shipping', $methods_id ) ) {
                $zones[ $zone->get_id() ]                            = $zone->get_data();
                $zones[ $zone->get_id() ]['zone_id']                 = $zone->get_id();
                $zones[ $zone->get_id() ]['formatted_zone_location'] = $zone->get_formatted_location();
                $zones[ $zone->get_id() ]['shipping_methods']        = self::get_shipping_methods( $zone->get_id(), $seller_id );
            }
        }

        // Everywhere zone if has method called vendor shipping
        $overall_zone    = new WC_Shipping_Zone(0);
        $enabled_methods = $overall_zone->get_shipping_methods( true );
        $methods_id      = wp_list_pluck( $enabled_methods, 'id' );

        if ( in_array( 'dokan_vendor_shipping', $methods_id ) ) {
            $zones[ $overall_zone->get_id() ]                            = $overall_zone->get_data();
            $zones[ $overall_zone->get_id() ]['zone_id']                 = $overall_zone->get_id();
            $zones[ $overall_zone->get_id() ]['formatted_zone_location'] = $overall_zone->get_formatted_location();
            $zones[ $overall_zone->get_id() ]['shipping_methods']        = self::get_shipping_methods( $overall_zone->get_id(), $seller_id );
        }
		
		$table = '<table id="dokan-shipping" class="dokan-table dokan-table-striped"><thead><tr>';
			$table .= sprintf( '<th>%s</th>', __( 'Zone Name', 'dokan' ) );
			$table .= sprintf( '<th>%s</th>', __( 'Region(s)', 'dokan' ) );
			$table .= sprintf( '<th>%s</th>', __( 'Shipping Method', 'dokan' ) );
		$table .= '</tr></thead><tbody>';
		if( ! empty($zones) ) {
			foreach( $zones as $k_zone => $zone) {
				$table .= '<tr>';
					$table .= sprintf( '<td>%s</td>', '<a href="#edit_zone" data-id="'.$zone['zone_id'].'">'. $zone['zone_name'] .'</a>');
					$table .= sprintf( '<td>%s</td>', $zone['formatted_zone_location'] );
					
					if( empty($zone['shipping_methods']) ) {
						$ship_label = __( 'No method found', 'dokan' );
						$ship_label .= '&nbsp;<a href="#add_shipping_method" data-id="shipping-'. $zone['zone_id'].'-method">'. __( 'Add Shipping Method', 'dokan' ) .'</a>';
					}else {
						$data_method = array();
						foreach( $zone['shipping_methods'] as $method => $methods ) {
							$data_method[] = $methods['title'];
						}
						
						$ship_label = implode(' ', $data_method);
					}
					
					$table .= sprintf( '<td>%s</td>', $ship_label );
					
				$table .= '</tr>';
			}
		}else {
			$table .= '<tr><td colspan="3">'. esc_html__('No shipping zone found for configuration. Please contact with admin for manage your store shipping', 'dokan').'</tr>';
		}
		$table .= '</table>';
		
		echo $table;
		
		// $a = WC()->shipping->get_shipping->methods();


		foreach( $zones as $k_zone => $zone) {
			$get_zone_locations = $this->get_zone_locations($zone['zone_id'], $current_user->ID);
			?>
			<div id="shipping-<?php echo $zone['zone_id'];?>-method" class="add-shipping-method-wrapper">
				<div class="add-shipping-method">
					<?php
					if( ! empty($zone['zone_locations']) ) {
						foreach( $zone['zone_locations'] as $kzl => $zone_local) {
							echo sprintf( '<input type="hidden" name="data_country[%s][code]" value="%s" />', $kzl, $zone_local->code);
							echo sprintf( '<input type="hidden" name="data_country[%s][type]" value="%s" />', $kzl, $zone_local->type);
						}
					}?>
	
					<div class="row">
						<label class="col-4"><?php _e('Zone Name', 'dokan');?></label>
						<div class="col-8"><?php echo esc_attr($zone['zone_name']);?></div>
					</div>
					
					<div class="row">
						<label class="col-4"><?php _e('Zone Location', 'dokan');?></label>
						<div class="col-8"><?php echo esc_attr($zone['formatted_zone_location']);?></div>
					</div>
					
					<div class="row">
						<label class="col-4"><?php _e( 'Limit your zone location', 'dokan' );?></label>
						<div class="col-8">
							<label class="switch tips">
								<input type="checkbox" class="toogle-checkbox" id="data_country_enable" name="data_country[enable]" value="yes"<?php echo ! empty($get_zone_locations) ? ' checked' : '';?>> <span class="slider round"></span>
							</label>
						</div>
					</div>
					
					<div class="row dokan-postcode"<?php echo ! empty($get_zone_locations) ? '' : ' style="display: none;"';?>>
						<label class="col-4"><?php _e( 'Set your postcode', 'dokan' );?></label>
						<div class="col-8">
							<input type="text" class="form-control m-input " name="data_country[1][code]" id="data_country_postcode" placeholder="" value="<?php echo ! empty($get_zone_locations) ? $get_zone_locations->location_code : '';?>">
							<input type="hidden" name="data_country[1][type]" value="postcode" />
							<input type="hidden" name="data_country[zoneID]" value="<?php echo $zone['zone_id'];?>" />
							
						</div>
					</div>
				</div>
				<div class="zone-wrapper">
					<div class="dokan-section-heading">
						<h2><i aria-hidden="true" class="fa fa-truck"></i> Shipping Method</h2>
						<p>Add your shipping method for appropiate zone</p>
					</div>
					
					<table class="dokan-table zone-method-table">
						<thead>
							<tr>
								<th class="title" style="white-space: nowrap;">Method Title</th>
								<th class="enabled">Status</th>
								<th class="description">Description</th>
							</tr>
						</thead>
						<tbody>
							<?php 
							$shipping_data = array();
							if( ! empty($zone['shipping_methods']) ) {
								foreach( $zone['shipping_methods'] as $k_shipping => $shipping) :
								$shipping_data[] = $shipping['id'];?>
								<tr>
									<td style="white-space: nowrap;">
										<?php echo $shipping['title'];?>
										<div class="row-actions"><span class="edit"><a href="#" class="shipping-method-edit" data-zone="<?php echo $zone['zone_id'];?>" data-instance="<?php echo $shipping['instance_id'];?>" data-title="<?php echo $shipping['title'];?>" data-method="<?php echo $shipping['id'];?>"><?php _e('Edit');?></a> | </span> <span class="delete"><a href="#" class="shipping-method-delete" data-zone="<?php echo $zone['zone_id'];?>" data-instance="<?php echo $shipping['instance_id'];?>"><?php _e('Delete');?></a></span></div>
									</td>
									<td>
										<label class="switch tips">
											<input type="checkbox" class="toogle-checkbox" value="<?php echo $shipping['instance_id'];?>" checked> <span class="slider round"></span>
										</label>
									</td>
									<td><?php echo $load_shipping_methods[$shipping['id']]->method_description;?></td>
								</tr>
							<?php endforeach;
							}else {
								echo '<tr><td colspan="3">'. __( 'No method found', 'dokan' ) .'</td></tr>';
							}?>
							
						</tbody>
					</table>
					
					<div class="dokan-section-footer"><a href="#add-shipping-popup" class="btn dokan-btn-theme" data-id="<?php echo $zone['zone_id'];?>" data-methods="<?php echo htmlspecialchars( json_encode($shipping_data) );?>"><i class="fa fa-plus"></i> Add Shipping Method</a></div>
				</div>
			</div>
			<?php
		}
		
		?>
		<div id="add-shipping-popup" class="white-popup mfp-hide">
			<header class="modal-header">
				<h1><?php esc_html_e('Add Shipping Method', 'dokan');?></h1>
				<button title="Close (Esc)" type="button" class="mfp-close">×</button>
			</header>
			
			<div class="modal-body">
				<p><?php _e( 'Choose the shipping method you wish to add. Only shipping methods which support zones are listed.', 'dokan' );?></p> 
				<select id="shipping_method" name="shipping_method" class="form-control m-input ">
					<option value=""><?php _e( 'Select a Method', 'dokan' );?></option>
					<?php
					foreach ( $load_shipping_methods as $method ) {
						if ( ! $method->supports( 'shipping-zones' ) ) {
							continue;
						}
						
						if( $method->id != 'dokan_vendor_shipping' ) {
							echo '<option data-description="' . esc_attr( wp_kses_post( wpautop( $method->get_method_description() ) ) ) . '" value="' . esc_attr( $method->id ) . '">' . esc_attr( $method->get_method_title() ) . '</li>';
						}
					}
					?>
				</select>
			</div>
			
			<footer class="modal-footer">
				<div class="inner"><button class="btn btn-primary btn-submit-shipping-add"><?php esc_html_e('Add Shipping Method', 'dokan');?></button></div>
			</footer>
		</div>
		

		<?php
	}
	
	function show_section_regular_shipping() {
		$country_obj     = new WC_Countries();
		$countries       = $country_obj->countries;
		$user_id         = get_current_user_id();
		$processing_time = dokan_get_shipping_processing_times();
		
		$dps_enable_shipping     = get_user_meta( $user_id, '_dps_shipping_enable', true );
		$dps_shipping_type_price = get_user_meta( $user_id, '_dps_shipping_type_price', true );
		$dps_additional_product  = get_user_meta( $user_id, '_dps_additional_product', true );
		$dps_additional_qty      = get_user_meta( $user_id, '_dps_additional_qty', true );
		
		$dps_pt                  = get_user_meta( $user_id, '_dps_pt', true );
		$dps_shipping_policy     = get_user_meta( $user_id, '_dps_ship_policy', true );
		$dps_refund_policy       = get_user_meta( $user_id, '_dps_refund_policy', true );
				
		$dps_form_location       = get_user_meta( $user_id, '_dps_form_location', true );
		$dps_country_rates       = get_user_meta( $user_id, '_dps_country_rates', true );
		$dps_state_rates         = get_user_meta( $user_id, '_dps_state_rates', true );



		woopanel_form_field(
			'dps_enable_shipping',
			array(
				'type'		  => 'checkbox',
				'id'          => 'dps_enable_shipping',
				'label'       => __( 'Enable Shipping', 'dokan' ),
				'description' => __( 'Enable shipping functionality', 'dokan' ),
				'default'	  => 'yes',
				'form_inline' => true,
			),
			$dps_enable_shipping
		);
		
		woopanel_form_field(
			'dps_shipping_type_price',
			array(
				'type'		  => 'number',
				'id'          => 'dps_shipping_type_price',
				'label'       => __( 'Default Shipping Price', 'dokan' ),
				'placeholder' => wc_format_localized_price( 0 ),
				'form_inline' => true,
				'description' => __( 'This is the base price and will be the starting shipping price for each product', 'dokan' ),
			),
			$dps_shipping_type_price
		);
		
		woopanel_form_field(
			'dps_additional_product',
			array(
				'type'		  => 'number',
				'id'          => 'dps_additional_product',
				'label'       => __( 'Per Product Additional Price', 'dokan' ),
				'placeholder' => wc_format_localized_price( 0 ),
				'form_inline' => true,
				'description' => __( 'If a customer buys more than one type product from your store, first product of the every second type will be charged with this price', 'dokan' ),
			),
			$dps_additional_product
		);
		
		woopanel_form_field(
			'dps_additional_qty',
			array(
				'type'		  => 'number',
				'id'          => 'dps_additional_qty',
				'label'       => __( 'Per Qty Additional Price', 'dokan' ),
				'placeholder' => wc_format_localized_price( 0 ),
				'form_inline' => true,
				'description' => __( 'Every second product of same type will be charged with this price', 'dokan' ),
			),
			$dps_additional_qty
		);
		
		woopanel_form_field(
			'dps_pt',
			array(
				'type'	  => 'select',
				'id'      => 'dps_pt',
				'label'   => __( 'Processing Time', 'dokan' ),
				'options' => $processing_time,
				'description' => __('The time required before sending the product for delivery', 'dokan'),
				'form_inline' => true
			),
			$dps_pt
		);
		
		woopanel_form_field(
			'dps_shipping_policy',
			array(
				'type'		  => 'textarea',
				'id'          => 'dps_shipping_policy',
				'label'       => __( 'Shipping Policy', 'dokan' ),
				'description' => __('Write your terms, conditions and instructions about shipping', 'dokan'),
				'form_inline' => true
			),
			$dps_shipping_policy
		);
		
		woopanel_form_field(
			'dps_refund_policy',
			array(
				'type'		  => 'textarea',
				'id'          => 'dps_refund_policy',
				'label'       => __( 'Refund Policy', 'dokan' ),
				'description' => __('Write your terms, conditions and instructions about refund', 'dokan'),
				'form_inline' => true
			),
			$dps_refund_policy
		);
		
		woopanel_form_field(
			'dps_form_location',
			array(
				'type'	  => 'select',
				'id'      => 'dps_form_location',
				'label'   => __( 'Ships from:', 'dokan' ),
				'options' => $countries,
				'description' => __('Location from where the products are shipped for delivery. Usually it is same as the store.', 'dokan'),
				'form_inline' => true
			),
			$dps_form_location
		);
		
		?>
		<div class="woopanel-shipping-location-wrapper">
			<p class="dokan-page-help"><?php _e( 'Add the countries you deliver your products to. You can specify states as well. If the shipping price is same except some countries/states, there is an option <strong>Everywhere Else</strong>, you can use that.', 'dokan' ) ?></p>
			
			<div class="woopanel-shipping-location-table">
				<?php if ( $dps_country_rates ) : ?>
					<?php foreach ( $dps_country_rates as $country => $country_rate ) : ?>
					<table class="shipping_repeater">
						<thead>
							<tr>
								<th class="pm-row-zero"></th>
								<th class="pm-th" colspan="2"><?php _e( 'Ship to', 'dokan' ); ?></th>
								<th class="pm-row-zero"><a class="pm-icon repeater-minus small " href="#" data-event="remove-row" title="Remove row"></a></th>
							</tr>
							
							<tr class="pm-header-row">
								<th class="pm-row-zero"></th>

								<th class="pm-field" colspan="2">
									<div class="pm-input">
										<div class="pm-input-wrap">
											<select class="form-control m-input shipping-country-repeater" name="dps_country_to[]">
												<?php dokan_country_dropdown( $countries, $country, true ); ?>
											</select>
											<input type="hidden" placeholder="0.00" class="form-control m-input" name="dps_country_to_price[]">
										</div>
									</div>
								</th>
								
								<th class="pm-row-zero"></th>
							</tr>
						</thead>

						<tbody>
					   <?php if ( $dps_state_rates ): ?>
							<?php if ( isset( $dps_state_rates[$country] ) ): ?>

								<?php foreach ( $dps_state_rates[$country] as $state => $state_rate ): ?>

									<?php if ( isset( $states[$country] ) && !empty( $states[$country] ) ): ?>
									<tr class="pm-row">
										<td class="pm-row-zero order pm-handle ui-sortable-handle">
											<span>1</span>
										</td>

										<td class="pm-field">
											<div class="pm-input">
												<div class="pm-input-wrap">
													<input type="text" class="form-control m-input" name="dps_state_to[{country}][]" placeholder="<?php esc_html_e('State name', 'dokan');?>">
												</div>
											</div>
										</td>
										<td class="pm-field">
											<div class="pm-input">
												<div class="pm-input-wrap">
													<input type="text" placeholder="0.00" class="form-control m-input" name="dps_state_to_price[{country}][]">
												</div>
											</div>
										</td>
										<td class="pm-row-zero">
											<a class="pm-icon repeater-plus small" href="#" data-event="add-row" title="Add row"></a>
											<a class="pm-icon repeater-minus small" href="#" data-event="remove-row" title="Remove row"></a>
										</td>
									</tr>
									<?php else: ?>
									<tr class="pm-row">
										<td class="pm-row-zero order pm-handle ui-sortable-handle">
											<span>1</span>
										</td>

										<td class="pm-field">
											<div class="pm-input">
												<div class="pm-input-wrap">
													<input type="text" class="form-control m-input" name="dps_state_to[<?php echo $country ?>][]" placeholder="<?php esc_html_e('State name', 'dokan');?>" value="<?php echo $state; ?>">
												</div>
											</div>
										</td>
										<td class="pm-field">
											<div class="pm-input">
												<div class="pm-input-wrap">
													<input type="text" placeholder="0.00" class="form-control m-input" name="dps_state_to_price[<?php echo $country ?>][]" value="<?php echo $state_rate; ?>">
												</div>
											</div>
										</td>
										<td class="pm-row-zero">
											<a class="pm-icon repeater-plus small" href="#" data-event="add-row" title="Add row"></a>
											<a class="pm-icon repeater-minus small" href="#" data-event="remove-row" title="Remove row"></a>
										</td>
									</tr>
									<?php endif ?>
									
									<?php endforeach ?>

								<?php endif ?>

							<?php endif ?>
						</tbody>
					</table>
					<?php endforeach;
				else: ?>
				<table class="shipping_repeater">
					<thead>
						<tr>
							<th class="pm-row-zero"></th>
							<th class="pm-th"><?php _e( 'Ship to', 'dokan' ); ?></th>
							<th class="pm-th"><?php _e( 'Cost', 'dokan' ); ?></th>
							<th class="pm-row-zero"><a class="pm-icon repeater-minus small " href="#" data-event="remove-row" title="Remove row"></a></th>
						</tr>
						
						<tr class="pm-header-row">
							<th class="pm-row-zero"></th>

							<th class="pm-field">
								<div class="pm-input">
									<div class="pm-input-wrap">
										<select class="form-control m-input shipping-country-repeater" name="dps_country_to[]">
											<?php dokan_country_dropdown( $countries, '', true ); ?>
										</select>
									</div>
								</div>
							</th>
							
							<th class="pm-field">
								<div class="pm-input">
									<div class="pm-input-wrap">
										<input type="text" placeholder="0.00" class="form-control m-input" name="dps_country_to_price[]">
									</div>
								</div>
							</th>
							
							<th class="pm-row-zero"></th>
						</tr>
					</thead>

					<tbody></tbody>
				</table>
				<?php endif; ?>
			</div>
			<button type="button" name="save" class="btn m-btn m-btn--wide m-btn--md m-loader--light m-loader--right btn-add-location"><i class="flaticon-plus"></i> <?php _e( 'Add Location', 'dokan' ); ?></button>
		</div>
		
		<script id="tmpl-shipping-repeater" type="text/html">
			<tr class="pm-row">
				<td class="pm-row-zero order pm-handle ui-sortable-handle">
					<span>1</span>
				</td>

				<td class="pm-field">
					<div class="pm-input">
						<div class="pm-input-wrap">
							<input type="text" class="form-control m-input" name="dps_state_to[{country}][]" placeholder="<?php esc_html_e('State name', 'dokan');?>">
						</div>
					</div>
				</td>
				<td class="pm-field">
					<div class="pm-input">
						<div class="pm-input-wrap">
							<input type="text" placeholder="0.00" class="form-control m-input" name="dps_state_to_price[{country}][]">
						</div>
					</div>
				</td>
				<td class="pm-row-zero">
					<a class="pm-icon repeater-plus small" href="#" data-event="add-row" title="Add row"></a>
					<a class="pm-icon repeater-minus small" href="#" data-event="remove-row" title="Remove row"></a>
				</td>
			</tr>
		</script>
		
		<script id="tmpl-shipping-table" type="text/html">
			<table class="shipping_repeater">
				<thead>
					<tr>
						<th class="pm-row-zero"></th>
						<th class="pm-th"><?php _e( 'Ship to', 'dokan' ); ?></th>
						<th class="pm-th"><?php _e( 'Cost', 'dokan' ); ?></th>
						<th class="pm-row-zero"><a class="pm-icon repeater-minus small " href="#" data-event="remove-row" title="Remove row"></a></th>
					</tr>
					
					<tr class="pm-header-row">
						<th class="pm-row-zero"></th>

						<th class="pm-field">
							<div class="pm-input">
								<div class="pm-input-wrap">
									<select class="form-control m-input shipping-country-repeater" name="dps_country_to[]">
										<?php dokan_country_dropdown( $countries, '', true ); ?>
									</select>
								</div>
							</div>
						</th>
						
						<th class="pm-field">
							<div class="pm-input">
								<div class="pm-input-wrap">
									<input type="text" placeholder="0.00" class="form-control m-input" name="dps_country_to_price[]">
								</div>
							</div>
						</th>
						
						<th class="pm-row-zero"></th>
					</tr>
				</thead>

				<tbody></tbody>
			</table>
		</script>
		<?php
	}

    /**
     * Get Shipping Methods for a zone
     *
     * @since 2.8.0
     *
     * @return void
     */
    public static function get_shipping_methods( $zone_id, $seller_id ) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}dokan_shipping_zone_methods WHERE `zone_id`={$zone_id} AND `seller_id`={$seller_id}";
        $results = $wpdb->get_results( $sql );

        $method = array();

        foreach ( $results as $key => $result ) {
            $default_settings = array(
                'title'       => self::get_method_label( $result->method_id ),
                'description' => __( 'Lets you charge a rate for shipping', 'dokan' ),
                'cost'        => '0',
                'tax_status'  => 'none'
            );

            $method_id = $result->method_id .':'. $result->instance_id;
            $settings = ! empty( $result->settings ) ? maybe_unserialize( $result->settings ) : array();
            $settings = wp_parse_args( $settings, $default_settings );

            $method[$method_id]['instance_id'] = $result->instance_id;
            $method[$method_id]['id']          = $result->method_id;
            $method[$method_id]['enabled']     = ( $result->is_enabled ) ? 'yes' : 'no';
            $method[$method_id]['title']       = $settings['title'];
            $method[$method_id]['settings']    = array_map( 'stripslashes_deep', maybe_unserialize( $settings ) );
        }

        return $method;
    }
	
    /**
     * get Shipping method label
     *
     * @since 2.8.0
     *
     * @return void
     */
    public static function get_method_label( $method_id ) {
        if ( 'flat_rate' == $method_id ) {
            return __( 'Flat Rate', 'dokan' );
        } elseif ( 'local_pickup' == $method_id ) {
            return __( 'Local Pickup', 'dokan' );
        } elseif( 'free_shipping' == $method_id ) {
            return __( 'Free Shipping', 'dokan' );
        } else {
            return __( 'Custom Shipping', 'dokan' );
        }
    }
	
    /**
     * Save zone location for seller
     *
     * @since 2.8.0
     *
     * @return void
     */
    public static function save_location( $location, $zone_id ) {
        global $wpdb;

        // Setup arrays for Actual Values, and Placeholders
        $values        = array();
        $place_holders = array();
        $seller_id     = dokan_get_current_user_id();
        $table_name    = "{$wpdb->prefix}dokan_shipping_zone_locations";

        $query = "INSERT INTO {$table_name} (seller_id, zone_id, location_code, location_type) VALUES ";

        if ( ! empty( $location ) ) {
            foreach( $location as $key => $value ) {
                array_push( $values, $seller_id, $zone_id, $value['code'], $value['type'] );
                $place_holders[] = "('%d', '%d', '%s', '%s')";
            }

            $query .= implode(', ', $place_holders);

            $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE zone_id=%d AND seller_id=%d", $zone_id, $seller_id ) );

            if ( $wpdb->query( $wpdb->prepare( "$query ", $values ) ) ) {
                return true;
            }
        } else {
            if( $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE zone_id=%d AND seller_id=%d", $zone_id, $seller_id ) ) ) {
                return true;
            }
        }

        return false;
    }
	
	public function get_zone_locations($zone_id, $vendor_id) {
		global $wpdb;
		
		return $wpdb->get_row( "SELECT zone_id, location_code FROM {$wpdb->prefix}dokan_shipping_zone_locations WHERE location_type = 'postcode' AND zone_id = {$zone_id} AND seller_id = {$vendor_id};" );

	}
	
	public function edit_html_shipping() {
		?>
		<div id="edit-shipping-popup" class="white-popup mfp-hide">
			<form id="edit-shipping-form" method="POST" action="">
				<header class="modal-header">
					<h1><?php esc_html_e('Edit Shipping Method', 'dokan');?></h1>
					<button title="Close (Esc)" type="button" class="mfp-close">×</button>
				</header>
				
				<div class="modal-body m-form">
					<div class="woopanel-form-group">
						<label for="method_title"><?php _e('Title', 'dokan');?></label>
						<input type="text" id="method_title" name="data[settings][title]" placeholder="<?php _e("Enter method title", "dokan");?>" class="form-control m-input">
					</div>
					
					<div class="woopanel-form-group">
						<label for="method_cost"><?php _e('Cost', 'dokan');?></label>
						<input type="text" id="method_cost" name="data[settings][cost]" placeholder="0.00" class="form-control m-input">
						<p class="m-form__help"><?php _e( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>. Use <code>[qty]</code> for the number of items, <code>[cost]</code> for the total cost of items, and <code>[fee percent=\'10\' min_fee=\'20\' max_fee=\'\']</code> for percentage based fees.', 'dokan' );?></p>
					</div>
					
					<div class="woopanel-form-group">
						<label for="tax_status"><?php _e('Tax Status', 'dokan');?></label>
						<select id="method_tax_status" name="data[settings][tax_status]" class="form-control m-input">
							<option value=""></option>
							<option value="none"><?php _e( 'None', 'dokan' );?></option>
							<option value="taxable"><?php _e( 'Taxable', 'dokan' );?></option>
						</select>
					</div>
					
					<div class="woopanel-form-group">
						<label for="method_description"><?php _e('Description', 'dokan');?></label>
						<textarea rows="3" id="method_description" name="data[settings][description]" class="form-control m-input"><?php _e( 'Lets you charge a rate for shipping', 'dokan' );?></textarea>
					</div>
					
					<hr />
					<h3><?php _e( 'Shipping Class Cost', 'dokan' );?></h3>
					<p><?php _e( 'These costs can optionally be added based on the product shipping class', 'dokan' );?></p>
					
					<div class="woopanel-form-group">
						<label for="no_class_cost"><?php _e('No shipping class cost', 'dokan');?></label>
						<input type="text" id="method_no_class_cost" name="data[settings][no_class_cost]" placeholder="N\A" class="form-control m-input">
						<p class="m-form__help"><?php _e( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>. Use <code>[qty]</code> for the number of items, <code>[cost]</code> for the total cost of items, and <code>[fee percent=\'10\' min_fee=\'20\' max_fee=\'\']</code> for percentage based fees.', 'dokan' );?></p>
					</div>
					
					<div class="woopanel-form-group">
						<label for="calculation_type"><?php _e('Calculation type', 'dokan');?></label>
						<select id="method_calculation_type" name="data[settings][calculation_type]" class="form-control m-input">
							<option value=""></option>
							<option value="class"><?php _e( 'Per class: Charge shipping for each shipping class individually', 'dokan' );?></option>
							<option value="order"><?php _e( 'Per order: Charge shipping for the most expensive shipping class', 'dokan' );?></option>
						</select>
					</div>
					
				</div>
				<footer class="modal-footer">
					<input type="hidden" id="instance_id" name="data[instance_id]" />
					<input type="hidden" id="method_id" name="data[method_id]" />
					<input type="hidden" id="zoneID" name="zoneID" />
					<div class="inner"><button type="submit" class="btn btn-primary btn-submit-shipping-edit"><?php _e( 'Save Settings', 'dokan' );?></button></div>
				</footer>
			</form>
		</div>
		<?php
	}
	
}

new NBT_Solutions_Dokan_Setting_Shipping();