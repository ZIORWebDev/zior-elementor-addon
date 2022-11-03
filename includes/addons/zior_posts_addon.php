<?php
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ZIOR_Posts_Addon {
	public function __construct() {
		add_action( 'elementor/element/posts/section_query/before_section_end', [ $this, 'posts_form_widget_controls' ], 10, 2 );
		add_action( 'elementor/frontend/before_render', [ $this, 'before_posts_render' ], 99 );
		add_action( 'elementor/query/query_results', [ $this, 'query_results_not_found' ], 10, 2 );
		add_action( 'elementor/frontend/after_render', [ $this, 'ajax_response_after' ], 99 );
	}

	/*
	* Empty posts message
	* 
	* @param array $element
	* @param array $args
	* 
	* @return void
	*/
	public function posts_form_widget_controls( $element, $args ) {
		$element->add_control(
			'show_empty_message',
			[
				'label'        => __( 'Show custom empty message?', 'zior-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'zior-elementor' ),
				'label_off'    => __( 'No', 'zior-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'render_type'  => 'template',
			]
		);

		$element->add_control(
			'custom_empty_message',
			[
				'type'        => \Elementor\Controls_Manager::WYSIWYG,
				'rows'        => 4,
				'label'       => __( 'Custom Empty message', 'zior-elementor' ),
				'description' => __( 'Show message when posts result is empty', 'zior-elementor' ),
				'condition'   => [
					'show_empty_message' => 'yes',
				],
			]
		);
	}

	/*
	* Custom not found messae
	* 
	* @param object $query
	* @param object $widget
	* 
	* @return void
	*/
	public function query_results_not_found( $query, $widget ) {
		$posts_count = $query->found_posts;
		if ( $posts_count === 0 ) {
			$settings = $widget->get_settings();
			if ( $settings['show_empty_message'] == 'yes' ) {
				echo  wp_kses( '<div class="app-posts-not-found">' . $settings['custom_empty_message'] . '</div>', wp_kses_allowed_html() );
			}
		}
	}

	/*
	* Add custom variables into global query object
	* 
	* @param object $query
	* 
	* @return object
	*/
	function custom_query_callback( $query, $widget ) {
		$keyword = sanitize_text_field( $_GET['keyword'] ?? '' );
		if ( ! empty( $keyword ) ) {
			$query->query_vars['s'] = $keyword;
		}

		$year = sanitize_text_field( $_GET['_year'] ?? '' );
		if ( is_int( intval( $year ) ) ) {
			$query->query_vars['year'] = $year;

			$month = sanitize_text_field( $_GET['month'] ?? '' );
			if ( is_int( intval( $month ) ) ) {
				$query->query_vars['monthnum'] = $month;
			}
		}
	
		$page_num = absint( sanitize_text_field( $_GET['page_num'] ?? 0 ) );
		$page_num = ( $page_num === 0 ) ? 1 : $page_num;
		$query->query_vars['paged'] = $page_num;

		$term = get_term( sanitize_text_field( $_GET['term_id'] ?? '' ) );
		if ( ! is_wp_error( $term ) ) {
			$taxonomy = sanitize_title( $_GET['taxonomy'] ?? '' );
			$query->query_vars[ $taxonomy ] = $term->slug ?? '';
			$query->tax_query->queries[0] = [
				'taxonomy' => $taxonomy,
				'terms'    => [ $term->slug ?? '' ],
				'field'    => 'slug',
				'operator' => 'IN'
			];

			$query->tax_query->queried_terms[ $taxonomy ] = [
				'terms'    => [ $term->slug ?? '' ],
				'field'    => 'slug'
			];
		}

		return $query;
	}

	/*
	* Clean buffer before widget content is generated
	* 
	* @param object $element
	* 
	* @return void
	*/
	public function before_posts_render( $element ) {
		$action   = sanitize_text_field( $_GET['action'] ?? '' );
		$is_ajax  = absint( sanitize_text_field( $_GET['is_ajax'] ?? 0 ) );
		$settings = $element->get_settings();
		$query_id = $settings['posts_query_id'] ?? '';
		if ( $action == 'filter_posts_widget' && $is_ajax === 1 ) {
			add_action( "elementor/query/{$query_id}", [ $this, 'custom_query_callback' ], 10, 2 );
			ob_end_clean();
			ob_start();
			
		}
	}

	/*
	* Clean buffer after widget content is generated
	* and get raw html data to return as JSON
	* 
	* @param object $element
	* 
	* @return void
	*/
	public function ajax_response_after( $element ) {
		$action   = sanitize_text_field( $_GET['action'] ?? '' );
		$is_ajax  = absint( sanitize_text_field( $_GET['is_ajax'] ?? 0 ) );
		$settings = $element->get_settings();
		$query_id = $settings['posts_query_id'] ?? '';

		if ( 'posts' === $element->get_name() && $action === 'filter_posts_widget' && $is_ajax === 1 ) {
			ob_end_clean();

			$output = $element->get_raw_data( true );
			wp_send_json_success( $output['htmlCache'] );
			exit;
		}
	}
}