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
	public $access_token;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $importevents;

		$options = wpea_get_import_options( 'meetup' );
		$this->api_key = isset( $options['meetup_api_key'] ) ? $options['meetup_api_key'] : '';
		if( empty( $this->api_key) ){
			$auth_token = $this->get_user_auth_token();
			$this->access_token = $auth_token;
		}
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
		$import_by       = isset( $event_data['import_by'] ) ? $event_data['import_by'] : '';
		$event_ids       = isset( $event_data['ime_event_ids'] ) ? $event_data['ime_event_ids'] : array();
		$meetup_url      = isset( $event_data['meetup_url'] ) ? $event_data['meetup_url'] : '';
		$api             = new WP_Event_Aggregator_Meetup_API();
		
		if( empty($this->api_key) && empty($this->access_token) ){
			$wpea_errors[] = __( 'Please insert "Meetup API key" Or OAuth key and secret in settings.', 'wp-event-aggregator');
			return;
		}

		if( 'group_url' === $import_by ){
			
			$meetup_group_id = $this->fetch_group_slug_from_url( $meetup_url );
			if( $meetup_group_id == '' ){ 
				$wpea_errors[] = esc_html__( 'Please provide valid Meetup group URL.', 'wp-event-aggregator' );	
				return; 
			}

			$itemsnum       = 50;
			$endcursor      = null;
			$have_next_page = true;
			
			while( true === $have_next_page ){
				$meetup_event_data   = $api->getGroupEvents( $meetup_group_id, $itemsnum, $endcursor );
				$get_upcoming_events = $meetup_event_data['data']['groupByUrlname']['upcomingEvents'];
				$meetup_events       = $get_upcoming_events['edges'];
			
				if( !empty( $meetup_events ) ){
					foreach ($meetup_events as $meetup_event) {
						$imported_events[] = $this->save_meetup_event( $meetup_event['node'], $event_data );
					}	
				}
				$endcursor      = $get_upcoming_events['pageInfo']['endCursor'];
				$have_next_page = $get_upcoming_events['pageInfo']['hasNextPage'];
			}

			return $imported_events;

		}elseif(  $import_by === 'event_id' ) {
			if( !empty( $event_ids ) ){
				foreach ( $event_ids as $event_id ) {
					if ( ! empty( $event_id ) ) {
						$meetup_event_data = $api->getEvent( $event_id );
						$meetup_event      = $meetup_event_data['data']['event'];				
						if( !empty( $meetup_event ) ){
							$imported_events[] = $this->save_meetup_event( $meetup_event, $event_data );	
						}else{
							$wpea_errors[] = __( 'Please insert valid Meetup Event ID.', 'wp-event-aggregator');
							return;
						}
					}
				}
			}
			return $imported_events;
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

		$parsedown         = new ParsedownWpea();
		$timezone          = isset( $meetup_event['timezone'] ) ? $meetup_event['timezone'] : '';
		$start             = isset( $meetup_event['dateTime'] ) ? $meetup_event['dateTime'] : ''; 
		$end               = isset( $meetup_event['endTime'] ) ? $meetup_event['endTime'] : '';
		$start_time        = strtotime( $this->convert_datetime_to_timezone_wise_datetime( $start, $timezone ) );
		$end_time          = strtotime( $this->convert_datetime_to_timezone_wise_datetime( $end, $timezone ) );
		$event_name        = isset( $meetup_event['title']) ? sanitize_text_field( $meetup_event['title'] ) : '';
		$event_url         = isset( $meetup_event['eventUrl'] ) ? $meetup_event['eventUrl'] : '';
		$image_url         = isset( $meetup_event['imageUrl'] ) ? $meetup_event['imageUrl'] : '';
		$event_description = isset( $meetup_event['description'] ) ? $parsedown->text($meetup_event['description']) : '';
		$shortDescription  = isset( $meetup_event['shortDescription'] ) ? $meetup_event['shortDescription'] : '';
		$status            = isset( $meetup_event['status'] ) ? $meetup_event['status'] : '';
		$isOnline          = isset( $meetup_event['isOnline'] ) ? $meetup_event['isOnline'] : '';
		$event_id          = isset( $meetup_event['id'] ) ? str_replace( '!chp', '', $meetup_event['id']  ) : '';

		$xt_event = array(
			'origin'          => 'meetup',
			'ID'              => $event_id,
			'name'            => $event_name,
			'description'     => $event_description,
			'starttime_local' => $start_time,
			'endtime_local'   => $end_time,
			'startime_utc'    => $start_time,
			'endtime_utc'     => $end_time,
			'timezone'        => $timezone,
			'timezone_name'   => $timezone,
			'utc_offset_hours'=> $this->get_utc_offset( $timezone),
			'utc_offset'      => $this->get_utc_offset( $timezone),
			'is_all_day'      => '',
			'url'             => $event_url,
			'image_url'       => $image_url,
			'shortDescription'=> $shortDescription,
			'status'          => $status,
			'isOnline'        => $isOnline,
		);

		if ( array_key_exists( 'group', $meetup_event ) ) {
			$xt_event['organizer'] = $this->get_organizer( $meetup_event );
		}

		if ( array_key_exists( 'venue', $meetup_event ) ) {
			$xt_event['location'] = $this->get_location( $meetup_event );
		}
		return apply_filters( 'wpea_meetup_generate_centralize_array', $xt_event, $meetup_event );
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
			'email'       => isset( $organizer['emailListAddress'] ) ? $organizer['emailListAddress'] : '',
			'phone'       => '',
			'url'         => isset( $organizer['urlname'] ) ? "https://www.meetup.com/".$organizer['urlname']."/":'',
		);
		return $event_organizer;
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
			'address_1'    => isset( $venue['address']) ? $venue['address'] : '',
			'full_address' => isset( $venue['address'] ) ? $venue['address'] : '',
			'city'         => isset( $venue['city'] ) ? $venue['city'] : '',
			'state'        => isset( $venue['state'] ) ? $venue['state'] : '',
			'country'      => isset( $venue['country'] ) ? strtoupper( $venue['country'] ) : '',
			'lat'          => isset( $venue['lat'] ) ? $venue['lat'] : '',
			'long'         => isset( $venue['lng'] ) ? $venue['lng'] : '',
			'zip'          => isset( $venue['postalCode'] ) ? $venue['postalCode'] : '',
			'phone'        => isset( $venue['phone'] ) ? $venue['phone'] : '',
			'url'          => '',
			'image_url'    => '',
			'description'  => '',
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
		global $wpea_errors;
		if( !$meetup_url || $meetup_url == '' ){
			return;
		}
		
		if( empty($this->api_key) && empty($this->access_token) ){
			$wpea_errors[] = __( 'Please insert "Meetup API key" Or OAuth key and secret in settings.', 'wp-event-aggregator');
			return;
		}

		$url_group_slug = $this->fetch_group_slug_from_url( $meetup_url );
		if( $url_group_slug == '' ){ return; }
		
		$api               = new WP_Event_Aggregator_Meetup_API();
		$meetup_group_data = $api->getGroupName( $url_group_slug );
		$get_group         = $meetup_group_data['data']['groupByUrlname'];
					
		if ( is_array( $get_group ) && ! isset( $get_group['errors'] ) ) {
			if ( ! empty( $get_group ) && array_key_exists( 'id', $get_group ) ) {
				$group_name = isset( $get_group['name'] ) ? $get_group['name'] : '';
				return $group_name;
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

	/*
	* Refresh Meetup user access token
	*/
    function wpea_refresh_user_token() {
    	$wpea_user_token_options = get_option( 'wpea_muser_token_options', array() );
    	$wpea_options = get_option( WPEA_OPTIONS );
		$meetup_options = isset($wpea_options['meetup'])? $wpea_options['meetup'] : array();
		$meetup_oauth_key = isset( $meetup_options['meetup_oauth_key'] ) ? $meetup_options['meetup_oauth_key'] : '';
		$meetup_oauth_secret = isset( $meetup_options['meetup_oauth_secret'] ) ? $meetup_options['meetup_oauth_secret'] : '';
		$refresh_token = isset($wpea_user_token_options->refresh_token) ? $wpea_user_token_options->refresh_token : '';

		if( $meetup_oauth_key != '' && $meetup_oauth_secret != '' && $refresh_token != '' ){
			$token_url = 'https://secure.meetup.com/oauth2/access';
			$args = array(
				'method' => 'POST',
				'headers' => array( 'content-type' => 'application/x-www-form-urlencoded'),
				'body'    => "client_id={$meetup_oauth_key}&client_secret={$meetup_oauth_secret}&grant_type=refresh_token&refresh_token={$refresh_token}"
			);
			$access_token = "";
			$wpea_user_token_options = array();
			$response = wp_remote_post( $token_url, $args );
			$body = wp_remote_retrieve_body( $response );
			$body_response = json_decode( $body );
			if ($body != '' && isset( $body_response->access_token ) ) {
				$access_token = $body_response->access_token;
				delete_transient('wpea_meetup_auth_token');
			    update_option('wpea_muser_token_options', $body_response);
			    return $access_token;
			}else{
				return false;
			}
		} else {
			return false;
		}
		return false;
    }

	/**
	 * Get User Auth Token
	 *
	 * @return string
	 */
	public function get_user_auth_token(){
		$wpea_transient_key = 'wpea_meetup_auth_token';
		$auth_token = get_transient( $wpea_transient_key );
		if ( false === $auth_token ) {
			$wpea_user_token_options = get_option( 'wpea_muser_token_options', array() );
			if( !empty( $wpea_user_token_options->refresh_token ) ){
				$auth_token = $this->wpea_refresh_user_token();
				if($auth_token){
					// Set transient.
					set_transient( $wpea_transient_key, $auth_token, 1800 );
				}
			}
		}
		return $auth_token;
	}

	/**
	 * Convert datetime to desired timezone.
	 *
	 * @param string $date_string     Date string to possibly convert
	 * @param string $timezone timezone to be conterted
	 *
	 * @return string
	 */
	public function convert_datetime_to_timezone_wise_datetime( $datetime, $timezone = false ) {
		try {
			$datetime = new DateTime( $datetime );
			if( $timezone && $timezone !='' ){
				try{
					$datetime->setTimezone(new DateTimeZone( $timezone ) );
				}catch ( Exception $ee ){ }
			}
			return $datetime->format( 'Y-m-d H:i:s' );
		}
		catch ( Exception $e ) {
		return $datetime;
		}
	}

	/**
	 * Get UTC offset for given timezone.
	 */
	public function get_utc_offset( $timezone = '') {
		$tz = new DateTimeZone($timezone);
		$utc_tz = new DateTimeZone('UTC');
		$now = new DateTime("now", $tz);
		$utc_now = new DateTime("now", $utc_tz);
		$offset = ( ( $tz->getOffset($now) - $utc_tz->getOffset($utc_now) ) / 3600 );
		return $offset;
	}

	/**
	 * import Meetup events by group in background.
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
			'import_id'        => $post_id, // Import_ID
			'limit'            => 25,
			'event_index'      => -1, // event index needed incase of memory issuee or timeout
			'prevent_timeouts' => true // Check memory and time usage and abort if reaching limit.
		);

		$params = $default_args;

		$import = new WPEA_Background_Process();
		$import->push_to_queue( $params );
		$import->save()->dispatch();
		return true;
	}
}
