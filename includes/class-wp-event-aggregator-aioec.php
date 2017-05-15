<?php
/**
 * Class for Import Events into All in One Event Calendar
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Aioec {

	// The Events Calendar Event Taxonomy
	protected $taxonomy;

	// The Events Calendar Event Posttype
	protected $event_posttype;

	// The Events Calendar Event Custom Table
	protected $event_db_table;

	// The Events Calendar Event Instance custom table
	protected $event_instances_table;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		
		global $wpdb;
		$this->event_posttype = 'ai1ec_event';
		$this->taxonomy = 'events_categories';
		$this->event_db_table = "{$wpdb->prefix}ai1ec_events";
		$this->event_instances_table = "{$wpdb->prefix}ai1ec_event_instances";
		
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
			if ( 'yes' != $update_events ) {
				return array( 'status'=> 'skipped' );
			}
		}

		$origin_event_id = $centralize_array['ID'];
		$post_title = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$post_description = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$start_time = $centralize_array['starttime_local'];
		$end_time = $centralize_array['endtime_local'];
		$event_uri = $centralize_array['url'];

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
			update_post_meta( $inserted_event_id, 'wpea_event_id', $centralize_array['ID'] );
			update_post_meta( $inserted_event_id, 'wpea_event_link', esc_url( $event_uri ) );
			update_post_meta( $inserted_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, '_wpea_starttime_str', $start_time );
			update_post_meta( $inserted_event_id, '_wpea_endtime_str', $end_time );
			
			// Custom table Details
			$event_array = array(
				'post_id' => $inserted_event_id,
				'start'   => $start_time,
				'end' 	  => $end_time,
			);

			$event_count = $wpdb->get_var( "SELECT COUNT(*) FROM $this->event_instances_table WHERE `post_id` = ".absint( $inserted_event_id ) );
			if( $event_count > 0 && is_numeric( $event_count ) ){
				$where = array( 'post_id' => absint( $inserted_event_id ) );
				$wpdb->update( $this->event_instances_table , $event_array, $where );	
			}else{
				$wpdb->insert( $this->event_instances_table , $event_array );
			}

			$venue   = isset( $centralize_array['location'] ) ? $centralize_array['location'] : '';
			$location_name = isset( $venue['name'] ) ? $venue['name'] : '';
			$address = isset( $venue['full_address'] ) ? $venue['full_address'] : $venue['address_1'];
			$city 	 = isset( $venue['city'] ) ? $venue['city'] : '';
			$state   = isset( $venue['state'] ) ? $venue['state'] : '';
			$zip     = isset( $venue['zip'] ) ? $venue['zip'] : '';
			$lat     = isset( $venue['lat'] ) ? $venue['lat'] : '';
			$lon     = isset( $venue['long'] ) ? $venue['long'] : '';
			$country = isset( $venue['country'] ) ? $venue['country'] : '';
			$show_map = $show_coordinates = 0;
			if( $lat != '' && $lon != '' ){
				$show_map = $show_coordinates = 1;
			}
			$full_address = $address;
			if( $city != '' ){
				$full_address .= ', '.$city;
			}
			if( $state != '' ){
				$full_address .= ', '.$state;
			}
			if( $zip != '' ){
				$full_address .= ' '.$zip;
			}

			$organizer = isset( $centralize_array['organizer'] ) ? $centralize_array['organizer'] : '';
			$org_name  = isset( $organizer['name'] ) ? $organizer['name'] : '';
			$org_phone = isset( $organizer['phone'] ) ? $organizer['phone'] : '';
			$org_email = isset( $organizer['email'] ) ? $organizer['email'] : '';
			$org_url   = isset( $organizer['url'] ) ? $organizer['url'] : '';

			$event_table_array = array(
				'post_id' 		   => $inserted_event_id,
				'start'            => $start_time,
				'end' 	  		   => $end_time,
				'timezone_name'    => 'UTC',
				'allday' 	  	   => 0,
				'instant_event'    => 0,
				'venue' 	  	   => $location_name,
				'country' 	  	   => $country,
				'address' 	  	   => $full_address,
				'city' 	       	   => $city,
				'province' 	       => $state,
				'postal_code' 	   => $zip,
				'show_map' 	       => $show_map,
				'contact_name' 	   => $org_name,
				'contact_phone'    => $org_phone,
				'contact_email'    => $org_email,
				'contact_url' 	   => $org_url,			
				'cost'   		   => '',
				'ticket_url' 	   => $event_uri,
				'ical_uid' 	  	   => $this->get_ical_uid_for_event( $inserted_event_id ),
				'show_coordinates' => $show_coordinates,
			);
			if( $lat != '' ){
				$event_table_array['latitude'] = $lat;
			}
			if( $lon != '' ){
				$event_table_array['longitude'] = $lon;
			}
			

			$event_format = array(
				'%d',  // post_id
				'%d',  // start
				'%d',  // end
				'%s',  // timezone_name
				'%d',  // allday
				'%d',  // instant_event
				'%s',  // venue
				'%s',  // country
				'%s',  // address
				'%s',  // city
				'%s',  // province
				'%s',  // postal_code
				'%d',  // show_map
				'%s',  // contact_name
				'%s',  // contact_phone
				'%s',  // contact_email
				'%s',  // contact_url
				'%s',  // cost
				'%s',  // ticket_url
				'%s',  // ical_uid
				'%d',  // show_coordinates
			);
			if( $lat != '' ){
				$event_format[] = '%f';  // latitude
			}
			if( $lon != '' ){
				$event_format[] = '%f';  // longitude
			}

			$event_exist_count = $wpdb->get_var( "SELECT COUNT(*) FROM $this->event_db_table WHERE `post_id` = ".absint( $inserted_event_id ) );
			if( $event_exist_count > 0 && is_numeric( $event_exist_count ) ){
				$where = array( 'post_id' => absint( $inserted_event_id ) );
				$wpdb->update( $this->event_db_table, $event_table_array, $where, $event_format );	
			}else{
				$wpdb->insert( $this->event_db_table, $event_table_array, $event_format );
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

	/**
	 * Get Uid for ai1ec event.
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @return str
	 */	
	public function get_ical_uid_for_event( $event_id ){
		$site_url = parse_url( ai1ec_get_site_url() );
		$format   = 'ai1ec-%d@' . $site_url['host'];
		if ( isset( $site_url['path'] ) ) {
			$format .= $site_url['path'];
		}
		return sprintf( $format, $event_id );
	}
}
