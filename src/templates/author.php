<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
	header( 'Location: /' );
	exit;
}

global $wp_subscribe_reloaded;

ob_start();

if ( ! empty( $_POST['email_list'] ) ) {
	$email_list = array();
	foreach ( $_POST['email_list'] as $a_email ) {
		if ( ! in_array( $a_email, $email_list ) ) {
			$email_list[] = urldecode( $a_email );
		}
	}

	$action = ! empty( $_POST['sra'] ) ? $_POST['sra'] : ( ! empty( $_GET['sra'] ) ? $_GET['sra'] : '' );
    $action = sanitize_text_field( $action );
	switch ( $action ) {
	case 'delete':
		$rows_affected = $wp_subscribe_reloaded->stcr->delete_subscriptions( $post_ID, $email_list );
		echo '<p class="updated">' . esc_html__( 'Subscriptions deleted:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p>';
		break;
	case 'suspend':
		$rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_ID, $email_list, 'C' );
		echo '<p class="updated">' . esc_html__( 'Subscriptions suspended:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p>';
		break;
	case 'activate':
		$rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_ID, $email_list, '-C' );
		echo '<p class="updated">' . esc_html__( 'Subscriptions activated:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p>';
		break;
	case 'force_y':
		$rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_ID, $email_list, 'Y' );
		echo '<p class="updated">' . esc_html__( 'Subscriptions updated:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p>';
		break;
	case 'force_r':
		$rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_ID, $email_list, 'R' );
		echo '<p class="updated">' . esc_html__( 'Subscriptions updated:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p>';
		break;
	default:
		break;
	}
}
$message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_author_text' ) ), ENT_QUOTES, 'UTF-8' );
if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
	$message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
}
echo "<p>" . wp_kses( $message, wp_kses_allowed_html( 'post' ) ) . "</p>";
?>

	<?php $server_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''; ?>
	<form action="<?php echo esc_url( $server_request_uri ); ?>" method="post" id="email_list_form" name="email_list_form" onsubmit="if(this.sra[0].checked) return confirm('<?php esc_attr_e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ); ?>')">
		<fieldset style="border:0">
			<?php
                $subscriptions = $wp_subscribe_reloaded->stcr->get_subscriptions( 'post_id', 'equals', $post_ID, 'dt', 'ASC' );
            // Let us translate those status
            $legend_translate = array(
                'R'  => esc_html__( 'Replies', 'subscribe-to-comments-reloaded'),
                'RC'  => esc_html__( 'Replies Unconfirmed', 'subscribe-to-comments-reloaded'),
                'Y'  => esc_html__( "All Comments", 'subscribe-to-comments-reloaded'),
                'YC' => esc_html__( "Unconfirmed", 'subscribe-to-comments-reloaded'),
                'C'	 => esc_html__( "Inactive", 'subscribe-to-comments-reloaded'),
                '-C' => esc_html__( "Active", 'subscribe-to-comments-reloaded')
            );
