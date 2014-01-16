<?php
// Avoid direct access to this piece of code
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}
$action = !empty($_POST['sra'])?$_POST['sra']:(!empty($_GET['sra'])?$_GET['sra']:'');
if ($action == 'edit-subscription'){
	require_once(WP_PLUGIN_DIR.'/subscribe-to-comments-reloaded/options/panel1-edit-subscription.php');
	return;
}
if (is_readable(WP_PLUGIN_DIR."/subscribe-to-comments-reloaded/options/panel1-business-logic.php"))
	require_once(WP_PLUGIN_DIR.'/subscribe-to-comments-reloaded/options/panel1-business-logic.php');
?>

<div class="postbox small">
<h3><?php _e('Mass Update Subscriptions','subscribe-reloaded') ?></h3>
<form action="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=1" method="post" id="update_address_form"
	onsubmit="if (this.oldsre.value == '') return false;return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded') ?>')">
<fieldset style="border:0">
<p class="liquid"><label for='oldsre'><?php _e('From','subscribe-reloaded') ?></label> <input type='text' size='30' name='oldsre' id='oldsre' value=''/></p>
<p class="liquid"><label for='sre'><?php _e('To','subscribe-reloaded') ?></label>
	<input type='text' size='30' name='sre' id='sre' value='<?php _e('optional','subscribe-reloaded') ?>' style="color:#ccc"
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
</fieldset>
</form>
</div>

<div class="postbox small">
<h3><?php _e('Add New Subscription','subscribe-reloaded') ?></h3>
<form action="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=1" method="post" id="update_address_form"
	onsubmit="if (this.srp.value == '' || this.sre.value == '') return false;">
<fieldset style="border:0">
<p class="liquid"><?php _e('Post ID','subscribe-reloaded') ?> <input type='text' size='30' name='srp' value='' /></p>
<p class="liquid"><?php _e('Email','subscribe-reloaded') ?> <input type='text' size='30' name='sre' value='' /></p>
<p class="liquid"><?php _e('Status','subscribe-reloaded') ?>
	<select name="srs">
		<option value='Y'><?php _e('Active','subscribe-reloaded') ?></option>
		<option value='R'><?php _e('Replies only','subscribe-reloaded') ?></option>
		<option value='YC'><?php _e('Ask user to confirm','subscribe-reloaded') ?></option>
	</select> <input type='submit' class='subscribe-form-button' value='<?php _e('Add','subscribe-reloaded') ?>' /></p>
<input type='hidden' name='sra' value='add'/>
</fieldset>
</form>
</div>

<div class="postbox">
<p class="subscribe-list-navigation"><?php echo "$previous_link $next_link" ?>
</p>
<h3><?php _e('Search subscriptions','subscribe-reloaded') ?></h3>
<form action="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=1" method="post">
<p><?php printf(__('You can either <a href="%s">view all the subscriptions</a> or find those where the','subscribe-reloaded'),
	'options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;srv=@&amp;srt=contains') ?>&nbsp;
<select name="srf">
	<option value='email'><?php _e('email','subscribe-reloaded') ?></option>
	<option value='post_id'><?php _e('post ID','subscribe-reloaded') ?></option>
	<option value='status'><?php _e('status','subscribe-reloaded') ?></option>
</select>
<select name="srt">
	<option value='equals'><?php _e('equals','subscribe-reloaded') ?></option>
	<option value='contains'><?php _e('contains','subscribe-reloaded') ?></option>
	<option value='does not contain'><?php _e('does not contain','subscribe-reloaded') ?></option>
	<option value='starts with'><?php _e('starts with','subscribe-reloaded') ?></option>
	<option value='ends with'><?php _e('ends with','subscribe-reloaded') ?></option>
</select>
<input type="text" size="20" name="srv" value="" />,
<?php _e('results per page:','subscribe-reloaded') ?><input type="text" size="2" name="srrp" value="25" />
<input type="submit" class="subscribe-form-button" value="<?php _e('Search','subscribe-reloaded') ?>" />
</form>

<form action="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1" method="post" id="subscription_form" name="subscription_form"
	onsubmit="if(this.sra[0].checked) return confirm('<?php _e('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded') ?>')">
