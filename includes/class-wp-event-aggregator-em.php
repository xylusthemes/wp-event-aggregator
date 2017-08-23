<?php
/**
 * Class for Import Events into Events Manager
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_EM {

	// The Events Calendar Event Taxonomy
	protected $taxonomy;

	// The Events Calendar Event Posttype
	protected $event_posttype;

	// The Events Calendar Venue Posttype
	protected $venue_posttype;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		if ( defined( 'EM_POST_TYPE_EVENT' ) ) {
			$this->event_posttype = EM_POST_TYPE_EVENT;
		} else {
			$this->event_posttype = 'event';
		}
		if ( defined( 'EM_TAXONOMY_CATEGORY' ) ) {
			$this->taxonomy = EM_TAXONOMY_CATEGORY;
		} else {
			$this->taxonomy = 'event-categories';
		}
		if ( defined( 'EM_POST_TYPE_LOCATION' ) ) {
			$this->venue_posttype = EM_POST_TYPE_LOCATION;
		} else {
			$this->venue_posttype = 'location';
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
		global $wpdb, $importevents;

		if( empty( $centralize_array ) || !isset( $centralize_array['ID'] ) ){
			return false;
		}

		$is_exitsing_event = $importevents->common->get_event_by_event_id( $this->event_posttype, $centralize_array );
				
		if ( $is_exitsing_event ) {
			// Update event or not?
			$options = wpea_get_import_options( $centralize_array['origin'] );
			$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
			if ( 'yes' != $update_events ) {
				return array( 'status'=> 'skipped' );
			}
		}

		$origin_event_id = $centralize_array['ID'];
		$post_title = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$post_description = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$start_time = $centralize_array['starttime_local'];
		$end_time = $centralize_array['endtime_local'];
		$ticket_uri = $centralize_array['url'];

		$emeventdata = array(
			'post_title'  => $post_title,
			'post_content' => $post_description,
			'post_type'   => $this->event_posttype,
			'post_status' => 'pending',
		);
		if ( $is_exitsing_event ) {
			$emeventdata['ID'] = $is_exitsing_event;
		}
		if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
			$emeventdata['post_status'] = $event_args['event_status'];
		}

		$inserted_event_id = wp_insert_post( $emeventdata, true );

		if ( ! is_wp_error( $inserted_event_id ) ) {
			$inserted_event = get_post( $inserted_event_id );
			if ( empty( $inserted_event ) ) { return '';}

			// Asign event category.
			$ife_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $ife_cats ) ) {
				foreach ( $ife_cats as $ife_catk => $ife_catv ) {
					$ife_cats[ $ife_catk ] = (int) $ife_catv;
				}
			}
			if ( ! empty( $ife_cats ) ) {
				wp_set_object_terms( $inserted_event_id, $ife_cats, $this->taxonomy );
			}

			// Assign Featured images
			$event_image = $centralize_array['image_url'];
			if( $event_image != '' ){
				$importevents->common->setup_featured_image_to_event( $inserted_event_id, $event_image );
			}else{
				if( $is_exitsing_event ){
					delete_post_thumbnail( $inserted_event_id );
				}
			}
			$location_id = 0;
			if ( $is_exitsing_event ) {
				if( isset( $centralize_array['location'] ) ){ 
					$location_id = $this->get_location_args( $centralize_array['location'], $inserted_event_id );
				}
			}else{
				if( isset( $centralize_array['location'] ) ){ 
					$location_id = $this->get_location_args( $centralize_array['location'], false );
				}
			}

			$event_status = null;
			if ( $inserted_event->post_status == 'publish' ) { $event_status = 1;}
			if ( $inserted_event->post_status == 'pending' ) { $event_status = 0;}
			// Save Meta.
			update_post_meta( $inserted_event_id, '_event_start_time', date( 'H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_event_end_time', date( 'H:i:s', $end_time ) );
			update_post_meta( $inserted_event_id, '_event_all_day', 0 );
			update_post_meta( $inserted_event_id, '_event_start_date', date( 'Y-m-d', $start_time ) );
			update_post_meta( $inserted_event_id, '_event_end_date', date( 'Y-m-d', $end_time ) );
			update_post_meta( $inserted_event_id, '_location_id', $location_id );
			update_post_meta( $inserted_event_id, '_event_status', $event_status );
			update_post_meta( $inserted_event_id, '_event_private', 0 );
			update_post_meta( $inserted_event_id, '_start_ts', str_pad( $start_time, 10, 0, STR_PAD_LEFT));
			update_post_meta( $inserted_event_id, '_end_ts', str_pad( $end_time, 10, 0, STR_PAD_LEFT));
			update_post_meta( $inserted_event_id, 'wpea_event_id', $centralize_array['ID'] );
			update_post_meta( $inserted_event_id, 'wpea_event_link', esc_url( $ticket_uri ) );
			update_post_meta( $inserted_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, '_wpea_starttime_str', $start_time );
			update_post_meta( $inserted_event_id, '_wpea_endtime_str', $end_time );
			
			// Custom table Details
			$event_array = array(
				'post_id' 		   => $inserted_event_id,
				'event_slug' 	   => $inserted_event->post_name,
				'event_owner' 	   => $inserted_event->post_author,
				'event_name'       => $inserted_event->post_title,
				'event_start_time' => date( 'H:i:s', $start_time ),
				'event_end_time'   => date( 'H:i:s', $end_time ),
				'event_all_day'    => 0,
				'event_start_date' => date( 'Y-m-d', $start_time ),
				'event_end_date'   => date( 'Y-m-d', $end_time ),
				'post_content' 	   => $inserted_event->post_content,
				'location_id' 	   => $location_id,
				'event_status' 	   => $event_status,
				'event_date_created' => $inserted_event->post_date,
			);

			$event_table = ( defined( 'EM_EVENTS_TABLE' ) ? EM_EVENTS_TABLE : $wpdb->prefix . 'em_events' );
			if ( $is_exitsing_event ) {
				$eve_id = get_post_meta( $inserted_event_id, '_event_id', true );
				$where = array( 'event_id' => $eve_id );
				$wpdb->update( $event_table , $event_array, $where );
			}else{
				if ( $wpdb->insert( $event_table , $event_array ) ) {
					update_post_meta( $inserted_event_id, '_event_id', $wpdb->insert_id );
				}
			}

			if ( $is_exitsing_event ) {
				do_action( 'wpea_after_update_em_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id' 	 => $inserted_event_id
				);
			}else{
				do_action( 'wpea_after_create_em_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'created',
					'id' 	 => $inserted_event_id
				);
			}

		}else{
			return array( 'status'=> 0, 'message'=> 'Something went wrong, please try again.' );
		}
	}

	/**
	 * Set Location for event
	 *
	 * @since    1.0.0
	 * @param array $venue location.
	 * @return array
	 */
	public function get_location_args( $venue, $event_id = false ) {
		global $wpdb, $importevents;

		if ( !isset( $venue['ID'] ) ) {
			return null;
		}
		$existing_venue = $this->get_venue_by_id( $venue['ID'] );
		
		if ( $existing_venue && is_numeric( $existing_venue ) && $existing_venue > 0 && !$event_id ) {
			return get_post_meta( $existing_venue, '_location_id', true );
		}
		
		$locationdata = array(
			'post_title'   => isset( $venue['name'] ) ? $venue['name'] : 'Untitled - Location',
			'post_content' => '',
			'post_type'    => $this->venue_posttype,
			'post_status'  => 'publish',
		);

		if ( $existing_venue && is_numeric( $existing_venue ) && $existing_venue > 0 ) {
			$locationdata['ID'] = $existing_venue;
		}
		$location_id = wp_insert_post( $locationdata, true );

		if ( ! is_wp_error( $location_id ) ) {
			$blog_id = 0;
			if ( is_multisite() ) {
				$blog_id = get_current_blog_id();
			}
			$location = get_post( $location_id );
			if ( empty( $location ) ) { return null;}

			// Location information.
			$country = isset( $venue['country'] ) ? $venue['country'] : '';
			if( strlen( $country ) > 2 && $country != '' ){
				$country = $importevents->common->wpea_get_country_code( $country );
			}
			$address = isset( $venue['full_address'] ) ? $venue['full_address'] : $venue['address_1'];
			$city 	 = isset( $venue['city'] ) ? $venue['city'] : '';
			$state   = isset( $venue['state'] ) ? $venue['state'] : '';
			$zip     = isset( $venue['zip'] ) ? $venue['zip'] : '';
			$lat     = isset( $venue['lat'] ) ? round( $venue['lat'], 6 ) : 0.000000;
			$lon     = isset( $venue['long'] ) ? round( $venue['long'], 6 ) : 0.000000;

			// Save metas.
			update_post_meta( $location_id, '_blog_id', $blog_id );
			update_post_meta( $location_id, '_location_address', $address );
			update_post_meta( $location_id, '_location_town', $city );
			update_post_meta( $location_id, '_location_state', $state );
			update_post_meta( $location_id, '_location_postcode', $zip );
			update_post_meta( $location_id, '_location_region','' );
			update_post_meta( $location_id, '_location_country', $country );
			update_post_meta( $location_id, '_location_latitude', $lat );
			update_post_meta( $location_id, '_location_longitude', $lon );
			update_post_meta( $location_id, '_location_status', 1 );
			update_post_meta( $location_id, 'wpea_event_venue_id', $venue['ID'] );

			global $wpdb;
			$location_array = array(
				'post_id' => $location_id,
				'blog_id' => $blog_id,
				'location_slug' => $location->post_name,
				'location_name' => $location->post_title,
				'location_owner' => $location->post_author,
				'location_address' => $address,
				'location_town' => $city,
				'location_state' => $state,
				'location_postcode' => $zip,
				'location_region' => $state,
				'location_country' => $country,
				'location_latitude' => $lat,
				'location_longitude' => $lon,
				'post_content' => $location->post_content,
				'location_status' => 1,
				'location_private' => 0,
			);
			$location_format = array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%d', '%d' );
			$where_format = array( '%d' );

			if( defined( 'EM_LOCATIONS_TABLE' ) ){
				$event_location_table = EM_LOCATIONS_TABLE;
			}else{
				$event_location_table = $wpdb->prefix . 'em_locations';
			}

			if( $event_id && is_numeric( $event_id ) && $event_id > 0 ){
				$loc_id = get_post_meta( $event_id, '_location_id', true );
				if( $loc_id != '' ){
					$where = array( 'location_id' => $loc_id );	
					$is_update = $wpdb->update( $event_location_table, $location_array, $where, $location_format, $where_format );
					if ( false !== $is_update ) {
						return $loc_id;    
					}

				}else{
					$is_insert = $wpdb->insert( $event_location_table , $location_array, $location_format );
					if ( false !== $is_insert ) {
						$insert_loc_id = $wpdb->insert_id;
						update_post_meta( $location_id, '_location_id', $insert_loc_id );
						return $insert_loc_id;
					}
				}				
				
			}else{
				$is_insert = $wpdb->insert( $event_location_table , $location_array, $location_format );
				if ( false !== $is_insert ) {
					$insert_loc_id = $wpdb->insert_id;
					update_post_meta( $location_id, '_location_id', $insert_loc_id );
					return $insert_loc_id;
				}
			}
		}
		return null;
	}

	/**
	 * Check for Existing TEC Venue
	 *
	 * @since    1.0.0
	 * @param int $venue_id Venue id.
	 * @return int/boolean
	 */
	public function get_venue_by_id( $venue_id ) {
		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $this->venue_posttype,
			'meta_key' => 'wpea_event_venue_id',
			'meta_value' => $venue_id,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return $existing_venue[0]->ID;
		}
		return false;
	}

}
