<?php
/**
 * Class for Import Events into Builtin Events.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_WPEA {

	// Event Taxonomy
	protected $taxonomy;

	// Event Posttype
	protected $event_posttype;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		$this->event_posttype = 'wp_events';
		$this->taxonomy = 'event_category';

	}


	/**
	 * Get Posttype and Taxonomy Functions
	 *
	 * @return string
	 */
	public function get_event_posttype(){
		return $this->event_posttype;
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
		global $wpdb, $importevents;

		if( empty( $centralize_array ) || !isset( $centralize_array['ID'] ) ){
			return false;
		}

		$is_exitsing_event = $importevents->common->get_event_by_event_id( $this->event_posttype, $centralize_array );
		
		if ( $is_exitsing_event ) {
			// Update event or not?
			$options = wpea_get_import_options( $centralize_array['origin'] );
			$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
			$wpea_options = get_option( WPEA_OPTIONS );
			$skip_trash = isset( $wpea_options['wpea']['skip_trash'] ) ? $wpea_options['wpea']['skip_trash'] : 'no';
			$post_status   = get_post_status( $is_exitsing_event );
			if ( 'trash' == $post_status && $skip_trash == 'yes' ) {
				return array(
					'status' => 'skip_trash',
					'id'     => $is_exitsing_event,
				);
			}
			if ( 'yes' != $update_events ) {
				return array(
					'status'=> 'skipped',
					'id' 	=> $is_exitsing_event
				);
			}
		}

		$origin_event_id = $centralize_array['ID'];
		$post_title = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$post_description = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$start_time = $centralize_array['starttime_local'];
		$end_time = $centralize_array['endtime_local'];
		$ticket_uri = $centralize_array['url'];
		$online_event = !empty( $centralize_array['online_event'] ) ? $centralize_array['online_event'] : false ;
		$timezone     = isset( $centralize_array['timezone'] ) ? sanitize_text_field(  $centralize_array['timezone'] ) : '';
		$timezone_name = isset( $centralize_array['timezone_name'] ) ? sanitize_text_field(  $centralize_array['timezone_name'] ) : '';

		$emeventdata = array(
			'post_title'  => $post_title,
			'post_content' => $post_description,
			'post_type'   => $this->event_posttype,
			'post_status' => 'pending',
			'post_author'  => isset( $event_args['event_author'] ) ? $event_args['event_author'] : get_current_user_id()
		);
		if ( $is_exitsing_event ) {
			$emeventdata['ID'] = $is_exitsing_event;
		}
		if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
			$emeventdata['post_status'] = $event_args['event_status'];
		}

		if ( $is_exitsing_event && ! $importevents->common->wpea_is_updatable('status') ) {
			$emeventdata['post_status'] = get_post_status( $is_exitsing_event );
			$event_args['event_status'] = get_post_status( $is_exitsing_event );
		}

		$inserted_event_id = wp_insert_post( $emeventdata, true );

		if ( ! is_wp_error( $inserted_event_id ) ) {
			$inserted_event = get_post( $inserted_event_id );
			if ( empty( $inserted_event ) ) { return false;}

			//Event ID
			update_post_meta( $inserted_event_id, 'wpea_event_id', $centralize_array['ID'] );

			$wpea_options       = get_option( WPEA_OPTIONS );
			$is_import_ical_cat = isset( $wpea_options['ical']['ical_cat_import'] ) ? $wpea_options['ical']['ical_cat_import'] : 'no';
			$ical_categories    = isset( $centralize_array['ical_categories'] ) ? $centralize_array['ical_categories'] : '';
			if( !empty( $ical_categories ) && $is_import_ical_cat == 'yes' ){
				$ical_cats      = explode( ',', $ical_categories );
				$event_cat_ids  = $importevents->common->wepa_create_update_ical_categories( $ical_cats, $this->taxonomy );
				$event_args['event_cats']  = array_merge( $event_args['event_cats'], $event_cat_ids );
			}

			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				if (!($is_exitsing_event && ! $importevents->common->wpea_is_updatable('category') )) {
					$append = apply_filters('wpea_taxonomy_terms_append', false, $wpea_cats, $this->taxonomy, $centralize_array['origin'] );
					wp_set_object_terms( $inserted_event_id, $wpea_cats, $this->taxonomy, $append );
				}
			}

			// Assign Featured images
			$event_image = $centralize_array['image_url'];
			if ( ! empty( $event_image ) ) {
				$importevents->common->setup_featured_image_to_event( $inserted_event_id, $event_image );
			}else{
				$default_thumb  = isset( $wpea_options['wpea']['wpea_event_default_thumbnail'] ) ? $wpea_options['wpea']['wpea_event_default_thumbnail'] : '';
				if( !empty( $default_thumb ) ){
					set_post_thumbnail( $inserted_event_id, $default_thumb );
				}else{
					if ( $is_exitsing_event ) {
						delete_post_thumbnail( $inserted_event_id );
					}
				}
			}
				

			//////////////////////////////////////////////
			// Event Date & time Details
			$event_start_date     = gmdate( 'Y-m-d', $start_time );
			$event_end_date       = gmdate( 'Y-m-d', $end_time );
			$event_start_hour     = gmdate( 'h', $start_time );
			$event_start_minute   = gmdate( 'i', $start_time );
			$event_start_meridian = gmdate( 'a', $start_time );
			$event_end_hour       = gmdate( 'h', $end_time );
			$event_end_minute     = gmdate( 'i', $end_time );
			$event_end_meridian   = gmdate( 'a', $end_time );

			if( $online_event == true ){
				$centralize_array['location']['name'] = 'Online Event';
			}

			// Venue Deatails
			$address_1 = isset( $venue_array['address_1'] ) ? $venue_array['address_1'] : '';
			$venue_array = isset( $centralize_array['location'] ) ? $centralize_array['location'] : array();
			$venue_name    = isset( $venue_array['name'] ) ? sanitize_text_field( $venue_array['name'] ) : '';
			$venue_address = isset( $venue_array['full_address'] ) ? sanitize_text_field( $venue_array['full_address'] ) : sanitize_text_field( $address_1 );
			$venue_city    = isset( $venue_array['city'] ) ? sanitize_text_field( $venue_array['city'] ) : '';
			$venue_state   = isset( $venue_array['state'] ) ? sanitize_text_field( $venue_array['state'] ) : '';
			$venue_country = isset( $venue_array['country'] ) ? sanitize_text_field( $venue_array['country'] ) : '';
			$venue_zipcode = isset( $venue_array['zip'] ) ? sanitize_text_field( $venue_array['zip'] ) : '';

			$venue_lat     = isset( $venue_array['lat'] ) ? sanitize_text_field( $venue_array['lat'] ) : '';
			$venue_lon     = isset( $venue_array['long'] ) ? sanitize_text_field( $venue_array['long'] ) : '';
			$venue_url     = isset( $venue_array['url'] ) ? esc_url( $venue_array['url'] ) : '';

			// Oraganizer Deatails
			$organizer_array = isset( $centralize_array['organizer'] ) ? $centralize_array['organizer'] : array();
			$organizer_name  = isset( $organizer_array['name'] ) ? sanitize_text_field( $organizer_array['name'] ) : '';
			$organizer_email = isset( $organizer_array['email'] ) ? sanitize_text_field( $organizer_array['email'] ) : '';
			$organizer_phone = isset( $organizer_array['phone'] ) ? sanitize_text_field( $organizer_array['phone'] ) : '';
			$organizer_url   = isset( $organizer_array['url'] ) ? sanitize_text_field( $organizer_array['url'] ) : '';

			// Save Event Data
			// Date & Time
			update_post_meta( $inserted_event_id, 'event_start_date', $event_start_date );
			update_post_meta( $inserted_event_id, 'event_start_hour', $event_start_hour );
			update_post_meta( $inserted_event_id, 'event_start_minute', $event_start_minute );
			update_post_meta( $inserted_event_id, 'event_start_meridian', $event_start_meridian );
			update_post_meta( $inserted_event_id, 'event_end_date', $event_end_date );
			update_post_meta( $inserted_event_id, 'event_end_hour', $event_end_hour );
			update_post_meta( $inserted_event_id, 'event_end_minute', $event_end_minute );
			update_post_meta( $inserted_event_id, 'event_end_meridian', $event_end_meridian );
			update_post_meta( $inserted_event_id, 'start_ts', $start_time );
			update_post_meta( $inserted_event_id, 'end_ts', $end_time );
			update_post_meta( $inserted_event_id, 'online_event', $online_event );
			update_post_meta( $inserted_event_id, 'timezone', $timezone );
			update_post_meta( $inserted_event_id, 'timezone_name', $timezone_name );

			// Venue
			update_post_meta( $inserted_event_id, 'venue_name', $venue_name );
			update_post_meta( $inserted_event_id, 'venue_address', $venue_address );
			update_post_meta( $inserted_event_id, 'venue_city', $venue_city );
			update_post_meta( $inserted_event_id, 'venue_state', $venue_state );
			update_post_meta( $inserted_event_id, 'venue_country', $venue_country );
			update_post_meta( $inserted_event_id, 'venue_zipcode', $venue_zipcode );
			update_post_meta( $inserted_event_id, 'venue_lat', $venue_lat );
			update_post_meta( $inserted_event_id, 'venue_lon', $venue_lon );
			update_post_meta( $inserted_event_id, 'venue_url', $venue_url );

			// Organizer
			update_post_meta( $inserted_event_id, 'organizer_name', $organizer_name );
			update_post_meta( $inserted_event_id, 'organizer_email', $organizer_email );
			update_post_meta( $inserted_event_id, 'organizer_phone', $organizer_phone );
			update_post_meta( $inserted_event_id, 'organizer_url', $organizer_url );

			update_post_meta( $inserted_event_id, 'wpea_event_link', esc_url( $ticket_uri ) );
			update_post_meta( $inserted_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, '_wpea_starttime_str', $start_time );
			update_post_meta( $inserted_event_id, '_wpea_endtime_str', $end_time );

			// Series id
			$series_id   = isset( $centralize_array['series_id'] ) ? $centralize_array['series_id'] : '';			
			if( !empty( $series_id ) ){
				update_post_meta( $inserted_event_id, 'series_id', $series_id );
			}


			if ( $is_exitsing_event ) {
				do_action( 'wpea_after_update_wpea_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id' 	 => $inserted_event_id
				);
			}else{
				do_action( 'wpea_after_create_wpea_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'created',
					'id' 	 => $inserted_event_id
				);
			}

		}else{
			return array( 'status'=> 0, 'message'=> 'Something went wrong, please try again.' );
		}
	}

}
