<?php


function logincust_customize_register($wp_customize)
{
    $wp_customize->add_panel( 'logincust_panel', array(
        'priority'       => 30,
        'capability'     => 'edit_theme_options',
        'title'          => __('Login Customizer', LOGINCUST_TEXTDOMAIN),
        'description'    => __('This section allows you to customize the login page of your website.<br/>Login Customizer by <a target="_blank" rel="nofollow" href="https://themeisle.com">ThemeIsle</a>', LOGINCUST_TEXTDOMAIN),
    ));

    $wp_customize->add_section('logincust_logo_section', array(
        'priority' => 5,
        'title' => __('Logo', LOGINCUST_TEXTDOMAIN),
        'panel'  => 'logincust_panel',
    ));

    $wp_customize->add_section('logincust_background_section', array(
        'priority' => 10,
        'title' => __('Background', LOGINCUST_TEXTDOMAIN),
        'panel'  => 'logincust_panel',
    ));

    $wp_customize->add_section('logincust_form_bg_section', array(
        'priority' => 15,
        'title' => __('Form Background', LOGINCUST_TEXTDOMAIN),
        'panel'  => 'logincust_panel',
    ));

    $wp_customize->add_section('logincust_form_section', array(
        'priority' => 20,
        'title' => __('Form Styling', LOGINCUST_TEXTDOMAIN),
        'panel'  => 'logincust_panel',
    ));

    $wp_customize->add_section('logincust_field_section', array(
        'priority' => 25,
        'title' => __('Fields Styling', LOGINCUST_TEXTDOMAIN),
        'panel'  => 'logincust_panel',
    ));

    $wp_customize->add_section('logincust_button_section', array(
        'priority' => 30,
        'title' => __('Button Styling', LOGINCUST_TEXTDOMAIN),
        'panel'  => 'logincust_panel',
    ));

    $wp_customize->add_section('logincust_other_section', array(
        'priority' => 35,
        'title' => __('Other', LOGINCUST_TEXTDOMAIN),
        'panel'  => 'logincust_panel',
    ));

	$wp_customize->add_section('logincust_security_section', array(
		'priority' => 40,
		'title' => __('Security', LOGINCUST_TEXTDOMAIN),
		'panel'  => 'logincust_panel',
	));

    $wp_customize->add_setting('logincust_logo', array(
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'logincust_logo', array(
        'label' => __('Login Logo', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_logo_section',
        'priority' => 5,
        'settings' => 'logincust_logo'
    )));

    $wp_customize->add_setting('logincust_logo_width', array(
        'default' => '84px',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_logo_width', array(
        'label' => __('Logo Width', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_logo_section',
        'priority' => 10,
        'settings' => 'logincust_logo_width'
    ));

    $wp_customize->add_setting('logincust_logo_height', array(
        'default' => '84px',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_logo_height', array(
        'label' => __('Logo Height', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_logo_section',
        'priority' => 15,
        'settings' => 'logincust_logo_height'
    ));

    $wp_customize->add_setting('logincust_logo_padding', array(
        'default' => '5px',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_logo_padding', array(
        'label' => __('Padding Bottom', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_logo_section',
        'priority' => 20,
        'settings' => 'logincust_logo_padding'
    ));

    $wp_customize->add_setting('logincust_bg_image', array(
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'logincust_bg_image', array(
        'label' => __('Background Image', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_background_section',
        'priority' => 5,
        'settings' => 'logincust_bg_image'
    )));

    $wp_customize->add_setting('logincust_bg_color', array(
        'default' => '#F1F1F1',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_bg_color', array(
        'label' => __('Background Color', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_background_section',
        'priority' => 10,
        'settings' => 'logincust_bg_color'
    )));

    $wp_customize->add_setting('logincust_bg_size', array(
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_bg_size', array(
        'label' => __('Background Size', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_background_section',
        'priority' => 15,
        'settings' => 'logincust_bg_size'
    ));

    $wp_customize->add_setting('logincust_form_bg_image', array(
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'logincust_form_bg_image', array(
        'label' => __('Background Image', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_form_bg_section',
        'priority' => 5,
        'settings' => 'logincust_form_bg_image'
    )));

    $wp_customize->add_setting('logincust_form_bg_color', array(
        'default' => '#FFF',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_form_bg_color', array(
        'label' => __('Background Color', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_form_bg_section',
        'priority' => 10,
        'settings' => 'logincust_form_bg_color'
    )));

    $wp_customize->add_setting('logincust_form_width', array(
        'default' => '320px',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_form_width', array(
        'label' => __('Width', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_form_section',
        'priority' => 15,
        'settings' => 'logincust_form_width'
    ));

    $wp_customize->add_setting('logincust_form_height', array(
        'default' => '194px',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_form_height', array(
        'label' => __('Height', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_form_section',
        'priority' => 20,
        'settings' => 'logincust_form_height'
    ));

    $wp_customize->add_setting('logincust_form_padding', array(
        'default' => '26px 24px 46px',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_form_padding', array(
        'label' => __('Padding', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_form_section',
        'priority' => 25,
        'settings' => 'logincust_form_padding'
    ));

    $wp_customize->add_setting('logincust_form_border', array(
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_form_border', array(
        'label' => __('Border (Example: 2px dotted black) ', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_form_section',
        'priority' => 30,
        'settings' => 'logincust_form_border'
    ));

    $wp_customize->add_setting('logincust_field_width', array(
        'default' => '100%',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_field_width', array(
        'label' => __('Input Field Width', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_field_section',
        'priority' => 5,
        'settings' => 'logincust_field_width'
    ));

    $wp_customize->add_setting('logincust_field_margin', array(
        'default' => '2px 6px 16px 0px',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_field_margin', array(
        'label' => __('Input Field Margin', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_field_section',
        'priority' => 10,
        'settings' => 'logincust_field_margin'
    ));

    $wp_customize->add_setting('logincust_field_bg', array(
        'default' => '#FFF',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_field_bg', array(
        'label' => __('Input Field Background', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_field_section',
        'priority' => 15,
        'settings' => 'logincust_field_bg'
    )));

    $wp_customize->add_setting('logincust_field_color', array(
        'default' => '#333',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_field_color', array(
        'label' => __('Input Field Color', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_field_section',
        'priority' => 20,
        'settings' => 'logincust_field_color'
    )));

    $wp_customize->add_setting('logincust_field_label', array(
        'default' => '#777',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_field_label', array(
        'label' => __('Label Color', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_field_section',
        'priority' => 25,
        'settings' => 'logincust_field_label'
    )));

    $wp_customize->add_setting('logincust_button_bg', array(
        'default' => '#2EA2CC',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_button_bg', array(
        'label' => __('Button Background', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_button_section',
        'priority' => 5,
        'settings' => 'logincust_button_bg'
    )));

    $wp_customize->add_setting('logincust_button_border', array(
        'default' => '#0074A2',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_button_border', array(
        'label' => __('Button Border', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_button_section',
        'priority' => 10,
        'settings' => 'logincust_button_border'
    )));

    $wp_customize->add_setting('logincust_button_hover_bg', array(
        'default' => '#1E8CBE',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_button_hover_bg', array(
        'label' => __('Button Background (Hover)', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_button_section',
        'priority' => 15,
        'settings' => 'logincust_button_hover_bg'
    )));

    $wp_customize->add_setting('logincust_button_hover_border', array(
        'default' => '#0074A2',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_button_hover_border', array(
        'label' => __('Button Border (Hover)', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_button_section',
        'priority' => 20,
        'settings' => 'logincust_button_hover_border'
    )));

    $wp_customize->add_setting('logincust_button_shadow', array(
        'default' => '#78C8E6',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_button_shadow', array(
        'label' => __('Button Box Shadow', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_button_section',
        'priority' => 25,
        'settings' => 'logincust_button_shadow'
    )));

    $wp_customize->add_setting('logincust_button_color', array(
        'default' => '#FFF',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_button_color', array(
        'label' => __('Button Color', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_button_section',
        'priority' => 30,
        'settings' => 'logincust_button_color'
    )));

    $wp_customize->add_setting('logincust_other_color', array(
        'default' => '#999',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_other_color', array(
        'label' => __('Text Color', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_other_section',
        'priority' => 5,
        'settings' => 'logincust_other_color'
    )));

    $wp_customize->add_setting('logincust_other_color_hover', array(
        'default' => '#2EA2CC',
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'logincust_other_color_hover', array(
        'label' => __('Text Color (Hover)', LOGINCUST_TEXTDOMAIN),
        'section' => 'logincust_other_section',
        'priority' => 10,
        'settings' => 'logincust_other_color_hover'
    )));

    $wp_customize->add_setting('logincust_other_css', array(
        'type' => 'option',
        'capability' => 'edit_theme_options',
    ));

    $wp_customize->add_control('logincust_other_css', array(
        'label' => __('Custom CSS', LOGINCUST_TEXTDOMAIN),
        'type' => 'textarea',
        'section' => 'logincust_other_section',
        'priority' => 15,
        'settings' => 'logincust_other_css'
    ));

}
add_action('customize_register', 'logincust_customize_register');

function logincust_customizer() {
$logo_url = get_option('logincust_logo');
$logo_width = get_option('logincust_logo_width');
$logo_height = get_option('logincust_logo_height');
$logo_padding = get_option('logincust_logo_padding');
$bg_img = get_option('logincust_bg_image');
$bg_color = get_option('logincust_bg_color');
$bg_size = get_option('logincust_bg_size');
$form_bg_image = get_option('logincust_form_bg_image');
$form_bg_color = get_option('logincust_form_bg_color');
$form_width = get_option('logincust_form_width');
$form_height = get_option('logincust_form_height');
$form_padding = get_option('logincust_form_padding');
$form_border = get_option('logincust_form_border');
$field_width = get_option('logincust_field_width');
$field_margin = get_option('logincust_field_margin');
$field_bg = get_option('logincust_field_bg');
$field_color = get_option('logincust_field_color');
$field_label = get_option('logincust_field_label');
$button_bg = get_option('logincust_button_bg');
$button_border = get_option('logincust_button_border');
$button_shadow = get_option('logincust_button_shadow');
$button_color = get_option('logincust_button_color');
$button_hover_bg = get_option('logincust_button_hover_bg');
$button_hover_border = get_option('logincust_button_hover_border');
$other_color = get_option('logincust_other_color');
$other_color_hover = get_option('logincust_other_color_hover');
$other_css = get_option('logincust_other_css');
?>
<style type="text/css">
    html, body {
<?php if( !empty($bg_img)) : ?>
    background-image: url(<?php echo $bg_img; ?>) !important;
<?php endif; ?>
<?php if( !empty($bg_color)) : ?>
    background-color: <?php echo $bg_color; ?> !important;
<?php endif; ?>
<?php if( !empty($bg_url)) : ?>
    background-size: <?php echo $bg_size; ?> !important;
<?php endif; ?>
    }
    body.login div#login h1 a {
<?php if( !empty($logo_url)) : ?>
    background-image: url(<?php echo $logo_url; ?>) !important;
<?php endif; ?>
<?php if( !empty($logo_width)) : ?>
    width: <?php echo $logo_width; ?> !important;
<?php endif; ?>
<?php if( !empty($logo_height)) : ?>
    height: <?php echo $logo_height; ?> !important;
<?php endif; ?>
<?php if( !empty($logo_width) || !empty($logo_height)) : ?>
    background-size: <?php echo $logo_width; ?> <?php echo $logo_height; ?> !important;
<?php endif; ?>
<?php if( !empty($logo_padding)) : ?>
    padding-bottom: <?php echo $logo_padding; ?> !important;
<?php endif; ?>
}
#loginform {
<?php if( !empty($form_bg_image)) : ?>
    background-image: url(<?php echo $form_bg_image; ?>) !important;
<?php endif; ?>
<?php if( !empty($form_bg_color)) : ?>
    background-color: <?php echo $form_bg_color; ?> !important;
<?php endif; ?>
<?php if( !empty($form_height)) : ?>
    height: <?php echo $form_height; ?> !important;
<?php endif; ?>
<?php if( !empty($form_padding)) : ?>
    padding: <?php echo $form_padding; ?> !important;
<?php endif; ?>
<?php if( !empty($form_border)) : ?>
    border: <?php echo $form_border; ?> !important;
<?php endif; ?>
}
#login {
<?php if( !empty($form_width)) : ?>
    width: <?php echo $form_width; ?> !important;
<?php endif; ?>
}
.login form .input, .login input[type="text"] {
<?php if( !empty($field_width)) : ?>
    width: <?php echo $field_width; ?> !important;
<?php endif; ?>
<?php if( !empty($field_margin)) : ?>
    margin: <?php echo $field_margin; ?> !important;
<?php endif; ?>
<?php if( !empty($field_bg)) : ?>
    background: <?php echo $field_bg; ?> !important;
<?php endif; ?>
<?php if( !empty($field_color)) : ?>
    color: <?php echo $field_color; ?> !important;
<?php endif; ?>
}
.login label {
<?php if( !empty($field_label)) : ?>
    color: <?php echo $field_label; ?> !important;
<?php endif; ?>
}
.wp-core-ui .button-primary {
<?php if( !empty($button_bg)) : ?>
    background: <?php echo $button_bg; ?> !important;
<?php endif; ?>
<?php if( !empty($button_border)) : ?>
    border-color: <?php echo $button_border; ?> !important;
<?php endif; ?>
<?php if( !empty($button_shadow)) : ?>
    box-shadow: 0px 1px 0px <?php echo $button_shadow; ?> inset, 0px 1px 0px rgba(0, 0, 0, 0.15);
<?php endif; ?>
<?php if( !empty($button_color)) : ?>
    color: <?php echo $button_color; ?> !important;
<?php endif; ?>
}
.wp-core-ui .button-primary.focus, .wp-core-ui .button-primary.hover, .wp-core-ui .button-primary:focus, .wp-core-ui .button-primary:hover {
<?php if( !empty($button_hover_bg)) : ?>
    background: <?php echo $button_hover_bg; ?> !important;
<?php endif; ?>
<?php if( !empty($button_hover_border)) : ?>
    border-color: <?php echo $button_hover_border; ?> !important;
<?php endif; ?>
}
.login #backtoblog a, .login #nav a {
<?php if( !empty($other_color)) : ?>
    color: <?php echo $other_color; ?> !important;
<?php endif; ?>
}
.login #backtoblog a:hover, .login #nav a:hover, .login h1 a:hover {
<?php if( !empty($other_color_hover)) : ?>
    color: <?php echo $other_color_hover; ?> !important;
<?php endif; ?>
}
<?php if( !empty($other_css)) : ?>
    <?php echo $other_css; ?>
<?php endif; ?>
</style>
<?php
}

add_action( 'login_enqueue_scripts', 'logincust_customizer' );

function logincust_login_logo_url() {
    return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'logincust_login_logo_url' );

function logincust_login_logo_url_title() {
    $title = get_bloginfo( 'name', 'display' );
    return $title;
}
add_filter( 'login_headertitle', 'logincust_login_logo_url_title' );

?>