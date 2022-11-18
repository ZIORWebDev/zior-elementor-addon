<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function zior_register_elementor_tags( $tags ) {
	spl_autoload_register( function ( $class ) {

		$allowed_class = [];

		if ( ! in_array( strtolower( $class ), $allowed_class ) ) {
			return;
		}

		include strtolower( $class ) . '.php';
	});

	//$tags->register( new ZIOR_Slides() );
	//$tags->register( new ZIOR_Posts_Filters() );
}
add_action( 'elementor/dynamic_tags/register', 'zior_register_elementor_tags', 10 );