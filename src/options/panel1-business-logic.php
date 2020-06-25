<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

$stcr_post_email     = ! empty( $_POST['sre'] ) ? $_POST['sre'] : ( ! empty( $_GET['sre'] ) ? $_GET['sre'] : '' );
$stcr_old_post_email = ! empty( $_POST['oldsre'] ) ? $_POST['oldsre'] : ( ! empty( $_GET['oldsre'] ) ? $_GET['oldsre'] : '' );
$status              = ! empty( $_POST['srs'] ) ? $_POST['srs'] : ( ! empty( $_GET['srs'] ) ? $_GET['srs'] : '' );
$post_id             = ! empty( $_POST['srp'] ) ? $_POST['srp'] : ( ! empty( $_GET['srp'] ) ? $_GET['srp'] : 0 );
$valid_email         = true;
$valid_post_id       = true;
// Clean data
$post_id             = sanitize_text_field( trim( $post_id ) );
$status              = sanitize_text_field( trim( $status ) );

switch ( $action ) {
    case 'add':
        $stcr_post_email     = $wp_subscribe_reloaded->stcr->utils->check_valid_email( $stcr_post_email );
        $valid_post_id       = $wp_subscribe_reloaded->stcr->utils->check_valid_number( $post_id );

        if ( $stcr_post_email === false )
        {
            $valid_email = false;
            break;
        }

        if ( $valid_post_id === false )
        {
            break;
        }

        $wp_subscribe_reloaded->stcr->add_subscription( $post_id, $stcr_post_email, $status );

        if ( strpos( $status, 'C' ) !== false ) {
            $wp_subscribe_reloaded->stcr->confirmation_email( $post_id, $email );
        }

        echo '<div class="updated"><p>' . __( 'Subscription added.', 'subscribe-to-comments-reloaded' ) . '</p></div>';
        break;

    case 'edit':
        $stcr_post_email     = $wp_subscribe_reloaded->stcr->utils->check_valid_email( $stcr_post_email );
        $stcr_old_post_email = $wp_subscribe_reloaded->stcr->utils->check_valid_email( $stcr_old_post_email );
        $valid_post_id       = $wp_subscribe_reloaded->stcr->utils->check_valid_number( $post_id );

        if ( $stcr_post_email === false || $stcr_old_post_email === false )
        {
            $valid_email = false;
            break;
        }

        if ( $valid_post_id === false )
        {
            break;
        }

        $old_email = $stcr_old_post_email;
        $new_email = $stcr_post_email;

        $wp_subscribe_reloaded->stcr->update_subscription_status( $post_id, $old_email, $status );
        $wp_subscribe_reloaded->stcr->update_subscription_email( $post_id, $old_email, $new_email );

        echo '<div class="updated"><p>' . __( 'Subscriptions updated.', 'subscribe-to-comments-reloaded' ) . '</p></div>';
        break;

    case 'delete-subscription':
        $stcr_post_email     = $wp_subscribe_reloaded->stcr->utils->check_valid_email( $stcr_post_email );
        $valid_post_id       = $wp_subscribe_reloaded->stcr->utils->check_valid_number( $post_id );

        if ( $stcr_post_email === false )
        {
            $valid_email = false;
            break;
        }

        $wp_subscribe_reloaded->stcr->delete_subscriptions( $post_id, $stcr_post_email );

        echo '<div class="updated"><p>' . __( 'Subscription deleted.', 'subscribe-to-comments-reloaded' ) . '</p></div>';
        break;

    default:
        if ( ! empty( $_POST['subscriptions_list'] ) ) {
            $post_list = $email_list = array();
            foreach ( $_POST['subscriptions_list'] as $a_subscription ) {
                list( $a_post, $a_email ) = explode( ',', $a_subscription );
                if ( ! in_array( $a_post, $post_list ) ) {
                    $post_list[] = $a_post;
                }
                if ( ! in_array( $a_email, $email_list ) ) {
                    $email_list[] = urldecode( $a_email );
                }
            }

            switch ( $action ) {
                case 'delete':
                    $rows_affected = $wp_subscribe_reloaded->stcr->delete_subscriptions( $post_list, $email_list );
                    echo '<div class="updated"><p>' . __( 'Subscriptions deleted:', 'subscribe-to-comments-reloaded' ) . " $rows_affected</p></div>";
                    break;
                case 'suspend':
                    $rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email_list, 'C' );
                    echo '<div class="updated"><p>' . __( 'Subscriptions suspended:', 'subscribe-to-comments-reloaded' ) . " $rows_affected</p></div>";
                    break;
                case 'activate':
                    $rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email_list, '-C' );
                    echo '<div class="updated"><p>' . __( 'Subscriptions activated:', 'subscribe-to-comments-reloaded' ) . " $rows_affected</p></div>";
                    break;
                case 'force_y':
                    $rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email_list, 'Y' );
                    echo '<div class="updated"><p>' . __( 'Subscriptions updated:', 'subscribe-to-comments-reloaded' ) . " $rows_affected</p></div>";
                    break;
                case 'force_r':
                    $rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email_list, 'R' );
                    echo '<div class="updated"><p>' . __( 'Subscriptions updated:', 'subscribe-to-comments-reloaded' ) . " $rows_affected</p></div>";
                    break;
                default:
                    break;
            }
        }
}

