<?php
/**
 * Ajax functions class for WP Event aggregator.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Event_Aggregator_Ajax {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wpea_load_paged_events',  array( $this, 'wpea_load_paged_events_callback' ) );
        add_action( 'wp_ajax_nopriv_wpea_load_paged_events',  array( $this, 'wpea_load_paged_events_callback' ) );
	}

	public function wpea_load_paged_events_callback() {
		if ( empty( $_POST['atts'] ) || empty( $_POST['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			wp_send_json_error( 'Missing params' );
		}

		$atts          = json_decode( stripslashes( $_POST['atts'] ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$atts['paged'] = intval( $_POST['page'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$html          = do_shortcode( '[wp_events ' . http_build_query( $atts, '', ' ' ) . ']' );

		wp_send_json_success( $html );
	}
}
