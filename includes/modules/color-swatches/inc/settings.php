<?php
if( ! class_exists('NBT_Color_Swatches_Settings') ) {
    class NBT_Color_Swatches_Settings{

        protected static $initialized = false;

        public static function initialize() {
            // Do nothing if pluggable functions already initialized.
            if ( self::$initialized ) {
                return;
            }
            

            // State that initialization completed.
            self::$initialized = true;
        }

        public static function get_settings() {
            $settings = array(
                'width' => array(
                    'name' => __( 'Width', 'woopanel' ),
                    // 'desc' => __( 'Chọn kích thước chữ trong bảng', 'woopanel' ),
                    'type' => 'number',
                    'desc' => 'px',
                    'id'   => 'wc_color_swatches_width',
                    'default' => 30,
                    'min' => 30,
                    'max' => 200,
                    'step' => 1
                )
            );
            return $settings;
        }
    }
}