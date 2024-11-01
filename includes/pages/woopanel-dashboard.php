<?php
class WooPanel_Template_Dashboard {
    public $widgets = array();

    private $statistic_status = array('completed');
    private $order_status = array( 'completed', 'processing', 'on-hold' );
    private $start_date; // format Y-m-d H:i:s
    private $end_date; // format Y-m-d H:i:s

    private $chart_start_date;
    private $chart_end_date;
    private $chart_default = 'this-week';

    private $cache = false;
    private $personal_options;

    public function __construct() {
        global $current_user;

        //add_filter('woopanel_dashboard_widget', array($this, 'set_woopanel_dashboard_widget'), 20, 1);

        $this->widgets      = $this->config();
        //$this->order_status = array_keys( wc_get_order_statuses() );
        //$this->start_date = date( 'Y-m-d H:i:s', strtotime("first day of this month") );
        //$this->end_date   = date( 'Y-m-d H:i:s', strtotime("last day of this month") );

        $this->chart_start_date = strtotime( 'monday this week' );
        $this->chart_end_date   = strtotime( 'sunday this week' );
        
        $this->personal_options = get_user_meta($current_user->ID, 'personal_options', true);
        if( ! empty($this->personal_options['report_from']) && ! empty($this->personal_options['report_to']) ) {
            $this->chart_default = 'custom-range';
            $this->chart_start_date = strtotime($this->personal_options['report_from']);
            $this->chart_end_date = strtotime($this->personal_options['report_to']);
        }

    }

    public function config() {

        $dashboard_sections = array(
            'dashboard_chart_order' => array(
                'cols'      => 'col-12 col-lg-8',
                'label'     => __('Chart Orders', 'woopanel'),
                'template'  => 'chart-order.php',
                'enable'    => is_woo_available()
            ),
            'dashboard_best_seller' => array(
                'cols'      => 'col-12 col-lg-4',
                'label'     => __('Best Sellers', 'woopanel'),
                'template'  => 'best-seller.php',
                'enable'    => is_woo_available()
            ),
            'dashboard_chart_amount' => array(
                'cols'      => 'col-12 col-lg-8',
                'label'     => __('Chart Amount', 'woopanel'),
                'template'  => 'chart-amount.php',
                'enable'    => is_woo_available()
            ),
            'dashboard_new_customer' => array(
                'cols'      => 'col-12 col-lg-4',
                'label'     => __('New Customers', 'woopanel'),
                'template'  => 'new-customer.php',
                'enable'    => is_woo_available()
            ),
            'dashboard_best_product' => array(
                'cols'      => 'col-12 col-lg-6',
                'label'     => __('Best Products', 'woopanel'),
                'template'  => 'best-product.php',
                'enable'    => is_woo_available()
            ),
            'dashboard_recent_order' => array(
                'cols'      => 'col-12 col-lg-6',
                'label'     => __('Recent Orders', 'woopanel'),
                'template'  => 'recent-order.php',
                'enable'    => is_woo_available(),
            ),
            'dashboard_recent_review' => array(
                'cols'      => 'col-12 col-lg-6',
                'label'     => __('Recent Reviews', 'woopanel'),
                'template'  => 'recent-review.php',
                'enable'    => is_woo_available()
            )
        );

        return apply_filters('woopanel_dashboard_widget', $dashboard_sections);
    }

    public function display() {
        global $current_user;

        
        
        $recent_orders = $query_seller = array();
        if( is_woo_installed() ) {
            $getAllStatus = wc_get_order_statuses();
            $recent_orders = $this->get_recent_orders();
            $total_orders = $this->get_recent_orders(true);
            
            $total_unpaid_order = $this->get_recent_orders(true, array('wc-processing'));

            $statistic_total_revenue = $this->get_statistic_total_revenue();
            $statistic_total_products = $this->get_statistic_total_products();
            $statistic_total_orders = $this->get_statistic_total_orders();
            $statistic_total_users = (new WooPanel_Template_Customer)->get_query(array('count' => true));

            $chart_orders = $this->get_chart_order_data();
            $chart_amount = $this->get_chart_amount_data();

            $best_products = $this->get_best_products();
            $recent_reviews = $this->get_recent_reviews();

            $new_customers = $this->get_new_customers();
  
        }

        include_once WOOPANEL_VIEWS_DIR . 'dashboard.php';
    }

