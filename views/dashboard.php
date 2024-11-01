<div id="dashboard-wrapper">
	<?php
	if( is_woo_available() ) {
		include WOOPANEL_TEMPLATE_DIR . 'dashboard/' . 'statistic.php';
	}
	
    global $woopanel_order_status;


    $dashboard_widgets = get_option('woopanel_dashboard_widgets');

	$new_widgets = array();
	if( ! empty($dashboard_widgets) && is_array($dashboard_widgets) ) {
		foreach ($dashboard_widgets as $key => $value) {
			$new_widgets[$value] = $this->widgets[$value];
		}

		if( ! empty($new_widgets) ) {
			$this->widgets = $new_widgets;
		}
	}

	
	if( ! empty($this->widgets) && is_array($this->widgets) ) {
		$total = count($this->widgets) - 1;
		$index = 0;
		foreach( $this->widgets as $k => $widget ) {
			if( $index % 2 == 0) {
				echo '<div class="row">';
			}

			if( isset($widget['cols']) && isset($widget['enable']) && $widget['enable'] ) {
				$file_template = WOOPANEL_TEMPLATE_DIR . 'dashboard/' . $widget['template'];
				$empty_class = '';
				if( ! file_exists( $file_template ) ) {
					$empty_class = ' empty-container';
				}

				echo '<div class="' . $widget['cols'] . '">';

				if( file_exists( $file_template ) ) {
					include $file_template;
				}else {
					echo $file_template;
				}

				echo '</div>';
			}

			if( $index % 2 == 1 || $index == $total) {
				echo '</div>';
			}

			$index++;
		}
	} ?>
	
</div>