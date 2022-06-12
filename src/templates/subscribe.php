<?php

// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
    header( 'Location: /' );
    exit;
}

global $wp_subscribe_reloaded;

$current_user_email = null; // Comes from wp_subscribe-to-comments-reloaded\subscribe_reloaded_manage()
$valid_all = true;
$valid_email = true;
$valid_challenge = true;

// google recaptcha
$valid_captcha = true;
$captcha_output = '';
$use_captcha = get_option( 'subscribe_reloaded_use_captcha', 'no' );
$captcha_site_key = get_option( 'subscribe_reloaded_captcha_site_key', '' );
$captcha_secret_key = get_option( 'subscribe_reloaded_captcha_secret_key', '' );
$recaptcha_version = get_option( 'subscribe_reloaded_recaptcha_version', 'v2' );

if ( get_option( 'subscribe_reloaded_allow_subscribe_without_comment', 'yes' ) != 'yes' ) {
    exit;
}

// google recaptcha confirm
if ( $use_captcha == 'yes' ) {
    if ( 'v2' == $recaptcha_version ) {
        $captcha_output .= '<div class="g-recaptcha" data-sitekey="' . $captcha_site_key . '"></div>';
        if ( isset( $_POST['g-recaptcha-response'] ) ) {
            $captcha = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
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
        }
    } elseif ( 'v3' == $recaptcha_version ) {
        if ( isset( $_POST['token'] ) ) {
            $captcha = sanitize_text_field( wp_unslash( $_POST['token'] ) );
            $action = sanitize_text_field( wp_unslash( $_POST['action'] ) );

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
                if ( ! $captcha_response['success'] || ! $captcha_response['action'] == $action || $captcha_response['score'] < 0.5 ) {
                    $valid_captcha = false;
                }
            }
        }
    } else {
        $valid_captcha = false;
    }
}

// get user email
if ( isset($current_user) && $current_user->ID > 0 ) {
    $current_user_email = $current_user->data->user_email;
}

// get post permalink
$post_permalink = get_permalink( $post_ID );

// challenge question
$challenge_question_state = get_option( 'subscribe_reloaded_use_challenge_question', 'no' );
$challenge_question = get_option( 'subscribe_reloaded_challenge_question', 'What is 1 + 2?' );
$challenge_answer = get_option( 'subscribe_reloaded_challenge_answer', '3' );

// start output buffer
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

    // captcha is not valid
    if ( ! $valid_captcha ) {
        $valid_all = false;
    }

    // email is valid
    if ( $valid_all ) {

        // Use Akismet, if available, to check this user is legit
        if ( function_exists( 'akismet_http_post' ) ) {

            global $akismet_api_host, $akismet_api_port;

            $user_agent            = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
            $akismet_query_string  = "user_ip={$_SERVER['REMOTE_ADDR']}";
            $akismet_query_string .= "&user_agent=" . esc_url( stripslashes( $user_agent ) );
            $akismet_query_string .= "&blog=" . esc_url( get_option( 'home' ) );
            $akismet_query_string .= "&blog_lang=" . get_locale();
            $akismet_query_string .= "&blog_charset=" . get_option( 'blog_charset' );
            $akismet_query_string .= "&permalink=".esc_url( $post_permalink );
            $akismet_query_string .= "&comment_author_email=" . esc_url( sanitize_email( $email ) );

            $akismet_response = akismet_http_post( $akismet_query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );

            // If this is considered SPAM, we stop here
            if ( $akismet_response[1] == 'true' ) {
                ob_end_clean();
                return '';
            }

        }

        // sanitize email address
        $clean_email = $wp_subscribe_reloaded->stcr->utils->clean_email( $email );

        // notify the administrator about the new subscription
        if ( get_option( 'subscribe_reloaded_enable_admin_messages' ) == 'yes' ) {

            $from_name  = stripslashes( get_option( 'subscribe_reloaded_from_name', 'admin' ) );
            $from_email = get_option( 'subscribe_reloaded_from_email', get_bloginfo( 'admin_email' ) );

            $subject = esc_html__( 'New subscription to', 'subscribe-to-comments-reloaded' ) . " $target_post->post_title";
            $message = esc_html__( 'New subscription to', 'subscribe-to-comments-reloaded' ) . " $target_post->post_title\n" . esc_html__( 'User:', 'subscribe-to-comments-reloaded' ) . " $clean_email";

            $email_settings = array(
                'subject'      => $subject,
                'message'      => $message,
                'toEmail'      => get_bloginfo( 'admin_email' )
            );

			$has_blacklist_email = $this->utils->blacklisted_emails( $clean_email );
			// Send the confirmation email only if the email
			// address is not in blacklist email list.
			if ( $has_blacklist_email ) {
				$wp_subscribe_reloaded->stcr->utils->send_email( $email_settings );
			}

        }

        // double check, send confirmation email
        if ( get_option( 'subscribe_reloaded_enable_double_check' ) == 'yes' && ! $wp_subscribe_reloaded->stcr->is_user_subscribed( $post_ID, $clean_email, 'C' ) ) {
            $wp_subscribe_reloaded->stcr->add_subscription( $post_ID, $clean_email, 'YC' );
            $wp_subscribe_reloaded->stcr->confirmation_email( $post_ID, $clean_email );
            $message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscription_confirmed_dci' ) ), ENT_QUOTES, 'UTF-8' );

        // not double check, add subscription
        } else {
            $this->add_subscription( $post_ID, $clean_email, 'Y' );
            $message = html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscription_confirmed' ) ), ENT_QUOTES, 'UTF-8' );
        }

        // new subscription message
        $message = str_replace( '[post_permalink]', $post_permalink, $message );
        if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
            $message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $target_post->post_title ), $message );
            $message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
        } else {
            $message = str_replace( '[post_title]', $target_post->post_title, $message );
        }

        echo wp_kses( wpautop( $message ), wp_kses_allowed_html( 'post' ) );

    }

