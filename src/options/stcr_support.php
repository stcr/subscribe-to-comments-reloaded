<?php
// Options

// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
    header( 'Location: /' );
    exit;
}


?>
<div class="container-fluid">
    <div class="mt-3"></div>
    <div class="row">
        <div class="col-sm-9">
            <div class="card card-font-size card-no-max-width">
                <div class="card-body">
                    <div class="card-text">

                        <h5><?php _e( "You can help", 'subscribe-to-comments-reloaded' ) ?></h5>
                        <p><?php _e( "Please consider blogging about my plugin with a link to the plugin's page. Please let your readers know what makes your blog better. You can also contribute donating your time: do not hesitate to send me bug reports, your localization files, ideas on how to improve <strong>Subscribe to Comments Reloaded</strong> and so on. Whatever you do, thanks for using my plugin!", 'subscribe-to-comments-reloaded' ) ?></p>

                        <h5><?php _e( "Subscribe to the Beta testers", 'subscribe-to-comments-reloaded' ) ?></h5>
                        <p><?php _e( "Before a new Update we release a Beta version so that our current users can give us feedback if they find a bug, If you want to join the tester list you can add your email <a href='http://eepurl.com/biCk1b' target='_blank'>here</a>", 'subscribe-to-comments-reloaded' ) ?></h5></p>

                        <h5><?php _e( "Vote and show your appreciation", 'subscribe-to-comments-reloaded' ) ?></h5>
                        <p><?php _e( 'Tell other people if <strong>Subscribe to Comments Reloaded</strong> works for you and how good it is. <a href="http://wordpress.org/extend/plugins/subscribe-to-comments-reloaded/">Rate it</a> on its Plugin Directory page.', 'subscribe-to-comments-reloaded' ) ?></p>

                        <h5><?php _e( "Did you find a Bug on the plugin?", 'subscribe-to-comments-reloaded' ) ?></h5>
                        <p><?php _e( 'Please report any bug on the <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues/new?title=Bug%20Report:%20%3Cshort%20description%3E&labels=bug" target="_blank">GitHub</a> Page rather than on the WordPress Support page.', 'subscribe-to-comments-reloaded' ) ?>
                        </p>
                        <div class="alert alert-info" role="alert">
                            <strong>Heads up!</strong>
                            <p><?php printf( __( 'The options on the WordPress forum at very limited to share media information, so I urge you to use GitHub to report any issue, you will get a better and faster experience than in WordPress. And you can use <a href="%s" target="_blank" >Markdown syntax</a>.', 'subscribe-to-comments-reloaded' ), "https://guides.github.com/features/mastering-markdown/" ); ?></p>
                        </div>
                    </div>
                </div>

            </div>
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