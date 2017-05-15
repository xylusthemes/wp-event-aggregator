<?php
/**
 * Common functions
 *
 * @package     WP_Event_Aggregator
 * @subpackage  Common functions
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Render multi-select category input for Events import.
 *
 * @since 1.0
 * @return void
*/
function wpea_render_category_input(){
	$wpea_event_cats = get_terms( 'tribe_events_cat', array( 'hide_empty' => 0 ) );
	?>
	<tr class="event_cats_wrapper">
		<th scope="row">
			<?php esc_attr_e( 'Event Categories for Event Import','wp-event-aggregator' ); ?> : 
		</th>
		<td>
			<select name="event_cats[]" multiple="multiple">
	            <?php if( ! empty( $wpea_event_cats ) ): ?>
	                <?php foreach ($wpea_event_cats as $wpea_cat ): ?>
	                    <option value="<?php echo $wpea_cat->term_id; ?>">
	                    	<?php echo $wpea_cat->name; ?>                                	
	                    </option>
	                <?php endforeach; ?>
	            <?php endif; ?>
	        </select>
	        <span class="wpea_small">
	            <?php esc_attr_e( 'These categories are assign to imported event.', 'wp-event-aggregator' ); ?>
	        </span>
		</td>
	</tr>
	<?php
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


/**
 * Render dropdown for Imported event status.
 *
 * @since 1.0
 * @return void
*/
function wpea_render_eventstatus_input(){
	?>
	<tr class="event_status_wrapper">
		<th scope="row">
			<?php esc_attr_e( 'Status','wp-event-aggregator' ); ?> :
		</th>
		<td>
			<select name="event_status" >
                <option value="publish">
                    <?php esc_html_e( 'Published','wp-event-aggregator' ); ?>
                </option>
                <option value="pending">
                    <?php esc_html_e( 'Pending','wp-event-aggregator' ); ?>
                </option>
                <option value="draft">
                    <?php esc_html_e( 'Draft','wp-event-aggregator' ); ?>
                </option>
            </select>
		</td>
	</tr>
	<?php
}

function wpea_render_import_frequency(){
	?>
	<select name="import_frequency" class="import_frequency" disabled="disabled">
        <option value='hourly'>
            <?php esc_html_e( 'Once Hourly','wp-event-aggregator' ); ?>
        </option>
        <option value='twicedaily'>
            <?php esc_html_e( 'Twice Daily','wp-event-aggregator' ); ?>
        </option>
        <option value="daily" selected="selected">
            <?php esc_html_e( 'Once Daily','wp-event-aggregator' ); ?>
        </option>
        <option value="weekly" >
            <?php esc_html_e( 'Once Weekly','wp-event-aggregator' ); ?>
        </option>
        <option value="monthly">
            <?php esc_html_e( 'Once a Month','wp-event-aggregator' ); ?>
        </option>
    </select>
	<?php
}

function wpea_render_import_type(){
	?>
	<select name="import_type" id="import_type" disabled="disabled">
    	<option value="onetime" disabled="disabled" ><?php esc_attr_e( 'One-time Import','wp-event-aggregator' ); ?></option>
    	<option value="scheduled" disabled="disabled" selected="selected" ><?php esc_attr_e( 'Scheduled Import','wp-event-aggregator' ); ?></option>
    </select>
    <span class="hide_frequency">
    	<?php wpea_render_import_frequency(); ?>
    </span>
    <?php
    do_action( 'wpea_render_pro_notice' );
}


/**
 * This function only for debuging
 *
 * @since 1.0.0
 */
function wp_p1( $data, $exit = false ){

	echo '<pre>';
	if ( is_array( $data ) || is_object( $data ) ){
		print_r( $data );
	} else {
		echo $data; 
	}
	echo '</pre>';
	if ( $exit ) {
		exit();
	}

}


/**
 * remove query string from URL.
 *
 * @since 1.0.0
 */
function wpea_remove_query_string_from_url( $url ) {
	$query = parse_url( $url, PHP_URL_QUERY );

	if ( is_string( $query ) ) {
		$url = str_replace( "?$query", '', $url );
	}
	$url_array = explode( '#', $url );
	return stripslashes( $url_array[0] );
}

/**
 * remove query string from URL.
 *
 * @since 1.0.0
 */
function convert_datetime_to_db_datetime( $datetime ) {
	try {
		$datetime = new DateTime( $datetime );
		return $datetime->format( 'Y-m-d H:i:s' );
	}
	catch ( Exception $e ) {
		return $datetime;
	}
}

/**
 * remove query string from URL.
 *
 * @since 1.0.0
 */
function wpea_clean_url( $url ) {
	
	$url = str_replace( '&amp;#038;', '&', $url );
	$url = str_replace( '&#038;', '&', $url );
	return $url;
	
}

/**
 * Display upgrade to pro notice in form.
 *
 * @since 1.0.0
 */
function wpea_render_pro_notice(){
	?>
	<span class="wpea_small">
        <?php printf( '<span style="color: red">%s</span> <a href="' . WPEA_PLUGIN_BUY_NOW_URL. '" target="_blank" >%s</a>', __( 'Available in Pro version.', 'wp-event-aggregator' ), __( 'Upgrade to PRO', 'wp-event-aggregator' ) ); ?>
    </span>
	<?php
}
add_action( 'wpea_render_pro_notice', 'wpea_render_pro_notice' );


/**
 * Display Ticket Section after events.
 *
 * @since 1.0.0
 */
function wpea_add_ticket_section() {
	$xt_post_type =  get_post_type();
	$event_id = get_the_ID();
	if ( $event_id > 0 ) {
		if( WPEA_TEC_POSTTYPE == $xt_post_type ){
			$eventbrite_id = get_post_meta( $event_id, 'wpea_eventbrite_event_id', true );
			if ( $eventbrite_id && $eventbrite_id > 0 && is_numeric( $eventbrite_id ) ) {
				$ticket_section = wpea_get_ticket_section( $eventbrite_id );
				echo $ticket_section;
			}
		}
	}
}
add_action( 'tribe_events_single_event_after_the_meta', 'wpea_add_ticket_section' );

function wpea_get_ticket_section( $eventbrite_id = 0 ) {
	$options = wpea_get_import_options( 'eventbrite' );
	
	$enable_ticket_sec = isset( $options['enable_ticket_sec'] ) ? $options['enable_ticket_sec'] : 'no';
	if ( 'yes' != $enable_ticket_sec ) {
		return '';
	}

	if( $eventbrite_id > 0 ){
		ob_start();
		?>
		<div class="eventbrite-ticket-section" style="width:100%; text-align:left;">
			<iframe id="eventbrite-tickets-<?php echo $eventbrite_id; ?>" src="http://www.eventbrite.com/tickets-external?eid=<?php echo $eventbrite_id; ?>" style="width:100%;height:300px; border: 0px;"></iframe>
		</div>
		<?php
		$ticket = ob_get_clean();
		return $ticket;
	}else{
		return '';
	}

}