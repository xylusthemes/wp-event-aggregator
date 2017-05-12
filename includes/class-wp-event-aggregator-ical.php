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

		if ( ! empty( $ical_event_object ) && $ical_event_object->uid != '' ) {

			$is_exitsing_event = $this->get_event_by_event_id( $ical_event_object->uid );
			$formated_args = $this->format_event_args_for_tec( $ical_event_object );

			if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
				$formated_args['post_status'] = $event_args['event_status'];
			}

			if ( $is_exitsing_event ) {
				// Update event using TEC advanced functions if already exits.
				$options = wpea_get_import_options( 'ical' );
				$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
				if ( 'yes' == $update_events ) {
					return $this->update_ical_event( $is_exitsing_event, $ical_event_object, $formated_args, $event_args );
				}
			} else {
				return $this->create_ical_event( $ical_event_object, $formated_args, $event_args );
			}
		}
	}

	/**
	 * Create New iCal event.
	 *
	 * @since    1.0.0
	 * @param array $ical_event Facebook event.
	 * @param array $formated_args Formated arguments for facebook event.
	 * @param array $event_args
	 * @return int
	 */
	public function create_ical_event( $ical_event = array(), $formated_args = array(), $event_args = array() ) {
		// Create event using TEC advanced functions.
		$new_event_id = tribe_create_event( $formated_args );
		if ( $new_event_id ) {
			update_post_meta( $new_event_id, 'wpea_ical_event_uid',  $ical_event->uid );
			update_post_meta( $new_event_id, 'wpea_ical_response_raw_data', wp_json_encode( $ical_event ) );

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

			do_action( 'wpea_after_create_ical_event', $new_event_id, $formated_args, $ical_event );
			return $new_event_id;

		}else{
			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator' );
			return;
		}
	}


	/**
	 * Update facebook event.
	 *
	 * @since 1.0.0
	 * @param int   $event_id existing ical event.
	 * @param array $ical_event iCal event.
	 * @param array $formated_args Formated arguments for ical event.
	 * @param array $event_args User submited data at a time of schedule event
	 * @return int   $post_id Post id.
	 */
	public function update_ical_event( $event_id, $ical_event, $formated_args = array(), $event_args = array() ) {
		// Update event using TEC advanced functions.
		$update_event_id =  tribe_update_event( $event_id, $formated_args );
		if ( $update_event_id ) {
			update_post_meta( $update_event_id, 'wpea_facebook_event_id',  $ical_event->uid );
			update_post_meta( $update_event_id, 'wpea_facebook_response_raw_data', wp_json_encode( $ical_event ) );

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

			do_action( 'wpea_after_update_ical_event', $update_event_id, $formated_args, $ical_event );
			return $update_event_id;

		}else{

			$errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator' );
			return;
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
	 * @param array $ical_event Facebook event.
	 * @return array
	 */
	public function format_event_args_for_tec( $ical_event ) {

		if( !isset( $ical_event->uid ) || $ical_event->uid == '' ){
			return;
		}

		$facebook_id = $ical_event->uid;
		$post_title = isset( $ical_event->summary ) ? $ical_event->summary : '';
		$post_description = isset( $ical_event->description ) ? $ical_event->description : '';
		
		$start_time = isset( $ical_event->dtstart_tz ) ? strtotime( convert_datetime_to_db_datetime( $ical_event->dtstart_tz ) ) : date( 'Y-m-d H:i:s');
		$end_time = isset( $ical_event->dtend_tz ) ? strtotime( convert_datetime_to_db_datetime( $ical_event->dtend_tz ) ) : $start_time;
		$website = isset( $ical_event->url ) ? esc_url( $ical_event->url ) : '';
		$timezone = $this->get_utc_offset( $ical_event->dtstart_tz );

		$event_args  = array(
			'post_type'             => WPEA_TEC_POSTTYPE,
			'post_title'            => $post_title,
			'post_status'           => 'pending',
			'post_content'          => $post_description,
			'EventStartDate'        => date( 'Y-m-d', $start_time ),
			'EventStartHour'        => date( 'h', $start_time ),
			'EventStartMinute'      => date( 'i', $start_time ),
			'EventStartMeridian'    => date( 'a', $start_time ),
			'EventEndDate'          => date( 'Y-m-d', $end_time ),
			'EventEndHour'          => date( 'h', $end_time ),
			'EventEndMinute'        => date( 'i', $end_time ),
			'EventEndMeridian'      => date( 'a', $end_time ),
			'EventTimezone' 		=> $timezone,
			'EventURL'              => $website,
			'EventShowMap' 			=> 1,
			'EventShowMapLink'		=> 1,
		);

		if ( isset( $ical_event->organizer ) && !empty( $ical_event->organizer ) ) {
			$event_args['organizer'] = $this->get_organizer_args( $ical_event );
		}

		if ( isset( $ical_event->location ) && !empty( $ical_event->location ) ) {
			$event_args['venue'] = $this->get_venue_args( $ical_event );
		}
		return $event_args;
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since  1.0.0
	 * @param  array $ical_event Facebook event.
	 * @return array
	 */
	public function get_organizer_args( $ical_event ) {

		if ( isset( $ical_event->organizer ) && !empty( $ical_event->organizer ) ) {
			return null;
		}


		$params = wp_parse_args( str_replace( ';', '&', $ical_event->organizer ) );
		$oraganizer_data = array();
		foreach ( $params as $k => $param ) {
			if ( $k == 'CN' ) {
				$oraganizer = explode( ':mailto:', $param);
				$oraganizer_data['Organizer'] = preg_replace( '/^"(.*)"$/', '\1', $oraganizer[0] );
				$oraganizer_data['Email'] = preg_replace( '/^"(.*)"$/', '\1', trim( $oraganizer[1]) );
			} else {
				if ( ! empty( $param ) ) {
					$oraganizer_data[ $k ] = $param;
				}
			}
		}

		$existing_organizer = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => WPEA_TEC_ORGANIZER_POSTTYPE,
			'post_title' => $oraganizer_data['Organizer'],
			'meta_key' => 'is_ical_organizer',
			'meta_value' => 'yes',
		) );

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return array(
				'OrganizerID' => $existing_organizer[0]->ID,
			);
		}

		$create_organizer = tribe_create_organizer(  $oraganizer_data );

		if ( $create_organizer ) {
			update_post_meta( $create_organizer, 'is_ical_organizer', 'yes' );
			return array(
				'OrganizerID' => $create_organizer,
			);
		}
		return null;
	}

	/**
	 * Get venue args for event
	 *
	 * @since    1.0.0
	 * @param array $ical_event Facebook event.
	 * @return array
	 */
	public function get_venue_args( $ical_event ) {
		
		if ( !isset( $ical_event->location ) || empty( $ical_event->location ) ) {
			return null;
		}
		
		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_title' => stripslashes( $ical_event->location ),
			'post_type' => WPEA_TEC_VENUE_POSTTYPE,
			'meta_key' => 'is_ical_venue',
			'meta_value' => 'yes',
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return array(
				'VenueID' => $existing_venue[0]->ID,
			);
		}

		$crate_venue = tribe_create_venue( array(
			'Venue' 	  => isset( $ical_event->location ) ? stripslashes( $ical_event->location ) : '',
			'Address'     => isset( $ical_event->location ) ? stripslashes( $ical_event->location ) : '',
			'ShowMap' 	  => true,
			'ShowMapLink' => true,
		) );

		if ( $crate_venue ) {
			update_post_meta( $crate_venue, 'is_ical_venue', 'yes' );
			return array(
				'VenueID' => $crate_venue,
			);
		}
		return null;
	}


	/**
	 * Check for Existing Facebook Event
	 *
	 * @since    1.0.0
	 * @param int $facebook_event_id facebook event id.
	 * @return /boolean
	 */
	public function get_event_by_event_id( $ical_event_uid ) {
		$event_args = array(
			'post_type' => WPEA_TEC_POSTTYPE,
			'post_status' => array( 'pending', 'draft', 'publish' ),
			'posts_per_page' => -1,
			'meta_key'   => 'wpea_ical_event_uid',
			'meta_value' => $ical_event_uid,
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
