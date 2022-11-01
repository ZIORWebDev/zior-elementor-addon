<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ZIOR_SearchForm_Addon {
	public function __construct() {
		$this->init_hooks();
	}

	public function init_hooks() {
		add_action( 'elementor/element/search-form/search_content/before_section_end', [ $this, 'search_form_widget_controls' ], 10, 2 );
		add_action( 'elementor_pro/search_form/after_input', [ $this, 'search_form_render_fields' ] );
	}

	/*
	* Add switcher control into search form widget
	* 
	* @param array $element
	* @param array $args
	* 
	* @return void
	*/
	public function search_form_widget_controls( $element, $args ) {
		$element->add_control(
			'ajax_load',
			[
				'label'        => __( 'Load Result via Ajax', 'zior-elementor' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'zior-elementor' ),
				'label_off'    => __( 'No', 'zior-elementor' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'prefix_class' => 'elementor-search-form--ajax-load-',
				'render_type'  => 'template',
			]
		);

		$element->add_control(
			'target_query_id',
			[
				'type'        => \Elementor\Controls_Manager::TEXT,
				'label'       => __( 'Target Query ID', 'zior-elementor' ),
				'description' => __( 'Target posts widget to render search results via ajax.', 'zior-elementor' ),
				'condition'   => [
					'ajax_load' => 'yes',
				],
			]
		);
	}

	/*
	* Add target_query_id hidden field to search form when the search result is to be loaded via ajax
	* 
	* @param object $widget
	* 
	* @return void
	*/
	public function search_form_render_fields( $widget ) {
		$settings = $widget->get_settings_for_display();
		$query_id = esc_attr( $settings['target_query_id'] );
		if ( ! empty( $query_id ) ) {
			echo "<input type='hidden' name='target_query_id' value='{$query_id}' />";
		}
	}
}