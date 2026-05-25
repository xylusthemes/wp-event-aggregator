<?php
/**
 * Feed upgrade page template.
 *
 * @package WP_Event_Aggregator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pro_url = WPEA_PLUGIN_BUY_NOW_URL;
?>
<style>
	.wpea-upgrade-wrap { max-width: 900px; margin: 40px auto; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
	.wpea-upgrade-hero { background: linear-gradient(135deg, #f06342 0%, #e84f2a 50%, #3d64f4 100%); border-radius: 12px; padding: 48px 40px; color: #fff; text-align: center; position: relative; overflow: hidden; margin-bottom: 32px; }
	.wpea-upgrade-hero::before { content:''; position:absolute; top:-60px; right:-60px; width:220px; height:220px; background:rgba(255,255,255,0.07); border-radius:50%; }
	.wpea-upgrade-hero::after { content:''; position:absolute; bottom:-40px; left:-40px; width:160px; height:160px; background:rgba(255,255,255,0.05); border-radius:50%; }
	.wpea-upgrade-hero h1 { font-size: 32px; font-weight: 800; margin: 0 0 12px; position:relative; z-index:1; }
	.wpea-upgrade-hero p { font-size: 16px; opacity: 0.92; margin: 0 0 28px; position:relative; z-index:1; max-width: 560px; margin-left:auto; margin-right:auto; margin-bottom:28px;}
	.wpea-upgrade-hero-btn { display: inline-flex; align-items: center; gap: 8px; background: #fff; color: #f06342; font-size: 15px; font-weight: 700; padding: 14px 32px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 16px rgba(0,0,0,0.2); position:relative; z-index:1; transition: transform 0.2s; }
	.wpea-upgrade-hero-btn:hover { transform: translateY(-2px); color: #e8411a; }
	.wpea-pro-badge-large { display:inline-block; background:#4CAF50; color:#fff; font-size:11px; font-weight:700; padding:3px 10px; border-radius:20px; letter-spacing:1px; text-transform:uppercase; margin-bottom:16px; }

	.wpea-features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
	.wpea-feature-card { background: #fff; border: 1px solid #e8e8e8; border-radius: 10px; padding: 24px 20px; text-align: center; transition: box-shadow 0.2s; }
	.wpea-feature-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
	.wpea-feature-icon { font-size: 32px; margin-bottom: 12px; display:block; }
	.wpea-feature-card h3 { font-size: 14px; font-weight: 700; color: #1d2327; margin: 0 0 8px; }
	.wpea-feature-card p { font-size: 12px; color: #666; margin: 0; line-height: 1.6; }

	.wpea-compare-table { background:#fff; border:1px solid #e8e8e8; border-radius:10px; overflow:hidden; margin-bottom:32px; }
	.wpea-compare-table table { width:100%; border-collapse:collapse; }
	.wpea-compare-table th { padding:14px 20px; font-size:13px; font-weight:700; text-align:center; }
	.wpea-compare-table th:first-child { text-align:left; background:#f8f9fa; }
	.wpea-compare-table th.free-col { background:#f8f9fa; color:#888; }
	.wpea-compare-table th.pro-col { background: linear-gradient(135deg, #f06342, #3d64f4); color:#fff; }
	.wpea-compare-table td { padding:11px 20px; font-size:13px; border-top:1px solid #f0f0f0; text-align:center; }
	.wpea-compare-table td:first-child { text-align:left; color:#444; font-weight:500; }
	.wpea-compare-table tr:hover td { background:#fafafa; }
	.wpea-check { color:#4CAF50; font-size:16px; font-weight:700; }
	.wpea-cross { color:#ccc; font-size:16px; }

	.wpea-bottom-cta { background:#f8f9fa; border:1px solid #e8e8e8; border-radius:10px; padding:32px; text-align:center; }
	.wpea-bottom-cta h3 { font-size:20px; font-weight:700; color:#1d2327; margin:0 0 8px; }
	.wpea-bottom-cta p { font-size:13px; color:#666; margin:0 0 20px; }

	@media (max-width: 782px) { .wpea-features-grid { grid-template-columns: 1fr 1fr; } }
</style>

<div class="wpea-upgrade-wrap">

	<div class="wpea-upgrade-hero">
		<span class="wpea-pro-badge-large"><?php esc_html_e( 'PRO Feature', 'wp-event-aggregator' ); ?></span>
		<h1 style="color:#fff;"><?php esc_html_e( 'WP Event Aggregator Widget', 'wp-event-aggregator' ); ?></h1>
		<p style="color:#ddd;"><?php esc_html_e( 'Display Eventbrite and Meetup events directly on your website no import, no authorization, no API token needed. Just paste a shortcode and go live!', 'wp-event-aggregator' ); ?></p>
		<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank" class="wpea-upgrade-hero-btn">
			✦ <?php esc_html_e( 'Upgrade to PRO', 'wp-event-aggregator' ); ?>
		</a>
	</div>

	<div class="wpea-features-grid">
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🚀</span>
			<h3><?php esc_html_e( 'No Import Needed', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Show live events directly from Eventbrite or Meetup, no manual importing, no syncing required.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🔑</span>
			<h3><?php esc_html_e( 'No Auth & No Token', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'No API key, no OAuth setup. Use Eventbrite organizer, collection, event IDs, or Meetup group and event IDs.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🔄</span>
			<h3><?php esc_html_e( 'Always Up-to-Date', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Events auto-refresh via smart caching. Your visitors always see fresh event data.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🎨</span>
			<h3><?php esc_html_e( '7 Layout Styles', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Card Grid, List, Masonry, Timeline, Ticket, Minimal Grid, Compact List — pick what fits your site.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🎟️</span>
			<h3><?php esc_html_e( 'Ticket & RSVP Buttons', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Show Eventbrite tickets with popup modal or link, and Meetup RSVP links with customizable labels.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">⚡</span>
			<h3><?php esc_html_e( 'Shortcode Builder', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Visual builder generates your shortcode instantly. Paste it anywhere — pages, posts, widgets.', 'wp-event-aggregator' ); ?></p>
		</div>
	</div>

	<div class="wpea-compare-table">
		<table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Feature', 'wp-event-aggregator' ); ?></th>
					<th class="free-col"><?php esc_html_e( 'Free', 'wp-event-aggregator' ); ?></th>
					<th class="pro-col"><?php esc_html_e( 'PRO', 'wp-event-aggregator' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><?php esc_html_e( 'Display Eventbrite and Meetup events via Live Feed (no import)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Eventbrite feed by Organizer ID', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Eventbrite feed by Collection ID', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Eventbrite feed by Specific Event IDs', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Meetup feed by Group URL or group slug', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Meetup feed by Specific Event IDs', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( '7 Display Layouts (Grid, List, Masonry & more)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Shortcode Builder', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Filter by Date & Time', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Ticket and RSVP Buttons (Eventbrite modal/link, Meetup link)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Smart Cache + Auto Refresh', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Pagination (Load More / Infinite Scroll)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Custom CSS per Feed', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="wpea-bottom-cta">
		<h3><?php esc_html_e( 'Ready to go live with Wp Event Aggregator Widget?', 'wp-event-aggregator' ); ?></h3>
		<p><?php esc_html_e( 'Upgrade to PRO and start displaying Eventbrite and Meetup events on your website in minutes, no technical setup needed.', 'wp-event-aggregator' ); ?></p>
		<a href="<?php echo esc_url( $pro_url ); ?>" target="_blank"
			style="display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#f06342,#3d64f4); color:#fff; font-size:14px; font-weight:700; padding:13px 30px; border-radius:8px; text-decoration:none; box-shadow:0 4px 16px rgba(240,99,66,0.35);">
			✦ <?php esc_html_e( 'Get PRO Now', 'wp-event-aggregator' ); ?>
		</a>
	</div>

</div>
