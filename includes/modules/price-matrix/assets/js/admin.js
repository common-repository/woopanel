jQuery.fn.selectText = function(){
	var doc = document;
	var element = this[0];

	if (doc.body.createTextRange) {
		var range = document.body.createTextRange();
		range.moveToElementText(element);
		range.select();
	} else if (window.getSelection) {
		var selection = window.getSelection();        
		var range = document.createRange();
		range.selectNodeContents(element);
		selection.removeAllRanges();
		selection.addRange(range);
	}
 };

jQuery( function( $ ) {
	if( typeof woocommerce_admin_meta_boxes_variations != 'undefined' ) {
		var WooPanel_Ajax_URL = woocommerce_admin_meta_boxes_variations.ajax_url;
		var WooPanel_Product_Load_Nonce = woocommerce_admin_meta_boxes_variations.load_variations_nonce;
		var WooPanel_Product_EnterPrice_Nonce = nb_pricematrix.input_price_nonce;
		var WooPanel_Product_SavePrice_Nonce = nb_pricematrix.save_price_nonce;
		
		var WooPanel_Product_ID = woocommerce_admin_meta_boxes_variations.post_id;
	}else {
		if( $('[name="post_title"]').length > 0 && $('#post_type').val() == 'product' ) {
			var WooPanel_Ajax_URL = WooPanel.ajaxurl;
			var WooPanel_Product_Load_Nonce = WooPanel.product.load_variations_nonce;
			var WooPanel_Product_EnterPrice_Nonce = WooPanel.product.input_price_nonce;
			var WooPanel_Product_SavePrice_Nonce = WooPanel.product.save_price_nonce;
			var WooPanel_Product_ID = WooPanel.product.post_id;		
		}

	}

	 var $el = $( '#woocommerce-product-data' );
	 /**
	  * Variations Price Matrix actions
	  */
	 var wc_meta_boxes_price_matrix_actions = {
 
		 /**
		  * Initialize variations actions
		  */
		 init: function() {
			 $( 'li.price_matrix_tab a' ).on( 'click', this.initial_load );
			 $(document).on('click', '.pm-icon.-plus', this.add_row);
			 $(document).on('click', '.pm-icon.-minus', this.remove_row);
			 $(document).on('focusout', '.pm-attributes-field', function(){
				 $(this).attr('data-option', this.value);
			 }).on('change', '.pm-attributes-field',this.change_attr);
			 $(document).on('click', '.save_price_matrix', this.save_price_matrix);
			 $(document).on('change', '#wc_price_matrix_is_heading', this.is_heading);
 
			 $(document).on('click', '.btn-enter-price', this.enter_price);
			 $(document).on('click', '.save_enter_price', this.save_price);
 
			 $(document).on('change', '.pm-direction-field',this.change_direction);
			 $(document).on('change', '.select-vacant-attribute', this.change_attr_enterprice);
 
			 $(document).on('click', '#_enable_price_matrix', this.enable_price_matrix);
			 $(document).on('keyup', '.entry-editing', this.tab_selected);
			$(document).on('click', '.entry-editing', this.text_selected);
			$(document).on('click', '.btn-order-attributes', this.show_order_attributes);
			$(document).on('change', '.select-order-attribute', this.change_order_attribute);
			$(document).on('keydown', '.entry-editing', this.nextTab);
			
			this.check_enable_price_matrix();
 
			this.is_heading();
		 },
		 
		 nextTab: function(e) {
			if( event.which == 9 ) {
				var $this = $(this),
					$td = $(this).closest('td.price');
				
				$('.price-matrix-table td.price').removeClass('selected');
				$('.price-matrix-table td.price .entry-editing').attr('contenteditable', 'false');
				$('.price-matrix-table td.price .entry-editing').removeClass('entry-editing');
				
				if( $td.next().hasClass('price') ) {
					var $action_td = $td.next();
				}else {
					var $tr = $this.closest('tr').next();
					
					if( $tr.length > 0) {
						var $action_td = $tr.find('td.price').first();
					}
				}
				
				if( typeof $action_td != 'undefined') {
					$action_td.addClass('selected');
					$action_td.find('.wrap > div').addClass('entry-editing');
					$action_td.find('.wrap > div').attr('contenteditable', 'true');
				}
			}
		 },
		 
		 /**
		  * Initial load variations
		  *
		  * @return {Bool}
		  */
		 initial_load: function() {
			wc_meta_boxes_price_matrix_ajax.block();

			var WooPanel_Product_ID = $('#post_ID').val();
			var isWP = false;
			if( typeof WooPanel != 'undefined') {
				isWP = true;
			}
			
			$.ajax({
				url: WooPanel_Ajax_URL,
				data: {
					action:     'pricematrix_load_variations',
					security:   WooPanel_Product_Load_Nonce,
					product_id: WooPanel_Product_ID,
					isWP: isWP
				},
				type: 'POST',
				datatype: 'json',
				success: function( rs ) {
					$('.woocommerce-message').remove();
					
					if(rs.complete != undefined){
						$('#price_matrix_options_inner').html(rs.template);
					}else{
						$('#price_matrix_options_inner').html($html_msg);
					}

					$("#price_matrix_table tbody").sortable({
						handle: '.pm-handle',
						update: function(event, ui) {
							$( "#price_matrix_table tbody > tr" ).each(function( index ) {
								$( this ).find('.pm-handle span').text( (index+1) );
							});
						}
					});
					$( "#order_attributes tbody" ).each(function( index ) {
						$(this).sortable({
							handle: '.pm-handle',
							update: function(event, ui) {
								var _order_status_key = [];
								var  _order_status_value = [];
								var attribute = $('.select-order-attribute').val();

								$(this).find( "> tr" ).each(function( index ) {
									var $val = $(this).find('.pm-attributes-field');

									$( this ).find('.pm-handle span').text( (index+1) );
									_order_status_key.push($val.val());
									_order_status_value.push($val.val().trim());
								});

					
								var WooPanel_Product_ID = $('#post_ID').val();
						
								$.ajax({
									url: WooPanel_Ajax_URL,
									data: {
										action:     'pricematrix_order_attribute',
										attribute: attribute,
										product_id: WooPanel_Product_ID,
										order_status: JSON.stringify(_order_status_key),
										order_status_text: JSON.stringify(_order_status_value)
									},
									type: 'POST',
									datatype: 'json',
									success: function( rs ) {

									},
									error:function(){
										alert('There was an error when processing data, please try again !');
										wc_meta_boxes_price_matrix_ajax.unblock();
									}
								});
							}
						});
					});
					

					wc_meta_boxes_price_matrix_ajax.unblock();
				},
				error:function() {
					alert('There was an error when processing data, please try again !');
					wc_meta_boxes_price_matrix_ajax.unblock();
				}
			});
		 },

		 show_order_attributes: function(e) {
			e.preventDefault();

			$('#order_attributes').slideToggle();
		 },

		 change_order_attribute: function(e) {
			e.preventDefault();

			var $attribute = $(this).val();

			$('#order_attributes table').hide();
			$('#order_attributes table[data-id="' + $attribute + '"]').show();
		 },

		 tab_selected: function(e){
			 if(e.which === 9) {
				 $(this).selectText();
			 }
		 },
		 text_selected: function(){
			 var $text = $(this).html();
			 if($text){
				 $(this).selectText();
			 }
		 },
		 check_enable_price_matrix: function(){
			 if($('#_enable_price_matrix').closest('label').hasClass('yes')){
				 $('#_enable_price_matrix').prop('checked', true);
				 $('.price_matrix_options').removeClass('hide');
			 }else{
				 $('#_enable_price_matrix').prop('checked', false);
				 $('.price_matrix_options').addClass('hide');
 
			 }
		 },
		 
		 enable_price_matrix: function() {

			if( $(this).is(':checked') ){
				
				$('.price_matrix_options').removeClass('hide');
				$('.m-tabs__item--active').removeClass('m-tabs__item--active');
			 }else
			 {
				 $('.price_matrix_options').addClass('hide');
				 $('.woocommerce_options_panel').hide();
				 $('#inventory_product_data').show();
				 $('.product_data_tabs > li').removeClass('active');
				 $('.inventory_options.inventory_tab').addClass('active');

				$('.inventory_options.inventory_tab > a').addClass('m-tabs__item--active');
				 $('#price_matrix').removeClass('m-tabs-content__item--active');
			 }
		 },
		 enter_price: function(){
			 wc_meta_boxes_price_matrix_ajax.block();
 
			 var wrapper_attributes = $( '#variable_product_options' ).find( '.woocommerce_variations' ).data( 'attributes' );
			 var $this = $(this);
			 var pm_attr = $("select[name='pm_attr[]']")
			   .map(function(){return $(this).val();}).get();
			 var pm_direction = $("select[name='pm_direction[]']")
			   .map(function(){return $(this).val();}).get();
 
			 $('#price-matrix-popup').remove();
			 
			var WooPanel_Product_ID = $('#post_ID').val();
	

			 $.ajax({
				 url: WooPanel_Ajax_URL,
				 data: {
					 action:     'pricematrix_input_price',
					 security:   WooPanel_Product_EnterPrice_Nonce,
					 product_id: WooPanel_Product_ID,
					 attr: wrapper_attributes
				 },
				 type: 'POST',
				 datatype: 'json',
				 success: function( rs ) {
					 $('.woocommerce-message').remove();
 
					 if(rs.complete == undefined){
						 alert(rs.msg);
					 }else{
						 $('body').append(rs.html);
 
						 $.magnificPopup.open({
						 items: {
							 src: '#price-matrix-popup'
						 },
							 type: 'inline',
							 midClick: true,
							 mainClass: 'mfp-fade',
							  callbacks: {
								  open: function(){
									  var $current_window = $(window).width() - 50;
									 var $width_table = $('.price-matrix-table').width() + 60;
 
									 if($width_table > 500 && $current_window > $width_table){
										 $('#price-matrix-popup').css({
											 "maxWidth": $width_table
										 });
									 }
								  }
							  }
						 });
					 }
					 
					 wc_meta_boxes_price_matrix_ajax.unblock();
				 },
				 error:function(){
					 alert('There was an error when processing data, please try again !');
					 wc_meta_boxes_price_matrix_ajax.unblock();
				 }
			});
 
		 },
		 save_price: function(){
			wc_meta_boxes_price_matrix_ajax.loading();
			$('.save_enter_price').prop('disabled', true);
 
			var price = [];
			var attr = [];
			$( ".price-matrix-table td.price .wrap > div" ).each(function( index ) {
				 var obj2 = JSON.parse($(this).closest('td.price').attr('data-attr'));
				 price.push({ "price" : $(this).text() });
				 attr.push(obj2);
			});
 
			$('.save_enter_price').text('Saving');
			
			var WooPanel_Product_ID = $('#post_ID').val();
	
			$.ajax({
				url: WooPanel_Ajax_URL,
				data: {
					 action:    'pricematrix_save_price',
					 security:   WooPanel_Product_SavePrice_Nonce,
					 product_id: WooPanel_Product_ID,
					 price: price,
					 attr: attr
				},
				type: 'POST',
				datatype: 'json',
				success: function( rs ) {
					 $('.woocommerce-message').remove();
 
					 if(rs.complete == undefined){
						 alert(rs.msg);
					 }else{
						 $('.save_enter_price').text('Saved');
					 }
 
					 wc_meta_boxes_price_matrix_ajax.unloaded();
					 $('.save_enter_price').prop('disabled', false);
					 
					 wc_meta_boxes_price_matrix_ajax.unblock();
				},
				error:function(){
					 alert('There was an error when processing data, please try again !');
					 wc_meta_boxes_price_matrix_ajax.unloaded();
				}
			});	 
		 },
		 is_heading: function(){
			 var $wc_price_matrix_heading = $('#wc_price_matrix_heading').closest('tr');
			 if($('#wc_price_matrix_is_heading').is(":checked")) {
				 $wc_price_matrix_heading.show();
			 }else{
				 $wc_price_matrix_heading.hide();
			 }
		 },
		add_row: function() {
			var totals_attributes = parseInt( $('#price_matrix_table').attr('data-count') ),
				totals_row = $('#price_matrix_table tbody .pm-row').length,
				wrapper_attributes = $( '#price_matrix_table' ).attr( 'data-product_variations' ),
				attributes = [],
				$row = $(this).closest('.pm-row');


			$( "#price_matrix_table .pm-attributes-field" ).each(function() {
				attributes.push( $(this).val() );
			});

			if( totals_attributes == totals_row ) {
				alert('Exceeds max number of attributes limit.');
			}else {
				wc_meta_boxes_price_matrix_ajax.block( $('#price_matrix') );

				$.ajax({
					url: WooPanel.ajaxurl,
					data: {
						action:     'pricematrix_add_row',
						security:   $('[name="security"]').val(),
						product_id: WooPanel.product.post_id,
						attributes: attributes.toString()
					},
					type: 'POST',
					datatype: 'json',
					success: function( response ) {
						wc_meta_boxes_price_matrix_ajax.unblock($('#price_matrix'));
						if( response.complete != undefined ) {
							$(response.template).insertAfter($row);
						}
					},
					error:function(){
						alert('There was an error when processing data, please try again !');
						wc_meta_boxes_price_matrix_ajax.unblock($('#price_matrix'));
					}
				});	
			}

			return false;
		},
		 remove_row: function(){
 
			 var $count = $('#price_matrix_table tbody > tr').length;
			 if($count > 2){
				 var $select = $(this).closest('.pm-row').find( ".pm-attributes-field" );
				 //wc_meta_boxes_price_matrix_actions.change_attr($select);
 
				 $( "#price_matrix_table tbody > tr" ).each(function(index) {
					 $(this).find('.order span').text(index + 1);
				 });
				 var str_options = $('.pm_repeater').attr('data-option');
				 if(!str_options){
					 var str_options = ',';
				 }
				 var array_options = str_options.split(",");
				 var index = array_options.indexOf($select.val());
				 if (index > -1) {
					 array_options.splice(index, 1);
				 }
				 array_options = array_options.filter(function(entry) { return entry.trim() != ''; });
				 html = array_options.join();
				 $('.pm_repeater').attr('data-option', html);
 
 
				 $( '.pm-attributes-field option[value="' + $select.val() + '"]' ).removeAttr('disabled');
 
				 $('.btn-enter-price').prop('disabled', true);
				 $('.save_price_matrix').prop('disabled', false);
				 $(this).closest('.pm-row').remove();
			 }else{
				 alert('Sorry, you can\'t remove this row, minimum requirement is 2 attributes!');
			 }
			 return false;
		 },
		 change_direction: function(){
			 $('.btn-enter-price').prop('disabled', true);
			 $('.save_price_matrix').prop('disabled', false);
		 },
		 change_attr: function( s ){
 
			 $('.btn-enter-price').prop('disabled', true);
			 $('.save_price_matrix').prop('disabled', false);
			 $('body').removeAttr('data-msg');
			 if(s.length){
				 var $this = s;
			 }else{
				 var $this = $(this);
				 var $pm_repeater = $('.pm_repeater');
				 var str_options = $pm_repeater.attr('data-option');
				 if(!str_options){
					 var str_options = ',';
				 }
				 var array_options = str_options.split(",");
 
				 if($this.val() != '0'){
					 array_options.push($this.val());
				 }else{
					 var old_val = $this.attr('data-option');
					 var index = array_options.indexOf(old_val);
					 if (index > -1) {
						 array_options.splice(index, 1);
					 }
				 }
				 array_options = array_options.filter(function(entry) { return entry.trim() != ''; });
				 html = array_options.join();
				 $pm_repeater.attr('data-option', html);
 
				 $( ".pm-attributes-field option" ).each(function( index ) {
					 if($this.val() == 0){
						 if(old_val == $(this).attr('value')){
							 $(this).removeAttr('disabled');
						 }
					 }else{
						 for(var i = 0; i<array_options.length; ++i){ 
							 if($(this).attr('value') != $(this).closest('select').val() && array_options[i] == $(this).attr('value')){
								 $(this).attr('disabled','disabled');
							 }
						 }
					 }
				 });
 
			 }
	 
		 },
		 change_attr_enterprice: function() {
			$('#price-matrix-popup').block({
				message: '<div class="loading-message"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>',
				overlayCSS: {
					background: '#555',
					opacity: 0.1
				}
			});
			 var $this = $(this);
			 var $val = $this.val();
 
 
			 var optionVal = {};
			 $( '.select-vacant-attribute' ).each(function( index ) {
				 if($(this).val()){
					 var $id = $(this).attr('id');
					 var $val = $(this).val();
 
					 optionVal[$id] = $val;
				 }
			 });
 
			 $.ajax({
				 url: WooPanel.ajaxurl,
				 data: {
					 action:     'pricematrix_load_table',
					 security:   $('[name="security"]').val(),
					 product_id: WooPanel.product.post_id,
					 vacant: optionVal,
					 load: true
				 },
				 type: 'POST',
				 datatype: 'json',
				 success: function( response ) {
					 $('.table-responsive').html(response.template);
					 $('#price-matrix-popup').unblock();
 
				 },
				 error:function(){
					 alert('There was an error when processing data, please try again !');
					 $('#price-matrix-popup').unblock();
				 }
			 });
		 },
		 save_price_matrix: function(){
			 var $this = $(this);
			 var pm_attr = $("select[name='pm_attr[]']")
			   .map(function(){return $(this).val();}).get();
			 var pm_direction = $("select[name='pm_direction[]']")
			   .map(function(){return $(this).val();}).get();
 
			wc_meta_boxes_price_matrix_ajax.block();

			$('.woocommerce-message.msg-enter-price').remove();

			var WooPanel_Product_ID = $('#post_ID').val();

			
			$.ajax({
				url: WooPanel_Ajax_URL,
				data: {
					action:     'pm_save_variations',
					security:   $('[name="security"]').val(),
					product_id: WooPanel_Product_ID,
					pm_attr: pm_attr,
					pm_direction: pm_direction,
					show : $('[name="_pm_show_on"]').val()
				},
				type: 'POST',
				datatype: 'json',
				success: function( response ) {
					if(response.complete != undefined) {
						$('#price_matrix_options_inner').append(response.notice);
						$this.prop('disabled', true);
						$('.btn-enter-price').removeAttr('disabled');
					}else{
						alert(response.message);
					}

					wc_meta_boxes_price_matrix_ajax.unblock();
				},
				error:function(){
					alert('There was an error when processing data, please try again !');
					wc_meta_boxes_price_matrix_ajax.unblock();
				}
			});
		}
	}
	 
	 var wc_meta_boxes_price_matrix_ajax = {
		 /**
		  * Init jQuery.BlockUI
		  */
		 block: function() {
			if( typeof woocommerce_admin_meta_boxes_variations != 'undefined' ) {
				$('#woocommerce-product-data').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
			}else {
				$('#product_data_portlet .m-portlet__body').block({
					message: '<div class="loading-message"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>',
					overlayCSS: {
						background: '#555',
						opacity: 0.1
					}
				});
			}
		 },
 
		 /**
		  * Remove jQuery.BlockUI
		  */
		 unblock: function() {
			if( typeof woocommerce_admin_meta_boxes_variations != 'undefined' ) {
				var $el = $('#woocommerce-product-data');
			}else {
				var $el = $('#product_data_portlet .m-portlet__body');
			}
			
			$el.unblock();
		 },
		 loading: function(){
			 $('#price-matrix-popup').block({
				 message: null,
				 overlayCSS: {
					 background: '#fff',
					 opacity: 0.6
				 }
			 });
		 },
		 /**
		  * Remove jQuery.BlockUI
		  */
		 unloaded: function() {
			 $('#price-matrix-popup').unblock();
		 },
		 log: function(msg) {
			 $('#log').append('<p style="margin: 0;padding: 0;">- ' + msg + '</p>');
		 },
		 option: function(msg) {
			 $('#log-cha span').html( msg );
		 },
	 }
 
	 var live_table_div = '.price-matrix-table td.price';
	 var pm_live_table = {
		 init: function(){
			 $(document).on('click', live_table_div, this.live_selected);
			 $(document).on('dblclick', live_table_div, this.input_data);
		 },
		 live_selected: function(){
			 var $wrap_edit = $(live_table_div).not(this).find('.wrap > div');
			 $wrap_edit.removeClass('entry-editing');
			 $wrap_edit.attr('contenteditable', false);
 
			 $(live_table_div).removeClass('selected');
			 $( this ).addClass('selected');
 
			 var $index = $(this).index();
		 },
		 input_data: function(){
			 var $wrap_edit = $( this ).find('.wrap > div');
			 $wrap_edit.addClass('entry-editing');
			 $wrap_edit.attr('contenteditable', true);
			 $wrap_edit.trigger('focus'); 
		 }
	 }
	 
	 wc_meta_boxes_price_matrix_actions.init();
	 pm_live_table.init();
 
 });