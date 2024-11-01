<div class="m-portlet">
	<div class="m-portlet__head">
		<div class="m-portlet__head-caption">
			<div class="m-portlet__head-title">
				<h3 class="m-portlet__head-text"><?php _e('FAQ Section', 'woopanel');?></h3>
			</div>
		</div>
	</div>
	<div class="m-portlet__body">
	<div id="nbt-repeater">
		<div id="nbt-repeater-wrap">
			<?php if(!empty($data) && is_array($data)){
				foreach ($data as $key => $value) {
					if(isset($value['heading'])){?>
					<div id="heading-<?php echo $key;?>" class="heading-box" data-id="<?php echo $key;?>">
						<div class="repeater-row row-heading">
							<div class="nbt-repeater-order">
								<p>
									<label for="nbt-repeater-content-title"><?php _e('Heading', 'nbt-solution');?>:</label>
									<input class="form-control m-input widefat heading_title" id="nbt-heading-content-title" name="faq_heading[]" type="text" value="<?php echo $value['heading'];?>">
								</p>
								<div class="faq-action-row wp-core-ui">
									<button type="button" class="button-link button-link-delete widget-control-remove">Delete</button> |
									<button type="button" class="button-link widget-control-close">Close</button>
								</div>
							</div>
						</div>
						<?php if(isset($value['lists'])) {
							foreach ($value['lists'] as $kl => $vl):?>
								<div class="repeater-row" id="repeater-row-<?php echo $key; ?>">
									<div class="repeater-heading nbt-repeater-order">
										Title<span class="nbt-repeater-title">: <?php echo $vl['faq_title']; ?></span>
										<button type="button" class="nbt-repeater-arrow" aria-expanded="false">
											<span class="nbt-toggle-indicator" aria-hidden="true"></span>
										</button>
									</div>

									<div class="repeater-content">
										<p>
											<label for="nbt-repeater-content-title"><?php _e('Title', 'nbt-solution'); ?>
												:</label>
											<input class="form-control m-input widefat faq_title" id="nbt-repeater-content-title"
												name="faq_title[<?php echo $key;?>][]" type="text" value="<?php echo $vl['faq_title']; ?>">
										</p>
										<p>
											<label for="nbt-repeater-content-title"><?php _e('Content', 'nbt-solution'); ?>
												:</label>

											<?php
											$id = md5($key . $kl);
											$settings = array(
												'media_buttons' => false,
												'quicktags' => array('buttons' => 'em,strong,link',),
												'textarea_name' => 'faq_content['.$key.'][]',//name you want for the textarea
												'quicktags' => false,
												'tinymce' => array(
													'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv',
													'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help'
												),
												'autoresize_min_height' => 250,
												'wp_autoresize_on'      => true,
											);
											
											wp_editor($vl['faq_content'], $id, $settings);
											?>
										</p>
										<div class="faq-action-row wp-core-ui">
											<button type="button"
													class="button-link button-link-delete widget-control-remove">Delete
											</button>
											|
											<button type="button" class="button-link widget-control-close">Close</button>
										</div>
									</div>
								</div>
							<?php endforeach;
						}?>
					</div>
					<?php
					}
				}
			}else {?>
			<div id="wpl-faq-empty" class="alert alert-danger fade show" role="alert">
				<div class="alert-text"><?php _e('Please add the heading before!', 'woopanel');?></div>
				<div class="alert-close">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true"><i class="la la-close"></i></span>
					</button>
				</div>
			</div>
			<?php }?>
		</div>
	</div>
	</div>
	<div class="m-portlet__foot kt-hidden">
		<div class="row">
			<div class="col-lg-6">
				<button type="button" class="btn btn-primary nbt-heading-btn-add"><?php _e('Add Heading', 'woopanel');?></button>
				<button type="button" class="btn btn-default nbt-section-btn-add"><?php _e('Add FAQ', 'woopanel');?></button>
			</div>
		</div>
	</div>
</div>



<script id="nbt-repeater-template" type="text/template">
	<div class="repeater-row" id="id_repeater">
		<div class="repeater-heading nbt-repeater-order">
			Title<span class="nbt-repeater-title">: </span>
			<button type="button" class="nbt-repeater-arrow" aria-expanded="false">
				<span class="nbt-toggle-indicator" aria-hidden="true"></span>
			</button>
		</div>

		<div class="repeater-content">
			<p>
				<label for="nbt-repeater-content-title"><?php _e('Title', 'nbt-solution');?>:</label>
				<input class="form-control m-input widefat faq_title" id="nbt-repeater-content-title" name="faq_title[]" type="text" value="">
			</p>
			<p>
				<label for="nbt-repeater-content-title"><?php _e('Content', 'nbt-solution');?>:</label>

				<div class="acf-field nbt-field-wysiwyg acf-field-59ae0d6039d7e" data-name="editor" data-type="wysiwyg" data-id="59ae0d6039d7e">
					<div class="nbt-editor-input">
						<div id="wp-nbt-editor-59ae0d6039d7e-wrap" class="nbt-editor-wrap wp-core-ui wp-editor-wrap tmce-active" data-toolbar="full">
							<div id="wp-nbt-editor-59ae0d6039d7e-editor-tools" class="wp-editor-tools hide-if-no-js">
								<div id="wp-nbt-editor-59ae0d6039d7e-media-buttons" class="wp-media-buttons">
									<button type="button" class="button insert-media add_media" data-editor="nbt-editor-59ae0d6039d7e"><span class="wp-media-buttons-icon"></span> Add Media</button>
								</div>
								<div class="wp-editor-tabs">
									<button id="nbt-editor-59ae0d6039d7e-tmce" class="wp-switch-editor switch-tmce" data-wp-editor-id="nbt-editor-59ae0d6039d7e" type="button">Visual</button>
								</div>
							</div>
							<div id="wp-nbt-editor-59ae0d6039d7e-editor-container" class="wp-editor-container">
								<textarea id="nbt-editor-59ae0d6039d7e" class="wp-editor-area" name="faq_content[]" style="height:250px;" disabled="" aria-hidden="false"></textarea>
							</div>
							<div class="uploader-editor">
								<div class="uploader-editor-content">
									<div class="uploader-editor-title">Drop files to upload</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</p>
			<div class="faq-action-row wp-core-ui">
				<button type="button" class="button-link button-link-delete widget-control-remove">Delete</button> | 
				<button type="button" class="button-link widget-control-close">Close</button>
			</div>
		</div>
	</div>
</script>

<script id="nbt-heading-template" type="text/template">
	<div class="repeater-row row-heading">
		<div class="nbt-repeater-order">
			<p>
				<label for="nbt-repeater-content-title"><?php _e('Heading', 'nbt-solution');?>:</label>
				<input class="form-control m-input widefat heading_title" name="faq_heading[]" type="text" value="" placeholder="<?php _e('Enter heading title', 'woopanel');?>">
			</p>
			<div class="faq-action-row wp-core-ui">
				<button type="button" class="button-link button-link-delete widget-control-remove">Delete</button> | 
				<button type="button" class="button-link widget-control-close">Close</button>
			</div>
		</div>
	</div>
</script>