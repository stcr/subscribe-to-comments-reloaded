<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

$stcr_post_email     = ! empty( $_POST['sre'] ) ? sanitize_text_field( wp_unslash( $_POST['sre'] ) ) : ( ! empty( $_GET['sre'] ) ? sanitize_text_field( wp_unslash( $_GET['sre'] ) ) : '' );
$stcr_old_post_email = ! empty( $_POST['oldsre'] ) ? sanitize_text_field( wp_unslash( $_POST['oldsre'] ) ) : ( ! empty( $_GET['oldsre'] ) ? sanitize_text_field( wp_unslash( $_GET['oldsre'] ) ) : '' );
$status              = ! empty( $_POST['srs'] ) ? sanitize_text_field( wp_unslash( $_POST['srs'] ) ) : ( ! empty( $_GET['srs'] ) ? sanitize_text_field( wp_unslash( $_GET['srs'] ) ) : '' );
$post_id             = ! empty( $_POST['srp'] ) ? sanitize_text_field( wp_unslash( $_POST['srp'] ) ) : ( ! empty( $_GET['srp'] ) ? sanitize_text_field( wp_unslash( $_GET['srp'] ) ) : 0 );
$valid_email         = true;
$valid_post_id       = true;
// Clean data
$post_id             = sanitize_text_field( trim( $post_id ) );
$status              = sanitize_text_field( trim( $status ) );

