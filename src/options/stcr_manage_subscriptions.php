<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

$action = esc_attr( ! empty( $_POST['sra'] ) ? $_POST['sra'] : ( ! empty( $_GET['sra'] ) ? $_GET['sra'] : '' ) );
if ( $action == 'edit-subscription' || $action == 'add-subscription' ) {
	require_once WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/options/panel1-' . $action . '.php';

	return;
}
if ( is_readable( WP_PLUGIN_DIR . "/subscribe-to-comments-reloaded/options/panel1-business-logic.php" ) ) {

	require_once WP_PLUGIN_DIR . '/subscribe-to-comments-reloaded/options/panel1-business-logic.php';

    // Display an alert in the admin interface if the email is wrong or the post id is not a number.
    if ( ! $valid_email )
    {
        echo '<div class="notice notice-error is-dismissible"><p>';
            _e( 'The email that you typed is not correct.', 'subscribe-to-comments-reloaded' );
        echo "</p></div>";
    }

    if ( ! $valid_post_id )
    {
        echo '<div class="notice notice-error is-dismissible"><p>';
            _e( 'Please enter a valid Post ID.', 'subscribe-to-comments-reloaded' );
        echo "</p></div>";
    }
}

?>
<style type="text/css">

</style>

<link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/datatables/media/css/jquery.dataTables.min.css" rel="stylesheet"/>
<link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/datatables/media/css/dataTables.bootstrap4.min.css" rel="stylesheet"/>
<link href="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet"/>

