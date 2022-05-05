<?php
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}
?>
<div class="postbox">
	<h3><?php esc_html_e( 'Add New Subscription', 'subscribe-to-comments-reloaded' ) ?></h3>

	<form action="" method="post" id="update_address_form"
		  onsubmit="if (this.srp.value == '' || this.sre.value == '') return false;">
		<fieldset style="border:0">
			<p>
				<?php
				esc_html_e( 'Post:', 'subscribe-to-comments-reloaded' );
				echo ' <strong>' . esc_html( get_the_title( intval( $_GET['srp'] ) ) ) . ' (' . intval( $_GET['srp'] ) . ')</strong>';
				?>
			</p>

			<p class="liquid"><label for='sre'><?php esc_html_e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
				<?php $sre = isset( $_GET['sre'] ) ? sanitize_text_field( wp_unslash( $_GET['sre'] ) ) : ''; ?>
				<input readonly='readonly' type='text' size='30' name='sre' id='sre' value='<?php echo esc_attr( $sre ); ?>' />
			</p>

			<p class="liquid"><label for='srs'><?php esc_html_e( 'Status', 'subscribe-to-comments-reloaded' ) ?></label>
				<select name="srs" id="srs">
					<option value='Y'><?php esc_html_e( 'Active', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='R'><?php esc_html_e( 'Replies only', 'subscribe-to-comments-reloaded' ) ?></option>
					<option value='YC'><?php esc_html_e( 'Ask user to confirm', 'subscribe-to-comments-reloaded' ) ?></option>
				</select>
				<input type='submit' class='subscribe-form-button' value='<?php esc_attr_e( 'Update', 'subscribe-to-comments-reloaded' ) ?>' />
			</p>
			<input type='hidden' name='sra' value='add' />
			<input type='hidden' name='srp' value='<?php echo intval( $_GET['srp'] ) ?>' />
		</fieldset>

		<?php wp_nonce_field( 'stcr_add_subscription_nonce', 'stcr_add_subscription_nonce' ); ?>

	</form>
</div>
