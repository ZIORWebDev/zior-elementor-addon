<?php
spl_autoload_register( function ( $class ) {
	$allowed_class = [
		'zior_posts_addon',
		'zior_searchform_addon'
	];

	if ( ! in_array( strtolower( $class ), $allowed_class ) ) {
		return;
	}

	include strtolower( $class ) . '.php';
});

new ZIOR_Posts_Addon();
new ZIOR_SearchForm_Addon();