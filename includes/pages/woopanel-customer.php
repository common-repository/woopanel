<?php
class WooPanel_Template_Customer extends WooPanel_List_Table {
	public $type;
	public $type_settings;
    protected $users;
    protected $_total_items;

	public function __construct( $args = array() ) {
		global $query_vars;
		$this->type = 'user';
		$this->type_settings = array(
			'name' => __('Customers', 'woopanel' ),
			'not_found' => false,
			'add_new' => false,
			'create_permission' => false,
			'search_items' => false,
			'singular_name' => 'customer',
		);
		
		parent::__construct( array(
			'type'           => 'WP_User_Query',
			'screen'         => 'customers',
			'columns'        => array(
				'title'      => __( 'Username' ),
				'name'       => __( 'Name' ),
				'email'      => __( 'Email' ),
				'orders'     => __( 'Orders', 'woocommerce' ),
				'money_spent'=> __( 'Money Spent', 'woopanel' ),
				'last_order' => __( 'Last Order', 'woopanel' ),
				'action'     => __( 'Actions', 'woopanel' )
			),
			'primary_columns' => 'title'
        ) );
		
		$this->hooks();
	}

	public function lists() {
		$this->_total_items = $this->get_query(array(
			'count' => true
        ));
	
		$this->users = $this->get_query(array(
			'per_page' => $this->per_page,
			'offset' => ($this->paged - 1) * $this->per_page,
			'orderby'      => 'ID',
			'order'        => 'ASC'
        ));
        
		$this->display();
	}
	
	public function form( $current_user = false) {
        $user_links = array();
        $user_statistics = array();
		$user_tabs = array();

		if( $current_user ) {
			$user = wp_get_current_user();

			if( is_woo_available() ) {
                $my_store = home_url();
                if( woopanel_is_marketplace() == 'dokan' &&
                    function_exists('dokan_get_store_url') ) {
                    $my_store = dokan_get_store_url( $user->ID );
                }

                $user_links[0] = array(
                    'title' => __('My Store', 'woopanel'),
                    'url' => $my_store,
                    'icon' => 'fa fa-store',
                    'target' => '_blank',
                );
            }

            $user_tabs['edit_my_profile'] = array(
                'title' => __('Edit My Profile'),
                'callback' => array( $this, 'edit_my_profile_content' ),
                'priority' => 0,
            );

            $user_tabs['personal_options'] = array(
                'title' => __('Personal Options'),
                'callback' => array( $this, 'personal_options_content' ),
                'priority' => 2,
            );

            if( isset($_POST['form_name']) && $_POST['form_name'] == 'edit_my_profile' ){
                $this->save_profile($user);
            }
            if( isset($_POST['form_name']) && $_POST['form_name'] == 'personal_options' ){
                $this->save_options($user);
            }
		}else {
			$user = get_user_by( 'id', absint($_GET['id']) );
            $total_items = wc_get_customer_order_count($user->ID);

            if( is_woo_available() ) {
                $user_statistics[0] = array(
                    'title' => __('Total Money Spent', 'woopanel'),
                    'number' => wc_price( wc_get_customer_total_spent($user->ID) ),
                    'number_class' => 'm--font-brand',
                );
                $user_statistics[5] = array(
                    'title' => __('Total Order Placed', 'woopanel'),
                    'number' => sprintf( _n( '%s order', '%s orders', $total_items, 'woopanel' ), number_format_i18n( $total_items ) ),
                    'number_class' => 'm--font-danger',
                );
            }

            $user_tabs['my_orders'] = array(
                'title' => __('List Orders'),
                'callback' => array( $this, 'my_orders_content' ),
                'priority' => 10,
            );
		}

        wpl_print_notices();
  
		woopanel_get_template_part('customer/edit', '', array(
			'current_user' => $current_user,
			'user' => $user,
            'user_links' => $user_links,
            'user_statistics' => $user_statistics,
			'user_tabs'  => $user_tabs,
		));
	}

