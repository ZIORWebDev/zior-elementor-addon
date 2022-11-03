<?php
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