<?php
/**
 * Class for Import Events into EventPrime
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

class WP_Event_Aggregator_EventPrime {

	// The Events Calendar Event Taxonomy
	protected $taxonomy;

	// The Events Calendar Event Posttype
	protected $event_posttype;

	// The Events Calendar Location Taxonomy
	protected $location_taxonomy;

	// The Events Calendar Location Taxonomy
	protected $organizer_taxonomy;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->taxonomy           = 'em_event_type';
		$this->event_posttype     = 'em_event';
		$this->location_taxonomy  = 'em_venue';
		$this->organizer_taxonomy = 'em_event_organizer';
	}

	/**
	 * Get Posttype and Taxonomy Functions
	 *
	 * @return string
	 */
	public function get_event_posttype() {
		return $this->event_posttype;
	}
	public function get_location_taxonomy() {
		return $this->location_taxonomy;
	}
	public function get_organizer_taxonomy() {
		return $this->organizer_taxonomy;
	}
	public function get_taxonomy() {
		return $this->taxonomy;
	}

	/**
	 * import event into TEC
	 *
	 * @since    1.0.0
	 * @param  array $centralize_array event array.
	 * @return array
	 */
	public function import_event( $centralize_array, $event_args ) {
		global $wpdb, $importevents;

		if ( empty( $centralize_array ) || ! isset( $centralize_array['ID'] ) ) {
			return false;
		}

		$is_exitsing_event = $importevents->common->get_event_by_event_id( $this->event_posttype, $centralize_array );

		if ( $is_exitsing_event ) {
			// Update event or not?
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
			if ( 'yes' != $update_events ) {
				return array(
					'status' => 'skipped',
					'id'     => $is_exitsing_event,
				);
			}
		}

		$origin_event_id  = $centralize_array['ID'];
		$post_title       = isset( $centralize_array['name'] ) ? convert_chars( stripslashes( $centralize_array['name'] ) ) : '';
		$post_description = isset( $centralize_array['description'] ) ? wpautop( convert_chars( stripslashes( $centralize_array['description'] ) ) ) : '';
		$start_time       = $centralize_array['starttime_local'];
		$end_time         = $centralize_array['endtime_local'];
		$ticket_uri       = $centralize_array['url'];

		$evon_eventdata = array(
			'post_title'   => $post_title,
			'post_content' => $post_description,
			'post_type'    => $this->event_posttype,
			'post_status'  => 'pending',
			'post_author'  => isset($event_args['event_author']) ? $event_args['event_author'] : get_current_user_id()
		);
		if ( $is_exitsing_event ) {
			$evon_eventdata['ID'] = $is_exitsing_event;
		}
		if ( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ) {
			$evon_eventdata['post_status'] = $event_args['event_status'];
		}

		if ( $is_exitsing_event && ! $importevents->common->wpea_is_updatable('status') ) {
			$evon_eventdata['post_status'] = get_post_status( $is_exitsing_event );
		}
		$inserted_event_id = wp_insert_post( $evon_eventdata, true );

		if ( ! is_wp_error( $inserted_event_id ) ) {
			$inserted_event = get_post( $inserted_event_id );
			if ( empty( $inserted_event ) ) {
				return '';}

			//Event ID
			update_post_meta( $inserted_event_id, 'wpea_event_id', $centralize_array['ID'] );

			// Asign event category.
			$wpea_cats = isset( $event_args['event_cats'] ) ? $event_args['event_cats'] : array();
			$category  = isset( $centralize_array['category'] ) ? $centralize_array['category'] : '';
			if ( ! empty( $category ) ) {
				$cat_id = $importevents->common->wpea_check_category_exists( $category, $this->taxonomy );

				if ( $cat_id ) {
					$wpea_cats[] = (int) $cat_id;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				foreach ( $wpea_cats as $wpea_catk => $wpea_catv ) {
					$wpea_cats[ $wpea_catk ] = (int) $wpea_catv;
				}
			}
			if ( ! empty( $wpea_cats ) ) {
				if (!($is_exitsing_event && ! $importevents->common->wpea_is_updatable('category') )) {
					wp_set_object_terms( $inserted_event_id, $wpea_cats, $this->taxonomy );
				}
			}

			// Assign Featured images
			$event_image = $centralize_array['image_url'];
			if ( ! empty( $event_image ) ) {
				$importevents->common->wpea_set_feature_image_logic( $inserted_event_id, $event_image, $event_args );
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
			$address = !empty( $centralize_array['location']['address_1'] ) ? $centralize_array['location']['address_1'] : '';
			if ( isset( $centralize_array['location']['full_address'] ) && !empty( $centralize_array['location']['full_address'] ) ) {
				$address = $centralize_array['location']['full_address'];
			}

			//Timezone
			$timezone      = isset( $centralize_array['timezone'] ) ? $centralize_array['timezone'] : '';
			$is_all_day    = isset( $centralize_array['is_all_day'] ) ? $centralize_array['is_all_day'] : '';

			update_post_meta( $inserted_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, 'wpea_event_link', $centralize_array['url'] );
			update_post_meta( $inserted_event_id, 'em_event_type', end( $wpea_cats ) );
			update_post_meta( $inserted_event_id, 'em_name', $post_title );
			update_post_meta( $inserted_event_id, 'em_performer', '' );
			update_post_meta( $inserted_event_id, 'em_hide_event_start_time', '0' );
			update_post_meta( $inserted_event_id, 'em_hide_event_start_date', '0' );
			update_post_meta( $inserted_event_id, 'em_hide_event_end_time', '0' );
			update_post_meta( $inserted_event_id, 'em_hide_end_date', '0' );
			update_post_meta( $inserted_event_id, 'em_event_more_dates', '0' );
			update_post_meta( $inserted_event_id, 'em_enable_booking', '0' );
			update_post_meta( $inserted_event_id, 'em_hide_booking_status', '0' );
			update_post_meta( $inserted_event_id, 'em_allow_cancellations', '0' );
			update_post_meta( $inserted_event_id, 'em_enable_recurrence', '0' );
			update_post_meta( $inserted_event_id, 'em_recurrence_step', '0' );

			// Ticket Price
			$wpea_ticket_price    = isset( $centralize_array['ticket_price'] ) ? sanitize_text_field( $centralize_array['ticket_price'] ) : '0';
			$wpea_ticket_currency = isset( $centralize_array['ticket_currency'] ) ? sanitize_text_field( $centralize_array['ticket_currency'] ) : '';
			
			// Update Ticket Price
			update_post_meta( $inserted_event_id, 'wpea_ticket_price', $wpea_ticket_price );
			update_post_meta( $inserted_event_id, 'wpea_ticket_currency', $wpea_ticket_currency );

			// Series id
			$series_id   = isset( $centralize_array['series_id'] ) ? $centralize_array['series_id'] : '';			
			if( !empty( $series_id ) ){
				update_post_meta( $inserted_event_id, 'series_id', $series_id );
			}

            //Event Date & Time
            $start_ampm = gmdate( "h:i A", $start_time );
            $end_ampm   = gmdate( "h:i A", $end_time );

            //get only day
            $em_start_date_time = strtotime( gmdate( "Y-m-d", $start_time ) );
            $em_end_date_time = strtotime( gmdate( "Y-m-d", $end_time ) );

			update_post_meta( $inserted_event_id, 'em_start_date', $start_time );
            update_post_meta( $inserted_event_id, 'em_start_time', $start_ampm );
			update_post_meta( $inserted_event_id, 'em_end_date', $end_time );
            update_post_meta( $inserted_event_id, 'em_end_time', $end_ampm );
            update_post_meta( $inserted_event_id, 'em_start_date_time', $em_start_date_time );
            update_post_meta( $inserted_event_id, 'em_end_date_time', $em_end_date_time );

			if( !empty( $is_all_day ) ){
				update_post_meta( $inserted_event_id, 'em_all_day', '1' );
			}

			// Update post meta fields
			if ( isset( $centralize_array['location']['name'] ) && !empty( $centralize_array['location']['name'] ) ) {
				$loc_term = term_exists( $centralize_array['location']['name'], $this->location_taxonomy );
				if ( $loc_term !== 0 && $loc_term !== null ) {
					if ( is_array( $loc_term ) ) {
						$loc_term_id = (int) $loc_term['term_id'];
					}
				} else {
					$new_loc_term = wp_insert_term(
						$centralize_array['location']['name'],
						$this->location_taxonomy
					);
					if ( ! is_wp_error( $new_loc_term ) ) {
						$loc_term_id = (int) $new_loc_term['term_id'];
					}
				}

				// latitude and longitude
				$loc_term_meta                        = array();
				$loc_term_meta['location_lon']        = ( ! empty( $centralize_array['location']['long'] ) ) ? $centralize_array['location']['long'] : null;
				$loc_term_meta['location_lat']        = ( ! empty( $centralize_array['location']['lat'] ) ) ? $centralize_array['location']['lat'] : null;
				$loc_term_meta['evcal_location_link'] = ( isset( $centralize_array['location']['url'] ) ) ? $centralize_array['location']['url'] : null;
				$loc_term_meta['location_address']    = $address;
				$loc_term_meta['evo_loc_img']         = ( isset( $centralize_array['location']['image_url'] ) ) ? $centralize_array['location']['image_url'] : null;

				$term_loc_ids = wp_set_object_terms( $inserted_event_id, $loc_term_id, $this->location_taxonomy );
				update_term_meta( $loc_term_id, 'em_status',  '1' );
				update_term_meta( $loc_term_id, 'em_address',  $address );

				$loc_data = array( $loc_term_id );
				update_post_meta( $inserted_event_id, 'em_venue', $loc_data );
			}

			if ( isset( $centralize_array['organizer']['name'] ) && $centralize_array['organizer']['name'] != '' ) {

				$org_contact = $centralize_array['organizer']['phone'];
				if ( $centralize_array['organizer']['email'] != '' ) {
					$org_contact = $centralize_array['organizer']['email'];
				}
				$org_term = term_exists( $centralize_array['organizer']['name'], $this->organizer_taxonomy );
				if ( $org_term !== 0 && $org_term !== null ) {
					if ( is_array( $org_term ) ) {
						$org_term_id = (int) $org_term['term_id'];
					}
				} else {
					$new_org_term = wp_insert_term(
						$centralize_array['organizer']['name'],
						$this->organizer_taxonomy
					);
					if ( ! is_wp_error( $new_org_term ) ) {
						$org_term_id = (int) $new_org_term['term_id'];
					}
				}

				$org_term_meta                      = array();
				$org_term_meta['evcal_org_contact'] = $org_contact;
				$org_term_meta['evcal_org_address'] = null;
				$org_term_meta['evo_org_img']       = ( isset( $centralize_array['organizer']['image_url'] ) ) ? $centralize_array['organizer']['image_url'] : null;
				$org_term_meta['evcal_org_exlink']  = ( isset( $centralize_array['organizer']['url'] ) ) ? $centralize_array['organizer']['url'] : null;
				$term_org_ids = wp_set_object_terms( $inserted_event_id, $org_term_id, $this->organizer_taxonomy );


                //
                $org_phone = isset( $centralize_array['organizer']['phone'] ) ? $centralize_array['organizer']['phone'] : '';
                $org_email = isset( $centralize_array['organizer']['email'] ) ? $centralize_array['organizer']['email'] : '';


				$org_data = array( 0, $org_term_id );
				update_post_meta( $inserted_event_id, 'em_organizer', $org_data );

				update_term_meta( $org_term_id, 'em_organizer_phones', $org_phone );
				update_term_meta( $org_term_id, 'em_organizer_emails', $org_email );
                update_term_meta( $org_term_id, 'em_status', '1' );
			}

			if ( $is_exitsing_event ) {
				do_action( 'wpea_after_update_event_prime_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id'     => $inserted_event_id,
				);
			} else {
				do_action( 'wpea_after_create_event_prime_' . $centralize_array['origin'] . '_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'created',
					'id'     => $inserted_event_id,
				);
			}
		} else {
			return array(
				'status'  => 0,
				'message' => 'Something went wrong, please try again.',
			);
		}
	}
}