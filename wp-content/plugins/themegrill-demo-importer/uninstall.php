<?php
/**
 * ThemeGrill Demo Importer Uninstall
 *
 * Uninstalls the plugin and associated data.
 *
 * @author   ThemeGrill
 * @category Core
 * @package  Importer/Unistaller
 * @version  1.3.4
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

/*
 * Only remove ALL demo importer data if TGDM_REMOVE_ALL_DATA constant is set to true in user's
 * wp-config.php. This is to prevent data loss when deleting the plugin from the backend
 * and to ensure only the site owner can perform this action.
 */
if ( defined( 'TGDM_REMOVE_ALL_DATA' ) && true === TGDM_REMOVE_ALL_DATA ) {
	// Delete options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'themegrill_demo_importer\_%';" );
}