// no email address supplied
} else {

    // email value for input field
    if ( isset( $current_user_email ) ) {
        $email = $current_user_email;
    } else if ( isset( $_COOKIE['comment_author_email_' . COOKIEHASH] )) {
        $email = sanitize_email( wp_unslash( $_COOKIE[ 'comment_author_email_' . COOKIEHASH ] ) );
    } else {
        $email = '';
    }

    // output message for subscribing without commenting
    $message = str_replace( '[post_permalink]', $post_permalink, html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscribe_without_commenting' ) ), ENT_QUOTES, 'UTF-8' ) );
    if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
        $message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $target_post->post_title ), $message );
        $message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
    } else {
        $message = str_replace( '[post_title]', $target_post->post_title, $message );
    }
    echo '<p>' . wp_kses( $message, wp_kses_allowed_html( 'post' ) ) . '</p>';

    // output the form


    ?>
    <?php $server_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''; ?>
    <form action="<?php echo esc_url( $server_request_uri ); ?>" method="post" name="sub-form">
        <fieldset style="border:0">
            <div>
                <?php if ( $challenge_question_state == 'yes' ) : ?>
                    <p>
                        <label for="sre"><?php esc_html_e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
                        <input id='sre' type="text" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $email ); ?>" size="22" required />
                    </p>
                    <p>
                        <label for="subscribe-reloaded-challenge"><?php echo esc_html( $challenge_question ); ?></label>
                        <input id="subscribe-reloaded-challenge" type="text" class="subscribe-form-field" name="subscribe_reloaded_challenge" />
                    </p>
                    <p>
                        <input name="submit" type="submit" class="subscribe-form-button" value="<?php esc_attr_e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
                    </p>
                    <?php echo wp_kses( $captcha_output, wp_kses_allowed_html( 'post' ) ); ?>
                    <p class="notice-email-error" style='color: #f55252;font-weight:bold; display: none;'></p>
                <?php else : ?>
                    <p>
                        <label for="sre"><?php esc_html_e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
                        <input id='sre' type="text" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $email ); ?>" size="22" required />
                        <input name="submit" type="submit" class="subscribe-form-button" value="<?php esc_attr_e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
                    </p>
                    <?php echo wp_kses( $captcha_output, wp_kses_allowed_html( 'post' ) ); ?>
                    <p class="notice-email-error" style='color: #f55252;font-weight:bold; display: none;'></p>
                <?php endif; ?>
            </div>
        </fieldset>
    </form>
    <?php

}

