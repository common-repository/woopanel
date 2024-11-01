var WooPanel_FAQs = {

    xhr_view: null,

	init: function() {
		/**
		 * Event actions
		 */
		jQuery(document).on('click', '.nbt-heading-btn-add', this.addHeading);
		jQuery(document).on('click', '.repeater-heading', this.openRow);
		jQuery(document).on('click', '.nbt-section-btn-add', this.addSection);
		jQuery(document).on('keyup', '.faq_title', this.liveTitle);
		jQuery(document).on('mouseenter', '.nbt-repeater-order', this.dragdrop);
		
		jQuery(document).on('click', '.faq-action-row .button-link-delete', this.deleteSection);
		jQuery(document).on('click', '.faq-action-row .widget-control-close', this.closeSection);
	},
	
	openRow: function(e) {
		e.preventDefault();
		
		var $row = jQuery(this).closest('.repeater-row');
		if($row.hasClass('open')){
			$row.removeClass('open');
			$row.find('.repeater-content').hide();
		}else{
			$row.addClass('open');
			$row.find('.repeater-content').show();
		}
	},
	
	addHeading: function(e) {
		e.preventDefault();
		
		jQuery('#wpl-faq-empty').remove();
		
		var $count	  = jQuery('.row-heading').length,
			$template = jQuery('#nbt-heading-template').html(),
			$template = $template.replace("faq_title[]", "faq_title[" + $count + "]");

		jQuery('#nbt-repeater-wrap').append('<div id="heading-' + $count + '" class="heading-box" data-id="' + $count + '">' + $template + '</div>');
	},
	
	addSection: function() {
		var count_heading = jQuery('.row-heading').length;
		if( count_heading <= 0) {
			alert('Please add heading first!');
			return;
		}

		var $count = jQuery('.repeater-row').length;
		var $id_heading = jQuery('#nbt-repeater-wrap > .heading-box:last-child').attr('data-id');
		var $template = jQuery('#nbt-repeater-template').html();
		$template = $template.replace( 'faq_title[]', "faq_title[" + $id_heading + "][]" );
		$template = nbt_editor.str_replace( 'id_repeater', 'repeater-row-' + $count, $template );
		jQuery('#repeater-row-' + $count).attr('data-id' + $count);
		
		jQuery('#nbt-repeater-wrap > .heading-box:last-child').append($template);
		jQuery('#repeater-row-' + $count).find('.wp-editor-area').attr('name', 'faq_content[' + $id_heading + '][]');

		nbt_editor.init('repeater-row-' + $count);
	},
	
	deleteSection: function() {
		jQuery(this).closest('.repeater-row').remove();
	},
	
	closeSection: function() {
		var $row = jQuery(this).closest('.repeater-row');
		$row.removeClass('open');
		$row.find('.repeater-content').hide();
	},
	
	liveTitle: function() {
		var text = jQuery(this).val();
		jQuery(this).closest('.repeater-row').find('.nbt-repeater-title').text(': ' + text);	
	},

	dragdrop: function() {
		// sortable
		jQuery('#nbt-repeater-wrap').sortable({
			items: '.repeater-row:not(.row-heading)',
			handle: 'div.nbt-repeater-order',
			forceHelperSize: true,
			forcePlaceholderSize: true,
			scroll: true,
			start: function(event, ui) {

				// acf.do_action('sortstart', ui.item, ui.placeholder);

			},
			stop: function(event, ui) {

				// render
				// self.render();

				// acf.do_action('sortstop', ui.item, ui.placeholder);

			},
			update: function(event, ui) {

				// trigger change
				//self.$input.trigger('change');

			}

		});
	}
}


