var WooPanel_BlockUI = {
	/**
	 * Init jQuery.BlockUI
	 */
	block: function($el) {
		$el.block({
			message: '<div class="loading-message"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>',
			overlayCSS: {
				background: '#555',
				opacity: 0.1
			}
		});
	},

	/**
	 * Remove jQuery.BlockUI
	 */
	unblock: function($el) {
		$el.unblock();
	}
}

function number_format(number, decimals, dec_point, thousands_point) {

    if (number == null || !isFinite(number)) {
        throw new TypeError("number is not valid");
    }

    if (!decimals) {
        var len = number.toString().split('.').length;
        decimals = len > 1 ? len : 0;
    }

    if (!dec_point) {
        dec_point = '.';
    }

    if (!thousands_point) {
        thousands_point = ',';
    }

    number = parseFloat(number).toFixed(decimals);

    number = number.replace(".", dec_point);

    var splitNum = number.split(dec_point);
    splitNum[0] = splitNum[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousands_point);
    number = splitNum.join(dec_point);

    return number;
}
	
var WooPanel_Dashboard_ChartJSOrder = {
	init: function() {
		var $ = jQuery.noConflict(),
			$chartorder = $('.chartorder_body');
	
		this.datepicker();
		this.display( $chartorder.find('canvas').attr('id'), jQuery.parseJSON($chartorder.attr('data-horizontal')), jQuery.parseJSON($chartorder.attr('data-vertical')));
		
		/**
		 * Event actions
		 */
		$(document).on( 'click', '#chart-order-wrapper .m-nav__item', this.setStatus );
	},
	
	setStatus: function(e) {
		e.preventDefault();
		
		var $ = jQuery.noConflict(),
            $this = $(this),
			wrapper = $('#chart-order-wrapper'),
			labelText = $this.find('.m-nav__link-text').text();
			
		$this.closest('ul').find('li').removeClass('active');
		$this.addClass('active');
		
		var orderStatus = wrapper.find('.m-chart-status li.active').attr('data-value'),
			orderFilter = wrapper.find('.m-chart-filter li.active').attr('data-value');

		if( orderFilter != 'custom-range' ) {
			$this.closest('.m-portlet__nav-item').find('.m-btn--label-brand').text(labelText);
			WooPanel_Dashboard_ChartJSOrder.change(orderStatus, orderFilter);
		}else {
			if( $this.closest('li.m-chart-filter').length > 0 ) {
				WooPanel_Dashboard_ChartJSOrder.show_datepicker($this);
			}else {
				$this.closest('.m-portlet__nav-item').find('.m-btn--label-brand').text(labelText);
				var orderRange = wrapper.find('.m-chart-filter li.active').attr('data-date');
				WooPanel_Dashboard_ChartJSOrder.change(orderStatus, orderFilter, orderRange);
			}
		}
	},
	
	change: function(orderStatus, orderFilter, orderRange = '') {

		var $ = jQuery.noConflict(),
			wrapper = $('#chart-order-wrapper');
			
		WooPanel_BlockUI.block(wrapper);
		
		$.ajax({
			url: WooPanel.ajaxurl,
			data: 'action=woopanel_get_chart_orders&filter=' + orderFilter + '&status=' + orderStatus + '&range=' + orderRange,
			type: 'POST',
			datatype: 'json',
			success: function( response ) {
				WooPanel_BlockUI.unblock(wrapper);
				
				
				wrapper.find('.m-portlet__body').html('<canvas id="chartorder-' + orderFilter + '" height="320" style="width: 100%; height: 320px;"></canvas>');
				
				$('#chart-order-wrapper .m-chart-label').html(response.total);
				$('#chart-order-wrapper canvas').attr( 'id', 'chartorder-' + orderFilter );
				
				WooPanel_Dashboard_ChartJSOrder.display('chartorder-' + orderFilter, response.horizontal, response.vertical);
			},
			error:function(){
				alert('There was an error when processing data, please try again !');
			}
		});
	},
	
	display: function(el, horizontal, vertical, range = false) {
		var $ = jQuery.noConflict(),
            canvas_chartorder = document.getElementById(el);
		var ctx_chartorder = canvas_chartorder.getContext("2d");
		var lineChartData_order = {
			labels : horizontal,
			datasets : [
				{
					label: "My Second dataset",
					backgroundColor: "rgb(113, 106, 202, 0.3)",
					borderColor: "rgb(113, 106, 202, 1)",
					borderWidth: 2,
					pointBackgroundColor: "rgb(113, 106, 202, 1)",
					pointBorderColor: "#fff",
					pointBorderWidth: 1,
					pointHoverRadius: 5,
					pointHoverBackgroundColor: "#fff",
					pointHoverBorderColor: "rgb(113, 106, 202, 1)",
					pointHoverBorderWidth: 1,
					pointRadius: 4,
					pointHitRadius: 10,
					data: vertical
				}
			]
		}

		new Chart(ctx_chartorder , {
			type: "line",
			data: lineChartData_order,
			responsive: true,
			scaleFontFamily: "'Open Sans'",
			tooltipTitleFontFamily: "'Open Sans'",
			options: {
				responsive: true,
				legend: {
					display: false,
				},
				tooltips: {
					callbacks: {
						label: function(tooltipItem, data) {
							price = data['datasets'][0]['data'][tooltipItem['index']];
							
							var label_order = WooPanel.label.items;
							if( price == 1) {
								label_order = WooPanel.label.item;
							}
							return ' ' + tooltipItem.xLabel + ': ' + price + ' ' + label_order;
						},
						title: () => null,
					}
				},
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true
						}
					}]
				}
			},
		});
	},
	
	datepicker: function() {
		if( jQuery().daterangepicker ) {
			var $ = jQuery.noConflict(),
                $datepick = $('#chart-order-wrapper input[name="datefilter"]');
			
			$datepick.daterangepicker({
				autoUpdateInput: false,
				locale: {
				  cancelLabel: 'Clear'
				}
			});

			$('<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: 40px; right: auto;"></span>').insertBefore('.daterangepicker .ranges');

			$datepick.on('cancel.daterangepicker', function(ev, picker) {
				$(this).val('');
			});
			
			$datepick.on('apply.daterangepicker', function(ev, picker) {
				var $this = $(this),
					$box = $this.closest('#chart-order-wrapper'),
					$wrapper = $this.closest('.m-chart-filter'),
					$val = picker.startDate.format('YYYY/MM/DD') + '-' + picker.endDate.format('YYYY/MM/DD'),
					orderStatus = $box.find('.m-chart-status li.active').attr('data-value'),
					orderFilter = $box.find('.m-chart-filter li.active').attr('data-value');
			
				$this.val($val);
				$box.find('.m-chart-filter .m-btn--label-brand').text(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
				$wrapper.find('.m-nav__item.active').attr('data-date', $val);

				WooPanel_Dashboard_ChartJSOrder.change(orderStatus, orderFilter, $val);
			});
		}
	},
	
	show_datepicker: function($this) {
		var $ = jQuery.noConflict();

            $('#chart-order-wrapper input[name="datefilter"]').trigger('click');
		$this.closest('.m-portlet__nav-item').removeClass('m-dropdown--open');
	},
}

