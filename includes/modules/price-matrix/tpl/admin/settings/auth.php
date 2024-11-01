<form action="<?php echo NBT_Solutions_Price_Matrix::$redirectURL;?>" method="POST">
	<p style="font-style: italic;"><?php _e('Please buy PRO version, if you purchased this product on CodeCanyhon, press button!', 'netbase_pricematrix');?></p>
	<input type="submit" id="wppm_connect_envato" value="<?php _e('Connect Envato', 'nbt-solution');?>" class="button button-primary">
	<input type="hidden" name="redirect_uri" value="<?php echo admin_url( 'admin.php?page=' . NBT_Solutions_Price_Matrix::$plugin_id );?>" />
</form>