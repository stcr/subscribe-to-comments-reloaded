<?php
// Avoid direct access to this piece of code
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}

function subscribe_reloaded_update_option( $_option = '', $_value = '', $_type = ''){
	if (!isset($_value)) return true;
	switch($_type){
		case 'yesno':
			if ($_value=='yes' || $_value=='no'){
				update_option('subscribe_reloaded_'.$_option, $_value);
				return true;
			}
			break;

		case 'integer':
			update_option('subscribe_reloaded_'.$_option, abs(intval($_value)));
			return true;
			break;

		case 'text-no-encode':
			update_option('subscribe_reloaded_'.$_option, $_value);
			return true;
			break;

		default:
			update_option('subscribe_reloaded_'.$_option, htmlentities($_value, ENT_COMPAT, 'UTF-8'));
			return true;
			break;
	}
	return false;
}

function subscribe_reloaded_get_option($_option = '', $_default = ''){
	$value = get_option('subscribe_reloaded_'.$_option, $_default);
	return stripslashes($value);
}

// Load localization files
load_plugin_textdomain('subscribe-reloaded', WP_PLUGIN_DIR .'/subscribe-to-comments-reloaded/langs', '/subscribe-to-comments-reloaded/langs');

// Define the panels
$array_panels = array(
	__('Manage subscriptions','subscribe-reloaded'),
	__('Comment Form','subscribe-reloaded'),
	__('Management Page','subscribe-reloaded'),
	__('Notifications','subscribe-reloaded'),
	__('Options','subscribe-reloaded'),
	__('You can help','subscribe-reloaded')
);

// What panel to display
$current_panel = empty($_GET['subscribepanel'])?1:intval($_GET['subscribepanel']);

// Text direction
if ($wp_locale->text_direction != 'ltr')
	$array_panels = array_reverse($array_panels, true);

?>
<div class="wrap">
	<div id="subscribe-to-comments-icon" class="icon32 <?php echo $wp_locale->text_direction ?>"></div>
	<h2 class="medium">
		<?php
		foreach($array_panels as $a_panel_id => $a_panel_details){
			echo '<a class="nav-tab nav-tab';
			echo ($current_panel == $a_panel_id+1)?'-active':'-inactive';
			echo '" href="options-general.php?page=subscribe-to-comments-reloaded/options/index.php&subscribepanel='.($a_panel_id+1).'">'.$a_panel_details.'</a>';
		}
		?>
	</h2>
	
	<?php if (is_readable(WP_PLUGIN_DIR."/subscribe-to-comments-reloaded/options/panel$current_panel.php")) require_once(WP_PLUGIN_DIR."/subscribe-to-comments-reloaded/options/panel$current_panel.php"); ?>
</div>