	public function hooks() {
		add_filter('woopanel_user_title_column', array($this, 'title_column'), 99, 2);
		add_filter('woopanel_user_name_column', array($this, 'name_column'), 99, 2);
		add_filter('woopanel_user_email_column', array($this, 'email_column'), 99, 2);
		add_filter('woopanel_user_orders_column', array($this, 'orders_column'), 99, 2);
		add_filter('woopanel_user_money_spent_column', array($this, 'money_spent_column'), 99, 2);
		add_filter('woopanel_user_last_order_column', array($this, 'last_order_column'), 99, 2);
		add_filter('woopanel_user_action_column', array($this, 'action_column'), 99, 2);
		add_action('woopanel_user_no_item_icon', array($this, 'no_item_icon'));

	}

	public function no_item_icon() {
		echo '<i class="flaticon-users"></i>';
	}
	
	public function title_column($return, $user) {
		echo '<strong><a class="row-title" href="' . $this->get_view_url ($user->ID ) . '" title="'. $user->user_nicename .'">'. $user->user_nicename .'</a></strong>';
	}

	public function name_column($return, $user) {
		$last_name = get_user_meta(  $user->ID, 'last_name', true);
		$first_name = get_user_meta(  $user->ID, 'first_name', true);

		if ( $last_name && $first_name ) {
			$customer_name = $first_name . ' ' . $last_name;
		} else {
			$customer_name = $user->display_name;
		}

		echo '<span>'.$customer_name .'</span>';
	}

	public function email_column($return, $user) {
		echo '<span>'. $user->user_email .'</span>';
	}

	public function orders_column($return, $user) {
		echo '<span>'. wc_get_customer_order_count( $user->ID ) .'</span>';
	}

	public function money_spent_column($return, $user) {
		echo wc_price( wc_get_customer_total_spent( $user->ID ) );
	}

	public function last_order_column($return, $user) {
        global $woopanel_post_types;
		$orders = wc_get_orders( array(
			'limit'    => 1,
			'status'   => array_map( 'wc_get_order_status_name', wc_get_is_paid_statuses() ),
			'customer' => $user->ID,
        ) );

		$html = '&ndash;';
		if ( ! empty( $orders ) ) {
            $order = $orders[0];
            $endpoint = $woopanel_post_types['shop_order']['slug'];

			$html = '<span class="m-badge m-badge--brand m-badge--wide order-status customer-order-number"><a href="' . esc_url( woopanel_dashboard_url ( $endpoint ) ) . '?id=' . $order->get_order_number() .'">' . _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() . '</a></span><br /><span>' . wc_format_datetime( $order->get_date_created() ) .'</span>';
		}

		echo $html;
	}

	public function action_column($return, $user) {
		echo '<a class="button wc-action-button wc-action-button-complete complete" href="' . $this->get_view_url ($user->ID ) . '" aria-label="Complete" title="" data-toggle="tooltip" data-placement="top" data-original-title="'. __('Manage Customer', 'woopanel' ) .'"><i class="la la-eye"></i></a>';
	}

	public function get_view_url( $user_id ) {
		return esc_url( woopanel_get_endpoint_url('customer').'?id='. absint($user_id));
	}

	/**
	 *
	 * @global array    $avail_post_stati
	 * @global WP_Query $wp_query
	 * @global int      $per_page
	 * @global string   $mode
	 */
	public function get_query($args = null, $site_id = null) {
		global $wpdb, $current_user;

		$args = wp_parse_args(
			$args,
			array(
				'count' 	=> false,
				'per_page'  => false,
				'offset'	=> 0
			)
        );
        
		$query = array();

		if( ! empty($args['count']) ) {
			$query['select']  = "SELECT COUNT(DISTINCT users.ID) as total_users FROM {$wpdb->posts} as posts";
		}else {
			// Custom select field here
			$query['select']  = "SELECT DISTINCT users.ID, users.user_nicename, users.display_name, users.user_email FROM {$wpdb->posts} as posts";
        }
        
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

		if( isset($_GET['search_name']) ) {
			$search_name = strip_tags($_GET['search_name']);
			$search_like = '%'.$wpdb->esc_like( $search_name ).'%';
			$query['where'] .=  $wpdb->prepare("AND (((users.user_login LIKE %s) OR (users.user_email LIKE %s) OR (users.display_name LIKE %s)))", $search_like, $search_like, $search_like);
		}
		$query['orderby'] = "ORDER BY posts.post_date DESC";

		if( empty($args['count']) ) {
			$query['limit']   = "LIMIT {$args["offset"]}, {$args["per_page"]}";
        }

        $sql = implode(' ', $query);

		if( ! empty($args['count']) ) {
			$results = $wpdb->get_row( $sql );
			return $results->total_users;
		}else {
			// Write custom select field here
			return $wpdb->get_results($sql);
		}
		
	}

