<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Current date shortcode
 * 
 * param @atts array shortcode attributes
 * return date string
 */
function zior_date_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'format' => '',
		), $atts
	);

	if ( ! empty( $atts['format'] ) ) {
		$dateFormat = sanitize_text_field( $atts['format'] );
	} else {
		$dateFormat = 'jS F Y';
	}

	if ( $dateFormat == 'z' ) {
		return date_i18n( $dateFormat ) + 1;
	} else {
		return date_i18n( $dateFormat );
	}
}
add_shortcode( 'zior_current_date',  'zior_date_shortcode' );