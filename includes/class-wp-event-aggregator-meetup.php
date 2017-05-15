<?php
/**
 * Class for meetup Imports.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Meetup {

	public $api_key;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $importevents;

		$options = wpea_get_import_options( 'meetup' );
		$this->api_key = isset( $options['meetup_api_key'] ) ? $options['meetup_api_key'] : '';
	}

	/**
	 * import Eventbrite events by oraganiser or by user.
	 *
	 * @since    1.0.0
	 * @param array $eventdata  import event data.
	 * @return /boolean
	 */
	public function import_events( $event_data = array() ){

		global $wpea_errors;
		$imported_events = array();
		$meetup_url = isset( $event_data['meetup_url'] ) ? $event_data['meetup_url'] : '';
		
		if( $this->api_key == '' ){
			$wpea_errors[] = __( 'Please insert "Meetup API key" in settings.', 'wp-event-aggregator');
			return;
		}

		$meetup_group_id = $this->fetch_group_slug_from_url( $meetup_url );
		if( $meetup_group_id == '' ){
			$wpea_errors[] = __( 'Please insert valid meetup group URL.', 'wp-event-aggregator');
			return;
		}

		$meetup_api_url = 'https://api.meetup.com/' . $meetup_group_id . '/events?key=' . $this->api_key;
	    $meetup_response = wp_remote_get( $meetup_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );
	    
	    if ( is_wp_error( $meetup_response ) ) {
			$wpea_errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
			return;
		}
		
		$meetup_events = json_decode( $meetup_response['body'], true );
		// Error Check
		if( isset( $meetup_events['errors'] ) && !empty( $meetup_events['errors'] ) ){
			foreach ( $meetup_events['errors'] as $meetuperror ) {
				$wpea_errors[] = $meetuperror['message'];
			}			
			return;
		}
		if ( is_array( $meetup_events ) && ! isset( $meetup_events['error'] ) ) {

			if( !empty( $meetup_events ) ){
				foreach ($meetup_events as $meetup_event) {
					$imported_events[] = $this->save_meetup_event( $meetup_event, $event_data );
				}	
			}
			return $imported_events;

		}else{
			$wpea_errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
			return;
		}

	}

	
	/**
	 * Save (Create or update) Meetup imported to The Event Calendar Events from a Meetup.com event.
	 *
	 * @since  1.0.0
	 * @param array  $meetup_event Event array get from Meetup.com.
	 * @param string $event_data events import data
	 * @return void
	 */
	public function save_meetup_event( $meetup_event = array(), $event_args = array() ) {
		global $importevents;
		if ( ! empty( $meetup_event ) && is_array( $meetup_event ) && array_key_exists( 'id', $meetup_event ) ) {
			$centralize_array = $this->generate_centralize_array( $meetup_event );
			return $importevents->common->import_events_into( $centralize_array, $event_args );
		}
	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function generate_centralize_array( $meetup_event ) {

		if( ! isset( $meetup_event['id'] ) ){
			return false;
		}

		$start_time = $start_time_utc = time();
		$end_time = $end_time_utc = time();
		$utc_offset = 0;

		if ( array_key_exists( 'time', $meetup_event ) ) {
			$start_time_utc = floor( $meetup_event['time'] / 1000 );
		}
		$event_duration = array_key_exists( 'duration', $meetup_event ) ? $meetup_event['duration'] : 0;
		$event_duration = absint( floor( $event_duration / 1000 ) ); // convert to seconds.
		$end_time_utc = absint( $start_time_utc + $event_duration );

		$utc_offset = array_key_exists( 'utc_offset', $meetup_event ) ? $meetup_event['utc_offset'] : 0;
		$utc_offset = floor( $utc_offset / 1000 );
		$start_time = absint( $start_time_utc + $utc_offset );
		$end_time = absint( $end_time_utc + $utc_offset );

		$event_name = isset( $meetup_event['name']) ? sanitize_text_field( $meetup_event['name'] ) : '';
		$event_description = isset( $meetup_event['description'] ) ? $meetup_event['description'] : '';
		$event_url = isset( $meetup_event['link'] ) ? $meetup_event['link'] : '';
		$image_url = '';

		$xt_event = array(
			'origin'          => 'meetup',
			'ID'              => isset( $meetup_event['id'] ) ? $meetup_event['id'] : '',
			'name'            => $event_name,
			'description'     => $event_description,
			'starttime_local' => $start_time,
			'endtime_local'   => $end_time,
			'startime_utc'    => $start_time_utc,
			'endtime_utc'     => $end_time_utc,
			'timezone'        => '',
			'utc_offset'      => $utc_offset,
			'event_duration'  => '',
			'is_all_day'      => '',
			'url'             => $event_url,
			'image_url'       => $image_url,
		);

		if ( array_key_exists( 'group', $meetup_event ) ) {
			$xt_event['organizer'] = $this->get_organizer( $meetup_event );
		}

		if ( array_key_exists( 'venue', $meetup_event ) ) {
			$xt_event['location'] = $this->get_location( $meetup_event );
		}
		return $xt_event;
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function get_organizer( $meetup_event ) {
		if ( ! array_key_exists( 'group', $meetup_event ) ) {
			return null;
		}

		$organizer = $meetup_event['group'];
		$event_organizer = array(
			'ID'          => isset( $organizer['id'] ) ? $organizer['id'] : '',
			'name'        => isset( $organizer['name'] ) ? $organizer['name'] : '',
			'description' => isset( $organizer['description'] ) ? $organizer['description'] : '',
			'email'       => '',
			'phone'       => '',
			'url'         => isset( $organizer['urlname'] ) ? "https://www.meetup.com/".$organizer['urlname']."/":'',
			'image_url'   => '',
		);
		return $event_organizer;

		/*$meetup_group_id = $this->fetch_group_slug_from_url( $meetup_url );
		if( $meetup_group_id != '' ){
			$meetup_api_url = 'https://api.meetup.com/' . $meetup_group_id . '/?key=' . $this->api_key;
		    $get_oraganizer = wp_remote_get( $meetup_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );
			if ( !is_wp_error( $get_oraganizer ) ) {
				$oraganizer = json_decode( $get_oraganizer['body'], true );
				if ( is_array( $oraganizer ) && ! isset( $oraganizer['errors'] ) ) {
					if ( ! empty( $oraganizer ) && array_key_exists( 'id', $oraganizer ) ) {

						$image_url  = isset( $oraganizer['organizer']['photo']['photo_link'] ) ? urldecode( $$oraganizer['organizer']['photo']['photo_link'] ) : '';
						
						$event_organizer = array(
							'ID'          => isset( $oraganizer['id'] ) ? $oraganizer['id'] : '',
							'name'        => isset( $oraganizer['name'] ) ? $oraganizer['name'] : '',
							'description' => isset( $oraganizer['description'] ) ? $oraganizer['description'] : '',
							'email'       => '',
							'phone'       => '',
							'url'         => isset( $oraganizer['link'] ) ? $oraganizer['link'] : '',
							'image_url'   => $image_url,
						);
						return $event_organizer;
					}
				}
			}
		}*/
	}

	/**
	 * Get location args for event
	 *
	 * @since    1.0.0
	 * @param array $meetup_event meetup event.
	 * @return array
	 */
	public function get_location( $meetup_event ) {
		if ( ! array_key_exists( 'venue', $meetup_event ) ) {
			return null;
		}
		$venue = $meetup_event['venue'];
		$event_location = array(
			'ID'           => isset( $venue['id'] ) ? $venue['id'] : '',
			'name'         => isset( $venue['name'] ) ? $venue['name'] : '',
			'description'  => '',
			'address_1'    => isset( $venue['address_1'] ) ? $venue['address_1'] : '',
			'address_2'    => isset( $venue['address_2'] ) ? $venue['address_2'] : '',
			'city'         => isset( $venue['city'] ) ? $venue['city'] : '',
			'state'        => isset( $venue['state'] ) ? $venue['state'] : '',
			'country'      => isset( $venue['country'] ) ? strtoupper( $venue['country'] ) : '',
			'zip'	       => isset( $venue['zip'] ) ? $venue['zip'] : '',
			'lat'     	   => isset( $venue['lat'] ) ? $venue['lat'] : '',
			'long'		   => isset( $venue['lon'] ) ? $venue['lon'] : '',
			'full_address' => isset( $venue['address_1'] ) ? $venue['address_1'] : '',
			'url'          => '',
			'image_url'    => '',
			'phone'	       => isset( $venue['phone'] ) ? $venue['phone'] : '',
		);
		return $event_location;
	}
	
	/**
	 * Get organizer Name based on Organiser ID.
	 *
	 * @since    1.0.0
	 * @param array $meetup_url Meetup event.
	 * @return array
	 */
	public function get_meetup_group_name_by_url( $meetup_url ) {
		
		if( !$meetup_url || $meetup_url == '' ){
			return;
		}
		
		if( $this->api_key == '' ){
			$wpea_errors[] = __( 'Please insert "Meetup API key" in settings.', 'wp-event-aggregator');
			return;
		}

		$url_group_slug = $this->fetch_group_slug_from_url( $meetup_url );
		if( $url_group_slug == '' ){ return; }

		$get_group = wp_remote_get( 'https://api.meetup.com/' . $url_group_slug .'/?key=' . $this->api_key, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );
		if ( ! is_wp_error( $get_group ) ) {
			$group = json_decode( $get_group['body'], true );
			if ( is_array( $group ) && ! isset( $group['errors'] ) ) {
				if ( ! empty( $group ) && array_key_exists( 'id', $group ) ) {

					$group_name = isset( $group['name'] ) ? $group['name'] : '';
					return $group_name;
				}
			}
		}
		return '';
	}

	/**
	 * Fetch group slug from group url.
	 *
	 * @since    1.0.0
	 * @param string $url Meetup group url.
	 * @return string
	 */
	public function fetch_group_slug_from_url( $url = '' ) {
		$url = str_replace( 'https://www.meetup.com/', '', $url );
		$url = str_replace( 'http://www.meetup.com/', '', $url );

		// Remove last slash and make grab slug upto slash.
		$slash_position = strpos( $url, '/' );
		if ( false !== $slash_position ) {
			$url = substr( $url, 0, $slash_position );
		}
		return $url;
	}
}
