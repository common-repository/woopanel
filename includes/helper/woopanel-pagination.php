<?php

function woopanel_paginate_links( $args ) {
	$old = [
		"page-numbers'",
		"page-numbers",
		"current'", 
		"next", 
		"prev"
	];

	$new = [
		"m-datatable__pager-link m-datatable__pager-link-number'", 
		"m-datatable__pager-link", 
		"m-datatable__pager-link--active'", 
		"m-datatable__pager-link--next", 
		"m-datatable__pager-link--prev"
	];

	$page_links = paginate_links( $args );

	if(!empty($page_links)) {
		echo '<ul class="m-datatable__pager-nav">';
		foreach ($page_links as $item) {
			echo '<li>'. str_replace($old, $new, $item) .'</li>';
		}
		echo '</ul>';
	}
}

function woopanel_paginate_text($pagenum, $limit, $total_items){
	$max_num_pages = ceil( $total_items/$limit );
	if($max_num_pages > 1) {
		printf('<span class="m-datatable__pager-detail">Displaying %s - %s of %s records</span>',
			(($pagenum-1) * $limit)+1,
			($pagenum * $limit) > $total_items ? $total_items : $pagenum * $limit,
			$total_items );
	} else {
		printf('<span class="m-datatable__pager-detail">Displaying %s records</span>',
			$total_items );
	}
}
