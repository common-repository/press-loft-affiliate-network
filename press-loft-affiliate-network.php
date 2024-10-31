<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.pressloft.com
 * @since             1.0.0
 * @package           Press_Loft_Affiliate_Network
 *
 * @wordpress-plugin
 * Plugin Name:       Press Loft Affiliate Network
 * Plugin URI:        https://www.pressloft.com/app/affiliate
 * Description:       The Press Loft Affiliate Network plugin enables brands to track sales generated through the Press Loft affiliate network.
 * Version:           1.0.6
 * Author:            Press Loft
 * Author URI:        https://www.pressloft.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       press-loft-affiliate-network
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PRESS_LOFT_AFFILIATE_NETWORK_VERSION', '1.0.6' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-press-loft-affiliate-network-activator.php
 */
function activate_press_loft_affiliate_network() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-press-loft-affiliate-network-activator.php';
	Press_Loft_Affiliate_Network_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-press-loft-affiliate-network-deactivator.php
 */
function deactivate_press_loft_affiliate_network() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-press-loft-affiliate-network-deactivator.php';
	Press_Loft_Affiliate_Network_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_press_loft_affiliate_network' );
register_deactivation_hook( __FILE__, 'deactivate_press_loft_affiliate_network' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-press-loft-affiliate-network.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_press_loft_affiliate_network() {

	$plugin = new Press_Loft_Affiliate_Network();
	$plugin->run();

}

run_press_loft_affiliate_network();
