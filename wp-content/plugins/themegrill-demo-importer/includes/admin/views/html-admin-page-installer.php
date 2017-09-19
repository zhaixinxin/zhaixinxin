<?php
/**
 * Admin View: Page - Installer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $current_filter;

$current_filter    = empty( $_GET['browse'] ) ? 'welcome' : sanitize_title( $_GET['browse'] );
$demo_filter_links = apply_filters( 'themegrill_demo_importer_filter_links_array', array(
	'welcome' => __( 'Welcome', 'themegrill-demo-importer' ),
	'uploads' => __( 'Installed Demos', 'themegrill-demo-importer' ),
	'preview' => __( 'Theme Demos', 'themegrill-demo-importer' ),
) );

?>
<div class="wrap demo-installer">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Demo Importer', 'themegrill-demo-importer' ); ?></h1>

	<?php if ( current_user_can( 'upload_files' ) ) : ?>
		<button type="button" class="upload-view-toggle page-title-action hide-if-no-js" aria-expanded="false"><?php _e( 'Upload Demo', 'themegrill-demo-importer' ); ?></button>
	<?php endif; ?>

	<hr class="wp-header-end">

	<div class="error hide-if-js">
		<p><?php _e( 'The Demo Importer screen requires JavaScript.', 'themegrill-demo-importer' ); ?></p>
	</div>

	<div class="upload-theme">
		<p class="install-help"><?php _e( 'If you have a demo pack in a .zip format, you may install it by uploading it here.', 'themegrill-demo-importer' ); ?></p>
		<form method="post" enctype="multipart/form-data" class="wp-upload-form" action="<?php echo self_admin_url( 'themes.php?page=demo-importer&action=upload-demo' ); ?>">
			<?php wp_nonce_field( 'demo-upload' ); ?>
			<label class="screen-reader-text" for="demozip"><?php _e( 'Demo zip file', 'themegrill-demo-importer' ); ?></label>
			<input type="file" id="demozip" name="demozip" />
			<?php submit_button( __( 'Install Now', 'themegrill-demo-importer' ), 'button', 'install-demo-submit', false ); ?>
		</form>
	</div>

	<h2 class="screen-reader-text hide-if-no-js"><?php _e( 'Filter demos list', 'themegrill-demo-importer' ); ?></h2>

	<div class="wp-filter hide-if-no-js">
		<div class="filter-count">
			<span class="count theme-count demo-count"><?php echo 'preview' == $current_filter ? count( $this->demo_packages ) : count( $this->demo_config ); ?></span>
		</div>

		<ul class="filter-links">
			<?php
				foreach ( $demo_filter_links as $name => $label ) {
					if ( ( empty( $this->demo_config ) && 'uploads' == $name ) || ( empty( $this->demo_packages ) && 'preview' == $name ) ) {
						continue;
					}
					echo '<li><a href="' . admin_url( 'themes.php?page=demo-importer&browse=' . $name ) . '" class="demo-tab ' . ( $current_filter == $name ? 'current' : '' ) . '">' . $label . '</a></li>';
				}
				do_action( 'themegrill_demo_importer_filter_links' );
			?>
		</ul>

		<div class="search-form"></div>
	</div>
	<?php if ( 'welcome' === $current_filter ) : ?>
		<div id="welcome-panel" class="welcome-panel">
			<div class="welcome-panel-content">
				<h2><?php _e( 'Welcome to ThemeGrill Demo Importer!', 'themegrill-demo-importer' ); ?></h2>
				<h3><?php _e( 'Get Started','themegrill-demo-importer' ); ?></h3>
				<div class="welcome-panel-column-container">
					<div class="welcome-panel-column">
						<ul>
							<li><?php printf( __( '1. Visit <a href="%s" target="_blank"><strong>this page</strong></a> and download the theme demo zip file.','themegrill-demo-importer' ), esc_url( self_admin_url( 'themes.php?page=demo-importer&browse=preview' ) ) ); ?></li>
							<li><?php _e( '2. Click on the <strong>Upload Demo</strong> button on the top of this page.','themegrill-demo-importer' ); ?></li>
							<li><?php _e( '3. Browse the downloaded demo zip file and click on <strong>Install Now</strong> button.','themegrill-demo-importer' ); ?></li>
							<li><?php _e( '4. Go to <strong>Installed Demos</strong> tab.','themegrill-demo-importer' ); ?></li>
							<li><?php _e( '5. Click on the <strong>Import</strong> button and wait for few minutes. Done!','themegrill-demo-importer' ); ?></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="welcome-panel-iframe-video">
				<div class="welcome-panel-iframe-video-inner">
					<iframe width="560" height="315" src="<?php echo esc_url( 'https://www.youtube.com/embed/rhiybsv3vUU?rel=0&amp;showinfo=0' ); ?>" allowtransparency="true" frameborder="0" scrolling="no" allowfullscreen mozallowfullscreen webkitallowfullscreen oallowfullscreen msallowfullscreen></iframe>
				</div>
			</div>
		</div>
	<?php else : ?>
		<h2 class="screen-reader-text hide-if-no-js"><?php _e( 'Available demos list', 'themegrill-demo-importer' ); ?></h2>
		<?php
			if ( in_array( $current_filter, array( 'uploads', 'preview' ) ) ) {
				include_once( dirname( __FILE__ ) . "/html-admin-page-installer-{$current_filter}.php" );
			}
			do_action( 'themegrill_demo_importer_' . $current_filter );
		?>
		<p class="no-themes"><?php _e( 'No demos found. Try a different search.', 'themegrill-demo-importer' ); ?></p>
	<?php endif; ?>
</div>

<script id="tmpl-demo" type="text/template">
	<# if ( data.screenshot ) { #>
		<div class="theme-screenshot">
			<img src="{{ data.screenshot }}" alt="" />
		</div>
	<# } else { #>
		<div class="theme-screenshot blank"></div>
	<# } #>

	<span class="more-details" id="{{ data.id }}-action"><?php _e( 'Demo Details', 'themegrill-demo-importer' ); ?></span>
	<div class="theme-author"><?php
		/* translators: %s: Demo author name */
		printf( __( 'By %s', 'themegrill-demo-importer' ), '{{{ data.author }}}' );
	?></div>

	<# if ( data.active ) { #>
		<h2 class="theme-name" id="{{ data.id }}-name"><?php
			/* translators: %s: Demo name */
			printf( __( '<span>Imported:</span> %s', 'themegrill-demo-importer' ), '{{{ data.name }}}' );
		?></h2>
	<# } else { #>
		<h2 class="theme-name" id="{{ data.id }}-name">{{{ data.name }}}</h2>
	<# } #>

	<div class="theme-actions">
		<# if ( data.active ) { #>
			<a class="button button-primary live-preview" target="_blank" href="{{{ data.actions.preview }}}"><?php _e( 'Live Preview', 'themegrill-demo-importer' ); ?></a>
		<# } else { #>
			<# if ( ! _.isEmpty( data.hasNotice ) ) { #>
				<# if ( data.hasNotice['required_theme'] ) { #>
					<a class="button button-primary hide-if-no-js tips demo-import disabled" href="#" data-name="{{ data.name }}" data-slug="{{ data.id }}" data-tip="<?php echo esc_attr( sprintf( __( 'Required %s theme must be activated to import this demo.', 'themegrill-demo-importer' ), '{{{ data.theme }}}' ) ); ?>"><?php _e( 'Import', 'themegrill-demo-importer' ); ?></a>
				<# } else if ( data.hasNotice['required_plugins'] ) { #>
					<a class="button button-primary hide-if-no-js tips demo-import disabled" href="#" data-name="{{ data.name }}" data-slug="{{ data.id }}" data-tip="<?php echo esc_attr( 'Required Plugin must be activated to import this demo.', 'themegrill-demo-importer' ); ?>"><?php _e( 'Import', 'themegrill-demo-importer' ); ?></a>
				<# } #>
			<# } else { #>
				<?php
				/* translators: %s: Demo name */
				$aria_label = sprintf( _x( 'Import %s', 'demo', 'themegrill-demo-importer' ), '{{ data.name }}' );
				?>
				<a class="button button-primary hide-if-no-js demo-import" href="#" data-name="{{ data.name }}" data-slug="{{ data.id }}" aria-label="<?php echo $aria_label; ?>"><?php _e( 'Import', 'themegrill-demo-importer' ); ?></a>
			<# } #>
			<a class="button button-secondary demo-preview" target="_blank" href="{{{ data.actions.demo_url }}}"><?php _e( 'Preview', 'themegrill-demo-importer' ); ?></a>
		<# } #>
	</div>

	<# if ( data.imported ) { #>
		<div class="notice notice-success notice-alt"><p><?php _ex( 'Imported', 'demo', 'themegrill-demo-importer' ); ?></p></div>
	<# } #>
