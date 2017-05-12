<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package     WP_Event_Aggregator
 * @subpackage  WP_Event_Aggregator/include
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The admin-specific functionality of the plugin.
 *
 * @package     WP_Event_Aggregator
 * @subpackage  WP_Event_Aggregator/admin
 * @author     Dharmesh Patel <dspatel44@gmail.com>
 */
class WP_Event_Aggregator_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		// Do nothing
		add_action( 'init', array( $this, 'register_scheduled_import_cpt' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_pages') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles') );
		add_action( 'admin_notices', array( $this, 'display_notices') );
	}

	/**
	 * Create the Admin menu and submenu and assign their links to global varibles.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_menu_pages(){

		add_menu_page( __( 'WP Event Aggregator', 'wp-event-aggregator' ), __( 'WP Event Aggregator', 'wp-event-aggregator' ), 'manage_options', 'import_events', array( $this, 'admin_page' ), 'dashicons-calendar', '30' );

		//add_submenu_page( 'import_events', __( 'Eventbrite', 'wp-event-aggregator' ), __( 'Eventbrite', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=eventbrite' ) );
	}

	/**
	 * Load Admin Scripts
	 *
	 * Enqueues the required admin scripts.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function enqueue_admin_scripts( $hook ) {

		$js_dir  = WPEA_PLUGIN_URL . 'assets/js/';
		wp_register_script( 'wp-event-aggregator', $js_dir . 'wp-event-aggregator-admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), WPEA_VERSION );
		wp_enqueue_script( 'wp-event-aggregator' );
		
	}

	/**
	 * Load Admin Styles.
	 *
	 * Enqueues the required admin styles.
	 *
	 * @since 1.0
	 * @param string $hook Page hook
	 * @return void
	 */
	function enqueue_admin_styles( $hook ) {

	  	$css_dir = WPEA_PLUGIN_URL . 'assets/css/';
	 	wp_enqueue_style('jquery-ui', $css_dir . 'jquery-ui.css', false, "1.12.0" );
	 	wp_enqueue_style('wp-event-aggregator', $css_dir . 'wp-event-aggregator-admin.css', false, "" );
	}

	/**
	 * Load Admin page.
	 *
	 * @since 1.0
	 * @return void
	 */
	function admin_page() {
		
		?>
		<div class="wrap">
		    <h2><?php esc_html_e( 'WP Event Aggregator', 'wp-event-aggregator' ); ?></h2>
		    <?php
		    // Set Default Tab to Import.
		    $tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'eventbrite';
		    $ntab = isset( $_GET[ 'ntab' ] ) ? $_GET[ 'ntab' ] : 'import';
		    ?>
		    <div id="poststuff">
		        <div id="post-body" class="metabox-holder columns-2">

		            <div id="postbox-container-1" class="postbox-container">
		            	<?php require_once WPEA_PLUGIN_DIR . '/templates/admin-sidebar.php'; ?>
		            </div>
		            <div id="postbox-container-2" class="postbox-container">

		                <h1 class="nav-tab-wrapper">

		                	<?php
		                    if ( ! function_exists( 'is_plugin_active' ) ) {
		            			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		            		}
		            		if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) { ?>
		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'eventbrite', remove_query_arg( 'ntab') ) ); ?>" class="nav-tab <?php if ( $tab == 'eventbrite' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Eventbrite', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'meetup', remove_query_arg( 'ntab') ) ); ?>" class="nav-tab <?php if ( $tab == 'meetup' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Meetup', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'facebook', remove_query_arg( 'ntab') ) ); ?>" class="nav-tab <?php if ( $tab == 'facebook' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Facebook', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'ical', remove_query_arg( 'ntab') ) ); ?>" class="nav-tab <?php if ( $tab == 'ical' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'iCalendar / .ics', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'scheduled', remove_query_arg( 'ntab') ) ); ?>" class="nav-tab <?php if ( $tab == 'scheduled' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Scheduled Imports', 'wp-event-aggregator' ); ?>
		                    </a>


		                    <?php } ?>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings', remove_query_arg( 'ntab') ) ); ?>" class="nav-tab <?php if ( $tab == 'settings' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Settings', 'wp-event-aggregator' ); ?>
		                    </a>
		                </h1>

		                <div class="wp-event-aggregator-page">

		                	<?php
		                	if ( $tab == 'eventbrite' ) {

		                		require_once WPEA_PLUGIN_DIR . '/templates/eventbrite-import-events.php';

		                	} elseif ( $tab == 'meetup' ) {

		                		require_once WPEA_PLUGIN_DIR . '/templates/meetup-import-events.php';

		                	} elseif ( $tab == 'facebook' ) {

		                		require_once WPEA_PLUGIN_DIR . '/templates/facebook-import-events.php';

		                	} elseif ( $tab == 'settings' ) {
		                		
		                		require_once WPEA_PLUGIN_DIR . '/templates/wp-event-aggregator-settings.php';

		                	} elseif ( $tab == 'ical' ) {

		                		require_once WPEA_PLUGIN_DIR . '/templates/ical-import-events.php';

		                	} elseif ( $tab == 'scheduled' ) {

		                		require_once WPEA_PLUGIN_DIR . '/templates/scheduled-import-events.php';

		                	}
			                ?>
		                	<div style="clear: both"></div>
		                </div>

		        </div>
		        
		    </div>
		</div>
		<?php
	}


	/**
	 * Display notices in admin.
	 *
	 * @since    1.0.0
	 */
	public function display_notices() {
		global $errors, $success_msg, $warnings, $info_msg;
		
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error ) :
			    ?>
			    <div class="notice notice-error is-dismissible">
			        <p><?php echo $error; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $success_msg ) ) {
			foreach ( $success_msg as $success ) :
			    ?>
			    <div class="notice notice-success is-dismissible">
			        <p><?php echo $success; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $warnings ) ) {
			foreach ( $warnings as $warning ) :
			    ?>
			    <div class="notice notice-warning is-dismissible">
			        <p><?php echo $warning; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $info_msg ) ) {
			foreach ( $info_msg as $info ) :
			    ?>
			    <div class="notice notice-info is-dismissible">
			        <p><?php echo $info; ?></p>
			    </div>
			    <?php
			endforeach;
		}

	}

	/**
	 * Register custom post type for scheduled imports.
	 *
	 * @since    1.0.0
	 */
	public function register_scheduled_import_cpt() {
		$labels = array(
			'name'               => _x( 'Scheduled Import', 'post type general name', 'wp-event-aggregator' ),
			'singular_name'      => _x( 'Scheduled Import', 'post type singular name', 'wp-event-aggregator' ),
			'menu_name'          => _x( 'Scheduled Imports', 'admin menu', 'wp-event-aggregator' ),
			'name_admin_bar'     => _x( 'Scheduled Import', 'add new on admin bar', 'wp-event-aggregator' ),
			'add_new'            => _x( 'Add New', 'book', 'wp-event-aggregator' ),
			'add_new_item'       => __( 'Add New Import', 'wp-event-aggregator' ),
			'new_item'           => __( 'New Import', 'wp-event-aggregator' ),
			'edit_item'          => __( 'Edit Import', 'wp-event-aggregator' ),
			'view_item'          => __( 'View Import', 'wp-event-aggregator' ),
			'all_items'          => __( 'All Scheduled Imports', 'wp-event-aggregator' ),
			'search_items'       => __( 'Search Scheduled Imports', 'wp-event-aggregator' ),
			'parent_item_colon'  => __( 'Parent Imports:', 'wp-event-aggregator' ),
			'not_found'          => __( 'No Imports found.', 'wp-event-aggregator' ),
			'not_found_in_trash' => __( 'No Imports found in Trash.', 'wp-event-aggregator' ),
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Scheduled Imports.', 'wp-event-aggregator' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_admin_bar'  => false,
			'show_in_nav_menus'  => false,
			'can_export'         => false,
			'rewrite'            => false,
			'capability_type'    => 'page',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title' ),
			'menu_position'		=> 5,
		);

		register_post_type( 'xt_scheduled_imports', $args );
	}


	/**
	 * Check for dependencies to work this plugin and deactive plugin if requirements not met.
	 *
	 * @since    1.0.0
	 * @param string $plugin_basename Plugin basename.
	 */
	public function check_requirements( $plugin_basename ) {
		if ( ! $this->is_meets_requirements() ) {
			deactivate_plugins( $plugin_basename );
			add_action( 'admin_notices',array( $this, 'deactivate_notice' ) );
			return false;
		}
		return true;
	}
	/**
	 * Check meets dependencies requirements
	 *
	 * @since  1.0.0
	 * @return boolean true if met requirements.
	 */
	public function is_meets_requirements() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'the-events-calendar/the-events-calendar.php' ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Display an error message when the plugin deactivates itself.
	 */
	public function deactivate_notice() {
		?>
		<div class="error">
		    <p>
				<?php _e( 'WP Event Aggregator requires <a href="https://wordpress.org/plugins/the-events-calendar/" target="_blank" >The Events Calendar</a> to be installed and activated. WP Event Aggregator has been deactivated itself.', 'the-events-calendar-meetup-import' ); ?>
		    </p>
		</div>
		<?php
	}

}
