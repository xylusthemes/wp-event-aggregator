<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ShortcodeTable = new WPEA_Shortcode_List_Table();
$ShortcodeTable->prepare_items();

?>
<div class="wpea_container">
    <div class="wpea_row">
    <h3 class="setting_bar"><?php esc_attr_e( 'WP Event Aggregator Shortcodes', 'wp-event-aggregator' ); ?></h3>
        <?php $ShortcodeTable->display(); ?>
    </div>
</div>