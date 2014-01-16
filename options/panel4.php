<?php
// Avoid direct access to this piece of code
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}

$is_html_enabled = (subscribe_reloaded_get_option('enable_html_emails', 'no') == 'yes');

// Update options
if (isset($_POST['options'])){
	$faulty_fields = '';
	if (isset($_POST['options']['from_name']) && !subscribe_reloaded_update_option('from_name', $_POST['options']['from_name'], 'text')) $faulty_fields = __('Sender name','subscribe-reloaded').', ';
	if (isset($_POST['options']['from_email']) && !subscribe_reloaded_update_option('from_email', $_POST['options']['from_email'], 'text')) $faulty_fields = __('Sender email address','subscribe-reloaded').', ';
	if (isset($_POST['options']['notification_subject']) && !subscribe_reloaded_update_option('notification_subject', $_POST['options']['notification_subject'], 'text')) $faulty_fields = __('Notification subject','subscribe-reloaded').', ';
	if (isset($_POST['options']['notification_content']) && !subscribe_reloaded_update_option('notification_content', $_POST['options']['notification_content'], 'text')) $faulty_fields = __('Notification message','subscribe-reloaded').', ';
	if (isset($_POST['options']['double_check_subject']) && !subscribe_reloaded_update_option('double_check_subject', $_POST['options']['double_check_subject'], 'text')) $faulty_fields = __('Double check subject','subscribe-reloaded').', ';
	if (isset($_POST['options']['double_check_content']) && !subscribe_reloaded_update_option('double_check_content', $_POST['options']['double_check_content'], 'text')) $faulty_fields = __('Double check message','subscribe-reloaded').', ';
	if (isset($_POST['options']['management_subject']) && !subscribe_reloaded_update_option('management_subject', $_POST['options']['management_subject'], 'text')) $faulty_fields = __('Management subject','subscribe-reloaded').', ';
	if (isset($_POST['options']['management_content']) && !subscribe_reloaded_update_option('management_content', $_POST['options']['management_content'], 'text')) $faulty_fields = __('Management message','subscribe-reloaded').', ';

	// Display an alert in the admin interface if something went wrong
	echo '<div class="updated fade"><p>';
	if (empty($faulty_fields)){
			_e('Your settings have been successfully updated.','subscribe-reloaded');
	}
	else{
		_e('There was an error updating the following fields:','subscribe-reloaded');
		echo ' <strong>'.substr($faulty_fields,0,-2).'</strong>';
	}
	echo "</p></div>\n";
}
wp_print_scripts( 'quicktags' );
?>
<form action="admin.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=<?php echo $current_panel ?>" method="post">
<h3><?php _e('Options','subscribe-reloaded') ?></h3>
<table class="form-table <?php echo $wp_locale->text_direction ?>">
<tbody>
	<tr>
		<th scope="row"><label for="from_name"><?php _e('Sender name','subscribe-reloaded') ?></label></th>
		<td><input type="text" name="options[from_name]" id="from_name" value="<?php echo subscribe_reloaded_get_option('from_name'); ?>" size="50">
			<div class="description"><?php _e('Name to use for the "from" field when sending a new notification to the user.','subscribe-reloaded'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="from_email"><?php _e('Sender email address','subscribe-reloaded') ?></label></th>
		<td><input type="text" name="options[from_email]" id="from_email" value="<?php echo subscribe_reloaded_get_option('from_email'); ?>" size="50">
			<div class="description"><?php _e('Email address to use for the "from" field when sending a new notification to the user.','subscribe-reloaded'); ?></div></td>
	</tr>
</tbody>
</table>

<h3><?php _e('Messages','subscribe-reloaded') ?></h3>
<table class="form-table <?php echo $wp_locale->text_direction ?>">
<tbody>
	<tr>
		<th scope="row"><label for="notification_subject"><?php _e('Notification subject','subscribe-reloaded') ?></label></th>
		<td><input type="text" name="options[notification_subject]" id="notification_subject" value="<?php echo subscribe_reloaded_get_option('notification_subject'); ?>" size="70">
			<div class="description"><?php _e('Subject of the notification email. Allowed tag: [post_title]','subscribe-reloaded'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="notification_content"><?php _e('Notification message','subscribe-reloaded') ?></label></th>
		<td><?php if($is_html_enabled): ?>
			<input type="button" id="qtbold1" class="button-secondary" onclick="edInsertTag(document.getElementById('notification_content'), 0);" value="<?php _e('Bold') ?>" />
			<input type="button" id="qtitalics1" class="button-secondary" onclick="edInsertTag(document.getElementById('notification_content'), 1);" value="<?php _e('Italic') ?>" />
			<input type="button" id="qtlink1" class="button-secondary" onclick="edInsertLink(document.getElementById('notification_content'), 2);" value="<?php _e('Link') ?>" />
			<input type="button" id="qtimg1" class="button-secondary" onclick="edInsertImage(document.getElementById('notification_content'));" value="<?php _e('Image') ?>" />
			<br/><?php endif ?>
			<textarea name="options[notification_content]" id="notification_content" cols="70" rows="5"><?php echo subscribe_reloaded_get_option('notification_content'); ?></textarea>
			<div class="description" style="padding-top:0"><?php _e('Content of the notification email. Allowed tags: [post_title], [comment_permalink], [comment_author], [comment_content], [post_permalink], [manager_link]','subscribe-reloaded'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="double_check_subject"><?php _e('Double check subject','subscribe-reloaded') ?></label></th>
		<td><input type="text" name="options[double_check_subject]" id="double_check_subject" value="<?php echo subscribe_reloaded_get_option('double_check_subject'); ?>" size="70">
			<div class="description" style="padding-top:0"><?php _e('Subject of the confirmation email. Allowed tag: [post_title]','subscribe-reloaded'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="double_check_content"><?php _e('Double check message','subscribe-reloaded') ?></label></th>
		<td><?php if($is_html_enabled): ?>
			<input type="button" id="qtbold2" class="button-secondary" onclick="edInsertTag(document.getElementById('double_check_content'), 0);" value="<?php _e('Bold') ?>" />
			<input type="button" id="qtitalics2" class="button-secondary" onclick="edInsertTag(document.getElementById('double_check_content'), 1);" value="<?php _e('Italic') ?>" />
			<input type="button" id="qtlink2" class="button-secondary" onclick="edInsertLink(document.getElementById('double_check_content'), 2);" value="<?php _e('Link') ?>" />
			<input type="button" id="qtimg2" class="button-secondary" onclick="edInsertImage(document.getElementById('double_check_content'));" value="<?php _e('Image') ?>" />
			<br/><?php endif ?>
			<textarea name="options[double_check_content]" id="double_check_content" cols="70" rows="5"><?php echo subscribe_reloaded_get_option('double_check_content'); ?></textarea>
			<div class="description" style="padding-top:0"><?php _e('Content of the confirmation email. Allowed tags: [post_permalink], [confirm_link], [post_title], [manager_link]','subscribe-reloaded'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="management_subject"><?php _e('Management subject','subscribe-reloaded') ?></label></th>
		<td><input type="text" name="options[management_subject]" id="management_subject" value="<?php echo subscribe_reloaded_get_option('management_subject'); ?>" size="70">
			<div class="description" style="padding-top:0"><?php _e('Subject of the mail sent to those who request to access their management page. Allowed tag: [blog_name]','subscribe-reloaded'); ?></div></td>
	</tr>
	<tr>
		<th scope="row"><label for="management_content"><?php _e('Management message','subscribe-reloaded') ?></label></th>
		<td><?php if($is_html_enabled): ?>
			<input type="button" id="qtbold3" class="button-secondary" onclick="edInsertTag(document.getElementById('management_content'), 0);" value="<?php _e('Bold') ?>" />
			<input type="button" id="qtitalics3" class="button-secondary" onclick="edInsertTag(document.getElementById('management_content'), 1);" value="<?php _e('Italic') ?>" />
			<input type="button" id="qtlink3" class="button-secondary" onclick="edInsertLink(document.getElementById('management_content'), 2);" value="<?php _e('Link') ?>" />
			<input type="button" id="qtimg3" class="button-secondary" onclick="edInsertImage(document.getElementById('management_content'));" value="<?php _e('Image') ?>" />
			<br/><?php endif ?>
			<textarea name="options[management_content]" id="management_content" cols="70" rows="5"><?php echo subscribe_reloaded_get_option('management_content'); ?></textarea>
			<div class="description" style="padding-top:0"><?php _e('Content of the management email. Allowed tags: [blog_name], [manager_link]','subscribe-reloaded'); ?></div></td>
	</tr>
</tbody>
</table>
<p class="submit"><input type="submit" value="<?php _e('Save Changes') ?>" class="button-primary" name="Submit"></p>
</form>
