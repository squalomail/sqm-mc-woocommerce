<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://squalomail.com
 * @since      1.0.1
 *
 * @package    SqualoMail_WooCommerce
 * @subpackage SqualoMail_WooCommerce/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    SqualoMail_WooCommerce
 * @subpackage SqualoMail_WooCommerce/admin
 * @author     Ryan Hungate <ryan@vextras.com>
 */
class SqualoMail_WooCommerce_Admin extends SqualoMail_WooCommerce_Options {

	protected $swapped_list_id = null;
	protected $swapped_store_id = null;

    /** @var null|static */
    protected static $_instance = null;

    /**
     * @return SqualoMail_WooCommerce_Admin
     */
    public static function instance()
    {
        if (!empty(static::$_instance)) {
            return static::$_instance;
        }
        $env = squalomail_environment_variables();
        static::$_instance = new SqualoMail_WooCommerce_Admin();
        static::$_instance->setVersion($env->version);
        return static::$_instance;
    }

	/**
	 * @return SqualoMail_WooCommerce_Admin|SqualoMail_WooCommerce_Options
	 */
	public static function connect()
	{
		return static::instance();
	}

    /**
     * @return array
     */
	private function disconnect_store()
	{
		// remove user from our marketing status audience
		try {
            squalomail_remove_communication_status();
        } catch (\Exception $e) {}

		if (($store_id = squalomail_get_store_id()) && ($sqm = squalomail_get_api()))  {
		    set_site_transient('squalomail_disconnecting_store', true, 15);
            if ($sqm->deleteStore($store_id)) {
                squalomail_log('store.disconnected', 'Store id ' . squalomail_get_store_id() . ' has been disconnected');
            } else {
                squalomail_log('store.NOT DISCONNECTED', 'Store id ' . squalomail_get_store_id() . ' has NOT been disconnected');
            }
        }

        // clean database
        squalomail_clean_database();

        $options = array();

		return $options;
	}
	
	/**
	 * Tests admin permissions, disconnect action and nonce
	 * @return bool 
	 */
	private function is_disconnecting() {
		return isset($_REQUEST['squalomail_woocommerce_disconnect_store'])
			   && current_user_can( 'manage_options' )
			   && $_REQUEST['squalomail_woocommerce_disconnect_store'] == 1 
			   && isset($_REQUEST['_disconnect-nonce']) 
			   && wp_verify_nonce($_REQUEST['_disconnect-nonce'], '_disconnect-nonce-'.squalomail_get_store_id());
	}

