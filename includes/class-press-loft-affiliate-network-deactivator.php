<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.pressloft.com
 * @since      1.0.0
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/includes
 * @author     Press Loft <info@pressloft.com>
 */
class Press_Loft_Affiliate_Network_Deactivator {

	/**
	 * Clear cron jobs and delete custom options.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		wp_clear_scheduled_hook( 'press_loft_heartbeat_cron_hook' );
		wp_clear_scheduled_hook( 'press_loft_sale_post_pack_cron_hook' );

		delete_option( 'pl_an_active' );

	}

}
