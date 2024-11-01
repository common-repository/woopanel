( function( $) {
	var xhr_tagsbox = null;

	$.nbTagsBox = function( options ) {
		var settings = $.extend({
			target : '.tagsdiv',
			action : 'get_tags',
			inputId : '#post_tag',
			inputAdd : 'input.newtag',
			loadingClass : 'nb-loading',
			disabled : false,
		}, options );

		var selector = $( settings.target );

		if( !selector.length ) return;

		var thetags = selector.find( settings.inputId ),
			thetags_str = thetags.val().trim(),
			current_tags = thetags_str.split(','),
			id = selector.attr('id'),
			tagchecklist = selector.find('.tagchecklist'),
			btn_add = selector.find('.tagadd');

		htmlTags = function(){
			htmlParse = '';
			$.each( current_tags, function( key, val ) {
				val = $.trim( val );

				if ( ! val ) return;

				// Create a new list item, and ensure the text is properly escaped.
				listItem = $( '<li />' ).attr('term_name', val).text( val );

				if ( ! settings.disabled ) {
					var xbutton = $( '<button type="button" id="' + id + '-check-num-' + key + '" class="ntdelbutton">' +
						'<span class="remove-tag-icon" aria-hidden="true"></span>' +
						'</button>' );

					listItem.prepend( '&nbsp;' ).prepend( xbutton );
				}
				htmlParse = htmlParse + listItem[0].outerHTML;
			});
			tagchecklist.show().html(htmlParse);
		}

		parseTags = function(){
			var new_tags = [];


			$.each( current_tags, function( key, val ) {
				val = $.trim( val );
				if ( val ) {
					new_tags.push( val );
				}
			});

			new_tags = new_tags.filter(function(elem, index, self) {
				return index === self.indexOf(elem);
			});

			current_tags = new_tags;

			thetags.val( current_tags.join() );
			htmlTags();
			return false;
		}
		parseTags();

		$(document).on( 'click keypress', '.ntdelbutton', function(){
			var current_tag = $(this).closest('li'),
				current_tag_val = current_tag.attr('term_name');

			current_tags.splice( $.inArray(current_tag_val, current_tags), 1 );
			parseTags();
		});

		$(document).on('click', '.wp-tag-cloud .tag-cloud-link', function(e) {
			e.preventDefault();

			var newtag = selector.find( settings.inputAdd ),
				$text = $(this).text(),
				$tax = $('#most-used-tags_link').attr('data-taxonomy'),
				$textarea = $('[name="' + $tax + '"]').val();
				
			
			var checklist = [];
			$( ".tagchecklist li" ).each(function( index ) {
				var tag_name = $(this).attr('term_name');
				checklist.push(tag_name);
			});

			if( jQuery.inArray( $text, checklist ) < 0 ) {
				$('[name="' + $tax + '"]').val($textarea + ',' + $text);
				$('[name="' + $tax + '"]').text($textarea + ',' + $text);
				$('.tagchecklist').append('<li term_name="' + $text + '"><button type="button" id="post_tag-wrapper-check-num-2" class="ntdelbutton"><span class="remove-tag-icon" aria-hidden="true"></span></button>&nbsp;' + $text + '</li>');
				$('.tagadd').trigger('click');
			}
		});
		
		$(document).on('click', '#most-used-tags_link', function(e) {
			e.preventDefault();
			
			var $this = $(this),
				$tags = $('#most-used-tags');
				
			if( ! $tags.hasClass('active') ) {
				
				$tags.addClass('active tag-toggle');
				$('.the-tagcloud').slideDown();
				$this.prop("disabled", true);
				$('#tagcloud-post_tag').addClass('m-loader');
				$('#tagcloud-post_tag').html('&nbsp;');
						
				$.ajax({
					url: WooPanel.ajaxurl,
					data: 'action=woopanel_tagcloud&tax=' + $this.attr('data-taxonomy') + '&security=' + $this.attr('data-security'),
					type: 'POST',
					success: function( response ) {
						$this.prop("disabled", false);
						$('#tagcloud-post_tag').removeClass('m-loader');
						$('#tagcloud-post_tag').html(response);
					},
					error:function( xhr, status, error ) {
						$this.prop("disabled", false);
						$('#tagcloud-post_tag').removeClass('m-loader');
	
						if( xhr.status == 403) {
							$('#tagcloud-post_tag').html('');
							alert( WooPanel.label.i18n_deny);
						}else {
							alert('There was an error when processing data, please try again !');
						}
					}
				});
				
			}else {
				if( ! $tags.hasClass('tag-toggle') ) {
					$tags.addClass('tag-toggle');
					$('.the-tagcloud').slideDown();
				}else {
					$tags.removeClass('tag-toggle');
					$('.the-tagcloud').slideUp();
				}
			}
		});

		btn_add.on( 'click', function() {
			var newtag = selector.find( settings.inputAdd ),
				newtag_str = newtag.val(),
				adding_tags = newtag_str.split(',').filter(function(v){return v!==''}),
				textarae_tags = $('.the-tags').val().split(',').filter(function(v){return v!==''});

			// Merge and unique value
			console.log(newtag_str);
			if( ! newtag_str || 0 === newtag_str.length ) {
				var adding_tags = $.merge( textarae_tags, adding_tags );
			}
			console.log(textarae_tags);
			console.log(adding_tags);
			$.merge( current_tags, textarae_tags, adding_tags );

			//parseTags();
			//newtag.val('');
			//newtag.focus();
		});

		$( settings.inputAdd ).keypress( function( event ) {
            if ( 13 == event.which ) {
                var newtag_str = $( this ).val(),
                    adding_tags = newtag_str.split(',').filter(function(v){return v!==''});

                // Merge and unique value
                $.merge( current_tags, adding_tags );

                parseTags();
                $( this ).val('');
                $( this ).focus();
            }
        }).blur( function( event ) {
            var newtag_str = $(this).val(),
                adding_tags = newtag_str.split(',').filter(function (v) {
                    return v !== ''
                });
        	if( newtag_str != '' ) {
                // Merge and unique value
                $.merge(current_tags, adding_tags);

                parseTags();
                // $(this).removeClass('loading');
            }
            $(this).val('');
            event.preventDefault();
        }).keypress( function( event ) {
			if ( 13 == event.which ) {
				event.preventDefault();
				event.stopPropagation();
			}
		});
	};
})(jQuery);