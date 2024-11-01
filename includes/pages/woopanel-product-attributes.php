<?php
class WooPanel_Product_Attributes {
	public $endpoint = 'product-attributes';
	protected $columns = array();
	public $panels = array();
	public $edit = false;
	public $taxonomy = false;

	private $total_items = 0;
	public $limits = array( 10, 20, 30, 50, 100 );
	public $per_page;
	public $paged = 1;
	public $attribute_type = false;
	public $attribute;

	public function __construct() {

		if( isset($_GET['edit']) && is_numeric($_GET['edit']) ) {
			$this->edit = $_GET['edit'];
		}

		if( isset($_GET['tax']) ) {
			$this->taxonomy = $_GET['tax'];
		}

		if( $this->taxonomy ) {
			$this->columns = array(
				'name' => array(
					'label' => __('Name'),
					'order' => 1
				),
				'slug' => array(
					'label' => __('Slug'),
					'order' => 2
				),
				'description' => array(
					'label' => __('Description'),
					'order' => 3
				),
				'count' => array(
					'label' => __('Count'),
					'order' => 4
				),
			);
		}else {
			$this->columns = array(
				'name' => array(
					'label' => __('Name'),
					'order' => 1
				),
				'slug' => array(
					'label' => __('Slug'),
					'order' => 2
				),
				'type'  => array(
					'label' => __('Type'),
					'order' => 3
				),
				'orderby' => array(
					'label' => __('Order by'),
					'order' => 4
				),
				'terms' => array(
					'label' => __('Terms'),
					'order' => 5
				)
			);

			if ( ! wc_has_custom_attribute_types() ) {
				unset($this->columns['type']);
			}
		}

		$this->attribute_type = wc_get_attribute_types();

		/* Paginate */
		$this->per_page = $this->limits[0];
		
		$this->paged       = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : $this->paged;
		if( isset($_GET['limit']) && in_array($_GET['limit'], $this->limits) ) {
			$this->per_page = $_GET['limit'];
		}

		$this->total_items = $this->get_count();

		$this->hook_columns();
	}

	public function hook_columns() {
		add_action('woopanel_product-attributes_name_column', array($this, 'name_column'), 20, 2);
		add_action('woopanel_product-attributes_slug_column', array($this, 'slug_column'), 20, 2);
		add_action('woopanel_product-attributes_orderby_column', array($this, 'orderby_column'), 20, 2);
		add_action('woopanel_product-attributes_terms_column', array($this, 'terms_column'), 20, 2);

		add_action('woopanel_product-attributes_description_column', array($this, 'description_column'), 20, 2);
		add_action('woopanel_product-attributes_count_column', array($this, 'count_column'), 20, 2);
		add_action('woopanel_product-attributes_type_column', array($this, 'type_column'), 20, 2);
	}

	public function name_column($return, $tax) {
		if( $this->taxonomy ) {
			printf('<strong><a class="row-title" href="%1$s" aria-label="%2$s (Edit)">%2$s</a></strong>', woopanel_dashboard_url('product-attributes') . '?tax='. $this->taxonomy .'&edit=' . $tax->term_id, $tax->name);
			echo '<div class="row-actions">';
			printf('<span class="edit"><a href="%1$s" aria-label="Edit “%2$s”">Edit</a> | </span>', woopanel_dashboard_url('product-attributes') . '?tax='. $this->taxonomy .'&edit=' . $tax->term_id, $tax->name);
			printf('<span class="delete"><a href="%1$s" class="submitdelete" aria-label="Delete “Business Card”">Delete</a></span>', woopanel_dashboard_url('product-attributes') . '?tax='. $this->taxonomy .'&edit=' . $tax->term_id .'&act=delete&_nonce=' . wp_create_nonce( 'wpl_term_delete_nonce' ), $tax->name);
			echo '</div>';
		}else {
			printf('<strong><a class="row-title" href="%1$s" aria-label="%2$s (Edit)">%2$s</a></strong>', woopanel_dashboard_url('product-attributes') . '?edit=' . $tax->attribute_id, $tax->attribute_label);
			echo '<div class="row-actions">';
			printf('<span class="edit"><a href="%1$s" aria-label="Edit “%2$s”">Edit</a> | </span>', woopanel_dashboard_url('product-attributes') . '?edit=' . $tax->attribute_id, $tax->attribute_label);
			printf('<span class="delete"><a href="%1$s" class="submitdelete" aria-label="Delete “%2$s”">%3$s</a></span>', woopanel_dashboard_url('product-attributes') . '?edit=' . $tax->attribute_id .'&act=delete&_nonce=' . wp_create_nonce( 'wpl_attr_delete_nonce' ), $tax->attribute_label, __('Delete', 'woopanel') );
			echo '</div>';
		}
	}

