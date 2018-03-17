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
            _e( 'The email that you typed is not correct.', 'subscribe-reloaded' );
        echo "</p></div>";
    }

    if ( ! $valid_post_id )
    {
        echo '<div class="notice notice-error is-dismissible"><p>';
            _e( 'Please enter a valid Post ID.', 'subscribe-reloaded' );
        echo "</p></div>";
    }
}
?>
<style type="text/css">
    .validate-error-text
    {
        color: #f55252;
        font-weight:bold;
    }
    .validate-error-field { border: 1px solid #ff9595 !important; }
    .stcr-hidden { display: none;}
</style>

<div class="container-fluid">

    <div class="row mx-auto">
        <div class="col-sm-6">
            <div class="card card-font-size">
                <h6 class="card-header">
                    <i class="fas fa-exchange-alt"></i> <?php _e( 'Mass Update Subscriptions', 'subscribe-reloaded' ) ?>
                </h6>
                <div class="card-body">
                    <div class="card-text">
                        <form action="" method="post" id="mass_update_address_form">

                            <table>
                                <tr>
                                    <td><label for='oldsre'><?php _e( 'From', 'subscribe-reloaded' ) ?></label></td>
                                    <td><input class="form-control form-controls-font" type='text' size='30' name='oldsre' id='oldsre' value='<?php _e( 'email address', 'subscribe-reloaded' ) ?>' style="color:#ccc;"></td>
                                    <td><span class="validate-error-text validate-error-text-oldsre stcr-hidden "></span></td>
                                </tr>
                                <tr>
                                    <td><label for='sre'><?php _e( 'To', 'subscribe-reloaded' ) ?></label></td>
                                    <td><input class="form-control form-controls-font" type='text' size='30' name='sre' id='sre' value='<?php _e( 'optional - new email address', 'subscribe-reloaded' ) ?>' style="color:#ccc;"
                                        >
                                    </td>
                                    <td><span class="validate-error-text validate-error-text-sre stcr-hidden "></span></td>
                                </tr>
                                <tr>
                                    <td><label for='srs'><?php _e( 'Status', 'subscribe-reloaded' ) ?></label></td>
                                    <td><select class="form-control form-controls-font" name="srs" id="srs" style="width: 65%; display: inline;">
                                            <option value=''><?php _e( 'Keep unchanged', 'subscribe-reloaded' ) ?></option>
                                            <option value='Y'><?php _e( 'Active', 'subscribe-reloaded' ) ?></option>
                                            <option value='R'><?php _e( 'Replies only', 'subscribe-reloaded' ) ?></option>
                                            <option value='C'><?php _e( 'Suspended', 'subscribe-reloaded' ) ?></option>
                                        </select>
                                        <input type='submit' style="font-size: 0.8rem;" class='subscribe-form-button btn btn-primary' value='<?php _e( 'Update', 'subscribe-reloaded' ) ?>' ></td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="more-info" data-infopanel="info-panel-mass-update" aria-label="<?php _e("More info", "subscribe-reloaded"); ?>">
                                            <i class="fas fa-question-circle"></i>
                                        </div>
                                    </td>

                                </tr>
                                <tr>
                                    <td colspan="2"><input type='hidden' name='sra' value='edit' /></td>
                                </tr>
                            </table>

                            <div class="alert alert-info hidden  info-panel-mass-update" role="alert">
                                <?php _e('This option will allow you to change an email address for another one or to update the same status for all the subscription on a specific email address.', 'subscribe-reloaded' ); ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="card card-font-size">
                <h6 class="card-header">
                    <i class="fas fa-plus-square"></i> <?php _e( 'Add New Subscription', 'subscribe-reloaded' ) ?>
                </h6>
                <div class="card-body">
                    <div class="card-text">
                        <form action="" method="post" id="add_new_subscription">
                            <fieldset style="border:0">
                                <table>
                                    <tr>
                                        <td><?php _e( 'Post ID', 'subscribe-reloaded' ) ?></td>
                                        <td><input class="form-control form-controls-font" type='text' size='30' name='srp' value='' ></td>
                                        <td><span class="validate-error-text validate-error-text-srp stcr-hidden "></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e( 'Email', 'subscribe-reloaded' ) ?></td>
                                        <td><input class="form-control form-controls-font" type='text' size='30' name='sre' value='' ></td>
                                        <td><span class="validate-error-text validate-error-text-sre stcr-hidden "></span></td>
                                    </tr>
                                    <tr>
                                        <td><?php _e( 'Status', 'subscribe-reloaded' ) ?></td>
                                        <td>
                                            <select name="srs" class="form-control form-controls-font" style="width: 65%; display: inline;">
                                                <option value='Y'><?php _e( 'Active', 'subscribe-reloaded' ) ?></option>
                                                <option value='R'><?php _e( 'Replies only', 'subscribe-reloaded' ) ?></option>
                                                <option value='YC'><?php _e( 'Ask user to confirm', 'subscribe-reloaded' ) ?></option>
                                            </select>
                                            <input type='submit' style="font-size: 0.8rem;" class='subscribe-form-button btn btn-primary' value='<?php _e( 'Add', 'subscribe-reloaded' ) ?>' >
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
            <div class="card" style="max-width: 100% !important;">
                <div class="card-body">

                    <div class="card-text postbox" style="border: none;">
                        <p class="subscribe-list-navigation"><?php echo "$previous_link $next_link" ?>
                        </p>

                        <h4><?php _e( 'Search subscriptions', 'subscribe-reloaded' ) ?></h4>

                        <form action="" method="post" id="search_subscriptions_form">
                            <p><?php printf(
                                    __( 'You can either <a href="%s">view all the subscriptions</a> or find those where the', 'subscribe-reloaded' ),
                                    'admin.php?page=stcr_manage_subscriptions&amp;srv=@&amp;srt=contains'
                                ) ?>&nbsp;
                                <select name="srf">
                                    <option value='email'><?php _e( 'email', 'subscribe-reloaded' ) ?></option>
                                    <option value='post_id'><?php _e( 'post ID', 'subscribe-reloaded' ) ?></option>
                                    <option value='status'><?php _e( 'status', 'subscribe-reloaded' ) ?></option>
                                </select>
                                <select name="srt">
                                    <option value='equals'><?php _e( 'equals', 'subscribe-reloaded' ) ?></option>
                                    <option value='contains'><?php _e( 'contains', 'subscribe-reloaded' ) ?></option>
                                    <option value='does not contain'><?php _e( 'does not contain', 'subscribe-reloaded' ) ?></option>
                                    <option value='starts with'><?php _e( 'starts with', 'subscribe-reloaded' ) ?></option>
                                    <option value='ends with'><?php _e( 'ends with', 'subscribe-reloaded' ) ?></option>
                                </select>
                                <input type="text" size="20" name="srv" value="" />,
                                <?php _e( 'results per page:', 'subscribe-reloaded' ) ?>
                                <input type="text" size="2" name="srrp" value="25" />
                                <input type="submit" class="subscribe-form-button" value="<?php _e( 'Search', 'subscribe-reloaded' ) ?>" />
                        </form>

                        <form action="" method="post" id="subscription_form" name="subscription_form"
                              onsubmit="if(this.sra[0].checked) return confirm('<?php _e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded' ) ?>')">

                                <?php

                                $alternate        = '';
                                $date_time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
                                // Let us form those status
                                $status_arry      = array(
                                                    'R'  => __( 'Replies', 'subscribe-reloaded'),
                                                    'RC' => __( 'Replies Unconfirmed', 'subscribe-reloaded'),
                                                    'Y'  => __( "All Comments", "subscribe-reloaded"),
                                                    'YC' => __( "Unconfirmed", "subscribe-reloaded"),
                                                    'C'	 => __( "Inactive", "subscribe-reloaded"),
                                                    '-C' => __( "Active", "subscribe-reloaded")
                                                );

                                if ( ! empty( $subscriptions ) && is_array( $subscriptions ) ) {
                                    $order_post_id = "<a style='text-decoration:none' title='" . __( 'Reverse the order by Post ID', 'subscribe-reloaded' ) . "' href='admin.php?page=stcr_manage_subscriptions&amp;srv=" . urlencode( $search_value ) . "&amp;srt=" . urlencode( $operator ) . "&amp;srob=post_id&amp;sro=" . ( ( $order == 'ASC' ) ? "DESC'>&or;" : "ASC'>&and;" ) . "</a>";
                                    $order_dt      = "<a style='text-decoration:none' title='" . __( 'Reverse the order by Date/Time', 'subscribe-reloaded' ) . "' href='admin.php?page=stcr_manage_subscriptions&amp;srv=" . urlencode( $search_value ) . "&amp;srt=" . urlencode( $operator ) . "&amp;srob=dt&amp;sro=" . ( ( $order == 'ASC' ) ? "DESC'>&or;" : "ASC'>&and;" ) . "</a>";
                                    $order_status  = "<a style='text-decoration:none' title='" . __( 'Reverse the order by Date/Time', 'subscribe-reloaded' ) . "' href='admin.php?page=stcr_manage_subscriptions&amp;srv=" . urlencode( $search_value ) . "&amp;srt=" . urlencode( $operator ) . "&amp;srob=status&amp;sro=" . ( ( $order == 'ASC' ) ? "DESC'>&or;" : "ASC'>&and;" ) . "</a>";

                                    $show_post_column  = ( $operator != 'equals' || $search_field != 'post_id' ) ?  __( 'Post (ID)', 'subscribe-reloaded' ) . "&nbsp;&nbsp;$order_post_id": '';
                                    $show_email_column = ( $operator != 'equals' || $search_field != 'email' ) ? __( 'Email', 'subscribe-reloaded' ) : '';

                                    echo '<p>' . __( 'Search query:', 'subscribe-reloaded' ) . " <code>$search_field $operator <strong>$search_value</strong> ORDER BY $order_by $order</code>. " . __( 'Rows:', 'subscribe-reloaded' ) . ' ' . ( $offset + 1 ) . " - $ending_to " . __( 'of', 'subscribe-reloaded' ) . " $count_total</p>";

                                    echo "<table class=\"table table-smx table-hover table-striped subscribers-table\" style=\"font-size: 0.8em\">
                                             <thead>";

                                    if( $wp_locale->text_direction == 'rtl' )
                                    {
                                        echo "<li class='subscribe-list-header'>
                                                <span class='subscribe-column subscribe-column-4'>" . __( 'Status', 'subscribe-reloaded' ) . " &nbsp;&nbsp;$order_status</span>
                                                <span class='subscribe-column subscribe-column-3'>" . __( 'Date and Time', 'subscribe-reloaded' ) . " &nbsp;&nbsp;$order_dt</span>
                                                $show_email_column
                                                $show_post_column
                                                <span class='subscribe-column' style='width:38px'>&nbsp;</span>
                                                <input class='checkbox' type='checkbox' name='subscription_list_select_all' id='stcr_select_all'
                                                onchange='t=document.forms[\"subscription_form\"].elements[\"subscriptions_list[]\"];c=t.length;if(!c){t.checked=this.checked}else{for(var i=0;i<c;i++){t[i].checked=!t[i].checked}}'/>
                                              </li>";
                                    }
                                    else
                                    {
                                        echo "<tr>
                                                  <th scope=\"col\">
                                                    <input class='checkbox' type='checkbox' name='subscription_list_select_all' id='stcr_select_all'
                                                    onchange='t=document.forms[\"subscription_form\"].elements[\"subscriptions_list[]\"];c=t.length;if(!c){t.checked=this.checked}else{for(var i=0;i<c;i++){t[i].checked=!t[i].checked}}'/>
                                                    &nbsp;&nbsp;&nbsp;<i class=\"fas fa-exchange-alt\"></i> <span>" . __('Actions', 'subscribe-reloaded') ."</span>
                                                  </th>
                                                  <th scope=\"col\"><i class=\"fas fa-thumbtack\"></i><span>$show_post_column</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-address-card\"></i><span>$show_email_column</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-calendar-alt\"></i><span>". __( 'Date and Time', 'subscribe-reloaded' ) . " &nbsp;&nbsp;$order_dt</span></th>
                                                  <th scope=\"col\"><i class=\"fas fa-info-circle\"></i><span>". __( 'Status', 'subscribe-reloaded' ) . " &nbsp;&nbsp;$order_status</span></th>
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

                                        $status_desc = $status_arry[$a_subscription->status];

                                        if( $wp_locale->text_direction == 'rtl' )
                                        {
                                            echo "<li>
                                                        <span class='subscribe-column subscribe-column-4'>$status_desc</span>
                                                        <span class='subscribe-column subscribe-column-3'>$date_time</span>
                                                        $row_email
                                                        $row_post
                                                        <a class='subscribe-column' href='admin.php?page=stcr_manage_subscriptions&amp;sra=delete-subscription&amp;srp=" . $a_subscription->post_id . "&amp;sre=" . urlencode( $a_subscription->email ) . "' onclick='return confirm(\"" . __( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded' ) . "\");'><img src='" . WP_PLUGIN_URL . "/subscribe-to-comments-reloaded/images/delete.png' alt='" . __( 'Delete', 'subscribe-reloaded' ) . "' width='16' height='16' /></a>
                                                        <a class='subscribe-column' href='admin.php?page=stcr_manage_subscriptions&amp;sra=edit-subscription&amp;srp=" . $a_subscription->post_id . "&amp;sre=" . urlencode( $a_subscription->email ) . "'><img src='" . WP_PLUGIN_URL . "/subscribe-to-comments-reloaded/images/edit.png' alt='" . __( 'Edit', 'subscribe-reloaded' ) . "' width='16' height='16' /></a>
                                                        <input class='checkbox' type='checkbox' name='subscriptions_list[]' value='$a_subscription->post_id," . urlencode( $a_subscription->email ) . "' id='sub_{$a_subscription->meta_id}' />
                                                        <label for='sub_{$a_subscription->meta_id}' class='hidden'>" . __( 'Subscription', 'subscribe-reloaded' ) . " {$a_subscription->meta_id}</label>
                                                  </li>";
                                        }
                                        else
                                        {
                                            echo "<tr>
                                                        <td>
                                                            <label for='sub_{$a_subscription->meta_id}' class='hidden'>" . __( 'Subscription', 'subscribe-reloaded' ) . " {$a_subscription->meta_id}</label>
                                                            <input class='checkbox' type='checkbox' name='subscriptions_list[]' value='$a_subscription->post_id," . urlencode( $a_subscription->email ) . "' id='sub_{$a_subscription->meta_id}' />                                                        
                                                            <a href='admin.php?page=stcr_manage_subscriptions&amp;sra=edit-subscription&amp;srp=" . $a_subscription->post_id . "&amp;sre=" . urlencode( $a_subscription->email ) . "' alt='" . __( 'Edit', 'subscribe-reloaded' ) . "'><i class=\"fas fa-edit\" style='font-size: 1.1em;color: #ffc53a;'></i></a>
                                                            <a href='admin.php?page=stcr_manage_subscriptions&amp;sra=delete-subscription&amp;srp=" . $a_subscription->post_id . "&amp;sre=" . urlencode( $a_subscription->email ) . "' onclick='return confirm(\"" . __( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded' ) . "\");' alt='" . __( 'Delete', 'subscribe-reloaded' ) . "'><i class=\"fas fa-trash-alt\" style='font-size: 1.1em;color: #ff695a;'></i></a>
                                                        </td>
                                                        <td>$row_post</td>
                                                        <td>$row_email</td>
                                                        <td>$date_time</td>
                                                        <td>$status_desc</td>
                                                  </tr>";
                                        }

                                    }
                                    echo "</tbody>";

                                    echo "</table>";

                                    echo "<nav aria-label=\"Subscriptions\">
                                              <ul class=\"pagination justify-content-end\">";
                                                for ( $p = 1; $p <= $total_pages; $p++ )
                                                {
                                                    if( $p == 1 )
                                                    {
                                                        echo "<li class=\"page-item disabled\">
                                                            <a class=\"page-link\" href=\"#\" tabindex=\"-1\">Previous</a>
                                                          </li>
                                                              <li class=\"page-item\"><a class=\"page-link\" href=\"#\">1</a></li>
                                                              <li class=\"page-item disabled\">
                                                                    <a class=\"page-link\" href=\"#\" tabindex=\"-1\">Next</a>
                                                                  </li>";
                                                    }
                                                    else if( $p > 1 )
                                                    {
                                                        echo "<li class=\"page-item\"><a class=\"page-link\" href=\"#\">$p</a></li>";

                                                        if( $p == $total_pages )
                                                        {
                                                            echo "<li class=\"page-item disabled\">
                                                                    <a class=\"page-link\" href=\"#\" tabindex=\"-1\">Next</a>
                                                                  </li>";
                                                        }
                                                        else if( $p > 1 && $p < $total_pages )
                                                        {
                                                            echo "<li class=\"page-item\">
                                                                    <a class=\"page-link\" href=\"#\" tabindex=\"-1\">Next</a>
                                                                  </li>";
                                                        }
                                                    }
                                                }
                                            echo "";
