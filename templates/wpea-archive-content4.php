<?php
/**
 * Template for displaying events
 */

$start_date_str = get_post_meta( get_the_ID(), 'start_ts', true );
$event_address  = get_post_meta( get_the_ID(), 'venue_name', true );
$venue_address  = get_post_meta( get_the_ID(), 'venue_address', true );
if ( '' != $event_address && '' != $venue_address ) {
	$event_address .= ' - ' . $venue_address;
} elseif ( '' != $venue_address ) {
	$event_address = $venue_address;
}

$wpea_options = get_option( WPEA_OPTIONS );
$accent_color = isset( $wpea_options['wpea']['accent_color'] ) ? $wpea_options['wpea']['accent_color'] : '#039ED7';
$time_format  = isset( $wpea_options['wpea']['time_format'] ) ? $wpea_options['wpea']['time_format'] : '12hours';

if ( '12hours' === $time_format ) {
	$time_format_string = 'h:i A';
} elseif ( '24hours' === $time_format ) {
	$time_format_string = 'H:i';
} else {
	$time_format_string = get_option('time_format');
}
$start_date        = date('l, j F, ' . $time_format_string, $start_date_str);
$event_source_url  = get_permalink();
$event_url         = get_permalink();
$target            = '';
if ( 'yes' === $direct_link ){
	$event_url = get_post_meta( get_the_ID(), 'wpea_event_link', true );
	$target    = 'target="_blank"';
}

?>
<div <?php post_class( array( $css_class, 'archive-event' ) ); ?> >
    <div class="wpea_widget_style1 wpea_widget wpea_event" >
        <div class="event_details" style="height: auto;">
            <div class="event_date event_date_style4" >
                <div>
                    <span class="month"><?php echo esc_attr( date_i18n( 'M', $start_date_str ) ); ?></span>
                    <span class="date"> <?php echo esc_attr( date_i18n( 'd', $start_date_str ) ); ?> </span>
                </div>
            </div>				
            
            <div class="event_desc">
                <a class="wpea-text-deco" style="color:<?php echo esc_attr( $accent_color ); ?>;" href="<?php echo esc_url( $event_url ); ?>" <?php echo esc_attr( $target ); ?> >
                    <?php the_title( '<div class="event_title">', '</div>' ); ?>
                </a>

                <?php 
                if( $start_date != '' ){
                    ?>
                    <div><p class="wpea-mb-0 widget_event_sdate"><i class="fa fa-calendar"></i> <?php echo esc_attr( $start_date ); ?></p></div>
                    <?php
                }

                if( $event_address != '' ){ ?>
                    <div class="wpea-w-90" >
                        <p class="wpea-mb-0 wpea-text-limit" ><i class="fa fa-map-marker"></i><?php echo esc_attr( ucfirst( $event_address ) ); ?></p>
                    </div>

                <?php }	?>
            </div>
            <div style="clear: both"></div>
        </div>
    </div>
</div>