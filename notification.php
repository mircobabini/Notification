<?php
/*
Plugin Name: Notification
Description: Send email notifications about various events in WordPress. You can also create your custom triggers for any action.
Author: underDEV
Author URI: https://www.wpart.co
Version: 1.3.1
License: GPL3
Text Domain: notification
Domain Path: /languages
*/

use \Notification\Notifications;

if ( ! defined( 'NOTIFICATION_URL' ) ) {
	define( 'NOTIFICATION_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'NOTIFICATION_DIR' ) ) {
	define( 'NOTIFICATION_DIR', plugin_dir_path( __FILE__ ) );
}

/**
 * Plugin's autoload function
 * @param  string $class class name
 * @return mixed         false if not plugin's class or void
 */
function notification_autoload( $class ) {

	$parts = explode( '\\', $class );

	if ( array_shift( $parts ) != 'Notification' ) {
		return false;
	}

	// Recipients
	if ( $parts[0] == 'Recipients' ) {
		$file = NOTIFICATION_DIR . strtolower( implode( '/', $parts ) ) . '.php';
	} else { // Other classes
		$file = NOTIFICATION_DIR . trailingslashit( 'inc' ) . strtolower( implode( '/', $parts ) ) . '.php';
	}

	if ( file_exists( $file ) ) {
		require_once( $file );
	}

}
spl_autoload_register( 'notification_autoload' );

/**
 * Setup plugin
 * @return void
 */
function notification_plugin_setup() {

	load_plugin_textdomain( 'notification', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

}
add_action( 'plugins_loaded', 'notification_plugin_setup' );

/**
 * Initialize plugin
 * @return void
 */
function notification_initialize() {

	/**
	 * Global functions
	 */
	require_once( NOTIFICATION_DIR . trailingslashit( 'inc' ) . 'global.php' );

	/**
	 * Notifications instance
	 */
	new Notifications();

	/**
	 * Load some default triggers
	 */
	require_once( NOTIFICATION_DIR . trailingslashit( 'triggers' ) . 'triggers.php' );

	/**
	 * Load default recipients
	 */
	require_once( NOTIFICATION_DIR . trailingslashit( 'recipients' ) . 'recipients.php' );

}
add_action( 'init', 'notification_initialize', 5 );

/**
 * Do some check on plugin activation
 * @return void
 */
function notification_activation() {

	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {

		deactivate_plugins( plugin_basename( __FILE__ ) );

		wp_die( __( 'This plugin requires PHP in version at least 5.3. WordPress itself <a href="https://wordpress.org/about/requirements/" target="_blank">requires at least PHP 5.6</a>. Please upgrade your PHP version or contact your Server administrator.', 'notification' ) );

	}

}
register_activation_hook( __FILE__, 'notification_activation' );
