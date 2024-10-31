<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.pressloft.com
 * @since      1.0.0
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/includes
 * @author     Press Loft <info@pressloft.com>
 */
class Press_Loft_Affiliate_Network_Activator {

	/**
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		if ( defined( 'PRESS_LOFT_AFFILIATE_NETWORK_VERSION' ) ) {
			$this->version = PRESS_LOFT_AFFILIATE_NETWORK_VERSION;
		} else {
			$this->version = '1.0.0';
		}

	}

	/**
	 * Create the required database table for logging sales.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'press_loft_affiliate_network';

		$sql = "CREATE TABLE $table_name (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT,
		    pl_token VARCHAR(100) NOT NULL,
		    wc_order_id VARCHAR(100) NOT NULL,
		    date_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		    status ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
			post_back_attempts TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
		    last_updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		    PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $sql );

		add_option( 'pl_an_active', 0 );

	}

}