	public function has_items() {
		if( ! empty( $this->_total_items ) ) {
			return true;
		}
	}

	/**
	 * Display the table
	 *
	 * @since 1.0.0
	 */
	public function display_rows_or_placeholder() {

		if ( $this->has_items() ) {
			foreach ( $this->users as $index => $user ) {
				?>
				<tr id="user-<?php echo $user->ID;?>" class="wpl-datatable__row iedit author-self<?php if( $index % 2 == 1 ) { echo ' m-datatable__row--even';}?> level-0 user-<?php echo $user->ID;?> format-standard has-post-thumbnail hentry category-comfort category-luxury category-market-updates category-sales category-uncategorized">
					<td class="wpl-datatable__cell--center wpl-datatable__cell wpl-datatable__cell--check">
						<span>
							<label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand" for="cb-select-<?php echo $user->ID;?>">
								<input id="cb-select-<?php echo $user->ID;?>" type="checkbox" name="user[]" value="<?php echo $user->ID;?>">
								<span></span>
							</label>
						</span>
					</td>

					<?php
					if( empty($this->template) ) {
						foreach( $this->columns as $key_column => $column ) {?>
							<td class="wpl-datatable__cell column-<?php echo $key_column;?>" data-colname="<?php echo $column;?>">
								<?php
								$action_name = "woopanel_{$this->type}_{$key_column}_column";
								if( ! has_filter($action_name) ) {
									echo '-';
								}else {
									echo apply_filters($action_name, '', $user, false);
								}
								
								?>
							</td>
						<?php }
					}?>
				</tr>
				<?php
			}
		}
	}

	public function display() {
		?>
		<form id="posts-filter" method="get">
			<div id="list-<?php echo $this->type;?>-table" class="woopanel-list-post-table m-portlet m-portlet--mobile">
				<!--begin: Head -->
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<h3 class="m-portlet__head-text">
								<?php echo $this->type_settings['name'];?>
								<small><span class="displaying-num"><?php echo sprintf( _n( '%s item', '%s items', $this->_total_items ), number_format_i18n( $this->_total_items ) );?></span></small>
							</h3>
						</div>
					</div>
					<div class="m-portlet__head-tools">
						<?php if ( apply_filters("woopanel_{$this->type}_user_can_create", true) && current_user_can( $this->type_settings['create_permission'] ) ) { ?>
							<a href="<?php echo esc_html( woopanel_post_new_url($this->type) );?>" class="btn btn-secondary m-btn m-btn--icon m-btn--md">
								<span>
									<i class="flaticon-add"></i>
									<span><?php echo esc_html( $this->type_settings['add_new']); ?></span>
								</span>
							</a>
						<?php } ?>
					</div>
				</div>
				<!--end: Head -->

				<div class="m-portlet__body">
				<?php if( $this->has_items() || isset($_GET['search_name']) ) {?>
					<!--begin: Search Form -->
					<div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
						<div class="row align-items-center">
							<div class="col-xl-9 order-2 order-xl-1">
								<div class="form-group m-form__group row align-items-center">
									<?php do_action( "woopanel_{$this->type}_filter_display", $this->type, $this->type_settings );?>

									<div class="col-md-4">
										<div class="m-input-icon m-input-icon--left">
											<input type="text" class="form-control m-input" name="search_name" placeholder="<?php _e( 'Search customer', 'woopanel' );?>" id="generalSearch" value="<?php echo isset($_GET['search_name']) ? strip_tags($_GET['search_name']) : '';?>">
											<span class="m-input-icon__icon m-input-icon__icon--left">
												<span><i class="la la-search"></i></span>
											</span>
										</div>
									</div>
								</div>
							</div>
							<div class="col-xl-3 order-1 order-xl-2 m--align-right">
								<button type="submit" name="filter_action" id="post-query-submit" class="btn btn-accent m-btn m-btn--custom m-btn--icon">
									<span>
										<i class="la la-filter"></i>
										<span><?php _e('Filter', 'woopanel'); ?></span>
									</span>
								</button>
								<div class="m-separator m-separator--dashed d-xl-none"></div>
							</div>
						</div>
					</div>
					<!--end: Search Form -->
					<?php }?>

					<div class="m-datatable m-datatable--default wpl_datatable<?php if( ! $this->has_items() ) { echo ' m-datatable-empty';}?>">
						<div class="table-responsive">
							<table class="wpl-datatable__table table m-table">
								<thead class="wpl-datatable__head">
									<tr class="wpl-datatable__row">
										<?php $this->print_column_headers(); ?>
									</tr>
								</thead>

								<tbody class="wpl-datatable__body">
									<?php
									if ( $this->has_items() ) {
										$this->display_rows_or_placeholder();
									}else {
										$this->no_items();
									}?>
								</tbody>
							</table>
						</div>

						<?php
						if ( $this->has_items() ) {
							$max_num_pages = ceil($this->_total_items / $this->per_page);
							$this->display_paginate($this->_total_items, $max_num_pages);
						}?>
	
					</div>
				</div>
			</div>
		</form>
		<?php
	}

