<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ZIOR_Posts_Filters_Addon {
	public function __construct() {
		add_action( 'elementor/frontend/widget/before_render', 'posts_filters_before_render' );
	}

	/*
	* Add _year and _month query strings to archive links
	* 
	* @param object $widget
	* 
	* @return void
	*/
	public function posts_filters_before_render( $widget ) {
		if ( $widget->get_name() === 'zior_posts_filters' ) {
			add_filter( 'month_link', '_month_link', 10, 3 );
			add_filter( 'year_link', '_year_link', 10, 2 );		
		}else{
			remove_filter( 'month_link', '_month_link', 10, 3 );
			remove_filter( 'year_link', '_year_link', 10, 2 );		
		}
	}

	public function _month_link( $monthlink, $year, $month ) {
		$separator = strpos( $monthlink, '?' ) === false ? '?' : '&';
		return $monthlink . $separator . '_year=' . $year . '&_month='  .$month;
	}

	public function _year_link( $yearlink, $year ) {
		$separator = strpos( $yearlink, '?' ) === false ? '?' : '&';
		return $yearlink . $separator . '_year=' . $year;
	}
}