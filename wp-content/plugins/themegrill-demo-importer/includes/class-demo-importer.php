<?php
/**
 * ThemeGrill Demo Importer.
 *
 * @class    TG_Demo_Importer
 * @version  1.0.0
 * @package  Importer/Classes
 * @category Admin
 * @author   ThemeGrill
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TG_Demo_Importer Class.
 */
class TG_Demo_Importer {

	/**
	 * Demo config.
	 * @var array
	 */
	public $demo_config;

	/**
	 * Demo packages.
	 * @var array
	 */
	public $demo_packages;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup' ), 5 );
		add_action( 'init', array( $this, 'includes' ) );

		// Add Demo Importer menu.
		if ( apply_filters( 'themegrill_show_demo_importer_page', true ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
			add_action( 'admin_head', array( $this, 'add_menu_classes' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		// Help Tabs.
		if ( apply_filters( 'themegrill_demo_importer_enable_admin_help_tab', true ) ) {
			add_action( 'current_screen', array( $this, 'add_help_tabs' ), 50 );
		}

		// Reset Wizard.
		add_action( 'wp_loaded', array( $this, 'hide_reset_notice' ) );
		add_action( 'admin_init', array( $this, 'reset_wizard_actions' ) );
		add_action( 'admin_notices', array( $this, 'reset_wizard_notice' ) );

		// Footer rating text.
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );

		// AJAX Events to import demo and update rating footer.
		add_action( 'wp_ajax_import-demo', array( $this, 'ajax_import_demo' ) );
		add_action( 'wp_ajax_footer-text-rated', array( $this, 'ajax_footer_text_rated' ) );

		// Update custom nav menu items and siteorigin panel data.
		add_action( 'themegrill_ajax_demo_imported', array( $this, 'update_nav_menu_items' ) );
		add_action( 'themegrill_ajax_demo_imported', array( $this, 'update_siteorigin_data' ), 10, 2 );

		// Update widget and customizer demo import settings data.
		add_filter( 'themegrill_widget_demo_import_settings', array( $this, 'update_widget_data' ), 10, 4 );
		add_filter( 'themegrill_customizer_demo_import_settings', array( $this, 'update_customizer_data' ), 10, 2 );
	}

	/**
	 * Demo importer setup.
	 */
	public function setup() {
		$this->demo_config   = apply_filters( 'themegrill_demo_importer_config', array() );
		$this->demo_packages = apply_filters( 'themegrill_demo_importer_packages', array() );
	}

	/**
	 * Include required core files.
	 */
	public function includes() {
		include_once( dirname( __FILE__ ) . '/importers/class-widget-importer.php' );
		include_once( dirname( __FILE__ ) . '/importers/class-customizer-importer.php' );
	}

	/**
	 * Get the import file URL.
	 *
	 * @param  string $demo_dir demo dir.
	 * @param  string $filename import filename.
	 * @return string the demo import data file URL.
	 */
	private function import_file_url( $demo_dir, $filename ) {
		$working_dir = tg_get_demo_file_url( $demo_dir );

		// If enabled demo pack, load from upload dir.
		if ( $this->is_enabled_demo_pack( $demo_dir ) ) {
			$working_dir = TGDM_DEMO_URL . $demo_dir;
		}

		return trailingslashit( $working_dir ) . sanitize_file_name( $filename );
	}

	/**
	 * Get the import file path.
	 *
	 * @param  string $demo_dir demo dir.
	 * @param  string $filename import filename.
	 * @return string the import data file path.
	 */
	private function import_file_path( $demo_dir, $filename ) {
		$working_dir = tg_get_demo_file_path( $demo_dir );

		// If enabled demo pack, load from upload dir.
		if ( $this->is_enabled_demo_pack( $demo_dir ) ) {
			$working_dir = TGDM_DEMO_DIR . $demo_dir . '/dummy-data';
		}

		return trailingslashit( $working_dir ) . sanitize_file_name( $filename );
	}

	/**
	 * Check if demo pack is enabled.
	 * @param  array $demo_id
	 * @return bool
	 */
	public function is_enabled_demo_pack( $demo_id ) {
		if ( isset( $this->demo_config[ $demo_id ]['demo_pack'] ) && true === $this->demo_config[ $demo_id ]['demo_pack'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Add menu item.
	 */
	public function admin_menu() {
		add_theme_page( __( 'Demo Importer', 'themegrill-demo-importer' ), __( 'Demo Importer', 'themegrill-demo-importer' ), 'switch_themes', 'demo-importer', array( $this, 'demo_importer' ) );
	}

	/**
	 * Adds the class to the menu.
	 */
	public function add_menu_classes() {
		global $submenu;

		if ( isset( $submenu['themes.php'] ) ) {
			$submenu_class = tg_demo_installer_enabled() ? 'demo-installer hide-if-no-js' : 'demo-importer';

			// Add menu classes if user has access.
			if ( apply_filters( 'themegrill_demo_importer_include_class_in_menu', true ) ) {
				foreach ( $submenu['themes.php'] as $order => $menu_item ) {
					if ( 0 === strpos( $menu_item[0], _x( 'Demo Importer', 'Admin menu name', 'themegrill-demo-importer' ) ) ) {
						$submenu['themes.php'][ $order ][4] = empty( $menu_item[4] ) ? $submenu_class : $menu_item[4] . ' ' . $submenu_class;
						break;
					}
				}
			}
		}
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		$screen      = get_current_screen();
		$screen_id   = $screen ? $screen->id : '';
		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$assets_path = TGDM()->plugin_url() . '/assets/';

		// Register admin styles.
		wp_register_style( 'tg-demo-importer', $assets_path . 'css/demo-importer.css', array(), TGDM_VERSION );

		// Add RTL support for admin styles.
		wp_style_add_data( 'tg-demo-importer', 'rtl', 'replace' );

		// Register admin scripts.
		wp_register_script( 'jquery-tiptip', $assets_path . 'js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), '1.3', true );
		wp_register_script( 'tg-demo-updates', $assets_path . 'js/admin/demo-updates' . $suffix . '.js', array( 'jquery', 'updates' ), TGDM_VERSION, true );
		wp_register_script( 'tg-demo-importer', $assets_path . 'js/admin/demo-importer' . $suffix . '.js', array( 'jquery', 'jquery-tiptip', 'wp-backbone', 'wp-a11y', 'tg-demo-updates' ), TGDM_VERSION, true );

		// Demo Importer appearance page.
		if ( 'appearance_page_demo-importer' === $screen_id ) {
			wp_enqueue_style( 'tg-demo-importer' );
			wp_enqueue_script( 'tg-demo-importer' );
			wp_localize_script( 'tg-demo-updates', '_demoUpdatesSettings', array(
				'l10n' => array(
					'importing'             => __( 'Importing...', 'themegrill-demo-importer' ),
					'demoImportingLabel'    => _x( 'Importing %s...', 'demo', 'themegrill-demo-importer' ), // no ellipsis
					'importingMsg'          => __( 'Importing... please wait.', 'themegrill-demo-importer' ),
					'importedMsg'           => __( 'Import completed successfully.', 'themegrill-demo-importer' ),
					'importFailedShort'     => __( 'Import Failed!', 'themegrill-demo-importer' ),
					'importFailed'          => __( 'Import failed: %s', 'themegrill-demo-importer' ),
					'demoImportedLabel'     => _x( '%s imported!', 'demo', 'themegrill-demo-importer' ),
					'demoImportFailedLabel' => _x( '%s import failed', 'demo', 'themegrill-demo-importer' ),
					'livePreview'           => __( 'Live Preview', 'themegrill-demo-importer' ),
					'livePreviewLabel'      => _x( 'Live Preview %s', 'demo', 'themegrill-demo-importer' ),
					'imported'              => __( 'Imported!', 'themegrill-demo-importer' ),
					'statusTextLink'        => '<a href="https://docs.themegrill.com/knowledgebase/demo-import-process-failed/" target="_blank">' . __( 'Try this solution!', 'themegrill-demo-importer' ) . '</a>',
				),
			) );
			wp_localize_script( 'tg-demo-importer', 'demoImporterLocalizeScript', array(
				'demos'    => $this->prepare_demos_for_js( tg_demo_installer_preview() ? $this->demo_packages : $this->demo_config ),
				'settings' => array(
					'isPreview'      => tg_demo_installer_preview(),
					'isInstall'      => tg_demo_installer_enabled(),
					'canInstall'     => current_user_can( 'upload_files' ),
					'installURI'     => current_user_can( 'upload_files' ) ? self_admin_url( 'themes.php?page=demo-importer&browse=preview' ) : null,
					'confirmReset'   => __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the reset wizard now?', 'themegrill-demo-importer' ),
					'confirmDelete'  => __( "Are you sure you want to delete this demo?\n\nClick 'Cancel' to go back, 'OK' to confirm the delete.", 'themegrill-demo-importer' ),
					'confirmImport'  => __( 'Importing demo content will replicate the live demo and overwrites your current customizer, widgets and other settings. It might take few minutes to complete the demo import. Are you sure you want to import this demo?', 'themegrill-demo-importer' ),
					'confirmInstall' => __( 'Are you sure you want to install the selected plugins and their data?', 'themegrill-demo-importer' ),
					'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
					'adminUrl'       => parse_url( self_admin_url(), PHP_URL_PATH ),
				),
				'l10n' => array(
					'addNew'            => __( 'Add New Demo', 'themegrill-demo-importer' ),
					'search'            => __( 'Search Demos', 'themegrill-demo-importer' ),
					'searchPlaceholder' => __( 'Search demos...', 'themegrill-demo-importer' ), // placeholder (no ellipsis)
					'demosFound'        => __( 'Number of Demos found: %d', 'themegrill-demo-importer' ),
					'noDemosFound'      => __( 'No demos found. Try a different search.', 'themegrill-demo-importer' ),
				),
				'installedDemos' => array_keys( $this->demo_config ),
			) );
		}
	}

	/**
	 * Change the admin footer text.
	 * @param  string $footer_text
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $footer_text;
		}

		$current_screen = get_current_screen();

		// Check to make sure we're on a ThemeGrill Demo Importer admin page.
		if ( isset( $current_screen->id ) && apply_filters( 'themegrill_demo_importer_display_admin_footer_text', in_array( $current_screen->id, array( 'appearance_page_demo-importer' ) ) ) ) {
			// Change the footer text.
			if ( ! get_option( 'themegrill_demo_importer_admin_footer_text_rated' ) ) {
				$footer_text = sprintf( __( 'If you like <strong>ThemeGrill Demo Importer</strong> please leave us a %1$s&#9733;&#9733;&#9733;&#9733;&#9733;%2$s rating. A huge thanks in advance!', 'themegrill-demo-importer' ), '<a href="https://wordpress.org/support/plugin/themegrill-demo-importer/reviews?rate=5#new-post" target="_blank" class="themegrill-demo-importer-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'themegrill-demo-importer' ) . '">', '</a>' );
			} else {
				$footer_text = __( 'Thank you for importing with ThemeGrill Demo Importer.', 'themegrill-demo-importer' );
			}
		}

		return $footer_text;
	}

	/**
	 * Add Contextual help tabs.
	 */
	public function add_help_tabs() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, array( 'appearance_page_demo-importer' ) ) ) {
			return;
		}

		$video_map = array(
			'demo-importer' => array(
				'title' => __( 'Importing Demo', 'themegrill-demo-importer' ),
				'id'    => 'ctdquor9uh',
			),
		);

		$video_key = empty( $_GET['page'] ) ? $screen->id : sanitize_title( $_GET['page'] );

		if ( ! tg_demo_installer_enabled() && isset( $video_map[ $video_key ] ) ) {
			$screen->add_help_tab( array(
				'id'        => 'themegrill_demo_importer_guided_tour_tab',
				'title'     => __( 'Guided Tour', 'themegrill-demo-importer' ),
				'content'   =>
					'<h2>' . __( 'Guided Tour', 'themegrill-demo-importer' ) . ' &ndash; ' . esc_html( $video_map[ $video_key ]['title'] ) . '</h2>' .
					'<script src="//fast.wistia.net/assets/external/E-v1.js" aync></script>
					<div class="wistia_embed wistia_async_' . esc_attr( $video_map[ $video_key ]['id'] ) . ' videoFoam=true seo=false" style="width:640px;height:360px;">&nbsp;</div>
				',
			) );
		}

		$screen->add_help_tab( array(
			'id'        => 'themegrill_demo_importer_support_tab',
			'title'     => __( 'Help &amp; Support', 'themegrill-demo-importer' ),
			'content'   =>
				'<h2>' . __( 'Help &amp; Support', 'themegrill-demo-importer' ) . '</h2>' .
				'<p>' . sprintf(
					__( 'Should you need help understanding, using, or extending ThemeGrill Demo Importer, <a href="%s">please read our documentation</a>. You will find all kinds of resources including snippets, tutorials and much more.' , 'themegrill-demo-importer' ),
					'https://themegrill.com/docs/themegrill-demo-importer/'
				) . '</p>' .
				'<p>' . sprintf(
					__( 'For further assistance with ThemeGrill Demo Importer core you can use the <a href="%1$s">community forum</a>. If you need help with premium themes sold by ThemeGrill, please <a href="%2$s">use our free support forum</a>.', 'themegrill-demo-importer' ),
					'https://wordpress.org/support/plugin/themegrill-demo-importer',
					'https://themegrill.com/support-forum/'
				) . '</p>' .
				'<p><a href="' . 'https://wordpress.org/support/plugin/themegrill-demo-importer' . '" class="button button-primary">' . __( 'Community forum', 'themegrill-demo-importer' ) . '</a> <a href="' . 'https://themegrill.com/support-forum/' . '" class="button">' . __( 'ThemeGrill Support', 'themegrill-demo-importer' ) . '</a></p>',
		) );

		$screen->add_help_tab( array(
			'id'        => 'themegrill_demo_importer_bugs_tab',
			'title'     => __( 'Found a bug?', 'themegrill-demo-importer' ),
			'content'   =>
				'<h2>' . __( 'Found a bug?', 'themegrill-demo-importer' ) . '</h2>' .
				'<p>' . sprintf( __( 'If you find a bug within ThemeGrill Demo Importer you can create a ticket via <a href="%1$s">Github issues</a>. Ensure you read the <a href="%2$s">contribution guide</a> prior to submitting your report. To help us solve your issue, please be as descriptive as possible.', 'themegrill-demo-importer' ), 'https://github.com/themegrill/themegrill-demo-importer/issues?state=open', 'https://github.com/themegrill/themegrill-demo-importer/blob/master/.github/CONTRIBUTING.md' ) . '</p>' .
				'<p><a href="' . 'https://github.com/themegrill/themegrill-demo-importer/issues?state=open' . '" class="button button-primary">' . __( 'Report a bug', 'themegrill-demo-importer' ) . '</a></p>',

		) );

		$screen->add_help_tab( array(
			'id'        => 'themegrill_demo_importer_reset_tab',
			'title'     => __( 'Reset wizard', 'themegrill-demo-importer' ),
			'content'   =>
				'<h2>' . __( 'Reset wizard', 'themegrill-demo-importer' ) . '</h2>' .
				'<p>' . __( 'If you need to reset the WordPress back to default again, please click on the button below.', 'themegrill-demo-importer' ) . '</p>' .
				'<p><a href="' . esc_url( add_query_arg( 'do_reset_wordpress', 'true', admin_url( 'themes.php?page=demo-importer' ) ) ) . '" class="button button-primary themegrill-reset-wordpress">' . __( 'Reset wizard', 'themegrill-demo-importer' ) . '</a></p>',
		) );

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'themegrill-demo-importer' ) . '</strong></p>' .
			'<p><a href="' . 'https://themegrill.com/demo-importer/' . '" target="_blank">' . __( 'About Demo Importer', 'themegrill-demo-importer' ) . '</a></p>' .
			'<p><a href="' . 'https://wordpress.org/plugins/themegrill-demo-importer/' . '" target="_blank">' . __( 'WordPress.org project', 'themegrill-demo-importer' ) . '</a></p>' .
			'<p><a href="' . 'https://github.com/themegrill/themegrill-demo-importer' . '" target="_blank">' . __( 'Github project', 'themegrill-demo-importer' ) . '</a></p>' .
			'<p><a href="' . 'https://themegrill.com/wordpress-themes/' . '" target="_blank">' . __( 'Official themes', 'themegrill-demo-importer' ) . '</a></p>' .
			'<p><a href="' . 'https://themegrill.com/plugins/' . '" target="_blank">' . __( 'Official plugins', 'themegrill-demo-importer' ) . '</a></p>'
		);
	}

	/**
	 * Reset wizard notice.
	 */
	public function reset_wizard_notice() {
		$screen              = get_current_screen();
		$demo_activated_id   = get_option( 'themegrill_demo_importer_activated_id' );
		$demo_notice_dismiss = get_option( 'themegrill_demo_importer_reset_notice' );

		if ( ! $screen || ! in_array( $screen->id, array( 'appearance_page_demo-importer' ) ) ) {
			return;
		}

		// Output reset wizard notice.
		if ( ! $demo_notice_dismiss && in_array( $demo_activated_id, array_keys( $this->demo_config ) ) ) {
			include_once( dirname( __FILE__ ) . '/admin/views/html-notice-reset-wizard.php' );
		} elseif ( isset( $_GET['reset'] ) && 'true' === $_GET['reset'] ) {
			include_once( dirname( __FILE__ ) . '/admin/views/html-notice-reset-wizard-success.php' );
		}
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public function hide_reset_notice() {
		if ( isset( $_GET['themegrill-demo-importer-hide-notice'] ) && isset( $_GET['_themegrill_demo_importer_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_themegrill_demo_importer_notice_nonce'], 'themegrill_demo_importer_hide_notice_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'themegrill-demo-importer' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheatin&#8217; huh?', 'themegrill-demo-importer' ) );
			}

			$hide_notice = sanitize_text_field( $_GET['themegrill-demo-importer-hide-notice'] );

			if ( ! empty( $hide_notice ) && 'reset_notice' == $hide_notice ) {
				update_option( 'themegrill_demo_importer_reset_notice', 1 );
			}
		}
	}

