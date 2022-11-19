<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function zior_register_elementor_tags( $tags ) {
	spl_autoload_register( function ( $class ) {

		$allowed_class = [
			'zior_acfextras_tag'
		];

		if ( ! in_array( strtolower( $class ), $allowed_class ) ) {
			return;
		}

		include strtolower( $class ) . '.php';
	});

	$tags->register( new ZIOR_ACFExtras_Tag() );
}
add_action( 'elementor/dynamic_tags/register', 'zior_register_elementor_tags', 10 );