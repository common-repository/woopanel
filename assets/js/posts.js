jQuery(document).ready(function($){
    $.nbMediaUploader({
        target : '#featured_image_container',
        action : 'get_featured',
        inputId : '#_thumbnail_id',
        uploaderTitle : 'Featured Image',
        btnSelect : 'Set featured image',
        btnSetId : '#set-post-thumbnail',
        btnSetText : 'Set featured image',
        btnRemoveId : '#remove-post-thumbnail',
        multiple : false,
    });

    $.nbMediaUploader({
        uploaderTitle : 'Add images to gallery',
        btnSelect : 'Add to gallery',
        target : '#gallery_images_container',
        btnSetId : '#add_gallery_images',
        action: 'get_gallery',
        inputId : '#_image_gallery',
        multiple : 'add',
    });
	
	if( $('.tax_tags').length > 0 ) {
		$.nbTagsBox({
			inputId: '#' + $('.tax_tags').attr('data-id')
		});
	}
});