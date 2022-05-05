<?php
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}
?>
<div class="postbox">
	<h3><?php esc_html_e( 'Update Subscription', 'subscribe-to-comments-reloaded' ) ?></h3>

	<form action="" method="post" id="update_address_form"
		  onsubmit="if (this.sre.value != '<?php esc_attr_e( 'optional', 'subscribe-to-comments-reloaded' ) ?>') return confirm('<?php esc_attr_e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ) ?>')">
		<fieldset style="border:0">
			<p>
				<?php
				esc_html_e( 'Post:', 'subscribe-to-comments-reloaded' );
				echo ' <strong>' . esc_html( get_the_title( intval( $_GET['srp'] ) ) ) . ' (' . intval( $_GET['srp'] ) . ')</strong>';
				?>
			</p>

			<p class="liquid"><label for='oldsre'><?php esc_html_e( 'From', 'subscribe-to-comments-reloaded' ) ?></label>
				<?php $sre = isset( $_GET['sre'] ) ? sanitize_text_field( wp_unslash( $_GET['sre'] ) ) : ''; ?>
				<input readonly='readonly' type='text' size='30' name='oldsre' id='oldsre' value='<?php echo esc_attr( $sre ); ?>' />
			</p>

			<p class="liquid"><label for='sre'><?php esc_html_e( 'To', 'subscribe-to-comments-reloaded' ) ?></label>
				<input type='text' size='30' name='sre' id='sre' value='<?php esc_attr_e( 'optional', 'subscribe-to-comments-reloaded' ) ?>' style="color:#ccc"
					   onfocus='if (this.value == "<?php esc_attr_e( 'optional', 'subscribe-to-comments-reloaded' ) ?>") this.value="";this.style.color="#000"'
					   onblur='if (this.value == ""){this.value="<?php esc_attr_e( 'optional', 'subscribe-to-comments-reloaded' ) ?>";this.style.color="#ccc"}' />
			</p>

			<p class="liquid"><label for='srs'><?php esc_html_e( 'Status', 'subscribe-to-comments-reloaded' ) ?></label>
				<select name="srs" id="srs">
					<option value=''><?php esc_html_e( 'Keep unchanged', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='Y'><?php esc_html_e( 'Active', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='R'><?php esc_html_e( 'Replies only', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='C'><?php esc_html_e( 'Suspended', 'subscribe-to-comments-reloaded' ) ?></option>
				</select>
				<input type='submit' class='subscribe-form-button' value='<?php esc_attr_e( 'Update', 'subscribe-to-comments-reloaded' ) ?>' />
			</p>
			<input type='hidden' name='sra' value='edit' />
			<input type='hidden' name='srp' value='<?php echo intval( $_GET['srp'] ) ?>' />
			<?php wp_nonce_field( 'stcr_edit_subscription_nonce', 'stcr_edit_subscription_nonce' ); ?>
		</fieldset>
	</form>
</div>
