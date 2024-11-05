<?php
/**
 * Plugin Name: Social Auto Share
 * Plugin URI: https://github.com/mehdiraized/social-auto-share
 * Description: Automatically share WordPress content (posts, products, etc.) to various social media platforms
 * Short Description: Automatically share WordPress content to social media
 * Version: 1.0.0
 * Author: Mehdi Rezaei
 * Author URI: https://mehd.ir
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: social-auto-share
 * Domain Path: /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WP_SOCIAL_AUTO_SHARE_VERSION', '1.0.0' );
define( 'WP_SOCIAL_AUTO_SHARE_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Core plugin class
 */
class WP_Social_Auto_Share {
	private static $instance = null;
	private $settings;
	private $platforms = [];
	private $content_types = [];

	private function __construct() {
		$this->init_hooks();
	}

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function init_hooks() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );

		// Add hook for admin styles
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );

		// Load platform modules
		$this->load_platforms();

		// Load content type handlers
		$this->load_content_types();

		// Register content hooks
		$this->register_content_hooks();


		add_filter( 'wp_social_auto_share_enabled_platforms', [ $this, 'get_enabled_platforms' ] );
	}

	/**
	 * Enqueue admin styles
	 */
	public function enqueue_admin_styles( $hook ) {
		// Only load on plugin settings page
		if ( $hook != 'toplevel_page_social-auto-share' ) {
			return;
		}

		// Add Select2 CSS and JS
		wp_enqueue_style(
			'select2',
			plugins_url( 'assets/css/select2.min.css', __FILE__ ),
			[],
			'4.1.0-rc.0'
		);

		wp_enqueue_script(
			'select2',
			plugins_url( 'assets/js/select2.min.js', __FILE__ ),
			[ 'jquery' ],
			'4.1.0-rc.0',
			true
		);

		// Enqueue admin styles
		wp_enqueue_style(
			'social-auto-share-admin',
			plugins_url( 'assets/css/admin.css', __FILE__ ),
			[ 'select2' ],
			WP_SOCIAL_AUTO_SHARE_VERSION
		);

		// Enqueue admin scripts
		wp_enqueue_script(
			'social-auto-share-admin',
			plugins_url( 'assets/js/admin.js', __FILE__ ),
			[ 'jquery', 'select2' ],
			WP_SOCIAL_AUTO_SHARE_VERSION,
			true
		);
	}

	private function load_platforms() {
		// Load available social media platform handlers
		require_once WP_SOCIAL_AUTO_SHARE_PATH . 'platforms/class-social-platform.php';
		require_once WP_SOCIAL_AUTO_SHARE_PATH . 'platforms/class-telegram-platform.php';

		// Register platforms
		$this->register_platform( new Social_Platform_Telegram() );

		// Filter for adding more platforms
		$this->platforms = apply_filters( 'wp_social_auto_share_platforms', $this->platforms );
	}

	private function load_content_types() {
		// Load content type handlers
		require_once WP_SOCIAL_AUTO_SHARE_PATH . 'content-types/class-content-type.php';
		require_once WP_SOCIAL_AUTO_SHARE_PATH . 'content-types/class-post-type.php';

		// Register default content types
		$this->register_content_type( new Content_Type_Post() );

		// Filter for adding more content types
		$this->content_types = apply_filters( 'wp_social_auto_share_content_types', $this->content_types );
	}

	public function register_platform( $platform ) {
		$this->platforms[ $platform->get_id()] = $platform;
	}

	public function register_content_type( $content_type ) {
		$this->content_types[ $content_type->get_id()] = $content_type;
	}

	private function register_content_hooks() {
		foreach ( $this->content_types as $content_type ) {
			foreach ( $content_type->get_hooks() as $hook => $callback ) {
				add_action( $hook, [ $content_type, $callback ], 10, 2 );
			}
		}
	}

	public function init() {
		load_plugin_textdomain( 'social-auto-share', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		$this->settings = get_option( 'wp_social_auto_share_settings' );
	}

	public function add_admin_menu() {
		add_menu_page(
			__( 'Social Auto Share', 'social-auto-share' ),
			__( 'Social Share', 'social-auto-share' ),
			'manage_options',
			'social-auto-share',
			[ $this, 'render_settings_page' ],
			'dashicons-share'
		);
	}

	public function register_settings() {
		register_setting( 'wp_social_auto_share_settings_group', 'wp_social_auto_share_settings' );

		// Register settings sections and fields for each platform
		foreach ( $this->platforms as $platform ) {
			$platform->register_settings();
		}

		// Register content type settings
		foreach ( $this->content_types as $content_type ) {
			$content_type->register_settings();
		}
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap social-auto-share">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wp_social_auto_share_settings_group' );

				// Render platform settings
				foreach ( $this->platforms as $platform ) {
					$platform->render_settings();
				}

				// Render content type settings
				foreach ( $this->content_types as $content_type ) {
					$content_type->render_settings();
				}

				submit_button( __( 'Save Settings', 'social-auto-share' ) );
				?>
			</form>
			<div class="wrap social-platform-settings">
				<h3><?php esc_html_e( 'Support Us', 'social-auto-share' ); ?></h3>
				<p>
					<?php esc_html_e( 'Thank you for using Social Auto Share! This plugin is a labor of love, designed to help you to automatically sharing content to social media platforms. If you find it useful, please consider supporting its development.', 'social-auto-share' ); ?>
				</p>
				<p>
					<?php esc_html_e( 'Your support helps cover the costs of maintaining and improving the plugin, ensuring it remains free and accessible for everyone. Every little bit helps and is greatly appreciated!', 'social-auto-share' ); ?>
				</p>
				<a href="https://www.buymeacoffee.com/mehdiraized" target="_blank">
					<img src="<?php echo esc_url( plugins_url( '/assets/img/bmc-button.png', __FILE__ ) ); ?>" alt="Buy Me A Coffee"
						style="height: 60px !important;width: 217px !important;">
				</a>
				<p><?php esc_html_e( 'Thank you for your generosity and support!', 'social-auto-share' ); ?></p>
			</div>
		</div>
		<?php
	}
	/**
	 * Get enabled platforms
	 *
	 * @return array Array of configured platform instances
	 */
	public function get_enabled_platforms() {
		$enabled_platforms = [];

		foreach ( $this->platforms as $platform ) {
			if ( $platform->is_enabled() && $platform->is_configured() ) {
				$enabled_platforms[] = $platform;
			}
		}

		return $enabled_platforms;
	}
}

// Initialize plugin
function wp_social_auto_share_init() {
	WP_Social_Auto_Share::get_instance();
	load_plugin_textdomain( 'social-auto-share', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'wp_social_auto_share_init' );

// Activation hook
register_activation_hook( __FILE__, 'wp_social_auto_share_activate' );
function wp_social_auto_share_activate() {
	// Create necessary database tables and default options
	add_option( 'wp_social_auto_share_settings', [] );
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'wp_social_auto_share_deactivate' );
function wp_social_auto_share_deactivate() {
	// Cleanup if needed
}