<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $importevents;
$open_source_support_url = 'https://wordpress.org/support/plugin/wp-event-aggregator/';
$support_url = 'https://xylusthemes.com/support/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin';

$review_url = 'https://wordpress.org/support/plugin/wp-event-aggregator/reviews/?rate=5#new-post';
$facebook_url = 'https://www.facebook.com/xylusinfo/';
$twitter_url = 'https://twitter.com/XylusThemes/';
add_thickbox();
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
            <a class="button button-secondary" href="<?php echo $review_url; ?>" target="_blank" ><?php _e( 'Leave us a review', 'wp-event-aggregator' ); ?></a> 
            <a class="button button-secondary" href="<?php echo $twitter_url; ?>" target="_blank" ><?php _e( 'Follow us on Twitter', 'wp-event-aggregator' ); ?></a> 
            <a class="button button-secondary" href="<?php echo $facebook_url; ?>" target="_blank" ><?php _e( 'Like us on Facebook', 'wp-event-aggregator' ); ?></a>
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
        <h3 class="setting_bar"><?php _e( 'Plugins you should try','wp-event-aggregator' ); ?></h3>
        <div class="plugin-list" style="margin-top: 20px;">
            <?php 
            if( !empty( $plugins ) ){
                foreach ($plugins as $plugin ) {
            ?>
                <div class="plugin-card">
                    <div class="plugin-card-top">
                        <div class="name column-name">
                            <h3>
                            <a href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin='. $plugin->slug.'&TB_iframe=1&width=772&height=600'); ?>" target="_blank" class="thickbox open-plugin-details-modal">
                            <img src="<?php echo $plugin->banners['high']; ?>" class="plugin-icon" alt="Plugin_photo">
                            </a>
                            </h3>
                        </div>
                    </div>
                    <div class="plugin-card-bottom">
                        <div style="font-size:16px;">
                            <strong><a class="plugin_name thickbox open-plugin-details-modal" href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin->slug . '&TB_iframe=1&width=600&height=550'); ?>"><?php echo $plugin->name; ?></strong>
                        </div><br>
                        <div class="vers column-rating">
                                <?php wp_star_rating( array(
                                        'rating' => $plugin->rating,
                                        'type'   => 'percent',
                                        'number' => $plugin->num_ratings,
                                    ) );
                                ?>
                        </div>
                        <div class="column-updated">
                            <a class="button button-secondary thickbox open-plugin-details-modal" href="<?php echo admin_url( 'plugin-install.php?tab=plugin-information&plugin=' . $plugin->slug . '&TB_iframe=1&width=772&height=600' ); ?>">
                            <?php _e( 'Install Now', 'wp-event-aggregator' ); ?>
                            </a>
                            <a class="button button-primary" href="<?php echo $plugin->homepage . '?utm_source=crosssell&utm_medium=web&utm_content=supportpage&utm_campaign=freeplugin'; ?>" target="_blank">
                            <?php _e( 'Buy Now', 'wp-event-aggregator' ); ?>
                            </a> 			
                        </div>
                        <div class="column-downloaded">
                            <strong><?php echo $plugin->active_installs; ?></strong><?php _e( '+ Active Installations', 'wp-event-aggregator' ); ?>
                        </div>
                        <div class="column-compatibility">
					        <strong><?php _e('Version:', 'wp-event-aggregator'); ?></strong><?php echo $plugin->version;?></span>				
                        </div>
                    </div>
                </div>
                <?php
                }
            }
            ?>
            <div style="clear: both;">
            <style>
            .plugin_name{
                text-decoration : none;
                color: #000000;
            }
            </style>
        </div>
    </div>
</div>