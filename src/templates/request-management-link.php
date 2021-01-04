<?php

// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
    header( 'Location: /' );
    exit;
}

global $wp_subscribe_reloaded;

// The the page where the user is coming from
$post_permalink = null;
$current_user_email = null; // Comes from wp_subscribe-to-comments-reloaded\subscribe_reloaded_manage()
$ID = $target_post;
$valid_all = true;
$valid_email = true;
$valid_challenge = true;

// google recaptcha
$valid_captcha = true;
$captcha_output = '';
$use_captcha = get_option( 'subscribe_reloaded_use_captcha', 'no' );
$captcha_site_key = get_option( 'subscribe_reloaded_captcha_site_key', '' );
$captcha_secret_key = get_option( 'subscribe_reloaded_captcha_secret_key', '' );

// google recaptcha confirm
if ( $use_captcha == 'yes' ) {
    $captcha_output .= '<div class="g-recaptcha" data-sitekey="' . $captcha_site_key . '"></div>';
    if ( isset( $_POST['g-recaptcha-response'] ) ) {
        $captcha = $_POST['g-recaptcha-response'];
        $captcha_result = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
            'method' => 'POST',
            'body' => array(
                'secret' => $captcha_secret_key,
                'response' => $captcha,
            )
        ));
        if ( is_wp_error( $captcha_result ) ) {
            $valid_captcha = false;
        } else {
            $captcha_response = json_decode( $captcha_result['body'], true );
            if ( ! $captcha_response['success'] ) {
                $valid_captcha = false;
            }
        }
    } else {
        $valid_captcha = false;
    }
}

// get email if user known
if ( isset( $current_user ) && $current_user->ID > 0 ) {
    $current_user_email = $current_user->data->user_email;
}

// post permalink supplied with $_GET
if ( array_key_exists('post_permalink', $_GET ) ) {
    if ( ! empty( $_GET['post_permalink'] ) ) {
        $post_permalink = $_GET['post_permalink'];
    }
}

// challenge question
$challenge_question_state = get_option( 'subscribe_reloaded_use_challenge_question', 'no' );
$challenge_question = get_option( 'subscribe_reloaded_challenge_question', 'What is 1 + 2?' );
$challenge_answer = get_option( 'subscribe_reloaded_challenge_answer', '3' );

// start output buffering
ob_start();

// email address supplied
if ( ! empty( $email ) ) {

    // check email validity
    $stcr_post_email = $wp_subscribe_reloaded->stcr->utils->check_valid_email( $email );

    // check challenge question validity
    if ( $challenge_question_state == 'yes' ) {
        $challenge_user_answer = sanitize_text_field( $_POST['subscribe_reloaded_challenge'] );
        if ( $challenge_answer != $challenge_user_answer ) {
            $valid_challenge = false;
            $valid_all = false;
        }
    }
    
    // email is invalid
    if ( $stcr_post_email === false ) {
        $valid_email = false;
        $valid_all = false;
    }

    // email is not subscribed
    if ( ! $wp_subscribe_reloaded->stcr->utils->get_subscriber_key( $stcr_post_email ) ) {
        $valid_email = false;
        $valid_all = false;
    }

    // captcha is not valid
    if ( ! $valid_captcha ) {
        $valid_all = false;
    }

    if ( $valid_all ) {

        // Send management link
        $subject        = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_subject', 'Manage your subscriptions on [blog_name]' ) ), ENT_QUOTES, 'UTF-8' );
        $page_message   = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_content', '' ) ), ENT_QUOTES, 'UTF-8' );
        $email_message  = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_management_email_content', '' ) ), ENT_QUOTES, 'UTF-8' );
        $manager_link   = get_bloginfo( 'url' ) . get_option( 'subscribe_reloaded_manager_page', '/comment-subscriptions/' );
        $one_click_unsubscribe_link = $manager_link;
        if ( function_exists( 'qtrans_convertURL' ) ) {
            $manager_link = qtrans_convertURL( $manager_link );
        }

        $clean_email     = $wp_subscribe_reloaded->stcr->utils->clean_email( $email );
        $subscriber_salt = $wp_subscribe_reloaded->stcr->utils->generate_temp_key( $clean_email );

        $manager_link .= ( strpos( $manager_link, '?' ) !== false ) ? '&' : '?';
        $manager_link .= "srek=" . $wp_subscribe_reloaded->stcr->utils->get_subscriber_key($clean_email) . "&srk=$subscriber_salt&amp;srsrc=e&post_permalink=" . esc_url( $post_permalink );
        $one_click_unsubscribe_link .= ( strpos( $one_click_unsubscribe_link, '?' ) !== false ) ? '&' : '?';
        $one_click_unsubscribe_link .= ( ( strpos( $one_click_unsubscribe_link, '?' ) !== false ) ? '&' : '?' )
            . "srek=" . esc_attr( $this->utils->get_subscriber_key( $clean_email ) )
            . "&srk=$subscriber_salt" . "&sra=u;srsrc=e" . "&srp=";

        // Replace tags with their actual values
        $subject = str_replace( '[blog_name]', get_bloginfo( 'name' ), $subject );
        $page_message = str_replace( '[blog_name]', get_bloginfo( 'name' ), $page_message );
        $email_message = str_replace( '[blog_name]', get_bloginfo( 'name' ), $email_message );
        $email_message = str_replace( '[manager_link]',  $manager_link, $email_message );
        $email_message = str_replace( '[oneclick_link]', $one_click_unsubscribe_link, $email_message );

        if ( get_option( 'subscribe_reloaded_enable_html_emails', 'yes' ) == 'yes' ) {
            $email_message = wpautop( $email_message );
        }

        // QTranslate support
        if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
            $subject       = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $subject );
            $page_message  = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $page_message );
            $email_message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $email_message );
        }

        // Prepare email settings
        $email_settings = array(
            'subject'      => $subject,
            'message'      => $email_message,
            'toEmail'      => $clean_email
        );

        $wp_subscribe_reloaded->stcr->utils->send_email( $email_settings );

        echo wpautop( $page_message );

    }

