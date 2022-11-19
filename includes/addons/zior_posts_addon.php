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
		add_action( 'elementor/editor/before_enqueue_styles', [ $this, 'widget_editor_styles' ] );
	}

	/*
	* Custom editor style
	* 
	* @return void
	*/
	public function widget_editor_styles() {
		$assets[] = [
			'handle' => 'zior-editor',
			'type' => 'css',
			'path' => ZIOR_PLUGIN_URL . 'assets/css/',
			'name' => 'editor',
			'name' => 'editor',
		];
		zior_enqueue_assets( $assets );
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

		$element->add_control(
			'aqf_meta_query_relation',
			[
				'label'        => __( 'Meta Query Relation', 'zior-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'AND', 'zior-elementor' ),
				'label_off'    => __( 'OR', 'zior-elementor' ),
				'return_value' => 'AND',
				'default'      => 'OR',
				'render_type'  => 'template'
			]
		);

		/**
		 * Meta queries repeater
		 */
		$repeater = new Repeater();
		$repeater->add_control(
			'key',
			[
				'label'    => __( 'Meta Key', 'zior-elementor' ),
				'type'     => Controls_Manager::TEXT,
				'dynamic'  => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'value',
			[
				'label'    => __( 'Meta Value', 'zior-elementor' ),
				'type'     => Controls_Manager::TEXT,
				'dynamic'  => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'type',
			[
				'label'   => __( 'Data Type', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'CHAR',
				'options' => [
					'CHAR'          => 'CHAR',
					'NUMERIC'       => 'NUMERIC',
					'DECIMAL(10,2)' => 'DECIMAL(10,2)',
					'DATE'          => 'DATE',
				],
			]
		);

		$repeater->add_control(
			'compare',
			[
				'label'   => __( 'Compare', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'LIKE',
				'options' => $this->getSupportedOperators(),
				'description' => 'For (IN and NOT IN), enter comma separated values.'
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

		$element->add_control(
			'aqf_tax_query_relation',
			[
				'label'        => __( 'Taxonomy Query Relation', 'zior-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'AND', 'zior-elementor' ),
				'label_off'    => __( 'OR', 'zior-elementor' ),
				'return_value' => 'AND',
				'default'      => 'OR',
				'render_type'  => 'template'
			]
		);

		/**
		 * Taxonomy queries repeater
		 */
		$repeater = new Repeater();
		$repeater->add_control(
			'taxonomy',
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
			'field',
			[
				'label'   => __( 'Field', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'term_id',
				'classes' => 'zior-taxonomy-field',
				'options' => [
					'term_id'  => __( 'Term ID', 'zior-elementor' ),
					'name' => __( 'Term Name', 'zior-elementor' ),
					'slug' => __( 'Term Slug', 'zior-elementor' ),
					'term_taxonomy_id' => __( 'Term Taxonomy ID', 'zior-elementor' ),
				],
			]
		);

		$repeater->add_control(
			'terms',
			[
				'label'    => __( 'Terms', 'zior-elementor' ),
				'type'     => Controls_Manager::TEXT,
				'dynamic'  => [
					'active' => true,
				],
			]
		);

		$repeater->add_control(
			'operator',
			[
				'label'   => __( 'Compare', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'LIKE',
				'options' => $this->getSupportedOperators(),
				'description' => 'For (IN and NOT IN), enter comma separated values.'
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
			'post_ids',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'rows'        => 2,
				'label'       => __( 'Post Ids', 'zior-elementor' ),
				'description' => __( 'Filter posts by comma separated ids.', 'zior-elementor' ),
				'separator'    => 'before',
				'dynamic'  => [
					'active' => true,
				],
				'frontend_available' => true,
				'render_type'  => 'template',
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
		/**
		 * Advanced query filters
		 */
		$settings = $widget->get_settings_for_display();
		$meta_queries_relation = $settings['aqf_meta_query_relation'] ?? 'OR';
		$meta_queries = $settings['aqf_meta_queries'];
		$available_meta_queries = [];
		$convert_to_array = [ 'IN', 'NOT IN' ];

		foreach( $meta_queries as $meta_query ) {
			if ( ! empty( trim( $meta_query['key'] ) ) && ! empty( trim( $meta_query['value'] ) ) ) {
				$value = $meta_query['value'];
				if ( in_array( $meta_query['compare'], $convert_to_array ) ) {
					$value = array_map( 'trim', explode( ',', $value ) );
				}

				$available_meta_queries[] = [
					'key'     => $meta_query['key'],
					'value'   => $value,
					'type'    => $meta_query['type'],
					'compare' => $meta_query['compare'],
				];
			}
		}

		if ( ! empty( $available_meta_queries ) ) {
			$query->set( 'meta_query', [
				'relation' => ! empty( $meta_queries_relation ) ? $meta_queries_relation : 'OR',
				$available_meta_queries
			] );
		}

		$tax_queries_relation = $settings['aqf_tax_query_relation'];
		$tax_queries = $settings['aqf_tax_queries'];
		$available_tax_queries = [];

		foreach( $tax_queries as $tax_query ) {
			if ( ! empty( trim( $tax_query['taxonomy'] ) ) && ! empty( trim( $tax_query['terms'] ) ) ) {
				$terms = $tax_query['terms'];
				if ( in_array( $tax_query['operator'], $convert_to_array ) ) {
					$terms = array_map( 'trim', explode( ',', $terms ) );
				}
				$available_tax_queries[] = [
					'taxonomy' => $tax_query['taxonomy'],
					'field'    => $tax_query['field'],
					'terms'    => $terms,
					'operator' => $tax_query['operator'],
				];
			}
		}

		$term_id = trim( sanitize_text_field( $_GET['term_id'] ?? '' ) );
		$taxonomy = trim( sanitize_title( $_GET['taxonomy'] ?? '' ) );
		if ( ! empty( $term_id ) && ! empty( $taxonomy ) ) {
			$available_tax_queries[] = [
				'taxonomy' => $taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
				'operator' => '=',
			];
		}
		
		if ( ! empty( $available_tax_queries ) ) {
			$query->set( 'tax_query', [
				'relation' => ! empty( $tax_queries_relation ) ? $tax_queries_relation : 'OR',
				$available_tax_queries
			] );

			// Build queried terms
			$queried_terms = [];
			foreach( $available_tax_queries as $tax_query ) {
				$queried_terms[] = [
					$tax_query['taxonomy'] => [
						'terms' => $tax_query['terms'],
						'field' => $tax_query['field']
					]
				];
			}
			$query->tax_query->queried_terms = $queried_terms;
		}

		/**
		 * Filter posts by array of post ids
		 */
		$post_ids = $settings['post_ids'];
		if ( ! empty( $post_ids ) ) {
			$post_ids = explode( ',', $post_ids );
			$query->set( 'post__in', $post_ids );
		}

		/**
		 * Filter posts by query vars
		 */
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

		if ( ! empty( $query_id ) ) {
			add_action( "elementor/query/{$query_id}", [ $this, 'custom_query_callback' ], 40, 2 );
		}

		// Set query id as selector reference for search form and posts filter to interact
		if ( 'posts' === $element->get_name() && ! empty( $query_id ) ) {
			$element->add_render_attribute( '_wrapper', 'data-query-id', $query_id );
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
			'='           => 'EQUAL',
			'!='          => 'NOT EQUAL',
			'<='          => 'LESS THAN OR EQUAL',
			'>='          => 'GREATER THAN OR EQUAL',
			'<'           => 'LESS THAN',
			'>'           => 'GREATER THAN',
			'LIKE'        => 'LIKE',
			'IN'          => 'IN',
			'NOT IN'      => 'NOT IN',
			'BETWEEN'     => 'BETWEEN',
			'NOT BETWEEN' => 'NOT BETWEEN',
			'NOT LIKE'    => 'NOT LIKE',
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