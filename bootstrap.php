<?php

// If this file is called directly, abort.
if (!defined( 'WPINC')) {
    die;
}

$squalomail_woocommerce_spl_autoloader = true;

spl_autoload_register(function($class) {
    $classes = array(
        // includes root
        'SqualoMail_Service' => 'includes/class-squalomail-woocommerce-service.php',
        'SqualoMail_WooCommerce_Options' => 'includes/class-squalomail-woocommerce-options.php',
        'SqualoMail_Newsletter' => 'includes/class-squalomail-woocommerce-newsletter.php',
        'SqualoMail_WooCommerce_Loader' => 'includes/class-squalomail-woocommerce-loader.php',
        'SqualoMail_WooCommerce_i18n' => 'includes/class-squalomail-woocommerce-i18n.php',
        'SqualoMail_WooCommerce_Deactivator' => 'includes/class-squalomail-woocommerce-deactivator.php',
        'SqualoMail_WooCommerce_Activator' => 'includes/class-squalomail-woocommerce-activator.php',
        'SqualoMail_WooCommerce' => 'includes/class-squalomail-woocommerce.php',
        'SqualoMail_WooCommerce_Privacy' => 'includes/class-squalomail-woocommerce-privacy.php',
        'Squalomail_Woocommerce_Deactivation_Survey' => 'includes/class-squalomail-woocommerce-deactivation-survey.php',
        'SqualoMail_WooCommerce_Rest_Api' => 'includes/class-squalomail-woocommerce-rest-api.php',
        'Squalomail_Wocoomerce_CLI' => 'includes/class-squalomail-woocommerce-cli.php',
        'SqualoMail_WooCommerce_HPOS' => 'includes/class-squalomail-woocommerce-hpos.php',
        
        // includes/api/assets
        'SqualoMail_WooCommerce_Address' => 'includes/api/assets/class-squalomail-address.php',
        'SqualoMail_WooCommerce_Cart' => 'includes/api/assets/class-squalomail-cart.php',
        'SqualoMail_WooCommerce_Customer' => 'includes/api/assets/class-squalomail-customer.php',
        'SqualoMail_WooCommerce_LineItem' => 'includes/api/assets/class-squalomail-line-item.php',
        'SqualoMail_WooCommerce_Order' => 'includes/api/assets/class-squalomail-order.php',
        'SqualoMail_WooCommerce_Product' => 'includes/api/assets/class-squalomail-product.php',
        'SqualoMail_WooCommerce_ProductVariation' => 'includes/api/assets/class-squalomail-product-variation.php',
        'SqualoMail_WooCommerce_PromoCode' => 'includes/api/assets/class-squalomail-promo-code.php',
        'SqualoMail_WooCommerce_PromoRule' => 'includes/api/assets/class-squalomail-promo-rule.php',
        'SqualoMail_WooCommerce_Store' => 'includes/api/assets/class-squalomail-store.php',
        'SqualoMail_WooCommerce_Category' => 'includes/api/assets/class-squalomail-category.php',

        // includes/api/errors
        'SqualoMail_WooCommerce_Error' => 'includes/api/errors/class-squalomail-error.php',
        'SqualoMail_WooCommerce_RateLimitError' => 'includes/api/errors/class-squalomail-rate-limit-error.php',
        'SqualoMail_WooCommerce_ServerError' => 'includes/api/errors/class-squalomail-server-error.php',

        // includes/api/helpers
        'SqualoMail_WooCommerce_CurrencyCodes' => 'includes/api/helpers/class-squalomail-woocommerce-api-currency-codes.php',
        'SqualoMail_Api_Locales' => 'includes/api/helpers/class-squalomail-woocommerce-api-locales.php',

        // includes/api
        'SqualoMail_WooCommerce_SqualoMailApi' => 'includes/api/class-squalomail-api.php',
        'SqualoMail_WooCommerce_Api' => 'includes/api/class-squalomail-woocommerce-api.php',
        'SqualoMail_WooCommerce_CreateListSubmission' => 'includes/api/class-squalomail-woocommerce-create-list-submission.php',
        'SqualoMail_WooCommerce_Transform_Coupons' => 'includes/api/class-squalomail-woocommerce-transform-coupons.php',
        'SqualoMail_WooCommerce_Transform_Orders' => 'includes/api/class-squalomail-woocommerce-transform-orders-wc3.php',
        'SqualoMail_WooCommerce_Transform_Products' => 'includes/api/class-squalomail-woocommerce-transform-products.php',
        'SqualoMail_WooCommerce_Transform_Categories' => 'includes/api/class-squalomail-woocommerce-transform-categories.php',

        // includes/processes
        'Squalomail_Woocommerce_Job' => 'includes/processes/class-squalomail-woocommerce-job.php',
        'SqualoMail_WooCommerce_Abstract_Sync' => 'includes/processes/class-squalomail-woocommerce-abstract-sync.php',
        'SqualoMail_WooCommerce_Cart_Update' => 'includes/processes/class-squalomail-woocommerce-cart-update.php',
        'SqualoMail_WooCommerce_Process_Coupons' => 'includes/processes/class-squalomail-woocommerce-process-coupons.php',
        'SqualoMail_WooCommerce_Process_Orders' => 'includes/processes/class-squalomail-woocommerce-process-orders.php',
        'SqualoMail_WooCommerce_Process_Products' => 'includes/processes/class-squalomail-woocommerce-process-products.php',
        'SqualoMail_WooCommerce_SingleCoupon' => 'includes/processes/class-squalomail-woocommerce-single-coupon.php',
        'SqualoMail_WooCommerce_Single_Order' => 'includes/processes/class-squalomail-woocommerce-single-order.php',
        'SqualoMail_WooCommerce_Single_Product' => 'includes/processes/class-squalomail-woocommerce-single-product.php',
        'SqualoMail_WooCommerce_User_Submit' => 'includes/processes/class-squalomail-woocommerce-user-submit.php',
        'SqualoMail_WooCommerce_Process_Full_Sync_Manager' => 'includes/processes/class-squalomail-woocommerce-full-sync-manager.php',
        'SqualoMail_WooCommerce_Product_Category' => 'includes/processes/class-squalomail-woocommerce-product-category.php',
        'SqualoMail_WooCommerce_Process_Categories' => 'includes/processes/class-squalomail-woocommerce-process-categories.php',
        
        'SqualoMail_WooCommerce_Public' => 'public/class-squalomail-woocommerce-public.php',
        'SqualoMail_WooCommerce_Admin' => 'admin/class-squalomail-woocommerce-admin.php',
    );

    // if the file exists, require it
    $path = plugin_dir_path( __FILE__ );
    if (array_key_exists($class, $classes) && file_exists($path.$classes[$class])) {
        require $path.$classes[$class];
    }
});

