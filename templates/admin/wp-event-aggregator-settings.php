<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $importevents;
$wpea_options = get_option( WPEA_OPTIONS );
$eventbrite_options = isset($wpea_options['eventbrite'])?$wpea_options['eventbrite']:array();
$meetup_options = isset($wpea_options['meetup'])? $wpea_options['meetup'] : array();
$facebook_options = isset($wpea_options['facebook'])? $wpea_options['facebook'] : array();
$ical_options = isset($wpea_options['ical'])? $wpea_options['ical'] : array();
$aggregator_options = isset($wpea_options['wpea'])? $wpea_options['wpea'] : array();
$facebook_app_id = isset( $facebook_options['facebook_app_id'] ) ? $facebook_options['facebook_app_id'] : '';
$facebook_app_secret = isset( $facebook_options['facebook_app_secret'] ) ? $facebook_options['facebook_app_secret'] : '';
$wpea_user_token_options = get_option( 'wpea_user_token_options', array() );
$wpea_fb_authorize_user = get_option( 'wpea_fb_authorize_user', array() );

$meetup_oauth_key = isset( $meetup_options['meetup_oauth_key'] ) ? $meetup_options['meetup_oauth_key'] : '';
$meetup_oauth_secret = isset( $meetup_options['meetup_oauth_secret'] ) ? $meetup_options['meetup_oauth_secret'] : '';
$meetup_user_token_options = get_option( 'wpea_muser_token_options', array() );
$meetup_authorized_user = get_option( 'wpea_mauthorized_user', array() );
?>
<div class="wpea_container">
    <div class="wpea_row">
    	
    	<form method="post" id="wpea_setting_form">                

            <h3 class="setting_bar"><?php esc_attr_e( 'Eventbrite Settings', 'wp-event-aggregator' ); ?></h3>
            <p><?php _e( 'You need a Eventbrite Personal OAuth token to import your events from Eventbrite.','wp-event-aggregator' ); ?> </p>
            <table class="form-table">
            	<tbody>
            		<tr>
            			<th scope="row">
            				<?php _e( 'Eventbrite Personal OAuth token','wp-event-aggregator' ); ?> : 
            			</th>
            			<td>
            				<input class="eventbrite_oauth_token" name="eventbrite[oauth_token]" type="text" value="<?php if ( isset( $eventbrite_options['oauth_token'] ) ) { echo $eventbrite_options['oauth_token']; } ?>" />
		                    <span class="wpea_small">
		                        <?php _e( 'Insert your eventbrite.com Personal OAuth token you can get it from <a href="http://www.eventbrite.com/myaccount/apps/" target="_blank">here</a>.', 'wp-event-aggregator' ); ?>
		                    </span>
            			</td>
            		</tr>		
            		<tr>
            			<th scope="row">
            				<?php _e( 'Display ticket option after event', 'wp-event-aggregator' ); ?> : 
            			</th>
            			<td>
            				<?php 
            				$enable_ticket_sec = isset( $eventbrite_options['enable_ticket_sec'] ) ? $eventbrite_options['enable_ticket_sec'] : 'no';
            				?>
            				<input type="checkbox" name="eventbrite[enable_ticket_sec]" value="yes" <?php if( $enable_ticket_sec == 'yes' ) { echo 'checked="checked"'; } ?> />
		                    <span class="wpea_small">
		                        <?php _e( 'Check to display ticket option after event.', 'wp-event-aggregator' ); ?>
		                    </span>
            			</td>
            		</tr>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Update existing events', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $update_eventbrite_events = isset( $eventbrite_options['update_events'] ) ? $eventbrite_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="eventbrite[update_events]" value="yes" <?php if( $update_eventbrite_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php _e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "(<em>%s</em>)", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Advanced Synchronization', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            if( wpea_is_pro() ){
                                $advanced_esync = isset( $eventbrite_options['advanced_sync'] ) ? $eventbrite_options['advanced_sync'] : 'no';
                                ?>
                                <input type="checkbox" name="eventbrite[advanced_sync]" value="yes" <?php if( $advanced_esync == 'yes' ) { echo 'checked="checked"'; } ?> />
                                <?php
                            }else{
                                ?>
                                <input type="checkbox" name="" disabled="disabled" />
                                <?php
                            }
                            ?>
                            <span class="wpea_small">
                                <?php _e( 'Check to enable advanced synchronization, this will delete events which are removed from source calendar. Also, it deletes passed events if source calendar is provide only upcoming events.', 'wp-event-aggregator' ); ?>
                            </span>
                            <?php do_action( 'wpea_render_pro_notice' ); ?>
                        </td>
                    </tr>
         		
            	</tbody>
            </table>
            <br/>

            <h3 class="setting_bar"><?php esc_attr_e( 'Meetup Settings', 'wp-event-aggregator' ); ?></h3>
            <?php
            $site_url = get_home_url();
            if( !isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) && $meetup_oauth_key != '' && $meetup_oauth_secret != '' && empty($meetup_authorized_user) ) {
                ?>
                <div class="widefat wpea_settings_error">
                    <?php _e( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required for authorize your meetup account.",'wp-event-aggregator' ); ?>
                </div>
            <?php
            } ?>

            <div class="widefat wpea_settings_notice">
                <?php printf( '<b>%1$s</b> %2$s <b><a href="https://secure.meetup.com/meetup_api/oauth_consumers/create" target="_blank">%3$s</a></b> %4$s',  __( 'Note : ','wp-event-aggregator' ), __( 'You have to create a Meetup OAuth Consumer before filling the following details.','wp-event-aggregator' ), __( 'Click here', 'wp-event-aggregator' ),  __( 'to create new OAuth Consumer','wp-event-aggregator' ) ); ?>
                <br/>
                <?php _e( 'For detailed step by step instructions ', 'wp-event-aggregator' ); ?>
                <strong><a href="http://docs.xylusthemes.com/docs/import-meetup-events/creating-oauth-consumer/" target="_blank"><?php _e( 'Click here', 'wp-event-aggregator' ); ?></a></strong>.
                <br/>
                <?php _e( '<strong>Set the Application Website as : </strong>', 'wp-event-aggregator' ); ?>
                <span style="color: green;"><?php echo get_site_url(); ?></span>
                <br/>
                <?php _e( '<strong>Set Redirect URI : </strong>', 'wp-event-aggregator' ); ?>
                <span style="color: green;"><?php echo admin_url( 'admin-post.php?action=wepa_meetup_authorize_callback' ); ?></span>
            </div>

            <?php
            if( $meetup_oauth_key != '' && $meetup_oauth_secret != '' ){
                ?>
                <h4 class="setting_bar"><?php esc_attr_e( 'Connect your Meetup Account', 'wp-event-aggregator' ); ?></h4>
                <div class="fb_authorize">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <?php _e( 'Meetup Authorization','wp-event-aggregator' ); ?> :
                                </th>
                                <td>
                                    <?php
                                    if( !empty($meetup_authorized_user) && isset($meetup_authorized_user->name) ) {
                                        $image = isset($meetup_authorized_user->photo->thumb_link) ? $meetup_authorized_user->photo->thumb_link : '';
                                        $email = isset($meetup_authorized_user->email) ? $meetup_authorized_user->email : '';
                                        $name = $meetup_authorized_user->name;
                                        ?>
                                        <div class="wpea_connection_wrapper">
                                            <div class="img_wrap">
                                                <img src="<?php echo $image; ?>"  alt="<?php echo $name; ?>">
                                            </div>
                                            <div class="name_wrap">
                                                <?php printf( __('Connected as: %s', 'wp-event-aggregator'), '<strong>'.$name.'</strong>' ); ?>
                                                <br/>
                                                <?php echo $email; ?>
                                                <br/>
                                                <a href="<?php echo admin_url('admin-post.php?action=wpea_mdeauthorize_action'); ?>">
                                                    <?php _e('Remove Connection', 'wp-event-aggregator'); ?>
                                                </a>
                                            </div>
                                        </div>
                                        <?php
                                    }else{
                                        ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=wpea_mauthorize_action'), 'wpea_mauthorize_action', 'wpea_mauthorize_nonce'); ?>" class="button button-primary">
                                            <?php _e('Connect', 'wp-event-aggregator'); ?>
                                        </a>
                                        <span class="wpea_small">
                                            <?php _e( 'Please connect your meetup account for import meetup events.','wp-event-aggregator' ); ?>
                                        </span>
                                    <?php } ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <br/>
                <?php
            }
            ?>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Meetup OAuth Key','wp-event-aggregator' ); ?> :
                        </th>
                        <td>
                            <input class="meetup_api_key" name="meetup[meetup_oauth_key]" type="text" value="<?php echo $meetup_oauth_key; ?>" />
                            <span class="wpea_small">
                                <?php printf('%s <a href="https://secure.meetup.com/meetup_api/oauth_consumers/" target="_blank">%s</a>', __( 'Insert your meetup.com OAuth Key you can get it from', 'wp-event-aggregator' ), __( 'here', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Meetup OAuth Secret','wp-event-aggregator' ); ?> :
                        </th>
                        <td>
                            <input class="meetup_api_key" name="meetup[meetup_oauth_secret]" type="text" value="<?php echo $meetup_oauth_secret; ?>" />
                            <span class="wpea_small">
                                <?php printf('%s <a href="https://secure.meetup.com/meetup_api/oauth_consumers/" target="_blank">%s</a>', __( 'Insert your meetup.com OAuth Secret you can get it from', 'wp-event-aggregator' ), __( 'here', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row" style="text-align: center" colspan="2">
                            <?php _e( ' - OR -', 'wp-event-aggregator' ); ?>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Meetup API key','wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <input class="meetup_api_key" name="meetup[meetup_api_key]" type="text" value="<?php if ( isset( $meetup_options['meetup_api_key'] ) ) { echo $meetup_options['meetup_api_key']; } ?>" />
                            <span class="wpea_small">
                                <?php printf('%s <a href="https://secure.meetup.com/meetup_api/key/" target="_blank">%s</a>', __( 'Insert your meetup.com API key you can get it from', 'wp-event-aggregator' ), __( 'here', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>       
                    <tr>
                        <th scope="row">
                            <?php _e( 'Update existing events', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $update_meetup_events = isset( $meetup_options['update_events'] ) ? $meetup_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="meetup[update_events]" value="yes" <?php if( $update_meetup_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php _e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "(<em>%s</em>)", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Advanced Synchronization', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            if( wpea_is_pro() ){
                                $advanced_msync = isset( $meetup_options['advanced_sync'] ) ? $meetup_options['advanced_sync'] : 'no';
                                ?>
                                <input type="checkbox" name="meetup[advanced_sync]" value="yes" <?php if( $advanced_msync == 'yes' ) { echo 'checked="checked"'; } ?> />
                                <?php
                            }else{
                                ?>
                                <input type="checkbox" name="" disabled="disabled" />
                                <?php
                            }
                            ?>
                            <span class="wpea_small">
                                <?php _e( 'Check to enable advanced synchronization, this will delete events which are removed from source calendar. Also, it deletes passed events if source calendar is provide only upcoming events.', 'wp-event-aggregator' ); ?>
                            </span>
                            <?php do_action( 'wpea_render_pro_notice' ); ?>
                        </td>
                    </tr>
                
                </tbody>
            </table>
            <br/>

            <h3 class="setting_bar"><?php esc_attr_e( 'Facebook Settings', 'wp-event-aggregator' ); ?></h3>
            
            <?php
            $site_url = get_home_url();
            if( !isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) ) {
                ?>
                <div class="widefat wpea_settings_error">
                    <?php printf( '%1$s <b><a href="https://developers.facebook.com/blog/post/2018/06/08/enforce-https-facebook-login/" target="_blank">%2$s</a></b> %3$s', __( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required for authorize your facebook account.","import-facebook-events" ), __( 'Click here','import-facebook-events' ), __( 'for more information.','import-facebook-events' ) ); ?>
                </div>
            <?php
            } ?>
            <div class="widefat wpea_settings_notice">
                <?php printf( '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s',  __( 'Note : ','import-facebook-events' ), __( 'You have to create a Facebook application before filling the following details.','import-facebook-events' ), __( 'Click here','import-facebook-events' ),  __( 'to create new Facebook application.','import-facebook-events' ) ); ?>
                <br/>
                <?php _e( 'For detailed step by step instructions ', 'import-facebook-events' ); ?>
                <strong><a href="http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/" target="_blank"><?php _e( 'Click here', 'import-facebook-events' ); ?></a></strong>.
                <br/>
                <?php _e( '<strong>Set the site url as : </strong>', 'import-facebook-events' ); ?>
                <span style="color: green;"><?php echo get_site_url(); ?></span>
                <br/>
                <?php _e( '<strong>Set Valid OAuth redirect URI : </strong>', 'import-facebook-events' ); ?>
                <span style="color: green;"><?php echo admin_url( 'admin-post.php?action=wpea_facebook_authorize_callback' ) ?></span>
            </div>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App ID','wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_id" name="facebook[facebook_app_id]" type="text" value="<?php if ( $facebook_app_id != '' ) { echo $facebook_app_id; } ?>" />
                            <span class="wpea_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can veiw or create your Facebook Apps', 'wp-event-aggregator'),
                                    __('from here', 'wp-event-aggregator')
                                 );
                                ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App secret','wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_secret" name="facebook[facebook_app_secret]" type="text" value="<?php if ( $facebook_app_secret != '' ) { echo $facebook_app_secret; } ?>" />
                            <span class="wpea_small">
                                <?php
                                printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                    __('You can veiw or create your Facebook Apps', 'wp-event-aggregator'),
                                    __('from here', 'wp-event-aggregator')
                                 );
                                ?>
                            </span>
                        </td>
                    </tr>       
                    
                    <tr>
                        <th scope="row">
                            <?php _e( 'Update existing events', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $update_facebook_events = isset( $facebook_options['update_events'] ) ? $facebook_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="facebook[update_events]" value="yes" <?php if( $update_facebook_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php _e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "(<em>%s</em>)", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <?php _e( 'Advanced Synchronization', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            if( wpea_is_pro() ){
                                $advanced_fsync = isset( $facebook_options['advanced_sync'] ) ? $facebook_options['advanced_sync'] : 'no';
                                ?>
                                <input type="checkbox" name="facebook[advanced_sync]" value="yes" <?php if( $advanced_fsync == 'yes' ) { echo 'checked="checked"'; } ?> />
                                <?php
                            }else{
                                ?>
                                <input type="checkbox" name="" disabled="disabled" />
                                <?php
                            }
                            ?>
                            <span class="wpea_small">
                                <?php _e( 'Check to enable advanced synchronization, this will delete events which are removed from source calendar. Also, it deletes passed events if source calendar is provide only upcoming events.', 'wp-event-aggregator' ); ?>
                            </span>
                            <?php do_action( 'wpea_render_pro_notice' ); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br/>

            <h3 class="setting_bar"><?php esc_attr_e( 'iCal Settings', 'wp-event-aggregator' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Update existing events', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $update_ical_events = isset( $ical_options['update_events'] ) ? $ical_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="ical[update_events]" value="yes" <?php if( $update_ical_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php _e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "(<em>%s</em>)", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <?php _e( 'Advanced Synchronization', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            if( wpea_is_pro() ){
                                $advanced_sync = isset( $ical_options['advanced_sync'] ) ? $ical_options['advanced_sync'] : 'no';
                                ?>
                                <input type="checkbox" name="ical[advanced_sync]" value="yes" <?php if( $advanced_sync == 'yes' ) { echo 'checked="checked"'; } ?> />
                                <?php
                            }else{
                                ?>
                                <input type="checkbox" name="" disabled="disabled" />
                                <?php
                            }
                            ?>
                            <span class="wpea_small">
                                <?php _e( 'Check to enable advanced synchronization, this will delete events which are removed from source calendar. Also, it deletes passed events if source calendar is provide only upcoming events.', 'wp-event-aggregator' ); ?>
                            </span>
                            <?php do_action( 'wpea_render_pro_notice' ); ?>
                        </td>
                    </tr>
                
                </tbody>
            </table>
            <br/>

            <h3 class="setting_bar"><?php esc_attr_e( 'WP Event Aggregator Settings', 'wp-event-aggregator' ); ?></h3>
            <table class="form-table">
                <tbody>
                    <?php 
                    do_action( 'wpea_admin_settings_start' );
                    ?>
                    
                    <tr>
                        <th scope="row">
                            <?php _e( 'Disable WP Events', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $deactive_wpevents = isset( $aggregator_options['deactive_wpevents'] ) ? $aggregator_options['deactive_wpevents'] : 'no';
                            ?>
                            <input type="checkbox" name="wpea[deactive_wpevents]" value="yes" <?php if( $deactive_wpevents == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php _e( 'Check to disable inbuilt event management system.', 'wp-event-aggregator' ); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Delete WP Event Aggregator data on Uninstall', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $delete_wpdata = isset( $aggregator_options['delete_wpdata'] ) ? $aggregator_options['delete_wpdata'] : 'no';
                            ?>
                            <input type="checkbox" name="wpea[delete_wpdata]" value="yes" <?php if( $delete_wpdata == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php _e( 'Delete WP Event Aggregator data like settings, scheduled imports, import history on Uninstall', 'wp-event-aggregator' ); ?>
                            </span>
                        </td>
                    </tr>
                
                </tbody>
            </table>
            <br/>

            <div class="wpea_element">
                <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                <input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
            </div>
            </form>

            <?php 
            if( $facebook_app_id != '' && $facebook_app_secret != '' ){
                ?>
                <h3 class="setting_bar"><?php esc_attr_e( 'Authorize your Facebook Account', 'wp-event-aggregator' ); ?></h3>
                <div class="fb_authorize">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <?php _e( 'Facebook Authorization','wp-event-aggregator' ); ?> : 
                                </th>
                                <td>
                                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                                        <input type="hidden" name="action" value="wpea_facebook_authorize_action"/>
                                        <?php wp_nonce_field('wpea_facebook_authorize_action', 'wpea_facebook_authorize_nonce'); ?>
                                        <?php 
                                        $button_value = __('Authorize', 'wp-event-aggregator');
                                        if( isset( $wpea_user_token_options['authorize_status'] ) && $wpea_user_token_options['authorize_status'] == 1 && isset(  $wpea_user_token_options['access_token'] ) &&  $wpea_user_token_options['access_token'] != '' ){
                                            $button_value = __('Reauthorize', 'wp-event-aggregator');
                                        }
                                        ?>
                                        <input type="submit" class="button" name="wpea_facebook_authorize" value="<?php echo $button_value; ?>" />
                                        <?php 
                                        if( !empty( $wpea_fb_authorize_user ) && isset( $wpea_fb_authorize_user['name'] ) && $importevents->common->has_authorized_user_token() ){
                                            $fbauthname = sanitize_text_field( $wpea_fb_authorize_user['name'] );
                                            if( $fbauthname != '' ){
                                               printf( __(' ( Authorized as: %s )', 'wp-event-aggregator'), '<b>'.$fbauthname.'</b>' );
                                            }   
                                        }
                                        ?>
                                    </form>

                                    <span class="wpea_small">
                                        <?php _e( 'Please authorize your facebook account for import facebook events.','wp-event-aggregator' ); ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php
            }
            ?>
    </div>
</div>
