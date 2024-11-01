<?php
class WooPanel_Template_Coupon {
	private $get_order_statuses = array();
	private $classes;

	public function __construct() {
		$this->get_order_statuses = array(
			'publish' => __('Published'),
			'pending' => __('Pending Review'),
			'draft' => __('Draft')
		);

		$this->classes = new WooPanel_Post_List_Table(array(
			'post_type'     	=> 'shop_coupon',
			'taxonomy'			=> false,
			'editor'			=> false,
			'thumbnail'			=> false,
			'preview'			=> false,
			'permalink'			=> false,
			'screen'        	=> 'coupons',
			'columns'       	=> array(
				'title'     	=> __( 'Code', 'woocommerce' ),
				'type'      	=> __( 'Coupon type', 'woocommerce' ),
				'amount'    	=> __( 'Coupon amount', 'woocommerce' ),
				'description'   => __( 'Description', 'woocommerce' ),
				'usage'     	=> __( 'Usage / Limit', 'woocommerce' ),
				'expiry_date'   => __( 'Expiry date', 'woocommerce' )
			),
			'primary_columns' 	=> 'code',
			'post_statuses' 	=> $this->get_order_statuses,
		));

		$this->hooks();
	}

	public function hooks() {
		// Custom column data
		add_filter( 'woopanel_shop_coupon_type_column', array($this, 'woopanel_shop_coupon_type_custom'), 99, 3);
		add_filter( 'woopanel_shop_coupon_amount_column', array($this, 'woopanel_shop_coupon_amount_custom'), 99, 3);
		add_filter( 'woopanel_shop_coupon_description_column', array($this, 'woopanel_shop_coupon_description_custom'), 99, 3);
		add_filter( 'woopanel_shop_coupon_usage_column', array($this, 'woopanel_shop_coupon_usage_custom'), 99, 3);
		add_filter( 'woopanel_shop_coupon_expiry_date_column', array($this, 'woopanel_shop_coupon_expiry_date_custom'), 99, 3);

		add_action( 'woopanel_shop_coupon_filter_display', array($this, 'woopanel_filter_display'), 99, 2 );
		add_filter( 'posts_distinct', array($this, 'coupon_search_distinct'), 99, 1 );
		add_action('woopanel_shop_coupon_no_item_icon', array($this, 'woopanel_shop_coupon_no_item_icon'));

		add_action('woopanel_shop_coupon_form_fields', array($this, 'woopanel_shop_coupon_form_fields'), 99, 1 );
		add_action('woopanel_save_shop_coupon_post_meta', array($this, 'woopanel_save_shop_coupon_post_meta'), 99, 2);
		
		add_filter('woopanel_shop_coupon_state', array($this, 'woopanel_shop_coupon_state'), 99, 2);
	}

	public function woopanel_shop_coupon_no_item_icon() {
		echo '<i class="flaticon-price-tag"></i>';
	}

	public function woopanel_shop_coupon_state($return, $post) {
		if( $post->post_status != 'publish') {
			return '  — <span class="post-state">'. $this->get_order_statuses[$post->post_status] .'</span>';
		}
	}

	public function woopanel_shop_coupon_type_custom($html, $post, $wc_coupon) {
		echo '<mark class="m-badge m-badge--brand m-badge--wide coupon-types-' . $wc_coupon->get_discount_type() . '"><span>' . esc_html( wc_get_coupon_type( $wc_coupon->get_discount_type() ) ) . '</span></mark>';
	}

	public function woopanel_shop_coupon_amount_custom($html, $post, $wc_coupon) {
		echo esc_html( wc_format_localized_price( $wc_coupon->get_amount() ) );
	}

	public function woopanel_shop_coupon_description_custom($html, $post, $wc_coupon) {
		echo esc_html( wc_format_localized_price( $wc_coupon->get_description() ) );
	}

	public function woopanel_shop_coupon_usage_custom($html, $post, $wc_coupon) {
		echo $wc_coupon->get_usage_limit() ? $wc_coupon->get_usage_limit() : '&ndash;';;
	}

	public function woopanel_shop_coupon_expiry_date_custom($html, $post, $wc_coupon) {
		echo $wc_coupon->get_date_expires() ? $wc_coupon->get_date_expires()->date_i18n( 'F j, Y' ) : '&ndash;';
	}

	public function woopanel_shop_coupon_action_custom($html, $post, $wc_coupon) {
		echo '<a class="button wc-action-button" href="" aria-label="Complete" title="" data-toggle="tooltip" data-placement="top" data-original-title="'. esc_html__( 'Edit coupon', 'woopanel' ) .'"><i class="la la-edit"></i></a>';
	}