</script>

<script id="tmpl-demo-single" type="text/template">
	<div class="theme-backdrop"></div>
	<div class="theme-wrap wp-clearfix">
		<div class="theme-header">
			<button class="left dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show previous demo', 'themegrill-demo-importer' ); ?></span></button>
			<button class="right dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Show next demo', 'themegrill-demo-importer' ); ?></span></button>
			<button class="close dashicons dashicons-no"><span class="screen-reader-text"><?php _e( 'Close details dialog', 'themegrill-demo-importer' ); ?></span></button>
		</div>
		<div class="theme-about wp-clearfix">
			<div class="theme-screenshots">
			<# if ( data.screenshot ) { #>
				<div class="screenshot"><img src="{{ data.screenshot }}" alt="" /></div>
			<# } else { #>
				<div class="screenshot blank"></div>
			<# } #>
			</div>

			<div class="theme-info">
				<# if ( data.active ) { #>
					<span class="current-label"><?php _e( 'Imported Demo', 'themegrill-demo-importer' ); ?></span>
				<# } #>
				<h2 class="theme-name">{{{ data.name }}}<span class="theme-version"><?php printf( __( 'Version: %s', 'themegrill-demo-importer' ), '{{ data.version }}' ); ?></span></h2>
				<p class="theme-author"><?php printf( __( 'By %s', 'themegrill-demo-importer' ), '{{{ data.authorAndUri }}}' ); ?></p>

				<# if ( ! _.isEmpty( data.hasNotice ) ) { #>
					<div class="notice demo-message notice-warning notice-alt">
						<# if ( data.hasNotice['required_theme'] ) { #>
							<p class="demo-notice"><?php printf( esc_html__( 'Required %s theme must be activated to import this demo.', 'themegrill-demo-importer' ), '<strong>{{{ data.theme }}}</strong>' ); ?></p>
						<# } else if ( data.hasNotice['required_plugins'] ) { #>
							<p class="demo-notice"><?php _e( 'Required Plugin must be activated to import this demo.', 'themegrill-demo-importer' ); ?></p>
						<# } #>
					</div>
				<# } #>
				<p class="theme-description">{{{ data.description }}}</p>

				<h3 class="plugins-info"><?php _e( 'Plugins Information', 'themegrill-demo-importer' ); ?></h3>

				<form method="post" id="bulk-action-form">
					<?php wp_nonce_field( 'bulk-plugins-activate' ); ?>
					<table class="plugins-list-table widefat striped">
						<thead>
							<tr>
								<td id="cb" class="manage-column check-column">
									<label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'themegrill-demo-importer' ); ?></label>
									<input id="cb-select-all-1" type="checkbox">
								</td>
								<th scope="col" class="manage-column plugin-name"><?php esc_html_e( 'Plugin Name', 'themegrill-demo-importer' ); ?></th>
								<th scope="col" class="manage-column plugin-type"><?php esc_html_e( 'Type', 'themegrill-demo-importer' ); ?></th>
								<th scope="col" class="manage-column plugin-status"><?php esc_html_e( 'Status', 'themegrill-demo-importer' ); ?></th>
							</tr>
						</thead>
						<tbody id="the-list">
							<# if ( ! _.isEmpty( data.plugins ) ) { #>
								<# _.each( data.plugins, function( plugin, slug ) { #>
									<# var checkboxIdPrefix = _.uniqueId( 'checkbox_' ) #>
									<tr class="plugin<# if ( ! plugin.is_install ) { #> install<# } #>" data-slug="{{ slug }}" data-plugin="{{ plugin.slug }}" data-name="{{ plugin.name }}">
										<th scope="row" class="check-column">
											<label class="screen-reader-text" for="{{ checkboxIdPrefix }}"><?php printf( __( 'Select %s', 'themegrill-demo-importer' ), '{{ plugin.name }}' ); ?></label>
											<input type="checkbox" name="checked[]" value="{{ plugin.slug }}" id="{{ checkboxIdPrefix }}"<# if ( plugin.required ) { #> data-checked="1" checked="checked" disabled="disabled"<# } #>>
											<# if ( plugin.required ) { #>
												<input type="hidden" name="checked[]" value="{{ plugin.slug }}">
											<# } #>
										</th>
										<td class="plugin-name">
											<# if ( plugin.link ) { #>
												<a href="{{{ plugin.link }}}" target="_blank">{{{ plugin.name }}}</a>
											<# } else { #>
												<a href="<?php printf( esc_url( 'https://wordpress.org/plugins/%s' ), '{{ slug }}' ); ?>" target="_blank">{{ plugin.name }}</a>
											<# } #>
										</td>
										<td class="plugin-type">
											<# if ( plugin.required ) { #>
												<abbr class="required"><?php esc_html_e( 'Required', 'themegrill-demo-importer' ); ?></abbr>
											<# } else { #>
												<abbr class="recommended"><?php esc_html_e( 'Recommended', 'themegrill-demo-importer' ); ?></abbr>
											<# } #>
										</td>
										<td class="plugin-status">
											<# if ( plugin.is_active && plugin.is_install ) { #>
												<span class="active"><?php esc_html_e( 'Active', 'themegrill-demo-importer' ); ?></span>
											<# } else if ( plugin.is_install ) { #>
												<span class="activate-now"><?php esc_html_e( 'Activate', 'themegrill-demo-importer' ); ?></span>
											<# } else { #>
												<span class="install-now"><?php esc_html_e( 'Install Now', 'themegrill-demo-importer' ); ?></span>
											<# } #>
										</td>
									</tr>
								<# }); #>
							<# } else { #>
								<tr class="no-items">
									<td class="colspanchange" colspan="4"><?php _e( 'No plugins are needed to import this demo.', 'themegrill-demo-importer' ); ?></td>
								</tr>
							<# } #>
						</tbody>
						<tfoot>
							<tr>
								<th scope="col" class="manage-column plugin-actions<# if ( ! data.pluginActions['install'] ) { #> installed<# } #>" colspan="4">
									<a href="#" class="button button-primary plugins-install<# if ( ! data.pluginActions['install'] ) { #> disabled<# } #>"><?php _e( 'Install Plugins', 'themegrill-demo-importer' ); ?></a>
									<input type="submit" name="bulk_action" id="bulk_action" class="button button-secondary plugins-activate" value="<?php esc_attr_e( __( 'Activate Plugins', 'themegrill-demo-importer' ) ); ?>"<# if ( ! data.pluginActions['activate'] ) { #> disabled<# } #>>
								</th>
							</tr>
						</tfoot>
					</table>
				</form>

				<# if ( data.tags ) { #>
					<p class="theme-tags"><span><?php _e( 'Tags:', 'themegrill-demo-importer' ); ?></span> {{{ data.tags }}}</p>
				<# } #>
			</div>
		</div>

		<div class="theme-actions">
			<div class="active-theme">
				<a href="{{{ data.actions.preview }}}" class="button button-primary live-preview" target="_blank"><?php _e( 'Live Preview', 'themegrill-demo-importer' ); ?></a>
			</div>
			<div class="inactive-theme">
				<?php
				/* translators: %s: Demo name */
				$aria_label = sprintf( _x( 'Import %s', 'demo', 'themegrill-demo-importer' ), '{{ data.name }}' );
				?>
				<# if ( _.isEmpty( data.hasNotice ) ) { #>
					<# if ( data.imported ) { #>
						<a href="{{{ data.actions.preview }}}" class="button button-primary live-preview" target="_blank"><?php _e( 'Live Preview', 'themegrill-demo-importer' ); ?></a>
					<# } else { #>
						<a class="button button-primary hide-if-no-js demo-import" href="#" data-name="{{ data.name }}" data-slug="{{ data.id }}" aria-label="<?php echo $aria_label; ?>"><?php _e( 'Import', 'themegrill-demo-importer' ); ?></a>
					<# } #>
				<# } #>
				<a class="button button-secondary demo-preview" target="_blank" href="{{{ data.actions.demo_url }}}"><?php _e( 'Preview', 'themegrill-demo-importer' ); ?></a>
			</div>

			<# if ( data.package && data.actions['delete'] ) { #>
				<a href="{{{ data.actions['delete'] }}}" class="button delete-theme delete-demo"><?php _e( 'Delete', 'themegrill-demo-importer' ); ?></a>
			<# } #>
		</div>
	</div>
