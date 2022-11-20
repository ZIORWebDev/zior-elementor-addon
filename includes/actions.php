<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * Enqueue scripts in the frontend
 * 
 * @return void
 */
function zior_frontend_scripts() {
	$assets = [
		[
			'handle' => 'zior-main',
			'type' => 'js',
			'path' => ZIOR_PLUGIN_URL . 'assets/js/',
			'localize' => true,
			'options' => [ 'ajax_url' => admin_url( 'admin-ajax.php' ) ],
			'name' => 'main',
			'variable' => 'zior',
			'dependencies' => [ 'jquery' ],
		],
		[
			'handle' => 'zior-main',
			'type' => 'css',
			'path' => ZIOR_PLUGIN_URL . 'assets/css/',
			'localize' => false,
			'name' => 'main'
		]
	];

	zior_enqueue_assets( $assets );
}
add_action( 'wp_enqueue_scripts', 'zior_frontend_scripts', 10 );