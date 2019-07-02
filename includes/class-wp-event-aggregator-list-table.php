<?php
/**
 *  List table for scheduled import.
 *
 * @link       http://xylusthemes.com/
 * @since      1.0.0
 *
 * @package    WP_Event_Aggregator
 * @subpackage WP_Event_Aggregator/includes
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class respoinsible for generate list table for scheduled import.
 */
class WP_Event_Aggregator_List_Table extends WP_List_Table {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $status, $page;
	        // Set parent defaults.
	        parent::__construct( array(
	            'singular'  => 'xt_scheduled_import',     // singular name of the listed records.
	            'plural'    => 'xt_scheduled_imports',    // plural name of the listed records.
	            'ajax'      => false,        // does this table support ajax?
	        ) );
	}

	/**
	 * Setup output for default column.
	 *
	 * @since    1.0.0
	 * @param array  $item Items.
	 * @param string $column_name  Column name.
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Setup output for title column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	function column_title( $item ) {
		global $importevents;
		$wpea_url_delete_args = array(
			'page'   => wp_unslash( $_REQUEST['page'] ),
			'wpea_action' => 'wpea_simport_delete',
			'import_id'  => absint( $item['ID'] ),
		);

		$page = 'import_events';
		$tab = 'scheduled';
		$wp_redirect = admin_url( 'admin.php?page='.$page );
		$wpea_url_edit_args = array(
			'tab'    =>  wp_unslash( $tab ),
			'edit'  => absint( $item['ID'] ),
		);

		// Build row actions.
		$actions = array(
			'edit' => sprintf( '<a href="%1$s">%2$s</a>',esc_url( add_query_arg( $wpea_url_edit_args, $wp_redirect ) ), esc_html__( 'Edit', 'wp-event-aggregator' ) ),
		    'delete' => sprintf( '<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure to Delete this scheduled import? Scheduled import will be permanatly deleted.\')">%2$s</a>',esc_url( wp_nonce_url( add_query_arg( $wpea_url_delete_args ), 'wpea_delete_import_nonce' ) ), esc_html__( 'Delete', 'wp-event-aggregator' ) ),
		);
		
		$import_into = '-';
		$active_plugins = $importevents->common->get_active_supported_event_plugins();
		if( isset( $active_plugins[$item["import_into"]] ) ){
			$import_into = $active_plugins[$item["import_into"]];
		}

		return sprintf( '<strong>%1$s</strong>
			<span>%2$s</span></br>
			<span>%3$s</span></br>
			<span style="color:silver">(id:%4$s)</span>%5$s',
			$item['title'],
			__('Origin', 'import-eventbrite-events') . ': <b>' . ucfirst( $item["import_origin"] ) . '</b>',
			__('Import Into', 'import-eventbrite-events') . ': <b>' . $import_into . '</b>',
			$item['ID'],
			$this->row_actions( $actions )
		);

		// Return the title contents.
		return sprintf('<strong>%1$s</strong><span>%4$s</span> <span style="display:block;">%5$s</span> <span style="color:silver">(id:%2$s)</span>%3$s',
		    $item['title'],
		    $item['ID'],
		    $this->row_actions( $actions ),
		    __('Origin', 'wp-event-aggregator') . ': <b>' . ucfirst( $item["import_origin"] ) . '</b>',
		    $import_into
		);
	}

	/**
	 * Setup output for Action column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	function column_action( $item ) {

		$xtmi_run_import_args = array(
			'page'   => wp_unslash( $_REQUEST['page'] ),
			'wpea_action' => 'wpea_run_import',
			'import_id'  => $item['ID'],
		);

		$current_import = '';
		if(isset($item['current_import'])){
			$cimport = '<strong>'.esc_html__( 'Import is running in Background', 'import-eventbrite-events' ).'</strong>';
			if(!empty($item['current_import'])){
				$stats = array();
				if( $item['current_import']['created'] > 0 ){
					$stats[] = sprintf( __( '%d Created', 'import-eventbrite-events' ), $item['current_import']['created']);
				}
				if( $item['current_import']['updated'] > 0 ){
					$stats[] = sprintf( __( '%d Updated', 'import-eventbrite-events' ), $item['current_import']['updated'] );
				}
				if( $item['current_import']['skipped'] > 0 ){
					$stats[] = sprintf( __( '%d Skipped', 'import-eventbrite-events' ), $item['current_import']['skipped'] );
				}
				if( !empty( $stats ) ){
					$stats = esc_html__( 'Stats: ', 'import-eventbrite-events' ).'<span style="color: silver">'.implode(', ', $stats).'</span>';
					$cimport .= '<br/>'.$stats;
				}
			}
			$current_import = '<div class="wpea_inprogress_import">'.$cimport.'</div>';
		}

		$total_import = '';
		if( $item['total_import'] > 0 ){
			$total_import = "<strong>".esc_html__( 'Total imported Events:', 'wp-event-aggregator' )."</strong> ".$item['total_import'];	
		}
		// Return the title contents.
		return sprintf( '<a class="button-primary" href="%1$s">%2$s</a><br/>%3$s<br/>%4$s<br/>%5$s<br/><br/>%6$s',
			esc_url( wp_nonce_url( add_query_arg( $xtmi_run_import_args ), 'wpea_run_import_nonce' ) ),
			esc_html__( 'Import Now', 'wp-event-aggregator' ),
			$item['last_import'],
			$item['stats'],
			$total_import,
			$current_import
		);
	}

	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("video")
            /*$2%s*/ $item['ID']             //The value of the checkbox should be the record's id
        );
    }

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */
	function get_columns() {
		$columns = array(
		 'cb'    => '<input type="checkbox" />',
		 'title'     => __( 'Scheduled import', 'wp-event-aggregator' ),
		 'import_status'   => __( 'Import Event Status', 'wp-event-aggregator' ),
		 'import_category'   => __( 'Import Category', 'wp-event-aggregator' ),
		 'import_frequency'   => __( 'Import Frequency', 'wp-event-aggregator' ),
		 'action'   => __( 'Action', 'wp-event-aggregator' ),
		);
		return $columns;
	}

	public function get_bulk_actions() {

        return array(
            'delete' => __( 'Delete', 'wp-event-aggregator' ),
        );

    }

	/**
	 * Prepare Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function prepare_items( $origin = '' ) {
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array( 'ID' );
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();
		
		if( $origin != '' ){
			$data = $this->get_scheduled_import_data( $origin );	
		}else{
			$data = $this->get_scheduled_import_data();
		}
		
		if ( ! empty( $data ) ) {
			$total_items = ( $data['total_records'] )? (int) $data['total_records'] : 0;
			// Set data to items.
			$this->items = ( $data['import_data'] )? $data['import_data'] : array();

			$this->set_pagination_args( array(
			    'total_items' => $total_items,  // WE have to calculate the total number of items.
			    'per_page'    => $per_page, // WE have to determine how many items to show on a page.
			    'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
			) );
		}
	}

	/**
	 * Get Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function get_scheduled_import_data( $origin = '' ) {
		global $importevents, $wpdb;

		// Check Running Imports.
		$current_imports = array();
		$batches = wpea_get_inprogress_import();
		if(!empty($batches)){
			foreach ($batches as $batch) {
				if ( is_multisite() ) {
					$batch = isset( $batch->meta_value ) ? maybe_unserialize( $batch->meta_value ) : array();
				}else{
				    $batch = isset( $batch->option_value ) ? maybe_unserialize( $batch->option_value ) : array();
				}
				if( !empty( $batch ) && is_array( $batch ) ){
					$batch = current( $batch );
					$import_data = isset( $batch['imported_events'] ) ? $batch['imported_events'] : array(); 
					$import_status = array(
						'created' => 0,
						'updated' => 0,
						'skipped' => 0
					);
					foreach ( $import_data as $key => $value ) {
						if ( $value['status'] == 'created' ) {
							$import_status['created'] += 1;
						} elseif ( $value['status'] == 'updated' ) {
							$import_status['updated'] += 1;
						} elseif ( $value['status'] == 'skipped' ) {
							$import_status['skipped'] += 1;
						}
					}	
					$current_imports[$batch['import_id']] = $import_status;
				}
			}
		}

		$scheduled_import_data = array( 'total_records' => 0, 'import_data' => array() );
		$per_page = 10;
		$current_page = $this->get_pagenum();

		$query_args = array(
			'post_type' => 'xt_scheduled_imports',
			'posts_per_page' => $per_page,
			'paged' => $current_page,
		);

		if( isset( $_REQUEST['s'] ) ){
			$query_args['s'] = sanitize_text_field($_REQUEST['s']);
		}

		if( $origin != '' ){
			$query_args['meta_key'] = 'import_origin';
			$query_args['meta_value'] = esc_attr( $origin );
		}
		$importdata_query = new WP_Query( $query_args );
		$scheduled_import_data['total_records'] = ( $importdata_query->found_posts ) ? (int) $importdata_query->found_posts : 0;
		// The Loop.
		if ( $importdata_query->have_posts() ) {
			while ( $importdata_query->have_posts() ) {
				$importdata_query->the_post();
				
				$import_id = get_the_ID();
				$import_title = get_the_title();
				$import_data = get_post_meta( $import_id, 'import_eventdata', true );
				$import_origin = get_post_meta( $import_id, 'import_origin', true );
				$import_plugin = isset( $import_data['import_into'] ) ? $import_data['import_into'] : '';
				$import_status = isset( $import_data['event_status'] ) ? $import_data['event_status'] : '';
				
				$term_names = array();
				$import_terms = isset( $import_data['event_cats'] ) ? $import_data['event_cats'] : array(); 
				
				if ( $import_terms && ! empty( $import_terms ) ) {
					foreach ( $import_terms as $term ) {
						$get_term = '';

						if( $import_plugin != '' && !empty( $importevents->$import_plugin ) ){
							$get_term = get_term( $term, $importevents->$import_plugin->get_taxonomy() );
						}

						if( !is_wp_error( $get_term ) && !empty( $get_term ) ){
							$term_names[] = $get_term->name;
						}
					}
				}	

				$stats = $last_import_history_date = '';
				$history_args = array(
					'post_type'      => 'wpea_import_history',
					'post_status'    => 'publish',
					'numberposts'    => 1,
					'meta_key'       => 'schedule_import_id',
					'meta_value'     => $import_id,
					'fields'         => 'ids'
				);

				$history = get_posts( $history_args );

				if( !empty( $history ) ){
					$last_import_history_date = sprintf( __( 'Last Import: %s ago', 'import-eventbrite-events' ), human_time_diff( get_the_date( 'U', $history[0] ), current_time( 'timestamp' ) ) );
					$created = get_post_meta( $history[0], 'created', true );
					$updated = get_post_meta( $history[0], 'updated', true );
					$skipped = get_post_meta( $history[0], 'skipped', true );
					$stats = array();
					if( $created > 0 ){
						$stats[] = sprintf( __( '%d Created', 'import-eventbrite-events' ), $created );
					}
					if( $updated > 0 ){
						$stats[] = sprintf( __( '%d Updated', 'import-eventbrite-events' ), $updated );
					}
					if( $skipped > 0 ){
						$stats[] = sprintf( __( '%d Skipped', 'import-eventbrite-events' ), $skipped );
					}
					if( !empty( $stats ) ){
						$stats = esc_html__( 'Last Import Stats: ', 'import-eventbrite-events' ).'<span style="color: silver">'.implode(', ', $stats).'</span>';
					}else{
						$nothing_to_import = get_post_meta( $history[0], 'nothing_to_import', true );
						if( $nothing_to_import ){
							$stats = '<span style="color: silver">'.__( 'No events are imported.', 'import-eventbrite-events' ).'</span>';	
						}else{
							$stats = '';
						}
					}
				}
	
				$totalimport_query = $wpdb->prepare( "SELECT SUM( meta_value) AS created_total FROM ".$wpdb->postmeta." WHERE post_id IN ( SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key = 'schedule_import_id' AND meta_value = %d ) AND meta_key = 'created'", $import_id );

				$totalimport = $wpdb->get_var( $totalimport_query );

				$scheduled_import = array(
					'ID' => $import_id,
					'title' => $import_title,
					'import_status'   => ucfirst( $import_status ),
					'import_category' => implode( ', ', $term_names ),
					'import_frequency'=> isset( $import_data['import_frequency'] ) ? ucfirst( $import_data['import_frequency'] ) : '',
					'import_origin'   => $import_origin,
					'import_into'     => $import_plugin,
					'last_import'     => $last_import_history_date,
					'stats'			  => $stats,
					'total_import'	  => $totalimport
				);

				if( isset( $current_imports[$import_id] ) ){
					$scheduled_import['current_import'] = $current_imports[$import_id];
				}
				$scheduled_import_data['import_data'][] = $scheduled_import;
			}
		}
		// Restore original Post Data.
		wp_reset_postdata();
		return $scheduled_import_data;
	}
}

/**
 * Class respoinsible for generate list table for scheduled import.
 */
