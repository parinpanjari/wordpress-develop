<?php

abstract class WP_Import_UnitTestCase extends WP_UnitTestCase {

	/**
	 * Require the WordPress Importer plugin.
	 *
	 * Fails the test if the plugin is not installed.
	 */
	protected function require_importer() {
		if ( ! file_exists( IMPORTER_PLUGIN_FOR_TESTS ) ) {
			$this->fail( 'This test requires the WordPress Importer plugin to be installed in the test suite. See: https://make.wordpress.org/core/handbook/contribute/git/#unit-tests' );
		}
		require_once IMPORTER_PLUGIN_FOR_TESTS;
	}

	/**
	 * Import a WXR file.
	 *
	 * The $users parameter provides information on how users specified in the import
	 * file should be imported. Each key is a user login name and indicates if the user
	 * should be mapped to an existing user, created as a new user with a particular login
	 * or imported with the information held in the WXR file. An example of this:
	 *
	 * <code>
	 * $users = array(
	 *   'alice' => 1,      // alice will be mapped to user ID 1.
	 *   'bob'   => 'john', // bob will be transformed into john.
	 *   'eve'   => false   // eve will be imported as is.
	 * );</code>
	 *
	 * @param string $filename Full path of the file to import
	 * @param array $users User import settings
	 * @param bool $fetch_files Whether or not do download remote attachments
	 */
	protected function _import_wp( $filename, $users = array(), $fetch_files = true ) {
		$this->require_importer();

		$importer = new WP_Import();
		$file     = realpath( $filename );

		$this->assertNotEmpty( $file, 'Path to import file is empty.' );
		$this->assertTrue( is_file( $file ), 'Import file is not a file.' );

		$authors = array();
		$mapping = array();
		$new     = array();
		$i       = 0;

		// Each user is either mapped to a given ID, mapped to a new user
		// with given login or imported using details in WXR file.
		foreach ( $users as $user => $map ) {
			$authors[ $i ] = $user;
			if ( is_int( $map ) ) {
				$mapping[ $i ] = $map;
			} elseif ( is_string( $map ) ) {
				$new[ $i ] = $map;
			}

			++$i;
		}

		$_POST = array(
			'imported_authors' => $authors,
			'user_map'         => $mapping,
			'user_new'         => $new,
		);

		ob_start();
		$importer->fetch_attachments = $fetch_files;
		$importer->import( $file );
		ob_end_clean();

		$_POST = array();
	}
}
