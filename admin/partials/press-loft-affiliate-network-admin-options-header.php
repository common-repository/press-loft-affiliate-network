<?php

/**
 * HTML for plugin status and setup instructions
 *
 * @link       https://www.pressloft.com
 * @since      1.0.0
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/admin/partials
 */

?>

<p class="pl-an-plugin-status">Plugin status: <span class="pl-an-plugin-<?php echo esc_attr( strtolower( $active ) ); ?>"><?php echo esc_html( $active ); ?></span></p>

<p>To activate this plugin please <a href="https://www.pressloft.com" target="_new">log in to your Press Loft account</a> and
    navigate to the <b>Manage installation</b> section of the <b>Affiliate</b> menu on your dashboard.  You will find your
    <b>Affiliate ID</b> and <b>Application Key</b> on this page.</p>

<p>Insert your <b>Affiliate ID</b> and <b>Application Key</b> into the form below and click <b>Save Changes</b>.</p>
