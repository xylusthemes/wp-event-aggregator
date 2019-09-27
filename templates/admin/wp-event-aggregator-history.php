<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;

// Add Thickbox support.
add_thickbox();
$listtable = new WP_Event_Aggregator_History_List_Table();
$listtable->prepare_items();
?>
<div class="wpea_container">
    <div class="wpea_row">
        <div class="">
			<form id="import-history" method="get">
				<input type="hidden" name="page" value="<?php echo sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ); ?>" />
				<input type="hidden" name="tab" value="<?php echo $tab = isset($_REQUEST['tab'])? sanitize_text_field( wp_unslash( $_REQUEST['tab'] ) ) : 'history' ?>" />
				<input type="hidden" name="ntab" value="" />
        		<?php
				$listtable->display();
        		?>
			</form>
        </div>
    </div>
</div>