var WooPanel_Dashboard_ChartJSAmount = {

	/**
	 * Initialize actions
	 */
	init: function() {
		var $ = jQuery.noConflict(),
            $chartamount = $('.chartamount_body');
		this.datepicker();
		this.display( $chartamount.find('canvas').attr('id'), jQuery.parseJSON($chartamount.attr('data-horizontal')), jQuery.parseJSON($chartamount.attr('data-vertical')) );
		
		/**
		 * Event actions
		 */
		$(document).on( 'click', '#chart-amount-wrapper .m-nav__item', this.setStatus );
	},
	
	setStatus: function(e) {
		e.preventDefault();
		
		var $ = jQuery.noConflict(),
            $this = $(this),
			wrapper = $('#chart-amount-wrapper'),
			labelText = $this.find('.m-nav__link-text').text();
			
		$this.closest('ul').find('li').removeClass('active');
		$this.addClass('active');
		
		var orderStatus = wrapper.find('.m-chart-status li.active').attr('data-value'),
			orderFilter = wrapper.find('.m-chart-filter li.active').attr('data-value');

		if( orderFilter != 'custom-range' ) {
			$this.closest('.m-portlet__nav-item').find('.m-btn--label-brand').text(labelText);
			WooPanel_Dashboard_ChartJSAmount.change(orderStatus, orderFilter);
		}else {
			if( $this.closest('li.m-chart-filter').length > 0 ) {
				WooPanel_Dashboard_ChartJSAmount.show_datepicker($this);
			}else {
				$this.closest('.m-portlet__nav-item').find('.m-btn--label-brand').text(labelText);
				var orderRange = wrapper.find('.m-chart-filter li.active').attr('data-date');
				WooPanel_Dashboard_ChartJSAmount.change(orderStatus, orderFilter, orderRange);
			}
		}
	},
	
	change: function(orderStatus, orderFilter, orderRange = '') {

		var $ = jQuery.noConflict(),
			wrapper = $('#chart-amount-wrapper');
			
		WooPanel_BlockUI.block(wrapper);
		
		$.ajax({
			url: WooPanel.ajaxurl,
			data: 'action=woopanel_get_chart_amounts&filter=' + orderFilter + '&status=' + orderStatus + '&range=' + orderRange,
			type: 'POST',
			datatype: 'json',
			success: function( response ) {
				WooPanel_BlockUI.unblock(wrapper);
				
				
				wrapper.find('.m-portlet__body').html('<canvas id="chartamount-' + orderFilter + '" height="360" style="width: 100%; height: 360px;"></canvas>');
				
				$('#chart-amount-wrapper .m-chart-label').html(response.total);
				$('#chart-amount-wrapper canvas').attr( 'id', 'chartamount-' + orderFilter );
				
				WooPanel_Dashboard_ChartJSAmount.display('chartamount-' + orderFilter, response.horizontal, response.vertical);
			},
			error:function(){
				alert('There was an error when processing data, please try again !');
			}
		});
	},
	
	display: function(el, horizontal, vertical, range = false) {
		var $ = jQuery.noConflict(),
            canvas_chartorder = document.getElementById(el);
		var ctx_chartorder = canvas_chartorder.getContext("2d");
		var lineChartData_order = {
			labels : horizontal,
			datasets : [
				{
					label: "My Second dataset",
					backgroundColor: "rgb(113, 106, 202, 0.3)",
					borderColor: "rgb(113, 106, 202, 1)",
					borderWidth: 2,
					pointBackgroundColor: "rgb(113, 106, 202, 1)",
					pointBorderColor: "#fff",
					pointBorderWidth: 1,
					pointHoverRadius: 5,
					pointHoverBackgroundColor: "#fff",
					pointHoverBorderColor: "rgb(113, 106, 202, 1)",
					pointHoverBorderWidth: 1,
					pointRadius: 4,
					pointHitRadius: 10,
					data: vertical
				}
			]
		}

		new Chart(ctx_chartorder , {
			type: "line",
			data: lineChartData_order,
			responsive: true,
			scaleFontFamily: "'Open Sans'",
			tooltipTitleFontFamily: "'Open Sans'",
			options: {
				responsive: true,
				legend: {
					display: false,
				},
				tooltips: {
					callbacks: {
						label: function(tooltipItem, data) {
							price = data['datasets'][0]['data'][tooltipItem['index']];
							price = number_format(price, WooPanel.decimals, WooPanel.decimal_separator, WooPanel.thousand_separator);
							return ' ' + tooltipItem.xLabel + ': ' + WooPanel.format_money.replace("number", price);
						},
						title: () => null,
					}
				},
				scales: {
					yAxes: [{
						ticks: {
							beginAtZero: true
						}
					}]
				}
			},
		});
	},
	
	datepicker: function() {
		if( jQuery().daterangepicker ) {
			var $ = jQuery.noConflict(),
                $datepick = $('#chart-amount-wrapper input[name="datefilter"]');
			
			$datepick.daterangepicker({
				autoUpdateInput: false,
				locale: {
				  cancelLabel: 'Clear'
				}
			});

			$('<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: 40px; right: auto;"></span>').insertBefore('.daterangepicker .ranges');

			$datepick.on('cancel.daterangepicker', function(ev, picker) {
				$(this).val('');
			});
			
			$datepick.on('apply.daterangepicker', function(ev, picker) {
				var $this = $(this),
					$box = $this.closest('#chart-amount-wrapper'),
					$wrapper = $this.closest('.m-chart-filter'),
					$val = picker.startDate.format('YYYY/MM/DD') + '-' + picker.endDate.format('YYYY/MM/DD'),
					orderStatus = $box.find('.m-chart-status li.active').attr('data-value'),
					orderFilter = $box.find('.m-chart-filter li.active').attr('data-value');
			
				$this.val($val);
				$box.find('.m-chart-filter .m-btn--label-brand').text(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
				$wrapper.find('.m-nav__item.active').attr('data-date', $val);

				WooPanel_Dashboard_ChartJSAmount.change(orderStatus, orderFilter, $val);
			});
		}
	},
	
	show_datepicker: function($this) {
		var $ = jQuery.noConflict();

            $('#chart-amount-wrapper input[name="datefilter"]').trigger('click');
		$this.closest('.m-portlet__nav-item').removeClass('m-dropdown--open');
	},
}

jQuery(document).ready(function() {
	WooPanel_Dashboard_ChartJSOrder.init();
	WooPanel_Dashboard_ChartJSAmount.init();
});