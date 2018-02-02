<?php
/**
 * Delicious Media utility functions
 */

// Disallow direct access.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'dm_is_dev' ) ) {
	/**
	 * Are we running under a Delicious Media development environment?
	 *
	 * @return bool
	 */
	function dm_is_dev() {
		if ( defined( 'DM_ENVIRONMENT' ) && 'DEV' == DM_ENVIRONMENT ) {
			return true;
		}
		return false;
	}
}
