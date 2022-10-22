<?php
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ZR_Posts_Filters extends Widget_Base {

	public function get_name() {
		return 'zr_posts_filters';
	}

	public function get_title() {
		return esc_html__( 'Posts Filter', 'zr-elementor' );
	}

	public function get_icon() {
		return 'eicon-filter';
	}

	public function get_keywords() {
		return [ 'posts', 'posts widget', 'filters' ];
	}

	public function get_script_depends() {
		wp_register_script( 'zr-posts-filter', ZR_PLUGIN_URL . 'assets/js/posts_filter.js', array( 'jquery' ), NULL, true );
		return [ 'zr-posts-filter' ];
	}
	
	public function get_style_depends() {
		wp_register_style( 'zr-main', ZR_PLUGIN_URL . 'assets/css/main.css' );
		return [ 'zr-main' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Filter Options', 'zr-elementor' ),
				'type' => Controls_Manager::SECTION,
			]
		);

		$this->add_control(
			'filter_type',
			[
				'label' => esc_html__( 'Filter Type', 'zr-elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'category',
				'options' => [
					'category' => esc_html__( 'Category / Taxonomy', 'zr-elementor' ),
					'archive' => esc_html__( 'Date Archive', 'zr-elementor' ),
				],
				'frontend_available' => true,
				'prefix_class' => 'posts-filter--type-',
			]
		);

		$posttypes = $this->get_post_types();

		$this->add_control(
			'filter_post_type',
			[
				'label' => esc_html__( 'Post Type', 'zr-elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => $posttypes,
				'frontend_available' => true
			]
		);
		
		foreach( $posttypes as $key => $posttype ) {
			$this->add_control(
				'selected_taxonomy_' . $key,
				[
					'label' => esc_html__( 'Taxonomy', 'zr-elementor' ),
					'type' => Controls_Manager::SELECT,
					'default' => 'category',
					'options' => $this->get_taxonomies( $key ),
					'frontend_available' => true,
					'condition' => [
						'filter_type' => 'category',
						'filter_post_type' => $key,
					]
				]
			);
		}

		$this->add_control(
			'archive_filter',
			[
				'label' => esc_html__( 'Archive Type', 'zr-elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yearly',
				'options' => [
					'yearly' => esc_html__( 'Yearly', 'zr-elementor' ),
					'monthly' => esc_html__( 'Monthly', 'zr-elementor' )
				],
				'frontend_available' => true,
				'condition' => [
					'filter_type' => 'archive',
				],
			]
		);

		$this->add_control(
			'display_type',
			[
				'label' => esc_html__( 'Display Type', 'zr-elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'html',
				'options' => [
					'html' => esc_html__( 'HTML', 'zr-elementor' ),
					'option' => esc_html__( 'Select', 'zr-elementor' )
				],
				'prefix_class' => 'posts-filter--display-',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'ajax_load',
			[
				'label'        => esc_html__( 'Load Result via Ajax', 'zr-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'zr-elementor' ),
				'label_off'    => esc_html__( 'No', 'zr-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'prefix_class' => 'posts-filter--ajax-',
				'render_type' => 'template',
			]
		);
	
		$this->add_control(
			'target_query_id',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => esc_html__( 'Target Query ID', 'zr-elementor' ),
				'description' => esc_html__( 'Target posts widget to render search results via ajax.', 'zr-elementor' )
			]
		);

		$this->end_controls_section();
	}

	private function get_taxonomies( $post_type ) {
		$args = [
			'object_type' => [ $post_type ]
		];

		$taxonomies = get_taxonomies( $args, 'objects' ); 
		$data       = [];

		foreach( $taxonomies as $taxonomy ) {
			$data[ $taxonomy->name ] = $taxonomy->label;
		}

		return $data;
	}

	public function get_archived_posts( $args ) {
		$html = '';
		$atts = array_merge( [
			'type'            => 'yearly',
			'limit'           => '',
			'format'          => 'html', 
			'before'          => '',
			'after'           => '',
			'show_post_count' => false,
			'echo'            => 0,
			'order'           => 'DESC',
			'post_type'       => 'post'
		], $args );

		$archives = wp_get_archives( $atts );

		if ( $args['format'] === 'option' ) {
			$archives = '<option>All</option>' . $archives;
			$html .= '<select data-post-type="'.$args['post_type'].'">' . $archives . '</select>';
		} else {
			$html .= $archives;
		}
		
		return $html;
	}

	public function get_post_types() {
		$types = [];
		$post_types = get_post_types( [ 'capability_type' => 'post', 'public' => 1 ], 'objects' );

		foreach( $post_types as $post_type ) {
			$types[ $post_type->name ] = $post_type->label;
		}

		return $types;
	}

	public function get_terms( $args ) {
		$terms = [];
		$terms = get_terms( [
			'taxonomy' => $args['taxonomy']
		] );

		return $terms;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$taxonomy = $settings['selected_taxonomy_' . $settings['filter_post_type']];

		$html = '<div data-taxonomy="'. $taxonomy .'" data-targetid="'. $settings['target_query_id'] . '">';
		
		if ( $settings['display_type'] === 'html' ) {
			$html .= '<a href="#">All</a> ';
		}

		if ( $settings['filter_type'] === 'archive' ) {
			$args = [
				'type'      => $settings['archive_filter'],
				'post_type' => $settings['filter_post_type'],
				'format'    => $settings['display_type'],
				'year'      => trim( $_GET['_year'] ),
				'monthnum'  => trim( $_GET['month'] ),
			];
			$html .= $this->get_archived_posts( $args );
		}else{
			$args = [
				'object_type' => [ $settings['filter_post_type'] ],
				'taxonomy' => $taxonomy
			];

			$terms = $this->get_terms( $args );

			if ( $settings['display_type'] === 'option' ) {
				$html .= $this->build_select( $terms );
			} else {
				$html .= $this->build_link( $terms );
			}
		}
		
		$html .= '</div>';

		echo $html;
	}

	public function build_select( $terms ) {
		$select = '<select name="data-termid">';
		$select .= '<option value="">All </option>';
		foreach( $terms as $term ) {
			$select .= '<option value="'. $term->term_id .'">'. $term->name .'</option>';
		}
		$select .= '</select>';
		return $select;
	}

	public function build_link( $terms ) {
		$links = '';
		foreach( $terms as $term ) {
			$links .= '<li><a href="'. get_term_link( $term->term_id ) .'" data-termid="'. $term->term_id .'">'. $term->name .'</a></li>';
		}

		return $links;
	}
}