/**
 * @return object
 */
function squalomail_environment_variables() {
    global $wp_version;

    $o = get_option('squalomail-woocommerce', false);

    return (object) array(
        'repo' => 'master',
        'environment' => 'production', // staging or production
        'version' => '2.5.1',
        'php_version' => phpversion(),
        'wp_version' => (empty($wp_version) ? 'Unknown' : $wp_version),
        'wc_version' => function_exists('WC') ? WC()->version : null,
        'logging' => ($o && is_array($o) && isset($o['squalomail_logging'])) ? $o['squalomail_logging'] : 'standard',
    );
}

/**
 * Remove pending jobs.
 * 
 * @param string $job
 * @param string $jobId
 */
function squalomail_unqueue($job, $jobId) {
    $existing_actions = function_exists('as_get_scheduled_actions') ? as_get_scheduled_actions(array(
        'hook' => $job, 
        'status' => ActionScheduler_Store::STATUS_PENDING,  
        'args' => array('obj_id' => $jobId),
        'group' => 'sqm-woocommerce'
    )) : null;
    
    if (!empty($existing_actions)) {
        try {
            as_unschedule_action($job, array('obj_id' => $jobId), 'sqm-woocommerce');
        } catch (\Exception $e) {}
    }
}

/**
 * Push a job onto the Action Scheduler queue.
 *
 * @param Squalomail_Woocommerce_Job $job
 * @param int $delay
 *
 * @return true
 */
function squalomail_as_push( Squalomail_Woocommerce_Job $job, $delay = 0 ) {			
    global $wpdb;
    $current_page = isset($job->current_page) && $job->current_page >= 0 ? $job->current_page : false;
    $job_id = isset($job->id) ? $job->id : ($current_page ? $job->current_page : get_class($job));


    $message = ($job_id != get_class($job)) ? ' :: '. (isset($job->current_page) ? 'page ' : 'obj_id ') . $job_id : '';
    
    $attempts = $job->get_attempts() > 0 ? ' attempt:' . $job->get_attempts() : '';

    if ($job->get_attempts() <= 5) {
        
        $jobSerialized = maybe_serialize($job);
        $createdAt = gmdate( 'Y-m-d H:i:s', time() );
        $args = array(
            'job' => $jobSerialized,
            'obj_id' => $job_id,
            'created_at'   => $createdAt
        );
        
        $existing_actions =  function_exists('as_get_scheduled_actions') ? as_get_scheduled_actions(array(
            'hook' => get_class($job), 
            'status' => ActionScheduler_Store::STATUS_PENDING,  
            'args' => array(
                'obj_id' => isset($job->id) ? $job->id : null), 
                'group' => 'sqm-woocommerce'
            )
        ) : null;
        
        if (!empty($existing_actions)) {
            try {
                as_unschedule_action(get_class($job), array('obj_id' => $job->id), 'sqm-woocommerce');
                if (strpos($jobSerialized, 'SqualoMail_WooCommerce_Cart_Update') !== false) {
                    $wpdb->update(
                        $wpdb->prefix."squalomail_jobs",
                        ['job' => $jobSerialized, 'created_at' => $createdAt],
                        ['obj_id' => $job_id],
                        ['%s', '%s'],
                        ['%s']
                    );
                }
            } catch (\Exception $e) {}
        }
        else {
            $inserted = $wpdb->insert($wpdb->prefix."squalomail_jobs", $args);
            if (!$inserted) {
                try {
                    if (squalomail_string_contains($wpdb->last_error, 'Table')) {
                        squalomail_debug('DB Issue: `squalomail_job` table was not found!', 'Creating Tables');
                        install_squalomail_queue();
                        $inserted = $wpdb->insert($wpdb->prefix."squalomail_jobs", $args);
                        if (!$inserted) {
                            squalomail_debug('Queue Job '.get_class($job), $wpdb->last_error);
                        }
                    }
                } catch (\Exception $e) {
                    squalomail_error_trace($e, 'trying to create queue tables');
                }
            }
        }
        
        $action_args = array(
            'obj_id' => $job_id,
        );

        if ($current_page !== false) {
            $action_args['page'] = $current_page;
        }

        $action = as_schedule_single_action( strtotime( '+'.$delay.' seconds' ), get_class($job), $action_args, "sqm-woocommerce");
      
        if (!empty($existing_actions)) {
            squalomail_debug('action_scheduler.reschedule_job', get_class($job) . ($delay > 0 ? ' restarts in '.$delay. ' seconds' : ' re-queued' ) . $message . $attempts);
        } 
        else {
            squalomail_log('action_scheduler.queue_job', get_class($job) . ($delay > 0 ? ' starts in '.$delay. ' seconds' : ' queued' ) . $message . $attempts);
        }
    
        return $action;	
    }
    else {
        $job->set_attempts(0);
        squalomail_log('action_scheduler.fail_job', get_class($job) . ' cancelled. Too many attempts' . $message . $attempts);
        return false;
    }
}


/**
 * @param Squalomail_Woocommerce_Job $job
 * @param int $delay
 * @param bool $force_now
 */
