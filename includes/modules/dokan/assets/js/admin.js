jQuery( function( $ ) {
	
	var woopanel_dokan_ajax = {
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
	
	$(document).on('click', '.open-close select', function() {
		var status = $(this).val(),
			$time = $(this).closest('.show-group-row').find('.shop-group-col.time');
		
		if( status == 'open' ) {
			$time.css( "visibility", "visible" );
		}else {
			$time.css( "visibility", "hidden" );
		}
	});
	
	$(document).on('change', '#data_country_enable', function(e) {
		if( $(this).is(':checked') ) {
			$('.dokan-postcode').show();
		}else {
			$('.dokan-postcode').hide();
		}
		
	});
	
	$(document).on('click', '[href="#add_shipping_method"]', function(e) {
		e.preventDefault();
		
		var $id = $(this).attr('data-id');
		
		$('#' + $id).show();
		$('#dokan-shipping').hide();
		
	});
	
	$(document).on('click', '[href="#edit_zone"]', function(e) {
		e.preventDefault();
		
		var $id = $(this).attr('data-id');
		
		$('#shipping-' + $id + '-method').show();
		$('#dokan-shipping').hide();
		
	});
	
	$(document).on('click', '[href="#dokan_shipping"]', function() {
		$('#dokan-shipping').show();
		$('.add-shipping-method-wrapper').hide();
	});


	$(document).on('click', '[href="#add-shipping-popup"]', function(e) {
		e.preventDefault();
		
		var $id = $(this).attr('data-id'),
			$methods = JSON.parse( $(this).attr('data-methods') );
		
		$.magnificPopup.open({
			items: {
				src: '#add-shipping-popup'
			},
			type: 'inline',
			midClick: true,
			mainClass: 'mfp-fade',
			callbacks: {
				open: function() {
					$.each( $methods, function( index, value ) {
						$('#shipping_method option[value="' + value + '"]').attr('disabled','disabled');
					});
					
					$('.btn-submit-shipping-add').attr('data-id', $id);
				},
				close: function() {
					$('#shipping_method option').removeAttr('disabled');
				}
			}
		});
		
	});
	
	$(document).on('click', '.btn-submit-shipping-add', function(e) {
		e.preventDefault();
		
		woopanel_dokan_ajax.block( $('#add-shipping-popup') );
		
		var $btn =  $(this),
			$method = $('[name="shipping_method"]').val(),
			zoneID = $btn.attr('data-id');
		
			$.ajax({
				url: WooPanel.ajaxurl,
				data: {
					 action:    'woopanel_add_shipping_method',
					 zoneID: zoneID,
					 method: $method
				},
				type: 'POST',
				datatype: 'json',
				success: function( rs ) {
					if( rs.success ) {
						location.reload();		
					}
				},
				error:function(){
					 alert('There was an error when processing data, please try again !');
				}
			});
	});
	
	$(document).on('change', '#limit_zone', function(e) {
		e.preventDefault();
		
		if( $(this).is(':checked') ) {
			$('.dokan-postcode').show();
		}else {
			$('.dokan-postcode').hide();
		}
		
	});
	
	$(document).on('click', '.repeater-plus', function(e) {
		e.preventDefault();
		
		var $this = $(this),
			$table = $this.closest('.shipping_repeater'),
			$row = $('#tmpl-shipping-repeater').html(),
			$val = $table.find('.shipping-country-repeater').val();
			
		$row = str_replace( '{country}', $val, $row );
		$table.find('tbody').append($row);
		reindex_repeater( $table );
		
	});
	
	$(document).on('click', '.btn-add-location', function(e) {
		e.preventDefault();
		
		var $table = $('#tmpl-shipping-table').html();
		
		$('.woopanel-shipping-location-table').append($table);
	});
	
	$(document).on('click', '.shipping_repeater thead .repeater-minus', function(e) {
		e.preventDefault();
		
		var $this = $(this),
			$table = $this.closest('.shipping_repeater');
			
			$table.remove();
	});
	
	
	$(document).on('click', '.repeater-minus', function(e) {
		e.preventDefault();
		
		var $this = $(this),
			$table = $this.closest('.shipping_repeater');
		
		$this.closest('.pm-row').remove();
		
		reindex_repeater( $table );
		
	});
	
	$(document).on('change', '.shipping-country-repeater', function(e) {
		e.preventDefault();
		
		var $this = $(this),
			$table = $this.closest('.shipping_repeater'),
			$row = $('#tmpl-shipping-repeater').html(),
			$val = $this.val();

		$row = str_replace( '{country}', $val, $row );
		
		$table.find('tbody').empty();
		$table.find('tbody').append($row);
		reindex_repeater( $table );
	});
	
	$(document).on('click', '.shipping-method-delete', function(e) {
		e.preventDefault();
		
		woopanel_dokan_ajax.block( $('.zone-wrapper') );
		
		var $btn =  $(this),
			zoneID = $btn.attr('data-zone'),
			instanceID = $btn.attr('data-instance');
		
			$.ajax({
				url: WooPanel.ajaxurl,
				data: {
					 action:    'woopanel_delete_shipping_method',
					 zoneID: zoneID,
					 instance_id: instanceID
				},
				type: 'POST',
				datatype: 'json',
				success: function( rs ) {
					if( rs.success ) {
						location.reload();		
					}
				},
				error:function(){
					 alert('There was an error when processing data, please try again !');
				}
			});
	});
	
	$(document).on('click', '.shipping-method-edit', function(e) {
		e.preventDefault();
		
		var $btn =  $(this),
			zoneID = $btn.attr('data-zone'),
			instanceID = $btn.attr('data-instance'),
			$label = $btn.attr('data-title'),
			$method = $btn.attr('data-method');
			
		$.ajax({
			url: WooPanel.ajaxurl,
			data: {
				 action:    'woopanel_load_shipping_method',
				 zoneID: zoneID,
				 instance_id: instanceID
			},
			type: 'POST',
			datatype: 'json',
			success: function( rs ) {
				if( rs.success ) {
					$.each( rs.data, function( index, value ) {
						$('#method_' + index).val(value );
					});
					woopanel_dokan_ajax.unblock( $('#edit-shipping-popup') );
				}
			},
			error:function(){
				 alert('There was an error when processing data, please try again !');
			}
		});
		
		$.magnificPopup.open({
			items: {
				src: '#edit-shipping-popup'
			},
			type: 'inline',
			midClick: true,
			mainClass: 'mfp-fade',
			callbacks: {
				open: function() {
					$('#edit-shipping-popup #instance_id').val(instanceID);
					$('#edit-shipping-popup #method_id').val($method);
					$('#edit-shipping-popup #zoneID').val(zoneID);
					$('#method_title').val($label);
					
					woopanel_dokan_ajax.block( $('#edit-shipping-popup') );
				},
				close: function() {
					$('.blockUI').remove();
					//$('#shipping_method option').removeAttr('disabled');
				}
			}
		});	

			

	});
	
	$(document).on('submit', '#edit-shipping-form', function(e) {
		e.preventDefault();

		woopanel_dokan_ajax.block( $('#edit-shipping-popup') );
		
		var $form =  $(this);
		
		$.ajax({
			url: WooPanel.ajaxurl,
			data: {
				 action:    'woopanel_edit_shipping_method',
				 zoneID: $form.find('#zoneID').val(),
				 data: $form.serialize()
			},
			type: 'POST',
			datatype: 'json',
			success: function( rs ) {
				if( rs.success ) {
					location.reload();		
				}
			},
			error:function(){
				 alert('There was an error when processing data, please try again !');
			}
		});
	});
	
	
	function reindex_repeater( $table ) {
		$table.find( "tbody > tr" ).each(function( index ) {
			$(this).find('.pm-row-zero span').text( (index + 1) );
		});
	}
	
	function str_replace( search, replace, subject ) {
		return subject.split(search).join(replace);
	}
		
});