$initial_limit_results  = 1000;
$official_limit_results = '18446744073709551610';

$search_field  = ! empty( $_POST['srf'] ) ? $_POST['srf'] : ( ! empty( $_GET['srf'] ) ? $_GET['srf'] : 'email' );
$operator      = ! empty( $_POST['srt'] ) ? $_POST['srt'] : ( ! empty( $_GET['srt'] ) ? $_GET['srt'] : 'contains' );
$search_value  = ! empty( $_POST['srv'] ) ? $_POST['srv'] : ( ! empty( $_GET['srv'] ) ? $_GET['srv'] : '@' );
$order_by      = ! empty( $_POST['srob'] ) ? $_POST['srob'] : ( ! empty( $_GET['srob'] ) ? $_GET['srob'] : 'dt' );
$order         = ! empty( $_POST['sro'] ) ? $_POST['sro'] : ( ! empty( $_GET['sro'] ) ? $_GET['sro'] : 'DESC' );
$offset        = ! empty( $_POST['srsf'] ) ? intval( $_POST['srsf'] ) : ( ! empty( $_GET['srsf'] ) ? intval( $_GET['srsf'] ) : 0 );
$limit_results = ! empty( $_POST['srrp'] ) ? intval( $_POST['srrp'] ) : ( ! empty( $_GET['srrp'] ) ? intval( $_GET['srrp'] ) : $initial_limit_results );
// Clean data
$search_field  = sanitize_text_field($search_field);
$operator      = sanitize_text_field($operator);
$order_by      = sanitize_text_field($order_by);
$order         = sanitize_text_field($order);
$offset        = sanitize_text_field($offset);
$search_value  = sanitize_text_field(trim($search_value));
$limit_results = sanitize_text_field(trim($limit_results));

$subscriptions = $wp_subscribe_reloaded->stcr->get_subscriptions( $search_field, $operator, $search_value, $order_by, $order, $offset, $limit_results );
$count_total   = count( $wp_subscribe_reloaded->stcr->get_subscriptions( $search_field, $operator, $search_value ) );

$count_results = count( $subscriptions ); // 0 if $results is null
$ending_to     = min( $count_total, $offset + $limit_results );
$previous_link = $next_link = $next_page_link = $previous_page_link = '';

if ( $offset > 0 ) {
	$new_starting  = ( $offset > $limit_results ) ? $offset - $limit_results : 0;
	$previous_link = "<a href='admin.php?page=stcr_manage_subscriptions&amp;srf=$search_field&amp;srt=" . urlencode( $operator ) . "&amp;srv=$search_value&amp;srob=$order_by&amp;sro=$order&amp;srsf=$new_starting&amp;srrp=$limit_results'>" . __( '&laquo; Previous', 'subscribe-to-comments-reloaded' ) . "</a> ";
}
if ( ( $ending_to < $count_total ) && ( $count_results > 0 ) ) {
	$new_starting = $offset + $limit_results;
	$next_link    = "<a href='admin.php?page=stcr_manage_subscriptions&amp;srf=$search_field&amp;srt=" . urlencode( $operator ) . "&amp;srv=$search_value&amp;srob=$order_by&amp;sro=$order&amp;srsf=$new_starting&amp;srrp=$limit_results'>" . __( 'Next &raquo;', 'subscribe-to-comments-reloaded' ) . "</a> ";
}
