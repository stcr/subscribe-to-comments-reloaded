<?php
// Comment Form
//
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

$options = array(
    "show_subscription_box"         => "yesno",
    "checked_by_default"            => "yesno",
    "checked_by_default_value"      => "integer",
    "enable_advanced_subscriptions" => "yesno",
    "default_subscription_type"     => "integer",
    "checkbox_inline_style"         => "text-html",
    "checkbox_html"                 => "text-html",
    "checkbox_label"                => "text-html",
    "subscribed_label"              => "text-html",
    "subscribed_waiting_label"      => "text-html",
    "author_label"                  => "text-html"
);

// Update options
if ( isset( $_POST['options'] ) ) {
	$faulty_fields = array();

    foreach ( $_POST['options'] as $option => $value )
    {
//        echo $option . '<br>';
        if ( ! $wp_subscribe_reloaded->stcr->utils->stcr_update_menu_options( $option, $value, $options[$option] ) )
        {
            array_push( $faulty_fields, $option );
        }
    }

	// Display an alert in the admin interface if something went wrong
	echo '<div class="updated"><p>';
	if ( sizeof( $faulty_fields ) == 0 ) {
		_e( 'Your settings have been successfully updated.', 'subscribe-to-comments-reloaded' );
	} else {
		_e( 'There was an error updating the following fields:', 'subscribe-to-comments-reloaded' );
		// echo ' <strong>' . substr( $faulty_fields, 0, - 2 ) . '</strong>';
	}
	echo "</p></div>";
}
?>
<link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/webui-popover/dist/jquery.webui-popover.min.css" rel="stylesheet"/>

