<?php
/*
 * Enqueue scripts in the frontend
 * 
 * @return void
 */
function zior_frontend_scripts() {
	wp_enqueue_script( 'zior-main', ZIOR_PLUGIN_URL . 'assets/js/main.js', array( 'jquery' ), NULL, true );
	
	$options = [
		'ajax_url' => admin_url( 'admin-ajax.php' )
	];

	wp_localize_script( 'zior-main', 'zior', $options );

}
add_action( 'wp_enqueue_scripts', 'zior_frontend_scripts', 10 );