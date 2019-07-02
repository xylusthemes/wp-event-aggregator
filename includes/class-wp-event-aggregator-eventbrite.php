<?php
/**
 * Class for eventbrite Imports.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Eventbrite {

	public $oauth_token;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $importevents;
		$options = wpea_get_import_options( 'eventbrite' );
		$this->oauth_token = isset( $options['oauth_token'] ) ? $options['oauth_token'] : '';
	}

	/**
	 * import Eventbrite events by oraganiser or by user.
	 *
	 * @since    1.0.0
	 * @param array $eventdata  import event data.
	 * @return /boolean
	 */
	public function import_events( $event_data = array() ){

		global $wpea_errors, $importevents;
		$imported_events = array();
		$options = wpea_get_import_options( 'eventbrite' );
		$eventbrite_oauth_token = isset( $options['oauth_token'] ) ? $options['oauth_token'] : '';
		$organizer_id = isset( $event_data['organizer_id'] ) ? $event_data['organizer_id'] : '';
		
		if( $event_data['import_by'] == 'organizer_id' ){

			$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/organizers/' . $organizer_id . '/events/?status=live&token=' .  $this->oauth_token;

		}elseif( $event_data['import_by'] == 'your_events' ){

			$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/users/me/events/?status=live&token=' .  $this->oauth_token;
		}

		$eventbrite_response = wp_remote_get( $eventbrite_api_url );

		if ( is_wp_error( $eventbrite_response ) ) {
			$wpea_errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
			return;
		}

		$eventbrite_events = json_decode( $eventbrite_response['body'], true );
		if ( is_array( $eventbrite_events ) && ! isset( $eventbrite_events['error'] ) ) {

			$total_pages = $eventbrite_events['pagination']['page_count'];
			if( $total_pages > 1 ){
				for( $i = 1; $i <= $total_pages; $i++ ){
					$eventbrite_api = $eventbrite_api_url. '&page=' . $i;
					$eventbrite_response_loop = wp_remote_get( $eventbrite_api );
					if ( is_wp_error( $eventbrite_response_loop ) ) {
						$wpea_errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
						return;
					}
					$eventbrite_events_loop = json_decode( $eventbrite_response_loop['body'], true );
					if ( is_array( $eventbrite_events_loop ) && ! isset( $eventbrite_events_loop['error'] ) ) {
						$events_loop = $eventbrite_events_loop['events'];
						if( !empty( $events_loop ) ){
							foreach( $events_loop as $event_loop ){
								$imported_events[] = $this->save_eventbrite_event( $event_loop, $event_data );
							}
						}	
					}					
				}
			}else{
				$events = $eventbrite_events['events'];
				if( !empty( $events ) ){
					foreach( $events as $event ){
						$imported_events[] = $this->save_eventbrite_event( $event, $event_data );
					}
				}	
			}			
			return $imported_events;

		}else{
			$wpea_errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
			return;
		}

	}

	/**
	 * import Eventbrite event by ID.
	 *
	 * @since    1.0.0
	 * @param array $eventdata  import event data.
	 * @return /boolean
	 */
	public function import_event_by_event_id( $event_data = array() ){
		global $wpea_errors, $importevents;
		$options = wpea_get_import_options( 'eventbrite' );
		$eventbrite_oauth_token = isset( $options['oauth_token'] ) ? $options['oauth_token'] : '';
		
		if ( $this->oauth_token == '' ) {
			$wpea_errors[] = __( 'Please insert Eventbrite "Personal OAuth token".', 'wp-event-aggregator');
			return;
		}

		$imported_events = array();
		$eventbrite_ids = isset( $event_data['eventbrite_event_id'] ) ? $event_data['eventbrite_event_id'] : 0;

		if( !empty( $eventbrite_ids ) ){
			foreach ($eventbrite_ids as $eventbrite_id ) {
				if( $eventbrite_id != '' ){

					if( !is_numeric( $eventbrite_id ) ){
						$wpea_errors[] = sprintf( esc_html__( 'Please provide valid Eventbrite event ID: %s.', 'wp-event-aggregator' ), $eventbrite_id ) ;
						continue;
					}

					$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/events/' . $eventbrite_id . '/?token=' .  $this->oauth_token;
				    $eventbrite_response = wp_remote_get( $eventbrite_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

					if ( is_wp_error( $eventbrite_response ) ) {

						$wpea_errors[] = sprintf( esc_html__( 'Something went wrong with Eventbrite event ID: %s, please try again.', 'wp-event-aggregator' ), $eventbrite_id ) ;
						continue;
					}

					$eventbrite_event = json_decode( $eventbrite_response['body'], true );
					if ( is_array( $eventbrite_event ) && ! isset( $eventbrite_event['error'] ) ) {

						$imported_events[] = $this->save_eventbrite_event( $eventbrite_event, $event_data );
						
					}else{
						$wpea_errors[] = sprintf( esc_html__( 'Something went wrong with Eventbrite event ID: %s, please try again.', 'wp-event-aggregator' ), $eventbrite_id ) ;
						continue;
					}
				}		
			}
		}
		return $imported_events;
	}


	/**
	 * Save (Create or update) Eventbrite imported to The Event Calendar Events from a Eventbrite.com event.
	 *
	 * @since  1.0.0
	 * @param array  $eventbrite_event Event array get from Eventbrite.com.
	 * @param int    $post_id Eventbrite Url id.
	 * @return void
	 */
	public function save_eventbrite_event( $eventbrite_event = array(), $event_args = array() ) {

		global $importevents;
		if ( ! empty( $eventbrite_event ) && is_array( $eventbrite_event ) && array_key_exists( 'id', $eventbrite_event ) ) {
			$centralize_array = $this->generate_centralize_array( $eventbrite_event );
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
	public function generate_centralize_array( $eventbrite_event ) {
		global $importevents;

		if( ! isset( $eventbrite_event['id'] ) ){
			return false;
		}

		$start_time = $start_time_utc = time();
		$end_time = $end_time_utc = time();
		$utc_offset = '';

		if ( array_key_exists( 'start', $eventbrite_event ) ) {
			$start_time = isset( $eventbrite_event['start']['local'] ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $eventbrite_event['start']['local'] ) ) : strtotime( date( 'Y-m-d H:i:s') );
			$start_time_utc = isset( $eventbrite_event['start']['utc'] ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $eventbrite_event['start']['utc'] ) ) : '';
			$utc_offset = $importevents->common->get_utc_offset( $eventbrite_event['start']['local'] );
		}

		if ( array_key_exists( 'end', $eventbrite_event ) ) {
			$end_time = isset( $eventbrite_event['end']['local'] ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $eventbrite_event['end']['local'] ) ) : $start_time;
			$end_time_utc = isset( $eventbrite_event['end']['utc'] ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $eventbrite_event['end']['utc'] ) ) : $start_time_utc;

		}
		
		$timezone = isset( $eventbrite_event['start']['timezone'] ) ? $eventbrite_event['start']['timezone']:'';
		$event_name = isset( $eventbrite_event['name']['text']) ? sanitize_text_field( $eventbrite_event['name']['text'] ) : '';
		$event_description = isset( $eventbrite_event['description']['html'] ) ? $eventbrite_event['description']['html'] : '';
		$event_url = array_key_exists( 'url', $eventbrite_event ) ? esc_url($eventbrite_event['url']): '';
		$event_image  = array_key_exists( 'logo', $eventbrite_event ) ? urldecode( $eventbrite_event['logo']['original']['url'] ) : '';
		$image = explode( '?s=', $event_image );
		$image_url = esc_url( urldecode( str_replace('https://img.evbuc.com/', '', $image[0] ) ) );


		$xt_event = array(
			'origin'          => 'eventbrite',
			'ID'              => isset( $eventbrite_event['id'] ) ? $eventbrite_event['id'] : '',
			'name'            => $event_name,
			'description'     => $event_description,
			'starttime_local' => $start_time,
			'endtime_local'   => $end_time,
			'startime_utc'    => $start_time_utc,
			'endtime_utc'     => $end_time_utc,
			'timezone'        => $timezone,
			'utc_offset'      => $utc_offset,
			'event_duration'  => '',
			'is_all_day'      => '',
			'url'             => $event_url,
			'image_url'       => $image_url,
		);

		if ( array_key_exists( 'organizer_id', $eventbrite_event ) ) {
			$xt_event['organizer'] = $this->get_organizer( $eventbrite_event );
		}

		if ( array_key_exists( 'venue_id', $eventbrite_event ) ) {
			$xt_event['location'] = $this->get_location( $eventbrite_event );
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
	public function get_organizer( $eventbrite_event ) {
		if ( ! array_key_exists( 'organizer_id', $eventbrite_event ) ) {
			return null;
		}
		$event_organizer = $eventbrite_event['organizer_id'];
		$get_oraganizer = wp_remote_get( 'https://www.eventbriteapi.com/v3/organizers/' . $event_organizer .'/?token=' . $this->oauth_token, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( ! is_wp_error( $get_oraganizer ) ) {
			$oraganizer = json_decode( $get_oraganizer['body'], true );
			if ( is_array( $oraganizer ) && ! isset( $oraganizer['errors'] ) ) {
				if ( ! empty( $oraganizer ) && array_key_exists( 'id', $oraganizer ) ) {

					$org_image  = isset( $oraganizer['logo']['original']['url'] ) ? urldecode( $oraganizer['logo']['original']['url'] ) : '';
					$image = explode( '?s=', $org_image );
					$image_url = esc_url( urldecode( str_replace('https://img.evbuc.com/', '', $image[0] ) ) );

					$event_organizer = array(
						'ID'          => isset( $oraganizer['id'] ) ? $oraganizer['id'] : '',
						'name'        => isset( $oraganizer['name'] ) ? $oraganizer['name'] : '',
						'description' => isset( $oraganizer['description']['text'] ) ? $oraganizer['description']['text'] : '',
						'email'       => '',
						'phone'       => '',
						'url'         => isset( $oraganizer['url'] ) ? $oraganizer['url'] : '',
						'image_url'   => $image_url,
					);
					return $event_organizer;
				}
			}
		}
		return null;
	}

	/**
	 * Get location args for event
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function get_location( $eventbrite_event ) {
		if ( ! array_key_exists( 'venue_id', $eventbrite_event ) ) {
			return null;
		}
		$event_venue_id = $eventbrite_event['venue_id'];
		$get_venue = wp_remote_get( 'https://www.eventbriteapi.com/v3/venues/' . $event_venue_id .'/?token=' . $this->oauth_token, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( ! is_wp_error( $get_venue ) ) {
			$venue = json_decode( $get_venue['body'], true );
			if ( is_array( $venue ) && ! isset( $venue['errors'] ) ) {
				if ( ! empty( $venue ) && array_key_exists( 'id', $venue ) ) {

					$event_location = array(
						'ID'           => isset( $venue['id'] ) ? $venue['id'] : '',
						'name'         => isset( $venue['name'] ) ? $venue['name'] : '',
						'description'  => '',
						'address_1'    => isset( $venue['address']['address_1'] ) ? $venue['address']['address_1'] : '',
						'address_2'    => isset( $venue['address']['address_2'] ) ? $venue['address']['address_2'] : '',
						'city'         => isset( $venue['address']['city'] ) ? $venue['address']['city'] : '',
						'state'        => isset( $venue['address']['region'] ) ? $venue['address']['region'] : '',
						'country'      => isset( $venue['address']['country'] ) ? $venue['address']['country'] : '',
						'zip'	       => isset( $venue['address']['postal_code'] ) ? $venue['address']['postal_code'] : '',
						'lat'     	   => isset( $venue['address']['latitude'] ) ? $venue['address']['latitude'] : '',
						'long'		   => isset( $venue['address']['longitude'] ) ? $venue['address']['longitude'] : '',
						'full_address' => isset( $venue['address']['localized_address_display'] ) ? $venue['address']['localized_address_display'] : $venue['address']['address_1'],
						'url'          => '',
						'image_url'    => ''
					);
					return $event_location;
				}
			}
		}
		return null;
	}

	/**
	 * Get organizer Name based on Organiser ID.
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function get_organizer_name_by_id( $organizer_id ) {
		
		if( !$organizer_id || $organizer_id == '' ){
			return;
		}

		$get_oraganizer = wp_remote_get( 'https://www.eventbriteapi.com/v3/organizers/' . $organizer_id .'/?token=' . $this->oauth_token, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( ! is_wp_error( $get_oraganizer ) ) {
			$oraganizer = json_decode( $get_oraganizer['body'], true );
			if ( is_array( $oraganizer ) && ! isset( $oraganizer['errors'] ) ) {
				if ( ! empty( $oraganizer ) && array_key_exists( 'id', $oraganizer ) ) {

					$oraganizer_name = isset( $oraganizer['name'] ) ? $oraganizer['name'] : '';
					return $oraganizer_name;
				}
			}
		}
		return '';
	}

	/**
	 * Get Event description from eventbrite.
	 *
	 * @param $eventbrite_id
	 * @return string description
	 */
	function get_eventbrite_event_description($eventbrite_id){
		$description = '';
		$eventbrite_desc_url  = 'https://www.eventbriteapi.com/v3/events/' . $eventbrite_id . '/description/?token=' . $this->oauth_token;
		$eventbrite_response = wp_remote_get( $eventbrite_desc_url, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );
		if ( !is_wp_error( $eventbrite_response ) ) {
			$event_desc = json_decode( wp_remote_retrieve_body($eventbrite_response) );
			$description = isset( $event_desc->description ) ? $event_desc->description : '';
		}
		return $description;
	}

	/**
	 * import Eventbrite events by oraganiser or by user in background.
	 *
	 * @since    1.0
	 * @param array $post_id  import event data.
	 * @return /boolean
	 */
	public function background_import_events( $post_id = 0 ){
		$post = get_post( $post_id );
		if( !$post || empty( $post ) ){
			return; 
		}

		$default_args = array(
			'import_id'			=> $post_id, // Import_ID
			'page'				=> 1, // Page Number
			'event_index'		=> -1, // product index needed incase of memory issuee or timeout
			'prevent_timeouts'	=> true // Check memory and time usage and abort if reaching limit.
		);

		$params = $default_args;

		$import = new WPEA_Background_Process();
		$import->push_to_queue( $params );
		$import->save()->dispatch();
		return true;
	}
}
