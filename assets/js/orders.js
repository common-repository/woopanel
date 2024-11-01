
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
var WooPanel_Orders = {

    xhr_view: null,

	init: function() {
		/**
		 * Event actions
		 */
		jQuery(document).on( 'click', '.order-preview', this.getOrderPreview );
		jQuery(document).on( 'click', '.m-invoice-edit > a', this.editOrderPopup );
		jQuery(document).on( 'click', '.m-invoice-address-edit > i', this.editAddressPopup );
		jQuery(document).on( 'click', '.m-invoice-shipping-edit > i', this.editShippingPopup );
		jQuery(document).on( 'submit', '.frm_edit_item', this.formEditItem );
		jQuery(document).on( 'submit', '.frm_edit_billing', this.formEditBilling );
		jQuery(document).on( 'submit', '.frm_edit_shipping', this.formEditShipping );
		jQuery(document).on( 'click', '.delete_note', this.deleteNote );
		
		jQuery(document).on( 'click', '.m-messenger__form-send', this.sendOrder );
		if( jQuery().select2 ) {
			this.searchCustomer();
		}
	},
	
	deleteNote: function(e) {
		e.preventDefault();
		
		if ( window.confirm( wpel_orders_params.i18n_delete_note ) ) {
			var note = jQuery( this ).closest( '.m-messenger-note' );

			jQuery( note ).block({
				message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});

			var data = {
				action:   'woocommerce_delete_order_note',
				note_id:  jQuery( note ).attr( 'rel' ),
				security: wpel_orders_params.delete_order_note_nonce
			};

			jQuery.post( WooPanel.ajaxurl, data, function() {
				jQuery( note ).remove();
			});
		}
	},
	
	sendOrder: function() {
		var $order_message = jQuery('.order_message').val();
		var $order_note_type = jQuery('#order_note_type').val();
		
		if( $order_message == null || $order_message.length === 0) {
			alert(wpel_orders_params.i18n_add_note);
			
			return;
		}
		
		var data = {
			action:    'woocommerce_add_order_note',
			post_id:   jQuery('#post_ID').val(),
			note:      $order_message,
			note_type: $order_note_type,
			security:  wpel_orders_params.add_order_note_nonce
		};
		
		WooPanel_BlockUI.block( jQuery('.m-order-notes') );
		jQuery.ajax({
			url:     wpel_orders_params.ajax_url,
			data:    data,
			type:    'POST',
			success: function( response ) {
				var this_page = window.location.toString();
				
				jQuery( '.m-order-notes .m-portlet__body' ).load( this_page + ' .m-order-notes .m-messenger', function() {
					WooPanel_BlockUI.unblock( jQuery('.m-order-notes') );
				});
			}
		});
		
	},
	
	searchCustomer: function(e) {
		jQuery(".select2-tags").select2({
				width: '100%',
				allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
				placeholder: jQuery( this ).data( 'placeholder' ),
				tags: true
		});
		
		jQuery( ':input.select2-customer-ajax' ).each( function() {
			var select2_args = {
				width: '100%',
				allowClear:  jQuery( this ).data( 'allow_clear' ) ? true : false,
				placeholder: jQuery( this ).data( 'placeholder' ),
				minimumInputLength: jQuery( this ).data( 'minimum_input_length' ) ? jQuery( this ).data( 'minimum_input_length' ) : '3',
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         WooPanel.ajaxurl,
					dataType:    'json',
					delay:       1000,
					data:        function( params ) {
						return {
							term         : params.term,
							action       : jQuery( this ).data( 'action' ) || 'woocommerce_json_search_customers',
							security     : wpel_orders_params.search_customers_nonce,
							exclude:  jQuery( this ).data( 'exclude' )
						};
					},
					processResults: function( data ) {
						var terms = [];
						if ( data ) {
							jQuery.each( data, function( id, text ) {
								terms.push({
									id: id,
									text: text
								});
							});
						}
						return {
							results: terms
						};
					},
					cache: true
				}
			};
					
			jQuery(".select2-customer-ajax").select2(select2_args);
			
		});		
	},
	
	changeOrder: function(e) {
		var $item = jQuery(this).closest('.item'),
			$quantity = jQuery(this).val(),
			$price = $item.find('.line_subtotal').val();
			
		var total_price = $price * $quantity;
		$item.find('.line_total').val( total_price );
		
		var $html_price = jQuery('#order_line_items').attr('data-price-format'),
			$number_price = jQuery('#order_line_items').attr('data-price');
			
			
		var $total_price = number_format(total_price, WooPanel.decimals, WooPanel.decimal_separator, WooPanel.thousand_separator);
		var res = $html_price.replace($number_price, $total_price);

		$item.find('.line_cost').html(res);
		
		return false;
		
	},
	
	formEditItem: function(e) {
		e.preventDefault();
		
		var $this = jQuery(this);
		WooPanel_BlockUI.block( $this.find('.modal-content') );
		
		jQuery.ajax({
			url:     wpel_orders_params.ajax_url,
			data:    jQuery(this).serializeArray(),
			type:    'POST',
			success: function( response ) {
				var this_page = window.location.toString();
				
				jQuery( '.m-invoice-1' ).load( this_page + ' .m-invoice__wrapper', function() {
					WooPanel_BlockUI.unblock( $this.find('.modal-content') );
					jQuery('#edit_order_modal').modal('hide');
				});
			}
		});
		
	},

	formEditBilling: function(e) {
		e.preventDefault();

		var $this = jQuery(this);
		WooPanel_BlockUI.block( $this.find('.modal-content') );

		jQuery.ajax({
			url:     wpel_orders_params.ajax_url,
			data:    jQuery(this).serializeArray(),
			type:    'POST',
			success: function( response ) {
				var this_page = window.location.toString();
				
				jQuery( '.m-invoice-1' ).load( this_page + ' .m-invoice__wrapper', function() {
					WooPanel_BlockUI.unblock( $this.find('.modal-content') );
					jQuery('#edit_order_address_modal').modal('hide');
				});
			}
		});
	},
	
	
	formEditShipping: function(e) {
		e.preventDefault();

		var $this = jQuery(this);
		WooPanel_BlockUI.block( $this.find('.modal-content') );

		jQuery.ajax({
			url:     wpel_orders_params.ajax_url,
			data:    jQuery(this).serializeArray(),
			type:    'POST',
			success: function( response ) {
				var this_page = window.location.toString();
				
				jQuery( '.m-invoice-1' ).load( this_page + ' .m-invoice__wrapper', function() {
					WooPanel_BlockUI.unblock( $this.find('.modal-content') );
					jQuery('#edit_order_shipping_modal').modal('hide');
				});
			}
		});
	},
	
	editAddressPopup: function(e) {
		e.preventDefault();
		
		jQuery("#edit_order_address_modal").modal();
		
		jQuery('.wpl-select2').select2({
			width: '100%',
			theme: "default wpl-select2-default",
		});
	},
	
	editShippingPopup: function(e) {
		e.preventDefault();
		
		jQuery("#edit_order_shipping_modal").modal();
		jQuery('.wpl-select2').select2({
			width: '100%',
			theme: "default wpl-select2-default",
		});
	},

	
	editOrderPopup: function(e) {
		e.preventDefault();
		
		jQuery("#edit_order_modal").modal();
	},
	
	getOrderPreview: function(e) {
		e.preventDefault();
		
		var $ = jQuery.noConflict(),
            $previewButton = $(this),
			$order_id      = $previewButton.data( 'order-id' );
		if ( $previewButton.data( 'order-data' ) ) {
			$( this ).WCBackboneModal({
				template: 'wc-modal-view-order',
				variable : $previewButton.data( 'order-data' )
			});
			
			$("#order-preview").modal("show");
		} else {
			$previewButton.find('.icon-preview').hide();
			$previewButton.find('.icon-loader').show();
			$previewButton.addClass( 'disabled' );

            if( this.xhr_view && this.xhr_view.readyState != 4 ){ this.xhr_view.abort(); }
            this.xhr_view = jQuery.ajax({
				url:     wpel_orders_params.ajax_url,
				data:    {
					order_id: $order_id,
					action  : 'woocommerce_get_order_details',
					security: wpel_orders_params.preview_nonce
				},
				type:    'GET',
				success: function( response ) {
					$( '.order-preview' ).removeClass( 'disabled' );
					$previewButton.find('.icon-preview').show();
					$previewButton.find('.icon-loader').hide();
					
					if ( response.success ) {
						
						$previewButton.data( 'order-data', response.data );

						$( this ).WCBackboneModal({
							template: 'wc-modal-view-order',
							variable : response.data
						});
						
						$("#order-preview").modal("show");
					}
				}
			});
		}
	},
	
}

jQuery(document).ready(function($) {
	WooPanel_Orders.init();
	
	$(document).on('hide.bs.modal', '#order-preview', function(){
		$("#wc-backbone-modal-dialog").remove();
		$('body').removeAttr('style');
		
	});
	
	$(document).on('show.bs.modal', '#order-preview', function(){
		$('[data-toggle="tooltip"]').tooltip("hide");
	});
});