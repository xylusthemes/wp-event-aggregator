<?php
/**
 * Class for Import Events into The Events Calendar
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_TEC {

	// The Events Calendar Event Taxonomy
	protected $taxonomy;

	// The Events Calendar Event Posttype
	protected $event_posttype;

	// The Events Calendar Venue Posttype
	protected $venue_posttype;

	// The Events Calendar Oraganizer Posttype
	protected $oraganizer_posttype;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		$this->taxonomy = 'tribe_events_cat';
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			$this->event_posttype = Tribe__Events__Main::POSTTYPE;
		}else{
			$this->event_posttype = 'tribe_events';
		}

		if ( class_exists( 'Tribe__Events__Organizer' ) ) {
			$this->oraganizer_posttype = Tribe__Events__Organizer::POSTTYPE;
		}else{
			$this->oraganizer_posttype = 'tribe_organizer';
		}

		if ( class_exists( 'Tribe__Events__Venue' ) ) {
			$this->venue_posttype = Tribe__Events__Venue::POSTTYPE;
		}else{
			$this->venue_posttype = 'tribe_venue';
		}

	}
	/**
	 * Get Posttype and Taxonomy Functions
	 *
	 * @return string
	 */
	public function get_event_posttype(){
		return $this->event_posttype;
	}	
	public function get_oraganizer_posttype(){
		return $this->oraganizer_posttype;
	}
	public function get_venue_posttype(){
		return $this->venue_posttype;
	}
	public function get_taxonomy(){
		return $this->taxonomy;
	}

	/**
	 * import event into TEC
	 *
	 * @since    1.0.0
	 * @param  array $centralize event array.
	 * @return array
	 */
	public function import_event( $centralize_array, $event_args ){
		global $importevents;

		$is_exitsing_event = $importevents->common->get_event_by_event_id( $this->event_posttype, $centralize_array );
		$formated_args = $this->format_event_args_for_tec( $centralize_array );
		if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
			$formated_args['post_status'] = $event_args['event_status'];
		}

		if ( $is_exitsing_event && is_numeric( $is_exitsing_event ) && $is_exitsing_event > 0 ) {
			$options = wpea_get_import_options( $centralize_array['origin'] );
			$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
			if ( 'yes' == $update_events ) {
				return $this->update_event( $is_exitsing_event, $centralize_array, $formated_args, $event_args );
			}else{
				return array( 'status'=> 'skipped' );
			}
		} else {
			return $this->create_event( $centralize_array, $formated_args, $event_args );
		}

	}

	/**
	 * Create New TEC event.
	 *
	 * @since    1.0.0
	 * @param array $centralize_array Centralize Array event.
	 * @param array $formated_args Formated arguments for eventbrite event.
	 * @param int   $post_id Post id.
	 * @return void
	 */
	public function create_event( $centralize_array = array(), $formated_args = array(), $event_args = array() ) {
		// Create event using TEC advanced functions.
		global $importevents;
		$new_event_id = tribe_create_event( $formated_args );
		if ( $new_event_id ) {
			update_post_meta( $new_event_id, 'wpea_event_id',  $centralize_array['ID'] );
			update_post_meta( $new_event_id, 'wpea_event_origin',  $event_args['import_origin'] );
			update_post_meta( $new_event_id, 'wpea_event_link', esc_url( $centralize_array['url'] ) );
			update_post_meta( $new_event_id, '_wpea_starttime_str', $centralize_array['starttime_local'] );
            update_post_meta( $new_event_id, '_wpea_endtime_str', $centralize_array['endtime_local'] );
			
			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				wp_set_object_terms( $new_event_id, $wpea_cats, $this->taxonomy );
			}

			$event_featured_image  = $centralize_array['image_url'];
			if( $event_featured_image != '' ){
				$importevents->common->setup_featured_image_to_event( $new_event_id, $event_featured_image );
			}

			do_action( 'wpea_after_create_tec_'.$centralize_array["origin"].'_event', $new_event_id, $formated_args, $centralize_array );
			return array(
				'status' => 'created',
				'id' 	 => $new_event_id
			);

		}else{
			$wpea_errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator' );
			return;
		}
	}


	/**
	 * Update eventbrite event.
	 *
	 * @since 1.0.0
	 * @param array $centralize_array Eventbrite event.
	 * @param array $formated_args Formated arguments for eventbrite event.
	 * @param int   $post_id Post id.
	 * @return void
	 */
	public function update_event( $event_id, $centralize_array, $formated_args = array(), $event_args = array() ) {
		// Update event using TEC advanced functions.
		global $importevents;

		$update_event_id =  tribe_update_event( $event_id, $formated_args );
		if ( $update_event_id ) {
			update_post_meta( $update_event_id, 'wpea_event_id',  $centralize_array['ID'] );
			update_post_meta( $update_event_id, 'wpea_event_origin',  $event_args['import_origin'] );
			update_post_meta( $update_event_id, 'wpea_event_link', esc_url( $centralize_array['url'] ) );
			update_post_meta( $update_event_id, '_wpea_starttime_str', $centralize_array['starttime_local'] );
            update_post_meta( $update_event_id, '_wpea_endtime_str', $centralize_array['endtime_local'] );
			
			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? (array) $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				wp_set_object_terms( $update_event_id, $wpea_cats, $this->taxonomy );
			}

			$event_featured_image  = $centralize_array['image_url'];
			if( $event_featured_image != '' ){
				$importevents->common->setup_featured_image_to_event( $update_event_id, $event_featured_image );
			}else{
				delete_post_thumbnail( $update_event_id );
			}

			do_action( 'wpea_after_update_tec_'.$centralize_array["origin"].'_event', $update_event_id, $formated_args, $centralize_array );
			return array(
				'status' => 'updated',
				'id' 	 => $update_event_id
			);
		}else{
			$wpea_errors[] = __( 'Something went wrong, please try again.', 'wp-event-aggregator' );
			return;
		}
	}


	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $centralize_array Eventbrite event.
	 * @return array
	 */
	public function format_event_args_for_tec( $centralize_array ) {

		if( empty( $centralize_array ) ){
			return;
		}
		$start_time = $centralize_array['starttime_local'];
		$end_time = $centralize_array['endtime_local'];
		$event_args  = array(
			'post_type'             => $this->event_posttype,
			'post_title'            => $centralize_array['name'],
			'post_status'           => 'pending',
			'post_content'          => $centralize_array['description'],
			'EventStartDate'        => date( 'Y-m-d', $start_time ),
			'EventStartHour'        => date( 'h', $start_time ),
			'EventStartMinute'      => date( 'i', $start_time ),
			'EventStartMeridian'    => date( 'a', $start_time ),
			'EventEndDate'          => date( 'Y-m-d', $end_time ),
			'EventEndHour'          => date( 'h', $end_time ),
			'EventEndMinute'        => date( 'i', $end_time ),
			'EventEndMeridian'      => date( 'a', $end_time ),
			'EventStartDateUTC'     => !empty( $centralize_array['startime_utc'] ) ? date( 'Y-m-d H:i:s', $centralize_array['startime_utc'] ) : '',
			'EventEndDateUTC'       => !empty( $centralize_array['endtime_utc'] ) ? date( 'Y-m-d H:i:s', $centralize_array['endtime_utc'] ) : '',
			'EventURL'              => $centralize_array['url'],
			'EventShowMap' 			=> 1,
			'EventShowMapLink'		=> 1,
		);
		if( isset( $centralize_array['is_all_day'] ) && true === $centralize_array['is_all_day'] ){
            $event_args['EventAllDay'] = 'yes';
        }

		if ( array_key_exists( 'organizer', $centralize_array ) ) {
			$event_args['organizer'] = $this->get_organizer_args( $centralize_array['organizer'] );
		}

		if ( array_key_exists( 'location', $centralize_array ) ) {
			$event_args['venue'] = $this->get_venue_args( $centralize_array['location'] );
		}
		return $event_args;
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since    1.0.0
	 * @param array $centralize_org_array Location array.
	 * @return array
	 */
	public function get_organizer_args( $centralize_org_array ) {

		if ( !isset( $centralize_org_array['ID'] ) ) {
			return null;
		}
		$existing_organizer = $this->get_organizer_by_id( $centralize_org_array['ID'] );
		if ( $existing_organizer && is_numeric( $existing_organizer ) && $existing_organizer > 0 ) {
			return array(
				'OrganizerID' => $existing_organizer,
			);
		}

		$create_organizer = tribe_create_organizer( array(
			'Organizer' => isset( $centralize_org_array['name'] ) ? $centralize_org_array['name'] : '',
			'Phone' => isset( $centralize_org_array['phone'] ) ? $centralize_org_array['phone'] : '',
			'Email' => isset( $centralize_org_array['email'] ) ? $centralize_org_array['email'] : '',
			'Website' => isset( $centralize_org_array['url'] ) ? $centralize_org_array['url'] : '',
		) );

		if ( $create_organizer ) {
			update_post_meta( $create_organizer, 'wpea_event_organizer_id', $centralize_org_array['ID'] );
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
	 * @param array $venue venue array.
	 * @return array
	 */
	public function get_venue_args( $venue ) {
		global $importevents; 

		if ( !isset( $venue['ID'] ) ) {
			return null;
		}
		$existing_venue = $this->get_venue_by_id( $venue['ID'] );

		if ( $existing_venue && is_numeric( $existing_venue ) && $existing_venue > 0 ) {
			return array(
				'VenueID' => $existing_venue,
			);
		}

		$country = isset( $venue['country'] ) ? $venue['country'] : '';
		if( strlen( $country ) > 2 && $country != '' ){
			$country = $importevents->common->wpea_get_country_code( $country );
		}
		$create_venue = tribe_create_venue( array(
			'Venue'   => isset( $venue['name'] ) ? $venue['name'] : '',
			'Address' => isset( $venue['full_address'] ) ? $venue['full_address'] : $venue['address_1'],
			'City'    => isset( $venue['city'] ) ? $venue['city'] : '',
			'State'   => isset( $venue['state'] ) ? $venue['state'] : '',
			'Country' => $country,
			'Zip'     => isset( $venue['zip'] ) ? $venue['zip'] : '',
			'Phone'   => isset( $venue['phone'] ) ? $venue['phone'] : '',
			'ShowMap' => true,
			'ShowMapLink' => true,
		) );

		if ( $create_venue ) {
			update_post_meta( $create_venue, 'wpea_event_venue_id', $venue['ID'] );
			return array(
				'VenueID' => $create_venue,
			);
		}
		return false;		
	}

	/**
	 * Check for Existing TEC Organizer
	 *
	 * @since    1.0.0
	 * @param int $organizer_id Organizer id.
	 * @return int/boolean
	 */
	public function get_organizer_by_id( $organizer_id ) {
		$existing_organizer = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $this->oraganizer_posttype,
			'meta_key' => 'wpea_event_organizer_id',
			'meta_value' => $organizer_id,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return $existing_organizer[0]->ID;
		}
		return false;
	}

	/**
	 * Check for Existing TEC Venue
	 *
	 * @since    1.0.0
	 * @param int $venue_id Venue id.
	 * @return int/boolean
	 */
	public function get_venue_by_id( $venue_id ) {
		$existing_organizer = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $this->venue_posttype,
			'meta_key' => 'wpea_event_venue_id',
			'meta_value' => $venue_id,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return $existing_organizer[0]->ID;
		}
		return false;
	}

}
