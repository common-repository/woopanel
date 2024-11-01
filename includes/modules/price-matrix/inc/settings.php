<?php
if( ! class_exists('NBT_Price_Matrix_Settings') ) {
    class NBT_Price_Matrix_Settings{

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
                'show_on' => array(
                    'name' => __( 'Price matrix table position', 'woopanel' ),
                    'type' => 'select',
                    // 'desc' => __( 'Vị trí hiển thị bảng bên cạnh ảnh hoặc phía dưới ảnh', 'woopanel' ),
                    'id'   => 'wc_price-matrix_show_on',
                    'options'       => array(
                        'default'   => __( 'Default', 'woopanel' ),
                        'before_tab'    => __( 'Before Tab', 'woopanel' )
                    ),
                    'default' => 'default'
                ),
                'hide_info' => array(
                    'name' => __( 'Hide Additional information', 'woopanel' ),
                    'type' => 'checkbox',
                    'id'   => 'wc_price-matrix_hide_info',
                    'default' => false,
                    'desc' => __('Hide Additional information tab in Product Details', 'woopanel')
                ),
                'show_calculator' => array(
                    'name' => __( 'Show calculator text', 'woopanel' ),
                    'type' => 'checkbox',
                    'id'   => 'wc_price-matrix_show_calculator',
                    'default' => false,
                    'desc' => __('Show calculator text after Add to cart button', 'woopanel')                
                ),
                'is_heading' => array(
                    'name' => __( 'Enable heading', 'woopanel' ),
                    'type' => 'checkbox',
                    'id'   => 'wc_price-matrix_is_heading',
                    'default' => false,
                    'desc' => __('Turn on heading before Price Matrix table', 'woopanel')                
                ),
                'heading_label' => array(
                    'name' => __( 'Heading title', 'woopanel' ),
                    // 'desc' => __( 'Hiển thị tiêu đề heading ở trên bảng', 'woopanel' ),
                    'type' => 'text',
                    'id'   => 'wc_price-matrix_heading',
                    'default' => ''
                ),
                'is_scroll' => array(
                    'name' => __( 'Scroll when select price', 'woopanel' ),
                    'type' => 'checkbox',
                    'id'   => 'wc_price-matrix_is_scroll',
                    'default' => false,
                    'desc' => __('Scroll the screen to the Price Matrix table when user choose attributes', 'woopanel')                                
                ),
                'is_show_sales' => array(
                    'name' => __( 'Display regular & sale price', 'woopanel' ),
                    // 'desc' => __( 'Hiển thị giá giảm trong bảng', 'woopanel' ),
                    'type' => 'checkbox',
                    'id'   => 'wc_price-matrix_show_sales',
                    'default' => false,
                    'desc' => __('Display the sale price in the Price Matrix table', 'woopanel')                                
                ),
                array(
                    'type' => 'border'
                ),
                'table_bg' => array(
                    'name' => __( 'Background color of price matrix table', 'woopanel' ),
                    // 'desc' => __( 'Chọn màu chính cho bảng', 'woopanel' ),
                    'type' => 'color',
                    'id'   => 'wc_price-matrix_color_table',
                    'default' => '#efefef',
                ),
                'table_color' => array(
                    'name' => __( 'Table Text color', 'woopanel' ),
                    // 'desc' => __( 'Chọn màu chữ cho bảng', 'woopanel' ),
                    'type' => 'color',
                    'id'   => 'wc_price-matrix_color_text',
                    'default' => '#333'
                ),
                'border_color' => array(
                    'name' => __( 'Table Border color', 'woopanel' ),
                    // 'desc' => __( 'Chọn màu border cho bảng', 'woopanel' ),
                    'type' => 'color',
                    'id'   => 'wc_price-matrix_color_border',
                    'default' => '#ccc'
                ),
                array(
                    'type' => 'border'
                ),
                'bg_tooltip' => array(
                    'name' => __( 'Tooltips background color', 'woopanel' ),
                    // 'desc' => __( 'Chọn background color cho tooltip', 'woopanel' ),
                    'type' => 'color',
                    'id'   => 'wc_price-matrix_bg_tooltip',
                    'default' => '#efefef'
                ),
                'color_tooltip' => array(
                    'name' => __( 'Tooltips text color', 'woopanel' ),
                    // 'desc' => __( 'Chọn màu chữ cho tooltip', 'woopanel' ),
                    'type' => 'color',
                    'id'   => 'wc_price-matrix_color_tooltip',
                    'default' => '#333'
                ),
                'border_tooltip' => array(
                    'name' => __( 'Tooltips border color', 'woopanel' ),
                    // 'desc' => __( 'Chọn border cho tooltip', 'woopanel' ),
                    'type' => 'color',
                    'id'   => 'wc_price-matrix_border_tooltip',
                    'default' => '#ccc'
                ),
                'font_size' => array(
                    'name' => __( 'Font Size', 'woopanel' ),
                    // 'desc' => __( 'Chọn kích thước chữ trong bảng', 'woopanel' ),
                    'type' => 'number',
                    'desc' => 'px',
                    'id'   => 'wc_price-matrix_font_size',
                    'default' => 14,
                    'min' => 14,
                    'max' => 50,
                    'step' => 1
                )
            );
            return $settings;
        }
    }
}