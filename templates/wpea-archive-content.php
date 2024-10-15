<?php
/**
 * Template for displaying events
 */

$event_date = get_post_meta( get_the_ID(), 'event_start_date', true );
if( $event_date != '' ){
	$event_date = strtotime( $event_date );	
}
$event_address = get_post_meta( get_the_ID(), 'venue_name', true );
$venue_address = get_post_meta( get_the_ID(), 'venue_address', true );
if( $event_address != '' && $venue_address != '' ){
	$event_address .= ' - '.$venue_address;
}elseif( $venue_address != '' ){
	$event_address = $venue_address;
}
$image_url =  array();
if ( '' !== get_the_post_thumbnail() ){
	$image_url =  wp_get_attachment_image_src( get_post_thumbnail_id(  get_the_ID() ), 'full' );
}else{
	if ( '' !== $wpea_ed_image ) {
		$image_url = wp_get_attachment_image_src( $wpea_ed_image, 'full' );
	}else{
		$image_date  = date_i18n( 'F+d', $event_date );
		$image_url[] = 'https://dummyimage.com/420x210/ccc/969696.png?text=' . $image_date;
	}
}
$target = '';
$event_source_url = esc_url( get_permalink() );
if ('yes' === $direct_link) { 
	$event_origin = get_post_meta( get_the_ID(), 'wpea_event_origin', true );
    if ( $event_origin =='facebook' ) {
        $facebook_event_id = get_post_meta(get_the_ID(), 'wpea_event_id', true);
        $event_source_url = "https://www.facebook.com/events/". $facebook_event_id;
    } elseif( $event_origin =='eventbrite' ) {
        $eventbrite_event_id = get_post_meta(get_the_ID(), 'wpea_event_id', true);
        $event_source_url = "https://www.eventbrite.com/e/". $eventbrite_event_id;
    } elseif($event_origin =='meetup') {
        $meetup_organizer_link = get_post_meta(get_the_ID(), 'organizer_url', true);
        $event_source_url = $meetup_organizer_link .'events/'.get_post_meta(get_the_ID(), 'wpea_event_id', true);
    } elseif($event_origin =='ical') {
        $event_source_url = get_post_meta(get_the_ID(), 'wpea_event_link', true);
	}
	if( empty($event_source_url )){
		$event_source_url = esc_url( get_permalink() ); 
	}
    $target = 'target="_blank"';
}
?>
<a href="<?php echo $event_source_url; ?>" <?php echo $target; ?>>	
	<div <?php post_class( array( $css_class, 'archive-event' ) ); ?>>
		<div class="wepa_event" >
			<div class="img_placeholder" style=" background: url('<?php echo $image_url[0]; ?>') no-repeat left top;"></div>
			<div class="event_details">
				<div class="event_date">
					<span class="month"><?php echo date_i18n('M', $event_date) ; ?></span>
					<span class="date"> <?php echo date_i18n('d', $event_date) ; ?> </span>
				</div>
				<div class="event_desc">
					<a href="<?php echo $event_source_url; ?>" <?php echo $target; ?> rel="bookmark">
					<?php the_title( '<div class="event_title">','</div>' ); ?>
					</a>
					<?php if( $event_address != '' ){ ?>
						<div class="event_address"><i class="fa fa-map-marker"></i>  <?php echo $event_address; ?></div>
					<?php }	?>
				</div>
				<div style="clear: both"></div>
			</div>
		</div>
	</div>
</a>