function squalomail_handle_or_queue(Squalomail_Woocommerce_Job $job, $delay = 0)
{   
    if ($job instanceof \SqualoMail_WooCommerce_Single_Order && isset($job->id)) {
        // if this is a order process already queued - just skip this
        if (get_site_transient("squalomail_order_being_processed_{$job->id}") == true) {
            return;
        }
        // tell the system the order is already queued for processing in this saving process - and we don't need to process it again.
        set_site_transient( "squalomail_order_being_processed_{$job->id}", true, 30);
    }
    
    $as_job_id = squalomail_as_push($job, $delay);
    
    if (!is_int($as_job_id)) {
        squalomail_log('action_scheduler.queue_fail', get_class($job) .' FAILED :: as_job_id: '.$as_job_id);
    }
}

function squalomail_get_remaining_jobs_count($job_hook) {
    $existing_actions =  function_exists('as_get_scheduled_actions') ? as_get_scheduled_actions(
        array(
            'hook' => $job_hook, 
            'status' => ActionScheduler_Store::STATUS_PENDING,  
            'group' => 'sqm-woocommerce', 
            'per_page' => -1,
        ), 'ids'
    ) : null;
    // squalomail_log('sync.full_sync_manager.queue', "counting {$job_hook} actions:", array($existing_actions));		
    return count($existing_actions);
}

/**
 * @param bool $force
 * @return bool
 */
function squalomail_list_has_double_optin($force = false) {
    if (!squalomail_is_configured()) {
        return false;
    }

    $key = 'double_optin';

    $double_optin = squalomail_get_transient($key);

    if (!$force && ($double_optin === 'yes' || $double_optin === 'no')) {
        return $double_optin === 'yes';
    }

    try {
        $data = squalomail_get_api()->getList(squalomail_get_list_id());
        $double_optin = array_key_exists('double_optin', $data) ? ($data['double_optin'] ? 'yes' : 'no') : 'no';
        squalomail_set_transient($key, $double_optin, 600);
        return $double_optin === 'yes';
    } catch (\Exception $e) {
        squalomail_error('api.list', __('Error retrieving list for double_optin check', 'squalomail-for-woocommerce'));
        throw $e;
    }

    return $double_optin === 'yes';
}


/**
 * @return bool
 */
function squalomail_is_configured() {
    return (bool) (squalomail_get_api_key() && squalomail_get_list_id());
}

/**
 * @return bool|int
 */
function squalomail_get_api_key() {
    return squalomail_get_option('squalomail_api_key', false);
}

/**
 * @return bool|int
 */
function squalomail_get_list_id() {
    return squalomail_get_option('squalomail_list', false);
}

/**
 * @return string
 */
function squalomail_get_store_id() {
    $store_id = squalomail_get_data('store_id', false);

    // if the store ID is not empty, let's check the last time the store id's have been verified correctly
    if (!empty($store_id)) {
        // see if we have a record of the last verification set for this job.
        $last_verification = squalomail_get_data('store-id-last-verified');
        // if it's less than 300 seconds, we don't need to beat up on Squalo's API to do this so often.
        // just return the store ID that was in memory.
        if ((!empty($last_verification) && is_numeric($last_verification)) && ((time() - $last_verification) < 600)) {
            //squalomail_log('debug.performance', 'prevented store endpoint api call');
            return $store_id;
        }
    }

    $api = squalomail_get_api();
    if (squalomail_is_configured()) {
        //squalomail_log('debug.performance', 'get_store_id - calling STORE endpoint.');
        // let's retrieve the store for this domain, through the API
        $store = $api->getStore($store_id, false);
        // if there's no store, try to fetch from sqm a store related to the current domain
        if (!$store) {
            //squalomail_log('debug.performance', 'get_store_id - no store found - calling STORES endpoint to update site id.');
            $stores = $api->stores();
            if (!empty($stores)) {
                //iterate thru stores, find correct store ID and save it to db
                foreach ($stores as $sqm_store) {
                    if ($sqm_store->getDomain() === get_option('siteurl')) {
                        update_option('squalomail-woocommerce-store_id', $sqm_store->getId(), 'yes');
                        $store_id = $sqm_store->getId();
                    }
                }
            }
        }
    }

    if (empty($store_id)) {
        squalomail_set_data('store_id', $store_id = uniqid(), 'yes');
    }

    // tell the system the last time we verified this store ID is valid with a timestamp.
    squalomail_set_data('store-id-last-verified', time(), 'yes');
    //squalomail_log('debug.performance', 'setting store id in memory for 300 seconds.');

    return $store_id;
}

/**
 * @return array
 */
function squalomail_get_user_tags_to_update($email = null, $order = null) {
    $tags = squalomail_get_option('squalomail_user_tags');
    $formatted_tags = array();
    
    if (!empty($tags)) {
        $tags = explode(',', $tags);

        foreach ($tags as $tag) {
            $formatted_tags[] = array("name" => $tag, "status" => 'active');
        }
    }

    // apply filter to user custom tags addition/removal
    $formatted_tags = apply_filters('squalomail_user_tags', $formatted_tags, $email, $order);
    
    if (empty($formatted_tags)){
        return false;
    }

    return $formatted_tags;
}

/**
 * @return bool|SqualoMail_WooCommerce_SqualoMailApi
 */
function squalomail_get_api() {

    if (($api = SqualoMail_WooCommerce_SqualoMailApi::getInstance())) {
        return $api;
    }

    if (($key = squalomail_get_api_key())) {
        return SqualoMail_WooCommerce_SqualoMailApi::constructInstance($key);
    }

    return false;
}

/**
 * @param $key
 * @param null $default
 * @return null
 */
function squalomail_get_option($key, $default = null) {
    $options = get_option('squalomail-woocommerce');
    if (!is_array($options)) {
        return $default;
    }
    if (!array_key_exists($key, $options)) {
        return $default;
    }
    return $options[$key];
}

/**
 * @param $key
 * @param null $default
 * @return mixed
 */
function squalomail_get_data($key, $default = null) {
    return get_option('squalomail-woocommerce-'.$key, $default);
}

/**
 * @param $key
 * @param $value
 * @param string $autoload
 * @return bool
 */
