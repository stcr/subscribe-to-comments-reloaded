<?php
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}
?>
<div class="postbox">
	<h3><?php _e( 'Update Subscription', 'subscribe-to-comments-reloaded' ) ?></h3>

	<form action="" method="post" id="update_address_form"
		  onsubmit="if (this.sre.value != '<?php _e( 'optional', 'subscribe-to-comments-reloaded' ) ?>') return confirm('<?php _e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ) ?>')">
		<fieldset style="border:0">
			<p><?php _e( 'Post:', 'subscribe-to-comments-reloaded' );
echo ' <strong>' . get_the_title( intval( $_GET['srp'] ) ) . " (" . intval( $_GET['srp'] ) . ")"; ?></strong></p>

			<p class="liquid"><label for='oldsre'><?php _e( 'From', 'subscribe-to-comments-reloaded' ) ?></label>
				<input readonly='readonly' type='text' size='30' name='oldsre' id='oldsre' value='<?php echo esc_attr($_GET['sre']) ?>' />
			</p>

			<p class="liquid"><label for='sre'><?php _e( 'To', 'subscribe-to-comments-reloaded' ) ?></label>
				<input type='text' size='30' name='sre' id='sre' value='<?php esc_attr_e( 'optional', 'subscribe-to-comments-reloaded' ) ?>' style="color:#ccc"
					   onfocus='if (this.value == "<?php _e( 'optional', 'subscribe-to-comments-reloaded' ) ?>") this.value="";this.style.color="#000"'
					   onblur='if (this.value == ""){this.value="<?php _e( 'optional', 'subscribe-to-comments-reloaded' ) ?>";this.style.color="#ccc"}' />
			</p>

			<p class="liquid"><label for='srs'><?php _e( 'Status', 'subscribe-to-comments-reloaded' ) ?></label>
				<select name="srs" id="srs">
					<option value=''><?php _e( 'Keep unchanged', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='Y'><?php _e( 'Active', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='R'><?php _e( 'Replies only', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='C'><?php _e( 'Suspended', 'subscribe-to-comments-reloaded' ) ?></option>
				</select>
				<input type='submit' class='subscribe-form-button' value='<?php esc_attr_e( 'Update', 'subscribe-to-comments-reloaded' ) ?>' />
			</p>
			<input type='hidden' name='sra' value='edit' />
			<input type='hidden' name='srp' value='<?php echo intval( $_GET['srp'] ) ?>' />
		</fieldset>
	</form>
</div>