var nbt_editor = {

	toolbars: {},


	init: function(id){
		// bail early if no tinyMCEPreInit (needed by both tinymce and quicktags)
		if( typeof tinyMCEPreInit === 'undefined' ) return;

		var $wrap = jQuery('#' + id).find('.nbt-editor-wrap');
		var toolbar = $wrap.attr('data-toolbar');

		var old_id = 'nbt-editor-' + $wrap.closest('.nbt-field-wysiwyg').attr('data-id'),
			new_id = nbt_editor.get_uniqid('nbt-editor-'),
			html = $wrap[0].outerHTML;

		// replace
		html = nbt_editor.str_replace( old_id, new_id, html );
		// swap

		jQuery('#' + id).find('.nbt-editor-input').html( html );

		this.initialize_tinymce(new_id);
		//this.initialize_quicktags(new_id);
	},
	initialize_tinymce: function(id){
		// bail early if no tinymce
		if( typeof tinymce === 'undefined' ) return;
		
		// bail early if no tinyMCEPreInit.mceInit
		if( typeof tinyMCEPreInit.mceInit === 'undefined' ) return;

		// vars
		var mceInit = this.get_mceInit(id);

		// append
		tinyMCEPreInit.mceInit[ mceInit.id ] = mceInit;

		// bail early if not visual active
		if( !jQuery('#' + id).closest('.nbt-editor-wrap').hasClass('tmce-active') ) return;

		// initialize
		try {
			
			// init
			tinymce.init( mceInit );
			
			
			// vars
			var ed = tinyMCE.get( mceInit.id );
			
	
		} catch(e){}

	},
	initialize_quicktags: function(id){
		
		// bail early if no quicktags
		if( typeof quicktags === 'undefined' ) return;
		
		
		// bail early if no tinyMCEPreInit.qtInit
		if( typeof tinyMCEPreInit.qtInit === 'undefined' ) return;
		
		
		// vars
		var qtInit = this.get_qtInit(id);
		
		
		// append
		tinyMCEPreInit.qtInit[ qtInit.id ] = qtInit;
		
		
		// initialize
		try {
			
			// init
			var qtag = quicktags( qtInit );
			
			
			// buttons
			this._buttonsInit( qtag );

			jQuery('#' + id).prop("disabled", false);;

		} catch(e){}
		
	},
	get_mceInit : function(id){
		var itoolbar = jQuery('#wp-' + id + '-wrap').attr('data-toolbar');


		
		// reference
		var $field = jQuery('#' + id).closest('.repeater-row');

		// vars
		var toolbar = this.get_toolbar( itoolbar ),
			mceInit = jQuery.extend({}, tinyMCEPreInit.mceInit.content);


		// selector
		mceInit.selector = '#' + id;
		
		
		// id
		mceInit.id = id; // tinymce v4
		mceInit.elements = id; // tinymce v3
		
		
		// toolbar
		if( toolbar ) {
			
			var k = (tinymce.majorVersion < 4) ? 'theme_advanced_buttons' : 'toolbar';
			
			for( var i = 1; i < 5; i++ ) {
				
				mceInit[ k + i ] = this.isset(toolbar, i) ? toolbar[i] : '';
				
			}

			
		}
		
		
		
		// events
		if( tinymce.majorVersion < 4 ) {
			
			mceInit.setup = function( ed ){
				
				ed.onInit.add(function(ed, event) {
					
					// focus
					jQuery(ed.getBody()).on('focus', function(){
				
						acf.validation.remove_error( $field );
						
					});
					
					jQuery(ed.getBody()).on('blur', function(){
						
						// update the hidden textarea
						// - This fixes a bug when adding a taxonomy term as the form is not posted and the hidden textarea is never populated!
		
						// save to textarea	
						ed.save();
						
						
						// trigger change on textarea
						$field.find('textarea').trigger('change');
						
					});
				
				});
				
			};
		
		} else {
		
			mceInit.setup = function( ed ){
				
				ed.on('focus', function(e) {
			
					
					
				});
				
				ed.on('change', function(e) {
					
					// save to textarea	
					ed.save();
					
					
					$field.find('textarea').trigger('change');
					
				});
									
			};
		
		}
		
		
		// disable wp_autoresize_on (no solution yet for fixed toolbar)
		mceInit.wp_autoresize_on = false;
	
		// return
		return mceInit;
		
	},
	get_qtInit : function(id){
			
		// vars
		var qtInit = jQuery.extend({}, tinyMCEPreInit.qtInit.content);
		
		
		// id
		qtInit.id = id;
		
		
		// return
		return qtInit;
		
	},
	_buttonsInit: function( ed ) {
		var defaults = ',strong,em,link,block,del,ins,img,ul,ol,li,code,more,close,';

		canvas = ed.canvas;
		name = ed.name;
		settings = ed.settings;
		html = '';
		theButtons = {};
		use = '';

		// set buttons
		if ( settings.buttons ) {
			use = ','+settings.buttons+',';
		}

		for ( i in edButtons ) {
			if ( !edButtons[i] ) {
				continue;
			}

			id = edButtons[i].id;
			if ( use && defaults.indexOf( ',' + id + ',' ) !== -1 && use.indexOf( ',' + id + ',' ) === -1 ) {
				continue;
			}

			if ( !edButtons[i].instance || edButtons[i].instance === inst ) {
				theButtons[id] = edButtons[i];

				if ( edButtons[i].html ) {
					html += edButtons[i].html(name + '_');
				}
			}
		}

		if ( use && use.indexOf(',fullscreen,') !== -1 ) {
			theButtons.fullscreen = new qt.FullscreenButton();
			html += theButtons.fullscreen.html(name + '_');
		}


		if ( 'rtl' === document.getElementsByTagName('html')[0].dir ) {
			theButtons.textdirection = new qt.TextDirectionButton();
			html += theButtons.textdirection.html(name + '_');
		}

		ed.toolbar.innerHTML = html;
		ed.theButtons = theButtons;
		
	},
	get_toolbar : function( name ){
		this.toolbars.basic = {};
		this.toolbars.full = {};
		this.toolbars.basic[1] = 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,unlink,fullscreen';
		this.toolbars.full[1] = 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv';
		this.toolbars.full[2] = 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help';
		
		// bail early if toolbar doesn't exist
		if( typeof this.toolbars[ name ] !== 'undefined' ) {
			return this.toolbars[ name ];
		}
		
		// return
		return false;
		
	},
	isset : function(){
		
		var a = arguments,
			l = a.length,
			c = null,
			undef;
		
		if (l === 0) {
			throw new Error('Empty isset');
		}
		
		c = a[0];
		
		for (i = 1; i < l; i++) {
			
			if (a[i] === undef || c[ a[i] ] === undef) {
				return false;
			}
			
			c = c[ a[i] ];
			
		}
		
		return true;	
		
	},
	str_replace: function( search, replace, subject ) {
		return subject.split(search).join(replace);
	},
	get_uniqid : function( prefix, more_entropy ){
	
		// + original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// + revised by: Kankrelune (http://www.webfaktory.info/)
		// % note 1: Uses an internal counter (in php_js global) to avoid collision
		// * example 1: uniqid();
		// * returns 1: 'a30285b160c14'
		// * example 2: uniqid('foo');
		// * returns 2: 'fooa30285b1cd361'
		// * example 3: uniqid('bar', true);
		// * returns 3: 'bara20285b23dfd1.31879087'
		if (typeof prefix === 'undefined') {
			prefix = "";
		}
		
		var retId;
		var formatSeed = function (seed, reqWidth) {
			seed = parseInt(seed, 10).toString(16); // to hex str
			if (reqWidth < seed.length) { // so long we split
				return seed.slice(seed.length - reqWidth);
			}
			if (reqWidth > seed.length) { // so short we pad
				return Array(1 + (reqWidth - seed.length)).join('0') + seed;
			}
			return seed;
		};
		
		// BEGIN REDUNDANT
		if (!this.php_js) {
			this.php_js = {};
		}
		// END REDUNDANT
		if (!this.php_js.uniqidSeed) { // init seed with big random int
			this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
		}
		this.php_js.uniqidSeed++;
		
		retId = prefix; // start with prefix, add current milliseconds hex string
		retId += formatSeed(parseInt(new Date().getTime() / 1000, 10), 8);
		retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
		if (more_entropy) {
			// for more entropy we add a float lower to 10
			retId += (Math.random() * 10).toFixed(8).toString();
		}
		
		return retId;
	}
}

jQuery(document).ready(function($) {
	WooPanel_FAQs.init();
});