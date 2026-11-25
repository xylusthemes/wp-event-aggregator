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
		$organizer_id  = isset( $event_data['organizer_id'] ) ? $event_data['organizer_id'] : '';
		$collection_id = isset( $event_data['collection_id'] ) ? $event_data['collection_id'] : '';
		$import_private_events = isset( $options['private_events'] ) ? $options['private_events'] : 'no';
		
		if( $event_data['import_by'] == 'organizer_id' ){

			$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/organizers/' . $organizer_id . '/events/?status=live&expand=venue,ticket_availability,organizer,organizer.logo,category&token=' .  $this->oauth_token;
		
		}elseif( $event_data['import_by'] == 'collection_id' ){
			$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/collections/' . $collection_id . '/events/?time_filter=current_future&expand=venue,ticket_availability,organizer,organizer.logo,category&token=' .  $this->oauth_token;

		}elseif( $event_data['import_by'] == 'your_events' ){

			$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/users/me/events/?status=live&expand=venue,ticket_availability,organizer,organizer.logo,category&token=' .  $this->oauth_token;
		}

		if( $event_data['import_by'] != 'collection_id' ){
			if( $import_private_events === 'no'){
				$eventbrite_api_url .= '&only_public=on';
			}
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
			if( $eventbrite_events['error'] == 'INVALID_AUTH' ){
				$error_description =  str_replace( 'OAuth token', 'Private token', $eventbrite_events['error_description'] );
				$wpea_errors[] = sprintf(
					// translators: %s: The error description after the replacement of 'OAuth token' with 'Private token'.
					__( 'Error: %s', 'wp-event-aggregator' ),
					$error_description
				);
				return;
			}
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
						$wpea_errors[] = sprintf( 
							// translators: %s: Eventbrite event ID
							esc_html__( 'Please provide valid Eventbrite event ID: %s.', 'wp-event-aggregator' ), 
							$eventbrite_id 
						); 
						continue;
					}

					$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/events/' . $eventbrite_id . '/?expand=venue,ticket_availability,organizer,organizer.logo,category&token=' .  $this->oauth_token;
				    $eventbrite_response = wp_remote_get( $eventbrite_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

					if ( is_wp_error( $eventbrite_response ) ) {
						$wpea_errors[] = sprintf( 
							// translators: %s: Eventbrite event ID
							esc_html__( 'Something went wrong with Eventbrite event ID: %s, please try again.', 'wp-event-aggregator' ), 
							$eventbrite_id 
						); 
						continue;
					}

					$eventbrite_event = json_decode( $eventbrite_response['body'], true );
					if ( is_array( $eventbrite_event ) && ! isset( $eventbrite_event['error'] ) ) {

						$imported_events[] = $this->save_eventbrite_event( $eventbrite_event, $event_data );
						
					}else{

						if( $eventbrite_event['error'] == 'INVALID_AUTH' ){
							$error_description =  str_replace( 'OAuth token', 'Private token', $eventbrite_event['error_description'] );
							$wpea_errors[] = sprintf(
								// translators: %s: The error description after the replacement of 'OAuth token' with 'Private token'.
								__( 'Error: %s', 'wp-event-aggregator' ),
								$error_description
							);
							return;
						}
						$wpea_errors[] = sprintf( 
							// translators: %s: Eventbrite event ID
							esc_html__( 'Something went wrong with Eventbrite event ID: %s, please try again.', 'wp-event-aggregator' ), 
							$eventbrite_id
						); 
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
		$options = wpea_get_import_options( 'eventbrite' );

		if( ! isset( $eventbrite_event['id'] ) ){
			return false;
		}

		$start_time = $start_time_utc = time();
		$end_time = $end_time_utc = time();
		$utc_offset = '';

		if ( array_key_exists( 'start', $eventbrite_event ) ) {
			$start_time = isset( $eventbrite_event['start']['local'] ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $eventbrite_event['start']['local'] ) ) : strtotime( gmdate( 'Y-m-d H:i:s') );
			$start_time_utc = isset( $eventbrite_event['start']['utc'] ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $eventbrite_event['start']['utc'] ) ) : '';
			$utc_offset = $importevents->common->get_utc_offset( $eventbrite_event['start']['local'] );
		}

		if ( array_key_exists( 'end', $eventbrite_event ) ) {
			$end_time = isset( $eventbrite_event['end']['local'] ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $eventbrite_event['end']['local'] ) ) : $start_time;
			$end_time_utc = isset( $eventbrite_event['end']['utc'] ) ? strtotime( $importevents->common->convert_datetime_to_db_datetime( $eventbrite_event['end']['utc'] ) ) : $start_time_utc;

		}
		
		$timezone = isset( $eventbrite_event['start']['timezone'] ) ? $eventbrite_event['start']['timezone']:'';
		$event_name = isset( $eventbrite_event['name']['text']) ? sanitize_text_field( $eventbrite_event['name']['text'] ) : '';
		$series_id   = isset( $eventbrite_event['series_id'] ) ? $eventbrite_event['series_id'] : '';
		$event_description = $this->get_eventbrite_event_description( $eventbrite_event['id'], $series_id );
		$event_url = array_key_exists( 'url', $eventbrite_event ) ? esc_url($eventbrite_event['url']): '';
		$event_image  = array_key_exists( 'logo', $eventbrite_event ) ? urldecode( $eventbrite_event['logo']['original']['url'] ) : '';
		$image = explode( '?s=', $event_image );
		$image_url = esc_url( urldecode( str_replace('https://img.evbuc.com/', '', $image[0] ) ) );
		$online_event = ( isset( $eventbrite_event['online_event'] ) && $eventbrite_event['online_event'] ) ? 'yes' : 'no';
		$ticket_price      = isset( $eventbrite_event['ticket_availability']['minimum_ticket_price']['major_value'] ) ? $eventbrite_event['ticket_availability']['minimum_ticket_price']['major_value'] : '';	
		$ticket_currency   = isset( $eventbrite_event['ticket_availability']['minimum_ticket_price']['currency'] ) ? $eventbrite_event['ticket_availability']['minimum_ticket_price']['currency'] : '';	
		$eventbrite_cat    = isset( $eventbrite_event['category']['name'] ) ? $eventbrite_event['category']['name'] : '';
		$organization_id   = isset( $eventbrite_event['organization_id'] ) ? $eventbrite_event['organization_id'] : '';

		// Get is_sold_out and has_tickets status
		$is_sold_out = 'no';
		$has_tickets = 'no';

		if ( isset( $eventbrite_event['ticket_availability'] ) ) {
			$has_tickets = 'yes';
		}

		if ( isset( $eventbrite_event['ticket_availability']['is_sold_out'] ) && $eventbrite_event['ticket_availability']['is_sold_out'] ) {
			$is_sold_out = 'yes';
		} elseif ( ! empty( $this->oauth_token ) && ! empty( $eventbrite_event['id'] ) ) {
			// Check ticket classes API if ticket_availability is not conclusive
			$tickets_api_url  = 'https://www.eventbriteapi.com/v3/events/' . $eventbrite_event['id'] . '/ticket_classes/?token=' . $this->oauth_token;
			$tickets_response = wp_remote_get( $tickets_api_url, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );
			if ( ! is_wp_error( $tickets_response ) ) {
				$tickets_data = json_decode( $tickets_response['body'], true );
				if ( isset( $tickets_data['ticket_classes'] ) && is_array( $tickets_data['ticket_classes'] ) ) {
					$all_sold_out = true;
					$tc_has_tickets = false;
					foreach ( $tickets_data['ticket_classes'] as $tc ) {
						$tc_has_tickets = true;
						if ( isset( $tc['on_sale_status'] ) && $tc['on_sale_status'] === 'AVAILABLE' ) {
							$all_sold_out = false;
							break;
						}
					}
					if ( $tc_has_tickets ) {
						$has_tickets = 'yes';
						if ( $all_sold_out ) {
							$is_sold_out = 'yes';
						}
					}
				}
			}
		}

		$import_promo_codes = isset( $options['import_promo_codes'] ) ? $options['import_promo_codes'] : 'no';
		$get_promocode     = '';
		if ( 'yes' === $import_promo_codes ) {
			$get_promocode = $importevents->common->wpea_get_event_discount_code( $eventbrite_event['id'], $organization_id );
		}

		$is_insert_etags   = isset( $options['eventbritre_tags'] ) ? $options['eventbritre_tags'] : 'no';
		$eventbrite_tags   = array();

		if ( 'yes' === $is_insert_etags ) {
			$eventbrite_tags = isset( $eventbrite_event['tags'] ) ? $importevents->common->prepare_eventbrite_tag_names( $eventbrite_event['tags'] ) : array();

			if ( empty( $eventbrite_tags ) ) {
				$eventbrite_tags = $importevents->common->get_eventbrite_tags_by_event_id( $eventbrite_event['id'] );
			}
		}

		$import_structured_content = isset( $options['import_structured_content'] ) ? $options['import_structured_content'] : 'no';
		$structured_content        = array();

		if ( 'yes' === $import_structured_content ) {
			$dont_update_sc = isset( $options['dont_update']['structured_content'] ) ? $options['dont_update']['structured_content'] : 'no';
			$skip_api       = false;
			if ( 'yes' === $dont_update_sc ) {
				global $wpdb;
				$is_existing = $wpdb->get_var( $wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
					'wpea_event_id',
					$eventbrite_event['id']
				) );
				if ( ! empty( $is_existing ) ) {
					$skip_api = true;
				}
			}
			if ( ! $skip_api ) {
				$structured_content = $this->get_structured_content( $eventbrite_event['id'] );
			}
		}

		$xt_event = array(
			'origin'             => 'eventbrite',
			'ID'                 => isset( $eventbrite_event['id'] ) ? $eventbrite_event['id'] : '',
			'name'               => $event_name,
			'description'        => $event_description,
			'starttime_local'    => $start_time,
			'endtime_local'      => $end_time,
			'startime_utc'       => $start_time_utc,
			'endtime_utc'        => $end_time_utc,
			'timezone'           => $timezone,
			'timezone_name'      => $timezone,
			'utc_offset'         => $utc_offset,
			'event_duration'     => '',
			'is_all_day'         => '',
			'url'                => $event_url,
			'image_url'          => $image_url,
			'online_event'       => $online_event,
			'series_id'          => $series_id,
			'ticket_price'       => $ticket_price,
			'ticket_currency'    => $ticket_currency,
			'e_category'         => $eventbrite_cat,
			'discount_code'      => $get_promocode,
			'e_tags'             => $eventbrite_tags,
			'is_sold_out'        => $is_sold_out,
			'has_tickets'        => $has_tickets,
			'structured_content' => $structured_content,
		);

		if ( array_key_exists( 'organizer', $eventbrite_event ) ) {
			$organizer_details = $eventbrite_event['organizer'];
			$xt_event['organizer'] = $this->get_organizer( $organizer_details );
		}

		if ( array_key_exists( 'venue', $eventbrite_event ) ) {
			$location_details = $eventbrite_event['venue'];
			$online_event     = $eventbrite_event['online_event'] ?? false;
			if( $online_event ){
				$location_details['name'] = 'Online Event';
			}
			$xt_event['location'] = $this->get_location( $location_details );
		}

		return apply_filters( 'wpea_eventbrite_generate_centralize_array', $xt_event, $eventbrite_event );
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function get_organizer( $organizer_details ) {
		if ( array_key_exists( 'id', $organizer_details ) && isset( $organizer_details['name'] ) && ! empty( $organizer_details['name'] ) ) {
			$org_image = isset( $organizer_details['logo']['original']['url'] ) ? urldecode( $organizer_details['logo']['original']['url'] ) : '';
			$image     = explode( '?s=', $org_image );
			$image_url = esc_url( urldecode( str_replace( 'https://img.evbuc.com/', '', $image[0] ) ) );

			$event_organizer = array(
				'ID'          => isset( $organizer_details['id'] ) ? $organizer_details['id'] : '',
				'name'        => isset( $organizer_details['name'] ) ? $organizer_details['name'] : '',
				'description' => isset( $organizer_details['description']['text'] ) ? $organizer_details['description']['text'] : '',
				'email'       => '',
				'phone'       => '',
				'url'         => isset( $organizer_details['url'] ) ? $organizer_details['url'] : '',
				'image_url'   => $image_url,
			);
			return $event_organizer;
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
	// public function get_location( $eventbrite_event, $series_id ) {
	// 	if ( ! array_key_exists( 'venue_id', $eventbrite_event ) ) {
	// 		return null;
	// 	}

	// 	if ( ! empty( $series_id ) ) {
	// 		$loc_transient_key = 'wpea_series_location_' . $series_id;
	// 		$cached_loc        = get_transient( $loc_transient_key );

	// 		if ( ! empty( $cached_loc ) ) {
	// 			return $cached_loc;
	// 		}
	// 	}

	// 	$event_venue_id = $eventbrite_event['venue_id'];
	// 	$is_online      = $eventbrite_event['online_event'];
	// 	if( $is_online === true ){
	// 		$event_location = array(
	// 			'name'         => 'Online Event',
	// 		);
	// 		return $event_location;
	// 	}
	// 	$get_venue = wp_remote_get( 'https://www.eventbriteapi.com/v3/venues/' . $event_venue_id .'/?token=' . $this->oauth_token, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

	// 	if ( ! is_wp_error( $get_venue ) ) {
	// 		$venue = json_decode( $get_venue['body'], true );
	// 		if ( is_array( $venue ) && ! isset( $venue['errors'] ) ) {
	// 			if ( ! empty( $venue ) && array_key_exists( 'id', $venue ) ) {

	// 				$event_location = array(
	// 					'ID'           => isset( $venue['id'] ) ? $venue['id'] : '',
	// 					'name'         => isset( $venue['name'] ) ? $venue['name'] : '',
	// 					'description'  => '',
	// 					'address_1'    => isset( $venue['address']['address_1'] ) ? $venue['address']['address_1'] : '',
	// 					'address_2'    => isset( $venue['address']['address_2'] ) ? $venue['address']['address_2'] : '',
	// 					'city'         => isset( $venue['address']['city'] ) ? $venue['address']['city'] : '',
	// 					'state'        => isset( $venue['address']['region'] ) ? $venue['address']['region'] : '',
	// 					'country'      => isset( $venue['address']['country'] ) ? $venue['address']['country'] : '',
	// 					'zip'	       => isset( $venue['address']['postal_code'] ) ? $venue['address']['postal_code'] : '',
	// 					'lat'     	   => isset( $venue['address']['latitude'] ) ? $venue['address']['latitude'] : '',
	// 					'long'		   => isset( $venue['address']['longitude'] ) ? $venue['address']['longitude'] : '',
	// 					'full_address' => isset( $venue['address']['localized_address_display'] ) ? $venue['address']['localized_address_display'] : $venue['address']['address_1'],
	// 					'url'          => '',
	// 					'image_url'    => ''
	// 				);

	// 				if ( ! empty( $series_id ) ) {
	// 					set_transient( 'wpea_series_location_' . $series_id, $event_location, HOUR_IN_SECONDS );
	// 				}
	// 				return $event_location;
	// 			}
	// 		}
	// 	}
	// 	return null;
	// }
	public function get_location( $location_details ) {

		if ( isset( $location_details['name'] ) && ! empty( $location_details['name'] ) ) {
			$event_location = array(
				'ID'           => isset( $location_details['id'] ) ? $location_details['id'] : '',
				'name'         => isset( $location_details['name'] ) ? $location_details['name'] : '',
				'description'  => '',
				'address_1'    => isset( $location_details['address']['address_1'] ) ? $location_details['address']['address_1'] : '',
				'address_2'    => isset( $location_details['address']['address_2'] ) ? $location_details['address']['address_2'] : '',
				'city'         => isset( $location_details['address']['city'] ) ? $location_details['address']['city'] : '',
				'state'        => isset( $location_details['address']['region'] ) ? $location_details['address']['region'] : '',
				'country'      => isset( $location_details['address']['country'] ) ? $location_details['address']['country'] : '',
				'zip'          => isset( $location_details['address']['postal_code'] ) ? $location_details['address']['postal_code'] : '',
				'lat'          => isset( $location_details['address']['latitude'] ) ? $location_details['address']['latitude'] : '',
				'long'         => isset( $location_details['address']['longitude'] ) ? $location_details['address']['longitude'] : '',
				'full_address' => isset( $location_details['address']['localized_address_display'] ) ? $location_details['address']['localized_address_display'] : '',
				'url'          => '',
				'image_url'    => '',
			);
			return $event_location;
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

		$get_oraganizer = wp_remote_get( 'https://www.eventbriteapi.com/v3/organizers/' . $organizer_id .'/?token=' . $this->oauth_token, array( 'headers' => array( 'Content-Type' => 'application/json' ), 'timeout' => 20,  ) );

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
	function get_eventbrite_event_description( $eventbrite_id, $series_id = '' ) {
		$description = '';


		if ( ! empty( $series_id ) ) {
			$desc_transient_key = 'wpea_series_description_' . $series_id;
			$cached_desc        = get_transient( $desc_transient_key );

			if ( ! empty( $cached_desc ) ) {
				return $cached_desc;
			}
		}

		$eventbrite_desc_url  = 'https://www.eventbriteapi.com/v3/events/' . $eventbrite_id . '/description/?token=' . $this->oauth_token;
		$eventbrite_response = wp_remote_get( $eventbrite_desc_url, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );
		if ( !is_wp_error( $eventbrite_response ) ) {
			$event_desc = json_decode( wp_remote_retrieve_body($eventbrite_response) );
			$description = isset( $event_desc->description ) ? $event_desc->description : '';
			if ( ! empty( $description ) && ! empty( $series_id ) ) {
				set_transient( 'wpea_series_description_' . $series_id, $description, HOUR_IN_SECONDS );
			}
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

	/**
	 * Get Collection Name based on Collection ID.
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function wpea_get_collection_name_by_id( $collection_id ) {

		if ( ! $collection_id || $collection_id == '' ) {
			return;
		}

		$get_collection = wp_remote_get(
			'https://www.eventbriteapi.com/v3/collections/' . $collection_id . '/?token=' . $this->oauth_token,
			array(
				'headers' => array(
					'Content-Type' => 'application/json'
				),
				'timeout' => 20,
			)
		);

		if ( ! is_wp_error( $get_collection ) ) {
			$collection = json_decode( $get_collection['body'], true );
			if ( is_array( $collection ) && ! isset( $collection['errors'] ) ) {
				if ( ! empty( $collection ) && array_key_exists( 'id', $collection ) ) {

					$collection_name = isset( $collection['name'] ) ? $collection['name'] : '';
					return $collection_name;
				}
			}
		}
		return '';
	}

	/**
	 * Get structured content from Eventbrite API.
	 *
	 * @since 1.0.0
	 * @param string $event_id Event ID.
	 * @return array Structured content data.
	 */
	public function get_structured_content( $event_id ) {
		$structured_data = array(
			'lineup'         => array(),
			'speakers'       => array(),
			'images'         => array(),
			'slider_images'  => array(),
			'faqs'           => array(),
			'videos'         => array(),
			'featured_video' => array(),
			'parking'        => '',
		);

		if ( empty( $event_id ) ) {
			return $structured_data;
		}

		$eventbrite_api_url  = 'https://www.eventbriteapi.com/v3/events/' . $event_id . '/structured_content/?token=' . $this->oauth_token;
		$response            = wp_remote_get( $eventbrite_api_url, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( is_wp_error( $response ) ) {
			return $structured_data;
		}

		$body = json_decode( $response['body'], true );

		if ( empty( $body ) || ! is_array( $body ) ) {
			return $structured_data;
		}

		if ( isset( $body['modules'] ) && is_array( $body['modules'] ) ) {
			foreach ( $body['modules'] as $module ) {
				if ( isset( $module['type'] ) ) {
					if ( $module['type'] === 'image' && isset( $module['data']['image']['url'] ) ) {
						$structured_data['images'][] = $module['data']['image']['url'];
					} elseif ( $module['type'] === 'video' && isset( $module['data']['video']['url'] ) ) {
						$structured_data['videos'][] = $module['data']['video']['url'];
					}
				}
			}
		}

		$faq_widget = array();

		if ( isset( $body['widgets'] ) && is_array( $body['widgets'] ) ) {
			foreach ( $body['widgets'] as $widget ) {
				if ( isset( $widget['type'] ) ) {
					if ( $widget['type'] === 'lineup' && isset( $widget['data']['artist_list'] ) ) {
						if ( isset( $widget['data']['artist_type'] ) && $widget['data']['artist_type'] === 'speakers' ) {
							$structured_data['speakers'] = array_merge( $structured_data['speakers'], $widget['data']['artist_list'] );
						} else {
							$structured_data['lineup'] = array_merge( $structured_data['lineup'], $widget['data']['artist_list'] );
						}
					} elseif ( ( $widget['type'] === 'faqs' || $widget['type'] === 'faq' ) && isset( $widget['data']['faqs'] ) ) {
						if ( 'faqs' === $widget['type'] || empty( $faq_widget ) ) {
							$faq_widget = $widget['data']['faqs'];
						}
					} elseif ( $widget['type'] === 'herocarousel' && isset( $widget['data']['slides'] ) ) {
						foreach ( $widget['data']['slides'] as $slide ) {
							if ( isset( $slide['image']['url'] ) ) {
								$structured_data['slider_images'][] = $slide['image']['url'];
							} elseif ( isset( $slide['image']['original']['url'] ) ) {
								$structured_data['slider_images'][] = $slide['image']['original']['url'];
							}
						}
					} elseif ( $widget['type'] === 'native_video' && isset( $widget['data']['id'] ) ) {
						$structured_data['videos'][] = 'https://d1xykzbd1twk8p.cloudfront.net/' . $widget['data']['id'] . '_480p_vertical.mp4';
					} elseif ( $widget['type'] === 'featured_video' && ! empty( $widget['data']['video'] ) ) {
						$featured_video = $widget['data']['video'];
						$thumbnail      = '';

						if ( ! empty( $featured_video['thumbnail'] ) ) {
							foreach ( array( 'large', 'medium', 'small', 'url' ) as $size ) {
								if ( isset( $featured_video['thumbnail'][ $size ] ) ) {
									$candidate    = $featured_video['thumbnail'][ $size ];
									$thumbnail    = is_array( $candidate ) ? ( isset( $candidate['url'] ) ? $candidate['url'] : '' ) : $candidate;
									$thumbnail    = is_string( $thumbnail ) ? $thumbnail : '';
									if ( '' !== $thumbnail ) {
										break;
									}
								}
							}
						}

						$structured_data['featured_video'] = array(
							'url'       => isset( $featured_video['url'] ) ? $featured_video['url'] : '',
							'embed_url' => isset( $featured_video['embed_url'] ) ? $featured_video['embed_url'] : '',
							'provider'  => isset( $featured_video['provider'] ) ? $featured_video['provider'] : '',
							'thumbnail' => $thumbnail,
						);
					} elseif ( $widget['type'] === 'parking' && isset( $widget['data']['availability'] ) ) {
						$structured_data['parking'] = $widget['data']['availability'];
					}
				}
			}
		}

		if ( ! empty( $faq_widget ) ) {
			$structured_data['faqs'] = $faq_widget;
		}

		return $structured_data;
	}
}
