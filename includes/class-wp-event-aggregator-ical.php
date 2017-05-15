<?php
/**
 * Class for iCal Imports.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Ical {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// init operations for iCal
	}

	/**
	 * import ical events by iCal URL
	 *
	 * @since  1.0.0
	 * @param  array $eventdata  import event data.
	 * @return array/boolean
	 */
	public function import_events( $event_data = array() ){

		global $wpea_errors;
		$imported_events = array();

		$import_by = isset( $event_data['import_by'] ) ? esc_attr( $event_data['import_by'] ) : '';

		if( 'ical_url' != $import_by ){
			return;
		}

		if( $event_data['ical_url'] == '' ){
			$wpea_errors[] = esc_html__( 'Please provide iCal URL.', 'wp-event-aggregator');
			return;
		}

		$ical_url = str_replace( 'webcal://', 'http://', $event_data['ical_url'] );
		$ics_content =  $this->get_remote_content( $ical_url );
		
		if( false == $ics_content ){
			return false;
		}

		if( $ics_content != "" ){

			$imported_events = $this->import_events_from_ics_content( $event_data, $ics_content );

		}
		return $imported_events;
	}


	/**
	 * import ical events using .ics file
	 *
	 * @since  1.0.0
	 * @param  array $eventdata  import event data.
	 * @param  array $ics_content  ics content data.
	 * @return array/boolean
	 */
	public function import_events_from_ics_content( $event_data = array(), $ics_content = '' ){
		global $importevents, $wpea_errors;

		error_reporting(0);
		// Set time and memory limit.
		set_time_limit(0);
		$xt_memory_limit = (int)str_replace( 'M', '',ini_get('memory_limit' ) );
		if( $xt_memory_limit < 512 ){
			ini_set('memory_limit', '512M');
		}

		$imported_events = array();
		if( empty( $ics_content ) ){
			return array();
		}
		
		$imported_events = $importevents->ical_parser->parse_import_events( $event_data, $ics_content );
		return $imported_events;
	}

	/**
	 * load Content using wp_remote_get
	 *
	 * @param  string $ical_url
	 * @since    1.1.0
	 */
	protected function get_remote_content( $ical_url ) {

		global $wp_version, $wpea_errors;
		$ical_url = str_replace( 'webcal://', 'http://', $ical_url );
		$timeout_in_seconds = 5;
		$response = null;

		$request_args = array(
			'timeout'     => $timeout_in_seconds,
			'sslverify'   => false,
			'method'      => 'GET',
			'user-agent'  => 'WordPress/' . $wp_version . '; ' . home_url(),
		);

		$response = wp_remote_get( $ical_url, $request_args );

		if ( is_wp_error( $response ) ) {
			$request_args['sslverify'] = true;
			$response = wp_remote_head( $ical_url, $request_args );
		}

		if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
			$wpea_errors[] = esc_html__( 'Unable to retrieve content from the provided URL.', 'wp-event-aggregator');
			return false;
		}
		return $response['body'];
	}

}
