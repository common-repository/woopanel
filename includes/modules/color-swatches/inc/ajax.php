<?php
class NBT_Color_Swatches_Ajax{

	protected static $initialized = false;
	
    /**
     * Initialize functions.
     *
     * @return  void
     */
    public static function initialize() {
        if ( self::$initialized ) {
            return;
        }

	    self::admin_hooks();
        self::$initialized = true;
    }


    public static function admin_hooks(){
		add_action( 'wp_ajax_nopriv_cs_load_variations', array( __CLASS__, 'cs_load_variations') );
		add_action( 'wp_ajax_cs_load_variations', array( __CLASS__, 'cs_load_variations') );

		add_action( 'wp_ajax_nopriv_cs_load_style', array( __CLASS__, 'cs_load_style') );
		add_action( 'wp_ajax_cs_load_style', array( __CLASS__, 'cs_load_style') );

		add_action( 'wp_ajax_nopriv_cs_save', array( __CLASS__, 'cs_save') );
		add_action( 'wp_ajax_cs_save', array( __CLASS__, 'cs_save') );

		add_action( 'wp_ajax_nbcs_update_required', array( __CLASS__, 'nbcs_update_required') );
    }


    public static function nbcs_update_required() {
    	global $wpdb;
    	$json = array();

    	$color_swatches = get_transient('id_color_swatches');
    	$step = $_REQUEST['step'];
    	if( $color_swatches && is_numeric($step) && isset($color_swatches[$step]) ) {
    		$data = $color_swatches[$step];

    		$rs = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_color_swatches' AND meta_value = 'on' AND post_id = '". $data['id']."'" );

    		$json['curren_step'] = $step;
    		if( ! $rs ) {
    			$json['error'] = true;
    			$json['message'] = 'Not found';
    		}else {
    			$results_run = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_test_swatches' AND meta_value = 'on' AND post_id = '". $data['id']."'" );
    			if( ! $results_run ) {
    				$next_step = ($step + 1);
					$product = wc_get_product($data['id']);
					$old_data = get_post_meta($product->get_id(), '_cs_type', true);

					$new_attr = array();
					$get_attributes = $product->get_attributes( 'edit' );
					foreach( $get_attributes as $attribute_name => $attribute ) {
						if ( $attribute->is_taxonomy() && ( $attribute_taxonomy = $attribute->get_taxonomy_object() ) ) {
							$terms = $attribute->get_terms();
						} else {
							$array = array();
							foreach ($attribute->get_options() as $key => $value) {
								$array[] = array(
									'term_id' => $key,
									'taxonomy' => $attribute_name,
									'name' => trim($value),
									'slug' => trim($value),
									'is_taxonomy' => false
								);
							}
							$terms = json_decode(json_encode($array), FALSE);
						}

						$new_attr[$attribute_name] = $terms;
					}




					if( ! empty($old_data) ) {
						$new_data = array();
						foreach( $old_data as $k_data => $v) {
							$new_data[$k_data] = array(
								'type' => $v['type'],
								'style' => $v['style']
							);

							if( $v['type'] == 'color' && ! empty($v['value']) || $v['type'] == 'image' && ! empty($v['value']) ) {
								$new_value = array();
								foreach( $new_attr[$k_data] as $k => $term) {
									$new_value[$term->slug] = $v['value'][$k];
								}

								$new_data[$k_data]['repeater'] = $new_value;
							}
						}

       					update_post_meta($product->get_id(), '_nb_color_swatches', $new_data);
					}

    				if(isset($color_swatches[$next_step]) ) {
	    				$json['complete'] = true;
	    				$json['next'] = $next_step;
	    				$json['message'] = '<span style="color: green">Successfully!</span>';
	    			}else {
	    				$json['end'] = true;
	    				$json['message'] = 'Data updated!';
    					$json['not_found'] = true;
    					update_option('nbcs_update_required', true);
	    			}

   					NB_Solution::log('======= ' . $data['id'] . '=========');
   					NB_Solution::log('---NEW DATA---');
   					NB_Solution::log($new_data);
    			}
    		}

    	}else {
    		$json['message'] = 'Data updated!';
    		$json['not_found'] = true;
    	}



    	$rs = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = '_color_swatches' AND meta_value = 'on' ORDER BY post_id ASC" );




    	wp_send_json($json);
    }

