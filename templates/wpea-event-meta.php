<?php
/**
 * The template for displaying all single Event meta
 */	
global $importevents;

$event_id = get_the_ID();

$start_date_str = get_post_meta( $event_id, 'start_ts', true );
$end_date_str = get_post_meta( $event_id, 'end_ts', true );
$start_date_formated = date_i18n( 'F j', $start_date_str );
$end_date_formated = date_i18n( 'F j', $end_date_str );
$website = get_post_meta( $event_id, 'wpea_event_link', true );
$get_gmap_key = get_option( 'wpea_google_maps_api_key', false );

$wpea_options = get_option( WPEA_OPTIONS );
$time_format = isset( $wpea_options['wpea']['time_format'] ) ? $wpea_options['wpea']['time_format'] : '12hours';

if($time_format === '12hours' ){
    $start_time = date_i18n( 'h:i a', $start_date_str );
    $end_time   = date_i18n( 'h:i a', $end_date_str );
}elseif($time_format === '24hours' ){
    $start_time = date_i18n( 'G:i', $start_date_str );
    $end_time   = date_i18n( 'G:i', $end_date_str );
}else{
    $start_time = date_i18n( get_option( 'time_format' ), $start_date_str );
    $end_time   = date_i18n( get_option( 'time_format' ), $end_date_str );
}
?>
<div class="wpea_organizer">
  <div class="details">
    <div class="titlemain" > <?php esc_html_e( 'Details','wp-event-aggregator' ); ?> </div>

    <?php 
    if( date( 'Y-m-d', $start_date_str ) == date( 'Y-m-d', $end_date_str ) ){
    	?>
    	<strong><?php esc_html_e( 'Date','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo $start_date_formated; ?></p>

	    <strong><?php esc_html_e( 'Time','wp-event-aggregator' ); ?>:</strong>
	    <p><?php if( $start_time != $end_time ){ 
	    		echo $start_time . ' - ' . $end_time;
	    	}else{
	    		echo $start_time;
    		}?>
		</p>
		<?php
	}else{
		?>
		<strong><?php esc_html_e( 'Start','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo $start_date_formated . ' - ' . $start_time; ?></p>

	    <strong><?php esc_html_e( 'End','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo $end_date_formated . ' - ' . $end_time; ?></p>
		<?php
	}

	$eve_tags = $eve_cats = array();
	$event_categories = wp_get_post_terms( $event_id, $importevents->cpt->get_event_categroy_taxonomy() );
	if( !empty( $event_categories ) ){
		foreach ($event_categories as $event_category ) {
			$eve_cats[] = '<a href="'. esc_url( get_term_link( $event_category->term_id ) ).'">' . $event_category->name. '</a>';
		}
	}

	$event_tags = wp_get_post_terms( $event_id, $importevents->cpt->get_event_tag_taxonomy() );
	if( !empty( $event_tags ) ){
		foreach ($event_tags as $event_tag ) {
			$eve_tags[] = '<a href="'. esc_url( get_term_link( $event_tag->term_id ) ).'">' . $event_tag->name. '</a>';
		}
	}

	if( !empty( $eve_cats ) ){
		?>
		<strong><?php esc_html_e( 'Event Category','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo implode(', ', $eve_cats ); ?></p>
		<?php
	}

	if( !empty( $eve_tags ) ){
		?>
		<strong><?php esc_html_e( 'Event Tags','wp-event-aggregator' ); ?>:</strong>
	    <p><?php echo implode(', ', $eve_tags ); ?></p>
		<?php
	}
	?>

    <?php if( $website != '' ){ ?>
    	<strong><?php esc_html_e( 'Click to Register','wp-event-aggregator' ); ?>:</strong>
    	<a href="<?php echo esc_url( $website ); ?>"><?php _e( "Click to Register", 'wp-event-aggregator' ); ?></a>
    <?php } ?>

  </div>

  <?php
  		// Organizer
		$org_name = get_post_meta( $event_id, 'organizer_name', true );
		$org_email = get_post_meta( $event_id, 'organizer_email', true );
		$org_phone = get_post_meta( $event_id, 'organizer_phone', true );
		$org_url = get_post_meta( $event_id, 'organizer_url', true );

		if( $org_name != '' ){
			?>
			<div class="organizer">
				<div class="titlemain"><?php esc_html_e( 'Organizer','wp-event-aggregator' ); ?></div>
				<p><strong><?php echo $org_name; ?></strong></p>
			</div>
			<?php if( $org_email != '' ){ ?>
		    	<strong style="display: block;"><?php esc_html_e( 'Email','wp-event-aggregator' ); ?>:</strong>
		    	<a href="<?php echo 'mailto:'.$org_email; ?>"><?php echo $org_email; ?></a>
		    <?php } ?>
		    <?php if( $org_phone != '' ){ ?>
		    	<strong style="display: block;"><?php esc_html_e( 'Phone','wp-event-aggregator' ); ?>:</strong>
		    	<a href="<?php echo 'tel:'.$org_phone; ?>"><?php echo $org_phone; ?></a>
		    <?php } ?>
		    <?php if( $org_url != '' ){ ?>
		    	<strong style="display: block;"><?php esc_html_e( 'Website','wp-event-aggregator' ); ?>:</strong>
		    	<a href="<?php echo esc_url( $org_url ); ?>"><?php _e( "Organizer's Website", 'wp-event-aggregator' ); ?></a>
		    <?php }
		}
    ?>
	<div style="clear: both"></div>
</div>

<?php
$venue_name    = get_post_meta( $event_id, 'venue_name', true );
$venue_address = get_post_meta( $event_id, 'venue_address', true );
$venue['city'] = get_post_meta( $event_id, 'venue_city', true );
$venue['state'] = get_post_meta( $event_id, 'venue_state', true );
$venue['country'] = get_post_meta( $event_id, 'venue_country', true );
$venue['zipcode'] = get_post_meta( $event_id, 'venue_zipcode', true );
$venue['lat'] = get_post_meta( $event_id, 'venue_lat', true );
$venue['lon'] = get_post_meta( $event_id, 'venue_lon', true );
$venue_url = esc_url( get_post_meta( $event_id, 'venue_url', true ) );
$venue_address_name = !empty( $venue_address ) ? $venue_address : $venue_name;

if ( wpea_is_pro() && empty( $get_gmap_key ) ) {
	$map_api_key  = WPEAPRO_GM_APIKEY;
}elseif( !empty( $get_gmap_key ) ){
	$map_api_key  = $get_gmap_key;
}else{
	$map_api_key  = '';
}

if ( ! empty( $venue_address_name ) || ( ! empty( $venue['lat'] ) && ! empty( $venue['lon'] ) ) ) {
	?>
	<div class="wpea_organizer library">
		<div class="venue">
			<div class="titlemain"><strong><?php esc_html_e( 'Venue','wp-event-aggregator' ); ?></strong></div>
			<p><?php echo $venue_name; ?></p>
			<?php
			if( $venue_address != '' ){
				echo '<p>' . $venue_address . '</p>';
			}
			$venue_array = array();
			foreach ($venue as $key => $value) {
				if( in_array( $key, array( 'city', 'state', 'country', 'zipcode' ) ) ){
					if( $value != ''){
						$venue_array[] = $value;
					}
				}
			}
			echo '<p>' . implode( ", ", $venue_array ) . '</p>';
			?>
		</div>
		<?php
		$q = '';
		$lat_lng = '';
		if ( ! empty( $venue['lat'] ) && ! empty( $venue['lon'] ) ) {
			$lat_lng = esc_attr( $venue['lat'] ) . ',' . esc_attr( $venue['lon'] );
		}
		if ( ! empty( $venue_name ) ) {
			$q = esc_attr( $venue_name );
		}
		if ( ! empty( $venue_address ) ) {
			$q = esc_attr( $venue_address );
		}
		if( ! empty( $venue_name ) && ! empty( $venue_address ) ){
			$q = esc_attr( $venue_name ).esc_attr( $venue_address );
		}
		if(empty($q)){
			$q = $lat_lng;
		}
		if ( ! empty( $q ) ) {
			$params = array(
				'q' => $q
			);
			if ( ! empty( $lat_lng ) ) {
				$params['center'] = $lat_lng;
			}
			$query = http_build_query($params);

			if( empty( $map_api_key ) ){
				$full_address = str_replace( ' ', '%20', $venue_address ) .','. $venue['city'] .','. $venue['state'] .','. $venue['country'].'+(' . str_replace( ' ', '%20', $venue_name ) . ')';	
				?>
				<div class="map">
					<iframe src="https://maps.google.com/maps?q=<?php echo $full_address; ?>&hl=es;z=14&output=embed" width="100%" height="350" frameborder="0" style="border:0; margin:0;" allowfullscreen></iframe>
				</div>
				<?php
			}else{ 
				?>
				<div class="map">
					<iframe src="https://www.google.com/maps/embed/v1/place?key=<?php echo $map_api_key; ?>&<?php echo $query; ?>" width="100%" height="350" frameborder="0" style="border:0; margin:0;" allowfullscreen></iframe>
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