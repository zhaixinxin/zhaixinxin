<?php
// add any new or customised functions here
add_action( 'wp_enqueue_scripts', 'metro_pirate_enqueue_styles' );
function metro_pirate_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	// Loads our main stylesheet.
	wp_enqueue_style( 'metro_pirate-child-style', get_stylesheet_uri() );
}	

add_action( 'after_setup_theme', 'metro_pirate_setup' );
function metro_pirate_setup() {
    load_child_theme_textdomain( 'metro-pirate', get_stylesheet_directory() . '/languages' );
}


/**
 * Notice in Customize to announce the theme is not maintained anymore
 */

function metro_pirate_customize_register( $wp_customize ) {

	require_once get_stylesheet_directory() . '/class-ti-notify.php';

	$wp_customize->register_section_type( 'Ti_Notify' );

	$wp_customize->add_section(
		new Ti_Notify(
			$wp_customize,
			'ti-notify',
			array( /* translators: link to our latest theme */
				'text'     => sprintf( __( 'This theme is not maintained anymore, check-out our latest free one-page theme: %1$s.','metro-pirate' ), sprintf( '<a href="' . admin_url( 'theme-install.php?theme=hestia' ) . '">%s</a>', 'Hestia' ) ),
				'priority' => 0,
			)
		)
	);

	$wp_customize->add_setting( 'metro-pirate-notify', array(
		'sanitize_callback' => 'esc_html',
	) );

	$wp_customize->add_control( 'metro-pirate-notify', array(
		'label'    => __( 'Notification', 'metro-pirate' ),
		'section'  => 'ti-notify',
		'priority' => 1,
	) );
}

add_action( 'customize_register', 'metro_pirate_customize_register' );

/**
 * Notice in admin dashboard to announce the theme is not maintained anymore
 */

function metro_pirate_admin_notice() {

	global $pagenow;

	if ( is_admin() && ( 'themes.php' == $pagenow ) && isset( $_GET['activated'] ) ) {
		echo '<div class="updated notice is-dismissible"><p>';
		/* translators: link to our latest theme */
		printf( __( 'This theme is not maintained anymore, check-out our latest free one-page theme: %1$s.','metro-pirate' ), sprintf( '<a href="' . admin_url( 'theme-install.php?theme=hestia' ) . '">%s</a>', 'Hestia' ) );
		echo '</p></div>';
	}
}

add_action( 'admin_notices', 'metro_pirate_admin_notice', 99 );