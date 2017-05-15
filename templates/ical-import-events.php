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
            <h3><?php esc_attr_e( 'iCal / .ics Import', 'wp-event-aggregator' ); ?></h3>
            <form method="post" enctype="multipart/form-data" id="wpea_eventbrite_form">
           	
               	<table class="form-table">
		            <tbody>
		                <tr>
					        <th scope="row">
					        	<?php esc_attr_e( 'Import by','wp-event-aggregator' ); ?> :
					        </th>
					        <td>
					            <select name="ical_import_by" id="ical_import_by">
					            	<option value="ics_file"><?php esc_attr_e( '.ics File','wp-event-aggregator' ); ?></option>
			                    	<option value="ical_url"><?php esc_attr_e( 'iCal URL','wp-event-aggregator' ); ?></option>
			                    </select>
			                    <span class="wpea_small">
			                        <?php _e( 'Select Event source.', 'wp-event-aggregator' ); ?>
			                    </span>
					        </td>
					    </tr>
					    
					    <tr class="ical_url_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( 'iCal URL','wp-event-aggregator' ); ?> : 
					    	</th>
					    	<td>
					    		<input class="wpea_text ical_url" name="ical_url" type="text" disabled="disabled" />
			                    <span class="wpea_small">
			                        <?php _e( 'Enter iCal URL ( Eg. https://www.xyz.com/ical-url.ics )', 'wp-event-aggregator' ); ?>
			                    </span>
			                    <?php do_action( 'wpea_render_pro_notice' ); ?>
					    	</td>
					    </tr>

					    <tr class="ics_file_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( '.ics File','wp-event-aggregator' ); ?> : 
					    	</th>
					    	<td>
					    		<input class="wpea_text ics_file_class" name="ics_file" type="file" accept=".ics" />
					    	</td>
					    </tr>

					    <tr class="import_date_range">
					        <th scope="row">
					        	<?php esc_attr_e( 'Events date range','wp-event-aggregator' ); ?> :
					        </th>
					        <td>
					            <input type="text" name="start_date" class="xt_datepicker start_date" placeholder="<?php esc_html_e('Select start date', 'wp-event-aggregator' ); ?>"> - 
					            <input type="text" name="end_date" class="xt_datepicker end_date" placeholder="<?php esc_html_e('Select end date', 'wp-event-aggregator' ); ?>">
			                    <span class="wpea_small">
			                        <?php _e( 'Select date range from which you want to import events. Default startdate is Today', 'wp-event-aggregator' ); ?>
			                    </span>
					        </td>
					    </tr>

					    <tr class="import_type_wrapper">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Import type','wp-event-aggregator' ); ?> : 
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
                	<input type="hidden" name="import_origin" value="ical" />
                    <input type="hidden" name="wpea_action" value="wpea_import_submit" />
                    <?php wp_nonce_field( 'wpea_import_form_nonce_action', 'wpea_import_form_nonce' ); ?>
                    <input type="submit" class="button-primary wpea_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'wp-event-aggregator' ); ?>" />
                </div>
            </form>
			<?php } elseif( $ntab == 'scheduled_import' ){
				?>
				<form id="scheduled-import" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<input type="hidden" name="tab" value="<?php echo $tab = isset($_REQUEST['tab'])? $_REQUEST['tab'] : 'ical' ?>" />
				<input type="hidden" name="ntab" value="<?php echo $_REQUEST['ntab'] ?>" />
				<?php do_action( 'wpea_render_pro_notice' ); ?>
				</form>
				<?php
			} ?>
        </div>
    </div>
</div>
