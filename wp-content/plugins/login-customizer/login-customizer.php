<?php
/**
 *  Plugin loader
 *
 * @package LOGINCUST
 * @author Themeisle
 * @since 1.0.0
 */

/**
 * Plugin Name: Custom Login Page Customizer
 * Plugin URI: https://themeisle.com/plugins/login-customizer/
 * Description: Custom Login Customizer plugin allows you to easily customize your login page straight from your WordPress Customizer! Awesome, right?
 * Author: Hardeep Asrani
 * Author URI:  https://themeisle.com/
 * Version: 1.1.0
 */

define( 'LOGINCUST_VERSION','1.1.0' );
define( 'LOGINCUST_FREE_PATH', plugin_dir_path( __FILE__ ) );
define( 'LOGINCUST_FREE_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'LOGINCUST_TEXTDOMAIN' ) ) {
	define( 'LOGINCUST_TEXTDOMAIN','login-customizer' );
}
define( 'LOGINCUST_PRO_TEXT',__( '<p class="logincust_pro_text">You need to buy the <a href="http://themeisle.com/plugins/custom-login-customizer-security-addon/" target="_blank">SECURITY ADDON</a> to have this options. </p>',LOGINCUST_TEXTDOMAIN ) );

/**
 * Check if security addon is active
 * @package LOGINCUST
 * @since 1.0.2
 * @version 1.0.0
 * @return bool
 */
function logincust_check_security() {
	return ( defined( 'LOGINCUST_SECURITY_VERSION' ) );
}


include( LOGINCUST_FREE_PATH . 'customizer.php' );
include( LOGINCUST_FREE_PATH . 'option-panel.php' );

require dirname( __FILE__ ) . '/dashboard/dashboard.php';
