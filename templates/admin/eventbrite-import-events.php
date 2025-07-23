<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $importevents;
?>
<div class="wpea-card" style="margin-top:20px;" >			
	<div class="wpea-app" >
		<div class="wpea-tabs" >
			<div class="tabs-scroller">
				<div class="var-tabs var-tabs--item-horizontal var-tabs--layout-horizontal-padding">
					<div class="var-tabs__tab-wrap var-tabs--layout-horizontal">
						<a href="?page=import_events&tab=eventbrite&ntab=import" class="var-tab <?php echo $ntab == 'import' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
							<span class="tab-label"><?php esc_attr_e( 'New Import', 'wp-event-aggregator' ); ?></span>
						</a>

						<a href="?page=import_events&tab=eventbrite&ntab=scheduled_import" class="var-tab <?php echo $ntab == 'scheduled_import' ? 'var-tab--active' : 'var-tab--inactive'; ?>">
							<span class="tab-label"><?php esc_attr_e( 'Scheduled Imports', 'wp-event-aggregator' ); ?></span>
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php 
		if( $ntab == 'import' ){ ?>
			<form method="post" enctype="multipart/form-data" id="wpea_eventbrite_form">
				<div class="wpea-card" >			
					<div class="wpea-content wpea_source_import" >
						<div class="wpea-inner-main-section" >
							<div class="wpea-inner-section-1" >
								<span class="wpea-title-text" ><?php esc_attr_e( 'Import by', 'wp-event-aggregator' ); ?>
									<span class="wpea-tooltip">
										<div>
											<svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpea-circle-question-mark">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
											</svg>
											<span class="wpea-popper">
												<?php 
													$text = sprintf(
														/* translators: 1: First option (by Facebook Event ID), 2: Second option (Facebook Page) */
														esc_html__( 'Select Event source. %1$s %2$s %3$s', 'wp-event-aggregator' ),
														'<br><strong>' . esc_html__( '1. Eventbrite Event ID', 'wp-event-aggregator' ) . '</strong>',
														'<br><strong>' . esc_html__( '2. Eventbrite Organizer ID', 'wp-event-aggregator' ) . '</strong>',
														'<br><strong>' . esc_html__( '3. Eventbrite Collection ID', 'wp-event-aggregator' ) . '</strong>'
													);
													
													echo wp_kses(
														$text,
														array(
															'strong' => array(),
															'br' => array(),
														)
													);
												?>
												<div class="wpea-popper__arrow"></div>
											</span>
										</div>
									</span>
								</span>
							</div>
							<div class="wpea-inner-section-2">
								<select name="eventbrite_import_by" id="eventbrite_import_by">
									<option value="event_id"><?php esc_attr_e( 'Event ID','wp-event-aggregator' ); ?></option>
									<option value="organizer_id"><?php esc_attr_e( 'Organizer ID','wp-event-aggregator' ); ?></option>
									<option value="collection_id"><?php esc_attr_e( 'Collection ID', 'wp-event-aggregator' ); ?></option>
								</select>
							</div>
						</div>

						<div class="wpea-inner-main-section eventbrite_event_id" >
							<div class="wpea-inner-section-1" >
								<span class="wpea-title-text" >
									<?php 
										if( wpea_is_pro() ){
											esc_attr_e( 'Eventbrite Event IDs','wp-event-aggregator' );
										}else{
											esc_attr_e( 'Eventbrite Event ID','wp-event-aggregator' ); 	
										}					    		
									?>
								</span>
							</div>
							<div class="wpea-inner-section-2">
								<?php if( wpea_is_pro() ){ ?>
									<textarea class="wpea_text" name="wpea_eventbrite_id" rows="5" cols="53"></textarea>
								<?php } else { ?>
									<input class="wpea_text" name="wpea_eventbrite_id" type="text" />					    		
								<?php }?>
								<span class="wpea_small">
									<?php 
										if ( wpea_is_pro() ) {
											printf(
												/* translators: %s: Example Eventbrite event ID */
												esc_html__( 'Eventbrite Event IDs, one Event ID per line (Eg. https://www.eventbrite.com/e/event-import-with-wordpress-%s).', 'wp-event-aggregator' ),
												'<span class="borderall">12265498440</span>'
											);
										} else {
											printf(
												/* translators: %s: Example Eventbrite event ID */
												esc_html__( 'Insert Eventbrite event ID (Eg. https://www.eventbrite.com/e/event-import-with-wordpress-%s).', 'wp-event-aggregator' ),
												'<span class="borderall">12265498440</span>'
											);
										}									
									?>
							</div>
						</div>

						<div class="wpea-inner-main-section eventbrite_organizer_id" >
							<div class="wpea-inner-section-1" >
								<span class="wpea-title-text" ><?php esc_attr_e( 'Eventbrite Organizer ID','wp-event-aggregator' ); ?></span>
							</div>
							<div class="wpea-inner-section-2">
								<input class="wpea_text wpea_organizer_id" name="wpea_organizer_id" type="text" <?php if( !wpea_is_pro() ){ echo 'disabled="disabled';} ?> />
								<span class="wpea_small">
									<?php
										printf(
											/* translators: %s: Organizer ID wrapped in span */
											esc_html__( 'Insert Eventbrite Organizer ID (e.g. https://www.eventbrite.com/o/cept-university-%s).', 'wp-event-aggregator' ),
											'<span class="borderall">9151813372</span>'
										);
									?>
								</span>
								<?php do_action( 'wpea_render_pro_notice' ); ?>
							</div>
						</div>

						<div class="wpea-inner-main-section eventbrite_collection_id" >
							<div class="wpea-inner-section-1" >
								<span class="wpea-title-text" ><?php esc_attr_e( 'Eventbrite Collection ID','wp-event-aggregator' ); ?></span>
							</div>
							<div class="wpea-inner-section-2">
								<input class="wpea_text wpea_collection_id" name="wpea_collection_id" type="text" <?php if( !wpea_is_pro() ){ echo 'disabled="disabled';} ?> />
								<span class="wpea_small">
									<?php
										printf(
											/* translators: %s: Organizer ID wrapped in span */
											esc_html__( 'Insert Eventbrite Collection ID (e.g. https://www.eventbrite.com/cc/xxxxx-xxxxx-%s).', 'wp-event-aggregator' ),
											'<span class="borderall">3732699</span>'
										);
									?>
								</span>
								<?php do_action( 'wpea_render_pro_notice' ); ?>
							</div>
						</div>

						<div class="wpea-inner-main-section" >
							<div class="wpea-inner-section-1" >
								<span class="wpea-title-text" ><?php esc_attr_e( 'Import Type','wp-event-aggregator' ); ?></span>
							</div>
							<div class="wpea-inner-section-2">
								<?php $importevents->common->render_import_type(); ?>
							</div>
						</div>

						<?php 
							// import into.
							$importevents->common->render_import_into_and_taxonomy();
							$importevents->common->render_eventstatus_input();
						?>

						<div class="wpea-inner-main-section">
							<div class="wpea-inner-section-1" >
								<span class="wpea-title-text" ><?php esc_attr_e( 'Author', 'wp-event-aggregator' ); ?> 
									<span class="wpea-tooltip">
										<div>
											<svg viewBox="0 0 20 20" fill="#000" xmlns="http://www.w3.org/2000/svg" class="wpea-circle-question-mark">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M1.6665 10.0001C1.6665 5.40008 5.39984 1.66675 9.99984 1.66675C14.5998 1.66675 18.3332 5.40008 18.3332 10.0001C18.3332 14.6001 14.5998 18.3334 9.99984 18.3334C5.39984 18.3334 1.6665 14.6001 1.6665 10.0001ZM10.8332 13.3334V15.0001H9.1665V13.3334H10.8332ZM9.99984 16.6667C6.32484 16.6667 3.33317 13.6751 3.33317 10.0001C3.33317 6.32508 6.32484 3.33341 9.99984 3.33341C13.6748 3.33341 16.6665 6.32508 16.6665 10.0001C16.6665 13.6751 13.6748 16.6667 9.99984 16.6667ZM6.6665 8.33341C6.6665 6.49175 8.15817 5.00008 9.99984 5.00008C11.8415 5.00008 13.3332 6.49175 13.3332 8.33341C13.3332 9.40251 12.6748 9.97785 12.0338 10.538C11.4257 11.0695 10.8332 11.5873 10.8332 12.5001H9.1665C9.1665 10.9824 9.9516 10.3806 10.6419 9.85148C11.1834 9.43642 11.6665 9.06609 11.6665 8.33341C11.6665 7.41675 10.9165 6.66675 9.99984 6.66675C9.08317 6.66675 8.33317 7.41675 8.33317 8.33341H6.6665Z" fill="currentColor"></path>
											</svg>
											<span class="wpea-popper">
												<?php esc_attr_e( 'Select event author for imported events. Default event auther is current loggedin user.', 'wp-event-aggregator' ); ?>
												<div class="wpea-popper__arrow"></div>
											</span>
										</div>
									</span>
								</span>
							</div>
							<div class="wpea-inner-section-2">
								<?php wp_dropdown_users( array( 'show_option_none' => esc_attr__( 'Select Author','wp-event-aggregator'), 'name' => 'event_author', 'option_none_value' => get_current_user_id() ) ); ?>
							</div>
						</div>

						<div class="">
							<input type="hidden" name="import_origin" value="eventbrite" />
							<input type="hidden" name="wpea_action" value="wpea_import_submit" />
							<?php wp_nonce_field( 'wpea_import_form_nonce_action', 'wpea_import_form_nonce' ); ?>
							<input type="submit" class="wpea_button wpea_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'wp-event-aggregator' ); ?>" />
						</div>
					</div>
				</div>
			</form>
	<?php 
		} elseif( $ntab == 'scheduled_import' ){
			?>
			<div class="wpea-card" >			
				<div class="wpea-content wpea_source_import" >
					<div class="wpea-inner-main-section">
						<form id="scheduled-import" method="get">
							<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated  ?>" />
							<input type="hidden" name="tab" value="<?php echo $tab = isset($_REQUEST['tab'])? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) ) : 'eventbrite';// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated ?>" />
							<input type="hidden" name="ntab" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['ntab'] ) ) );  // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated ?>" />
							<?php
							if( wpea_is_pro() ){
								$listtable = new WP_Event_Aggregator_List_Table();
								$listtable->prepare_items('eventbrite');
								$listtable->display();
							}else{
								do_action( 'wpea_render_pro_notice' );	
							}				
							?>
						</form>
					</div>
				</div>
			</div>
			<?php
		} 
	?>
</div>

