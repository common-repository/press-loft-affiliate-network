<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.pressloft.com
 * @since      1.0.0
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/public
 * @author     Press Loft <info@pressloft.com>
 */
class Press_Loft_Affiliate_Network_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $press_loft_affiliate_network    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $press_loft_affiliate_network       The name of the plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']); // For transients, create a unique user fingerprint (not foolproof but only used in case of no cookie support)
		$this->transient_name = $this->fingerprint . '_pltoken';

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/press-loft-affiliate-network-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/press-loft-affiliate-network-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Parse the URL and process the pltoken
	 *
	 * @since    1.0.0
	 */
	public function process_pltoken() {

		// Only trigger if this is a public page
		if( wp_using_themes() ) {

			// Retrieve and sanitize the token in the url
			$pltoken = ( string ) filter_input( INPUT_GET, 'pltoken' );

			if( $pltoken ) {

				// If the token was in the URL then save this as a cookie and set the transient value

				// Set the token to prepare to retrieve the cookie period
		 	    $query = array(
		 	        'token' => $pltoken
		 	    );

		 	    // Send the request to the /cookieperiod endpoint
		 	    $response = wp_remote_get( 'https://affiliates.pressloft.com/cookieperiod?' . http_build_query( $query ) );

				// Process the response
				$body = json_decode( wp_remote_retrieve_body( $response ), true );

				if( isset( $body['cookiePeriod'] ) && $body['cookiePeriod'] > 0 && isset( $body['status'] ) && $body['status'] == 'success' ) {

					// If we get a response, set the cookie period
					$cookie_period = time() + (86400 * $body['cookiePeriod']);

				}
				else {

					// Otherwise default to 30 days
					$cookie_period = time() + (86400 * 30);

				}

				// Get the domain name without subdomains
				$urlparts = parse_url( home_url() );
    			$domain = isset( $urlparts['host'] ) ? $urlparts['host'] : '';
			    if( preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs ) ) {
			        $domain = $regs['domain'];
			    }

				// Set the cookie
				setcookie( 'pl_wp_pltoken', $pltoken, $cookie_period, COOKIEPATH, $domain, is_ssl(), true );

				// Also save the data as a transient (cache) for 1 hour
				set_transient($this->transient_name, $pltoken, 3600);

			}
			else if( isset( $_COOKIE['pl_wp_pltoken'] ) ) {

				// Else, check the cookie and assign the token from this if it exists
				$pltoken = sanitize_text_field( $_COOKIE['pl_wp_pltoken'] );
				set_transient($this->transient_name, $pltoken, 3600);

			}
			else if( get_transient($this->transient_name) !== false ) {

				// Else check to see if the transient is already set and assign this as the current token
				$pltoken = sanitize_text_field( get_transient($this->transient_name) );

			}

		}

	}

	/**
	 * Post back a completed tracked sale
	 *
	 * @since    1.0.0
	 */
	public function register_sale( $order_id ) {

		if( get_transient($this->transient_name) !== false ) {

			// Grab the token from the transient
			$pltoken = sanitize_text_field( get_transient($this->transient_name) );
			$order = wc_get_order( $order_id );

			global $wpdb;

			$table_name = $wpdb->prefix . 'press_loft_affiliate_network';

			// Insert the sale into the database
			$wpdb->insert(
				$table_name,
				[
					'pl_token' => $pltoken,
					'wc_order_id' => $order_id
				]
			);

		}

	}

}
