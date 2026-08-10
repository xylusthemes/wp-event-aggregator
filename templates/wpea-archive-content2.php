<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) exit;
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
$wpea_start_date_str      = get_post_meta( get_the_ID(), 'start_ts', true );
$wpea_start_date_formated = date_i18n( 'F j, Y ', $wpea_start_date_str );
$wpea_event_address       = get_post_meta( get_the_ID(), 'venue_name', true );
$wpea_venue_address       = get_post_meta( get_the_ID(), 'wpea_venue_address', true );
if ( $wpea_event_address != '' && $wpea_venue_address != '' ) {
	$wpea_event_address .= ' - ' . $wpea_venue_address;
} elseif ( $wpea_venue_address != '' ) {
	$wpea_event_address = $wpea_venue_address;
}

$wpea_options      = get_option( WPEA_OPTIONS );
$wpea_accent_color = isset( $wpea_options['wpea']['accent_color'] ) ? $wpea_options['wpea']['accent_color'] : '#039ED7';
$wpea_time_format       = isset( $wpea_options['wpea']['time_format'] ) ? $wpea_options['wpea']['time_format'] : '12hours';

if( $wpea_time_format === '12hours' ){
	$wpea_start_time = date_i18n( 'h:i a', $wpea_start_date_str );
}elseif($wpea_time_format === '24hours' ){
	$wpea_start_time = date_i18n( 'G:i', $wpea_start_date_str );
}else{
    $wpea_start_time = date_i18n( get_option( 'time_format' ), $wpea_start_date_str );
}

$wpea_image_url = array();
$wpea_thumbnail_id = get_post_meta( get_the_ID(), '_thumbnail_id', true );
if ( ! empty( $wpea_thumbnail_id ) ) {
	$wpea_image_url = wp_get_attachment_image_src( $wpea_thumbnail_id, 'full' );
}
if ( empty( $wpea_image_url ) ) {
	$wpea_external_image = get_post_meta( get_the_ID(), '_wpea_external_image_url', true );
	if ( ! empty( $wpea_external_image ) ) {
		$wpea_image_url[] = $wpea_external_image;
	} else {
		$wpea_image_date  = date_i18n( 'F+d', $wpea_start_date_str );
		$wpea_image_url[] = 'https://dummyimage.com/420x210/ccc/969696.png&text=' . $wpea_image_date;
	}
}

$wpea_event_url = get_permalink();
$wpea_target = '';
if ( 'yes' === $direct_link ){
	$wpea_event_url = get_post_meta( get_the_ID(), 'wpea_event_link', true );
	$wpea_target = 'target="_blank"';
}

$wpea_eve_cats = array();
$wpea_event_categories = wp_get_post_terms( get_the_ID(), $importevents->cpt->get_event_categroy_taxonomy() );
if ( ! empty( $wpea_event_categories ) ) {
	foreach ( $wpea_event_categories as $wpea_event_category ) {
		$wpea_eve_cats[] = '<a class="wpea_event_cat" style="background-color:'. esc_attr( $wpea_accent_color ) .';" href="' . esc_url( get_term_link( $wpea_event_category->term_id ) ) . '">' . esc_attr( $wpea_event_category->name ) . '</a>';
	}
}

?>
<div <?php post_class( array( $css_class, 'archive-event' ) ); ?> >
	<div class="wpea-style2">
		<div class="wpea_event_style2">
			<div class="wpea_event_thumbnail">
				<a href="<?php echo esc_url( $wpea_event_url ); ?>" <?php echo esc_attr( $wpea_target ); ?> >
					<div class="wpea_img_placeholder" style=" background: url('<?php echo esc_url( $wpea_image_url[0] ); ?>') no-repeat left top;"></div>
				</a>
				<span class="wpea_event_meta_cat">
					<?php
						foreach( $wpea_eve_cats as $wpea_eve_cat ){
							echo wp_kses_post( $wpea_eve_cat );
						}
					?>
				</span>
			</div>
			<div class="wpea_event_detail_style2">
				<h2 class="wpea_event_title_style2">
					<a style="color:<?php echo esc_attr( $wpea_accent_color ); ?>" href="<?php echo esc_url( $wpea_event_url ); ?>"><?php the_title(); ?></a>
				</h2>
				<div class="wpea_event_location_time">
					<div class="wpea_event_time">
						<span class="wpea_time">
							<i style="color:<?php echo esc_attr( $wpea_accent_color ); ?>" class="fa fa-clock-o" aria-hidden="true"></i> <?php echo esc_attr( $wpea_start_date_formated . ' ' . $wpea_start_time ) ; ?>
						</span>
					</div>
					<div class="wpea_location_style2">
						<div class="wpea_event_location">
							<?php if ( $wpea_event_address != '' ) { ?>
								<i style="color:<?php echo esc_attr( $wpea_accent_color ); ?>" class="fa fa-map-marker"></i> <?php echo esc_attr( ucfirst( $wpea_event_address ) ); ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>