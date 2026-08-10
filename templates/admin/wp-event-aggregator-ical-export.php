<?php
/**
 * iCal export admin page.
 *
 * @package WP_Event_Aggregator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $importevents;

$wpea_export_post_type = $importevents->ical_export->get_export_post_type();
$wpea_export_taxonomy  = $importevents->ical_export->get_export_taxonomy();

?>

<div class="wpea-card wpea-ical-export-card" style="margin-top: 20px;">
	<form method="get" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wpea-ical-export-form">
		<input type="hidden" name="action" value="wpea_export_ical" />
		<?php wp_nonce_field( 'wpea_export_ical_nonce_action', 'wpea_export_ical_nonce' ); ?>

		<?php
		if ( empty( $wpea_export_post_type ) || ! post_type_exists( $wpea_export_post_type ) ) : ?>
			<h3><?php esc_html_e( 'The Wp Event Aggregator post type is not available for export.', 'wp-event-aggregator' ); ?></h3>
		<?php else : ?>
			<div class="wpea-inner-main-section">
				<div class="wpea-inner-section-1">
					<label for="wpea_ical_date_filter"><?php esc_html_e( 'Date filter', 'wp-event-aggregator' ); ?></label>
				</div>
				<div class="wpea-inner-section-2">
					<select id="wpea_ical_date_filter" name="date_filter" class="wpea-ical-control">
						<option value="upcoming"><?php esc_html_e( 'Upcoming only', 'wp-event-aggregator' ); ?></option>
						<option value="past"><?php esc_html_e( 'Past only', 'wp-event-aggregator' ); ?></option>
						<option value="all"><?php esc_html_e( 'All events', 'wp-event-aggregator' ); ?></option>
						<option value="range"><?php esc_html_e( 'Custom date range', 'wp-event-aggregator' ); ?></option>
					</select>
				</div>
			</div>

			<div class="wpea-inner-main-section date-range-section" style="display: none;">
				<div class="wpea-inner-section-1">
					<span><?php esc_html_e( 'Date range', 'wp-event-aggregator' ); ?></span>
				</div>
				<div class="wpea-inner-section-2">
					<div class="wpea-ical-inline-fields">
						<label for="wpea_ical_start_date"><?php esc_html_e( 'Start', 'wp-event-aggregator' ); ?></label>
						<input type="date" id="wpea_ical_start_date" name="start_date" value="" />

						<label for="wpea_ical_end_date"><?php esc_html_e( 'End', 'wp-event-aggregator' ); ?></label>
						<input type="date" id="wpea_ical_end_date" name="end_date" value="" />
					</div>
					<p class="description"><?php esc_html_e( 'Used only when Custom date range is selected.', 'wp-event-aggregator' ); ?></p>
				</div>
			</div>

			<div class="wpea-inner-main-section">
				<div class="wpea-inner-section-1">
					<label for="wpea_ical_post_status"><?php esc_html_e( 'Post status', 'wp-event-aggregator' ); ?></label>
				</div>
				<div class="wpea-inner-section-2">
					<select id="wpea_ical_post_status" name="post_status" class="wpea-ical-control">
						<option value="publish"><?php esc_html_e( 'Published', 'wp-event-aggregator' ); ?></option>
						<option value="future"><?php esc_html_e( 'Scheduled', 'wp-event-aggregator' ); ?></option>
						<option value="draft"><?php esc_html_e( 'Draft', 'wp-event-aggregator' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending', 'wp-event-aggregator' ); ?></option>
						<option value="private"><?php esc_html_e( 'Private', 'wp-event-aggregator' ); ?></option>
						<option value="any"><?php esc_html_e( 'Any status', 'wp-event-aggregator' ); ?></option>
					</select>
				</div>
			</div>

			<div class="wpea-inner-main-section">
				<div class="wpea-inner-section-1">
					<label for="wpea_ical_event_cat"><?php esc_html_e( 'Category', 'wp-event-aggregator' ); ?></label>
				</div>
				<div class="wpea-inner-section-2">
					<select id="wpea_ical_event_cat" name="event_cat" class="wpea-ical-control">
						<option value="0"><?php esc_html_e( 'Any category', 'wp-event-aggregator' ); ?></option>
						<?php
						$wpea_terms = array();
						if ( ! empty( $wpea_export_taxonomy ) && taxonomy_exists( $wpea_export_taxonomy ) ) {
							$wpea_terms = get_terms(
								array(
									'taxonomy'   => $wpea_export_taxonomy,
									'hide_empty' => false,
								)
							);
						}

						if ( ! is_wp_error( $wpea_terms ) && ! empty( $wpea_terms ) ) :
							foreach ( $wpea_terms as $term ) :
								?>
								<option value="<?php echo esc_attr( $term->term_id ); ?>">
									<?php echo esc_html( $term->name ); ?>
								</option>
								<?php
							endforeach;
						endif;
						?>
					</select>
				</div>
			</div>

			<div class="wpea-inner-main-section">
				<div class="wpea-inner-section-1">
					<label for="wpea_ical_search"><?php esc_html_e( 'Keyword', 'wp-event-aggregator' ); ?></label>
				</div>
				<div class="wpea-inner-section-2">
					<input type="search" id="wpea_ical_search" name="s" placeholder="<?php esc_attr_e( 'Search Keywords...', 'wp-event-aggregator' ); ?>" class="regular-text wpea-ical-control" value="" />
				</div>
			</div>

			<div class="wpea-inner-main-section">
				<div class="wpea-inner-section-1">
					<label for="wpea_ical_limit"><?php esc_html_e( 'Maximum events', 'wp-event-aggregator' ); ?></label>
				</div>
				<div class="wpea-inner-section-2">
					<input type="number" id="wpea_ical_limit" name="limit" min="1" max="5000" value="500" class="wpea-ical-number" />
				</div>
			</div>

			<div class="wpea-inner-main-section">
				<div class="wpea-inner-section-1">
					<span><?php esc_html_e( 'iCal fields', 'wp-event-aggregator' ); ?></span>
				</div>
				<div class="wpea-inner-section-2">
					<div class="wpea-ical-checkboxes">
						<label>
							<input type="checkbox" name="include_description" value="1" checked="checked" />
							<?php esc_html_e( 'Include description', 'wp-event-aggregator' ); ?>
						</label>
						<label>
							<input type="checkbox" name="include_location" value="1" checked="checked" />
							<?php esc_html_e( 'Include location', 'wp-event-aggregator' ); ?>
						</label>
					</div>
				</div>
			</div>

			<div class="wpea-ical-actions">
				<?php submit_button( __( 'Export iCal File', 'wp-event-aggregator' ), 'primary', 'submit', false ); ?>
			</div>
		<?php endif; ?>
	</form>
</div>