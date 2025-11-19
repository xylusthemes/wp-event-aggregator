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
			'label'        => __( 'Scheduled Actions', 'import-eventbrite-events' ),
			'description'  => __( 'Scheduled actions are hooks triggered on a certain date and time.', 'import-eventbrite-events' ),
			'public'       => false,
			'map_meta_cap' => true,
			'hierarchical' => false,
			'supports'     => array( 'title', 'editor', 'comments' ),
			'rewrite'      => false,
			'query_var'    => false,
			'can_export'   => true,
			'ep_mask'      => EP_NONE,
			'labels'       => array(
				'name'               => __( 'Scheduled Actions', 'import-eventbrite-events' ),
				'singular_name'      => __( 'Scheduled Action', 'import-eventbrite-events' ),
				'menu_name'          => _x( 'Scheduled Actions', 'Admin menu name', 'import-eventbrite-events' ),
				'add_new'            => __( 'Add', 'import-eventbrite-events' ),
				'add_new_item'       => __( 'Add New Scheduled Action', 'import-eventbrite-events' ),
				'edit'               => __( 'Edit', 'import-eventbrite-events' ),
				'edit_item'          => __( 'Edit Scheduled Action', 'import-eventbrite-events' ),
				'new_item'           => __( 'New Scheduled Action', 'import-eventbrite-events' ),
				'view'               => __( 'View Action', 'import-eventbrite-events' ),
				'view_item'          => __( 'View Action', 'import-eventbrite-events' ),
				'search_items'       => __( 'Search Scheduled Actions', 'import-eventbrite-events' ),
				'not_found'          => __( 'No actions found', 'import-eventbrite-events' ),
				'not_found_in_trash' => __( 'No actions found in trash', 'import-eventbrite-events' ),
			),
		);

		$args = apply_filters( 'action_scheduler_post_type_args', $args );
		return $args;
	}
}
