<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
$wpea_options = get_option( WPEA_OPTIONS );
$eventbrite_options = isset($wpea_options['eventbrite'])?$wpea_options['eventbrite']:array();
$meetup_options = isset($wpea_options['meetup'])? $wpea_options['meetup'] : array();
$facebook_options = isset($wpea_options['facebook'])? $wpea_options['facebook'] : array();
$ical_options = isset($wpea_options['ical'])? $wpea_options['ical'] : array();
$aggregator_options = isset($wpea_options['wpea'])? $wpea_options['wpea'] : array();
$facebook_app_id = isset( $facebook_options['facebook_app_id'] ) ? $facebook_options['facebook_app_id'] : '';
$facebook_app_secret = isset( $facebook_options['facebook_app_secret'] ) ? $facebook_options['facebook_app_secret'] : '';
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
                            <span class="xtei_small">
                                <?php _e( 'Insert your eventbrite.com Personal OAuth token you can get it from <a href="http://www.eventbrite.com/myaccount/apps/" target="_blank">here</a>.', 'xt-eventbrite-import-pro' ); ?>
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
                            <span class="xtei_small">
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
                            <span class="xtei_small">
                                <?php _e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "(<em>%s</em>)", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>
                
                </tbody>
            </table>
            <br/>

            <h3 class="setting_bar"><?php esc_attr_e( 'Meetup Settings', 'wp-event-aggregator' ); ?></h3>
            <p><?php _e( 'You need a Meetup API key to import your events from Meetup.','wp-event-aggregator' ); ?> </p>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Meetup API key','wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <input class="meetup_api_key" name="meetup[meetup_api_key]" type="text" value="<?php if ( isset( $meetup_options['meetup_api_key'] ) ) { echo $meetup_options['meetup_api_key']; } ?>" />
                            <span class="xtei_small">
                                <?php printf('%s <a href="http://www.eventbrite.com/myaccount/apps/" target="_blank">%s</a>', __( 'Insert your meetup.com API key you can get it from', 'wp-event-aggregator' ), __( 'here', 'wp-event-aggregator' ) ); ?>
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
                            <span class="xtei_small">
                                <?php _e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "(<em>%s</em>)", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </td>
                    </tr>
                
                </tbody>
            </table>
            <br/>

            <h3 class="setting_bar"><?php esc_attr_e( 'Facebook Settings', 'wp-event-aggregator' ); ?></h3>
            <div class="widefat" style="width: 100%;background-color: #FFFBCC;border: 1px solid #e5e5e5;
-webkit-box-shadow: 0 1px 1px rgba(0,0,0,.04);box-shadow: 0 1px 1px rgba(0,0,0,.04);padding: 10px;">
                <?php printf( '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s',  __( 'Note : ','wp-event-aggregator' ), __( 'You have to create a Facebook application before filling the following details.','wp-event-aggregator' ), __( 'Click here','wp-event-aggregator' ),  __( 'to create new Facebook application.','wp-event-aggregator' ) ); ?>
                <br/>
                <?php _e( 'In the application page in facebook, navigate to <b>Apps &gt; Settings &gt; Edit settings &gt; Website &gt; Site URL</b>. Set the site url as : ', 'wp-event-aggregator' ); ?>
                <span style="color: green;"><?php echo get_site_url(); ?></span>

                <br><?php _e( 'For detailed step by step instructions ', 'wp-event-aggregator' ); ?>
                <b><a href="http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/" target="_blank"><?php _e( 'Click here', 'wp-event-aggregator' ); ?></a></b>.
            </div>

            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <?php _e( 'Facebook App ID','wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <input class="facebook_app_id" name="facebook[facebook_app_id]" type="text" value="<?php if ( isset( $facebook_options['facebook_app_id'] ) ) { echo $facebook_options['facebook_app_id']; } ?>" />
                            <span class="xtei_small">
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
                            <input class="facebook_app_secret" name="facebook[facebook_app_secret]" type="text" value="<?php if ( isset( $facebook_options['facebook_app_secret'] ) ) { echo $facebook_options['facebook_app_secret']; } ?>" />
                            <span class="xtei_small">
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
                            <span class="xtei_small">
                                <?php _e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "(<em>%s</em>)", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
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
                            <span class="xtei_small">
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
                            <input type="checkbox" name="" disabled="disabled" />
                            <span class="xtei_small">
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
                    <tr>
                        <th scope="row">
                            <?php _e( 'Disable WP Events', 'wp-event-aggregator' ); ?> : 
                        </th>
                        <td>
                            <?php 
                            $deactive_wpevents = isset( $aggregator_options['deactive_wpevents'] ) ? $aggregator_options['deactive_wpevents'] : 'no';
                            ?>
                            <input type="checkbox" name="wpea[deactive_wpevents]" value="yes" <?php if( $deactive_wpevents == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="xtei_small">
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
                            <span class="xtei_small">
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
                <h3 class="setting_bar"><?php esc_attr_e( 'Authorize your Facebook Account (Optional)', 'wp-event-aggregator' ); ?></h3>
                <div class="fb_authorize">
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <?php _e( 'Facebook Authorization','wp-event-aggregator' ); ?> : 
                                </th>
                                <td>
                                    <?php 
                                    $button_value = __('Authorize', 'wp-event-aggregator');
                                    ?>
                                    <input type="submit" class="button" name="wpea_facebook_authorize" value="<?php echo $button_value; ?>" disabled="disabled" />
                                    <?php 
                                    do_action( 'wpea_render_pro_notice' );
                                    ?>
                                    <span class="wpea_small">
                                    <?php _e( 'By Authorize your account you are able to import private facebook events which you can see with your profile and import events by group. Authorization is not require if you want to import only public events.','wp-event-aggregator' ); ?>
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
