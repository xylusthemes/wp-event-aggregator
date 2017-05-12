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

		$wpea_url_delete_args = array(
			'page'   => wp_unslash( $_REQUEST['page'] ),
			'wpea_action' => 'wpea_simport_delete',
			'import_id'  => absint( $item['ID'] ),
		);
		// Build row actions.
		$actions = array(
		    'delete' => sprintf( '<a href="%1$s" onclick="return confirm(\'Warning!! Are you sure to Delete this scheduled import? Scheduled import will be permanatly deleted.\')">%2$s</a>',esc_url( wp_nonce_url( add_query_arg( $wpea_url_delete_args ), 'wpea_delete_import_nonce' ) ), esc_html__( 'Delete', 'wp-event-aggregator' ) ),
		);

		// Return the title contents.
		return sprintf('<strong>%1$s</strong><span>%4$s</span> <span style="color:silver">(id:%2$s)</span>%3$s',
		    $item['title'],
		    $item['ID'],
		    $this->row_actions( $actions ),
		    __('Origin', 'wp-event-aggregator') . ': <b>' . ucfirst( $item["import_origin"] ) . '</b>'
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

		// Return the title contents.
		return sprintf( '<a class="button-primary" href="%1$s">%2$s</a>',
			esc_url( wp_nonce_url( add_query_arg( $xtmi_run_import_args ), 'wpea_run_import_nonce' ) ),
			esc_html__( 'Import Now', 'wp-event-aggregator' )
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
		 'title'     => 'Scheduled import',
		 'import_category'   => 'Import Category',
		 'import_frequency'   => 'Import Frequency',
		 'action'   => 'Action',
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
		$scheduled_import_data = array( 'total_records' => 0, 'import_data' => array() );
		$per_page = 10;
		$current_page = $this->get_pagenum();

		$query_args = array(
			'post_type' => 'xt_scheduled_imports',
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
				$import_data = get_post_meta( $import_id, 'import_eventdata', true );
				$import_origin = get_post_meta( $import_id, 'import_origin', true );
				$term_names = array();
				$import_terms = isset( $import_data['event_cats'] ) ? $import_data['event_cats'] : array(); 
				if ( $import_terms && ! empty( $import_terms ) ) {
					foreach ( $import_terms as $term ) {
						
						$get_term = get_term( $term, WPEA_TEC_TAXONOMY );
						if( !is_wp_error( $import_terms ) ){
							$term_names[] = $get_term->name;
						}
					}
				}
				$scheduled_import_data['import_data'][] = array(
					'ID' => $import_id,
					'title' => get_the_title(),
					'import_category' => implode( ', ', $term_names ),
					'import_frequency'=> isset( $import_data['import_frequency'] ) ? ucfirst( $import_data['import_frequency'] ) : '',
					'import_origin' => $import_origin
				);
			}
		}
		// Restore original Post Data.
		wp_reset_postdata();
		return $scheduled_import_data;
	}
}
