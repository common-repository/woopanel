<?php
class WooPanel_Report_Order extends WooPanel_Report {
	
	/**
	 * The report data.
	 *
	 * @var stdClass
	 */
	private $report_data;
	
	public $start_date;
	
	public $end_date;

	public $order_status;

	public function get_report_filter_data() {
		if ( empty( $this->report_data ) ) {
			$this->query_report_filter_data();
		}

		$datediff = $this->end_date - $this->start_date;
		$total_day = ($datediff + 86400) / 86400;


		$new = array();
		for($day = 0; $day < $total_day; $day++) {
			$strtotime = strtotime("+" . $day . " day", $this->start_date);
			
			$new['vertical'][$day] = 0;
			if($this->report_data->order_amount) {
				foreach( $this->report_data->order_amount as $k => $v) {
					if($v['post_date'] == date('Y-m-d', $strtotime)) {
						$new['vertical'][$day] = $v['total_sales'];
					}
				}
			}

			$new['horizontal'][$day] = date('j M', $strtotime);
		}

		$new['total'] = wc_price($this->report_data->total_amount);


		return $new;
	}

	/**
	 * Get report data.
	 *
	 * @return stdClass
	 */
	public function get_report_status_data() {
		if ( empty( $this->report_data ) ) {
			$this->query_report_status_data();
		}
		
		$datediff = $this->end_date - $this->start_date;
		$total_day = ($datediff + 86400) / 86400;

		$new = array();
		for($day = 0; $day < $total_day; $day++) {
			$strtotime = strtotime("+" . $day . " day", $this->start_date);
			
			$new['vertical'][$day] = 0;
			if($this->report_data->order_counts) {
				foreach( $this->report_data->order_counts as $k => $v) {
					if($v['post_date'] == date('Y-m-d', $strtotime)) {
						$new['vertical'][$day] = $v['count'];
					}
				}
			}

			$new['horizontal'][$day] = date('j M', $strtotime);
		}

		

		
		$total = $this->report_data->total_orders;
		$total = $total . ' ' . ($total == 1 ? __('order', 'nb-dashboard') : __('orders', 'nb-dashboard') );
		$new['total'] = $total;


		return $new;
	}

	public function get_total_rev() {
		$this->query_rev_data();
		return $this->report_data->total_amount;
	}
	
	/**
	 * Get all data needed for this report and store in the class.
	 */
	private function query_report_status_data() {
		$this->report_data = new stdClass();

		if( empty($this->order_status) ) {
			$this->order_status = array( 'completed' );
		}

		$this->report_data->order_counts = (array) $this->get_order_report_data(
			array(
				'select'	   => 'product_linked.*, COUNT(DISTINCT posts.id) as count, DATE_FORMAT(posts.post_date, "%Y-%m-%d") as post_date',
				'data'         => array(
					'_product_id' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => '',
						'name'            => 'product_id',
					),
					'product_linked' => array(
						'type'            => 'product_linked'
					),
					'ID'        => array(
						'type'     => 'post_data',
						'function' => 'COUNT',
						'name'     => 'count',
						'distinct' => true,
					),
					'post_date' => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'     => $this->group_by_query,
				'order_by'     => 'posts.post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_types'  => wc_get_order_types( 'order-count' ),
				'order_status' => $this->order_status,
				'debug' => false
			)
		);

		// Total orders in this period, even if refunded.
		$this->report_data->total_orders = absint( array_sum( wp_list_pluck( $this->report_data->order_counts, 'count' ) ) );
	}

	/**
	 * Get all data needed for this report and store in the class.
	 */
	private function query_report_filter_data() {
		$this->report_data = new stdClass();

		if( empty($this->order_status) ) {
			$this->order_status = array( 'completed' );
		}

		$this->report_data->order_amount = (array) $this->get_order_report_data(
			array(
				'select'	   => 'product_linked.*, SUM( meta__order_total.meta_value) as total_sales, DATE_FORMAT(posts.post_date, "%Y-%m-%d") as post_date',
				'data'         => array(
					'_product_id' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => '',
						'name'            => 'product_id',
					),
					'product_linked' => array(
						'type'            => 'product_linked'
					),
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'     => $this->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => true,
				'order_types'  => wc_get_order_types( 'sales-reports' ),
				'order_status' => $this->order_status,
			)
		);

		// Total orders in this period, even if refunded.
		$this->report_data->total_amount = absint( array_sum( wp_list_pluck( $this->report_data->order_amount, 'total_sales' ) ) );
	}

	/**
	 * Get all data needed for this report and store in the class.
	 */
	private function query_rev_data() {
		$this->report_data = new stdClass();

		if( empty($this->order_status) ) {
			$this->order_status = array( 'completed' );
		}

		$this->report_data->order_amount = (array) $this->get_order_report_data(
			array(
				'select'	   => 'product_linked.*, SUM( meta__order_total.meta_value) as total_sales, DATE_FORMAT(posts.post_date, "%Y-%m-%d") as post_date',
				'data'         => array(
					'_product_id' => array(
						'type'            => 'order_item_meta',
						'order_item_type' => 'line_item',
						'function'        => '',
						'name'            => 'product_id',
					),
					'product_linked' => array(
						'type'            => 'product_linked'
					),
					'_order_total'        => array(
						'type'     => 'meta',
						'function' => 'SUM',
						'name'     => 'total_sales',
					),
					'post_date'           => array(
						'type'     => 'post_data',
						'function' => '',
						'name'     => 'post_date',
					),
				),
				'group_by'     => $this->group_by_query,
				'order_by'     => 'post_date ASC',
				'query_type'   => 'get_results',
				'filter_range' => false,
				'order_types'  => wc_get_order_types( 'sales-reports' ),
				'order_status' => $this->order_status,
			)
		);

		// Total orders in this period, even if refunded.
		$this->report_data->total_amount = absint( array_sum( wp_list_pluck( $this->report_data->order_amount, 'total_sales' ) ) );
	}
}
