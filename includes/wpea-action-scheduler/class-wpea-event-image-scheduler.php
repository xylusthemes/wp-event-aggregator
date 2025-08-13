<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPEA_Event_Image_Scheduler {

	public static function schedule_image_download( $event_id, $image_url, $event_args ) {
		if ( ! empty( $event_id ) && ! empty( $image_url ) ) {
			$ac_run_time = ( !empty($event_args['import_type']) && $event_args['import_type'] === 'onetime' ) ? 30 : 60;
			as_schedule_single_action( time() + $ac_run_time, 'wpea_process_image_download', array( $event_id, $image_url ), 'wpea_image_group' );
		}
	}

	public static function process_image_download( $event_id, $image_url ) {
        global $importevents;
		if ( empty( $event_id ) || empty( $image_url ) ) return;
        
		if ( method_exists( $importevents->common, 'setup_featured_image_to_event' ) ) {
			$importevents->common->setup_featured_image_to_event( $event_id, $image_url );
		}
	}
}