    public function get_statistic_total_revenue() {
        global $wpdb, $current_user;

        $sales_by_date  = new WooPanel_Report_Order();
        $sales_by_date->order_status = $this->statistic_status;

        $sales_by_date->chart_groupby  = 'day';
        $sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

        return $sales_by_date->get_total_rev();
    }

    public function get_statistic_total_products() {
        global $current_user;

        if( is_woo_installed() ) {
            $woopanel_total_products = get_transient( 'woopanel_total_products' );
            if( ! $woopanel_total_products ) {
                $author_in = array();
        
                if( ! is_shop_staff(false, true) ) {
                    $author_in['author__in'] = array($current_user->ID);
                }

                $total_product = new WP_Query( array_merge(
                    array(
                        'post_type' => 'product',
                        'post_status' => 'publish',
                        'posts_per_page' => -1
                    ), $author_in
                ) );

                if( ! empty($total_product->found_posts) ) {
                    $woopanel_total_products = $total_product->found_posts;
                }else {
                    $woopanel_total_products = 0;
                }
            }
        }else {
            $woopanel_total_products = 0;
        }


        if( ($woopanel_total_products/1000) > 1) {
            return '<span data-toggle="tooltip" data-placement="top" data-original-title="'. number_format($woopanel_total_products, 0, ',', '.') .'">'. woopanel_nice_number($woopanel_total_products) .'</span>';
        }

        return $woopanel_total_products;
        
    }

    public function get_statistic_total_orders() {
        global $wpdb;

        $query = $this->get_query_order( true, array('wc-completed') );

        // Filter date
        if( ! empty($this->start_date) && ! empty($this->end_date) ) {
            $query['where'] .= " AND CAST(posts.post_date AS DATE) BETWEEN '{$this->start_date}' AND '{$this->end_date}'";
        }

        return $this->getCount( $query, __FUNCTION__ );
    }

    public function get_new_customers() {
        global $wpdb, $current_user;

		$query = array();


		$query['select']  = "SELECT DISTINCT users.ID, users.user_nicename, users.display_name, users.user_email, users.user_registered FROM {$wpdb->posts} as posts";
        
        $query['join']   = "INNER JOIN {$wpdb->postmeta} AS meta__customer_user ON posts.ID = meta__customer_user.post_id ";
        $query['join']  .= "INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON (posts.ID = order_items.order_id) AND (order_items.order_item_type = 'line_item') ";
        $query['join']  .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__product_id ON (order_items.order_item_id = order_item_meta__product_id.order_item_id)  AND (order_item_meta__product_id.meta_key = '_product_id') ";
        $query['join']  .= "INNER JOIN {$wpdb->posts} AS products ON order_item_meta__product_id.meta_value = products.ID ";
        $query['join']  .= "INNER JOIN {$wpdb->users} AS users ON meta__customer_user.meta_value = users.ID ";
        $query['join']  .= "INNER JOIN {$wpdb->usermeta} AS usermeta ON users.ID = usermeta.user_id";

        $query['where']  = "WHERE posts.post_type = 'shop_order' ";
        $query['where'] .= "AND ( ( meta__customer_user.meta_key   = '_customer_user' AND meta__customer_user.meta_value > '0' ))";
        
        // Permission
		if( ! is_shop_staff(false, true) ) {
			$query['where']  .= " AND products.post_author = ".$current_user->ID;
        }

        $query['orderby'] = "ORDER BY posts.post_date DESC";
        $query['limit']   = "LIMIT 5";

        return $this->getResults( $query, __FUNCTION__ );
    }

    public function get_chart_order_data() {
        $report              = new WooPanel_Report_Order();
        $report->start_date  = $this->chart_start_date;
        $report->end_date    = $this->chart_end_date;

        $report->order_status    = $this->order_status;
        $report->chart_groupby   = 'day';
        $report->group_by_query  = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
        
        return $report->get_report_status_data();
    }

    public function get_chart_amount_data() {
        $report              = new WooPanel_Report_Order();
        $report->start_date  = $this->chart_start_date;
        $report->end_date    = $this->chart_end_date;

        $report->order_status    = $this->order_status;
        $report->chart_groupby   = 'day';
        $report->group_by_query  = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';
        
        return $report->get_report_filter_data();
    }

