<?php
/*
 * Enqueue scripts in the frontend
 * 
 * @return void
 */
function zior_frontend_scripts() {
	wp_enqueue_script( 'zior-main', ZIOR_PLUGIN_URL . 'assets/js/main.js', array( 'jquery' ), NULL, true );
	
	$options = [
		'ajax_url' => admin_url( 'admin-ajax.php' )
	];

	wp_localize_script( 'zior-main', 'zior', $options );

}
add_action( 'wp_enqueue_scripts', 'zior_frontend_scripts', 10 );

/*
 * Add switcher control into search form widget
 * 
 * @param array $element
 * @param array $args
 * 
 * @return void
 */
function zior_search_form_widget_controls( $element, $args ) {
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
add_action( 'elementor/element/search-form/search_content/before_section_end', 'zior_search_form_widget_controls', 10, 2 );

/*
 * Add custom variables into global query object
 * 
 * @param object $query
 * 
 * @return object
 */
function zior_custom_query_callback( $query ) {
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

/*
 * Add _year and _month query strings to archive links
 * 
 * @param object $widget
 * 
 * @return void
 */
function zior_posts_filters_before_render( $widget ) {
	if ( $widget->get_name() === 'zior_posts_filters' ) {
		add_filter( 'month_link', 'zior_month_link', 10, 3 );
		add_filter( 'year_link', 'zior_year_link', 10, 2 );		
	}else{
		remove_filter( 'month_link', 'zior_month_link', 10, 3 );
		remove_filter( 'year_link', 'zior_year_link', 10, 2 );		
	}
}
add_action( 'elementor/frontend/widget/before_render', 'zior_posts_filters_before_render' );

function zior_month_link( $monthlink, $year, $month ) {
	$separator = strpos( $monthlink, '?' ) === false ? '?' : '&';
	return $monthlink . $separator . '_year=' . $year . '&_month='  .$month;
}

function zior_year_link( $yearlink, $year ) {
	$separator = strpos( $yearlink, '?' ) === false ? '?' : '&';
	return $yearlink . $separator . '_year=' . $year;
}

function zior_elementor_loaded() {
	$action   = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
	$query_id = isset( $_GET['target_query_id'] ) ? sanitize_text_field( $_GET['target_query_id'] ) : '';
	if ( $action === 'filter_posts_widget' && ! empty( $query_id ) ) {
		add_action( "elementor/query/{$query_id}", 'zior_custom_query_callback' );
	}
}
add_action( 'elementor/frontend/before_render', 'zior_elementor_loaded' );

/*
 * Add target_query_id hidden field to search form when the search result is to be loaded via ajax
 * 
 * @param object $widget
 * 
 * @return void
 */
function zior_search_form_render_fields( $widget ) {
	$settings = $widget->get_settings_for_display();
	$query_id = esc_attr( $settings['target_query_id'] );
	if ( ! empty( $query_id ) ) {
		echo "<input type='hidden' name='target_query_id' value='{$query_id}' />";
	}
}
add_action( 'elementor_pro/search_form/after_input', 'zior_search_form_render_fields' );