<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.pressloft.com
 * @since      1.0.0
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/admin
 * @author     Press Loft <info@pressloft.com>
 */
class Press_Loft_Affiliate_Network_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
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
	 * @param    string    $plugin_name       The name of this plugin.
	 * @param    string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->queue_cron_jobs();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/press-loft-affiliate-network-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/press-loft-affiliate-network-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Define all of the cron jobs.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function queue_cron_jobs() {

		// Define a bespoke schedule of 5 minutes
		add_filter( 'cron_schedules', function ( $schedules ) {
		   $schedules['every_five_minutes'] = array(
		       'interval' => 300,
		       'display' => __( 'Every Five Minutes' )
		   );
		   return $schedules;
		} );

		// Queue the heartbeat cron job
		if( !wp_next_scheduled( 'press_loft_heartbeat_cron_hook' ) ) {
		    wp_schedule_event( time(), 'hourly', 'press_loft_heartbeat_cron_hook' );
		}

		// Queue the sale post back cron job
		if( !wp_next_scheduled( 'press_loft_sale_post_pack_cron_hook' ) ) {
		    wp_schedule_event( time(), 'every_five_minutes', 'press_loft_sale_post_pack_cron_hook' );
		}

	}

	/**
	 * Add the settings link to plug in list.
	 *
	 * @since    1.0.0
	 */
	public function add_settings_link( $links ) {

		$settings_link[] = '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', $this->plugin_name ) . '</a>';

		return array_merge( $settings_link, $links );

	}

	/**
	 * Send a request to the Press Loft API /heartbeat endpoint.
	 *
	 * @since    1.0.0
	 */
	public function heartbeat_cron_exec() {

		/**
		 * This function will send a request to the Press Loft API /heartbeat endpoint in
		 * order to notify Press Loft that the plugin is active.
		 */

		 // Check WooCommerce is active, our plugin is active, and credentials are set
		 if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && get_option( 'pl_an_brand_credentials' ) && get_option( 'pl_an_active' ) ) {

			// Get the brand credentials from options
			$pl_an_brand_credentials = get_option( 'pl_an_brand_credentials' );
	 		$pl_an_affiliate_id = isset( $pl_an_brand_credentials['pl_an_affiliate_id'] ) ? $pl_an_brand_credentials['pl_an_affiliate_id'] : null;
			$pl_an_application_key = isset( $pl_an_brand_credentials['pl_an_application_key'] ) ? $pl_an_brand_credentials['pl_an_application_key'] : null;

			// Set the affiliate id and afiliate key from settings
	 	    $query = array(
	 	        'id' => $pl_an_affiliate_id,
	 	        'key' => $pl_an_application_key
	 	    );

	 	    // Send the request to the heartbeat endpoint
	 	    wp_remote_get( 'https://affiliates.pressloft.com/heartbeat?' . http_build_query( $query ) );

		}

	}

	/**
	 * Process all pending tracked sales.
	 *
	 * @since    1.0.0
	 */
	public function sale_post_back_cron_exec() {

		/**
		 * This function will attempt to post back all 'pending' sales in the database (maximum 3 attempts).
		 */

		 if( get_option( 'pl_an_brand_credentials' ) && get_option( 'pl_an_active' ) ) {

			// Get the affiliate ID
			$pl_an_brand_credentials = get_option( 'pl_an_brand_credentials' );
			$pl_an_affiliate_id = isset( $pl_an_brand_credentials['pl_an_affiliate_id'] ) ? $pl_an_brand_credentials['pl_an_affiliate_id'] : null;

			global $wpdb;

			$table_name = $wpdb->prefix . 'press_loft_affiliate_network';

			// Retrieve all 'pending' tracked sales from the db
			$tracked_orders = $wpdb->get_results( "SELECT * FROM $table_name WHERE status = 'pending'" );

			// Iterate through the tracked sales
			foreach ( $tracked_orders as $tracked_order ) {

				// Get the order from WooCommerce
				$order = wc_get_order( $tracked_order->wc_order_id );

				if( $order ) {

					// Define the order values
					$order_currency = $order->get_currency();
					$postage = $order->get_total_shipping();
					$tax = $order->get_total_tax();
					$order_total = $order->get_total();
					$order_subtotal = number_format( ( ( float ) $order_total - ( float ) $postage ), 2, '.', '' );

					// Prepare the post back array
					$post_back_array = [
						'token' => $tracked_order->pl_token,
						'affiliate_id' => $pl_an_affiliate_id,
						'order_id' => $tracked_order->wc_order_id,
						'order_details' => [
							'order_datetime' => $tracked_order->date_created,
							'order_subtotal' => $order_subtotal,
							'order_currency' => $order_currency,
							'discount' => '0.00',
							'tax' => number_format( ( float ) $tax, 2, '.', '' ),
							'postage' => number_format( ( float ) $postage, 2, '.', '' ),
							'order_total' => number_format( ( float ) $order_total, 2, '.', '' )
						]
					];

					$i = 1;

					// Iterate through the items included in the order
					foreach( $order->get_items() as $item_id => $item ) {

						// Get the associated product
		                $product = $item->get_product();

		                if ($product) {

							$sku = $product->get_sku();
							$sku = empty( $sku ) ? $item['product_id'] : $sku;

							// Calculate the price for each single item
							$singlePrice = number_format( ( ( float ) $item['total'] / ( float ) $item['quantity'] ), 2, '.', '' );

							// Add to the post back array
							$post_back_array['order_details']['order_lines']['order_line_'.$i] = [
								'sku' => $sku,
								'product_name' => $item['name'],
								'quantity' => $item['quantity'],
								'unit_price' => $singlePrice,
								'line_total' => $item['total']
							];

							$i++;

						}

					}

					// Update database as we make the post back attempt
					$wpdb->update( $table_name, ['post_back_attempts' => $tracked_order->post_back_attempts + 1], [ 'id' => $tracked_order->id ] );

					// Send the request to the sale endpoint
			 	    $response = wp_remote_post( 'https://affiliates.pressloft.com/sale', ['body' => json_encode($post_back_array)] );

					// Process the response
					$body = json_decode( wp_remote_retrieve_body( $response ), true );

					if( $body['status'] == 'success' ) {

						// If successfull update the record
						$wpdb->update( $table_name, ['status' => 'success'], [ 'id' => $tracked_order->id ] );

					}
					else {

						if( $tracked_order->post_back_attempts == 4 ) {

							// If the post back failed a 5th time mark it as such
							$wpdb->update( $table_name, ['status' => 'failed'], [ 'id' => $tracked_order->id ] );

						}

					}

				}
				else {

					// If the order is not found in WooCommerce then fail
					$wpdb->update( $table_name, ['status' => 'failed'], [ 'id' => $tracked_order->id ] );

				}

			}

		}

	}

	/**
	 * Load the settings page.
	 *
	 * @since    1.0.0
	 */
	public function options_page_html() {

		/**
		 * Loads the content of the settings page.
		 */

		// Check user has access
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check for the WooCommerce plugin
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			// Load the html
			include_once( 'partials/press-loft-affiliate-network-admin-options.php' );

		}
		else {

			// Load the html
			include_once( 'partials/press-loft-affiliate-network-admin-woocommerce-not-installed.php' );

		}

	}

	/**
	 * Redirect the user to the settings page when activated.
	 *
	 * @since    1.0.0
	 */
	public function redirect_on_activate( $plugin ) {

		$this_plugin = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );

		// If the plugin that was activated is our plugin, then redirect to settings
		if( $plugin == $this_plugin ) {

			exit( wp_redirect( admin_url( 'options-general.php?page=' . $this->plugin_name ) ) );

		}

	}

	/**
	 * Adds the setting page to the menu.
	 *
	 * @since    1.0.0
	 */
	public function options_page() {

		/**
		 * Adds the settings page to the menu.
		 *
		 * @since    1.0.0
		 */

	    add_options_page(
			'Press Loft Affiliate Network',
			'Press Loft Affiliate Network',
			'manage_options',
			$this->plugin_name,
			array($this, 'options_page_html')
		);

	}

	/**
	 * Defines the options to display and registers the settings.
	 *
	 * @since    1.0.0
	 */
	public function display_options() {

		/**
		 * This function defines the output fields for the options page and registers the settings.
		 *
		 * @since    1.0.0
		 */

		// Add settings section
        add_settings_section( 'settings_section', 'Settings', array( $this, 'display_header_options_content' ), 'options_page' );

		// Add affiliate id and application key form fields
        add_settings_field( 'affiliate_id', 'Affiliate ID', array( $this, 'display_affiliate_id_form_element' ), 'options_page', 'settings_section' );
        add_settings_field( 'application_key', 'Application Key', array( $this, 'display_application_key_form_element' ), 'options_page', 'settings_section' );

		// Register the settings
		register_setting( 'settings_section', 'pl_an_brand_credentials', array( $this, 'validate_brand_credentials' ) );

	}

	/**
	 * Validates the inputted brand credentials
	 *
	 * @since    1.0.0
	 */
	public function validate_brand_credentials( $input ) {

		$output = [];

		// Check that both an Affiliate ID and an Application ID were input
		if( empty( $input['pl_an_affiliate_id'] ) || empty( $input['pl_an_application_key'] ) ) {

			$output = $input;

			add_settings_error( 'pl_an_brand_credentials', 'pl_an_brand_credentials_error', 'Please input both an Affiliate ID and an Application Key.', 'error' );
			update_option( 'pl_an_active', 0 );

		}
		else {

			// Clean up the variables
			foreach( $input as $key => $value ) {

				$output[$key] = sanitize_text_field( $value );

			}

			// Set the affiliate id and afiliate key
	 	    $query = array(
	 	        'id' => $output['pl_an_affiliate_id'],
	 	        'key' => $output['pl_an_application_key']
	 	    );

	 	    // Send the request to the heartbeat endpoint
	 	    $response = wp_remote_get( 'https://affiliates.pressloft.com/heartbeat?' . http_build_query( $query ) );

			// Process the response
			$body = json_decode( wp_remote_retrieve_body( $response ), true );

			// If an error was returned output an error message
			if( isset( $body['status'] ) ) {

				if( $body['status'] == 'success' ) {

					update_option( 'pl_an_active', 1 );

				}
				else {

					add_settings_error( 'pl_an_brand_credentials', 'pl_an_brand_credentials_error', 'Your Affiliate ID and Application Key are invalid, please check and try again.', 'error' );
					update_option( 'pl_an_active', 0 );

				}

			}
			else {

				add_settings_error( 'pl_an_brand_credentials', 'pl_an_brand_credentials_error', 'Something went wrong, please try again.', 'error' );
				update_option( 'pl_an_active', 0 );

			}

		}

	    return apply_filters( 'validate_brand_credentials', $output, $input );

	}

	/**
	 * Defines the content for the options page header.
	 *
	 * @since    1.0.0
	 */
	public function display_header_options_content() {

		$active = ( get_option( 'pl_an_active' ) ) ? 'ACTIVE' : 'INACTIVE';

		include_once( 'partials/press-loft-affiliate-network-admin-options-header.php' );

	}

	/**
	 * Defines the content for the options page affiliate id field.
	 *
	 * @since    1.0.0
	 */
	public function display_affiliate_id_form_element() {

		$pl_an_brand_credentials = get_option( 'pl_an_brand_credentials' );
		$pl_an_affiliate_id = isset( $pl_an_brand_credentials['pl_an_affiliate_id'] ) ? $pl_an_brand_credentials['pl_an_affiliate_id'] : null;

		require_once( 'partials/press-loft-affiliate-network-admin-options-field-affiliate-id.php' );

    }

	/**
	 * Defines the content for the options page application key field.
	 *
	 * @since    1.0.0
	 */
    public function display_application_key_form_element() {

		$pl_an_brand_credentials = get_option( 'pl_an_brand_credentials' );
		$pl_an_application_key = isset( $pl_an_brand_credentials['pl_an_application_key'] ) ? $pl_an_brand_credentials['pl_an_application_key'] : null;

		require_once( 'partials/press-loft-affiliate-network-admin-options-field-application-key.php' );

    }

}
