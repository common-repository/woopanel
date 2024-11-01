<?php


	/**
	* Total products
	* @author anhlt <netbasejsc@gmail.com>
	* @since 0.1.0
    */

    if( ! function_exists('woopanel_total_users') ) {
        function woopanel_total_users() {
            global $wpdb;

            if( is_woo_installed() ) {
                $woopanel_total_users = get_transient( 'woopanel_total_users' );

                if( ! $woopanel_total_users ) {
                    $custom_meta = 'a:1:{s:8:"customer";b:1;}';

                    $query = array();
                    $query['fields'] = "SELECT COUNT(user.ID) as total_user FROM {$wpdb->base_prefix}usermeta as usermeta";
                    $query['join']   = "INNER JOIN {$wpdb->base_prefix}users AS user ON user.ID = usermeta.user_id";
                    $query['where']	 = "WHERE usermeta.meta_key LIKE 'wp_capabilities' AND usermeta.meta_value = '". $custom_meta ."' ORDER BY ID DESC";

                    $sql = implode(' ', $query);
                    $query = $wpdb->get_row( $sql );

                    $woopanel_total_users = $query->total_user;
                }
            }else {
                $woopanel_total_users = 0;
            }

            if( ($woopanel_total_users/1000) > 1) {
                return '<span data-toggle="tooltip" data-placement="top" data-original-title="'. number_format($woopanel_total_users, 0, ',', '.') .'">'. woopanel_nice_number($woopanel_total_users) .'</span>';
            }

            return $woopanel_total_users;
        }
    }

    if( ! function_exists('woopanel_format_statistic') ) {
        function woopanel_format_statistic($number) {
            if( ($number/1000) > 1) {
                return '<span data-toggle="tooltip" data-placement="top" data-original-title="'. number_format($number, 0, ',', '.') .'">'. woopanel_nice_number($number) .'</span>';
            }

            return $number;
        }
    }


    if( ! function_exists('woopanel_get_order_status') ) {
        function woopanel_get_order_status($status) {
            if( is_woo_installed()) {
                $statuses = wc_get_order_statuses();
                
                if( isset($statuses['wc-' . $status]) ) {
                    return $statuses['wc-' . $status];
                }
            }
        }
    }

    /* Function which displays your post date in time ago format */
    if( ! function_exists('woopanel_chartorder_default') ) {
        function woopanel_chartorder_default() {
            $user = wp_get_current_user();
            $options_val = get_user_meta( $user->ID, 'personal_options' );

            if( is_woo_installed() ) {
                $sales_by_date  = new WooPanel_Report_Order();
                $sales_by_date->start_date = strtotime( 'monday this week' );
                $sales_by_date->end_date = strtotime( 'sunday this week' );

                if( isset( $options_val[0]['report_from']) &&
                    !empty( $options_val[0]['report_from'] ) &&
                    isset( $options_val[0]['report_to']) &&
                    !empty( $options_val[0]['report_to'] ) &&
                    strtotime( $options_val[0]['report_to'] ) >= strtotime( $options_val[0]['report_from'] )
                ){
                    $sales_by_date->start_date = strtotime( $options_val[0]['report_from'] );
                    $sales_by_date->end_date = strtotime( $options_val[0]['report_to'] );
                }

        
                $sales_by_date->chart_groupby  = 'day';
                $sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
        
                return $sales_by_date->get_report_status_data();
            }
        }
    }


    /* Function which displays your post date in time ago format */
    if( ! function_exists('woopanel_chartamount_default') ) {
        function woopanel_chartamount_default() {
            $user = wp_get_current_user();
            $options_val = get_user_meta( $user->ID, 'personal_options' );

            if( is_woo_installed() ) {
                $sales_by_date  = new WooPanel_Report_Order();
                $sales_by_date->start_date = strtotime( 'monday this week' );
                $sales_by_date->end_date = strtotime( 'sunday this week' );

                if( isset( $options_val[0]['report_from']) &&
                    !empty( $options_val[0]['report_from'] ) &&
                    isset( $options_val[0]['report_to']) &&
                    !empty( $options_val[0]['report_to'] ) &&
                    strtotime( $options_val[0]['report_to'] ) >= strtotime( $options_val[0]['report_from'] )
                ){
                    $sales_by_date->start_date = strtotime( $options_val[0]['report_from'] );
                    $sales_by_date->end_date = strtotime( $options_val[0]['report_to'] );
                }

                $sales_by_date->chart_groupby  = 'day';
                $sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
        
                return $sales_by_date->get_report_filter_data();
            }
        }
    }
    
    if( ! function_exists('woopanel_dashboard_filter_range') ) {
        function woopanel_dashboard_filter_range() {
            return array(
                'this-week' => __('This Week', 'woopanel'),
                'last-week' => __('Last Week', 'woopanel'),
                'this-month' => __('This Month', 'woopanel'),
                'last-month' => __('Last Month', 'woopanel'),
                'custom-range' => __('Custom Range', 'woopanel')
            );
        }
    }
    if( ! function_exists('woopanel_get_all_users') ) {
        function woopanel_get_all_users() {
            global $wpdb;
        
            $get_total_user = get_transient( 'woopanel_get_total_user' );
            if ( empty($get_total_user) ) {
                $custom_meta = '"customer"';
        
                $query = array();
                $query['fields'] = "SELECT * FROM {$wpdb->base_prefix}usermeta as usermeta";
                $query['join']   = "INNER JOIN {$wpdb->base_prefix}users AS user ON user.ID = usermeta.user_id";
                $query['where']	 = "WHERE usermeta.meta_key LIKE 'wp_capabilities' AND usermeta.meta_value LIKE '%". $custom_meta ."%' ORDER BY ID DESC LIMIT 5";
        
                $sql = implode(' ', $query);
                $get_total_user = $wpdb->get_results( $sql );
            }
        
            return $get_total_user;
        }
    }

    if( ! function_exists('woopanel_get_best_customers') ) {
        function woopanel_get_best_customers() {
            global $wpdb, $current_user;
            $get_best_customers = get_transient( 'woopanel_get_total_user' );
        
            if( is_woo_available() ) {
                if ( empty($get_total_user) ) {
                    $query = array();
                    $query['fields'] = "SELECT SUM(order_total.meta_value) as price, user.*  FROM {$wpdb->posts} as posts";
                    $query['join']   = "INNER JOIN {$wpdb->base_prefix}usermeta AS usermeta ON usermeta.meta_key LIKE '" . $wpdb->prefix ."capabilities' ";
                    $query['join']   .= "INNER JOIN {$wpdb->prefix}postmeta AS order_total ON posts.ID = order_total.post_id ";
                    $query['join']   .= "INNER JOIN {$wpdb->prefix}postmeta AS postmeta ON posts.ID = postmeta.post_id ";

                    $query['join']  .= "INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON (posts.ID = order_items.order_id) AND (order_items.order_item_type = 'line_item') ";
                    $query['join']  .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__product_id ON (order_items.order_item_id = order_item_meta__product_id.order_item_id)  AND (order_item_meta__product_id.meta_key = '_product_id') ";
                    $query['join']  .= "INNER JOIN {$wpdb->posts} AS products ON order_item_meta__product_id.meta_value = products.ID ";
                    $query['join']   .= "INNER JOIN {$wpdb->prefix}users AS user ON postmeta.meta_value = user.ID";
                    $query['where']   = "WHERE posts.post_type = 'shop_order' ";
                    
                    // Permission
                    if( ! is_shop_staff(false, true) ) {
                        $query['where']  .= "AND products.post_author = ".$current_user->ID . " ";
                    }
                    
                    $query['where']	 .= "AND posts.post_status IN ( 'wc-" . implode( "','wc-", array( 'completed', 'processing' ) ) . "' ) ";
                    $query['where']	 .= "AND postmeta.meta_key = '_customer_user' AND postmeta.meta_value = usermeta.user_id ";
                    $query['where']	 .= "AND order_total.meta_key = '_order_total' AND order_total.meta_value > 0 ";
                    $query['groupby'] = "GROUP BY postmeta.meta_value ORDER BY price DESC LIMIT 5";
            
                    $sql = implode( ' ', apply_filters( 'woocommerce_dashboard_status_widget_top_seller_query', $query ) );
            
                    $get_best_customers = $wpdb->get_results( $sql );
                }
            }
        
            return $get_best_customers;
        }
    }

    if( ! function_exists('woopanel_get_total_sales') ) {
        function woopanel_get_total_sales($product_id) {
            global $wpdb;
            $get_total_sales = get_transient( 'woopanel_get_total_sales' );
        
            if( is_woo_available() ) {
                if ( empty($get_total_sales) ) {
                    $query = array();
                    $query['fields'] = "SELECT COUNT(order_itemmeta2.meta_value) as total_sales FROM {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta";
                    $query['join']   = "INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON order_itemmeta.order_item_id = order_items.order_item_id ";
                    $query['join']   .= "INNER JOIN {$wpdb->posts} AS orders ON order_items.order_id = orders.ID ";
                    $query['join']   .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_itemmeta2 ON order_itemmeta.order_item_id = order_itemmeta2.order_item_id";
                    $query['where']   = "WHERE order_itemmeta.meta_key = '_product_id' ";
                    $query['where']	 .= "AND order_itemmeta.meta_value = " . $product_id ." ";
                    $query['where']	 .= "AND order_itemmeta2.meta_key = '_line_total' ";
                    $query['where']	 .= "AND order_itemmeta2.meta_value != 0 ";
                    $query['where']	 .= "AND orders.post_status = 'wc-completed'";
        
                    $sql = implode( ' ', apply_filters( 'woocommerce_dashboard_status_widget_top_product_query', $query ) );
        
                    $rs = $wpdb->get_row($sql);
                    $get_total_sales = 0;
                    if( $rs ) {
                        $get_total_sales = $rs->total_sales;
                    }
                }
            }
        
            return $get_total_sales;
        }
    }

    /* Function which displays your post date in time ago format */
    if( ! function_exists('woopanel_time_ago') ) {
        function woopanel_time_ago($time) {
            $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
            $lengths = array("60","60","24","7","4.35","12","10");

            $now = time();

                $difference     = $now - $time;
                $tense         = "ago";

            for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
                $difference /= $lengths[$j];
            }

            $difference = round($difference);

            if($difference != 1) {
                $periods[$j].= "s";
            }

            return "$difference $periods[$j] ago";
        }
    }