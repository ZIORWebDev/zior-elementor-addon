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
		add_action( 'elementor/frontend/before_render', [ $this, 'before_posts_render' ], 99 );
		add_action( 'elementor/element/posts/section_query/before_section_end', [ $this, 'posts_form_widget_controls' ], 10, 2 );
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

	public function query_results_not_found( $query, $widget ) {
		$posts_count = $query->found_posts;

		if ( $posts_count === 0 ) {
			$settings = $widget->get_settings();

			if ( $settings['show_empty_message'] == 'yes' ) {
				echo '<div class="app-posts-not-found">' . wp_kses( $settings['custom_empty_message'], wp_kses_allowed_html() )  . '</div>';
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
	function custom_query_callback( $query ) {
		$keyword = sanitize_text_field( $_GET['keyword'] ?? '' );
		if ( ! empty( $keyword ) ) {
			$query->query_vars['s'] = $keyword;
		}

		$year = sanitize_text_field( $_GET['_year'] ?? '' );
		if ( is_int( $year ) ) {
			$query->query_vars['year'] = $year;

			$month = sanitize_text_field( $_GET['month'] ?? '' );
			if ( is_int( $month ) ) {
				$query->query_vars['monthnum'] = $month;
			}
		}
		
		$page_num = absint( sanitize_text_field( $_GET['page_num'] ?? '' ) );
		$page_num = ( $page_num === 0 ) ? 1 : $page_num;
		$query->query_vars['paged'] = trim( $page_num );

		$term = get_term( absint( $_GET['term_id'] ?? '' ) );
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

	public function before_posts_render( $element ) {
		$action   = sanitize_text_field( $_GET['action'] ?? '' );
		$query_id = sanitize_text_field( $_GET['target_query_id'] ?? '' );
		if ( $action == 'filter_posts_widget' && ! empty( $query_id ) ) {
			add_action( "elementor/query/{$query_id}", [ $this, 'custom_query_callback' ] );
			ob_end_clean();
			ob_start();
		}
	}

	public function ajax_response_after( $element ) {
		$action   = sanitize_text_field( $_GET['action'] ?? '' );
		$query_id = sanitize_text_field( $_GET['target_query_id'] ?? '' );
		$settings = $element->get_settings_for_display();

		if ( 'posts' === $element->get_name() && $action === 'filter_posts_widget' && $settings['posts_query_id'] === $query_id ) {
			$output = ob_get_contents();
			ob_end_clean();
			wp_send_json_success( $output );
			exit;
		}
	}
}