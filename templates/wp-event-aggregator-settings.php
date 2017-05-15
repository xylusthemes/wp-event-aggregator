<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
$wpea_options = get_option( WPEA_OPTIONS );
$eventbrite_options = isset($wpea_options['eventbrite'])?$wpea_options['eventbrite']:array();
$meetup_options = isset($wpea_options['meetup'])? $wpea_options['meetup'] : array();
$facebook_options = isset($wpea_options['facebook'])? $wpea_options['facebook'] : array();
$ical_options = isset($wpea_options['ical'])? $wpea_options['ical'] : array();
$aggregator_options = isset($wpea_options['wpea'])? $wpea_options['wpea'] : array();
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
            <p><?php _e( 'You need a Facebook App ID and App Secret to import your events from Facebook.','wp-event-aggregator' ); ?> </p>
            
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
    </div>
</div>
