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
$wpea_delete_wpdata = isset( $wpea_options['wpea']['delete_wpdata'] ) ? $wpea_options['wpea']['delete_wpdata'] : 'no';
if( $wpea_delete_wpdata == 'yes' ){
	// Remove options
	delete_option( 'wpea_options' );

	// Remove schduled Imports
	$wpea_scheduled_import_args = array(
			'post_type'     => 'xt_scheduled_imports',
			'posts_per_page' => -1,
		);
	$wpea_scheduled_imports = get_posts( $wpea_scheduled_import_args );
	if( !empty( $wpea_scheduled_imports ) ){
		foreach ( $wpea_scheduled_imports as $wpea_import ) {
			if( $wpea_import->ID != '' ){
				wp_delete_post( $wpea_import->ID, true );
			}		
		}
	}

	// Remove Import History
	$wpea_import_history_args = array(
			'post_type'     => 'wpea_import_history',
			'posts_per_page' => -1,
		);
	$wpea_import_histories = get_posts( $wpea_import_history_args );
	if( !empty( $wpea_import_histories ) ){
		foreach ( $wpea_import_histories as $wpea_import_history ) {
			if( $wpea_import_history->ID != '' ){
				wp_delete_post( $wpea_import_history->ID, true );
			}		
		}
	}
}