if ( is_array( $subscriptions ) && ! empty( $subscriptions ) ) {
	echo '<h1 id="subscribe-reloaded-title-p">' . esc_html__( 'Title', 'subscribe-to-comments-reloaded' ) . ': <strong>' . esc_html( $target_post->post_title ) . '</strong></h1>'; // $target_post comes from wp_subscribe_reloaded\subscribe_reloaded_manage

	echo "<table class='stcr-subscription-list'><thead><tr>
                <th style='width:30%; text-align: center;'><i class=\"fa fa-calendar\" aria-hidden=\"true\"></i>&nbsp;&nbsp;". esc_html__('Subscription Date','subscribe-to-comments-reloaded')."</th>
                <th style='width:35%;'><i class=\"fa fa-envelope\" aria-hidden=\"true\"></i>&nbsp;&nbsp;". esc_html__('Subscription Email','subscribe-to-comments-reloaded')."</th>
                <th style='width:20%; text-align: center;'><i class=\"fa fa-info\" aria-hidden=\"true\"></i>&nbsp;&nbsp; ". esc_html__('Subscription Status','subscribe-to-comments-reloaded')."</th>
            </tr></thead>";
    echo "<tbody>";

    foreach ( $subscriptions as $i => $a_subscription ) {
        $t_status       = $a_subscription->status;
        $date           = strtotime( $a_subscription->dt );
        $formatted_date = date( get_option( "subscribe_reloaded_date_format" ), $date );
        $date_translated = $wp_subscribe_reloaded->stcr->utils->stcr_translate_month( $formatted_date );

        echo "<tr>";
            echo "<td style='text-align: center;'><input type='checkbox' name='email_list[]' value='" . esc_html( $a_subscription->email ) . "' id='e_" . esc_attr( $i ) . "'/><label for='e_" . esc_attr( $i ) . "'>" . esc_html( $date_translated ) . "</label></td>";
            echo "<td>". esc_html( $a_subscription->email ) . "</td>";
            echo "<td style='text-align: center;'>" . esc_html( $legend_translate[ $t_status ] ) . "</td>";
        echo "</tr>";
	}
        echo "</tbody>";
    echo "</table>";

	echo '<p id="subscribe-reloaded-select-all-p"><i class="fa fa-expand" aria-hidden="true"></i>&nbsp;<a class="subscribe-reloaded-small-button  stcr-subs-select-all" href="#" onclick="stcrCheckAll(event)">' . esc_html__( 'Select all', 'subscribe-to-comments-reloaded' ) . '</a> ';
	echo '&nbsp;&nbsp;<i class="fa fa-compress" aria-hidden="true"></i>&nbsp;<a class="subscribe-reloaded-small-button stcr-subs-select-none" href="#" onclick="stcrUncheckAll(event)">' . esc_html__( 'Invert selection', 'subscribe-to-comments-reloaded' ) . '</a></p>';
	echo '<p id="subscribe-reloaded-action-p">' . esc_html__( 'Action:', 'subscribe-to-comments-reloaded' );
	echo '&nbsp;&nbsp;<select name="sra">';
		echo '<option value="">'. esc_html__( 'Choose your action', 'subscribe-to-comments-reloaded' ) .'</option>';
		echo '<option value="delete">'. esc_html__( 'Unsubscribe', 'subscribe-to-comments-reloaded' ) .'</option>';
		echo '<option value="suspend">'. esc_html__( 'Suspend', 'subscribe-to-comments-reloaded' ) .'</option>';
		echo '<option value="force_y">'. esc_html__( 'All comments', 'subscribe-to-comments-reloaded' ) .'</option>';
		echo '<option value="force_r">'. esc_html__( 'Replies to my comments', 'subscribe-to-comments-reloaded' ) .'</option>';
//		echo '<option value="activate">'. esc_html__( 'Activate', 'subscribe-to-comments-reloaded' ) .'</option>';
	echo '<select>';
    echo '&nbsp;&nbsp;<input type="submit" class="subscribe-form-button" value="' . esc_html__( 'Update subscriptions', 'subscribe-to-comments-reloaded' ) . '" />
          <input type="hidden" name="srp" value="' . intval( $post_ID ) . '"/></p>';
	echo '<p id="subscribe-reloaded-update-p">
            <a style="margin-right: 10px; text-decoration: none; box-shadow: unset;" href="'. esc_url( get_permalink( $post_ID ) ) .'"><i class="fa fa-arrow-circle-left fa-2x" aria-hidden="true" style="vertical-align: middle;"></i>&nbsp;'. esc_html__('Return to Post','subscribe-to-comments-reloaded').'</a>
          </p>';


} else {
	echo '<p>' . esc_html__( 'No subscriptions match your search criteria.', 'subscribe-to-comments-reloaded' ) . '</p>';
}
?>
		</fieldset>
	</form>
    <script type="text/javascript">

        function stcrCheckAll(e) {

            var items = document.getElementsByName('email_list[]');
            for ( var i=0; i<items.length; i++ ) {
                if ( items[i].type == 'checkbox' ) {
                    items[i].checked = true;
                }
            }

            e.preventDefault();

        }

        function stcrUncheckAll(e) {

            var items = document.getElementsByName('email_list[]');
            for ( var i=0; i<items.length; i++ ) {
                if ( items[i].type == 'checkbox' ) {
                    items[i].checked = false;
                }
            }

            e.preventDefault();

        }

    </script>
<?php
$output = ob_get_contents();
ob_end_clean();
return $output;
?>
