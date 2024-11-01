<?php
class WooPanel_List_Table {

	/**
	 * The current list of items.
	 *
	 * @since 3.1.0
	 * @var array
	 */
	public $items;
	public $type_query;
	public $type;
	public $user_query;
	public $max_num_pages;
	public $taxonomy;

    private $_actions;

    public $custom_query;

	/**
	 * The current screen.
	 *
	 * @since 3.1.0
	 * @var object
	 */
	protected $post_type;
	public $type_settings = array(
		'name' => false,
		'not_found' => false,
		'add_new' => false,
		'create_permission' => false,
		'search_items' => false
	);
	public $post_statuses = array();
	
	/**
	 * The current screen.
	 *
	 * @since 3.1.0
	 * @var object
	 */
	protected $template;

	/**
	 * The current screen.
	 *
	 * @since 3.1.0
	 * @var object
	 */
	protected $columns = array();

	public $limits = array( 10, 20, 30, 50, 100 );
	public $per_page;
	public $paged = 1;
	public $total_items;

	
    
	/**
	 * Constructor.
	 *
	 * The child class should call this constructor from its own constructor to override
	 * the default $args.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $args {
	 *     Array or string of arguments.
	 *
	 *     @type string $plural   Plural value used for labels and the objects being listed.
	 *                            This affects things such as CSS class-names and nonces used
	 *                            in the list table, e.g. 'posts'. Default empty.
	 *     @type string $singular Singular label for an object being listed, e.g. 'post'.
	 *                            Default empty
	 *     @type bool   $ajax     Whether the list table supports Ajax. This includes loading
	 *                            and sorting data, for example. If true, the class will call
	 *                            the _js_vars() method in the footer to provide variables
	 *                            to any scripts handling Ajax events. Default false.
	 *     @type string $screen   String containing the hook name used to determine the current
	 *                            screen. If left null, the current screen will be automatically set.
	 *                            Default null.
	 * }
	 */
	public function __construct( $args = array() ) {
		global $wp_query;

		if( ! empty($args) ) {
			$this->type_query = $args['type'];
			$this->columns = $args['columns'];
			$this->template = isset($args['template']) ? $args['template'] : '';
			$this->primary_columns = $args['primary_columns'];
			$this->taxonomy = isset($args['taxonomy']) ? $args['taxonomy'] : '';

			if( $this->type_query != 'WP_User_Query') {
				/* Hooks for WP_Query */
				$this->type = $args['post_type'];
				$this->post_statuses = $args['post_statuses'];
				$this->type_object = get_post_type_object( $this->type );

				$this->type_settings = array(
					'name' => $this->type_object->labels->name,
					'singular_name' => $this->type_object->labels->singular_name,
					'not_found' => $this->type_object->labels->not_found,
					'add_new' => $this->type_object->labels->add_new,
					'create_permission' => $this->type_object->cap->create_posts,
					'search_items' => $this->type_object->labels->search_items
				);

				add_filter( 'query_vars', array( $this, 'query_vars'), 999, 1 );
				add_filter( 'post_password_required', '__return_false');
				add_action( 'pre_get_posts', array( $this, 'pre_get_posts'), 99, 1 );
				add_filter( 'the_content', array( $this, 'the_content'), 99, 1 );
			}
		}

		$this->custom_query = isset( $args['custom_query'] ) ? $args['custom_query'] : false;



		/* Paginate */
		$this->per_page = $this->limits[0];
		
		$this->paged       = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : $this->paged;
		if( isset($_GET['limit']) && in_array($_GET['limit'], $this->limits) ) {
			$this->per_page = $_GET['limit'];
		}
	}

	/**
	 * Form create/edit for displaying.
	 *
	 * @since 1.0.0
	 * @abstract
	 */
	public function form() {
		die( 'function WooPanel_List_Table::form() must be over-ridden in a sub-class.' );
	}
	
