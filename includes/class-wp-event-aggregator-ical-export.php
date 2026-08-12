<?php
/**
 * iCal export functionality.
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Event_Aggregator_Ical_Export {

	/**
	 * Initialize hooks.
	 *
	 * @since 1.8.0
	 */
	public function __construct() {
		add_action( 'admin_post_wpea_export_ical', array( $this, 'handle_export_request' ) );
	}

	/**
	 * Get the static post type used for iCal exports.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_export_post_type() {
		return apply_filters( 'wpea_ical_export_post_type', 'wp_events' );
	}

	/**
	 * Get the static taxonomy used for iCal export category filtering.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public function get_export_taxonomy() {
		return apply_filters( 'wpea_ical_export_taxonomy', 'event_category' );
	}

	/**
	 * Handle iCal export request.
	 *
	 * @since 1.8.0
	 * @return void
	 */
	public function handle_export_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to export events.', 'wp-event-aggregator' ) );
		}

		check_admin_referer( 'wpea_export_ical_nonce_action', 'wpea_export_ical_nonce' );

		$args   = $this->sanitize_export_args( $_GET ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$events = $this->get_events( $args );
		$ics    = $this->build_calendar( $events, $args );

		nocache_headers();
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $this->get_export_filename( $args ) );
		header( 'X-Content-Type-Options: nosniff' );

		echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Sanitize export request args.
	 *
	 * @since 1.8.0
	 * @param array $raw_args Raw request args.
	 * @return array
	 */
	public function sanitize_export_args( $raw_args = array() ) {
		$date_filter       = isset( $raw_args['date_filter'] ) ? sanitize_key( wp_unslash( $raw_args['date_filter'] ) ) : 'upcoming';
		$post_status       = isset( $raw_args['post_status'] ) ? sanitize_key( wp_unslash( $raw_args['post_status'] ) ) : 'publish';
		$start_date        = isset( $raw_args['start_date'] ) ? sanitize_text_field( wp_unslash( $raw_args['start_date'] ) ) : '';
		$end_date          = isset( $raw_args['end_date'] ) ? sanitize_text_field( wp_unslash( $raw_args['end_date'] ) ) : '';
		$keyword           = isset( $raw_args['s'] ) ? sanitize_text_field( wp_unslash( $raw_args['s'] ) ) : '';
		$category          = isset( $raw_args['event_cat'] ) ? absint( $raw_args['event_cat'] ) : 0;
		$limit             = isset( $raw_args['limit'] ) ? absint( $raw_args['limit'] ) : 500;

		if ( ! in_array( $date_filter, array( 'upcoming', 'past', 'all', 'range' ), true ) ) {
			$date_filter = 'upcoming';
		}

		if ( ! in_array( $post_status, array( 'publish', 'future', 'draft', 'pending', 'private', 'any' ), true ) ) {
			$post_status = 'publish';
		}

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
			$start_date = '';
		}

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
			$end_date = '';
		}

		if ( $limit < 1 ) {
			$limit = 500;
		}

		$limit = min( $limit, 5000 );

		return array(
			'post_type'           => $this->get_export_post_type(),
			'taxonomy'            => $this->get_export_taxonomy(),
			'date_filter'         => $date_filter,
			'post_status'         => $post_status,
			'start_date'          => $start_date,
			'end_date'            => $end_date,
			's'                   => $keyword,
			'event_cat'           => $category,
			'limit'               => $limit,
			'include_description' => ! empty( $raw_args['include_description'] ),
			'include_location'    => ! empty( $raw_args['include_location'] ),
		);
	}

	/**
	 * Get events for export.
	 *
	 * @since 1.8.0
	 * @param array $args Export args.
	 * @return WP_Post[]
	 */
	public function get_events( $args = array() ) {
		$post_type = ! empty( $args['post_type'] ) ? $args['post_type'] : $this->get_export_post_type();
		if ( empty( $post_type ) || ! post_type_exists( $post_type ) ) {
			return array();
		}

		$query_args = array(
			'post_type'              => $post_type,
			'post_status'            => $args['post_status'],
			'posts_per_page'        => $args['limit'],
			'orderby'               => 'meta_value_num',
			'order'                 => 'ASC',
			'meta_key'              => 'start_ts', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_query'            => $this->get_date_meta_query($args), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'no_found_rows'         => true,
			'update_post_meta_cache'=> true,
			'update_post_term_cache'=> true,
			'suppress_filters'      => false,
		);

		if (!empty($args['s'])) {
			$query_args['s'] = $args['s'];
		}

		if (
			$args['event_cat'] > 0 &&
			!empty($args['taxonomy']) &&
			taxonomy_exists($args['taxonomy'])
		) {
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => $args['taxonomy'],
					'field'    => 'term_id',
					'terms'    => $args['event_cat'],
				),
			);
		}

		$query = new WP_Query( apply_filters( 'wpea_ical_export_query_args', $query_args, $args ) );

		return $query->posts;
	}

	/**
	 * Get date meta query.
	 *
	 * @since 1.8.0
	 * @param array $args Export args.
	 * @return array
	 */
	public function get_date_meta_query( $args = array() ) {
		$current_time = current_time( 'timestamp' );
		$meta_query   = array(
			array(
				'key'     => 'start_ts',
				'compare' => 'EXISTS',
			),
		);

		if ( 'upcoming' === $args['date_filter'] ) {
			$meta_query[] = array(
				'key'     => 'end_ts',
				'value'   => $current_time,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			);
		} elseif ( 'past' === $args['date_filter'] ) {
			$meta_query[] = array(
				'key'     => 'end_ts',
				'value'   => $current_time,
				'compare' => '<',
				'type'    => 'NUMERIC',
			);
		} elseif ( 'range' === $args['date_filter'] ) {
			if ( ! empty( $args['start_date'] ) ) {
				$meta_query[] = array(
					'key'     => 'end_ts',
					'value'   => strtotime( $args['start_date'] . ' 00:00:00' ),
					'compare' => '>=',
					'type'    => 'NUMERIC',
				);
			}

			if ( ! empty( $args['end_date'] ) ) {
				$meta_query[] = array(
					'key'     => 'start_ts',
					'value'   => strtotime( $args['end_date'] . ' 23:59:59' ),
					'compare' => '<=',
					'type'    => 'NUMERIC',
				);
			}
		}

		return $meta_query;
	}

	/**
	 * Build iCalendar file contents.
	 *
	 * @since 1.8.0
	 * @param WP_Post[] $events Events.
	 * @param array     $args Export args.
	 * @return string
	 */
	public function build_calendar( $events = array(), $args = array() ) {
		$lines = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//Xylus Themes//WP Event Aggregator Events//EN',
			'CALSCALE:GREGORIAN',
			'METHOD:PUBLISH',
			'X-WR-CALNAME:' . $this->escape_ical_text( get_bloginfo( 'name' ) . ' - WP Event Aggregator Events' ),
		);

		foreach ( $events as $event ) {
			$event_lines = $this->build_event( $event, $args );
			if ( ! empty( $event_lines ) ) {
				$lines = array_merge( $lines, $event_lines );
			}
		}

		$lines[] = 'END:VCALENDAR';

		return $this->fold_lines( implode( "\r\n", $lines ) . "\r\n" );
	}

	/**
	 * Build a VEVENT block.
	 *
	 * @since 1.8.0
	 * @param WP_Post $event Event.
	 * @param array   $args Export args.
	 * @return array
	 */
	public function build_event( $event, $args = array() ) {
		$start_ts = absint( get_post_meta( $event->ID, 'start_ts', true ) );
		$end_ts   = absint( get_post_meta( $event->ID, 'end_ts', true ) );

		if ( empty( $start_ts ) ) {
			return array();
		}

		if ( empty( $end_ts ) || $end_ts < $start_ts ) {
			$end_ts = $start_ts + HOUR_IN_SECONDS;
		}

		$eventbrite_id = get_post_meta( $event->ID, 'wpea_event_id', true );
		$uid_source    = ! empty( $eventbrite_id ) ? $eventbrite_id : $event->ID;
		$site_host     = wp_parse_url( home_url(), PHP_URL_HOST );
		$url           = get_post_meta( $event->ID, 'wpea_event_link', true );

		if ( empty( $url ) ) {
			$url = get_permalink( $event->ID );
		}

		$lines = array(
			'BEGIN:VEVENT',
			'UID:' . sanitize_key( $uid_source ) . '-' . absint( $event->ID ) . '@' . sanitize_key( $site_host ),
			'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
			'DTSTART:' . gmdate( 'Ymd\THis\Z', $start_ts ),
			'DTEND:' . gmdate( 'Ymd\THis\Z', $end_ts ),
			'SUMMARY:' . $this->escape_ical_text( get_the_title( $event ) ),
			'URL:' . esc_url_raw( $url ),
		);

		if ( ! empty( $args['include_description'] ) ) {
			$description = wp_strip_all_tags( $event->post_content );
			if ( ! empty( $description ) ) {
				$lines[] = 'DESCRIPTION:' . $this->escape_ical_text( $description );
			}
		}

		if ( ! empty( $args['include_location'] ) ) {
			$location = $this->get_event_location( $event->ID );
			if ( ! empty( $location ) ) {
				$lines[] = 'LOCATION:' . $this->escape_ical_text( $location );
			}
		}

		$lines[] = 'END:VEVENT';

		return apply_filters( 'wpea_ical_export_event_lines', $lines, $event, $args );
	}

	/**
	 * Get event location text.
	 *
	 * @since 1.8.0
	 * @param int $event_id Event ID.
	 * @return string
	 */
	public function get_event_location( $event_id = 0 ) {
		$location_parts = array();
		$meta_keys      = array( 'venue_name', 'venue_address', 'venue_city', 'venue_state', 'venue_country', 'venue_zipcode' );

		foreach ( $meta_keys as $meta_key ) {
			$value = trim( (string) get_post_meta( $event_id, $meta_key, true ) );
			if ( '' !== $value ) {
				$location_parts[] = $value;
			}
		}

		return implode( ', ', array_unique( $location_parts ) );
	}

	/**
	 * Escape iCalendar text values.
	 *
	 * @since 1.8.0
	 * @param string $text Text.
	 * @return string
	 */
	public function escape_ical_text( $text = '' ) {
		$text = wp_strip_all_tags( html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' ) );
		$text = str_replace( '\\', '\\\\', $text );
		$text = str_replace( array( ';', ',' ), array( '\;', '\,' ), $text );
		$text = preg_replace( "/\r\n|\r|\n/", '\n', $text );

		return $text;
	}

	/**
	 * Fold iCalendar lines to 75 octets.
	 *
	 * @since 1.8.0
	 * @param string $ics iCalendar string.
	 * @return string
	 */
	public function fold_lines( $ics = '' ) {
		$folded = array();
		$lines  = preg_split( "/\r\n|\r|\n/", $ics );

		foreach ( $lines as $line ) {
			while ( strlen( $line ) > 75 ) {
				$folded[] = substr( $line, 0, 75 );
				$line     = ' ' . substr( $line, 75 );
			}

			$folded[] = $line;
		}

		return implode( "\r\n", $folded );
	}

	/**
	 * Get export filename.
	 *
	 * @since 1.8.0
	 * @param array $args Export args.
	 * @return string
	 */
	public function get_export_filename( $args = array() ) {
		$post_type = ! empty( $args['post_type'] ) ? $args['post_type'] : $this->get_export_post_type();
		return sanitize_file_name( $post_type . '-' . gmdate( 'Y-m-d H-i-s' ) . '.ics' );
	}
}
