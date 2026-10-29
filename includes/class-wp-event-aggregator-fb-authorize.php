<?php
/**
 * class for Facebook User Authorization
 *
 * @link       http://xylusthemes.com/
 * @since      1.2
 *
 * @package    WP_Event_Aggregator_Pro
 * @subpackage WP_Event_Aggregator_Pro/includes
 */
// Exit if accessed directly
if ( ! defined( "ABSPATH" ) ) exit;

class WP_Event_Aggregator_FB_Authorize {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.2
	 */
	public function __construct() {
		add_action( "admin_post_wpea_facebook_authorize_action", array( $this, "wpea_facebook_authorize_user" ) );
		add_action( "admin_post_wpea_facebook_authorize_callback", array( $this, "wpea_facebook_authorize_user_callback" ) );
	}

	/*
	* Authorize facebook user to get access token
	*/
    function wpea_facebook_authorize_user() {
		if ( ! current_user_can( "manage_options" ) ) {
			wp_die( esc_html__( "You do not have sufficient permissions to perform this action.", "wp-event-aggregator" ) );
		}

    	if ( ! empty($_POST) && wp_verify_nonce( esc_attr( sanitize_text_field( wp_unslash( $_POST["wpea_facebook_authorize_nonce"] ) ) ), "wpea_facebook_authorize_action" ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

        	$wpea_options = get_option( WPEA_OPTIONS , array() );
        	$facebook_options = isset($wpea_options["facebook"])? $wpea_options["facebook"] : array();
        	$app_id = isset( $facebook_options["facebook_app_id"] ) ? $facebook_options["facebook_app_id"] : "";
			$app_secret = isset( $facebook_options["facebook_app_secret"] ) ? $facebook_options["facebook_app_secret"] : "";
			$redirect_url = admin_url( "admin-post.php?action=wpea_facebook_authorize_callback" );
			$api_version = "v24.0";
			$param_url = urlencode($redirect_url);
			$wpea_session_state = wp_generate_password( 32, false );
			set_transient( "wpea_oauth_state_" . get_current_user_id(), $wpea_session_state, 15 * MINUTE_IN_SECONDS );

			if( $app_id != "" && $app_secret != "" ){

				$dialog_url = "https://www.facebook.com/" . $api_version . "/dialog/oauth?client_id="
				        . $app_id . "&redirect_uri=" . $param_url . "&state="
				        . $wpea_session_state . "&scope=pages_show_list,pages_manage_metadata,pages_read_engagement,pages_read_user_content,page_events";
				wp_redirect( $dialog_url );
				exit();

			}else{
				wp_die( esc_html__( "Please insert Facebook App ID and Secret.", "wp-event-aggregator" ) );
			}			

        } else {
            wp_die( esc_html__("You do not have access to do this operation.", "wp-event-aggregator" ) );
        }
    }	

    /*
	* Authorize facebook user on callback to get access token
	*/
    function wpea_facebook_authorize_user_callback() {
    	global $wpea_success_msg;

		if ( ! current_user_can( "manage_options" ) ) {
			wp_die( esc_html__( "You do not have sufficient permissions to perform this action.", "wp-event-aggregator" ) );
		}

		$user_id        = get_current_user_id();
		$stored_state   = get_transient( "wpea_oauth_state_" . $user_id );
		$returned_state = isset( $_REQUEST["state"] ) ? sanitize_text_field( wp_unslash( $_REQUEST["state"] ) ) : "";

		if ( ! empty( $stored_state ) && ! empty( $returned_state ) && hash_equals( $stored_state, $returned_state ) ) {
			delete_transient( "wpea_oauth_state_" . $user_id );
                
    			$code = esc_attr( sanitize_text_field( wp_unslash( $_GET["code"] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
    			$wpea_options = get_option( WPEA_OPTIONS , array() );
	        	$facebook_options = isset($wpea_options["facebook"])? $wpea_options["facebook"] : array();
	        	$app_id = isset( $facebook_options["facebook_app_id"] ) ? $facebook_options["facebook_app_id"] : "";
				$app_secret = isset( $facebook_options["facebook_app_secret"] ) ? $facebook_options["facebook_app_secret"] : "";
    			
				$redirect_url = admin_url("admin-post.php?action=wpea_facebook_authorize_callback");
				$api_version = "v24.0";
				$param_url = urlencode($redirect_url);

				if( $app_id != "" && $app_secret != "" ){

					$token_url = "https://graph.facebook.com/" . $api_version . "/oauth/access_token?"
        . "client_id=" . $app_id . "&redirect_uri=" . $param_url
        . "&client_secret=" . $app_secret . "&code=" . $code;

					$access_token = "";
					$wpea_user_token_options = $wpea_fb_authorize_user = array();
					$response = wp_remote_get( $token_url );
					$body = wp_remote_retrieve_body( $response );
					$body_response = json_decode( $body );
					if ($body != "" && isset( $body_response->access_token ) ) {
						
						$access_token = $body_response->access_token;
					    $wpea_user_token_options["authorize_status"] = 1;
					    $wpea_user_token_options["access_token"] = sanitize_text_field($access_token);
					    update_option("wpea_user_token_options", $wpea_user_token_options);

					   	$profile_call= wp_remote_get("https://graph.facebook.com/".$api_version."/me?fields=id,name,picture&access_token=$access_token");
					   	$profile = wp_remote_retrieve_body( $profile_call );
					   	$profile = json_decode( $profile );
					   	if( isset( $profile->id ) && isset( $profile->name ) ){
					   		$wpea_fb_authorize_user["ID"] = sanitize_text_field( $profile->id );
					   		$wpea_fb_authorize_user["name"] = sanitize_text_field( $profile->name );
					   		if( isset( $profile->picture->data->url ) ){
					   			$wpea_fb_authorize_user["avtar"] = esc_url_raw( $profile->picture->data->url );	
					   		}					   		
					   	}

					   	update_option("wpea_fb_authorize_user", $wpea_fb_authorize_user );

						//getting page token
						$args          = array( "timeout" => 15 );
						$accounts_call = wp_remote_get( "https://graph.facebook.com/" . $api_version . "/me/accounts?access_token=$access_token&limit=100&offset=0", $args );
						$accounts      = wp_remote_retrieve_body( $accounts_call );
						$accounts      = json_decode( $accounts );
						$accounts      = isset( $accounts->data ) ? $accounts->data : array();
						if ( ! empty( $accounts ) ) {
							$pages = array();
							foreach ( $accounts as $account ) {
								$pages[ $account->id ] = array(
									"id"           => $account->id,
									"name"         => $account->name,
									"access_token" => $account->access_token,
								);
							}
							update_option( "wpea_fb_user_pages", $pages );
						}

					   	$redirect_url = admin_url("admin.php?page=import_events&tab=settings&wauthorize=1");
					    wp_safe_redirect($redirect_url);
					    exit();
					}else{
						$redirect_url = admin_url("admin.php?page=import_events&tab=settings&wauthorize=0");
					    wp_safe_redirect($redirect_url);
					    exit();					
					}
				} else {
					$redirect_url = admin_url("admin.php?page=import_events&tab=settings&wauthorize=2");
					wp_safe_redirect($redirect_url);
					exit();
				}

            } else {
            	wp_die( esc_html__("Invalid or expired state parameter. Please try authorizing again.", "wp-event-aggregator" ) );
            }
    }	
}