<div class="container-fluid">
    <div class="mt-3"></div>
    <div class="row">
        <div class="col-sm-9">
            <form action="" method="post">
                <div class="form-group row">
                    <label for="show_subscription_box" class="col-sm-3 col-form-label text-right"><?php _e( 'Enable default checkbox', 'subscribe-to-comments-reloaded' ) ?></label>
                    <div class="col-sm-7">
                        <div class="switch">
                            <input type="radio" class="switch-input" name="options[show_subscription_box]"
                                   value="yes" id="show_subscription_box-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'show_subscription_box' ) == 'yes' ) ? ' checked' : ''; ?> />
                            <label for="show_subscription_box-yes" class="switch-label switch-label-off">
                                <?php _e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                            </label>
                            <input type="radio" class="switch-input" name="options[show_subscription_box]" value="no" id="show_subscription_box-no"
                                <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'show_subscription_box' ) == 'no' ) ? '  checked' : ''; ?> />
                            <label for="show_subscription_box-no" class="switch-label switch-label-on">
                                <?php _e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                            </label>
                            <span class="switch-selection"></span>
                        </div>
                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( 'Disable this option if you want to move the subscription checkbox to a different place on your page.', 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( 'Disable this option if you want to move the subscription checkbox to a different place on your page.', 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="checked_by_default" class="col-sm-3 col-form-label text-right"><?php _e( 'Checked by default', 'subscribe-to-comments-reloaded' ) ?></label>
                    <div class="col-sm-7">
                        <div class="switch">
                            <input type="radio" class="switch-input" name="options[checked_by_default]"
                                   value="yes" id="checked_by_default-yes" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'checked_by_default' ) == 'yes' ) ? ' checked' : ''; ?> />
                            <label for="checked_by_default-yes" class="switch-label switch-label-off">
                                <?php _e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                            </label>
                            <input type="radio" class="switch-input" name="options[checked_by_default]" value="no" id="checked_by_default-no"
                                <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'checked_by_default' ) == 'no' ) ? '  checked' : ''; ?> />
                            <label for="checked_by_default-no" class="switch-label switch-label-on">
                                <?php _e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                            </label>
                            <span class="switch-selection"></span>
                        </div>
                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( 'Decide if the checkbox should be checked by default or not.', 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( 'Decide if the checkbox should be checked by default or not.', 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>
                    </div>
                </div>
            <?php
            // This option will be visible only when the Checkbox option is enable
            if ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_advanced_subscriptions' ) == 'no' ) :
            ?>
                <div class="form-group row">
                    <label for="checked_by_default_value" class="col-sm-3 col-form-label text-right"><?php _e( 'Subscription type', 'subscribe-to-comments-reloaded' ) ?></label>
                    <div class="col-sm-7">
                        <select name="options[checked_by_default_value]" id="checked_by_default_value" class="form-control form-control-select">
                            <option value="0" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'checked_by_default_value' ) === '0' ) ? "selected='selected'" : ''; ?>><?php _e( 'All new comments', 'subscribe-to-comments-reloaded' ); ?></option>
                            <option value="1" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'checked_by_default_value' ) === '1' ) ? "selected='selected'" : ''; ?>><?php _e( 'Replies to this comment', 'subscribe-to-comments-reloaded' ); ?></option>
                        </select>
                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( 'Select the type of subscription.', 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( 'Select the type of subscription.', 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>
                    </div>
                </div>
            <?php
            else :
                echo "<input type='hidden' name='options[checked_by_default_value]' value = '0'>";
            endif; ?>

                <div class="form-group row">
                    <label for="enable_advanced_subscriptions" class="col-sm-3 col-form-label text-right">
                        <?php _e( 'Advanced subscription', 'subscribe-to-comments-reloaded' ) ?></label>
                    <div class="col-sm-7">
                        <div class="switch">
                            <input type="radio" class="switch-input" name="options[enable_advanced_subscriptions]"
                                   value="yes" id="enable_advanced_subscriptions-yes"
                                <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_advanced_subscriptions' ) == 'yes' ) ? ' checked' : ''; ?> />
                            <label for="enable_advanced_subscriptions-yes" class="switch-label switch-label-off">
                                <?php _e( 'Yes', 'subscribe-to-comments-reloaded' ) ?>
                            </label>
                            <input type="radio" class="switch-input" name="options[enable_advanced_subscriptions]" value="no" id="enable_advanced_subscriptions-no"
                                <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_advanced_subscriptions' ) == 'no' ) ? '  checked' : ''; ?> />
                            <label for="enable_advanced_subscriptions-no" class="switch-label switch-label-on">
                                <?php _e( 'No', 'subscribe-to-comments-reloaded' ) ?>
                            </label>
                            <span class="switch-selection"></span>
                        </div>
                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( 'Allow users to choose from different subscription types (all, replies only).', 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( 'Allow users to choose from different subscription types (all, replies only).', 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>
                    </div>
                </div>

                <?php
                // Make sure that the default subscription type is visible only when advance subscriptions are set to yes.
                if ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'enable_advanced_subscriptions' ) == 'yes' ):    ?>
                    <div class="form-group row">
                        <label for="default_subscription_type" class="col-sm-3 col-form-label text-right">
                            <?php _e( 'Advanced default', 'subscribe-to-comments-reloaded' ) ?></label>
                        <div class="col-sm-7">
                            <select name="options[default_subscription_type]" id="default_subscription_type" class="form-control form-control-select">
                                <option value="0" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'default_subscription_type' ) === '0' ) ? "selected='selected'" : ''; ?>><?php _e( 'None', 'subscribe-to-comments-reloaded' ); ?></option>
                                <option value="1" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'default_subscription_type' ) === '1' ) ? "selected='selected'" : ''; ?>><?php _e( 'All new comments', 'subscribe-to-comments-reloaded' ); ?></option>
                                <option value="2" <?php echo ( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'default_subscription_type' ) === '2' ) ? "selected='selected'" : ''; ?>><?php _e( 'Replies to this comment', 'subscribe-to-comments-reloaded' ); ?></option>
                            </select>
                            <div class="helpDescription subsOptDescriptions"
                                 data-content="<?php _e( 'The default subscription type that should be selected when Advanced subscriptions are enable.', 'subscribe-to-comments-reloaded' ); ?>"
                                 data-placement="right"
                                 aria-label="<?php _e( 'The default subscription type that should be selected when Advanced subscriptions are enable.', 'subscribe-to-comments-reloaded' ); ?>">
                                <i class="fas fa-question-circle"></i>
                            </div>
                        </div>
                    </div>
                    <?php
                else :
                    echo "<input type='hidden' name='options[default_subscription_type]' value = '0'>";
                endif; ?>

                <div class="form-group row">
                    <label for="checkbox_inline_style" class="col-sm-3 col-form-label text-right">
                        <?php _e( 'Custom inline style', 'subscribe-to-comments-reloaded' ) ?></label>
                    <div class="col-sm-7">
                        <input type="text" name="options[checkbox_inline_style]" id="checkbox_inline_style"
                               class="form-control form-control-input-8"
                               value="<?php echo esc_attr( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( 'checkbox_inline_style' ) ); ?>" size="20">

                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( 'Custom inline CSS to add to the checkbox.', 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( 'Custom inline CSS to add to the checkbox.', 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="checkbox_html" class="col-sm-3 offset-sm-1 col-form-label">
                        <?php _e( 'Custom HTML', 'subscribe-to-comments-reloaded' ) ?>

                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( 'Custom HTML code to be used when displaying the checkbox. Allowed tags: [checkbox_field], [checkbox_label]', 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( 'Custom HTML code to be used when displaying the checkbox. Allowed tags: [checkbox_field], [checkbox_label]', 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>

                    </label>
                    <div class="clearfix"></div>
                    <div class="col-sm-9 offset-sm-1">
                        <?php
                        $id_checkbox_html = "checkbox_html";
                        $args_notificationContent = array(
                            "media_buttons" => false,
                            "textarea_rows" => 5,
                            "teeny"         => true,
                            "textarea_name" => "options[{$id_checkbox_html}]",
                            "tinymce"		=> false
                        );
                        wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_checkbox_html ), $id_checkbox_html, $args_notificationContent );
                        ?>
                    </div>
                </div>

                <h3><?php _e( 'Messages for your visitors', 'subscribe-to-comments-reloaded' ) ?></h3>

                <div class="form-group row">
                    <label for="checkbox_label" class="col-sm-3 offset-sm-1 col-form-label" style="z-index: 9999;">
                        <?php _e( 'Default label', 'subscribe-to-comments-reloaded' ) ?>

                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( 'Label associated to the checkbox. Allowed tag: [subscribe_link]', 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( 'Label associated to the checkbox. Allowed tag: [subscribe_link]', 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>

                    </label>
                    <div class="clearfix"></div>
                    <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                        <?php
                        $id_checkbox_label = "checkbox_label";
                        $args_notificationContent = array(
                            "media_buttons" => false,
                            "textarea_rows" => 3,
                            "teeny"         => true,
                            "textarea_name" => "options[{$id_checkbox_label}]"
                        );
                        wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_checkbox_label ), $id_checkbox_label, $args_notificationContent );
                        ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="subscribed_label" class="col-sm-3 offset-sm-1 col-form-label" style="z-index: 9999;">
                        <?php _e( 'Subscribed label', 'subscribe-to-comments-reloaded' ) ?>

                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( 'Label shown to those who are already subscribed to a post. Allowed tag: [manager_link]', 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( 'Label shown to those who are already subscribed to a post. Allowed tag: [manager_link]', 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>

                    </label>
                    <div class="clearfix"></div>
                    <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                        <?php
                        $id_subscribed_label = "subscribed_label";
                        $args_notificationContent = array(
                            "media_buttons" => false,
                            "textarea_rows" => 3,
                            "teeny"         => true,
                            "textarea_name" => "options[{$id_subscribed_label}]"
                        );
                        wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_subscribed_label ), $id_subscribed_label, $args_notificationContent );
                        ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="subscribed_waiting_label" class="col-sm-3 offset-sm-1 col-form-label" style="z-index: 9999;">
                        <?php _e( 'Pending label', 'subscribe-to-comments-reloaded' ) ?>

                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( "Label shown to those who are already subscribed, but haven't clicked on the confirmation link yet. Allowed tag: [manager_link]", 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( "Label shown to those who are already subscribed, but haven't clicked on the confirmation link yet. Allowed tag: [manager_link]", 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>

                    </label>
                    <div class="clearfix"></div>
                    <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                        <?php
                        $id_subscribed_waiting_label = "subscribed_waiting_label";
                        $args_notificationContent = array(
                            "media_buttons" => false,
                            "textarea_rows" => 3,
                            "teeny"         => true,
                            "textarea_name" => "options[{$id_subscribed_waiting_label}]"
                        );
                        wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_subscribed_waiting_label ), $id_subscribed_waiting_label, $args_notificationContent );
                        ?>
                    </div>
                </div>

                <div class="form-group row">
                    <label for="author_label" class="col-sm-3 offset-sm-1 col-form-label" style="z-index: 9999;">
                        <?php _e( 'Author label', 'subscribe-to-comments-reloaded' ) ?>

                        <div class="helpDescription subsOptDescriptions"
                             data-content="<?php _e( "Label shown to authors (and administrators). Allowed tag: [manager_link]", 'subscribe-to-comments-reloaded' ); ?>"
                             data-placement="right"
                             aria-label="<?php _e( "Label shown to authors (and administrators). Allowed tag: [manager_link]", 'subscribe-to-comments-reloaded' ); ?>">
                            <i class="fas fa-question-circle"></i>
                        </div>

                    </label>
                    <div class="clearfix"></div>
                    <div class="col-sm-9 offset-sm-1" style="margin-top: -30px;">
                        <?php
                        $id_author_label = "author_label";
                        $args_notificationContent = array(
                            "media_buttons" => false,
                            "textarea_rows" => 3,
                            "teeny"         => true,
                            "textarea_name" => "options[{$id_author_label}]"
                        );
                        wp_editor( $wp_subscribe_reloaded->stcr->utils->stcr_get_menu_options( $id_author_label ), $id_author_label, $args_notificationContent );
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
// Includes the Panel JS resource file as well as the JS text domain translations.
//$wp_subscribe_reloaded->stcr->stcr_i18n->stcr_localize_script( "stcr-subs-options", "stcr_i18n", $wp_subscribe_reloaded->stcr->stcr_i18n->get_js_subs_translation() );
// Enqueue the JS File
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-subs-options" );

?>
