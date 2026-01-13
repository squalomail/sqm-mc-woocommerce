<?php

/**
 * Created by Squalo.
 *
 * Name: Ryan Hungate
 * Email: ryan@vextras.com
 * Date: 2/22/16
 * Time: 3:45 PM
 */
abstract class SqualoMail_WooCommerce_Options
{
    /**
     * @var SqualoMail_WooCommerce_SqualoMailApi
     */
    protected $api;
    protected $plugin_name = 'squalomail-woocommerce';
    protected $environment = 'production';
    protected $version = '1.0.0';
    protected $plugin_options = null;
    protected $is_admin = false;

    /**
     * hook calls this so that we know the admin is here.
     */
    public function adminReady()
    {
        $this->is_admin = current_user_can(squalomail_get_allowed_capability());
        if (get_option('squalomail_woocommerce_plugin_do_activation_redirect', false)) {
            delete_option('squalomail_woocommerce_plugin_do_activation_redirect');

            // don't do the redirect while activating the plugin through the rest API. ( Bartosz from Woo asked for this )
            if ((defined( 'REST_REQUEST' ) && REST_REQUEST)) {
                return;
            }

            // the woocommerce onboarding wizard will have a profile
            $onboarding_profile = get_option('woocommerce_onboarding_profile');
            // if the onboarding profile has business extensions
            if (is_array($onboarding_profile) && array_key_exists('business_extensions', $onboarding_profile)) {
                // if the business extensions contains our plugin, we just skip this.
                if (is_array($onboarding_profile['business_extensions']) && in_array('squalomail-for-woocommerce', $onboarding_profile['business_extensions'])) {
                    return;
                }
            }

            if (!isset($_GET['activate-multi'])) {
                wp_redirect("admin.php?page=squalomail-woocommerce");
            }
        }
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * @param $version
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getUniqueStoreID()
    {
        return squalomail_get_store_id();
    }

    /**
     * @param $env
     * @return $this
     */
    public function setEnvironment($env)
    {
        $this->environment = $env;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param $key
     * @param null $default
     * @return null
     */
    public function getOption($key, $default = null)
    {
        $options = $this->getOptions();
        if (isset($options[$key])) {
            return $options[$key];
        }
        return $default;
    }

    /**
     * @param $key
     * @param bool $default
     * @return bool
     */
    public function hasOption($key, $default = false)
    {
        return (bool) $this->getOption($key, $default);
    }

    /**
     * @return array
     */
    public function resetOptions()
    {
        return $this->plugin_options = get_option($this->plugin_name);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        if (empty($this->plugin_options)) {
            $this->plugin_options = get_option($this->plugin_name);
        }
        return is_array($this->plugin_options) ? $this->plugin_options : array();
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key, $value)
    {
        update_option($this->plugin_name.'-'.$key, $value, 'yes');
        return $this;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|void
     */
    public function getData($key, $default = null)
    {
        return get_option($this->plugin_name.'-'.$key, $default);
    }


    /**
     * @param $key
     * @return bool
     */
    public function removeData($key)
    {
        return delete_option($this->plugin_name.'-'.$key);
    }

    /**
     * @param $key
     * @param null $default
     * @return null|mixed
     */
    public function getCached($key, $default = null)
    {
        $cached = $this->getData("cached-$key", false);
        if (empty($cached) || !($cached = unserialize($cached))) {
            return $default;
        }

        if (empty($cached['till']) || (time() > $cached['till'])) {
            $this->removeData("cached-$key");
            return $default;
        }

        return $cached['value'];
    }

    /**
     * @param $key
     * @param $value
     * @param $seconds
     * @return $this
     */
    public function setCached($key, $value, $seconds = 60)
    {
        $time = time();
        $data = array('at' => $time, 'till' => $time + $seconds, 'value' => $value);
        $this->setData("cached-$key", serialize($data));

        return $this;
    }

    /**
     * @param $key
     * @param $callable
     * @param int $seconds
     * @return mixed|null
     */
    public function getCachedWithSetDefault($key, $callable, $seconds = 60)
    {
        if (!($value = $this->getCached($key, false))) {
            $value = call_user_func($callable);
            $this->setCached($key, $value, $seconds);
        }
        return $value;
    }

    /**
     * @return bool
     */
    public function isConfigured()
    {
        return true;
        //return $this->getOption('public_key', false) && $this->getOption('secret_key', false);
    }

    /**
     * @return bool
     */
    protected function doingAjax()
    {
        return defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * @return SqualoMail_WooCommerce_SqualoMailApi
     */
    public function api()
    {
        if (empty($this->api)) {
            $this->api = new SqualoMail_WooCommerce_SqualoMailApi($this->getOption('squalomail_api_key', false));
        }

        return $this->api;
    }

    /**
     * @param array $data
     * @param $key
     * @param null $default
     * @return null|mixed
     */
    public function array_get(array $data, $key, $default = null)
    {
        if (isset($data[$key])) {
            return $data[$key];
        }

        return $default;
    }

    /**
     * @param bool $products
     * @param bool $orders
     * @return $this
     */
    public function removePointers($products = true, $orders = true)
    {
        if ($products) {
            $this->removeProductPointers();
        }

        if ($orders) {
            $this->removeOrderPointers();
        }

        $this->removeSyncPointers();

        $this->removeMiscPointers();

        return $this;
    }

    public function removeProductPointers()
    {
        delete_option('squalomail-woocommerce-sync.products.completed_at');
        delete_option('squalomail-woocommerce-sync.products.current_page');
    }

    public function removeOrderPointers()
    {
        delete_option('squalomail-woocommerce-sync.orders.prevent');
        delete_option('squalomail-woocommerce-sync.orders.completed_at');
        delete_option('squalomail-woocommerce-sync.orders.current_page');
    }

    public function removeSyncPointers()
    {
        squalomail_flush_sync_pointers();
    }

    public function removeMiscPointers()
    {
        delete_option('squalomail-woocommerce-errors.store_info');
        delete_option('squalomail-woocommerce-validation.api.ping');
        delete_option('squalomail-woocommerce-cached-api-lists');
        delete_option('squalomail-woocommerce-cached-api-ping-check');
    }
}