    public function get_recent_orders($count = false, $status = array()) {
        $query      = $this->get_query_order($count, $status);

        if( $count ) {
            return $this->getCount( $query, __FUNCTION__ );
        }else {
            return $this->getResults( $query, __FUNCTION__ );
        }
    }

    public function get_best_products() {
        global $current_user;

        $author_in = array();

        if( ! is_shop_staff(false, true) ) {
            $author_in['author__in'] = array($current_user->ID);
        }

        $args = array_merge(
            array(
                'post_type' => 'product',
                'meta_key' => 'total_sales',
                'orderby' => 'meta_value_num',
                'posts_per_page' => 5,
            ), $author_in
        );

        return new WP_Query( $args );
    }

    public function get_recent_reviews() {
        global $wpdb, $current_user;

        $query = array();

        $query['select'] = "SELECT * FROM {$wpdb->comments} as comments";
        $query['join']   = "INNER JOIN {$wpdb->posts} AS products ON comments.comment_post_ID = products.ID";
        $query['where']  = "WHERE comment_type = 'review'";

		// Permission
		if( ! is_shop_staff(false, true) ) {
			$query['where']  .= " AND products.post_author = ".$current_user->ID;
        }
        $query['order'] = 'ORDER BY comments.comment_date DESC';
        $query['limit'] = 'LIMIT 10';

        return $this->getResults( $query, __FUNCTION__ );
    }
    
    
    public function getCount( $query, $func_name ) {
        global $wpdb, $current_user;

        $query               = implode(' ', $query);
        $dashboard_transient = strtolower( get_class( $this ) ) . $current_user->ID;
        $cached_results      = get_transient( $dashboard_transient );
        $query_hash          = $func_name . md5( $query );
        
        if( false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
            if( $this->cache ) {
                $cached_results[ $query_hash ] = $wpdb->get_var( $query );
                set_transient( $dashboard_transient, $cached_results, DAY_IN_SECONDS );
            }
        }

        if( $this->cache && isset($cached_results[ $query_hash ])) {
            $result = $cached_results[ $query_hash ];
        }else {
            $result = $wpdb->get_var( $query );
        }

        return $result;
    }

    public function getResults( $query, $func_name ) {
        global $wpdb, $current_user;
        
        $query            = implode(' ', $query);
        $cached_results = get_transient( strtolower( get_class( $this ) ) );
        $query_hash     = $func_name . md5( $query );
        
        if( false === $cached_results || ! isset( $cached_results[ $query_hash ] ) ) {
            if( $this->cache ) {
                $cached_results[ $query_hash ] = $wpdb->get_results( $query, ARRAY_A );
                set_transient( strtolower( get_class( $this ) ), $cached_results, DAY_IN_SECONDS );
            }
        }

        if( $this->cache && isset($cached_results[ $query_hash ])) {
            $result = $cached_results[ $query_hash ];
        }else {
            $result = $wpdb->get_results( $query, ARRAY_A );
        }

        return $result;
    }

    public function get_query_order( $count = false, $status = array() ) {
		global $wpdb, $current_user;

		$query = array();

		if( $count ) {
			$query['select'] = "SELECT COUNT( DISTINCT posts.ID ) as total FROM {$wpdb->posts} as posts";
		}else {
			$query['select'] = "SELECT DISTINCT posts.ID, posts.* FROM {$wpdb->posts} as posts";
        }
        
        if( empty($status) ) {
            $status = array_keys( wc_get_order_statuses() );
        }
        
        $query['join']   = "INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON (posts.ID = order_items.order_id) AND (order_items.order_item_type = 'line_item') ";
        $query['join']  .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta__product_id ON (order_items.order_item_id = order_item_meta__product_id.order_item_id)  AND (order_item_meta__product_id.meta_key = '_product_id') ";
		$query['join']  .= "INNER JOIN {$wpdb->posts} AS products ON order_item_meta__product_id.meta_value = products.ID";
        $query['where']  = "WHERE posts.post_type = 'shop_order' ";
        $query['where'] .= "AND posts.post_status IN ( '" . implode( "','", $status ) . "')";
		
		// Permission
		if( ! is_shop_staff(false, true) ) {
			$query['where']  .= " AND products.post_author = ".$current_user->ID;
        }

        if( ! $count ) {
            $query['order'] = 'ORDER BY posts.post_date DESC';
            $query['limit'] = 'LIMIT 10';
        }

        return $query;
    }
}