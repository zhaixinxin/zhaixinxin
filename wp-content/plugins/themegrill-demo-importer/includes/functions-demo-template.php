<?php
/**
 * Demo Importer Template.
 *
 * Functions for the templating system.
 *
 * @author   ThemeGrill
 * @category Admin
 * @package  Importer/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tg_get_demo_file_url' ) ) {

	/**
	 * Get a demo file URL.
	 *
	 * @param  string $demo_dir demo dir.
	 * @return string the demo data file URL.
	 */
	function tg_get_demo_file_url( $demo_dir ) {
		return apply_filters( 'themegrill_demo_file_url', get_template_directory_uri() . '/inc/demo-importer/demos/' . $demo_dir, $demo_dir );
	}
}

if ( ! function_exists( 'tg_get_demo_file_path' ) ) {

	/**
	 * Get a demo file path.
	 *
	 * @param  string $demo_dir demo dir.
	 * @return string the demo data file path.
	 */
	function tg_get_demo_file_path( $demo_dir ) {
		return apply_filters( 'themegrill_demo_file_path', get_template_directory() . '/inc/demo-importer/demos/' . $demo_dir . '/dummy-data', $demo_dir );
	}
}

if ( ! function_exists( 'tg_get_demo_preview_screenshot_url' ) ) {

	/**
	 * Get the demo preview screenshot URL.
	 *
	 * @param  string $demo_dir
	 * @param  string $current_template
	 * @return string the demo preview screenshot URL.
	 */
	function tg_get_demo_preview_screenshot_url( $demo_dir, $current_template ) {
		$screenshot_theme_path  = get_template_directory() . "/images/demo/{$demo_dir}.jpg";
		$screenshot_plugin_path = TGDM()->plugin_path() . "/assets/images/{$current_template}/{$demo_dir}.jpg";

		if ( file_exists( $screenshot_theme_path ) ) {
			$screenshot_url = get_template_directory_uri() . "/images/demo/{$demo_dir}.jpg";
		} elseif ( file_exists( $screenshot_plugin_path ) ) {
			$screenshot_url = TGDM()->plugin_url() . "/assets/images/{$current_template}/{$demo_dir}.jpg";
		} else {
			$theme_data = wp_get_theme();
			$screenshot_url = $theme_data->get_screenshot();
		}

		return $screenshot_url;
	}
}
