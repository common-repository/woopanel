<?php
define('NB_DOKAN_PATH', plugin_dir_path( __FILE__ ));
define('NB_DOKAN_URL', plugin_dir_url( __FILE__ ));

class NBT_Solutions_Dokan {
    static $plugin_id = 'dokan';
    /**
     * Variable to hold the initialization state.
     *
     * @var  boolean
     */
    protected static $initialized = false;

    public static $types = array();
    
    /**
     * Initialize functions.
     *
     * @return  void
     */
    public static function initialize() {
        // Do nothing if pluggable functions already initialized.
        if ( self::$initialized ) {
            return;
        }

        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            add_action( 'admin_notices', array( __CLASS__, 'install_woocommerce_admin_notice') );
        }else{
			
			if( class_exists('WeDevs_Dokan') ) {
				include NB_DOKAN_PATH .'settings/dokan-store.php';
				include NB_DOKAN_PATH .'settings/dokan-payment.php';
			}
			
			if( class_exists('Dokan_Pro') ) {
				include NB_DOKAN_PATH .'settings/dokan-shipping.php';
				include NB_DOKAN_PATH .'settings/dokan-social.php';
				include NB_DOKAN_PATH .'settings/dokan-seo.php';
			}
			
			include NB_DOKAN_PATH .'inc/ajax.php';
        }
        // State that initialization completed.
        self::$initialized = true;
    }


    /**
     * Method Featured.
     *
     * @return  array
     */
    public static function install_woocommerce_admin_notice() {?>
        <div class="error">
            <p><?php _e( 'WooCommerce plugin is not activated. Please install and activate it to use for plugin <strong>Ajax Drop Down Cart for WooCommerce Wordpress</strong>.', 'nbt-ajax-cart' ); ?></p>
        </div>
        <?php    
    }
}

NBT_Solutions_Dokan::initialize();