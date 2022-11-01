<?php
function zior_register_elementor_widgets( $widgets ) {
	spl_autoload_register( function ( $class ) {

		$allowed_class = [
			'zior_slides',
			'zior_posts_filters'
		];

		if ( ! in_array( strtolower( $class ), $allowed_class ) ) {
			return;
		}

		include strtolower( $class ) . '.php';
	});

	$widgets->register( new ZIOR_Slides() );
	$widgets->register( new ZIOR_Posts_Filters() );
}
add_action( 'elementor/widgets/register', 'zior_register_elementor_widgets', 10 );