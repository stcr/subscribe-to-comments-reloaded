<?php
// Avoid direct access to this piece of code
if ( ! function_exists( 'is_admin' ) || ! is_admin() ) {
	header( 'Location: /' );
	exit;
}

$action = ! empty( $_POST['sra'] ) ? sanitize_text_field( wp_unslash( $_POST['sra'] ) ) : ( ! empty( $_GET['sra'] ) ? sanitize_text_field( wp_unslash( $_GET['sra'] ) ) : '' );
if ( $action == 'edit-subscription' || $action == 'add-subscription' ) {
	require_once trailingslashit( dirname( STCR_PLUGIN_FILE ) ) . 'options/panel1-' . $action . '.php';

	return;
}
if ( is_readable( trailingslashit( dirname( STCR_PLUGIN_FILE ) ) . 'options/panel1-business-logic.php' ) ) {

	require_once trailingslashit( dirname( STCR_PLUGIN_FILE ) ) . 'options/panel1-business-logic.php';

    // Display an alert in the admin interface if the email is wrong or the post id is not a number.
    if ( ! $valid_email )
    {
        echo '<div class="notice notice-error is-dismissible"><p>';
            esc_html_e( 'The email that you typed is not correct.', 'subscribe-to-comments-reloaded' );
        echo '</p></div>';
    }

    if ( ! $valid_post_id )
    {
        echo '<div class="notice notice-error is-dismissible"><p>';
            esc_html_e( 'Please enter a valid Post ID.', 'subscribe-to-comments-reloaded' );
        echo '</p></div>';
    }
}

?>

