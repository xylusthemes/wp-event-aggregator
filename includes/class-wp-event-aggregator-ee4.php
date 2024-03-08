<?php
/**
 * Class for Import Events into Event Espresso 4
 *
 * @link       http://xylusthemes.com/
 * @since      1.3.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_EE4 {

	// Event Taxonomy
	protected $taxonomy;

	// Event Posttype
	protected $event_posttype;

	// The Events Calendar Venue Posttype
	protected $venue_posttype;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.3.0
	 */
	public function __construct() {
		
		$this->event_posttype = 'espresso_events';
		$this->taxonomy = 'espresso_event_categories';
		$this->venue_posttype = 'espresso_venues';

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
	public function get_venue_posttype(){
		return $this->venue_posttype;
	}

	/**
	 * import event into EE4
	 *
	 * @since    1.3.0
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
			if( $event_image != '' ){
				$importevents->common->setup_featured_image_to_event( $inserted_event_id, $event_image );
			}else{
				if( $is_exitsing_event ){
					delete_post_thumbnail( $inserted_event_id );
				}
			}

			// Event Date & time Details
			$event_start_date     = date( 'Y-m-d H:i:s', $start_time );
			$event_end_date       = date( 'Y-m-d H:i:s', $end_time );
			
			$datetime_table = $wpdb->prefix . 'esp_datetime';
			$event_meta_table = $wpdb->prefix . 'esp_event_meta';

			$datetime_data = array(
				'EVT_ID' 		=> $inserted_event_id,
				'DTT_EVT_start' => $event_start_date,
				'DTT_EVT_end' 	=> $event_end_date
			);

			if( $is_exitsing_event ){
				$where = array( 'EVT_ID' => $inserted_event_id );
				$is_insert = $wpdb->update( $datetime_table , $datetime_data, $where );
			}else{
				$is_insert = $wpdb->insert( $datetime_table , $datetime_data );
			}


			// Disable event registration
			if( ! $is_exitsing_event ){ 
				$event_meta_data = array(
					'EVT_display_desc' 			  => 0,
					'EVT_display_ticket_selector' => 0,
					'EVT_visible_on'			  => date('Y-m-d H:i:s')
				);
				$event_meta_id = $wpdb->get_var( $wpdb->prepare( "SELECT `EVTM_ID` FROM {$event_meta_table} WHERE EVT_ID = %d", $inserted_event_id ) );
				if( !empty($event_meta_id) && $event_meta_id > 0 ){
					$wpdb->update( $event_meta_table, $event_meta_data, array( 'EVTM_ID' => $event_meta_id, 'EVT_ID' => $inserted_event_id ) );
				}else{
					$event_meta_data['EVT_ID'] = $inserted_event_id;
					$wpdb->insert( $event_meta_table, $event_meta_data );	
				}
			}

			/*
			 * Add Venue.
			 */
			$location = isset( $centralize_array['location'] ) ? $centralize_array['location'] : '';
			$venue_id = $this->add_ee4_venue( $location, $inserted_event_id );

			if( !empty( $venue_id ) && $venue_id > 0 ){
				// Connect venue with Event
				$event_venue_table = $wpdb->prefix . 'esp_event_venue';
				$result = $wpdb->get_col( $wpdb->prepare( "SELECT * FROM {$event_venue_table} WHERE EVT_ID = %d", $inserted_event_id ) );
				if( count( $result ) > 0 ){
					$wpdb->update( $event_venue_table, array( 'VNU_ID' => $venue_id ), array( 'EVT_ID' => $inserted_event_id ) );
				}else{
					$wpdb->insert( $event_venue_table, array( 'EVT_ID' => $inserted_event_id, 'VNU_ID' => $venue_id ) );	
				}
			}

			// Save Event Data
			update_post_meta( $inserted_event_id, 'wpea_event_link', esc_url( $ticket_uri ) );
			update_post_meta( $inserted_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, '_wpea_starttime_str', $start_time );
			update_post_meta( $inserted_event_id, '_wpea_endtime_str', $end_time );
			update_post_meta( $inserted_event_id, 'start_ts', $start_time );
			update_post_meta( $inserted_event_id, 'end_ts', $end_time );

			if ( $is_exitsing_event ) {
				do_action( 'wpea_after_update_ee4_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id' 	 => $inserted_event_id
				);
			}else{
				do_action( 'wpea_after_create_ee4_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'created',
					'id' 	 => $inserted_event_id
				);
			}

		}else{
			return array( 'status'=> 0, 'message'=> 'Something went wrong, please try again.' );
		}
	}

	/*
	 * Add Venue to EE4 Events
	 *
	 * @param $centralize_array array Event data array.
	 *
	 * @retun int|bool venue ID on success or false on failure
	 */
	public function add_ee4_venue( $venue_array, $event_id ){
		global $wpdb;

		if( empty( $venue_array ) ){
			return false;
		}

		$location_name = isset( $venue_array['name'] ) ? $venue_array['name'] : '';
		$venue_id      = isset( $venue_array['ID'] ) ? $venue_array['ID'] : '';

		if( !empty( $location_name ) && $location_name == 'Online Event' ){
			$is_exitsing_venue = $this->get_ee4_venue_by_name( $location_name );
			if ( $is_exitsing_venue ) {
				return $is_exitsing_venue;
			}
		}else{
			if ( empty( $venue_id ) ) {
				return false;
			}

			$is_exitsing_venue = $this->get_ee4_venue_by_id( $venue_id );
			if( $is_exitsing_venue ){
				return $is_exitsing_venue;
			}
		}

		// Venue Deatails
		$address_1 = isset( $venue_array['address_1'] ) ? $venue_array['address_1'] : '';
		$address_2 = isset( $venue_array['address_2'] ) ? $venue_array['address_2'] : '';
		$venue_name    = isset( $venue_array['name'] ) ? sanitize_text_field( $venue_array['name'] ) : '';
		$venue_desc    = isset( $venue_array['description'] ) ? sanitize_text_field( $venue_array['description'] ) : '';
		$venue_address = isset( $venue_array['full_address'] ) ? sanitize_text_field( $venue_array['full_address'] ) : sanitize_text_field( $address_1 );
		$venue_city    = isset( $venue_array['city'] ) ? sanitize_text_field( $venue_array['city'] ) : '';
		$venue_state   = isset( $venue_array['state'] ) ? sanitize_text_field( $venue_array['state'] ) : '';
		$venue_country = isset( $venue_array['country'] ) ? sanitize_text_field( $venue_array['country'] ) : '';
		$venue_zipcode = isset( $venue_array['zip'] ) ? sanitize_text_field( $venue_array['zip'] ) : '';
		$venue_lat     = isset( $venue_array['lat'] ) ? sanitize_text_field( $venue_array['lat'] ) : '';
		$venue_lon     = isset( $venue_array['long'] ) ? sanitize_text_field( $venue_array['long'] ) : '';
		$venue_url     = isset( $venue_array['url'] ) ? esc_url( $venue_array['url'] ) : '';

		$venuedata = array(
			'post_title'   => $venue_name,
			'post_content' => $venue_desc,
			'post_type'    => $this->venue_posttype,
			'post_status' => 'publish',
		);

		$ivenue_id = wp_insert_post( $venuedata, true );
		if( !empty( $venue_id ) ){
			update_post_meta( $ivenue_id, 'wpea_ee4_venue_id', $venue_id );
		}else{
			update_post_meta( $ivenue_id, 'wpea_ee4_venue_id', $location_name );
		}
		
		// Get Country code
		$cnt_iso = $sta_id = '';
		$country_table = $wpdb->prefix . 'esp_country';
		$state_table = $wpdb->prefix . 'esp_state';
		if( $venue_country != ''){
			$cnt_country = $wpdb->get_row( $wpdb->prepare( "SELECT `CNT_ISO`,`CNT_active` FROM {$country_table} WHERE `CNT_name` = %s OR `CNT_ISO` = %s OR `CNT_ISO3` = %s", $venue_country, $venue_country, $venue_country ) );
			if( !empty( $cnt_country ) && isset( $cnt_country->CNT_ISO ) ){
				$cnt_iso = $cnt_country->CNT_ISO;
				if( $cnt_country->CNT_active == 0 ){
					$active_con = $wpdb->update( $country_table, array( 'CNT_active' => 1 ), array( 'CNT_ISO' => $cnt_iso ) );
				}
			}
		}

		if( $venue_state != '' && $cnt_iso != ''){
			$sta_id = $wpdb->get_var( $wpdb->prepare( "SELECT `STA_ID` FROM {$state_table} WHERE `CNT_ISO` = %s AND (`STA_abbrev` = %s OR `STA_name` = %s)", $cnt_iso, $venue_state, $venue_state ) );
			if( empty( $sta_id ) || is_null( $sta_id ) ){
				$inserted = $wpdb->insert( $state_table, array( 'CNT_ISO' => $cnt_iso, 'STA_abbrev' => $venue_state, 'STA_name' => $venue_state ) );
				if( $inserted ){
					$sta_id = $wpdb->insert_id;
				}
			}
		}
		
		// Add Venue Meta
		$venue_data = array(
			'VNU_ID'		=> $ivenue_id,
			'VNU_address'   => $address_1,
			'VNU_address2'  => $address_2,
			'VNU_city'      => $venue_city,
			'VNU_zip'       => $venue_zipcode,
			'VNU_url'       => $venue_url,
			'VNU_enable_for_gmap'     => apply_filters( 'wpea_ee4_venue_enable_for_map', 1 ),
		);
		if( $cnt_iso != ''){
			$venue_data['CNT_ISO'] = $cnt_iso;
		}
		if( $sta_id != ''){
			$venue_data['STA_ID'] = $sta_id;
		}

		$venue_table = $wpdb->prefix . 'esp_venue_meta';

		$wpdb->insert( $venue_table , $venue_data );

		return $ivenue_id;
	}

	/**
	 * Check for Existing EE4 Venue
	 *
	 * @since    1.0.0
	 * @param int $venue_id Venue id.
	 * @return int/boolean
	 */
	public function get_ee4_venue_by_id( $venue_id ) {
		if( empty( $venue_id ) ){
			return false;
		}

		$existing_venue = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $this->venue_posttype,
			'meta_key' => 'wpea_ee4_venue_id',
			'meta_value' => $venue_id,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return $existing_venue[0]->ID;
		}
		return false;
	}

	/**
	 * Check for Existing EE4 Venue
	 *
	 * @since    1.7.3
	 * @param int $venue_name Venue id.
	 * @return int/boolean
	 */
	public function get_ee4_venue_by_name( $venue_name ) {
		if ( empty( $venue_name ) ) {
			return false;
		}

		$existing_venue = get_posts( array(
			'posts_per_page'   => 1,
			'post_type'        => $this->venue_posttype,
			'meta_key'         => 'wpea_ee4_venue_id',
			'meta_value'       => $venue_name,
			'suppress_filters' => false,
		) );

		if ( is_array( $existing_venue ) && ! empty( $existing_venue ) ) {
			return $existing_venue[0]->ID;
		}
		return false;
	}

}