<?php
/**
 * Upgrade API: TG_Demo_Upgrader class
 *
 * Core class used for upgrading/installing demos.
 *
 * It is designed to upgrade/install demo from a local zip, remote zip URL,
 * or uploaded zip file.
 *
 * @see WP_Upgrader
 */
class TG_Demo_Upgrader extends WP_Upgrader {

	/**
	 * Result of the demo upgrade offer.
	 *
	 * @since 2.8.0
	 * @access public
	 * @var array|WP_Error $result
	 * @see WP_Upgrader::$result
	 */
	public $result;

	/**
	 * Whether multiple demos are being upgraded/installed in bulk.
	 *
	 * @since 2.9.0
	 * @access public
	 * @var bool $bulk
	 */
	public $bulk = false;

	/**
	 * Initialize the install strings.
	 *
	 * @since 2.8.0
	 * @access public
	 */
	public function install_strings() {
		$this->strings['no_package'] = __( 'Install package not available.', 'themegrill-demo-importer' );
		$this->strings['downloading_package'] = __( 'Downloading install package from <span class="code">%s</span>&#8230;', 'themegrill-demo-importer' );
		$this->strings['unpack_package'] = __( 'Unpacking the package&#8230;', 'themegrill-demo-importer' );
		$this->strings['remove_old'] = __( 'Removing the old version of the demo&#8230;', 'themegrill-demo-importer' );
		$this->strings['remove_old_failed'] = __( 'Could not remove the old demo.', 'themegrill-demo-importer' );
		$this->strings['installing_package'] = __( 'Installing the demo&#8230;', 'themegrill-demo-importer' );
		$this->strings['no_files'] = __( 'The demo contains no files.', 'themegrill-demo-importer' );
		$this->strings['process_failed'] = __( 'Demo install failed.', 'themegrill-demo-importer' );
		$this->strings['process_success'] = __( 'Demo installed successfully.', 'themegrill-demo-importer' );
	}

	/**
	 * Install a demo package.
	 *
	 * @since 2.8.0
	 * @since 3.7.0 The `$args` parameter was added, making clearing the update cache optional.
	 * @access public
	 *
	 * @param string $package The full local path or URI of the package.
	 * @param array  $args {
	 *     Optional. Other arguments for installing a demo package. Default empty array.
	 *
	 *     @type bool $clear_update_cache Whether to clear the updates cache if successful.
	 *                                    Default true.
	 * }
	 *
	 * @return bool|WP_Error True if the install was successful, false or a WP_Error object otherwise.
	 */
	public function install( $package, $args = array() ) {
		$upload_dir = wp_upload_dir();

		$defaults = array(
			'clear_update_cache' => true,
		);
		$parsed_args = wp_parse_args( $args, $defaults );

		$this->init();
		$this->install_strings();

		add_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );

		$this->run( array(
			'package' => $package,
			'destination' => TGDM_DEMO_DIR,
			'clear_destination' => true, // Do overwrite files.
			'protect_destination' => true,
			'clear_working' => true,
			'hook_extra' => array(
				'type' => 'demo',
				'action' => 'install',
			),
		) );

		remove_filter( 'upgrader_source_selection', array( $this, 'check_package' ) );

		if ( ! $this->result || is_wp_error( $this->result ) ) {
			return $this->result;
		}