switch ( $action ) {
    case 'add':

        if ( empty( $_POST['stcr_add_subscription_nonce'] ) ) {
            exit();
        }

        if ( ! wp_verify_nonce( $_POST['stcr_add_subscription_nonce'], 'stcr_add_subscription_nonce' ) ) {
            exit();
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            exit();
        }

        $subscriber_post_email = explode( ',', $stcr_post_email );

        foreach ( $subscriber_post_email as $subscriber_email ) {
            $stcr_post_email = $wp_subscribe_reloaded->stcr->utils->check_valid_email( $subscriber_email );
        }

        $valid_post_id = $wp_subscribe_reloaded->stcr->utils->check_valid_number( $post_id );

        if ( $stcr_post_email === false )
        {
            $valid_email = false;
            break;
        }

        if ( $valid_post_id === false )
        {
            break;
        }

        foreach ( $subscriber_post_email as $subscriber_email ) {
            $wp_subscribe_reloaded->stcr->add_subscription( $post_id, $subscriber_email, $status );

            if ( strpos( $status, 'C' ) !== false ) {
                $wp_subscribe_reloaded->stcr->confirmation_email( $post_id, $subscriber_email );
            }
        }

        echo '<div class="updated"><p>' . esc_html__( 'Subscription added.', 'subscribe-to-comments-reloaded' ) . '</p></div>';
        break;

    case 'edit':

        if ( empty( $_POST['stcr_edit_subscription_nonce'] ) ) {
            exit();
        }

        if ( ! wp_verify_nonce( $_POST['stcr_edit_subscription_nonce'], 'stcr_edit_subscription_nonce' ) ) {
            exit();
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            exit();
        }

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

        echo '<div class="updated"><p>' . esc_html__( 'Subscriptions updated.', 'subscribe-to-comments-reloaded' ) . '</p></div>';
        break;

    case 'delete-subscription':

        if ( empty( $_GET['stcr_delete_subscription_nonce'] ) ) {
            exit();
        }

        if ( ! wp_verify_nonce( $_GET['stcr_delete_subscription_nonce'], 'stcr_delete_subscription_nonce' ) ) {
            exit();
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            exit();
        }

        $stcr_post_email     = $wp_subscribe_reloaded->stcr->utils->check_valid_email( $stcr_post_email );
        $valid_post_id       = $wp_subscribe_reloaded->stcr->utils->check_valid_number( $post_id );

        if ( $stcr_post_email === false )
        {
            $valid_email = false;
            break;
        }

        $wp_subscribe_reloaded->stcr->delete_subscriptions( $post_id, $stcr_post_email );

        echo '<div class="updated"><p>' . esc_html__( 'Subscription deleted.', 'subscribe-to-comments-reloaded' ) . '</p></div>';
        break;

    default:
        if ( ! empty( $_POST['subscriptions_list'] ) ) {

            if ( empty( $_POST['stcr_update_subscriptions_nonce'] ) ) {
                exit();
            }

            if ( ! wp_verify_nonce( $_POST['stcr_update_subscriptions_nonce'], 'stcr_update_subscriptions_nonce' ) ) {
                exit();
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                exit();
            }

            $post_list = $email_list = array();
            $subscription_lists = wp_unslash( $_POST['subscriptions_list'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            $subscription_lists = array_map( 'wp_kses_post', $subscription_lists );
            foreach ( $subscription_lists as $a_subscription ) {
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
                    echo '<div class="updated"><p>' . esc_html__( 'Subscriptions deleted:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p></div>';
                    break;
                case 'suspend':
                    $rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email_list, 'C' );
                    echo '<div class="updated"><p>' . esc_html__( 'Subscriptions suspended:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p></div>';
                    break;
                case 'activate':
                    $rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email_list, '-C' );
                    echo '<div class="updated"><p>' . esc_html__( 'Subscriptions activated:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p></div>';
                    break;
                case 'force_y':
                    $rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email_list, 'Y' );
                    echo '<div class="updated"><p>' . esc_html__( 'Subscriptions updated:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p></div>';
                    break;
                case 'force_r':
                    $rows_affected = $wp_subscribe_reloaded->stcr->update_subscription_status( $post_list, $email_list, 'R' );
                    echo '<div class="updated"><p>' . esc_html__( 'Subscriptions updated:', 'subscribe-to-comments-reloaded' ) . esc_html( $rows_affected ) . '</p></div>';
                    break;
                default:
                    break;
            }
        }
}

$initial_limit_results  = 1000;
$official_limit_results = '18446744073709551610';

$search_field  = ! empty( $_POST['srf'] ) ? sanitize_text_field( wp_unslash( $_POST['srf'] ) ) : ( ! empty( $_GET['srf'] ) ? sanitize_text_field( wp_unslash( $_GET['srf'] ) ) : 'email' );
$operator      = ! empty( $_POST['srt'] ) ? sanitize_text_field( wp_unslash( $_POST['srt'] ) ) : ( ! empty( $_GET['srt'] ) ? sanitize_text_field( wp_unslash( $_GET['srt'] ) ) : 'contains' );
$search_value  = ! empty( $_POST['srv'] ) ? sanitize_text_field( wp_unslash( $_POST['srv'] ) ) : ( ! empty( $_GET['srv'] ) ? sanitize_text_field( wp_unslash( $_GET['srv'] ) ) : '@' );
$order_by      = ! empty( $_POST['srob'] ) ? sanitize_text_field( wp_unslash( $_POST['srob'] ) ) : ( ! empty( $_GET['srob'] ) ? sanitize_text_field( wp_unslash( $_GET['srob'] ) ) : 'dt' );
$order         = ! empty( $_POST['sro'] ) ? sanitize_text_field( wp_unslash( $_POST['sro'] ) ) : ( ! empty( $_GET['sro'] ) ? sanitize_text_field( wp_unslash( $_GET['sro'] ) ) : 'DESC' );
$offset        = ! empty( $_POST['srsf'] ) ? intval( $_POST['srsf'] ) : ( ! empty( $_GET['srsf'] ) ? intval( $_GET['srsf'] ) : 0 );
$limit_results = ! empty( $_POST['srrp'] ) ? intval( $_POST['srrp'] ) : ( ! empty( $_GET['srrp'] ) ? intval( $_GET['srrp'] ) : $initial_limit_results );

$subscriptions = $wp_subscribe_reloaded->stcr->get_subscriptions( $search_field, $operator, $search_value, $order_by, $order, $offset, $limit_results );
$count_total   = count( $wp_subscribe_reloaded->stcr->get_subscriptions( $search_field, $operator, $search_value ) );

$count_results = count( $subscriptions ); // 0 if $results is null
$ending_to     = min( $count_total, $offset + $limit_results );
$previous_link = $next_link = $next_page_link = $previous_page_link = '';

if ( $offset > 0 ) {
	$new_starting  = ( $offset > $limit_results ) ? $offset - $limit_results : 0;
	$previous_link = "<a href='admin.php?page=stcr_manage_subscriptions&amp;srf=$search_field&amp;srt=" . urlencode( $operator ) . "&amp;srv=$search_value&amp;srob=$order_by&amp;sro=$order&amp;srsf=$new_starting&amp;srrp=$limit_results'>" . esc_html__( '&laquo; Previous', 'subscribe-to-comments-reloaded' ) . "</a> ";
}
if ( ( $ending_to < $count_total ) && ( $count_results > 0 ) ) {
	$new_starting = $offset + $limit_results;
	$next_link    = "<a href='admin.php?page=stcr_manage_subscriptions&amp;srf=$search_field&amp;srt=" . urlencode( $operator ) . "&amp;srv=$search_value&amp;srob=$order_by&amp;sro=$order&amp;srsf=$new_starting&amp;srrp=$limit_results'>" . esc_html__( 'Next &raquo;', 'subscribe-to-comments-reloaded' ) . "</a> ";
}
