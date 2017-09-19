<?php
/**
 * Plugin Name: ThemeGrill Demo Importer
 * Plugin URI: https://themegrill.com/demo-importer/
 * Description: Import your demo content, widgets and theme settings with one click for ThemeGrill official themes.
 * Version: 1.4.0
 * Author: ThemeGrill
 * Author URI: https://themegrill.com
 * License: GPLv3 or later
 * Text Domain: themegrill-demo-importer
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'ThemeGrill_Demo_Importer' ) ) :

/**
 * ThemeGrill_Demo_Importer main class.
 */
final class ThemeGrill_Demo_Importer {

	/**
	 * Plugin version.
	 * @var string
	 */
	public $version = '1.4.0';

	/**
	 * Instance of this class.
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Return an instance of this class.
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Cloning is forbidden.
	 * @since 1.4
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'themegrill-demo-importer' ), '1.4' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 * @since 1.4
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'themegrill-demo-importer' ), '1.4' );
	}

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		$this->define_constants();
		$this->init_hooks();

		do_action( 'themegrill_demo_importer_loaded' );
	}

	/**
	 * Define TGDM Constants.
	 */
	private function define_constants() {
		$upload_dir = wp_upload_dir();

		$this->define( 'TGDM_PLUGIN_FILE', __FILE__ );
		$this->define( 'TGDM_ABSPATH', dirname( TGDM_PLUGIN_FILE ) . '/' );
		$this->define( 'TGDM_PLUGIN_BASENAME', plugin_basename( TGDM_PLUGIN_FILE ) );
		$this->define( 'TGDM_VERSION', $this->version );
		$this->define( 'TGDM_DEMO_DIR', $upload_dir['basedir'] . '/tg-demo-pack/' );
		$this->define( 'TGDM_DEMO_URL', $upload_dir['baseurl'] . '/tg-demo-pack/' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Register activation hook.
		register_activation_hook( TGDM_PLUGIN_FILE, array( $this, 'install' ) );

		// Check with ThemeGrill theme is installed.
		if ( in_array( get_option( 'template' ), $this->get_core_supported_themes() ) ) {
			$this->includes();

			add_action( 'after_setup_theme', array( $this, 'include_template_functions' ), 11 );
			add_filter( 'plugin_action_links_' . TGDM_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		} else {
			add_action( 'admin_notices', array( $this, 'theme_support_missing_notice' ) );
		}
	}

	/**
	 * Get core supported themes.
	 * @return array
	 */
	private function get_core_supported_themes() {
		$core_themes = array( 'spacious', 'colormag', 'flash', 'estore', 'ample', 'accelerate', 'colornews', 'foodhunt', 'fitclub', 'radiate', 'freedom', 'himalayas', 'esteem', 'envince', 'suffice', 'explore', 'masonic' );

		// Check for core themes pro version :)
		$pro_themes = array_diff( $core_themes, array( 'explore', 'masonic' ) );
		if ( ! empty( $pro_themes ) ) {
			$pro_themes = preg_replace( '/$/', '-pro', $pro_themes );
		}

		return array_merge( $core_themes, $pro_themes );
	}

	/**
	 * Includes.
	 */
	private function includes() {
		include_once( TGDM_ABSPATH . 'includes/class-demo-importer.php' );
		include_once( TGDM_ABSPATH . 'includes/functions-demo-importer.php' );

		// Include valid demo packages config.
		if ( false === strpos( get_option( 'template' ), '-pro' ) ) {
			$files = glob( TGDM_DEMO_DIR . '**/tg-demo-config.php' );
			if ( $files ) {
				foreach ( $files as $file ) {
					if ( $file && is_readable( $file ) ) {
						include_once( $file );
					}
				}
			}
		}
	}

	/**
	 * Template Functions - This makes them pluggable by plugins and themes.
	 */
	public function include_template_functions() {
		include_once( TGDM_ABSPATH . 'includes/functions-demo-template.php' );
	}

	/**
	 * Install TG Demo Importer.
	 */
	public function install() {
		if ( ! is_blog_installed() ) {
			return;
		}

		// Install files and folders for uploading files and prevent hotlinking.
		$files = array(
			array(
				'base'    => TGDM_DEMO_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}

		// Redirect to demo importer page.
		set_transient( '_tg_demo_importer_activation_redirect', 1, 30 );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/themegrill-demo-importer/themegrill-demo-importer-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/themegrill-demo-importer-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'themegrill-demo-importer' );

		load_textdomain( 'themegrill-demo-importer', WP_LANG_DIR . '/themegrill-demo-importer/themegrill-demo-importer-' . $locale . '.mo' );
		load_plugin_textdomain( 'themegrill-demo-importer', false, plugin_basename( dirname( TGDM_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', TGDM_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( TGDM_PLUGIN_FILE ) );
	}

	/**
	 * Display action links in the Plugins list table.
	 * @param  array $actions
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'importer' => '<a href="' . admin_url( 'themes.php?page=demo-importer' ) . '" aria-label="' . esc_attr( __( 'View Demo Importer', 'themegrill-demo-importer' ) ) . '">' . __( 'Demo Importer', 'themegrill-demo-importer' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Display row meta in the Plugins list table.
	 * @param  array  $plugin_meta
	 * @param  string $plugin_file
	 * @return array
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( TGDM_PLUGIN_BASENAME == $plugin_file ) {
			$new_plugin_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'themegrill_demo_importer_docs_url', 'https://themegrill.com/docs/themegrill-demo-importer/' ) ) . '" title="' . esc_attr( __( 'View Demo Importer Documentation', 'themegrill-demo-importer' ) ) . '">' . __( 'Docs', 'themegrill-demo-importer' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'themegrill_demo_importer_support_url', 'https://themegrill.com/support-forum/' ) ) . '" title="' . esc_attr( __( 'Visit Free Customer Support Forum', 'themegrill-demo-importer' ) ) . '">' . __( 'Free Support', 'themegrill-demo-importer' ) . '</a>',
			);

			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return (array) $plugin_meta;
	}

	/**
	 * Theme support fallback notice.
	 * @return string
	 */
	public function theme_support_missing_notice() {
		echo '<div class="error notice is-dismissible"><p><strong>' . __( 'ThemeGrill Demo Importer', 'themegrill-demo-importer' ) . '</strong> &#8211; ' . sprintf( __( 'This plugin requires %s by ThemeGrill to work.', 'themegrill-demo-importer' ), '<a href="https://themegrill.com/themes/" target="_blank">' . __( 'Official Theme', 'themegrill-demo-importer' ) . '</a>' ) . '</p></div>';
	}
}

endif;

/**
 * Main instance of ThemeGrill Demo importer.
 *
 * Returns the main instance of TGDM to prevent the need to use globals.
 *
 * @since  1.3.4
 * @return ThemeGrill_Demo_Importer
 */
function TGDM() {
	return ThemeGrill_Demo_Importer::get_instance();
}

// Global for backwards compatibility.
$GLOBALS['themegrill_demo_importer'] = TGDM();
