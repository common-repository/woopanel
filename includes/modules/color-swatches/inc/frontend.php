<?php
class NBT_Color_Swatches_Frontend {
	/**
	 * Class constructor.
	 */
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action('woocommerce_before_add_to_cart_form', array($this, 'is_single_product') );
		add_filter('woocommerce_is_attribute_in_product_name', array($this, 'woocommerce_is_attribute_in_product_name'), 20, 2 );

		add_filter( 'body_class', array($this, 'nbt_body_classes'), 10, 1 );
	}
	
	public function nbt_body_classes( $classes ) {
		global $post, $pm_settings;

		if(isset($post) && get_post_meta($post->ID, '_color_swatches', true) == 'on' || isset($post) && get_post_meta($post->ID, '_color_swatches', true) == 'yes'){
			$classes[] = 'has-color_swatches';
		}
	     
	    return $classes;  
	}
	
	public function woocommerce_is_attribute_in_product_name($attribute, $name){
		return false;
	}
	public function is_single_product(){
		global $product;

		if( get_post_meta($product->get_id(), '_color_swatches', true) == 'on' || get_post_meta($product->get_id(), '_color_swatches', true) == 'yes' ) {

			add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'get_swatch_html' ), 100, 2 );
			add_filter( 'nbtcs_swatch_html', array( $this, 'swatch_html' ), 5, 4 );
			add_filter( 'nbtcs_swatch_html_custom', array( $this, 'custom_swatch_html' ), 5, 3 );
		}
	}
	/**
	 * Enqueue scripts and stylesheets
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( 'js-md5', NBT_SWATCHES_URL . 'assets/frontend/js/md5.min.js', array( 'jquery' ), time(), true );
		
		if( !defined('PREFIX_NBT_SOL') ) {
			wp_enqueue_style( 'frontend-nbtcs', NBT_SWATCHES_URL . 'assets/css/frontend.css'  );
			wp_enqueue_script( 'frontend-nbtcs', NBT_SWATCHES_URL . 'assets/js/frontend.js', null, null, true );
			wp_localize_script( 'frontend-nbtcs', 'nbt_solutions', array(
				'ajax_url' => admin_url( 'admin-ajax.php' )
			));		
		}

	}

	/**
	 * Filter function to add swatches bellow the default selector
	 *
	 * @param $html
	 * @param $args
	 *
	 * @return string
	 */
	public function get_swatch_html( $html, $args ) {
		global $product;

		$swatches = $class = '';
		$swatch_types = NBT_Solutions_Color_Swatches::$types;
		$get_attributes = $product->get_attributes( 'edit' );
		$nb_color_swatches = get_post_meta($product->get_id(), '_nb_color_swatches', true);

		if( isset($args['attribute']) && preg_match('/< *select[^>]*name *= *["\']?([^"\']*)/i', $html, $matches) ) :
			$attribute_name = trim( str_replace('attribute_', '', $matches[1]) );
			if( isset($nb_color_swatches[$attribute_name]) && isset($get_attributes[$attribute_name]) ) :
				$cs = $nb_color_swatches[$attribute_name];
				if ( array_key_exists( $cs['type'], $swatch_types ) ) :
					$attribute = $get_attributes[$attribute_name];
					if ( $attribute->is_taxonomy() && ( $attribute_taxonomy = $attribute->get_taxonomy_object() ) ) :
						$terms = $attribute->get_terms();
						$wc_attribute_tax = NBT_Solutions_Color_Swatches::get_attribute_taxonomies( $product->get_id(), str_replace('pa_', '', $attribute_name) );
					else :
						
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
						$wc_attribute_tax = new stdClass();
						$wc_attribute_tax->attribute_type = $cs['type'];
						$wc_attribute_tax->attribute_label = $args['attribute'];
						$wc_attribute_tax->attribute_name = $attribute_name;
					endif;

					if( $terms ) :

						if($wc_attribute_tax->attribute_type == 'radio' || $cs['type'] == 'radio' ){
							$swatches .= '<ul class="swatches-radio">';
						}

						foreach ($terms as $key => $term) :
							$swatches .= $this->render_color_swatches( $term, $wc_attribute_tax, array(
								'cs' => $cs,
								'attr' => $attribute_name
							) );
						endforeach;

						if($wc_attribute_tax->attribute_type == 'radio' || $cs['type'] == 'radio' ){
							$swatches .= '</ul>';
						}
					endif;
				endif;
			endif;
		endif;

		if( $swatches ) :
			$class .= 'nbcs-hidden';

			$swatches = '<div class="nbtcs-swatches" data-attribute_name="' . esc_attr( $args['attribute'] ) . '">' . $swatches . '</div>';
			$html     = '<div class="' . esc_attr( $class ) . '">' . $html . '</div>' . $swatches;
		endif;

		return $html;
	}

	/**
	 * Print HTML of a single swatch
	 *
	 * @param $html
	 * @param $term
	 * @param $attr
	 * @param $args
	 *
	 * @return string
	 */
	public function render_color_swatches($term, $attribute, $args) {
		global $product, $color_swatches_settings;

		$default = $html = $selected = '';


		$default_attribute = $product->get_default_attributes();


		if( isset($args['attr']) && isset($default_attribute[$args['attr']]) && $default_attribute[$args['attr']] == $term->slug ) {
			$selected = ' selected';
			$default = $default_attribute[$args['attr']];
		}

		if( isset($args['cs']) ) {
			$cs = $args['cs'];
			$style = isset($cs['style']) ? $cs['style'] : "square";
			switch ( $cs['type'] ) {
				case 'color':
					$color = get_term_meta( $term->term_id, 'color', true );
					
					if( isset($cs['repeater'][$term->slug]) && ! empty($cs['repeater']) ) {
						$color = $cs['repeater'][$term->slug];
					}
	
					list( $r, $g, $b ) = sscanf( $color, "#%02x%02x%02x" );

					
	
					 $html = sprintf(
						'<span class="swatch swatch-color %s %s" style="background-color:%s;color:%s;%s" title="%s" data-value="%s"></span>',
						esc_attr( $style ),
						$selected,
						esc_attr( $color ),
						"rgba($r,$g,$b,0.5)",
						'width: ' . $color_swatches_settings['wc_color_swatches_width'] . 'px; height: ' . $color_swatches_settings['wc_color_swatches_width'] . 'px;',
						esc_attr( $term->name ),
						esc_attr( $term->slug )
					);
	
					break;
				case 'image':
					$image = get_term_meta( $term->term_id, 'image', true );
					if( isset($cs['repeater']) && ! empty($cs['repeater']) ) {
						$image = $cs['repeater'][$term->slug];
					}

					$image = $image ? wp_get_attachment_image_src( $image ) : '';
					$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
	
					$html = sprintf(
						'<span class="swatch swatch-image %s %s" title="%s" data-value="%s" style="%s"><img src="%s" alt="%s"></span>',
						esc_attr( $style ),
						$selected,
						esc_attr( $term->name ),
						esc_attr( $term->slug ),
						'width: ' . $color_swatches_settings['wc_color_swatches_width'] . 'px; height: ' . $color_swatches_settings['wc_color_swatches_width'] . 'px;',
						$image,
						esc_attr( $term->slug )
					);
					break;
				case 'label':
					$html = sprintf(
						'<span class="swatch swatch-text %s %s" title="%s" data-value="%s" style="%s">%s</span>',
						esc_attr( $style ),
						$selected,
						esc_attr( $term->name ),
						esc_attr( $term->slug ),
						'width: ' . $color_swatches_settings['wc_color_swatches_width'] . 'px; height: ' . $color_swatches_settings['wc_color_swatches_width'] . 'px; line-height: ' . ($color_swatches_settings['wc_color_swatches_width'] - 1) . 'px;',
						esc_attr( $term->name )
					);
					break;
				case 'radio':
	
					$html = sprintf(
						'<li>
						<input id="%s" type="radio" name="nbt_cs_%s" value="%s" %s>
						<label for="%s" class="swatch swatch-radio" title="%s" data-value="%s"> %s</label><div class="check %s"></div></li>',
						esc_attr( $term->slug ),
						$attribute->attribute_name,
						esc_attr( $term->slug ),
						($default == $term->slug) ? ' checked' : false,
						esc_attr( $term->slug ),
						esc_attr( $term->slug ),
						esc_attr( $term->slug ),
						esc_attr( $term->name ),
						$style
					);
					break;
				default:
					break;
			}	
		}

		return $html;
	}

	/**
	 * Print HTML of a single swatch
	 *
	 * @param $html
	 * @param $term
	 * @param $attr
	 * @param $args
	 *
	 * @return string
	 */
	public function swatch_html( $html, $term, $attr, $args ) {
		
		$selected = $checked = '';
		$name     = esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) );


		$style = '';
		if(isset($args['style']) && isset(NBT_Solutions_Color_Swatches::get_style()[$args['style']])){
			$style = $args['style'];
		}

		$cs = $args['cs'];

