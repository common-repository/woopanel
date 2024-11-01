<div class="nb-faqs-container">
	<?php if($data){
		foreach ($data as $key => $faq) {
			$heading = $faq['heading'];
			if(!isset($faq['lists'])){
				$faqs = get_post_meta($heading['faq'], '_nbt_faq', true);
				$faq = $faqs[$heading['id']];
			}
			include WOOPANEL_VIEWS_DIR .'faqs/frontend-repeater.php';
		}
	}?>
</div>