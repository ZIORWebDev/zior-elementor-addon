<?php
function zior_ajax_page_template( $template ) {
	if ( isset( $_GET['action'] ) && trim( $_GET['action'] ) === 'filter_posts_widget'
		&& isset( $_GET['is_ajax'] ) && intval( $_GET['is_ajax'] ) === 1 ) {
		if ( is_file( ZIOR_PLUGIN_DIR . 'templates/ajax-content.php' ) ) {
			$template = ZIOR_PLUGIN_DIR . 'templates/ajax-content.php';
		}
	}
	return $template;
}
add_filter( 'template_include', 'zior_ajax_page_template', 99 );