    /**
     * Tests admin permissions, disconnect action and nonce
     * @return bool
     */
    private function is_resyncing() {
        return isset($_REQUEST['squalomail_woocommerce_resync'])
            && current_user_can( 'manage_options' )
            && $_REQUEST['squalomail_woocommerce_resync'] == 1
            && isset($_REQUEST['_resync-nonce'])
            && wp_verify_nonce($_REQUEST['_resync-nonce'], '_resync-nonce-'.squalomail_get_store_id());
    }
		
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook) {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/squalomail-woocommerce-admin.css', array(), $this->version.'.21', 'all' );

		if ( strpos($hook, 'page_squalomail-woocommerce') !== false ) {
			if ( get_bloginfo( 'version' ) < '5.3') {
				wp_enqueue_style( $this->plugin_name."-settings", plugin_dir_url( __FILE__ ) . 'css/squalomail-woocommerce-admin-settings-5.2.css', array(), $this->version, 'all' );
			}	
			wp_enqueue_style( $this->plugin_name."-settings", plugin_dir_url( __FILE__ ) . 'css/squalomail-woocommerce-admin-settings.css', array(), $this->version, 'all' );
			wp_style_add_data( $this->plugin_name."-settings", 'rtl', 'replace' );	
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook) {
		if ( strpos($hook, 'page_squalomail-woocommerce') !== false ) {
			$label = $this->getOption('newsletter_label');
            if ($label == '') $label = __('Subscribe to our newsletter', 'squalomail-for-woocommerce');
			$options = get_option($this->plugin_name, array());
			$checkbox_default_settings = (array_key_exists('squalomail_checkbox_defaults', $options) && !is_null($options['squalomail_checkbox_defaults'])) ? $options['squalomail_checkbox_defaults'] : 'check';
			wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/squalomail-woocommerce-admin.js', array( 'jquery', 'swal' ), $this->version.'.21', false );
			wp_localize_script(
				$this->plugin_name,
				'phpVars',
				array( 
					'removeReviewBannerRestUrl' => SqualoMail_WooCommerce_Rest_Api::url('review-banner'),
					'l10n' => array(
						'are_you_sure' => __('Are you sure?', 'squalomail-for-woocommerce'),
						'log_delete_subtitle' => __('You will not be able to revert.', 'squalomail-for-woocommerce'),
						'log_delete_confirm' => __('Yes, delete it!', 'squalomail-for-woocommerce'),
						'no_cancel' => __('No, cancel!', 'squalomail-for-woocommerce'),
						'please_wait' => __('Please wait', 'squalomail-for-woocommerce'),
						'store_disconnect_subtitle' => __('You are about to disconnect your store from Squalomail.', 'squalomail-for-woocommerce'),
						'store_disconnect_confirm' => __('Yes, disconnect.', 'squalomail-for-woocommerce'),
						'try_again' => __('Try again', 'squalomail-for-woocommerce'),
						'resync_in_progress' => __('Resync request in progress', 'squalomail-for-woocommerce'),
						'resync_failed' => __('Could not resync orders, please try again.', 'squalomail-for-woocommerce'),
						'store_disconnect_in_progress' => __('Disconnecting store in progress', 'squalomail-for-woocommerce'),
						'login_popup_blocked' => __('Login Popup is blocked!', 'squalomail-for-woocommerce'),
						'login_popup_blocked_desc' => __('Please allow your browser to show popups for this page', 'squalomail-for-woocommerce'),
						'support_message_sending' => __('Sending support request', 'squalomail-for-woocommerce'),
						'support_message_ok' => __('Message received', 'squalomail-for-woocommerce'),
						'support_message_desc' => __('Thanks, your message has been received.', 'squalomail-for-woocommerce'),
						'subscribe_newsletter' => $label
					),
					'current_optin_state' => $checkbox_default_settings,
				)
			);
			wp_enqueue_script( $this->plugin_name);
			wp_enqueue_script('swal', "//cdn.jsdelivr.net/npm/sweetalert2@8", '', $this->version, false);
		}
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {
		// Add woocommerce menu subitem
		add_submenu_page( 
			'woocommerce', 
			__( 'Squalomail for WooCommerce', 'squalomail-for-woocommerce'), 
			__( 'Squalomail', 'squalomail-for-woocommerce' ),
			squalomail_get_allowed_capability(),
			$this->plugin_name,
			array($this, 'display_plugin_setup_page')
		);
	}

	/**
	 * Include the new Navigation Bar the Admin page.
	 */
	public function add_woocommerce_navigation_bar() {
		if ( function_exists( 'wc_admin_connect_page' ) ) {
			wc_admin_connect_page(
				array(
					'id'        => $this->plugin_name,
					'screen_id' => 'woocommerce_page_squalomail-woocommerce',
					'title'     => __( 'Squalomail for WooCommerce', 'squalomail-for-woocommerce' ),
				)
			);
		}
	}

	/**
	 * check if current user can view options pages/ save plugin options
	 */
	public function squalomail_woocommerce_option_page_capability() {
		return squalomail_get_allowed_capability();
	}

	/**
	 * Setup Feedback Survey Form
	 *
	 * @since    2.1.15
	 */
	public function setup_survey_form() {
		if (is_admin()) {
            try {
                new Squalomail_Woocommerce_Deactivation_Survey($this->plugin_name, 'squalomail-for-woocommerce');
            } catch (\Throwable $e) {
                squalomail_error('admin@setup_survey_form', $e->getCode() . ' :: ' . $e->getMessage() . ' on ' . $e->getLine() . ' in ' . $e->getFile());
                return false;
            }
        }
	}

    /**
     * @return string
     */
    protected function squalomail_svg()
    {
        return base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52.03 55"><defs><style>.cls-1{fill:#fff;}</style></defs><title>Asset 1</title><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M11.64,28.54a4.75,4.75,0,0,0-1.17.08c-2.79.56-4.36,2.94-4.05,6a6.24,6.24,0,0,0,5.72,5.21,4.17,4.17,0,0,0,.8-.06c2.83-.48,3.57-3.55,3.1-6.57C15.51,29.83,13.21,28.63,11.64,28.54Zm2.77,8.07a1.17,1.17,0,0,1-1.1.55,1.53,1.53,0,0,1-1.37-1.58A4,4,0,0,1,12.23,34a1.44,1.44,0,0,0-.55-1.74,1.48,1.48,0,0,0-1.12-.21,1.44,1.44,0,0,0-.92.64,3.39,3.39,0,0,0-.34.79l0,.11c-.13.34-.33.45-.47.43s-.16-.05-.21-.21a3,3,0,0,1,.78-2.55,2.46,2.46,0,0,1,2.11-.76,2.5,2.5,0,0,1,1.91,1.39,3.19,3.19,0,0,1-.23,2.82l-.09.2A1.16,1.16,0,0,0,13,36a.74.74,0,0,0,.63.32,1.38,1.38,0,0,0,.34,0c.15,0,.3-.07.39,0A.24.24,0,0,1,14.41,36.61Z"/><path class="cls-1" d="M51,33.88a3.84,3.84,0,0,0-1.15-1l-.11-.37-.14-.42a5.57,5.57,0,0,0,.5-3.32,5.43,5.43,0,0,0-1.54-3,10.09,10.09,0,0,0-4.24-2.26c0-.67,0-1.43-.06-1.9a12.83,12.83,0,0,0-.49-3.25,10.46,10.46,0,0,0-1.3-2.92c2.14-2.56,3.29-5.21,3.29-7.57,0-3.83-3-6.3-7.59-6.3a19.3,19.3,0,0,0-7.22,1.6l-.34.14L28.7,1.52A6.31,6.31,0,0,0,24.43,0,14.07,14.07,0,0,0,17.6,2.2a36.93,36.93,0,0,0-6.78,5.21c-4.6,4.38-8.3,9.63-9.91,14A12.51,12.51,0,0,0,0,26.54a6.16,6.16,0,0,0,2.13,4.4l.78.66A10.44,10.44,0,0,0,2.74,35a9.36,9.36,0,0,0,3.21,6,10,10,0,0,0,5.13,2.43,20.19,20.19,0,0,0,7.31,8A23.33,23.33,0,0,0,30.17,55H31a23.27,23.27,0,0,0,12-3.16,19.1,19.1,0,0,0,7.82-9.06l0,0A16.89,16.89,0,0,0,52,37.23,5.17,5.17,0,0,0,51,33.88Zm-1.78,8.21c-3,7.29-10.3,11.35-19,11.09-8.06-.24-14.94-4.5-18-11.43a7.94,7.94,0,0,1-5.12-2.06,7.56,7.56,0,0,1-2.61-4.85A8.31,8.31,0,0,1,5,31L3.32,29.56C-4.42,23,19.77-3.86,27.51,2.89l2.64,2.58,1.44-.61c6.79-2.81,12.3-1.45,12.3,3,0,2.33-1.48,5.05-3.86,7.52a7.54,7.54,0,0,1,2,3.48,11,11,0,0,1,.42,2.82c0,1,.09,3.16.09,3.2l1,.27A8.64,8.64,0,0,1,47.2,27a3.66,3.66,0,0,1,1.06,2.06A4,4,0,0,1,47.55,32,10.15,10.15,0,0,1,48,33.08c.2.64.35,1.18.37,1.25.74,0,1.89.85,1.89,2.89A15.29,15.29,0,0,1,49.18,42.09Z"/><path class="cls-1" d="M48,36a1.36,1.36,0,0,0-.86-.16,11.76,11.76,0,0,0-.82-2.78A17.89,17.89,0,0,1,40.45,36a23.64,23.64,0,0,1-7.81.84c-1.69-.14-2.81-.63-3.23.74a18.3,18.3,0,0,0,8,.81.14.14,0,0,1,.16.13.15.15,0,0,1-.09.15s-3.14,1.46-8.14-.08a2.58,2.58,0,0,0,1.83,1.91,8.24,8.24,0,0,0,1.44.39c6.19,1.06,12-2.47,13.27-3.36.1-.07.16,0,.08.12l-.13.18c-1.59,2.06-5.88,4.44-11.45,4.44-2.43,0-4.86-.86-5.75-2.17-1.38-2-.07-5,2.24-4.71l1,.11a21.13,21.13,0,0,0,10.5-1.68c3.15-1.46,4.34-3.07,4.16-4.37A1.87,1.87,0,0,0,46,28.34a6.8,6.8,0,0,0-3-1.41c-.5-.14-.84-.23-1.2-.35-.65-.21-1-.39-1-1.61,0-.53-.12-2.4-.16-3.16-.06-1.35-.22-3.19-1.36-4a1.92,1.92,0,0,0-1-.31,1.86,1.86,0,0,0-.58.06,3.07,3.07,0,0,0-1.52.86,5.24,5.24,0,0,1-4,1.32c-.8,0-1.65-.16-2.62-.22l-.57,0a5.22,5.22,0,0,0-5,4.57c-.56,3.83,2.22,5.81,3,7a1,1,0,0,1,.22.52.83.83,0,0,1-.28.55h0a9.8,9.8,0,0,0-2.16,9.2,7.59,7.59,0,0,0,.41,1.12c2,4.73,8.3,6.93,14.43,4.93a15.06,15.06,0,0,0,2.33-1,12.23,12.23,0,0,0,3.57-2.67,10.61,10.61,0,0,0,3-5.82C48.6,36.7,48.33,36.23,48,36Zm-8.25-7.82c0,.5-.31.91-.68.9s-.66-.42-.65-.92.31-.91.68-.9S39.72,27.68,39.71,28.18Zm-1.68-6c.71-.12,1.06.62,1.32,1.85a3.64,3.64,0,0,1-.05,2,4.14,4.14,0,0,0-1.06,0,4.13,4.13,0,0,1-.68-1.64C37.29,23.23,37.31,22.34,38,22.23Zm-2.4,6.57a.82.82,0,0,1,1.11-.19c.45.22.69.67.53,1a.82.82,0,0,1-1.11.19C35.7,29.58,35.47,29.13,35.63,28.8Zm-2.8-.37c-.07.11-.23.09-.57.06a4.24,4.24,0,0,0-2.14.22,2,2,0,0,1-.49.14.16.16,0,0,1-.11,0,.15.15,0,0,1-.05-.12.81.81,0,0,1,.32-.51,2.41,2.41,0,0,1,1.27-.53,1.94,1.94,0,0,1,1.75.57A.19.19,0,0,1,32.83,28.43Zm-5.11-1.26c-.12,0-.17-.07-.19-.14s.28-.56.62-.81a3.6,3.6,0,0,1,3.51-.42A3,3,0,0,1,33,26.87c.12.2.15.35.07.44s-.44,0-.95-.24a4.18,4.18,0,0,0-2-.43A21.85,21.85,0,0,0,27.71,27.17Z"/><path class="cls-1" d="M35.5,13.29c.1,0,.16-.15.07-.2a11,11,0,0,0-4.69-1.23.09.09,0,0,1-.07-.14,4.78,4.78,0,0,1,.88-.89.09.09,0,0,0-.06-.16,12.46,12.46,0,0,0-5.61,2,.09.09,0,0,1-.13-.09,6.16,6.16,0,0,1,.59-1.45.08.08,0,0,0-.11-.11A22.79,22.79,0,0,0,20,16.24a.09.09,0,0,0,.12.13A19.53,19.53,0,0,1,27,13.32,19.1,19.1,0,0,1,35.5,13.29Z"/><path class="cls-1" d="M28.34,6.42S26.23,4,25.6,3.8C21.69,2.74,13.24,8.57,7.84,16.27,5.66,19.39,2.53,24.9,4,27.74a11.43,11.43,0,0,0,1.79,1.72A6.65,6.65,0,0,1,10,26.78,34.21,34.21,0,0,1,20.8,11.62,55.09,55.09,0,0,1,28.34,6.42Z"/></g></g></svg>');
    }

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links($links) {
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __('Settings') . '</a>',
		);
		return array_merge($settings_link, $links);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_setup_page() {
		include_once( 'partials/squalomail-woocommerce-admin-tabs.php' );
	}

	/**
	 *
	 */
	public function options_update() {
		global $pagenow;

		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));

		// tammullen found this.
        if ($pagenow == 'admin.php' && isset($_GET) && isset($_GET['page']) && 'squalomail-woocommerce' === $_GET['page']) {
            $this->handle_abandoned_cart_table();
            $this->update_db_check();
			$active_tab = isset($_GET['tab']) ? $_GET['tab'] : ($this->getOption('active_tab') ? $this->getOption('active_tab') : 'api_key');
			if ($active_tab == 'sync' && get_option('squalomail-woocommerce-sync.initial_sync') == 1 && get_option('squalomail-woocommerce-sync.completed_at') > 0 ) {
                $this->squalomail_show_initial_sync_message();
            }
			if (isset($_GET['log_removed']) && $_GET['log_removed'] == "1") {
				add_settings_error('squalomail_log_settings', '', __('Log file deleted.', 'squalomail-for-woocommerce'), 'info');
			}
        }
	}


	/**
	 * Displays notice when plugin is installed but not yet configured / connected to Squalomail.
	 */
	public function initial_notice() {
		if (!squalomail_is_configured()) {
            $class = 'notice notice-warning is-dismissible';
            $message = sprintf(
            /* translators: Placeholders %1$s - opening strong HTML tag, %2$s - closing strong HTML tag, %3$s - opening link HTML tag, %4$s - closing link HTML tag */
                esc_html__(
                    '%1$sSqualomail for Woocommerce%2$s is not yet connected to a Squalomail account. To complete the connection, %3$svisit the plugin settings page%4$s.',
                    'squalomail-for-woocommerce'
                ),
                '<strong>',
                '</strong>',
                '<a href="' . admin_url( 'admin.php?page=') . $this->plugin_name . '">',
                '</a>'
            );
            printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
        }
	}

	/**
	 * Depending on the version we're on we may need to run some sort of migrations.
	 */
	public function update_db_check() {
		// grab the current version set in the plugin variables
		global $wpdb;
		global $pagenow;

		$version = squalomail_environment_variables()->version;

		// grab the saved version or default to 1.0.3 since that's when we first did this.
		$saved_version = get_site_option('squalomail_woocommerce_version', '1.0.3');

		// if the saved version is less than the current version
		if (version_compare($version, $saved_version) > 0) {
			// resave the site option so this only fires once.
			update_site_option('squalomail_woocommerce_version', $version);

			// get plugin options
			$options = $this->getOptions();
			
			// set permission_cap in case there's none set.
			if (!isset($options['squalomail_permission_cap']) || empty($options['squalomail_permission_cap']) ) {
				$options['squalomail_permission_cap'] = 'manage_options';
				update_option($this->plugin_name, $options);
			}

			// resend marketing status to update latest changes
			if (!empty($options['admin_email'])) {
				try {
					// send the post to the squalomail server
					$comm_opt = get_option('squalomail-woocommerce-comm.opt', 0);
					$this->squalomail_set_communications_status_on_server($comm_opt, $options['admin_email']);
				} catch (\Exception $e) {
					squalomail_error("marketing_status_update", $e->getMessage());
				}
			}
		}

		if (!get_option( $this->plugin_name.'_cart_table_add_index_update')) {
			$check_index_sql = "SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema='{$wpdb->dbname}' AND table_name='{$wpdb->prefix}squalomail_carts' AND index_name='primary' and column_name='email';";
			$index_exists = $wpdb->get_var($check_index_sql);
			if ($index_exists == '1') {
				update_option( $this->plugin_name.'_cart_table_add_index_update', true);
			}
			else {
				//remove table duplicates
				$delete_sql = "DELETE carts_1 FROM {$wpdb->prefix}squalomail_carts carts_1 INNER JOIN {$wpdb->prefix}squalomail_carts carts_2 WHERE carts_1.created_at < carts_2.created_at AND carts_1.email = carts_2.email;";
				if ($wpdb->query($delete_sql) !== false) {
					$sql = "ALTER TABLE {$wpdb->prefix}squalomail_carts ADD PRIMARY KEY (email);";
					// only update the option if the query returned sucessfully
					try {
                        if ($wpdb->query($sql) !== false) {
                            update_option( $this->plugin_name.'_cart_table_add_index_update', true);
                        }
                    } catch (\Exception $e) {
                        update_option( $this->plugin_name.'_cart_table_add_index_update', true);
                    }
				}
			}
		}
		
		if (!get_option( $this->plugin_name.'_woo_currency_update')) {
			if ($this->squalomail_update_woo_settings()) {
				update_option( $this->plugin_name.'_woo_currency_update', true);
			} 
		}
		
		if($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}squalomail_jobs';") != $wpdb->prefix.'squalomail_jobs') {
			SqualoMail_WooCommerce_Activator::create_queue_tables();
			SqualoMail_WooCommerce_Activator::migrate_jobs();
		}

		if (defined( 'DISABLE_WP_HTTP_WORKER' ) || defined( 'SQUALOMAIL_USE_CURL' ) || defined( 'SQUALOMAIL_REST_LOCALHOST' ) || defined( 'SQUALOMAIL_REST_IP' ) || defined( 'SQUALOMAIL_DISABLE_QUEUE') && true === SQUALOMAIL_DISABLE_QUEUE) {
			$constants_used = array();
			
			if (defined( 'DISABLE_WP_HTTP_WORKER')) {
				$constants_used[] = 'DISABLE_WP_HTTP_WORKER';
			}

			if (defined( 'SQUALOMAIL_DISABLE_QUEUE')) {
				$constants_used[] = 'SQUALOMAIL_DISABLE_QUEUE';
			}

			if (defined( 'SQUALOMAIL_USE_CURL')) {
				$constants_used[] = 'SQUALOMAIL_USE_CURL';
			}

			if (defined( 'SQUALOMAIL_REST_LOCALHOST')) {
				$constants_used[] = 'SQUALOMAIL_REST_LOCALHOST';
			}

			if (defined( 'SQUALOMAIL_REST_IP')) {
				$constants_used[] = 'SQUALOMAIL_REST_IP';
			}
			
			$text = __('Squalomail for Woocommerce','squalomail-for-woocommerce').'<br/>'.
			'<p id="http-worker-deprecated-message">'.__('We dectected that this site has the following constants defined, likely at wp-config.php file' ,'squalomail-for-woocommerce').': '.
			implode(' | ', $constants_used).'<br/>'.
			__('These constants are deprecated since Squalomail for Woocommerce version 2.3. Please refer to the <a href="https://github.com/squalomail/sqm-woocommerce/wiki/">plugin official wiki</a> for further details.' ,'squalomail-for-woocommerce').'</p>';
			
			// only print notice for deprecated constants, on squalomail woocoomerce pages
			if ($pagenow == 'admin.php' && 'squalomail-woocommerce' === $_GET['page']) {
				add_settings_error('squalomail-woocommerce_notice', $this->plugin_name, $text, 'info');
			}
		}
		
	}

	/**
	 * Sets the Store Currency code on plugin options
	 * 
	 * @param string $code
	 * @return array $options 
	 */
	private function squalomail_set_store_currency_code($code = null) {
		if (!isset($code)) {
			$code = get_woocommerce_currency();
		}
		$options = $this->getOptions();
		$options['woocommerce_settings_save_general'] = true;
		$options['store_currency_code'] = $code;
		update_option($this->plugin_name, $options);
		return $options;
	}

	/**
	 * Fired when woocommerce store settings are saved
	 * 
	 * @param string $code
	 * @return array $options 
	 */
	public function squalomail_update_woo_settings() {
		$new_currency_code = null;

		if (isset($_POST['woo_multi_currency_params'])) {
			$new_currency_code = $_POST['currency_default'];
		}
		else if (isset($_POST['woocommerce_currency'])) {
			$new_currency_code = $_POST['woocommerce_currency'];
		}
		
		$data = $this->squalomail_set_store_currency_code($new_currency_code);
		
		// sync the store with SQM
		try {
			$store_created = $this->syncStore($data);
		}
		catch (Exception $e){
			squalomail_log('store.sync@woo.update', 'Store cannot be synced', $e->getMessage());
			return false;
		}
		
		return $store_created;
	}

    /**
     * We were considering auto subscribing people that had just updated the plugin for the first time
     * after releasing the marketing status block, but decided against that. The admin user must subscribe specifically.
     *
     * @return array|WP_Error|null
     */
	protected function automatically_subscribe_admin_to_marketing()
    {
        $site_option = 'squalomail_woocommerce_updated_marketing_status';

        // if we've already done this, just return null.
        if (get_site_option($site_option, false)) {
            return null;
        }

        // if we've already set this value to something other than NULL, that means they've already done this.
        if (($original_opt = $this->getData('comm.opt',null)) !== null) {
            return null;
        }

        // if they have not set the admin_email yet during plugin setup, we will just return null
        $admin_email = $this->getOption('admin_email');

        if (empty($admin_email)) {
            return null;
        }

        // tell the site options that we've already subscribed this person to marketing through the
        // plugin update process.
        update_site_option($site_option, true);

        try {
            // send the post to the squalomail server
            return $this->squalomail_set_communications_status_on_server(true, $admin_email);
        } catch (\Exception $e) {
            squalomail_error("initial_marketing_status", $e->getMessage());
            return null;
        }
    }
	
	/**
	 * We need to do a tidy up function on the squalomail_carts table to
	 * remove anything older than 30 days.
	 *
	 * Also if we don't have the configuration set, we need to create the table.
	 */
	protected function handle_abandoned_cart_table()
	{
		global $wpdb;

		if (get_site_option('squalomail_woocommerce_db_squalomail_carts', false)) {
			// need to tidy up the squalomail_cart table and make sure we don't have anything older than 30 days old.
			$date = gmdate( 'Y-m-d H:i:s', strtotime(date ("Y-m-d") ."-30 days"));
			$sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}squalomail_carts WHERE created_at <= %s", $date);
			$wpdb->query($sql);
		} else {

			// create the table for the first time now.
			$charset_collate = $wpdb->get_charset_collate();
			$table = "{$wpdb->prefix}squalomail_carts";

			$sql = "CREATE TABLE IF NOT EXISTS $table (
				id VARCHAR (255) NOT NULL,
				email VARCHAR (100) NOT NULL,
				user_id INT (11) DEFAULT NULL,
                cart text NOT NULL,
                created_at datetime NOT NULL,
				PRIMARY KEY  (email)
				) $charset_collate;";

			if (($result = $wpdb->query($sql)) > 0) {
				update_site_option('squalomail_woocommerce_db_squalomail_carts', true);
			}
		}
	}

	/**
	 * @param $input
	 * @return array
	 */
	public function validate($input) {

		$active_tab = isset($input['squalomail_active_tab']) ? $input['squalomail_active_tab'] : null;

		if (empty($active_tab) && isset($input['woocommerce_settings_save_general']) && $input['woocommerce_settings_save_general']) {
			unset($input['woocommerce_settings_save_general']);
			$data['store_currency_code'] = (string) $input['store_currency_code'];
		}

		if (get_site_transient('squalomail_disconnecting_store')) {
			delete_site_transient('squalomail_disconnecting_store');
			return array(
                'active_tab' => 'api_key',
                'squalomail_api_key' => null,
                'squalomail_list' => null,
            );
        }

		switch ($active_tab) {

			case 'api_key':
				$data = $this->validatePostApiKey($input);
				break;

			case 'store_info':
				$data = $this->validatePostStoreInfo($input);
				break;

			case 'newsletter_settings':
				$data = $this->validatePostNewsletterSettings($input);
				break;

			case 'sync':
				//case sync
				if ($this->is_resyncing()) {

					// remove all the pointers to be sure
					$service = new SqualoMail_Service();
					$service->removePointers(true, true);
					$this->startSync();
					$this->showSyncStartedMessage();
					$this->setData('sync.config.resync', true);
				}
				break;

            case 'logs':

                if (isset($_POST['log_file']) && !empty($_POST['log_file'])) {
                    set_site_transient('squalomail-woocommerce-view-log-file', $_POST['log_file'], 30);
                }
                
                $data = array(
                    'squalomail_logging' => isset($input['squalomail_logging']) ? $input['squalomail_logging'] : 'none',
                );

                if (isset($_POST['sqm_action']) && in_array($_POST['sqm_action'], array('view_log', 'remove_log'))) {
                    $path = 'admin.php?page=squalomail-woocommerce&tab=logs';
                    wp_redirect($path);
                    exit();
                }

				break;
			case 'plugin_settings':

				// case disconnect
				if ($this->is_disconnecting()) {
					// Disconnect store!
					if ($this->disconnect_store()) {
					    return array(
                            'active_tab' => 'api_key',
                            'squalomail_api_key' => null,
                            'squalomail_list' => null,
                        );
						add_settings_error('squalomail_store_settings', '', __('Store Disconnected', 'squalomail-for-woocommerce'), 'info');
					} else {
						$data['active_tab'] = 'plugin_settings';
						add_settings_error('squalomail_store_settings', '', __('Store Disconnect Failed', 'squalomail-for-woocommerce'), 'warning');
					}	
				}
				break;
		}

		// if no API is provided, check if the one saved on the database is still valid, ** only not if disconnect store is issued **.
		if (!$this->is_disconnecting() && !isset($input['squalomail_api_key']) && $this->getOption('squalomail_api_key')) {
			// set api key for validation
			$input['squalomail_api_key'] = $this->getOption('squalomail_api_key');
			$api_key_valid = $this->validatePostApiKey($input);
			
			// if there's no error, remove the api_ping_error from the db
			if (!$api_key_valid['api_ping_error'])
				$data['api_ping_error'] = $api_key_valid['api_ping_error'];
		}

		return (isset($data) && is_array($data)) ? array_merge($this->getOptions(), $data) : $this->getOptions();
	}

	/**
	 * STEP 1.
	 *
	 * Handle the 'api_key' tab post.
	 *
	 * @param $input
	 * @return array
	 */
	protected function validatePostApiKey($input)
	{
		$data = array(
			'squalomail_api_key' => isset($input['squalomail_api_key']) ? trim($input['squalomail_api_key']) : false,
			'squalomail_debugging' => isset($input['squalomail_debugging']) ? $input['squalomail_debugging'] : false,
			'squalomail_account_info_id' => null,
			'squalomail_account_info_username' => null,
		);

		$api = new SqualoMail_WooCommerce_SqualoMailApi($data['squalomail_api_key']);

		try {
		    $profile = $api->ping(true, true);
            // tell our reporting system whether or not we had a valid ping.
            $this->setData('validation.api.ping', true);
            $data['active_tab'] = 'store_info';
            if (isset($profile) && is_array($profile) && array_key_exists('account_id', $profile)) {
                $data['squalomail_account_info_id'] = $profile['account_id'];
                $data['squalomail_account_info_username'] = $profile['username'];
            }
            $data['api_ping_error'] = false;
        } catch (Exception $e) {
            unset($data['squalomail_api_key']);
            $data['active_tab'] = 'api_key';
            $data['api_ping_error'] = $e->getCode().' :: '.$e->getMessage().' on '.$e->getLine().' in '.$e->getFile();
            squalomail_error('admin@validatePostApiKey', $e->getCode().' :: '.$e->getMessage().' on '.$e->getLine().' in '.$e->getFile());
            add_settings_error('squalomail_store_settings', $e->getCode(), $e->getMessage());
            return $data;
        }

		return $data;
	}

	/**
     * Squalomail OAuth connection start
     */
    public function squalomail_woocommerce_ajax_oauth_start()
    {   
		$secret = uniqid();
        $args = array(
            'domain' => site_url(),
            'secret' => $secret,
        );

        $pload = array(
            'headers' => array( 
                'Content-type' => 'application/json',
            ),
            'body' => json_encode($args)
        );

        $response = wp_remote_post( 'https://woocommerce.squalomailapp.com/api/start', $pload);
        if ($response['response']['code'] == 201 ){
			set_site_transient('squalomail-woocommerce-oauth-secret', $secret, 60*60);
			wp_send_json_success($response);
        }
        else wp_send_json_error( $response );
        
	}
	
	/**
     * Squalomail OAuth connection status
     */
    public function squalomail_woocommerce_ajax_oauth_status()
    {   
		$url = $_POST['url'];
		// set the default headers to NOTHING because the oauth server will block
		// any non standard header that it was not expecting to receive and it was
		// preventing folks from being able to connect.
        $pload = array(
            'headers' => array(),
        );

		$response = wp_remote_post($url, $pload);
		
        if ($response['response']['code'] == 200 && isset($response['body'])){
			wp_send_json_success(json_decode($response['body']));
        }
        else wp_send_json_error( $response );
    }

	/**
     * Squalomail OAuth connection finish
     */
    public function squalomail_woocommerce_ajax_oauth_finish()
    {  
		$token = $_POST['token'];
		$api = new SqualoMail_WooCommerce_SqualoMailApi($token);
		try {
			$result = $api->ping(false, true);
			wp_send_json_success([]);
		} catch (SqualoMail_WooCommerce_Error $e) {
			wp_send_json_error(['error' => $e->getMessage()]);
		}
    }

	public function squalomail_woocommerce_ajax_create_account_check_username () {
		$user = $_POST['username'];
		$response = wp_remote_get( 'https://woocommerce.squalomailapp.com/api/usernames/available/' . $_POST['username']);
		$response_body = json_decode($response['body']);
		if ($response['response']['code'] == 200 && $response_body->success == true ){
			wp_send_json_success($response);
		}
		
		else if ($response['response']['code'] == 404 ){
			wp_send_json_error(array(
				'success' => false,
			));
		}

        else {
			$suggestion = wp_remote_get( 'https://woocommerce.squalomailapp.com/api/usernames/suggestions/' . preg_replace('/[^A-Za-z0-9\-\@\.]/', '', $_POST['username']));
			$suggested_username = json_decode($suggestion['body'])->data;
			wp_send_json_error( array(
				'success' => false,
				'suggestion' => $suggested_username[0]
			));
		}
	}
	
	public function squalomail_woocommerce_ajax_support_form() {
		$data = $_POST['data'];
		
		// try to figure out user IP address
		if ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ) {
			$data['ip_address'] = '127.0.0.1';
		}
		else {
			$data['ip_address'] = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
		}

		$pload = array(
            'headers' => array( 
                'Content-type' => 'application/json',
            ),
			'body' => json_encode($data),
			'timeout'     => 30,
        );

		$response = wp_remote_post( 'https://woocommerce.squalomailapp.com/api/support', $pload);
		$response_body = json_decode($response['body']);
		if ($response['response']['code'] == 200 && $response_body->success == true ) {
			wp_send_json_success($response_body);
		} else if ($response['response']['code'] == 404 ) {
			wp_send_json_error(array('success' => false, 'error' => $response));
		}
	}

    /**
     * @return mixed|null
     */
	public function squalomail_send_sync_finished_email() {
        try {
            $order_count = squalomail_get_api()->getOrderCount(squalomail_get_store_id());
            $list_name = $this->getListName();
        } catch (\Exception $e) {
            $list_name = squalomail_get_list_id();
            $order_count = squalomail_get_order_count();
        }

        $admin_email = $this->getOption('admin_email');

        if (empty($admin_email)) {
            return null;
        }

        $pload = array(
            'headers' => array(
                'Content-type' => 'application/json',
            ),
            'body' => json_encode(array(
                'sync_finished' => true,
                'audience_name' => $list_name,
                'total_orders' => $order_count,
                'store_name' => get_option('blogname'),
                'email' => $admin_email,
            )),
            'timeout'     => 30,
        );
        $response = wp_remote_post( 'https://woocommerce.squalomailapp.com/api/support', $pload);
        $response_body = json_decode($response['body']);
        return $response_body;
    }

	public function squalomail_woocommerce_ajax_create_account_signup() {
		$data = $_POST['data'];
		
		// try to figure out user IP address
		if ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ) {
			$data['ip_address'] = '127.0.0.1';
		}
		else {
			$data['ip_address'] = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']);
		}

		$pload = array(
            'headers' => array( 
                'Content-type' => 'application/json',
            ),
			'body' => json_encode($data),
			'timeout'     => 30,
        );

		$response = wp_remote_post( 'https://woocommerce.squalomailapp.com/api/signup/', $pload);
		$response_body = json_decode($response['body']);
		if ($response['response']['code'] == 200 && $response_body->success == true) {
			wp_send_json_success($response_body);
		} else if ($response['response']['code'] == 404 ) {
			wp_send_json_error(array('success' => false));
		} else {
			$suggestion = wp_remote_get( 'https://woocommerce.squalomailapp.com/api/usernames/suggestions/' . $_POST['username']);
			$suggested_username = json_decode($suggestion['body'])->data;
			wp_send_json_error( array(
				'success' => false,
				'suggestion' => $suggested_username[0]
			));
		}
	}

	/**
	 * STEP 2.
	 *
	 * Handle the 'store_info' tab post.
	 *
	 * @param $input
	 * @return array
	 */
	protected function validatePostStoreInfo($input)
	{
		$data = $this->compileStoreInfoData($input);

		if (!$this->hasValidStoreInfo($data)) {

		    if ($this->hasInvalidStoreAddress($data)) {
		        $this->addInvalidAddressAlert();
            }

            if ($this->hasInvalidStorePhone($data)) {
		        $this->addInvalidPhoneAlert();
            }

            if ($this->hasInvalidStoreName($data)) {
		        $this->addInvalidStoreNameAlert();
            }

			$this->setData('validation.store_info', false);

            $data['active_tab'] = 'store_info';

			return $input;
		}

		// change communication status options
		$comm_opt = get_option('squalomail-woocommerce-comm.opt', 0);
		$this->squalomail_set_communications_status_on_server($comm_opt, $data['admin_email']);

		$this->setData('validation.store_info', true);

		if ($this->hasValidSqualoMailList()) {
			// sync the store with SQM
			try {
				$this->syncStore(array_merge($this->getOptions(), $data));
			}
			catch (Exception $e){
				$this->setData('validation.store_info', false);
				squalomail_log('errors.store_info', 'Store cannot be synced :: ' . $e->getMessage());
				add_settings_error('squalomail_store_info', '', __('Cannot create or update Store at Squalomail.', 'squalomail-for-woocommerce') . ' Squalomail says: ' . $e->getMessage());
				return $data;
			}
		}
		
		$data['active_tab'] = 'newsletter_settings';
		$data['store_currency_code'] = get_woocommerce_currency();
		
		return $data;
	}

    /**
     * @param $input
     * @return array
     */
	protected function compileStoreInfoData($input)
    {	
		$checkbox = $this->getOption('squalomail_permission_cap', 'check');

		// see if it's posted in the form.
		if (isset($input['squalomail_permission_cap']) && !empty($input['squalomail_permission_cap'])) {
			$checkbox = $input['squalomail_permission_cap'];
		}
        return array(
            // store basics
            'store_name' => trim((isset($input['store_name']) ? $input['store_name'] : get_option('blogname'))),
            'store_street' => isset($input['store_street']) ? $input['store_street'] : false,
            'store_city' => isset($input['store_city']) ? $input['store_city'] : false,
            'store_state' => isset($input['store_state']) ? $input['store_state'] : false,
            'store_postal_code' => isset($input['store_postal_code']) ? $input['store_postal_code'] : false,
            'store_country' => isset($input['store_country']) ? $input['store_country'] : false,
            'store_phone' => isset($input['store_phone']) ? $input['store_phone'] : false,
            // locale info
            'store_locale' => isset($input['store_locale']) ? $input['store_locale'] : false,
			'store_timezone' => squalomail_get_timezone(),
            'admin_email' => isset($input['admin_email']) && is_email($input['admin_email']) ? $input['admin_email'] : $this->getOption('admin_email', false),
			'squalomail_permission_cap' => $checkbox,
        );
    }

    /**
     * @param array $data
     * @return array|bool
     */
	protected function hasInvalidStoreAddress($data)
    {
        $address_keys = array(
            'admin_email',
            'store_city',
            'store_state',
            'store_postal_code',
            'store_country',
            'store_street'
        );

        $invalid = array();
        foreach ($address_keys as $address_key) {
            if (empty($data[$address_key])) {
                $invalid[] = $address_key;
            }
        }
        return empty($invalid) ? false : $invalid;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function hasInvalidStorePhone($data)
    {
        if (empty($data['store_phone']) || strlen($data['store_phone']) <= 6) {
            return true;
        }

        return false;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function hasInvalidStoreName($data)
    {
        if (empty($data['store_name'])) {
            return true;
        }
        return false;
    }

    /**
     *
     */
	protected function addInvalidAddressAlert()
    {
        add_settings_error('squalomail_store_settings', '', __('As part of the Squalomail Terms of Use, we require a contact email and a physical mailing address.', 'squalomail-for-woocommerce'));
    }

    /**
     *
     */
    protected function addInvalidPhoneAlert()
    {
        add_settings_error('squalomail_store_settings', '', __('As part of the Squalomail Terms of Use, we require a valid phone number for your store.', 'squalomail-for-woocommerce'));
    }

    /**
     *
     */
    protected function addInvalidStoreNameAlert()
    {
        add_settings_error('squalomail_store_settings', '', __('Squalomail for WooCommerce requires a Store Name to connect your store.', 'squalomail-for-woocommerce'));
    }

	/**
	 * STEP 3.
	 *
	 * Handle the 'newsletter_settings' tab post.
	 *
	 * @param $input
	 * @return array
	 */
	protected function validatePostNewsletterSettings($input)
	{
		// default value.
		$checkbox = $this->getOption('squalomail_checkbox_defaults', 'check');

		// see if it's posted in the form.
		if (isset($input['squalomail_checkbox_defaults']) && !empty($input['squalomail_checkbox_defaults'])) {
			$checkbox = $input['squalomail_checkbox_defaults'];
		}
		$sanitized_tags = array_map("sanitize_text_field", explode(",", $input['squalomail_user_tags']));

		$allowed_html = array(
			'a' => array(
				'href' => array(),
				'title' => array(),
				'target' => array()
			),
			'br' => array()
		);

		$data = array(
			'squalomail_list' => isset($input['squalomail_list']) ? $input['squalomail_list'] : $this->getOption('squalomail_list', ''),
			'newsletter_label' => (isset($input['newsletter_label'])) ? wp_kses($input['newsletter_label'], $allowed_html) : $this->getOption('newsletter_label', __('Subscribe to our newsletter', 'squalomail-for-woocommerce')),
			'squalomail_auto_subscribe' => isset($input['squalomail_auto_subscribe']) ? (bool) $input['squalomail_auto_subscribe'] : false,
			'squalomail_checkbox_defaults' => $checkbox,
			'squalomail_checkbox_action' => isset($input['squalomail_checkbox_action']) ? $input['squalomail_checkbox_action'] : $this->getOption('squalomail_checkbox_action', 'woocommerce_after_checkout_billing_form'),
			'squalomail_user_tags' => isset($input['squalomail_user_tags']) ? implode(",",$sanitized_tags) : $this->getOption('squalomail_user_tags'),
			'squalomail_product_image_key' => isset($input['squalomail_product_image_key']) ? $input['squalomail_product_image_key'] : 'medium',
			'campaign_from_name' => isset($input['campaign_from_name']) ? $input['campaign_from_name'] : false,
			'campaign_from_email' => isset($input['campaign_from_email']) && is_email($input['campaign_from_email']) ? $input['campaign_from_email'] : false,
			'campaign_subject' => isset($input['campaign_subject']) ? $input['campaign_subject'] : get_option('blogname'),
			'campaign_language' => isset($input['campaign_language']) ? $input['campaign_language'] : 'en',
			'campaign_permission_reminder' => isset($input['campaign_permission_reminder']) ? $input['campaign_permission_reminder'] : sprintf(/* translators: %s - plugin name. */esc_html__( 'You were subscribed to the newsletter from %s', 'squalomail-for-woocommerce' ),get_option('blogname')),
		);

		if (!$this->hasValidCampaignDefaults($data)) {
			$this->setData('validation.newsletter_settings', false);
			add_settings_error('squalomail_list_settings', '', __('One or more fields were not updated', 'squalomail-for-woocommerce'));
			return array('active_tab' => 'newsletter_settings');
		}
		$this->setData('validation.newsletter_settings', true);

		$list_id = squalomail_get_list_id();

		if (!empty($list_id)) {
			$this->updateSqualoMailList(array_merge($this->getOptions(), $data), $list_id);
		}
		
		//if we don't have any audience on the account, create one
		if ($data['squalomail_list'] === 'create_new') {
			$data['squalomail_list'] = $this->updateSqualoMailList(array_merge($this->getOptions(), $data));
		}

		// as long as we have a list set, and it's currently in SQM as a valid list, let's sync the store.
		if (!empty($data['squalomail_list']) && $this->api()->hasList($data['squalomail_list'])) {

            $this->setData('validation.newsletter_settings', true);

			// sync the store with SQM
			try {
				$store_created = $this->syncStore(array_merge($this->getOptions(), $data));
			}
			catch (Exception $e){
				$this->setData('validation.newsletter_settings', false);
				squalomail_log('errors.newsletter_settings', 'Store cannot be synced :: ' . $e->getMessage());
				add_settings_error('squalomail_newsletter_settings', '', __('Cannot create or update Store at Squalomail.', 'squalomail-for-woocommerce') . ' Squalomail says: ' . $e->getMessage());
				$data['active_tab'] = 'newsletter_settings';
				return $data;
			}

			// if there was already a store in Squalomail, use the list ID from Squalomail
			if ($this->swapped_list_id) {
				$data['squalomail_list'] = $this->swapped_list_id;
			}

			// start the sync automatically if the sync is false
			if ($store_created && ((bool) $this->getData('sync.started_at', false) === false)) {
				// tell the next page view to start the sync with a transient since the data isn't available yet
                set_site_transient('squalomail_woocommerce_start_sync', microtime(), 300);

                $this->showSyncStartedMessage();
			}
			
            $data['active_tab'] = 'sync';

            return $data;
		}

		$this->setData('validation.newsletter_settings', false);
		
		add_settings_error('squalomail_newsletter_settings', '', __('One or more fields were not updated', 'squalomail-for-woocommerce'));

        $data['active_tab'] = 'newsletter_settings';

        return $data;
	}



	/**
	 * @param null|array $data
	 * @return bool
	 */
	public function hasValidStoreInfo($data = null)
	{
		return $this->validateOptions(array(
			'store_name', 'store_street', 'store_city', 'store_state',
			'store_postal_code', 'store_country', 'store_phone',
			'store_locale',
			'store_phone','squalomail_permission_cap',
		), $data);
	}

	/**
	 * @param null|array $data
	 * @return bool
	 */
	public function hasValidCampaignDefaults($data = null)
	{
		return $this->validateOptions(array(
			'campaign_from_name', 'campaign_from_email', 'campaign_subject', 'campaign_language',
			'campaign_permission_reminder'
		), $data);
	}

    /**
     * @param null $data
     * @param bool $throw_if_not_valid
     * @return array|bool|mixed|null|object
     * @throws Exception
     */
	public function hasValidApiKey($data = null, $throw_if_not_valid = false)
	{
		if (!$this->validateOptions(array('squalomail_api_key'), $data)) {
			return false;
		}

		if (($pinged = $this->getCached('api-ping-check', null)) === null) {
            if (($pinged = $this->api()->ping(false, $throw_if_not_valid === true))) {
                $this->setCached('api-ping-check', true, 120);
            }
		}

		return $pinged;
	}

    /**
     * @return array|bool|mixed|null|object
     * @throws Exception
     * @throws SqualoMail_WooCommerce_Error
     * @throws SqualoMail_WooCommerce_ServerError
     */
	public function hasValidSqualoMailList()
	{
		if (!$this->hasValidApiKey()) {
			add_settings_error('squalomail_api_key', '', __('You must supply your Squalomail API key to pull the audiences.', 'squalomail-for-woocommerce'));
			return false;
		}

		if (!($this->validateOptions(array('squalomail_list')))) {
			return $this->api()->getLists(true);
		}

		return $this->api()->hasList($this->getOption('squalomail_list'));
	}


    /**
     * @return array|bool|mixed|null|object
     * @throws Exception
     */
	public function getAccountDetails()
	{
		if (!$this->hasValidApiKey()) {
			return false;
		}

		try {
			if (($account = $this->getCached('api-account-name', null)) === null) {
				if (($account = $this->api()->getProfile())) {
					$this->setCached('api-account-name', $account, 120);
				}
			}
			return $account;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @return array|bool
	 */
	public function getSqualoMailLists()
	{
		if (!$this->hasValidApiKey()) {
			return false;
		}

		try {
			if (($pinged = $this->getCached('api-lists', null)) === null) {
				$pinged = $this->api()->getLists(true);
				if ($pinged) {
					$this->setCached('api-lists', $pinged, 120);
				}
				return $pinged;
			}
			return $pinged;
		} catch (\Exception $e) {
			return array();
		}
	}

	/**
	 * @return array|bool
	 */
	public function getListName()
	{
		if (!$this->hasValidApiKey()) {
			return false;
		}

		if (!($list_id = $this->getOption('squalomail_list', false))) {
			return false;
		}

		try {
			if (($lists = $this->getCached('api-lists', null)) === null) {
				$lists = $this->api()->getLists(true);
				if ($lists) {
					$this->setCached('api-lists', $lists, 120);
				}
			}

			return array_key_exists($list_id, $lists) ? $lists[$list_id] : false;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function isReadyForSync()
	{
		if (!$this->hasValidApiKey()) {
			return false;
		}

		if (!$this->getOption('squalomail_list', false)) {
			return false;
		}

		if (!$this->api()->hasList($this->getOption('squalomail_list'))) {
			return false;
		}

		if (!$this->api()->getStore($this->getUniqueStoreID())) {
			return false;
		}

		return true;
	}

    public function inject_sync_ajax_call() { global $wp; ?>
        <script type="text/javascript" >
            jQuery(document).ready(function($) {
                var endpoint = '<?php echo SqualoMail_WooCommerce_Rest_Api::url('sync/stats'); ?>';
                var on_sync_tab = '<?php echo (squalomail_check_if_on_sync_tab() ? 'yes' : 'no')?>';
                var sync_status = '<?php echo ((squalomail_has_started_syncing() && !squalomail_is_done_syncing()) ? 'historical' : 'current') ?>';
				
				var promo_rulesProgress = 0;
				var orderProgress = 0;
				var productProgress = 0;
				var categoriesProgress = 0;

                if (on_sync_tab === 'yes') {
                    var call_squalomail_for_stats = function (showSpinner = false) {
						if (showSpinner ) jQuery('#squalomail_last_updated').next('.spinner').css('visibility', 'visible');
                        jQuery.get(endpoint, function(response) {
                            if (response.success) {
								
                                // if the response is now finished - but the original sync status was "historical"
                                // perform a page refresh because we need the re-sync buttons to show up again.
                                if (response.has_finished === true && sync_status === 'historical') {
                                	return document.location.reload(true);
                                }
								
								if (response.has_started && !response.has_finished) {
									jQuery('.sync-stats-audience .sync-loader').css('visibility', 'visible');
									jQuery('.sync-stats-audience .card_count').hide();
									
									jQuery('.sync-stats-store .card_count').hide();

									jQuery('.sync-stats-card .progress-bar-wrapper').show();
									
									if (response.promo_rules_page == 'complete') {
										promo_rulesProgress = 100;
										jQuery('#squalomail_promo_rules_count').html(response.promo_rules_in_squalomail.toLocaleString(undefined, {maximumFractionDigits: 0})).css('display', 'inline-block');
										jQuery('.sync-stats-card.promo_rules .progress-bar-wrapper').hide();
									} else {
										if (response.promo_rules_in_squalomail == 0) {
											promo_rulesProgress = 0;
											promo_rulesPartial = "0 / " + response.promo_rules_in_store;
										} else {
											promo_rulesProgress = response.promo_rules_in_squalomail / response.promo_rules_in_store * 100
											promo_rulesPartial = response.promo_rules_in_squalomail + " / " + response.promo_rules_in_store;
										}
										if (promo_rulesProgress > 100) promo_rulesProgress = 100;
										jQuery('.squalomail_promo_rules_count_partial').html(promo_rulesPartial);
									}
									jQuery('.sync-stats-card.promo_rules .progress-bar').width(promo_rulesProgress+"%");

									if (response.products_page == 'complete') {
										productsProgress = 100;
										jQuery('#squalomail_product_count').html(response.products_in_squalomail.toLocaleString(undefined, {maximumFractionDigits: 0})).css('display', 'inline-block');
										jQuery('.sync-stats-card.products .progress-bar-wrapper').hide();
									} else {
										if (response.products_in_squalomail == 0) {
											productsProgress = 0;
											productsPartial = "0 / " + response.products_in_store;
										} else {
											productsProgress = response.products_in_squalomail / response.products_in_store * 100
											productsPartial = response.products_in_squalomail + " / " + response.products_in_store;
										}
										if (productsProgress > 100) productsProgress = 100;
										jQuery('.squalomail_product_count_partial').html(productsPartial);
									}
									jQuery('.sync-stats-card.products .progress-bar').width(productsProgress+"%");

									if (response.categories_page == 'complete') {
										categoriesProgress = 100;
										jQuery('#squalomail_category_count').html(response.categories_in_squalomail.toLocaleString(undefined, {maximumFractionDigits: 0})).css('display', 'inline-block');
										jQuery('.sync-stats-card.categories .progress-bar-wrapper').hide();
									} else {
										if (response.categories_in_squalomail == 0) {
											categoriesProgress = 0;
											categoriesPartial = "0 / " + response.categories_in_store;
										} else {
											categoriesProgress = response.categories_in_squalomail / response.categories_in_store * 100
											categoriesPartial = response.categories_in_squalomail + " / " + response.categories_in_store;
										}
										if (categoriesProgress > 100) categoriesProgress = 100;
										jQuery('.squalomail_category_count_partial').html(categoriesPartial);
									}
									jQuery('.sync-stats-card.categories .progress-bar').width(categoriesProgress+"%");

									if (response.orders_page == 'complete') {
										ordersProgress = 100;
										jQuery('#squalomail_order_count').html(response.orders_in_squalomail.toLocaleString(undefined, {maximumFractionDigits: 0})).css('display', 'inline-block');
										jQuery('.sync-stats-card.orders .progress-bar-wrapper').hide();
									} else {
										if (response.orders_in_squalomail == 0) {
											ordersProgress = 0;
											ordersPartial = "0 / " + response.orders_in_store;
										} else {
											ordersProgress = response.orders_in_squalomail / response.orders_in_store * 100
											ordersPartial = response.orders_in_squalomail + " / " + response.orders_in_store;
										}
										if (ordersProgress > 100) ordersProgress = 100;
										jQuery('.squalomail_order_count_partial').html(ordersPartial);
									}
									jQuery('.sync-stats-card.orders .progress-bar').width(ordersProgress+"%");

									jQuery('#squalomail_last_updated').html(response.date);

									// only call status again if sync is running.
									setTimeout(function() {
										call_squalomail_for_stats(true);
									}, 10000);
									jQuery('#squalomail_last_updated').next('.spinner').css('visibility', 'hidden');
								}
								else {
									jQuery('#squalomail_last_updated').next('.spinner').css('visibility', 'hidden');	
									jQuery('.sync-stats-card .progress-bar-wrapper').hide();
									jQuery('#squalomail_order_count').css('display', 'inline-block');
									jQuery('#squalomail_product_count').css('display', 'inline-block');
									jQuery('#squalomail_category_count').css('display', 'inline-block');
									jQuery('#squalomail_promo_rules_count').css('display', 'inline-block');
								}
                            }
                        });
                    };
					
					call_squalomail_for_stats();
                }
            });
        </script> <?php
    }

	/**
	 * @param null|array $data
	 * @return bool|string
	 */
	private function updateSqualoMailList($data = null, $list_id = null)
	{
		if (empty($data)) {
			$data = $this->getOptions();
		}

		$required = array(
			'store_name', 'store_street', 'store_city', 'store_state',
			'store_postal_code', 'store_country', 'campaign_from_name',
			'campaign_from_email', 'campaign_subject', 'campaign_permission_reminder',
		);

		foreach ($required as $requirement) {
			if (!isset($data[$requirement]) || empty($data[$requirement])) {
			    squalomail_log('admin', 'does not have enough data to update the squalomail list.');
				return false;
			}
		}

		$submission = new SqualoMail_WooCommerce_CreateListSubmission();

		// allow the subscribers to choose preferred email type (html or text).
		$submission->setEmailTypeOption(true);

        // set the store name if the list id is not set.
		if (empty($list_id)) {
            $submission->setName($data['store_name']);
			if (isset($data['admin_email']) && !empty($data['admin_email'])) {
				$submission->setNotifyOnSubscribe($data['admin_email']);
				$submission->setNotifyOnUnSubscribe($data['admin_email']);
			}
        }

		// set the campaign defaults
		$submission->setCampaignDefaults(
			$data['campaign_from_name'],
			$data['campaign_from_email'],
			$data['campaign_subject'],
			$data['campaign_language']
		);

		// set the permission reminder message.
		$submission->setPermissionReminder($data['campaign_permission_reminder']);

		$submission->setContact($this->address($data));

		try {
			$submission->setDoi(squalomail_list_has_double_optin(true));
		}
		catch (\Exception $e) {
			add_settings_error('list_sync_error', '', __('Cannot create or update List at Squalomail.', 'squalomail-for-woocommerce') . ' ' . $e->getMessage() . ' ' . __('Please retry.', 'squalomail-for-woocommerce'));
			$this->setData('errors.squalomail_list', $e->getMessage());
			return false;
		}
		
		// let's turn this on for debugging purposes.
		squalomail_debug('admin', 'list info submission', array('submission' => print_r($submission->getSubmission(), true)));

		try {
			$response = !empty($list_id) ?
                $this->api()->updateList($list_id, $submission) :
                $this->api()->createList($submission);

			if (empty($list_id)) {
			    $list_id = array_key_exists('id', $response) ? $response['id'] : false;
            }

			$this->setData('errors.squalomail_list', false);

			return $list_id;

		} catch (SqualoMail_WooCommerce_Error $e) {
            squalomail_error('admin', $e->getMessage());
			$this->setData('errors.squalomail_list', $e->getMessage());
			return false;
		}
	}

	/**
	 * @param null $data
	 * @return bool
	 */
	private function syncStore($data = null)
	{
		if (empty($data)) {
			$data = $this->getOptions();
		}

        $list_id = $this->array_get($data, 'squalomail_list', false);
        $site_url = $this->getUniqueStoreID();

		if (empty($list_id) || empty($site_url)) {
		    return false;
        }

		$new = false;

		if (!($store = $this->api()->getStore($site_url))) {
			$new = true;
			$store = new SqualoMail_WooCommerce_Store();
		}

		$call = $new ? 'addStore' : 'updateStore';
		$time_key = $new ? 'store_created_at' : 'store_updated_at';

		$store->setId($site_url);
		$store->setPlatform('woocommerce');

		// set the locale data
		$store->setPrimaryLocale($this->array_get($data, 'store_locale', 'en'));
		$store->setTimezone(squalomail_get_timezone());
		$store->setCurrencyCode($this->array_get($data, 'store_currency_code', 'USD'));
		$store->setMoneyFormat($store->getCurrencyCode());

		// set the basics
		$store->setName($this->array_get($data, 'store_name'));
		$store->setDomain(get_option('siteurl'));

        // don't know why we did this before
        //$store->setEmailAddress($this->array_get($data, 'campaign_from_email'));
        $store->setEmailAddress($this->array_get($data, 'admin_email'));

		$store->setAddress($this->address($data));
		$store->setPhone($this->array_get($data, 'store_phone'));
		$store->setListId($list_id);

		try {
            squalomail_log('sync_store', 'posting data', array(
                'store_post' => $store->toArray(),
            ));

			// let's create a new store for this user through the API
			$this->api()->$call($store, false);

			// apply extra meta for store created at
			$this->setData('errors.store_info', false);
			$this->setData($time_key, time());

			// on a new store push, we need to make sure we save the site script into a local variable.
            squalomail_update_connected_site_script();

			// we need to update the list again with the campaign defaults
			$this->updateSqualoMailList($data, $list_id);

			return true;

		} catch (\Exception $e) {
			if (squalomail_string_contains($e->getMessage(),'woocommerce already exists in the account' )) {
			    // retrieve Squalomail store using domain
				$stores = $this->api()->stores();
				//iterate thru stores, find correct store ID and save it to db
				foreach ($stores as $sqm_store) {
					if ($sqm_store->getDomain() === $store->getDomain() && $store->getPlatform() == "woocommerce") {
						update_option('squalomail-woocommerce-store_id', $sqm_store->getId(), 'yes');
						
						// update the store with the previous listID
						$store->setListId($sqm_store->getListId());
						$store->setId($sqm_store->getId());

						$this->swapped_list_id = $sqm_store->getListId();
						$this->swapped_store_id = $sqm_store->getId();

						// check if list id is the same, if not, throw error saying that there's already a store synched to a list, so we can't proceed.
						
						if ($this->api()->updateStore($store)) {
							return true;
						}
					}
				}
			}
			$this->setData('errors.store_info', $e->getMessage());
			throw($e);
		}

		return false;
	}

	/**
	 * @param array $data
	 * @return SqualoMail_WooCommerce_Address
	 */
	private function address(array $data)
	{
		$address = new SqualoMail_WooCommerce_Address();

		if (isset($data['store_street']) && $data['store_street']) {
			$address->setAddress1($data['store_street']);
		}

		if (isset($data['store_city']) && $data['store_city']) {
			$address->setCity($data['store_city']);
		}

		if (isset($data['store_state']) && $data['store_state']) {
			$address->setProvince($data['store_state']);
		}

		if (isset($data['store_country']) && $data['store_country']) {
			$address->setCountry($data['store_country']);
		}

		if (isset($data['store_postal_code']) && $data['store_postal_code']) {
			$address->setPostalCode($data['store_postal_code']);
		}

		if (isset($data['store_name']) && $data['store_name']) {
			$address->setCompany($data['store_name']);
		}

		if (isset($data['store_phone']) && $data['store_phone']) {
			$address->setPhone($data['store_phone']);
		}
		
		$woo_countries = new WC_Countries();
		$address->setCountryCode($woo_countries->get_base_country());

		return $address;
	}

	/**
	 * @param array $required
	 * @param null $options
	 * @return bool
	 */
	private function validateOptions(array $required, $options = null)
	{
		$options = is_array($options) ? $options : $this->getOptions();

		foreach ($required as $requirement) {
			if (!isset($options[$requirement]) || empty($options[$requirement])) {
				return false;
			}
		}

		return true;
	}

    /**
     * Start the sync
     */
	public function startSync()
	{
	    // delete the transient so this only happens one time.
	    delete_site_transient('squalomail_woocommerce_start_sync');

		$full_sync = new SqualoMail_WooCommerce_Process_Full_Sync_Manager();
		
		// make sure the storeeId saved on DB is the same on Squalomail
		try {
			$this->syncStore();
		}
		catch (\Exception $e) {
			squalomail_log('error.sync', 'Store cannot be synced :: ' . $e->getMessage());
			add_settings_error('squalomail_sync_error', '', __('Cannot create or update Store at Squalomail.', 'squalomail-for-woocommerce') . ' Squalomail says: ' . $e->getMessage());
			return false;
		}

        // tell Squalomail that we're syncing
		$full_sync->start_sync();
		
        // enqueue sync manager
		as_enqueue_async_action( 'SqualoMail_WooCommerce_Process_Full_Sync_Manager', array(), 'sqm-woocommerce' );
	}

	/**
	 * Show the sync started message right when they sync things.
	 */
	private function showSyncStartedMessage()
	{
		$text = '<b>' . __('Starting the sync process...', 'squalomail-for-woocommerce').'</b><br/>'.
			'<p id="sync-status-message">'.
			__('The plugin has started the initial sync with your store, and the process will work in the background automatically.', 'squalomail-for-woocommerce') .
			' ' .
            __('Sometimes the sync can take a while, especially on sites with lots of orders and/or products. It is safe to navigate away from this screen while it is running.', 'squalomail-for-woocommerce') .
            '</p>';
		add_settings_error('squalomail-woocommerce_notice', $this->plugin_name, $text, 'success');
	}

	/**
	 * Show the review banner.
	 */
	private function squalomail_show_initial_sync_message()
	{
	    try {
            $order_count = squalomail_get_api()->getOrderCount(squalomail_get_store_id());
        } catch (\Exception $e) {
	        $order_count = squalomail_get_order_count();
        }

		$text = '<p id="sync-status-message">'.
			/* translators: %1$s: Number of synced orders %2$s: Audience name */	
			sprintf(__('We successfully synced %1$s orders to your Audience, %2$s. If you’re happy with this integration, leave a 5-star review. It helps our community know we’re working hard to make it better each day.', 'squalomail-for-woocommerce'),
                $order_count,
				$this->getListName()
			).
		'</p>'.
		'<a style="display:inline align-right" class="button sqm-review-button" href="https://wordpress.org/support/plugin/squalomail-for-woocommerce/reviews/" target=_blank>'.
			esc_html__('Leave a Review', 'squalomail-for-woocommerce').
        '</a>';
		
		add_settings_error('squalomail-woocommerce_notice', $this->plugin_name.'-initial-sync-end', $text, 'success');
	}

	/**
	 * set Communications status via sync page.
	 */
	public function squalomail_woocommerce_communication_status() {
		$original_opt = $this->getData('comm.opt',0);
		$opt = $_POST['opt'];
		$admin_email = $this->getOptions()['admin_email'];

		squalomail_debug('communication_status', "setting to {$opt}");

		// try to set the info on the server
		// post to communications api
		$response = $this->squalomail_set_communications_status_on_server($opt, $admin_email);
		
		// if success, set internal option to check for opt and display on sync page
		if ($response['response']['code'] == 200) {
			$response_body = json_decode($response['body']);
			if (isset($response_body) && $response_body->success == true) {
				$this->setData('comm.opt', $opt);
				wp_send_json_success(__('Saved', 'squalomail-for-woocommerce'));	
			}
		}
		else {
			//if error, keep option to original value 
			wp_send_json_error(array('error' => __('Error setting communications status', 'squalomail-for-woocommerce'), 'opt' => $original_opt));	
		}
		
		wp_die();
	}
	
	/**
	 * set Communications box status.
	 */
	public function squalomail_set_communications_status_on_server($opt, $admin_email, $remove = false) {
		$env = squalomail_environment_variables();
		$audience = !empty(squalomail_get_list_id()) ? 1 : 0;
		$synced = get_option('squalomail-woocommerce-sync.completed_at') > 0 ? 1 : 0;
		
		$post_data = array(
			'store_id' => squalomail_get_store_id(),
			'email' => $admin_email,
			'domain' => site_url(),
			'marketing_status' => $opt,
			'audience' => $audience,
			'synced' => $synced,
			'plugin_version' => "SqualoMail for WooCommerce/{$env->version}; PHP/{$env->php_version}; WordPress/{$env->wp_version}; Woo/{$env->wc_version};",
			
		);
		if ($remove) {
			$post_data['remove_email'] = true;
		}

		$route = "https://woocommerce.squalomailapp.com/api/opt_in_status";
		
		return wp_remote_post(esc_url_raw($route), array(
			'timeout'   => 12,
			'cache-control' => 'no-cache',
            'blocking'  => true,
            'method'      => 'POST',
            'data_format' => 'body',
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => json_encode($post_data),
		));
	}

    public function squalomail_woocommerce_ajax_delete_log_file() {
        if (isset($_POST['log_file']) && !empty($_POST['log_file'])) {
            $requested_log_file = $_POST['log_file'];
        }
        else {
            return wp_send_json_error(  __('No log file provided', 'squalomail-for-woocommerce'));
        }
        $log_handler = new WC_Log_Handler_File();
        $removed = $log_handler->remove(str_replace('-log', '.log', $requested_log_file));
        wp_send_json_success(array('success' => $removed));
    }

	public function squalomail_woocommerce_ajax_load_log_file() {
		if (isset($_POST['log_file']) && !empty($_POST['log_file'])) {
			$requested_log_file = $_POST['log_file'];
		}
		else {
			return wp_send_json_error(  __('No log file provided', 'squalomail-for-woocommerce'));
		}
		
		$files  = defined('WC_LOG_DIR') ? @scandir( WC_LOG_DIR ) : array();

		$logs = array();
		if (!empty($files)) {
			foreach (array_reverse($files) as $key => $value) {
				if (!in_array( $value, array( '.', '..' ))) {
					if (!is_dir($value) && squalomail_string_contains($value, 'squalomail_woocommerce')) {
						$logs[sanitize_title($value)] = $value;
					}
				}
			}
		}

		if (!empty($requested_log_file) && isset($logs[sanitize_title($requested_log_file)])) {
			$viewed_log = $logs[sanitize_title($requested_log_file)];
		} else {
			return wp_send_json_error( __('Error loading log file contents', 'squalomail-for-woocommerce'));
		}

		return wp_send_json_success( esc_html( file_get_contents( WC_LOG_DIR . $viewed_log ) ) );
		
	}

}