function squalomail_set_data($key, $value, $autoload = 'yes') {
    return update_option('squalomail-woocommerce-'.$key, $value, $autoload);
}

/**
 * @param $date
 * @return DateTime
 */
function squalomail_date_utc($date) {
    $timezone = wc_timezone_string();
    if (is_numeric($date)) {
        $stamp = $date;
        $date = new \DateTime('now', new DateTimeZone($timezone));
        $date->setTimestamp($stamp);
    } else {
        $date = new \DateTime($date, new DateTimeZone($timezone));
    }

    $date->setTimezone(new DateTimeZone('UTC'));
    return $date;
}

/**
 * @param $date
 * @return DateTime
 */
function squalomail_date_local($date) {
    $timezone = str_replace(':', '', squalomail_get_timezone());
    
    if (is_numeric($date)) {
        $stamp = $date;
        $date = new \DateTime('now', new DateTimeZone('UTC'));
        $date->setTimestamp($stamp);
    } else {
        $date = new \DateTime($date, new DateTimeZone('UTC'));
    }

    $date->setTimezone(new DateTimeZone($timezone));
    return $date;
}

/**
 * @param array $data
 * @return mixed
 */
function squalomail_array_remove_empty($data) {
    if (empty($data) || !is_array($data)) {
        return array();
    }
    foreach ($data as $key => $value) {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            unset($data[$key]);
        }
    }
    return $data;
}

/**
 * @return array
 */
function squalomail_get_timezone_list() {
    $zones_array = array();
    $timestamp = time();
    $current = date_default_timezone_get();

    foreach(timezone_identifiers_list() as $key => $zone) {
        date_default_timezone_set($zone);
        $zones_array[$key]['zone'] = $zone;
        $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
    }

    date_default_timezone_set($current);

    return $zones_array;
}

/**
 * Gets the current tomezone from wordpress settings
 * 
 * @return String timezone 
 */
function squalomail_get_timezone($humanReadable = false) {
    // get timezone data from options
    $timezone_string = get_option( 'timezone_string' );
    $offset  = get_option( 'gmt_offset' );
    
    $signal = ($offset <=> 0 ) < 0 ? "-" : "+";
    $offset = sprintf('%1s%02d:%02d', $signal, abs((int) $offset), abs(fmod($offset, 1) * 60));
    
    // shows timezone name + offset in hours and minutes, or only the timezone name. If no timezone string is set, show only offset
    if (!$humanReadable && $timezone_string) {
        $timezone = $timezone_string;
    }
    else if ($humanReadable && $timezone_string) {
        $timezone = "UTC" . $offset .' '. $timezone_string;
    }
    else if ($humanReadable && !$timezone_string) {
         $timezone = "UTC" . $offset;
    }
    else if (!$timezone_string) {
        $timezone = $offset;
    }
    
    return $timezone;
}

/**
 * @return bool
 */
function squalomail_check_woocommerce_plugin_status()
{
    // if you are using a custom folder name other than woocommerce just define the constant to TRUE
    if (defined("RUNNING_CUSTOM_WOOCOMMERCE") && RUNNING_CUSTOM_WOOCOMMERCE === true) {
        return true;
    }
    // it the plugin is active, we're good.
    if (in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option('active_plugins')))) {
        return true;
    }
    if (!is_multisite()) return false;
    $plugins = get_site_option( 'active_sitewide_plugins');
    return isset($plugins['woocommerce/woocommerce.php']);
}

/**
 * Get all the registered image sizes along with their dimensions
 *
 * @global array $_wp_additional_image_sizes
 *
 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
 *
 * @return array $image_sizes The image sizes
 */
function squalomail_woocommerce_get_all_image_sizes() {
    global $_wp_additional_image_sizes;
    $image_sizes = array();
    $default_image_sizes = get_intermediate_image_sizes();
    foreach ($default_image_sizes as $size) {
        $image_sizes[$size]['width'] = intval( get_option("{$size}_size_w"));
        $image_sizes[$size]['height'] = intval( get_option("{$size}_size_h"));
        $image_sizes[$size]['crop'] = get_option("{$size}_crop") ? get_option("{$size}_crop") : false;
    }
    if (isset($_wp_additional_image_sizes) && count($_wp_additional_image_sizes)) {
        $image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
    }
    return $image_sizes;
}

/**
 * @return array
 */