<div class="container-fluid">

    <div class="row mx-auto">
        <div class="col-sm-6">
            <div class="card card-font-size mass-update-subs">
                <h6 class="card-header">
                    <i class="fas fa-exchange-alt"></i> <?php esc_html_e( 'Mass Update Subscriptions', 'subscribe-to-comments-reloaded' ) ?>
                    <i class="fas fa-caret-down pull-right"></i>
                </h6>
                <div class="card-body cbody-mass" style="padding: 0;">
                    <div class="card-text stcr-hidden">
                        <form action="" method="post" id="mass_update_address_form">

                            <table>
                                <tr>
                                    <td><label for='oldsre'><?php esc_html_e( 'From', 'subscribe-to-comments-reloaded' ) ?></label></td>
                                    <td><input class="form-control form-controls-font" type='text' size='30' name='oldsre' id='oldsre' value='<?php esc_attr_e( 'email address', 'subscribe-to-comments-reloaded' ) ?>' style="color:#ccc;"></td>
                                    <td><span class="validate-error-text validate-error-text-oldsre stcr-hidden "></span></td>
                                </tr>
                                <tr>
                                    <td><label for='sre'><?php esc_html_e( 'To', 'subscribe-to-comments-reloaded' ) ?></label></td>
                                    <td><input class="form-control form-controls-font" type='text' size='30' name='sre' id='sre' value='<?php esc_attr_e( 'optional - new email address', 'subscribe-to-comments-reloaded' ) ?>' style="color:#ccc;"
                                        >
                                    </td>
                                    <td><span class="validate-error-text validate-error-text-sre stcr-hidden "></span></td>
                                </tr>
                                <tr>
                                    <td><label for='srs'><?php esc_html_e( 'Status', 'subscribe-to-comments-reloaded' ) ?></label></td>
                                    <td><select class="form-control form-controls-font mass-update-select-status" name="srs" id="srs">
                                            <option value=''><?php esc_html_e( 'Keep unchanged', 'subscribe-to-comments-reloaded' ) ?></option>
                                            <option value='Y'><?php esc_html_e( 'Active', 'subscribe-to-comments-reloaded' ) ?></option>
                                            <option value='R'><?php esc_html_e( 'Replies only', 'subscribe-to-comments-reloaded' ) ?></option>
                                            <option value='C'><?php esc_html_e( 'Suspended', 'subscribe-to-comments-reloaded' ) ?></option>
                                        </select>
                                        <input type='submit' style="font-size: 0.8rem;" class='subscribe-form-button btn btn-primary' value='<?php esc_attr_e( 'Update', 'subscribe-to-comments-reloaded' ) ?>' ></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="more-info" data-infopanel="info-panel-mass-update" aria-label="<?php esc_html_e("More info", 'subscribe-to-comments-reloaded'); ?>">
                                            <i class="fas fa-question-circle"></i>
                                        </div>
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="2"><input type='hidden' name='sra' value='edit' /></td>
                                </tr>
                            </table>

                            <div class="alert alert-info hidden  info-panel-mass-update" role="alert">
                                <?php esc_html_e('This option will allow you to change an email address for another one or to update the same status for all the subscription on a specific email address.', 'subscribe-to-comments-reloaded' ); ?>
                            </div>

                            <?php wp_nonce_field( 'stcr_edit_subscription_nonce', 'stcr_edit_subscription_nonce' ); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card card-font-size add-new-subs">
                <h6 class="card-header">
                    <i class="fas fa-plus-square"></i> <?php esc_html_e( 'Add New Subscription', 'subscribe-to-comments-reloaded' ) ?>
                    <i class="fas fa-caret-down pull-right"></i>
                </h6>
                <div class="card-body" style="padding: 0;">
                    <div class="card-text stcr-hidden">
                        <form action="" method="post" id="add_new_subscription">
                            <fieldset style="border:0">
                                <table>
                                    <tr>
                                        <td><?php esc_html_e( 'Post ID', 'subscribe-to-comments-reloaded' ) ?></td>
                                        <td><input class="form-control form-controls-font" type='text' size='30' name='srp' value='' ></td>
                                        <td><span class="validate-error-text validate-error-text-srp stcr-hidden "></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e( 'Email', 'subscribe-to-comments-reloaded' ) ?></td>
                                        <td><textarea name='sre' class="form-control form-controls-font" cols="10" rows="6"></textarea></td>
                                        <td><span class="validate-error-text validate-error-text-sre stcr-hidden "></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php esc_html_e( 'Status', 'subscribe-to-comments-reloaded' ) ?></td>
                                        <td>
                                            <select name="srs" class="form-control form-controls-font new-sub-select-status">
                                                <option value='Y'><?php esc_html_e( 'Active', 'subscribe-to-comments-reloaded' ) ?></option>
                                                <option value='R'><?php esc_html_e( 'Replies only', 'subscribe-to-comments-reloaded' ) ?></option>
                                                <option value='YC'><?php esc_html_e( 'Ask user to confirm', 'subscribe-to-comments-reloaded' ) ?></option>
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
                            <?php wp_nonce_field( 'stcr_add_subscription_nonce', 'stcr_add_subscription_nonce' ); ?>
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

                        <h4><i class="fas fa-search"></i> <?php esc_html_e( 'Search subscriptions', 'subscribe-to-comments-reloaded' ) ?></h4>

                        <?php if ( ! empty( $_POST['srv'] ) || ( is_array( $subscriptions ) && count( $subscriptions ) == 1000 ) ) : ?>

                            <?php
                                $search_term = '';
                                if ( ! empty( $_POST['srv'] ) ) {
                                    $search_term = sanitize_text_field( wp_unslash( $_POST['srv'] ) );
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

                        <div class="col-md-2 subs-spinner mx-auto"><h5><?php esc_html_e( "Loading", 'subscribe-to-comments-reloaded'); ?> <i class="fas fa-play-circle"></i></h5></div>

                        <div class="clearfix"></div>

                        <form style="border: 1px solid #eee; padding: 15px; margin-top: 20px;" action="" method="post" id="subscription_form" name="subscription_form"
                              onsubmit="if(this.sra[0].checked) return confirm('<?php esc_attr_e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ) ?>')">

                                <?php

                                $alternate        = '';
                                $date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
                                // Let us form those status
                                $status_arry      = array(
                                                    'R'  => esc_html__( 'Replies', 'subscribe-to-comments-reloaded'),
                                                    'RC' => esc_html__( 'Replies Unconfirmed', 'subscribe-to-comments-reloaded'),
                                                    'Y'  => esc_html__( "All Comments", 'subscribe-to-comments-reloaded'),
                                                    'YC' => esc_html__( "Unconfirmed", 'subscribe-to-comments-reloaded'),
                                                    'C'	 => esc_html__( "Inactive", 'subscribe-to-comments-reloaded'),
                                                    '-C' => esc_html__( "Active", 'subscribe-to-comments-reloaded')
                                                );

                                if ( ! empty( $subscriptions ) && is_array( $subscriptions ) ) {

                                    $show_post_column  = ( $operator != 'equals' || $search_field != 'post_id' ) ?  esc_html__( 'Post (ID)', 'subscribe-to-comments-reloaded' ) : '';
                                    $show_email_column = ( $operator != 'equals' || $search_field != 'email' ) ? esc_html__( 'Email', 'subscribe-to-comments-reloaded' ) : '';

                                    echo "<table class=\"table table-smx table-hover table-striped subscribers-table stcr-hidden\" style=\"font-size: 0.8em\">
                                             <thead>";

                                    if( $wp_locale->text_direction == 'rtl' )
                                    {

                                        echo "<tr>
                                                  <th scope=\"col\">
                                                    &nbsp;&nbsp;&nbsp;<i class=\"fas fa-exchange-alt\"></i> <span>" . esc_html__( 'Actions', 'subscribe-to-comments-reloaded' ) . "</span>
                                                    <input class='checkbox' type='checkbox' name='subscription_list_select_all' id='stcr_select_all' class='stcr_select_all'/>
                                                  </th>
                                                  <th scope=\"col\"><i class=\"fas fa-thumbtack\"></i><span>" . esc_html( $show_post_column ) . "</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-address-card\"></i><span>" . esc_html( $show_email_column ) . "</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-calendar-alt\"></i><span>" . esc_html__( 'Date and Time', 'subscribe-to-comments-reloaded' ) . "</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-info-circle\"></i><span>" . esc_html__( 'Status', 'subscribe-to-comments-reloaded' ) . "</span></th>
                                              </tr>";
                                    }
                                    else
                                    {
                                        echo "<tr>
                                                  <th scope=\"col\">
                                                    <input class='checkbox' type='checkbox' name='subscription_list_select_all' id='stcr_select_all' class='stcr_select_all'/>
                                                    &nbsp;&nbsp;&nbsp;<i class=\"fas fa-exchange-alt\"></i> <span>" . esc_html__( 'Actions', 'subscribe-to-comments-reloaded' ) . "</span>
                                                  </th>
                                                  <th scope=\"col\"><i class=\"fas fa-thumbtack\"></i><span>" . esc_html( $show_post_column ) . "</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-address-card\"></i><span>" . esc_html( $show_email_column ) . "</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-calendar-alt\"></i><span>" . esc_html__( 'Date and Time', 'subscribe-to-comments-reloaded' ) . "</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-info-circle\"></i><span>" . esc_html__( 'Status', 'subscribe-to-comments-reloaded' ) . "</span></th>
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

                                        $delete_url = "admin.php?page=stcr_manage_subscriptions&sra=delete-subscription&srp=" . $a_subscription->post_id . "&sre=" . urlencode( $a_subscription->email );
                                        $delete_url = wp_nonce_url( $delete_url, 'stcr_delete_subscription_nonce', 'stcr_delete_subscription_nonce' );

                                        if( $wp_locale->text_direction == 'rtl' )
                                        {
                                            echo "<tr>
                                                        <td>
                                                            <label for='sub_" . esc_attr( $a_subscription->meta_id ) . "' class='hidden'>" . esc_html__( 'Subscription', 'subscribe-to-comments-reloaded' ) . esc_attr( $a_subscription->meta_id ) . "</label>
                                                            <input class='checkbox' type='checkbox' name='subscriptions_list[]' value='" . esc_attr( $a_subscription->post_id ) . "," . urlencode( $a_subscription->email ) . "' id='sub_" . esc_attr( $a_subscription->meta_id ) . "' />
                                                            <a href='admin.php?page=stcr_manage_subscriptions&amp;sra=edit-subscription&amp;srp=" . esc_attr( $a_subscription->post_id ) . "&amp;sre=" . urlencode( $a_subscription->email ) . "' alt='" . esc_html__( 'Edit', 'subscribe-to-comments-reloaded' ) . "'><i class=\"fas fa-edit\" style='font-size: 1.1em;color: #ffc53a;'></i></a>
                                                            &nbsp;&nbsp;&nbsp;&nbsp;<a href='" . esc_url( $delete_url ) . "' onclick='return confirm(\"" . esc_html__( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ) . "\");' alt='" . esc_html__( 'Delete', 'subscribe-to-comments-reloaded' ) . "'><i class=\"fas fa-trash-alt\" style='font-size: 1.1em;color: #ff695a;'></i></a>
                                                        </td>
                                                        <td>" . wp_kses( $row_post, wp_kses_allowed_html( 'post' ) ) . "</td>
                                                        <td>" . wp_kses( $row_email, wp_kses_allowed_html( 'post' ) ) . "</td>
                                                        <td data-sort='" . esc_attr( $date_time_sort ) . "'>" . esc_html( $date_time ) . "</td>
                                                        <td>" . esc_html( $status_desc ) . "</td>
                                                  </tr>";
                                        }
                                        else
                                        {
                                            echo "<tr>
                                                        <td>
                                                            <label for='sub_" . esc_attr( $a_subscription->meta_id ) . "' class='hidden'>" . esc_html__( 'Subscription', 'subscribe-to-comments-reloaded' ) . esc_attr( $a_subscription->meta_id ) . "</label>
                                                            <input class='checkbox' type='checkbox' name='subscriptions_list[]' value='" . esc_attr( $a_subscription->post_id ) . "," . urlencode( $a_subscription->email ) . "' id='sub_" . esc_attr( $a_subscription->meta_id ) . "' />
                                                            <a href='admin.php?page=stcr_manage_subscriptions&amp;sra=edit-subscription&amp;srp=" . esc_attr( $a_subscription->post_id ) . "&amp;sre=" . urlencode( $a_subscription->email ) . "' alt='" . esc_html__( 'Edit', 'subscribe-to-comments-reloaded' ) . "'><i class=\"fas fa-edit\" style='font-size: 1.1em;color: #ffc53a;'></i></a>
                                                            &nbsp;&nbsp;&nbsp;&nbsp;<a href='" . esc_url( $delete_url ) . "' onclick='return confirm(\"" . esc_html__( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-to-comments-reloaded' ) . "\");' alt='" . esc_html__( 'Delete', 'subscribe-to-comments-reloaded' ) . "'><i class=\"fas fa-trash-alt\" style='font-size: 1.1em;color: #ff695a;'></i></a>
                                                        </td>
                                                        <td>" . wp_kses( $row_post, wp_kses_allowed_html( 'post' ) ) . "</td>
                                                        <td>" . wp_kses( $row_email, wp_kses_allowed_html( 'post' ) ) . "</td>
                                                        <td data-sort='" . esc_attr( $date_time_sort ) . "'>" . esc_html( $date_time ) . "</td>
                                                        <td>" . esc_html( $status_desc ) . "</td>
                                                  </tr>";
                                        }

                                    }
                                    echo "</tbody>";

                                    echo "</table>";

                                    echo "<div class='subscribers-mass-actions form-group row'>";
                                        echo '<label for="action_type" class="col-sm-1 col-form-label">' . esc_html__( 'Action:', 'subscribe-to-comments-reloaded' ) . '</label >' ;
                                    ?>          <div class="col-sm-3">
                                                    <select name="sra" id="action_type" class="form-control">
                                                        <option value="delete"><?php esc_html_e( 'Delete forever', 'subscribe-to-comments-reloaded' ) ?></option>
                                                        <option value="suspend"><?php esc_html_e( 'Suspend', 'subscribe-to-comments-reloaded' ) ?></option>
                                                        <option value="force_y"><?php esc_html_e( 'Activate and set to notify on all comments', 'subscribe-to-comments-reloaded' ) ?></option>
                                                        <option value="force_r"><?php esc_html_e( 'Activate and set to notify on replies only ', 'subscribe-to-comments-reloaded' ) ?></option>
                                                        <option value="activate"><?php esc_html_e( 'Activate', 'subscribe-to-comments-reloaded' ) ?></option>
                                                    </select>
                                                </div>
                                    <?php
                                                echo '<div class="col-sm-2"><button type="submit" class="subscribe-form-button btn btn-primary" >' . esc_html__( 'Update subscriptions', 'subscribe-to-comments-reloaded' ) . '</button></div>';
                                                echo "<input type='hidden' name='srf' value='" . esc_attr( $search_field ) . "'/><input type='hidden' name='srt' value='" . esc_attr( $operator ) . "'/><input type='hidden' name='srv' value='" . esc_attr( $search_value ) . "'/><input type='hidden' name='srsf' value='" .esc_attr( $offset ) . "'/><input type='hidden' name='srrp' value='" . esc_attr( $limit_results ) . "'/><input type='hidden' name='srob' value='" . esc_attr( $order_by ) . "'/><input type='hidden' name='sro' value='" . esc_attr( $order ) . "'/>";
                                    echo '</div>';

                                } elseif ( $action == 'search' ) {
                                    echo '<p>' . esc_html__( 'Sorry, no subscriptions match your search criteria.', 'subscribe-to-comments-reloaded' ) . '</p>';
                                }
                                ?>

                                <?php wp_nonce_field( 'stcr_update_subscriptions_nonce', 'stcr_update_subscriptions_nonce' ); ?>

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
                        <a href="https://wordpress.org/support/plugin/subscribe-to-comments-reloaded/reviews/#new-post" target="_blank"><img src="<?php echo esc_url( plugins_url( '/images/rate.png', STCR_PLUGIN_FILE ) ); ?>" alt="Rate Subscribe to Comments Reloaded" style="vertical-align: sub;" /></a>
                    </p>
                    <p>
                        <i class="fas fa-bug"></i> Having issues? Please <a href="https://github.com/stcr/subscribe-to-comments-reloaded/issues/" target="_blank">create a ticket</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Tell WP that we are going to use a resource.
$wp_subscribe_reloaded->stcr->utils->register_script_to_wp( "stcr-subs-management", "subs_management.js", "includes/js/admin");
// Includes the Panel JS resource file as well as the JS text domain translations.
$wp_subscribe_reloaded->stcr->stcr_i18n->stcr_localize_script( "stcr-subs-management", "stcr_i18n", $wp_subscribe_reloaded->stcr->stcr_i18n->get_js_subs_translation() );
// Enqueue the JS File
$wp_subscribe_reloaded->stcr->utils->enqueue_script_to_wp( "stcr-subs-management" );

?>
