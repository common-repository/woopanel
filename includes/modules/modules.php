<?php
class WooPanel_Modules {

    public $modules = array();

    function __construct()
    {
        $this->modules = array(
            'price-matrix' => array(
                'label' => 'Price Matrix',
                'class' => 'Price_Matrix',
                'file'  => 'price-matrix.php',
                'description' => __('Allow customers easy enter price for variation product.', 'woopanel')
            ),
            'color-swatches' => array(
                'label' => 'Color Swatches',
                'class' => 'Color_Swatches',
                'file'  => 'color-swatches.php',
                'description' => __('Change default select box of attributes to something more intuitive: Radio images, Radio buttons, Color, etc.', 'woopanel')
            ),
            'dokan' => array(
                'label' => 'Dokan',
                'class' => 'Dokan',
                'file'  => 'dokan.php',
                'description' => __('Module for Dokan', 'woopanel')
            )
        );

        $this->load_modules();
        add_action( 'wp_enqueue_scripts', array($this, 'frontend_embed_assets') );

    }

    public function load_modules() {
        $woopanel_modules = $this->modules;

        if( ! empty($woopanel_modules) ) {
            if( isset($_GET['id']) ) {
                $GLOBALS['_post'] = get_post($_GET['id']);
            }
            foreach( $woopanel_modules as $module_name => $module) {
                $_module_name = str_replace('-', '_', $module_name);
                $load_module = WOOPANEL_INC_DIR . 'modules/'. $module_name .'/'. $module['file'];
        
                $load_setting = WOOPANEL_INC_DIR . 'modules/'. $module_name .'/inc/settings.php';
        
        
                if( file_exists($load_setting) ) {
        
                    include_once $load_setting;
        
                    $settings = get_option($module_name . '_settings');
        
                    if( ! $settings && class_exists('NBT_' . $module['class'] . '_Settings') && method_exists('NBT_' . $module['class'] . '_Settings', 'settings') ) {
                        $module_setting = call_user_func('NBT_' . $module['class'] . '_Settings::settings');
        
                        if( is_array($module_setting) ) {
                            $settings = array();
                            foreach( $module_setting as $key => $set) {
                                if( isset($set['id']) ) {
                                    $settings[$set['id']] = $set['default'];
                                }
                            }
                        }
                    }
        
                    $GLOBALS[$_module_name . '_settings'] = $settings;
                }
        
                if( file_exists($load_module) ) {
                    include_once $load_module;
                }
            }
        }
    }

    public function frontend_embed_assets() {

        $localize_array = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'geoLocation' => array(
                'ApplicationID' => 'uPpJlH7GwJ5VFivyyrjn',
                'ApplicationCode' => 'jt713YZorNYpkHhGCkelOQ',
            )
        );
        wp_enqueue_style( 'woopanel-modules', WOOPANEL_URL . 'assets/css/modules.css',false,'1.1','all');
        wp_enqueue_script( 'woopanel-modules', WOOPANEL_URL . 'assets/js/frontend-modules.js', array( 'jquery' ) );


        wp_localize_script( 'woopanel-modules', 'wplModules', $localize_array);
    }
}

$woo_modules = new WooPanel_Modules();

$GLOBALS['woopanel_modules'] = $woo_modules->modules;