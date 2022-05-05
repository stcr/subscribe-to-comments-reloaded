<?php
/**
 * Class with management functions for Subscribe to Comments Reloaded
 * @author reedyseth
 * @since 27-Mar-2018
 * @version 1.0.0
 */
namespace stcr;

// Avoid direct access to this piece of code
if ( ! function_exists( 'add_action' ) ) {
    header( 'Location: /' );
    exit;
}

class stcr_i18n {

    private $js_subs_translation = array();
    private $wp_locale = null;

    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'register_js_subs_translation' ) );
        global $wp_locale;
        $this->wp_locale = get_locale();
    }

    /**
     * Enqueue a script a translated array into a Object Name that will be use in the handle JS file.
     *
     * @since 28-Mar-2018
     * @author reedyseth
     * @param string $handle Script handle that will be enqueue
     * @param string $object_name Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
     *                            Example: '/[a-zA-Z0-9_]+/'.
     * @param string $l10n The data itself. The data can be either a single or multi-dimensional array.
     */
    public function stcr_localize_script( $handle, $object_name, $l10n ) {
        wp_localize_script( $handle, $object_name, $l10n );
    }
    /**
     * Create the translation array for the plugin jQuery Datatables.
     *
     * @since 28-Mar-2018
     * @author reedyseth
     */
    public function register_js_subs_translation() {
        $translation_array = array (
            "decimal"        => esc_html__( " ", 'subscribe-to-comments-reloaded' ),
            "emptyTable"     => esc_html__( "No data available in table", 'subscribe-to-comments-reloaded' ),
            "info"           => esc_html__( "Showing _START_ to _END_ of _TOTAL_ entries", 'subscribe-to-comments-reloaded' ),
            "infoEmpty"      => esc_html__( "Showing 0 to 0 of 0 entries", 'subscribe-to-comments-reloaded' ),
            "infoFiltered"   => esc_html__( "(filtered from _MAX_ total entries)", 'subscribe-to-comments-reloaded' ),
            "infoPostFix"    => esc_html__( " ", 'subscribe-to-comments-reloaded' ),
            "thousands"      => esc_html__( ",", 'subscribe-to-comments-reloaded' ),
            "lengthMenu"     => esc_html__( "Show _MENU_ entries", 'subscribe-to-comments-reloaded' ),
            "loadingRecords" => esc_html__( "Loading...", 'subscribe-to-comments-reloaded' ),
            "processing"     => esc_html__( "Processing...", 'subscribe-to-comments-reloaded' ),
            "search"         => esc_html__( "Search", 'subscribe-to-comments-reloaded' ),
            "zeroRecords"    => esc_html__( "No matching records found", 'subscribe-to-comments-reloaded' ),
            "paginate"       => array(
                "first"    => esc_html__( "First", 'subscribe-to-comments-reloaded' ),
                "last"     => esc_html__( "Last", 'subscribe-to-comments-reloaded' ),
                "next"     => esc_html__( "Next", 'subscribe-to-comments-reloaded' ),
                "previous" => esc_html__( "Previous", 'subscribe-to-comments-reloaded' )
            ),
            "aria"           => array(
                "sortAscending" => esc_html__( "activate to sort column ascending", 'subscribe-to-comments-reloaded' ),
                "sortDescending"=> esc_html__( "activate to sort column descending", 'subscribe-to-comments-reloaded' )
            ),
            "langTextDirection" => $this->get_text_direction()
        );

        $this->set_js_subs_translation( $translation_array );
    }

    /**
     * @return mixed
     */
    public function get_js_subs_translation() {
        return $this->js_subs_translation;
    }

    /**
     * @param mixed $translation_array
     */
    public function set_js_subs_translation( $translation_array ) {
        $this->js_subs_translation = array_merge( $this->get_js_subs_translation(), $translation_array );
    }

    /**
     * @return null
     */
    public function get_wp_locale() {
        return $this->wp_locale;
    }

    public function get_text_direction() {

        if ( function_exists( 'is_rtl' ) && is_rtl() ) {
            $text_direction = "rtl";
        } else {
            $text_direction = "ltr";
        }

        return $text_direction;

    }

    /**
     * @param null $wp_locale
     */
    public function set_wp_locale($wp_locale) {
        $this->wp_locale = $wp_locale;
    }

}
