<?php
// Avoid direct access to this piece of code
if (!function_exists('add_action')){
	header('Location: /');
	exit;
}

global $wp_subscribe_reloaded;

ob_start();
if (!empty($_POST['post_list'])){
	$post_list = array();
	foreach($_POST['post_list'] as $a_post_id){
		if (!in_array($a_post_id, $post_list))
			$post_list[] = intval($a_post_id);
	}

	$action = !empty($_POST['sra'])?$_POST['sra']:(!empty($_GET['sra'])?$_GET['sra']:'');
	switch($action){
		case 'delete':
			$rows_affected = $wp_subscribe_reloaded->delete_subscriptions($post_list, $email);
			echo '<p class="updated">'.__('Subscriptions deleted:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'suspend':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, 'C');
			echo '<p class="updated">'.__('Subscriptions suspended:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'activate':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, '-C');
			echo '<p class="updated">'.__('Subscriptions activated:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		case 'force_r':
			$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email, 'R');
			echo '<p class="updated">'.__('Subscriptions updated:', 'subscribe-reloaded')." $rows_affected</p>";
			break;
		default:
			break;
	}
}
$message = html_entity_decode(stripslashes(get_option('subscribe_reloaded_user_text')), ENT_COMPAT, 'UTF-8');
if(function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage'))
	$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($message);
echo "<p>$message</p>";
?>

<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="post" id="post_list_form" name="post_list_form" onsubmit="if(this.sra[0].checked) return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded') ?>')">
<fieldset style="border:0">
<?php
	$subscriptions = $wp_subscribe_reloaded->get_subscriptions('email', 'equals', $email, 'dt', 'DESC');
	if (is_array($subscriptions) && !empty($subscriptions)){
		echo '<p id="subscribe-reloaded-email-p">'.__('Email','subscribe-reloaded').': <strong>'.$email.'</strong></p>';
		echo '<p id="subscribe-reloaded-legend-p">'.__('Legend: Y = all comments, R = replies only, C = inactive', 'subscribe-reloaded').'</p>';
		echo '<ul id="subscribe-reloaded-list">';
		foreach($subscriptions as $a_subscription){
			$permalink = get_permalink($a_subscription->post_id);
			$title = get_the_title($a_subscription->post_id);
			echo "<li><label for='post_{$a_subscription->post_id}'><input type='checkbox' name='post_list[]' value='{$a_subscription->post_id}' id='post_{$a_subscription->post_id}'/> <span class='subscribe-column-1'>$a_subscription->dt</span> <span class='subscribe-separator subscribe-separator-1'>&mdash;</span> <span class='subscribe-column-2'>{$a_subscription->status}</span> <span class='subscribe-separator subscribe-separator-2'>&mdash;</span> <a class='subscribe-column-3' href='$permalink'>$title</a></label></li>\n";
		}
		echo '</ul>';
		echo '<p id="subscribe-reloaded-select-all-p"><a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=true}else{for(var i=0;i<c;i++){t[i].checked=true}};return false;">'.__('Select all','subscribe-reloaded').'</a> ';
		echo '<a class="subscribe-reloaded-small-button" href="#" onclick="t=document.forms[\'post_list_form\'].elements[\'post_list[]\'];c=t.length;if(!c){t.checked=!t.checked}else{for(var i=0;i<c;i++){t[i].checked=false}};return false;">'.__('Invert selection','subscribe-reloaded').'</a></p>';
		echo '<p id="subscribe-reloaded-action-p">'.__('Action:','subscribe-reloaded').'
			<input type="radio" name="sra" value="delete" id="action_type_delete" /> <label for="action_type_delete">'.__('Delete','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp; 
			<input type="radio" name="sra" value="suspend" id="action_type_suspend" checked="checked" /> <label for="action_type_suspend">'.__('Suspend','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sra" value="force_r" id="action_type_force_y" /> <label for="action_type_force_y">'.__('Replies to my comments','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sra" value="activate" id="action_type_activate" /> <label for="action_type_activate">'.__('Activate','subscribe-reloaded').'</label></p>';
		echo '<p id="subscribe-reloaded-update-p"><input type="submit" class="subscribe-form-button" value="'.__('Update subscriptions','subscribe-reloaded').'" /><input type="hidden" name="sre" value="'.urlencode($email).'"/></p>';
		
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