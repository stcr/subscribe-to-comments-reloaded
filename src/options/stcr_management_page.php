<?php
// Management Page

// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
    header( 'Location: /' );
    exit;
}

$options = array(
    "manager_page_enabled"         => "yesno",
    "manager_page_title"           => "text",
    "manager_page"                 => "url",
    "custom_header_meta"           => "text-html",
    "request_mgmt_link"            => "text-html",
    "request_mgmt_link_thankyou"   => "text-html",
    "subscribe_without_commenting" => "text-html",
    "subscription_confirmed"       => "text-html",
    "subscription_confirmed_dci"   => "text-html",
    "author_text"                  => "text-html",
    "user_text"                    => "text-html"
);

$options_readable = array(
        "manager_page" => __("Management URL",'subscribe-to-comments-reloaded')
);

// Update options
if ( isset( $_POST['options'] ) ) {
    $faulty_fields = array();

    foreach ( $_POST['options'] as $option => $value )
    {
        if ( ! $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( $option, $value, $options[$option] ) )
        {
            array_push( $faulty_fields, $option );
        }
        if( $option == "manager_page" && $value == "") //TODO: Add validation for all he require fields.
        {
            array_push( $faulty_fields, $option );
        }
    }

    // Display an alert in the admin interface if something went wrong
    if ( sizeof( $faulty_fields ) == 0 ) {
        echo '<div class="updated"><p>';
            _e( 'Your settings have been successfully updated.', 'subscribe-to-comments-reloaded' );
        echo "</p></div>";
    } else {
        echo '<div class="error"><p>';
            _e( 'There was an error updating the following fields:', 'subscribe-to-comments-reloaded' );
            echo "<ul style='font-size: 0.8em;'>";
            foreach( $faulty_fields as $field )
            {
                echo ' <li>> ' . $options_readable[$field] . '</li>';
            }
            echo "</ul>";
        echo "</p></div>";
    }
}
wp_print_scripts( 'quicktags' );

