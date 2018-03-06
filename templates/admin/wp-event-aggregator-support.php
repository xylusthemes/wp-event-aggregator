<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $importevents;
$open_source_support_url = 'https://wordpress.org/support/plugin/wp-event-aggregator/';
$support_url = 'https://xylusthemes.com/support/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin';

$review_url = 'https://wordpress.org/support/plugin/wp-event-aggregator/reviews/?rate=5#new-post';
$facebook_url = 'https://www.facebook.com/xylusinfo/';
$twitter_url = 'https://twitter.com/XylusThemes/';

?>
<div class="wpea_container">
    <div class="wpea_row">
        <div class="wpea-column support_well">
        	<h3><?php esc_attr_e( 'Getting Support', 'wp-event-aggregator' ); ?></h3>
            <p><?php _e( 'Thanks you for using WP Event Aggregator, We are sincerely appreciate your support and we’re excited to see you using our plugins.','wp-event-aggregator' ); ?> </p>
            <p><?php _e( 'Our support team is always around to help you.','wp-event-aggregator' ); ?></p>
                
            <p><strong><?php _e( 'Looking for free support?','wp-event-aggregator' ); ?></strong></p>
            <a class="button button-secondary" href="<?php echo $open_source_support_url; ?>" target="_blank" >
                <?php _e( 'Open-source forum on WordPress.org','wp-event-aggregator' ); ?>
            </a>

            <p><strong><?php _e( 'Looking for more immediate support?','wp-event-aggregator' ); ?></strong></p>
            <p><?php _e( 'We offer premium support on our website with the purchase of our premium plugins.','wp-event-aggregator' ); ?>
            </p>
            
            <a class="button button-primary" href="<?php echo $support_url; ?>" target="_blank" >
                <?php _e( 'Contact us directly (Premium Support)','wp-event-aggregator' ); ?>
            </a>

            <p><strong><?php _e( 'Enjoying WP Event Aggregator or have feedback?','wp-event-aggregator' ); ?></strong></p>
            <a class="button button-secondary" href="<?php echo $review_url; ?>" target="_blank" >Leave us a review</a> 
            <a class="button button-secondary" href="<?php echo $twitter_url; ?>" target="_blank" >Follow us on Twitter</a> 
            <a class="button button-secondary" href="<?php echo $facebook_url; ?>" target="_blank" >Like us on Facebook</a>
        </div>

        <?php 
        $plugins = array();
        $plugin_list = $importevents->admin->get_xyuls_themes_plugins();
        if( !empty( $plugin_list ) ){
            foreach ($plugin_list as $key => $value) {
                $plugins[] = $importevents->admin->get_wporg_plugin( $key );
            }
        }
        ?>
        <div class="" style="margin-top: 20px;">
            <h3 class="setting_bar"><?php _e( 'Plugins you should try','wp-event-aggregator' ); ?></h3>
            <?php 
            if( !empty( $plugins ) ){
                foreach ($plugins as $plugin ) {
                    ?>
                    <div class="plugin_box">
                        <?php if( $plugin->banners['low'] != '' ){ ?>
                            <img src="<?php echo $plugin->banners['low']; ?>" class="plugin_img" title="<?php echo $plugin->name; ?>">
                        <?php } ?>                    
                        <div class="plugin_content">
                            <h3><?php echo $plugin->name; ?></h3>

                            <?php wp_star_rating( array(
                            'rating' => $plugin->rating,
                            'type'   => 'percent',
                            'number' => $plugin->num_ratings,
                            ) );?>

                            <?php if( $plugin->version != '' ){ ?>
                                <p><strong><?php _e( 'Version:','wp-event-aggregator' ); ?> </strong><?php echo $plugin->version; ?></p>
                            <?php } ?>

                            <?php if( $plugin->requires != '' ){ ?>
                                <p><strong><?php _e( 'Requires:','wp-event-aggregator' ); ?> </strong> <?php _e( 'WordPress ','wp-event-aggregator' ); echo $plugin->requires; ?>+</p>
                            <?php } ?>

                            <?php if( $plugin->active_installs != '' ){ ?>
                                <p><strong><?php _e( 'Active Installs:','wp-event-aggregator' ); ?> </strong><?php echo $plugin->active_installs; ?>+</p>
                            <?php } ?>

                            <?php //print_r( $plugin ); ?>
                            <a class="button button-secondary" href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin='. $plugin->slug.'&TB_iframe=1&width=772&height=600'); ?>" target="_blank">
                                <?php _e( 'Install Now','wp-event-aggregator' ); ?>
                            </a>
                            <a class="button button-primary" href="<?php echo $plugin->homepage; ?>" target="_blank">
                                <?php _e( 'Buy Now','wp-event-aggregator' ); ?>
                            </a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
            <div style="clear: both;">
        </div>
    </div>

</div>