<?php
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ZIOR_Posts_Addon {
	public function __construct() {
		add_action( 'elementor/element/posts/section_query/after_section_end', [ $this, 'posts_form_widget_controls' ], 10, 2 );

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

		$element->start_controls_section(
			'advanced_query_filter_section',
			[
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				'label' => esc_html__( 'Advanced Query Filter', 'zior-elementor' ),
			]
		);

		/**
		 * Meta queries repeater
		 */
		$repeater = new Repeater();
		$repeater->add_control(
			'aqf_meta_query_relation',
			[
				'label'   => __( 'Query Relation', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'or',
				'options' => [
					'or'  => __( 'OR', 'zior-elementor' ),
					'and' => __( 'AND', 'zior-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'aqf_meta_query_key',
			[
				'label'   => __( 'Meta Key', 'zior-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'aqf_meta_query_value',
			[
				'label'   => __( 'Meta Value', 'zior-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'aqf_meta_query_compare',
			[
				'label'   => __( 'Compare', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'LIKE',
				'options' => $this->getSupportedOperators(),
			]
		);

		$element->add_control(
			'aqf_meta_queries',
			[
				'label'       => __( 'Meta Queries', 'zior-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'show_label'  => true,
				'fields'      => $repeater->get_controls(),
				'default'     => [],
				'separator'    => 'after',
			]
		);

		/**
		 * Taxonomy queries repeater
		 */
		$repeater = new Repeater();
		$repeater->add_control(
			'aqf_tax_query_relation',
			[
				'label'   => __( 'Query Relation', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'or',
				'options' => [
					'or'  => __( 'OR', 'zior-elementor' ),
					'and' => __( 'AND', 'zior-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'aqf_tax_query_taxonomy',
			[
				'label'   => __( 'Taxonomy', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'options' => $this->get_taxonomies(),
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'aqf_tax_query_field',
			[
				'label'   => __( 'Field', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'term_id',
				'options' => [
					'term_id'  => __( 'Term ID', 'zior-elementor' ),
					'name' => __( 'Term Name', 'zior-elementor' ),
					'slug' => __( 'Term Slug', 'zior-elementor' ),
					'term_taxonomy_id' => __( 'Term Taxonomy ID', 'zior-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'aqf_tax_query_value',
			[
				'label'   => __( 'Value', 'zior-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'aqf_tax_query_compare',
			[
				'label'   => __( 'Compare', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'LIKE',
				'options' => $this->getSupportedOperators(),
			]
		);

		$element->add_control(
			'aqf_tax_queries',
			[
				'label'       => __( 'Taxonomy Queries', 'zior-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'show_label'  => true,
				'fields'      => $repeater->get_controls(),
				'default'     => [],
			]
		);

		$element->add_control(
			'show_empty_message',
			[
				'label'        => __( 'Empty message?', 'zior-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'zior-elementor' ),
				'label_off'    => __( 'No', 'zior-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'render_type'  => 'template',
				'separator'    => 'before',
			]
		);

		$element->add_control(
			'custom_empty_message',
			[
				'type'        => \Elementor\Controls_Manager::WYSIWYG,
				'rows'        => 2,
				'label'       => __( 'Custom Empty message', 'zior-elementor' ),
				'description' => __( 'Show message when posts result is empty', 'zior-elementor' ),
				'condition'   => [
					'show_empty_message' => 'yes',
				],
			]
		);

		$element->end_controls_section();
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
	
		$page_num = absint( sanitize_text_field( $_GET['page_num'] ?? 1 ) );
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
				'terms' => [ $term->slug ?? '' ],
				'field' => 'slug'
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
		
		if ( $action == 'filter_posts_widget' ) {
			add_action( "elementor/query/{$query_id}", [ $this, 'custom_query_callback' ], 10, 2 );
		}

		if ( $action == 'filter_posts_widget' && $is_ajax === 1 ) {
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

	public function getSupportedOperators() {
		return [
			'like' => 'LIKE',
			'!='   => 'NOT EQUAL',
			'<='   => 'LESS THAN OR EQUAL',
			'>='   => 'GREATER THAN OR EQUAL',
			'<'    => 'LESS THAN',
			'>'    => 'GREATER THAN',
		];
	}

	public function get_taxonomies() {
		$taxonomies = [];
		foreach( get_taxonomies( [], 'objects' ) as $key => $taxonomy ) {
			$type = $taxonomy->object_type[0] ?? '';
			$taxonomies[ $key ] = $taxonomy->label . '(' . $type . ')';
		}

		return $taxonomies;
	}
}