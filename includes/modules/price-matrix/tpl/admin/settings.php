<?php
$all_settings = NBT_Price_Matrix_Settings::get_settings();
$options = get_option(NBT_Solutions_Price_Matrix::$plugin_id.'_settings');
$key_id = str_replace('-', '_', NBT_Solutions_Price_Matrix::$plugin_id);

// Save settings data
if(isset($_POST['submit-'.$key_id])){
	$options = array();
	foreach ($all_settings as $key => $value) {
		$id = $value['id'];
		if(isset($_POST[$id])){
			$options[$id] = $_POST[$id];
		}
		
	}
	
	printf( '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>%s</strong></p></div>', __('Settings Saved', 'nbt-plugins') );
	update_option(NBT_Solutions_Price_Matrix::$plugin_id.'_settings', $options);
}

// Check file template
$temp_file = NBT_PRICEMATRIX_PATH . 'tpl/admin/settings/general.php';
$temp_auth = NBT_PRICEMATRIX_PATH . 'tpl/admin/settings/auth.php';
if( nbpm_check_license() && ! isset($_POST['_license']) && ! isset($_GET['tab']) ) {
	if( file_exists($temp_auth) ) {
		$temp_file = $temp_auth;
	}
}

if( file_exists($temp_auth) ) {
	$auth = true;
}

// Display HTML nav tabs
echo '<nav class="nav-tab-wrapper woo-nav-tab-wrapper">';
	$admin_url = admin_url( 'admin.php?page=' . NBT_Solutions_Price_Matrix::$plugin_id );
	if( isset($auth) && nbpm_check_license() && ! isset($_POST['_license']) ) {
		printf( '<a href="%s" class="nav-tab%s">%s</a>',
			$admin_url,
			! isset($_GET['tab']) ? ' nav-tab-active' : '',
			__('Authentication', 'nbt-solution')
		);
	}
	printf( '<a href="%s&amp;tab=settings" class="nav-tab%s">%s</a>',
		$admin_url,
		(! nbpm_check_license() || isset($_POST['_license']) || isset($_GET['tab']) && $_GET['tab'] == 'settings' || ! isset($auth) ) ? ' nav-tab-active' : '',
		__('Settings', 'nbt-solution')
	);
echo '</nav>';

// Display HTML tab content
include_once $temp_file;
?>