// email address not supplied
} else {

    $message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_request_mgmt_link' ) ), ENT_QUOTES, 'UTF-8' );
    
    // get email address
    $email = '';
    if ( isset($current_user_email) ) {
        $email = $current_user_email;
    } else if ( isset( $_COOKIE['comment_author_email_' . COOKIEHASH] )) {
        $email = sanitize_email( $_COOKIE['comment_author_email_' . COOKIEHASH] );
    } else {
        $email = '';
    }

    // qTrans compatibility
    if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
        $message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
    }

    ?>
    <p><?php echo wpautop( $message ); ?></p>
    <form action="<?php echo esc_url( $_SERVER[ 'REQUEST_URI' ]);?>" method="post" name="sub-form">
        <fieldset style="border:0">
            <?php if ( $challenge_question_state == 'yes' ) : ?>
                <p>
                    <label for="subscribe_reloaded_email"><?php _e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
                    <input id='subscribe_reloaded_email' type="email" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $email ); ?>" size="22" required />
                </p>
                <p>
                    <label for="subscribe-reloaded-challenge"><?php echo $challenge_question; ?></label>
                    <input id="subscribe-reloaded-challenge" type="text" class="subscribe-form-field" name="subscribe_reloaded_challenge" />
                </p>
                <p>
                    <input name="submit" type="submit" class="subscribe-form-button" value="<?php _e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
                </p>
                <?php echo $captcha_output; ?>
                <p class="notice-email-error" style='color: #f55252;font-weight:bold; display: none;'></p>
            <?php else : ?>
                <p>
                    <label for="subscribe_reloaded_email"><?php _e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
                    <input id='subscribe_reloaded_email' type="email" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $email ); ?>" size="22" required />
                    <input name="submit" type="submit" class="subscribe-form-button" value="<?php _e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
                </p>
                <?php echo $captcha_output; ?>
                <p class="notice-email-error" style='color: #f55252;font-weight:bold; display: none;'></p>
            <?php endif; ?>

        </fieldset>
    </form>
    <?php

    if ( isset( $post_permalink ) ) {
        echo '<p id="subscribe-reloaded-update-p"> 
            <a style="margin-right: 10px; text-decoration: none; box-shadow: unset;" href="'. esc_url( $post_permalink ) .'"><i class="fa fa-arrow-circle-left fa-2x" aria-hidden="true" style="vertical-align: middle;"></i>&nbsp; '. __('Return to Post','subscribe-to-comments-reloaded').'</a>
          </p>';
    }

}

// email invalid
if( ! $valid_all ) {

    $message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_request_mgmt_link' ) ), ENT_QUOTES, 'UTF-8' );
    
    if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
        $message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
    }

    ?>
    <p><?php echo wpautop( $message ); ?></p>
    <form action="<?php echo esc_url( $_SERVER[ 'REQUEST_URI' ]);?>" method="post" name="sub-form">
        <fieldset style="border:0">
            
            <?php if ( $challenge_question_state == 'yes' ) : ?>
                <p>
                    <label for="subscribe_reloaded_email"><?php _e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
                    <input id='subscribe_reloaded_email' type="email" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $email ); ?>" size="22" required />
                </p>
                <p>
                    <label for="subscribe-reloaded-challenge"><?php echo $challenge_question; ?></label>
                    <input id="subscribe-reloaded-challenge" type="text" class="subscribe-form-field" name="subscribe_reloaded_challenge" />
                </p>
                <p>
                    <input name="submit" type="submit" class="subscribe-form-button" value="<?php _e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
                </p>
                <?php echo $captcha_output; ?>
                <p class="notice-email-error" style='color: #f55252;font-weight:bold;'></p>
            <?php else : ?>
                <p>
                    <label for="subscribe_reloaded_email"><?php _e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
                    <input id='subscribe_reloaded_email' type="email" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $email ); ?>" size="22" required />
                    <input name="submit" type="submit" class="subscribe-form-button" value="<?php _e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
                </p>
                <?php echo $captcha_output; ?>
                <p class="notice-email-error" style='color: #f55252;font-weight:bold;'></p>
            <?php endif; ?>

            <?php if ( ! $valid_email ) : ?>
                <p style='color: #f55252;font-weight:bold;'><i class="fa fa-exclamation-triangle"></i> <?php _e("Email address is not valid", 'subscribe-to-comments-reloaded') ?></p>
            <?php endif; ?>
            
            <?php if ( ! $valid_challenge ) : ?>
                <p style='color: #f55252;font-weight:bold;'><i class="fa fa-exclamation-triangle"></i> <?php _e("Challenge answer is not correct", 'subscribe-to-comments-reloaded') ?></p>
            <?php endif; ?>

            <?php if ( ! $valid_captcha ) : ?>
                <p style='color: #f55252;font-weight:bold;'><i class="fa fa-exclamation-triangle"></i> <?php _e("Challenge answer is not correct", 'subscribe-to-comments-reloaded') ?></p>
            <?php endif; ?>

        </fieldset>
    </form>
    <?php
}

// stop output buffer and pass it back
$output = ob_get_contents();
ob_end_clean();
return $output;
?>