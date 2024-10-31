=== Plugin Name ===
Tags: press loft, affiliate
Requires at least: 6.0.0
Tested up to: 6.5.2
Stable tag: 1.0.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin is for use by Press Loft members who are put of the Press Loft Affiliate Network and use WooCommerce.  The plugin will automatically track relevant sales generated from the Press Loft Affiliate Network.

== Description ==

If you are a member of the Press Loft Affiliate Network and you use WooCommerce then this plugin is for you!

This plugin will use first part cookies to track any sales that are generated from customers who are directed to your store via the Press Loft Affiliate network.  Once transactions are completed the details are posted back to Press Loft so that the appropriate commission can be attributed.

We do not collect any data about the customer.  The only data collected is the order number, items ordered, and total order value.

== Installation ==

First, log in to your [PressLoft](https://www.pressloft.com/ "Press Loft account") and go to the 'Manage Installation' section of the Affiliate menu.  Make a note of your `Affiliate ID` and `Application Key`, you will need these for the following steps.

1. Upload the `press-loft-affiliate-network` folder to your `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Click the 'Settings' link under the plugin name, or navigate to the Press Loft Affiliate Network settings page through the 'Settings' menu in WordPress.
4. Input your `Affiliate ID` and `Application Key` and click 'Save Changes'.

= 1.0 =
* First release

= 1.0.1 =
* Tested up to WordPress 6.2
* Enable cookie tracking across site subdomains

= 1.0.3 =
* Fix bug with session management

= 1.0.4 =
* Switch to using cache instead of sessions

= 1.0.5 =
* Improve use of transients

= 1.0.6 =
* Tested up to WordPress 6.5.2
