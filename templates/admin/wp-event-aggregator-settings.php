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
$wpea_google_maps_api_key = get_option( 'wpea_google_maps_api_key', array() );

if( is_object( $meetup_authorized_user ) ){
    $meetup_authorized_user = (array)$meetup_authorized_user;
}

?>
<div class="wpea_container">
    <div class="wpea_row">
        <div class="wpea-setting-tab-container">
            <div class="wpea-setting-main-tab">
                <span class="wpea-setting-tab active" data-tab="facebooksetting">Facebook</span>
                <span class="wpea-setting-tab" data-tab="eventbritesetting">Eventbrite</span>
                <span class="wpea-setting-tab" data-tab="meetupsetting">Meetup</span>
                <span class="wpea-setting-tab" data-tab="icalsettings">iCal</span>
                <span class="wpea-setting-tab" data-tab="aggregatorsetting">General Settings</span>
                <span class="wpea-setting-tab" data-tab="googlemapsetting">Google Maps API</span>
                <?php if( wpea_is_pro() ){ ?>
                    <span class="wpea-setting-tab" data-tab="licensesection">License Key</span>
                <?php } ?>
            </div>
                <div class="wpea-setting-tab-child active" id="facebooksetting">
                    <?php
                    $site_url = get_home_url();
                    if( !isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) ) {
                        ?>
                        <div class="widefat wpea_settings_error wpea_mt_10">
                            <?php printf( '%1$s <b><a href="https://developers.facebook.com/blog/post/2018/06/08/enforce-https-facebook-login/" target="_blank">%2$s</a></b> %3$s', __( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required for authorize your facebook account.","wp-event-aggregator" ), __( 'Click here','wp-event-aggregator' ), __( 'for more information.','wp-event-aggregator' ) ); ?>
                        </div>
                    <?php
                    } ?>
                    <div class="widefat wpea_settings_notice wpea_mt_10" >
                        <?php printf( '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s',  __( 'Note : ','wp-event-aggregator' ), __( 'You have to create a Facebook application before filling the following details.','wp-event-aggregator' ), __( 'Click here','wp-event-aggregator' ),  __( 'to create new Facebook application.','wp-event-aggregator' ) ); ?>
                        <br/>
                        <?php _e( 'For detailed step by step instructions ', 'wp-event-aggregator' ); ?>
                        <strong><a href="http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/" target="_blank"><?php _e( 'Click here', 'wp-event-aggregator' ); ?></a></strong>.
                        <br/>
                        <?php _e( '<strong>Set the site url as : </strong>', 'wp-event-aggregator' ); ?>
                        <span style="color: green;"><?php echo get_site_url(); ?></span>
                        <span class="dashicons dashicons-admin-page wpea-btn-copy-shortcode wpea_link_cp" data-value='<?php echo esc_url( get_site_url() ); ?>' ></span>
                        <br/>
                        <?php _e( '<strong>Set Valid OAuth redirect URI : </strong>', 'wp-event-aggregator' ); ?>
                        <span style="color: green;"><?php echo admin_url( 'admin-post.php?action=wpea_facebook_authorize_callback' ) ?></span>
                        <span class="dashicons dashicons-admin-page wpea-btn-copy-shortcode wpea_link_cp" data-value='<?php echo admin_url( 'admin-post.php?action=wpea_facebook_authorize_callback' ) ?>' ></span>
                    </div>
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

            <form method="post" id="wpea_setting_form">
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
                                        <?php printf( "( <em>%s</em> )", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
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
                    <div class="wpea_element">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>
                </div>
            </form>
            <form method="post" id="wpea_setting_form">
                <div class="wpea-setting-tab-child" id="eventbritesetting" >
                    <p><?php _e( 'You need a Eventbrite Personal OAuth token to import your events from Eventbrite.','wp-event-aggregator' ); ?> </p>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <?php _e( 'Eventbrite Private token','wp-event-aggregator' ); ?> : 
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
                                    $ticket_model = isset( $eventbrite_options['ticket_model'] ) ? $eventbrite_options['ticket_model'] : '0';
                                    ?>
                                    <input type="checkbox" class="enable_ticket_sec" name="eventbrite[enable_ticket_sec]" value="yes" <?php if ( $enable_ticket_sec == 'yes' ) { echo 'checked="checked"'; } ?> />
                                    <span>
                                        <?php _e( 'Check to display ticket option after event.', 'wp-event-aggregator' ); ?>
                                    </span>
                                    <?php if( is_ssl() ){ ?>
                                    <div class="wpea_small checkout_model_option">
                                        <input type="radio" name="eventbrite[ticket_model]" value="0" <?php checked( $ticket_model, '0'); ?>>
                                            <?php _e( 'Non-Modal Checkout', 'wp-event-aggregator' ); ?><br/>
                                        <input type="radio" name="eventbrite[ticket_model]" value="1" <?php checked( $ticket_model, '1'); ?>>
                                            <?php _e( 'Popup Checkout Widget (Display your checkout as a modal popup)', 'wp-event-aggregator' ); ?><br/>
                                    </div>
                                    <?php } ?>
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
                                        <?php printf( "( <em>%s</em> )", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <?php _e( 'Import Private Events', 'wp-event-aggregator' ); ?> : 
                                </th>
                                <td>
                                    <?php 
                                    $private_eventbrite_events = isset( $eventbrite_options['private_events'] ) ? $eventbrite_options['private_events'] : 'no';
                                    ?>
                                    <input type="checkbox" name="eventbrite[private_events]" value="yes" <?php if( $private_eventbrite_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                                    <span class="wpea_small">
                                        <?php _e( 'Tick to import Private events, Untick to not import private event.', 'wp-event-aggregator' ); ?>
                                        <?php printf( "( <em>%s</em> )", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                                    </span>
                                </td>
                            </tr>


                            <tr>
                                <th scope="row">
                                    <?php _e( 'Import Small Event Thumbnail', 'wp-event-aggregator' ); ?> : 
                                </th>
                                <td>
                                    <?php
                                    $small_thumbnail = isset( $eventbrite_options['small_thumbnail'] ) ? $eventbrite_options['small_thumbnail'] : 'no';
                                    ?>
                                    <input type="checkbox" name="eventbrite[small_thumbnail]" value="yes" <?php if ( $small_thumbnail == 'yes' ) { echo 'checked="checked"'; } ?> />
                                    <span>
                                    <?php _e( 'You can import small thumbnails of events into an event by enabling this option.', 'wp-event-aggregator' ); ?>
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
                    <div class="wpea_element">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>
                </div>
                
                <div class="wpea-setting-tab-child" id="meetupsetting">
                    <div class="widefat wpea_settings_notice wpea_mt_10">
                        <?php printf( '<b>%1$s</b> %2$s <b><a href="https://www.meetup.com/api/oauth/list/" target="_blank">%3$s</a></b> %4$s',  __( 'Note : ','wp-event-aggregator' ), __( 'You have to create a Meetup OAuth Consumer before filling the following details.','wp-event-aggregator' ), __( 'Click here', 'wp-event-aggregator' ),  __( 'to create new OAuth Consumer','wp-event-aggregator' ) ); ?>
                        <br/>
                        <?php _e( 'For detailed step by step instructions ', 'wp-event-aggregator' ); ?>
                        <strong><a href="http://docs.xylusthemes.com/docs/import-meetup-events/creating-oauth-consumer/" target="_blank"><?php _e( 'Click here', 'wp-event-aggregator' ); ?></a></strong>.
                        <br/>
                        <?php _e( '<strong>Set the Application Website as : </strong>', 'wp-event-aggregator' ); ?>
                        <span style="color: green;"><?php echo get_site_url(); ?></span>
                        <span class="dashicons dashicons-admin-page wpea-btn-copy-shortcode wpea_link_cp" data-value='<?php echo get_site_url(); ?>' ></span>
                        <br/>
                        <?php _e( '<strong>Set Redirect URI : </strong>', 'wp-event-aggregator' ); ?>
                        <span style="color: green;"><?php echo admin_url( 'admin-post.php?action=wepa_meetup_authorize_callback' ); ?></span>
                        <span class="dashicons dashicons-admin-page wpea-btn-copy-shortcode wpea_link_cp" data-value='<?php echo admin_url( 'admin-post.php?action=wepa_meetup_authorize_callback' ); ?>' ></span>
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
                                            if( !empty($meetup_authorized_user) && isset($meetup_authorized_user['name']) ) {
                                                $email = isset($meetup_authorized_user['email']) ? $meetup_authorized_user['email'] : '';
                                                $name = $meetup_authorized_user['name'];
                                                ?>
                                                <div class="wpea_connection_wrapper">
                                                    <div class="name_wrap">
                                                        <?php printf( __('Connected as: %s', 'wp-event-aggregator'), '<strong>'.$name.'</strong>' ); ?>
                                                        <br/>
                                                        <?php echo $email; ?>
                                                        <br/>
                                                        <a href="<?php echo admin_url('admin-post.php?action=wpea_mdeauthorize_action&_wpnonce=' . wp_create_nonce('wpea_deauthorize_nonce')); ?>">
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
                                        <?php printf('%s <a href="https://www.meetup.com/api/oauth/list/" target="_blank">%s</a>', __( 'Insert your meetup.com OAuth Key you can get it from', 'wp-event-aggregator' ), __( 'here', 'wp-event-aggregator' ) ); ?>
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
                                        <?php printf('%s <a href="https://www.meetup.com/api/oauth/list/" target="_blank">%s</a>', __( 'Insert your meetup.com OAuth Secret you can get it from', 'wp-event-aggregator' ), __( 'here', 'wp-event-aggregator' ) ); ?>
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
                                        <?php printf('%s <a href="https://www.meetup.com/api/oauth/list" target="_blank">%s</a>', __( 'Insert your meetup.com API key you can get it from', 'wp-event-aggregator' ), __( 'here', 'wp-event-aggregator' ) ); ?>
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
                                        <?php printf( "( <em>%s</em> )", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
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
                    <div class="wpea_element">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>
                </div>

                <div class="wpea-setting-tab-child" id="icalsettings">
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
                                        <?php printf( "( <em>%s</em> )", __( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
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
                    <div class="wpea_element">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>
                </div>


                <div class="wpea-setting-tab-child" id="aggregatorsetting">
                    <table class="form-table">
                        <tbody>
                            <?php 
                            do_action( 'wpea_admin_settings_start' );
                            ?>
                            
                            <tr>
                                <th scope="row">
                                    <?php _e('Direct link to Event Source', 'wp-event-aggregator'); ?> :
                                </th>
                                <td>
                                    <?php
                                    $direct_link = isset($aggregator_options['direct_link']) ? $aggregator_options['direct_link'] : 'no';
                                    ?>
                                    <input type="checkbox" name="wpea[direct_link]" value="yes" <?php if ($direct_link == 'yes') { echo 'checked="checked"'; }if (!wpea_is_pro()) {echo 'disabled="disabled"'; } ?> />
                                    <span class="wpea_small">
                                        <?php _e('Check to enable direct event link to Event Source instead of event detail page.', 'wp-event-aggregator'); ?>
                                    </span>
                                    <?php do_action('wpea_render_pro_notice'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <?php _e('Event Slug', 'wp-event-aggregator'); ?> :
                                </th>
                                <td>
                                    <?php
                                    $events_slug = isset($aggregator_options['events_slug']) ? $aggregator_options['events_slug'] : 'wp-event';
                                    ?>
                                    <input type="text" name="wpea[events_slug]" value="<?php if ( $events_slug ) { echo $events_slug; } ?>" <?php if (!wpea_is_pro()) { echo 'disabled="disabled"'; } ?> />
                                    <span class="wpea_small">
                                        <?php _e('Slug for the event.', 'wp-event-aggregator'); ?>
                                    </span>
                                    <?php do_action('wpea_render_pro_notice'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <?php _e('Skip Trashed Events', 'wp-event-aggregator'); ?> :
                                </th>
                                <td>
                                    <?php
                                    $skip_trash = isset($aggregator_options['skip_trash']) ? $aggregator_options['skip_trash'] : 'no';
                                    ?>
                                    <input type="checkbox" name="wpea[skip_trash]" value="yes" <?php if ($skip_trash == 'yes') { echo 'checked="checked"'; }if (!wpea_is_pro()) {echo 'disabled="disabled"'; } ?> />
                                    <span class="wpea_small">
                                        <?php _e('Check to enable skip-the-trash events during importing.', 'wp-event-aggregator'); ?>
                                    </span>
                                    <?php do_action('wpea_render_pro_notice'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <?php esc_attr_e( 'Event Display Time Format', 'wp-event-aggregator' ); ?> :
                                </th>
                                <td>
                                <?php
                                $time_format = isset( $aggregator_options['time_format'] ) ? $aggregator_options['time_format'] : '12hours';
                                ?>
                                <select name="wpea[time_format]">
                                        <option value="12hours" <?php selected( '12hours', $time_format ); ?>><?php esc_attr_e( '12 Hours', 'wp-event-aggregator' );  ?></option>
                                        <option value="24hours" <?php selected( '24hours', $time_format ); ?>><?php esc_attr_e( '24 Hours', 'wp-event-aggregator' ); ?></option>						
                                        <option value="wordpress_default" <?php selected( 'wordpress_default', $time_format ); ?>><?php esc_attr_e( 'WordPress Default', 'wp-event-aggregator' ); ?></option>
                                </select>
                                <span class="wpea_small">
                                    <?php esc_attr_e( 'Choose event display time format for front-end.', 'wp-event-aggregator' ); ?>
                                </span>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <?php esc_attr_e( 'Accent Color', 'wp-event-aggregator' ); ?> :
                                </th>
                                <td>
                                <?php

                                $accent_color = isset( $aggregator_options['accent_color'] ) ? $aggregator_options['accent_color'] : '#039ED7';
                                ?>
                                <input class="wpea_color_field" type="text" name="wpea[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>"/>
                                <span class="wpea_small">
                                    <?php esc_attr_e( 'Choose accent color for front-end event grid and event widget.', 'wp-event-aggregator' ); ?>
                                </span>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><?php esc_attr_e( 'Default Event Thumbnail', 'wp-event-aggregator' ); ?>:</th>
                                <td>
                                    <?php
                                    wp_enqueue_media();

                                    $wpea_cfulb     = ' upload-button button-add-media button-add-site-icon ';
                                    $wpea_cfub      = ' button ';
                                    $wpea_cfw       = '';

                                    if ( has_site_icon() ) {
                                        $wpea_cfw  .= ' has-site-icon';
                                        $wpea_cfb   = $wpea_cfub;
                                        $wpea_cfboc = $wpea_cfulb;
                                    } else {
                                        $wpea_cfw  .= ' hidden';
                                        $wpea_cfb   = $wpea_cfulb;
                                        $wpea_cfboc = $wpea_cfub;
                                    }

                                    $wpea_options   = get_option( WPEA_OPTIONS );
                                    $wpea_edt_id    = isset( $wpea_options['wpea']['wpea_event_default_thumbnail'] ) ? $wpea_options['wpea']['wpea_event_default_thumbnail'] : '';
                                    $wpea_edt_url   = !empty( $wpea_edt_id ) ? wp_get_attachment_url( $wpea_edt_id ) : '';
                                    $button_text    = empty( $wpea_edt_url ) ? 'Choose Event Thumbnail' : 'Change Event Thumbnail';
                                    $remove_class   = empty( $wpea_edt_url ) ? 'hidden' : '';
                                    ?>

                                    <div id="wpea-event-thumbnail-preview" class="wp-clearfix settings-page-preview <?php echo esc_attr( ! empty( $wpea_edt_url ) ? '' : 'hidden' ); ?>">
                                        <img id="wpea-event-thumbnail-img" src="<?php echo esc_url( $wpea_edt_url ); ?>" alt="<?php esc_attr_e( 'Event Thumbnail', 'wp-event-aggregator' ); ?>" style="max-width:100%;width: 15%;height: auto;" >
                                    </div>

                                    <input type="hidden" name="wpea[wpea_event_default_thumbnail]" id="wpea-event_thumbnail_hidden_field" value="<?php echo esc_attr( $wpea_edt_id ); ?>" />

                                    <div class="action-buttons">
                                        <button type="button" id="wpea-choose-from-library-button" class="<?php echo esc_attr( $wpea_cfb ); ?>" data-alt-classes="<?php echo esc_attr( $wpea_cfboc ); ?>" >
                                            <?php echo $button_text; ?>
                                        </button>
                                        <button id="wpea-js-remove-thumbnail" type="button" data-alt-classes="<?php echo esc_attr( $wpea_cfboc ); ?>" class="reset <?php echo esc_attr( $remove_class ); ?><?php echo esc_attr( $wpea_cfb ); ?>" >
                                            <?php _e( 'Remove Event Thumbnail', 'wp-event-aggregator' ); ?>
                                        </button>
                                    </div>
                                    <span class="wpea_small">
                                        <?php esc_attr_e( "This option will display this image in the event's grid view if the event does not have a featured image.", 'wp-event-aggregator' ); ?>
                                    </span>
                                </td>
                            </tr>

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
                    <div class="wpea_element">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="button-primary xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>
                </div>
            </form>

            <div class="wpea-setting-tab-child" id="googlemapsetting">
                <div class="wpea_container">
                    <div class="wpea_row">
                        <form method="post" id="wpea_gma_setting_form">
                            <table class="form-table">
                                <tbody>
                                    <?php do_action( 'wpea_before_settings_section' ); ?>
                                    <tr>
                                        <th scope="row">
                                            <?php esc_attr_e( 'Google Maps API', 'wp-event-aggregator' ); ?> :
                                        </th>
                                        <td>
                                            <input class="wpea_google_maps_api_key" name="wpea_google_maps_api_key" Placeholder="Enter Google Maps API Key Here..." type="text" value="<?php echo( ! empty( $wpea_google_maps_api_key ) ? esc_attr( $wpea_google_maps_api_key ) : '' ); ?>" />
                                            <span class="wpea_check_key"><a href="javascript:void(0)" > Check Google Maps Key</a><span class="wpea_loader" id="wpea_loader"></span></span>
                                            <span id="wpea_gmap_error_message"></span>
                                            <span id="wpea_gmap_success_message"></span><br/>
                                            <span class="wpea_small wpea_mt_10" >
                                                <?php
                                                    printf(
                                                        '%s <a href="https://developers.google.com/maps/documentation/embed/get-api-key#create-api-keys" target="_blank">%s</a> / %s',
                                                        esc_attr__( 'Google maps API Key (Required)', 'wp-event-aggregator' ),
                                                        esc_attr__( 'How to get an API Key', 'wp-event-aggregator' ),
                                                        '<a href="https://developers.google.com/maps/documentation/embed/get-api-key#restrict_key" target="_blank">' . esc_attr__( 'Find out more about API Key restrictions', 'wp-event-aggregator' ) . '</a>'
                                                    );
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="wpea_element">
                                <input type="hidden" name="wpea_gma_action" value="wpea_save_gma_settings" />
                                <?php wp_nonce_field( 'wpea_gma_setting_form_nonce_action', 'wpea_gma_setting_form_nonce' ); ?>
                                <input type="submit" class="button-primary xtei_gma_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php if( wpea_is_pro() ){ ?>
                <div class="wpea-setting-tab-child" id="licensesection">
                    <div id="license" class="wpea_tab_content">
                        <?php
                            if( class_exists( 'WP_Event_Aggregator_Pro_Common' ) && method_exists( $importevents->common_pro, 'wpea_licence_page_in_setting' ) ){
                                $importevents->common_pro->wpea_licence_page_in_setting(); 
                            }else{
                                $license_section = sprintf(
                                    '<h3 class="setting_bar" >Once you have updated the plugin Pro version <a href="%s">%s</a>, you will be able to access this section.</h3>',
                                    esc_url( admin_url( 'plugins.php?s=WP+Event+Aggregator+Pro' ) ),
                                    esc_html__( 'Here', 'wp-event-aggregator' )
                                );
                                echo $license_section;
                            }
                        ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>