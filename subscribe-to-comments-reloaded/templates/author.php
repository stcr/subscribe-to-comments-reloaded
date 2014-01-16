<?php
// Avoid direct access to this piece of code
if (!function_exists('add_action')){
	header('Location: /');
	exit;
}

global $wp_subscribe_reloaded;

ob_start();
if (!empty($_POST['email_list'])){
	$email_list = array();
	foreach($_POST['email_list'] as $a_email){
		if (!in_array($a_email, $email_list))
			$email_list[] = urldecode($a_email);
	}

	$action = !empty($_POST['sra'])?$_POST['sra']:(!empty($_GET['sra'])?$_GET['sra']:'');
	switch($action){
		case 'delete':
			$rows_affected = $wp_subscribe_reloaded->delete_subscriptions($post_ID, $email_list);
			echo '<p class="updated">'.__('Subscriptions deleted:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'suspend':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_ID, $email_list, 'C');
			echo '<p class="updated">'.__('Subscriptions suspended:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'activate':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_ID, $email_list, '-C');
			echo '<p class="updated">'.__('Subscriptions activated:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'force_y':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_ID, $email_list, 'Y');
			echo '<p class="updated">'.__('Subscriptions updated:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'force_r':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_ID, $email_list, 'R');
			echo '<p class="updated">'.__('Subscriptions updated:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		default:
			break;
	}
}
$message = html_entity_decode(stripslashes(get_option('subscribe_reloaded_author_text')), ENT_COMPAT, 'UTF-8');
if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))
	$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($message);
echo "<p>$message</p>";
?>

<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" id="email_list_form" name="email_list_form" onsubmit="if(this.sra[0].checked) return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded') ?>')">
<fieldset style="border:0">
<?php
	$subscriptions = $wp_subscribe_reloaded->get_subscriptions('post_id', 'equals', $post_ID, 'dt', 'ASC');
	if (is_array($subscriptions) && !empty($subscriptions)){
		echo '<p id="subscribe-reloaded-title-p">'.__('Title','subscribe-reloaded').': <strong>'.$target_post->post_title.'</strong></p>';
		echo '<p id="subscribe-reloaded-legend-p">'.__('Legend: Y = all comments, R = replies only, C = inactive', 'subscribe-reloaded').'</p>';
		echo '<ul id="subscribe-reloaded-list">';
		foreach($subscriptions as $i => $a_subscription)
			echo "<li><input type='checkbox' name='email_list[]' value='".urlencode($a_subscription->email)."' id='e_$i'/>
				<label for='e_$i'><span class='subscribe-column-1'>$a_subscription->dt</span>
					<span class='subscribe-separator subscribe-separator-1'>&mdash;</span>
					<span class='subscribe-column-2'>$a_subscription->status</span>
					<span class='subscribe-separator subscribe-separator-2'>&mdash;</span>
					<span class='subscribe-column-3'>$a_subscription->email</span></label></li>\n";
		echo '</ul>';
		echo '<p id="subscribe-reloaded-select-all-p"><a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'email_list_form\'].elements[\'email_list[]\'];c=t.length;if(!c){t.checked=true}else{for(var i=0;i<c;i++){t[i].checked=true}};return false;">'.__('Select all','subscribe-reloaded').'</a> ';
		echo '<a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'email_list_form\'].elements[\'email_list[]\'];c=t.length;if(!c){t.checked=!t.checked}else{for(var i=0;i<c;i++){t[i].checked=false}};return false;">'.__('Invert selection','subscribe-reloaded').'</a></p>';
		echo '<p id="subscribe-reloaded-action-p">'.__('Action:','subscribe-reloaded').'
			<input type="radio" name="sra" value="delete" id="action_type_delete" /> <label for="action_type_delete">'.__('Delete','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp; 
			<input type="radio" name="sra" value="suspend" id="action_type_suspend" checked="checked" /> <label for="action_type_suspend">'.__('Suspend','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sra" value="force_y" id="action_type_force_y" /> <label for="action_type_force_y">'.__('Set to Y','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sra" value="activate" id="action_type_activate" /> <label for="action_type_activate">'.__('Activate','subscribe-reloaded').'</label></p>';
		echo '<p id="subscribe-reloaded-update-p"><input type="submit" class="subscribe-form-button" value="'.__('Update subscriptions','subscribe-reloaded').'" /><input type="hidden" name="srp" value="'.intval($post_ID).'"/></p>';
		
	}
	else{
		echo '<p>'.__('No subscriptions match your search criteria.', 'subscribe-reloaded').'</p>';
	}
?>
</fieldset>
</form>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
?>