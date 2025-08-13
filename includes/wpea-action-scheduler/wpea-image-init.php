<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Load Action Scheduler if not loaded
if ( ! class_exists( 'ActionScheduler' ) ) {
	require_once WPEA_PLUGIN_DIR . 'includes/wpea-action-scheduler/action-scheduler/action-scheduler.php';
}

// Load custom scheduler
require_once WPEA_PLUGIN_DIR . 'includes/wpea-action-scheduler/class-wpea-event-image-scheduler.php';

// Register hook
add_action( 'wpea_process_image_download', array( 'WPEA_Event_Image_Scheduler', 'process_image_download' ), 10, 2 );
