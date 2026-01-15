<?php

/**
 * Class ActionScheduler_wpPostStore_PostTypeRegistrar
 *
 * @codeCoverageIgnore
 */
class ActionScheduler_wpPostStore_PostTypeRegistrar {
	/**
	 * Registrar.
	 */
	public function register() {
		register_post_type( ActionScheduler_wpPostStore::POST_TYPE, $this->post_type_args() );
	}

	/**
	 * Build the args array for the post type definition
	 *
	 * @return array
	 */
	protected function post_type_args() {
		$args = array(
			'label'        => __( 'Scheduled Actions', 'wp-event-aggregator' ),
			'description'  => __( 'Scheduled actions are hooks triggered on a certain date and time.', 'wp-event-aggregator' ),
			'public'       => false,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'supports'     => array( 'title', 'editor', 'comments' ),
			'rewrite'      => false,
			'query_var'    => false,
			'can_export'   => true,
			'ep_mask'      => EP_NONE,
			'labels'       => array(
				'name'               => __( 'Scheduled Actions', 'wp-event-aggregator' ),
				'singular_name'      => __( 'Scheduled Action', 'wp-event-aggregator' ),
				'menu_name'          => _x( 'Scheduled Actions', 'Admin menu name', 'wp-event-aggregator' ),
				'add_new'            => __( 'Add', 'wp-event-aggregator' ),
				'add_new_item'       => __( 'Add New Scheduled Action', 'wp-event-aggregator' ),
				'edit'               => __( 'Edit', 'wp-event-aggregator' ),
				'edit_item'          => __( 'Edit Scheduled Action', 'wp-event-aggregator' ),
				'new_item'           => __( 'New Scheduled Action', 'wp-event-aggregator' ),
				'view'               => __( 'View Action', 'wp-event-aggregator' ),
				'view_item'          => __( 'View Action', 'wp-event-aggregator' ),
				'search_items'       => __( 'Search Scheduled Actions', 'wp-event-aggregator' ),
				'not_found'          => __( 'No actions found', 'wp-event-aggregator' ),
				'not_found_in_trash' => __( 'No actions found in trash', 'wp-event-aggregator' ),
			),
		);

		$args = apply_filters( 'action_scheduler_post_type_args', $args );
		return $args;
	}
}
