<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Template for displaying events
 */

$wpea_start_date_str = get_post_meta( get_the_ID(), 'start_ts', true );
$wpea_event_address  = get_post_meta( get_the_ID(), 'venue_name', true );
$wpea_venue_address  = get_post_meta( get_the_ID(), 'venue_address', true );
if ( '' != $wpea_event_address && '' != $wpea_venue_address ) {
	$wpea_event_address .= ' - ' . $wpea_venue_address;
} elseif ( '' != $wpea_venue_address ) {
	$wpea_event_address = $wpea_venue_address;
}

$wpea_options = get_option( WPEA_OPTIONS );
$wpea_accent_color = isset( $wpea_options['wpea']['accent_color'] ) ? $wpea_options['wpea']['accent_color'] : '#039ED7';
$wpea_time_format  = isset( $wpea_options['wpea']['time_format'] ) ? $wpea_options['wpea']['time_format'] : '12hours';

if ( '12hours' === $wpea_time_format ) {
	$wpea_time_format_string = 'h:i A';
} elseif ( '24hours' === $wpea_time_format ) {
	$wpea_time_format_string = 'H:i';
} else {
	$wpea_time_format_string = get_option('time_format');
}
$wpea_start_date        = gmdate('l, j F, ' . $wpea_time_format_string, $wpea_start_date_str);
$wpea_event_source_url  = get_permalink();
$wpea_event_url         = get_permalink();
$wpea_target            = '';
if ( 'yes' === $direct_link ){
	$wpea_event_url = get_post_meta( get_the_ID(), 'wpea_event_link', true );
	$wpea_target    = 'target="_blank"';
}

?>
<div <?php post_class( array( $css_class, 'archive-event' ) ); ?> >
    <div class="wpea_widget_style1 wpea_widget wpea_event" >
        <div class="event_details" style="height: auto;">
            <div class="event_date event_date_style4" >
                <div>
                    <span class="month"><?php echo esc_attr( date_i18n( 'M', $wpea_start_date_str ) ); ?></span>
                    <span class="date"> <?php echo esc_attr( date_i18n( 'd', $wpea_start_date_str ) ); ?> </span>
                </div>
            </div>				
            
            <div class="event_desc">
                <a class="wpea-text-deco" style="color:<?php echo esc_attr( $wpea_accent_color ); ?>;" href="<?php echo esc_url( $wpea_event_url ); ?>" <?php echo esc_attr( $wpea_target ); ?> >
                    <?php the_title( '<div class="event_title">', '</div>' ); ?>
                </a>

                <?php 
                if( $wpea_start_date != '' ){
                    ?>
                    <div><p class="wpea-mb-0 widget_event_sdate"><i class="fa fa-calendar"></i> <?php echo esc_attr( $wpea_start_date ); ?></p></div>
                    <?php
                }

                if( $wpea_event_address != '' ){ ?>
                    <div class="wpea-w-90" >
                        <p class="wpea-mb-0 wpea-text-limit" ><i class="fa fa-map-marker"></i><?php echo esc_attr( ucfirst( $wpea_event_address ) ); ?></p>
                    </div>

                <?php }	?>
            </div>
            <div style="clear: both"></div>
        </div>
    </div>
</div>