/* 		echo '<pre>';
		print_r($cs);
		echo '</pre>'; */
		switch ( $attr->attribute_type ) {
			case 'color':
				$color = get_term_meta( $term->term_id, 'color', true );
				if( isset($cs['repeater']) && ! empty($cs['repeater']) ) {
					$color = $cs['repeater'][$term->slug];
				}
				list( $r, $g, $b ) = sscanf( $color, "#%02x%02x%02x" );

/* 				$html = sprintf(
					'<span class="swatch swatch-color swatch-%s%s" style="background-color:%s;color:%s;%s" title="%s" data-value="%s">%s</span>',
					esc_attr( $term->slug ),
					$selected,
					esc_attr( $color ),
					"rgba($r,$g,$b,0.5)",
					$args['swatch_css'],
					esc_attr( $name ),
					esc_attr( $term->slug ),
					$name
				); */

				$html = 'xxxxxxx';

				break;
			case 'image':
				$image = get_term_meta( $term->term_id, 'image', true );
				if(isset($args['data_alt']['value']) && $args['data_alt']['value']){
					$image = $args['data_alt']['value'][$args['key']];
				}
				$image = $image ? wp_get_attachment_image_src( $image ) : '';
				$image = $image ? $image[0] : WC()->plugin_url() . '/assets/images/placeholder.png';
				$html  = sprintf(
					'<span class="%s swatch swatch-image swatch-%s%s" title="%s" data-value="%s" style="%s"><img src="%s" alt="%s">%s</span>',
					$style,
					esc_attr( $term->slug ),
					$selected,
					esc_attr( $name ),
					esc_attr( $term->slug ),
					$args['swatch_css'],
					esc_url( $image ),
					esc_attr( $name ),
					esc_attr( $name )
				);
				break;

			case 'radio':
				if(!isset($term->is_taxonomy)){
					$name = $term->slug;
				}
				$label = get_term_meta( $term->term_id, 'radio', true );
				$label = $label ? $label : $name;
				$html  = sprintf(
					'<li><input type="radio" name="nbt_cs_%s" value="%s" id="%s_%d"%s>
					<label for="%s_%d" class="swatch swatch-radio" title="%s" data-value="%s"> %s</label><div class="check %s"></div></li>',
					$attr->attribute_name,
					esc_attr( $name ),
					$attr->attribute_name,
					$term->term_id,
					$checked,
					$attr->attribute_name,
					$term->term_id,
					esc_attr( $term->slug ),
					esc_attr( $name ),
					esc_attr( $term->name ),
					$style
				);
				break;
			case 'label':
				if(!isset($term->is_taxonomy)){
					$name = $term->slug;

				}
				$label = get_term_meta( $term->term_id, 'radio', true );
				$label = $label ? $label : $name;
				$html  = sprintf(
					'<span class="swatch swatch-text %s" title="%s" data-value="%s" style="%s">%s</span>',
					$style,
					esc_attr( $name ),
					esc_attr( $name ),
					$args['swatch_css'],
					esc_attr( $term->name )
				);
				break;
		}
		return $html;
	}
}
new NBT_Color_Swatches_Frontend();