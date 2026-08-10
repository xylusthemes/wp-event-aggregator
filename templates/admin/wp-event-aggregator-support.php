<?php
// If this file is called directly, abort.
// Icon Credit: Icon made by Freepik and Vectors Market from www.flaticon.com
if ( ! defined( 'ABSPATH' ) ) exit;
global $importevents;
$wpea_open_source_support_url = 'https://wordpress.org/support/plugin/wp-event-aggregator/';
$wpea_support_url = 'https://xylusthemes.com/support/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin';

$wpea_review_url = 'https://wordpress.org/support/plugin/wp-event-aggregator/reviews/';
$wpea_facebook_url = 'https://www.facebook.com/xylusinfo/';
$wpea_twitter_url = 'https://twitter.com/XylusThemes/';

?>
<div class="wpea_container">
    <div class="wpea_row">
        <div class="wpea-column support_well">
        	<h3 class="setting_bar"><?php esc_attr_e( 'Getting Support', 'wp-event-aggregator' ); ?></h3>
            <div class="wpea-support-features">
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="wpea-support-features-icon" src="<?php echo esc_url( WPEA_PLUGIN_URL.'assets/images/document.svg' ); ?>" alt="<?php esc_attr_e( 'Looking for Something?', 'wp-event-aggregator' ); ?>">
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Looking for Something?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'We have documentation of how to import Facebook events.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="http://docs.xylusthemes.com/docs/wp-event-aggregator/"><?php esc_attr_e( 'Plugin Documentation', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="wpea-support-features-icon" src="<?php echo esc_url( WPEA_PLUGIN_URL.'assets/images/call-center.svg' ); ?>" alt="<?php esc_attr_e( 'Need Assistance?', 'wp-event-aggregator' ); ?>">
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Need Assistance?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'Our EXPERT Support Team is always ready to Help you out.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/support/"><?php esc_attr_e( 'Contact Support', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="wpea-support-features-icon"  src="<?php echo esc_url( WPEA_PLUGIN_URL.'assets/images/bug.svg' ); ?>" alt="<?php esc_attr_e( 'Find any Bugs?', 'wp-event-aggregator' ); ?>" />
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Find any Bugs?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'Report Bugs, and get Instant Solutions.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://github.com/xylusthemes/wp-event-aggregator"><?php esc_attr_e( 'Report to GitHub.', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="wpea-support-features-icon" src="<?php echo esc_url( WPEA_PLUGIN_URL.'assets/images/tools.svg' ); ?>" alt="<?php esc_attr_e( 'Require Customization?', 'wp-event-aggregator' ); ?>" />
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Require Customization?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'We would Love to hear your Integration and Customization Ideas.', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://xylusthemes.com/what-we-do/"><?php esc_attr_e( 'Connect Our Service', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
				<div class="wpea-support-features-card">
					<div class="wpea-support-features-img">
                        <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
						<img class="wpea-support-features-icon" src="<?php echo esc_url( WPEA_PLUGIN_URL.'assets/images/like.svg' ); ?>" alt="<?php esc_attr_e( 'Like The Plugin?', 'wp-event-aggregator' ); ?>" />
					</div>
					<div class="wpea-support-features-text">
						<h3 class="wpea-support-features-title"><?php esc_attr_e( 'Like The Plugin?', 'wp-event-aggregator' ); ?></h3>
						<p><?php esc_attr_e( 'Your Review is very important to us, and helps us to grow!', 'wp-event-aggregator' ); ?></p>
						<a target="_blank" class="button button-primary" href="https://wordpress.org/support/plugin/wp-event-aggregator/reviews/"><?php esc_attr_e( 'Review WP Event Aggregator on WP.org', 'wp-event-aggregator' ); ?></a>
					</div>
				</div>
			</div>
		</div>

        <?php 
			$wpea_plugin_list = array();
			$wpea_plugin_list = $importevents->common->wpea_get_xylus_themes_plugins();
		?>

        <div class="" style="margin-top: 20px;">
			<h3 class="setting_bar"><?php esc_html_e( 'Plugins you should try','wp-event-aggregator' ); ?></h3>
			<div class="wpea-about-us-plugins">
				<!-- <div class="wpea-row"> -->
				<div class="wpea-support-features2">
				
					<?php 
						if( !empty( $wpea_plugin_list ) ){
							foreach ($wpea_plugin_list as $wpea_key => $plugin ) {

								$wpea_plugin_slug = ucwords( str_replace( '-', ' ', $wpea_key ) );
								$wpea_plugin_name =  $plugin['plugin_name'];
								$wpea_plugin_description =  $plugin['description'];
								if( $wpea_key == 'wp-event-aggregator' ){
									$wpea_plugin_icon = 'https://ps.w.org/'.$wpea_key.'/assets/icon-256x256.jpg';
								} elseif( $wpea_key == 'xt-feed-for-linkedin' ) {
									$wpea_plugin_icon = 'https://ps.w.org/'.$wpea_key.'/assets/icon-256x256.gif';
                                } else {
                                    $wpea_plugin_icon = 'https://ps.w.org/'.$wpea_key.'/assets/icon-256x256.png';
								}

								// Check if the plugin is installed
								$wpea_plugin_installed = false;
								$wpea_plugin_active = false;
								include_once(ABSPATH . 'wp-admin/includes/plugin.php');
								$wpea_all_plugins = get_plugins();
								$wpea_plugin_path = $wpea_key . '/' . $wpea_key . '.php';

								if (isset($wpea_all_plugins[$wpea_plugin_path])) {
									$wpea_plugin_installed = true;
									$wpea_plugin_active = is_plugin_active( $wpea_plugin_path );
								}

								// Determine the status text
								$wpea_status_text = 'Not Installed';
								if ($wpea_plugin_installed) {
									$wpea_status_text = $wpea_plugin_active ? 'Active' : 'Installed (Inactive)';
								}
								
								?>
								<div class="wpea-support-features-card2 wpea-plugin">
									<div class="wpea-plugin-main">
										<div>
											<?php
												// translators: %s: Plugin slug used in image alt text.
												$wpea_alt_text = sprintf( esc_attr__( '%s Image', 'wp-event-aggregator' ), $wpea_plugin_slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
											?>
											<?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
											<img alt="<?php echo $wpea_alt_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" src="<?php echo esc_url( $wpea_plugin_icon ); ?>">
										</div>
										<div>
											<div class="wpea-main-name"><?php echo esc_attr( $wpea_plugin_slug ); ?></div>
											<div><?php echo esc_attr( $wpea_plugin_description ); ?></div>
										</div>
									</div>
									<div class="wpea-plugin-footer">
										<div class="wpea-footer-status">
											<div class="wpea-footer-status-label"><?php esc_attr_e( 'Status : ', 'wp-event-aggregator' ); ?></div>
											<div class="wpea-footer-status wpea-footer-status-<?php echo esc_attr( strtolower(str_replace(' ', '-', $wpea_status_text) ) ); ?>">
												<span <?php echo ( $wpea_status_text == 'Active' ) ? 'style="color:green;"' : ''; ?>>
													<?php echo esc_attr( $wpea_status_text ); ?>
												</span>
											</div>
										</div>
										<div class="wpea-footer-action">
											<?php if (!$wpea_plugin_installed): ?>
												<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" type="button" class="button button-primary">Install Free Plugin</a>
											<?php elseif (!$wpea_plugin_active): ?>
												<?php 
													$wpea_activate_nonce = wp_create_nonce('activate_plugin_' . $wpea_plugin_slug); 
													$wpea_activation_url = add_query_arg(array( 'action' => 'activate_plugin', 'plugin_slug' => $wpea_plugin_slug, 'nonce' => $wpea_activate_nonce, ), admin_url('admin.php?page=eventbrite_event&tab=support'));
												?>
												<a href="<?php echo esc_url( admin_url( 'plugins.php?s='. $wpea_plugin_name ) ); ?>" class="button button-primary">Activate Plugin</a>
											<?php endif; ?>
										</div>
									</div>
								</div>
								<?php
							}
						}
					?>
				</div>
			</div>
			<div style="clear: both;">
		</div>
    </div>

</div>