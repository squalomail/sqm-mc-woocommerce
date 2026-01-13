<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://squalomail.com
 * @since      1.0.1
 *
 * @package    SqualoMail_WooCommerce
 */

// If uninstall not called from WordPress, then exit.
if (!defined( 'WP_UNINSTALL_PLUGIN')) {
	exit;
}

if (!isset($squalomail_woocommerce_spl_autoloader) || $squalomail_woocommerce_spl_autoloader === false) {
    include_once __DIR__ . "/bootstrap.php";
}

function squalomail_woocommerce_uninstall() {
    try {
        if (($options = get_option('squalomail-woocommerce', false)) && is_array($options)) {
            if (isset($options['squalomail_api_key'])) {
                $store_id = get_option('squalomail-woocommerce-store_id', false);
                if (!empty($store_id)) {
                    $api = new SqualoMail_WooCommerce_SqualoMailApi($options['squalomail_api_key']);
                    $result = $api->deleteStore($store_id) ? 'has been deleted' : 'did not delete';
                    error_log("store id {$store_id} {$result} Squalo");
                }
            }
        }
    } catch (\Exception $e) {
        error_log($e->getMessage().' on '.$e->getLine().' in '.$e->getFile());
    }
    squalomail_remove_communication_status();
    squalomail_clean_database();
    squalomail_remove_activity_panel_inbox_notes();
}

if (!is_multisite()) {
    squalomail_woocommerce_uninstall();
} else {
    global $wpdb;
    try {
        foreach ($wpdb->get_col("SELECT blog_id FROM $wpdb->blogs") as $squalomail_current_blog_id) {
            switch_to_blog($squalomail_current_blog_id);
            squalomail_woocommerce_uninstall();
        }
        restore_current_blog();
    } catch (\Exception $e) {}
}


