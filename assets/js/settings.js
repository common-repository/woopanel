jQuery(document).ready(function($){
    $.nbMediaUploader({
        action : 'get_image',
        uploaderTitle : 'Image',
        btnSelect : 'Set Image',
        btnSetText : 'Set Image',
        multiple : false,
    });
	
	$(document).on('click', '#m_nav .m-nav__link', function() {
		var $tab = $(this).attr('href');
		Cookies.set('setting_current_tab', $tab);
	});
	
	var currentTab = Cookies.get('setting_current_tab');
	if( typeof currentTab != 'undefined'  ) {
		$('#m_nav a[href="' + currentTab + '"]').trigger('click');
	}
	
	if( jQuery().timepicker ) {
		$('input.timepicker').timepicker({});
	}
	
});