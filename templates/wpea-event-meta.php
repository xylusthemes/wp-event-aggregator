<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * The template for displaying all single Event meta
 */	
global $importevents;

$wpea_event_id = get_the_ID();

$wpea_start_date_str = get_post_meta( $wpea_event_id, 'start_ts', true );
$wpea_end_date_str = get_post_meta( $wpea_event_id, 'end_ts', true );
$wpea_start_date_formated = date_i18n( 'F j', $wpea_start_date_str );
$wpea_end_date_formated = date_i18n( 'F j', $wpea_end_date_str );
$wpea_website = get_post_meta( $wpea_event_id, 'wpea_event_link', true );
$wpea_get_gmap_key = get_option( 'wpea_google_maps_api_key', false );

$wpea_options = get_option( WPEA_OPTIONS );
$wpea_time_format = isset( $wpea_options['wpea']['time_format'] ) ? $wpea_options['wpea']['time_format'] : '12hours';

if($wpea_time_format === '12hours' ){
    $wpea_start_time = date_i18n( 'h:i a', $wpea_start_date_str );
    $wpea_end_time   = date_i18n( 'h:i a', $wpea_end_date_str );
}elseif($wpea_time_format === '24hours' ){
    $wpea_start_time = date_i18n( 'G:i', $wpea_start_date_str );
    $wpea_end_time   = date_i18n( 'G:i', $wpea_end_date_str );
}else{
    $wpea_start_time = date_i18n( get_option( 'time_format' ), $wpea_start_date_str );
    $wpea_end_time   = date_i18n( get_option( 'time_format' ), $wpea_end_date_str );
}
?>
<div class="wpea_organizer">
  <div class="details">
    <div class="wpea_titlemain" > <?php esc_html_e( 'Details','wp-event-aggregator' ); ?> </div>

    <?php 
	if( gmdate( 'Y-m-d', strtotime( $wpea_start_date_str ) ) == gmdate( 'Y-m-d', strtotime( $wpea_end_date_str ) ) ){
    	?>
    	<strong><?php esc_html_e( 'Date','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo esc_attr( $wpea_start_date_formated ); ?></p>

	    <strong><?php esc_html_e( 'Time','wp-event-aggregator' ); ?>:</strong>
	    <p><?php if( $wpea_start_time != $wpea_end_time ){ 
	    		echo esc_attr( $wpea_start_time . ' - ' . $wpea_end_time );
	    	}else{
	    		echo esc_attr( $wpea_start_time );
    		}?>
		</p>
		<?php
	}else{
		?>
		<strong><?php esc_html_e( 'Start','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo esc_attr( $wpea_start_date_formated . ' - ' . $wpea_start_time ); ?></p>

	    <strong><?php esc_html_e( 'End','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo esc_attr( $wpea_end_date_formated . ' - ' . $wpea_end_time ); ?></p>
		<?php
	}

	$wpea_eve_tags = $wpea_eve_cats = array();
	$wpea_event_categories = wp_get_post_terms( $wpea_event_id, $importevents->cpt->get_event_categroy_taxonomy() );
	if( !empty( $wpea_event_categories ) ){
		foreach ($wpea_event_categories as $wpea_event_category ) {
			$wpea_eve_cats[] = '<a href="'. esc_url( get_term_link( $wpea_event_category->term_id ) ).'">' . $wpea_event_category->name. '</a>';
		}
	}

	$wpea_event_tags = wp_get_post_terms( $wpea_event_id, $importevents->cpt->get_event_tag_taxonomy() );
	if( !empty( $wpea_event_tags ) ){
		foreach ($wpea_event_tags as $wpea_event_tag ) {
			$wpea_eve_tags[] = '<a href="'. esc_url( get_term_link( $wpea_event_tag->term_id ) ).'">' . $wpea_event_tag->name. '</a>';
		}
	}

	if( !empty( $wpea_eve_cats ) ){
		?>
		<strong><?php esc_html_e( 'Event Category','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo wp_kses_post( implode(', ', $wpea_eve_cats ) ); ?></p>
		<?php
	}

	if( !empty( $wpea_eve_tags ) ){
		?>
		<strong><?php esc_html_e( 'Event Tags','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo wp_kses_post( implode(', ', $wpea_eve_tags ) ); ?></p>
		<?php
	}
	?>

    <?php if( $wpea_website != '' ){ ?>
    	<strong><?php esc_html_e( 'Click to Register','wp-event-aggregator' ); ?>:</strong>
    	<a href="<?php echo esc_url( $wpea_website ); ?>"><?php esc_attr_e( "Click to Register", 'wp-event-aggregator' ); ?></a>
    <?php } ?>

  </div>

  <?php
  		// Organizer
		$wpea_org_name = get_post_meta( $wpea_event_id, 'organizer_name', true );
		$wpea_org_email = get_post_meta( $wpea_event_id, 'organizer_email', true );
		$wpea_org_phone = get_post_meta( $wpea_event_id, 'organizer_phone', true );
		$wpea_org_url = get_post_meta( $wpea_event_id, 'organizer_url', true );

		if( $wpea_org_name != '' ){
			?>
			<div class="organizer">
				<div class="wpea_titlemain"><?php esc_html_e( 'Organizer','wp-event-aggregator' ); ?></div>
				<p><strong><?php echo esc_attr( $wpea_org_name ); ?></strong></p>
			</div>
			<?php if( $wpea_org_email != '' ){ ?>
		    	<strong style="display: block;"><?php esc_html_e( 'Email','wp-event-aggregator' ); ?>:</strong>
		    	<a href="<?php echo 'mailto:'. esc_attr( $wpea_org_email ); ?>"><?php echo esc_attr( $wpea_org_email ); ?></a>
		    <?php } ?>
		    <?php if( $wpea_org_phone != '' ){ ?>
		    	<strong style="display: block;"><?php esc_html_e( 'Phone','wp-event-aggregator' ); ?>:</strong>
		    	<a href="<?php echo 'tel:'. esc_attr( $wpea_org_phone ); ?>"><?php echo esc_attr( $wpea_org_phone ); ?></a>
		    <?php } ?>
		    <?php if( $wpea_org_url != '' ){ ?>
		    	<strong style="display: block;"><?php esc_html_e( 'Website','wp-event-aggregator' ); ?>:</strong>
		    	<a href="<?php echo esc_url( $wpea_org_url ); ?>"><?php esc_attr_e( "Organizer's Website", 'wp-event-aggregator' ); ?></a>
		    <?php }
		}
    ?>
	<div style="clear: both"></div>
</div>

<?php
$wpea_venue_name    = get_post_meta( $wpea_event_id, 'venue_name', true );
$wpea_venue_address = get_post_meta( $wpea_event_id, 'venue_address', true );
$wpea_venue['city'] = get_post_meta( $wpea_event_id, 'venue_city', true );
$wpea_venue['state'] = get_post_meta( $wpea_event_id, 'venue_state', true );
$wpea_venue['country'] = get_post_meta( $wpea_event_id, 'venue_country', true );
$wpea_venue['zipcode'] = get_post_meta( $wpea_event_id, 'venue_zipcode', true );
$wpea_venue['lat'] = get_post_meta( $wpea_event_id, 'venue_lat', true );
$wpea_venue['lon'] = get_post_meta( $wpea_event_id, 'venue_lon', true );
$wpea_venue_url = esc_url( get_post_meta( $wpea_event_id, 'venue_url', true ) );
$wpea_venue_address_name = !empty( $wpea_venue_address ) ? $wpea_venue_address : $wpea_venue_name;

if ( wpea_is_pro() && empty( $wpea_get_gmap_key ) ) {
	$wpea_map_api_key  = WPEAPRO_GM_APIKEY;
}elseif( !empty( $wpea_get_gmap_key ) ){
	$wpea_map_api_key  = $wpea_get_gmap_key;
}else{
	$wpea_map_api_key  = '';
}

if ( ! empty( $wpea_venue_address_name ) || ( ! empty( $wpea_venue['lat'] ) && ! empty( $wpea_venue['lon'] ) ) ) {
	?>
	<div class="wpea_organizer library">
		<div class="venue">
			<div class="wpea_titlemain"><strong><?php esc_html_e( 'Venue','wp-event-aggregator' ); ?></strong></div>
			<p><?php echo esc_attr( $wpea_venue_name ); ?></p>
			<?php
			if( $wpea_venue_address != '' ){
				echo '<p>' . esc_attr( $wpea_venue_address ) . '</p>';
			}
			$wpea_venue_array = array();
			foreach ($wpea_venue as $wpea_key => $wpea_value) {
				if( in_array( $wpea_key, array( 'city', 'state', 'country', 'zipcode' ) ) ){
					if( $wpea_value != ''){
						$wpea_venue_array[] = $wpea_value;
					}
				}
			}
			echo '<p>' . esc_attr( implode( ", ", $wpea_venue_array ) ) . '</p>';
			?>
		</div>
		<?php
		$wpea_q = '';
		$wpea_lat_lng = '';
		if ( ! empty( $wpea_venue['lat'] ) && ! empty( $wpea_venue['lon'] ) ) {
			$wpea_lat_lng = esc_attr( $wpea_venue['lat'] ) . ',' . esc_attr( $wpea_venue['lon'] );
		}
		if ( ! empty( $wpea_venue_name ) ) {
			$wpea_q = esc_attr( $wpea_venue_name );
		}
		if ( ! empty( $wpea_venue_address ) ) {
			$wpea_q = esc_attr( $wpea_venue_address );
		}
		if( ! empty( $wpea_venue_name ) && ! empty( $wpea_venue_address ) ){
			$wpea_q = esc_attr( $wpea_venue_name ).esc_attr( $wpea_venue_address );
		}
		if(empty($wpea_q)){
			$wpea_q = $wpea_lat_lng;
		}
		if ( ! empty( $wpea_q ) ) {
			$wpea_params = array(
				'q' => $wpea_q
			);
			if ( ! empty( $wpea_lat_lng ) ) {
				$wpea_params['center'] = $wpea_lat_lng;
			}
			$wpea_query = http_build_query($wpea_params);

			if( empty( $wpea_map_api_key ) ){
				$wpea_full_address = str_replace( ' ', '%20', $wpea_venue_address ) .','. $wpea_venue['city'] .','. $wpea_venue['state'] .','. $wpea_venue['country'].'+(' . str_replace( ' ', '%20', $wpea_venue_name ) . ')';	
				?>
				<div class="map">
					<iframe src="https://maps.google.com/maps?q=<?php echo esc_attr( $wpea_full_address ); ?>&hl=es;z=14&output=embed" width="100%" height="350" frameborder="0" style="border:0; margin:0;" allowfullscreen></iframe>
				</div>
				<?php
			}else{ 
				?>
				<div class="map">
					<iframe src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr( $wpea_map_api_key ); ?>&<?php echo esc_attr( $wpea_query ); ?>" width="100%" height="350" frameborder="0" style="border:0; margin:0;" allowfullscreen></iframe>
				</div>
			<?php
			}
		}
		?>
		<div style="clear: both;"></div>
	</div>
	<?php
}
?>
<div style="clear: both;"></div>