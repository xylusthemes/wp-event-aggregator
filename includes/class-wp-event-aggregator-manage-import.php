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
		add_action( 'init', array( $this, 'register_session' ) );
		add_action( 'init', array( $this, 'handle_import_form_submit' ) , 99);
		add_action( 'init', array( $this, 'handle_import_settings_submit' ), 99 );
		add_action( 'init', array( $this, 'handle_listtable_oprations' ), 99 );
		add_action( 'admin_notices', array( $this, 'display_session_success_message' ), 100 );
	}

	/**
	 * Process insert group form for TEC.
	 *
	 * @since    1.0.0
	 */
	public function handle_import_form_submit() {
		global $errors;

		if ( isset( $_POST['wpea_action'] ) && $_POST['wpea_action'] == 'wpea_import_submit' &&  check_admin_referer( 'wpea_import_form_nonce_action', 'wpea_import_form_nonce' ) ) {
			
			if( !isset( $_POST['import_origin'] ) || empty( $_POST['import_origin'] ) ){
				$errors[] = esc_html__( 'Please provide import origin.', 'wp-event-aggregator' );
				return;
			}
			$event_origin = $_POST['import_origin'];
			switch ( $event_origin ) {
				case 'eventbrite':
					$this->handle_eventbrite_import_form_submit( $_POST );
					break;

				case 'meetup':
					$this->handle_meetup_import_form_submit( $_POST );
					break;

				case 'facebook':
					$this->handle_facebook_import_form_submit( $_POST );
					break;

				case 'ical':
					$this->handle_ical_import_form_submit( $_POST );
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
		global $errors, $success_msg;
		if ( isset( $_POST['wpea_action'] ) && $_POST['wpea_action'] == 'wpea_save_settings' &&  check_admin_referer( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ) ) {
				
			$wpea_options = array();
			$wpea_options['eventbrite'] = isset( $_POST['eventbrite'] ) ? $_POST['eventbrite'] : array();
			$wpea_options['meetup'] = isset( $_POST['meetup'] ) ? $_POST['meetup'] : array();
			$wpea_options['facebook'] = isset( $_POST['facebook'] ) ? $_POST['facebook'] : array();
			$wpea_options['ical'] = isset( $_POST['ical'] ) ? $_POST['ical'] : array();

			$is_update = update_option( WPEA_OPTIONS, $wpea_options );
			if( $is_update ){
				$success_msg[] = __( 'Import settings has been saved successfully.', 'wp-event-aggregator' );
			}else{
				$errors[] = __( 'Something went wrong! please try again.', 'wp-event-aggregator' );
			}
		}
	}

	/**
	 * Delete scheduled import from list table.
	 *
	 * @since    1.0.0
	 */
	public function handle_listtable_oprations() {
		
		global $success_msg;
		if ( isset( $_GET['wpea_action'] ) && $_GET['wpea_action'] == 'wpea_simport_delete' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wpea_delete_import_nonce') ) {
			$import_id = $_GET['import_id'];
			if ( $import_id > 0 ) {
				$post_type = get_post_type( $import_id );
				if ( $post_type == 'xt_scheduled_imports' ) {
					wp_delete_post( $import_id, true );
					$this->set_success_message_session( esc_html__( 'Scheduled import deleted successfully.', 'wp-event-aggregator' ) );
					wp_redirect( remove_query_arg( array( 'wpea_action', 'import_id', '_wpnonce' ) ) );
					exit;
				}
			}
		}

		if ( isset( $_GET['wpea_action'] ) && $_GET['wpea_action'] == 'wpea_run_import' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wpea_run_import_nonce') ) {
			$import_id = (int)$_GET['import_id'];
			if ( $import_id > 0 ) {
				do_action( 'xt_run_scheduled_import', $import_id );
				$this->set_success_message_session( esc_html__( 'Event imported successfully.', 'wp-event-aggregator' ) );
				wp_redirect( remove_query_arg( array( 'wpea_action', 'import_id', '_wpnonce' ) ) );
				exit;
			}
		}

		if ( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'bulk-xt_scheduled_imports') ) {

			$wp_redirect = get_site_url() . urldecode( $_REQUEST['_wp_http_referer'] );
        	$delete_ids = $_REQUEST['xt_scheduled_import'];
        	if( !empty( $delete_ids ) ){
        		foreach ($delete_ids as $delete_id ) {
        			wp_delete_post( $delete_id, true );
        		}            		
        	}
        	$this->set_success_message_session( esc_html__( 'Scheduled imports are deleted successfully.', 'wp-event-aggregator' ) );
			wp_redirect( $wp_redirect );
			exit;
		}
	}

	/**
	 * Handle Eventbrite import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_eventbrite_import_form_submit(){
		global $errors, $success_msg, $importevents;
		
		$event_data = array();
		$event_data['import_by'] = 'event_id';
		$event_data['eventbrite_event_id'] = isset( $_POST['wpea_eventbrite_id'] ) ? sanitize_text_field( $_POST['wpea_eventbrite_id']) : '';
		$event_data['organizer_id'] = '';
		$event_data['import_type'] = 'onetime';
		$event_data['import_frequency'] = isset( $_POST['import_frequency'] ) ? sanitize_text_field( $_POST['import_frequency']) : 'daily';
		$event_data['event_status'] = isset( $_POST['event_status'] ) ? sanitize_text_field( $_POST['event_status']) : 'pending';
		$event_data['event_cats'] = isset( $_POST['event_cats'] ) ? $_POST['event_cats'] : array();

		if( !is_numeric( $event_data['eventbrite_event_id'] ) ){
			$errors[] = esc_html__( 'Please provide valid Eventbrite event ID.', 'wp-event-aggregator' );
			return;
		}

		$maybe_event_id = $importevents->eventbrite->import_event_by_event_id( $event_data );
		if( $maybe_event_id && $maybe_event_id > 0 ){
			$success_msg[] = esc_html__( 'Event imported successfully.', 'wp-event-aggregator' );
		}

	}

	/**
	 * Handle meetup import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_meetup_import_form_submit(){
		global $errors, $success_msg, $importevents;
		
		$event_data = array();
		$event_data['meetup_url'] = isset( $_POST['meetup_url'] ) ? esc_url( $_POST['meetup_url'] ) : '';
		$event_data['import_type'] = 'onetime';
		$event_data['event_status'] = isset( $_POST['event_status'] ) ? sanitize_text_field( $_POST['event_status']) : 'pending';
		$event_data['event_cats'] = isset( $_POST['event_cats'] ) ? $_POST['event_cats'] : array();

		if ( filter_var( $event_data['meetup_url'], FILTER_VALIDATE_URL) === false ){
			$errors[] = esc_html__( 'Please provide valid Meetup group URL.', 'wp-event-aggregator' );
			return;
		}

		$meetup_group_name = $importevents->meetup->get_meetup_group_name_by_url( $event_data['meetup_url'] );
		
		$import_events = $importevents->meetup->import_events( $event_data );
		if( $import_events && !empty( $import_events ) ){
			$success_msg[] = esc_html__( 'Events are imported successfully.', 'wp-event-aggregator' );
		}
	}

	/**
	 * Handle Facebook import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_facebook_import_form_submit(){
		global $errors, $success_msg, $importevents;
		
		$event_data = array();
		$event_data['import_by'] = 'facebook_event_id';

		$event_data['event_ids'] = isset( $_POST['facebook_event_ids'] ) ? array_map( 'trim', (array) explode( "\n", preg_replace( "/^\n+|^[\t\s]*\n+/m", '', $_POST['facebook_event_ids'] ) ) ) : array();

		$event_data['page_username'] = '';
		$event_data['import_type'] = 'onetime';
		$event_data['import_frequency'] = isset( $_POST['import_frequency'] ) ? sanitize_text_field( $_POST['import_frequency']) : 'daily';
		$event_data['event_status'] = isset( $_POST['event_status'] ) ? sanitize_text_field( $_POST['event_status']) : 'pending';
		$event_data['event_cats'] = isset( $_POST['event_cats'] ) ? $_POST['event_cats'] : array();

		//wp_p1( $event_data, true);

		$import_events = $importevents->facebook->import_events( $event_data );
		if( $import_events && !empty( $import_events ) ){
			$success_msg[] = esc_html__( 'Events are imported successfully.', 'wp-event-aggregator' );
		}

	}

	/**
	 * Handle iCal import form submit.
	 *
	 * @since    1.0.0
	 */
	public function handle_ical_import_form_submit(){
		global $errors, $success_msg, $importevents;

		$event_data = array();
		$event_data['import_by'] = 'ics_file';
		$event_data['ical_url'] = '';
		$event_data['start_date'] = isset( $_POST['start_date'] ) ? $_POST['start_date'] : '';
		$event_data['end_date'] = isset( $_POST['end_date'] ) ? $_POST['end_date'] : '';

		$event_data['import_type'] = 'onetime';
		$event_data['import_frequency'] = isset( $_POST['import_frequency'] ) ? sanitize_text_field( $_POST['import_frequency']) : 'daily';
		$event_data['event_status'] = isset( $_POST['event_status'] ) ? sanitize_text_field( $_POST['event_status']) : 'pending';
		$event_data['event_cats'] = isset( $_POST['event_cats'] ) ? $_POST['event_cats'] : array();


		$file_ext = pathinfo( $_FILES['ics_file']['name'], PATHINFO_EXTENSION );
		$file_type = $_FILES['ics_file']['type'];

		if( $file_type != 'text/calendar' && $file_ext != 'ics' ){
			$errors[] = esc_html__( 'Please upload .ics file', 'wp-event-aggregator');
			return;
		}

		$ics_content =  file_get_contents( $_FILES['ics_file']['tmp_name'] );
		$ics_content_array = explode("\n", $ics_content);

		$import_events = $importevents->ical->import_events_from_ics_content( $event_data, $ics_content_array );

		if( $import_events && !empty( $import_events ) ){
			$success_msg[] = sprintf( esc_html__( '%s Events are imported successfully.', 'wp-event-aggregator' ), count($import_events) );
		}else{
			if( empty( $errors ) ){
				$success_msg[] = esc_html__( 'Nothing to import.', 'wp-event-aggregator' );	
			}
		}

	}


	/**
	 * Set Success message
	 *
	 * @since    1.0.0
	 */
	public function set_success_message_session( $message = '' ){
		$_SESSION['succ_message'] = $message;
	}

	/**
	 * Register Session
	 *
	 * @since    1.0.0
	 */
	public function register_session(){
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Set Success message
	 *
	 * @since    1.0.0
	 */
	public function display_session_success_message() {
		if ( isset( $_SESSION['succ_message'] ) && $_SESSION['succ_message'] != "" ) {
		?>
		    <div class="notice notice-success is-dismissible">
		        <p><?php esc_html_e( $_SESSION['succ_message'] ); ?></p>
		    </div>
	    <?php
		unset( $_SESSION['succ_message'] );
		}
	}

}
