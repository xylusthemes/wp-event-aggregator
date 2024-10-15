<?php
/**
 * Template part for displaying posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */
global $importevents;
$start_date_str      = get_post_meta( get_the_ID(), 'start_ts', true );
$start_date_formated = date_i18n( 'F j, Y ', $start_date_str );
$event_address       = get_post_meta( get_the_ID(), 'venue_name', true );
$venue_address       = get_post_meta( get_the_ID(), 'venue_address', true );
if ( $event_address != '' && $venue_address != '' ) {
	$event_address .= ' - ' . $venue_address;
} elseif ( $venue_address != '' ) {
	$event_address = $venue_address;
}

$wpea_options = get_option( WPEA_OPTIONS );
$accent_color = isset( $wpea_options['wpea']['accent_color'] ) ? $wpea_options['wpea']['accent_color'] : '#039ED7';
$time_format  = isset( $wpea_options['wpea']['time_format'] ) ? $wpea_options['wpea']['time_format'] : '12hours';

if( $time_format === '12hours' ){
	$start_time = date_i18n( 'h:i a', $start_date_str );
}elseif($time_format === '24hours' ){
	$start_time = date_i18n( 'G:i', $start_date_str );
}else{
    $start_time = date_i18n( get_option( 'time_format' ), $start_date_str );
}

$image_url = array();
if ( '' !== get_the_post_thumbnail() ) {
	$image_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'full' );
} else {
	if ( '' !== $wpea_ed_image ) {
		$image_url = wp_get_attachment_image_src( $wpea_ed_image, 'full' );
	}else{
		$image_date  = date_i18n( 'F+d', $start_date_str );
		$image_url[] = 'https://dummyimage.com/420x210/ccc/969696.png?text=' . $image_date;
	}
}

$event_url = get_permalink();
$target = '';
if ( 'yes' === $direct_link ){
	$event_url = get_post_meta( get_the_ID(), 'wpea_event_link', true );
	$target = 'target="_blank"';
}

$eve_cats = array();
$event_categories = wp_get_post_terms( get_the_ID(), $importevents->cpt->get_event_categroy_taxonomy() );
if ( ! empty( $event_categories ) ) {
	foreach ( $event_categories as $event_category ) {
		$eve_cats[] = '<a class="wpea_event_cat" style="background-color:'. esc_attr( $accent_color ) .';" href="' . esc_url( get_term_link( $event_category->term_id ) ) . '">' . esc_attr( $event_category->name ) . '</a>';
	}
}

?>
<div <?php post_class( array( $css_class, 'archive-event' ) ); ?> >
	<div class="wpea-style2">
		<div class="wpea_event_style2">
			<div class="wpea_event_thumbnail">
				<a href="<?php echo esc_url( $event_url ); ?>" <?php echo $target; ?> >
					<div class="wpea_img_placeholder" style=" background: url('<?php echo esc_url( $image_url[0] ); ?>') no-repeat left top;"></div>
				</a>
				<span class="wpea_event_meta_cat">
					<?php
						foreach( $eve_cats as $eve_cat ){
							echo $eve_cat;
						}
					?>
				</span>
			</div>
			<div class="wpea_event_detail_style2">
				<h2 class="wpea_event_title_style2">
					<a style="color:<?php echo esc_attr( $accent_color ); ?>" href="<?php echo esc_url( $event_url ); ?>"><?php the_title(); ?></a>
				</h2>
				<div class="wpea_event_location_time">
					<div class="wpea_event_time">
						<span class="wpea_time">
							<i style="color:<?php echo esc_attr( $accent_color ); ?>" class="fa fa-clock-o" aria-hidden="true"></i> <?php echo esc_attr( $start_date_formated . ' ' . $start_time ) ; ?>
						</span>
					</div>
					<div class="wpea_location_style2">
						<div class="wpea_event_location">
							<?php if ( $event_address != '' ) { ?>
								<i style="color:<?php echo esc_attr( $accent_color ); ?>" class="fa fa-map-marker"></i> <?php echo esc_attr( ucfirst( $event_address ) ); ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>