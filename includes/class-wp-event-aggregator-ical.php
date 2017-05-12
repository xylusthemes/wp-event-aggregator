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

		global $errors;
		$imported_events = $ics_content_array = array();

		$import_by = isset( $event_data['import_by'] ) ? esc_attr( $event_data['import_by'] ) : '';

		if( 'ical_url' != $import_by ){
			return;
		}

		if( $event_data['ical_url'] == '' ){
			$errors[] = esc_html__( 'Please provide iCal URL.', 'wp-event-aggregator');
			return;
		}

		$ical_url = str_replace( 'webcal://', 'http://', $event_data['ical_url'] );
		$ics_content =  @file_get_contents( $ical_url );
		
		if( $ics_content != '' ){
			$ics_content_array = explode("\n", $ics_content);
		}else{
			$errors[] = esc_html__( 'Please provide Valid iCal URL.', 'wp-event-aggregator');
			return;
		}

		if( !empty( $ics_content_array ) ){

			$imported_events = $this->import_events_from_ics_content( $event_data, $ics_content_array );
		}
		return $imported_events;
	}


	/**
	 * import ical events using .ics file
	 *
	 * @since  1.0.0
	 * @param  array $eventdata  import event data.
	 * @param  array $ics_content_array  ics event array data.
	 * @return array/boolean
	 */
	public function import_events_from_ics_content( $event_data = array(), $ics_content_array = array() ){
		// Set time and memory limit.
		set_time_limit(0);
		$xt_memory_limit = (int)str_replace( 'M', '',ini_get('memory_limit' ) );
		if( $xt_memory_limit < 512 ){
			ini_set('memory_limit', '512M');
		}		
		
		global $errors;
		$start_date = $end_date = false;

		require_once 'lib/ICal.php';
		require_once 'lib/EventObject.php';

		$imported_events = array();
		if( empty( $ics_content_array ) ){
			return;
		}
		if( isset( $event_data['start_date'] ) && $event_data['start_date'] != '' ){
			$start_date = $event_data['start_date'];
		}

		if( isset( $event_data['end_date'] ) && $event_data['end_date'] != '' ){
 			$end_date = $event_data['end_date'];
		}

		$ical = new ICal( $ics_content_array );
		if( $ical->hasEvents() ){
			
			if( $start_date == false && $end_date == false ){

				$ical_events = $ical->events();

			} else {
				$ical_events = $ical->eventsFromRange( $start_date, $end_date);
			}
			
			if( !empty( $ical_events ) ){
	        	foreach ($ical_events as $event ) {
					$imported_events[]  = $this->save_ical_event( $event, $event_data );
	        	}
	        }
		}

		return $imported_events;
	}


	/**
	 * Save (Create or update) ical imported to The Event Calendar Events.
	 *
	 * @since  1.0.0
	 * @param array  $facebook_event_object Event object get from facebook.com.
	 * @return void
	 */
	public function save_ical_event( $ical_event_object = array(), $event_args = array() ) {
		global $importevents;
		if ( ! empty( $ical_event_object ) && $ical_event_object->uid != '' ) {
			$centralize_array = $this->generate_centralize_array( $ical_event_object );
			return $importevents->common->import_events_into( $centralize_array, $event_args );
		}
	}

	/**
	* Get body data from url and return decoded data.
	*
	* @since 1.0.0
	*/
	public function get_json_response_from_url( $url ) {
		
		$response = wp_remote_get( $url );
		$response = json_decode( wp_remote_retrieve_body( $response ) );
		return $response;
	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $ical_event iCal event.
	 * @return array
	 */
	public function generate_centralize_array( $ical_event ) {
		
		global $importevents;

		if( !isset( $ical_event->uid ) || $ical_event->uid == '' ){
			return;
		}

		$ical_event_id = $ical_event->uid;
		$post_title = isset( $ical_event->summary ) ? $ical_event->summary : '';
		$post_description = isset( $ical_event->description ) ? $ical_event->description : '';
		$start_time = isset( $ical_event->dtstart_tz ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $ical_event->dtstart_tz ) ) : date( 'Y-m-d H:i:s');
		$end_time = isset( $ical_event->dtend_tz ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $ical_event->dtend_tz ) ) : $start_time;
		$website = isset( $ical_event->url ) ? esc_url( $ical_event->url ) : '';
		$timezone = $this->get_utc_offset( $ical_event->dtstart_tz );

		$xt_event = array(
			'origin'          => 'ical',
			'ID'              => $ical_event_id,
			'name'            => $post_title,
			'description'     => $post_description,
			'starttime_local' => $start_time,
			'endtime_local'   => $end_time,
			'startime_utc'    => '',
			'endtime_utc'     => '',
			'timezone'        => $timezone,
			'utc_offset'      => '',
			'event_duration'  => '',
			'is_all_day'      => '',
			'url'             => $website,
			'image_url'       => '',
		);

		if ( isset( $ical_event->organizer ) && !empty( $ical_event->organizer ) ) {
			$xt_event['organizer'] = $this->get_organizer( $ical_event );
		}

		if ( isset( $ical_event->location ) && !empty( $ical_event->location ) ) {
			$xt_event['location'] = $this->get_location( $ical_event );
		}
		return $xt_event;
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function get_organizer( $ical_event ) {

		if ( isset( $ical_event->organizer ) && !empty( $ical_event->organizer ) ) {
			return null;
		}
		
		$params = wp_parse_args( str_replace( ';', '&', $ical_event->organizer ) );
		$oraganizer_data = array();
		foreach ( $params as $k => $param ) {
			if ( $k == 'CN' ) {
				$oraganizer = explode( ':mailto:', $param);
				$oraganizer_data['ID'] = strtolower( trim( preg_replace( '/^"(.*)"$/', '\1', $oraganizer[0] ) ) );
				$oraganizer_data['name'] = preg_replace( '/^"(.*)"$/', '\1', $oraganizer[0] );
				$oraganizer_data['email'] = preg_replace( '/^"(.*)"$/', '\1', trim( $oraganizer[1]) );
			} else {
				if ( ! empty( $param ) ) {
					$oraganizer_data[ $k ] = $param;
				}
			}
		}
		return $oraganizer_data;
	}

	/**
	 * Get location args for event
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function get_location( $ical_event ) {

		if ( !isset( $ical_event->location ) || empty( $ical_event->location ) ) {
			return null;
		}

		$event_location = array(
			'ID'           => strtolower( trim( stripslashes( $ical_event->location ) ) ),
			'name'         => isset( $ical_event->location ) ? stripslashes( $ical_event->location ) : '',
			'description'  => '',
			'address_1'    => isset( $ical_event->location ) ? stripslashes( $ical_event->location ) : '',
			'address_2'    => '',
			'city'         => '',
			'state'        => '',
			'country'      => '',
			'zip'	       => '',
			'lat'     	   => '',
			'long'		   => '',
			'full_address' => isset( $ical_event->location ) ? stripslashes( $ical_event->location ) : '',
			'url'          => '',
			'image_url'    => ''
		);
		return $event_location;
	}

	/**
	 * Get UTC offset
	 *
	 * @since    1.0.0
	 */
	public function get_utc_offset( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
		} catch ( Exception $e ) {
			return '';
		}

		$timezone = $datetime->getTimezone();
		$offset   = $timezone->getOffset( $datetime ) / 60 / 60;

		if ( $offset >= 0 ) {
			$offset = '+' . $offset;
		}

		return 'UTC' . $offset;
	}
}
