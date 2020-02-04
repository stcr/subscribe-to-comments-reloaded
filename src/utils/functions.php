<?php
if ( ! function_exists( 'stcr_add_subscriber' ) ) {

    /**
     * Add subscriber
     * 
     * @since 200205
     */
    function stcr_add_subscription( $data ) {

        // no data supplied, return
        if ( ! is_array( $data ) ) return;

        // no post ID supplied, return
        if ( empty( $data['post_id'] ) ) return;

        // no email supplied, return
        if ( empty( $data['email'] ) ) return;

        // default type
        if ( empty( $data['type'] ) ) $data['type'] = 'all';

        // get status code
        switch ( $data['type'] ) {
            
            case 'all':
                $status = 'Y';
                break;
            
            case 'all_unconfirmed':
                $status = 'YC';
                break;
            
            case 'replies':
                $status = 'R';
                break;
            
            case 'replies_unconfirmed':
                $status = 'RC';
                break;
            
            case 'inactive':
                $status = 'C';
                break;
            
            case 'active':
                $status = '-C';
                break;

            default:
                $status = 'Y';
                break;
        }

        // notify admin?
        $notify_admin = false;
        if ( get_option( 'subscribe_reloaded_enable_admin_messages' ) == 'yes' ) $notify_admin = true;
        if ( isset( $data['notify_admin'] ) && $data['notify_admin'] == true ) $notify_admin = true;
        if ( isset( $data['notify_admin'] ) && $data['notify_admin'] == false ) $notify_admin = false;

        // notify subscriber?
        $notify_subscriber = false;
        if ( get_option( 'subscribe_reloaded_enable_double_check' ) == 'yes' ) $notify_subscriber = true;
        if ( in_array( $status, array( 'Y', 'R' ) ) ) {
            // no need for confirmation email beacuse it's a confirmed subscription
            $notify_subscriber = false;
        }

        // get the class instance
        global $wp_subscribe_reloaded;

        // sanitize email address
        $clean_email = $wp_subscribe_reloaded->stcr->utils->clean_email( $data['email'] );

        // notify the administrator about the new subscription
        if ( $notify_admin ) {
            
            $from_name  = stripslashes( get_option( 'subscribe_reloaded_from_name', 'admin' ) );
            $from_email = get_option( 'subscribe_reloaded_from_email', get_bloginfo( 'admin_email' ) );

            $subject = __( 'New subscription to', 'subscribe-to-comments-reloaded' ) . ' ' . get_the_title( $data['post_id'] );
            $message = __( 'New subscription to', 'subscribe-to-comments-reloaded' ) . ' ' . get_the_title( $data['post_id'] ) . PHP_EOL . __( 'User:', 'subscribe-to-comments-reloaded' ) . " $clean_email";
            
            $email_settings = array(
                'subject'      => $subject,
                'message'      => $message,
                'toEmail'      => get_bloginfo( 'admin_email' )
            );
            
            $wp_subscribe_reloaded->stcr->utils->send_email( $email_settings );

        }

        // double check, send confirmation email
        if ( $notify_subscriber && ! $wp_subscribe_reloaded->stcr->is_user_subscribed( $data['post_id'], $clean_email, 'C' ) ) {
            
            $wp_subscribe_reloaded->stcr->add_subscription( $data['post_id'], $clean_email, $status );
            $wp_subscribe_reloaded->stcr->confirmation_email( $data['post_id'], $clean_email );
        
        // not double check, add subscription
        } else {
            $wp_subscribe_reloaded->stcr->add_subscription( $data['post_id'], $clean_email, $status );
        }

    }

}