	/**
	 * Reset actions when a reset button is clicked.
	 */
	public function reset_wizard_actions() {
		global $wpdb, $current_user;

		if ( ! empty( $_GET['do_reset_wordpress'] ) ) {
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

			$template     = get_option( 'template' );
			$blogname     = get_option( 'blogname' );
			$admin_email  = get_option( 'admin_email' );
			$blog_public  = get_option( 'blog_public' );
			$footer_rated = get_option( 'themegrill_demo_importer_admin_footer_text_rated' );

			if ( 'admin' != $current_user->user_login ) {
				$user = get_user_by( 'login', 'admin' );
			}

			if ( empty( $user->user_level ) || $user->user_level < 10 ) {
				$user = $current_user;
			}

			// Drop tables.
			$drop_tables = $wpdb->get_col( sprintf( "SHOW TABLES LIKE '%s%%'", str_replace( '_', '\_', $wpdb->prefix ) ) );
			foreach ( $drop_tables as $table ) {
				$wpdb->query( "DROP TABLE IF EXISTS $table" );
			}

			// Installs the site.
			$result = wp_install( $blogname, $user->user_login, $user->user_email, $blog_public );

			// Updates the user password with a old one.
			$wpdb->update( $wpdb->users, array( 'user_pass' => $user->user_pass, 'user_activation_key' => '' ), array( 'ID' => $result['user_id'] ) );

			// Set up the Password change nag.
			$default_password_nag = get_user_option( 'default_password_nag', $result['user_id'] );
			if ( $default_password_nag ) {
				update_user_option( $result['user_id'], 'default_password_nag', false, true );
			}

			// Update footer text.
			if ( $footer_rated ) {
				update_option( 'themegrill_demo_importer_admin_footer_text_rated', $footer_rated );
			}

			// Switch current theme.
			$current_theme = wp_get_theme( $template );
			if ( $current_theme->exists() ) {
				switch_theme( $template );
			}

			// Activate required plugins.
			$required_plugins = (array) apply_filters( 'themegrill_demo_importer_' . $template . '_required_plugins', array() );
			if ( is_array( $required_plugins ) ) {
				if ( ! in_array( TGDM_PLUGIN_BASENAME, $required_plugins ) ) {
					$required_plugins = array_merge( $required_plugins, array( TGDM_PLUGIN_BASENAME ) );
				}
				activate_plugins( $required_plugins, '', is_network_admin(), true );
			}

			// Update the cookies.
			wp_clear_auth_cookie();
			wp_set_auth_cookie( $result['user_id'] );

			// Redirect to demo importer page to display reset success notice.
			wp_safe_redirect( admin_url( 'themes.php?page=demo-importer&reset=true' ) );
			exit();
		}
	}

