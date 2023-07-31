<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
global $importevents;
?>
<div class="wpea_container">
    <div class="wpea_row">
    	<div class="wp-filter">
		    <ul class="filter-links">
			    <li>
		    		<a class="<?php if ( $ntab == 'import' ) { echo 'current'; } ?>" href="<?php echo esc_url( add_query_arg( 'ntab', 'import' ) ); ?>">
		    		<?php esc_html_e( 'New Import', 'wp-event-aggregator' ); ?>
		    		</a>
			    </li>
			    <li>
		    		<a class="<?php if ( $ntab == 'scheduled_import' ) { echo 'current'; } ?>" href="<?php echo esc_url( add_query_arg( 'ntab', 'scheduled_import' ) ); ?>">
		    		<?php esc_html_e( 'Scheduled Imports', 'wp-event-aggregator' ); ?>
		    		</a>
			    </li>
			</ul>
		</div>

		<?php if( $ntab == 'import' ){ ?>
        <div class="wpea-column wpea_well">
            <h3><?php esc_attr_e( 'Meetup Import', 'wp-event-aggregator' ); ?></h3>
            <form method="post" id="wpea_meetup_form">
           	
				<table class="form-table">
		            <tbody>
						<tr>
							<th scope="row">
								<?php esc_attr_e( 'Import by', 'wp-event-aggregator' ); ?> :
							</th>
							<td>
								<select name="meetup_import_by" id="wpea_meetup_import_by">
									<option value="event_id"><?php esc_attr_e( 'Event ID', 'wp-event-aggregator' ); ?></option>
									<option value="group_url"><?php esc_attr_e( 'Group URL', 'wp-event-aggregator' ); ?></option>
								</select>
								<span class="wpea_small">
									<?php _e( 'Select Event source. 1. by Event ID, 2. by Group URL', 'wp-event-aggregator' ); ?>
								</span>
							</td>
						</tr>

						<tr class="meetup_event_id">
							<th scope="row">
								<?php esc_attr_e( 'Meetup Event ID', 'wp-event-aggregator' ); ?> : 
							</th>
							<td>
								<?php if ( wpea_is_pro() ) { ?>
								<textarea class="ime_meetup_ids" name="ime_event_ids" type="text" rows="5" cols="50"></textarea>
								<span class="wpea_small">
									<?php _e( 'One event ID per line, (Eg. Event ID for https://www.meetup.com/xxxx-xxx-xxxx/events/xxxxxxxxx is <span class="borderall">xxxxxxxxx</span>).<br> ', 'wp-event-aggregator' ); ?>
								</span>
								<?php } else { ?>
								<input class="wpea_text" name="ime_event_ids" type="text" />
								<span class="wpea_small">
									<?php _e( 'Insert Meetup event ID (Eg. https://www.meetup.com/xxxx-xxx-xxxx/events/<span class="borderall">xxxxxxxxx</span>).', 'wp-event-aggregator' ); ?>
								</span>
								<?php } ?>
							</td>
						</tr>

						<tr class="meetup_group_url">
							<th scope="row">
								<?php esc_attr_e( 'Meetup Group URL','wp-event-aggregator' ); ?> : 
							</th>
							<td>
								<input class="wpea_text" name="meetup_url" type="text" <?php if ( ! wpea_is_pro() ) { echo 'disabled="disabled"'; } ?> />
								<span class="wpea_small">
									<?php _e( 'Insert Meetup group url (Eg. -<span class="borderall">https://www.meetup.com/xxxx-xxx-xxxx/</span>).', 'wp-event-aggregator' ); ?>
								</span>
								<?php do_action( 'wpea_render_pro_notice' ); ?>
							</td>
						</tr>

						<tr class="import_type_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Import Type','wp-event-aggregator' ); ?> : 
					    	</th>
					    	<td>
						    	<?php $importevents->common->render_import_type(); ?>
					    	</td>
					    </tr>

					    <?php 
					    $importevents->common->render_import_into_and_taxonomy();
					    $importevents->common->render_eventstatus_input();
					    ?>


					</tbody>
		        </table>
                
                <div class="wpea_element">
                	<input type="hidden" name="import_origin" value="meetup" />
                    <input type="hidden" name="wpea_action" value="wpea_import_submit" />
                    <?php wp_nonce_field( 'wpea_import_form_nonce_action', 'wpea_import_form_nonce' ); ?>
                    <input type="submit" class="button-primary wpea_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'wp-event-aggregator' ); ?>" />
                </div>
            </form>
			<?php } elseif( $ntab == 'scheduled_import' ){
				?>
				<form id="scheduled-import" method="get">
				<input type="hidden" name="page" value="<?php echo sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); ?>" />
				<input type="hidden" name="tab" value="<?php echo $tab = isset($_REQUEST['tab'])? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'eventbrite' ?>" />
				<input type="hidden" name="ntab" value="<?php echo sanitize_text_field( wp_unslash( $_REQUEST['ntab'] ) ); ?>" />
				<?php 
				if( wpea_is_pro() ){
					$listtable = new WP_Event_Aggregator_List_Table();
					$listtable->prepare_items('meetup');
					$listtable->display();
				}else{
					do_action( 'wpea_render_pro_notice' );
				}
				?>
				</form>
				<?php
			} ?>
        </div>
    </div>
</div>
