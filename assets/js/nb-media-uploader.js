/**
 * Set Featured Image
 * Copyright (c) 2019 Netbase JSC
 */
( function( $) {
	var xhr_uploader = null;

	$.nbMediaUploader = function( options, callback = false ) {
		var settings = $.extend({
			target : '.media-uploader',
			action : 'get_image',
			uploaderTitle : 'Image',
			btnSelect : 'Set Image',
			btnSetId : '.add_image',
			btnSetText : 'Set Image',
			btnRemoveId : '.remove_image',
			loadingClass : 'nb-loading',
			multiple : false,
		}, options );

		if( !$( settings.target ).length ) return; 


		// Image ordering.
		gallery_images( settings.target, settings.action, settings.loadingClass );

		// Add link.
		$( settings.target ).on('click', settings.btnSetId, function(e) {
			e.preventDefault();
			var selector = $(this).closest( settings.target );
			var inputMedia = selector.find('input[type=hidden]');
			var woopanel_uploader = wp.media({
				title: settings.uploaderTitle,
				button: {
					text: settings.btnSelect
				},
				multiple: settings.multiple
			});
			if ( inputMedia.length == 0 ) return;

			selector.addClass(settings.loadingClass);

			woopanel_uploader.on('select', function() {
				var selection = woopanel_uploader.state().get('selection');
				var attachment_ids = selection.map( function( attachment ) {
						attachment = attachment.toJSON();
						return attachment.id;
					}).join();

				if( xhr_uploader && xhr_uploader.readyState != 4 ){ xhr_uploader.abort(); }
				xhr_uploader = $.ajax({
					type : "post",
					dataType : "html",
					url : WooPanel.ajaxurl,
					data : {
						action: settings.action,
                        image_ids: attachment_ids,
                        input_name: inputMedia.attr('name'),
					},
					success: function(response) {
						selector.removeClass(settings.loadingClass);
						selector.html(response);
						gallery_images( settings.target, settings.action, settings.loadingClass );
	
						if ( typeof callback === 'function' ) {
							callback( selector );
						}
					},
					error: function( jqXHR, textStatus, errorThrown ){
						console.log( 'The following error occured: ' + textStatus, errorThrown );
					}
				});
			});
			woopanel_uploader.on('open', function() {
				var imgIDs = inputMedia.val().split(',');

				var selection = woopanel_uploader.state().get('selection');

				if (imgIDs && imgIDs.length) {
					$.each(imgIDs, function(idx, val) {
						attachment = wp.media.attachment(val);
						attachment.fetch();
						selection.add( attachment ? [ attachment ] : [] );
					});
				}
			});
			woopanel_uploader.open();
		});

		// Remove link.
		$( settings.target ).on('click', settings.btnRemoveId, function(e) {
			e.preventDefault();
			var selector = $(this).closest( settings.target );
            var inputMedia = selector.find('input[type=hidden]');

			selector.addClass(settings.loadingClass);
			
			if( xhr_uploader && xhr_uploader.readyState != 4 ){ xhr_uploader.abort(); }
			xhr_uploader = $.ajax({
				type : "post",
				dataType : "html",
				url : WooPanel.ajaxurl,
				data : {
					action: settings.action,
					image_ids: null,
                    input_name: inputMedia.attr('name'),
				},
				success: function(response) {
					selector.removeClass(settings.loadingClass);
					selector.html(response);
					gallery_images( settings.target, settings.action, settings.loadingClass );
				},
				error: function( jqXHR, textStatus, errorThrown ){
					selector.removeClass(settings.loadingClass);
					console.log( 'The following error occured: ' + textStatus, errorThrown );
				}
			});
		});

		// Close Modal.
		$( document ).on('click', '.media-modal-close', function(e) {
			$( settings.target ).removeClass(settings.loadingClass);
		});

		// Remove images.
		$( settings.target ).on( 'click', '.image a.delete', function() {
			var selector = $(this).closest( settings.target );
            var inputMedia = selector.find('input[type=hidden]');
			var imgIDs = inputMedia.val().split(',');
			var removeItem = $( this ).closest( 'li.image' );
			var removeItemID = removeItem.data('attachment_id');

			selector.addClass(settings.loadingClass);

			imgIDs.splice( $.inArray(removeItemID, imgIDs), 1 );

			var attachment_ids = imgIDs.join();

			if( xhr_uploader && xhr_uploader.readyState != 4 ){ xhr_uploader.abort(); }
			xhr_uploader = $.ajax({
				type : "post",
				dataType : "html",
				url : WooPanel.ajaxurl,
				data : {
					action: settings.action,
					image_ids: attachment_ids,
                    input_name: inputMedia.attr('name'),
				},
				success: function(response) {
					removeItem.remove();
					selector.removeClass(settings.loadingClass);
					selector.html(response);
					gallery_images( settings.target, settings.action, settings.loadingClass );
					
					if ( typeof callback === 'function' ) {
						callback( response );
					}

				},
				error: function( jqXHR, textStatus, errorThrown ){
					console.log( 'The following error occured: ' + textStatus, errorThrown );
				}
			});
		});
	}

	function gallery_images( $target, $action, $loadingClass ){
		var product_images = $( $target ).find( 'ul.images' );
        var inputMedia = $( $target ).find('input[type=hidden]');
		if( product_images.length > 0 ) {
			product_images.sortable({
				items: 'li.image',
				cursor: 'move',
				scrollSensitivity: 40,
				forcePlaceholderSize: true,
				forceHelperSize: false,
				helper: 'clone',
				opacity: 0.65,
				placeholder: 'sortable-placeholder',
				start: function( event, ui ) {
					ui.item.css( 'background-color', '#f6f6f6' );
				},
				stop: function( event, ui ) {
					ui.item.removeAttr( 'style' );
				},
				update: function() {
					var selector = $(this).closest( $target );
					var attachment_ids = '';

					selector.addClass( $loadingClass );

					$( $target ).find( 'ul li.image' ).css( 'cursor', 'default' ).each( function() {
						var attachment_id = $(this).data( 'attachment_id' );
						attachment_ids = attachment_ids + attachment_id + ',';
					});
					attachment_ids = attachment_ids.slice(0,-1);

					if( xhr_uploader && xhr_uploader.readyState != 4 ){ xhr_uploader.abort(); }
					xhr_uploader = $.ajax({
						type : "post",
						dataType : "html",
						url : WooPanel.ajaxurl,
						data : {
							action: $action,
							image_ids: attachment_ids,
                            input_name: inputMedia.attr('name'),
						},
						success: function(response) {
							selector.removeClass( $loadingClass );
							selector.html(response);
							gallery_images( $target, $action, $loadingClass );
						},
						error: function( jqXHR, textStatus, errorThrown ){
							console.log( 'The following error occured: ' + textStatus, errorThrown );
						}
					});
				}
			});
		};
		
		
	}
})(jQuery);