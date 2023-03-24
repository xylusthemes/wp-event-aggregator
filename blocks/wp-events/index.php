<?php
/**
 * Facebook Events Block Initializer
 *
 * @since   1.6
 * @package    Import_Facebook_Events
 * @subpackage Import_Facebook_Events/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Gutenberg Block
 *
 * @return void
 */
function wpea_register_gutenberg_block() {
	global $importevents;
	if ( function_exists( 'register_block_type' ) ) {
		// Register block editor script.
		$js_dir = WPEA_PLUGIN_URL . 'assets/js/blocks/';
		wp_register_script(
			'wpea-wp-events-block',
			$js_dir . 'gutenberg.blocks.js',
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' ),
			WPEA_VERSION
		);

		// Register block editor style.
		$css_dir = WPEA_PLUGIN_URL . 'assets/css/';
		wp_register_style(
			'wpea-wp-events-block-style',
			$css_dir . 'wp-event-aggregator.css',
			array(),
			WPEA_VERSION
		);

		wp_register_style(
			'wpea-wp-events-block-style2',
			$css_dir . 'grid-style2.css',
			array(),
			WPEA_VERSION
		);

		// Register our block.
		register_block_type( 'wpea-block/wp-events', array(
			'attributes'      => array(
				'col'            => array(
					'type'    => 'number',
					'default' => 2,
				),
				'posts_per_page' => array(
					'type'    => 'number',
					'default' => 12,
				),
				'past_events'    => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'start_date'     => array(
					'type'    => 'string',
					'default' => '',
				),
				'end_date'       => array(
					'type'    => 'string',
					'default' => '',
				),
				'order'          => array(
					'type'    => 'string',
					'default' => 'ASC',
				),
				'orderby'        => array(
					'type'    => 'string',
					'default' => 'event_start_date',
				),
				'layout'        => array(
					'type'    => 'string',
					'default' => '',
				),

			),
			'editor_script'   => 'wpea-wp-events-block', // The script name we gave in the wp_register_script() call.
			'editor_style'    => 'wpea-wp-events-block-style', // The script name we gave in the wp_register_style() call.
			'style'           => 'wpea-wp-events-block-style2',
			'render_callback' => array( $importevents->cpt, 'wp_events_archive' ),
		) );
	}
}

add_action( 'init', 'wpea_register_gutenberg_block' );
