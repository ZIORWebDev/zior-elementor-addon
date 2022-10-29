<?php
use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ZIOR_Posts_Filters extends Widget_Base {

	public function get_name() {
		return 'zior_posts_filters';
	}

	public function get_title() {
		return __( 'Posts Filter', 'zior-elementor' );
	}

	public function get_icon() {
		return 'eicon-filter';
	}

	public function get_keywords() {
		return [ 'posts', 'posts widget', 'filters' ];
	}

	public function get_script_depends() {
		wp_register_script( 'zior-posts-filter', ZIOR_PLUGIN_URL . 'assets/js/posts-filter.js', array( 'jquery' ), NULL, true );
		return [ 'zior-posts-filter' ];
	}
	
	public function get_style_depends() {
		wp_register_style( 'zior-main', ZIOR_PLUGIN_URL . 'assets/css/main.css' );
		return [ 'zior-main' ];
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			[
				'label' => __( 'Filter Options', 'zior-elementor' ),
				'type'  => Controls_Manager::SECTION,
			]
		);

		$this->add_control(
			'filter_type',
			[
				'label'              => __( 'Filter Type', 'zior-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'category',
				'frontend_available' => true,
				'prefix_class'       => 'posts-filter--type-',
				'options'            => [
					'category'       => __( 'Category / Taxonomy', 'zior-elementor' ),
					'archive'        => __( 'Date Archive', 'zior-elementor' ),
				],
			]
		);

		$posttypes = $this->get_post_types();

		$this->add_control(
			'filter_post_type',
			[
				'label'              => __( 'Post Type', 'zior-elementor' ),
				'type'               => Controls_Manager::SELECT,
				'default'            => 'post',
				'options'            => $posttypes,
				'frontend_available' => true
			]
		);
		
		foreach( $posttypes as $key => $posttype ) {
			$this->add_control(
				'selected_taxonomy_' . $key,
				[
					'label'                => __( 'Taxonomy', 'zior-elementor' ),
					'type'                 => Controls_Manager::SELECT,
					'default'              => 'category',
					'options'              => $this->get_taxonomies( $key ),
					'frontend_available'   => true,
					'condition'            => [
						'filter_type'      => 'category',
						'filter_post_type' => $key,
					]
				]
			);
		}

		$this->add_control(
			'archive_filter',
			[
				'label' => __( 'Archive Type', 'zior-elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'yearly',
				'options' => [
					'yearly' => __( 'Yearly', 'zior-elementor' ),
					'monthly' => __( 'Monthly', 'zior-elementor' )
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
				'label' => __( 'Display Type', 'zior-elementor' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'html',
				'options' => [
					'html' => __( 'HTML', 'zior-elementor' ),
					'option' => __( 'Select', 'zior-elementor' )
				],
				'prefix_class' => 'posts-filter--display-',
				'frontend_available' => true
			]
		);

		$this->add_control(
			'ajax_load',
			[
				'label'        => __( 'Load Result via Ajax', 'zior-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'zior-elementor' ),
				'label_off'    => __( 'No', 'zior-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'prefix_class' => 'posts-filter--ajax-',
				'render_type'  => 'template',
			]
		);
	
		$this->add_control(
			'target_query_id',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => __( 'Target Query ID', 'zior-elementor' ),
				'description' => __( 'Target posts widget to render search results via ajax.', 'zior-elementor' )
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
			$html .= '<li><a href="#">All</a> </li>';
		}

		if ( $settings['filter_type'] === 'archive' ) {
			$args = [
				'type'      => $settings['archive_filter'],
				'post_type' => $settings['filter_post_type'],
				'format'    => $settings['display_type'],
				'year'      => isset( $_GET['_year'] ) ? trim( $_GET['_year'] ) : '',
				'monthnum'  => isset( $_GET['month'] ) ? trim( $_GET['month'] ) : '',
			];
			$html .= $this->get_archived_posts( $args );
		} else {
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

		echo wp_kses( $html, wp_kses_allowed_html() );
	}

	public function build_select( $terms ) {
		$select = '<select name="data-termid">';
		$select .= '<option value="">All </option>';
		foreach( $terms as $term ) {
			$select .= '<option value="'. esc_attr( $term->term_id ) .'">'. $term->name .'</option>';
		}
		$select .= '</select>';
		return $select;
	}

	public function build_link( $terms ) {
		$links = '';
		foreach( $terms as $term ) {
			$links .= '<li><a href="'. get_term_link( $term->term_id ) .'" data-termid="'. $term->term_id .'">'. $term->name .'</a> <span clss="taxonomy-description">' . $term->description . '</span></li>';
		}

		return $links;
	}
}