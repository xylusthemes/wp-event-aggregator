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
            <h3><?php esc_attr_e( 'Eventbrite Import', 'wp-event-aggregator' ); ?></h3>
            <form method="post" enctype="multipart/form-data" id="wpea_eventbrite_form">
           	
               	<table class="form-table">
		            <tbody>
		                <tr>
					        <th scope="row">
					        	<?php esc_attr_e( 'Import by','wp-event-aggregator' ); ?> :
					        </th>
					        <td>
					            <select name="eventbrite_import_by" id="eventbrite_import_by">
			                    	<option value="event_id"><?php esc_attr_e( 'Event ID','wp-event-aggregator' ); ?></option>
			                    	<option value="organizer_id"><?php esc_attr_e( 'Organizer ID','wp-event-aggregator' ); ?></option>
			                    </select>
			                    <span class="wpea_small">
			                        <?php _e( 'Select Event source. 1. by Event ID, 2. by Oraganizer ID.', 'wp-event-aggregator' ); ?>
			                    </span>
					        </td>
					    </tr>
					    
					    <tr class="eventbrite_event_id">
					    	<th scope="row">
					    		<?php 
					    		if( wpea_is_pro() ){
					    			esc_attr_e( 'Eventbrite Event IDs','wp-event-aggregator' );
					    		}else{
					    			esc_attr_e( 'Eventbrite Event ID','wp-event-aggregator' ); 	
					    		}					    		
					    		?> : 
					    	</th>
					    	<td>
					    		<?php if( wpea_is_pro() ){ ?>
					    			<textarea class="wpea_text" name="wpea_eventbrite_id" rows="5" cols="50"></textarea>
					    		<?php } else { ?>
									<input class="wpea_text" name="wpea_eventbrite_id" type="text" />					    		
					    		<?php }?>
			                    <span class="wpea_small">
			                        <?php 
			                        if( wpea_is_pro() ){
			                        	_e( 'Eventbrite Event IDs, one Event ID per line (Eg. https://www.eventbrite.com/e/event-import-with-wordpress-<span class="borderall">12265498440</span>).', 'wp-event-aggregator' );
			                        } else {
			                        	_e( 'Insert Eventbrite event ID ( Eg. https://www.eventbrite.com/e/event-import-with-wordpress-<span class="borderall">12265498440</span>  ).', 'wp-event-aggregator' );
			                        }
			                        ?>
			                    </span>
					    	</td>
					    </tr>

					    <tr class="eventbrite_organizer_id">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Eventbrite Organizer ID','wp-event-aggregator' ); ?> : 
					    	</th>
					    	<td>
					    		<input class="wpea_text wpea_organizer_id" name="wpea_organizer_id" type="text" <?php if( !wpea_is_pro() ){ echo 'disabled="disabled';} ?> />
			                    <span class="wpea_small">
			                        <?php _e( 'Insert Eventbrite Organizer ID (Eg. https://www.eventbrite.com/o/cept-university-<span class="borderall">9151813372</span>).', 'wp-event-aggregator' ); ?>
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
						// import into.
					    $importevents->common->render_import_into_and_taxonomy();
					    $importevents->common->render_eventstatus_input();
					    ?>
					</tbody>
		        </table>
                
                <div class="wpea_element">
                	<input type="hidden" name="import_origin" value="eventbrite" />
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
					$listtable->prepare_items('eventbrite');
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
