<?php
/**
 * Plugin Name: DM Twilio Notifications
 * Plugin URI: https://github.com/DeliciousMedia/DM-Twilio
 * Description: Send SMS via Twilio and track message delivery within WordPress.
 * Version: 1.0.2
 * Author: Delicious Media Limited
 * Author URI: https://www.deliciousmedia.co.uk
 * Text Domain: dm-twilio
 * License: GPLv3 or later
 * Contributors: davepullig
 *
 * @package dm-twilio
 **/

/**
 *
 *  Copyright (c) 2018 Delicious Media Limited.
 *
 *  This file is part of DM Twilio.
 *
 *  DM Twilio is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  DM Twilio is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with DM Twilio. If not, see <http://www.gnu.org/licenses/>.
 *
 *  This software requires the Twilio PHP SDK (https://www.twilio.com/docs/libraries/php)
 *  which is licensed under the MIT licence.
 */

// Disallow direct access.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Definitions
 */
define( 'DMTWILIO_VERSION', '1.0.1' );
define( 'DMTWILIO_PLUGIN_PATH', plugin_basename( __FILE__ ) );

// Allow overriding the callback URL, useful for development.
defined( 'DMTWILIO_CALLBACK_ENDPOINT' ) || define( 'DMTWILIO_CALLBACK_ENDPOINT', 'dmtwilio/v1/callback/' );

/**
 * Try and find and include autoload.php.
 */
if ( file_exists( ABSPATH . '../vendor/autoload.php' ) ) { // WP in subdirectory.
	require_once( ABSPATH . '../vendor/autoload.php' );
} elseif ( file_exists( ABSPATH . '/vendor/autoload.php' ) ) { // If not, maybe WP isn't in a subdirectory.
	require_once( ABSPATH . '/vendor/autoload.php' );
} elseif ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) { // Try within the plugin directory.
	require_once( __DIR__ . '/vendor/autoload.php' );

}

/**
* Include plugin code.
*/
// Delicious Media utility functions.
require_once( dirname( __FILE__ ) . '/inc/dm.php' );

// Setup data structures.
require_once( dirname( __FILE__ ) . '/inc/structure.php' );

// Settings pages.
if ( is_admin() ) {
	require_once( dirname( __FILE__ ) . '/inc/settings.php' );
}
// Main plugin functionality.
require_once( dirname( __FILE__ ) . '/inc/twilio-functions.php' );

/**
 * Activation hooks.
 */

// Populate the message_status taxonomy.
register_activation_hook( __FILE__, 'dmtwilio_populate_dmtwilio_message_status_tax' );
