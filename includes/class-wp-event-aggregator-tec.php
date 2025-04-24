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
		$formated_args = array();
		if ( $is_exitsing_event && is_numeric( $is_exitsing_event ) && $is_exitsing_event > 0 ) {
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
			if ( 'yes' == $update_events ) {

				$formated_args['post_status'] = $event_args['event_status'];
				$formated_args['post_author'] = isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id();
				if ( ! $importevents->common->wpea_is_updatable( 'status' ) ) {
					$formated_args['post_status'] = get_post_status( $is_exitsing_event );
				}

				return $this->update_event( $is_exitsing_event, $centralize_array, $formated_args, $event_args );
			}else{
				return array(
					'status'=> 'skipped',
					'id' 	=> $is_exitsing_event
				);
			}
		} else {

			if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
				$formated_args['post_status'] = $event_args['event_status'];
			}
			$formated_args['post_author'] = isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id();

			if ( ! $importevents->common->wpea_is_updatable( 'status' ) ) {
				$formated_args['post_status'] = get_post_status( $is_exitsing_event );
			}

			return $this->create_event( $centralize_array, $formated_args, $event_args );
		}
	}

	/**
	 * Create New TEC event.
	 *
	 * @since    1.0.0
	 * @param  array $centralize_array  Centralize Array event.
	 * @param  array $formated_args     Formated arguments for eventbrite event.
	 * @param  int   $post_id Post id.
	 * @return void
	 */
	public function create_event( $centralize_array = array(), $formated_args = array(), $event_args = array() ) {
		// Create event using TEC advanced functions.
		global $importevents ,$wpdb;
	
		$event_title   = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$event_content = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$event_status  = $formated_args['post_status'];
		$event_author  = $formated_args['post_author'];
		
		$tec_event     = array(
			'post_title'   => $event_title,
			'post_content' => $event_content,
			'post_status'  => $event_status,
			'post_author'  => $event_author,
			'post_type'    => $this->event_posttype,
		);
		
		$new_event_id = wp_insert_post( $tec_event, true );


		if ( $new_event_id ) {
			
			//update all metadata
			$allmetas = $this->format_event_args_for_tec( $centralize_array );
			if( !empty( $allmetas ) ){
				foreach( $allmetas as $key => $value ){
					if( !empty( $value ) ){
						update_post_meta( $new_event_id, $key, $value );
					}
				}
			}

			update_post_meta( $new_event_id, 'wpea_event_id',  $centralize_array['ID'] );
			update_post_meta( $new_event_id, 'wpea_event_origin',  $event_args['import_origin'] );
			update_post_meta( $new_event_id, 'wpea_event_link', esc_url( $centralize_array['url'] ) );
			update_post_meta( $new_event_id, '_wpea_starttime_str', $centralize_array['starttime_local'] );
			update_post_meta( $new_event_id, '_wpea_endtime_str', $centralize_array['endtime_local'] );

			$timezone_name = isset( $centralize_array['timezone_name'] ) ? $centralize_array['timezone_name'] : 'Africa/Abidjan';
			update_post_meta( $new_event_id, '_EventTimezone', $timezone_name );
			
			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				$append = apply_filters('wpea_taxonomy_terms_append', false, $wpea_cats, $this->taxonomy, $centralize_array['origin'] );
				wp_set_object_terms( $new_event_id, $wpea_cats, $this->taxonomy, $append );
			}

			$event_featured_image  = $centralize_array['image_url'];
			if( $event_featured_image != '' ){
				$importevents->common->setup_featured_image_to_event( $new_event_id, $event_featured_image );
			}

			//Insert in Custom Table 
			$esource_id     = $centralize_array['ID'];
			$start_time     = gmdate( 'Y-m-d H:i:s', $centralize_array['starttime_local'] );
			$end_time       = gmdate( 'Y-m-d H:i:s', $centralize_array['endtime_local'] );
			
			if( $centralize_array['origin'] == 'ical' ){
				$start_date_utc = $allmetas['_EventStartDateUTC'];
				$end_date_utc   = $allmetas['_EventEndDateUTC'];
			}else{
				$start_date_utc = date( 'Y-m-d H:i:s', (int) $allmetas['_EventStartDateUTC'] );
				$end_date_utc   = date( 'Y-m-d H:i:s', (int) $allmetas['_EventEndDateUTC'] );
			}

			$timezone       = isset( $allmetas['timezone'] ) ? $allmetas['timezone'] : 'UTC';
			$duration       = 0;
			$hash           = sha1( $new_event_id . $duration . $start_time . $end_time . $start_date_utc . $end_date_utc . $timezone );

			// insert the $wpdb->prefix.tec_events table
			$tetable_name   = $wpdb->prefix . 'tec_events';
			$tedata         = array( 'post_id'   => $new_event_id, 'start_date' => $start_time, 'end_date'  => $end_time, 'timezone'  => $timezone, 'start_date_utc' => $start_date_utc, 'end_date_utc' => $end_date_utc );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->insert( $tetable_name, $tedata );
			$tec_e_id       = $wpdb->insert_id;

			// Update the $wpdb->prefix.tec_occurrences table
			$totable_name   = $wpdb->prefix . 'tec_occurrences';
			$todata         = array( 'event_id' => $tec_e_id, 'post_id' => $new_event_id, 'start_date' => $start_time, 'start_date_utc' => $start_date_utc, 'end_date' => $end_time, 'end_date_utc' => $end_date_utc, 'hash' => $hash );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->insert( $totable_name, $todata );

			do_action( 'wpea_after_create_tec_' . $centralize_array['origin'] . '_event', $new_event_id, $formated_args, $centralize_array );
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
		global $importevents, $wpdb;

		$event_title   = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$event_content = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$event_status  = $formated_args['post_status'];
		$event_author  = $formated_args['post_author'];
		
		$tec_event     = array(
			'ID'           => $event_id,
			'post_title'   => $event_title,
			'post_content' => $event_content,
			'post_status'  => $event_status,
			'post_author'  => $event_author,
			'post_type'    => $this->event_posttype,
		);
		
		$update_event_id = wp_update_post( $tec_event, true );

		if ( $update_event_id ) {

			//update all metadata
			$allmetas = $this->format_event_args_for_tec( $centralize_array );
			if( !empty( $allmetas ) ){
				foreach( $allmetas as $key => $value ){
					if( !empty( $value ) ){
						update_post_meta( $update_event_id, $key, $value );
					}
				}
			}

			update_post_meta( $update_event_id, 'wpea_event_id',  $centralize_array['ID'] );
			update_post_meta( $update_event_id, 'wpea_event_origin',  $event_args['import_origin'] );
			update_post_meta( $update_event_id, 'wpea_event_link', esc_url( $centralize_array['url'] ) );
			update_post_meta( $update_event_id, '_wpea_starttime_str', $centralize_array['starttime_local'] );
			update_post_meta( $update_event_id, '_wpea_endtime_str', $centralize_array['endtime_local'] );
			
			delete_post_meta( $update_event_id, '_tribe_is_classic_editor' );
			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? (array) $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				if ( $importevents->common->wpea_is_updatable('category') ){
					$append = apply_filters('wpea_taxonomy_terms_append', false, $wpea_cats, $this->taxonomy, $centralize_array['origin'] );
					wp_set_object_terms( $update_event_id, $wpea_cats, $this->taxonomy, $append );
				}
			}

			$event_featured_image  = $centralize_array['image_url'];
			if( $event_featured_image != '' ){
				$importevents->common->setup_featured_image_to_event( $update_event_id, $event_featured_image );
			}else{
				if( has_post_thumbnail( $update_event_id ) ){
					$attachment_id = get_post_thumbnail_id( $update_event_id );
					$imagemeta = get_post_meta( $attachment_id, '_wpea_attachment_source', true );
					if( !empty( $imagemeta ) ){
						delete_post_thumbnail( $update_event_id );
					}
				}
			}


			//Update in Custom Table 
			$esource_id     = $centralize_array['ID'];
			$start_time     = gmdate( 'Y-m-d H:i:s', $centralize_array['starttime_local'] );
			$end_time       = gmdate( 'Y-m-d H:i:s', $centralize_array['endtime_local'] );

			if( $centralize_array['origin'] == 'ical' ){
				$start_date_utc = $allmetas['_EventStartDateUTC'];
				$end_date_utc   = $allmetas['_EventEndDateUTC'];
			}else{
				$start_date_utc = date( 'Y-m-d H:i:s', (int) $allmetas['_EventStartDateUTC'] );
				$end_date_utc   = date( 'Y-m-d H:i:s', (int)$allmetas['_EventEndDateUTC'] );
			}
			
			$timezone       = isset( $allmetas['timezone'] ) ? $allmetas['timezone'] : 'UTC';

			// Update the $wpdb->prefix.tec_events table
			$tetable_name   = $wpdb->prefix . 'tec_events';
			$tedata         = array( 'start_date' => $start_time, 'end_date' => $end_time, 'timezone' => $timezone, 'start_date_utc' => $start_date_utc, 'end_date_utc' => $end_date_utc );
			$where          = array( 'post_id' => $update_event_id );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->update( $tetable_name, $tedata, $where );

			//update the $wpdb->prefix.tec_occurrences table
			$totable_name   = $wpdb->prefix . 'tec_occurrences';
			$todata         = array( 'start_date' => $start_time, 'start_date_utc' => $start_date_utc, 'end_date' => $end_time, 'end_date_utc' => $end_date_utc  );
			$where          = array( 'post_id' => $update_event_id );
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$wpdb->update( $totable_name, $todata, $where );


			do_action( 'wpea_after_update_tec_' . $centralize_array['origin'] . '_event', $update_event_id, $formated_args, $centralize_array );
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
	 * @param array $centralize_array WP Events event.
	 * @return array
	 */
	public function format_event_args_for_tec( $centralize_array ) {

		if( empty( $centralize_array ) ){
			return;
		}
		$start_time      = $centralize_array['starttime_local'];
		$end_time        = $centralize_array['endtime_local'];
		$timezone        = isset( $centralize_array['timezone'] ) ? sanitize_text_field( $centralize_array['timezone'] ) : 'UTC';
		$timezone_name   = isset( $centralize_array['timezone_name'] ) ? $centralize_array['timezone_name'] : 'Africa/Abidjan';
		$esource_url     = isset( $centralize_array['url'] ) ? esc_url( $centralize_array['url'] ) : '';
		$esource_id      = $centralize_array['ID'];
		$start_time_utc = isset( $centralize_array['startime_utc'] ) ? gmdate( 'Y-m-d H:i:s', (int) $centralize_array['startime_utc'] ) : '';
		$end_time_utc    = isset( $centralize_array['endtime_utc'] ) ? gmdate( 'Y-m-d H:i:s', (int) $centralize_array['endtime_utc'] ) : '';

		$event_args = array(
			'_EventStartDate'         => gmdate( 'Y-m-d H:i:s', $start_time ),
			'_EventStartHour'         => gmdate( 'h', $start_time ),
			'_EventStartMinute'       => gmdate( 'i', $start_time ),
			'_EventStartMeridian'     => gmdate( 'a', $start_time ),
			'_EventEndDate'           => gmdate( 'Y-m-d H:i:s', $end_time ),
			'_EventEndHour'           => gmdate( 'h', $end_time ),
			'_EventEndMinute'         => gmdate( 'i', $end_time ),
			'_EventEndMeridian'       => gmdate( 'a', $end_time ),
			'_EventStartDateUTC'      => !empty( $start_time_utc ) ? gmdate( 'Y-m-d H:i:s', (int) $start_time_utc ) : '',
			'_EventEndDateUTC'        => !empty( $end_time_utc ) ? gmdate( 'Y-m-d H:i:s', (int) $end_time_utc ) : '',
			'_EventURL'               => $centralize_array['url'],
			'_EventShowMap'           => 1,
			'_EventShowMapLink'       => 1,
			'_EventTimezone'          => $timezone_name,
			'wpea_event_timezone'      => $timezone,
			'wpea_event_link'          => $esource_url,
			'wpea_event_timezone_name' => $timezone_name,
		);

		if( isset( $centralize_array['is_all_day'] ) && true === $centralize_array['is_all_day'] ){
			$event_args['_EventAllDay']      = 'yes';
		}

		if ( array_key_exists( 'organizer', $centralize_array ) ) {
			$get_organizer = $this->get_organizer_args( $centralize_array['organizer'] );
			$event_args['_EventOrganizerID'] = $get_organizer['OrganizerID'];
		}

		if ( array_key_exists( 'location', $centralize_array ) ) {
			$get_location = $this->get_venue_args( $centralize_array['location'] );
			$event_args['_EventVenueID'] = $get_location['VenueID'];
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

		$organizer_id = isset( $centralize_org_array['ID'] ) ? $centralize_org_array['ID'] : '';
		if( !empty( $organizer_id ) && $organizer_id != 'noreply@facebookmail.com' ){
			if( $organizer_id != 'noreply@facebookmail_com' ){
				$existing_organizer = $this->get_organizer_by_id( $organizer_id );
			}
		}
		if( empty( $existing_organizer ) ){
			$organizer_name = str_replace( '\\', '', $centralize_org_array['name'] );
			$existing_organizer = $this->get_organizer_by_name( $organizer_name );
		}
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
			update_post_meta( $create_organizer, 'wpea_event_organizer_name', $centralize_org_array['name'] );
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

		if( empty( $venue ) ){
			return false;
		}
		$venue_id = !empty( $venue['ID'] ) ? $venue['ID'] : '';
		if( !empty( $venue['ID'] ) ){
			$existing_venue = $this->get_venue_by_id( $venue_id );
		}
		if( empty( $existing_venue ) ){
			$existing_venue = $this->get_venue_by_name( $venue['name'] );
		}
		if ( $existing_venue && is_numeric( $existing_venue ) && $existing_venue > 0 ) {
			return array(
				'VenueID' => $existing_venue,
			);
		}

		$country = isset( $venue['country'] ) ? $venue['country'] : '';
		$address_1 = isset( $venue['address_1'] ) ? $venue['address_1'] : '';
		if( strlen( $country ) > 2 && $country != '' ){
			$country = $importevents->common->wpea_get_country_code( $country );
		}
		$create_venue = tribe_create_venue( array(
			'Venue'   => isset( $venue['name'] ) ? $venue['name'] : '',
			'Address' => isset( $venue['full_address'] ) ? $venue['full_address'] : $address_1,
			'City'    => isset( $venue['city'] ) ? $venue['city'] : '',
			'State'   => isset( $venue['state'] ) ? $venue['state'] : '',
			'Country' => $country,
			'Zip'     => isset( $venue['zip'] ) ? $venue['zip'] : '',
			'Phone'   => isset( $venue['phone'] ) ? $venue['phone'] : '',
			'ShowMap' => true,
			'ShowMapLink' => true,
		) );

		if ( $create_venue ) {
			update_post_meta( $create_venue, 'wpea_event_venue_name', $venue['name'] );
			update_post_meta( $create_venue, 'wpea_event_venue_id', $venue_id );
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
	 * Check for Existing TEC Organizer
	 *
	 * @since    1.0.0
	 * @param int $organizer_id Organizer id.
	 * @return int/boolean
	 */
	public function get_organizer_by_name( $organizer_name ) {
		$existing_organizer = get_posts( array(
			'posts_per_page' => 1,
			'post_type' => $this->oraganizer_posttype,
			'meta_key' => 'wpea_event_organizer_name',
			'meta_value' => $organizer_name,
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

	/**
	 * Check for Existing TEC Venue Name
	 *
	 * @since    1.0.0
	 * @param int $venue_name Venue Name.
	 * @return int/boolean
	 */
	public function get_venue_by_name( $venue_name ) {
		$existing_organizer = get_posts(
			array(
				'posts_per_page'   => 1,
				'post_type'        => $this->venue_posttype,
				'meta_key'         => 'wpea_event_venue_name', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Ignore.
				'meta_value'       => $venue_name, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- Ignore.
				'suppress_filters' => false,
			)
		);

		if ( is_array( $existing_organizer ) && ! empty( $existing_organizer ) ) {
			return $existing_organizer[0]->ID;
		}
		return false;
	}

}
