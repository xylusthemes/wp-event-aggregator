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
		add_action( 'admin_init', array( $this, 'wpea_check_delete_pst_event_cron_status' ) );
		add_action( 'wpea_delete_past_events_cron', array( $this, 'wpea_delete_past_events' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_pages') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts') );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles') );
		add_action( 'wpea_display_all_notice', array( $this, 'wpea_display_notices' ) );
		add_filter( 'submenu_file', array( $this, 'get_selected_tab_submenu_wpea' ) );
		add_filter( 'admin_footer_text', array( $this, 'add_event_aggregator_credit' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget') );
		add_action( 'admin_action_wpea_view_import_history',  array( $this, 'wpea_view_import_history_handler' ) );
		add_action( 'admin_init', array( $this, 'setup_success_messages' ) );
		add_action( 'admin_init', array( $this, 'wpea_wp_cron_check' ) );
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
		$submenu['import_events'][] = array( __( 'Dashboard', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=dashboard' ) );
		$submenu['import_events'][] = array( __( 'Eventbrite Import', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=eventbrite' ) );
    	$submenu['import_events'][] = array( __( 'Meetup Import', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=meetup' ) );
    	$submenu['import_events'][] = array( __( 'Facebook Import', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=facebook' ));
    	$submenu['import_events'][] = array( __( 'iCalendar/.ics Import', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=ical' ));
		
		do_action( 'wpea_addon_submenus' );
		
		$submenu['import_events'][] = array( __( 'Schedule Imports', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=scheduled' ));
    	$submenu['import_events'][] = array( __( 'Import History', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=history' ));
    	$submenu['import_events'][] = array( __( 'Settings', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=settings' ));
    	$submenu['import_events'][] = array( __( 'Shortcode', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=shortcodes' ));
    	$submenu['import_events'][] = array( __( 'Support', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=support' ));
		$submenu['import_events'][] = array( __( 'Wizard', 'wp-event-aggregator' ), 'manage_options', admin_url( 'admin.php?page=import_events&tab=wpea_setup_wizard' ));
		if( !wpea_is_pro() ){
        	$submenu['import_events'][] = array( '<li class="wpea_upgrade_pro current">' . __( 'Upgrade to Pro', 'wp-event-aggregator' ) . '</li>', 'manage_options', esc_url( "https://xylusthemes.com/plugins/wp-event-aggregator/"));
		}

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
		wp_register_script( 'wp-event-aggregator', $js_dir . 'wp-event-aggregator-admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker', 'wp-color-picker'), WPEA_VERSION, true );

		wp_localize_script( 'wp-event-aggregator', 'wpea_data', array(
			'ajax_url' => esc_url( admin_url( 'admin-post.php' ) ),
			'nonce'    => wp_create_nonce( 'wpea_facebook_authorize_action' ),
		));
		wp_enqueue_script( 'wp-event-aggregator' );

		if( isset( $_GET['tab'] ) && $_GET['tab'] == 'wpea_setup_wizard' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_register_script( 'wp-event-aggregator-wizard-js', $js_dir . 'wp-event-aggregator-wizard.js',  array( 'jquery', 'jquery-ui-core' ), WPEA_VERSION, false );
			wp_enqueue_script( 'wp-event-aggregator-wizard-js' );
		}
		
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

		$css_dir = WPEA_PLUGIN_URL . 'assets/css/';
		$page    = isset( $_GET['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Load styles on plugin admin page
		if ( 'import_events' === $page || 'wpea_pro_import_by_files' === $page ) {
			wp_enqueue_style( 'wp-event-aggregator', $css_dir . 'wp-event-aggregator-admin.css', false, WPEA_VERSION );
			wp_enqueue_style( 'wp-color-picker' );

			$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( 'wpea_setup_wizard' === $tab ) {
				wp_enqueue_style( 'wp-event-aggregator-wizard-css', $css_dir . 'wp-event-aggregator-wizard.css', false, WPEA_VERSION );
			}
		}

		// Load styles on widgets/post screen
		if ( in_array( $pagenow, [ 'widgets.php', 'post.php', 'post-new.php' ], true ) || ( 'admin.php' === $pagenow && isset( $_GET['page'] ) && in_array( $_GET['page'], [ 'import_events', 'wpea_pro_import_by_files' ], true ) ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_style( 'jquery-ui', $css_dir . 'jquery-ui.css', false, '1.12.0' );
			wp_enqueue_style( 'wp-event-aggregator-admin-global', $css_dir . 'wp-event-aggregator-admin-global.css', false, WPEA_VERSION );
			wp_enqueue_style( 'wp-color-picker' );
		}
	}

	/**
	 * Load Admin page.
	 *
	 * @since 1.0
	 * @return void
	 */
	function admin_page() {

		global $importevents;

		$active_tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) )  : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ntab       = isset( $_GET[ 'ntab' ] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET[ 'ntab' ] ) ) ) : 'import'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$gettab     = str_replace( 'by_', '', $active_tab );
		$gettab     = ucwords( str_replace( '_', ' & ', $gettab ) );
		if( $active_tab == 'support' ){
			$page_title = 'Support & Help';
		}elseif( $active_tab == 'eventbrite' ){
			$page_title = 'Eventbrite Import';
		}elseif( $active_tab == 'meetup' ){
			$page_title = 'Meetup Import';
		}elseif( $active_tab == 'facebook' ){
			$page_title = 'Facebook Import';
		}elseif( $active_tab == 'ics' ){
			$page_title = 'ICS Import';
		}elseif( $active_tab == 'scheduled' ){
			$page_title = 'Scheduled Import';
		}else{
			$page_title = $gettab;
		}

		if( $active_tab == 'wpea_setup_wizard' ){
			require_once WPEA_PLUGIN_DIR . '/templates/admin/wp-event-aggregator-wizard.php';
			exit();
		}

		$posts_header_result = $importevents->common->wpea_render_common_header( $page_title );
	
		if( $active_tab != 'dashboard' ){
			?>
				<div class="wpea-container" style="margin-top: 60px;">
					<div class="wpea-wrap" >
						<div id="poststuff">
							<div id="post-body" class="metabox-holder columns-2">
								<?php 
									do_action( 'wpea_display_all_notice' );
								?>
								<div class="delete_notice"></div>
								<div id="postbox-container-2" class="postbox-container">
									<div class="wpea-app">
										<div class="wpea-tabs">
											<div class="tabs-scroller">
												<div class="var-tabs var-tabs--item-horizontal s">
													<div class="var-tabs__tab-wrap var-tabs--layout-horizontal">
														<a href="<?php echo esc_url( add_query_arg( 'tab', 'eventbrite', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $active_tab == 'eventbrite' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Eventbrite', 'wp-event-aggregator' ); ?></span>
														</a>
														<a href="<?php echo esc_url( add_query_arg( 'tab', 'meetup', $this->adminpage_url ) ); ?>" class="var-tab <?php echo ( $active_tab == 'meetup' )  ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Meetup', 'wp-event-aggregator' ); ?></span>
														</a>
														<a href="<?php echo esc_url( add_query_arg( 'tab', 'facebook', $this->adminpage_url ) ); ?>" class="var-tab <?php echo ( $active_tab == 'facebook' )  ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Facebook', 'wp-event-aggregator' ); ?></span>
														</a>
														<a href="<?php echo esc_url( add_query_arg( 'tab', 'ical', $this->adminpage_url ) ); ?>" class="var-tab <?php echo ( $active_tab == 'ical' )  ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'iCalendar / .ics', 'wp-event-aggregator' ); ?></span>
														</a>
														
														<?php do_action( 'wpea_addon_submenus_tabs', $active_tab ); ?>

														<a href="<?php echo esc_url( add_query_arg( 'tab', 'scheduled', $this->adminpage_url ) ); ?>" class="var-tab <?php echo ( $active_tab == 'scheduled' )  ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Scheduled Imports', 'wp-event-aggregator' ); if( !wpea_is_pro() ){ echo '<div class="wpea-pro-badge"> PRO </div>'; } ?></span>
														</a>
														<a href="<?php echo esc_url( add_query_arg( 'tab', 'history', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $active_tab == 'history' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Import History', 'wp-event-aggregator' ); ?></span>
														</a>
														<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $active_tab == 'settings' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Settings', 'wp-event-aggregator' ); ?></span>
														</a>
														<a href="<?php echo esc_url( add_query_arg( 'tab', 'shortcodes', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $active_tab == 'shortcodes' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Shortcodes', 'wp-event-aggregator'  ); ?></span>
														</a>
														<a href="<?php echo esc_url( add_query_arg( 'tab', 'support', $this->adminpage_url ) ); ?>" class="var-tab <?php echo $active_tab == 'support' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
															<span class="tab-label"><?php esc_attr_e( 'Support & Help', 'wp-event-aggregator' ); ?></span>
														</a>
													</div>
												</div>
											</div>
										</div>
									</div>

									<?php


										if ( $active_tab == 'eventbrite' ) {
											require_once WPEA_PLUGIN_DIR . '/templates/admin/eventbrite-import-events.php';
										} elseif ( $active_tab == 'meetup' ) {
											require_once WPEA_PLUGIN_DIR . '/templates/admin/meetup-import-events.php';
										} elseif ( $active_tab == 'facebook' ) {
											require_once WPEA_PLUGIN_DIR . '/templates/admin/facebook-import-events.php';
										} elseif ( $active_tab == 'settings' ) {
											require_once WPEA_PLUGIN_DIR . '/templates/admin/wp-event-aggregator-settings.php';
										} elseif ( $active_tab == 'ical' ) {
											require_once WPEA_PLUGIN_DIR . '/templates/admin/ical-import-events.php';
										} elseif ( $active_tab == 'scheduled' ) {
											if( wpea_is_pro() ){
												require_once WPEAPRO_PLUGIN_DIR . '/templates/admin/scheduled-import-events.php';
											}else{
												?>
													<div class="wpea-blur-filter" >
														<?php do_action( 'wpea_render_pro_notice' ); ?>
													</div>
												<?php
												
											}		                		
										}elseif ( $active_tab == 'history' ) {
											require_once WPEA_PLUGIN_DIR . '/templates/admin/wp-event-aggregator-history.php';
										}elseif ( $active_tab == 'support' ) {
											require_once WPEA_PLUGIN_DIR . '/templates/admin/wp-event-aggregator-support.php';
										}elseif ( $active_tab == 'shortcodes' ) {
											require_once WPEA_PLUGIN_DIR . '/templates/admin/wp-event-aggregator-shortcode.php';
										}

										do_action( 'wpea_addon_submenus_pages', $active_tab, $ntab );
									?>
								</div>
							</div>
							<div style="clear: both"></div>
						</div>
					</div>
				</div>
			<?php
		}else{
			require_once WPEA_PLUGIN_DIR . '/templates/admin/wp-event-aggregator-dashboard.php';
		}
		$posts_footer_result = $importevents->common->wpea_render_common_footer();
	}


	/**
	 * Display notices in admin.
	 *
	 * @since    1.0.0
	 */
	public function wpea_display_notices() {
		global $wpea_errors, $wpea_success_msg, $wpea_warnings, $wpea_info_msg;
		
		if ( ! empty( $wpea_errors ) ) {
			foreach ( $wpea_errors as $error ) :
			    ?>
			    <div class="notice notice-error is-dismissible wpea_notice">
			        <p><?php echo $error; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NonSingularStringLiteralText ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $wpea_success_msg ) ) {
			foreach ( $wpea_success_msg as $success ) :
			    ?>
			    <div class="notice notice-success is-dismissible wpea_notice">
			        <p><?php echo $success; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NonSingularStringLiteralText ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $wpea_warnings ) ) {
			foreach ( $wpea_warnings as $warning ) :
			    ?>
			    <div class="notice notice-warning is-dismissible wpea_notice">
			        <p><?php echo $warning; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NonSingularStringLiteralText ?></p>
			    </div>
			    <?php
			endforeach;
		}

		if ( ! empty( $wpea_info_msg ) ) {
			foreach ( $wpea_info_msg as $info ) :
			    ?>
			    <div class="notice notice-info is-dismissible wpea_notice">
			        <p><?php echo $info; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.I18n.NonSingularStringLiteralText ?></p>
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
		$page = isset( $_GET['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $page != '' && $page == 'import_events' ) {
			$rate_url = 'https://wordpress.org/support/plugin/wp-event-aggregator/reviews/?rate=5#new-post';
			$footer_text .= sprintf(
				// translators: %1$s: Opening HTML tag for WP Event Aggregator, %2$s: Closing HTML tag for WP Event Aggregator, %3$s: The star rating link
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
	public function get_selected_tab_submenu_wpea( $submenu_file ){
		if( !empty( $_GET['page'] ) && esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) == 'import_events' ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$allowed_tabs = array( 'dashboard', 'eventbrite', 'meetup', 'facebook', 'ical', 'scheduled', 'history', 'settings', 'shortcodes', 'support' );
			$tab = isset( $_GET['tab'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['tab'] ) ) ) : 'dashboard'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if( in_array( $tab, $allowed_tabs ) ){
				$submenu_file = admin_url( 'admin.php?page=import_events&tab='.$tab );
			}
		}
		return $submenu_file;
	}
	/**
	 * Render imported Events in history Page.
	 *
	 * @return void
	 */
	public function wpea_view_import_history_handler() {
		if( ! defined( 'IFRAME_REQUEST' ) ){
		    define( 'IFRAME_REQUEST', true );
		}
	    iframe_header();
	    $history_id = isset( $_GET['history'] ) ? absint( $_GET['history'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	    if( $history_id > 0){
	    	$imported_data = get_post_meta($history_id, 'imported_data', true);
	    	if(!empty($imported_data)){
	    		?>
			    <table class="widefat fixed striped">
				<thead>
					<tr>
						<th id="title" class="column-title column-primary"><?php esc_html_e( 'Event', 'wp-event-aggregator' ); ?></th>
						<th id="action" class="column-operation"><?php esc_html_e( 'Created/Updated', 'wp-event-aggregator' ); ?></th>
						<th id="action" class="column-date"><?php esc_html_e( 'Action', 'wp-event-aggregator' ); ?></th>
					</tr>
				</thead>
				<tbody id="the-list">
					<?php
					foreach ($imported_data as $event) {
						?>
						<tr>
							<td class="title column-title">
								<?php 
								printf(
									'<a href="%1$s" target="_blank">%2$s</a>',
									esc_url( get_the_permalink($event['id'] ) ),
									esc_attr( get_the_title( $event['id'] ) )
								);
								?>
							</td>
							<td class="title column-title">
								<?php echo esc_attr( ucfirst( $event['status'] ) ); ?>
							</td>
							<td class="title column-action">
								<?php 
								printf(
									'<a href="%1$s" target="_blank">%2$s</a>',
									esc_url( get_edit_post_link( $event['id'] ) ),
									esc_attr__( 'Edit', 'wp-event-aggregator' )
								);
								?>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
				<?php
	    		?>
	    		<?php
	    	}else{
	    		?>
	    		<div class="wpea_no_import_events">
		    		<?php esc_html_e( 'No Data Found', 'wp-event-aggregator' ); ?>
		    	</div>
	    		<?php
	    	}
	    }else{
	    	?>
    		<div class="wpea_no_import_events">
	    		<?php esc_html_e( 'No Data Found', 'wp-event-aggregator' ); ?>
	    	</div>
    		<?php
	    }
	    ?>
	    <style>
	    	.wpea_no_import_events{
				text-align: center;
				margin-top: 60px;
				font-size: 1.4em;
			}
	    </style>
	    <?php
	    iframe_footer();
	    exit;
	}

	/**
	 * Display Success Messages.
	 *
	 * @since    1.0.0
	 */
	public function setup_success_messages(){
		global $wpea_success_msg, $wpea_errors;
		$wpeam_authorize = isset( $_GET['wpeam_authorize'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['wpeam_authorize'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['wpeam_authorize'] ) && trim( $wpeam_authorize ) != '' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if( trim( $wpeam_authorize ) == '1' ){
				$wpea_success_msg[] = esc_html__( 'Authorized Successfully.', 'wp-event-aggregator' );	
			} elseif( trim( $wpeam_authorize ) == '2' ){
				$wpea_errors[] = esc_html__( 'Please insert Meetup Auth Key and Secret.', 'wp-event-aggregator' );	
			} elseif( trim( $wpeam_authorize ) == '0' ){
				$wpea_errors[] = esc_html__( 'Something went wrong during authorization. Please try again.', 'wp-event-aggregator' );
			}			
		}
	}

	/**
	 * Render Delete Past Event in the wp_events post type
	 * @return void
	 */
	public function wpea_delete_past_events() {

		$current_time = current_time('timestamp');
		$args         = array(
			'post_type'       => 'wp_events',
			'posts_per_page'  => 100,
			'post_status'     => 'publish',
			'fields'          => 'ids',
			'meta_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'end_ts',
					'value'   => current_time( 'timestamp' ) - ( 24 * 3600 ),
					'compare' => '<',      
					'type'    => 'NUMERIC',
				),
			),
		);
		$events = get_posts( $args );

		if ( empty( $events ) ) {
			return;
		}

		foreach ( $events as $event_id ) {
			wp_trash_post( $event_id );
		}
	}

	/**
	 * re-create if the past event cron is delete
	 */
	public function wpea_check_delete_pst_event_cron_status(){

		$wpea_options        = get_option( WPEA_OPTIONS );
		$move_peit_wpeavents = isset( $wpea_options['wpea']['move_peit'] ) ? $wpea_options['wpea']['move_peit'] : 'no';
		if ( $move_peit_wpeavents == 'yes' ) {
			if ( !wp_next_scheduled( 'wpea_delete_past_events_cron' ) ) {
				wp_schedule_event( time(), 'daily', 'wpea_delete_past_events_cron' );
			}
		}else{
			if ( wp_next_scheduled( 'wpea_delete_past_events_cron' ) ) {
				wp_clear_scheduled_hook( 'wpea_delete_past_events_cron' );
			}
		}

	}

	/**
	 * Check if WP-Cron is enabled
	 *
	 * Checks if WP-Cron is enabled and if the current page is the scheduled imports page.
	 * If WP-Cron is disabled, it will display an error message.
	 *
	 * @since 1.0
	 * @return void
	 */
	public function wpea_wp_cron_check() {
		global $wpea_errors;

		if ( ! is_admin() || empty($_GET['page']) || empty($_GET['tab']) || $_GET['page'] !== 'import_events' || $_GET['tab']  !== 'scheduled' ) {
			return;
		}

		if ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ) {
			$wpea_errors[] = __(
				'<strong>Scheduled imports are paused.</strong> WP-Cron is disabled on your site, so scheduled imports won’t run automatically. Enable WP-Cron or set a server cron job to keep imports running smoothly.',
				'wp-event-aggregator'
			);

		}
	}
}
