<?php
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ZIOR_Posts_Addon {
	public function __construct() {
		$this->init_hooks();
	}

	public function init_hooks() {
		add_action( 'elementor/frontend/before_render', [ $this, 'elementor_before_render' ] );
	}
	/*
	* Add custom variables into global query object
	* 
	* @param object $query
	* 
	* @return object
	*/
	function custom_query_callback( $query ) {
		$keyword = sanitize_text_field( $_GET['keyword'] );
		if ( ! empty( $keyword ) ) {
			$query->query_vars['s'] = $keyword;
		}

		$year = sanitize_text_field( $_GET['_year'] );
		if ( is_int( $year ) ) {
			$query->query_vars['year'] = $year;

			$month = sanitize_text_field( $_GET['month'] );
			if ( is_int( $month ) ) {
				$query->query_vars['monthnum'] = $month;
			}
		}
		
		$page_num = absint( sanitize_text_field( $_GET['page_num'] ) );
		$page_num = ( $page_num === 0 ) ? 1 : $page_num;
		$query->query_vars['paged'] = trim( $_GET['page_num'] );

		$term = get_term( absint( $_GET['term_id'] ) );

		if ( $term ) {
			$taxonomy = sanitize_title( $_GET['taxonomy'] );
			$query->query_vars[ $taxonomy ] = $term->slug;
			$query->tax_query->queries[0] = [
				'taxonomy' => $taxonomy,
				'terms'    => [ $term->slug ],
				'field'    => 'slug',
				'operator' => 'IN'
			];

			$query->tax_query->queried_terms[ $taxonomy ] = [
				'terms'    => [ $term->slug ],
				'field'    => 'slug'
			];
		}

		return $query;
	}

	function elementor_frontend_before_render() {
		$action   = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		$query_id = isset( $_GET['target_query_id'] ) ? sanitize_text_field( $_GET['target_query_id'] ) : '';
		if ( $action === 'filter_posts_widget' && ! empty( $query_id ) ) {
			add_action( "elementor/query/{$query_id}", [ $this, 'custom_query_callback' ] );
		}
	}
	
}