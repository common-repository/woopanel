jQuery(document).ready(function($) {
	var xhr_view = null;
	
	$(document).on('click', '.btn-tax-actions', function(e) {
		e.preventDefault();
		
		var favorite = [],
			$this = $(this),
			this_page = window.location.toString();
		$.each( $('.wpl-datatable__body .m-checkbox input[type="checkbox"]:checked' ), function(){            
			favorite.push($(this).val());
		});

		this.xhr_view = $.ajax({
			url: WooPanel.ajaxurl,
			data: {
				action: 'woopanel_delete_category',
				term_id: favorite,
				taxonomy: $this.attr('data-taxonomy'),
				security: $this.attr('data-security')
			},
			type: 'POST',
			datatype: 'json',
			success: function( response ) {
				if(response.complete != undefined ) {
					jQuery( '#posts-filter' ).load( this_page + ' .woopanel-list-post-table', function() {
						
					});
				}
			},
			error:function( xhr, status, error ) {

			}
		});
	});
		
		
	
	
	$(document).on('click', '#link-category-add-submit', function(e) {
		e.preventDefault();

		var $this = $(this);
		$this.addClass('m-loader');
		$this.prop('disabled', true);

		$.ajax({
			url: WooPanel.ajaxurl,
			data: 'action=woopanel_add_category&name=' + $('.add_category_name').val() + '&parent=' + $('#newcategory_parent').val() + '&taxonomy=' + $('.taxonomy_type').val() + '&security=' + $this.attr('data-security'),
			type: 'POST',
			datatype: 'json',
			success: function( response ) {
				$this.removeClass('m-loader');
				$this.prop('disabled', false);
				
				if( response.complete != undefined ) {
					$('[name="add_category_name"]').val('');
					$('#newcategory_parent').prop('selectedIndex', 0);
					$('#' +  response.element).append(response.html);
				}else {
					alert(response.data['message']);
				}
			},
			error:function( xhr, status, error ) {
				$this.removeClass('m-loader');
				$this.prop('disabled', false);
				
				if( xhr.status == 403) {
					alert( WooPanel.label.i18n_deny);
				}else {
					alert('There was an error when processing data, please try again !');
				}
			}
		});
	});

	$(document).on('click', '#category-add-toggle', function(e) {
		e.preventDefault();

		$('#link-category-add').slideToggle();
	});
	$(document).on('click', '#editable-post-name', function(e) {
		e.preventDefault();
	});

	$(document).on('click', '.edit-slug', function(e) {
		e.preventDefault();
		
		var $field_edit = $('#editable-post-name');
		
		if( $('#new-post-slug').length <= 0 ) {
			var $permalink = $('#editable-post-name').attr('data-title');
			console.log($permalink);
			$field_edit.html('<input type="text" id="new-post-slug" name="post_permalink" value="' + $permalink + '" class="form-control m-input" autocomplete="off">');
			$('#edit-slug-buttons').hide();
		}
	});

	hightlight_table();
	function hightlight_table() {
		$( "tr.wpl-datatable_edit" ).each(function( index ) {
			if (index % 2 === 0) {
				$(this).addClass('td-white');
			}
		});
		
		$( "tr.wpl-datatable__row_cmhide" ).each(function( index ) {
			if (index % 2 === 0) {
				$(this).addClass('td-white');
			}
		});
	}

	$(document).on('click', '.cm-destructive', function(e) {
		e.preventDefault();
		
		var $id = $(this).attr('data-id');

		$('#cm-hide-' + $id + ' td .spam-undo-inside, #cm-hide-' + $id + ' td .trash-undo-inside').hide();
		$('#cm-hide-' + $id + ' td').css('background-color', '#dff0d8');
		$('#cm-hide-' + $id + ' td').fadeOut( 400, function() {
			$('#cm-hide-' + $id).hide();
			$('#user-' + $id + ' td').removeAttr('style');
			$('#user-' + $id).show();
		});

		
		$.ajax({
			url: WooPanel.ajaxurl,
			data: 'action=woopanel_comment_link&method=undo&id=' + $id,
			type: 'POST',
			datatype: 'json',
			success: function( response ) {
			},
			error:function(){
				alert('There was an error when processing data, please try again !');
			}
		});
	});
	
	$(document).on('click', '.comment-link', function(e) {
		e.preventDefault();

		var $action = $(this).attr('data-action'),
			$id = $(this).attr('data-id'),
			$row = $(this).closest('.row-actions');

		if( $action == 'spam' || $action == 'trash' ) {
			if( $action == 'spam' ) {
				$('#cm-hide-' + $id + ' td .spam-undo-inside').show();
			}
			
			if( $action == 'trash' ) {
				$('#cm-hide-' + $id + ' td .trash-undo-inside').show();
			}

			$('#user-' + $id + ' td').css('background-color', '#f2dede');
			$('#cm-hide-' + $id + ' td').removeAttr('style');
			$('#user-' + $id ).fadeOut( "slow", function() {
				$('#cm-hide-' + $id).show();
			});
			
			$row.closest('.wpl-datatable__row').css('background-color', '#f2dede');
		}
	
		if( $action === "unapprove" ||
			$action === "approve" ||
			$action === "delete" ||
        	$action === "unspam" ||
            $action === "untrash" ) {
			$('.woopanel-list-post-table .table-responsive').block({
				message: '<div class="loading-message"><div class="block-spinner-bar"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>',
				overlayCSS: {
					background: '#555',
					opacity: 0.1
				}
			});
		}

		
		$.ajax({
			url: WooPanel.ajaxurl,
			data: 'action=woopanel_comment_link&method=' + $action + '&id=' + $id,
			type: 'POST',
			datatype: 'json',
			success: function( response ) {
				$('.woopanel-list-post-table .table-responsive').unblock();
				if( response.complete != undefined ) {
                    console.log( $action );
                    console.log(response.status_msg);

					if( response.status_class == 'approved' ) {
                        $row.find('span.approve').removeClass('hidden');
                        $row.find('span.unapproved').addClass('hidden');
					} else if( response.status_class == 'unapproved' ) {
                        $row.find('span.approve').addClass('hidden');
                        $row.find('span.unapproved').removeClass('hidden');
					}
					if( $action === 'unspam' || $action == 'untrash' || $action == 'delete' ) {
                        $row.closest('tr').remove();
                    }
				}
			},
			error:function(){
				alert('There was an error when processing data, please try again !');
			}
		});
	});
    function display_bulk_actions(){
        var checked = $('tbody .check-column input[type=checkbox]:checked').length;
        if( checked > 0 ) {
            $('.tablenav').show();
            $('.tablenav .selected_number').html( checked );
        } else {
            $('.tablenav').hide();
        }
    }
    display_bulk_actions();
	
	if( jQuery().selectpicker ) {
		$('.m-bootstrap-select').selectpicker();
	}

    $("table").on("click", "thead .check-column :checkbox, tfoot .check-column :checkbox", function(){
        var c = this.checked;
        $(':checkbox').prop('checked', c);
        display_bulk_actions();
    });
    $("table").on("click", "tbody .check-column input[type=checkbox]", function(){
        display_bulk_actions();
    });
	
	if( jQuery().tooltip ) {
		$('[data-toggle="tooltip"]').tooltip({ html: true });
	}
	
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
	
	if( $('.woopanel-readmore').length > 0 ) {
		$( '.woopanel-readmore' ).each(function( index ) {
			var $max_height = $(this).attr('data-height');
			var $length = $(this).outerHeight();
			
			if( $length > $max_height ) {
				$(this).css({"height": $max_height, "overflow": "hidden"});
				$(this).after('<a href="javascript:;" class="woopanel-readmore-text">... view more</a>');
			}
		});
		
		$(document).on('click', '.woopanel-readmore-text', function(e) {
			e.preventDefault();
			
			$(this).prev().removeAttr('style');
			$(this).remove();
		});
	}
	
	if( $( '.m-datatable__table' ).length > 0 ) {
		
		$( '.m-datatable__table tbody tr' ).each(function( index ) {
			var $tr = $(this);

			var max = 0;
			$tr.find('td').each(function( index ) {
				if( $(this).children().length > 0 ) {
					var $width = $(this).children().outerWidth();
				}else {
					var $width = $(this).outerWidth();
				}
				
				$width = Math.ceil($width) + 50;
				$(this).addClass('xxx-' + $width);
			});
			
			$tr.addClass('x' + max);
		});
	}
	

});