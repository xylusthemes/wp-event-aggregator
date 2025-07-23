<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $importevents;
$counts = $importevents->common->wpea_get_wpea_events_counts();

?>
<div class="wpea-container" style="margin-top: 60px;">
    <div class="wpea-wrap" >
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <?php 
                    do_action( 'wpea_display_all_notice' );
                ?>
                <div class="delete_notice"></div>
                <div id="postbox-container-2" class="postbox-container">
                    <div class="wpea-app">
                        <div class="wpea-card" style="margin-top:20px;" >			
                            <div class="wpea-content"  aria-expanded="true"  >
                                <div id="wpea-dashboard" class="wrap about-wrap" >
                                    <div class="wpea-w-row" >
                                        <div class="wpea-intro-section" >
                                            <div class="wpea-w-box-content wpea-intro-section-welcome" >
                                                <h3><?php esc_attr_e( 'Getting started with Import Eventbrite, Meetup, Facebook and iCal Events', 'wp-event-aggregator' ); ?></h3>
                                                <p style="margin-bottom: 25px;"><?php esc_attr_e( 'In this video, you can learn how to Import Eventbrite, Meetup, Facebook and iCal Events into your website. Please watch this 7 minutes video to the end.', 'wp-event-aggregator' ); ?></p>
                                            </div>
                                            <div class="wpea-w-box-content wpea-intro-section-ifarme" >
                                                <iframe width="850" height="450" src="https://www.youtube.com/embed/swl_2OqXTnc?si=A_jRhlcNqYWIQETt" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen=""></iframe>
                                            </div>
                                            <div class="wpea-intro-section-links wp-core-ui" >
                                                <a class="wpea-intro-section-link-tag button wpea-button-primary button-hero" href="<?php echo esc_attr( admin_url('post-new.php?post_type=wp_events') ); ?>" target="_blank"><?php esc_attr_e( 'Add New Event', 'wp-event-aggregator' ); ?></a>
                                                <a class="wpea-intro-section-link-tag button wpea-button-secondary button-hero" href="<?php echo esc_attr( admin_url('admin.php?page=import_events&tab=settings') ); ?>"target="_blank"><?php esc_attr_e( 'Settings', 'wp-event-aggregator' ); ?></a>
                                                <a class="wpea-intro-section-link-tag button wpea-button-secondary button-hero" href="https://docs.xylusthemes.com/docs/wp-event-aggregator/" target="_blank"><?php esc_attr_e( 'Documentation', 'wp-event-aggregator' ); ?></a>
                                            </div>
                                        </div>

                                        <div class="wpea-counter-main-container" >
                                            <div class="wpea-col-sm-3" >
                                                <div class="wpea-w-box " >
                                                    <p class="wpea_dash_count"><?php echo esc_attr( $counts['all'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Total Events', 'wp-event-aggregator' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="wpea-col-sm-3" >
                                                <div class="wpea-w-box " >
                                                    <p class="wpea_dash_count"><?php echo esc_attr( $counts['upcoming'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Upcoming Events', 'wp-event-aggregator' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="wpea-col-sm-3" >
                                                <div class="wpea-w-box " >
                                                    <p class="wpea_dash_count"><?php echo esc_attr( $counts['past'] ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Past Events', 'wp-event-aggregator' ); ?></strong></span>
                                                </div>
                                            </div>
                                            <div class="wpea-col-sm-3" >
                                                <div class="wpea-w-box " >
                                                    <p class="wpea_dash_count"><?php echo esc_attr( WPEA_VERSION ); ?></p>
                                                    <span><strong><?php esc_attr_e( 'Version', 'wp-event-aggregator' ); ?></strong></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear: both"></div>
        </div>
    </div>
</div>