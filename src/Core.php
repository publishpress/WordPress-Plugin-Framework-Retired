<?php

namespace AllediaFramework;

class Core {
	/**
	 * Core constructor.
	 */
	public function __construct() {
		$this->load_textdomain();
	}

	/**
	 * Load the framework's text domain.
	 */
	protected function load_textdomain() {
		$mo_file = __DIR__ . 'languages/alledia-framework-' . get_locale() . '.mo';

		if ( file_exists( $mo_file ) ) {
			load_textdomain( 'alledia-framework', $mo_file );
		}
	}
}
