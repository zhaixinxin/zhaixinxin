<?php
/**
 * Customize API: OC_Customize_Demo_Importer_Setting class
 *
 * @version  1.0.0
 * @package  Importer/Classes
 * @category Customize
 * @author   ThemeGrill
 */

/**
 * Customizer Demo Importer Setting class.
 * @see WP_Customize_Setting
 */
final class OC_Customize_Demo_Importer_Setting extends WP_Customize_Setting {

	/**
	 * Import an option value for this setting.
	 * @param mixed $value The value to update.
	 */
	public function import( $value ) {
		$this->update( $value );
	}
}