	public function woopanel_filter_display($post_type, $post_type_object) {
		$status = isset($_GET['status']) ? strip_tags($_GET['status']) : '';
		?>
		<div class="col-md-4">
			<div class="m-form__group m-form__group--inline">
				<div class="m-form__label"><label for="filter-status"><?php _e('Status', 'woocommerce');?></label></div>
				<div class="m-form__control">
					<select name="status" id="filter-status" class="form-control m-bootstrap-select">
						<option selected='selected' value="0"><?php _e( 'All status', 'woopanel' );?></option>
						<?php foreach( $this->get_order_statuses as $k_status => $val_status) {
							printf('<option value="%s" %s>%s</option>', $k_status, selected( $k_status, $status, false ), $val_status);
						}?>
					</select>
				</div>
			</div>
			<div class="d-md-none m--margin-bottom-10"></div>
		</div>
		<?php
	}

	public function coupon_search_distinct( $where ) {
		return "DISTINCT";
	}

	public function woopanel_shop_coupon_form_fields($post_id) {
		$coupon_id = absint( $post_id );
		$coupon    = new WC_Coupon( $coupon_id );

		?>
		<div class="m-portlet m-portlet--tabs">
			<div class="m-portlet__head">
				<div class="m-portlet__head-tools">
					<ul class="nav nav-tabs m-tabs-line m-tabs-line--primary m-tabs-line--2x" role="tablist">
						<li class="nav-item m-tabs__item">
							<a class="nav-link m-tabs__link active" data-toggle="tab" href="#m_tabs_general" role="tab">
								<i class="la la-cog"></i> <?php esc_html_e('General', 'woocommerce');?>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<div class="m-portlet__body">
				<div class="tab-content">
					<div class="tab-pane active" id="m_tabs_general" role="tabpanel">
						<div class="m-form__section  m--margin-top-20 m--margin-bottom-20">
							<?php
								woopanel_form_field(
									'discount_type',
									array(
										'type'	  => 'select',
										'id'      => 'discount_type',
										'label'   => __( 'Discount type', 'woocommerce' ),
										'options' => wc_get_coupon_types(),
										'form_inline' => true
									),
									$coupon->get_discount_type( 'edit' )
								);
						
								woopanel_form_field(
									'coupon_amount',
									array(
										'type'		  => 'number',
										'id'          => 'coupon_amount',
										'label'       => __( 'Coupon amount', 'woocommerce' ),
										'placeholder' => wc_format_localized_price( 0 ),
										'description' => __( 'Value of the coupon.', 'woocommerce' ),
										'form_inline' => true
									),
									$coupon->get_amount( 'edit' )
								);
						
								if ( wc_shipping_enabled() ) {
									woopanel_form_field(
										'free_shipping',
										array(
											'type'		  => 'checkbox',
											'id'          => 'free_shipping',
											'label'       => __( 'Allow free shipping', 'woocommerce' ),
											'description' => sprintf( __( 'Check this box if the coupon grants free shipping. A <a href="%s" target="_blank">free shipping method</a> must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).', 'woocommerce' ), 'https://docs.woocommerce.com/document/free-shipping/' ),
											'form_inline' => true,
											'default'	  => 'yes'
										),
										wc_bool_to_string( $coupon->get_free_shipping( 'edit' ) )
									);
								}
						
								// Expiry date.
								$expiry_date = $coupon->get_date_expires( 'edit' ) ? $coupon->get_date_expires( 'edit' )->date( 'Y-m-d' ) : '';
								woopanel_form_field(
									'expiry_date',
									array(
										'type'				=> 'datepicker',
										'id'                => 'expiry_date',
										'default'             => esc_attr( $expiry_date ),
										'label'             => __( 'Coupon expiry date', 'woocommerce' ),
										'placeholder'       => 'YYYY-MM-DD',
										'description'       => '',
										'input_class'        => array('m-datepicker', 'date-picker'),
										'custom_attributes' => array(
											'pattern' => apply_filters( 'woocommerce_date_input_html_pattern', '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])' ),
										),
										'form_inline' => true
									),
									$coupon->get_date_expires( 'edit' ) ? $coupon->get_date_expires( 'edit' )->date( 'Y-m-d' ) : ''
									
								);
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function woopanel_save_shop_coupon_post_meta($post_id, $data) {
		update_post_meta( $post_id, 'discount_type', $data['discount_type'] );
		update_post_meta( $post_id, 'coupon_amount', $data['coupon_amount'] );
		update_post_meta( $post_id, 'free_shipping', $data['free_shipping'] );
		update_post_meta( $post_id, 'expiry_date', $data['expiry_date'] );
	}


	public function lists() {
		$this->classes->prepare_items();
		$this->classes->display();
	}

	public function form() {
		$this->classes->form();
	}
}