<?php
/**
 * Admin View: Page - Demo Preview
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="theme-browser rendered">
	<div class="themes wp-clearfix">
		<?php foreach ( $demos as $demo ) : ?>
			<div class="theme" tabindex="0">
				<a target="_blank" href="<?php echo esc_url( $demo['actions']['preview_url'] ); ?>">
					<?php if ( $demo['screenshot'] ) : ?>
						<div class="theme-screenshot">
							<img src="<?php echo esc_url( $demo['screenshot'] ); ?>" alt="" />
						</div>
					<?php else : ?>
						<div class="theme-screenshot blank"></div>
					<?php endif; ?>
					<span class="more-details"><?php _e( 'Demo Preview', 'themegrill-demo-importer' ); ?></span>
					<?php if ( $demo['actions']['pro_link'] ) : ?>
						<span class="premium-ribbon"><span><?php _e( 'Pro', 'themegrill-demo-importer' ); ?></span></span>
					<?php endif; ?>
				</a>
				<div class="theme-author"><?php
					/* translators: %s: Demo author name */
					printf( __( 'By %s', 'themegrill-demo-importer' ), $demo['author'] );
				?></div>
				<h3 class="theme-name"><?php echo esc_html( $demo['name'] ); ?></h3>

				<div class="theme-actions">
					<?php if ( ! $demo['installed'] && ! $demo['actions']['pro_link'] ) : ?>
						<?php
						/* translators: %s: Demo name */
						$aria_label = sprintf( _x( 'Download %s', 'demo', 'themegrill-demo-importer' ), esc_attr( $demo['name'] ) );
						?>
						<a class="button button-primary demo-download" data-name="<?php echo esc_attr( $demo['name'] ); ?>" href="<?php echo esc_url( $demo['actions']['download_url'] ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>"><?php _e( 'Download', 'themegrill-demo-importer' ); ?></a>
					<?php elseif ( $demo['actions']['pro_link'] ) : ?>
						<?php
						/* translators: %s: Demo name */
						$aria_label = sprintf( _x( 'View %s Pro', 'demo', 'themegrill-demo-importer' ), esc_attr( $demo['name'] ) );
						?>
						<a class="button button-primary demo-premium" target="_blank" data-name="<?php echo esc_attr( $demo['name'] ); ?>" href="<?php echo esc_url( $demo['actions']['pro_link'] ); ?>" aria-label="<?php echo esc_attr( $aria_label ); ?>"><?php _e( 'View Pro', 'themegrill-demo-importer' ); ?></a>
					<?php endif; ?>
					<a class="button button-secondary demo-preview" target="_blank" href="<?php echo esc_url( $demo['actions']['preview_url'] ); ?>"><?php _e( 'Preview', 'themegrill-demo-importer' ); ?></a>
				</div>

				<?php if ( $demo['installed'] ) : ?>
					<div class="notice notice-success notice-alt inline"><p><?php _ex( 'Installed', 'theme', 'themegrill-demo-importer' ); ?></p></div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
