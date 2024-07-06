<?php
/**
 * Class for manane Imports submissions.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Manage_Import {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'setup_success_messages' ) );
		add_action( 'admin_init', array( $this, 'handle_import_form_submit' ) , 99);
		add_action( 'admin_init', array( $this, 'handle_import_settings_submit' ), 99 );
		add_action( 'admin_init', array( $this, 'handle_listtable_oprations' ), 99 );
		add_action( 'admin_init', array( $this, 'handle_gma_settings_submit' ), 99 );
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_form_submit() {
		global $wpea_errors; 
		$event_data = array();

		if ( isset( $_POST['wpea_action'] ) && sanitize_text_field( wp_unslash( $_POST['wpea_action'] ) ) == 'wpea_import_submit' &&  check_admin_referer( 'wpea_import_form_nonce_action', 'wpea_import_form_nonce' ) ) {
			
			if( !isset( $_POST['import_origin'] ) || empty( sanitize_text_field( wp_unslash( $_POST['import_origin'] ) ) ) ) {
				$wpea_errors[] = esc_html__( 'Please Provide Import origin.', 'wp-event-aggregator' );
				return;
			}

			$event_data['import_into'] = isset( $_POST['event_plugin'] ) ? sanitize_text_field( $_POST['event_plugin']) : '';
			if( $event_data['import_into'] == '' ){
				$wpea_errors[] = esc_html__( 'Please provide Import into plugin for Event import.', 'wp-event-aggregator' );
				return;
			}
			$event_data['import_type'] = isset( $_POST['import_type'] ) ? sanitize_text_field( $_POST['import_type']) : 'onetime';
			$event_data['import_frequency'] = isset( $_POST['import_frequency'] ) ? sanitize_text_field( $_POST['import_frequency']) : 'daily';
			$event_data['event_status'] = isset( $_POST['event_status'] ) ? sanitize_text_field( $_POST['event_status']) : 'pending';
			$event_data['ical_import_by_date'] = isset( $_POST['ical_import_by_date'] ) ? sanitize_text_field( $_POST['ical_import_by_date']) : '';

			$event_data['event_cats'] = isset( $_POST['event_cats'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_cats'] ) ) : array();
			$event_data['event_cats2'] = isset( $_POST['event_cats2'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['event_cats2'] ) ) : array();

			$event_origin = sanitize_text_field( wp_unslash( $_POST['import_origin'] ) );
			switch ( $event_origin ) {
				case 'eventbrite':
					$this->handle_eventbrite_import_form_submit( $event_data );
					break;

				case 'meetup':
					$this->handle_meetup_import_form_submit( $event_data );
					break;

				case 'facebook':
					$this->handle_facebook_import_form_submit( $event_data );
					break;

				case 'ical':
					$this->handle_ical_import_form_submit( $event_data );
					break;
				
				default:
					break;
			}
		}
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_settings_submit() {
		global $wpea_errors, $wpea_success_msg;
		if ( isset( $_POST['wpea_action'] ) && sanitize_text_field( wp_unslash( $_POST['wpea_action'] ) ) == 'wpea_save_settings' &&  check_admin_referer( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ) ) {
				
			$existing_options = get_option(WPEA_OPTIONS, array());
			
			// Update only the options present in $_POST, keeping the existing values for others
			$wpea_options = array(
				'eventbrite' => isset( $_POST['eventbrite'] ) ? array_map( 'esc_attr', array_map( 'sanitize_text_field', wp_unslash( $_POST['eventbrite'] ) ) ) : ( isset( $existing_options['eventbrite'] ) ? array_map( 'esc_attr', $existing_options['eventbrite'] ) : array() ),
				'meetup'     => isset( $_POST['meetup'] ) ? array_map( 'esc_attr', array_map('sanitize_text_field', wp_unslash( $_POST['meetup'] ) ) ) : ( isset( $existing_options['meetup'] ) ? array_map( 'esc_attr', $existing_options['meetup'] ) : array() ),
				'facebook'   => isset( $_POST['facebook']) ? array_map( 'esc_attr', array_map( 'sanitize_text_field', wp_unslash( $_POST['facebook'] ) ) ) : ( isset( $existing_options['facebook'] ) ? array_map( 'esc_attr', $existing_options['facebook'] ) : array() ),
				'ical'       => isset( $_POST['ical'] ) ? array_map( 'esc_attr', array_map( 'sanitize_text_field', wp_unslash( $_POST['ical'] ) ) ) : ( isset( $existing_options['ical'] ) ? array_map('esc_attr', $existing_options['ical'] ) : array() ),
				'wpea'       => isset( $_POST['wpea'] ) ? array_map( 'esc_attr', array_map( 'sanitize_text_field', wp_unslash( $_POST['wpea'] ) ) ) : ( isset( $existing_options['wpea'] ) ? array_map('esc_attr', $existing_options['wpea'] ) : array() )
			);

			// Update the options
			$is_update = update_option( WPEA_OPTIONS, $wpea_options );
			if( $is_update ){
				$wpea_success_msg[] = __( 'Import settings has been saved successfully.', 'wp-event-aggregator' );
			}
		}
	}

	/**
	 * Delete scheduled import from list table.
	 *
	 * @since    1.0.0
	 */
	public function handle_listtable_oprations() {

		global $wpea_success_msg;
		if ( isset( $_GET['wpea_action'] ) && $_GET['wpea_action'] == 'wpea_simport_delete' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wpea_delete_import_nonce') ) {
			$import_id = $_GET['import_id'];
			$page = isset($_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'import_events';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'scheduled';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $import_id > 0 ) {
				$post_type = get_post_type( $import_id );
				if ( $post_type == 'xt_scheduled_imports' ) {
					wp_delete_post( $import_id, true );
					$query_args = array( 'imp_msg' => 'import_del', 'tab' => $tab );
        			wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
					exit;
				}
			}
		}

		if ( isset( $_GET['wpea_action'] ) && $_GET['wpea_action'] == 'wpea_history_delete' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wpea_delete_history_nonce' ) ) {
			$history_id = (int)sanitize_text_field( wp_unslash( $_GET['history_id'] ) );
			$page = isset($_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'import_events';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'history';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $history_id > 0 ) {
				wp_delete_post( $history_id, true );
				$query_args = array( 'imp_msg' => 'history_del', 'tab' => $tab );
        		wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
				exit;
			}
		}

		if ( isset( $_GET['wpea_action'] ) && $_GET['wpea_action'] == 'wpea_run_import' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wpea_run_import_nonce') ) {
			$import_id = (int)$_GET['import_id'];
			$page = isset($_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : 'import_events';
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'scheduled';
			$wp_redirect = admin_url( 'admin.php?page='.$page );
			if ( $import_id > 0 ) {
				do_action( 'xt_run_scheduled_import', $import_id );
				$query_args = array( 'imp_msg' => 'import_success', 'tab' => $tab );
        		wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
				exit;
			}
		}

		$is_bulk_delete = ( ( isset( $_GET['action'] ) && sanitize_text_field( wp_unslash( $_GET['action'] ) ) == 'delete' ) || ( isset( $_GET['action2'] ) && sanitize_text_field( wp_unslash( $_GET['action2'] ) ) == 'delete' ) );

		if ( $is_bulk_delete && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bulk-xt_scheduled_imports') ) {
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'scheduled';
			$wp_redirect = get_site_url() . urldecode( sanitize_text_field( $_REQUEST['_wp_http_referer'] ) );
        	$delete_ids = array_map( 'sanitize_text_field', $_REQUEST['xt_scheduled_import'] );
        	if( !empty( $delete_ids ) ){
        		foreach ($delete_ids as $delete_id ) {
					$timestamp = wp_next_scheduled('xt_run_scheduled_import', array( 'post_id' => (int)$delete_id ) );
					if ( $timestamp ) {
						wp_unschedule_event( $timestamp, 'xt_run_scheduled_import', array( 'post_id' => (int)$delete_id ) );
					}
        			wp_delete_post( $delete_id, true );
        		}            		
        	}
        	$query_args = array( 'imp_msg' => 'import_dels', 'tab' => $tab );
        	wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}

		if ( $is_bulk_delete && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bulk-import_histories') ) {
			$tab = isset($_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'history';
			$wp_redirect = get_site_url() . urldecode( sanitize_text_field( $_REQUEST['_wp_http_referer'] ) );        	$delete_ids = array_map( 'sanitize_text_field', $_REQUEST['import_history'] );
        	if( !empty( $delete_ids ) ){
        		foreach ($delete_ids as $delete_id ) {
        			wp_delete_post( $delete_id, true );
        		}            		
        	}	
        	$query_args = array( 'imp_msg' => 'history_dels', 'tab' => $tab );
        	wp_redirect(  add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}

		// Delete All History Data 
		if ( isset( $_GET['wpea_action'] ) && $_GET['wpea_action'] === 'wpea_all_history_delete' && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'wpea_delete_all_history_nonce' ) ) {
			$page        = isset( $_GET['page'] ) ? $_GET['page'] : 'import_events';
			$tab         = isset( $_GET['tab'] ) ? $_GET['tab'] : 'history';
			$wp_redirect = admin_url( 'admin.php?page=' . $page );
			$delete_ids  = get_posts( array( 'numberposts' => -1,'fields' => 'ids', 'post_type' => 'wpea_import_history' ) );

			if ( ! empty( $delete_ids ) ) {
				foreach ( $delete_ids as $delete_id ) {
					wp_delete_post( $delete_id, true );
				}
			}		
			$query_args = array(
				'imp_msg' => 'history_dels',
				'tab'     => $tab,
			);			
			wp_redirect( add_query_arg( $query_args, $wp_redirect ) );
			exit;
		}
	}

	/**
	 * Handle Eventbrite import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_eventbrite_import_form_submit( $event_data ){
		global $wpea_errors, $wpea_success_msg, $importevents;
		$import_events = array();
		$eventbrite_options = wpea_get_import_options('eventbrite');
		if( !isset( $eventbrite_options['oauth_token'] ) || $eventbrite_options['oauth_token'] == '' ){
			$wpea_errors[] = esc_html__( 'Please insert Eventbrite "Personal OAuth token" in settings.', 'wp-event-aggregator' );
			return;
		}

		$event_data['import_origin'] = 'eventbrite';
		$event_data['import_by'] = 'event_id';
		$eventbrite_event_id = array();
		$eventbrite_event_id[] = isset( $_POST['wpea_eventbrite_id'] ) ? sanitize_text_field( $_POST['wpea_eventbrite_id']) : '';
		$event_data['eventbrite_event_id'] = $eventbrite_event_id;
		$event_data['organizer_id'] = '';
		
		if( !is_numeric( $event_data['eventbrite_event_id'][0] ) ){
			$wpea_errors[] = esc_html__( 'Please provide valid Eventbrite event ID.', 'wp-event-aggregator' );
			return;
		}
		$import_events = $importevents->eventbrite->import_event_by_event_id( $event_data );
	
		if( $import_events && !empty( $import_events ) ){
			$importevents->common->display_import_success_message( $import_events, $event_data );
		}
	}

	/**
	 * Handle meetup import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_meetup_import_form_submit( $event_data ){
		global $wpea_errors, $wpea_success_msg, $importevents;
		

		$event_data['import_by']     = isset( $_POST['meetup_import_by'] ) ? sanitize_text_field( $_POST['meetup_import_by'] ) : '';
		$event_data['ime_event_ids'] = isset( $_POST['ime_event_ids'] ) ? array_map( 'trim', array_map( 'sanitize_text_field', explode( "\n", preg_replace( "/^\n+|^[\t\s]*\n+/m", '', wp_unslash( $_POST['ime_event_ids'] ) ) ) ) ) : array(); // input var okay.
		$event_data['meetup_url']    = isset( $_POST['meetup_url'] ) ? sanitize_text_field( $_POST['meetup_url'] ) : '';

		if ( 'group_url' === $event_data['import_by'] && !empty( $event_data['meetup_url'] ) ) {
			if ( filter_var( $event_data['meetup_url'], FILTER_VALIDATE_URL) === false ) {
				$ime_errors[] = esc_html__( 'Please provide valid Meetup group URL.', 'wp-event-aggregator' );
				return;
			}
			$event_data['meetup_url'] = esc_url( $event_data['meetup_url'] );
		}

		$import_events = $importevents->meetup->import_events( $event_data );
		if( $import_events && !empty( $import_events ) ){
			$importevents->common->display_import_success_message( $import_events, $event_data );
		}
	}

	/**
	 * Handle Facebook import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_facebook_import_form_submit( $event_data ){
		global $wpea_errors, $wpea_success_msg, $importevents;

		$fboptions = wpea_get_import_options( 'facebook' );
		$facebook_app_id = isset( $fboptions['facebook_app_id'] ) ? $fboptions['facebook_app_id'] : '';
		$facebook_app_secret = isset( $fboptions['facebook_app_secret'] ) ? $fboptions['facebook_app_secret'] : '';
		if( $facebook_app_id == '' || $facebook_app_secret == '' ){
			$wpea_errors[] = __( 'Please insert Facebook app ID and app Secret.', 'wp-event-aggregator');
			return;
		}
		
		$event_data['import_origin'] = 'facebook';
		$event_data['import_by'] = 'facebook_event_id';

		$event_data['event_ids'] = isset( $_POST['facebook_event_ids'] ) ? array_map( 'trim', array_map( 'sanitize_text_field', explode( "\n", preg_replace( "/^\n+|^[\t\s]*\n+/m", '', $_POST['facebook_event_ids'] ) ) ) ) : array();

		$event_data['page_username'] = '';

		$import_events = $importevents->facebook->import_events( $event_data );
		if( $import_events && !empty( $import_events ) ){
			$importevents->common->display_import_success_message( $import_events, $event_data );
		}
	}

	/**
	 * Handle iCal import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_ical_import_form_submit( $event_data ){
		global $wpea_errors, $wpea_success_msg, $importevents;

		$event_data['import_origin'] = 'ical';
		$event_data['import_by'] = 'ics_file';
		$event_data['ical_url'] = '';
		$event_data['ical_import_by_date'] = $_POST['ical_import_by_date'];
		$event_data['start_date'] = isset( $_POST['start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['start_date'] ) ) : '';
		$event_data['end_date'] = isset( $_POST['end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['end_date'] ) ) : '';

		if( $event_data['import_by'] == 'ics_file' ){

			$file_ext = pathinfo( $_FILES['ics_file']['name'], PATHINFO_EXTENSION );
			$file_type = $_FILES['ics_file']['type'];

			if( $file_type != 'text/calendar' && $file_ext != 'ics' ){
				$wpea_errors[] = esc_html__( 'Please upload .ics file', 'wp-event-aggregator');
				return;
			}

			$ics_content =  file_get_contents( $_FILES['ics_file']['tmp_name'] );
			$import_events = $importevents->ical->import_events_from_ics_content( $event_data, $ics_content );

			if( $import_events && !empty( $import_events ) ){
				if( $import_events && !empty( $import_events ) ){
					$importevents->common->display_import_success_message( $import_events, $event_data );
				}
			}else{
				if( empty( $wpea_errors ) ){
					$wpea_success_msg[] = esc_html__( 'Nothing to import.', 'wp-event-aggregator' );	
				}
			}

		}
	}

	/**
	 * Register Session
	 *
	 * @since    1.0.0
	 */
	public function setup_success_messages(){
		global $wpea_success_msg, $wpea_errors;
		if( isset( $_GET['imp_msg'] ) && sanitize_text_field( wp_unslash( $_GET['imp_msg'] ) ) != '' ){
			switch ( sanitize_text_field( wp_unslash( $_GET['imp_msg'] ) ) ) {
				case 'import_del':
					$wpea_success_msg[] = esc_html__( 'Scheduled import deleted successfully.', 'wp-event-aggregator' );
					break;

				case 'import_dels':
					$wpea_success_msg[] = esc_html__( 'Scheduled imports successfully deleted.', 'wp-event-aggregator' );
					break;

				case 'import_success':
					$wpea_success_msg[] = esc_html__( 'Scheduled import has been run successfully.', 'wp-event-aggregator' );
					break;

				case 'history_del':
					$wpea_success_msg[] = esc_html__( 'Import history successfully deleted.', 'wp-event-aggregator' );
					break;

				case 'history_dels':
					$wpea_success_msg[] = esc_html__( 'Import history successfully deleted.', 'wp-event-aggregator' );
					break;

				case 'wpsiu_success':
					$wpea_success_msg[] = esc_html__( 'Scheduled import successfully updated.', 'wp-event-aggregator' );
					break;			
								
				default:
					$wpea_success_msg[] = esc_html__( 'Scheduled imports successfully deleted.', 'wp-event-aggregator' );
					break;
			}
		}
	}

	/**
	 * Process insert google maps api key for embed maps
	 *
	 * @since    1.7.3
	 */
	public function handle_gma_settings_submit() {
		global $wpea_errors, $wpea_success_msg;
		if ( isset( $_POST['wpea_gma_action'] ) && 'wpea_save_gma_settings' === sanitize_text_field( wp_unslash( $_POST['wpea_gma_action'] ) ) && check_admin_referer( 'wpea_gma_setting_form_nonce_action', 'wpea_gma_setting_form_nonce' ) ) { // input var okay.
			$gma_option = array();
			$gma_option['wpea_google_maps_api_key'] = isset( $_POST['wpea_google_maps_api_key'] ) ? wp_unslash( $_POST['wpea_google_maps_api_key'] ) : ''; // input var okay.
			$is_update = update_option( 'wpea_google_maps_api_key', $gma_option['wpea_google_maps_api_key'] );
			if ( $is_update ) {
				$wpea_success_msg[] = __( 'Google Maps API Key has been saved successfully.', 'wp-event-aggregator' );
			} else {
				$wpea_errors[] = __( 'Something went wrong! please try again.', 'wp-event-aggregator' );
			}
		}
	}
}
