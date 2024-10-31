<?php
/**
 * Plugin Name: Revised Publishing Status
 * Plugin URI: http://www.yivoff.com/plugins/revised-status
 * Description: Saves the publihed status (publish, draft, pending) alongside revision history.
 * Version: 0.8.6
 * Author: yivi
 * Text Domain: wp-revised-status
 * Domain Path: /lang
 *
 *
 * @package: RevisedStatus
 */

require_once( 'autoload.php' );

/**
 * Custommeta key to store the pubstatus for each revision
 */
define( 'REVISED_STATUS_METAKEY', '_pubstatus_history' );
define( 'WP_REVSTATUS_SLUG', 'wp-revised-status' );
define( 'WP_REVSTATUS_SETTINGS', 'revised_status_options' );

register_deactivation_hook( __FILE__, 'revised_status_deactivate' );
register_uninstall_hook( __FILE__, 'revised_status_uninstall' );

/**
 *
 * Deletes publishing status history custommeta from the DB on uninstall
 *
 */
function revised_status_uninstall() {
	delete_post_meta_by_key( WP_REVSTATUS_SETTINGS );
}

add_action( 'plugins_loaded', 'revised_status_setup' );

/**
 *
 * Loads textdomain and setup up the plugin.
 *
 */
function revised_status_setup() {

	load_plugin_textdomain( 'wp-revised-status', false, basename( dirname( __FILE__ ) ) . '/lang/' );

	RevisedStatus\Controller\RevisionManager::getInstance()->setup();
	RevisedStatus\Controller\Options::getInstance()->setup();
	RevisedStatus\View\RevisionMetabox::getInstance()->setup();

}