</script>

<script id="tmpl-demo-preview" type="text/template">
	<a target="_blank" href="{{{ data.actions.preview_url }}}">
		<# if ( data.screenshot ) { #>
			<div class="theme-screenshot">
				<img src="{{ data.screenshot }}" alt="" />
			</div>
		<# } else { #>
			<div class="theme-screenshot blank"></div>
		<# } #>
		<span class="more-details"><?php _e( 'Demo Preview', 'themegrill-demo-importer' ); ?></span>
		<# if ( data.actions.pro_link ) { #>
			<span class="premium-ribbon"><span><?php _e( 'Pro', 'themegrill-demo-importer' ); ?></span></span>
		<# } #>
	</a>
	<div class="theme-author"><?php
		/* translators: %s: Demo author name */
		printf( __( 'By %s', 'themegrill-demo-importer' ), '{{{ data.author }}}' );
	?></div>
	<h3 class="theme-name">{{ data.name }}</h3>

	<div class="theme-actions">
		<# if ( ! data.installed && ! data.actions.pro_link ) { #>
			<?php
			/* translators: %s: Demo name */
			$aria_label = sprintf( _x( 'Download %s', 'demo', 'themegrill-demo-importer' ), '{{ data.name }}' );
			?>
			<a class="button button-primary demo-download" data-name="{{ data.name }}" href="{{ data.actions.download_url }}" aria-label="<?php echo esc_attr( $aria_label ); ?>"><?php _e( 'Download', 'themegrill-demo-importer' ); ?></a>
		<# } else if ( data.actions.pro_link ) { #>
			<?php
			/* translators: %s: Demo name */
			$aria_label = sprintf( _x( 'View %s Pro', 'demo', 'themegrill-demo-importer' ), '{{ data.name }}' );
			?>
			<a class="button button-primary demo-premium" target="_blank" data-name="{{ data.name }}" href="{{ data.actions.pro_link }}" aria-label="<?php echo esc_attr( $aria_label ); ?>"><?php _e( 'View Pro', 'themegrill-demo-importer' ); ?></a>
		<# } #>
		<a class="button button-secondary demo-preview" target="_blank" href="{{{ data.actions.preview_url }}}"><?php _e( 'Preview', 'themegrill-demo-importer' ); ?></a>
	</div>

	<# if ( data.installed ) { #>
		<div class="notice notice-success notice-alt"><p><?php _ex( 'Installed', 'demo', 'themegrill-demo-importer' ); ?></p></div>
	<# } #>
</script>

<?php
wp_print_request_filesystem_credentials_modal();
wp_print_admin_notice_templates();
tg_print_admin_notice_templates();
