<?php
// Avoid direct access to this piece of code
if (!function_exists('is_admin') || !is_admin()){
	header('Location: /');
	exit;
}

switch ($action){
	case 'add':
		$post_id = !empty($_POST['srp'])?$_POST['srp']:(!empty($_GET['srp'])?$_GET['srp']:0);
		$email = !empty($_POST['sre'])?$_POST['sre']:(!empty($_GET['sre'])?$_GET['sre']:'');
		$status = !empty($_POST['srs'])?$_POST['srs']:(!empty($_GET['srs'])?$_GET['srs']:'');

		$wp_subscribe_reloaded->add_subscription($post_id, $email, $status);
		if (strpos($status, 'C') !== false)
			$wp_subscribe_reloaded->confirmation_email($post_id, $email);
		
		echo '<div class="updated"><p>'.__('Subscription added.', 'subscribe-reloaded').'</p></div>';
		break;

	case 'edit':
		$post_id = !empty($_POST['srp'])?$_POST['srp']:(!empty($_GET['srp'])?$_GET['srp']:0);
		$old_email = !empty($_POST['oldsre'])?$_POST['oldsre']:(!empty($_GET['oldsre'])?$_GET['oldsre']:'');
		$new_email = !empty($_POST['sre'])?$_POST['sre']:(!empty($_GET['sre'])?$_GET['sre']:'');
		$status = !empty($_POST['srs'])?$_POST['srs']:(!empty($_GET['srs'])?$_GET['srs']:'');

		$wp_subscribe_reloaded->update_subscription_status($post_id, $old_email, $status);
		$wp_subscribe_reloaded->update_subscription_email($post_id, $old_email, $new_email);
		
		echo '<div class="updated"><p>'.__('Subscriptions updated.', 'subscribe-reloaded').'</p></div>';
		break;

	case 'delete-subscription':
		$post_id = !empty($_POST['srp'])?$_POST['srp']:(!empty($_GET['srp'])?$_GET['srp']:0);
		$email = !empty($_POST['sre'])?$_POST['sre']:(!empty($_GET['sre'])?$_GET['sre']:'');

		$wp_subscribe_reloaded->delete_subscriptions($post_id, $email);
		
		echo '<div class="updated"><p>'.__('Subscription deleted.', 'subscribe-reloaded').'</p></div>';
		break;

	default:
		if (!empty($_POST['subscriptions_list'])){
			$post_list = $email_list = array();
			foreach($_POST['subscriptions_list'] as $a_subscription){
				list($a_post,$a_email) = explode(',', $a_subscription);
				if (!in_array($a_post, $post_list)) $post_list[] = $a_post;
				if (!in_array($a_email, $email_list)) $email_list[] = urldecode($a_email);
			}

			switch($action){
				case 'delete':
					$rows_affected = $wp_subscribe_reloaded->delete_subscriptions($post_list, $email_list);
					echo '<div class="updated"><p>'.__('Subscriptions deleted:', 'subscribe-reloaded')." $rows_affected</p></div>";
					break;
				case 'suspend':
					$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email_list, 'C');
					echo '<div class="updated"><p>'.__('Subscriptions suspended:', 'subscribe-reloaded')." $rows_affected</p></div>";
					break;
				case 'activate':
					$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email_list, '-C');
					echo '<div class="updated"><p>'.__('Subscriptions activated:', 'subscribe-reloaded')." $rows_affected</p></div>";
					break;
				case 'force_y':
					$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email_list, 'Y');
					echo '<div class="updated"><p>'.__('Subscriptions updated:', 'subscribe-reloaded')." $rows_affected</p></div>";
					break;
				case 'force_r':
					$rows_affected = $wp_subscribe_reloaded->update_subscription_status($post_list, $email_list, 'R');
					echo '<div class="updated"><p>'.__('Subscriptions updated:', 'subscribe-reloaded')." $rows_affected</p></div>";
					break;
				default:
					break;
			}
		}
}

$search_field = !empty($_POST['srf'])?$_POST['srf']:(!empty($_GET['srf'])?$_GET['srf']:'email');
$operator = !empty($_POST['srt'])?$_POST['srt']:(!empty($_GET['srt'])?$_GET['srt']:'contains');
$search_value = !empty($_POST['srv'])?$_POST['srv']:(!empty($_GET['srv'])?$_GET['srv']:'@');
$order_by = !empty($_POST['srob'])?$_POST['srob']:(!empty($_GET['srob'])?$_GET['srob']:'dt');
$order = !empty($_POST['sro'])?$_POST['sro']:(!empty($_GET['sro'])?$_GET['sro']:'DESC');
$offset = !empty($_POST['srsf'])?intval($_POST['srsf']):(!empty($_GET['srsf'])?intval($_GET['srsf']):0);
$limit_results = !empty($_POST['srrp'])?intval($_POST['srrp']):(!empty($_GET['srrp'])?intval($_GET['srrp']):25);

$subscriptions = $wp_subscribe_reloaded->get_subscriptions($search_field, $operator, $search_value, $order_by, $order, $offset, $limit_results);
$count_total = count($wp_subscribe_reloaded->get_subscriptions($search_field, $operator, $search_value));

$count_results = count($subscriptions); // 0 if $results is null
$ending_to = min($count_total, $offset+$limit_results);
$previous_link = $next_link = '';
if ($offset > 0){
	$new_starting = ($offset > $limit_results)?$offset-$limit_results:0;
	$previous_link = "<a href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;srf=$search_field&amp;srt=".urlencode($operator)."&amp;srv=$search_value&amp;srob=$order_by&amp;sro=$order&amp;srsf=$new_starting&amp;srrp=$limit_results'>".__('&laquo; Previous','subscribe-reloaded')."</a> ";
}
if (($ending_to < $count_total) && ($count_results > 0)){
	$new_starting = $offset+$limit_results;
	$next_link = "<a href='options-general.php?page=subscribe-to-comments-reloaded/options/index.php&amp;subscribepanel=1&amp;srf=$search_field&amp;srt=".urlencode($operator)."&amp;srv=$search_value&amp;srob=$order_by&amp;sro=$order&amp;srsf=$new_starting&amp;srrp=$limit_results'>".__('Next &raquo;','subscribe-reloaded')."</a> ";
}