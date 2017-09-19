<?php
/**
 * Admin View: Notice - Reset Wizard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated themegrill-demo-importer-message">
	<p><?php _e( '<strong>Reset Wizard</strong> &#8211; If you need to reset the WordPress back to default again :)', 'themegrill-demo-importer' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_reset_wordpress', 'true', admin_url( 'themes.php?page=demo-importer' ) ) ); ?>" class="button button-primary themegrill-reset-wordpress"><?php _e( 'Run the Reset Wizard', 'themegrill-demo-importer' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'themegrill-demo-importer-hide-notice', 'reset_notice' ), 'themegrill_demo_importer_hide_notice_nonce', '_themegrill_demo_importer_notice_nonce' ) ); ?>"><?php _e( 'Hide this notice', 'themegrill-demo-importer' ); ?></a></p>
</div>