	public function slug_column($return, $tax) {
		if( $this->taxonomy ) {
			echo $tax->slug;
		}else {
			echo $tax->attribute_name;
		}
	}

	public function orderby_column($return, $tax) {
		echo woopanel_wc_orderby($tax->attribute_orderby);
	}

	public function terms_column($return, $tax) {
		$taxonomy = wc_attribute_taxonomy_name( $tax->attribute_name );

		if ( taxonomy_exists( $taxonomy ) ) {
			$terms        = get_terms( $taxonomy, 'hide_empty=0' );
			$terms_string = implode( ', ', wp_list_pluck( $terms, 'name' ) );
			if ( $terms_string ) {
				echo esc_html( $terms_string );
			} else {
				echo '<span class="na">&ndash;</span>';
			}
		} else {
				echo '<span class="na">&ndash;</span>';
		}
		?>
		<br /><a href="<?php echo woopanel_dashboard_url('product-attributes');?>/?tax=<?php echo esc_html( wc_attribute_taxonomy_name( $tax->attribute_name ) ); ?>" class="configure-terms"><?php esc_html_e( 'Configure terms', 'woocommerce' ); ?></a>
		<?php
	}

	public function description_column($return, $tax) {
		if( empty($tax->description) ) {
			echo '<span aria-hidden="true">—</span>';
		}else {
			echo $tax->description;
		}
	}

	public function count_column($return, $tax) {
		echo $tax->count;
	}

	public function type_column($return, $tax) {
		echo $this->attribute_type[$tax->attribute_type];
	}

	public function index() {
		global $wpdb;

		if( $this->taxonomy ) {
			$attribute = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s", 
				str_replace('pa_', '', $this->taxonomy)
			));

			$this->attribute = $attribute;
			$term = new stdClass();
			$term->name = $term->slug = $term->description = '';


			if( $this->edit ) {
				$term = $wpdb->get_row(
					$wpdb->prepare( "SELECT * FROM {$wpdb->terms} as t INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_id = t.term_id WHERE t.term_id = %d", 
					$this->edit
				));
			}

			// Delete Terms
			if ( isset($_GET['_nonce']) && wp_verify_nonce( $_GET['_nonce'], 'wpl_term_delete_nonce' ) ) {
				wp_delete_term( $this->edit, $this->taxonomy );
				$redirect_url = woopanel_dashboard_url('product-attributes') . '?tax=' . $this->taxonomy;
				wpl_add_notice( "delete_term", esc_html__( 'Term deleted successfully', 'woocommerce' ), 'success' );
				wp_redirect($redirect_url);
				die();
			}

