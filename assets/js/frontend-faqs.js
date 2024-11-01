jQuery(document).ready(function($){
	var nbtfaq_js = {
		init: function(){
			$(document).on('click', '.nb-faq-title', this.faq_triggle);
		},
		faq_triggle: function(e){
		    e.preventDefault();

		    if($(this).hasClass('active')){
		    	$(this).removeClass('active');
		    	$(this).closest('li').find('.nb-faq-content').slideUp();

		    }else{
		    	$(this).addClass('active');
		    	$(this).closest('li').find('.nb-faq-content').slideDown();
		    }
		}
	}
	
	nbtfaq_js.init();
});	



