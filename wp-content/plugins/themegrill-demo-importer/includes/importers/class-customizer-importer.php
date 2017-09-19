<?php
/**
 * Customizer importer - import customizer settings.
 *
 * Code adapted from the "Customizer Export/Import" plugin.
 *
 * @class    TG_Customizer_Importer
 * @version  1.0.0
 * @package  Importer/Classes
 * @category Admin
 * @author   ThemeGrill
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TG_Customizer_Importer Class.
 */
class TG_Customizer_Importer {

	/**
	 * Imports uploaded mods and calls WordPress core customize_save actions so
	 * themes that hook into them can act before mods are saved to the database.
	 *
	 * Update: WP core customize_save actions were removed, because of some errors.
	 *
	 * @param  string $import_file Path to the import file.
	 * @param  string $demo_id     The ID of demo being imported.
	 * @param  array  $demo_data   The data of demo being imported.
	 * @return void|WP_Error
	 */
	public static function import( $import_file, $demo_id, $demo_data ) {
		global $wp_customize;

		$temp = get_template();
		$data = maybe_unserialize( file_get_contents( $import_file ) );

		// Data checks.
		if ( ! is_array( $data ) && ( ! isset( $data['template'] ) || ! isset( $data['mods'] ) ) ) {
			return new WP_Error( 'themegrill_customizer_import_data_error', __( 'The customizer import file is not in a correct format. Please make sure to use the correct customizer import file.', 'themegrill-demo-importer' ) );
		}

		if ( $data['template'] !== $temp ) {
			return new WP_Error( 'themegrill_customizer_import_wrong_theme', __( 'The customizer import file is not suitable for current theme. You can only import customizer settings for the same theme or a child theme.', 'themegrill-demo-importer' ) );
		}

		// Import Images.
		if ( apply_filters( 'themegrill_customizer_import_images', true ) ) {
			$data['mods'] = self::import_customizer_images( $data['mods'] );
		}

		// Modify settings array.
		$data = apply_filters( 'themegrill_customizer_demo_import_settings', $data, $demo_data, $demo_id );

		// Import custom options.
		if ( isset( $data['options'] ) ) {

			// Load WordPress Customize Setting Class.
			if ( ! class_exists( 'WP_Customize_Setting' ) ) {
				require_once( ABSPATH . WPINC . '/class-wp-customize-setting.php' );
			}

			// Include Customizer Demo Importer Setting class.
			include_once( dirname( __FILE__ ) . '/customize/class-oc-customize-demo-importer-setting.php' );

			foreach ( $data['options'] as $option_key => $option_value ) {
				$option = new OC_Customize_Demo_Importer_Setting( $wp_customize, $option_key, array(
					'default'    => '',
					'type'       => 'option',
					'capability' => 'edit_theme_options',
				) );

				$option->import( $option_value );
			}
		}

		// Loop through theme mods and update them.
		if ( ! empty( $data['mods'] ) ) {
			foreach ( $data['mods'] as $key => $value ) {
				set_theme_mod( $key, $value );
			}
		}
	}

	/**
	 * Imports images for settings saved as mods.
	 *
	 * @param  array $mods An array of customizer mods.
	 * @return array The mods array with any new import data.
	 */
	private static function import_customizer_images( $mods ) {
		foreach ( $mods as $key => $value ) {
			if ( self::is_image_url( $value ) ) {
				$data = self::media_handle_sideload( $value );
				if ( ! is_wp_error( $data ) ) {
					$mods[ $key ] = $data->url;

					// Handle header image controls.
					if ( isset( $mods[ $key . '_data' ] ) ) {
						$mods[ $key . '_data' ] = $data;
						update_post_meta( $data->attachment_id, '_wp_attachment_is_custom_header', get_stylesheet() );
					}
				}
			}
		}

		return $mods;
	}

	/**
	 * Checks to see whether a url is an image url or not.
	 *
	 * @param  string $url The url to check.
	 * @return bool Whether the url is an image url or not.
	 */
	private static function is_image_url( $url ) {
		if ( is_string( $url ) && preg_match( '/\.(jpg|jpeg|png|gif)/i', $url ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Taken from the core media_sideload_image function and
	 * modified to return an array of data instead of html.
	 *
	 * @param  string $file The image file path.
	 * @return array An array of image data.
	 */
	private static function media_handle_sideload( $file ) {
		$data = new stdClass();

		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
		}

		if ( ! empty( $file ) ) {
			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file, $matches );
			$file_array = array();
			$file_array['name'] = basename( $matches[0] );

			// Download file to temp location.
			$file_array['tmp_name'] = download_url( $file );

			// If error storing temporarily, return the error.
			if ( is_wp_error( $file_array['tmp_name'] ) ) {
				return $file_array['tmp_name'];
			}

			// Do the validation and storage stuff.
			$id = media_handle_sideload( $file_array, 0 );

			// If error storing permanently, unlink.
			if ( is_wp_error( $id ) ) {
				@unlink( $file_array['tmp_name'] );
				return $id;
			}

			// Build the object to return.
			$meta                = wp_get_attachment_metadata( $id );
			$data->attachment_id = $id;
			$data->url           = wp_get_attachment_url( $id );
			$data->thumbnail_url = wp_get_attachment_thumb_url( $id );
			$data->height        = $meta['height'];
			$data->width         = $meta['width'];
		}

		return $data;
	}
}
