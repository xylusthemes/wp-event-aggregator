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
						<p><?php esc_attr_e( 'We have documentation of how to import Facebook events.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="http://docs.xylusthemes.com/docs/wp-event-aggregator/"><?php esc_attr_e( 'Plugin Documentation', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
						<img class="wpea-support-features-icon" src="<?php echo WPEA_PLUGIN_URL.'assets/images/call-center.svg'; ?>" alt="<?php esc_attr_e( 'Need Assistance?', 'wp-event-aggregator' ); ?>">
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Need Assistance?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'Our EXPERT Support Team is always ready to Help you out.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/support/"><?php esc_attr_e( 'Contact Support', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
						<img class="wpea-support-features-icon"  src="<?php echo WPEA_PLUGIN_URL.'assets/images/bug.svg'; ?>" alt="<?php esc_attr_e( 'Find any Bugs?', 'wp-event-aggregator' ); ?>" />
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Find any Bugs?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'Report Bugs, and get Instant Solutions.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://github.com/xylusthemes/wp-event-aggregator"><?php esc_attr_e( 'Report to GitHub.', 'wp-event-aggregator' ); ?></a>
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
						<p><?php esc_attr_e( 'Your Review is very important to us, and helps us to grow!', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/wp-event-aggregator/reviews/?rate=5#new-post"><?php esc_attr_e( 'Review WP Event Aggregator on WP.org', 'wp-event-aggregator' ); ?></a>
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