    public static function cs_load_variations(){
		$nonce = $_REQUEST['security'];

		if ( ! wp_verify_nonce( $nonce, 'load-variations' ) ) {
		     die( 'Security check' ); 
		} else {
			$json = array();
			$exclude_type = array('select', 'radio', 'label');
			global $wpdb, $woocommerce;

			$product_id = isset( $_POST['product_id'] ) ? wc_clean( $_POST['product_id'] ) : '';
			$is_admin = ! empty( $_POST['is_admin'] ) ? $_POST['is_admin'] : false;

			$product = wc_get_product($product_id);
			$cs = get_post_meta($product->get_id(), '_nb_color_swatches', true);
			$attributes = $product->get_attributes( 'edit' );

			if( $attributes ):
				$json['complete'] = true;
				foreach ($attributes as $key_tax => $attribute) :
					$key_tax = esc_attr($key_tax);

					if ( $attribute->is_taxonomy() && ( $attribute_taxonomy = $attribute->get_taxonomy_object() ) ) :
						$terms = $attribute->get_terms();
						$wc_attribute_tax = NBT_Solutions_Color_Swatches::get_attribute_taxonomies( $product->get_id(), str_replace('pa_', '', $key_tax) );
						$attribute_label = $wc_attribute_tax->attribute_label;
						$attribute_type = $wc_attribute_tax->attribute_type;
					else:			
						$array = array();
						foreach ($attribute->get_options() as $key => $value) {
							$array[] = array(
								'term_id' => $key,
								'taxonomy' => $attribute_name,
								'name' => trim($value),
								'slug' => trim($value),
								'is_taxonomy' => false
							);
						}

						$terms = json_decode(json_encode($array), FALSE);
						$attribute_label = $attribute->get_name();
						$wc_attribute_tax = new stdClass();
						$attribute_type = $cs[$key_tax]['type'];
					endif;

					if( isset($cs[$key_tax]['type']) ) {
						$attribute_type = $cs[$key_tax]['type'];
					}

					ob_start();
					
					if ( $is_admin ) {
						include NBT_SWATCHES_PATH .'tpl/admin/panels.php';
					}else {
						include NBT_SWATCHES_PATH .'tpl/admin/woopanel-panels.php';
					}
					$html .= ob_get_clean();
				endforeach;
				$json['html'] = $html;
			endif;
		}

		wp_send_json($json);
    }

    public static function cs_load_style(){
		$nonce = $_REQUEST['security'];

		if ( ! wp_verify_nonce( $nonce, 'load-variations' ) ) {

		     die( 'Security check' ); 

		} else {
			$exclude_type = array('select', 'radio', 'label');
			$json = array();
			global $wpdb, $woocommerce;

			$product_id = isset( $_POST['product_id'] ) ? wc_clean( $_POST['product_id'] ) : '';
			$product = wc_get_product($product_id);
			$cs = get_post_meta($product->get_id(), '_nb_color_swatches', true);
			$attributes = $product->get_attributes( 'edit' );


			$row = $_POST['row'];
			$key_tax = $_POST['tax'];
			$type = $_POST['type'];


			$product = wc_get_product($product_id);



			$html = '';
    		$json['complete'] = true;

    		if($attributes):
    			

    
    				ob_start();?>

									<?php if($type != 'radio'){?>
											<table class="pm_repeater<?php echo $selected;?>">
												<thead>
													<tr>
														<th class="pm-row-zero" style="width: 5%"></th>
														<th class="pm-th" style="width: 50%">Value</th>
														<th class="pm-th" style="width: 45%">Display</th>
													</tr>
												</thead>
												<tbody>
												<?php $get_attributes = $product->get_attributes( 'edit' );
												if( isset($get_attributes[$key_tax]) ){
													$_attribute = $get_attributes[$key_tax];
													$terms = array();

													$_cs_type = get_post_meta($product->get_id(), '_cs_type', TRUE);
 	

											
													if ( $_attribute->is_taxonomy() ) :
														$terms = json_decode(json_encode($_attribute->get_terms()), true);
													else :

														$value_array = $_attribute->get_options();
														$terms = array();

														foreach ($value_array as $key => $value) {
															$terms[] = array(
																'term_id' => $key,
																'taxonomy' => $attr_id,
																'name' => trim($value),
																'slug' => trim($value),
																'is_taxonomy' => false
															);
														}
													endif;

				





													if($terms){
														foreach ($terms as $key => $term) {
															$value = get_term_meta( $term['term_id'], $type, true );
											    			if(isset($_cs_type[$key_tax]['value'])){
											    				$value = $_cs_type[$key_tax]['value'][$key];
												    		}
															?>
													<tr class="pm-row">
														<td class="pm-row-zero order">
															<span><?php echo ($key+1);?></span>
														</td>

														<td class="pm-field">
															<div class="pm-input">
																<div class="pm-input-wrap">
																	<select class="form-control m-input pm-attributes-field" name="alt_css" data-option="0">
																		<option value="<?php echo $term['name'];?>"><?php echo $term['name'];?></option>
																	</select>
																</div>
															</div>
														</td>
														<td class="pm-field">
															<div class="pm-input">
																<div class="pm-input-wrap">
																	<?php NBT_Color_Swatches_Admin::show_field($type, 'color_swatches['. $key_tax .'][repeater]['. $term['slug'] .']', $value);?>
																</div>
															</div>
														</td>
													</tr>
													<?php }
													}?>
												</tbody>
												<?php }?>
											</table>
									<?php }?>
    			<?php



    				$html .= ob_get_clean();

    		endif;

    		$json['html'] = $html;
 
    	}

    	echo wp_json_encode($json);
    	wp_die();
    }


    public function cs_save(){
    	$product_id = intval($_REQUEST['product_id']);
    	$product = wc_get_product($product_id);

    	$types = $_REQUEST['type'];
    	$tax = $_REQUEST['tax'];
    	$style = $_REQUEST['style'];
    	$custom = $_REQUEST['custom'];


    	$new_array = array();
    	foreach ($types as $key => $type) {
    		$value = $custom[$key][1][$type];
    		$new_array[$tax[$key]] = array(
    			'type' => $type,
    			'style' => $style[$key],
    			'value' => $value
    		);
    	}

    	update_post_meta($product->get_id(), '_cs_type', $new_array);

    	$json['complete'] = true;
 	
        echo wp_json_encode($json);
    	wp_die();	
    }
}