<?php
/**
 * Import Events Cron.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Cron {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct() {
		$this->load_scheduler();
	}

	/**
	 * Load the all requred hooks for run scheduler
	 *
	 * @since    1.0.0
	 */
	public function load_scheduler() {
		// Remove cron on delete meetup url.
		add_action( 'delete_post', array( $this, 'remove_scheduled_import' ) );

		// Setup cron on add new scheduled import for TEC.
		//add_action( 'save_post_xt_scheduled_imports', array( $this, 'setup_scheduled_import' ), 10, 3 );

		// setup custom cron recurrences.
		add_action( 'cron_schedules', array( $this, 'setup_custom_cron_recurrences' ) );

		// run scheduled importer
		add_action( 'xt_run_scheduled_import', array( $this, 'run_scheduled_importer' ), 100 );
	}

	/**
	 * Run scheduled event importer.
	 *
	 * @since    1.0.0
	 * @param int $post_id Options.
	 * @return null/void
	 */
	public function run_scheduled_importer( $post_id = 0 ) {
		global $importevents;

		$post = get_post( $post_id );
		if( !$post || empty( $post ) ){
			return; 
		}
		$import_origin = get_post_meta( $post_id, 'import_origin', true );
		$import_eventdata = get_post_meta( $post_id, 'import_eventdata', true );

		if( 'eventbrite' == $import_origin ){

			$importevents->eventbrite->import_events( $import_eventdata );

		}elseif( 'meetup' == $import_origin ){

			$importevents->meetup->import_events( $import_eventdata );

		}elseif( 'facebook' == $import_origin ){

			$importevents->facebook->import_events( $import_eventdata );

		}elseif( 'ical' == $import_origin ){

			$importevents->ical->import_events( $import_eventdata );
		}

	}

	/**
	 * Setup cron on add new scheduled import.
	 *
	 * @since    1.0.0
	 * @param int 	 $post_id Post ID.
	 * @param object $post Post.
	 * @param bool   $update is update or new insert.
	 * @return void
	 */
	public function setup_scheduled_import( $post_id, $post, $update ) {
		// check if not post update.
		if ( ! $update ) {

			$import_eventdata = get_post_meta( $post_id, 'import_eventdata', true );
			$import_frequency = isset( $import_eventdata['import_frequency']) ? $import_eventdata['import_frequency'] : 'twicedaily';
			wp_schedule_event( time(), $import_frequency, 'xt_run_scheduled_import', array( 'post_id' => $post_id ) );

		}
	}

	/**
	 * Remove saved cron scheduled import on delete scheduled event.
	 *
	 * @since    1.0.0
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function remove_scheduled_import( $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( $post_type == 'xt_scheduled_imports' ){
			wp_clear_scheduled_hook( 'xt_run_scheduled_import', array( 'post_id' => $post_id ) );
		}
	}

	/**
	 * Setup custom cron recurrences.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function setup_custom_cron_recurrences() {
		// Weekly Schedule.
		$schedules['weekly'] = array(
			'display' => __( 'Once Weekly', 'wp-event-aggregator' ),
			'interval' => 604800,
		);
		// Monthly Schedule.
		$schedules['monthly'] = array(
			'display' => __( 'Once a Month', 'wp-event-aggregator' ),
			'interval' => 2635200,
		);
		return $schedules;
	}

}
