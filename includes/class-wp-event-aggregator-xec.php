<?php
/**
 * Class for Import Events into Xylus Events Calendar (Easy Events Calendar)
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Event_Aggregator_XEC {

	// Xylus Events Calendar Event Taxonomy
	protected $taxonomy;

	// Xylus Events Calendar Event Posttype
	protected $event_posttype;

	// Xylus Events Calendar Venue Taxonomy
	protected $venue_taxonomy;

	// Xylus Events Calendar Organizer Taxonomy
	protected $organizer_taxonomy;

	// Xylus Events Calendar Organizer Taxonomy
	protected $tag_taxonomy;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->taxonomy           = 'eec_category';
		$this->tag_taxonomy       = 'eec_tag';
		$this->event_posttype     = 'eec_events';
		$this->venue_taxonomy     = 'eec_venue';
		$this->organizer_taxonomy = 'eec_organizer';

	}
	/**
	 * Get Posttype and Taxonomy Functions
	 *
	 * @return string
	 */
	public function get_event_posttype() {
		return $this->event_posttype;
	}
	public function get_organizer_posttype() {
		return $this->organizer_taxonomy;
	}
	public function get_venue_posttype() {
		return $this->venue_taxonomy;
	}
	public function get_tags() {
		return $this->tag_taxonomy;
	}
	public function get_taxonomy() {
		return $this->taxonomy;
	}

	/**
	 * import event into XEC
	 *
	 * @since    1.0.0
	 * @param  array $centralize_array event array.
	 * @return array
	 */
	public function import_event( $centralize_array, $event_args ) {
		global $importevents;

		$is_exitsing_event = $importevents->common->get_event_by_event_id( $this->event_posttype, $centralize_array );

		$formated_args = array();
		if ( $is_exitsing_event && is_numeric( $is_exitsing_event ) && $is_exitsing_event > 0 ) {

			$options       = wpea_get_import_options( $centralize_array['origin'] );
			$update_events = isset( $options['update_events'] ) ? $options['update_events'] : 'no';
			$skip_trash    = isset( $options['skip_trash'] ) ? $options['skip_trash'] : 'no';
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
			} else {

				return array(
					'status' => 'skipped',
					'id'     => $is_exitsing_event,
				);
			}
		} else {

			if ( isset( $event_args['event_status'] ) && ! empty( $event_args['event_status'] ) ) {
				$formated_args['post_status'] = $event_args['event_status'];
			}
			$formated_args['post_author'] = isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id();

			if ( ! $importevents->common->wpea_is_updatable( 'status' ) ) {
				$formated_args['post_status'] = 'publish'; // Default for new events if not specified
			}

			return $this->create_event( $centralize_array, $formated_args, $event_args );
		}

	}

	/**
	 * Create New XEC event.
	 *
	 * @since    1.0.0
	 * @param array $centralize_array Event array.
	 * @param array $formated_args Formated arguments for event.
	 * @param array $event_args Event arguments.
	 * @return array
	 */
	public function create_event( $centralize_array = array(), $formated_args = array(), $event_args = array() ) {
		global $importevents ,$wpdb;
	
		$event_title   = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$event_content = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$event_status  = $formated_args['post_status'];
		$event_author  = $formated_args['post_author'];
		$event_content = $importevents->htmltoblock->convert( $event_content );
		
		$xec_event     = array(
			'post_title'   => $event_title,
			'post_content' => $event_content,
			'post_status'  => $event_status,
			'post_author'  => $event_author,
			'post_type'    => $this->event_posttype,
		);
		
		$new_event_id = wp_insert_post( $xec_event, true );

		if ( $new_event_id && !is_wp_error( $new_event_id ) ) {
			
			//update all metadata
			$allmetas = $this->format_event_args_for_xec( $centralize_array );
			if( !empty( $allmetas ) ){
				foreach( $allmetas as $key => $value ){
					update_post_meta( $new_event_id, $key, $value );
				}
			}

			update_post_meta( $new_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $new_event_id, 'wpea_event_id', $centralize_array['ID'] );

			// Series id
			$series_id   = isset( $centralize_array['series_id'] ) ? $centralize_array['series_id'] : '';			
			if( !empty( $series_id ) ){
				update_post_meta( $new_event_id, 'series_id', $series_id );
			}

			// Assign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
				wp_set_object_terms( $new_event_id, $wpea_cats, $this->taxonomy );
			}

			// Assign event tag.
			$wpea_tags = isset( $event_args['event_tags'] ) ? $event_args['event_tags'] : array();
			if ( ! empty( $wpea_tags ) ) {
				foreach ( $wpea_tags as $wpea_tagk => $wpea_tagv ) {
					$wpea_tags[ $wpea_tagk ] = (int) $wpea_tagv;
				}
				wp_set_object_terms( $new_event_id, $wpea_tags, $this->tag_taxonomy );
			}

			// Handle Location/Venue
			if ( array_key_exists( 'location', $centralize_array ) ) {
				$this->get_venue_args( $new_event_id, $centralize_array['location'] );
			}

			// Handle Organizer
			if ( array_key_exists( 'organizer', $centralize_array ) ) {
				$this->get_organizer_args( $new_event_id, $centralize_array['organizer'] );
			}

			$event_featured_image = $centralize_array['image_url'];
			if ( $event_featured_image != '' ) {
				$importevents->common->wpea_set_feature_image_logic( $new_event_id, $event_featured_image, $event_args );
			}

			// Handle Instances
			update_post_meta( $new_event_id, 'event_recurrence_type', 'none' );
			update_post_meta( $new_event_id, 'event_recurrence_interval', 1 );
			update_post_meta( $new_event_id, 'event_recurrence_end_type', 'never' );
			$this->add_occurrence_instance( $new_event_id, $centralize_array );

			do_action( 'wpea_after_create_xec_' . $centralize_array['origin'] . '_event', $new_event_id, $formated_args, $centralize_array );
			return array(
				'status' => 'created',
				'id'     => $new_event_id,
			);

		} else {
			return;
		}
	}


	/**
	 * Update XEC event.
	 *
	 * @since 1.0.0
	 * @param int $event_id Post id.
	 * @param array $centralize_array Event array.
	 * @param array $formated_args Formated arguments for event.
	 * @param array $event_args Event arguments.
	 * @return array
	 */
	public function update_event( $event_id, $centralize_array, $formated_args = array(), $event_args = array() ) {
		global $importevents, $wpdb;

		$event_title   = isset( $centralize_array['name'] ) ? $centralize_array['name'] : '';
		$event_content = isset( $centralize_array['description'] ) ? $centralize_array['description'] : '';
		$event_status  = $formated_args['post_status'];
		$event_author  = $formated_args['post_author'];
		$event_content = $importevents->htmltoblock->convert( $event_content );
		
		$xec_event     = array(
			'ID'           => $event_id,
			'post_title'   => $event_title,
			'post_content' => $event_content,
			'post_status'  => $event_status,
			'post_author'  => $event_author,
			'post_type'    => $this->event_posttype,
		);
		
		$update_event_id = wp_update_post( $xec_event, true );
		if ( $update_event_id && !is_wp_error( $update_event_id ) ) {

			//update all metadata
			$allmetas = $this->format_event_args_for_xec( $centralize_array );
			if( !empty( $allmetas ) ){
				$series_id      = isset( $centralize_array['series_id'] ) ? $centralize_array['series_id'] : '';
				$current_start  = get_post_meta( $update_event_id, 'start_ts', true );
				$is_date_meta   = array( 'event_start_date', 'event_start_hour', 'event_start_minute', 'event_start_meridian', 'event_end_date', 'event_end_hour', 'event_end_minute', 'event_end_meridian', 'start_ts', 'end_ts' );

				foreach( $allmetas as $key => $value ){
					if ( $importevents->common->wpea_is_updatable( $key ) ) {
						// For series, do not overwrite the main event date if it's already set (prevents last occurrence from winning)
						if ( !empty( $series_id ) && !empty( $current_start ) && in_array( $key, $is_date_meta ) ) {
							continue;
						}
						update_post_meta( $update_event_id, $key, $value );
					}
				}
			}

			update_post_meta( $update_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $update_event_id, 'wpea_event_id', $centralize_array['ID'] );

			// Series id
			$series_id   = isset( $centralize_array['series_id'] ) ? $centralize_array['series_id'] : '';			
			if( !empty( $series_id ) ){
				update_post_meta( $update_event_id, 'series_id', $series_id );
			}

			// Assign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? (array) $event_args['event_cats'] : array();
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
				if ( $importevents->common->wpea_is_updatable('category') ){
					wp_set_object_terms( $update_event_id, $wpea_cats, $this->taxonomy );
				}
			}

			// Assign event tag.
			$wpea_tags = isset( $event_args['event_tags'] ) ? $event_args['event_tags'] : array();
			if ( ! empty( $wpea_tags ) ) {
				foreach ( $wpea_tags as $wpea_tagk => $wpea_tagv ) {
					$wpea_tags[ $wpea_tagk ] = (int) $wpea_tagv;
				}
				if ( $importevents->common->wpea_is_updatable( 'category' ) ) {
					wp_set_object_terms( $update_event_id, $wpea_tags, $this->tag_taxonomy );
				}
			}

			// Handle Location/Venue
			if ( array_key_exists( 'location', $centralize_array ) ) {
				$this->get_venue_args( $update_event_id, $centralize_array['location'] );
			}

			// Handle Organizer
			if ( array_key_exists( 'organizer', $centralize_array ) ) {
				$this->get_organizer_args( $update_event_id, $centralize_array['organizer'] );
			}

			$event_featured_image = $centralize_array['image_url'];
			if ( $event_featured_image != '' ) {
				$importevents->common->wpea_set_feature_image_logic( $update_event_id, $event_featured_image, $event_args );
			} else {
				delete_post_thumbnail( $update_event_id );
			}

			update_post_meta( $update_event_id, 'event_recurrence_type', 'none' );
			update_post_meta( $update_event_id, 'event_recurrence_interval', 1 );
			update_post_meta( $update_event_id, 'event_recurrence_end_type', 'never' );
			$this->add_occurrence_instance( $update_event_id, $centralize_array );

			do_action( 'wpea_after_update_xec_' . $centralize_array['origin'] . '_event', $update_event_id, $formated_args, $centralize_array );
			return array(
				'status' => 'updated',
				'id'     => $update_event_id,
			);
		} else {
			return;
		}
	}

	/**
	 * Format events arguments as per XEC
	 *
	 * @since    1.0.0
	 * @param array $centralize_array Event array.
	 * @return array
	 */
	public function format_event_args_for_xec( $centralize_array ) {

		if ( empty( $centralize_array ) ) {
			return;
		}
		$start_time    = $centralize_array['starttime_local'];
		$end_time      = $centralize_array['endtime_local'];
		
		$event_args = array(
			'event_start_date'     => gmdate( 'Y-m-d', $start_time ),
			'event_start_hour'     => gmdate( 'h', $start_time ),
			'event_start_minute'   => gmdate( 'i', $start_time ),
			'event_start_meridian' => gmdate( 'a', $start_time ),
			'event_end_date'       => gmdate( 'Y-m-d', $end_time ),
			'event_end_hour'       => gmdate( 'h', $end_time ),
			'event_end_minute'     => gmdate( 'i', $end_time ),
			'event_end_meridian'   => gmdate( 'a', $end_time ),
			'start_ts'             => $start_time,
			'end_ts'               => $end_time,
			'eec_event_link'       => $centralize_array['url'],
		);

		return $event_args;
	}

	/**
	 * Get organizer args for event.
	 *
	 * @since    1.0.0
	 * @param int   $post_id Event post ID.
	 * @param array $organizer Organizer array.
	 * @return void
	 */
	public function get_organizer_args( $post_id, $organizer ) {

		if ( ! isset( $organizer['ID'] ) && ! isset( $organizer['name'] ) ) {
			return;
		}

		$organizer_name = isset( $organizer['name'] ) ? $organizer['name'] : '';
		if ( empty( $organizer_name ) ) {
			return;
		}

		$term = get_term_by( 'name', $organizer_name, $this->organizer_taxonomy );

		if ( ! $term ) {
			$term = wp_insert_term( $organizer_name, $this->organizer_taxonomy );
			if ( is_wp_error( $term ) ) {
				return;
			}
			$term_id = $term['term_id'];
		} else {
			$term_id = $term->term_id;
		}

		if ( $term_id ) {
			wp_set_object_terms( $post_id, array( (int) $term_id ), $this->organizer_taxonomy );
			
			// Update term meta
			update_term_meta( $term_id, 'organizer_email', isset( $organizer['email'] ) ? $organizer['email'] : '' );
			update_term_meta( $term_id, 'organizer_phone', isset( $organizer['phone'] ) ? $organizer['phone'] : '' );
		}
	}

	/**
	 * Get venue args for event
	 *
	 * @since    1.0.0
	 * @param int   $post_id Event post ID.
	 * @param array $venue Venue array.
	 * @return void
	 */
	public function get_venue_args( $post_id, $venue ) {
		if ( ! isset( $venue['ID'] ) && ! isset( $venue['name'] ) ) {
			return;
		}

		$venue_name = isset( $venue['name'] ) ? $venue['name'] : '';
		if ( empty( $venue_name ) ) {
			return;
		}

		$term = get_term_by( 'name', $venue_name, $this->venue_taxonomy );

		if ( ! $term ) {
			$term = wp_insert_term( $venue_name, $this->venue_taxonomy );
			if ( is_wp_error( $term ) ) {
				return;
			}
			$term_id = $term['term_id'];
		} else {
			$term_id = $term->term_id;
		}

		if ( $term_id ) {
			wp_set_object_terms( $post_id, array( (int) $term_id ), $this->venue_taxonomy );
			
			// Update term meta
			update_term_meta( $term_id, 'venue_full_address', isset( $venue['full_address'] ) ? $venue['full_address'] : (isset($venue['address_1']) ? $venue['address_1'] : '') );
			update_term_meta( $term_id, 'venue_address1', isset( $venue['address_1'] ) ? $venue['address_1'] : '' );
			update_term_meta( $term_id, 'venue_city', isset( $venue['city'] ) ? $venue['city'] : '' );
			update_term_meta( $term_id, 'venue_state', isset( $venue['state'] ) ? $venue['state'] : '' );
			update_term_meta( $term_id, 'venue_country', isset( $venue['country'] ) ? $venue['country'] : '' );
			update_term_meta( $term_id, 'venue_zip', isset( $venue['zip'] ) ? $venue['zip'] : '' );
			update_term_meta( $term_id, 'venue_latitude', isset( $venue['lat'] ) ? $venue['lat'] : '' );
			update_term_meta( $term_id, 'venue_longitude', isset( $venue['long'] ) ? $venue['long'] : '' );
		}
	}

	/**
	 * Add occurrence instance to XEC custom table.
	 *
	 * @since    1.8.0
	 * @param int   $post_id Event post ID.
	 * @param array $centralize_array Event array.
	 * @return void
	 */
	public function add_occurrence_instance( $post_id, $centralize_array ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'eec_event_instances';
		
		$start_time = $centralize_array['starttime_local'];
		$end_time   = $centralize_array['endtime_local'];
		
		$start_dt = gmdate( 'Y-m-d H:i:s', $start_time );
		$end_dt   = gmdate( 'Y-m-d H:i:s', $end_time );
		
		// Check if instance already exists
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table_name} WHERE event_id = %d AND start_date = %s", $post_id, $start_dt ) );
		
		if ( ! $exists ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$table_name,
				array(
					'event_id'      => $post_id,
					'start_date'    => $start_dt,
					'end_date'      => $end_dt,
					'is_recurrence' => 1
				),
				array( '%d', '%s', '%s', '%d' )
			);
		}
	}

}
