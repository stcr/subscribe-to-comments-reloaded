<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

global $wp_subscribe_reloaded;
$post_permalink = null;

if (array_key_exists('post_permalink', $_GET))
{
    if ( ! empty( $_GET['post_permalink'] ) )
    {
        $post_permalink = $_GET['post_permalink'];
    }
}

ob_start();

if ( ! empty( $_POST['post_list'] ) ) {
	$post_list = array();
	foreach ( $_POST['post_list'] as $a_post_id ) {
		if ( ! in_array( $a_post_id, $post_list ) ) {
			$post_list[] = intval( $a_post_id );
		}
	}

	$action = ! empty( $_POST['sra'] ) ? $_POST['sra'] : ( ! empty( $_GET['sra'] ) ? $_GET['sra'] : '' );
	switch ( $action ) {
	case 'delete':
		$rows_affected = $wp_subscribe_reloaded->stcr->delete_subscriptions( $post_list, $email );
		echo '<p class="updated">' . __( 'Subscriptions deleted:', 'subscribe-reloaded' ) . " $rows_affected</p>";
		break;
	case 'suspend':
		$rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email, 'C' );
		echo '<p class="updated">' . __( 'Subscriptions suspended:', 'subscribe-reloaded' ) . " $rows_affected</p>";
		break;
	case 'activate':
		$rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email, '-C' );
		echo '<p class="updated">' . __( 'Subscriptions activated:', 'subscribe-reloaded' ) . " $rows_affected</p>";
		break;
	case 'force_y':
		$rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email, 'Y' );
		echo '<p class="updated">' . __( 'Subscriptions updated:', 'subscribe-reloaded' ) . " $rows_affected</p>";
		break;
	case 'force_r':
		$rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email, 'R' );
		echo '<p class="updated">' . __( 'Subscriptions updated:', 'subscribe-reloaded' ) . " $rows_affected</p>";
		break;
	default:
		break;
	}
}
$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_user_text' ) ), ENT_QUOTES, 'UTF-8' );

if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
	$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
}

echo "<p>$message</p>";

?>

	<form action="<?php echo htmlspecialchars( $_SERVER['REQUEST_URI'] ) ?>" method="post" id="post_list_form" name="post_list_form" onsubmit="if(this.sra[0].checked) return confirm('<?php _e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded' ) ?>')">
		<fieldset style="border:0">
			<?php
                $subscriptions = $wp_subscribe_reloaded->stcr->get_subscriptions( 'email', 'equals', $email, 'dt', 'DESC' );
                // Let us translate those status
                $legend_translate = array(
                    'R'  => __( 'Replies', 'subscribe-reloaded'),
                    'RC'  => __( 'Replies Unconfirmed', 'subscribe-reloaded'),
                    'Y'  => __( "All Comments", "subscribe-reloaded"),
                    'YC' => __( "Unconfirmed", "subscribe-reloaded"),
                    'C'	 => __( "Inactive", "subscribe-reloaded"),
                    '-C' => __( "Active", "subscribe-reloaded")
                );
if ( is_array( $subscriptions ) && ! empty( $subscriptions ) ) {
	echo '<p id="subscribe-reloaded-email-p">' . __( 'Email to manage', 'subscribe-reloaded' ) . ': <strong>' . $email . '</strong></p>';

    echo "<table class='stcr-subscription-list'><thead><tr>
                <th style='width:24%; text-align: center;'><i class=\"fa fa-calendar\" aria-hidden=\"true\"></i>&nbsp;&nbsp;". __('Subscription Date','subscribe-reloaded')."</th>
                <th style='width:40%;'><i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>&nbsp;&nbsp;". __('Title','subscribe-reloaded')."</th>
                <th style='width:20%; text-align: center;'><i class=\"fa fa-info\" aria-hidden=\"true\"></i>&nbsp;&nbsp;". __('Subscription Status','subscribe-reloaded')."</th>
            </tr></thead>";
    echo "<tbody>";

    foreach ( $subscriptions as $i => $a_subscription ) {
        $t_status  = $a_subscription->status;
        $permalink = get_permalink( $a_subscription->post_id );
        $title     = get_the_title( $a_subscription->post_id );

        echo "<tr>";
        echo "<td style='text-align: center;'><input type='checkbox' name='post_list[]' value='{$a_subscription->post_id}' id='e_$i'/><label for='e_$i'>$a_subscription->dt</td>";
        echo "<td><a href='$permalink' target='_blank'>$title</a> </td>";
        echo "<td style='text-align: center;'>$legend_translate[$t_status]</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";

	echo '<p id="subscribe-reloaded-select-all-p">
               <i class="fa fa-expand" aria-hidden="true"></i>&nbsp;
               <a class="subscribe-reloaded-small-button stcr-subs-select-all" href="#">' . __( 'Select all', 'subscribe-reloaded' ) . '</a> ';
	echo '&nbsp;&nbsp;<i class="fa fa-compress" aria-hidden="true"></i>&nbsp;
                <a class="subscribe-reloaded-small-button stcr-subs-select-none" href="#">' . __( 'Invert selection', 'subscribe-reloaded' ) . '</a></p>';
	echo '<p id="subscribe-reloaded-action-p">' . __( 'Action:', 'subscribe-reloaded' );

	echo '<select name="sra">';
		echo '<option value="">'. __( 'Choose your action', 'subscribe-reloaded' ) .'</option>';
		echo '<option value="delete">'. __( 'Delete', 'subscribe-reloaded' ) .'</option>';
		echo '<option value="suspend">'. __( 'Suspend', 'subscribe-reloaded' ) .'</option>';
		echo '<option value="force_y">'. __( 'All comments', 'subscribe-reloaded' ) .'</option>';
		echo '<option value="force_r">'. __( 'Replies to my comments', 'subscribe-reloaded' ) .'</option>';
//		echo '<option value="activate">'. __( 'Activate', 'subscribe-reloaded' ) .'</option>';
	echo '<select>';

    echo '&nbsp;&nbsp;<input type="submit" class="subscribe-form-button" value="' . __( 'Update subscriptions', 'subscribe-reloaded' ) . '" />
          <input type="hidden" name="srek" value="' . $wp_subscribe_reloaded->stcr->utils->get_subscriber_key( $email ) . '"></p>';

    if ( isset( $post_permalink ) )
    {
        echo '<p id="subscribe-reloaded-update-p"> 
            <a style="margin-right: 10px; text-decoration: none; box-shadow: unset;" href="'. esc_url( $post_permalink ) .'"><i class="fa fa-arrow-circle-left fa-2x" aria-hidden="true" style="vertical-align: middle;"></i>&nbsp; '. __('Return to Post','subscribe-reloaded').'</a>
          </p>';
    }
} else {
	echo '<p>' . __( 'No subscriptions match your search criteria.', 'subscribe-reloaded' ) . '</p>';
}
?>
		</fieldset>
	</form>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
?>