		return true;
	}

	/**
	 * Check that the package source contains a valid demo.
	 *
	 * Hooked to the {@see 'upgrader_source_selection'} filter by TG_Demo_Upgrader::install().
	 * It will return an error if the demo doesn't have tg-demo-config.php
	 * files.
	 *
	 * @since 3.3.0
	 * @access public
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 * @global array              $wp_theme_directories
	 *
	 * @param string $source The full path to the package source.
	 * @return string|WP_Error The source or a WP_Error.
	 */
	public function check_package( $source ) {
		global $wp_filesystem, $wp_theme_directories;

		if ( is_wp_error( $source ) )
			return $source;

		// Check the folder contains a valid demo.
		$working_directory = str_replace( $wp_filesystem->wp_content_dir(), trailingslashit( WP_CONTENT_DIR ), $source );
		if ( ! is_dir( $working_directory ) ) // Sanity check, if the above fails, let's not prevent installation.
			return $source;

		// A proper archive should have a tg-demo-config.php file in the single subdirectory
		if ( ! file_exists( $working_directory . 'tg-demo-config.php' ) ) {
			return new WP_Error( 'incompatible_archive_no_demos', $this->strings['incompatible_archive'], __( 'No valid demos were found.', 'themegrill-demo-importer' ) );
		}

		return $source;
	}

	/**
	 * Install a package.
	 *
	 * Copies the contents of a package form a source directory, and installs them in
	 * a destination directory. Optionally removes the source. It can also optionally
	 * clear out the destination folder if it already exists.
	 *
	 * Stuck with this until a fix for https://core.trac.wordpress.org/ticket/38946.
	 * We use a custom upgrader, just like WordPress does.
	 *
	 * @since 2.8.0
	 * @access public
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 * @global array              $wp_theme_directories
	 *
	 * @param array|string $args {
	 *     Optional. Array or string of arguments for installing a package. Default empty array.
	 *
	 *     @type string $source                      Required path to the package source. Default empty.
	 *     @type string $destination                 Required path to a folder to install the package in.
	 *                                               Default empty.
	 *     @type bool   $clear_destination           Whether to delete any files already in the destination
	 *                                               folder. Default false.
	 *     @type bool   $clear_working               Whether to delete the files from the working directory
	 *                                               after copying to the destination. Default false.
	 *     @type bool   $protect_destination         Whether to protect against deleting any files already
	 *                                               in the destination folder. Default false.
	 *     @type bool   $abort_if_destination_exists Whether to abort the installation if
	 *                                               the destination folder already exists. Default true.
	 *     @type array  $hook_extra                  Extra arguments to pass to the filter hooks called by
	 *                                               WP_Upgrader::install_package(). Default empty array.
	 * }
	 *
	 * @return array|WP_Error The result (also stored in `WP_Upgrader::$result`), or a WP_Error on failure.
	 */
	public function install_package( $args = array() ) {
		global $wp_filesystem, $wp_theme_directories;

		$defaults = array(
			'source' => '', // Please always pass this
			'destination' => '', // and this
			'clear_destination' => false,
			'clear_working' => false,
			'protect_destination' => true, // If fixed in core then it will be false :)
			'abort_if_destination_exists' => true,
			'hook_extra' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		// These were previously extract()'d.
		$source = $args['source'];
		$destination = $args['destination'];
		$clear_destination = $args['clear_destination'];

		@set_time_limit( 300 );

		if ( empty( $source ) || empty( $destination ) ) {
			return new WP_Error( 'bad_request', $this->strings['bad_request'] );
		}
		$this->skin->feedback( 'installing_package' );

		/**
		 * Filters the install response before the installation has started.
		 *
		 * Returning a truthy value, or one that could be evaluated as a WP_Error
		 * will effectively short-circuit the installation, returning that value
		 * instead.
		 *
		 * @since 2.8.0
		 *
		 * @param bool|WP_Error $response   Response.
		 * @param array         $hook_extra Extra arguments passed to hooked filters.
		 */
		$res = apply_filters( 'upgrader_pre_install', true, $args['hook_extra'] );

		if ( is_wp_error( $res ) ) {
			return $res;
		}

		// Retain the Original source and destinations
		$remote_source = $args['source'];
		$local_destination = $destination;

		$source_files = array_keys( $wp_filesystem->dirlist( $remote_source ) );
		$remote_destination = $wp_filesystem->find_folder( $local_destination );

		// Locate which directory to copy to the new folder, This is based on the actual folder holding the files.
		if ( 1 == count( $source_files ) && $wp_filesystem->is_dir( trailingslashit( $args['source'] ) . $source_files[0] . '/' ) ) { // Only one folder? Then we want its contents.
			$source = trailingslashit( $args['source'] ) . trailingslashit( $source_files[0] );
		} elseif ( count( $source_files ) == 0 ) {
			return new WP_Error( 'incompatible_archive_empty', $this->strings['incompatible_archive'], $this->strings['no_files'] ); // There are no files?
		} else { // It's only a single file, the upgrader will use the folder name of this file as the destination folder. Folder name is based on zip filename.
			$source = trailingslashit( $args['source'] );
		}

		/**
		 * Filters the source file location for the upgrade package.
		 *
		 * @since 2.8.0
		 * @since 4.4.0 The $hook_extra parameter became available.
		 *
		 * @param string      $source        File source location.
		 * @param string      $remote_source Remote file source location.
		 * @param WP_Upgrader $this          WP_Upgrader instance.
		 * @param array       $hook_extra    Extra arguments passed to hooked filters.
		 */
		$source = apply_filters( 'upgrader_source_selection', $source, $remote_source, $this, $args['hook_extra'] );

		if ( is_wp_error( $source ) ) {
			return $source;
		}

		// Has the source location changed? If so, we need a new source_files list.
		if ( $source !== $remote_source ) {
			$source_files = array_keys( $wp_filesystem->dirlist( $source ) );
		}

		/*
		 * Protection against deleting files in any important base directories.
		 * Theme_Upgrader & Plugin_Upgrader also trigger this, as they pass the
		 * destination directory (WP_PLUGIN_DIR / wp-content/themes) intending
		 * to copy the directory into the directory, whilst they pass the source
		 * as the actual files to copy.
		 */
		$protected_directories = array( ABSPATH, WP_CONTENT_DIR, WP_PLUGIN_DIR, WP_CONTENT_DIR . '/themes' );

		if ( is_array( $wp_theme_directories ) ) {
			$protected_directories = array_merge( $protected_directories, $wp_theme_directories );
		}

		if ( in_array( $destination, $protected_directories ) || $args['protect_destination'] ) {
			$remote_destination = trailingslashit( $remote_destination ) . trailingslashit( basename( $source ) );
			$destination = trailingslashit( $destination ) . trailingslashit( basename( $source ) );
		}

		if ( $clear_destination ) {
			// We're going to clear the destination if there's something there.
			$this->skin->feedback( 'remove_old' );

			$removed = $this->clear_destination( $remote_destination );

			/**
			 * Filters whether the upgrader cleared the destination.
			 *
			 * @since 2.8.0
			 *
			 * @param mixed  $removed            Whether the destination was cleared. true on success, WP_Error on failure
			 * @param string $local_destination  The local package destination.
			 * @param string $remote_destination The remote package destination.
			 * @param array  $hook_extra         Extra arguments passed to hooked filters.
			 */
			$removed = apply_filters( 'upgrader_clear_destination', $removed, $local_destination, $remote_destination, $args['hook_extra'] );

			if ( is_wp_error( $removed ) ) {
				return $removed;
			}
		} elseif ( $args['abort_if_destination_exists'] && $wp_filesystem->exists( $remote_destination ) ) {
			// If we're not clearing the destination folder and something exists there already, Bail.
			// But first check to see if there are actually any files in the folder.
			$_files = $wp_filesystem->dirlist( $remote_destination );
			if ( ! empty( $_files ) ) {
				$wp_filesystem->delete( $remote_source, true ); // Clear out the source files.
				return new WP_Error( 'folder_exists', $this->strings['folder_exists'], $remote_destination );
			}
		}

		// Create destination if needed
		if ( ! $wp_filesystem->exists( $remote_destination ) ) {
			if ( ! $wp_filesystem->mkdir( $remote_destination, FS_CHMOD_DIR ) ) {
				return new WP_Error( 'mkdir_failed_destination', $this->strings['mkdir_failed'], $remote_destination );
			}
		}
		// Copy new version of item into place.
		$result = copy_dir( $source, $remote_destination );
		if ( is_wp_error( $result ) ) {
			if ( $args['clear_working'] ) {
				$wp_filesystem->delete( $remote_source, true );
			}
			return $result;
		}

		// Clear the Working folder?
		if ( $args['clear_working'] ) {
			$wp_filesystem->delete( $remote_source, true );
		}

		$destination_name = basename( str_replace( $local_destination, '', $destination ) );
		if ( '.' == $destination_name ) {
			$destination_name = '';
		}

		$this->result = compact( 'source', 'source_files', 'destination', 'destination_name', 'local_destination', 'remote_destination', 'clear_destination' );

		/**
		 * Filters the install response after the installation has finished.
		 *
		 * @since 2.8.0
		 *
		 * @param bool  $response   Install response.
		 * @param array $hook_extra Extra arguments passed to hooked filters.
		 * @param array $result     Installation result data.
		 */
		$res = apply_filters( 'upgrader_post_install', true, $args['hook_extra'], $this->result );

		if ( is_wp_error( $res ) ) {
			$this->result = $res;
			return $res;
		}

		// Bombard the calling function will all the info which we've just used.
		return $this->result;
	}
}
