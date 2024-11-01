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
	},

	alert: function( msg ) {
		toastr.options = {
			"closeButton": true,
			"debug": false,
			"newestOnTop": true,
			"progressBar": false,
			"positionClass": "toast-top-right",
			"preventDuplicates": false,
			"onclick": null,
			"showDuration": "300",
			"hideDuration": "1000",
			"timeOut": "5000",
			"extendedTimeOut": "1000",
			"showEasing": "swing",
			"hideEasing": "linear",
			"showMethod": "fadeIn",
			"hideMethod": "fadeOut",
			"tapToDismiss": false
		};
		  
		toastr.error(msg);

		WooPanel_BlockUI.unblock( $('#product_data_portlet') );
	},

	info: function( msg ) {
		toastr.options = {
			"closeButton": true,
			"debug": false,
			"newestOnTop": true,
			"progressBar": false,
			"positionClass": "toast-top-right",
			"preventDuplicates": false,
			"onclick": null,
			"showDuration": "300",
			"hideDuration": "1000",
			"timeOut": "5000",
			"extendedTimeOut": "1000",
			"showEasing": "swing",
			"hideEasing": "linear",
			"showMethod": "fadeIn",
			"hideMethod": "fadeOut",
			"tapToDismiss": false
		};
		  
		toastr.info(msg);

		WooPanel_BlockUI.unblock( $('#product_data_portlet') );
	}
}

