var x = false;

jQuery( function( $ ) {
	
	

	var nbtcs_frontend = {
		init: function(){
	

			$('.variations_form').addClass( 'swatches-support' );

			$('.variations_form').on( 'click', '.swatch', this.select_attributes);
			$('.variations_form').on( 'click', '.reset_variations', this.reset_attributes);
			//$(document).on( 'change', 'table.variations td.value select', this.trigger_price_matrix);

	   		$(document).ajaxComplete(this.ajax_quick_view);
	   		$(document).ajaxStop(function() {
	   			x = false;
			});
			   
			$(document).ajaxComplete(function(event, xhr, options) {
				if( typeof(options.url) == "string" && options.url.includes("get_variation")) { 
					if( xhr.status == 200) {
						nbtcs_frontend.trigger_price_matrix();
					}
				}
			});
		},

		ajax_quick_view: function(event, request, settings){
			if(!x && typeof(settings.data) == "string" && settings.data && settings.data.includes("action=yith_load_product_quick_view")){
				nbtcs_frontend.init();
				x = true;
			}
		},
		
		select_attributes: function(){
			var selected = [];
			var $el = $( this );
			
			var attr = $el.closest('.nbtcs-swatches');

			var $select = attr.prev().find('select'),
				$nbtcs_swatches = $el.closest('.nbtcs-swatches'),
				attribute_name = $select.data( 'attribute_name' ) || $select.attr( 'name' ),
				value = $el.attr( 'data-value' );
				

			$select.trigger( 'focusin' );

			// Check if this combination is available
			if ( ! $select.find( 'option[value="' + value + '"]' ).length ) {
				$el.siblings( '.swatch' ).removeClass( 'selected' );
				$select.val( '' ).change();
				$('.variations_form').trigger( 'tawcvs_no_matching_variations', [$el] );
				return;
			}

			clicked = attribute_name;

			if ( selected.indexOf( attribute_name ) === -1 ) {
				selected.push(attribute_name);
			}

			if($el.hasClass('swatch-radio')){
				$select.val( value );
			}else{
				if ( $el.hasClass( 'selected' ) ) {
					$select.val( '' );
					$el.removeClass( 'selected' );
					delete selected[selected.indexOf(attribute_name)];
				} else {
					$el.addClass( 'selected' ).siblings( '.selected' ).removeClass( 'selected' );
					$select.val( value );
				}		
			}

			/* Trigger last change */
			var table_variation = $( '.variations_form table.variations td.value select' ),
				total_variations = table_variation.length,
				total_selected = 0,
				new_push = [];

			table_variation.each(function( index ) {
				var attribute_name = $(this).attr('data-attribute_name'),
					attribute_value = $( this ).val(),
					attribute_name = attribute_name.replace("attribute_", "");


				if( (total_variations - 1) == index && $('.pm-select-last').length <= 0 ) {
					$(this).addClass('pm-select-last');
				}

				if( attribute_value ) {
					total_selected += 1;
					new_push.push( md5(attribute_name + attribute_value) );
				}
			});

			if(total_variations == total_selected ) {
				$select.trigger('change');
				nbtcs_frontend.trigger_price_matrix();
			}
		},

		trigger_price_matrix: function() {
			var $this = $(this);

			if( $('.price-matrix-table').length > 0 ) {
				$('.pm-td-price').removeClass('selected');

				var variation_id = 'pm-price-' + $('.variation_id').val();

				$('#' + variation_id).addClass('selected');

				/* Vacant Attributes */
				$('table.un-variations td.value select').each(function( index ) {
					var attr_name = $(this).attr('data-attribute_name'),
						attr_value = $('[data-attribute_name="attribute_' + attr_name + '"]').val();

					$(this).val(attr_value);

				});


			}
		},
		
		select_attributes_radio: function(){
			var $el = $( this );

			alert(2);
		},
		
		reset_attributes: function(){
			$( this ).closest( '.variations_form' ).find( '.swatch.selected' ).removeClass( 'selected' );
			$( this ).closest( '.variations_form' ).find('[type="radio"]').prop('checked', false); 
			selected = [];
		}
	}
	nbtcs_frontend.init();
});