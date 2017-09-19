<?php
/**
 * Admin View: Notice - Reset Wizard Success
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$user = get_user_by( 'id', 1 );

?>
<div id="message" class="notice notice-info is-dismissible">
	<p><?php printf( __( 'WordPress has been reset back to defaults. The user <strong>"%1$s"</strong> was recreated with its previous password.', 'themegrill-demo-importer' ), $user->user_login ); ?></p>
</div>
