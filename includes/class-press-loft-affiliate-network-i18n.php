<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.pressloft.com
 * @since      1.0.0
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/includes
 * @author     Press Loft <info@pressloft.com>
 */
class Press_Loft_Affiliate_Network_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'press-loft-affiliate-network',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
