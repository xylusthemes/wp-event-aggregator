<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template for displaying events
 */

$wpea_event_date = get_post_meta( get_the_ID(), 'event_start_date', true );
if( $wpea_event_date != '' ){
	$wpea_event_date = strtotime( $wpea_event_date );	
}
$wpea_event_address = get_post_meta( get_the_ID(), 'venue_name', true );
$wpea_venue_address = get_post_meta( get_the_ID(), 'venue_address', true );
if( $wpea_event_address != '' && $wpea_venue_address != '' ){
	$wpea_event_address .= ' - '.$wpea_venue_address;
}elseif( $wpea_venue_address != '' ){
	$wpea_event_address = $wpea_venue_address;
}
$wpea_image_url = array();
$wpea_thumbnail_id = get_post_meta( get_the_ID(), '_thumbnail_id', true );
if ( ! empty( $wpea_thumbnail_id ) ) {
	$wpea_image_url = wp_get_attachment_image_src( $wpea_thumbnail_id, 'full' );
}
if ( empty( $wpea_image_url ) ) {
	$wpea_external_image = get_post_meta( get_the_ID(), '_wpea_external_image_url', true );
	if ( ! empty( $wpea_external_image ) ) {
		$wpea_image_url[] = $wpea_external_image;
	} else {
		$wpea_start_date_str      = get_post_meta( get_the_ID(), 'start_ts', true );
		$wpea_image_date  = date_i18n( 'F+d', $wpea_start_date_str );
		$wpea_image_url[] = 'https://dummyimage.com/420x210/ccc/969696.png&text=' . $wpea_image_date;
	}
}
$wpea_target = '';
$wpea_event_source_url = esc_url( get_permalink() );
if ('yes' === $direct_link) { 
	$wpea_event_origin = get_post_meta( get_the_ID(), 'wpea_event_origin', true );
    if ( $wpea_event_origin =='facebook' ) {
        $wpea_facebook_event_id = get_post_meta(get_the_ID(), 'wpea_event_id', true);
        $wpea_event_source_url = "https://www.facebook.com/events/". $wpea_facebook_event_id;
    } elseif( $wpea_event_origin =='eventbrite' ) {
        $wpea_eventbrite_event_id = get_post_meta(get_the_ID(), 'wpea_event_id', true);
        $wpea_event_source_url = "https://www.eventbrite.com/e/". $wpea_eventbrite_event_id;
    } elseif($wpea_event_origin =='meetup') {
        $wpea_meetup_organizer_link = get_post_meta(get_the_ID(), 'organizer_url', true);
        $wpea_event_source_url = $wpea_meetup_organizer_link .'events/'.get_post_meta(get_the_ID(), 'wpea_event_id', true);
    } elseif($wpea_event_origin =='ical') {
        $wpea_event_source_url = get_post_meta(get_the_ID(), 'wpea_event_link', true);
	}
	if( empty($wpea_event_source_url )){
		$wpea_event_source_url = esc_url( get_permalink() ); 
	}
    $wpea_target = 'target="_blank"';
}
?>
<a href="<?php echo esc_url( $wpea_event_source_url ); ?>" <?php echo esc_attr( $wpea_target ); ?>>	
	<div <?php post_class( array( $css_class, 'archive-event' ) ); ?>>
		<div class="wepa_event" >
			<div class="img_placeholder" style=" background: url('<?php echo esc_url( $wpea_image_url[0] ); ?>') no-repeat left top;"></div>
			<div class="event_details">
				<div class="event_date">
					<span class="month"><?php echo esc_attr( date_i18n( 'M', $wpea_event_date ) ); ?></span>
					<span class="date"> <?php echo esc_attr( date_i18n( 'd', $wpea_event_date ) ); ?> </span>
				</div>
				<div class="event_desc">
					<a href="<?php echo esc_url( $wpea_event_source_url ); ?>" <?php echo esc_attr( $wpea_target ); ?> rel="bookmark">
					<?php the_title( '<div class="event_title">','</div>' ); ?>
					</a>
					<?php if( $wpea_event_address != '' ){ ?>
						<div class="event_address"><i class="fa fa-map-marker"></i>  <?php echo esc_attr( $wpea_event_address ); ?></div>
					<?php }	?>
				</div>
				<div style="clear: both"></div>
			</div>
		</div>
	</div>
</a>