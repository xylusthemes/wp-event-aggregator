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
	$image_date = date_i18n('F+d', $event_date );
	$image_url[] =  "http://placehold.it/420x150?text=".$image_date;
}
?>
<a href="<?php echo esc_url( get_permalink() ) ?>">	
	<div class="<?php echo $css_class; ?> archive-event <?php post_class(); ?>">
		<div class="wepa_event" >
			<div class="img_placeholder" style=" background: url('<?php echo $image_url[0]; ?>') no-repeat left top;"></div>
			<div class="event_details">
				<div class="event_date">
					<span class="month"><?php echo date_i18n('M', $event_date) ; ?></span>
					<span class="date"> <?php echo date_i18n('d', $event_date) ; ?> </span>
				</div>
				<div class="event_desc">
					<a href="<?php echo esc_url( get_permalink() ) ?>" rel="bookmark">
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