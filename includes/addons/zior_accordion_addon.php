<?php
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ZIOR_Accordion_Addon {
	public function __construct() {
		add_action( 'elementor/element/accordion/section_title/after_section_end', [ $this, 'accordion_widget_controls' ], 10, 2 );
		add_action( 'elementor/frontend/widget/before_render', [ $this, 'before_accordion_render' ], 99 );
	}

	/*
	* Register advanced control
	* 
	* @param array $element
	* @param array $args
	* 
	* @return void
	*/
	public function accordion_widget_controls( $element, $args ) {

		$element->start_controls_section(
			'advanced_cpt_section',
			[
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
				'label' => esc_html__( 'Advanced Accordion Settings', 'zior-elementor' ),
			]
		);

		$element->add_control(
			'acs_enabled',
			[
				'label'        => __( 'Enabled?', 'zior-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'No', 'zior-elementor' ),
				'label_off'    => __( 'Yes', 'zior-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'render_type'  => 'template',
			]
		);
		
		$element->add_control(
			'acs_post_type',
			[
				'label'   => __( 'Post Type', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => $this->get_post_types(),
				'description' => 'Select custom post type to loop.',
				'render_type'  => 'template',
			]
		);

		$element->add_control(
			'acs_post_per_page',
			[
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'label'   => esc_html__( 'Post Per Page', 'textdomain' ),
				'min'     => 1,
				'max'     => 999,
				'step'    => 1,
				'default' => 10,
			]
		);

		$element->add_control(
			'acs_date',
			[
				'label'       => __( 'Date', 'zior-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'anytime',
				'render_type' => 'template',
				'options'     => [
					'anytime'    => 'All',
					'today'      => 'Past Day',
					'week'       => 'Past Week',
					'month'      => 'Past Month',
					'quarter'    => 'Past Quarter',
					'year'       => 'Past Year',
					'exact'      => 'Custom',
				],
			]
		);

		$element->add_control(
			'acs_date_before',
			[
				'label'       => esc_html__( 'Before', 'zior-elementor' ),
				'type'        => Controls_Manager::DATE_TIME,
				'post_type'   => '',
				'label_block' => false,
				'multiple'    => false,
				'placeholder' => esc_html__( 'Choose', 'zior-elementor' ),
				'description' => esc_html__( 'Setting a `Before` date will show all the posts published until the chosen date (inclusive).', 'zior-elementor' ),
				'condition'   => [
					'acs_date' => 'exact',
				],
			]
			);

		$element->add_control(
			'acs_date_after',
			[
				'label' => esc_html__( 'After', 'zior-elementor' ),
				'type' => Controls_Manager::DATE_TIME,
				'post_type' => '',
				'label_block' => false,
				'multiple' => false,
				'placeholder' => esc_html__( 'Choose', 'zior-elementor' ),
				'description' => esc_html__( 'Setting an `After` date will show all the posts published since the chosen date (inclusive).', 'zior-elementor' ),
				'condition' => [
					'acs_date' => 'exact'
				],
			]
		);

		$element->add_control(
			'acs_order_by',
			[
				'label'   => __( 'Order By', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'post_date',
				'options' => [
					'post_date'  => 'Date',
					'post_title' => 'Title',
					'menu_order' => 'Menu Order',
					'rand' => 'Random',
				],
				'render_type'  => 'template',
			]
		);

		$element->add_control(
			'acs_order',
			[
				'label'   => __( 'Order', 'zior-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'asc',
				'options' => [
					'asc'  => 'ASC',
					'desc' => 'DESC',
				],
				'render_type'  => 'template',
			]
		);

		$element->end_controls_section();
	}

	/*
	* Before accordion render
	* 
	* @param object $element
	* 
	* @return void
	*/
	public function before_accordion_render( $element ) {
		$settings = $element->get_settings();

		if ( 'accordion' === $element->get_name() && 'yes' === $settings['acs_enabled'] && ! empty( $settings['acs_post_type'] ) ) {
			


			$args = [
				'post_type'      => $settings['acs_post_type'],
				'post_status'    => 'publish',
				'posts_per_page' => $settings['acs_post_per_page'],
				'orderby'        => $settings['acs_order_by'],
				'order'          => $settings['acs_order'],
			];

			if ( 'anytime' !== $settings['acs_date'] ) {
				$args['date_query'] = $this->set_date_args( $settings );
			}
			
			$tabs = [];
			$data  = new WP_Query( $args );

			foreach( $data->posts as $post ) {
				$tabs[] = [
					'tab_title'   => get_the_title( $post ),
					'tab_content' => get_the_content( $post ),
					'_id'         => md5( $post->ID ),
					'__dynamic__' => [],
				];
			}

			$element->set_settings( 'tabs', $tabs );
		}
	}

	public function get_post_types() {
		$post_types = [];
		foreach( get_post_types( [], 'objects' ) as $post_type ) {
			$post_types[ $post_type->name ] = $post_type->label;
		}

		return $post_types;
	}

	public function get_taxonomies() {
		$taxonomies = [];
		foreach( get_taxonomies( [], 'objects' ) as $key => $taxonomy ) {
			$type = $taxonomy->object_type[0] ?? '';
			$taxonomies[ $key ] = $taxonomy->label . '(' . $type . ')';
		}

		return $taxonomies;
	}

	public function set_date_args( $settings ) {	
		$date_query = [];

		switch ( $settings['acs_date'] ) {
			case 'today':
				$date_query['after'] = '-1 day';
				break;
			case 'week':
				$date_query['after'] = '-1 week';
				break;
			case 'month':
				$date_query['after'] = '-1 month';
				break;
			case 'quarter':
				$date_query['after'] = '-3 month';
				break;
			case 'year':
				$date_query['after'] = '-1 year';
				break;
			case 'exact':
				$after_date = $this->get_widget_settings( 'date_after' );
				if ( ! empty( $after_date ) ) {
					$date_query['after'] = $after_date;
				}
				$before_date = $this->get_widget_settings( 'date_before' );
				if ( ! empty( $before_date ) ) {
					$date_query['before'] = $before_date;
				}
				$date_query['inclusive'] = true;
				break;
		}

		return $date_query;
	}
}