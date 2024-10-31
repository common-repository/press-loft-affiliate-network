<?php

/**
 * HTML for the options page
 *
 * @link       https://www.pressloft.com
 * @since      1.0.0
 *
 * @package    Press_Loft_Affiliate_Network
 * @subpackage Press_Loft_Affiliate_Network/admin/partials
 */

?>

<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ) ;?></h1>
    <form action="options.php" method="post">
        <?php echo settings_fields( 'settings_section' ); ?>
        <?php echo do_settings_sections( 'options_page' ); ?>
        <?php echo submit_button(); ?>
    </form>
</div>
