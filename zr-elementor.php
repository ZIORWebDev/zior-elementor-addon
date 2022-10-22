<?php
/**
 * Plugin Name: ZR Elementor Addon
 * Description: Custom addon for elementor and elementor pro.
 * Version: 0.10
 *
 * Text Domain: zr-elementor
 *
 */
if ( ! class_exists( 'ZR_Elementor_Addon' ) ) :
	/**
	 * Main ZR_Elementor_Addon class.
	 */
	final class ZR_Elementor_Addon {
		/**
		 * @var string
		 */
		protected $version = '1.0';

		/**
		 * @var ZR_Elementor_Addon
		 */
		protected static $_instance;

		function __construct() { }
		
		/**
		 * @return ZR_Elementor_Addon
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
			require_once ZR_PLUGIN_DIR . 'includes/filters.php';
			require_once ZR_PLUGIN_DIR . 'includes/actions.php';
			add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ], 10 );
		}

		/**
		 * Setup plugin constants
		 *
		 */
		private function setup_constants() {

			// Plugin Folder Path.
			if ( ! defined( 'ZR_PLUGIN_DIR')) {
				define( 'ZR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'ZR_PLUGIN_URL' ) ) {
				define( 'ZR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'ZR_PLUGIN_FILE' ) )
			{
				define( 'ZR_PLUGIN_FILE', __FILE__ );
			}
		}
		
		public function missing_plugin_notice() { ?>
			<div class="error">
				<p><?php printf( 'The <stron>%s</strong> addon plugin cannot be activated because Elementor Pro is missing.', esc_html( 'ZR Elementor' ) ); ?></p>
			</div>
			<?php
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}

		public function register_elementor_widgets( $widgets ) {
			spl_autoload_register( function ( $class ) {
				include 'includes/widgets/' . strtolower( $class ) . '.php';
			});
	
			$widgets->register( new ZR_Slides() );
			$widgets->register( new ZR_Posts_Filters() );
		}
	}

endif;

/**
 * Start the addon.
 *
 * @return ZR_Elementor_Addon
 */
function ZR_Elementor_Addon_Initialize() {
	return ZR_Elementor_Addon::instance();
}

ZR_Elementor_Addon_Initialize();