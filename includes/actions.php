<?php
function zr_frontend_scripts() {
	wp_enqueue_script( 'zr-main', ZR_PLUGIN_URL . 'assets/js/main.js', array( 'jquery' ), NULL, true );
	
	$options = [
		'ajax_url' => admin_url( 'admin-ajax.php' )
	];

	wp_localize_script( 'zr-main', 'zr', $options );

}
add_action( 'wp_enqueue_scripts', 'zr_frontend_scripts', 10 );

function zr_search_form_widget_controls( $element, $args ) {
	$element->add_control(
		'zr_ajax_load',
		[
			'label'        => esc_html__( 'Load Result via Ajax', 'zr-elementor' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => esc_html__( 'Yes', 'zr-elementor' ),
			'label_off'    => esc_html__( 'No', 'zr-elementor' ),
			'return_value' => 'yes',
			'default'      => 'no',
			'prefix_class' => 'elementor-search-form--ajax-load-',
			'render_type' => 'template',
		]
	);

	$element->add_control(
		'target_query_id',
		[
			'type'        => \Elementor\Controls_Manager::TEXT,
			'label'       => esc_html__( 'Target Query ID', 'zr-elementor' ),
			'description' => esc_html__( 'Target posts widget to render search results via ajax.', 'zr-elementor' ),
			'condition' => [
				'zr_ajax_load' => 'yes',
			],
		]
	);
}
add_action( 'elementor/element/search-form/search_content/before_section_end', 'zr_search_form_widget_controls', 10, 2 );

function zr_custom_query_callback( $query ) {
	
	if ( isset($_GET['keyword'] ) && trim( $_GET['keyword'] ) !== '' ) {
		$query->query_vars['s'] = trim( $_GET['keyword'] );
	}

	if ( isset($_GET['year'] ) && is_int( intval( $_GET['year'] ) ) ) {
		$query->query_vars['year'] = trim( $_GET['year'] );
	}
	
	if ( isset($_GET['month'] ) && is_int( intval( $_GET['month'] ) ) ) {
		$query->query_vars['monthnum'] = trim( $_GET['month'] );
	}

	if ( isset($_GET['page_num'] ) && trim( $_GET['page_num'] ) !== '' ) {
		$query->query_vars['paged'] = trim( $_GET['page_num'] );
	}

	$term = get_term( intval( $_GET['term_id'] ) );

	if ( isset( $_GET['term_id'] ) && intval( trim( $_GET['term_id'] ) ) > 0 ) {
		$query->query_vars[ trim( $_GET['taxonomy'] ) ] = $term->slug;
		$query->tax_query->queries[0] = [
			'taxonomy' => trim( $_GET['taxonomy'] ),
			'terms'    => [ $term->slug ],
			'field'    => 'slug',
			'operator' => 'IN'
		];

		$query->tax_query->queried_terms[ trim( $_GET['taxonomy'] ) ] = [
			'terms'    => [ $term->slug ],
			'field'    => 'slug'
		];
	}

	return $query;
}

function zr_elementor_loaded() {
	if ( isset( $_GET['action'] ) && trim( $_GET['action'] ) === 'filter_posts_widget' && isset( $_GET['target_query_id'] ) ) {
		$target_query_id = trim( $_GET['target_query_id'] );
		add_action( "elementor/query/{$target_query_id}", 'zr_custom_query_callback' );
	}
}
add_action( 'elementor/frontend/before_render', 'zr_elementor_loaded' );


function zr_search_form_render_fields( $widget ) {
	$settings = $widget->get_settings_for_display();
	echo "<input type='hidden' name='target_query_id' value='{$settings['target_query_id']}' />";
}

add_action( 'elementor_pro/search_form/after_input', 'zr_search_form_render_fields' );