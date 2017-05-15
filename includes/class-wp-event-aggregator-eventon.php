<?php
/**
 * Class for Import Events into EventON
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_EventON {

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
		
		$this->taxonomy = 'event_type';
		$this->event_posttype = 'ajde_events';
		$this->location_taxonomy = 'event_location';
		$this->organizer_taxonomy = 'event_organizer';
	}

	/**
	 * Get Posttype and Taxonomy Functions
	 *
	 * @return string
	 */
	public function get_event_posttype(){
		return $this->event_posttype;
	}	
	public function get_location_taxonomy(){
		return $this->location_taxonomy;
	}
	public function get_organizer_taxonomy(){
		return $this->organizer_taxonomy;
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
		$post_title = isset( $centralize_array['name'] ) ? convert_chars(stripslashes($centralize_array['name'])) : '';
		$post_description = isset( $centralize_array['description'] ) ? wpautop(convert_chars(stripslashes($centralize_array['description']))) : '';
		$start_time = $centralize_array['starttime_local'];
		$end_time = $centralize_array['endtime_local'];
		$ticket_uri = $centralize_array['url'];

		$evon_eventdata = array(
			'post_title'  => $post_title,
			'post_content' => $post_description,
			'post_type'   => $this->event_posttype,
			'post_status' => 'pending',
		);
		if ( $is_exitsing_event ) {
			$evon_eventdata['ID'] = $is_exitsing_event;
		}
		if( isset( $event_args['event_status'] ) && $event_args['event_status'] != '' ){
			$evon_eventdata['post_status'] = $event_args['event_status'];
		}
		/*echo "<pre>";
		print_r( $centralize_array );
		print_r( $evon_eventdata );
		exit();
		*/
		$inserted_event_id = wp_insert_post( $evon_eventdata, true );

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
			}
			$address = $centralize_array['location']['address_1'];
			if(  $centralize_array['location']['full_address'] != '' ){
				$address = $centralize_array['location']['full_address'];
			}
			$city = isset( $centralize_array['location']['city'] ) ? sanitize_text_field($centralize_array['location']['city']) : '';
			$state = isset( $centralize_array['location']['state'] ) ? sanitize_text_field($centralize_array['location']['state']) : '';
			$country = isset( $centralize_array['location']['country'] ) ? sanitize_text_field($centralize_array['location']['country']) : '';
			
			update_post_meta( $inserted_event_id, 'wpea_event_id', $centralize_array['ID'] );
			update_post_meta( $inserted_event_id, 'wpea_event_origin', $event_args['import_origin'] );
			update_post_meta( $inserted_event_id, 'wpea_event_link', $centralize_array['url'] );
			update_post_meta( $inserted_event_id, '_wpea_starttime_str', $start_time );
			update_post_meta( $inserted_event_id, '_wpea_endtime_str', $end_time );
			update_post_meta( $inserted_event_id, 'evcal_srow', $start_time );
			update_post_meta( $inserted_event_id, 'evcal_erow', $end_time );
			update_post_meta( $inserted_event_id, 'evcal_lmlink', $centralize_array['url'] );

			if( $centralize_array['location']['name'] != '' ){
				$loc_term = term_exists( $centralize_array['location']['name'], $this->location_taxonomy );
				if ($loc_term !== 0 && $loc_term !== null) {
				  if( is_array( $loc_term ) ){
				  	$loc_term_id = (int)$loc_term['term_id'];
				  }
				}else{
					$new_loc_term = wp_insert_term(
					  $centralize_array['location']['name'], 
					  $this->location_taxonomy
					);
					if( !is_wp_error( $new_loc_term ) ){
						$loc_term_id = (int)$new_loc_term['term_id'];
					}
				}

				// latitude and longitude
				$loc_term_meta = array();
				$loc_term_meta['location_lon'] = (!empty($centralize_array['location']['long']))? $centralize_array['location']['long']: null;
				$loc_term_meta['location_lat'] = (!empty($centralize_array['location']['lat']))? $centralize_array['location']['lat']: null;
				$loc_term_meta['evcal_location_link'] = (isset($centralize_array['location']['url']))?$centralize_array['location']['url']:null;
				$loc_term_meta['location_address'] = $address;
				$loc_term_meta['location_city'] = $city;
				$loc_term_meta['location_state'] = $state;
				$loc_term_meta['location_country'] = $country;
				$loc_term_meta['evo_loc_img'] = (isset($centralize_array['location']['image_url']))?$centralize_array['location']['image_url']:null;
				update_option("taxonomy_".$loc_term_id, $loc_term_meta);
				if( function_exists( 'evo_save_term_metas' ) ){
					evo_save_term_metas( $this->location_taxonomy, $loc_term_id, $loc_term_meta);
				}

				$term_loc_ids = wp_set_object_terms( $inserted_event_id, $loc_term_id, $this->location_taxonomy );
				update_post_meta( $inserted_event_id, 'evo_location_tax_id', $loc_term_id );
				update_post_meta( $inserted_event_id, 'evcal_location_name', $centralize_array['location']['name'] );
				update_post_meta( $inserted_event_id, 'evcal_location_link', $centralize_array['location']['url'] );
				update_post_meta( $inserted_event_id, 'evcal_location', $address );
				update_post_meta( $inserted_event_id, 'evcal_lat', $centralize_array['location']['lat'] );
			 	update_post_meta( $inserted_event_id, 'evcal_lon', $centralize_array['location']['long'] );
			 	if( $centralize_array['location']['long'] != '' && $centralize_array['location']['lat'] != '' ){
			 		update_post_meta( $inserted_event_id, 'evcal_gmap_gen', 'yes' );	
			 	}
			}

			if( $centralize_array['organizer']['name'] != '' ){

				$org_contact = $centralize_array['organizer']['phone'];
				if(  $centralize_array['organizer']['email'] != '' ){
					$org_contact = $centralize_array['organizer']['email'];
				}
				$org_term = term_exists( $centralize_array['organizer']['name'], $this->organizer_taxonomy );
				if ($org_term !== 0 && $org_term !== null) {
				  if( is_array( $org_term ) ){
				  	$org_term_id = (int)$org_term['term_id'];
				  }
				}else{
					$new_org_term = wp_insert_term(
					  $centralize_array['organizer']['name'], 
					  $this->organizer_taxonomy
					);
					if( !is_wp_error( $new_org_term ) ){
						$org_term_id = (int)$new_org_term['term_id'];
					}
				}	
				

				$org_term_meta = array();
				$org_term_meta['evcal_org_contact'] = $org_contact;
				$org_term_meta['evcal_org_address'] = null;
				$org_term_meta['evo_org_img'] 		= (isset($centralize_array['organizer']['image_url']))?$centralize_array['organizer']['image_url']:null;
				$org_term_meta['evcal_org_exlink']  = (isset($centralize_array['organizer']['url']))?$centralize_array['organizer']['url']:null;

				update_option("taxonomy_".$org_term_id, $org_term_meta);
				if( function_exists( 'evo_save_term_metas' ) ){
					evo_save_term_metas( $this->organizer_taxonomy, $org_term_id, $org_term_meta);
				}

				$term_org_ids = wp_set_object_terms( $inserted_event_id, $org_term_id, $this->organizer_taxonomy );
				update_post_meta( $inserted_event_id, 'evo_organizer_tax_id', $org_term_id );
				update_post_meta( $inserted_event_id, 'evcal_organizer', $centralize_array['organizer']['name'] );
				update_post_meta( $inserted_event_id, 'evcal_org_contact', $org_contact );
				update_post_meta( $inserted_event_id, 'evcal_org_exlink', $centralize_array['organizer']['url'] );
				update_post_meta( $inserted_event_id, 'evo_evcrd_field_org', 'no' );

			}
			
			if ( $is_exitsing_event ) {
				do_action( 'wpea_after_update_event_on_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
				return array(
					'status' => 'updated',
					'id' 	 => $inserted_event_id
				);
			}else{
				do_action( 'wpea_after_create_event_on_'.$centralize_array["origin"].'_event', $inserted_event_id, $centralize_array );
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