?>
    <link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.css" rel="stylesheet"/>

    <div class="container-fluid">
        <div class="mt-3"></div>
        <div class="row">
            <div class="col-sm-9">
                <form class="management_page_form" action="" method="post">
                    <div class="form-group row">
                        <label for="manager_page_enabled" class="col-sm-3 col-form-label text-right"><?php _e( 'Virtual Management Page', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <div class="switch">
                                <input type="radio" class="switch-input" name="options[manager_page_enabled]"
                                       value="yes" id="manager_page_enabled-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'manager_page_enabled' ) == 'yes' ) ? ' checked' : ''; ?> />
                                <label for="manager_page_enabled-yes" class="switch-label switch-label-off">
                                    <?php _e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <input type="radio" class="switch-input" name="options[manager_page_enabled]" value="no" id="manager_page_enabled-no"
                                    <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'manager_page_enabled' ) == 'no' ) ? '  checked' : ''; ?> />
                                <label for="manager_page_enabled-no" class="switch-label switch-label-on">
                                    <?php _e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                                </label>
                                <span class="switch-selection"></span>
                            </div>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Disable the virtual management page if you need to create a <a href='https://github.com/stcr/subscribe-to-comments-reloaded/wiki/KB#create-a-real-management-page'>real page</a> to make your theme happy.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Disable the virtual management page if you need to create a <a href='https://github.com/stcr/subscribe-to-comments-reloaded/wiki/KB#create-a-real-management-page'>real page</a> to make your theme happy.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="manager_page_title" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Page title', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[manager_page_title]" id="manager_page_title"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'manager_page_title' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( 'Title of the page your visitors will use to manage their subscriptions.', 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( 'Title of the page your visitors will use to manage their subscriptions.', 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="manager_page" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Management URL', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <code><?php echo get_bloginfo( 'url' ) ?></code>
                            <input type="text" name="options[manager_page]" id="manager_page"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'manager_page' ) ); ?>" style=" width: 60% !important;">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "The permalink for your management page (something like <code>/manage-subscriptions</code> or <code>/?page_id=345</code>). This page <b>does not</b> actually exist in the system, but its link must follow your permalink structure.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="bottom"
                                 aria-label="<?php _e( "The permalink for your management page (something like <code>/manage-subscriptions</code> or <code>/?page_id=345</code>). This page <b>does not</b> actually exist in the system, but its link must follow your permalink structure.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                            <?php
                            if ( ( get_option( 'permalink_structure' ) == '' ) && ( strpos( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'manager_page' ), '?page_id=' ) === false ) ) {
                                    echo "<div class=\"alert alert-danger\" role=\"alert\">";
                                    echo '<strong>' . __( "Warning: it looks like the value you are using may be incompatible with your permalink structure", 'subscribe-to-comments-reloaded' ) . '</strong>';
                                    echo "</div>";
                            }
                                ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="custom_header_meta" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Custom HEAD meta', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <input type="text" name="options[custom_header_meta]" id="custom_header_meta"
                                   class="form-control form-control-input-8"
                                   value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'custom_header_meta' ) ); ?>" size="20">

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( 'Specify your custom HTML code to be added to the HEAD section of the page. Use <strong>single</strong> quotes for values.', 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( 'Specify your custom HTML code to be added to the HEAD section of the page. Use <strong>single</strong> quotes for values.', 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>

                    <h3><?php _e( 'Messages', 'subscribe-to-comments-reloaded' ) ?></h3>

                    <div class="form-group row">
                        <label for="request_mgmt_link" class="col-sm-2 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Request link', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( 'Text shown to those who request to manage their subscriptions.', 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( 'Text shown to those who request to manage their subscriptions.', 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_request_mgmt_link = "request_mgmt_link";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 3,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_request_mgmt_link}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_request_mgmt_link ), $id_request_mgmt_link, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row" style="display: none;">
                        <label for="request_mgmt_link_thankyou" class="col-sm-3 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Request submitted', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( 'Thank you note shown after the request here above has been processed. Allowed tags: [post_title], [post_permalink]', 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( 'Thank you note shown after the request here above has been processed. Allowed tags: [post_title], [post_permalink]', 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_request_mgmt_link_thankyou = "request_mgmt_link_thankyou";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 3,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_request_mgmt_link_thankyou}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_request_mgmt_link_thankyou ), $id_request_mgmt_link_thankyou, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="subscribe_without_commenting" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Subscribe without commenting', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( 'Text shown to those who want to subscribe without commenting. Allowed tags: [post_title], [post_permalink]', 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( 'Text shown to those who want to subscribe without commenting. Allowed tags: [post_title], [post_permalink]', 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_subscribe_without_commenting = "subscribe_without_commenting";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 3,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_subscribe_without_commenting}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_subscribe_without_commenting ), $id_subscribe_without_commenting, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="subscription_confirmed" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Subscription processed', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( 'Thank you note shown after the subscription request has been processed (double check-in disabled). Allowed tags: [post_title], [post_permalink]', 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( 'Thank you note shown after the subscription request has been processed (double check-in disabled). Allowed tags: [post_title], [post_permalink]', 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_subscription_confirmed = "subscription_confirmed";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 3,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_subscription_confirmed}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_subscription_confirmed ), $id_subscription_confirmed, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="subscription_confirmed_dci" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Subscription processed (DCI)', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( 'Thank you note shown after the subscription request has been processed (double check-in enabled). Allowed tags: [post_title], [post_permalink]', 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( 'Thank you note shown after the subscription request has been processed (double check-in enabled). Allowed tags: [post_title], [post_permalink]', 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_subscription_confirmed_dci = "subscription_confirmed_dci";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 3,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_subscription_confirmed_dci}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_subscription_confirmed_dci ), $id_subscription_confirmed_dci, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="author_text" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Authors', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Introductory text for the authors' management page.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Introductory text for the authors' management page.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_author_text = "author_text";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 3,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_author_text}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_author_text ), $id_author_text, $args_notificationContent );
                            ?>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="user_text" class="col-sm-4 offset-sm-1 col-form-label" style="z-index: 9999;">
                            <?php _e( 'Users', 'subscribe-to-comments-reloaded' ) ?>

                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( "Introductory text for the users' management page.", 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( "Introductory text for the users' management page.", 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>

                        </label>
                        <div class="clearfix"></div>
                        <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                            <?php
                            $id_user_text = "user_text";
                            $args_notificationContent = array(
                                "media_buttons" => false,
                                "textarea_rows" => 3,
                                "teeny"         => true,
                                "textarea_name" => "options[{$id_user_text}]"
                            );
                            wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_user_text ), $id_user_text, $args_notificationContent );
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
global $wp_subscribe_reloaded;
// Tell WP that we are going to use a resource.
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-subs-options", "subs_options.js", "includes/js/admin");
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-management-page", "management_page.js", "includes/js/admin");
// Includes the Panel JS resource file as well as the JS text domain translations.
//$wp_subscribe_reloaded->stcr->stcr_i18n->stcr_localize_script( "stcr-subs-options", "stcr_i18n", $wp_subscribe_reloaded->stcr->stcr_i18n->get_js_subs_translation() );
// Enqueue the JS File
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-subs-options" );
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-management-page" );

?>