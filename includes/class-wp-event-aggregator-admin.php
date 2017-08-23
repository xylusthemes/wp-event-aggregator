<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @package     WP_Event_Aggregator
 * @subpackage  WP_Event_Aggregator/admin
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


	public $adminpage_url;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->adminpage_url = admin_url('admin.php?page=import_events' );

		add_action( 'init', array( $this, 'register_scheduled_import_cpt' ) );
		add_action( 'init', array( $this, 'register_history_cpt' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_pages') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles') );
		add_action( 'admin_notices', array( $this, 'display_notices') );
		add_filter( 'submenu_file', array( $this, 'get_selected_tab_submenu' ) );
		add_filter( 'admin_footer_text', array( $this, 'add_event_aggregator_credit' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget') );
	}

	/**
	 * Create the Admin menu and submenu and assign their links to global varibles.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_menu_pages(){

		add_menu_page( __( 'WP Event Aggregator', 'wp-event-aggregator' ), __( 'WP Event Aggregator', 'wp-event-aggregator' ), 'manage_options', 'import_events', array( $this, 'admin_page' ), 'dashicons-calendar', '30' );

		global $submenu;	
		$submenu['import_events'][] = array( __( 'Eventbrite Import', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=eventbrite' ) );
    	$submenu['import_events'][] = array( __( 'Meetup Import', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=meetup' ) );
    	$submenu['import_events'][] = array( __( 'Facebook Import', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=facebook' ));
    	$submenu['import_events'][] = array( __( 'iCalendar/.ics Import', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=ical' ));
    	$submenu['import_events'][] = array( __( 'Settings', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=settings' ));
    	$submenu['import_events'][] = array( __( 'Support & help', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=support' ));

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

		global $pagenow;
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if( 'import_events' == $page || 'widgets.php' == $pagenow || 'post.php' == $pagenow ){
			$css_dir = WPEA_PLUGIN_URL . 'assets/css/';
	 		wp_enqueue_style('jquery-ui', $css_dir . 'jquery-ui.css', false, "1.11.4" );
	 		wp_enqueue_style('wp-event-aggregator', $css_dir . 'wp-event-aggregator-admin.css', false, "" );
		}

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

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'eventbrite', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'eventbrite' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Eventbrite', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'meetup', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'meetup' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Meetup', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'facebook', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'facebook' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Facebook', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'ical', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'ical' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'iCalendar / .ics', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'scheduled', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'scheduled' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Scheduled Imports', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'history', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'history' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Import History', 'wp-event-aggregator' ); ?>
		                    </a>

		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'settings', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'settings' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Settings', 'wp-event-aggregator' ); ?>
		                    </a>
		                    <a href="<?php echo esc_url( add_query_arg( 'tab', 'support', $this->adminpage_url ) ); ?>" class="nav-tab <?php if ( $tab == 'support' ) { echo 'nav-tab-active'; } ?>">
		                        <?php esc_html_e( 'Support & Help', 'wp-event-aggregator' ); ?>
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

		                	}elseif ( $tab == 'history' ) {
		                		
		                		require_once WPEA_PLUGIN_DIR . '/templates/wp-event-aggregator-history.php';

		                	}elseif ( $tab == 'support' ) {
		                		
		                		require_once WPEA_PLUGIN_DIR . '/templates/wp-event-aggregator-support.php';

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
		global $wpea_errors, $wpea_success_msg, $wpea_warnings, $wpea_info_msg;
		
		if ( ! empty( $wpea_errors ) ) {
			foreach ( $wpea_errors as $error ) :
			    ?>
			    <div class="notice notice-error is-dismissible">
			        <p><?php echo $error; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $wpea_success_msg ) ) {
			foreach ( $wpea_success_msg as $success ) :
			    ?>
			    <div class="notice notice-success is-dismissible">
			        <p><?php echo $success; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $wpea_warnings ) ) {
			foreach ( $wpea_warnings as $warning ) :
			    ?>
			    <div class="notice notice-warning is-dismissible">
			        <p><?php echo $warning; ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $wpea_info_msg ) ) {
			foreach ( $wpea_info_msg as $info ) :
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
			'show_ui'            => false,
			'show_in_menu'       => false,
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
	 * Register custom post type for Save import history.
	 *
	 * @since    1.0.0
	 */
	public function register_history_cpt() {
		$labels = array(
			'name'               => _x( 'Import History', 'post type general name', 'wp-event-aggregator' ),
			'singular_name'      => _x( 'Import History', 'post type singular name', 'wp-event-aggregator' ),
			'menu_name'          => _x( 'Import History', 'admin menu', 'wp-event-aggregator' ),
			'name_admin_bar'     => _x( 'Import History', 'add new on admin bar', 'wp-event-aggregator' ),
			'add_new'            => _x( 'Add New', 'book', 'wp-event-aggregator' ),
			'add_new_item'       => __( 'Add New', 'wp-event-aggregator' ),
			'new_item'           => __( 'New History', 'wp-event-aggregator' ),
			'edit_item'          => __( 'Edit History', 'wp-event-aggregator' ),
			'view_item'          => __( 'View History', 'wp-event-aggregator' ),
			'all_items'          => __( 'All Import History', 'wp-event-aggregator' ),
			'search_items'       => __( 'Search History', 'wp-event-aggregator' ),
			'parent_item_colon'  => __( 'Parent History:', 'wp-event-aggregator' ),
			'not_found'          => __( 'No History found.', 'wp-event-aggregator' ),
			'not_found_in_trash' => __( 'No History found in Trash.', 'wp-event-aggregator' ),
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Import History', 'wp-event-aggregator' ),
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
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

		register_post_type( 'wpea_import_history', $args );
	}

	/**
	 * Register the dashboard widget.
	 *
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'wpea_dashboard_widget',
			esc_html__( 'News from Xylus Themes', 'wp-event-aggregator' ),
			array($this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render the dashboard widget.
	 *
	 */
	function render_dashboard_widget( $posts = 10 ) {
		echo '<div class="wpea-dashboard-widget">';
		wp_widget_rss_output( 'https://xylusthemes.com/feed/', array( 'items' => $posts ) );
		echo '</div>';
	}

	/**
	 * Add WP Event Aggregator ratting text
	 *
	 * @since 1.0
	 * @return void
	 */
	public function add_event_aggregator_credit( $footer_text ){
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( $page != '' && $page == 'import_events' ) {
			$rate_url = 'https://wordpress.org/support/plugin/wp-event-aggregator/reviews/?rate=5#new-post';

			$footer_text .= sprintf(
				esc_html__( ' Rate %1$sWP Event Aggregator%2$s %3$s', 'wp-event-aggregator' ),
				'<strong>',
				'</strong>',
				'<a href="' . $rate_url . '" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}
		return $footer_text;
	}

	/**
	 * Get Plugin array
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_xyuls_themes_plugins(){
		return array(
			'import-facebook-events' => esc_html__( 'Import Facebook Events', 'wp-event-aggregator' ),
			'import-eventbrite-events' => esc_html__( 'Import Eventbrite Events', 'wp-event-aggregator' ),
			'import-meetup-events' => esc_html__( 'Import Meetup Events', 'wp-event-aggregator' ),
			'wp-bulk-delete' => esc_html__( 'WP Bulk Delete', 'wp-event-aggregator' ),
			'xt-facebook-events' => esc_html__( 'Facebook Events', 'wp-event-aggregator' ),
			'event-schema' => esc_html__( 'Event Schema / Structured Data: Google Rich Snippet Schema for Event', 'wp-event-aggregator' ),
		);
	}

	/**
	 * Get Plugin Details.
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function get_wporg_plugin( $slug ){

		if( $slug == '' ){
			return false;
		}

		$transient_name = 'support_plugin_box'.$slug;
		$plugin_data = get_transient( $transient_name );
		if( false === $plugin_data ){
			if ( ! function_exists( 'plugins_api' ) ) {
				include_once ABSPATH . '/wp-admin/includes/plugin-install.php';
			}

			$plugin_data = plugins_api( 'plugin_information', array(
				'slug' => $slug,
				'is_ssl' => is_ssl(),
				'fields' => array(
					'banners' => true,
					'active_installs' => true,
				),
			) );

			if ( ! is_wp_error( $plugin_data ) ) {
				
			} else {
				// If there was a bug on the Current Request just leave
				return false;
			}
			set_transient( $transient_name, $plugin_data, 24 * HOUR_IN_SECONDS );
		}
		return $plugin_data;
	}

	/**
	 * Tab Submenu got selected.
	 *
	 * @since 1.2
	 * @return void
	 */
	public function get_selected_tab_submenu( $submenu_file ){
		if( !empty( $_GET['page'] ) && $_GET['page'] == 'import_events' ){
			$allowed_tabs = array( 'eventbrite', 'meetup', 'facebook', 'ical', 'settings', 'support' );
			$tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : '';
			if( in_array( $tab, $allowed_tabs ) ){
				$submenu_file = admin_url( 'admin.php?page=import_events&tab='.$tab );
			}
		}
		return $submenu_file;
	}
}
