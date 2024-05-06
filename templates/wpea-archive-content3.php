<?php
/**
 * Template for displaying events
 */

$start_date_str      = get_post_meta( get_the_ID(), 'start_ts', true );
$event_address       = get_post_meta( get_the_ID(), 'venue_name', true );
$venue_address       = get_post_meta( get_the_ID(), 'venue_address', true );
if ( $event_address != '' && $venue_address != '' ) {
	$event_address .= ' - ' . $venue_address;
} elseif ( $venue_address != '' ) {
	$event_address = $venue_address;
}

$wpea_options = get_option( WPEA_OPTIONS );
$accent_color = isset( $wpea_options['wpea']['accent_color'] ) ? $wpea_options['wpea']['accent_color'] : '#039ED7';
$time_format  = isset( $wpea_options['wpea']['time_format'] ) ? $wpea_options['wpea']['time_format'] : '12hours';
// Define the time format string based on the selected option
if ($time_format === '12hours') {
	$time_format_string = 'h:i A';
} elseif ($time_format === '24hours') {
	$time_format_string = 'H:i';
} else {
	$time_format_string = get_option('time_format');
}

// Format the start and end dates
$start_date        = date('l, j F, ' . $time_format_string, $start_date_str);
$event_url         = get_permalink();
$target            = '';
if ( 'yes' === $direct_link ){
	$event_url = get_post_meta( get_the_ID(), 'wpea_event_link', true );
	$target = 'target="_blank"';
}

?>
<div <?php post_class( array( $css_class, 'archive-event' ) ); ?> >
    <div class="wpea-card" >
        <div class="wpea-card-body" >
            <div class="wpea-d-flex wpea-border-bottom wpea-pb-3 wpea-align-items" >
                <div class="wpea-badge wpea-bg-label wpea-d-flex wpea-flex-column wpea-justify-content-center wpea-px-3 wpea-rounded-3 wpea-me-3 wpea-align-items" >
                    <span class="wpea-fw-bold wpea-mt-5" ><?php echo esc_attr( date_i18n( 'M', $start_date_str ) ); ?></span>
                    <span class="wpea-fw-bold wpea-mt-0 wpea-mb-5 " ><?php echo esc_attr( date_i18n( 'd', $start_date_str ) ); ?></span>
                </div>
                <div class="wpea-w-75 wpea-text-limit wpea-fw-bold" >
                    <a class="wpea-text-deco" style="color:<?php echo esc_attr( $accent_color ); ?>;" href="<?php echo esc_url( $event_url ); ?>" <?php echo esc_attr( $target ); ?> ><span class="wpea-card-title wpea-mb-1" ><?php the_title(); ?></span></a>
                </div>
            </div>
            <div class="wpea-h-60">
                <div class="wpea-d-flex wpea-mt-3 wpea-gap-2 wpea-align-items" >
                    <span class="wpea-d-flex" >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                            <path fill="<?php echo esc_attr( $accent_color ); ?>" d="M12 20a8 8 0 0 0 8-8a8 8 0 0 0-8-8a8 8 0 0 0-8 8a8 8 0 0 0 8 8m0-18a10 10 0 0 1 10 10a10 10 0 0 1-10 10C6.47 22 2 17.5 2 12A10 10 0 0 1 12 2m.5 5v5.25l4.5 2.67l-.75 1.23L11 13V7z"/>
                        </svg>
                    </span>
                    <div >
                        <p class="wpea-mb-0"><?php echo esc_attr( $start_date ); ?></p>
                    </div>
                </div>
                <div class="wpea-d-flex wpea-gap-2 wpea-align-items" >
                    <?php if( !empty( $event_address ) ){ ?>
                        <span class="wpea-d-flex" >
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                <path fill="<?php echo esc_attr( $accent_color ); ?>" d="M12 6.5A2.5 2.5 0 0 1 14.5 9a2.5 2.5 0 0 1-2.5 2.5A2.5 2.5 0 0 1 9.5 9A2.5 2.5 0 0 1 12 6.5M12 2a7 7 0 0 1 7 7c0 5.25-7 13-7 13S5 14.25 5 9a7 7 0 0 1 7-7m0 2a5 5 0 0 0-5 5c0 1 0 3 5 9.71C17 12 17 10 17 9a5 5 0 0 0-5-5"/>
                            </svg>
                        </span>
                        <div class="wpea-w-90" >
                            <p class="wpea-mb-0 wpea-text-limit" ><?php echo esc_attr( ucfirst( $event_address ) ); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>  
</div>
<style>
.wpea-bg-label {
	background-color: <?php echo esc_attr( $accent_color ); ?>;
	color: #fff;
}
.wpea-badge {
	background: <?php echo esc_attr( $accent_color ); ?>;
	border-color: <?php echo esc_attr( $accent_color ); ?>;
	color: #fff;
}
</style>