<?php
/**
 * Upgrader API: TG_Demo_Installer_Skin class
 *
 * Demo Installer Skin for the WordPress Demo Importer.
 *
 * @class    TG_Demo_Installer_Skin
 * @extends  WP_Upgrader_Skin
 * @version  1.0.0
 * @package  Importer/Classes
 * @category Admin
 * @author   ThemeGrill
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TG_Demo_Installer_Skin Class.
 */
class TG_Demo_Installer_Skin extends WP_Upgrader_Skin {
	public $type;

	/**
	 *
	 * @param array $args
	 */
	public function __construct( $args = array() ) {
		$defaults = array( 'type' => 'web', 'url' => '', 'demo' => '', 'nonce' => '', 'title' => '' );
		$args = wp_parse_args( $args, $defaults );

		$this->type = $args['type'];

		parent::__construct( $args );
	}

	/**
	 * @access public
	 */
	public function after() {
		$install_actions = array();

		$from = isset( $_GET['from'] ) ? wp_unslash( $_GET['from'] ) : 'demos';

		if ( 'web' == $this->type ) {
			$install_actions['demos_page'] = '<a href="' . admin_url( 'themes.php?page=demo-importer&browse=uploads' ) . '" target="_parent">' . __( 'Return to Demo Importer', 'themegrill-demo-importer' ) . '</a>';
		} elseif ( 'upload' == $this->type && 'demos' == $from ) {
			$install_actions['demos_page'] = '<a href="' . admin_url( 'themes.php?page=demo-importer&browse=uploads' ) . '">' . __( 'Return to Demo Importer', 'themegrill-demo-importer' ) . '</a>';
		} else {
			$install_actions['demos_page'] = '<a href="' . admin_url( 'themes.php?page=demo-importer&browse=uploads' ) . '" target="_parent">' . __( 'Return to Demos page', 'themegrill-demo-importer' ) . '</a>';
		}

		/**
		 * Filters the list of action links available following a single demo installation.
		 * @param array $install_actions Array of demo action links.
		 */
		$install_actions = apply_filters( 'themegrill_demo_install_complete_actions', $install_actions );

		if ( ! empty( $install_actions ) ) {
			$this->feedback( implode( ' | ', (array) $install_actions ) );
		}
	}
}