	/**
	 * Prepares the list of items for displaying.
	 * @uses WP_List_Table::set_pagination_args()
	 *
	 * @since 1.0.0
	 * @abstract
	 */
	public function prepare_items() {
		die( 'function WooPanel_List_Table::prepare_items() must be over-ridden in a sub-class.' );
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 1.0.0
	 *
	 * @staticvar int $cb_counter
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
        ?>
        <th id="cb" class="wpl-datatable__cell--center wpl-datatable__cell wpl-datatable__cell--check check-column">
            <span>
                <label class="m-checkbox m-checkbox--single m-checkbox--all m-checkbox--solid m-checkbox--brand" for="cb-select-all-1">
                    <input id="cb-select-all-1" type="checkbox" />
                    <span></span>
                </label>
            </span>
        </th>
        <?php

        if( is_array($this->columns) && ! empty($this->columns) ) {
            foreach( $this->columns as $column_name => $column_label ) { ?>
                <th scope="col" class="wpl-datatable__cell column-<?php echo $column_name;?>">
                    <span><?php echo $column_label;?></span>
                    <span class="sorting-indicator"></span>
                </th>
                <?php
            }
        }
	}

	/**
	 *
	 * @return bool
	 */
	public function has_items() {
		global $total_items;

		return $total_items;
	}

	/**
	 */
	public function no_items() {
		?>
		<tr class="no-items">
			<td class="colspanchange" colspan="<?php echo (count($this->columns) + 1);?>" style="text-align:center;">
				<div class="dashboard-block-empty">
					<?php
					if( ! has_action("woopanel_{$this->type}_no_item_icon") ) {
						echo '<i class="la la-shopping-cart"></i>';
					}else {
						do_action("woopanel_{$this->type}_no_item_icon");
					}?>
                    <?php if( isset($this->type_settings['singular_name']) ){ ?>
					    <h3><?php echo sprintf( __( 'No %s found.', 'woopanel' ), $this->type_settings['singular_name'] );?></h3>
                    <?php } ?>
					<p><?php echo $this->type_settings['not_found'];?></p>
				</div>
			</td>
		</tr>
		<?php
	}

	/**
	 * Display the table
	 *
	 * @since 1.0.0
	 */
	public function display_rows_or_custom() {
		foreach( $this->get_results() as $index => $post) {
			$this->get_html_display_rows($post, $index);
		}
	}

	/**
	 * Display the table
	 *
	 * @since 1.0.0
	 */
	public function display_rows_or_placeholder() {
		global $post;

		$index = 0;
		while ( have_posts() ) : the_post();
			$this->get_html_display_rows($post, $index);
			$index++;
		endwhile;
	}

	public function get_html_display_rows($post, $index) {
		$product = false;
		$data = '';
		if( is_woo_installed() ) {
			if( $post->post_type == 'product' ) {
				$data = wc_get_product($post->ID);
			}
			
			if( $post->post_type == 'shop_order' ) {
				$data = wc_get_order($post->ID);
			}

			if( $post->post_type == 'shop_coupon' ) {
				$data = new WC_Coupon($post->ID);
			}
		}

		?>
		<tr id="post-<?php echo $post->ID;?>" class="wpl-datatable__row iedit author-self<?php if( $index % 2 == 1 ) { echo ' m-datatable__row--even';}?> level-0 post-<?php echo $post->ID;?> post type-post status-publish format-standard has-post-thumbnail hentry category-comfort category-luxury category-market-updates category-sales category-uncategorized">
			<td class="wpl-datatable__cell--center wpl-datatable__cell wpl-datatable__cell--check check-column">
				<span>
					<label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand" for="cb-select-<?php echo $post->ID;?>">
						<input id="cb-select-<?php echo $post->ID;?>" type="checkbox" name="ids[]" value="<?php echo $post->ID;?>">
						<span></span>
					</label>
				</span>
			</td>
			<?php
			if( empty($this->template) ) {
				foreach( $this->columns as $key_column => $column ) {?>
				<td class="wpl-datatable__cell column-<?php echo $key_column;?>" data-colname="<?php echo strip_tags($column);?>">
					<?php
					$action_name = "woopanel_{$this->type}_{$key_column}_column";
					if( ! has_filter($action_name) ) {
						echo 'ok';
					}else {
						echo apply_filters($action_name, '', $post, $data);
					}
					
					?>
				</td>
			<?php
				}
			}else {
				woopanel_get_template_part( $this->template, '', array(
					'data' => $post,
					'columns' => $this->columns
				));
			}?>
		</tr>
		<?php
	}

