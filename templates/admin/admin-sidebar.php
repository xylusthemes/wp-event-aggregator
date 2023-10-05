<?php
/**
 * Sidebar for Admin Pages
 *
 * @package     WP_Event_Aggregator
 * @subpackage  WP_Event_Aggregator/templates
 * @copyright   Copyright (c) 2016, Dharmesh Patel
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="upgrade_to_pro">
	<h2><?php esc_html_e( 'Upgrade to Pro', 'wp-event-aggregator' ); ?></h2>
	<p><?php esc_html_e( 'Unlock more powerful event import operations, and enable scheduled imports. Upgrade today!','wp-event-aggregator'); ?></p>
	<a class="button button-primary wpea-lh upgrade_button" href="<?php echo esc_url( WPEA_PLUGIN_BUY_NOW_URL ); ?>" target="_blank">
		<?php esc_html_e( 'Upgrade to Pro', 'wp-event-aggregator'); ?>
	</a>
</div>

<div class="upgrade_to_pro">
	<h2><?php esc_html_e( 'Custom WordPress Development Services','wp-event-aggregator'); ?></h2>
	<p><?php esc_html_e( "From small blogs to complex web apps, we push the limits of what is possible with WordPress.","wp-event-aggregator" ); ?></p>
	<a class="button button-primary wpea-lh upgrade_button" href="<?php echo esc_url('https://xylusthemes.com/contact/?utm_source=insideplugin&utm_medium=web&utm_content=sidebar&utm_campaign=freeplugin'); ?>" target="_blank">
		<?php esc_html_e( 'Hire Us','wp-event-aggregator'); ?>
	</a>
</div>

<div>
	<p style="text-align:center">
		<strong><?php esc_html_e( 'Would you like to remove these ads?','wp-event-aggregator'); ?></strong><br>
		<a href="<?php echo esc_url( WPEA_PLUGIN_BUY_NOW_URL ); ?>" target="_blank">
			<?php esc_html_e( 'Get Premium Add-on','wp-event-aggregator'); ?>
		</a>
	</p>
</div>