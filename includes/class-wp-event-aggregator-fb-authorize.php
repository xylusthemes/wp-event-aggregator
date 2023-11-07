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
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_FB_Authorize {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.2
	 */
	public function __construct() {
		add_action( 'admin_post_wpea_facebook_authorize_action', array( $this, 'wpea_facebook_authorize_user' ) );
		add_action( 'admin_post_wpea_facebook_authorize_callback', array( $this, 'wpea_facebook_authorize_user_callback' ) );
	}

	/*
	* Authorize facebook user to get access token
	*/
    function wpea_facebook_authorize_user() {
    	if ( ! empty($_POST) && wp_verify_nonce($_POST['wpea_facebook_authorize_nonce'], 'wpea_facebook_authorize_action' ) ) {

        	$wpea_options = get_option( WPEA_OPTIONS , array() );
        	$facebook_options = isset($wpea_options['facebook'])? $wpea_options['facebook'] : array();
        	$app_id = isset( $facebook_options['facebook_app_id'] ) ? $facebook_options['facebook_app_id'] : '';
			$app_secret = isset( $facebook_options['facebook_app_secret'] ) ? $facebook_options['facebook_app_secret'] : '';
			$redirect_url = admin_url( 'admin-post.php?action=wpea_facebook_authorize_callback' );
			$api_version = 'v18.0';
			$param_url = urlencode($redirect_url);
			$wpea_session_state = md5(uniqid(rand(), TRUE));
			setcookie("wpea_session_state", $wpea_session_state, "0", "/");

			if( $app_id != '' && $app_secret != '' ){

				$dialog_url = "https://www.facebook.com/" . $api_version . "/dialog/oauth?client_id="
				        . $app_id . "&redirect_uri=" . $param_url . "&state="
				        . $wpea_session_state . "&scope=pages_show_list,pages_manage_metadata,pages_read_engagement,pages_read_user_content,page_events";
				header("Location: " . $dialog_url);

			}else{
				die( __( 'Please insert Facebook App ID and Secret.', 'wp-event-aggregator' ) );
			}			

        } else {
            die( __('You have not access to do this operations.', 'wp-event-aggregator' ) );
        }
    }	

    /*
	* Authorize facebook user on callback to get access token
	*/
    function wpea_facebook_authorize_user_callback() {
    	global $wpea_success_msg;
		if ( isset( $_COOKIE['wpea_session_state'] ) && isset($_REQUEST['state']) && ( $_COOKIE['wpea_session_state'] === sanitize_text_field( $_REQUEST['state'] ) ) ) {
                
    			$code = sanitize_text_field($_GET['code']);
    			$wpea_options = get_option( WPEA_OPTIONS , array() );
	        	$facebook_options = isset($wpea_options['facebook'])? $wpea_options['facebook'] : array();
	        	$app_id = isset( $facebook_options['facebook_app_id'] ) ? $facebook_options['facebook_app_id'] : '';
				$app_secret = isset( $facebook_options['facebook_app_secret'] ) ? $facebook_options['facebook_app_secret'] : '';
    			
				$redirect_url = admin_url('admin-post.php?action=wpea_facebook_authorize_callback');
				$api_version = 'v18.0';
				$param_url = urlencode($redirect_url);

				if( $app_id != '' && $app_secret != '' ){

					$token_url = "https://graph.facebook.com/" . $api_version . "/oauth/access_token?"
        . "client_id=" . $app_id . "&redirect_uri=" . $param_url
        . "&client_secret=" . $app_secret . "&code=" . $code;

					$access_token = "";
					$wpea_user_token_options = $wpea_fb_authorize_user = array();
					$response = wp_remote_get( $token_url );
					$body = wp_remote_retrieve_body( $response );
					$body_response = json_decode( $body );
					if ($body != '' && isset( $body_response->access_token ) ) {
						
						$access_token = $body_response->access_token;
					    $wpea_user_token_options['authorize_status'] = 1;
					    $wpea_user_token_options['access_token'] = sanitize_text_field($access_token);
					    update_option('wpea_user_token_options', $wpea_user_token_options);

					   	$profile_call= wp_remote_get("https://graph.facebook.com/".$api_version."/me?fields=id,name,picture&access_token=$access_token");
					   	$profile = wp_remote_retrieve_body( $profile_call );
					   	$profile = json_decode( $profile );
					   	if( isset( $profile->id ) && isset( $profile->name ) ){
					   		$wpea_fb_authorize_user['ID'] = sanitize_text_field( $profile->id );
					   		$wpea_fb_authorize_user['name'] = sanitize_text_field( $profile->name );
					   		if( isset( $profile->picture->data->url ) ){
					   			$wpea_fb_authorize_user['avtar'] = esc_url_raw( $profile->picture->data->url );	
					   		}					   		
					   	}

					   	update_option('wpea_fb_authorize_user', $wpea_fb_authorize_user );
					   	$redirect_url = admin_url('admin.php?page=import_events&tab=settings&wauthorize=1');
					    wp_redirect($redirect_url);
					    exit();
					}else{
						$redirect_url = admin_url('admin.php?page=import_events&tab=settings&wauthorize=0');
					    wp_redirect($redirect_url);
					    exit();					
					}
				} else {
					$redirect_url = admin_url('admin.php?page=import_events&tab=settings&wauthorize=2');
					wp_redirect($redirect_url);
					exit();
					die( __( 'Please insert Facebook App ID and Secret.', 'wp-event-aggregator' ) );
				}

            } else {
            	die( __('You have not access to do this operations.', 'wp-event-aggregator' ) );
            }
    }	
}