    public function save_profile($user) {
        if( isset($_POST['save']) ) {

            $user->first_name = $_POST['first_name'];
            $user->last_name = $_POST['last_name'];
            $user->user_email = $_POST['email'];
            $user->display_name = $_POST['display_name'];
            $user->user_url = $_POST['url'];

            // Change password
            $pass_cur  = ! empty( $_POST['password_current'] ) ? $_POST['password_current'] : '';
            $pass1     = ! empty( $_POST['password_1'] ) ? $_POST['password_1'] : '';
            $pass2     = ! empty( $_POST['password_2'] ) ? $_POST['password_2'] : '';
            $save_pass = true;

            if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
                wpl_add_notice( "profile_settings", __( 'Please fill out all password fields.', 'woopanel' ), 'error' );
                $save_pass = false;
            } elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
                wpl_add_notice( "profile_settings", __( 'Please enter your current password.', 'woopanel' ), 'error' );
                $save_pass = false;
            } elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
                wpl_add_notice( "profile_settings", __( 'Please re-enter your password.', 'woopanel' ), 'error' );
                $save_pass = false;
            } elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
                wpl_add_notice( "profile_settings", __( 'New passwords do not match.', 'woopanel' ), 'error' );
                $save_pass = false;
            } elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $user->user_pass, $user->ID ) ) {
                wpl_add_notice( "profile_settings", __( 'Your current password is incorrect.', 'woopanel' ), 'error' );
                $save_pass = false;
            }

            if ( $pass1 && $save_pass ) {
                $user->user_pass = $pass1;
            }

            $user_id = wp_update_user( $user );

            if ( is_wp_error( $user_id ) ) {
                $error_string = $result->get_error_message();
                wpl_add_notice( "profile_settings", $error_string, 'error' );
            } else {
                wpl_add_notice( "profile_settings", __('Settings saved.'), 'success' );
            }
        }
    }

    public function save_options($user) {
        if( isset($_POST['save']) ) {

            $options = array(
                'report_from' => $_POST['report_from'],
                'report_to' => $_POST['report_to'],
            );

            update_user_meta( $user->ID, 'personal_options', $options );
        }
    }

    function my_orders_content(){
        $user = ( absint($_GET['id']) > 0 ) ? get_user_by('id', absint($_GET['id'])) : wp_get_current_user();

        $args = array(
            'category'         => '',
            'category_name'    => '',
            'orderby'          => 'date',
            'order'            => 'DESC',
            'include'          => '',
            'exclude'          => '',
            'meta_key'         => '_customer_user',
            'meta_value'       => $user->ID,
            'post_type'        => 'shop_order',
            'post_mime_type'   => '',
            'post_parent'      => '',
            'post_status'      => 'any',
            'suppress_filters' => 0
        );

        $per_page = (new WooPanel_List_Table)->per_page;
        $paged    = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;

        $args['posts_per_page'] = $per_page;
        $args['offset']         = ($paged - 1) * $per_page;
        $orders = get_posts( $args );

        $total_items = count($orders);
        
        ?>

        <div style="padding: 15px 20px;">
            <?php if ($orders) { ?>
                <div class="m-datatable m-datatable--default wpl_datatable">
                    <div class="table-responsive">
                        <table class="wpl-datatable__table table m-table">
                            <thead class="wpl-datatable__head">
                            <tr class="wpl-datatable__row">
                                <th class="wpl-datatable__cell--center wpl-datatable__cell column-status th-status">
                                    <i class="wcicon woopanel-status-order woopanel-status-processing"
                                       data-toggle="tooltip" data-placement="top"
                                       data-original-title="<?php _e('Status', 'woopanel'); ?>"></i>
                                </th>
                                <th class="wpl-datatable__cell column-order"><?php esc_html_e('Order', 'woocommerce'); ?></th>
                                <th class="wpl-datatable__cell column-purchased"><?php esc_html_e('Purchased', 'woopanel'); ?></th>
                                <th class="wpl-datatable__cell column-gross"><?php esc_html_e('Gross Sales', 'woopanel'); ?></th>
                                <th class="wpl-datatable__cell column-date"><?php esc_html_e('Date', 'woocommerce'); ?></th>
                                <th class="wpl-datatable__cell column-actions"><?php esc_html_e('Actions', 'woopanel'); ?></th>
                            </tr>
                            </thead>
                            <tbody class="wpl-datatable__body">
                            <?php foreach ($orders as $order_id) {
                                $order = wc_get_order($order_id);
                                ?>
                                <tr class="wpl-datatable__row">
                                    <th scope="row"
                                        class="wpl-datatable__cell th-status"><?php echo '<span class="wcicon woopanel-status-order woopanel-status-' . sanitize_title($order->get_status()) . '" data-toggle="tooltip" data-placement="top" data-original-title="' . wc_get_order_status_name($order->get_status()) . '"></span>'; ?></th>
                                    <td class="wpl-datatable__cell column-order">
                                        <?php
                                        $username = __('Guest', 'woopanel');

                                        $user_info = array();
                                        if ($order->get_user_id()) {
                                            $user_info = get_userdata($order->get_user_id());
                                        }

                                        if (!empty($user_info)) {

                                            $username = '';

                                            if ($user_info->first_name || $user_info->last_name) {
                                                $username .= esc_html(sprintf(_x('%1$s %2$s', 'full name', 'woopanel'), ucfirst($user_info->first_name), ucfirst($user_info->last_name)));
                                            } else {
                                                $username .= esc_html(ucfirst($user_info->display_name));
                                            }

                                        } else {
                                            if ($order->get_billing_first_name() || $order->get_billing_last_name()) {
                                                $username = trim(sprintf(_x('%1$s %2$s', 'full name', 'woopanel'), $the_order->get_billing_first_name(), $the_order->get_billing_last_name()));
                                            } else if ($order->get_billing_company()) {
                                                $username = trim($order->get_billing_company());
                                            } else {
                                                $username = __('Guest', 'woopanel');
                                            }
                                        }

                                        echo '<a href="' . esc_url(woopanel_post_edit_url($order->get_id())) . '" class="woopanel_dashboard_item_title">#' . esc_attr($order->get_order_number()) . '</a>' . ' ' . __('by', 'woopanel') . ' ' . $username;
                                        ?>
                                    </td>
                                    <td class="wpl-datatable__cell column-purchased">
                                        <?php
                                        $order_item_details = '<div class="order_items" cellspacing="0">';
                                        $items = $order->get_items();
                                        foreach ($items as $key => $item) {
                                            $product = $order->get_product_from_item($item);
                                            $item_meta_html = strip_tags(wc_display_item_meta($item, array(
                                                'before' => "\n- ",
                                                'separator' => "\n- ",
                                                'after' => "",
                                                'echo' => false,
                                                'autop' => false,
                                            )));

                                            $order_item_details .= '<div class=""><span class="qty">' . $item->get_quantity() . ' x </span><span class="name">' . $item->get_name();
                                            if (!empty($item_meta_html)) $order_item_details .= '<span class="img_tip" data-tip="' . $item_meta_html . '"></span>';
                                            $order_item_details .= '</div>';
                                        }
                                        $order_item_details .= '</div>';
                                        echo '<a href="#" class="show_order_items">' . sprintf(_n('%d item', '%d items', $order->get_item_count(), 'wc-frontend-manager'), $order->get_item_count()) . '</a>' . $order_item_details;
                                        ?>
                                    </td>
                                    <td class="wpl-datatable__cell column-gross">
                                        <?php
                                        $gross_sales = $order->get_total();
                                        $total = '<span class="order_total">' . $order->get_formatted_order_total() . '</span>';

                                        if ($order->get_payment_method_title()) {
                                            $total .= '<br /><small class="meta">' . __('Via', 'wc-frontend-manager') . ' ' . esc_html($order->get_payment_method_title()) . '</small>';
                                        }
                                        echo $total;
                                        ?>
                                    </td>
                                    <td class="wpl-datatable__cell column-date">
                                        <?php
                                        $order_date = (version_compare(WC_VERSION, '2.7', '<')) ? $order->order_date : $order->get_date_created();
                                        echo date_i18n(wc_date_format(), strtotime($order_date));
                                        ?>
                                    </td>
                                    <td class="wpl-datatable__cell column-actions">
                                        <?php
                                        $actions = array();

                                        if ($order->has_status(array('pending', 'on-hold'))) {
                                            $actions['processing'] = array(
                                                'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=processing&order_id=' . $order->get_id()), 'woocommerce-mark-order-status'),
                                                'name' => __('Processing', 'woocommerce'),
                                                'action' => 'processing',
                                            );
                                        }

                                        if ($order->has_status(array('pending', 'on-hold', 'processing'))) {
                                            $actions['complete'] = array(
                                                'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $order->get_id()), 'woocommerce-mark-order-status'),
                                                'name' => __('Complete', 'woocommerce'),
                                                'action' => 'complete',
                                            );
                                        }

                                        $actions_html = '';

                                        foreach ($actions as $action) {
                                            if (isset($action['group'])) {
                                                $actions_html .= '<div class="wc-action-button-group"><label>' . $action['group'] . '</label> <span class="wc-action-button-group__items">' . wc_render_action_buttons($action['actions']) . '</span></div>';
                                            } elseif (isset($action['action'], $action['url'], $action['name'])) {
                                                $actions_html .= sprintf('<a class="button wc-action-button wc-action-button-%1$s %1$s" href="%2$s" aria-label="%3$s" title="%3$s" data-toggle="tooltip" data-placement="top"><i class="la la-check"></i></a>', esc_attr($action['action']), esc_url($action['url']), esc_attr(isset($action['title']) ? $action['title'] : $action['name']), esc_html($action['name']));
                                            }
                                        }

                                        echo $actions_html;
                                        ?>
                                        <?php echo '<a class="button wc-action-button wc-action-button-view" href="' . esc_url(woopanel_post_edit_url($order->get_id())) . '" aria-label="Complete" title="" data-toggle="tooltip" data-placement="top" data-original-title="' . __('View order', 'woopanel') . '"><i class="la la-eye"></i></a>'; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="m-datatable__pager m-datatable--paging-loaded clearfix">
                        <?php
                        $max_num_pages = ceil($total_items / $per_page);
                        woopanel_paginate_links(array(
                            'current' => $paged,
                            'total' => $max_num_pages,
                            'format' => '?pagenum=%#%',
                            'end_size' => 1,
                            'mid_size' => 2,
                            'type' => 'array',
                            'prev_text' => '<i class="la la-angle-double-left"></i>',
                            'next_text' => '<i class="la la-angle-double-right"></i>',
                        )); ?>

                        <div class="m-datatable__pager-info">
                            <?php
                            if( $total_items > $this->limits[0]) { ?>
                            <select name="limit"
                                    class="selectpicker m-bootstrap-select m-datatable__pager-size"
                                    title="Select page size" data-width="70px" tabindex="-98"
                                    onchange="this.form.submit()">
                                <?php foreach ((new WooPanel_List_Table)->limits as $value) {
                                    echo '<option value="' . $value . '" ' . (($value == $per_page) ? 'selected' : '') . '>' . $value . '</option>';
                                } ?>
                            </select>
                            <?php }?>
                            <?php woopanel_paginate_text($paged, $per_page, $total_items); ?>
                        </div>
                    </div>
                </div>
            <?php } else {
                woopanel_no_content( array(
                    'icon' => 'flaticon-notepad',
                    'title' => __('Your Orders List Is Empty', 'woopanel')
                ) );
            } ?>
        </div> <?php
    }

    function edit_my_profile_content() {
        $user = wp_get_current_user();

        $profileuser = woopanel_get_user_to_edit($user->ID);

        $public_display = array();
        $public_display['display_nickname'] = $profileuser->nickname;
        $public_display['display_username'] = $profileuser->user_login;

        if (!empty($profileuser->first_name))
            $public_display['display_firstname'] = $profileuser->first_name;

        if (!empty($profileuser->last_name))
            $public_display['display_lastname'] = $profileuser->last_name;

        if (!empty($profileuser->first_name) && !empty($profileuser->last_name)) {
            $public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
            $public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
        }

        if (!in_array($profileuser->display_name, $public_display)) // Only add this if it isn't duplicated elsewhere
            $public_display = array('display_displayname' => $profileuser->display_name) + $public_display;

        $public_display = array_map('trim', $public_display);
        $public_display = array_unique($public_display);

        $display_name = array();
        foreach ($public_display as $k => $name) {
            $display_name[$name] = $name;
        }

        $user_infos = [
            'general' => [
                'title' => __('Name'),
                'desc' => '',
                'parent' => '',
                'icon' => '',
                'fields' => array(
                    array(
                        'id' => 'username',
                        'type' => 'text',
                        'title' => __('Username'),
                        'default' => $user->data->user_login,
                        'description' => __('Usernames cannot be changed.'),
                        'disable' => true,
                        'form_inline' => true
                    ),
                    array(
                        'id' => 'first_name',
                        'type' => 'text',
                        'title' => __('First Name'),
                        'default' => $profileuser->first_name,
                        'form_inline' => true
                    ),
                    array(
                        'id' => 'last_name',
                        'type' => 'text',
                        'title' => __('Last Name'),
                        'default' => $profileuser->last_name,
                        'form_inline' => true
                    ),
                    array(
                        'id' => 'display_name',
                        'type' => 'select',
                        'title' => __('Display name publicly as'),
                        'options' => $display_name,
                        'default' => $profileuser->display_name,
                        'form_inline' => true
                    ),
                )
            ],
            'contact_info' => [
                'title' => __('Contact Info'),
                'desc' => '',
                'parent' => '',
                'icon' => '',
                'fields' => array(
                    array(
                        'id' => 'email',
                        'type' => 'text',
                        'title' => __('Email'),
                        'default' => $profileuser->user_email,
                        'form_inline' => true
                    ),
                    array(
                        'id' => 'url',
                        'type' => 'text',
                        'title' => __('Website'),
                        'default' => $profileuser->user_url,
                        'form_inline' => true
                    ),
                )
            ],
            'password' => [
                'title' => __( 'Password change', 'woopanel' ),
                'desc' => 'Leave blank to leave unchanged',
                'parent' => '',
                'icon' => '',
                'fields' => array(
                    array(
                        'id' => 'password_current',
                        'type' => 'password',
                        'title' => __( 'Current password', 'woopanel' ),
                        'custom_attributes' => array( 'autocomplete'=>'off' ),
                        'form_inline' => true
                    ),
                    array(
                        'id' => 'password_1',
                        'type' => 'password',
                        'title' => __( 'New password', 'woopanel' ),
                        'custom_attributes' => array( 'autocomplete'=>'off' ),
                        'form_inline' => true
                    ),
                    array(
                        'id' => 'password_2',
                        'type' => 'password',
                        'title' => __( 'Confirm new password', 'woopanel' ),
                        'custom_attributes' => array( 'autocomplete'=>'off' ),
                        'form_inline' => true
                    ),
                )
            ],
        ]; ?>
        <form class="m-form m-form--fit m-form--label-align-right" method="post" id="post">
            <input type="hidden" name="form_name" value="edit_my_profile" />
            <div class="m-portlet__body">
                <?php $i = 0;
                foreach ($user_infos as $section_id => $section) {
                    if (!count($section['fields']) > 0) continue;
                    if ($i > 0) { ?>
                        <div class="m-form__seperator m-form__seperator--dashed"></div>
                    <?php } ?>
                    <div class="m-form__section" id="<?php echo $section_id; ?>">
                        <div class="form-group m-form__group row">
                            <div class="col-10 ml-auto">
                                <h3 class="m-form__section"><?php echo ($i + 1) . '. ' . $section['title']; ?></h3>
                                <?php if($section['desc']) echo "<p>{$section['desc']}</p>"; ?>
                            </div>
                        </div>
                        <?php foreach ($section['fields'] as $field) :
                            $args = [
                                'type' => $field['type'],
                                'label' => $field['title'],
                                'id' => $field['id'],
                                'input_class' => isset($field['class']) ? $field['class'] : array(),
                                'description' => isset($field['description']) ? $field['description'] : '',
                                'disable' => isset($field['disable']) ? $field['disable'] : '',
                                'options' => isset($field['options']) ? $field['options'] : '',
                                'default' => isset($field['default']) ? $field['default'] : '',
                                'custom_attributes' => isset($field['custom_attributes']) ? $field['custom_attributes'] : '',
                                'form_inline' => true
                            ];
                            woopanel_form_field($field['id'], $args, $args['default']);
                        endforeach; ?>
                    </div>
                    <?php $i++;
                } ?>
            </div>

            <div class="m-portlet__foot m-portlet__foot--fit">
                <div class="m-form__actions">
                    <div class="row">
                        <div class="col-2">
                        </div>
                        <div class="col-7">
                            <button type="submit" name="save"
                                    class="btn btn-accent m-btn m-btn--wide m-btn--md m-loader--light m-loader--right"
                                    onclick="if(!this.classList.contains('m-loader')) this.className+=' m-loader';"><?php _e('Update'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form><?php
    }

    function personal_options_content(){
        $user = wp_get_current_user();
        $options_val = get_user_meta( $user->ID, 'personal_options' );

        $user_options = [
            'report' => [
                'title' => __('Report Options'),
                'desc' => '',
                'parent' => '',
                'icon' => '',
                'fields' => array(
                    array(
                        'id' => 'report_from',
                        'type' => 'datepicker',
                        'title' => __('Report From'),
                        'placeholder' => esc_html__( _x( 'From&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD',
                        'default' => isset( $options_val[0]['report_from'] ) ? $options_val[0]['report_from'] : '',
                        'form_inline' => true
                    ),
                    array(
                        'id' => 'report_to',
                        'type' => 'datepicker',
                        'title' => __('Report To'),
                        'placeholder' => esc_html__( _x( 'To&hellip;', 'placeholder', 'woocommerce' ) ) . ' YYYY-MM-DD',
                        'default' => isset( $options_val[0]['report_to'] ) ? $options_val[0]['report_to'] : '',
                        'form_inline' => true
                    ),
                )
            ],
        ]; ?>
        <form class="m-form m-form--fit m-form--label-align-right" method="post" id="post">
            <input type="hidden" name="form_name" value="personal_options" />
            <div class="m-portlet__body">
                <?php $i = 0;
                foreach ($user_options as $section_id => $section) {
                    if (!count($section['fields']) > 0) continue;
                    if ($i > 0) { ?>
                        <div class="m-form__seperator m-form__seperator--dashed"></div>
                    <?php } ?>
                    <div class="m-form__section" id="<?php echo $section_id; ?>">
                        <div class="form-group m-form__group row">
                            <div class="col-10 ml-auto">
                                <h3 class="m-form__section"><?php echo ($i + 1) . '. ' . $section['title']; ?></h3>
                            </div>
                        </div>
                        <?php foreach ($section['fields'] as $field) :
                            $args = [
                                'type' => $field['type'],
                                'label' => $field['title'],
                                'id' => $field['id'],
                                'input_class' => isset($field['class']) ? $field['class'] : array(),
                                'description' => isset($field['description']) ? $field['description'] : '',
                                'placeholder' => isset($field['placeholder']) ? $field['placeholder'] : '',
                                'disable' => isset($field['disable']) ? $field['disable'] : '',
                                'options' => isset($field['options']) ? $field['options'] : '',
                                'default' => isset($field['default']) ? $field['default'] : '',
                                'form_inline' => true
                            ];
                            woopanel_form_field($field['id'], $args, $field['default']);
                        endforeach; ?>
                    </div>
                    <?php $i++;
                } ?>
            </div>

            <div class="m-portlet__foot m-portlet__foot--fit">
                <div class="m-form__actions">
                    <div class="row">
                        <div class="col-2">
                        </div>
                        <div class="col-7">
                            <button type="submit" name="save"
                                    class="btn btn-accent m-btn m-btn--wide m-btn--md m-loader--light m-loader--right"
                                    onclick="if(!this.classList.contains('m-loader')) this.className+=' m-loader';"><?php _e('Update'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form><?php
    }
}