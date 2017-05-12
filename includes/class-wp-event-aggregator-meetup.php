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

		global $errors;
		$imported_events = array();
		$meetup_url = isset( $event_data['meetup_url'] ) ? $event_data['meetup_url'] : '';
		
		if( $this->api_key == '' ){
			$errors[] = __( 'Please insert "Meetup API key" in settings.', 'wp-event-aggregator');
			return;
		}

		$meetup_group_id = $this->fetch_group_slug_from_url( $meetup_url );
		if( $meetup_group_id == '' ){ return; }

		$meetup_api_url = 'https://api.meetup.com/' . $meetup_group_id . '/events?key=' . $this->api_key;
	    $meetup_response = wp_remote_get( $meetup_api_url , array( 'headers' => array( 'Content-Type' => 'application/json' ) ) );

		if ( is_wp_error( $meetup_response ) ) {
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
			return;
		}

		$meetup_events = json_decode( $meetup_response['body'], true );
		if ( is_array( $meetup_events ) && ! isset( $meetup_events['error'] ) ) {

			if( !empty( $meetup_events ) ){
				foreach ($meetup_events as $meetup_event) {
					$imported_events[] = $this->save_meetup_event( $meetup_event, $event_data );
				}	
			}
			return $imported_events;

		}else{
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator');
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
	public function save_meetup_event( $meetup_event = array(), $event_data = array() ) {

		if ( ! empty( $meetup_event ) && is_array( $meetup_event ) && array_key_exists( 'id', $meetup_event ) ) {

			$is_exitsing_event = $this->get_event_by_event_id( $meetup_event['id'] );
			$formated_args = $this->format_event_args_for_tec( $meetup_event, $event_data );

			if ( $is_exitsing_event ) {
				// Update event using TEC advanced functions if already exits.
				$options = wpea_get_import_options( 'meetup' );
				$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';

				if ( 'yes' == $update_events ) {
					return $this->update_meetup_event( $is_exitsing_event, $meetup_event, $formated_args, $event_data );
				}

			} else {
				return $this->create_meetup_event( $meetup_event, $formated_args, $event_data );
			}
		}
	}

	/**
	 * Create New meetup event.
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @param array $formated_args Formated arguments for meetup event.
	 * @param int   $post_id Post id.
	 * @return void
	 */
	public function create_meetup_event( $meetup_event = array(), $formated_args = array(), $event_data = array()  ) {
		// Create event using TEC advanced functions.
		if( empty( $formated_args ) ){ return; }
		$new_event_id = tribe_create_event( $formated_args );
		if ( $new_event_id ) {
			update_post_meta( $new_event_id, 'wpea_meetup_event_id', absint( $meetup_event['id'] ) );
			update_post_meta( $new_event_id, 'wpea_meetup_event_link', esc_url( $meetup_event['link'] ) );
			update_post_meta( $new_event_id, 'wpea_meetup_response_raw_data', wp_json_encode( $meetup_event ) );

			// Asign event category.
			$event_cats = isset( $event_data['event_cats'] ) ? $event_data['event_cats'] : array();
			if( !empty( $event_cats ) ){
				$cat_ids = array();
				if ( ! empty( $event_cats ) ) {
					foreach ( $event_cats as $event_cat ) {
						$cat_ids[] = (int)$event_cat;
					}
				}
				if ( ! empty( $cat_ids ) ) {
					wp_set_object_terms( $new_event_id, $cat_ids, WPEA_TEC_TAXONOMY );
				}
			}
			do_action( 'wpea_after_create_meetup_event', $new_event_id, $formated_args, $meetup_event );
			return $new_event_id;
		}else{
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator' );
			return;
		}
	}

	/**
	 * Update meetup event.
	 *
	 * @since 1.0.0
	 * @param int   $event_id Exsting event ID.
	 * @param array $meetup_event Meetup event.
	 * @param array $formated_args Formated arguments for meetup event.
	 * @param array $event_args import data.
	 * @return void
	 */
	public function update_meetup_event( $event_id, $meetup_event, $formated_args = array(), $event_args = array() ) {
		// Update event using TEC advanced functions.
		$update_event_id =  tribe_update_event( $event_id, $formated_args );
		if ( $update_event_id ) {
			update_post_meta( $update_event_id, 'wpea_meetup_event_id', absint( $meetup_event['id'] ) );
			update_post_meta( $update_event_id, 'wpea_meetup_event_link', esc_url( $meetup_event['link'] ) );
			update_post_meta( $update_event_id, 'wpea_meetup_response_raw_data', wp_json_encode( $meetup_event ) );

			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				wp_set_object_terms( $update_event_id, $wpea_cats, WPEA_TEC_TAXONOMY );
			}

			do_action( 'wpea_after_update_meetup_event', $update_event_id, $formated_args, $meetup_event );
			return $update_event_id;
		}else{
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator' );
			return;
		}
	}

	/**
	 * Fetch group slug from group url.
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function format_event_args_for_tec( $meetup_event, $event_data = array() ) {

		if ( array_key_exists( 'time', $meetup_event ) ) {
			$event_start_time_utc = floor( $meetup_event['time'] / 1000 );
		} else {
			$event_start_time_utc = time();
		}

		$event_duration = array_key_exists( 'duration', $meetup_event ) ? $meetup_event['duration'] : 0;
		$event_duration = absint( floor( $event_duration / 1000 ) ); // convert to seconds.
		$event_end_time_utc = absint( $event_start_time_utc + $event_duration );

		$utc_offset = array_key_exists( 'utc_offset', $meetup_event ) ? $meetup_event['utc_offset'] : 0;
		$utc_offset = floor( $utc_offset / 1000 );
		$event_start_time = absint( $event_start_time_utc + $utc_offset );
		$event_end_time = absint( $event_end_time_utc + $utc_offset );
		
		$default_status = isset( $event_data['event_status']) ? $event_data['event_status'] : 'pending';
		$post_type = 'tribe_events';

		if( defined( 'WPEA_TEC_POSTTYPE' ) ){
			$post_type = WPEA_TEC_POSTTYPE;			
		}

		$event_args  = array(
			'post_type'             => $post_type,
			'post_title'            => array_key_exists( 'name', $meetup_event ) ? sanitize_text_field( $meetup_event['name'] ) : '',
			'post_status'           => $default_status,
			'post_content'          => array_key_exists( 'description', $meetup_event ) ? $meetup_event['description'] : '',
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
			'EventURL'              => array_key_exists( 'link', $meetup_event ) ? $meetup_event['link'] : '',
			'EventShowMap' 			=> 1,
			'EventShowMapLink'		=> 1,
		);

		if ( array_key_exists( 'group', $meetup_event ) ) {
			$event_args['organizer'] = $this->get_organizer_args( $meetup_event );
		}

		if ( array_key_exists( 'venue', $meetup_event ) ) {
			$event_args['venue'] = $this->get_venue_args( $meetup_event );
		}

		return $event_args;
	}

	/**
	 * Get organizer args for event
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function get_organizer_args( $meetup_event ) {
		if ( ! array_key_exists( 'group', $meetup_event ) ) {
			return null;
		}
		$event_organizer = $meetup_event['group'];
		$post_type = 'tribe_organizer';
		if ( class_exists( 'Tribe__Events__Organizer' ) ) {
			$post_type = Tribe__Events__Organizer::POSTTYPE;
		}
		$existing_organizer = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $post_type,
			'meta_key' => 'wpea_event_organizer_id',
			'meta_value' => $event_organizer['id'],
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return array(
				'OrganizerID' => $existing_organizer[0]->ID,
			);
		}

		$creat_organizer = tribe_create_organizer( array(
			'Organizer' => isset( $event_organizer['name'] ) ? $event_organizer['name'] : '',
		) );

		if ( $creat_organizer ) {
			update_post_meta( $creat_organizer, 'wpea_event_organizer_id', $event_organizer['id'] );
			return array(
				'OrganizerID' => $creat_organizer,
			);
		}

		return null;
	}

	/**
	 * Get venue args for event
	 *
	 * @since    1.0.0
	 * @param array $meetup_event Meetup event.
	 * @return array
	 */
	public function get_venue_args( $meetup_event ) {
		if ( ! array_key_exists( 'venue', $meetup_event ) ) {
			return null;
		}
		$event_venue = $meetup_event['venue'];
		$post_type = 'tribe_venue';
		if ( class_exists( 'Tribe__Events__Venue' ) ) {
			$post_type = Tribe__Events__Venue::POSTTYPE;
		}

		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $post_type,
			'meta_key' => 'wpea_event_venue_id',
			'meta_value' => $event_venue['id'],
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return array(
				'VenueID' => $existing_venue[0]->ID,
			);
		}

		$crate_venue = tribe_create_venue( array(
			'Venue' => $event_venue['name'],
			'Address' => isset( $event_venue['address_1'] ) ? $event_venue['address_1'] : '',
			'City' => isset( $event_venue['city'] ) ? $event_venue['city'] : '',
			'State' => isset( $event_venue['state'] ) ? $event_venue['state'] : '',
			'Country' => isset( $event_venue['country'] ) ? strtoupper( $event_venue['country'] ) : '',
			'Zip' => isset( $event_venue['zip'] ) ? $event_venue['zip'] : '',
			'Phone' => isset( $event_venue['phone'] ) ? $event_venue['phone'] : '',
		) );

		if ( $crate_venue ) {
			update_post_meta( $crate_venue, 'wpea_event_venue_id', $event_venue['id'] );
			return array(
				'VenueID' => $crate_venue,
			);
		}

		return null;
	}

	/**
	 * Check for Existing Event
	 *
	 * @since    1.0.0
	 * @param int $meetup_event_id meetup event id.
	 * @return /boolean
	 */
	public function get_event_by_event_id( $meetup_event_id ) {
		$event_args = array(
			'post_type' => WPEA_TEC_POSTTYPE,
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'meta_key'   => 'wpea_meetup_event_id',
			'meta_value' => $meetup_event_id,
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
	 * @param array $meetup_url Meetup event.
	 * @return array
	 */
	public function get_meetup_group_name_by_url( $meetup_url ) {
		
		if( !$meetup_url || $meetup_url == '' ){
			return;
		}
		
		if( $this->api_key == '' ){
			$errors[] = __( 'Please insert "Meetup API key" in settings.', 'wp-event-aggregator');
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
