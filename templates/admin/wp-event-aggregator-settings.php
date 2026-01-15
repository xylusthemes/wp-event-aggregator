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
<div class="wpea-card" style="margin-top:20px;" >
	<div class="wpea-app" >
		<div class="wpea-tabs" >
			<div class="tabs-scroller">
				<div class="var-tabs var-tabs--item-horizontal var-tabs--layout-horizontal-padding">
					<div class="var-tabs__tab-wrap var-tabs--layout-horizontal aggregator-setting-tags">
                        <a href="javascript:void(0)" class="var-settings-tab var-tab  var-tab--active" data-tab="eventbritesetting">
                            <span class="tab-label"><?php esc_attr_e( 'Eventbrite', 'wp-event-aggregator' ); ?></span>
                        </a>
                        <a href="javascript:void(0)" class="var-settings-tab var-tab var-tab--inactive" data-tab="meetupsetting">
                            <span class="tab-label"><?php esc_attr_e( 'Meetup', 'wp-event-aggregator' ); ?></span>
                        </a>
                        <a href="javascript:void(0)" class="var-settings-tab var-tab var-tab--inactive" data-tab="facebooksetting">
                            <span class="tab-label"><?php esc_attr_e( 'Facebook', 'wp-event-aggregator' ); ?></span>
                        </a>
                        <a href="javascript:void(0)" class="var-settings-tab var-tab var-tab--inactive" data-tab="icalsettings">
                            <span class="tab-label"><?php esc_attr_e( 'iCalendar / .ics', 'wp-event-aggregator' ); ?></span>
                        </a>

                        <?php do_action( 'wpea_setting_page_tabs' ); ?>

                        <a href="javascript:void(0)" class="var-settings-tab var-tab var-tab--inactive" data-tab="aggregatorsetting">
                            <span class="tab-label"><?php esc_attr_e( 'General Settings', 'wp-event-aggregator' ); ?></span>
                        </a>
                        <a href="javascript:void(0)" class="var-settings-tab var-tab var-tab--inactive" data-tab="googlemapsetting">
                            <span class="tab-label"><?php esc_attr_e( 'Google Maps API', 'wp-event-aggregator' ); ?></span>
                        </a>
                        <?php if( wpea_is_pro() ){ ?>
                            <a href="javascript:void(0)" class="var-settings-tab var-tab var-tab--inactive" data-tab="licensesection">
                                <span class="tab-label"><?php esc_attr_e( 'License Key', 'wp-event-aggregator' ); ?></span>
                            </a>
                        <?php } ?>

                        <?php do_action( 'wpea_setting_license_page_tabs' ); ?>
                    </div>
				</div>
			</div>
		</div>
	</div>
    
    <form method="post" id="wpea_setting_form">
        <!-- Eventbrite Tab Section -->
        <div id="eventbritesetting" class="wpea-setting-tab-content">
            <div class="wpea-card" >
                <div class="wpea-content wpea_source_import" >

                    <div class="wpea-inner-main-section wpea-new-feature" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text">
                                <?php esc_attr_e( 'Import Event With Standard API', 'wp-event-aggregator' ); ?>
                                <br/>
                                <?php esc_attr_e( '(No Private Token Required)', 'wp-event-aggregator' ); ?>
                            </span>
                        </div>
                        <div class="wpea-inner-section-2" >
                            <?php
                                $using_standard_api = isset( $eventbrite_options['using_standard_api'] ) ? $eventbrite_options['using_standard_api'] : 'no';
                            ?>
                            <input type="checkbox" name="eventbrite[using_standard_api]" value="yes" <?php if( $using_standard_api == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <strong><?php esc_attr_e( 'Using "Import Event With Standard API" lets you fetch events directly. No Eventbrite private token is required.', 'wp-event-aggregator' ); ?></strong>
                            </span>
                        </div>
                    </div>

                    <div class="wpea-inner-main-section" >
                        <div class="aggregator_or_keyandsecrate">
                            <span class="wpea-title-text" ><?php esc_attr_e( '- OR -', 'wp-event-aggregator' ); ?></span>
                        </div>
                    </div> 

                    <!-- Eventbrite Notice Section -->
                    <div class="widefat wpea_settings_notice">
                        <p style="margin:0;">
                            <strong><?php esc_attr_e( 'Note:', 'wp-event-aggregator' ); ?></strong>
                            <?php echo wp_kses_post( __( 'You need an Eventbrite Personal OAuth token to import your events. You can get your Eventbrite private token from <strong><a href="http://www.eventbrite.com/myaccount/apps/" target="_blank" rel="noopener noreferrer">Here</a></strong>.', 'wp-event-aggregator' ) ); ?>
                        </p>
                    </div>

                    <!-- Eventbrite Private Token Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Eventbrite Private token','wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <input class="eventbrite_oauth_token" name="eventbrite[oauth_token]" type="text" value="<?php if ( isset( $eventbrite_options['oauth_token'] ) ) { echo esc_attr( $eventbrite_options['oauth_token'] ); } ?>" />
                        </div>
                    </div>

                    <!-- Display ticket option Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Display ticket option after event', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php
                                $enable_ticket_sec = isset( $eventbrite_options['enable_ticket_sec'] ) ? $eventbrite_options['enable_ticket_sec'] : 'no';
                                $ticket_model = isset( $eventbrite_options['ticket_model'] ) ? $eventbrite_options['ticket_model'] : '0';
                                ?>
                                <input type="checkbox" class="enable_ticket_sec" name="eventbrite[enable_ticket_sec]" value="yes" <?php if ( $enable_ticket_sec == 'yes' ) { echo 'checked="checked"'; } ?> />
                                <span>
                                    <?php esc_attr_e( 'Check to display ticket option after event.', 'wp-event-aggregator' ); ?>
                                </span>
                            <?php 
                                if( is_ssl() ){ ?>
                                    <div class="wpea_small checkout_model_option">
                                        <input type="radio" name="eventbrite[ticket_model]" value="0" <?php checked( $ticket_model, '0'); ?>>
                                            <?php esc_attr_e( 'Non-Modal Checkout', 'wp-event-aggregator' ); ?><br/>
                                        <input type="radio" name="eventbrite[ticket_model]" value="1" <?php checked( $ticket_model, '1'); ?>>
                                            <?php esc_attr_e( 'Popup Checkout Widget (Display your checkout as a modal popup)', 'wp-event-aggregator' ); ?><br/>
                                    </div>
                            <?php 
                                } ?>
                        </div>
                    </div>

                    <!-- Update existing events Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Update existing events', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php 
                                $update_eventbrite_events = isset( $eventbrite_options['update_events'] ) ? $eventbrite_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="eventbrite[update_events]" value="yes" <?php if( $update_eventbrite_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "( <em>%s</em> )", esc_attr__( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Private Events Section -->
                    <?php
                        $private_events     = isset( $eventbrite_options['private_events'] ) ? $eventbrite_options['private_events'] : 'no';
                        $using_standard_api = isset( $eventbrite_options['using_standard_api'] ) ? $eventbrite_options['using_standard_api'] : 'no';
                        $disable_section    = ( $using_standard_api === 'yes' );
                    ?>
                    <div class="wpea-inner-main-section" <?php echo $disable_section ? 'style="opacity:0.5; pointer-events:none;"' : ''; ?> >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Import Private Events', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php 
                                $private_eventbrite_events = isset( $eventbrite_options['private_events'] ) ? $eventbrite_options['private_events'] : 'no';
                            ?>
                            <input type="checkbox" <?php if ( $private_events == 'yes' ) { echo 'checked="checked"'; } if ( $disable_section ) { echo 'disabled="disabled"'; }  ?>  name="eventbrite[private_events]" value="yes" <?php if( $private_eventbrite_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Tick to import Private events, Untick to not import private event.', 'wp-event-aggregator' ); ?>
                                <?php printf( "( <em>%s</em> )", esc_attr__( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                            <?php if ( $disable_section ): ?>
                                <div class="wpea_notice" style="margin-top:5px; color:#d63638; font-size:13px;">
                                    <?php esc_html_e( 'This option only works with a eventbrite private token.', 'wp-event-aggregator' ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Small Event Thumbnail Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Import Small Event Thumbnail', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php
                                $small_thumbnail = isset( $eventbrite_options['small_thumbnail'] ) ? $eventbrite_options['small_thumbnail'] : 'no';
                            ?>
                            <input type="checkbox" name="eventbrite[small_thumbnail]" value="yes" <?php if ( $small_thumbnail == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e( 'You can import small thumbnails of events into an event by enabling this option.', 'wp-event-aggregator' ); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Advanced Synchronization Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Advanced Synchronization', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
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
                                <?php esc_attr_e( 'Check to enable advanced synchronization, this will delete events which are removed from source calendar. Also, it deletes passed events if source calendar is provide only upcoming events.', 'wp-event-aggregator' ); ?>
                            </span>
                            <?php do_action( 'wpea_render_pro_notice' ); ?>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="wpea_button xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Meetup Tab Section -->
        <div id="meetupsetting" class="wpea-setting-tab-content">
            <div class="wpea-card" >
                <div class="wpea-content wpea_source_import" >
                    
                    <!-- Meetup Notice Section -->
                    <div class="widefat wpea_settings_notice">
                        <?php
                            printf(
                                /* translators: 1: Note label, 2: Message, 3: Link text, 4: Additional info */
                                '<b>%1$s</b> %2$s <b><a href="https://www.meetup.com/api/oauth/list/" target="_blank">%3$s</a></b> %4$s',
                                esc_html__( 'Note:', 'wp-event-aggregator' ),
                                esc_html__( 'You have to create a Meetup OAuth Consumer before filling the following details.', 'wp-event-aggregator' ),
                                esc_html__( 'Click here', 'wp-event-aggregator' ),
                                esc_html__( 'to create new OAuth Consumer.', 'wp-event-aggregator' )
                            );
                        ?>
                        <br/>
                        <?php esc_attr_e( 'For detailed step by step instructions ', 'wp-event-aggregator' ); ?>
                        <strong><a href="http://docs.xylusthemes.com/docs/import-meetup-events/creating-oauth-consumer/" target="_blank"><?php esc_attr_e( 'Click here', 'wp-event-aggregator' ); ?></a></strong>.
                        <br/>
                        <?php echo  '<strong>' . esc_attr__( 'Set the Application Website as : ', 'wp-event-aggregator' ). '</strong>'; ?>
                        <span style="color: green;"><?php echo esc_url( get_site_url() ); ?></span>
                        <span class="dashicons dashicons-admin-page wpea-btn-copy-shortcode wpea_link_cp" data-value='<?php echo esc_url( get_site_url() ); ?>' ></span>
                        <br/>
                        <?php echo  '<strong>' . esc_attr__( 'Set Redirect URI : ', 'wp-event-aggregator' ). '</strong>'; ?>
                        <span style="color: green;"><?php echo esc_url( admin_url( 'admin-post.php?action=wepa_meetup_authorize_callback' ) ); ?></span>
                        <span class="dashicons dashicons-admin-page wpea-btn-copy-shortcode wpea_link_cp" data-value='<?php echo esc_url( admin_url( 'admin-post.php?action=wepa_meetup_authorize_callback' ) ); ?>' ></span>
                    </div>

                    <!-- Connect Meetup App Section -->
                    <?php
                        if( $meetup_oauth_key != '' && $meetup_oauth_secret != '' ){
                            ?>
                            <div> 
                                <div class="wpea-inner-main-section" >
                                    <div class="wpea-inner-section-1" >
                                        <span class="wpea-title-text" ><?php esc_attr_e( 'Meetup Authorization','wp-event-aggregator' ); ?></span>
                                    </div>
                                    <div class="wpea-inner-section-2">
                                        <?php
                                            if( !empty($meetup_authorized_user) && isset($meetup_authorized_user['name']) ) {
                                                $email = isset($meetup_authorized_user['email']) ? $meetup_authorized_user['email'] : '';
                                                $name = $meetup_authorized_user['name'];
                                                ?>
                                                <div class="wpea_connection_wrapper">
                                                    <div class="name_wrap">
                                                        <?php 
                                                            // translators: %s: Connected user name
                                                            printf( esc_attr__('Connected as: %s', 'wp-event-aggregator'), '<strong>'. esc_attr( $name ) .'</strong>' ); 
                                                        ?>
                                                        <br/>
                                                        <?php echo esc_attr( $email ); ?>
                                                        <br/>
                                                        <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=wpea_mdeauthorize_action&_wpnonce=' . wp_create_nonce( 'wpea_deauthorize_nonce' ) ) ); ?>">
                                                            <?php esc_attr_e('Remove Connection', 'wp-event-aggregator'); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                                <?php
                                            }else{
                                                ?>
                                                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wpea_mauthorize_action' ), 'wpea_mauthorize_action', 'wpea_mauthorize_nonce' ) ); ?>" class="wpea_button wpea_button-a">
                                                    <?php esc_attr_e('Connect', 'wp-event-aggregator'); ?>
                                                </a>
                                                <span class="wpea_small">
                                                    <?php esc_attr_e( 'Please connect your meetup account for import meetup events.','wp-event-aggregator' ); ?>
                                                </span>
                                        <?php 
                                            } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    ?>

                    <div class="wpea-inner-main-section wpea-new-feature" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" >
                                <?php esc_attr_e( 'Import Event With Meetup API Key ', 'wp-event-aggregator' ); ?>
                                <br/>
                                <?php esc_attr_e( '(No Auth Required) ', 'wp-event-aggregator' ); ?>
                            </span>
                        </div>
                        <div class="wpea-inner-section-2" >
                            <?php
                                $using_public_api = isset( $meetup_options['using_public_api'] ) ? $meetup_options['using_public_api'] : 'no';
                            ?>
                            <input type="checkbox" name="meetup[using_public_api]" value="yes" <?php if( $using_public_api == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <strong><?php esc_attr_e( 'Using "Import Event With Meetup API Key (No Auth Required)" lets you fetch events directly. No Key or authorization needed.', 'wp-event-aggregator' ); ?></strong>
                            </span>
                        </div>
                    </div>

                    <div class="wpea-inner-main-section" >
                        <div class="meetup_or_keyandsecrate">
                            <span class="wpea-title-text" ><?php esc_attr_e( '- OR -', 'wp-event-aggregator' ); ?></span>
                        </div>
                    </div>

                    <!-- Meetup OAuth Key Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Meetup OAuth Key', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <input class="meetup_api_key" name="meetup[meetup_oauth_key]" type="text" value="<?php echo esc_attr( $meetup_oauth_key ); ?>" />
                            <span class="wpea_small">
                                <?php printf('%s <a href="https://www.meetup.com/api/oauth/list/" target="_blank">%s</a>', esc_attr__( 'Insert your meetup.com OAuth Key you can get it from', 'wp-event-aggregator' ), esc_attr__( 'here', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Meetup OAuth Secret Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Meetup OAuth Secret', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <input class="meetup_api_key" name="meetup[meetup_oauth_secret]" type="text" value="<?php echo esc_attr( $meetup_oauth_secret ); ?>" />
                            <span class="wpea_small">
                                <?php printf('%s <a href="https://www.meetup.com/api/oauth/list/" target="_blank">%s</a>', esc_attr__( 'Insert your meetup.com OAuth Secret you can get it from', 'wp-event-aggregator' ), esc_attr__( 'here', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Meetup OAuth Secret Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="meetup_or_keyandsecrate">
                            <span class="wpea-title-text" ><?php esc_attr_e( '- OR -', 'wp-event-aggregator' ); ?></span>
                        </div>
                    </div>

                    <!-- Meetup OAuth Secret Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Meetup API key', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <input class="meetup_api_key" name="meetup[meetup_api_key]" type="text" value="<?php if ( isset( $meetup_options['meetup_api_key'] ) ) { echo esc_attr( $meetup_options['meetup_api_key'] ); } ?>" />
                            <span class="wpea_small">
                                <?php printf('%s <a href="https://www.meetup.com/api/oauth/list" target="_blank">%s</a>', esc_attr__( 'Insert your meetup.com API key you can get it from', 'wp-event-aggregator' ), esc_attr__( 'here', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Update Existing Events Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Update existing events', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php 
                                $update_meetup_events = isset( $meetup_options['update_events'] ) ? $meetup_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="meetup[update_events]" value="yes" <?php if( $update_meetup_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "( <em>%s</em> )", esc_attr__( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Advanced Synchronization Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Advanced Synchronization', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
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
                                <?php esc_attr_e( 'Check to enable advanced synchronization, this will delete events which are removed from source calendar. Also, it deletes passed events if source calendar is provide only upcoming events.', 'wp-event-aggregator' ); ?>
                            </span>
                            <?php do_action( 'wpea_render_pro_notice' ); ?>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="wpea_button xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>

                </div>
            </div>
        </div>

        <!-- Facebook Tab Section -->
        <div id="facebooksetting" class="wpea-setting-tab-content">
            <div class="wpea-card" >
                <div class="wpea-content wpea_source_import" >
                    
                    <!-- Facebook Notice Section -->
                    <?php
                        $site_url = get_home_url();
                        if( !isset( $_SERVER['HTTPS'] ) && false === stripos( $site_url, 'https' ) ) {
                            ?>
                            <div class="widefat wpea_settings_error">
                                <?php
                                    printf(
                                        /* translators: 1: Message, 2: Link text, 3: Additional info */
                                        '%1$s <b><a href="https://developers.facebook.com/blog/post/2018/06/08/enforce-https-facebook-login/" target="_blank">%2$s</a></b> %3$s',
                                        esc_html__( "It looks like you don't have HTTPS enabled on your website. Please enable it. HTTPS is required to authorize your Facebook account.", 'wp-event-aggregator' ),
                                        esc_html__( 'Click here', 'wp-event-aggregator' ),
                                        esc_html__( 'for more information.', 'wp-event-aggregator' )
                                    ); 
                                ?>
                            </div>
                    <?php
                        } ?>
                        <div class="widefat wpea_settings_notice" >
                                <?php
                                printf(
                                    /* translators: 1: 'Note :', 2: Facebook app instruction, 3: 'Click here' link text, 4: final sentence */
                                    '<b>%1$s</b> %2$s <b><a href="https://developers.facebook.com/apps" target="_blank">%3$s</a></b> %4$s',
                                    esc_html__( 'Note :', 'wp-event-aggregator' ),
                                    esc_html__( 'You have to create a Facebook application before filling the following details.', 'wp-event-aggregator' ),
                                    esc_html__( 'Click here', 'wp-event-aggregator' ),
                                    esc_html__( 'to create new Facebook application.', 'wp-event-aggregator' )
                                );
                            ?>
                            <br/>
                            <?php esc_attr_e( 'For detailed step by step instructions ', 'wp-event-aggregator' ); ?>
                            <strong><a href="http://docs.xylusthemes.com/docs/import-facebook-events/creating-facebook-application/" target="_blank"><?php esc_attr_e( 'Click here', 'wp-event-aggregator' ); ?></a></strong>.
                            <br/>
                            <?php echo  '<strong>' . esc_attr__( 'Set the site url as : ', 'wp-event-aggregator' ). '</strong>'; ?>
                            <span style="color: green;"><?php echo esc_url( get_site_url() ); ?></span>
                            <span class="dashicons dashicons-admin-page wpea-btn-copy-shortcode wpea_link_cp" data-value='<?php echo esc_url( get_site_url() ); ?>' ></span>
                            <br/>
                            <?php echo  '<strong>' . esc_attr__( 'Set Valid OAuth redirect URI : ', 'wp-event-aggregator' ). '</strong>'; ?>
                            <span style="color: green;"><?php echo esc_url( admin_url( 'admin-post.php?action=wpea_facebook_authorize_callback' ) ) ?></span>
                            <span class="dashicons dashicons-admin-page wpea-btn-copy-shortcode wpea_link_cp" data-value='<?php echo esc_url( admin_url( 'admin-post.php?action=wpea_facebook_authorize_callback' ) ); ?>' ></span>
                        </div>

                        <!-- Facebook Authorization Section -->
                        <?php 
                            if( $facebook_app_id != '' && $facebook_app_secret != '' ){
                                ?>

                                <div class="wpea-inner-main-section" >
                                    <div class="wpea-inner-section-1" >
                                        <span class="wpea-title-text" ><?php esc_attr_e( 'Facebook Authorization','wp-event-aggregator' ); ?></span>
                                    </div>
                                    <div class="wpea-inner-section-2">
                                        <input type="hidden" name="action" value="wpea_facebook_authorize_action"/>
                                        <?php wp_nonce_field('wpea_facebook_authorize_action', 'wpea_facebook_authorize_nonce'); ?>
                                        <?php 
                                        $button_value = esc_attr__('Authorize', 'wp-event-aggregator');
                                        if( isset( $wpea_user_token_options['authorize_status'] ) && $wpea_user_token_options['authorize_status'] == 1 && isset(  $wpea_user_token_options['access_token'] ) &&  $wpea_user_token_options['access_token'] != '' ){
                                            $button_value = esc_attr__('Reauthorize', 'wp-event-aggregator');
                                        }
                                        ?>
                                        <button type="button" class="wpea_button" id="wpea-facebook-auth-btn"><?php echo esc_attr($button_value); ?></button>
                                        <?php 
                                        if( !empty( $wpea_fb_authorize_user ) && isset( $wpea_fb_authorize_user['name'] ) && $importevents->common->has_authorized_user_token() ){
                                            $fbauthname = sanitize_text_field( $wpea_fb_authorize_user['name'] );
                                            if( $fbauthname != '' ){
                                                // translators: %s: Authorized user name
                                                printf( esc_attr__(' ( Authorized as: %s )', 'wp-event-aggregator'), '<b>'. esc_attr( $fbauthname ) .'</b>' );
                                            }   
                                        }
                                        ?>
                                        <span class="wpea_small">
                                            <?php esc_attr_e( 'Please authorize your facebook account for import facebook events.','wp-event-aggregator' ); ?>
                                        </span>
                                    </div>
                                </div>
                                <?php
                            }
                        ?>

                        <!-- Facebook App ID Section -->
                        <div class="wpea-inner-main-section" >
                            <div class="wpea-inner-section-1" >
                                <span class="wpea-title-text" ><?php esc_attr_e( 'Facebook App ID','wp-event-aggregator' ); ?></span>
                            </div>
                            <div class="wpea-inner-section-2">
                                <input class="facebook_app_id" name="facebook[facebook_app_id]" type="text" value="<?php if ( $facebook_app_id != '' ) { echo esc_attr( $facebook_app_id ); } ?>" />
                                <span class="wpea_small">
                                    <?php
                                    printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                        esc_attr__('You can view or create your Facebook Apps from', 'wp-event-aggregator'),
                                        esc_attr__(' here', 'wp-event-aggregator')
                                    );
                                    ?>
                                </span>
                            </div>
                        </div>

                        <!-- Facebook App Secret Section -->
                        <div class="wpea-inner-main-section" >
                            <div class="wpea-inner-section-1" >
                                <span class="wpea-title-text" ><?php esc_attr_e( 'Facebook App secret','wp-event-aggregator' ); ?></span>
                            </div>
                            <div class="wpea-inner-section-2">
                                <input class="facebook_app_secret" name="facebook[facebook_app_secret]" type="text" value="<?php if ( $facebook_app_secret != '' ) { echo esc_attr( $facebook_app_secret ); } ?>" />
                                <span class="wpea_small">
                                    <?php
                                    printf( '%s <a href="https://developers.facebook.com/apps" target="_blank">%s</a>', 
                                        esc_attr__('You can view or create your Facebook Apps from', 'wp-event-aggregator'),
                                        esc_attr__(' here', 'wp-event-aggregator')
                                    );
                                    ?>
                                </span>
                            </div>
                        </div>

                        <!-- Update Existing Events Section -->
                        <div class="wpea-inner-main-section" >
                            <div class="wpea-inner-section-1" >
                                <span class="wpea-title-text" ><?php esc_attr_e( 'Update existing events', 'wp-event-aggregator' ); ?></span>
                            </div>
                            <div class="wpea-inner-section-2">
                                <?php 
                                    $update_facebook_events = isset( $facebook_options['update_events'] ) ? $facebook_options['update_events'] : 'no';
                                ?>
                                <input type="checkbox" name="facebook[update_events]" value="yes" <?php if( $update_facebook_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                                <span class="wpea_small">
                                    <?php esc_attr_e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                    <?php printf( "( <em>%s</em> )", esc_attr__( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                                </span>
                            </div>
                        </div>

                        <div class="wpea-inner-main-section"  >
                            <div class="wpea-inner-section-1" >
                                <span class="wpea-title-text" ><?php esc_attr_e( "Import Facebook's Event Category", 'wp-event-aggregator' ); ?></span>
                            </div>
                            <div class="wpea-inner-section-2">
                                <?php
                                $import_fb_event_cats = isset( $facebook_options['import_fb_event_cats'] ) ? $facebook_options['import_fb_event_cats'] : 'no';
                                ?>
                                <input type="checkbox" id="import_fb_event_cats" name="facebook[import_fb_event_cats]" value="yes" <?php echo( ( 'yes' === $import_fb_event_cats ) ? 'checked="checked"' : '' ); ?> />
                                <span class="wpea_small">
                                    <?php esc_attr_e( 'Check to import the Facebook event category and assign it to events.', 'wp-event-aggregator' ); ?>
                                </span>
                            </div>
                        </div>

                        <!-- Advanced Synchronization Section -->
                        <div class="wpea-inner-main-section" >
                            <div class="wpea-inner-section-1" >
                                <span class="wpea-title-text" ><?php esc_attr_e( 'Advanced Synchronization', 'wp-event-aggregator' ); ?></span>
                            </div>
                            <div class="wpea-inner-section-2">
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
                                    <?php esc_attr_e( 'Check to enable advanced synchronization, this will delete events which are removed from source calendar. Also, it deletes passed events if source calendar is provide only upcoming events.', 'wp-event-aggregator' ); ?>
                                </span>
                                <?php do_action( 'wpea_render_pro_notice' ); ?>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="">
                            <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                            <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                            <input type="submit" class="wpea_button xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                        </div>
                </div>
            </div>
        </div>

        <!-- iCal Tab Section -->
        <div id="icalsettings" class="wpea-setting-tab-content">
            <div class="wpea-card" >
                <div class="wpea-content wpea_source_import" >
                    <!-- Microsoft Authorization Section -->
                    <?php do_action( 'wpea_microsoft_authorize' ); ?>
                    
                    <!-- Update Existing Events Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Update existing events', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php 
                                $update_ical_events = isset( $ical_options['update_events'] ) ? $ical_options['update_events'] : 'no';
                            ?>
                            <input type="checkbox" name="ical[update_events]" value="yes" <?php if( $update_ical_events == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Check to updates existing events.', 'wp-event-aggregator' ); ?>
                                <?php printf( "( <em>%s</em> )", esc_attr__( 'Not Recommend', 'wp-event-aggregator' ) ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Advanced Synchronization Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Advanced Synchronization', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
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
                                <?php esc_attr_e( 'Check to enable advanced synchronization, this will delete events which are removed from source calendar. Also, it deletes passed events if source calendar is provide only upcoming events.', 'wp-event-aggregator' ); ?>
                            </span>
                            <?php do_action( 'wpea_render_pro_notice' ); ?>
                        </div>
                    </div>

                    <!-- Advanced Synchronization Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Import iCal Category', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php 
                                if( wpea_is_pro() ){
                                    $advanced_sync = isset( $ical_options['ical_cat_import'] ) ? $ical_options['ical_cat_import'] : 'no';
                                    ?>
                                    <input type="checkbox" name="ical[ical_cat_import]" value="yes" <?php if( $advanced_sync == 'yes' ) { echo 'checked="checked"'; } ?> />
                                    <?php
                                }else{
                                    ?>
                                    <input type="checkbox" name="" disabled="disabled" />
                                    <?php
                                }
                            ?>
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Check to enable importing the iCal category, this will import and assign the iCal category to the events.', 'wp-event-aggregator' ); ?>
                            </span>
                            <?php do_action( 'wpea_render_pro_notice' ); ?>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="wpea_button xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>

                </div>
            </div>
        </div>

        <!-- Addon tab Section -->
         <?php do_action( 'wpea_addon_source_settings' ); ?>

        <!-- Aggregator General Tab Section -->
        <div id="aggregatorsetting" class="wpea-setting-tab-content">
            <div class="wpea-card" >
                <div class="wpea-content wpea_source_import" >
                    
                    <!-- Move past events in trash -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Move past events in trash', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php
                                $wpea_move_peit = isset( $aggregator_options['move_peit'] ) ? $aggregator_options['move_peit'] : 'no';
                            ?>
                            <input type="checkbox" name="wpea[move_peit]" value="yes" <?php if ( $wpea_move_peit == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Check to move past events in the trash, Automatically move events to the trash 24 hours after their end date using wp-cron. This runs once daily in the background.', 'wp-event-aggregator' ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Direct link to Event Source Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e('Direct link to Event Source', 'wp-event-aggregator'); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php
                                $direct_link = isset($aggregator_options['direct_link']) ? $aggregator_options['direct_link'] : 'no';
                            ?>
                            <input type="checkbox" name="wpea[direct_link]" value="yes" <?php if ($direct_link == 'yes') { echo 'checked="checked"'; }if (!wpea_is_pro()) {echo 'disabled="disabled"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e('Check to enable direct event link to Event Source instead of event detail page.', 'wp-event-aggregator'); ?>
                            </span>
                            <?php do_action('wpea_render_pro_notice'); ?>
                        </div>
                    </div>

                    <!-- Event Slug Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e('Event Slug', 'wp-event-aggregator'); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php
                                $events_slug = isset($aggregator_options['events_slug']) ? $aggregator_options['events_slug'] : 'wp-event';
                            ?>
                            <input type="text" name="wpea[events_slug]" value="<?php if ( $events_slug ) { echo esc_attr( $events_slug ); } ?>" <?php if (!wpea_is_pro()) { echo 'disabled="disabled"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e('Slug for the event.', 'wp-event-aggregator'); ?>
                            </span>
                            <?php do_action('wpea_render_pro_notice'); ?>
                        </div>
                    </div>

                    <!-- Skip Trashed Events Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e('Skip Trashed Events', 'wp-event-aggregator'); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php
                                $skip_trash = isset($aggregator_options['skip_trash']) ? $aggregator_options['skip_trash'] : 'no';
                            ?>
                            <input type="checkbox" name="wpea[skip_trash]" value="yes" <?php if ($skip_trash == 'yes') { echo 'checked="checked"'; }if (!wpea_is_pro()) {echo 'disabled="disabled"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e('Check to enable skip-the-trash events during importing.', 'wp-event-aggregator'); ?>
                            </span>
                            <?php do_action('wpea_render_pro_notice'); ?>
                        </div>
                    </div>

                    <!-- Event Display Time Format Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Event Display Time Format', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
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
                        </div>
                    </div>

                    <!-- Accent Color Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Accent Color', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php
                                $accent_color = isset( $aggregator_options['accent_color'] ) ? $aggregator_options['accent_color'] : '#039ED7';
                            ?>
                            <input class="wpea_color_field" type="text" name="wpea[accent_color]" value="<?php echo esc_attr( $accent_color ); ?>"/>
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Choose accent color for front-end event grid and event widget.', 'wp-event-aggregator' ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Default Event Thumbnail Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Default Event Thumbnail', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php
                            wp_enqueue_media();

                            $wpea_cfulb     = ' upload-button button-add-media button-add-site-icon ';
                            $wpea_cfub      = ' button-add-site-icon  ';
                            $wpea_options   = get_option( WPEA_OPTIONS );
                            $wpea_edt_id    = isset( $wpea_options['wpea']['wpea_event_default_thumbnail'] ) ? $wpea_options['wpea']['wpea_event_default_thumbnail'] : '';
                            $wpea_edt_url   = !empty( $wpea_edt_id ) ? wp_get_attachment_url( $wpea_edt_id ) : '';
                            $button_text    = empty( $wpea_edt_url ) ? 'Choose Event Thumbnail' : 'Change Event Thumbnail';
                            $remove_class   = empty( $wpea_edt_url ) ? 'hidden' : '';
                            ?>

                            <div id="wpea-event-thumbnail-preview" class="wp-clearfix settings-page-preview <?php echo esc_attr( ! empty( $wpea_edt_url ) ? '' : 'hidden' ); ?>">
                                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                                <img id="wpea-event-thumbnail-img" src="<?php echo esc_url( $wpea_edt_url ); ?>" alt="<?php esc_attr_e( 'Event Thumbnail', 'wp-event-aggregator' ); ?>" style="max-width:100%;width: 15%;height: auto;" >
                            </div>

                            <input type="hidden" name="wpea[wpea_event_default_thumbnail]" id="wpea-event_thumbnail_hidden_field" value="<?php echo esc_attr( $wpea_edt_id ); ?>" />

                            <div class="action-buttons">
                                <button type="button" id="wpea-choose-from-library-button" class="button-add-site-icon"  >
                                    <?php echo esc_attr( $button_text ); ?>
                                </button>
                                <button id="wpea-js-remove-thumbnail" type="button" class="reset <?php echo esc_attr( $remove_class ); ?><?php echo esc_attr( $wpea_cfub ); ?>" >
                                    <?php esc_attr_e( 'Remove Event Thumbnail', 'wp-event-aggregator' ); ?>
                                </button>
                            </div>
                            <span class="wpea_small">
                                <?php esc_attr_e( "This option will display this image in the event's grid view if the event does not have a featured image.", 'wp-event-aggregator' ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Disable WP Events Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Disable WP Events', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php 
                                $deactive_wpevents = isset( $aggregator_options['deactive_wpevents'] ) ? $aggregator_options['deactive_wpevents'] : 'no';
                            ?>
                            <input type="checkbox" name="wpea[deactive_wpevents]" value="yes" <?php if( $deactive_wpevents == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Check to disable inbuilt event management system.', 'wp-event-aggregator' ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Disable WP Events Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Delete WP Event Aggregator data on Uninstall', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
                            <?php 
                                $delete_wpdata = isset( $aggregator_options['delete_wpdata'] ) ? $aggregator_options['delete_wpdata'] : 'no';
                            ?>
                            <input type="checkbox" name="wpea[delete_wpdata]" value="yes" <?php if( $delete_wpdata == 'yes' ) { echo 'checked="checked"'; } ?> />
                            <span class="wpea_small">
                                <?php esc_attr_e( 'Delete WP Event Aggregator data like settings, scheduled imports, import history on Uninstall', 'wp-event-aggregator' ); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="">
                        <input type="hidden" name="wpea_action" value="wpea_save_settings" />
                        <?php wp_nonce_field( 'wpea_setting_form_nonce_action', 'wpea_setting_form_nonce' ); ?>
                        <input type="submit" class="wpea_button xtei_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>

                </div>
            </div>
        </div>
    </form>

    <!-- Google Maps Tab Section -->
    <div id="googlemapsetting" class="wpea-setting-tab-content">
        <div class="wpea-card" >
            <form method="post" id="wpea_gma_setting_form">
                <div class="wpea-content wpea_source_import" >
                    <!-- Disable WP Events Section -->
                    <div class="wpea-inner-main-section" >
                        <div class="wpea-inner-section-1" >
                            <span class="wpea-title-text" ><?php esc_attr_e( 'Google Maps API', 'wp-event-aggregator' ); ?></span>
                        </div>
                        <div class="wpea-inner-section-2">
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
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div>
                        <input type="hidden" name="wpea_gma_action" value="wpea_save_gma_settings" />
                        <?php wp_nonce_field( 'wpea_gma_setting_form_nonce_action', 'wpea_gma_setting_form_nonce' ); ?>
                        <input type="submit" class="wpea_button xtei_gma_submit_button" style=""  value="<?php esc_attr_e( 'Save Settings', 'wp-event-aggregator' ); ?>" />
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Google Maps Tab Section -->
    <div id="licensesection" class="wpea-setting-tab-content">
        <div class="wpea-card" >
            <?php
                if( class_exists( 'WP_Event_Aggregator_Pro_Common' ) && method_exists( $importevents->common_pro, 'wpea_licence_page_in_setting' ) ){
                    $importevents->common_pro->wpea_licence_page_in_setting(); 
                }else{
                    $license_section = sprintf(
                        '<h3 class="setting_bar" >Once you have updated the plugin Pro version <a href="%s">%s</a>, you will be able to access this section.</h3>',
                        esc_url( admin_url( 'plugins.php?s=WP+Event+Aggregator+Pro' ) ),
                        esc_html__( 'Here', 'wp-event-aggregator' )
                    );
                    echo wp_kses_post( $license_section );
                }
            ?>
        </div>
    </div>

    <?php do_action( 'wpea_addon_license_settings' ); ?>

</div>