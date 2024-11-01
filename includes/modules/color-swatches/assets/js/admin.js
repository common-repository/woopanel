jQuery( function( $ ) {


	if( typeof woocommerce_admin_meta_boxes_variations != 'undefined' ) {
		var WooPanel_Ajax_URL = woocommerce_admin_meta_boxes_variations.ajax_url;
		var WooPanel_Product_Load_Nonce = woocommerce_admin_meta_boxes_variations.load_variations_nonce;
		
		var WooPanel_Product_ID = woocommerce_admin_meta_boxes_variations.post_id;
		var isAdmin = true;
	}else {
		if( $('[name="post_title"]').length > 0 && $('#post_type').val() == 'product' ) {
			var WooPanel_Ajax_URL = WooPanel.ajaxurl;
			var WooPanel_Product_Load_Nonce = WooPanel.product.load_variations_nonce;
			var WooPanel_Product_EnterPrice_Nonce = WooPanel.product.input_price_nonce;
			var WooPanel_Product_SavePrice_Nonce = WooPanel.product.save_price_nonce;
			var WooPanel_Product_ID = WooPanel.product.post_id;
			
		}
		var isAdmin = null;
	}
	
	var wp = window.wp;
	/**
	 * Variations Price Matrix actions
	 */
	var nbt_cs_admin = {

		/**
		 * Initialize variations actions
		 */
		init: function() {
			if( jQuery().wpColorPicker ) {
				$( '#term-color, .term-color' ).wpColorPicker();
			}
			if( jQuery().spectrum ) {
				this._spectrum();
			}
			
			$(document).on('click', '.nbtcs-upload-image-button', this.upload_image);
			$(document).on( 'click', '.nbtcs-remove-image-button', this.remove_upload_image);
			$(document).on('click', '#_color_swatches', this.enable_color_swatches);
			$(document).on('click', 'li.color_swatches_options a, .color_swatches_tab a', this.initial_load);
			$(document).on('click', '.save_color_swatches', this.save_color_swatches);
			$(document).on('click', '#color_swatches .m-accordion__item-head', this.openAccordion);
			

			$(document).on('click', '.cs-radio', this.style_selected);
	   		$(document).on('click', '.enable_custom_checkbox', this.custom_repeater);
	   		

			$(document).on('change', '.cs-type-tax', this.change_type);
			$(document).ajaxComplete(this.remove_field_tags);
			this.check_enable_color_swatches();
		},
		
		openAccordion: function(e) {
			e.preventDefault();
			
			var $wrapper = $(this).closest('.m-accordion__item');
				

			if( $wrapper.hasClass('open') ) {
				$wrapper.removeClass('open');
				$wrapper.find('.m-accordion__item-body').stop().slideUp();
			}else {
				$wrapper.addClass('open');
				$wrapper.find('.m-accordion__item-body').stop().slideDown();
			}
			
		},
		
		_spectrum: function() {
			$("#term-color, .term-color").each(function( index ) {
				var $this = $(this),
					$val = $( this ).val();
				
				$( this ).spectrum({
					allowEmpty:true,
					color: $val,
					showInput: true,
					containerClassName: "full-spectrum",
					showInitial: true,
					showPalette: true,
					showSelectionPalette: true,
					showAlpha: true,
					maxPaletteSize: 10,
					preferredFormat: "hex",
		
					move: function (color) {
						var hexColor = "transparent";
						if(color) {
							hexColor = color.toHexString();
						}
						
						$this.val(hexColor);
					},
					palette: [
						["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)", /*"rgb(153, 153, 153)","rgb(183, 183, 183)",*/
						"rgb(204, 204, 204)", "rgb(217, 217, 217)", /*"rgb(239, 239, 239)", "rgb(243, 243, 243)",*/ "rgb(255, 255, 255)"],
						["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
						"rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
						["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
						"rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
						"rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
						"rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
						"rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
						"rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
						"rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
						"rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
						/*"rgb(133, 32, 12)", "rgb(153, 0, 0)", "rgb(180, 95, 6)", "rgb(191, 144, 0)", "rgb(56, 118, 29)",
						"rgb(19, 79, 92)", "rgb(17, 85, 204)", "rgb(11, 83, 148)", "rgb(53, 28, 117)", "rgb(116, 27, 71)",*/
						"rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
						"rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
					]
				});
			});

		},
		
		initial_load: function(){
			nbtcs_ajax.block();

			var tpl = $('#tpl-color-swatches').html();
			var wrapper_attributes = $( '#variable_product_options' ).find( '.woocommerce_variations' ).data( 'attributes' );
			if(wrapper_attributes == undefined){
				total_attr = 0;
			}else{
				var total_attr = Object.keys(wrapper_attributes).length;
			}

			$.ajax({
				url: WooPanel_Ajax_URL,
				data: {
					action:     'cs_load_variations',
					security:   WooPanel_Product_Load_Nonce,
					product_id: WooPanel_Product_ID,
					attributes: wrapper_attributes,
					is_admin: isAdmin
				},
				type: 'POST',
				datatype: 'json',
				success: function( rs ) {
					$('.woocommerce-message').remove();
					
					if(rs.complete != undefined){
						$('#color_swatches').html(tpl);
						$('.color_swatches.wc-metaboxes').html(rs.html);
						
						if( jQuery().wpColorPicker ) {
							$('.term-color' ).wpColorPicker();
						}
						
						if( jQuery().spectrum ) {
							nbt_cs_admin._spectrum()
						}
					}else{
						if( $('#m-portlet__tabright #color_swatches').length > 0 ) {
							$('#m-portlet__tabright #color_swatches').html($('#msg-js').html());
						}else {
							$('#price_matrix_options_inner').html( $('#msg-js').html() );
						}
					}

					nbtcs_ajax.unblock();
				},
				error:function(){
					alert('There was an error when processing data, please try again !');
					nbtcs_ajax.unblock();
				}
			});
		},
		remove_field_tags: function(event, request, options) {
			if ( request && 4 === request.readyState && 200 === request.status && options.data && ( 0 <= options.data.indexOf( '_inline_edit' ) || 0 <= options.data.indexOf( 'add-tag' ) ) ) {
				var res = wpAjax.parseAjaxResponse( request.responseXML, 'ajax-response' );
				
				if ( ! res || res.errors ) {
					return;
				}
				$( '#term-color, .term-color' ).wpColorPicker();
				$( '#wpbody-content' ).trigger('click');
				$('.wp-color-result').css( 'background-color', '' );
			}
		},
		style_selected: function(){
			var $this =  $(this).closest('li');
			$(this).closest('ul').find('li').removeClass('selected');
			$(this).closest('ul').find('.input-radio').removeAttr('checked')
			$this.find('.input-radio').attr('checked', 'checked');
			$this.addClass('selected');
		},
		custom_repeater: function(){
			var $this = $(this).closest('.woocommerce_attribute_data').find('.pm_repeater');
			if($(this).is(':checked')){
				$this.show();
			}else{
				$this.hide();
			}
		},
		change_type: function(){
			var panels = $(this).closest('.woocommerce_attribute');
			var $tax = panels.attr('data-taxonomy');
			var $table = $(this).closest('.woocommerce_attribute').find('.pm_repeater');
			var tax_type = panels.find('.cs-type-tax').val();
			var wrapper_attributes = $( '#variable_product_options' ).find( '.woocommerce_variations' ).data( 'attributes' );
			if(wrapper_attributes == undefined){
				total_attr = 0;
			}else{
				var total_attr = Object.keys(wrapper_attributes).length;
			}

			if(tax_type != '0'){
				nbtcs_ajax.block();
				$.ajax({
					url: WooPanel_Ajax_URL,
					data: {
						action:     'cs_load_style',
						security:   WooPanel_Product_Load_Nonce,
						product_id: WooPanel_Product_ID,
						tax: $tax,
						type: tax_type
					},
					type: 'POST',
					datatype: 'json',
					success: function( response ) {
						var rs = JSON.parse(response);
						$('.woocommerce-message').remove();
						if(rs.complete != undefined){
							if(tax_type == '' || tax_type == 'radio' || tax_type == 'label' ){
								panels.find('.pm_repeater').empty();
								panels.find('.pm_repeater').hide();
							}else {
								$table.show().html(rs.html);
								if( jQuery().wpColorPicker ) {
									$('.term-color' ).wpColorPicker();
								}
								
								if( jQuery().spectrum ) {
									nbt_cs_admin._spectrum()
								}
							}
						}

						nbtcs_ajax.unblock();
					},
					error:function(){
						alert('There was an error when processing data, please try again !');
						nbtcs_ajax.unblock();
					}
				});
			}else{
				$table.hide();
			}


		},
		upload_image: function(e){

			e.preventDefault();
			var $button = $( this ).closest('.nbtcs-wrap-image');

			// Create the media frame.
			var frame = wp.media.frames.downloadable_file = wp.media( {
				title   : nbtcs.i18n.mediaTitle,
				button  : {
					text: nbtcs.i18n.mediaButton
				},
				multiple: false
			} );

			// When an image is selected, run a callback.
			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();

				if( $button.closest('.pm-row').length > 0 ) {
					var pm_row = $button.closest('.pm-row')

					pm_row.find( 'input.nbtcs-term-image' ).val( attachment.id );
					pm_row.find( '.nbtcs-remove-image-button' ).show();
					pm_row.find( 'img' ).attr( 'src', attachment.url );			

				}else {
					$button.addClass('class_name' + attachment.id);
					$button.find( 'input.nbtcs-term-image' ).val( attachment.id );
					$button.find( '.nbtcs-remove-image-button' ).show();
					$button.find( 'img' ).attr( 'src', attachment.url );
				}
			} );

			// Finally, open the modal.
			frame.open();
		},
		remove_upload_image: function(){
			var $button = $( this );

			$button.siblings( 'input.nbtcs-term-image' ).val( '' );
			$button.siblings( '.nbtcs-remove-image-button' ).show();
			$button.parent().prev( '.nbtcs-term-image-thumbnail' ).find( 'img' ).attr( 'src', nbtcs.placeholder );

			return false;
		},
		check_enable_color_swatches: function(){
			var $cs = $('#_color_swatches');
			if($cs.closest('label').hasClass('yes')) {
				$cs.prop('checked', true);
				$('.color_swatches_options').removeClass('hide');
			}else {
				$cs.prop('checked', false);
				$('.color_swatches_options').addClass('hide');

			}
		},
		enable_color_swatches: function(){
			if($(this).is(':checked')){
			 	$('.color_swatches_options').removeClass('hide');
			}else{
				$('.color_swatches_options').addClass('hide');

				if( $('#color_swatches').is(":visible") ) {
					$('.woocommerce_options_panel').hide();
					$('#inventory_product_data').show();
					$('.product_data_tabs > li').removeClass('active');
					$('.inventory_options.inventory_tab').addClass('active');
				}
			}
		},
		save_color_swatches: function(){
			nbtcs_ajax.block();
			var type = [];
			var tax = [];
			
			var custom = [];
			$('.cs-type-tax :selected').each(function(i, selected){ 
				type[i] = $(selected).val();
				tax[i] = $(selected).closest('select').attr('data-id'); 
			});

			var style = [];

			var datas = [];
			
			$('#color_swatches .woocommerce_attribute').each(function(i, selected){ 
				style[i] = $(selected).find('.input-radio:checked').attr('value');

				var $tax = $(selected).attr('data-taxonomy');
				var $type = $(selected).find('.cs-type-tax').val();
				var data = [$type];

				if($type != 'radio'){
					var color = $(selected).find('.term-alt-color').map(function(_, el) {
					    return $(el).val();
					}).get();
					
					var image = $(selected).find('.nbtcs-term-image').map(function(_, el) {
					    return $(el).val();
					}).get();

					if($type == 'image'){
						data.push({image: image});
					}else{
						data.push({color: color});
					}
					
				}

				datas.push(data);



			});


			$.ajax({
				url: WooPanel_Ajax_URL,
				data: {
					action: 'cs_save',
					product_id: WooPanel_Product_ID,
					type: type,
					tax: tax,
					style: style,
					custom: datas
				},
				type: 'POST',
				datatype: 'json',
				success: function( response ) {
					var rs = JSON.parse(response);

					nbtcs_ajax.unblock();

				},
				error:function(){
					alert('There was an error when processing data, please try again !');
					nbtcs_ajax.unblock();
				}
			});
		}
	}


	var nbtcs_ajax = {
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
		}
	}
	
	nbt_cs_admin.init();

});