function squalomail_woocommerce_get_all_image_sizes_list() {
    $response = array();
    foreach (squalomail_woocommerce_get_all_image_sizes() as $key => $data) {
        $label = ucwords(str_replace('_', ' ', $key));
        $label = __($label);
        $response[$key] = "{$label} ({$data['width']} x {$data['height']})";
    }
    return $response;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-squalomail-woocommerce-activator.php
 */
function activate_squalomail_woocommerce() {
    // if we don't have woocommerce we need to display a horrible error message before the plugin is installed.
    if (!squalomail_check_woocommerce_plugin_status()) {
        // Deactivate the plugin
        deactivate_plugins(__FILE__);
        $error_message = __('The Squalo For WooCommerce plugin requires the <a href="http://wordpress.org/extend/plugins/woocommerce/">WooCommerce</a> plugin to be active!', 'woocommerce');
        wp_die($error_message);
    }
    SqualoMail_WooCommerce_Activator::activate();
}

/**
 * Create the queue tables
 */
function install_squalomail_queue() {
    SqualoMail_WooCommerce_Activator::create_queue_tables();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-squalomail-woocommerce-deactivator.php
 */
function deactivate_squalomail_woocommerce() {
    SqualoMail_WooCommerce_Deactivator::deactivate();
}

/**
 * @param $action
 * @param $message
 * @param null $data
 */
function squalomail_debug($action, $message, $data = null) {
    if (squalomail_environment_variables()->logging === 'debug' && function_exists('wc_get_logger')) {
        if (is_array($data) && !empty($data)) $message .= " :: ".wc_print_r($data, true);
        wc_get_logger()->debug("{$action} :: {$message}", array('source' => 'squalomail_woocommerce'));
    }
}

/**
 * @param $action
 * @param $message
 * @param array $data
 * @return array|WP_Error
 */
function squalomail_log($action, $message, $data = array()) {
    if (squalomail_environment_variables()->logging !== 'none' && function_exists('wc_get_logger')) {
        if (is_array($data) && !empty($data)) $message .= " :: ".wc_print_r($data, true);
        wc_get_logger()->notice("{$action} :: {$message}", array('source' => 'squalomail_woocommerce'));
    }
}

/**
 * @param $action
 * @param $message
 * @param array $data
 * @return array|WP_Error
 */
function squalomail_error($action, $message, $data = array()) {
    if (squalomail_environment_variables()->logging !== 'none' && function_exists('wc_get_logger')) {
        if ($message instanceof \Exception) $message = squalomail_error_trace($message);
        if (is_array($data) && !empty($data)) $message .= " :: ".wc_print_r($data, true);
        wc_get_logger()->error("{$action} :: {$message}", array('source' => 'squalomail_woocommerce'));
    }
}

/**
 * @param Exception $e
 * @param string $wrap
 * @return string
 */
function squalomail_error_trace($e, $wrap = "") {
    $error = "Error Code {$e->getCode()} :: {$e->getMessage()} on {$e->getLine()} in {$e->getFile()}";
    if (empty($wrap)) return $error;
    return "{$wrap} :: {$error}";
}

/**
 * Determine if a given string contains a given substring.
 *
 * @param  string  $haystack
 * @param  string|array  $needles
 * @return bool
 */
function squalomail_string_contains($haystack, $needles) {
    $has_mb = function_exists('mb_strpos');
    foreach ((array) $needles as $needle) {
        $has_needle = $needle != '';
        // make sure the server has "mb_strpos" otherwise this fails. Fallback to "strpos"
        $position = $has_mb ? mb_strpos($haystack, $needle) : strpos($haystack, $needle);
        if ($has_needle && $position !== false) {
            return true;
        }
    }
    return false;
}

/**
 * @return int
 */
function squalomail_get_coupons_count() {
    $posts = squalomail_count_posts('shop_coupon');
    unset($posts['auto-draft'], $posts['trash']);
    $total = 0;
    foreach ($posts as $status => $count) {
        $total += $count;
    }
    return $total;
}

/**
 * @return int
 */
function squalomail_get_category_count() {
    $terms = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false
    ));
    $total = 0;

    if (!empty($terms) && !is_wp_error($terms)) {
        $total = count($terms);
    } 

    return $total;
}

/**
 * @return int
 */
function squalomail_get_product_count() {
    $posts = squalomail_count_posts('product');
    unset($posts['auto-draft'], $posts['trash']);
    $total = 0;
    foreach ($posts as $status => $count) {
        $total += $count;
    }
    return $total;
}

/**
 * @return int
 */
function squalomail_get_order_count() {
    if(SqualoMail_WooCommerce_HPOS::enabled()) {
        foreach (wc_get_order_statuses() as $status_slug => $status_name) {
            $order_count += wc_orders_count($status_slug);
        }
        return $order_count;
    }

    $posts = squalomail_count_posts('shop_order');
    unset($posts['auto-draft'], $posts['trash']);
    $total = 0;
    foreach ($posts as $status => $count) {
        $total += $count;
    }
    return $total;
}

/**
 * @param $type
 * @return array|null|object
 */
