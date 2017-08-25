<?php
/**
 * Plugin Name:       WP Event Aggregator
 * Plugin URI:        http://xylusthemes.com/plugins/wp-event-aggregator/
 * Description:       Import Events from anywhere - Facebook, Eventbrite, Meetup, iCalendar and ICS into your WordPress site.
 * Version:           1.3.0
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
			add_action( 'wp_enqueue_scripts', array( self::$instance, 'wpea_enqueue_style' ) );
			add_action( 'wp_enqueue_scripts', array( self::$instance, 'wpea_enqueue_script' ) );

			self::$instance->includes();
			self::$instance->common = new WP_Event_Aggregator_Common();
			self::$instance->cpt    = new WP_Event_Aggregator_Cpt();
			self::$instance->eventbrite = new WP_Event_Aggregator_Eventbrite();
			self::$instance->meetup = new WP_Event_Aggregator_Meetup();
			self::$instance->facebook = new WP_Event_Aggregator_Facebook();
			self::$instance->ical_parser = new WP_Event_Aggregator_Ical_Parser();
			self::$instance->ical = new WP_Event_Aggregator_Ical();
			self::$instance->admin = new WP_Event_Aggregator_Admin();
			self::$instance->manage_import = new WP_Event_Aggregator_Manage_Import();
			self::$instance->wpea    = new WP_Event_Aggregator_WPEA();
			self::$instance->tec = new WP_Event_Aggregator_TEC();
			self::$instance->em = new WP_Event_Aggregator_EM();
			self::$instance->eventon = new WP_Event_Aggregator_EventON();
			self::$instance->event_organizer = new WP_Event_Aggregator_Event_Organizer();
			self::$instance->aioec = new WP_Event_Aggregator_Aioec();
			self::$instance->my_calendar = new WP_Event_Aggregator_My_Calendar();
			
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
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-event-aggregator' ), '1.3.0' ); }

	/**
	 * A dummy magic method to prevent WP_Event_Aggregator from being unserialized.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'wp-event-aggregator' ), '1.3.0' ); }


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
			define( 'WPEA_VERSION', '1.3.0' );
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

		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-common.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-list-table.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-admin.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-manage-import.php';
		if( !class_exists( 'vcalendar' ) ){
			require_once WPEA_PLUGIN_DIR . 'includes/lib/iCalcreator/iCalcreator.php';
		}
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-cpt.php';

		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-eventbrite.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-meetup.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-facebook.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-ical_parser.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-ical.php';
		
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-wpea.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-tec.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-em.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-eventon.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-event_organizer.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-aioec.php';
		require_once WPEA_PLUGIN_DIR . 'includes/class-wp-event-aggregator-my-calendar.php';

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
			basename( dirname( __FILE__ ) ) . '/languages'
		);
	
	}
	
	/**
	 * enqueue style front-end
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function wpea_enqueue_style() {

		$css_dir = WPEA_PLUGIN_URL . 'assets/css/';
	 	wp_enqueue_style('wp-event-aggregator-front', $css_dir . 'wp-event-aggregator.css', false, "" );		
	}

	/**
	 * enqueue script front-end
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function wpea_enqueue_script() {
		
		// enqueue script here.
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

/**
 * Get Import events setting options
 *
 * @since 1.0
 * @return void
*/
function wpea_get_import_options( $type = '' ){

	$wpea_options = get_option( WPEA_OPTIONS );
	if( $type != '' ){
		$wpea_options = isset( $wpea_options[$type] ) ? $wpea_options[$type] : array();	
	}
	return $wpea_options;	
}

// Get WP_Event_Aggregator Running.
global $importevents, $wpea_errors, $wpea_success_msg, $wpea_warnings, $wpea_info_msg;
$importevents = run_wp_event_aggregator();
$wpea_errors = $wpea_warnings = $wpea_success_msg = $wpea_info_msg = array();

/**
 * The code that runs during plugin activation.
 *
 * @since 1.1.2
 */
function wpea_activate_wp_event_aggregator() {
	global $importevents;
	$importevents->cpt->register_event_post_type();
	flush_rewrite_rules();	
}
register_activation_hook( __FILE__, 'wpea_activate_wp_event_aggregator' );
