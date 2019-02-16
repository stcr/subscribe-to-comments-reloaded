<?php
/**
 * Created by PhpStorm.
 * User: reedyseth
 * Date: 12/8/18
 * Time: 12:16 AM
 */

require_once( $_SERVER["DOCUMENT_ROOT"] . "/wp-load.php");

// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
    header( 'Location: /' );
    exit;
}
// Check that is a WordPress Admin, otherwise reject request.
if ( current_user_can( 'manage_options' ) ) {
    $fileName = isset($_GET['name']) ? $_GET['name'] : die("<h1>Incorrect header information</h1>");
    $fileName = esc_attr( $fileName );

    header('Content-type: text/plain');

    header('Content-Disposition: attachment; filename="'. $fileName .'.txt"');

    readfile("{$fileName}.txt");

    unlink( "{$fileName}.txt" ); // Clean house
}
else
{
    echo ""; // Silence is Golden.
}

