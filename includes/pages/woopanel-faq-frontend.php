<?php
class WooPanel_Template_FAQ_Frontend {
	/**
	 * Class constructor.
	 */
	public function __construct() {
        add_filter( 'woocommerce_product_tabs', array($this, 'faq_product_tab') );
        add_shortcode( 'wpl_faq', array($this, 'shortcode_faqs') );
        add_action('wp_enqueue_scripts', array($this, 'embed_style'));
    }

    public function faq_product_tab( $tabs ) {
        global $post;
        
		$data = get_post_meta($post->ID, '_nbt_faq', true);

		if($data){
			// Adds the new tab
			$tabs['faq_tab'] = array(
				'title' 	=> __( 'Product FAQs', 'woopanel' ),
				'priority' 	=> 20,
				'callback' 	=> array($this, 'faq_product_tab_content')
			);
		}

		return $tabs;
    }

	function faq_product_tab_content() {
		global $post;
		echo do_shortcode('[wpl_faq id='.$post->ID.']');
    }
    
	public function shortcode_faqs($args, $content){
		global $post;

		if(isset($args['id'])){
			$post->ID = $args['id'];
		}

		$data = get_post_meta($post->ID, '_nbt_faq', true);

		include WOOPANEL_VIEWS_DIR .'faqs/frontend-shortcode.php';
    }
    
	/**
	 * Enqueue scripts and stylesheets
	 */
	public function embed_style() {

			wp_enqueue_style( 'faq-frontend', WOOPANEL_URL .'assets/css/frontend-faqs.css', array(), '20160615' );


		
		wp_enqueue_script( 'js-md5', WOOPANEL_URL . 'assets/frontend/js/md5.min.js', '', '', true );

		wp_enqueue_script( 'faq-frontend', WOOPANEL_URL . 'assets/js/frontend-faqs.js', array( 'jquery' ), time(), true );

	}
}

new WooPanel_Template_FAQ_Frontend();