	/**
	 * Prepare demos for JavaScript.
	 *
	 * @param  array $demos Demo config array.
	 * @return array An associative array of demo data, sorted by name.
	 */
	private function prepare_demos_for_js( $demos = null ) {
		$prepared_demos    = array();
		$current_template  = get_option( 'template' );
		$demo_activated_id = get_option( 'themegrill_demo_importer_activated_id' );

		/**
		 * Filters demo data before it is prepared for JavaScript.
		 *
		 * @param array      $prepared_demos    An associative array of demo data. Default empty array.
		 * @param null|array $demos             An array of demo config to prepare, if any.
		 * @param string     $demo_activated_id The current demo activated id.
		 */
		$prepared_demos = (array) apply_filters( 'themegrill_demo_importer_pre_prepare_demos_for_js', array(), $demos, $demo_activated_id );

		if ( ! empty( $prepared_demos ) ) {
			return $prepared_demos;
		}

		// Make sure the imported demo is listed first.
		if ( ! tg_demo_installer_preview() && isset( $demos[ $demo_activated_id ] ) ) {
			$prepared_demos[ $demo_activated_id ] = array();
		}

		if ( ! empty( $demos ) ) {
			foreach ( $demos as $demo_id => $demo_data ) {
				$author       = isset( $demo_data['author'] ) ? $demo_data['author'] : __( 'ThemeGrill', 'themegrill-demo-importer' );
				$version      = isset( $demo_data['version'] ) ? $demo_data['version'] : TGDM_VERSION;
				$description  = isset( $demo_data['description'] ) ? $demo_data['description'] : '';
				$premium_link = isset( $demo_data['pro_link'] ) ? $demo_data['pro_link'] : '';
				$download_url = isset( $demo_data['download'] ) ? $demo_data['download'] : "https://github.com/themegrill/themegrill-demo-pack/raw/master/packages/{$current_template}/{$demo_id}.zip";
				$demo_package = isset( $demo_data['demo_pack'] ) ? $demo_data['demo_pack'] : false;
				$plugins_list = isset( $demo_data['plugins_list'] ) ? $demo_data['plugins_list'] : array();

				// Plugins status.
				foreach ( $plugins_list as $plugin => $plugin_data ) {
					$plugins_list[ $plugin ]['is_active'] = is_plugin_active( $plugin_data['slug'] );

					// Looks like a plugin is installed, but not active.
					if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
						$plugins = get_plugins( '/' . $plugin );
						if ( ! empty( $plugins ) ) {
							$plugins_list[ $plugin ]['is_install'] = true;
						}
					} else {
						$plugins_list[ $plugin ]['is_install'] = false;
					}
				}

				// Add demo notices.
				$demo_notices = array();
				if ( isset( $demo_data['template'] ) && $current_template !== $demo_data['template'] ) {
					$demo_notices['required_theme'] = true;
				} elseif ( wp_list_filter( $plugins_list, array( 'required' => true, 'is_active' => false ) ) ) {
					$demo_notices['required_plugins'] = true;
				}

				// Prepare all demos.
				if ( tg_demo_installer_preview() ) {
					$prepared_demos[ $demo_id ] = array(
						'id'              => $demo_id,
						'name'            => $demo_data['name'],
						'installed'       => in_array( $demo_id, array_keys( $this->demo_config ) ),
						'screenshot'      => tg_get_demo_preview_screenshot_url( $demo_id, $current_template ),
						'description'     => $description,
						'author'          => $author,
						'actions'         => array(
							'pro_link'     => $premium_link,
							'preview_url'  => $demo_data['preview'],
							'download_url' => $download_url,
						),
					);
				} else {
					$prepared_demos[ $demo_id ] = array(
						'id'              => $demo_id,
						'name'            => $demo_data['name'],
						'theme'           => $demo_data['theme'],
						'package'         => $demo_package,
						'screenshot'      => $this->import_file_url( $demo_id, 'screenshot.jpg' ),
						'description'     => $description,
						'author'          => $author,
						'authorAndUri'    => '<a href="https://themegrill.com" target="_blank">ThemeGrill</a>',
						'version'         => $version,
						'active'          => $demo_id === $demo_activated_id,
						'hasNotice'       => $demo_notices,
						'plugins'         => $plugins_list,
						'pluginActions'   => array(
							'install'  => wp_list_filter( $plugins_list, array( 'is_install' => false ) ) ? true : false,
							'activate' => wp_list_filter( $plugins_list, array( 'is_active' => false ) ) ? true : false,
						),
						'actions'         => array(
							'preview'  => home_url( '/' ),
							'demo_url' => $demo_data['demo_url'],
							'delete'   => current_user_can( 'upload_files' ) ? wp_nonce_url( admin_url( 'themes.php?page=demo-importer&browse=uploads&action=delete&amp;demo_pack=' . urlencode( $demo_id ) ), 'delete-demo_' . $demo_id ) : null,
						),
					);
				}
			}
		}

