<?php
/**
 * Plugin Name: WooPanel – FrontEnd Manager for Store Manager and WooCommerce Vendor
 * Plugin URL: http://demo9.cmsmart.net/demo-woopanel/sellercenter/
 * Description: You are the store/ vendor manager and you want to get everything in the easiest way to know how your business works well or not.  WooPanel is the right plugin that you must to integrate in your site. Your articles, products, orders, coupons, customers will be arraigned clean and neat to bring you a general look with optimized UX/UI compare to our other competitors.
 * Version: 2.0.0
 * Author: NetBase team
 * Author URI: http://netbaseteam.com
 * Text Domain: woopanel
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

final class NBWooPanel {

    public $version = '2.0.0';
    public $query = null;
    public $shortcodes = null;
    public $session = null;
    static $permission = array(
        'shop_manager', 'seller'
    );

    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        $this->query->init();
    }
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new NBWooPanel();
        }

        return $instance;
    }

    private function init_hooks() {
        register_activation_hook( __FILE__, array( 'WooPanel_Installer', 'do_install' ) );
        add_action( 'init', array($this, 'load_textdomain') );
        add_action( 'wp_before_admin_bar_render', array( $this, 'woopanel_admin_toolbar' ) );
        add_action( 'admin_init', array( $this, 'woopanel_block_wp_admin' ) );
        add_filter( 'plugin_action_links_'. plugin_basename(__FILE__), array( &$this, 'woopanel_plugin_action_links' ) );
        add_action( 'activate_woocommerce/woocommerce.php', array($this, 'clear_cache') );
    }

    private function define_constants() {
        $this->define( 'WOOPANEL_DIR', plugin_dir_path( __FILE__ ) );
        $this->define( 'WOOPANEL_URL', plugin_dir_url( __FILE__ ) );
        $this->define( 'WOOPANEL_FILE', __FILE__ );
        $this->define( 'WOOPANEL_INC_DIR', plugin_dir_path( __FILE__ ) .'includes/' );
        $this->define( 'WOOPANEL_VIEWS_DIR', plugin_dir_path( __FILE__ ) .'views/' );
        $this->define( 'WOOPANEL_TEMPLATE_DIR', plugin_dir_path( __FILE__ ) .'templates/' );
        $this->define( 'WOOPANEL_TEMPLATE_DEBUG', false );

        include_once WOOPANEL_INC_DIR . "global.php";
    }

    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    private function includes() {
        include_once WOOPANEL_INC_DIR . "classes/class-session.php";
        include_once WOOPANEL_INC_DIR ."woopanel-functions.php";
        include_once WOOPANEL_DIR . 'admin/woopanel-admin-options.php';
        include_once WOOPANEL_INC_DIR . 'helper/woopanel-options.php';


        include_once WOOPANEL_INC_DIR . "classes/class-rewrites.php";
        include_once WOOPANEL_INC_DIR . "classes/class-installer.php";
        include_once WOOPANEL_INC_DIR . "classes/class-shortcodes.php";
        include_once WOOPANEL_INC_DIR . "classes/class-template.php";

        
        include_once WOOPANEL_INC_DIR . 'helper/woopanel-actions.php';

        include_once WOOPANEL_INC_DIR . 'fields/woopanel-form-fields.php';
        include_once WOOPANEL_INC_DIR . 'helper/woopanel-notice.php';
        
        include_once WOOPANEL_INC_DIR . "classes/dashboard/class-dashboard-report.php";
        include_once WOOPANEL_INC_DIR . "classes/dashboard/class-dashboard-report-order.php";
        include_once WOOPANEL_INC_DIR . "classes/dashboard/class-dashboard-ajax.php";

        if( !is_admin() ) {
            include_once WOOPANEL_INC_DIR . 'walkers/class-woopanel-walker-category.php';

            include_once WOOPANEL_INC_DIR . 'helper/woopanel-post.php';
            include_once WOOPANEL_INC_DIR . 'helper/woopanel-list-table.php';
            
            include_once WOOPANEL_INC_DIR . 'helper/woopanel-template.php';
            include_once WOOPANEL_INC_DIR . 'helper/woopanel-menus.php';
            include_once WOOPANEL_INC_DIR . 'helper/woopanel-permalinks.php';
            include_once WOOPANEL_INC_DIR . 'helper/woopanel-filter.php';
            include_once WOOPANEL_INC_DIR . 'helper/woopanel-pagination.php';
            
            include_once WOOPANEL_INC_DIR . 'helper/woopanel-dashboard.php';
            include_once WOOPANEL_INC_DIR . "helper/woopanel-metabox.php";

            include_once WOOPANEL_INC_DIR . 'woocommerce.php';

            include_once WOOPANEL_INC_DIR . "classes/class-list-table.php";
            include_once WOOPANEL_INC_DIR . "classes/class-list-post-table.php";
            include_once WOOPANEL_INC_DIR . 'classes/class-taxonomy.php';

            // WooPanel Pages
            include_once WOOPANEL_INC_DIR . "pages/woopanel-dashboard.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-article.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-product.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-product-categories.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-product-tags.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-product-attributes.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-coupon.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-customer.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-order.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-comment.php";
            include_once WOOPANEL_INC_DIR . "pages/woopanel-review.php";
            

            include_once WOOPANEL_INC_DIR . 'helper/woopanel-actions.php';

            $this->shortcodes = new WooPanel_Shortcodes();
        }

        include_once WOOPANEL_INC_DIR . 'modules/modules.php';

        include_once WOOPANEL_INC_DIR . "pages/woopanel-faq.php";
        include_once WOOPANEL_INC_DIR . "classes/live-chat/class-live-chat.php";
        include_once WOOPANEL_INC_DIR . 'helper/woopanel-post-function.php';
        include_once WOOPANEL_INC_DIR . 'woopanel-ajax.php';

        $this->query = new WooPanel_Rewrites();
        $this->session = new WooPanel_Session();
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'woopanel', false, WOOPANEL_DIR . 'languages' ); 
    }

    function woopanel_admin_toolbar() {
        global $wp_admin_bar;

        if ( ! is_shop_staff() ) {
            return;
        }

        $args = array(
            'id'     => 'woopanel',
            'title'  => __( 'WooPanel', 'woopanel' ),
            'href'   => woopanel_dashboard_url()
        );

        $wp_admin_bar->add_menu( $args );

        if( woopanel_dashboard_url() ) {
            $wp_admin_bar->add_menu(array(
                'id' => 'woopanel-dashboard',
                'parent' => 'woopanel',
                'title' => __('Seller Center', 'woopanel'),
                'href' => woopanel_dashboard_url()
            ));
            if( current_user_can('administrator') ) {
                $wp_admin_bar->add_menu(array(
                    'id' => 'woopanel-settings',
                    'parent' => 'woopanel',
                    'title' => __( 'Settings' ),
                    'href' => admin_url( 'options-general.php?page=woopanel-settings' )
                ));
            }

            if (is_admin()) {
                $wp_admin_bar->add_menu(array(
                    'id' => 'view-dashboard',
                    'parent' => 'site-name',
                    'title' => __('Visit Seller Center', 'woopanel'),
                    'href' => woopanel_dashboard_url()
                ));
            }
        }
    }

    function woopanel_block_wp_admin() {
        if ( is_admin() &&
            ( WooPanel_Admin_Options::get_option('block_wp_admin') == 'yes' ) &&
            !current_user_can( 'administrator' ) &&
            !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            wp_die( __('You need a higher level of permission.') );
        }
    }

    function woopanel_plugin_action_links( $links ) {
        $action_links = array(
            'settings' => '<a href="' . admin_url( 'options-general.php?page=woopanel-settings' ) . '" aria-label="' . esc_attr__( 'View settings' ) . '">' . esc_html__( 'Settings' ) . '</a>',
        );

        $action_links = array_merge( $action_links, $links );

        return $action_links;
    }

    function clear_cache() {
        $this->session->set( 'woopanel_notices', null );
    }

    function plugin_url( $slug = null ){
        return WOOPANEL_URL . $slug;
    }
}

if ( ! function_exists( 'WooPanel' ) ) {
    function WooPanel() {
        return NBWooPanel::init();
    }
}

WooPanel();