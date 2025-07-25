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

		$eo_eventdata = array(
			'post_title'   => $post_title,
			'post_content' => $post_description,
			'post_type'    => $this->event_posttype,
			'post_status'  => 'pending',
			'post_author'  => isset( $event_args['event_author'] ) ? $event_args['event_author'] : get_current_user_id()
		);
		if ( $is_exitsing_event ) {
			$eo_eventdata['ID'] = $is_exitsing_event;
		}

		if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
			$eo_eventdata['post_status'] = $event_args['event_status'];
		}

		if ( $is_exitsing_event && ! $importevents->common->wpea_is_updatable('status') ) {
			$eo_eventdata['post_status'] = get_post_status( $is_exitsing_event );
			$event_args['event_status'] = get_post_status( $is_exitsing_event );
		}

		$inserted_event_id = wp_insert_post( $eo_eventdata, true );

		if ( ! is_wp_error( $inserted_event_id ) ) {
			$inserted_event = get_post( $inserted_event_id );
			if ( empty( $inserted_event ) ) { return '';}

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

			// Save Meta.
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_until', gmdate( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_start_start', gmdate( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_start_finish', gmdate( 'Y-m-d H:i:s', $end_time ) );
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_last_start', gmdate( 'Y-m-d H:i:s', $start_time ) );
			update_post_meta( $inserted_event_id, '_eventorganiser_schedule_last_finish', gmdate( 'Y-m-d H:i:s', $end_time ) );
			update_post_meta( $inserted_event_id, 'wpea_event_link', esc_url( $ticket_uri ) );
			update_post_meta( $inserted_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, '_wpea_starttime_str', $start_time );
			update_post_meta( $inserted_event_id, '_wpea_endtime_str', $end_time );
			$is_all_day    = !empty( $centralize_array['is_all_day'] ) ? $centralize_array['is_all_day'] : 0;
			$args = array(
				'all_day' => $is_all_day,
				'schedule' => 'once',
				'frequency' => 1
			);
			update_post_meta( $inserted_event_id, '_eventorganiser_event_schedule', $args );

			// Series id
			$series_id   = isset( $centralize_array['series_id'] ) ? $centralize_array['series_id'] : '';			
			if( !empty( $series_id ) ){
				update_post_meta( $inserted_event_id, 'series_id', $series_id );
			}
			
			// Custom table Details
			$event_array = array(
				'post_id' 		   => $inserted_event_id,
				'StartDate' 	   => gmdate( 'Y-m-d', $start_time ),
				'EndDate' 	       => gmdate( 'Y-m-d', $end_time ),
				'StartTime'        => gmdate( 'H:i:s', $start_time ),
				'FinishTime' 	   => gmdate( 'H:i:s', $end_time ),
				'event_occurrence' => 0,
			);

			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$event_count = $wpdb->get_var( "SELECT COUNT(*) FROM $this->event_db_table WHERE `post_id` = ".absint( $inserted_event_id ) );
			if( $event_count > 0 && is_numeric( $event_count ) ){
				$where = array( 'post_id' => absint( $inserted_event_id ) );
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
				$wpdb->update( $this->event_db_table , $event_array, $where );	
			}else{
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
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
				$address_1 = isset( $venue['address_1'] ) ? $venue['address_1'] : '';
				$address = isset( $venue['full_address'] ) ? $venue['full_address'] : $address_1;
				$city 	 = isset( $venue['city'] ) ? $venue['city'] : '';
				$state   = isset( $venue['state'] ) ? $venue['state'] : '';
				$zip     = isset( $venue['zip'] ) ? $venue['zip'] : '';
				$lat     = !empty( $venue['lat'] ) ? round( $venue['lat'], 6 ) : 0.000000;
				$lon     = !empty( $venue['long'] ) ? round( $venue['long'], 6 ) : 0.000000;
				$country = isset( $venue['country'] ) ? $venue['country'] : '';

				$loc_term_meta = array();
				$$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_address', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
					'meta_value'  => $address,   // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_city',   // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
					'meta_value'  => $city,     // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_state',   // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
					'meta_value'  => $state,     // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_postcode',  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
					'meta_value'  => $zip,         // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_country',  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
					'meta_value'  => $country,    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_lat',  // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
					'meta_value'  => $lat,    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
				);
				$loc_term_meta[] = array(
					'eo_venue_id' => $loc_term_id,
					'meta_key' 	  => '_lng', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key 
					'meta_value'  => $lon,   // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value 
				);	

				if( !empty( $loc_term_meta ) ){
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
					$meta_keys = $wpdb->get_col( "SELECT `meta_key` FROM {$wpdb->prefix}eo_venuemeta WHERE `eo_venue_id` = ".$loc_term_id );
					foreach ($loc_term_meta as $loc_value) {
						if( in_array( $loc_value['meta_key'], $meta_keys) ){
							$where = array( 'eo_venue_id' => absint( $loc_term_id ), 'meta_key' => $loc_value['meta_key'] ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
							// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
							$wpdb->update( $this->venue_db_table , $loc_value, $where );	
						}else{
							// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
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
