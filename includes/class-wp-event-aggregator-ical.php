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

		error_reporting(0); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting
		// Set time and memory limit.
		set_time_limit(0); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		$xt_memory_limit = (int)str_replace( 'M', '',ini_get('memory_limit' ) );
		if( $xt_memory_limit < 512 ){
			ini_set('memory_limit', '512M'); // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
		}

		$imported_events = array();
		if( empty( $ics_content ) ){
			return array();
		}

		if( wpea_aioec_active() && post_type_exists( 'ai1ec_event' ) ){
			$imported_events = $importevents->ical_parser_aioec->parse_import_events( $event_data, $ics_content );
			return $imported_events;
		}else{
			$imported_events = $importevents->ical_parser->parse_import_events( $event_data, $ics_content );
			return $imported_events;
		}
	}

	/**
	 * load Content using wp_remote_get
	 *
	 * @param  string $ical_url
	 * @since    1.1.0
	 */
	// phpcs:disable WordPress.WP.AlternativeFunctions
	protected function get_remote_content( $ical_url ) {
		global $wp_version;

		$ical_url = str_replace( 'webcal://', 'https://', $ical_url );

		$ch = curl_init();

		curl_setopt( $ch, CURLOPT_URL, $ical_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 600 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_ENCODING, '' );
		curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		curl_setopt(
			$ch,
			CURLOPT_USERAGENT,
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36'
		);

		// SSL
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );

		$response = curl_exec( $ch );

		if ( curl_errno( $ch ) ) {
			curl_close( $ch );
			return false;
		}

		$http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		if ( $http_code !== 200 ) {
			return false;
		}
		return $response;
	}
	// phpcs:enable WordPress.WP.AlternativeFunctions
}
