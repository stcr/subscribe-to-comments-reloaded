<?php
// Notifications

// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
    header( 'Location: /' );
    exit;
}

$options = array(
    "from_name"                => "text",
    "from_email"               => "email",
    "reply_to"                 => "email",
    "notification_subject"     => "text",
    "notification_content"     => "text-html",
    "double_check_subject"     => "text",
    "double_check_content"     => "text-html",
    "management_subject"       => "text",
    "management_content"       => "text-html",
    "management_email_content" => "text-html",
    "oneclick_text"            => "text-html"
);

// Update options
if ( isset( $_POST['options'] ) ) {
    $faulty_fields = array();

    foreach ( $_POST['options'] as $option => $value )
    {
//        echo $option . '<br>';

        if ( $option === "notification_content" )
        {
            if ( trim( $value ) === "" &&
                ! $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( $option, "<h1>There is a new comment on [post_title].</h1><hr><p><strong>Comment link:</strong>&nbsp;<a href=\"[comment_permalink]\" data-mce-href=\"[comment_permalink]\">[comment_permalink]</a>&nbsp;<br><strong>Author:</strong>&nbsp;[comment_author]</p><p><strong>Comment:</strong><br>[comment_content]</p><div style=\"font-size: 0.8em\" data-mce-style=\"font-size: 0.8em;\"><strong>Permalink:</strong>&nbsp;<a href=\"[post_permalink]\" data-mce-href=\"[post_permalink]\">[post_permalink]</a><br><a href=\"[manager_link]\" data-mce-href=\"[manager_link]\">Manage your subscriptions</a>&nbsp;|&nbsp;<a href=\"[oneclick_link]\" data-mce-href=\"[oneclick_link]\">One click unsubscribe</a></div>", $options[$option] ) )
            {
                array_push( $faulty_fields, $option );
            }

            if ( trim( $value ) !== "" &&
                ! $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( $option, $value, $options[$option] )  )
            {
                array_push( $faulty_fields, $option );
            }
        }
        elseif ( ! $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( $option, $value, $options[$option] ) )
        {
            array_push( $faulty_fields, $option );
        }
    }

    // Display an alert in the admin interface if something went wrong
    echo '<div class="updated"><p>';
    if ( sizeof( $faulty_fields ) == 0 ) {
        _e( 'Your settings have been successfully updated.', 'subscribe-to-comments-reloaded' );
    } else {
        _e( 'There was an error updating the options.', 'subscribe-to-comments-reloaded' );
        // echo ' <strong>' . substr( $faulty_fields, 0, - 2 ) . '</strong>';
    }
    echo "</p></div>";
}
wp_print_scripts( 'quicktags' );

