<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       http://xylusthemes.com
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove options
delete_option( WPEA_OPTIONS );

// Remove schduled Imports
$scheduled_import_args = array(
		'post_type'     => 'xt_scheduled_imports',
		'posts_per_page' => -1,
	);
$scheduled_imports = get_posts( $scheduled_import_args );
if( !empty( $scheduled_imports ) ){
	foreach ( $scheduled_imports as $import ) {
		if( $import->ID != '' ){
			wp_delete_post( $import->ID, true );
		}		
	}
}
