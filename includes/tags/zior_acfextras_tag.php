<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use \Elementor\Core\DynamicTags\Tag;
use \Elementor\Modules\DynamicTags\Module;
use ElementorPro\Plugin;

/**
 * Elementor Dynamic Tag - Additional ACF Tags
 *
 * Elementor dynamic tag that returns an Additional ACF tags.
 *
 * @since 0.1.3
 */
class ZIOR_ACFExtras_Tag extends Tag {

	/**
	 * Get dynamic tag name.
	 *
	 * Retrieve the name of the Additional ACF tag.
	 *
	 * @since 0.1.3
	 * @access public
	 * @return string Dynamic tag name.
	 */
	public function get_name() {
		return 'zior-acf';
	}

	/**
	 * Get dynamic tag title.
	 *
	 * Returns the title of the Additional ACF tag.
	 *
	 * @since 0.1.3
	 * @access public
	 * @return string Dynamic tag title.
	 */
	public function get_title() {
		return esc_html__( 'ACF Extras', 'zior-elementor' );
	}

	/**
	 * Get dynamic tag groups.
	 *
	 * Retrieve the list of groups the Additional ACF tag belongs to.
	 *
	 * @since 0.1.3
	 * @access public
	 * @return array Dynamic tag groups.
	 */
	public function get_group() {
		return [ 'acf' ];
	}

	/**
	 * Get dynamic tag categories.
	 *
	 * Retrieve the list of categories the Additional ACF tag belongs to.
	 *
	 * @since 0.1.3
	 * @access public
	 * @return array Dynamic tag categories.
	 */
	public function get_categories() {
		return [
			Module::TEXT_CATEGORY,
			Module::POST_META_CATEGORY,
		];
	}

	/**
	 * Register dynamic tag controls.
	 *
	 * Add input fields to allow the user to customize the Additional ACF tag settings.
	 *
	 * @since 0.1.3
	 * @access protected
	 * @return void
	 */
	protected function register_controls() {
		$this->add_control(
			'acf_key',
			[
				'label' => esc_html__( 'Key', 'zior-elementor' ),
				'type' => 'select',
				'groups' => self::get_control_options( $this->get_supported_fields() ),
			]
		);
	}

	/**
	 * @param array $types
	 *
	 * @return array
	 */
	public static function get_control_options( $types ) {
		// ACF >= 5.0.0
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$acf_groups = acf_get_field_groups();
		} else {
			$acf_groups = apply_filters( 'acf/get_field_groups', [] );
		}

		$groups = [];

		$options_page_groups_ids = [];

		if ( function_exists( 'acf_options_page' ) ) {
			$pages = acf_options_page()->get_pages();
			foreach ( $pages as $slug => $page ) {
				$options_page_groups = acf_get_field_groups( [
					'options_page' => $slug,
				] );

				foreach ( $options_page_groups as $options_page_group ) {
					$options_page_groups_ids[] = $options_page_group['ID'];
				}
			}
		}

		foreach ( $acf_groups as $acf_group ) {
			// ACF >= 5.0.0
			if ( function_exists( 'acf_get_fields' ) ) {
				if ( isset( $acf_group['ID'] ) && ! empty( $acf_group['ID'] ) ) {
					$fields = acf_get_fields( $acf_group['ID'] );
				} else {
					$fields = acf_get_fields( $acf_group );
				}
			} else {
				$fields = apply_filters( 'acf/field_group/get_fields', [], $acf_group['id'] );
			}

			$options = [];

			if ( ! is_array( $fields ) ) {
				continue;
			}

			$has_option_page_location = in_array( $acf_group['ID'], $options_page_groups_ids, true );
			$is_only_options_page = $has_option_page_location && 1 === count( $acf_group['location'] );

			foreach ( $fields as $field ) {
				if ( ! in_array( $field['type'], $types, true ) ) {
					continue;
				}

				// Use group ID for unique keys
				if ( $has_option_page_location ) {
					$key = 'options:' . $field['name'];
					$options[ $key ] = esc_html__( 'Options', 'elementor-pro' ) . ':' . $field['label'];
					if ( $is_only_options_page ) {
						continue;
					}
				}

				$key = $field['key'] . ':' . $field['name'];
				$options[ $key ] = $field['label'];
			}

			if ( empty( $options ) ) {
				continue;
			}

			if ( 1 === count( $options ) ) {
				$options = [ -1 => ' -- ' ] + $options;
			}

			$groups[] = [
				'label' => $acf_group['title'],
				'options' => $options,
			];
		} // End foreach().

		return $groups;
	}

	public function get_supported_fields() {
		return [
			'image',
			'file',
			'page_link',
			'post_object',
			'relationship',
			'taxonomy',
			'url',
		];
	}

	public function get_tag_value_field( $key ) {
		$key = $this->get_settings( $key );
		if ( ! empty( $key ) ) {
			list( $field_key, $meta_key ) = explode( ':', $key );
			$document = Plugin::elementor()->documents->get_current();

			if ( 'options' === $field_key ) {
				$field = get_field_object( $meta_key, $field_key );
			} elseif ( ! empty( $document ) && 'loop-item' == $document::get_type() ) {
				$field = get_field_object( $field_key, get_the_ID() );
			} else {
				$field = get_field_object( $field_key, get_queried_object() );
			}

			return $field;
		}

		return [];
	}

	/**
	 * Render tag output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 0.1.3
	 * @access public
	 * @return void
	 */
	public function render() {
		$value = '';
		// Make sure that ACF if installed and activated
		if ( ! function_exists( 'get_field' ) ) {
			echo 0;
			return;
		}
		
		$field = $this->get_tag_value_field( 'acf_key' );

		if ( is_array( $field['value'] ) && $field['return_format'] === 'object' ) {
			$value = array_column( $field['value'], 'ID' );
			$value = implode( ',', $value );
		} elseif ( is_array( $field['value'] ) && $field['return_format'] === 'id' ) {
			$value = implode( ',', $field['value'] );
		}else{
			$value = $field['value'];
		}

		if ( is_array( $value ) ) {
			$value = implode( ',', $value );
		}

		echo $value;
	}
}