?>
    <link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.css" rel="stylesheet"/>

    <div class="container-fluid">
        <div class="mt-3"></div>
        <div class="row">
            <div class="col-sm-9">
                <form action="" method="post">

                    <div class="form-group row">
                        <label for="from_name" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Sender email address', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[from_name]" id="from_name"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'from_name' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Name to use for the 'from' field when sending a new notification to the user.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Name to use for the 'from' field when sending a new notification to the user.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="from_email" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Sender email address', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[from_email]" id="from_email"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'from_email' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Email address to use for the \"from\" field when sending a new notification to the user.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Email address to use for the \"from\" field when sending a new notification to the user.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="reply_to" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Reply To', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[reply_to]" id="reply_to"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'reply_to' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "This will be use when the user click reply on their email agent. If not set it will be the same as the Sender email address.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "This will be use when the user click reply on their email agent. If not set it will be the same as the Sender email address.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <h3><?php _e( 'Messages', 'subscribe-to-comments-reloaded' ) ?></h3>

                    <div class="form-group row">
                        <label for="notification_subject" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Notification subject', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[notification_subject]" id="notification_subject"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'notification_subject' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Subject of the notification email. Allowed tag: [post_title]", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Subject of the notification email. Allowed tag: [post_title]", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="notification_content" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Notification message', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Content of the notification email. Allowed tags: [post_title], [comment_permalink], [comment_author], [comment_content], [post_permalink], [manager_link], [comment_gravatar]<p style='color: #156dc7;'><strong>Note: To get a default template clear all the content and save the options.</strong></p>", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Content of the notification email. Allowed tags: [post_title], [comment_permalink], [comment_author], [comment_content], [post_permalink], [manager_link], [comment_gravatar]<p style='color: #156dc7;'><strong>Note: To get a default template clear all the content and save the options.</strong></p>", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_notification_content = "notification_content";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 15,
                                "teeny"         => true,
                                "textarea_name" => "options[$id_notification_content]"
                                // "tinymce"		=> array(
                                // 						"theme_advance_buttons1" => "bold, italic, ul, min_size, max_size"
                                // 					)
                            );

                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_notification_content ), $id_notification_content, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="double_check_subject" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Double check subject', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[double_check_subject]" id="double_check_subject"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'double_check_subject' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Subject of the confirmation email. Allowed tag: [post_title]", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Subject of the confirmation email. Allowed tag: [post_title]", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="double_check_content" class="col-sm-3 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Double check message', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Content of the confirmation email. Allowed tags: [post_permalink], [confirm_link], [post_title], [manager_link]", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Content of the confirmation email. Allowed tags: [post_permalink], [confirm_link], [post_title], [manager_link]", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_double_check_content = "double_check_content";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 7,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_double_check_content}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_double_check_content ), $id_double_check_content, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="management_subject" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Management subject', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[management_subject]" id="management_subject"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'management_subject' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Subject of the mail sent to those who request to access their management page. Allowed tag: [blog_name]", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Subject of the mail sent to those who request to access their management page. Allowed tag: [blog_name]", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>
                    
                    <?php 
                        $disallowed_tags = array();
                        $management_page_message = $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'management_content' );
                        if ( empty( $management_page_message ) ) { $management_page_message = ''; }
                        if ( strpos( $management_page_message, '[manager_link]' ) ) {
                            $disallowed_tags['manager_link'] = __( '[manager_link] tag only works for "Management Email message". It is a private link that takes to a management page and for security reasons has to be sent to the email address.', 'subscribe-to-comments-reloaded' );
                        }
                    ?>
                    <div class="form-group row">
                        <label for="management_content" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Management Page message', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Content of the management Page message. Allowed tags: [blog_name].", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Content of the management Page message. Allowed tags: [blog_name].", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" <?php if ( empty( $disallowed_tags ) ) { echo 'style="margin-top:-30px;"'; } ?>>
                            
                            <?php if ( ! empty( $disallowed_tags ) ) : ?>
                                <p class="notice notice-error" style="margin: 0;padding:8px;">
                                    <?php foreach ( $disallowed_tags as $disallowed_tag_id => $disallowed_tag_message ) : ?>
                                        <?php echo $disallowed_tag_message; ?>
                                    <?php endforeach; ?>
                                    </ul>
                                </p>
                            <?php endif; ?>

                            <?php
                            $id_management_content = "management_content";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 5,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_management_content}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_management_content ), $id_management_content, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="management_email_content" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Management Email message', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Content of the management email message. Allowed tags: [blog_name], [manager_link].", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Content of the management email message. Allowed tags: [blog_name], [manager_link].", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_management_email_content = "management_email_content";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 5,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_management_email_content}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_management_email_content ), $id_management_email_content, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="oneclick_text" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'One Click Unsubscribe', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php esc_attr_e( "Content of the One Click confirmation. Allowed tags: [post_title], [blog_name].", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php esc_attr_e( "Content of the One Click confirmation. Allowed tags: [post_title], [blog_name].", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_oneclick_text = "oneclick_text";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 5,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_oneclick_text}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_oneclick_text ), $id_oneclick_text, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-9 offset-sm-1">
                            <button type="submit" class="btn btn-primary subscribe-form-button" name="Submit">
                                <?php _e( 'Save Changes', 'subscribe-to-comments-reloaded' ) ?>
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <div class="col-md-3">
                <div class="card card-font-size">
                    <div class="card-body">
                        <p>
                            Thank you for using Subscribe to Comments Reloaded. You can Support the plugin by rating it
                            <a href="https://wordpress.org/support/plugin/subscribe-to-comments-reloaded/reviews/#new-post" target="_blank"><img src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/images/rate.png" alt="Rate Subscribe to Comments Reloaded" style="vertical-align: sub;" /></a>
                        </p>
                        <p>
                            <i class="fas fa-bug"></i> Having issues? Please <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues/" target="_blank">create a ticket</a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.js"></script>
<?php
//global $wp_subscribe_reloaded;
// Tell WP that we are going to use a resource.
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-subs-options", "subs_options.js", "includes/js/admin");
// Includes the Panel JS resource file as well as the JS text domain translations.
//$wp_subscribe_reloaded->stcr->stcr_i18n->stcr_localize_script( "stcr-subs-options", "stcr_i18n", $wp_subscribe_reloaded->stcr->stcr_i18n->get_js_subs_translation() );
// Enqueue the JS File
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-subs-options" );

?>