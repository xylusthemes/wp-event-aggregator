<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$shortcodeTable = new WPEA_Shortcode_List_Table();
$shortcodeTable->prepare_items();

?>

<div class="wpea-xylus-promo-wrapper">
    <div class="wpea-xylus-promo-header">
        <h2><?php esc_attr_e( '🎉 Try Our New Plugin – Xylus Events Calendar', 'wp-event-aggregator' ); ?></h2>
        <p><?php esc_attr_e( 'A modern, clean and powerful way to display events. Includes calendar view, search, filters, pagination, and tons of settings. And it’s 100% FREE!', 'wp-event-aggregator' ); ?></p>
    </div>
    <div class="wpea-xylus-main-inner-container">
        <div>
            <ul class="wpea-xylus-feature-list">
                <li><?php esc_attr_e( '✅ Full Calendar Monthly View', 'wp-event-aggregator' ); ?></li>
                <li><?php esc_attr_e( '🔍 Event Search & Filter Support', 'wp-event-aggregator' ); ?></li>
                <li><?php esc_attr_e( '📅 Pagination & Multiple Layouts', 'wp-event-aggregator' ); ?></li>
                <li><?php esc_attr_e( '⚙️ Tons of Settings for Customization', 'wp-event-aggregator' ); ?></li>
                <li><?php esc_attr_e( '🎨 Frontend Styling Options', 'wp-event-aggregator' ); ?></li>
                <li><?php esc_attr_e( '💯 100% Free Plugin', 'wp-event-aggregator' ); ?></li>
            </ul>
            <?php
                $plugin_slug = 'xylus-events-calendar';
                $plugin_file = 'xylus-events-calendar/xylus-events-calendar.php';
                $current_page = admin_url( 'admin.php?page=eventbrite_event&tab=shortcodes' );
                if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
                    $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ), 'install-plugin_' . $plugin_slug );
                    echo '<a href="' . esc_url( $install_url ) . '" class="button button-primary">🚀 Install Now – It’s Free!</a>';
                } elseif ( ! is_plugin_active( $plugin_file ) ) {
                    $activate_url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ), 'activate-plugin_' . $plugin_file );
                    echo '<a href="' . esc_url( $activate_url ) . '" class="button button-secondary">⚡ Activate Plugin</a>';
                } else {
                    echo '<div class="wpea-xylus-plugin-box">';
                    echo '<h3>✅ Xylus Events Calendar is Active</h3>';
                    echo '<p style="margin: 0;">You can now display events anywhere using this shortcode</p>';
                    echo '<span class="wpea_short_code">[xylus_events_calendar]</span>';
                    echo '<button class="wpea-btn-copy-shortcode wpea_button" data-value="[xylus_events_calendar]">Copy</button>';
                    echo '</div>';
                }
            ?>
        </div>
        <div class="wpea-xylus-screenshot-slider">
            <div class="wpea-screenshot-slide active">
                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                <img src="<?php echo esc_url( 'https://ps.w.org/xylus-events-calendar/assets/screenshot-1.png' ); ?>" alt="Monthly View">
            </div>
            <div class="wpea-screenshot-slide">
                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                <img src="<?php echo esc_url( 'https://ps.w.org/xylus-events-calendar/assets/screenshot-2.png' ); ?>" alt="Event Settings">
            </div>
            <div class="wpea-screenshot-slide">
                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                <img src="<?php echo esc_url( 'https://ps.w.org/xylus-events-calendar/assets/screenshot-3.png' ); ?>" alt="List View">
            </div>
            <div class="wpea-screenshot-slide">
                <?php // phpcs:disable PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage  ?>
                <img src="<?php echo esc_url( 'https://ps.w.org/xylus-events-calendar/assets/screenshot-4.png' ); ?>" alt="Event Details">
            </div>
        </div>
    </div>
</div>
<div class="wpea_container">
    <div class="wpea_row">
    <h3 class="setting_bar"><?php esc_attr_e( 'WP Event Aggregator Shortcodes', 'wp-event-aggregator' ); ?></h3>
        <?php $shortcodeTable->display(); ?>
    </div>
</div>