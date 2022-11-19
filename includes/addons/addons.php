<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

spl_autoload_register( function ( $class ) {
	$allowed_class = [
		'zior_posts_addon',
		'zior_searchform_addon',
		'zior_posts_filters_addon'
	];

	if ( ! in_array( strtolower( $class ), $allowed_class ) ) {
		return;
	}

	include strtolower( $class ) . '.php';
});

new ZIOR_Posts_Addon();
new ZIOR_SearchForm_Addon();
new ZIOR_Posts_Filters_Addon();