			if( isset($_POST['submit']) ) {
				// START: Filter Save Data
				$data = array();
				$data['name'] = isset($_POST['term_name']) ? woopanel_clean(wp_unslash($_POST['term_name'])) : '';
				if ( ! isset( $_POST['term_slug'] ) ) {
					$data['slug'] = wc_sanitize_taxonomy_name( stripslashes( $data['term_name'] ) );
				} else {
					$data['slug'] = preg_replace( '/^pa\_/', '', wc_sanitize_taxonomy_name( stripslashes( $_POST['term_slug'] ) ) );
				}
				$data['description'] = isset($_POST['term_description']) ? woopanel_clean(wp_unslash($_POST['term_description'])) : 'select';

				$redirect_url = woopanel_dashboard_url('product-attributes') . '?tax=' . $this->taxonomy;
				if( $this->edit ) {

					$rs = wp_update_term($term->term_id, $this->taxonomy, array(
						'name' => sanitize_text_field($data['name']),
						'slug' => sanitize_title($data['slug']),
						'parent' => 0,
						'description' => $data['description']
					));

					$redirect_url = $redirect_url .'&edit='. $term->term_id;
					if( ! is_wp_error($rs) ) {
						do_action('woopanel_edit_term', $term->term_id, array() );
					}
					
				}else {
					$rs = wp_insert_term(
						sanitize_text_field($data['name']),
						$this->taxonomy,
						array(
						  'description'=> sanitize_text_field($data['description']),
						  'slug' => $data['slug'],
						  'parent'=> 0
						)
					);
					if( ! is_wp_error($rs) ) {
						do_action('woopanel_created_term', $rs['term_id'], array());
					}
				}

				if( is_wp_error($rs) ) {
					wpl_add_notice( "edit_term", $rs->get_error_message(), 'error' );
				}else {
					wpl_add_notice( "edit_term", esc_html__( 'Term updated successfully', 'woocommerce' ), 'success' );
				}

				wp_redirect($redirect_url);
				die();



			}


		}else {
			$attribute = new stdClass();
			$attribute->attribute_name = '';
			$attribute->attribute_label = '';
			$attribute->attribute_type = 'select';
			$attribute->attribute_orderby = 'menu_order';
			$attribute->attribute_public = 0;

			if( $this->edit ) {
				$attribute = $wpdb->get_row($wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_id = %d", 
					$this->edit
				));
			}

			// Delete Terms
			if ( isset($_GET['_nonce']) && wp_verify_nonce( $_GET['_nonce'], 'wpl_attr_delete_nonce' ) ) {
				wc_delete_attribute($this->edit);

				$redirect_url = woopanel_dashboard_url('product-attributes');
				wpl_add_notice( "delete_term", esc_html__( 'Attribute deleted successfully', 'woopanel' ), 'success' );
				wp_redirect($redirect_url);
				die();
			}

			if( isset($_POST['submit']) ) {
				// START: Filter Save Data
				$data = array();
				$data['attribute_label'] = isset($_POST['attribute_name']) ? woopanel_clean(wp_unslash($_POST['attribute_name'])) : '';
				if ( ! isset( $_POST['attribute_slug'] ) ) {
					$data['attribute_name'] = wc_sanitize_taxonomy_name( stripslashes( $data['tax_name'] ) );
				} else {
					$data['attribute_name'] = preg_replace( '/^pa\_/', '', wc_sanitize_taxonomy_name( stripslashes( $_POST['attribute_slug'] ) ) );
				}
				$data['attribute_type'] = isset($_POST['attribute_type']) ? woopanel_clean(wp_unslash($_POST['attribute_type'])) : 'select';
				$data['attribute_orderby'] = isset($_POST['attribute_orderby']) ? woopanel_clean(wp_unslash($_POST['attribute_orderby'])) : 'menu_order';
				$data['attribute_public'] = isset($_POST['attribute_public']) ? $_POST['attribute_public'] : 0;
				// END: Filter Save Data

				$redirect_url = woopanel_dashboard_url('product-attributes');
				if( $this->edit ) {
					$args      = array(
						'name'         => $data['attribute_label'],
						'slug'         => $data['attribute_name'],
						'type'         => $data['attribute_type'],
						'order_by'     => $data['attribute_orderby'],
						'has_archives' => $data['attribute_public'],
					);
			
					$id = wc_update_attribute( $this->edit, $args );
					$redirect_url = $redirect_url .'?edit='. $this->edit;
					if( is_wp_error($id) ) {
						do_action( 'woocommerce_attribute_updated', $term->term_id, array(), array() );
					}
				}else {
					$args      = array(
						'name'         => $data['attribute_label'],
						'slug'         => $data['attribute_name'],
						'type'         => $data['attribute_type'],
						'order_by'     => $data['attribute_orderby'],
						'has_archives' => $data['attribute_public'],
					);
			
					$id = wc_create_attribute( $args );

					if( is_wp_error($id) ) {
						do_action( 'woocommerce_attribute_added', $term->term_id, array() );
					}
				}

				if( is_wp_error($id) ) {
					wpl_add_notice( "edit_attribute", $id->get_error_message(), 'error' );
				}else {
					wpl_add_notice( "edit_attribute", esc_html__( 'Attribute updated successfully', 'woocommerce' ), 'success' );
				}

				wp_redirect($redirect_url);
				die();
			}
		}

		include_once WOOPANEL_VIEWS_DIR . 'attribute.php';
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
            foreach( $this->columns as $column_name => $column ) {
				if( isset($column['label']) ) { ?>
                <th scope="col" class="wpl-datatable__cell column-<?php echo $column_name;?>">
                    <span><?php echo $column['label'];?></span>
                    <span class="sorting-indicator"></span>
                </th>
                <?php
				}
            }
        }
	}

	/**
	 *
	 * @return bool
	 */
	public function has_items() {
		return $this->total_items;
	}

	/**
	 */
	public function no_items() {
		?>
		<tr class="no-items">
			<td class="colspanchange" colspan="<?php echo (count($this->columns) + 1);?>" style="text-align:center;">
				<div class="dashboard-block-empty">
					<?php
					if( ! has_action("woopanel_{$this->endpoint}_no_item_icon") ) {
						echo '<i class="la la-shopping-cart"></i>';
					}else {
						do_action("woopanel_{$this->endpoint}_no_item_icon");
					}?>
                    <?php if( isset($this->type_settings['singular_name']) ){ ?>
					    <h3><?php echo sprintf( __( 'No %s found.', 'woopanel' ), $this->type_settings['singular_name'] );?></h3>
                    <?php } ?>
					<p><?php _e('Not found', 'woopanel');?></p>
				</div>
			</td>
		</tr>
		<?php
	}
	
    protected function display_tablenav( $which ) {
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->endpoint );
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

    protected function bulk_actions( $which = '' ) {
		echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\" class=\"m-bootstrap-select m-bootstrap-select--solid m_selectpicker\" data-style=\"btn-sm\" data-width=\"\">\n";
		 echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";
 
		 foreach ( $this->_actions as $name => $title ) {
			 $class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
 
			 echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
		 }
 
		 echo "</select>\n";
 
		 echo '<button data-taxonomy="'. $this->taxonomy .'" data-security="'. wp_create_nonce( 'tax_verify_security' ) .'" type="button" id="doaction'. $two .'" class="btn btn-accent btn-sm m-btn m-loader--light m-loader--right btn-tax-actions" onclick="if(!this.classList.contains(\'m-loader\')) this.className+=\' m-loader\';">'. __( 'Apply' ) .'</button>';
		 echo "\n";
	 }

	/**
	 * Display the table
	 *
	 * @since 1.0.0
	 */
	public function display_rows_or_placeholder() {
		foreach( $this->get_results() as $index => $post) {
			$this->get_html_display_rows($post, $index);
		}
	}

	public function get_html_display_rows($term, $index) {
		$product = false;
		$data = '';
		?>
		<tr id="post-<?php echo $term->term_id;?>" class="wpl-datatable__row iedit author-self<?php if( $index % 2 == 1 ) { echo ' m-datatable__row--even';}?> level-0 post-<?php echo $term->term_id;?> post type-post status-publish format-standard has-post-thumbnail hentry category-comfort category-luxury category-market-updates category-sales category-uncategorized">
			<td class="wpl-datatable__cell--center wpl-datatable__cell wpl-datatable__cell--check check-column">
				<?php
				if( apply_filters("woopanel_bulk_action_{$this->taxonomy}", true, $term) ) { ?>
				<span>
					<label class="m-checkbox m-checkbox--single m-checkbox--solid m-checkbox--brand" for="cb-select-<?php echo $term->term_id;?>">
						<input id="cb-select-<?php echo $term->term_id;?>" type="checkbox" name="ids[]" value="<?php echo $term->term_id;?>">
						<span></span>
					</label>
				</span>
				<?php }?>
			</td>
			<?php
			if( empty($this->template) ) {
				foreach( $this->columns as $key_column => $column ) {
					if( isset($column['label']) ) {?>
						<td class="wpl-datatable__cell column-<?php echo $key_column;?>" data-colname="<?php echo strip_tags($column['label']);?>">
							<?php
							$action_name = "woopanel_{$this->endpoint}_{$key_column}_column";
							if( ! has_filter($action_name) ) {
								echo $action_name;
							}else {
								echo apply_filters($action_name, '', $term, $data);
							}
							
							?>
						</td>
					<?php
					}
				}
			}else {
				woopanel_get_template_part( $this->template, '', array(
					'data' => $term,
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
		$max_num_pages = ceil($this->total_items / $this->per_page);
		?>
		<form id="posts-filter" method="GET">
			<div id="list-<?php echo $this->endpoint;?>-table" class="woopanel-list-post-table m-portlet m-portlet--mobile">
				<!--begin: Head -->
				<div class="m-portlet__head">
					<div class="m-portlet__head-caption">
						<div class="m-portlet__head-title">
							<h3 class="m-portlet__head-text">
								<?php
								if( $this->taxonomy ) {
									echo $this->attribute->attribute_label;
								}else {
									echo __('Attributes', 'woocommerce');
								}?>
								<small><span class="displaying-num"><?php echo sprintf( _n( '%s item', '%s items', $this->total_items ), number_format_i18n( $this->total_items ) );?></span></small>
							</h3>
						</div>
					</div>
				</div>
				<!--end: Head -->
				<div class="m-portlet__body">
					<?php if( $this->has_items() || isset($_GET['date']) ) {?>
					<!--begin: Search Form -->
					<div class="m-form m-form--label-align-right m--margin-top-20 m--margin-bottom-30">
						<div class="row align-items-center">
							<div class="col-xl-9 order-2 order-xl-1">
								<div class="form-group m-form__group align-items-center">
									<div class="m-input-icon m-input-icon--left">
										<input type="text" class="form-control m-input" placeholder="<?php echo empty($this->tax->hierarchical) ? __( 'Search Tags' ) : __( 'Search Categories' );?>" name="search_name" placeholder="<?php echo esc_html( $this->type_settings['search_items']); ?>" id="generalSearch" value="<?php echo isset($_GET['search_name']) ? strip_tags($_GET['search_name']) : '';?>">
										<span class="m-input-icon__icon m-input-icon__icon--left">
											<span><i class="la la-search"></i></span>
										</span>
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
										$this->display_rows_or_placeholder();
									}else {
										$this->no_items();
									}?>
								</tbody>
							</table>
						</div>

						
						<?php
						if ( $this->has_items() ) {

							$this->display_paginate($this->total_items, $max_num_pages);
						}?>
					</div>
					
				</div><!-- .m-portlet__body -->
			</div>
		</form>
        <?php
	}

	public function query() {
		global $wpdb;

		

		

		$query = array();

		if( $this->taxonomy ) {
			$query['select'] = "SELECT * FROM {$wpdb->terms} as t";
			$query['join']   = "INNER JOIN {$wpdb->prefix}term_taxonomy AS tt ON tt.term_id = t.term_id";
			$query['where']  = $wpdb->prepare("WHERE tt.taxonomy = %s", $this->taxonomy);
		}else {
			$query['select'] = "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies as at";
			$query['where'] = "WHERE 1=1";
		}

		return $query;

	}

	public function get_count() {
		global $wpdb;

		$query = $this->query();

		if( $this->taxonomy ) {
			$query['select'] = "SELECT COUNT( DISTINCT t.term_id ) as total FROM {$wpdb->terms} as t";
		}else {
			$query['select'] = "SELECT COUNT( DISTINCT at.attribute_id ) as total FROM {$wpdb->prefix}woocommerce_attribute_taxonomies as at";
		}

		$sql = implode(' ', $query);

		return $wpdb->get_var($sql);
	}

	public function get_results() {
		global $wpdb;

		$offset = ($this->paged - 1) * $this->per_page;

		$query = $this->query();
		$query['limit']   = "LIMIT {$offset}, {$this->per_page}";

		$sql = implode(' ', $query);

		return $wpdb->get_results($sql);
	}

}