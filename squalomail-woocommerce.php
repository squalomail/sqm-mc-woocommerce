<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://squalomail.com
 * @since             1.0.0
 * @package           SqualoMail_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Squalo for WooCommerce
 * Plugin URI:        https://squalomail.com/connect-your-store/
 * Description:       Connects WooCommerce to Squalo to sync your store data, send targeted campaigns to your customers, and sell more stuff.
 * Version:           2.5.4
 * Author:            Squalo
 * Author URI:        https://squalomail.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       squalomail-for-woocommerce
 * Domain Path:       /languages
 * Requires at least: 4.9
 * Tested up to: 5.7
 * WC requires at least: 3.5
 * WC tested up to: 5.1
 */

// If this file is called directly, abort.
if (!defined( 'WPINC')) {
    die;
}

if (!isset($squalomail_woocommerce_spl_autoloader) || $squalomail_woocommerce_spl_autoloader === false) {
    // require Action Scheduler
    include_once __DIR__ . "/includes/vendor/action-scheduler/action-scheduler.php";
    // bootstrapper
    include_once __DIR__ . "/bootstrap.php";
}

register_activation_hook( __FILE__, 'activate_squalomail_woocommerce');

// plugins loaded callback
add_action('plugins_loaded', 'squalomail_on_all_plugins_loaded', 12);
add_action('before_woocommerce_init', function(){
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});