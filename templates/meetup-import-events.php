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
		                <tr class="meetup_group_url">
					    	<th scope="row">
					    		<?php esc_attr_e( 'Meetup Group URL','wp-event-aggregator' ); ?> : 
					    	</th>
					    	<td>
					    		<input class="wpea_text" name="meetup_url" type="url" required="required" />
			                    <span class="wpea_small">
			                        <?php _e( 'Insert meetup group url ( Eg. https://www.meetup.com/ny-tech/).', 'wp-event-aggregator' ); ?>
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
                	<input type="hidden" name="import_origin" value="meetup" />
                    <input type="hidden" name="wpea_action" value="wpea_import_submit" />
                    <?php wp_nonce_field( 'wpea_import_form_nonce_action', 'wpea_import_form_nonce' ); ?>
                    <input type="submit" class="button-primary wpea_submit_button" style=""  value="<?php esc_attr_e( 'Import Event', 'wp-event-aggregator' ); ?>" />
                </div>
            </form>
			<?php } elseif( $ntab == 'scheduled_import' ){
				?>
				<form id="scheduled-import" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<input type="hidden" name="tab" value="<?php echo $tab = isset($_REQUEST['tab'])? $_REQUEST['tab'] : 'eventbrite' ?>" />
				<input type="hidden" name="ntab" value="<?php echo $_REQUEST['ntab'] ?>" />
				<?php do_action( 'wpea_render_pro_notice' ); ?>
				</form>
				<?php
			} ?>
        </div>
    </div>
</div>