function squalomail_count_posts($type) {
    global $wpdb;
    if ($type === 'shop_order') {
        $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN (%s, %s, %s, %s, %s, %s)  group BY post_status";
        $posts = $wpdb->get_results( $wpdb->prepare($query, 'shop_order', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled', 'wc-refunded', 'wc-failed' ));
    } else if ($type === 'product') {
        $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN (%s, %s, %s) group BY post_status";
        $posts = $wpdb->get_results( $wpdb->prepare($query, $type, 'private', 'publish', 'draft'));
    } else {
        $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s";
        $posts = $wpdb->get_results( $wpdb->prepare($query, $type, 'publish'));
    }

    $response = array();
    foreach ($posts as $post) {
        $response[$post->post_status] = $post->num_posts;
    }
    return $response;
}

/**
 * @return bool
 */
function squalomail_update_connected_site_script() {
    // pull the store ID
    $store_id = squalomail_get_store_id();

    // if the api is configured
    if ($store_id && ($api = squalomail_get_api())) {
        // if we have a store
        if (($store = $api->getStore($store_id))) {
            return squalomaili_refresh_connected_site_script($store);
        }
    }
    return false;
}

/**
 * @return bool|DateTime
 */
function squalomail_get_updated_connected_site_since_as_date_string() {
    $updated_at = get_option('squalomail-woocommerce-script_updated_at', false);
    if (empty($updated_at)) return '';
    try {
        $date = new \DateTime();
        $date->setTimestamp($updated_at);
        return $date->format('D, M j, Y g:i A');
    } catch (\Exception $e) {
        return '';
    }
}

/**
 * @return int
 */
function squalomail_get_updated_connected_site_since() {
    $updated_at = get_option('squalomail-woocommerce-script_updated_at', false);
    return empty($updated_at) ? 1000000 : (time() - $updated_at);
}

/**
 * @param int $seconds
 * @return bool
 */
function squalomail_should_update_connected_site_script($seconds = 600) {
    return squalomail_get_updated_connected_site_since() >= $seconds;
}

/**
 *
 */
function squalomail_update_connected_site_script_from_cdn() {
    if (squalomail_is_configured() && squalomail_should_update_connected_site_script() && ($store_id = squalomail_get_store_id())) {
        try {
            // pull the store, refresh the connected site url
            squalomaili_refresh_connected_site_script(squalomail_get_api()->getStore($store_id));
        } catch (\Exception $e) {
            squalomail_error("admin.update_connected_site_script", $e->getMessage());
        }
    }
}

/**
 * @param SqualoMail_WooCommerce_Store $store
 * @return bool
 */
function squalomaili_refresh_connected_site_script(SqualoMail_WooCommerce_Store $store) {

    $api = squalomail_get_api();

    $url = $store->getConnectedSiteScriptUrl();
    $fragment = $store->getConnectedSiteScriptFragment();

    // if it's not empty we need to set the values
    if ($url && $fragment) {

        // update the options for script_url and script_fragment
        update_option('squalomail-woocommerce-script_url', $url);
        update_option('squalomail-woocommerce-script_fragment', $fragment);
        update_option('squalomail-woocommerce-script_updated_at', time());

        // check to see if the site is connected
        if (!$api->checkConnectedSite($store->getId())) {

            // if it's not, connect it now.
            $api->connectSite($store->getId());
        }

        return true;
    }
    return false;
}

/**
 * @return string|false
 */
function squalomail_get_connected_site_script_url() {
    return get_option('squalomail-woocommerce-script_url', false);
}

/**
 * @return string|false
 */
function squalomail_get_connected_site_script_fragment() {
    return get_option('squalomail-woocommerce-script_fragment', false);
}

/**
 * @param $email
 * @return bool
 */
function squalomail_email_is_allowed($email) {
    if (!is_email($email) || squalomail_email_is_amazon($email) || squalomail_email_is_privacy_protected($email)) {
        return false;
    }
    return true;
}

/**
 * @param $email
 * @return bool
 */
function squalomail_email_is_privacy_protected($email) {
    return $email === 'deleted@site.invalid';
}

/**
 * @param $email
 * @return bool
 */
function squalomail_email_is_amazon($email) {
    return squalomail_string_contains($email, '@marketplace.amazon.');
}

/**
 * @param $str
 * @return string
 */
function squalomail_hash_trim_lower($str) {
    return md5(trim(strtolower($str)));
}

/**
 * @param $key
 * @param null $default
 * @return mixed|null
 */
function squalomail_get_transient($key, $default = null) {
    $transient = get_site_transient("squalomail-woocommerce.{$key}");
    return empty($transient) ? $default : $transient;
}

/**
 * @param $key
 * @param $value
 * @param int $seconds
 * @return bool
 */
function squalomail_set_transient($key, $value, $seconds = 60) {
    squalomail_delete_transient($key);
    return set_site_transient("squalomail-woocommerce.{$key}", array(
        'value' => $value,
        'expires' => time()+$seconds,
    ), $seconds);
}

/**
 * @param $key
 * @return bool
 */
function squalomail_delete_transient($key) {
    return delete_site_transient("squalomail-woocommerce.{$key}");
}

/**
 * @param $key
 * @param null $default
 * @return mixed|null
 */
function squalomail_get_transient_value($key, $default = null) {
    $transient = squalomail_get_transient($key, false);
    return (is_array($transient) && array_key_exists('value', $transient)) ? $transient['value'] : $default;
}

/**
 * @param $key
 * @param $value
 * @return bool|null
 */
function squalomail_check_serialized_transient_changed($key, $value) {
    if (($saved = squalomail_get_transient_value($key)) && !empty($saved)) {
        return serialize($saved) === serialize($value);
    }
    return null;
}

/**
 * @param $email
 * @return bool|string
 */
function squalomail_get_transient_email_key($email) {
    $email = md5(trim(strtolower($email)));
    return empty($email) ? false : 'SqualoMail_WooCommerce_User_Submit@'.$email;
}

/**
 * @param $email
 * @param $status_meta
 * @param int $seconds
 * @return bool
 */
function squalomail_tell_system_about_user_submit($email, $status_meta, $seconds = 60) {
   return squalomail_set_transient(squalomail_get_transient_email_key($email), $status_meta, $seconds);
}

/**
 * @param $subscribed
 * @return array
 */
function squalomail_get_subscriber_status_options($subscribed) {
    try {
        $requires = squalomail_list_has_double_optin();
    } catch (\Exception $e) {
        return false;
    }

    // if it's true - we set this value to NULL so that we do a 'pending' association on the member.
    $status_if_new = $requires ? null : $subscribed;
    $status_if_update = $requires ? 'pending' : $subscribed;

    // set an array of status meta that we will use for comparison below to the transient data
    return array(
        'requires_double_optin' => $requires,
        'created' => $status_if_new,
        'updated' => $status_if_update
    );
}

function squalomail_check_if_on_sync_tab() {
    if ((isset($_GET['page']) && $_GET['page'] === 'squalomail-woocommerce')) {
        $options = get_option('squalomail-woocommerce', array());
        if (isset($_GET['tab'])) {
            if ($_GET['tab'] === 'sync') {
                return true;
            }
            return false;
        }
        else if (isset($options['active_tab']) && $options['active_tab'] === 'sync') {
			return true;
		}
    }
    return false;
}

function squalomail_flush_database_tables() {
    try {
        /** @var \ */
        global $wpdb;
        
        squalomail_delete_as_jobs();
        
        $wpdb->query("TRUNCATE `{$wpdb->prefix}squalomail_carts`");
        $wpdb->query("TRUNCATE `{$wpdb->prefix}squalomail_jobs`");
    } catch (\Exception $e) {}
}

function squalomail_flush_sync_job_tables() {
    try {
        /** @var \ */
        global $wpdb;
        
        squalomail_delete_as_jobs();
        
        $wpdb->query("TRUNCATE `{$wpdb->prefix}squalomail_jobs`");
    } catch (\Exception $e) {}
}

function squalomail_delete_as_jobs() {

    $existing_as_actions = function_exists('as_get_scheduled_actions') ? as_get_scheduled_actions(
        array(
            'status' => ActionScheduler_Store::STATUS_PENDING,  
            'group' => 'sqm-woocommerce',
            'per_page' => -1,
        )
    ) : null;
    
    if (!empty($existing_as_actions)) {
        foreach ($existing_as_actions as $as_action) {
            try {
                as_unschedule_action($as_action->get_hook(), $as_action->get_args(), 'sqm-woocommerce');    # code...
            } catch (\Exception $e) {}
        }
        return true;
    }
    return false;

}
function squalomail_flush_sync_pointers() {
    // clean up the initial sync pointers
    foreach (array('orders', 'products', 'categories', 'coupons') as $resource_type) {
        delete_option("squalomail-woocommerce-sync.{$resource_type}.started_at");
        delete_option("squalomail-woocommerce-sync.{$resource_type}.completed_at");
        delete_option("squalomail-woocommerce-sync.{$resource_type}.started_at");
        delete_option("squalomail-woocommerce-sync.{$resource_type}.current_page");
    }
}

/**
 * To be used when running clean up for uninstalls or store disconnection.
 */
function squalomail_clean_database() {
    global $wpdb;
    
    // delete custom tables data
    squalomail_flush_database_tables();

    // delete plugin options
    $plugin_options = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE 'squalomail%woocommerce%'" );

    foreach( $plugin_options as $option ) {
        delete_option( $option->option_name );
    }
}

/**
 * @return bool
 */
function squalomail_has_started_syncing() {
    $sync_started_at = get_option('squalomail-woocommerce-sync.started_at');
    $sync_completed_at = get_option('squalomail-woocommerce-sync.completed_at');
    return ($sync_completed_at < $sync_started_at);
}

/**
 * @return bool
 */
function squalomail_is_done_syncing() {
    $sync_started_at = get_option('squalomail-woocommerce-sync.started_at');
    $sync_completed_at = get_option('squalomail-woocommerce-sync.completed_at');
    if ($sync_completed_at == false) return false;
    else return ($sync_completed_at >= $sync_started_at);
}

function run_squalomail_woocommerce() {
    $env = squalomail_environment_variables();
    $plugin = new SqualoMail_WooCommerce($env->environment, $env->version);
    $plugin->run();
    if (isset($_GET['restart_order_sync']) && $_GET['restart_order_sync'] === '1') {
        squalomail_as_push(new SqualoMail_WooCommerce_Process_Orders());
    }
}

function squalomail_on_all_plugins_loaded() {
    if (squalomail_check_woocommerce_plugin_status()) {
        run_squalomail_woocommerce();
    }
}

function squalomail_get_allowed_capability() {
    $capability = 'manage_options';
    if (current_user_can('manage_woocommerce') && squalomail_get_option('squalomail_permission_cap') == 'manage_woocommerce') {
        return 'manage_woocommerce';
    }
    return apply_filters('squalomail_allowed_capability', $capability);
}

/**
 * @param SqualoMail_WooCommerce_Order $order
 * @param null|boolean $subscribed
 */
function squalomail_update_member_with_double_opt_in(SqualoMail_WooCommerce_Order $order, $subscribed = null)
{
    if (!squalomail_is_configured()) return;

    $api = squalomail_get_api();

    // if the customer has a flag to double opt in - we need to push this data over to Squalo as pending
    // before the order is submitted.
    if ($subscribed) {
        if ($order->getCustomer()->requiresDoubleOptIn()) {
            try {
                $list_id = squalomail_get_list_id();
                $merge_fields = $order->getCustomer()->getMergeFields();
                $email = $order->getCustomer()->getEmailAddress();

                try {
                    $member = $api->member($list_id, $email);
                    if ($member['status'] === 'transactional') {
                        $api->update($list_id, $email, 'pending', $merge_fields);
                        squalomail_tell_system_about_user_submit($email, squalomail_get_subscriber_status_options('pending'), 60);
                        squalomail_log('double_opt_in', "Updated {$email} Using Double Opt In - previous status was '{$member['status']}'", $merge_fields);
                    }
                } catch (\Exception $e) {
                    // if the error code is 404 - need to subscribe them because it means they were not on the list.
                    if ($e->getCode() == 404) {
                        $api->subscribe($list_id, $email, false, $merge_fields);
                        squalomail_tell_system_about_user_submit($email, squalomail_get_subscriber_status_options(false), 60);
                        squalomail_log('double_opt_in', "Subscribed {$email} Using Double Opt In", $merge_fields);
                    } else {
                        squalomail_error('double_opt_in.update', $e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                squalomail_error('double_opt_in.create', $e->getMessage());
            }
        } else {
            // if we've set the wordpress user correctly on the customer
            if (($wordpress_user = $order->getCustomer()->getWordpressUser())) {
                $user_submit = new SqualoMail_WooCommerce_User_Submit($wordpress_user->ID, true, null);
                $user_submit->handle();
            }
        }
    }
}

// call server to update comm status
function squalomail_update_communication_status() {
    $plugin_admin = SqualoMail_WooCommerce_Admin::instance();
    $original_opt = $plugin_admin->getData('comm.opt',0);
    $options = $plugin_admin->getOptions();
    if (is_array($options) && array_key_exists('admin_email', $options)) {
        $plugin_admin->squalomail_set_communications_status_on_server($original_opt, $options['admin_email']);
    }
}

// call server to update comm status
function squalomail_remove_communication_status() {
    $plugin_admin = SqualoMail_WooCommerce_Admin::instance();
    $original_opt = $plugin_admin->getData('comm.opt',0);
    $options = $plugin_admin->getOptions();
    if (is_array($options) && array_key_exists('admin_email', $options)) {
        $remove = true;
        $plugin_admin->squalomail_set_communications_status_on_server($original_opt, $options['admin_email'], $remove);
    }
}

/**
 * Removes any Woocommece inbox notes this plugin created.
 */
function squalomail_remove_activity_panel_inbox_notes() {
    if ( ! class_exists( '\Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ) {
        return;
    }

    // if we can't use woocommerce for some reason - just return null
    if (!function_exists('WC')) {
        return;
    }

    // if we do not have the ability to use notes, just cancel out here.
    if (!method_exists(WC(), 'is_wc_admin_active') || !WC()->is_wc_admin_active()) {
        return;
    }

    try {
        \Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'squalomail-for-woocommerce-incomplete-install' );
    } catch (\Exception $e) {
        // do nothing.
    }
}

// Print notices outside woocommerce admin bar
function squalomail_settings_errors() {
    $settings_errors = get_settings_errors();
    $notices_html = '';
    foreach ($settings_errors as $notices) {
        $notices_html .= '<div id="setting-error-'. $notices['code'].'" class="notice notice-'. $notices['type'].' inline is-dismissible"><p>' . $notices['message'] . '</p></div>';
    }
    return $notices_html;
}

/**
 * @param null $user_email
 * @param null $language
 * @param string $caller
 * @param string $status_if_new
 * @param SqualoMail_WooCommerce_Order|null $order
 * @throws SqualoMail_WooCommerce_Error
 * @throws SqualoMail_WooCommerce_ServerError
 */
function squalomail_member_data_update($user_email = null, $language = null, $caller = '', $status_if_new = 'transactional', $order = null, $gdpr_fields = null) {
    squalomail_debug('debug', "squalomail_member_data_update", array(
        'user_email' => $user_email,
        'user_language' => $language,
        'caller' => $caller,
        'status_if_new' => $status_if_new,
    ));
    if (!$user_email) return;
    
    $hash = md5(strtolower(trim($user_email)));
    $gdpr_fields_to_save = null;

    if ($caller !== 'cart' || !squalomail_get_transient($caller . ".member.{$hash}")) {
        $list_id = squalomail_get_list_id();
        try {
            // try to get the member to update if already synced
            $member = squalomail_get_api()->member($list_id, $user_email);
            // update member with new data
            // if the member's subscriber status was transactional - and if we're passing in either one of these options below,
            // we can attach the new status to the member.
            

            if ($member['status'] === 'transactional' && in_array($status_if_new, array('subscribed', 'pending'))) {
                $member['status'] = $status_if_new;
            }

            if (($member['status'] === 'transactional' && in_array($status_if_new, array('subscribed', 'pending'))) || $member['status'] === 'subscribed') {
                if (!empty($gdpr_fields)) {
                    $gdpr_fields_to_save = [];
                    foreach ($gdpr_fields as $id => $value) {
                        $gdpr_field['marketing_permission_id'] = $id;
                        $gdpr_field['enabled'] = (bool) $value;
                        $gdpr_fields_to_save[] = $gdpr_field;
                    }
                }
            }
            $merge_fields = $order ? apply_filters('squalomail_get_ecommerce_merge_tags', array(), $order) : array();
            if (!is_array($merge_fields)) $merge_fields = array();
            squalomail_get_api()->update($list_id, $user_email, $member['status'], $merge_fields, null, $language, $gdpr_fields_to_save);
            // set transient to prevent too many calls to update language
            squalomail_set_transient($caller . ".member.{$hash}", true, 3600);
            squalomail_log($caller . '.member.updated', "Updated {$user_email} subscriber status to {$member['status']} and language to {$language}");
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                $merge_fields = $order ? apply_filters('squalomail_get_ecommerce_merge_tags', array(), $order) : array();
                if (!is_array($merge_fields)) $merge_fields = array();
                // member doesn't exist yet, create as transactional ( or what was passed in the function args )
                squalomail_get_api()->subscribe($list_id, $user_email, $status_if_new, $merge_fields, array(), $language);
                // set transient to prevent too many calls to update language
                squalomail_set_transient($caller . ".member.{$hash}", true, 3600);
                squalomail_log($caller . '.member.created', "Added {$user_email} as transactional, setting language to [{$language}]");
            } else {
                squalomail_error($caller . '.member.sync.error', $e->getMessage(), $user_email);
            }
        }
    }
}

/**
 * @param string $name
 * @param string $value
 * @param int $expire
 * @param string $path
 * @param string $domain
 * @param bool $secure
 * @param bool $httponly
 * @param string $samesite
 * @return void
 */
function squalomail_set_cookie($name, $value, $expire, $path, $domain = '', $secure = true, $httponly = false, $samesite = 'Strict') {
    if (PHP_VERSION_ID < 70300) {
        @setcookie($name, $value, $expire, $path . '; samesite=' . $samesite, $domain, $secure, $httponly);
        return;
    }
    @setcookie($name, $value, [
        'expires' => $expire,
        'path' => $path,
        'domain' => $domain,
        'samesite' => $samesite,
        'secure' => $secure,
        'httponly' => $httponly,
    ]);
}


// Add WP CLI commands
if (defined( 'WP_CLI' ) && WP_CLI) {
    try {
        /**
         * Service push to Squalo
         *
         * <type>
         * : product_sync order_sync order product
         */
        function squalomail_cli_push_command( $args, $assoc_args ) {
            if (is_array($args) && isset($args[0])) {
                switch($args[0]) {

                    case 'product_sync':
                        squalomail_handle_or_queue(new SqualoMail_WooCommerce_Process_Products());
                        WP_CLI::success("queued up the product sync!");
                        break;

                    case 'order_sync':
                        squalomail_handle_or_queue(new SqualoMail_WooCommerce_Process_Orders());
                        WP_CLI::success("queued up the order sync!");
                        break;

                    case 'category_sync':
                        squalomail_handle_or_queue(new SqualoMail_WooCommerce_Process_Categories());
                        WP_CLI::success("queued up the category sync!");
                        break;

                    case 'order':
                        if (!isset($args[1])) {
                            wp_die('You must specify an order id as the 2nd parameter.');
                        }
                        squalomail_handle_or_queue(new SqualoMail_WooCommerce_Single_Order($args[1]));
                        WP_CLI::success("queued up the order {$args[1]}!");
                        break;

                    case 'product':
                        if (!isset($args[1])) {
                            wp_die('You must specify a product id as the 2nd parameter.');
                        }
                        squalomail_handle_or_queue(new SqualoMail_WooCommerce_Single_Product($args[1]));
                        WP_CLI::success("queued up the product {$args[1]}!");
                        break;
                }
            }
        };
        WP_CLI::add_command( 'squalomail_push', 'squalomail_cli_push_command');
        WP_CLI::add_command( 'queue', 'Squalomail_Wocoomerce_CLI' );
    } catch (\Exception $e) {}
}
