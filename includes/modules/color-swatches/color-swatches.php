<?php
/**
 * @version    1.0
 * @package    Package Name
 * @author     Your Team <support@yourdomain.com>
 * @copyright  Copyright (C) 2014 yourdomain.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 */

/**
 * Plug additional sidebars into WordPress.
 *
 * @package  Package Name
 * @since    1.0
 */
define('NBT_SWATCHES_PATH', plugin_dir_path( __FILE__ ));
define('NBT_SWATCHES_URL', plugin_dir_url( __FILE__ ));

class NBT_Solutions_Color_Swatches {

    static $plugin_id = 'color-swatches';
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

        if ( ! function_exists( 'WC' ) ) {
            add_action( 'admin_notices', array( __CLASS__, 'install_woocommerce_admin_notice') );
        }else{
            self::$types = array(
                'color' => esc_html__( 'Color', 'wcvs' ),
                'image' => esc_html__( 'Image', 'wcvs' ),
                'radio' => esc_html__( 'Radio', 'wcvs' ),
                'label' => esc_html__( 'Label', 'wcvs' ),
            );

            add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
            add_filter( 'product_attributes_type_selector', array( __CLASS__, 'add_attribute_types' ) );


            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                require_once 'inc/ajax.php';
                NBT_Color_Swatches_Ajax::initialize();
            }
            
            require_once NBT_SWATCHES_PATH . 'inc/admin.php';
            require_once NBT_SWATCHES_PATH . 'inc/frontend.php';

            if( is_admin() && ! get_option('nbcs_update_db') && function_exists( 'WC' ) ) {
                require_once 'inc/class.update-db.php';
            }
        }
        // Register actions to do something.
        //add_action( 'action_name'   , array( __CLASS__, 'method_name'    ) );
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
            <p><?php _e( 'WooCommerce plugin is not activated. Please install and activate it to use for plugin <strong>NBT WooCommerce Price Matrix</strong>.', 'nbt-ajax-cart' ); ?></p>
        </div>
        <?php    
    }


    /**
     * Load plugin text domain
     */
    public static function load_textdomain() {
        load_plugin_textdomain( 'wcvs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Add extra attribute types
     * Add color, image and label type
     *
     * @param array $types
     *
     * @return array
     */
    public static function add_attribute_types( $types ) {
        $extra = array(
            'woocommerce_add_attribute',
            'woocommerce_save_attributes'
        );

        if( isset($_POST['action']) && in_array($_POST['action'], $extra) ) {
            return $types;
        }

        return array_merge( $types, self::$types );
    }

    /**
     * Get attribute's properties
     *
     * @param string $taxonomy
     *
     * @return object
     */
    public static function get_tax_attribute( $taxonomy ) {
        global $wpdb;

        $attr = substr( $taxonomy, 3 );
        $attr = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '$attr'" );

        return $attr;
    }

    public static function get_style(){
        return array(
            'square' => __('Square', 'nbtcs'),
            'circle' => __('Circle', 'nbtcs')

        );
    }

    public static function get_attribute_taxonomies($product_id, $attribute_name) {
        $transient_name = 'wc_attr_tax_' . md5($product_id . $attribute_name);
        $attribute_taxonomies = get_transient( $transient_name );
        if ( false === $attribute_taxonomies ) {
            global $wpdb;

            $attribute_taxonomies = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . "woocommerce_attribute_taxonomies WHERE attribute_name = '$attribute_name'" );

            set_transient( $transient_name, $attribute_taxonomies );
        }
        //delete_transient( $transient_name );

        return $attribute_taxonomies;
    }
}


NBT_Solutions_Color_Swatches::initialize();