	public function display_paginate($total_items, $max_num_pages) {
		global $wp_query;
		?>
		<div class="m-datatable__pager m-datatable--paging-loaded clearfix">
			<?php

			woopanel_paginate_links( array(
				'current'   => $this->paged,
				'total'     => $max_num_pages,
				'format'    => '?pagenum=%#%',
				'end_size'  => 1,
				'mid_size'  => 2,
				'type'      => 'array',
				'prev_text' => '<i class="la la-angle-double-left"></i>',
				'next_text' => '<i class="la la-angle-double-right"></i>',
			) ); ?>
			
			<div class="m-datatable__pager-info">
				<?php if( $total_items > $this->limits[0] ) {?>
				<select name="limit" class="selectpicker m-bootstrap-select m-datatable__pager-size" title="Select page size" data-width="70px" tabindex="-98" onchange="this.form.submit()">
					<?php foreach ($this->limits as $value) {
						echo '<option value="'. $value .'" '. (($value == $this->per_page) ? 'selected' : '') .'>'. $value .'</option>';
					} ?>
				</select>
				<?php }?>
				<?php woopanel_paginate_text($this->paged, $this->per_page, $total_items); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Display the table
	 *
	 * @since 1.0.0
	 */
	public function display() {
		global $wp_query, $total_items;

		if( $this->custom_query ) {
			$total_items = $this->get_count();
			$max_num_pages = ceil($total_items / $this->per_page);
		}else {
			$total_items = $wp_query->found_posts;
			$max_num_pages = $wp_query->max_num_pages;
		}
		?>

		<form id="posts-filter" method="get">
			<div id="list-<?php echo $this->type;?>-table" class="woopanel-list-post-table m-portlet m-portlet--mobile">
				<!--begin: Head -->
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<h3 class="m-portlet__head-text">
								<?php echo $this->type_settings['name'];?>
								<small><span class="displaying-num"><?php echo sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) );?></span></small>
							</h3>
						</div>
					</div>
					<div class="m-portlet__head-tools">
						<?php if ( apply_filters("woopanel_{$this->type}_user_can_create", true) && current_user_can( $this->type_settings['create_permission'] ) ) { ?>
							<a href="<?php echo esc_html( woopanel_post_new_url($this->type) );?>" class="btn btn-primary m-btn m-btn--icon m-btn--md">
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
					<?php if( $this->has_items() || isset($_GET['date']) ) {?>
					<!--begin: Search Form -->
					<div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
						<div class="row align-items-center">
							<div class="col-xl-9 order-2 order-xl-1">
								<div class="form-group m-form__group row align-items-center">
									<div class="col-md-4">
										<div class="m-form__group m-form__group--inline">
											<?php woopanel_filter_months_dropdown($this->type, 'date'); ?>
										</div>
										<div class="d-md-none m--margin-bottom-10"></div>
									</div>

									<?php do_action( "woopanel_{$this->type}_filter_display", $this->type, $this->type_settings );?>
									<?php
									if( isset($_GET['post_status']) ) {
										echo '<input type="hidden" name="post_status" value="' . $_GET['post_status'] .'">';
									}?>
									<div class="col-md-4">
										<div class="m-input-icon m-input-icon--left">
											<input type="text" class="form-control m-input" name="search_name" placeholder="<?php echo esc_html( $this->type_settings['search_items']); ?>" id="generalSearch" value="<?php echo isset($_GET['search_name']) ? strip_tags($_GET['search_name']) : '';?>">
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
										<span><?php _e( 'Filter', 'woopanel' ); ?></span>
									</span>
								</button>
								<div class="m-separator m-separator--dashed d-xl-none"></div>
							</div>
						</div>
					</div>
					<!--end: Search Form -->
					<?php }?>

                    <?php $this->display_tablenav( 'top' ); ?>

					<div id="m-listtable-<?php echo $this->post_type;?>" class="m-datatable m-datatable--default wpl_datatable <?php if( ! $this->has_items() ) { echo ' m-datatable-empty';}?>">
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
										if( $this->custom_query ) {
											$this->display_rows_or_custom();
										}else {
											$this->display_rows_or_placeholder();
										}
									}else {
										$this->no_items();
									}?>
								</tbody>
							</table>
						</div>

						
						<?php
						if ( $this->has_items() ) {

							$this->display_paginate($total_items, $max_num_pages);
						}?>
					</div>
					