if ( $use_captcha == 'yes' && $valid_captcha && 'v3' == $recaptcha_version ) {
    ?>
    <div class="stcr-recaptcha">
        <script src="https://www.google.com/recaptcha/api.js?render=<?php echo esc_attr( $captcha_site_key ); ?>"></script>
        <script>
            jQuery(document).ready(function(){
                jQuery('form.sub-form').on( 'submit', function(event) {

                    event.preventDefault();
                    var stcrForm  = jQuery(this);
                    var stcrEmail = jQuery(this).find('#sre').val();

                    grecaptcha.ready(function() {
                        grecaptcha.execute('<?php echo esc_attr( $captcha_site_key ); ?>', {action: 'subscribe'}).then(function(token) {
                            stcrForm.prepend('<input type="hidden" name="token" value="' + token + '">');
                            stcrForm.prepend('<input type="hidden" name="action" value="subscribe">');
                            stcrForm.unbind('submit').submit();
                        });
                    });

                });
            });
        </script>
    </div>
    <?php
}

// invalid email
if ( ! $valid_all ) {

    // message for subscribing without commenting
    $message = str_replace( '[post_permalink]', $post_permalink, html_entity_decode( stripslashes( get_option( 'subscribe_reloaded_subscribe_without_commenting' ) ), ENT_QUOTES, 'UTF-8' ) );
    if ( function_exists( 'qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage' ) ) {
        $message = str_replace( '[post_title]', qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $target_post->post_title ), $message );
        $message = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage( $message );
    } else {
        $message = str_replace( '[post_title]', esc_html( $target_post->post_title ), $message );
    }
    echo '<p>' . wp_kses( $message, wp_kses_allowed_html( 'post' ) ) . '</p>';

    ?>
    <?php $server_request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''; ?>
    <form action="<?php echo esc_url( $server_request_uri ); ?>" method="post" name="sub-form">
        <fieldset style="border:0">

            <?php if ( $challenge_question_state == 'yes' ) : ?>
                <p>
                    <label for="sre"><?php esc_html_e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
                    <input id='sre' type="text" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $email ); ?>" size="22" required />
                </p>
                <p>
                    <label for="subscribe-reloaded-challenge"><?php echo esc_html( $challenge_question ); ?></label>
                    <input id="subscribe-reloaded-challenge" type="text" class="subscribe-form-field" name="subscribe_reloaded_challenge" />
                </p>
                <p>
                    <input name="submit" type="submit" class="subscribe-form-button" value="<?php esc_attr_e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
                </p>
                <?php echo wp_kses( $captcha_output, wp_kses_allowed_html( 'post' ) ); ?>
                <p class="notice-email-error" style='color: #f55252;font-weight:bold; display: none;'></p>
            <?php else : ?>
                <label for="sre"><?php esc_html_e( 'Email', 'subscribe-to-comments-reloaded' ) ?></label>
                <input id='sre' type="text" class="subscribe-form-field" name="sre" value="<?php echo esc_attr( $email ); ?>" size="22" required />
                <input name="submit" type="submit" class="subscribe-form-button" value="<?php esc_attr_e( 'Send', 'subscribe-to-comments-reloaded' ) ?>" />
                <?php echo wp_kses( $captcha_output, wp_kses_allowed_html( 'post' ) ); ?>
                <p class="notice-email-error" style='color: #f55252;font-weight:bold; display: none;'></p>
            <?php endif; ?>

            <?php if ( ! $valid_email ) : ?>
                <p style='color: #f55252;font-weight:bold;'><i class="fa fa-exclamation-triangle"></i> <?php esc_html_e("Email address is not valid", 'subscribe-to-comments-reloaded') ?></p>
            <?php endif; ?>

            <?php if ( ! $valid_challenge ) : ?>
                <p style='color: #f55252;font-weight:bold;'><i class="fa fa-exclamation-triangle"></i> <?php esc_html_e("Challenge answer is not correct", 'subscribe-to-comments-reloaded') ?></p>
            <?php endif; ?>

            <?php if ( ! $valid_captcha ) : ?>
                <p style='color: #f55252;font-weight:bold;'><i class="fa fa-exclamation-triangle"></i> <?php esc_html_e("Challenge answer is not correct", 'subscribe-to-comments-reloaded') ?></p>
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
