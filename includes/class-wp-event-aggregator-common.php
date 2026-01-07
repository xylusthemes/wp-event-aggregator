<?php
/**
 * Common functions class for WP Event aggregator.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WP_Event_Aggregator_Common {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_wpea_render_terms_by_plugin', array( $this,'wpea_render_terms_by_plugin' ) );	
		add_action( 'tribe_events_single_event_after_the_meta', array( $this, 'wpea_add_tec_ticket_section' ) ) ;
		add_filter( 'the_content', array( $this, 'wpea_add_em_add_ticket_section'), 20 );
		add_filter( 'mc_event_content', array( $this, 'wpea_add_my_calendar_ticket_section') , 10, 4);
		add_action( 'wpea_render_pro_notice', array( $this, 'render_pro_notice') );
		add_action( 'admin_init', array( $this, 'wpea_check_if_access_token_invalidated' ) );
		add_action( 'admin_init', array( $this, 'wpea_check_for_minimum_pro_version' ) );
		add_action( 'ep_after_single_event_contant', array( $this, 'wpea_add_eventprime_add_ticket_section' ) );
		add_action( 'admin_init', array( $this, 'wpea_redirect_after_activation' ) );
	}	

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function render_import_into_and_taxonomy( $selected = '', $taxonomy_terms = array() ) {

		$active_plugins = $this->get_active_supported_event_plugins();
		?>	
		<div class="wpea-inner-main-section" >
			<div class="wpea-inner-section-1" >
				<span class="wpea-title-text" ><?php esc_attr_e( 'Import into','wp-event-aggregator' ); ?></span>
			</div>
			<div class="wpea-inner-section-2">
				<select name="event_plugin" class="event_plugin">
					<?php
					if( !empty( $active_plugins ) ){
						foreach ($active_plugins as $slug => $name ) {
							?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected, $slug ); ?>><?php echo esc_attr( $name ); ?></option>
							<?php
						}
					}
					?>
	            </select>
			</div>
		</div>

		<div class="wpea-inner-main-section" >
			<div class="wpea-inner-section-1" >
				<span class="wpea-title-text" ><?php esc_attr_e( 'Event Categories for Event Import','wp-event-aggregator' ); ?>
					<span class="wpea-tooltip">
						<div>
							<svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpea-circle-question-mark">
								<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
							</svg>
							<span class="wpea-popper">
								<?php esc_attr_e( 'These categories are assign to imported event.', 'wp-event-aggregator' ); ?>
								<div class="wpea-popper__arrow"></div>
							</span>
						</div>
					</span>
				</span>
			</div>
			<div class="wpea-inner-section-2">
				<?php 
					$taxo_cats = $taxo_tags = '';
					if( !empty( $taxonomy_terms ) && isset( $taxonomy_terms['cats'] ) ){
						$taxo_cats = implode(',', $taxonomy_terms['cats'] );
					}
					if( !empty( $taxonomy_terms ) && isset( $taxonomy_terms['tags'] ) ){
						$taxo_tags = implode(',', $taxonomy_terms['tags'] );
					}
				?>
				<input type="hidden" id="wpea_taxo_cats" value="<?php echo esc_attr( $taxo_cats ); ?>">
				<input type="hidden" id="wpea_taxo_tags" value="<?php echo esc_attr( $taxo_tags ); ?>">
				<div class="event_taxo_terms_wraper"></div>
			</div>
		</div>
		<?php		

	}

	/**
	 * Render Taxonomy Terms based on event import into Selection.
	 *
	 * @since 1.0
	 * @return void
	 */
	function wpea_render_terms_by_plugin() {
		global $importevents;
		$event_plugin  = isset( $_REQUEST['event_plugin'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['event_plugin'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$taxo_cats = $taxo_tags = array();
		if( isset( $_REQUEST['taxo_cats'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$texo_cats = isset( $_REQUEST['taxo_cats'] ) ? explode(',', esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['taxo_cats'] ) ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$taxo_cats = $texo_cats;
		}
		if( isset( $_REQUEST['taxo_tags'] ) ){ // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$texo_tags = isset( $_REQUEST['taxo_tags'] ) ? explode(',', sanitize_text_field( wp_unslash( $_REQUEST['taxo_tags'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$taxo_tags = $texo_tags; 
		}
		$event_taxonomy = $event_tag_taxonomy = '';
		$event_taxonomy2 = '';

		if( !empty( $event_plugin ) && !empty( $importevents->$event_plugin ) ){
			$event_taxonomy = $importevents->$event_plugin->get_taxonomy();
		}

		if( 'eventon' === $event_plugin && wpea_is_pro() ){
			$event_taxonomy2 = $importevents->eventon->get_taxonomy2();
		}

		$terms = array();
		if ( $event_taxonomy != '' ) {
			if( taxonomy_exists( $event_taxonomy ) ){
				$terms = get_terms( array( 'taxonomy' => $event_taxonomy, 'hide_empty' => false, ) );
			}
		}
		if( ! empty( $terms ) ){ ?>
			<?php if( 'eventon' == $event_plugin && wpea_is_pro() ){ ?>
				<strong style="display: block;margin-bottom: 5px;">
					<?php esc_attr_e( 'Event Type:', 'wp-event-aggregator' );?>
				</strong>
			<?php } ?>
			<select name="event_cats[]" multiple="multiple">
		        <?php foreach ($terms as $term ) { ?>
					<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php if( in_array( $term->term_id, $taxo_cats ) ){ echo 'selected="selected"'; } ?> >
	                	<?php echo esc_attr( $term->name ); ?>                                	
	                </option>
				<?php } ?> 
			</select>
			<?php
		}

		// Second Taxonomy (EventON)
		$terms2 = array();
		if ( $event_taxonomy2 != '' && wpea_is_pro() ) {
			if( taxonomy_exists( $event_taxonomy2 ) ){
				$terms2 = get_terms( array( 'taxonomy'   => $event_taxonomy2, 'hide_empty' => false, ) );
			}
		}
		if( ! empty( $terms2 ) ){ ?>
			<?php if( 'eventon' == $event_plugin ){ ?>	
				<strong style="display: block;margin: 5px 0px;">
					<?php esc_attr_e( 'Event Type 2:', 'wp-event-aggregator' );?>
				</strong>
			<?php } ?>
			<select name="event_cats2[]" multiple="multiple">
		        <?php foreach ($terms2 as $term2 ) { ?>
					<option value="<?php echo esc_attr( $term2->term_id ); ?>">
	                	<?php echo esc_attr( $term2->name ); ?>                                	
	                </option>
				<?php } ?> 
			</select>
			<?php
		}
		do_action('wpea_after_render_terms_by_plugin');
		wp_die();
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_active_supported_event_plugins() {

		$supported_plugins = array();
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		// check The Events Calendar active or not if active add it into array.
		if( class_exists( 'Tribe__Events__Main' ) ){
			$supported_plugins['tec'] = __( 'The Events Calendar', 'wp-event-aggregator' );
		}

		// check Events Manager.
		if( defined( 'EM_VERSION' ) ){
			$supported_plugins['em'] = __( 'Events Manager', 'wp-event-aggregator' );
		}
		
		// Check event_organizer.
		if( defined( 'EVENT_ORGANISER_VER' ) &&  defined( 'EVENT_ORGANISER_DIR' ) ){
			$supported_plugins['event_organizer'] = __( 'Event Organiser', 'wp-event-aggregator' );
		}

		// check EventON.
		if( class_exists( 'EventON' ) ){
			$supported_plugins['eventon'] = __( 'EventON', 'wp-event-aggregator' );
		}

		// check EventPrime.
		if ( class_exists( 'Eventprime_Event_Calendar_Management_Admin' ) ) {
			$supported_plugins['eventprime'] = __( 'EventPrime', 'wp-event-aggregator' );
		}

		// check All in one Event Calendar
		if( class_exists( 'Ai1ec_Event' ) ){
			$supported_plugins['aioec'] = __( 'All-in-one Event Calendar', 'wp-event-aggregator' );
		}

		// check My Calendar
		if ( is_plugin_active( 'my-calendar/my-calendar.php' ) ) {
			$supported_plugins['my_calendar'] = __( 'My Calendar', 'wp-event-aggregator' );
		}

		// Check EE4
		if( defined( 'EVENT_ESPRESSO_VERSION' ) &&  defined( 'EVENT_ESPRESSO_MAIN_FILE' ) ){
			$supported_plugins['ee4'] = __( 'Event Espresso (EE4)', 'wp-event-aggregator' );
		}

		$wpea_options = get_option( WPEA_OPTIONS );
		$deactive_wpevents = isset( $wpea_options['wpea']['deactive_wpevents'] ) ? $wpea_options['wpea']['deactive_wpevents'] : 'no';
		if( $deactive_wpevents != 'yes' ){
			$supported_plugins['wpea'] = __( 'WP Event Aggregator', 'wp-event-aggregator' );
		}
		return apply_filters( 'wpea_supported_plugins', $supported_plugins );
	}

	/**
	 * Get Sourse data.
	 *
	 * @since  1.7.1
	 * @return array
	 */
	public function get_source_data( $source_data = array(), $schedule_title = '' ) {
		
		$source = '';
		if( $source_data['import_by'] == 'facebook_organization' ){
			if( $source_data['page_username'] != 'me' ){
				$source = '<a href="https://facebook.com/' . $source_data['page_username'] . '" target="_blank" >' . $schedule_title . '</a>';
			}
		}elseif( $source_data['import_by'] == 'organizer_id' ){
			$source = '<a href="https://eventbrite.com/o/' . $source_data['organizer_id'] . '" target="_blank" >' . $schedule_title . '</a>';
		}elseif( $source_data['import_by'] == 'collection_id' ){
			$source = '<a href="https://eventbrite.com/cc/' . $source_data['collection_id'] . '" target="_blank" >' . $schedule_title . '</a>';
		}elseif( $source_data['import_by'] == 'ical_url' ){
			$source = '<a href="' . $source_data['ical_url'] . '" target="_blank" >iCal URL</a>';
		}elseif( $source_data['import_by'] == 'facebook_group' ){
			$source = '<a href="https://www.facebook.com/groups/' . $source_data['facebook_group_id'] . '" target="_blank" >Facebook Group</a>';
		}elseif( $source_data['import_by'] == 'group_url' ){
			$source = '<a href="' . $source_data['meetup_url'] . '" target="_blank" >' . $schedule_title . '</a>';
		}elseif( $source_data['import_by'] == 'outlook_calendar' ){
			$source = 'Microsoft Outlook '. $schedule_title;
		}else{
			$source = 'No Data Found';
		}
		return $source;
	}

	/**
	 * Setup Featured image to events
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @param int $image_url Image URL
	 * @return void
	 */
	public function setup_featured_image_to_event( $event_id, $image_url = '' ) {

		if ( $image_url == '' ) {
			return;
		}
		$event = get_post( $event_id );
		if( empty ( $event ) ){
			return;
		}

		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$event_title = $event->post_title;
		if(strpos($image_url, "https://drive.google.com/") === 0 ){
			$ical_image_id = explode('/', str_replace('https://drive.google.com/', '', $image_url))[2];
			if(!empty($ical_image_id)){
				$image_url = 'https://drive.google.com/uc?export=download&id='.$ical_image_id;
			}
		}
		if ( ! empty( $image_url ) ) {
			$without_ext = false;
			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png|webp)\b/i', $image_url, $matches );
			if ( ! $matches ) {
				if(strpos($image_url, "https://cdn.evbuc.com") === 0 || strpos($image_url, "https://img.evbuc.com") === 0 || strpos($image_url, "https://drive.google.com") === 0 ){
					$without_ext = true;

					$e_options           = wpea_get_import_options( 'eventbrite' );
					$small_thumbnail     = isset( $e_options['small_thumbnail'] ) ? $e_options['small_thumbnail'] : 'no';
					if( $small_thumbnail == 'yes'){
						$image_url       = str_replace( 'original.', 'logo.', $image_url );
					}
					
				}else{
					return new WP_Error( 'image_sideload_failed', __( 'Invalid image URL', 'wp-event-aggregator' ) );
				}
			}

			$args = array(
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => array( // @codingStandardsIgnoreLine.
					array(
						'value' => $image_url,
						'key'   => '_wpea_attachment_source',
					),
				),
			);
			$id = 0;
			$ids = get_posts( $args ); // @codingStandardsIgnoreLine.
			if ( $ids ) {
				$id = current( $ids );
			}
			if( $id && $id > 0 ){
				set_post_thumbnail( $event_id, $id );
				return $id;
			}
			$image_name = '';
			if ( strpos( $image_url, 'meetupstatic' ) === false || strpos( $image_url, 'https://img.evbuc.com' ) !== false || strpos( $image_url, 'https://cdn.evbuc.com' ) !== false ) {
				$image_source = strtok( $image_url, '?');
				$path_info    = pathinfo( $image_source );
				$image_name   = $path_info['basename'];

				$i_args = array(
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'fields'      => 'ids',
					'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'value' => $image_name,
							'key'   => '_wpea_attachment_source_name',
						),
					),
				);

				$i_ids = get_posts( $i_args );
				if ( $i_ids ) {
					$i_id = current( $i_ids );
				}
				if ( isset( $i_id ) && $i_id > 0 ) {
					set_post_thumbnail( $event_id, $i_id );
					return $i_id;
				}
			}

			$file_array = array();
			$file_array['name'] = $event->ID . '_image';
			if($without_ext === true){
				$file_array['name'] .= '.jpg';
			}else{
				$file_array['name'] .=  '_'.basename( $matches[0] );
			}
			
			if( has_post_thumbnail( $event_id ) ){
				$attachment_id = get_post_thumbnail_id( $event_id );
				$attach_filename = basename( get_attached_file( $attachment_id ) );
				if( $attach_filename == $file_array['name'] ){
					return $attachment_id;
				}
			}

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $image_url );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$att_id = media_handle_sideload( $file_array, $event_id, $event_title );

			// If error storing permanently, unlink.
			if ( is_wp_error( $att_id ) ) {
				@unlink( $file_array['tmp_name'] );
				return $att_id;
			}

			if ($att_id) {
				set_post_thumbnail($event_id, $att_id);
			}

			// Save attachment source for future reference.
			update_post_meta( $att_id, '_wpea_attachment_source', $image_url );
			update_post_meta( $att_id, '_wpea_attachment_source_name', $image_name );

			return $att_id;
		}
	}

	/**
	 * Display Ticket Section after eventbrite events.
	 *
	 * @since 1.0.0
	 */
	public function wpea_add_tec_ticket_section() {
		global $importevents;
		$event_id = get_the_ID();
		$xt_post_type = get_post_type();
		$event_origin = get_post_meta( $event_id, 'wpea_event_origin', true );
		$eventbrite_event_id = get_post_meta( $event_id, 'wpea_eventbrite_event_id', true );
		if ( $event_id > 0 ) {
			if ( $event_origin == 'eventbrite' ) {
				if( $importevents->tec->get_event_posttype() == $xt_post_type ){
					$eventbrite_id = get_post_meta( $event_id, 'wpea_event_id', true );
					if ( $eventbrite_id && $eventbrite_id > 0 && is_numeric( $eventbrite_id ) ) {
						$ticket_section = $this->wpea_get_ticket_section( $eventbrite_id );
						echo $ticket_section; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					}
				}
			}elseif( $eventbrite_event_id && $eventbrite_event_id > 0 && is_numeric( $eventbrite_event_id ) ){
				$ticket_section = $this->wpea_get_ticket_section( $eventbrite_event_id );
				echo $ticket_section; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
			}
		}
	}

	/**
	 * Add ticket section to Eventbrite event.
	 *
	 * @since    1.0.0
	 */
	public function wpea_add_eventprime_add_ticket_section() {
		global $importevents;

		$xt_post_type = $importevents->eventprime->get_event_posttype();
		$event_id     = get_the_ID();
		$event_origin = get_post_meta( $event_id, 'wpea_event_origin', true );
		if ( $event_id > 0 && $event_origin == 'eventbrite' ) {
			if ( ( $importevents->eventprime->get_event_posttype() == $xt_post_type ) ) {
				$eventbrite_id = get_post_meta( $event_id, 'wpea_event_id', true );
				if ( $eventbrite_id && $eventbrite_id > 0 && is_numeric( $eventbrite_id ) ) {
					$ticket_section = $this->wpea_get_ticket_section( $eventbrite_id );
					echo $ticket_section; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				}
			}
		}
	}


	/**
	 * Display Ticket Section after eventbrite events.
	 *
	 * @since 1.0.0
	 */
	public function wpea_add_my_calendar_ticket_section( $details = '', $event = array(), $type = '', $time = '' ) {
		global $importevents;
		$ticket_section = '';
		if( $type == 'single' ){
			$event_id = $event->event_post;
			$xt_post_type = get_post_type( $event_id );
			$event_origin = get_post_meta( $event_id, 'wpea_event_origin', true );
			if ( $event_id > 0 && $event_origin == 'eventbrite' ) {
				if( $importevents->my_calendar->get_event_posttype() == $xt_post_type ){
					$eventbrite_id = get_post_meta( $event_id, 'wpea_event_id', true );
					if ( $eventbrite_id && $eventbrite_id > 0 && is_numeric( $eventbrite_id ) ) {
						$ticket_section = $this->wpea_get_ticket_section( $eventbrite_id );
					}
				}
			}	
		}
		return $details . $ticket_section;
	}

	

	/**
	 * Add ticket section to Eventbrite event.
	 *
	 * @since    1.0.0
	 */
	public function wpea_add_em_add_ticket_section( $content = '' ) {
		global $importevents;
		$xt_post_type =  get_post_type();
		$event_id = get_the_ID();
		$event_origin = get_post_meta( $event_id, 'wpea_event_origin', true );
		$eventum = false;
		if( wpea_is_pro() ){
			$eventum = ( $importevents->eventum->get_event_posttype()  == $xt_post_type );
		}
		if ( $event_id > 0 && $event_origin == 'eventbrite' ) {
			if( ( $importevents->em->get_event_posttype()  == $xt_post_type ) || ( $importevents->aioec->get_event_posttype()  == $xt_post_type ) || ( $importevents->wpea->get_event_posttype()  == $xt_post_type ) || ( $importevents->eventon->get_event_posttype()  == $xt_post_type ) || $eventum ){
				$eventbrite_id = get_post_meta( $event_id, 'wpea_event_id', true );
				if ( $eventbrite_id && $eventbrite_id > 0 && is_numeric( $eventbrite_id ) ) {
					$ticket_section = $this->wpea_get_ticket_section( $eventbrite_id );
					return $content.$ticket_section;
				}
			}
		}
		return $content;
	}

	/**
	 * Get Ticket Sectoin for eventbrite events.
	 *
	 * @since  1.1.0
	 * @return html
	 */
	public function wpea_get_ticket_section( $eventbrite_id = 0 ) {
		$options = wpea_get_import_options( 'eventbrite' );
		
		$enable_ticket_sec = isset( $options['enable_ticket_sec'] ) ? $options['enable_ticket_sec'] : 'no';
		$ticket_model = isset( $options['ticket_model'] ) ? $options['ticket_model'] : '0';
		if ( 'yes' != $enable_ticket_sec ) {
			return '';
		}

		if ( $eventbrite_id > 0 ) {
			ob_start();
			if( is_ssl() ){
				if('1'=== $ticket_model ){
					echo wpea_model_checkout_markup($eventbrite_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				}else{
					echo wpea_nonmodel_checkout_markup($eventbrite_id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
				}
			} else {
				?>
				<div class="eventbrite-ticket-section" style="width:100%; text-align:left;">
					<iframe id="eventbrite-tickets-<?php echo esc_attr( $eventbrite_id ); ?>" src="//www.eventbrite.com/tickets-external?eid=<?php echo esc_attr( $eventbrite_id ); ?>" style="width:100%;height:300px; border: 0px;"></iframe>
				</div>
				<?php
			}
			$ticket = ob_get_clean();
			return $ticket;
		} else {
			return '';
		}

	}

	/**
	 * Format events arguments as per TEC
	 *
	 * @since    1.0.0
	 * @param array $eventbrite_event Eventbrite event.
	 * @return array
	 */
	public function display_import_success_message( $import_data = array(),$import_args = array(), $schedule_post = '', $error_reason = '' ) {
		global $wpea_success_msg, $wpea_errors;
		if ( ! empty( $wpea_errors ) ) {
			return;
		}

		$import_status = $import_ids = array();
		if( !empty( $import_data ) ){
			foreach ($import_data as $key => $value) {
				if( $value['status'] == 'created'){
					$import_status['created'][] = $value;
				}elseif( $value['status'] == 'updated'){
					$import_status['updated'][] = $value;
				}elseif( $value['status'] == 'skipped'){
					$import_status['skipped'][] = $value;
				}elseif( $value['status'] == 'skip_trash'){
					$import_status['skip_trash'][] = $value;
				}else{

				}
				if( isset( $value['id'] ) ){
					$import_ids[] = $value['id'];
				}
			}
		}
		$created = $updated = $skipped = $skip_trash = 0;
		$created = isset( $import_status['created'] ) ? count( $import_status['created'] ) : 0;
		$updated = isset( $import_status['updated'] ) ? count( $import_status['updated'] ) : 0;
		$skipped = isset( $import_status['skipped'] ) ? count( $import_status['skipped'] ) : 0;
		$skip_trash = isset( $import_status['skip_trash'] ) ? count( $import_status['skip_trash'] ) : 0;
		
		$success_message = esc_html__( 'Event(s) successfully imported.', 'wp-event-aggregator' )."<br>";
		if( $created > 0 ){
			// translators: %d: Number of events Created.
			$success_message .= "<strong>".sprintf( __( '%d Created', 'wp-event-aggregator' ), $created )."</strong><br>";
		}
		if( $updated > 0 ){
			// translators: %d: Number of events Updated.
			$success_message .= "<strong>".sprintf( __( '%d Updated', 'wp-event-aggregator' ), $updated )."</strong><br>";
		}
		if( $skipped > 0 ){
			// translators: %d: Number of events Skipped.
			$success_message .= "<strong>".sprintf( __( '%d Skipped (Already exists)', 'wp-event-aggregator' ), $skipped ) ."</strong><br>";
		}
		if( $skip_trash > 0 ){
			// translators: %d: Number of events Skipped.
			$success_message .= "<strong>".sprintf( __( '%d Skipped (Already exists in Trash)', 'wp-event-aggregator' ), $skip_trash ) ."</strong><br>";
		}

		if ( !empty( $error_reason ) ) {
			$success_message .= "<strong>" . esc_html( $error_reason ) . "</strong> " . esc_html__( 'errors found', 'wp-event-aggregator' ) . "<br>";
		}

		$wpea_success_msg[] = $success_message;

		if( $schedule_post != '' && $schedule_post > 0 ){
			$temp_title = get_the_title( $schedule_post );
		}else{
			$temp_title = 'Manual Import';
		}
		
		$nothing_to_import = false;
		if($created == 0 && $updated == 0 && $skipped == 0 && $skip_trash == 0 ){
			$nothing_to_import = true;
		}
		if( $created > 0 || $updated > 0 || $skipped > 0 || $skip_trash > 0 || $nothing_to_import){
			$insert_args = array(
				'post_type'   => 'wpea_import_history',
				'post_status' => 'publish',
				'post_title'  => $temp_title . " - ".ucfirst( $import_args["import_origin"]),
			);
			
			$insert = wp_insert_post( $insert_args, true );
			if ( !is_wp_error( $insert ) ) {
				update_post_meta( $insert, 'import_origin', $import_args["import_origin"] );
				update_post_meta( $insert, 'created', $created );
				update_post_meta( $insert, 'updated', $updated );
				update_post_meta( $insert, 'skipped', $skipped );
				update_post_meta( $insert, 'skip_trash', $skip_trash );
				update_post_meta( $insert, 'nothing_to_import', $nothing_to_import );
				update_post_meta( $insert, 'imported_data', $import_data );
				update_post_meta( $insert, 'import_data', $import_args );
				update_post_meta( $insert, 'error_reason', $error_reason );
				if( $schedule_post != '' && $schedule_post > 0 ){
					update_post_meta( $insert, 'schedule_import_id', $schedule_post );
				}
			}	
		}				
	}

	/**
	 * Get Import events into selected destination.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function import_events_into( $centralize_array, $event_args ){
		global $importevents;
		$import_result = array();
		$event_import_into = isset( $event_args['import_into'] ) ?  $event_args['import_into'] : 'tec';

		if( !empty( $event_import_into ) && !empty( $importevents->$event_import_into ) ){
			$import_result = $importevents->$event_import_into->import_event( $centralize_array, $event_args );
		}
		return $import_result;
	}

	/**
	 * Render import Frequency
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_frequency( $selected = 'daily' ){
		?>
		<select name="import_frequency" class="import_frequency" <?php if(!wpea_is_pro()){ echo 'disabled="disabled"'; } ?>>
	        <option value='hourly' <?php selected( $selected, 'hourly' ); ?>>
	            <?php esc_html_e( 'Once Hourly','wp-event-aggregator' ); ?>
	        </option>
	        <option value='twicedaily' <?php selected( $selected, 'twicedaily' ); ?>>
	            <?php esc_html_e( 'Twice Daily','wp-event-aggregator' ); ?>
	        </option>
	        <option value="daily" <?php selected( $selected, 'daily' ); ?> >
	            <?php esc_html_e( 'Once Daily','wp-event-aggregator' ); ?>
	        </option>
	        <option value="weekly" <?php selected( $selected, 'weekly' ); ?> >
	            <?php esc_html_e( 'Once Weekly','wp-event-aggregator' ); ?>
	        </option>
	        <option value="monthly" <?php selected( $selected, 'monthly' ); ?> >
	            <?php esc_html_e( 'Once a Month','wp-event-aggregator' ); ?>
	        </option>
	    </select>
		<?php
	}

	/**
	 * Display schedule import source 
	 *
	 * @since   1.6.5
	 * @return  void
	 */
	public function render_import_source( $schedule_eventdata = '' ) {

		// Allow addons to override this logic via filter
		$custom_source = apply_filters( 'wpea_render_import_source', null, $schedule_eventdata );

		if ( is_array( $custom_source ) && ! empty( $custom_source['name'] ) && isset( $custom_source['value'], $custom_source['label'] ) ) {
			$name          = $custom_source['name'];
			$event_source  = $custom_source['value'];
			$event_origins = $custom_source['label'];
		}elseif( !empty( $schedule_eventdata['page_username'] ) ) {
			$event_source  = $schedule_eventdata['page_username'];
			$event_origins = 'Facebook Page ID';
			$name          = 'page_username';
		}elseif( !empty( $schedule_eventdata['facebook_group_id'] ) ){
			$event_source  = $schedule_eventdata['facebook_group_id'];
			$event_origins = 'Facebook Group ID';
			$name          = 'facebook_group_id';
		}elseif( !empty( $schedule_eventdata['meetup_url'] ) ){
			$event_source  = $schedule_eventdata['meetup_url'];
			$event_origins = 'Meetup Group URL';
			$name          = 'meetup_url';
		}elseif( !empty( $schedule_eventdata['organizer_id'] ) ){
			$event_source  = $schedule_eventdata['organizer_id'];
			$event_origins = 'Eventbrite Organizer ID';
			$name          = 'organizer_id';
		}elseif( !empty( $schedule_eventdata['collection_id'] ) ){
			$event_source  = $schedule_eventdata['collection_id'];
			$event_origins = 'Eventbrite Collection ID';
			$name          = 'collection_id';
		}elseif( !empty( $schedule_eventdata['ical_url'] ) ){
			$event_source  = $schedule_eventdata['ical_url'];
			$event_origins = 'iCal URL';
			$name          = 'ical_url';
		}else{
			$event_source  = '';
			$event_origins = 'Please create a new schedule after deleting this';
			$name          = '';
		}
		?>
		<td>
			<input type="text" name="<?php echo esc_attr( $name ); ?>" required="required" value="<?php echo esc_attr( $event_source ); ?>">
			<span><?php echo esc_html( $event_origins ); ?></span>
		</td>
		<?php
	}

	/**
	 * Render import type, one time or scheduled
	 *
	 * @since   1.0.0
	 * @return  void
	 */
	function render_import_type(){
		?>
		<select name="import_type" id="import_type" <?php if(!wpea_is_pro()){ echo 'disabled="disabled"'; } ?>>
	    	<option value="onetime" <?php if(!wpea_is_pro()){ echo 'disabled="disabled"'; } ?>><?php esc_attr_e( 'One-time Import','wp-event-aggregator' ); ?></option>
	    	<option value="scheduled" <?php if(!wpea_is_pro()){ echo 'disabled="disabled" selected="selected"'; } ?>><?php esc_attr_e( 'Scheduled Import','wp-event-aggregator' ); ?></option>
	    </select>
	    <span class="hide_frequency">
	    	<?php $this->render_import_frequency(); ?>
	    </span>
	    <?php
	    do_action( 'wpea_render_pro_notice' );
	}

	/**
	 * Clean URL.
	 *
	 * @since 1.0.0
	 */
	function clean_url( $url ) {
		
		$url = str_replace( '&amp;#038;', '&', $url );
		$url = str_replace( '&#038;', '&', $url );
		return $url;
		
	}

	/**
	 * Get UTC offset
	 *
	 * @since    1.0.0
	 */
	function get_utc_offset( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
		} catch ( Exception $e ) {
			return '';
		}

		$timezone = $datetime->getTimezone();
		$offset   = $timezone->getOffset( $datetime ) / 60 / 60;

		if ( $offset >= 0 ) {
			$offset = '+' . $offset;
		}

		return 'UTC' . $offset;
	}

	/**
	 * Render dropdown for Imported event status.
	 *
	 * @since 1.0
	 * @return void
	 */
	function render_eventstatus_input( $selected = 'publish' ){
		?>

		<div class="wpea-inner-main-section" >
			<div class="wpea-inner-section-1" >
				<span class="wpea-title-text" ><?php esc_attr_e( 'Status','wp-event-aggregator' ); ?></span>
			</div>
			<div class="wpea-inner-section-2">
				<select name="event_status" >
	                <option value="publish" <?php selected( $selected, 'publish' ); ?>>
	                    <?php esc_html_e( 'Published','wp-event-aggregator' ); ?>
	                </option>
	                <option value="pending" <?php selected( $selected, 'pending' ); ?>>
	                    <?php esc_html_e( 'Pending','wp-event-aggregator' ); ?>
	                </option>
	                <option value="draft" <?php selected( $selected, 'draft' ); ?>>
	                    <?php esc_html_e( 'Draft','wp-event-aggregator' ); ?>
	                </option>
	            </select>
			</div>
		</div>
		<?php
	}

	/**
	 * remove query string from URL.
	 *
	 * @since 1.0.0
	 */
	function convert_datetime_to_db_datetime( $datetime ) {
		try {
			$datetime = new DateTime( $datetime );
			return $datetime->format( 'Y-m-d H:i:s' );
		}
		catch ( Exception $e ) {
			return $datetime;
		}
	}

	/**
	 * Check for Existing Event
	 *
	 * @since    1.0.0
	 * @param int $event_id event id.
	 * @return /boolean
	 */
	public function get_event_by_event_id( $post_type, $centralize_array ) {
		global $wpdb;
		$event_id = $centralize_array['ID'];
		$wpea_options = get_option( WPEA_OPTIONS );
		$skip_trash = isset( $wpea_options['wpea']['skip_trash'] ) ? $wpea_options['wpea']['skip_trash'] : 'no';
		
		if( $skip_trash == 'yes' ){
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$get_post_id = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT ' . $wpdb->prefix . 'posts.ID FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'posts.post_type = %s AND ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'posts.ID AND (' . $wpdb->prefix . 'postmeta.meta_key = %s AND ' . $wpdb->prefix . 'postmeta.meta_value = %s ) LIMIT 1',
					$post_type,
					'wpea_event_id',
					$event_id
				)
			);
		}else{
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$get_post_id = $wpdb->get_col(
				$wpdb->prepare(
					'SELECT ' . $wpdb->prefix . 'posts.ID FROM ' . $wpdb->prefix . 'posts, ' . $wpdb->prefix . 'postmeta WHERE ' . $wpdb->prefix . 'posts.post_type = %s AND ' . $wpdb->prefix . 'postmeta.post_id = ' . $wpdb->prefix . 'posts.ID AND ' . $wpdb->prefix . 'posts.post_status != %s AND (' . $wpdb->prefix . 'postmeta.meta_key = %s AND ' . $wpdb->prefix . 'postmeta.meta_value = %s ) LIMIT 1',
					$post_type,
					'trash',
					'wpea_event_id',
					$event_id
				)
			);
		}

		if ( !empty( $get_post_id[0] ) ) {
			return $get_post_id[0];
		}

		if( isset( $centralize_array['origin'] ) && $centralize_array['origin'] == 'ical' ){
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, 	WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder
			$search_query = $wpdb->prepare( "SELECT DISTINCT ".$wpdb->posts.".`ID` FROM ".$wpdb->posts." INNER JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".`ID` = ".$wpdb->postmeta.".`post_id` WHERE ".$wpdb->posts.".`post_title` = '%s' AND ".$wpdb->posts.".`post_type` = '%s' AND ( ".$wpdb->postmeta.".`meta_key` = '_wpea_starttime_str' AND ".$wpdb->postmeta.".`meta_value` = '%s' ) LIMIT 1", $centralize_array['name'], $post_type, $centralize_array['starttime_local'] );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
			$is_exists = $wpdb->get_var( $search_query );
			if( $is_exists && is_numeric( $is_exists ) && $is_exists > 0 ){
				return $is_exists;
			}
		}
		return false;
	}

	/**
	 * Check for user have Authorized user Token
	 *
	 * @since    1.2
	 * @return /boolean
	 */
	public function has_authorized_user_token() {
		$wpea_user_token_options = get_option( 'wpea_user_token_options', array() );
		if( !empty( $wpea_user_token_options ) ){
			$authorize_status =	isset( $wpea_user_token_options['authorize_status'] ) ? $wpea_user_token_options['authorize_status'] : 0;
			$access_token = isset( $wpea_user_token_options['access_token'] ) ? $wpea_user_token_options['access_token'] : '';
			if( 1 == $authorize_status && $access_token != '' ){
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if user access token has beed invalidated.
	 *
	 * @since    1.2
	 * @return /boolean
	 */
	public function wpea_check_if_access_token_invalidated() {
		global $wpea_warnings;
		$wpea_user_token_options = get_option( 'wpea_user_token_options', array() );
		if( !empty( $wpea_user_token_options ) ){
			$authorize_status =	isset( $wpea_user_token_options['authorize_status'] ) ? $wpea_user_token_options['authorize_status'] : 0;
			if( 0 == $authorize_status ){
				$settings_url = esc_url( admin_url( 'admin.php?page=import_events&tab=settings' ) );
				$wpea_warnings[] = sprintf(
					// translators: %s: Settings page URL.
					__( 'The Access Token has been invalidated because the user has changed their password, or Facebook has changed the session for security reasons. Please reauthorize your Facebook account from <strong>WP Event Aggregator</strong> > <strong> <a style="text-decoration: none;" href="%s" target="_blank">Settings</a> </strong>.', 'wp-event-aggregator' ), //phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
					$settings_url
				);
			}
		}
	}

	/**
	 * Check if user has minimum pro version.
	 *
	 * @since    1.6
	 * @return /boolean
	 */
	public function wpea_check_for_minimum_pro_version(){
		if( defined('WPEAPRO_VERSION') ){
			if ( version_compare( WPEAPRO_VERSION, WPEA_MIN_PRO_VERSION, '<' ) ) {
				global $wpea_warnings;
				$wpea_warnings[] = __( 'The current "WP Event Aggregator Pro" add-on is not compatible with the Free plugin. Please update to Pro for flawless importing.', 'wp-event-aggregator' );
			}
		}
	}

	/**
	 * Display upgrade to pro notice in form.
	 *
	 * @since 1.0.0
	 */
	public function render_pro_notice(){
		if( !wpea_is_pro() ){
		?>
		<div class="wpea-blur-filter-cta" >
			<span style="color: red"><?php echo esc_html_e( 'Available in Pro version.', 'wp-event-aggregator' ); ?> </span><a href="<?php echo esc_url( WPEA_PLUGIN_BUY_NOW_URL ); ?>"><?php echo esc_html_e( 'Upgrade to PRO', 'wp-event-aggregator' ); ?></a>
		</div>
		<?php
		}
	}
	
	/**
	 * Get do not update data fields
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function wpea_is_updatable( $field = '' ) {
		if( '' == $field ){ return true; }
		$wpea_options = get_option( WPEA_OPTIONS, array() );
		$aggregator_options = isset($wpea_options['wpea'])? $wpea_options['wpea'] : array();
		$dontupdate = isset( $aggregator_options['dont_update'] ) ? $aggregator_options['dont_update'] : array();
		if( isset( $dontupdate[$field] ) &&  'yes' == $dontupdate[$field] ){
			return false;
		}
		return true;
	}

	/**
	 * Get Active supported active plugins.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function wpea_get_country_code( $country ) {
		if( $country == '' ){
			return '';
		}
		
		$countries = array(
		    'AF'=>'AFGHANISTAN',
		    'AL'=>'ALBANIA',
		    'DZ'=>'ALGERIA',
		    'AS'=>'AMERICAN SAMOA',
		    'AD'=>'ANDORRA',
		    'AO'=>'ANGOLA',
		    'AI'=>'ANGUILLA',
		    'AQ'=>'ANTARCTICA',
		    'AG'=>'ANTIGUA AND BARBUDA',
		    'AR'=>'ARGENTINA',
		    'AM'=>'ARMENIA',
		    'AW'=>'ARUBA',
		    'AU'=>'AUSTRALIA',
		    'AT'=>'AUSTRIA',
		    'AZ'=>'AZERBAIJAN',
		    'BS'=>'BAHAMAS',
		    'BH'=>'BAHRAIN',
		    'BD'=>'BANGLADESH',
		    'BB'=>'BARBADOS',
		    'BY'=>'BELARUS',
		    'BE'=>'BELGIUM',
		    'BZ'=>'BELIZE',
		    'BJ'=>'BENIN',
		    'BM'=>'BERMUDA',
		    'BT'=>'BHUTAN',
		    'BO'=>'BOLIVIA',
		    'BA'=>'BOSNIA AND HERZEGOVINA',
		    'BW'=>'BOTSWANA',
		    'BV'=>'BOUVET ISLAND',
		    'BR'=>'BRAZIL',
		    'IO'=>'BRITISH INDIAN OCEAN TERRITORY',
		    'BN'=>'BRUNEI DARUSSALAM',
		    'BG'=>'BULGARIA',
		    'BF'=>'BURKINA FASO',
		    'BI'=>'BURUNDI',
		    'KH'=>'CAMBODIA',
		    'CM'=>'CAMEROON',
		    'CA'=>'CANADA',
		    'CV'=>'CAPE VERDE',
		    'KY'=>'CAYMAN ISLANDS',
		    'CF'=>'CENTRAL AFRICAN REPUBLIC',
		    'TD'=>'CHAD',
		    'CL'=>'CHILE',
		    'CN'=>'CHINA',
		    'CX'=>'CHRISTMAS ISLAND',
		    'CC'=>'COCOS (KEELING) ISLANDS',
		    'CO'=>'COLOMBIA',
		    'KM'=>'COMOROS',
		    'CG'=>'CONGO',
		    'CD'=>'CONGO, THE DEMOCRATIC REPUBLIC OF THE',
		    'CK'=>'COOK ISLANDS',
		    'CR'=>'COSTA RICA',
		    'CI'=>'COTE D IVOIRE',
		    'HR'=>'CROATIA',
		    'CU'=>'CUBA',
		    'CY'=>'CYPRUS',
		    'CZ'=>'CZECH REPUBLIC',
		    'DK'=>'DENMARK',
		    'DJ'=>'DJIBOUTI',
		    'DM'=>'DOMINICA',
		    'DO'=>'DOMINICAN REPUBLIC',
		    'TP'=>'EAST TIMOR',
		    'EC'=>'ECUADOR',
		    'EG'=>'EGYPT',
		    'SV'=>'EL SALVADOR',
		    'GQ'=>'EQUATORIAL GUINEA',
		    'ER'=>'ERITREA',
		    'EE'=>'ESTONIA',
		    'ET'=>'ETHIOPIA',
		    'FK'=>'FALKLAND ISLANDS (MALVINAS)',
		    'FO'=>'FAROE ISLANDS',
		    'FJ'=>'FIJI',
		    'FI'=>'FINLAND',
		    'FR'=>'FRANCE',
		    'GF'=>'FRENCH GUIANA',
		    'PF'=>'FRENCH POLYNESIA',
		    'TF'=>'FRENCH SOUTHERN TERRITORIES',
		    'GA'=>'GABON',
		    'GM'=>'GAMBIA',
		    'GE'=>'GEORGIA',
		    'DE'=>'GERMANY',
		    'GH'=>'GHANA',
		    'GI'=>'GIBRALTAR',
		    'GR'=>'GREECE',
		    'GL'=>'GREENLAND',
		    'GD'=>'GRENADA',
		    'GP'=>'GUADELOUPE',
		    'GU'=>'GUAM',
		    'GT'=>'GUATEMALA',
		    'GN'=>'GUINEA',
		    'GW'=>'GUINEA-BISSAU',
		    'GY'=>'GUYANA',
		    'HT'=>'HAITI',
		    'HM'=>'HEARD ISLAND AND MCDONALD ISLANDS',
		    'VA'=>'HOLY SEE (VATICAN CITY STATE)',
		    'HN'=>'HONDURAS',
		    'HK'=>'HONG KONG',
		    'HU'=>'HUNGARY',
		    'IS'=>'ICELAND',
		    'IN'=>'INDIA',
		    'ID'=>'INDONESIA',
		    'IR'=>'IRAN, ISLAMIC REPUBLIC OF',
		    'IQ'=>'IRAQ',
		    'IE'=>'IRELAND',
		    'IL'=>'ISRAEL',
		    'IT'=>'ITALY',
		    'JM'=>'JAMAICA',
		    'JP'=>'JAPAN',
		    'JO'=>'JORDAN',
		    'KZ'=>'KAZAKSTAN',
		    'KE'=>'KENYA',
		    'KI'=>'KIRIBATI',
		    'KP'=>'KOREA DEMOCRATIC PEOPLES REPUBLIC OF',
		    'KR'=>'KOREA REPUBLIC OF',
		    'KW'=>'KUWAIT',
		    'KG'=>'KYRGYZSTAN',
		    'LA'=>'LAO PEOPLES DEMOCRATIC REPUBLIC',
		    'LV'=>'LATVIA',
		    'LB'=>'LEBANON',
		    'LS'=>'LESOTHO',
		    'LR'=>'LIBERIA',
		    'LY'=>'LIBYAN ARAB JAMAHIRIYA',
		    'LI'=>'LIECHTENSTEIN',
		    'LT'=>'LITHUANIA',
		    'LU'=>'LUXEMBOURG',
		    'MO'=>'MACAU',
		    'MK'=>'MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF',
		    'MG'=>'MADAGASCAR',
		    'MW'=>'MALAWI',
		    'MY'=>'MALAYSIA',
		    'MV'=>'MALDIVES',
		    'ML'=>'MALI',
		    'MT'=>'MALTA',
		    'MH'=>'MARSHALL ISLANDS',
		    'MQ'=>'MARTINIQUE',
		    'MR'=>'MAURITANIA',
		    'MU'=>'MAURITIUS',
		    'YT'=>'MAYOTTE',
		    'MX'=>'MEXICO',
		    'FM'=>'MICRONESIA, FEDERATED STATES OF',
		    'MD'=>'MOLDOVA, REPUBLIC OF',
		    'MC'=>'MONACO',
		    'MN'=>'MONGOLIA',
		    'MS'=>'MONTSERRAT',
		    'MA'=>'MOROCCO',
		    'MZ'=>'MOZAMBIQUE',
		    'MM'=>'MYANMAR',
		    'NA'=>'NAMIBIA',
		    'NR'=>'NAURU',
		    'NP'=>'NEPAL',
		    'NL'=>'NETHERLANDS',
		    'AN'=>'NETHERLANDS ANTILLES',
		    'NC'=>'NEW CALEDONIA',
		    'NZ'=>'NEW ZEALAND',
		    'NI'=>'NICARAGUA',
		    'NE'=>'NIGER',
		    'NG'=>'NIGERIA',
		    'NU'=>'NIUE',
		    'NF'=>'NORFOLK ISLAND',
		    'MP'=>'NORTHERN MARIANA ISLANDS',
		    'NO'=>'NORWAY',
		    'OM'=>'OMAN',
		    'PK'=>'PAKISTAN',
		    'PW'=>'PALAU',
		    'PS'=>'PALESTINIAN TERRITORY, OCCUPIED',
		    'PA'=>'PANAMA',
		    'PG'=>'PAPUA NEW GUINEA',
		    'PY'=>'PARAGUAY',
		    'PE'=>'PERU',
		    'PH'=>'PHILIPPINES',
		    'PN'=>'PITCAIRN',
		    'PL'=>'POLAND',
		    'PT'=>'PORTUGAL',
		    'PR'=>'PUERTO RICO',
		    'QA'=>'QATAR',
		    'RE'=>'REUNION',
		    'RO'=>'ROMANIA',
		    'RU'=>'RUSSIAN FEDERATION',
		    'RW'=>'RWANDA',
		    'SH'=>'SAINT HELENA',
		    'KN'=>'SAINT KITTS AND NEVIS',
		    'LC'=>'SAINT LUCIA',
		    'PM'=>'SAINT PIERRE AND MIQUELON',
		    'VC'=>'SAINT VINCENT AND THE GRENADINES',
		    'WS'=>'SAMOA',
		    'SM'=>'SAN MARINO',
		    'ST'=>'SAO TOME AND PRINCIPE',
		    'SA'=>'SAUDI ARABIA',
		    'SN'=>'SENEGAL',
		    'SC'=>'SEYCHELLES',
		    'SL'=>'SIERRA LEONE',
		    'SG'=>'SINGAPORE',
		    'SK'=>'SLOVAKIA',
		    'SI'=>'SLOVENIA',
		    'SB'=>'SOLOMON ISLANDS',
		    'SO'=>'SOMALIA',
		    'ZA'=>'SOUTH AFRICA',
		    'GS'=>'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS',
		    'ES'=>'SPAIN',
		    'LK'=>'SRI LANKA',
		    'SD'=>'SUDAN',
		    'SR'=>'SURINAME',
		    'SJ'=>'SVALBARD AND JAN MAYEN',
		    'SZ'=>'SWAZILAND',
		    'SE'=>'SWEDEN',
		    'CH'=>'SWITZERLAND',
		    'SY'=>'SYRIAN ARAB REPUBLIC',
		    'TW'=>'TAIWAN, PROVINCE OF CHINA',
		    'TJ'=>'TAJIKISTAN',
		    'TZ'=>'TANZANIA, UNITED REPUBLIC OF',
		    'TH'=>'THAILAND',
		    'TG'=>'TOGO',
		    'TK'=>'TOKELAU',
		    'TO'=>'TONGA',
		    'TT'=>'TRINIDAD AND TOBAGO',
		    'TN'=>'TUNISIA',
		    'TR'=>'TURKEY',
		    'TM'=>'TURKMENISTAN',
		    'TC'=>'TURKS AND CAICOS ISLANDS',
		    'TV'=>'TUVALU',
		    'UG'=>'UGANDA',
		    'UA'=>'UKRAINE',
		    'AE'=>'UNITED ARAB EMIRATES',
		    'GB'=>'UNITED KINGDOM',
		    'US'=>'UNITED STATES',
		    'UM'=>'UNITED STATES MINOR OUTLYING ISLANDS',
		    'UY'=>'URUGUAY',
		    'UZ'=>'UZBEKISTAN',
		    'VU'=>'VANUATU',
		    'VE'=>'VENEZUELA',
		    'VN'=>'VIET NAM',
		    'VG'=>'VIRGIN ISLANDS, BRITISH',
		    'VI'=>'VIRGIN ISLANDS, U.S.',
		    'WF'=>'WALLIS AND FUTUNA',
		    'EH'=>'WESTERN SAHARA',
		    'YE'=>'YEMEN',
		    'YU'=>'YUGOSLAVIA',
		    'ZM'=>'ZAMBIA',
		    'ZW'=>'ZIMBABWE',
		  );

		foreach ($countries as $code => $name ) {
			if( strtoupper( $country) == $name ){
				return $code;
			}
		}
		return $country;
	}

	/**
	 * Ubnbale to hyperlink in description
	 *
	 * @since  1.0.0
	 * @return array
	 */
	function wpea_convert_text_to_hyperlink( $post_description = '' ){

		if( !empty($post_description ) ){
			$url = '@(http(s)?)?(://)?(([a-zA-Z])([-\w]+\.)+([^\s\.]+[^\s]*)+[^,.\s])@';
			$post_description = preg_replace($url, '<a href="http$2://$4" target="_blank" title="$0">$0</a>', $post_description );

			$search  = ['  ', '_ ', ' _'];
			$replace = ['<br />', '<br />', '<br />'];
			$post_description = str_replace($search, $replace, $post_description);
		}
		return $post_description;
	}

	/**
	 * Remove the facebook event link in event desction
	 *
	 * @since  1.0.0
	 * @return array
	 */
	function wpea_remove_facebook_link_in_event_description( $post_description = '', $event_id = '' ){

		if ( !empty( $post_description ) && !empty( $event_id ) ) {
			$event_url        = 'https://www.facebook.com/events/'.$event_id.'/';
			$post_description = str_replace( $event_url, '', $post_description );
		}
		return $post_description;
	}

	/**
	 * Rendering Evebnt update category
	 */
	public function wepa_create_update_ical_categories( $ical_cateories = array(), $source_taxonomy ){

		$event_cat_ids  = [];
		foreach ( $ical_cateories as $category_name ) {
			$term = term_exists( $category_name, $source_taxonomy );
			if( $term && isset($term['term_id'] ) ) {
				$event_cat_ids[] = (int) $term['term_id'];
			} else {
				$new_term = wp_insert_term( $category_name, $source_taxonomy );
				if (!is_wp_error($new_term) && isset($new_term['term_id'])) {
					$event_cat_ids[] = (int) $new_term['term_id'];
				}
			}
		}
		return $event_cat_ids;

	}

	/**
	 * Redirect after activate the plugin.
	 * @return void
	 */
	public function wpea_redirect_after_activation() {
		if ( get_option( 'wpea_plugin_activated' ) ) {
			delete_option( 'wpea_plugin_activated' );
			wp_safe_redirect( admin_url( 'admin.php?page=import_events&tab=wpea_setup_wizard' ) );
			exit;
		}
	}

	/**
	 * Render Page header Section
	 *
	 * @since 1.1
	 * @return void
	 */
	function wpea_render_common_header( $page_title  ){
		?>
		<div class="wpea-header" >
			<div class="wpea-container" >
				<div class="wpea-header-content" >
					<span style="font-size:18px;"><?php esc_attr_e( 'Dashboard','wp-event-aggregator' ); ?></span>
					<span class="spacer"></span>
					<span class="page-name"><?php echo esc_attr( $page_title ); ?></span></span>
					<div class="header-actions" title="documentation" >
						<span class="round">
							<a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/wp-event-aggregator/' ); ?>" target="_blank">
								<svg viewBox="0 0 20 20" fill="#000000" height="20px" xmlns="http://www.w3.org/2000/svg" class="wpea-circle-question-mark">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
								</svg>
							</a>
						</span>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Page Footer Section
	 *
	 * @since 1.1
	 * @return void
	 */
	function wpea_render_common_footer(){
		?>
			<div id="wpea-footer-links" >
				<div class="wpea-footer">
					<div><?php esc_attr_e( 'Made with ♥ by the Xylus Themes', 'wp-event-aggregator' ); ?></div>
					<div class="wpea-links" >
						<a href="<?php echo esc_url( 'https://xylusthemes.com/support/' ); ?>" target="_blank" ><?php esc_attr_e( 'Support', 'wp-event-aggregator'); ?></a>
						<span>/</span>
						<a href="<?php echo esc_url( 'https://docs.xylusthemes.com/docs/wp-event-aggregator/' ); ?>" target="_blank" ><?php esc_attr_e( 'Docs', 'wp-event-aggregator' ); ?></a>
						<span>/</span>
						<a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=xylus&tab=search&type=term' ) ); ?>" ><?php esc_attr_e( 'Free Plugins', 'wp-event-aggregator' ); ?></a>
					</div>
					<div class="wpea-social-links">
						<a href="<?php echo esc_url( 'https://www.facebook.com/xylusinfo/' ); ?>" target="_blank" >
							<svg class="wpea-facebook">
								<path fill="currentColor" d="M16 8.05A8.02 8.02 0 0 0 8 0C3.58 0 0 3.6 0 8.05A8 8 0 0 0 6.74 16v-5.61H4.71V8.05h2.03V6.3c0-2.02 1.2-3.15 3-3.15.9 0 1.8.16 1.8.16v1.98h-1c-1 0-1.31.62-1.31 1.27v1.49h2.22l-.35 2.34H9.23V16A8.02 8.02 0 0 0 16 8.05Z"></path>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://www.linkedin.com/company/xylus-consultancy-service-xcs-/' ); ?>" target="_blank" >
							<svg class="wpea-linkedin">
								<path fill="currentColor" d="M14 1H1.97C1.44 1 1 1.47 1 2.03V14c0 .56.44 1 .97 1H14a1 1 0 0 0 1-1V2.03C15 1.47 14.53 1 14 1ZM5.22 13H3.16V6.34h2.06V13ZM4.19 5.4a1.2 1.2 0 0 1-1.22-1.18C2.97 3.56 3.5 3 4.19 3c.65 0 1.18.56 1.18 1.22 0 .66-.53 1.19-1.18 1.19ZM13 13h-2.1V9.75C10.9 9 10.9 8 9.85 8c-1.1 0-1.25.84-1.25 1.72V13H6.53V6.34H8.5v.91h.03a2.2 2.2 0 0 1 1.97-1.1c2.1 0 2.5 1.41 2.5 3.2V13Z"></path>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://x.com/XylusThemes" target="_blank' ); ?>" target="_blank" >
							<svg class="wpea-twitter" width="24" height="24" viewBox="0 0 24 24">
								<circle cx="12" cy="12" r="12" fill="currentColor"></circle>
								<g>
									<path d="M13.129 11.076L17.588 6H16.5315L12.658 10.4065L9.5665 6H6L10.676 12.664L6 17.9865H7.0565L11.1445 13.332L14.41 17.9865H17.9765L13.129 11.076ZM11.6815 12.7225L11.207 12.0585L7.4375 6.78H9.0605L12.1035 11.0415L12.576 11.7055L16.531 17.2445H14.908L11.6815 12.7225Z" fill="white"></path>
								</g>
							</svg>
						</a>
						<a href="<?php echo esc_url( 'https://www.youtube.com/@xylussupport7784' ); ?>" target="_blank" >
							<svg class="wpea-youtube">
								<path fill="currentColor" d="M16.63 3.9a2.12 2.12 0 0 0-1.5-1.52C13.8 2 8.53 2 8.53 2s-5.32 0-6.66.38c-.71.18-1.3.78-1.49 1.53C0 5.2 0 8.03 0 8.03s0 2.78.37 4.13c.19.75.78 1.3 1.5 1.5C3.2 14 8.51 14 8.51 14s5.28 0 6.62-.34c.71-.2 1.3-.75 1.49-1.5.37-1.35.37-4.13.37-4.13s0-2.81-.37-4.12Zm-9.85 6.66V5.5l4.4 2.53-4.4 2.53Z"></path>
							</svg>
						</a>
					</div>
				</div>
			</div>
		<?php   
	}

	/**
	 * Get WPEA Events Counts
	 *
	 * @since 1.5.0
	 * @return array
	 */
	function wpea_get_wpea_events_counts() {
		global $wpdb;

		// Table names with WordPress prefix
		$posts_table    = $wpdb->prefix . 'posts';
		$postmeta_table = $wpdb->prefix . 'postmeta';

		// Current Unix timestamp
		$current_time = current_time( 'timestamp' );

		// Single query to get all counts
		$sql = "SELECT 
				COUNT( p.ID ) AS all_posts_count,
				SUM( CASE WHEN pm.meta_value > %d THEN 1 ELSE 0 END ) AS upcoming_events_count,
				SUM( CASE WHEN pm.meta_value <= %d THEN 1 ELSE 0 END ) AS past_events_count
			FROM {$posts_table} AS p
			INNER JOIN {$postmeta_table} AS pm ON p.ID = pm.post_id
			WHERE p.post_type = %s
			AND p.post_status = %s
			AND pm.meta_key = %s";

		$prepared_sql = $wpdb->prepare( $sql, $current_time, $current_time, 'wp_events', 'publish', 'end_ts' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$counts       = $wpdb->get_row( $prepared_sql );

		// Return the counts as an array
		return [
			'all'      => intval( $counts->all_posts_count ),
			'upcoming' => intval( $counts->upcoming_events_count ),
			'past'     => intval( $counts->past_events_count ),
		];
	}

	/**
     * Get Plugin array
     *
     * @since 1.1.0
     * @return array
     */
    public function wpea_get_xylus_themes_plugins(){
        return array(
            'wp-bulk-delete' => array( 'plugin_name' => esc_html__( 'WP Bulk Delete', 'wp-event-aggregator' ), 'description' => 'Delete posts, pages, comments, users, taxonomy terms and meta fields in bulk with different powerful filters and conditions.' ),
            'import-eventbrite-events' => array( 'plugin_name' => esc_html__( 'Import Eventbrite Events', 'wp-event-aggregator' ), 'description' => 'Import Eventbrite Events into WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
            'import-facebook-events' => array( 'plugin_name' => esc_html__( 'Import Social Events', 'wp-event-aggregator' ), 'description' => 'Import Facebook events into your WordPress website and/or Event Calendar. Nice Display with shortcode & Event widget.' ),
            'import-meetup-events' => array( 'plugin_name' => esc_html__( 'Import Meetup Events', 'wp-event-aggregator' ), 'description' => 'Import Meetup Events allows you to import Meetup (meetup.com) events into your WordPress site effortlessly.' ),
            'event-schema' => array( 'plugin_name' => esc_html__( 'Event Schema / Structured Data', 'wp-event-aggregator' ), 'description' => 'Automatically Google Event Rich Snippet Schema Generator. This plug-in generates complete JSON-LD based schema (structured data for Rich Snippet) for events.' ),
            'wp-smart-import' => array( 'plugin_name' => esc_html__( 'WP Smart Import : Import any XML File to WordPress', 'wp-event-aggregator' ), 'description' => 'The most powerful solution for importing any CSV files to WordPress. Create Posts and Pages any Custom Posttype with content from any CSV file.' ),
            'xylus-events-calendar' => array( 'plugin_name' => esc_html__( 'Xylus Events Calendar', 'wp-event-aggregator' ), 'description' => 'Display upcoming events from multiple sources in a responsive calendar with customizable layouts like grid, row, calendar, and masonry.' ),
            'xt-feed-for-linkedin' => array( 'plugin_name' => esc_html__( 'XT Feed for LinkedIn', 'wp-event-aggregator' ), 'description' => 'XT Feed for LinkedIn auto-shares WordPress posts to LinkedIn with one click, making content distribution easy and boosting your reach effortlessly.' ),
        );
    }

	/**
	 * Render Event Feature Image Action
	 *
	 * @since 1.1
	 * @return void
	 */
	public function wpea_set_feature_image_logic( $event_id, $image_url, $event_args ){
		global $importevents;
		
		if ( $event_args['import_type'] === 'onetime' && $event_args['import_by'] === 'event_id' ) {
			$importevents->common->setup_featured_image_to_event( $event_id, $image_url );
		} else {
			if ( class_exists( 'WPEA_Event_Image_Scheduler' ) ) {
				WPEA_Event_Image_Scheduler::schedule_image_download( $event_id, $image_url, $event_args );
			}
		}
	}

	/**
	 * Checks if a category exists
	 *
	 * @param string $cat_name The name of the category to check
	 * @param string $taxonomy The taxonomy to check in
	 * @return int The ID of the category if it exists, 0 otherwise
	 */
	public function wpea_check_category_exists( $cat_name, $taxonomy ) {
		$cat_id = 0;

		if ( ! empty( $cat_name ) ) {
			$cat_slug = strtolower(str_replace('_', '-', trim($cat_name)));
			$term = term_exists( $cat_slug, $taxonomy );

			if ( $term === 0 || $term === null ) {
				$cat_name_formatted = str_replace( '_', ' ', $cat_name );
				$cat_name_formatted = ucwords( strtolower( trim( $cat_name_formatted ) ) );

				$term = term_exists( $cat_name_formatted, $taxonomy );

				if ( $term === 0 || $term === null ) {
					$new_term = wp_insert_term( $cat_name_formatted, $taxonomy, array(
						'slug' => $cat_slug
					));

					if ( ! is_wp_error( $new_term ) && isset( $new_term['term_id'] ) ) {
						$cat_id = (int) $new_term['term_id'];
					}
				} else {
					if ( is_array( $term ) && isset( $term['term_id'] ) ) {
						$cat_id = (int) $term['term_id'];
					} elseif ( is_numeric( $term ) ) {
						$cat_id = (int) $term;
					}
				}
			} else {
				if ( is_array( $term ) && isset( $term['term_id'] ) ) {
					$cat_id = (int) $term['term_id'];
				} elseif ( is_numeric( $term ) ) {
					$cat_id = (int) $term;
				}
			}
		}

		return $cat_id;
	}
}


/**
 * Check if Pro addon is enabled or not.
 *
 * @since 1.5.0
 */
function wpea_is_pro(){
	if( !function_exists( 'is_plugin_active' ) ){
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	}
	if ( is_plugin_active( 'wp-event-aggregator-pro/wp-event-aggregator-pro.php' ) ) {
		return true;
	}
	return false;
}

/**
 * Check is pro active or not.
 *
 * @since  1.5.0
 * @return boolean
 */
function wpea_aioec_active() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( is_plugin_active( 'all-in-one-event-calendar/all-in-one-event-calendar.php' ) ) {
		return true;
	}
	return false;
}

/**
 * Template Functions
 *
 * Template functions specifically created for Event Listings
 *
 * @author 		Dharmesh Pate
 * @version     1.5.0
 */

/**
 * Gets and includes template files.
 *
 * @since 1.5.0
 * @param mixed  $template_name
 * @param array  $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function get_wpea_template( $template_name, $args = array(), $template_path = 'wp-event-aggregator', $default_path = '' ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}
	include( locate_wpea_template( $template_name, $template_path, $default_path ) );
}

/**
 * Locates a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *		yourtheme		/	$template_path	/	$template_name
 *		yourtheme		/	$template_name
 *		$default_path	/	$template_name
 *
 * @since 1.5.0
 * @param string      $template_name
 * @param string      $template_path (default: 'wp-event-aggregator')
 * @param string|bool $default_path (default: '') False to not load a default
 * @return string
 */
function locate_wpea_template( $template_name, $template_path = 'wp-event-aggregator', $default_path = '' ) {
	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);
	// Get default template
	if ( ! $template && $default_path !== false ) {
		$default_path = $default_path ? $default_path : WPEA_PLUGIN_DIR . '/templates/';
		if ( file_exists( trailingslashit( $default_path ) . $template_name ) ) {
			$template = trailingslashit( $default_path ) . $template_name;
		}
	}
	// Return what we found
	return apply_filters( 'wepa_locate_template', $template, $template_name, $template_path );
}

/**
 * Gets template part (for templates in loops).
 *
 * @since 1.0.0
 * @param string      $slug
 * @param string      $name (default: '')
 * @param string      $template_path (default: 'wp-event-aggregator')
 * @param string|bool $default_path (default: '') False to not load a default
 */
function get_wpea_template_part( $slug, $name = '', $template_path = 'wp-event-aggregator', $default_path = '' ) {
	$template = '';
	if ( $name ) {
		$template = locate_wpea_template( "{$slug}-{$name}.php", $template_path, $default_path );
	}
	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/wp-event-aggregator/slug.php
	if ( ! $template ) {
		$template = locate_wpea_template( "{$slug}.php", $template_path, $default_path );
	}
	if ( $template ) {
		load_template( $template, false );
	}
}

/**
 * Get Batch of in-progress background imports.
 *
 * @return array $batches
 */
function wpea_get_inprogress_import(){
	global $wpdb;
	$batch_query = "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '%wpea_import_batch_%' ORDER BY option_id ASC";
	if ( is_multisite() ) {
		$batch_query = "SELECT * FROM {$wpdb->sitemeta} WHERE meta_key LIKE '%wpea_import_batch_%' ORDER BY meta_id ASC";
	}
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$batches = $wpdb->get_results( $batch_query );
	return $batches;
}

/**
 * Get Markup for eventbrite non-model checkout.
 *
 * @return string
 */
function wpea_nonmodel_checkout_markup( $eventbrite_id ){
	ob_start();
	?>
	<div id="wpea-eventbrite-checkout-widget"></div>
	<script src="https://www.eventbrite.com/static/widgets/eb_widgets.js"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<script type="text/javascript">
		var orderCompleteCallback = function() {
			console.log("Order complete!");
		};
		window.EBWidgets.createWidget({
			widgetType: "checkout",
			eventId: "<?php echo esc_attr( $eventbrite_id ); ?>",
			iframeContainerId: "wpea-eventbrite-checkout-widget",
			iframeContainerHeight: <?php echo esc_attr( apply_filters('wpea_embeded_checkout_height', 530 ) ); ?>,
			onOrderComplete: orderCompleteCallback
		});
	</script>
	<?php
	return ob_get_clean();
}

/**
 * Get Markup for eventbrite model checkout.
 *
 * @return string
 */
function wpea_model_checkout_markup( $eventbrite_id ){
	ob_start();
	?>
	<button id="wpea-eventbrite-checkout-trigger" type="button">
		<?php esc_html_e( 'Buy Tickets', 'wp-event-aggregator' ); ?>
	</button>
	<script src="https://www.eventbrite.com/static/widgets/eb_widgets.js"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<script type="text/javascript">
		var orderCompleteCallback = function() {
			console.log("Order complete!");
		};

		window.EBWidgets.createWidget({
			widgetType: "checkout",
			eventId: "<?php echo esc_attr( $eventbrite_id ); ?>",
			modal: true,
			modalTriggerElementId: "wpea-eventbrite-checkout-trigger",
			onOrderComplete: orderCompleteCallback
		});
	</script>
	<?php
	return ob_get_clean();
}