				</div><!-- .m-portlet__body -->
			</div>
		</form>
        <?php
	}
	
	public function wp_edit_posts_query( $q = false ) {
		global $wp;
		if ( false === $q )
			$q = $_GET;
		$q['m'] = isset($q['date']) ? (int) $q['date'] : 0;
		$q['cat'] = isset($q['cat']) ? (int) $q['cat'] : 0;
		$post_stati  = get_post_stati();
	 
		$q['post_type'] = $this->type;
		$post_type = $this->type;
		
		$avail_post_stati = $this->get_available_post_statuses($this->type);
		$post_status      = array_keys($this->post_statuses);
		$perm             = '';

		if( isset($q['post_status']) ) {
			$post_status = $q['post_status'];
		}

		if ( isset($q['status']) && in_array( $q['status'], $post_stati ) ) {
			$post_status = $q['status'];
			$perm = 'readable';
		}
		 
		$wp_query['orderby'] = 'post_date';
		$wp_query['order'] = 'DESC';
	
		
		$posts_per_page = isset($q['posts_per_page']) ? (int) $q['posts_per_page'] : $this->per_page;

	 
		$wp_query = compact('post_type', 'post_status', 'perm', 'posts_per_page');

	 
		if ( ! empty( $q['show_sticky'] ) ) {
			$wp_query['post__in'] = (array) get_option( 'sticky_posts' );
		}

		$wp_query['paged'] = $this->paged;

		$wp->main( $wp_query );

		return $avail_post_stati;
	}

	public function get_available_post_statuses($type = 'post') {
		$stati = wp_count_posts($type);
		return array_keys(get_object_vars($stati));
	}

	public function pre_get_posts($query) {
		global $current_user;
		if ( is_admin() || ! $query->is_main_query() ){
			return;
		}

		if( isset($_GET['date']) && ! empty($_GET['date']) && is_numeric($_GET['date']) ) {
			$query->set( 'm', $_GET['date'] );
		}
		

		if ( isset( $_GET['search_name']) && !empty( $_GET['search_name'] ) ) {
			$query->set( 's', $_GET['search_name'] );
		}

		if( $this->taxonomy && isset( $_GET['cat'] ) && $_GET['cat'] != -1 ) {
			$query->set( 'tax_query', array(
				array(
					'taxonomy' => $this->taxonomy,
					'field' => 'id',
					'terms' => (int)  $_GET['cat'],
					'include_children' => false
				)
			));
		}


		// Permission
		if( ! is_shop_staff(false, true) ) {
			$query->set('author', $current_user->ID);
		}

		$query->set('paged', $this->paged);
		$query->set('orderby', 'post_date');
		$query->set('order', 'DESC');
	}

	public function query_vars( $query_vars ) {
		return array();
	}

    /**
     * Bulk actions
     * @since 1.0.0
     */
    protected function get_bulk_actions() {
        return array();
    }

    protected function bulk_actions( $which = '' ) {
        if ( is_null( $this->_actions ) ) {
            $this->_actions = $this->get_bulk_actions();
            $this->_actions = apply_filters( "bulk_actions-{$this->type}", $this->_actions );
            $two = '';
        } else {
            $two = '2';
        }

        if ( empty( $this->_actions ) )
            return;

       echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\" class=\"m-bootstrap-select m-bootstrap-select--solid m_selectpicker\" data-style=\"btn-sm\" data-width=\"\">\n";
        echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";

        foreach ( $this->_actions as $name => $title ) {
            $class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

            echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
        }

        echo "</select>\n";

        echo '<button type="submit" id="doaction'. $two .'" class="btn btn-accent btn-sm m-btn m-loader--light m-loader--right" onclick="if(!this.classList.contains(\'m-loader\')) this.className+=\' m-loader\';">'. __( 'Apply' ) .'</button>';
        echo "\n";
    }

    protected function display_tablenav( $which ) {
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->type );
        }
        ?>
        <?php if ( $this->has_items() ): ?>
            <div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30 tablenav hidden <?php echo esc_attr( $which ); ?>">
                <div class="m-form__group m-form__group--inline">
                    <div class="m-form__label m-form__label-no-wrap">
                        <label class="m--font-bold m--font-danger-">
                            <?php printf( __( 'Selected %s:' ), '<span id="m_datatable_selected_number" class="selected_number">0</span> records' ); ?>
                        </label>
                    </div>
                    <div class="m-form__control actions bulkactions">
                        <?php $this->bulk_actions( $which ); ?>
                    </div>
                </div>
            </div>
        <?php endif;
    }

    public function current_action() {
        if ( isset( $_GET['action'] ) && -1 != $_GET['action'] )
            return $_GET['action'];

        if ( isset( $_GET['action2'] ) && -1 != $_GET['action2'] )
            return $_GET['action2'];

        return false;
    }

	public function the_content( $content ) {
		return $content;
	}

	public function get_count() {
		global $wpdb;

		$this->custom_query['query_count']['orderby'] = "ORDER BY posts.post_date DESC";
		$sql = implode( ' ', $this->merge_query($this->custom_query['query_count']) );

		return $wpdb->get_var($sql);
	}

	public function get_results() {
		global $wpdb;
	// && in_array($_GET['status'], $this->post_statuses)
		$offset = ($this->paged - 1) * $this->per_page;

		$this->custom_query['query_results']['orderby'] = "ORDER BY posts.post_date DESC";
		$this->custom_query['query_results']['limit']   = "LIMIT {$offset}, {$this->per_page}";
		$sql = implode( ' ', $this->merge_query($this->custom_query['query_results']) );

		return $wpdb->get_results($sql);
	}

	public function merge_query($custom_query) {
		global $wpdb;

		if( isset($_GET['status']) && isset($this->post_statuses[$_GET['status']]) ) {
			$custom_query['where'] .= " AND posts.post_status = '". $_GET['status'] ."'";
		}

		if( isset($_GET['date']) && !empty($_GET['date']) ) {
			$month = substr($_GET['date'], -2);
			$year = str_replace($month, '', $_GET['date']);

			$custom_query['where'] .= " AND YEAR(posts.post_date)=". $year ." AND MONTH(posts.post_date)=" . $month;
		}

		if( isset($_GET['date']) && !empty($_GET['search_name']) ) {
			$search_name = strip_tags($_GET['search_name']);
			$search_like = '%'.$wpdb->esc_like( $search_name ).'%';
			$custom_query['where'] .=  $wpdb->prepare(" AND (((posts.post_title  LIKE %s) OR (posts.post_excerpt LIKE %s) OR (posts.ID LIKE %s)))", $search_like, $search_like, $search_like);
		}

		

		return $custom_query;
	}
}