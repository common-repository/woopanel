<form action="<?php echo admin_url('admin.php?page='.NBT_Solutions_Price_Matrix::$plugin_id) ?>" method="post" >
	<table class="form-table">
		<tbody>
		<?php foreach ($all_settings as $key => $set) {
			echo NBT_Solutions_Metabox::show_field($set, $options);
		}?>
		</tbody>
	</table>
	<div class="submit">
		<?php wp_nonce_field( 'plugin-settings' ) ?>
		<input type="submit" name="submit-<?php echo $key_id;?>" value="<?php _e('Save Changes', 'nbt-solution-core') ?>" class="button-primary" />
	</div>
</form>