<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.pressloft.com
 * @since      1.0.0
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/includes
 * @author     Press Loft <info@pressloft.com>
 */
class Press_Loft_Affiliate_Network {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Press_Loft_Affiliate_Network_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
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

		$this->plugin_name = 'press-loft-affiliate-network';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Press_Loft_Affiliate_Network_Loader. Orchestrates the hooks of the plugin.
	 * - Press_Loft_Affiliate_Network_i18n. Defines internationalization functionality.
	 * - Press_Loft_Affiliate_Network_Admin. Defines all hooks for the admin area.
	 * - Press_Loft_Affiliate_Network_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-press-loft-affiliate-network-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-press-loft-affiliate-network-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-press-loft-affiliate-network-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-press-loft-affiliate-network-public.php';

		$this->loader = new Press_Loft_Affiliate_Network_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Press_Loft_Affiliate_Network_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Press_Loft_Affiliate_Network_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Press_Loft_Affiliate_Network_Admin( $this->get_plugin_name(), $this->get_version() );

		// Admin hooks
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'options_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'display_options' );
		$this->loader->add_action( 'activated_plugin', $plugin_admin, 'redirect_on_activate' );

		// Define admin cron jobs
		$this->loader->add_action( 'press_loft_heartbeat_cron_hook', $plugin_admin, 'heartbeat_cron_exec' );
		$this->loader->add_action( 'press_loft_sale_post_pack_cron_hook', $plugin_admin, 'sale_post_back_cron_exec' );

		// Add link to settings page
		$this->loader->add_filter( 'plugin_action_links_' . plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php'), $plugin_admin, 'add_settings_link' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Press_Loft_Affiliate_Network_Public( $this->get_plugin_name(), $this->get_version() );

		// Public hooks
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_public, 'process_pltoken' );
		$this->loader->add_action( 'woocommerce_thankyou', $plugin_public, 'register_sale' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Press_Loft_Affiliate_Network_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