var WooPanel_Product_Metaboxes_Variation = {
	/**
	 * Initialize variations ajax methods
	 */
	init: function() {
		$( document.body )
		.on( 'change', '#variable_product_options .woocommerce_variations :input', this.input_changed )
		.on( 'change', '.variations-defaults select', this.defaults_changed );

		$( '#variable_product_options' )
			.on( 'click', 'button.save-variation-changes', this.save_variations )
			.on( 'click', 'a.remove_variation', this.remove_variation );

		$(document).on('click', '.sale_schedule_variation', this.show_hide_sale_schedule );
		$( '#variable_product_options' )
			.on( 'change', 'input.variable_is_downloadable', this.variable_is_downloadable )
			.on( 'change', 'input.variable_is_virtual', this.variable_is_virtual )
			.on( 'change', 'input.variable_manage_stock', this.variable_manage_stock );

		$( '#variable_product_options' ).on( 'click', 'a.do_variation_action', this.do_variation_action );

		 
		$( document.body ).on( 'woopanel_variations_added', this.variation_added )
						.on( 'change', '.variations-pagenav .page-selector', woopanel_product_variations_pagenav.page_selector )
						.on( 'click', '.variations-pagenav .prev-page', woopanel_product_variations_pagenav.prev_page )
						.on( 'click', '.variations-pagenav .next-page', woopanel_product_variations_pagenav.next_page );
						
		$(document).on('click', '.attribute_options .m-nav__link', function() {
			$('.attribute_variation.show_if_variable').css( "display", "flex" );
		});
	},

	/**
	 * Run actions when added a variation
	 *
	 * @param {Object} event
	 * @param {Int} qty
	 */
	variation_added: function( event, qty ) {
		if ( 1 === qty ) {
			WooPanel_Product_Metaboxes.variations_loaded( null, true );
		}

		WooPanel_Product_Metaboxes_Variation.update_single_quantity(qty);
	},

	/**
	 * Update variations quantity when add a new variation
	 *
	 * @param {Object} event
	 * @param {Int} qty
	 */
	update_single_quantity: function( qty ) {
		if ( 1 === qty ) {
			var page_nav = $( '.variations-pagenav' );
			woopanel_product_variations_pagenav.update_variations_count( qty );

			if ( page_nav.is( ':hidden' ) ) {
				$( 'option, optgroup', '.variation_actions' ).show();
				$( '.variation_actions' ).val( 'add_variation' );
				$( '#variable_product_options' ).find( '.toolbar' ).show();
				page_nav.show();
				$( '.pagination-links', page_nav ).hide();
				$('.m-toolbar').show();
			}
		}
	},

	/**
	 * Actions
	 */
	do_variation_action: function() {
		var do_variation_action = $( 'select.variation_actions' ).val(),
			data       = {},
			changes    = 0,
			value;

		switch ( do_variation_action ) {
			case 'add_variation' :
				WooPanel_Product_Metaboxes_Variation.add_variation();
				return;
			case 'link_all_variations' :
				WooPanel_Product_Metaboxes_Variation.link_all_variations();
				return;
			case 'delete_all' :
				if ( window.confirm( WooPanel.product.i18n_delete_all_variations ) ) {
					if ( window.confirm( WooPanel.product.i18n_last_warning ) ) {
						data.allowed = true;
						changes      = parseInt( $( '#variable_product_options' ).find( '.woocommerce_variations' ).attr( 'data-total' ), 10 ) * -1;
					}
				}
				break;
		}

		if ( 'delete_all' === do_variation_action && data.allowed ) {
			$( '#variable_product_options' ).find( '.variation-needs-update' ).removeClass( 'variation-needs-update' );
		} else {
			WooPanel_Product_Metaboxes.check_for_changes();
		}

		WooPanel_BlockUI.block( $('#product_data_portlet') );

		$.ajax({
			url: WooPanel.ajaxurl,
			data: {
				action:       'woopanel_bulk_edit_variations',
				security:     WooPanel.product.bulk_edit_variations_nonce,
				product_id:   $("#post_ID").val(),
				product_type: $( '#m-dropdown-product_type .m-nav__item--active' ).attr('data-value'),
				bulk_action:  do_variation_action,
				data:         data
			},
			type: 'POST',
			success: function() {
				woopanel_product_variations_pagenav.go_to_page( 1, changes );
			}
		});
	},

	/**
	 * Remove variation
	 *
	 * @return {Bool}
	 */
	remove_variation: function() {
		WooPanel_Product_Metaboxes.check_for_changes();

		if ( window.confirm( WooPanel.product.i18n_remove_variation ) ) {
			var variation     = $( this ).attr( 'rel' ),
				variation_ids = [],
				data          = {
					action: 'woopanel_remove_variations'
				};

				WooPanel_BlockUI.block( $('#product_data_portlet') );

			if ( 0 < variation ) {
				variation_ids.push( variation );

				data.variation_ids = variation_ids;
				data.security      = WooPanel.product.delete_variations_nonce;

				$.post( WooPanel.ajaxurl, data, function() {
					var wrapper      = $( '#variable_product_options' ).find( '.woocommerce_variations' ),
						current_page = parseInt( wrapper.attr( 'data-page' ), 10 ),
						total_pages  = Math.ceil( ( parseInt( wrapper.attr( 'data-total' ), 10 ) - 1 ) / WooPanel.product.variations_per_page ),
						page         = 1;

					if ( current_page === total_pages || current_page <= total_pages ) {
						page = current_page;
					} else if ( current_page > total_pages && 0 !== total_pages ) {
						page = total_pages;
					}

					woopanel_product_variations_pagenav.go_to_page( page, -1 );
				});

			} else {
				WooPanel_BlockUI.unblock( $('#product_data_portlet') );
			}
		}

		return false;
	},

	/**
	 * Check if variation is downloadable and show/hide elements
	 */
	variable_is_downloadable: function() {
		$( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_downloadable' ).hide();

		if ( $( this ).is( ':checked' ) ) {
			$( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_downloadable' ).show();
		}
	},

	/**
	 * Check if variation is virtual and show/hide elements
	 */
	variable_is_virtual: function() {
		$( this ).closest( '.woocommerce_variation' ).find( '.hide_if_variation_virtual' ).show();

		if ( $( this ).is( ':checked' ) ) {
			$( this ).closest( '.woocommerce_variation' ).find( '.hide_if_variation_virtual' ).hide();
		}
	},

	/**
	 * Check if variation manage stock and show/hide elements
	 */
	variable_manage_stock: function() {
		$( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_manage_stock' ).hide();
		$( this ).closest( '.woocommerce_variation' ).find( '.hide_if_variation_manage_stock' ).show();

		if ( $( this ).is( ':checked' ) ) {
			$( this ).closest( '.woocommerce_variation' ).find( '.show_if_variation_manage_stock' ).show();
			$( this ).closest( '.woocommerce_variation' ).find( '.hide_if_variation_manage_stock' ).hide();
		}
	},

	/**
	 * Add new class when have changes in some input
	 */
	input_changed: function() {
		$( this )
			.closest( '.woocommerce_variation' )
			.addClass( 'variation-needs-update' );

		$( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );

		//$( '#variable_product_options' ).trigger( 'woocommerce_variations_input_changed' );
	},

	/**
	 * Added new .variation-needs-update class when defaults is changed
	 */
	defaults_changed: function() {
		$( this )
			.closest( '#variable_product_options' )
			.find( '.woocommerce_variation:first' )
			.addClass( 'variation-needs-update' );

		$( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );

		//$( '#variable_product_options' ).trigger( 'woocommerce_variations_defaults_changed' );
	},

	/**
	 * Ger variations fields and convert to object
	 *
	 * @param  {Object} fields
	 *
	 * @return {Object}
	 */
	get_variations_fields: function( fields ) {
		var data = $( ':input', fields ).serializeJSON();

		$( '.variations-defaults select' ).each( function( index, element ) {
			var select = $( element );
			data[ select.attr( 'name' ) ] = select.val();
		});

		return data;
	},

	/**
	 * Save variations changes
	 *
	 * @param {Function} callback Called once saving is complete
	 */
	save_changes: function( callback ) {
		var wrapper     = $( '#variable_product_options' ).find( '.woocommerce_variations' ),
		   need_update = $( '.variation-needs-update', wrapper ),
		   data        = {};

	   // Save only with products need update.
		if ( 0 < need_update.length ) {
			WooPanel_BlockUI.block( $('#product_data_portlet') );
			data                 = WooPanel_Product_Metaboxes_Variation.get_variations_fields( need_update );
			data.action          = 'woopanel_save_variations';
			data.security        = WooPanel.product.save_variations_nonce;
			data.product_id      = $("#post_ID").val();
			data['product-type'] = $( '#m-dropdown-product_type .m-nav__item--active' ).attr('data-value');

			$.ajax({
				url: WooPanel.ajaxurl,
				data: data,
				type: 'POST',
				success: function( response ) {
					// Allow change page, delete and add new variations
					need_update.removeClass( 'variation-needs-update' );
					$( 'button.cancel-variation-changes, button.save-variation-changes' ).attr( 'disabled', 'disabled' );

					$( '#woocommerce-product-data' ).trigger( 'woocommerce_variations_saved' );

					if ( typeof callback === 'function' ) {
						callback( response );
					}

					

					WooPanel_BlockUI.unblock( $('#product_data_portlet') );
				}
			});
		}
   },

   /**
	* Save variations
	*
	* @return {Bool}
	*/
	save_variations: function(e) {
		e.preventDefault();

		WooPanel_Product_Metaboxes_Variation.save_changes( function( error ) {
			console.log('save_variations');
		});
	},

	/**
	 * Add variation
	 *
	 * @return {Bool}
	 */
	add_variation: function() {
		WooPanel_BlockUI.block($( '#variable_product_options' ));

		var data = {
			action: 'woopanel_add_variation',
			post_id: $("#post_ID").val(),
			loop: $( '.woocommerce_variation' ).length,
			security: WooPanel.product.add_variation_nonce
		};

		$.post( WooPanel.ajaxurl, data, function( response ) {
			var variation = $( response );
			variation.addClass( 'variation-needs-update' );

			$( '#variable_product_options' ).find( '.woocommerce_variations' ).prepend( variation );
			$( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );
			$( '#variable_product_options' ).trigger( 'woopanel_variations_added', 1 );
			WooPanel_BlockUI.unblock($( '#variable_product_options' ));
		});

		return false;
	},

	/**
	 * Link all variations (or at least try :p)
	 *
	 * @return {Bool}
	 */
	link_all_variations: function() {
		WooPanel_Product_Metaboxes.check_for_changes();

		if ( window.confirm( WooPanel.product.i18n_link_all_variations ) ) {
			WooPanel_BlockUI.block( $('#product_data_portlet') );

			$.ajax({
				url: WooPanel.ajaxurl,
				data: {
					action: 'woopanel_link_all_variations',
					post_id: $("#post_ID").val(),
					security: WooPanel.product.link_variation_nonce
				},
				type: 'POST',
				success: function( response ) {
					var count = parseInt( response, 10 );

					if ( 1 === count ) {
						WooPanel_BlockUI.info( count + ' ' + WooPanel.product.i18n_variation_added );
					} else if ( 0 === count || count > 1 ) {
						WooPanel_BlockUI.info( count + ' ' + WooPanel.product.i18n_variations_added );
					} else {
						WooPanel_BlockUI.info( WooPanel.product.i18n_no_variations_added );
					}
	
					if ( count > 0 ) {
						woopanel_product_variations_pagenav.go_to_page( 1, count );
						$( '#variable_product_options' ).trigger( 'woocommerce_variations_added', count );
					} else {
						WooPanel_BlockUI.unblock( $('#product_data_portlet') );
					}
				},
				error:function( xhr, status, error ) {
					WooPanel_BlockUI.unblock( $('#product_data_portlet') );

					if( xhr.status == 403) {
						alert( WooPanel.label.i18n_deny);
					}else {
						alert('There was an error when processing data, please try again !');
					}
				}
			});
		}

		return false;
	},

	show_hide_sale_schedule: function(e) {
		e.preventDefault();

		var $label_cancel = $(this).attr('data-label-cancel');
		var $text_cancel = $(this).attr('data-label-text');

		if( $(this).hasClass('active') ) {
			$('.sale_schedule_variation').removeClass('active');
			$('.woocommerce_variation .sale_price_dates_fields').css('display', 'none');
			$(this).text($text_cancel);
		}else {
			$('.sale_schedule_variation').addClass('active');
			$('.woocommerce_variation .sale_price_dates_fields').css('display', 'flex');
			$(this).text($label_cancel);
		}
	},

	datepicker: function() {
		if( jQuery().datepicker ) {
			var arrows;
			arrows = {
				leftArrow: '<i class="la la-angle-left"></i>',
				rightArrow: '<i class="la la-angle-right"></i>'
			}
		
			$('.m-datepicker').datepicker({
				todayHighlight: true,
				templates: arrows,
				format: 'yyyy-mm-dd'
			});
			
			$('.m-datepicker').on('changeDate', function(ev){
				$(this).datepicker('hide');
			});
		}
	}
}

var WooPanel_Product_Metaboxes = {
	init: function() {
        this.show_and_hide_panels();

        $(document).on('click', '#m-dropdown-product_type .m-nav__item', this.change_product_type );
		$(document).on('change', 'input#_manage_stock', this._manage_stock );
		$(document).on('click', 'a[href="#variable_product_options"]', this.initial_load );
		$(document).on('click', '.sale_schedule', this.show_hide_sale_schedule );


		$(document).on('click', '.btn-save-attribute', this.save_attributes );
		$(document).on('reload', '#variable_product_options', this.reload );
		$(document).on('click', '.btn-add-attribute', this.add_attribute );
		$(document).on('click', '#product_attributes .remove_row', this.remove_attribute );
		
		
		$(document).on('click', '#variable_product_options .woocommerce_variation .m-accordion__item-head', this.show_hide_variations );


		
		$( '#product_data_portlet' ).on( 'woopanel_variations_loaded', this.variations_loaded );
		$( 'input#_downloadable, input#_virtual' ).change( function() {
			WooPanel_Product_Metaboxes.show_and_hide_panels();
		});
		this.call_select2();
		this.loaded();

	},

	/**
	 * Initial load variations
	 *
	 * @return {Bool}
	 */
	initial_load: function() {
		if ( 0 === $( '#variable_product_options' ).find( '.woocommerce_variations .woocommerce_variation' ).length ) {
			woopanel_product_variations_pagenav.go_to_page();
		}
	},

	show_hide_variations: function(e) {
		e.preventDefault();

		var $this = $(this),
			$id = $(this).attr('data-id');

		if( $(e.target).hasClass('col-3') || $(e.target).hasClass('row') || $(e.target).hasClass('m-accordion__item-mode') || $(e.target).hasClass('m-accordion__item-head') ) {
			var $body = $('#' + $id);

			if( $this.hasClass('collapsed') ) {
				$this.removeClass('collapsed');
				$body.addClass('show');
			}else {
				$this.addClass('collapsed');
				$body.removeClass('show');
			}
		}
	},

	loaded: function() {
		$('input#_manage_stock').trigger('change');
		$( '.product_attributes .woocommerce_attribute' ).each( function( index, el ) {
			if ( $( el ).css( 'display' ) !== 'none' && $( el ).is( '.taxonomy' ) ) {
				$( 'select.attribute_taxonomy' ).find( 'option[value="' + $( el ).data( 'taxonomy' ) + '"]' ).attr( 'disabled', 'disabled' );
			}
		});

		$( '.product_attributes' ).on( 'blur', 'input.attribute_name', function() {
			$( this ).closest( '.woocommerce_attribute' ).find( '.m-accordion__item-title' ).text( $( this ).val() );
		});

		// Attribute ordering.
		$( '.product_attributes' ).sortable({
			items: '.woocommerce_attribute',
			cursor: 'move',
			axis: 'y',
			handle: '.m-accordion__item-head',
			scrollSensitivity: 40,
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
			placeholder: 'woopanel-sortable-placeholder',
			start: function( event, ui ) {
				ui.item.css( 'background-color', '#f6f6f6' );
			},
			stop: function( event, ui ) {
				ui.item.removeAttr( 'style' );
				WooPanel_Product_Metaboxes.attribute_row_indexes();
			}
		});
	},

	attribute_row_indexes: function () {
		$( '.product_attributes .woocommerce_attribute' ).each( function( index, el ) {
			$( '.attribute_position', el ).val( parseInt( $( el ).index( '.product_attributes .woocommerce_attribute' ), 10 ) );
		});
	},
	
	reload: function() {
		WooPanel_BlockUI.unblock( $( '#product_data_portlet' ) );
		WooPanel_Product_Metaboxes.load_variations( 1 );
	},

	add_attribute: function(e) {
		e.preventDefault();

		WooPanel_BlockUI.block($( '#product_data_portlet' ));

		var size         = $( '.product_attributes .woocommerce_attribute' ).length;
		var attribute    = $( 'select.attribute_taxonomy' ).val();
		var $wrapper     = $( this ).closest( '#product_attributes' );
		var $attributes  = $wrapper.find( '.product_attributes' );
		var product_type = $( 'select#product-type' ).val();
		var data         = {
			post_id: $("#post_ID").val(),
			action:   'woopanel_add_attribute',
			taxonomy: attribute,
			i:        size,
			type: $('#input_product_type').val(),
			security: WooPanel.product.add_attribute_nonce
		};

		$.ajax({
			url: WooPanel.ajaxurl,
			data: data,
			type: 'POST',
			success: function( response ) {
				$attributes.append( response );
				WooPanel_Product_Metaboxes.call_select2();
				$attributes.find( '.woocommerce_attribute' ).last().find( '.m-accordion__item-head' ).click();
				WooPanel_Product_Metaboxes.attribute_row_indexes();
				WooPanel_BlockUI.unblock($( '#product_data_portlet' ));
			},
			error:function( xhr, status, error ) {
				WooPanel_BlockUI.unblock($( '#product_data_portlet' ));

				if( xhr.status == 403) {
					alert( WooPanel.label.i18n_deny);
				}else {
					alert('There was an error when processing data, please try again !');
				}
			}
		});

		if ( attribute ) {
			$( 'select.attribute_taxonomy' ).find( 'option[value="' + attribute + '"]' ).attr( 'disabled','disabled' );
			$( 'select.attribute_taxonomy' ).val( '' );
		}
		
/* 		if( type != 'variable') {
			$('.attribute_variation.show_if_variable').hide();
		}else {
			$('.attribute_variation.show_if_variable').show();
		} */
	},

	save_attributes: function(e) {
		e.preventDefault();

		WooPanel_BlockUI.block( $( '#product_data_portlet' ) );

		var original_data = $( '.product_attributes' ).find( 'input:not(.m-exclude-input), select, textarea' );

		
		if( $('#post_ID').length <= 0 ) {
			var data = {
				post_id     : $("#post_ID").val(),
				product_type: $( '#m-dropdown-product_type .m-nav__item--active' ).attr('data-value'),
				data        : $("#post").serialize(),
				action      : 'woopanel_save_attributes',
				security    : WooPanel.product.save_attributes_nonce
			};
		}else {
			var data = {
				post_id     : $("#post_ID").val(),
				product_type: $( '#m-dropdown-product_type .m-nav__item--active' ).attr('data-value'),
				data        : original_data.serialize(),
				action      : 'woopanel_save_attributes',
				security    : WooPanel.product.save_attributes_nonce
			};
		}
		
		$.ajax({
			url: WooPanel.ajaxurl,
			data: data,
			type: 'POST',
			success: function( response ) {
				WooPanel_BlockUI.unblock( $( '#product_data_portlet' ) );

				if ( response.error ) {
					// Error.
					WooPanel_BlockUI.alert( response.error );
	
				} else if ( response.data ) {
					$( '.product_attributes' ).html( response.data.html );
					
					// Reload variations panel.
					
					if( WooPanel.product.post_id == '' ) {
						if( $('#post_ID').length <= 0 ) {
							$('#post').append('<input type="hidden" id="post_ID" name="post_ID" value="' + response.data.post_id + '" />');
						}
						
						var this_page = window.location.toString() + '?id=' + response.data.post_id;
						$('.btn-save-attribute').text(WooPanel.product.i18n_save_attribute);
						$('#publish').text(WooPanel.product.i18n_update);
						$('.m-portlet__head-tools .btn-accent > span span').text(WooPanel.product.i18n_update);
						$('#hiddenaction').val('edit');
					}else {
						var this_page = window.location.toString();
					}
	
					 $( '#variable_product_options' ).load( this_page + ' #variable_product_options_inner', function() {
						$( '#variable_product_options' ).trigger( 'reload' );
					});
				}
			},
			error:function( xhr, status, error ) {
				WooPanel_BlockUI.unblock( $( '#product_data_portlet' ) );

				if( xhr.status == 403) {
					alert( WooPanel.label.i18n_deny);
				}else {
					alert('There was an error when processing data, please try again !');
				}
			}
		});
	},

	remove_attribute: function(e) {
		e.preventDefault();

		if ( window.confirm( WooPanel.product.label_remove_attribute ) ) {
			var $parent = $( this ).parent().parent();

			if ( $parent.is( '.woocommerce_attribute' ) ) {
				$parent.find( 'select, input[type=text]' ).val( '' );
				$parent.remove();
				$( 'select.attribute_taxonomy' ).find( 'option[value="' + $parent.data( 'taxonomy' ) + '"]' ).removeAttr( 'disabled' );
			} else {
				$parent.find( 'select, input[type=text]' ).val( '' );
				$parent.remove();
			}
		}
	},

	show_hide_sale_schedule: function(e) {
		e.preventDefault();

		var $label_cancel = $(this).attr('data-label-cancel');
		var $text_cancel = $(this).attr('data-label-text');

		if( $(this).hasClass('active') ) {
			$('.sale_schedule').removeClass('active');
			$('.sale_price_dates_fields').slideUp();
			$(this).text($text_cancel);
		}else {
			$('.sale_schedule').addClass('active');
			$('.sale_price_dates_fields').slideDown();
			$(this).text($label_cancel);
		}
	},

	load_variations: function() {
		if( $('#variable_product_options .m-accordion__item').length <= 0 && $( '#variable_product_options' ).find( '.woocommerce_variations').attr('data-total') > 0 ) {
			WooPanel_Product_Metaboxes.woopanel_load_variations();
		}

		WooPanel_Product_Metaboxes.call_select2();
	},
	
	woopanel_load_variations: function(page, per_page) {
		WooPanel_BlockUI.block( $('#product_data_portlet') );
		page     = page || 1;
		per_page = per_page || WooPanel.product.variations_per_page;

		var wrapper = $( '#variable_product_options' ).find( '.woocommerce_variations' );

		$.ajax({
			url: WooPanel.ajaxurl,
			data: {
				action:     'woopanel_load_variations',
				security:   WooPanel.product.load_variations_nonce,
				product_id: $("#post_ID").val(),
				attributes: wrapper.data( 'attributes' ),
				page:       page,
				per_page:   per_page
			},
			type: 'POST',
			success: function( response ) {
				wrapper.empty().append( response ).attr( 'data-page', page );

				$( '#product_data_portlet' ).trigger( 'woopanel_variations_loaded' );
				
				WooPanel_BlockUI.unblock( $('#product_data_portlet') );
			},
			error:function( xhr, status, error ) {
				WooPanel_BlockUI.unblock( $('#product_data_portlet') );

				if( xhr.status == 403) {
					alert( WooPanel.label.i18n_deny);
				}else {
					alert('There was an error when processing data, please try again !');
				}
			}
		});
	},

	/**
	 * Run actions when variations is loaded
	 *
	 * @param {Object} event
	 * @param {Int} needsUpdate
	 */
	variations_loaded: function( event, needsUpdate ) {
		needsUpdate = needsUpdate || false;

		var wrapper = $( '#product_data_portlet' );

		if ( ! needsUpdate ) {
			$.nbMediaUploader({
                target : '.variation-thumbnail .media-uploader',
				action : 'get_image',
				uploaderTitle : 'Image',
				btnSelect : 'Set Image',
				btnSetText : 'Set Image',
				multiple : false,
			}, function(selector) {
				selector.closest( '.woocommerce_variation' ).addClass( 'variation-needs-update' );
				$( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );
			});

			WooPanel_Product_Metaboxes_Variation.datepicker();

			$( 'input.variable_is_downloadable, input.variable_is_virtual, input.variable_manage_stock' ).change();

			$( '.woocommerce_variations .variation-needs-update', wrapper ).removeClass( 'variation-needs-update' );
			$( 'button.cancel-variation-changes, button.save-variation-changes', wrapper ).attr( 'disabled', 'disabled' );
		}
	},
	
	call_select2: function() {
		$(".select2-tags").select2({
				width: '100%',
				allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
				placeholder: $( this ).data( 'placeholder' ),
				tags: true
		});
		
		$( ':input.select2-tags-ajax' ).each( function() {
			var select2_args = {
				width: '100%',
				allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
				placeholder: $( this ).data( 'placeholder' ),
				minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         WooPanel.ajaxurl,
					dataType:    'json',
					delay:       250,
					data:        function( params ) {
						return {
							term         : params.term,
							action       : $( this ).data( 'action' ) || 'woocommerce_json_search_products_and_variations',
							security     : WooPanel.product.search_products_nonce,
							exclude      : $( this ).data( 'exclude' ),
							include      : $( this ).data( 'include' ),
							limit        : $( this ).data( 'limit' ),
							display_stock: $( this ).data( 'display_stock' )
						};
					},
					processResults: function( data ) {
						var terms = [];
						if ( data ) {
							$.each( data, function( id, text ) {
								terms.push( { id: id, text: text } );
							});
						}
						return {
							results: terms
						};
					},
					cache: true
				}
			};
					
			$(".select2-tags-ajax").select2(select2_args);
			
		});
	},
    
    change_product_type: function(e) {
        e.preventDefault();
		$(this).addClass('m-nav__item--active');
		var $text = $(this).find('.m-nav__link-text').text();
		$('#m-dropdown-product_type li').not($(this)).removeClass('m-nav__item--active');

		$('.m-product_type > a').text($text);
		$('.m-product_type').removeClass('m-dropdown--open');

		$('#input_product_type').val( $(this).attr('data-value') );

		$('.m-tabs-content').attr('data-panel', $(this).attr('data-value') );
		WooPanel_Product_Metaboxes.show_and_hide_panels();

		if( $(this).attr('data-value') == 'variable') {
			$('.attribute_variation.show_if_variable').css( "display", "flex" );
		}else {
			$('.attribute_variation.show_if_variable').removeAttr('style');
		}


    },

    show_and_hide_panels: function() {
		var product_type    = $( '#m-dropdown-product_type .m-nav__item--active' ).attr('data-value');
		var is_virtual      = $( 'input#_virtual:checked' ).length;
		var is_downloadable = $( 'input#_downloadable:checked' ).length;

		// Hide/Show all with rules.
		var hide_classes = '.hide_if_downloadable, .hide_if_virtual';
        var show_classes = '.show_if_downloadable, .show_if_virtual';


		$.each( WooPanel.product.product_types, function( index, value ) {
			hide_classes = hide_classes + ', .hide_if_' + value;
			show_classes = show_classes + ', .show_if_' + value;
		});

		$( hide_classes ).show();
		$( show_classes ).hide();

		// Shows rules.
		if ( is_downloadable ) {
			$( '.show_if_downloadable' ).show();
		}
		if ( is_virtual ) {
			$( '.show_if_virtual' ).show();
		}

        $( '.show_if_' + product_type ).show();

		// Hide rules.
		if ( is_downloadable ) {
			$( '.hide_if_downloadable' ).hide();
		}
		if ( is_virtual ) {
			$( '.hide_if_virtual' ).hide();
        }
        


		$( '.hide_if_' + product_type ).hide();

		$( 'input#_manage_stock' ).change();

		// Hide empty panels/tabs after display.
		$( '.m-tabs-content__item' ).each( function() {
			var $children = $( this ).children( '.options_group' );

			if ( 0 === $children.length ) {
				return;
			}

			var $invisble = $children.filter( function() {
				return 'none' === $( this ).css( 'display' );
            });
            
            

			// Hide panel.
			if ( $invisble.length === $children.length ) {
                var $id = $( this ).prop( 'id' );
				$( '#product_data_tabs' ).find( 'li a[href="#' + $id + '"]' ).parent().hide();
            }
			$('#product_data_portlet li a').removeClass('m-tabs__item--active');
			$('#product_data_portlet .m-tabs-content__item').removeClass('m-tabs-content__item--active');
            var $tab_first = $('#product_data_tabs > li:visible a').first(),
                $tab_id = $tab_first.attr('href');
            $tab_first.addClass('m-tabs__item--active');

            $('#product_data_portlet ' + $tab_id).addClass('m-tabs-content__item--active');
            

		});
    },

    _manage_stock: function() {
		if ( $( this ).is( ':checked' ) ) {
			$( 'div.stock_fields' ).show();
			$( 'p.stock_status_field' ).hide();
		} else {
			var product_type = $( 'select#product-type' ).val();

			$( 'div.stock_fields' ).hide();
			$( 'p.stock_status_field:not( .hide_if_' + product_type + ' )' ).show();
		}
	},
	
	/**
	 * Check if have some changes before leave the page
	 *
	 * @return {Bool}
	 */
	check_for_changes: function() {
		var need_update = $( '#variable_product_options' ).find( '.woocommerce_variations .variation-needs-update' );

		if ( 0 < need_update.length ) {
			if ( window.confirm( WooPanel.product.i18n_edited_variations ) ) {
				WooPanel_Product_Metaboxes_Variation.save_changes();
			} else {
				need_update.removeClass( 'variation-needs-update' );
				return false;
			}
		}

		return true;
	},
}


var woopanel_product_variations_pagenav = {
	/**
	 * Set the pagenav fields
	 *
	 * @param {Int} qty
	 */
	set_paginav: function( qty ) {
		var wrapper          = $( '#variable_product_options' ).find( '.woocommerce_variations' ),
		new_qty          = woopanel_product_variations_pagenav.update_variations_count( qty ),
		toolbar          = $( '#variable_product_options' ).find( '.m-toolbar' ),
		variation_action = $( '.variation_actions' ),
		page_nav         = $( '.variations-pagenav' ),
		displaying_links = $( '.pagination-links', page_nav ),
		total_pages      = Math.ceil( new_qty / WooPanel.product.variations_per_page ),
		options          = '';

		// Set the new total of pages
		wrapper.attr( 'data-total_pages', total_pages );

		$( '.total-pages', page_nav ).text( total_pages );

		// Set the new pagenav options
		for ( var i = 1; i <= total_pages; i++ ) {
			options += '<option value="' + i + '">' + i + '</option>';
		}

		$( '.page-selector', page_nav ).empty().html( options );

		// Show/hide pagenav
		if ( 0 === new_qty ) {

 			toolbar.not( '.toolbar-top, .toolbar-buttons' ).hide();
			page_nav.hide();
			$( 'option, optgroup', variation_action ).hide();
			$( '.variation_actions' ).val( 'add_variation' );
			$( 'option[data-global="true"]', variation_action ).show();

		} else {
			toolbar.show();
			page_nav.show();
 			$( 'option, optgroup', variation_action ).show();
			$( '.variation_actions' ).val( 'add_variation' );

			// Show/hide links
			if ( 1 === total_pages ) {
				displaying_links.hide();
			} else {
				displaying_links.show();
			}
		}
	},
	/**
	 * Set page
	 */
	set_page: function( page ) {
		$( '.variations-pagenav .page-selector' ).val( page ).first().change();
	},

	/**
	 * Navigate on variations pages
	 *
	 * @param {Int} page
	 * @param {Int} qty
	 */
	go_to_page: function( page, qty ) {
		page = page || 1;
		qty  = qty || 0;

		woopanel_product_variations_pagenav.set_paginav( qty );
		woopanel_product_variations_pagenav.set_page( page );
		
		WooPanel_Product_Metaboxes.woopanel_load_variations();
	},

	/**
	 * Go to previous page
	 *
	 * @return {Bool}
	 */
	prev_page: function() {
		if ( woopanel_product_variations_pagenav.check_is_enabled( this ) ) {
			var wrapper   = $( '#variable_product_options' ).find( '.woocommerce_variations' ),
				prev_page = parseInt( wrapper.attr( 'data-page' ), 10 ) - 1,
				new_page  = ( 0 < prev_page ) ? prev_page : 1;

			woopanel_product_variations_pagenav.set_page( new_page );
		}else {
			alert(WooPanel.product.i18n_first_page);
		}

		return false;
	},

	/**
	 * Go to next page
	 *
	 * @return {Bool}
	 */
	next_page: function() {
		if ( woopanel_product_variations_pagenav.check_is_enabled( this ) ) {
			console.log('next_page');
			var wrapper     = $( '#variable_product_options' ).find( '.woocommerce_variations' ),
				total_pages = parseInt( wrapper.attr( 'data-total_pages' ), 10 ),
				next_page   = parseInt( wrapper.attr( 'data-page' ), 10 ) + 1,
				new_page    = ( total_pages >= next_page ) ? next_page : total_pages;

			woopanel_product_variations_pagenav.set_page( new_page );
		}else {
			alert(WooPanel.product.i18n_last_page);
		}

		return false;
	},

		/**
		 * Check button if enabled and if don't have changes
		 *
		 * @return {Bool}
		 */
		check_is_enabled: function( current ) {
			return ! $( current ).hasClass( 'disabled' );
		},

	/**
	 * Paginav pagination selector
	 */
	page_selector: function() {
		var selected = parseInt( $( this ).val(), 10 ),
			wrapper  = $( '#variable_product_options' ).find( '.woocommerce_variations' );

		$( '.variations-pagenav .page-selector' ).val( selected );

		WooPanel_Product_Metaboxes.check_for_changes();
		woopanel_product_variations_pagenav.change_classes( selected, parseInt( wrapper.attr( 'data-total_pages' ), 10 ) );

		WooPanel_Product_Metaboxes.woopanel_load_variations(selected);
	},

	/**
	 * Change "disabled" class on pagenav
	 */
	change_classes: function( selected, total ) {
		var prev_page  = $( '.variations-pagenav .prev-page' ),
			next_page  = $( '.variations-pagenav .next-page' );

		if ( 1 === selected ) {
			prev_page.addClass( 'disabled' );
		} else {
			prev_page.removeClass( 'disabled' );
		}

		if ( total === selected ) {
			next_page.addClass( 'disabled' );
		} else {
			next_page.removeClass( 'disabled' );
		}
	},

	/**
	 * Set variations count
	 *
	 * @param {Int} qty
	 *
	 * @return {Int}
	 */
	update_variations_count: function( qty ) {
		var wrapper        = $( '#variable_product_options' ).find( '.woocommerce_variations' ),
			total          = parseInt( wrapper.attr( 'data-total' ), 10 ) + qty,
			displaying_num = $( '.variations-pagenav .displaying-num' );

		// Set the new total of variations
		wrapper.attr( 'data-total', total );

		if ( 1 === total ) {
			displaying_num.text( WooPanel.product.i18n_variation_count_single.replace( '%qty%', total ) );
		} else {
			displaying_num.text( WooPanel.product.i18n_variation_count_plural.replace( '%qty%', total ) );
		}

		return total;
	},
}

jQuery(document).ready(function() {
	WooPanel_Product_Metaboxes.init();
	WooPanel_Product_Metaboxes_Variation.init();
});