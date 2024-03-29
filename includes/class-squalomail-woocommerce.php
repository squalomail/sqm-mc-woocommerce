<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://squalomail.com
 * @since      1.0.1
 *
 * @package    SqualoMail_WooCommerce
 * @subpackage SqualoMail_WooCommerce/includes
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
 * @package    SqualoMail_WooCommerce
 * @subpackage SqualoMail_WooCommerce/includes
 * @author     Ryan Hungate <ryan@vextras.com>
 */
class SqualoMail_WooCommerce
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      SqualoMail_WooCommerce_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * @var string
     */
    protected $environment = 'production';

    protected $is_configured;

    protected static $logging_config = null;

    /**
     * @return object
     */
    public static function getLoggingConfig()
    {
        if (is_object(static::$logging_config)) {
            return static::$logging_config;
        }

        $plugin_options = get_option('squalomail-woocommerce');
        $is_options = is_array($plugin_options);

        $api_key = $is_options && array_key_exists('squalomail_api_key', $plugin_options) ?
            $plugin_options['squalomail_api_key'] : false;

        $enable_logging = $is_options &&
            array_key_exists('squalomail_debugging', $plugin_options) &&
            $plugin_options['squalomail_debugging'];

        $account_id = $is_options && array_key_exists('squalomail_account_info_id', $plugin_options) ?
            $plugin_options['squalomail_account_info_id'] : false;

        $username = $is_options && array_key_exists('squalomail_account_info_username', $plugin_options) ?
            $plugin_options['squalomail_account_info_username'] : false;

        $api_key_parts = str_getcsv($api_key, '-');
        $data_center = isset($api_key_parts[1]) ? $api_key_parts[1] : 'us1';

        return static::$logging_config = (object)array(
            'enable_logging' => (bool)$enable_logging,
            'account_id' => $account_id,
            'username' => $username,
            'endpoint' => 'https://ecommerce.' . $data_center . '.list-manage.com/ecommerce/log',
        );
    }


    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @param string $environment
     * @param string $version
     *
     * @since    1.0.0
     */
    public function __construct($environment = 'production', $version = '1.0.0')
    {
        $this->plugin_name = 'squalomail-woocommerce';
        $this->version = $version;
        $this->environment = $environment;
        $this->is_configured = squalomail_is_configured();

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_gdpr_hooks();

        $this->activateSqualoMailNewsletter();
        $this->activateSqualoMailService();
        $this->applyQueryStringOverrides();
    }

    /**
     *
     */
    private function applyQueryStringOverrides()
    {
        // if we need to refresh the double opt in for any reason - just do it here.
        if ($this->queryStringEquals('sqm_doi_refresh', '1')) {
            try {
                $enabled_doi = squalomail_list_has_double_optin(true);
            } catch (\Exception $e) {
                squalomail_error('sqm.utils.doi_refresh', 'failed updating doi transient');
                return false;
            }
            squalomail_log('sqm.utils.doi_refresh', ($enabled_doi ? 'turned ON' : 'turned OFF'));
        }
    }

    /**
     * @param $key
     * @param string $value
     * @return bool
     */
    private function queryStringEquals($key, $value = '1')
    {
        return isset($_GET[$key]) && $_GET[$key] === $value;
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - SqualoMail_WooCommerce_Loader. Orchestrates the hooks of the plugin.
     * - SqualoMail_WooCommerce_i18n. Defines internationalization functionality.
     * - SqualoMail_WooCommerce_Admin. Defines all hooks for the admin area.
     * - SqualoMail_WooCommerce_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        // fire up the loader
        $this->loader = new SqualoMail_WooCommerce_Loader();

        // change up the queue to use the new rest api version
        $service = new SqualoMail_WooCommerce_Rest_Api();
        $this->loader->add_action( 'rest_api_init', $service, 'register_routes');
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the SqualoMail_WooCommerce_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new SqualoMail_WooCommerce_i18n();
        $this->loader->add_action('init', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Define the GDPR additions from Automattic.
     */
    private function define_gdpr_hooks()
    {
        $gdpr = new SqualoMail_WooCommerce_Privacy();

        $this->loader->add_action('admin_init', $gdpr, 'privacy_policy');
        $this->loader->add_filter('wp_privacy_personal_data_exporters', $gdpr, 'register_exporter', 10);
        $this->loader->add_filter('wp_privacy_personal_data_erasers', $gdpr, 'register_eraser', 10);
    }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = SqualoMail_WooCommerce_Admin::instance();

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		// Add menu item
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu', 71);

        // Add WooCommerce Navigation Bar
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_woocommerce_navigation_bar');

        // Add Settings link to the plugin
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php');
		$this->loader->add_filter('plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links');

		// make sure we're listening for the admin init
        $this->loader->add_action('admin_init', $plugin_admin, 'options_update');
        $this->loader->add_action('admin_notices', $plugin_admin, 'initial_notice');
        
		// put the menu on the admin top bar.
		//$this->loader->add_action('admin_bar_menu', $plugin_admin, 'admin_bar', 100);

        $this->loader->add_action('plugins_loaded', $plugin_admin, 'update_db_check');
        $this->loader->add_action('admin_init', $plugin_admin, 'setup_survey_form');
        $this->loader->add_action('admin_footer', $plugin_admin, 'inject_sync_ajax_call');

        // update SQM store information when woocommerce general settings are saved
        $this->loader->add_action('woocommerce_settings_save_general', $plugin_admin, 'squalomail_update_woo_settings');
        
        // update SQM store information if "WooCommerce Multi-Currency Extension" settings are saved
        if ( class_exists( 'WOOMULTI_CURRENCY_F' ) ) {
            $this->loader->add_action('villatheme_support_woo-multi-currency', $plugin_admin, 'squalomail_update_woo_settings');
        }

        // Squalomail oAuth
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_oauth_start', $plugin_admin, 'squalomail_woocommerce_ajax_oauth_start' );
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_oauth_status', $plugin_admin, 'squalomail_woocommerce_ajax_oauth_status' );
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_oauth_finish', $plugin_admin, 'squalomail_woocommerce_ajax_oauth_finish' );

        // Create new squalomail Account methods
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_create_account_check_username', $plugin_admin, 'squalomail_woocommerce_ajax_create_account_check_username' );
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_create_account_signup', $plugin_admin, 'squalomail_woocommerce_ajax_create_account_signup' );
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_support_form', $plugin_admin, 'squalomail_woocommerce_ajax_support_form' );

        // add Shop Manager capability to save options
        $this->loader->add_action('option_page_capability_squalomail-woocommerce', $plugin_admin, 'squalomail_woocommerce_option_page_capability');

        // set communications box status
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_communication_status', $plugin_admin, 'squalomail_woocommerce_communication_status' );

        // Load log file via ajax
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_load_log_file', $plugin_admin, 'squalomail_woocommerce_ajax_load_log_file' );

        // delete log file via ajax
        $this->loader->add_action( 'wp_ajax_squalomail_woocommerce_delete_log_file', $plugin_admin, 'squalomail_woocommerce_ajax_delete_log_file' );
    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new SqualoMail_WooCommerce_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('wp_footer', $plugin_public, 'add_inline_footer_script');

        $this->loader->add_action('woocommerce_after_checkout_form', $plugin_public, 'add_JS_checkout', 10);
        $this->loader->add_action('woocommerce_register_form', $plugin_public, 'add_JS_checkout', 10);
	}

	/**
	 * Handle the newsletter actions here.
	 */
	private function activateSqualoMailNewsletter()
	{
		$service = SqualoMail_Newsletter::instance();

		if ($this->is_configured && $service->isConfigured()) {

			$service->setEnvironment($this->environment);
			$service->setVersion($this->version);

			// adding the ability to render the checkbox on another screen of the checkout page.
			$render_on = $service->getOption('squalomail_checkbox_action', 'woocommerce_after_checkout_billing_form');

			$this->loader->add_action($render_on, $service, 'applyNewsletterField', 10);

			$this->loader->add_action('woocommerce_ppe_checkout_order_review', $service, 'applyNewsletterField', 10);
			$this->loader->add_action('woocommerce_register_form', $service, 'applyNewsletterField', 10);

			$this->loader->add_action('woocommerce_checkout_order_processed', $service, 'processNewsletterField', 10, 2);
			$this->loader->add_action('woocommerce_ppe_do_payaction', $service, 'processPayPalNewsletterField', 10, 1);
			$this->loader->add_action('woocommerce_register_post', $service, 'processRegistrationForm', 10, 3);
		}
	}

	/**
	 * Handle all the service hooks here.
	 */
	private function activateSqualoMailService()
	{
		$service = SqualoMail_Service::instance();

		if ($service->isConfigured()) {

			$service->setEnvironment($this->environment);
			$service->setVersion($this->version);

			// core hook setup
			$this->loader->add_action('admin_init', $service, 'adminReady');
			$this->loader->add_action('woocommerce_init', $service, 'wooIsRunning');

			// for the data sync we need to configure basic auth.
			$this->loader->add_filter('http_request_args', $service, 'addHttpRequestArgs', 10, 2);

			// campaign tracking
			$this->loader->add_action( 'init', $service, 'handleCampaignTracking' );

			// order hooks
            $this->loader->add_action('woocommerce_new_order', $service, 'handleNewOrder', 11, 1);
            $this->loader->add_action('woocommerce_order_status_changed', $service, 'handleOrderStatusChanged', 11, 3);

			// refunds
            $this->loader->add_action('woocommerce_order_partially_refunded', $service, 'onPartiallyRefunded', 20, 1);

			// cart hooks
            $this->loader->add_filter('woocommerce_update_cart_action_cart_updated', $service, 'handleCartUpdated');
			$this->loader->add_action('woocommerce_add_to_cart', $service, 'handleCartUpdated');
			$this->loader->add_action('woocommerce_cart_item_removed', $service, 'handleCartUpdated');

			// save post hooks
			$this->loader->add_action('save_post', $service, 'handlePostSaved', 10, 3);
            $this->loader->add_action('wp_trash_post', $service, 'handlePostTrashed', 10, 1);
            $this->loader->add_action('untrashed_post', $service, 'handlePostRestored', 10, 1);

            // category hooks
            $this->loader->add_action('edited_product_cat', $service, 'handleCategorySaved', 10, 2);
            $this->loader->add_action('created_product_cat', $service, 'handleCategorySaved', 10, 2);
            $this->loader->add_action('delete_product_cat', $service, 'handleCategoryDeleted', 10, 4);
            $this->loader->add_action('set_object_terms', $service, 'handlePostCategoryUpdate', 10, 6);

			//coupons
            $this->loader->add_action('woocommerce_new_coupon', $service, 'handleNewCoupon', 10, 1);
            $this->loader->add_action('woocommerce_coupon_options_save', $service, 'handleCouponSaved', 10, 2);
            $this->loader->add_action('woocommerce_api_create_coupon', $service, 'handleCouponSaved', 9, 2);

            $this->loader->add_action('woocommerce_delete_coupon', $service, 'handlePostTrashed', 10, 1);
            $this->loader->add_action('woocommerce_trash_coupon', $service, 'handlePostTrashed', 10, 1);
            
            $this->loader->add_action('woocommerce_rest_delete_shop_coupon_object', $service, 'handleAPICouponTrashed', 10, 3);

			// handle the user registration hook
			$this->loader->add_action('user_register', $service, 'handleUserRegistration', 10, 1);
			// handle the user updated profile hook
			$this->loader->add_action('profile_update', $service, 'handleUserUpdated', 10, 2);

			// get user by hash ( public and private )
            $this->loader->add_action('wp_ajax_squalomail_get_user_by_hash', $service, 'get_user_by_hash');
            $this->loader->add_action('wp_ajax_nopriv_squalomail_get_user_by_hash', $service, 'get_user_by_hash');

            // set user by email hash ( public and private )
            $this->loader->add_action('wp_ajax_squalomail_set_user_by_email', $service, 'set_user_by_email');
            $this->loader->add_action('wp_ajax_nopriv_squalomail_set_user_by_email', $service, 'set_user_by_email');

            $jobs_classes = array(
                "SqualoMail_WooCommerce_Single_Order",
                "SqualoMail_WooCommerce_SingleCoupon",
                "SqualoMail_WooCommerce_Single_Product",
                "SqualoMail_WooCommerce_Product_Category",
                "SqualoMail_WooCommerce_Cart_Update",
                "SqualoMail_WooCommerce_User_Submit",
                "SqualoMail_WooCommerce_Process_Coupons",
                "SqualoMail_WooCommerce_Process_Orders",
                "SqualoMail_WooCommerce_Process_Products",
                "SqualoMail_WooCommerce_Process_Categories"
            );
            foreach ($jobs_classes as $job_class) {
                $this->loader->add_action($job_class, $service, 'squalomail_process_single_job', 10, 1);
            }
            // sync stats manager
            $this->loader->add_action('SqualoMail_WooCommerce_Process_Full_Sync_Manager', $service, 'squalomail_process_sync_manager', 10, 1);
		}
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
	 * @since     1.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.1
	 * @return    SqualoMail_WooCommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
