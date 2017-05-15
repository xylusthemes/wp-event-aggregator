<?php
/**
 * Class for Import Events into Event Organizer
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Event_Organizer {

	// The Events Calendar Event Taxonomy
	protected $taxonomy;

	// The Events Calendar Event Posttype
	protected $event_posttype;

	// The Events Calendar Venue Posttype
	protected $venue_taxonomy;

	// The Events Calendar Venue custom table
	protected $venue_db_table;

	// The Events Calendar Event Custom Table
	protected $event_db_table;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		global $wpdb;
		$this->event_posttype = 'event';
		$this->taxonomy = 'event-category';
		$this->venue_taxonomy = 'event-venue';
		$this->venue_db_table = "{$wpdb->prefix}eo_venuemeta";
		$this->event_db_table = "{$wpdb->prefix}eo_events";
	}


	/**
	 * Get Posttype and Taxonomy Functions
	 *
	 * @return string
	 */
	public function get_event_posttype(){
		return $this->event_posttype;
	}	
	public function get_venue_taxonomy(){
		return $this->venue_taxonomy;
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

		$eo_eventdata = array(
			'post_title'   => $post_title,
			'post_content' => $post_description,
			'post_type'    => $this->event_posttype,
			'post_status'  => 'pending',
		);
		if ( $is_exitsing_event ) {
			$eo_eventdata['ID'] = $is_exitsing_event;
		}

		if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
			$eo_eventdata['post_status'] = $event_args['event_status'];
		}

		$inserted_event_id = wp_insert_post( $eo_eventdata, true );

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

			// Save Meta.
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_until', date( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_start_start', date( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_start_finish', date( 'Y-m-d H:i:s', $end_time ) );
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_last_start', date( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_last_finish', date( 'Y-m-d H:i:s', $end_time ) );
			update_post_meta( $inserted_event_id, 'wpea_event_id', $centralize_array['ID'] );
			update_post_meta( $inserted_event_id, 'wpea_event_link', esc_url( $ticket_uri ) );
			update_post_meta( $inserted_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, '_wpea_starttime_str', $start_time );
			update_post_meta( $inserted_event_id, '_wpea_endtime_str', $end_time );
			
			// Custom table Details
			$event_array = array(
				'post_id' 		   => $inserted_event_id,
				'StartDate' 	   => date( 'Y-m-d', $start_time ),
				'EndDate' 	       => date( 'Y-m-d', $end_time ),
				'StartTime'        => date( 'H:i:s', $start_time ),
				'FinishTime' 	   => date( 'H:i:s', $end_time ),
				'event_occurrence' => 0,
			);

			$event_count = $wpdb->get_var( "SELECT COUNT(*) FROM $this->event_db_table WHERE `post_id` = ".absint( $inserted_event_id ) );
			if( $event_count > 0 && is_numeric( $event_count ) ){
				$where = array( 'post_id' => absint( $inserted_event_id ) );
				$wpdb->update( $this->event_db_table , $event_array, $where );	
			}else{
				$wpdb->insert( $this->event_db_table , $event_array );
			}

			// Save location Data
			if( isset( $centralize_array['location']['name'] ) && $centralize_array['location']['name'] != '' ){
				$loc_term = term_exists( $centralize_array['location']['name'], $this->venue_taxonomy );
				if ($loc_term !== 0 && $loc_term !== null) {
				  if( is_array( $loc_term ) ){
				  	$loc_term_id = (int)$loc_term['term_id'];
				  }
				}else{
					$new_loc_term = wp_insert_term(
					  $centralize_array['location']['name'], 
					  $this->venue_taxonomy
					);
					if( !is_wp_error( $new_loc_term ) ){
						$loc_term_id = (int)$new_loc_term['term_id'];
					}
				}
				$term_loc_ids = wp_set_object_terms( $inserted_event_id, $loc_term_id, $this->venue_taxonomy );
				$venue = $centralize_array['location'];
				$address = isset( $venue['full_address'] ) ? $venue['full_address'] : $venue['address_1'];
				$city 	 = isset( $venue['city'] ) ? $venue['city'] : '';
				$state   = isset( $venue['state'] ) ? $venue['state'] : '';
				$zip     = isset( $venue['zip'] ) ? $venue['zip'] : '';
				$lat     = isset( $venue['lat'] ) ? round( $venue['lat'], 6 ) : 0.000000;
				$lon     = isset( $venue['long'] ) ? round( $venue['long'], 6 ) : 0.000000;
				$country = isset( $venue['country'] ) ? $venue['country'] : '';

				$loc_term_meta = array();
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_address',
					'meta_value'  => $address,
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_city',
					'meta_value'  => $city,
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_state',
					'meta_value'  => $state,
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_postcode',
					'meta_value'  => $zip,
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_country',
					'meta_value'  => $country,
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_lat',
					'meta_value'  => $lat,
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_lng',
					'meta_value'  => $lon,
				);	

				if( !empty( $loc_term_meta ) ){
					$meta_keys = $wpdb->get_col( "SELECT `meta_key` FROM {$wpdb->prefix}eo_venuemeta WHERE `eo_venue_id` = ".$loc_term_id );
					foreach ($loc_term_meta as $loc_value) {
						if( in_array( $loc_value['meta_key'], $meta_keys) ){
							$where = array( 'eo_venue_id' => absint( $loc_term_id ), 'meta_key' => $loc_value['meta_key'] );
							$wpdb->update( $this->venue_db_table , $loc_value, $where );	
						}else{
							$wpdb->insert( $this->venue_db_table , $loc_value );
						}			
					}
				}
			}

			if ( $is_exitsing_event ) {
				do_action( 'wpea_after_update_event_organizer_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id' 	 => $inserted_event_id
				);
			}else{
				do_action( 'wpea_after_create_event_organizer_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
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
