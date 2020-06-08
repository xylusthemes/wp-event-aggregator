<?php
// If this file is called directly, abort.
// Icon Credit: Icon made by Freepik and Vectors Market from www.flaticon.com
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
        	<h3 class="setting_bar"><?php esc_attr_e( 'Getting Support', 'wp-event-aggregator' ); ?></h3>
            <div class="wpea-support-features">
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
						<img class="wpea-support-features-icon" src="<?php echo WPEA_PLUGIN_URL.'assets/images/document.svg'; ?>" alt="<?php esc_attr_e( 'Looking for Something?', 'wp-event-aggregator' ); ?>">
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Looking for Something?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'We have documentation of how to import facebook events.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="http://docs.xylusthemes.com/docs/wp-event-aggregator/"><?php esc_attr_e( 'Plugin Documentation', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
						<img class="wpea-support-features-icon" src="<?php echo WPEA_PLUGIN_URL.'assets/images/call-center.svg'; ?>" alt="<?php esc_attr_e( 'Need Any Assistance?', 'wp-event-aggregator' ); ?>">
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Need Any Assistance?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'Our EXPERT Support Team is always ready to Help you out.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/support/"><?php esc_attr_e( 'Contact Support', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
						<img class="wpea-support-features-icon"  src="<?php echo WPEA_PLUGIN_URL.'assets/images/bug.svg'; ?>" alt="<?php esc_attr_e( 'Found Any Bugs?', 'wp-event-aggregator' ); ?>" />
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Found Any Bugs?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'Report any Bug that you Discovered, Get Instant Solutions.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://github.com/xylusthemes/wp-event-aggregator"><?php esc_attr_e( 'Report to GitHub', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
						<img class="wpea-support-features-icon" src="<?php echo WPEA_PLUGIN_URL.'assets/images/tools.svg'; ?>" alt="<?php esc_attr_e( 'Require Customization?', 'wp-event-aggregator' ); ?>" />
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Require Customization?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'We would Love to hear your Integration and Customization Ideas.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/what-we-do/"><?php esc_attr_e( 'Connect Our Service', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
						<img class="wpea-support-features-icon" src="<?php echo WPEA_PLUGIN_URL.'assets/images/like.svg'; ?>" alt="<?php esc_attr_e( 'Like The Plugin?', 'wp-event-aggregator' ); ?>" />
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Like The Plugin?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'Your Review is very important to us as it helps us to grow more.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/wp-event-aggregator/reviews/?rate=5#new-post"><?php esc_attr_e( 'Review Us on WP.org', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
			</div>
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