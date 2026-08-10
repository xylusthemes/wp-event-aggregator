<?php
/**
 * Feed upgrade page template.
 *
 * @package WP_Event_Aggregator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wpea_pro_url = WPEA_PLUGIN_BUY_NOW_URL;
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
	.wpea-compare-table td.wpea-section-row { background:#f8f9fa; font-weight:700; color:#1d2327; text-transform:uppercase; font-size:11px; letter-spacing:0.5px; }
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
		<p style="color:#ddd;"><?php esc_html_e( 'Display Eventbrite, Meetup and Facebook events directly on your website, import from multiple pages, groups, organizers and collections at once. Just paste a shortcode and go live!', 'wp-event-aggregator' ); ?></p>
		<a href="<?php echo esc_url( $wpea_pro_url ); ?>" target="_blank" class="wpea-upgrade-hero-btn">
			✦ <?php esc_html_e( 'Upgrade to PRO', 'wp-event-aggregator' ); ?>
		</a>
	</div>

	<div class="wpea-features-grid">
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🚀</span>
			<h3><?php esc_html_e( 'No Import Needed', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Show live events directly from Eventbrite, Meetup or Facebook, no manual importing, no syncing required.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🔑</span>
			<h3><?php esc_html_e( 'No Auth & No Token', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'No API key, no OAuth setup. Use Eventbrite organizer, collection, event IDs, Meetup group and event IDs, or Facebook page, group and event IDs.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🔄</span>
			<h3><?php esc_html_e( 'Always Up-to-Date', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Events auto-refresh via smart caching with 1/6/12/24 hour or custom presets, plus background auto-refresh and one-click manual cache clear.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">📥</span>
			<h3><?php esc_html_e( 'Multiple Sources at Once', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Import from multiple Eventbrite Organizer/Collection/Event IDs, Meetup Group URLs, or Facebook Pages/Groups/Events/iCal feeds in a single go, one per line or comma separated.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">📘</span>
			<h3><?php esc_html_e( 'Facebook Page, Group & iCal Import', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Pull events from Facebook Pages, Groups, specific Event IDs, or a public Facebook iCal feed URL.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🎨</span>
			<h3><?php esc_html_e( '7 Layout Styles', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Card Grid, List, Masonry, Timeline, Ticket List, Minimal Grid, Compact List — pick what fits your site, in 1 to 4 columns.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🎟️</span>
			<h3><?php esc_html_e( 'Ticket & RSVP Buttons', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Show Eventbrite tickets with popup modal or link, and Meetup / Facebook links, with fully custom button labels.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">⚡</span>
			<h3><?php esc_html_e( '6-Step Shortcode Builder', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Platform → Source → Display → Tickets → Filters → Settings, with a live preview sidebar. Your shortcode is generated automatically.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🧩</span>
			<h3><?php esc_html_e( 'Toggle Any Field', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Show or hide the image, date & time, venue, organizer name, price badge, and ticket button independently for each feed.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">📄</span>
			<h3><?php esc_html_e( '4 Pagination Modes', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Numbered AJAX pagination, Load More button, Infinite Scroll, or show all events with no pagination at all.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🗓️</span>
			<h3><?php esc_html_e( '8 Smart Date Filters', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Today, Upcoming Week, Upcoming 15 Days, Upcoming Month, All Upcoming, Past Events (Eventbrite), Custom Date Range, or no filter at all.', 'wp-event-aggregator' ); ?></p>
		</div>
		<div class="wpea-feature-card">
			<span class="wpea-feature-icon">🏷️</span>
			<h3><?php esc_html_e( 'Custom Labels & CSS', 'wp-event-aggregator' ); ?></h3>
			<p><?php esc_html_e( 'Rename the Buy, Register, Free and Sold Out labels, plus add your own custom CSS per feed for pixel-perfect styling.', 'wp-event-aggregator' ); ?></p>
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
					<td><?php esc_html_e( 'Display Eventbrite, Meetup and Facebook events via Live Feed (no import)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>

				<tr>
					<td class="wpea-section-row" colspan="3"><?php esc_html_e( 'Eventbrite', 'wp-event-aggregator' ); ?></td>
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
					<td><?php esc_html_e( 'Multiple Organizer / Collection / Event IDs at once (one per line or comma separated)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Eventbrite ticket button as Popup Modal or Link to Eventbrite page', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Show or hide Sold Out Eventbrite events', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Past Events time filter (Eventbrite)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>

				<tr>
					<td class="wpea-section-row" colspan="3"><?php esc_html_e( 'Meetup', 'wp-event-aggregator' ); ?></td>
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
					<td><?php esc_html_e( 'Multiple Group URLs / Event IDs at once (one per line or comma separated)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Direct RSVP link to meetup.com', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>

				<tr>
					<td class="wpea-section-row" colspan="3"><?php esc_html_e( 'Facebook', 'wp-event-aggregator' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Facebook feed by Page ID / Username', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Facebook feed by Group ID / URL', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Facebook feed by Specific Event IDs', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Facebook feed by public iCal URL', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Multiple Page / Group IDs, Event IDs & iCal URLs at once (one per line or comma separated)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Direct link to facebook.com/events', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>

				<tr>
					<td class="wpea-section-row" colspan="3"><?php esc_html_e( 'Layout & Display', 'wp-event-aggregator' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( '7 Display Layouts (Card Grid, List, Masonry, Timeline, Ticket List, Minimal Grid, Compact List)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Choose 1 to 4 columns on desktop (auto single column on mobile)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Events per page: 6 / 9 / 10 / 12 / 20 / 30 / 40 / 50', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( '4 Pagination modes: Numbered AJAX, Load More, Infinite Scroll, or None (show all)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Toggle visible fields: Image, Date & Time, Venue/Location, Organizer, Price/Free badge, Ticket/RSVP button', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Ticket and RSVP Buttons (Eventbrite modal/link, Meetup and Facebook link-out)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>

				<tr>
					<td class="wpea-section-row" colspan="3"><?php esc_html_e( 'Filtering', 'wp-event-aggregator' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( '8 Time filter presets: Today, Upcoming Week, Upcoming 15 Days, Upcoming Month, All Upcoming, Custom Range, No Filter (+ Past Events for Eventbrite)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Custom Start / End date range picker', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Show/hide Sold Out events (Eventbrite)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Hide online-only events', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>

				<tr>
					<td class="wpea-section-row" colspan="3"><?php esc_html_e( 'Customization & Labels', 'wp-event-aggregator' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Custom "Get Tickets / Buy" button label', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Custom "Register / Attend" button label', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Custom "Free" badge label', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Custom "Sold Out" label (Eventbrite)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Custom CSS per feed', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>

				<tr>
					<td class="wpea-section-row" colspan="3"><?php esc_html_e( 'Performance & Caching', 'wp-event-aggregator' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Cache duration presets: 1 / 6 / 12 / 24 hours, or custom minutes', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Automatic background cache refresh (Action Scheduler)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'One-click manual cache clear with live cache status', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>

				<tr>
					<td class="wpea-section-row" colspan="3"><?php esc_html_e( 'Builder Experience', 'wp-event-aggregator' ); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e( '6-Step guided Feed Builder (Platform, Source, Display, Tickets, Filters, Settings)', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Live preview sidebar with full-screen preview toggle', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
				<tr>
					<td><?php esc_html_e( 'Auto-generated shortcode with one-click copy', 'wp-event-aggregator' ); ?></td>
					<td><span class="wpea-cross">✕</span></td>
					<td><span class="wpea-check">✔</span></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="wpea-bottom-cta">
		<h3><?php esc_html_e( 'Ready to go live with Wp Event Aggregator Widget?', 'wp-event-aggregator' ); ?></h3>
		<p><?php esc_html_e( 'Upgrade to PRO and start displaying Eventbrite, Meetup and Facebook events on your website in minutes, no technical setup needed.', 'wp-event-aggregator' ); ?></p>
		<a href="<?php echo esc_url( $wpea_pro_url ); ?>" target="_blank"
			style="display:inline-flex; align-items:center; gap:8px; background:linear-gradient(135deg,#f06342,#3d64f4); color:#fff; font-size:14px; font-weight:700; padding:13px 30px; border-radius:8px; text-decoration:none; box-shadow:0 4px 16px rgba(240,99,66,0.35);">
			✦ <?php esc_html_e( 'Get PRO Now', 'wp-event-aggregator' ); ?>
		</a>
	</div>

</div>