<div class="container-fluid">

    <div class="row mx-auto">
        <div class="col-sm-6">
            <div class="card card-font-size mass-update-subs">
                <h6 class="card-header">
                    <i class="fas fa-exchange-alt"></i> <?php _e( 'Mass Update Subscriptions', 'subscribe-to-comments-reloaded' ) ?>
                    <i class="fas fa-caret-down pull-right"></i>
                </h6>
                <div class="card-body cbody-mass" style="padding: 0;">
                    <div class="card-text stcr-hidden">
                        <form action="" method="post" id="mass_update_address_form">

                            <table>
                                <tr>
                                    <td><label for='oldsre'><?php _e( 'From', 'subscribe-to-comments-reloaded' ) ?></label></td>
                                    <td><input class="form-control form-controls-font" type='text' size='30' name='oldsre' id='oldsre' value='<?php esc_attr_e( 'email address', 'subscribe-to-comments-reloaded' ) ?>' style="color:#ccc;"></td>
                                    <td><span class="validate-error-text validate-error-text-oldsre stcr-hidden "></span></td>
                                </tr>
                                <tr>
                                    <td><label for='sre'><?php _e( 'To', 'subscribe-to-comments-reloaded' ) ?></label></td>
                                    <td><input class="form-control form-controls-font" type='text' size='30' name='sre' id='sre' value='<?php esc_attr_e( 'optional - new email address', 'subscribe-to-comments-reloaded' ) ?>' style="color:#ccc;"
                                        >
                                    </td>
                                    <td><span class="validate-error-text validate-error-text-sre stcr-hidden "></span></td>
                                </tr>
                                <tr>
                                    <td><label for='srs'><?php _e( 'Status', 'subscribe-to-comments-reloaded' ) ?></label></td>
                                    <td><select class="form-control form-controls-font mass-update-select-status" name="srs" id="srs">
                                            <option value=''><?php _e( 'Keep unchanged', 'subscribe-to-comments-reloaded' ) ?></option>
                                            <option value='Y'><?php _e( 'Active', 'subscribe-to-comments-reloaded' ) ?></option>
                                            <option value='R'><?php _e( 'Replies only', 'subscribe-to-comments-reloaded' ) ?></option>
                                            <option value='C'><?php _e( 'Suspended', 'subscribe-to-comments-reloaded' ) ?></option>
                                        </select>
                                        <input type='submit' style="font-size: 0.8rem;" class='subscribe-form-button btn btn-primary' value='<?php esc_attr_e( 'Update', 'subscribe-to-comments-reloaded' ) ?>' ></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="more-info" data-infopanel="info-panel-mass-update" aria-label="<?php _e("More info", 'subscribe-to-comments-reloaded'); ?>">
                                            <i class="fas fa-question-circle"></i>
                                        </div>
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="2"><input type='hidden' name='sra' value='edit' /></td>
                                </tr>
                            </table>

                            <div class="alert alert-info hidden  info-panel-mass-update" role="alert">
                                <?php _e('This option will allow you to change an email address for another one or to update the same status for all the subscription on a specific email address.', 'subscribe-to-comments-reloaded' ); ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card card-font-size add-new-subs">
                <h6 class="card-header">
                    <i class="fas fa-plus-square"></i> <?php _e( 'Add New Subscription', 'subscribe-to-comments-reloaded' ) ?>
                    <i class="fas fa-caret-down pull-right"></i>
                </h6>
                <div class="card-body" style="padding: 0;">
                    <div class="card-text stcr-hidden">
                        <form action="" method="post" id="add_new_subscription">
                            <fieldset style="border:0">
                                <table>
                                    <tr>
                                        <td><?php _e( 'Post ID', 'subscribe-to-comments-reloaded' ) ?></td>
                                        <td><input class="form-control form-controls-font" type='text' size='30' name='srp' value='' ></td>
                                        <td><span class="validate-error-text validate-error-text-srp stcr-hidden "></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e( 'Email', 'subscribe-to-comments-reloaded' ) ?></td>
                                        <td><input class="form-control form-controls-font" type='text' size='30' name='sre' value='' ></td>
                                        <td><span class="validate-error-text validate-error-text-sre stcr-hidden "></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e( 'Status', 'subscribe-to-comments-reloaded' ) ?></td>
                                        <td>
                                            <select name="srs" class="form-control form-controls-font new-sub-select-status">
                                                <option value='Y'><?php _e( 'Active', 'subscribe-to-comments-reloaded' ) ?></option>
                                                <option value='R'><?php _e( 'Replies only', 'subscribe-to-comments-reloaded' ) ?></option>
                                                <option value='YC'><?php _e( 'Ask user to confirm', 'subscribe-to-comments-reloaded' ) ?></option>
                                            </select>
                                            <input type='submit' style="font-size: 0.8rem;" class='subscribe-form-button btn btn-primary' value='<?php esc_attr_e( 'Add', 'subscribe-to-comments-reloaded' ) ?>' >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><input type='hidden' name='sra' value='add' ></td>
                                    </tr>
                                </table>
                                &nbsp;
                            </fieldset>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row mx-auto">
        <div class="col-sm-12 col-md-12 col-lg-12">
            <div class="card card-subscribers" style="max-width: 100% !important;">
                <div class="card-body">

                    <div class="card-text postbox" style="border: none;">

                        <h4><i class="fas fa-search"></i> <?php _e( 'Search subscriptions', 'subscribe-to-comments-reloaded' ) ?></h4>

                        <?php if ( ! empty( $_POST['srv'] ) || ( is_array( $subscriptions ) && count( $subscriptions ) == 1000 ) ) : ?>

                            <?php
                                $search_term = '';
                                if ( ! empty( $_POST['srv'] ) ) {
                                    $search_term = sanitize_text_field( $_POST['srv'] );
                                }
                            ?>
                            <form action="" method="post" style="background: #f5f5f5; padding: 15px; margin-top: 20px;">
                                <p>
                                    <strong><?php esc_html_e( 'The table below is limited to loading 1000 latest subscriptions.', 'subscribe-to-comments-reloaded' ); ?></strong>
                                    <br><?php esc_html_e( 'You have more than that, if you need to find an older subscription just enter a search term ( full email, partial email, post ID...) below.', 'subscribe-to-comments-reloaded' ); ?>
                                </p>
                                <p>
                                    <input type="text" name="srv" placeholder="<?php esc_attr_e( 'Enter search term', 'subscribe-to-comments-reloaded' ); ?>" value="<?php echo esc_attr( $search_term ); ?>">
                                    <input type="submit" class="button button-primary">
                                </p>
                            </form>
                        
                        <?php endif; ?>

                        <div class="col-md-2 subs-spinner mx-auto"><h5><?php _e( "Loading", 'subscribe-to-comments-reloaded'); ?> <i class="fas fa-play-circle"></i></h5></div>

                        <div class="clearfix"></div>

                        <form style="border: 1px solid #eee; padding: 15px; margin-top: 20px;" action="" method="post" id="subscription_form" name="subscription_form"
                              onsubmit="if(this.sra[0].checked) return confirm('<?php _e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ) ?>')">

                                <?php

                                $alternate        = '';
                                $date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
                                // Let us form those status
                                $status_arry      = array(
                                                    'R'  => __( 'Replies', 'subscribe-to-comments-reloaded'),
                                                    'RC' => __( 'Replies Unconfirmed', 'subscribe-to-comments-reloaded'),
                                                    'Y'  => __( "All Comments", 'subscribe-to-comments-reloaded'),
                                                    'YC' => __( "Unconfirmed", 'subscribe-to-comments-reloaded'),
                                                    'C'	 => __( "Inactive", 'subscribe-to-comments-reloaded'),
                                                    '-C' => __( "Active", 'subscribe-to-comments-reloaded')
                                                );

                                if ( ! empty( $subscriptions ) && is_array( $subscriptions ) ) {

                                    $show_post_column  = ( $operator != 'equals' || $search_field != 'post_id' ) ?  __( 'Post (ID)', 'subscribe-to-comments-reloaded' ) : '';
                                    $show_email_column = ( $operator != 'equals' || $search_field != 'email' ) ? __( 'Email', 'subscribe-to-comments-reloaded' ) : '';

                                    echo "<table class=\"table table-smx table-hover table-striped subscribers-table stcr-hidden\" style=\"font-size: 0.8em\">
                                             <thead>";

                                    if( $wp_locale->text_direction == 'rtl' )
                                    {

                                        echo "<tr>
                                                  <th scope=\"col\">
                                                    &nbsp;&nbsp;&nbsp;<i class=\"fas fa-exchange-alt\"></i> <span>" . __('Actions', 'subscribe-to-comments-reloaded') ."</span>
                                                    <input class='checkbox' type='checkbox' name='subscription_list_select_all' id='stcr_select_all' class='stcr_select_all'/>
                                                  </th>
                                                  <th scope=\"col\"><i class=\"fas fa-thumbtack\"></i><span>$show_post_column</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-address-card\"></i><span>$show_email_column</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-calendar-alt\"></i><span>". __( 'Date and Time', 'subscribe-to-comments-reloaded' ) . "</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-info-circle\"></i><span>". __( 'Status', 'subscribe-to-comments-reloaded' ) . "</span></th>
                                              </tr>";
                                    }
                                    else
                                    {
                                        echo "<tr>
                                                  <th scope=\"col\">
                                                    <input class='checkbox' type='checkbox' name='subscription_list_select_all' id='stcr_select_all' class='stcr_select_all'/>
                                                    &nbsp;&nbsp;&nbsp;<i class=\"fas fa-exchange-alt\"></i> <span>" . __('Actions', 'subscribe-to-comments-reloaded') ."</span>
                                                  </th>
                                                  <th scope=\"col\"><i class=\"fas fa-thumbtack\"></i><span>$show_post_column</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-address-card\"></i><span>$show_email_column</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-calendar-alt\"></i><span>". __( 'Date and Time', 'subscribe-to-comments-reloaded' ) . "</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-info-circle\"></i><span>". __( 'Status', 'subscribe-to-comments-reloaded' ) . "</span></th>
                                              </tr>";
                                    }

                                    echo "</thead>";
                                      echo "<tbody>";

                                    foreach ( $subscriptions as $a_subscription ) {
                                        //$wp_subscribe_reloaded->stcr->utils->stcr_logger( print_r($a_subscription, true) );
                                        $title     = get_the_title( $a_subscription->post_id );
                                        $title     = ( strlen( $title ) > 35 ) ? substr( $title, 0, 35 ) . '..' : $title;
                                        if( $wp_locale->text_direction == 'rtl' )
                                        {
                                            $row_post  = ( $operator != 'equals' || $search_field != 'post_id' ) ? "<a  href='admin.php?page=stcr_manage_subscriptions&amp;srf=post_id&amp;srt=equals&amp;srv=$a_subscription->post_id'>($a_subscription->post_id) $title</a> " : '';
                                        }
                                        else
                                        {
                                            $row_post  = ( $operator != 'equals' || $search_field != 'post_id' ) ? "<a  href='admin.php?page=stcr_manage_subscriptions&amp;srf=post_id&amp;srt=equals&amp;srv=$a_subscription->post_id'>$title ($a_subscription->post_id)</a> " : '';
                                        }
                                        $row_email = ( $operator != 'equals' || $search_field != 'email' ) ? "<a href='admin.php?page=stcr_manage_subscriptions&amp;srf=email&amp;srt=equals&amp;srv=" . urlencode( $a_subscription->email ) . "' title='email unique key: ( $a_subscription->email_key )'>$a_subscription->email</a> " : '';
                                        $date_time = date_i18n( $date_time_format, strtotime( $a_subscription->dt ) );
                                        $date_time_sort = date_i18n( 'YmdHis', strtotime( $a_subscription->dt ) );

                                        $status_desc = $status_arry[$a_subscription->status];

                                        if( $wp_locale->text_direction == 'rtl' )
                                        {
                                            echo "<tr>
                                                        <td>
                                                            <label for='sub_{$a_subscription->meta_id}' class='hidden'>" . __( 'Subscription', 'subscribe-to-comments-reloaded' ) . " {$a_subscription->meta_id}</label>
                                                            <input class='checkbox' type='checkbox' name='subscriptions_list[]' value='$a_subscription->post_id," . urlencode( $a_subscription->email ) . "' id='sub_{$a_subscription->meta_id}' />                                                        
                                                            <a href='admin.php?page=stcr_manage_subscriptions&amp;sra=edit-subscription&amp;srp=" . $a_subscription->post_id . "&amp;sre=" . urlencode( $a_subscription->email ) . "' alt='" . __( 'Edit', 'subscribe-to-comments-reloaded' ) . "'><i class=\"fas fa-edit\" style='font-size: 1.1em;color: #ffc53a;'></i></a>
                                                            &nbsp;&nbsp;&nbsp;&nbsp;<a href='admin.php?page=stcr_manage_subscriptions&amp;sra=delete-subscription&amp;srp=" . $a_subscription->post_id . "&amp;sre=" . urlencode( $a_subscription->email ) . "' onclick='return confirm(\"" . __( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ) . "\");' alt='" . __( 'Delete', 'subscribe-to-comments-reloaded' ) . "'><i class=\"fas fa-trash-alt\" style='font-size: 1.1em;color: #ff695a;'></i></a>
                                                        </td>
                                                        <td>$row_post</td>
                                                        <td>$row_email</td>
                                                        <td data-sort='$date_time_sort'>$date_time</td>
                                                        <td>$status_desc</td>
                                                  </tr>";
                                        }
                                        else
                                        {
                                            echo "<tr>
                                                        <td>
                                                            <label for='sub_{$a_subscription->meta_id}' class='hidden'>" . __( 'Subscription', 'subscribe-to-comments-reloaded' ) . " {$a_subscription->meta_id}</label>
                                                            <input class='checkbox' type='checkbox' name='subscriptions_list[]' value='$a_subscription->post_id," . urlencode( $a_subscription->email ) . "' id='sub_{$a_subscription->meta_id}' />                                                        
                                                            <a href='admin.php?page=stcr_manage_subscriptions&amp;sra=edit-subscription&amp;srp=" . $a_subscription->post_id . "&amp;sre=" . urlencode( $a_subscription->email ) . "' alt='" . __( 'Edit', 'subscribe-to-comments-reloaded' ) . "'><i class=\"fas fa-edit\" style='font-size: 1.1em;color: #ffc53a;'></i></a>
                                                            &nbsp;&nbsp;&nbsp;&nbsp;<a href='admin.php?page=stcr_manage_subscriptions&amp;sra=delete-subscription&amp;srp=" . $a_subscription->post_id . "&amp;sre=" . urlencode( $a_subscription->email ) . "' onclick='return confirm(\"" . __( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ) . "\");' alt='" . __( 'Delete', 'subscribe-to-comments-reloaded' ) . "'><i class=\"fas fa-trash-alt\" style='font-size: 1.1em;color: #ff695a;'></i></a>
                                                        </td>
                                                        <td>$row_post</td>
                                                        <td>$row_email</td>
                                                        <td data-sort='$date_time_sort'>$date_time</td>
                                                        <td>$status_desc</td>
                                                  </tr>";
                                        }

                                    }
                                    echo "</tbody>";

                                    echo "</table>";

                                    echo "<div class='subscribers-mass-actions form-group row'>";
                                        echo '<label for="action_type" class="col-sm-1 col-form-label">' . __( 'Action:', 'subscribe-to-comments-reloaded' ) . '</label >' ;
                                    ?>          <div class="col-sm-3">
                                                    <select name="sra" id="action_type" class="form-control">
                                                        <option value="delete"><?php _e( 'Delete forever', 'subscribe-to-comments-reloaded' ) ?></option>
                                                        <option value="suspend"><?php _e( 'Suspend', 'subscribe-to-comments-reloaded' ) ?></option>
                                                        <option value="force_y"><?php _e( 'Activate and set to notify on all comments', 'subscribe-to-comments-reloaded' ) ?></option>
                                                        <option value="force_r"><?php _e( 'Activate and set to notify on replies only ', 'subscribe-to-comments-reloaded' ) ?></option>
                                                        <option value="activate"><?php _e( 'Activate', 'subscribe-to-comments-reloaded' ) ?></option>
                                                    </select>
                                                </div>
                                    <?php
                                                echo '<div class="col-sm-2"><button type="submit" class="subscribe-form-button btn btn-primary" >' . __( 'Update subscriptions', 'subscribe-to-comments-reloaded' ) . '</button></div>';
                                                echo "<input type='hidden' name='srf' value='$search_field'/><input type='hidden' name='srt' value='$operator'/><input type='hidden' name='srv' value='$search_value'/><input type='hidden' name='srsf' value='$offset'/><input type='hidden' name='srrp' value='$limit_results'/><input type='hidden' name='srob' value='$order_by'/><input type='hidden' name='sro' value='$order'/>";
                                    echo "</div>";

                                } elseif ( $action == 'search' ) {
                                    echo '<p>' . __( 'Sorry, no subscriptions match your search criteria.', 'subscribe-to-comments-reloaded' ) . "</p>";
                                }
                                ?>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
        <div class="col-sm-6 col-md-6 col-lg-6 mx-auto">
            <div class="card" style="max-width: 100% !important;">
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

<script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/datatables/media/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/datatables/media/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
<script type="text/javascript" src="<?php echo plugins_url(); ?>/subscribe-to-comments-reloaded/vendor/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

<?php
// Tell WP that we are going to use a resource.
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-subs-management", "subs_management.js", "includes/js/admin");
// Includes the Panel JS resource file as well as the JS text domain translations.
$wp_subscribe_reloaded->stcr->stcr_i18n->stcr_localize_script( "stcr-subs-management", "stcr_i18n", $wp_subscribe_reloaded->stcr->stcr_i18n->get_js_subs_translation() );
// Enqueue the JS File
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-subs-management" );

?>

