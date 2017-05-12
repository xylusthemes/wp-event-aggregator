<?php
/**
 * Plugin Name:       WP Event Aggregator
 * Plugin URI:        http://xylusthemes.com/plugins/wp-event-aggregator/
 * Description:       Import Events from anywhere - Facebook, Eventbrite, Meetup, iCalendar and ICS into your WordPress site (The Events Calendar).
 * Version:           1.0.0
 * Author:            Xylus Themes
 * Author URI:        http://xylusthemes.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-event-aggregator
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'WP_Event_Aggregator' ) ):

/**
* Main WP Event Aggregator class
*/
class WP_Event_Aggregator{
	
	/** Singleton *************************************************************/
	/**
	 * WP_Event_Aggregator The one true WP_Event_Aggregator.
	 */
	private static $instance;

    /**
     * Main WP Event Aggregator Instance.
     * 
     * Insure that only one instance of WP_Event_Aggregator exists in memory at any one time.
     * Also prevents needing to define globals all over the place.
     *
     * @since 1.0.0
     * @static object $instance
     * @uses WP_Event_Aggregator::setup_constants() Setup the constants needed.
     * @uses WP_Event_Aggregator::includes() Include the required files.
     * @uses WP_Event_Aggregator::laod_textdomain() load the language files.
     * @see run_wp_event_aggregator()
     * @return object| WP Event Aggregator the one true WP Event Aggregator.
     */
	public static function instance() {
		if( ! isset( self::$instance ) && ! (self::$instance instanceof WP_Event_Aggregator ) ) {
			self::$instance = new WP_Event_Aggregator;
			self::$instance->setup_constants();

			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

			self::$instance->includes();
			self::$instance->eventbrite = new WP_Event_Aggregator_Eventbrite();
			self::$instance->meetup = new WP_Event_Aggregator_Meetup();
			self::$instance->facebook = new WP_Event_Aggregator_Facebook();
			self::$instance->ical = new WP_Event_Aggregator_Ical();
			self::$instance->cron = new WP_Event_Aggregator_Cron();
			self::$instance->admin = new WP_Event_Aggregator_Admin();
			self::$instance->manage_import = new WP_Event_Aggregator_Manage_Import();

		}
		return self::$instance;	
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent WP_Event_Aggregator from being loaded more than once.
	 *
	 * @since 1.0.0
	 * @see WP_Event_Aggregator::instance()
	 * @see run_wp_event_aggregator()
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent WP_Event_Aggregator from being cloned.
	 *
	 * @since 1.0.0
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-event-aggregator' ), '1.0.0' ); }

	/**
	 * A dummy magic method to prevent WP_Event_Aggregator from being unserialized.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-event-aggregator' ), '1.0.0' ); }


	/**
	 * Setup plugins constants.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if( ! defined( 'WPEA_VERSION' ) ){
			define( 'WPEA_VERSION', '1.0.0' );
		}

		// Plugin folder Path.
		if( ! defined( 'WPEA_PLUGIN_DIR' ) ){
			define( 'WPEA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin folder URL.
		if( ! defined( 'WPEA_PLUGIN_URL' ) ){
			define( 'WPEA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin root file.
		if( ! defined( 'WPEA_PLUGIN_FILE' ) ){
			define( 'WPEA_PLUGIN_FILE', __FILE__ );
		}

		// Options
		if( ! defined( 'WPEA_OPTIONS' ) ){
			define( 'WPEA_OPTIONS', 'wpea_options' );
		}

		define( 'WPEA_TEC_TAXONOMY', 'tribe_events_cat' );
		if ( class_exists( 'Tribe__Events__Main' ) ) {
			define( 'WPEA_TEC_POSTTYPE', Tribe__Events__Main::POSTTYPE );
		}else{
			define( 'WPEA_TEC_POSTTYPE', 'tribe_events' );
		}

		if ( class_exists( 'Tribe__Events__Organizer' ) ) {
			define( 'WPEA_TEC_ORGANIZER_POSTTYPE', Tribe__Events__Organizer::POSTTYPE );
		}else{
			define( 'WPEA_TEC_ORGANIZER_POSTTYPE', 'tribe_organizer' );
		}

		if ( class_exists( 'Tribe__Events__Venue' ) ) {
			define( 'WPEA_TEC_VENUE_POSTTYPE', Tribe__Events__Venue::POSTTYPE );
		}else{
			define( 'WPEA_TEC_VENUE_POSTTYPE', 'tribe_venue' );
		}

		// Pro plugin Buy now Link.
		if( ! defined( 'WPEA_PLUGIN_BUY_NOW_URL' ) ){
			define( 'WPEA_PLUGIN_BUY_NOW_URL', 'http://xylusthemes.com/plugins/wp-event-aggregator/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin' );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function includes() {

		require_once WPEA_PLUGIN_DIR . 'includes/common-functions.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-cron.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-eventbrite.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-meetup.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-facebook.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-ical.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-list-table.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-admin.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-manage-import.php';

	}

	/**
	 * Loads the plugin language files.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain(){

		load_plugin_textdomain(
			'wp-event-aggregator',
			false,
			WPEA_PLUGIN_DIR . '/languages/'
		);
	
	}
	
}

endif; // End If class exists check.

/**
 * The main function for that returns WP_Event_Aggregator
 *
 * The main function responsible for returning the one true WP_Event_Aggregator
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $importevents = xt_importevents(); ?>
 *
 * @since 1.0.0
 * @return object|WP_Event_Aggregator The one true WP_Event_Aggregator Instance.
 */
function run_wp_event_aggregator() {
	return WP_Event_Aggregator::instance();
}

// Get WP_Event_Aggregator Running.
global $importevents, $errors, $success_msg, $warnings, $info_msg;
$importevents = run_wp_event_aggregator();
$importevents->admin->check_requirements( plugin_basename( __FILE__ ) );
$errors = $warnings = $success_msg = $info_msg = array();
