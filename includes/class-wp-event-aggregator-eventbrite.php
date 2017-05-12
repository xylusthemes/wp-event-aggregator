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

		global $errors;
		$imported_events = array();
		$options = wpea_get_import_options( 'eventbrite' );
		$eventbrite_oauth_token = isset( $options['oauth_token'] ) ? $options['oauth_token'] : '';
		$organizer_id = isset( $event_data['organizer_id'] ) ? $event_data['organizer_id'] : '';
		$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
		
		if( $event_data['import_by'] == 'organizer_id' ){

			$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/organizers/' . $organizer_id . '/events/?token=' .  $this->oauth_token;

		}elseif( $event_data['import_by'] == 'your_events' ){

			$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/users/me/events/?token=' .  $this->oauth_token;
		}

		$eventbrite_response = wp_remote_get( $eventbrite_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( is_wp_error( $eventbrite_response ) ) {
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
			return;
		}

		$eventbrite_events = json_decode( $eventbrite_response['body'], true );
		if ( is_array( $eventbrite_events ) && ! isset( $eventbrite_events['error'] ) ) {

			$total_pages = $eventbrite_events['pagination']['page_count'];
			if( $total_pages > 1 ){
				for( $i = 0; $i <= $total_pages; $i++ ){
					$eventbrite_api = $eventbrite_api_url. ' &page=' . $i;
					$eventbrite_response_loop = wp_remote_get( $eventbrite_api , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );
					if ( is_wp_error( $eventbrite_response_loop ) ) {
						$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
						return;
					}
					$eventbrite_events_loop = json_decode( $eventbrite_response_loop['body'], true );
					if ( is_array( $eventbrite_events_loop ) && ! isset( $eventbrite_events_loop['error'] ) ) {
						$events_loop = $eventbrite_events['events'];
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
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
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
		global $errors;
		$options = wpea_get_import_options( 'eventbrite' );
		$eventbrite_oauth_token = isset( $options['oauth_token'] ) ? $options['oauth_token'] : '';
		$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
		$eventbrite_id = isset( $event_data['eventbrite_event_id'] ) ? $event_data['eventbrite_event_id'] : 0;

		$is_exitsing_event = $this->get_event_by_event_id( $eventbrite_id );
		if ( $is_exitsing_event && $update_events == 'no' ) {
			$errors[] = __( 'Eventbrite event is already exists', 'wp-event-aggregator');
			return;
		}

		if ( ! $eventbrite_id || $this->oauth_token == '' ) {
			$errors[] = __( 'Please insert Eventbrite "Personal OAuth token".', 'wp-event-aggregator');
			return;
		}

		$eventbrite_api_url = 'https://www.eventbriteapi.com/v3/events/' . $eventbrite_id . '/?token=' .  $this->oauth_token;
	    $eventbrite_response = wp_remote_get( $eventbrite_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( is_wp_error( $eventbrite_response ) ) {
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
			return;
		}

		$eventbrite_event = json_decode( $eventbrite_response['body'], true );
		if ( is_array( $eventbrite_event ) && ! isset( $eventbrite_event['error'] ) ) {

			return $this->save_eventbrite_event( $eventbrite_event, $event_data );
			
		}else{
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
			return;
		}
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

		if ( ! empty( $eventbrite_event ) && is_array( $eventbrite_event ) && array_key_exists( 'id', $eventbrite_event ) ) {

			$is_exitsing_event = $this->get_event_by_event_id( $eventbrite_event['id'] );
			$formated_args = $this->format_event_args_for_tec( $eventbrite_event );
			if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
				$formated_args['post_status'] = $event_args['event_status'];
			}

			if ( $is_exitsing_event ) {
				// Update event using TEC advanced functions if already exits.
				$options = wpea_get_import_options( 'eventbrite' );
				$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
				if ( 'yes' == $update_events ) {
					return $this->update_eventbrite_event( $is_exitsing_event, $eventbrite_event, $formated_args, $event_args );
				}
			} else {
				return $this->create_eventbrite_event( $eventbrite_event, $formated_args, $event_args );
			}
		}
	}

	/**
	 * Create New eventbrite event.
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @param array $formated_args Formated arguments for eventbrite event.
	 * @param int   $post_id Post id.
	 * @return void
	 */
	public function create_eventbrite_event( $eventbrite_event = array(), $formated_args = array(), $event_args = array() ) {
		// Create event using TEC advanced functions.
		$new_event_id = tribe_create_event( $formated_args );
		if ( $new_event_id ) {
			update_post_meta( $new_event_id, 'wpea_eventbrite_event_id',  $eventbrite_event['id'] );
			update_post_meta( $new_event_id, 'wpea_eventbrite_event_link', esc_url( $eventbrite_event['url'] ) );
			update_post_meta( $new_event_id, 'wpea_eventbrite_response_raw_data', wp_json_encode( $eventbrite_event ) );

			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				wp_set_object_terms( $new_event_id, $wpea_cats, WPEA_TEC_TAXONOMY );
			}

			$event_featured_image  = array_key_exists( 'logo', $eventbrite_event ) ? urldecode( $eventbrite_event['logo']['original']['url'] ) : '';

			if( $event_featured_image != '' ){
				$this->setup_featured_image_to_event( $new_event_id, $event_featured_image );
			}
			do_action( 'wpea_after_create_eventbrite_event', $new_event_id, $formated_args, $eventbrite_event );
			return $new_event_id;

		}else{
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator' );
			return;
		}
	}


	/**
	 * Update eventbrite event.
	 *
	 * @since 1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @param array $formated_args Formated arguments for eventbrite event.
	 * @param int   $post_id Post id.
	 * @return void
	 */
	public function update_eventbrite_event( $event_id, $eventbrite_event, $formated_args = array(), $event_args = array() ) {
		// Update event using TEC advanced functions.
		$update_event_id =  tribe_update_event( $event_id, $formated_args );
		if ( $update_event_id ) {
			update_post_meta( $update_event_id, 'wpea_eventbrite_event_id',  $eventbrite_event['id'] );
			update_post_meta( $update_event_id, 'wpea_eventbrite_event_link', esc_url( $eventbrite_event['url'] ) );
			update_post_meta( $update_event_id, 'wpea_eventbrite_response_raw_data', wp_json_encode( $eventbrite_event ) );

			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? (array) $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				wp_set_object_terms( $update_event_id, $wpea_cats, WPEA_TEC_TAXONOMY );
			}

			$event_featured_image  = array_key_exists( 'logo', $eventbrite_event ) ? urldecode( $eventbrite_event['logo']['original']['url'] ) : '';

			if( $event_featured_image != '' ){
				$this->setup_featured_image_to_event( $update_event_id, $event_featured_image );
			}

			do_action( 'wpea_after_update_eventbrite_event', $update_event_id, $formated_args, $eventbrite_event );
			return $update_event_id;
		}else{
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator' );
			return;
		}
	}


	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function format_event_args_for_tec( $eventbrite_event ) {

		if ( array_key_exists( 'start', $eventbrite_event ) ) {
			$start = str_replace( 'T',' ', str_replace( 'Z', ' ', $eventbrite_event['start']['local'] ) );
			$start_utc = str_replace( 'T',' ', str_replace( 'Z', ' ', $eventbrite_event['start']['utc'] ) );
			$event_start_time = strtotime( $start );
			$event_start_time_utc = strtotime( $start_utc );
		} else {
			$event_start_time = time();
			$event_start_time_utc = time();
		}

		if ( array_key_exists( 'end', $eventbrite_event ) ) {
			$end = str_replace( 'T',' ', str_replace( 'Z', ' ', $eventbrite_event['end']['local'] ) );
			$end_utc = str_replace( 'T',' ', str_replace( 'Z', ' ', $eventbrite_event['end']['utc'] ) );
			$event_end_time = strtotime( $end );
			$event_end_time_utc = strtotime( $end_utc );
		} else {
			$event_end_time = time();
			$event_end_time_utc = time();
		}

		$event_timezone = isset( $eventbrite_event['start']['timezone'] ) ? $eventbrite_event['start']['timezone'] : '';

		$event_args  = array(
			'post_type'             => WPEA_TEC_POSTTYPE,
			'post_title'            => array_key_exists( 'name', $eventbrite_event ) ? sanitize_text_field( $eventbrite_event['name']['text'] ) : '',
			'post_status'           => 'pending',
			'post_content'          => array_key_exists( 'description', $eventbrite_event ) ? $eventbrite_event['description']['html'] : '',
			'EventStartDate'        => date( 'Y-m-d', $event_start_time ),
			'EventStartHour'        => date( 'h', $event_start_time ),
			'EventStartMinute'      => date( 'i', $event_start_time ),
			'EventStartMeridian'    => date( 'a', $event_start_time ),
			'EventEndDate'          => date( 'Y-m-d', $event_end_time ),
			'EventEndHour'          => date( 'h', $event_end_time ),
			'EventEndMinute'        => date( 'i', $event_end_time ),
			'EventEndMeridian'      => date( 'a', $event_end_time ),
			'EventStartDateUTC'     => date( 'Y-m-d H:i:s', $event_start_time_utc ),
			'EventEndDateUTC'       => date( 'Y-m-d H:i:s', $event_end_time_utc ),
			'EventURL'              => array_key_exists( 'url', $eventbrite_event ) ? $eventbrite_event['url'] : '',
			'EventShowMap' 			=> 1,
			'EventShowMapLink'		=> 1,

		);

		if ( array_key_exists( 'organizer_id', $eventbrite_event ) ) {
			$event_args['organizer'] = $this->get_organizer_args( $eventbrite_event );
		}

		if ( array_key_exists( 'venue_id', $eventbrite_event ) ) {
			$event_args['venue'] = $this->get_venue_args( $eventbrite_event );
		}
		return $event_args;
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function get_organizer_args( $eventbrite_event ) {
		if ( ! array_key_exists( 'organizer_id', $eventbrite_event ) ) {
			return null;
		}
		$event_organizer = $eventbrite_event['organizer_id'];
		$existing_organizer = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => WPEA_TEC_ORGANIZER_POSTTYPE,
			'meta_key' => 'wpea_event_organizer_id',
			'meta_value' => $event_organizer,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return array(
				'OrganizerID' => $existing_organizer[0]->ID,
			);
		}

		$get_oraganizer = wp_remote_get( 'https://www.eventbriteapi.com/v3/organizers/' . $event_organizer .'/?token=' . $this->oauth_token, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( ! is_wp_error( $get_oraganizer ) ) {
			$oraganizer = json_decode( $get_oraganizer['body'], true );
			if ( is_array( $oraganizer ) && ! isset( $oraganizer['errors'] ) ) {
				if ( ! empty( $oraganizer ) && array_key_exists( 'id', $oraganizer ) ) {
					$creat_organizer = tribe_create_organizer( array(
						'Organizer' => isset( $oraganizer['name'] ) ? $oraganizer['name'] : '',
						'Website' => isset( $oraganizer['website'] ) ? $oraganizer['website'] : '',
					) );

					if ( $creat_organizer ) {
						update_post_meta( $creat_organizer, 'wpea_event_organizer_id', $event_organizer );
						return array(
							'OrganizerID' => $creat_organizer,
						);
					}
				}
			}
		}
		return null;
	}

	/**
	 * Get venue args for event
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function get_venue_args( $eventbrite_event ) {
		if ( ! array_key_exists( 'venue_id', $eventbrite_event ) ) {
			return null;
		}
		
		$event_venue_id = $eventbrite_event['venue_id'];
		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => WPEA_TEC_VENUE_POSTTYPE,
			'meta_key' => 'wpea_event_venue_id',
			'meta_value' => $event_venue_id,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return array(
				'VenueID' => $existing_venue[0]->ID,
			);
		}

		$get_venue = wp_remote_get( 'https://www.eventbriteapi.com/v3/venues/' . $event_venue_id .'/?token=' . $this->oauth_token, array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( ! is_wp_error( $get_venue ) ) {
			$venue = json_decode( $get_venue['body'], true );
			if ( is_array( $venue ) && ! isset( $venue['errors'] ) ) {
				if ( ! empty( $venue ) && array_key_exists( 'id', $venue ) ) {

					$crate_venue = tribe_create_venue( array(
						'Venue' => ( $venue['name'] ) ? $venue['name'] : '',
						'Address' => ( $venue['address']['localized_address_display'] ) ? $venue['address']['localized_address_display'] : $venue['address']['address_1'],
						'City' => ( $venue['address']['city'] ) ? $venue['address']['city'] : '',
						'State' => ( $venue['address']['region'] ) ? $venue['address']['region'] : '',
						'Country' => ( $venue['address']['country'] ) ? strtoupper( $venue['address']['country'] ) : '',
						'Zip' => ( $venue['address']['postal_code'] ) ? $venue['address']['postal_code'] : '',
						'ShowMap' => true,
						'ShowMapLink' => true,
					) );

					if ( $crate_venue ) {
						update_post_meta( $crate_venue, 'wpea_event_venue_id', $event_venue_id );
						return array(
							'VenueID' => $crate_venue,
						);
					}
				}
			}
		}
		return null;
	}

	/**
	 * Setup Featured image to events
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @param int $image_url Image URL
	 * @return void
	 */
	public function setup_featured_image_to_event( $event_id, $image_url = '' ) {
		if ( $image_url == '' ) {
			return;
		}
		$event = get_post( $event_id );
		if( Empty ( $event ) ){
			return;
		}
		$image = explode( '?s=', $image_url );
		$image_url = str_replace('https://img.evbuc.com/', '', $image[0] );
		// Add Featured Image to Post
		$image_name       = $event->post_name . '_image.png';
		$upload_dir       = wp_upload_dir(); // Set upload folder
		$image_data       = file_get_contents( $image_url ); // Get image data
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name ); // Generate unique name
		$filename         = basename( $unique_file_name ); // Create image file name

		// Check folder permission and define file location
		if( wp_mkdir_p( $upload_dir['path'] ) ) {
		    $file = $upload_dir['path'] . '/' . $filename;
		} else {
		    $file = $upload_dir['basedir'] . '/' . $filename;
		}

		// Create the image  file on the server
		file_put_contents( $file, $image_data );

		// Check image file type
		$wp_filetype = wp_check_filetype( $filename, null );

		// Set attachment data
		$attachment = array(
		    'post_mime_type' => $wp_filetype['type'],
		    'post_title'     => sanitize_file_name( $filename ),
		    'post_content'   => '',
		    'post_status'    => 'inherit'
		);

		// Create the attachment
		$attach_id = wp_insert_attachment( $attachment, $file, $event_id );

		// Include image.php
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		// Define attachment metadata
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );

		// Assign metadata to attachment
		wp_update_attachment_metadata( $attach_id, $attach_data );

		// And finally assign featured image to post
		set_post_thumbnail( $event_id, $attach_id );

	}

	/**
	 * Check for Existing Eventbrite Event
	 *
	 * @since    1.0.0
	 * @param int $eventbrite_event_id Eventbrite event id.
	 * @return /boolean
	 */
	public function get_event_by_event_id( $eventbrite_event_id ) {
		$event_args = array(
			'post_type' => WPEA_TEC_POSTTYPE,
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'meta_key'   => 'wpea_eventbrite_event_id',
			'meta_value' => $eventbrite_event_id,
		);

		$events = new WP_Query( $event_args );
		if ( $events->have_posts() ) {
			while ( $events->have_posts() ) {
				$events->the_post();
				return get_the_ID();
			}
		}
		wp_reset_postdata();
		return false;
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
}