class WP_Event_Aggregator_History_List_Table extends WP_List_Table {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		global $status, $page;
	        // Set parent defaults.
	        parent::__construct( array(
	            'singular'  => 'import_history',     // singular name of the listed records.
	            'plural'    => 'import_histories',   // plural name of the listed records.
	            'ajax'      => false,        // does this table support ajax?
	        ) );
	}

	/**
	 * Setup output for default column.
	 *
	 * @since    1.0.0
	 * @param array  $item Items.
	 * @param string $column_name  Column name.
	 * @return string
	 */
	function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Setup output for title column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	function column_title( $item ) {

		$wpea_url_delete_args = array(
			'page'   => wp_unslash( $_REQUEST['page'] ),
			'tab'   => wp_unslash( $_REQUEST['tab'] ),
			'wpea_action' => 'wpea_history_delete',
			'history_id'  => absint( $item['ID'] ),
		);
		// Build row actions.
		$actions = array(
		    'delete' => sprintf( '<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure to Delete this import history? Import history will be permanatly deleted.\')">%2$s</a>',esc_url( wp_nonce_url( add_query_arg( $wpea_url_delete_args ), 'wpea_delete_history_nonce' ) ), esc_html__( 'Delete', 'wp-event-aggregator' ) ),
		);

		// Return the title contents.
		return sprintf('<strong>%1$s</strong><span>%3$s</span> %2$s',
		    $item['title'],
		    $this->row_actions( $actions ),
		    __('Origin', 'wp-event-aggregator') . ': <b>' . ucfirst( get_post_meta( $item['ID'], 'import_origin', true ) ) . '</b>'
		);
	}

	/**
	 * Setup output for stats column.
	 *
	 * @since    1.0.0
	 * @param array $item Items.
	 * @return array
	 */
	function column_stats( $item ) {

		$created = get_post_meta( $item['ID'], 'created', true );
		$updated = get_post_meta( $item['ID'], 'updated', true );
		$skipped = get_post_meta( $item['ID'], 'skipped', true );
		$nothing_to_import = get_post_meta( $item['ID'], 'nothing_to_import', true );

		$success_message = '<span style="color: silver"><strong>';
		if( $created > 0 ){
			$success_message .= sprintf( __( '%d Created', 'wp-event-aggregator' ), $created )."<br>";
		}
		if( $updated > 0 ){
			$success_message .= sprintf( __( '%d Updated', 'wp-event-aggregator' ), $updated )."<br>";
		}
		if( $skipped > 0 ){
			$success_message .= sprintf( __( '%d Skipped', 'wp-event-aggregator' ), $skipped ) ."<br>";
		}
		if( $nothing_to_import ){
			$success_message .= __( 'No events are imported.', 'import-eventbrite-events' ) . '<br>';	
		}
		$success_message .= "</strong></span>";

		// Return the title contents.
		return $success_message;
	}

	/**
	 * Setup output for Action column.
	 *
	 * @param array $item Items.
	 * @return array
	 */
	function column_action( $item ) {
		$url = add_query_arg( array(
		    'action'    => 'wpea_view_import_history',
		    'history'   => $item['ID'],
		    'TB_iframe' => 'true',
		    'width'     => '800',
		    'height'    => '500'
		), admin_url( 'admin.php' ) );

		$imported_data = get_post_meta($item['ID'], 'imported_data', true);
	    if(!empty($imported_data)){
			return sprintf(
				'<a href="%1$s" title="%2$s" class="open-history-details-modal button button-primary thickbox">%3$s</a>',
				$url,
				$item['title'],
				__( 'View Imported Events', 'wp-event-aggregator' )
			);
		}else{
			return '-';
		}
	}

	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],  //Let's simply repurpose the table's singular label ("video")
            /*$2%s*/ $item['ID']                //The value of the checkbox should be the record's id
        );
    }

	/**
	 * Get column title.
	 *
	 * @since    1.0.0
	 */
	function get_columns() {
		$columns = array(
		 'cb'    => '<input type="checkbox" />',
		 'title'     => __( 'Import', 'wp-event-aggregator' ),
		 'import_category' => __( 'Import Category', 'wp-event-aggregator' ),
		 'import_date'  => __( 'Import Date', 'wp-event-aggregator' ),
		 'stats' => __( 'Import Stats', 'wp-event-aggregator' ),
		 'action' => __( 'Action', 'wp-event-aggregator' ),
		);
		return $columns;
	}

	public function get_bulk_actions() {

        return array(
            'delete' => __( 'Delete', 'wp-event-aggregator' ),
        );

    }

	/**
	 * Prepare Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function prepare_items( $origin = '' ) {
		$per_page = 10;
		$columns = $this->get_columns();
		$hidden = array( 'ID' );
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();
		
		if( $origin != '' ){
			$data = $this->get_import_history_data( $origin );	
		}else{
			$data = $this->get_import_history_data();
		}
		
		if ( ! empty( $data ) ) {
			$total_items = ( $data['total_records'] )? (int) $data['total_records'] : 0;
			// Set data to items.
			$this->items = ( $data['import_data'] )? $data['import_data'] : array();

			$this->set_pagination_args( array(
			    'total_items' => $total_items,  // WE have to calculate the total number of items.
			    'per_page'    => $per_page, // WE have to determine how many items to show on a page.
			    'total_pages' => ceil( $total_items / $per_page ), // WE have to calculate the total number of pages.
			) );
		}
	}

	/**
	 * Get Meetup url data.
	 *
	 * @since    1.0.0
	 */
	function get_import_history_data( $origin = '' ) {
		global $importevents;

		$scheduled_import_data = array( 'total_records' => 0, 'import_data' => array() );
		$per_page = 10;
		$current_page = $this->get_pagenum();

		$query_args = array(
			'post_type' => 'wpea_import_history',
			'posts_per_page' => $per_page,
			'paged' => $current_page,
		);

		if( $origin != '' ){
			$query_args['meta_key'] = 'import_origin';
			$query_args['meta_value'] = esc_attr( $origin );
		}

		$importdata_query = new WP_Query( $query_args );
		$scheduled_import_data['total_records'] = ( $importdata_query->found_posts ) ? (int) $importdata_query->found_posts : 0;
		// The Loop.
		if ( $importdata_query->have_posts() ) {
			while ( $importdata_query->have_posts() ) {
				$importdata_query->the_post();

				$import_id = get_the_ID();
				$import_data = get_post_meta( $import_id, 'import_data', true );
				$import_origin = get_post_meta( $import_id, 'import_origin', true );
				$import_plugin = isset( $import_data['import_into'] ) ? $import_data['import_into'] : '';
				
				$term_names = array();
				$import_terms = isset( $import_data['event_cats'] ) ? $import_data['event_cats'] : array(); 
				
				if ( $import_terms && ! empty( $import_terms ) ) {
					foreach ( $import_terms as $term ) {
						$get_term = '';
						if( $import_plugin != '' && !empty( $importevents->$import_plugin ) ){
							$get_term = get_term( $term, $importevents->$import_plugin->get_taxonomy() );
						}
						
						if( !is_wp_error( $get_term ) && !empty( $get_term ) ){
							$term_names[] = $get_term->name;
						}
					}
				}

				$scheduled_import_data['import_data'][] = array(
					'ID' => $import_id,
					'title' => get_the_title(),
					'import_category' => implode( ', ', $term_names ),
					'import_date' => get_the_date("F j Y, h:i A"),
				);
			}
		}
		// Restore original Post Data.
		wp_reset_postdata();
		return $scheduled_import_data;
	}
}