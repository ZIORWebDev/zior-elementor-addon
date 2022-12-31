<?php
/**
 * ZIOR Elementor Addon
 *
 * Plugin Name: ZIOR Elementor Addon
 * Description: Custom addon for elementor and elementor pro.
 * Version: 0.1.7
 * Author:      Rey Calantaol
 * Author URI:  https://github.com/ziorweb-dev
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: zior-elementor
 * Requires at least: 4.9
 * Tested up to: 6.1
 * Requires PHP: 7.4
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main ZIOR_Elementor_Addon class.
 */
final class ZIOR_Elementor_Addon {
	/**
	 * @var string
	 */
	protected $version = '0.1.7';

	/**
	 * @var ZIOR_Elementor_Addon
	 */
	protected static $_instance;

	function __construct() { }
	
	/**
	 * @return ZIOR_Elementor_Addon
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
			self::$_instance->run();
		}

		return self::$_instance;
	}

	public function run() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', [ $this, 'missing_plugin_notice' ] );
		}

		add_action( 'elementor/init', [ $this, 'elementor_init' ] );
	}
	
	/**
	 * Only load the addon on the Elementor core hook, ensuring the plugin is active.
	 */
	public function elementor_init() {
		$this->setup_constants();
		require_once ZIOR_PLUGIN_DIR . 'includes/functions.php';
		require_once ZIOR_PLUGIN_DIR . 'includes/filters.php';
		require_once ZIOR_PLUGIN_DIR . 'includes/actions.php';
		require_once ZIOR_PLUGIN_DIR . 'includes/widgets/widgets.php';
		require_once ZIOR_PLUGIN_DIR . 'includes/addons/addons.php';
		require_once ZIOR_PLUGIN_DIR . 'includes/tags/tags.php';
		require_once ZIOR_PLUGIN_DIR . 'includes/shortcodes/shortcodes.php';
	}

	/**
	 * Setup plugin constants
	 *
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'ZIOR_VERSION' ) ) {
			define( 'ZIOR_VERSION', $this->version );
		}
		// Plugin Folder Path.
		if ( ! defined( 'ZIOR_PLUGIN_DIR')) {
			define( 'ZIOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'ZIOR_PLUGIN_URL' ) ) {
			define( 'ZIOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'ZIOR_PLUGIN_FILE' ) )
		{
			define( 'ZIOR_PLUGIN_FILE', __FILE__ );
		}
	}
	
	public function missing_plugin_notice() {
		echo '<div class="error"><p>';
		printf( 'The <stron>%s</strong> addon plugin cannot be activated because Elementor is missing.', esc_html( 'ZIOR Elementor' ) );
		echo '</p></div>';

		deactivate_plugins( plugin_basename( __FILE__ ) );
	}
}

/**
 * Start the addon.
 *
 * @return ZIOR_Elementor_Addon
 */
function ZIOR_Elementor_Addon_Initialize() {
	return ZIOR_Elementor_Addon::instance();
}

ZIOR_Elementor_Addon_Initialize();