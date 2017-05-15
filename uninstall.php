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

$wpea_options = get_option( 'wpea_options' );
$delete_wpdata = isset( $wpea_options['wpea']['delete_wpdata'] ) ? $wpea_options['wpea']['delete_wpdata'] : 'no';
if( $delete_wpdata == 'yes' ){
	// Remove options
	delete_option( 'wpea_options' );

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

	// Remove Import History
	$import_history_args = array(
			'post_type'     => 'wpea_import_history',
			'posts_per_page' => -1,
		);
	$import_histories = get_posts( $import_history_args );
	if( !empty( $import_histories ) ){
		foreach ( $import_histories as $import_history ) {
			if( $import_history->ID != '' ){
				wp_delete_post( $import_history->ID, true );
			}		
		}
	}
}