		/**
		 * Filters the demos prepared for JavaScript.
		 *
		 * Could be useful for changing the order, which is by name by default.
		 *
		 * @param array $prepared_demos Array of demos.
		 */
		$prepared_demos = apply_filters( 'themegrill_demo_importer_prepare_demos_for_js', $prepared_demos );
		$prepared_demos = array_values( $prepared_demos );
		return array_filter( $prepared_demos );
	}

	/**
	 * Demo Importer page output.
	 */
	public function demo_importer() {
		$demos = $this->prepare_demos_for_js( tg_demo_installer_preview() ? $this->demo_packages : $this->demo_config );

		if ( isset( $_GET['action'] ) && 'upload-demo' === $_GET['action'] ) {
			$this->upload_demo_pack();
		} else {
			$suffix = tg_demo_installer_enabled() ? 'installer' : 'importer';
			include_once( dirname( __FILE__ ) . "/admin/views/html-admin-page-{$suffix}.php" );
		}
	}

	/**
	 * Upload demo pack.
	 */
	private function upload_demo_pack() {
		include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

		if ( ! current_user_can( 'upload_files' ) ) {
			wp_die( __( 'Sorry, you are not allowed to install demo on this site.', 'themegrill-demo-importer' ) );
		}

		check_admin_referer( 'demo-upload' );

		$file_upload = new File_Upload_Upgrader( 'demozip', 'package' );

		$title = sprintf( __( 'Installing Demo from uploaded file: %s', 'themegrill-demo-importer' ), esc_html( basename( $file_upload->filename ) ) );
		$nonce = 'demo-upload';
		$url   = add_query_arg( array( 'package' => $file_upload->id ), 'themes.php?page=demo-importer&action=upload-demo' );
		$type  = 'upload'; // Install demo type, From Web or an Upload.

		// Demo Upgrader Class.
		include_once( dirname( __FILE__ ) . '/admin/class-demo-upgrader.php' );
		include_once( dirname( __FILE__ ) . '/admin/class-demo-installer-skin.php' );

		$upgrader = new TG_Demo_Upgrader( new TG_Demo_Installer_Skin( compact( 'type', 'title', 'nonce', 'url' ) ) );
		$result = $upgrader->install( $file_upload->package );

		if ( $result || is_wp_error( $result ) ) {
			$file_upload->cleanup();
		}
	}

	/**
	 * Ajax handler for importing a demo.
	 */
	public function ajax_import_demo() {
		check_ajax_referer( 'updates' );

		if ( empty( $_POST['slug'] ) ) {
			wp_send_json_error( array(
				'slug'         => '',
				'errorCode'    => 'no_demo_specified',
				'errorMessage' => __( 'No demo specified.', 'themegrill-demo-importer' ),
			) );
		}

		$slug = sanitize_key( wp_unslash( $_POST['slug'] ) );

		$status = array(
			'import' => 'demo',
			'slug'   => $slug,
		);

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$status['errorMessage'] = __( 'Sorry, you are not allowed to import.', 'themegrill-demo-importer' );
			wp_send_json_error( $status );
		}

		$demo_data = isset( $this->demo_config[ $slug ] ) ? $this->demo_config[ $slug ] : array();

		do_action( 'themegrill_ajax_before_demo_import' );

		if ( ! empty( $demo_data ) ) {
			$this->import_dummy_xml( $slug, $demo_data, $status );
			$this->import_core_options( $slug, $demo_data );
			$this->import_customizer_data( $slug, $demo_data, $status );
			$this->import_widget_settings( $slug, $demo_data, $status );

			// Update imported demo ID.
			update_option( 'themegrill_demo_importer_activated_id', $slug );

			do_action( 'themegrill_ajax_demo_imported', $slug, $demo_data );
		}

		$status['demoName']   = $demo_data['name'];
		$status['previewUrl'] = get_home_url( '/' );

		wp_send_json_success( $status );
	}

	/**
	 * Triggered when clicking the rating footer.
	 */
	public function ajax_footer_text_rated() {
		if ( ! current_user_can( 'manage_options' ) ) {
			die( -1 );
		}

		update_option( 'themegrill_demo_importer_admin_footer_text_rated', 1 );
		die();
	}

	/**
	 * Import dummy content from a XML file.
	 * @param  string $demo_id
	 * @param  array  $demo_data
	 * @param  array  $status
	 * @return bool
	 */
	public function import_dummy_xml( $demo_id, $demo_data, $status ) {
		$import_file = $this->import_file_path( $demo_id, 'dummy-data.xml' );

		// Load Importer API
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

			if ( file_exists( $class_wp_importer ) ) {
				require $class_wp_importer;
			}
		}

		// Include WXR Importer.
		require( dirname( __FILE__ ) . '/importers/wordpress-importer/class-wxr-importer.php' );

		do_action( 'themegrill_ajax_before_dummy_xml_import', $demo_data, $demo_id );

		// Import XML file demo content.
		if ( is_file( $import_file ) ) {
			$wp_import = new TG_WXR_Importer();
			$wp_import->fetch_attachments = true;

			ob_start();
			$wp_import->import( $import_file );
			ob_end_clean();

			do_action( 'themegrill_ajax_dummy_xml_imported', $demo_data, $demo_id );

			flush_rewrite_rules();
		} else {
			$status['errorMessage'] = __( 'The XML file dummy content is missing.', 'themegrill-demo-importer' );
			wp_send_json_error( $status );
		}

		return true;
	}

	/**
	 * Import site core options from its ID.
	 * @param  string $demo_id
	 * @param  array  $demo_data
	 * @return bool
	 */
	public function import_core_options( $demo_id, $demo_data ) {
		if ( ! empty( $demo_data['core_options'] ) ) {
			foreach ( $demo_data['core_options'] as $option_key => $option_value ) {
				if ( ! in_array( $option_key, array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts' ) ) ) {
					continue;
				}

				// Format the value based on option key.
				switch ( $option_key ) {
					case 'show_on_front':
						if ( in_array( $option_value, array( 'posts', 'page' ) ) ) {
							update_option( 'show_on_front', $option_value );
						}
					break;
					case 'page_on_front':
					case 'page_for_posts':
						$page = get_page_by_title( $option_value );

						if ( is_object( $page ) && $page->ID ) {
							update_option( $option_key, $page->ID );
							update_option( 'show_on_front', 'page' );
						}
					break;
					default:
						update_option( $option_key, sanitize_text_field( $option_value ) );
					break;
				}
			}
		}

		return true;
	}

	/**
	 * Import customizer data from a DAT file.
	 * @param  string $demo_id
	 * @param  array  $demo_data
	 * @param  array  $status
	 * @return bool
	 */
	public function import_customizer_data( $demo_id, $demo_data, $status ) {
		$import_file = $this->import_file_path( $demo_id, 'dummy-customizer.dat' );

		if ( is_file( $import_file ) ) {
			$results = TG_Customizer_Importer::import( $import_file, $demo_id, $demo_data );

			if ( is_wp_error( $results ) ) {
				return false;
			}
		} else {
			$status['errorMessage'] = __( 'The DAT file customizer data is missing.', 'themegrill-demo-importer' );
			wp_send_json_error( $status );
		}

		return true;
	}

	/**
	 * Import widgets settings from WIE or JSON file.
	 * @param  string $demo_id
	 * @param  array  $demo_data
	 * @param  array  $status
	 * @return bool
	 */
	public function import_widget_settings( $demo_id, $demo_data, $status ) {
		$import_file = $this->import_file_path( $demo_id, 'dummy-widgets.wie' );

		if ( is_file( $import_file ) ) {
			$results = TG_Widget_Importer::import( $import_file, $demo_id, $demo_data );

			if ( is_wp_error( $results ) ) {
				return false;
			}
		} else {
			$status['errorMessage'] = __( 'The WIE file widget content is missing.', 'themegrill-demo-importer' );
			wp_send_json_error( $status );
		}

		return true;
	}

	/**
	 * Update custom nav menu items URL.
	 */
	public function update_nav_menu_items() {
		$menu_locations = get_nav_menu_locations();

		foreach ( $menu_locations as $location => $menu_id ) {

			if ( is_nav_menu( $menu_id ) ) {
				$menu_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

				if ( ! empty( $menu_items ) ) {
					foreach ( $menu_items as $menu_item ) {
						if ( isset( $menu_item->url ) && isset( $menu_item->db_id ) && 'custom' == $menu_item->type ) {
							$site_parts = parse_url( home_url( '/' ) );
							$menu_parts = parse_url( $menu_item->url );

							// Update existing custom nav menu item URL.
							if ( isset( $menu_parts['path'] ) && isset( $menu_parts['host'] ) && apply_filters( 'themegrill_demo_importer_nav_menu_item_url_hosts', in_array( $menu_parts['host'], array( 'demo.themegrill.com' ) ) ) ) {
								$menu_item->url = str_replace( array( $menu_parts['scheme'], $menu_parts['host'], $menu_parts['path'] ), array( $site_parts['scheme'], $site_parts['host'], trailingslashit( $site_parts['path'] ) ), $menu_item->url );
								update_post_meta( $menu_item->db_id, '_menu_item_url', esc_url_raw( $menu_item->url ) );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Updates widgets settings data.
	 * @param  array  $widget
	 * @param  string $widget_type
	 * @param  int    $instance_id
	 * @param  array  $demo_data
	 * @return array
	 */
	public function update_widget_data( $widget, $widget_type, $instance_id, $demo_data ) {
		if ( 'nav_menu' == $widget_type ) {
			$nav_menu = wp_get_nav_menu_object( $widget['title'] );

			if ( is_object( $nav_menu ) && $nav_menu->term_id ) {
				$widget['nav_menu'] = $nav_menu->term_id;
			}
		} elseif ( ! empty( $demo_data['widgets_data_update'] ) ) {
			foreach ( $demo_data['widgets_data_update'] as $dropdown_type => $dropdown_data ) {
				if ( ! in_array( $dropdown_type, array( 'dropdown_pages', 'dropdown_categories' ) ) ) {
					continue;
				}

				// Format the value based on dropdown type.
				switch ( $dropdown_type ) {
					case 'dropdown_pages':
						foreach ( $dropdown_data as $widget_id => $widget_data ) {
							if ( ! empty( $widget_data[ $instance_id ] ) && $widget_id == $widget_type ) {
								foreach ( $widget_data[ $instance_id ] as $widget_key => $widget_value ) {
									$page = get_page_by_title( $widget_value );

									if ( is_object( $page ) && $page->ID ) {
										$widget[ $widget_key ] = $page->ID;
									}
								}
							}
						}
					break;
					case 'dropdown_categories':
						foreach ( $dropdown_data as $taxonomy => $taxonomy_data ) {
							if ( ! taxonomy_exists( $taxonomy ) ) {
								continue;
							}

							foreach ( $taxonomy_data as $widget_id => $widget_data ) {
								if ( ! empty( $widget_data[ $instance_id ] ) && $widget_id == $widget_type ) {
									foreach ( $widget_data[ $instance_id ] as $widget_key => $widget_value ) {
										$term = get_term_by( 'name', $widget_value, $taxonomy );

										if ( is_object( $term ) && $term->term_id ) {
											$widget[ $widget_key ] = $term->term_id;
										}
									}
								}
							}
						}
					break;
				}
			}
		}

		return $widget;
	}

	/**
	 * Update customizer settings data.
	 * @param  array $data
	 * @param  array $demo_data
	 * @return array
	 */
	public function update_customizer_data( $data, $demo_data ) {
		if ( ! empty( $demo_data['customizer_data_update'] ) ) {
			foreach ( $demo_data['customizer_data_update'] as $data_type => $data_value ) {
				if ( ! in_array( $data_type, array( 'pages', 'categories', 'nav_menu_locations' ) ) ) {
					continue;
				}

				// Format the value based on data type.
				switch ( $data_type ) {
					case 'pages':
						foreach ( $data_value as $option_key => $option_value ) {
							if ( ! empty( $data['mods'][ $option_key ] ) ) {
								$page = get_page_by_title( $option_value );

								if ( is_object( $page ) && $page->ID ) {
									$data['mods'][ $option_key ] = $page->ID;
								}
							}
						}
					break;
					case 'categories':
						foreach ( $data_value as $taxonomy => $taxonomy_data ) {
							if ( ! taxonomy_exists( $taxonomy ) ) {
								continue;
							}

							foreach ( $taxonomy_data as $option_key => $option_value ) {
								if ( ! empty( $data['mods'][ $option_key ] ) ) {
									$term = get_term_by( 'name', $option_value, $taxonomy );

									if ( is_object( $term ) && $term->term_id ) {
										$data['mods'][ $option_key ] = $term->term_id;
									}
								}
							}
						}
					break;
					case 'nav_menu_locations':
						$nav_menus = wp_get_nav_menus();

						if ( ! empty( $nav_menus ) ) {
							foreach ( $nav_menus as $nav_menu ) {
								if ( is_object( $nav_menu ) ) {
									foreach ( $data_value as $location => $location_name ) {
										if ( $nav_menu->name == $location_name ) {
											$data['mods'][ $data_type ][ $location ] = $nav_menu->term_id;
										}
									}
								}
							}
						}
					break;
				}
			}
		}

		return $data;
	}

	/**
	 * Recursive function to address n level deep layoutbuilder data update.
	 * @param  array $panels_data
	 * @param  string $data_type
	 * @param  array $data_value
	 * @return array
	 */
	public function siteorigin_recursive_update( $panels_data, $data_type, $data_value ) {
		static $instance = 0;

		foreach ( $panels_data as $panel_type => $panel_data ) {
			// Format the value based on panel type.
			switch ( $panel_type ) {
				case 'grids':
					foreach ( $panel_data as $instance_id => $grid_instance ) {
						if ( ! empty( $data_value['data_update']['grids_data'] ) ) {
							foreach ( $data_value['data_update']['grids_data'] as $grid_id => $grid_data ) {
								if ( ! empty( $grid_data['style'] ) && $instance_id === $grid_id ) {
									$level = isset( $grid_data['level'] ) ? $grid_data['level'] : (int) 0;
									if ( $level == $instance ) {
										foreach ( $grid_data['style'] as $style_key => $style_value ) {
											if ( empty( $style_value ) ) {
												continue;
											}

											// Format the value based on style key.
											switch ( $style_key ) {
												case 'background_image_attachment':
													$attachment_id = tg_get_attachment_id( $style_value );

													if ( 0 !== $attachment_id ) {
														$grid_instance['style'][ $style_key ] = $attachment_id;
													}
												break;
												default:
													$grid_instance['style'][ $style_key ] = $style_value;
												break;
											}
										}
									}
								}
							}
						}

						// Update panel grids data.
						$panels_data['grids'][ $instance_id ] = $grid_instance;
				   }
				break;

				case 'widgets':
					foreach ( $panel_data as $instance_id => $widget_instance ) {
						if ( isset( $widget_instance['panels_data']['widgets'] ) ) {
							$instance = $instance + 1;
							$child_panels_data = $widget_instance['panels_data'];
							$panels_data['widgets'][ $instance_id ]['panels_data'] = $this->siteorigin_recursive_update( $child_panels_data, $data_type, $data_value );
							$instance = $instance - 1;
							continue;
						}

						if ( isset( $widget_instance['nav_menu'] ) && isset( $widget_instance['title'] ) ) {
							$nav_menu = wp_get_nav_menu_object( $widget_instance['title'] );

							if ( is_object( $nav_menu ) && $nav_menu->term_id ) {
								$widget_instance['nav_menu'] = $nav_menu->term_id;
							}
						} elseif ( ! empty( $data_value['data_update']['widgets_data'] ) ) {
							$instance_class = $widget_instance['panels_info']['class'];

							foreach ( $data_value['data_update']['widgets_data'] as $dropdown_type => $dropdown_data ) {
								if ( ! in_array( $dropdown_type, array( 'dropdown_pages', 'dropdown_categories' ) ) ) {
									continue;
								}

								// Format the value based on data type.
								switch ( $dropdown_type ) {
									case 'dropdown_pages':
										foreach ( $dropdown_data as $widget_id => $widget_data ) {
											if ( ! empty( $widget_data[ $instance_id ] ) && $widget_id == $instance_class ) {
												$level = isset( $widget_data['level'] ) ? $widget_data['level'] : (int) 0;

												if ( $level == $instance ) {
													foreach ( $widget_data[ $instance_id ] as $widget_key => $widget_value ) {
														$page = get_page_by_title( $widget_value );

														if ( is_object( $page ) && $page->ID ) {
														 $widget_instance[ $widget_key ] = $page->ID;
														}
													}
												}
											}
										}
									break;
									case 'dropdown_categories':
										foreach ( $dropdown_data as $taxonomy => $taxonomy_data ) {
											if ( ! taxonomy_exists( $taxonomy ) ) {
												continue;
											}

											foreach ( $taxonomy_data as $widget_id => $widget_data ) {
												if ( ! empty( $widget_data[ $instance_id ] ) && $widget_id == $instance_class ) {
													$level = isset( $widget_data['level'] ) ? $widget_data['level'] : (int) 0;

													if ( $level == $instance ) {
														foreach ( $widget_data[ $instance_id ] as $widget_key => $widget_value ) {
															$term = get_term_by( 'name', $widget_value, $taxonomy );

															if ( is_object( $term ) && $term->term_id ) {
																$widget_instance[ $widget_key ] = $term->term_id;
															}
														}
													}
												}
											}
										}
									break;
								}
							}
						}

						$panels_data['widgets'][ $instance_id ] = $widget_instance;
					}
				break;
			}
		}

		return $panels_data;
	}

	/**
	 * Update siteorigin panel settings data.
	 * @param string $demo_id
	 * @param array  $demo_data
	 */
	public function update_siteorigin_data( $demo_id, $demo_data ) {
		if ( ! empty( $demo_data['siteorigin_panels_data_update'] ) ) {
			foreach ( $demo_data['siteorigin_panels_data_update'] as $data_type => $data_value ) {
				if ( ! empty( $data_value['post_title'] ) ) {
					$page = get_page_by_title( $data_value['post_title'] );

					if ( is_object( $page ) && $page->ID ) {
						$panels_data = get_post_meta( $page->ID, 'panels_data', true );

						if ( ! empty( $panels_data ) ) {
							$panels_data = $this->siteorigin_recursive_update( $panels_data, $data_type, $data_value );
						}

						// Update siteorigin panels data.
						update_post_meta( $page->ID, 'panels_data', $panels_data );
					}
				}
			}
		}
	}
}

new TG_Demo_Importer();
