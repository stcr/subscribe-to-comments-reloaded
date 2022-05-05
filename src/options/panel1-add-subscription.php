<?php
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}
?>
<div class="postbox">
	<h3><?php _e( 'Add New Subscription', 'subscribe-to-comments-reloaded' ) ?></h3>

	<form action="" method="post" id="update_address_form"
		  onsubmit="if (this.srp.value == '' || this.sre.value == '') return false;">
		<fieldset style="border:0">
			<p>
				<?php
				_e( 'Post:', 'subscribe-to-comments-reloaded' );
				echo ' <strong>' . get_the_title( intval( $_GET['srp'] ) ) . ' (' . intval( $_GET['srp'] ) . ')</strong>';
				?>
			</p>

			<p class="liquid"><label for='sre'><?php _e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
				<?php $sre = isset( $_GET['sre'] ) ? sanitize_text_field( wp_unslash( $_GET['sre'] ) ) : ''; ?>
				<input readonly='readonly' type='text' size='30' name='sre' id='sre' value='<?php echo esc_attr( $sre ); ?>' />
			</p>

			<p class="liquid"><label for='srs'><?php _e( 'Status', 'subscribe-to-comments-reloaded' ) ?></label>
				<select name="srs" id="srs">
					<option value='Y'><?php _e( 'Active', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='R'><?php _e( 'Replies only', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='YC'><?php _e( 'Ask user to confirm', 'subscribe-to-comments-reloaded' ) ?></option>
				</select>
				<input type='submit' class='subscribe-form-button' value='<?php esc_attr_e( 'Update', 'subscribe-to-comments-reloaded' ) ?>' />
			</p>
			<input type='hidden' name='sra' value='add' />
			<input type='hidden' name='srp' value='<?php echo intval( $_GET['srp'] ) ?>' />
		</fieldset>

		<?php wp_nonce_field( 'stcr_add_subscription_nonce', 'stcr_add_subscription_nonce' ); ?>

	</form>
</div>
