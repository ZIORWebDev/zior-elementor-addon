<?php
/*
 * Add custom template for ajax filter
 * 
 * @param string $template
 * 
 * @return string
 */
function zior_ajax_page_template( $template ) {
	if ( isset( $_GET['action'] ) && trim( $_GET['action'] ) === 'filter_posts_widget'
		&& isset( $_GET['is_ajax'] ) && intval( $_GET['is_ajax'] ) === 1 ) {
		if ( is_file( ZIOR_PLUGIN_DIR . 'templates/posts-ajax-content.php' ) ) {
			$template = ZIOR_PLUGIN_DIR . 'templates/posts-ajax-content.php';
		}
	}
	return $template;
}
add_filter( 'template_include', 'zior_ajax_page_template', 99 );

/*
 * Add select and option tags to allowed html tags
 * 
 * @param array $allowed
 * @param mixed $context
 * 
 * @return array
 */
function zior_kses_filter_allowed_html( $allowed, $context ) {
	$allowed['select'] = array(
		'data-*' => 1,
		'name'   => 1,
		'id'     => 1,
	);
	
	$allowed['option'] = array(
		'id'    => 1,
		'value' => 1,
	);
	
	$allowed['span'] = array(
		'data-*' => 1,
		'class'  => 1,
	);

	$allowed['div']['data-*'] = 1;
	$allowed['div']['class'] = 1;
	$allowed['div']['id'] = 1;
	
	$allowed['a']['data-*'] = 1;

	return $allowed;
}
add_filter( 'wp_kses_allowed_html', 'zior_kses_filter_allowed_html', 9999, 2 );