//                                                ."<li class=\"page-item disabled\">
//                                                  <a class=\"page-link\" href=\"#\" tabindex=\"-1\">Previous</a>
//                                                </li>
//                                                <li class=\"page-item\"><a class=\"page-link\" href=\"#\">1</a></li>
//                                                <li class=\"page-item\"><a class=\"page-link\" href=\"#\">2</a></li>
//                                                <li class=\"page-item\"><a class=\"page-link\" href=\"#\">3</a></li>
//                                                <li class=\"page-item\">
//                                                  <a class=\"page-link\" href=\"#\">Next</a>
//                                                </li>
                                     echo  "    </ul>
                                            </nav>";

                                    echo '<label for="action_type" >' . __( 'Action:', 'subscribe-reloaded' ) . '</label >' ;
                                    ?>
                                    <select name="sra" id="action_type">
                                        <option value="delete"><?php _e( 'Delete forever', 'subscribe-reloaded' ) ?></option>
                                        <option value="suspend"><?php _e( 'Suspend', 'subscribe-reloaded' ) ?></option>
                                        <option value="force_y"><?php _e( 'Activate and set to Y', 'subscribe-reloaded' ) ?></option>
                                        <option value="force_r"><?php _e( 'Activate and set to R', 'subscribe-reloaded' ) ?></option>
                                        <option value="activate"><?php _e( 'Activate', 'subscribe-reloaded' ) ?></option>
                                    </select>

                                    <?php
                                    echo '<p><input type="submit" class="subscribe-form-button button-primary" value="' . __( 'Update subscriptions', 'subscribe-reloaded' ) . '" /></p>';
                                    echo "<input type='hidden' name='srf' value='$search_field'/><input type='hidden' name='srt' value='$operator'/><input type='hidden' name='srv' value='$search_value'/><input type='hidden' name='srsf' value='$offset'/><input type='hidden' name='srrp' value='$limit_results'/><input type='hidden' name='srob' value='$order_by'/><input type='hidden' name='sro' value='$order'/>";
                                } elseif ( $action == 'search' ) {
                                    echo '<p>' . __( 'Sorry, no subscriptions match your search criteria.', 'subscribe-reloaded' ) . "</p>";
                                }
                                ?>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script type="text/javascript">
    ( function($){
        $(document).ready(function(){

            var emailRegex   = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            var oldsre_input = $("form#mass_update_address_form input[name='oldsre']");
            var sre_input    = $("form#mass_update_address_form input[name='sre']");


            oldsre_input.focus(function(){
                if (oldsre_input.val() == "<?php _e( 'email address', 'subscribe-reloaded' ) ?>")
                {
                    oldsre_input.val("");
                }
                oldsre_input.css("color","#000");
            });

            oldsre_input.blur(function(){
                if (oldsre_input.val() == "")
                {
                    oldsre_input.val("<?php _e( 'email address', 'subscribe-reloaded' ) ?>");
                    oldsre_input.css("color","#ccc");
                }
            });

            sre_input.focus(function(){
                if (sre_input.val() == "<?php _e( 'optional - new email address', 'subscribe-reloaded' ) ?>")
                {
                    sre_input.val("");
                }
                sre_input.css("color","#000");
            });

            sre_input.blur(function(){
                if (sre_input.val() == "")
                {
                    sre_input.val("<?php _e( 'optional - new email address', 'subscribe-reloaded' ) ?>");
                    sre_input.css("color","#ccc");
                }
            });

            $("form#mass_update_address_form").submit(function(){
                var old_email      = $.trim( $("form#mass_update_address_form input[name='oldsre']").val() );
                var email          = $.trim( $("form#mass_update_address_form input[name='sre']").val() );
                var missing_fields = [];

                if( old_email == "<?php _e( 'email address', 'subscribe-reloaded' ) ?>" || old_email == "")
                {
                    missing_fields.push(
                        {
                            message: "<?php _e( 'Missing information', 'subscribe-reloaded' ) ?>",
                            field: "oldsre"
                        } );
                }
                else if( ! emailRegex.test(old_email) ) // check valid email
                {
                    missing_fields.push(
                        {
                            message: "<?php _e( 'Invalid email address.', 'subscribe-reloaded' ) ?>",
                            field: "oldsre"
                        } );
                }

                var missing_fields_size = missing_fields.length;

                if( missing_fields_size > 0 )
                {

                    for( var i = 0; i < missing_fields_size; i++ )
                    {
                        var field_obj = missing_fields[i];
                        $("form#mass_update_address_form .validate-error-text-" + field_obj.field).text(field_obj.message).show();
                        $("form#mass_update_address_form input[name='"+ field_obj.field +"']").addClass("validate-error-field");
                    }

                    return false;
                }
                else
                {
                    var answer = confirm('<?php _e( 'Please remember: this operation cannot be undone. Are you sure you want to proceed?', 'subscribe-reloaded' ) ?>');

                    if( ! answer )
                    {
                        return false;
                    }
                }


            });
            // Add New Subscription
            var stcr_post_id_input = $("form#add_new_subscription input[name='srp']");
            var sre_input          = $("form#add_new_subscription input[name='sre']");

            stcr_post_id_input.blur(function(){
                if( $.isNumeric(stcr_post_id_input.val() ) ) // check numeric value
                {
                    $(this).removeClass("validate-error-field");
                    $("form#add_new_subscription .validate-error-text-srp").hide();
                }
            });

            sre_input.blur(function(){
                if( emailRegex.test(sre_input.val() ) ) // check email value
                {
                    $(this).removeClass("validate-error-field");
                    $("form#add_new_subscription .validate-error-text-sre").hide();
                }
            });

            $("form#add_new_subscription").submit(function(){
                var post_id        = $.trim(stcr_post_id_input.val());
                var email          = $.trim(sre_input.val());
                var missing_fields = [];

                if( post_id == "")
                {
                    missing_fields.push(
                        {
                            message: "<?php _e( 'Missing information', 'subscribe-reloaded' ) ?>",
                            field: "srp"
                        } );
                }
                else if( ! $.isNumeric(post_id) ) // check numeric value
                {
                    missing_fields.push(
                        {
                            message: "<?php _e( 'Enter a numeric Post ID.', 'subscribe-reloaded' ) ?>",
                            field: "srp"
                        } );
                }

                if( email == "")
                {
                    missing_fields.push(
                        {
                            message: "<?php _e( 'Missing email information', 'subscribe-reloaded' ) ?>",
                            field: "sre"
                        } );
                }
                else if( ! emailRegex.test(email) ) // check valid email
                {
                    missing_fields.push(
                        {
                            message: "<?php _e( 'Invalid email address.', 'subscribe-reloaded' ) ?>",
                            field: "sre"
                        } );
                }

                var missing_fields_size = missing_fields.length;

                if( missing_fields_size > 0 )
                {

                    for( var i = 0; i < missing_fields_size; i++ )
                    {
                        var field_obj = missing_fields[i];
                        $("form#add_new_subscription .validate-error-text-" + field_obj.field).text(field_obj.message).show();
                        $("form#add_new_subscription input[name='"+ field_obj.field +"']").addClass("validate-error-field");
                    }

                    return false;
                }
            });

            var search_input = $("form#search_subscriptions_form input[name='srv']");

            $("form#search_subscriptions_form").submit(function(){
                var search_value = $.trim(search_input.val());

                if( search_value == "")
                {
                    search_input.val("<?php _e( 'Please enter a value', 'subscribe-reloaded' ) ?>");
                    search_input.addClass("validate-error-field");

                    return false;
                }
            });

            search_input.focus(function(){
                if( search_input.val() == "<?php _e( 'Please enter a value', 'subscribe-reloaded' ) ?>" )
                {
                    search_input.val("");
                }
            });

            search_input.blur(function(){
                if( $.trim(search_input.val() ) != "" )
                {
                    $(this).removeClass("validate-error-field");
                }
            });
        });

        // More info action
        $('div.more-info').on("click", function( event ) {
            event.preventDefault();
            var info_panel = $( this ).data( "infopanel" );
            info_panel = "." + info_panel;

            $( ".postbox-mass").css("overflow","hidden");

            if( $( info_panel ).hasClass( "hidden") )
            {
                $( info_panel ).slideDown( "fast" );
                $( info_panel).removeClass( "hidden" );
            }
            else
            {
                $( info_panel ).slideUp( "fast" );
                $( info_panel).addClass( "hidden" );
            }
        });
    } )( jQuery );
</script>