<fieldset style="border:0">
<?php
	if (!empty($subscriptions) && is_array($subscriptions)){
		$order_post_id = "<a style='text-decoration:none' title='".__('Reverse the order by Post ID','subscribe-reloaded')."' href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;srv=".urlencode($search_value)."&amp;srt=".urlencode($operator)."&amp;srob=post_id&amp;sro=".(($order=='ASC')?"DESC'>&or;":"ASC'>&and;")."</a>";
		$order_dt = "<a style='text-decoration:none' title='".__('Reverse the order by Date/Time','subscribe-reloaded')."' href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;srv=".urlencode($search_value)."&amp;srt=".urlencode($operator)."&amp;srob=dt&amp;sro=".(($order=='ASC')?"DESC'>&or;":"ASC'>&and;")."</a>";
		$order_status = "<a style='text-decoration:none' title='".__('Reverse the order by Date/Time','subscribe-reloaded')."' href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;srv=".urlencode($search_value)."&amp;srt=".urlencode($operator)."&amp;srob=status&amp;sro=".(($order=='ASC')?"DESC'>&or;":"ASC'>&and;")."</a>";

		$show_post_column = ($operator != 'equals' || $search_field != 'post_id')?"<span class='subscribe-column subscribe-column-1'>".__('Post (ID)','subscribe-reloaded')."&nbsp;&nbsp;$order_post_id</span>":'';
		$show_email_column = ($operator != 'equals' || $search_field != 'email')?"<span class='subscribe-column subscribe-column-2'>".__('Email','subscribe-reloaded')."</span>":'';

		echo '<p>'.__('Search query:','subscribe-reloaded')." <code>$search_field $operator <strong>$search_value</strong> ORDER BY $order_by $order</code>. ".__('Rows:','subscribe-reloaded').' '.($offset+1)." - $ending_to ".__('of','subscribe-reloaded')." $count_total</p>";
		echo '<p>'.__('Legend: Y = all comments, R = replies only, C = inactive','subscribe-reloaded').'</p>';
		echo '<ul>';

		echo "<li class='subscribe-list-header'>
				<input class='checkbox' type='checkbox' name='subscription_list_select_all' id='stcr_select_all' 
					onchange='t=document.forms[\"subscription_form\"].elements[\"subscriptions_list[]\"];c=t.length;if(!c){t.checked=this.checked}else{for(var i=0;i<c;i++){t[i].checked=!t[i].checked}}'/>
				<span class='subscribe-column' style='width:38px'>&nbsp;</span>
				$show_post_column
				$show_email_column
				<span class='subscribe-column subscribe-column-3'>".__('Date and Time','subscribe-reloaded')." &nbsp;&nbsp;$order_dt</span>
				<span class='subscribe-column subscribe-column-4'>".__('Status','subscribe-reloaded')." &nbsp;&nbsp;$order_status</span></li>\n";
		$alternate = '';
		$date_time_format = get_option('date_format').' '.get_option('time_format');
		foreach($subscriptions as $a_subscription){
			$title = get_the_title($a_subscription->post_id);
			$title = (strlen($title) > 35)?substr($title, 0, 35).'..':$title;
			$row_post = ($operator != 'equals' || $search_field != 'post_id')?"<a class='subscribe-column subscribe-column-1' href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;srf=post_id&amp;srt=equals&amp;srv=$a_subscription->post_id'>$title ($a_subscription->post_id)</a> ":'';
			$row_email = ($operator != 'equals' || $search_field != 'email')?"<span class='subscribe-column subscribe-column-2'><a href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel=1&amp;srf=email&amp;srt=equals&amp;srv=".urlencode($a_subscription->email)."'>$a_subscription->email</a></span> ":'';
			$date_time = date_i18n($date_time_format, strtotime($a_subscription->dt));
			$alternate = ($alternate==' class="row"')?' class="row alternate"':' class="row"';
			echo "<li$alternate>
					<label for='sub_{$a_subscription->meta_id}' class='hidden'>".__('Subscription','subscribe-reloaded')." {$a_subscription->meta_id}</label>
					<input class='checkbox' type='checkbox' name='subscriptions_list[]' value='$a_subscription->post_id,".urlencode($a_subscription->email)."' id='sub_{$a_subscription->meta_id}' />
					<a class='subscribe-column' href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;sra=edit-subscription&amp;srp=".$a_subscription->post_id."&amp;sre=".urlencode($a_subscription->email)."'><img src='".WP_PLUGIN_URL."/subscribe-to-comments-reloaded/images/edit.png' alt='".__('Edit','subscribe-reloaded')."' width='16' height='16' /></a>
					<a class='subscribe-column' href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;sra=delete-subscription&amp;srp=".$a_subscription->post_id."&amp;sre=".urlencode($a_subscription->email)."' onclick='return confirm(\"".__('Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded')."\");'><img src='".WP_PLUGIN_URL."/subscribe-to-comments-reloaded/images/delete.png' alt='".__('Delete','subscribe-reloaded')."' width='16' height='16' /></a>
					$row_post
					$row_email
					<span class='subscribe-column subscribe-column-3'>$date_time</span>
					<span class='subscribe-column subscribe-column-4'>$a_subscription->status</span>
					</li>\n";
		}
		echo '</ul>';
		echo '<p>'.__('Action:','subscribe-reloaded').'
				<input type="radio" name="sra" value="delete" id="action_type_delete" /> <label for="action_type_delete">'.__('Delete forever','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="sra" value="suspend" id="action_type_suspend" checked="checked" /> <label for="action_type_suspend">'.__('Suspend','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="sra" value="force_y" id="action_type_force_y" /> <label for="action_type_force_y">'.__('Activate and set to Y','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="sra" value="force_r" id="action_type_force_r" /> <label for="action_type_force_r">'.__('Activate and set to R','subscribe-reloaded').'</label> &nbsp;&nbsp;&nbsp;&nbsp;
				<input type="radio" name="sra" value="activate" id="action_type_activate" /> <label for="action_type_activate">'.__('Activate','subscribe-reloaded').'</label></p>';
		echo '<p><input type="submit" class="subscribe-form-button" value="'.__('Update subscriptions','subscribe-reloaded').'" /></p>';
		echo "<input type='hidden' name='srf' value='$search_field'/><input type='hidden' name='srt' value='$operator'/><input type='hidden' name='srv' value='$search_value'/><input type='hidden' name='srsf' value='$offset'/><input type='hidden' name='srrp' value='$limit_results'/><input type='hidden' name='srob' value='$order_by'/><input type='hidden' name='sro' value='$order'/>";
	}
	elseif ($action == 'search')
		echo '<p>'.__('Sorry, no subscriptions match your search criteria.','subscribe-reloaded')."</p>";
?>
</fieldset>
</form>
</div>