<?php
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}
?>
<div class="postbox">
<h3><?php _e('Update Subscription','subscribe-reloaded') ?></h3>
<form action="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=1" method="post" id="update_address_form"
	onsubmit="if (this.sre.value != '<?php _e('optional','subscribe-reloaded') ?>') return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded') ?>')">
<fieldset style="border:0">
<p><?php _e('Post:','subscribe-reloaded'); echo ' <strong>'.get_the_title(intval($_GET['srp']))." ({$_GET['srp']})"; ?></strong></p>
<p class="liquid"><label for='oldsre'><?php _e('From','subscribe-reloaded') ?></label> <input readonly='readonly' type='text' size='30' name='oldsre' id='oldsre' value='<?php echo $_GET['sre'] ?>' /></p>
<p class="liquid"><label for='sre'><?php _e('To','subscribe-reloaded') ?></label> <input type='text' size='30' name='sre' id='sre' value='<?php _e('optional','subscribe-reloaded') ?>' style="color:#ccc"
		onfocus='if (this.value == "<?php _e('optional','subscribe-reloaded') ?>") this.value="";this.style.color="#000"'
		onblur='if (this.value == ""){this.value="<?php _e('optional','subscribe-reloaded') ?>";this.style.color="#ccc"}'/></p>
<p class="liquid"><label for='srs'><?php _e('Status','subscribe-reloaded') ?></label>
	<select name="srs" id="srs">
		<option value=''><?php _e('Keep unchanged','subscribe-reloaded') ?></option>
		<option value='Y'><?php _e('Active','subscribe-reloaded') ?></option>
		<option value='R'><?php _e('Replies only','subscribe-reloaded') ?></option>
		<option value='C'><?php _e('Suspended','subscribe-reloaded') ?></option>
	</select> <input type='submit' class='subscribe-form-button' value='<?php _e('Update','subscribe-reloaded') ?>' /></p>
<input type='hidden' name='sra' value='edit'/>
<input type='hidden' name='srp' value='<?php echo intval($_GET['srp']) ?>'/>
</fieldset>
</form>
</div>