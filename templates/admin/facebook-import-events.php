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
            <h3><?php esc_attr_e( 'Facebook Import', 'wp-event-aggregator' ); ?></h3>
            <form method="post" id="wpea_facebook_form">
           	
               	<table class="form-table">
		            <tbody>
		                <tr>
					        <th scope="row">
					        	<?php esc_attr_e( 'Import by','wp-event-aggregator' ); ?> :
					        </th>
					        <td>
					            <select name="facebook_import_by" id="facebook_import_by">
			                    	<option value="facebook_event_id"><?php esc_attr_e( 'Facebook Event ID','wp-event-aggregator' ); ?></option>
			                    	<option value="facebook_organization"><?php esc_attr_e( 'Facebook Page','wp-event-aggregator' ); ?></option>
			                    </select>
			                    <span class="wpea_small">
			                        <?php echo wp_kses_post( __( 'Select Event source. <strong>1. by Facebook Event ID</strong>, <strong>2. Facebook Page</strong> (Import events from Facebook page).', 'wp-event-aggregator' ) ); ?>
			                    </span>
					        </td>
					    </tr>
					    
					    <tr class="facebook_eventid_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Facebook Event IDs','wp-event-aggregator' ); ?> : 
					    	</th>
					    	<td>
					    		<textarea name="facebook_event_ids" class="facebook_event_ids" rows="5" cols="50"></textarea>
					    		<span class="wpea_small">
			                        <?php echo wp_kses_post( __( 'One event ID per line, (Eg. Event ID for https://www.facebook.com/events/123456789/ is "123456789").', 'wp-event-aggregator' ) ); ?>
			                    </span>
					    	</td>
					    </tr>

					    <tr class="facebook_page_wrapper" style="display: none;">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Page username / ID to fetch events from','wp-event-aggregator' ); ?> : 
					    	</th>
					    	<td> 
					    		<input class="wpea_text" class="facebook_page_username" type="text"  <?php if( wpea_is_pro() ){ echo 'name="facebook_page_username"'; }else{ echo 'disabled="disabled"'; } ?>/>
			                    <span class="wpea_small">
			                        <?php echo wp_kses_post( __( ' Eg. username for https://www.facebook.com/xylusinfo/ is "xylusinfo".', 'wp-event-aggregator' ) ); ?>
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
                	<input type="hidden" name="import_origin" value="facebook" />
                    <input type="hidden" name="wpea_action" value="wpea_import_submit" />
                    <?php wp_nonce_field( 'wpea_import_form_nonce_action', 'wpea_import_form_nonce' ); ?>
                    <input type="submit" class="button-primary wpea_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'wp-event-aggregator' ); ?>" />
                </div>
            </form>
			<?php } elseif( $ntab == 'scheduled_import' ){
				?>
				<form id="scheduled-import" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated ?>" />
				<input type="hidden" name="tab" value="<?php echo $tab = isset($_REQUEST['tab'])? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) ) : 'eventbrite'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated ?>" />
				<input type="hidden" name="ntab" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['ntab'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated ?>" />
				<?php
				if( wpea_is_pro() ){
					$listtable = new WP_Event_Aggregator_List_Table();
					$listtable->prepare_items('facebook');
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
