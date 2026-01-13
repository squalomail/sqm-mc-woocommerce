<?php

/**
 * Created by Squalo.
 *
 * Name: Ryan Hungate
 * Email: ryan@vextras.com
 * Date: 2/17/16
 * Time: 12:03 PM
 */
class SqualoMail_Service extends SqualoMail_WooCommerce_Options
{
    protected $user_email = null;
    protected $previous_email = null;
    protected $user_language = null;
    protected $force_cart_post = false;
    protected $cart_was_submitted = false;
    protected $cart = array();
    protected $validated_cart_db = false;
    /** @var null|static */
    protected static $_instance = null;

    /**
     * @return SqualoMail_Service
     */
    public static function instance()
    {
        if (!empty(static::$_instance)) {
            return static::$_instance;
        }
        $env = squalomail_environment_variables();
        static::$_instance = new SqualoMail_Service();
        static::$_instance->setVersion($env->version);
        return static::$_instance;
    }

    /**
     * hook fired when we know everything is booted
     */
    public function wooIsRunning()
    {
        // make sure the site option for setting the squalomail_carts has been saved.
        $this->validated_cart_db = get_site_option('squalomail_woocommerce_db_squalomail_carts', false);
        $this->is_admin = current_user_can('administrator');
    }

    /**
     * @param $r
     * @param $url
     * @return mixed
     */
    public function addHttpRequestArgs( $r, $url ) {
        // not sure whether or not we need to implement something like this yet.
        //$r['headers']['Authorization'] = 'Basic ' . base64_encode('username:password');
        return $r;
    }

    /**
     * @param $key
     * @param $default
     * @return mixed
     */
    protected function cookie($key, $default = null)
    {
        if ($this->is_admin) {
            return $default;
        }

        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default;
    }

    /**
     * @param WC_Order $order
     */
    public function onNewPayPalOrder($order)
    {
        $this->onNewOrder($order->get_id());
    }

    /**
     * This should only fire on a web based order so we can do real campaign tracking here.
     *
     * @param $order_id
     */
    public function onNewOrder($order_id)
    {
        if (!squalomail_is_configured()) return;

        // see if we have a session id and a campaign id, also only do this when this user is not the admin.
        $campaign_id = $this->getCampaignTrackingID();
        if (empty($campaign_id)) {
            $campaign_id = SqualoMail_WooCommerce_HPOS::get_order_meta($order_id, 'squalomail_woocommerce_campaign_id', true);
            // make sure this campaign ID has a valid format before we submit something
            if (!$this->campaignIdMatchesFormat($campaign_id)) {
                $campaign = null;
            }
        }

        // grab the landing site cookie if we have one here.
        $landing_site = $this->getLandingSiteCookie();
        if (empty($landing_site)) {
            $landing_site = SqualoMail_WooCommerce_HPOS::get_order_meta($order_id, 'squalomail_woocommerce_landing_site', true);
            if (!$landing_site) $campaign = null;
        }

        // expire the landing site cookie so we can rinse and repeat tracking
        $this->expireLandingSiteCookie();

        // remove this record from the db.
        $this->clearCartData();

        return array (
            'campaign_id' => $campaign_id,
            'landing_site' => $landing_site
        );
    }

    /**
      * @param $order_id
      */
    public function handleNewOrder($order_id)
    {
        if (!squalomail_is_configured()) {
            return;
        }
    
        squalomail_log('debug', "Order ID {$order_id} was created", array('tracking' => 'tracking'));
        
        $this->onOrderSave($order_id, null, true);
    }

    /**
     * @param $order_id
     * @param bool $is_admin
     */
    public function handleOrderStatusChanged($order_id, $old_status, $new_status)
    {
        if (!squalomail_is_configured()) return;
        
        $tracking = null;
        $newOrder = false;

        if ("pending" == $old_status && ("processing" == $new_status || "completed" == $new_status)) {
            $tracking = $this->onNewOrder($order_id);
            // $newOrder = true;
        }

        squalomail_log('debug', "Order ID {$order_id} was {$old_status} and is now {$new_status}", array('new_order' => $newOrder, 'tracking' => $tracking));

        $this->onOrderSave($order_id, $tracking, $newOrder);
    }

    /**
     * @param $order_id
     * @param $tracking
     */
    public function onOrderSave($order_id, $tracking = null, $newOrder = null)
    {
        if (!squalomail_is_configured()) return;

        // queue up the single order to be processed.
        $campaign_id = isset($tracking) && isset($tracking['campaign_id']) ? $tracking['campaign_id'] : null;
        $landing_site = isset($tracking) && isset($tracking['landing_site']) ? $tracking['landing_site'] : null;
        $language = $newOrder ? substr( get_locale(), 0, 2 ) : null;
        
        $gdpr_fields = isset($_POST['squalomail_woocommerce_gdpr']) ? 
            $_POST['squalomail_woocommerce_gdpr'] : false;

        if (isset($tracking)) {
            // update the post meta with campaing tracking details for future sync
            SqualoMail_WooCommerce_HPOS::update_order_meta($order_id, 'squalomail_woocommerce_campaign_id', $campaign_id);
            SqualoMail_WooCommerce_HPOS::update_order_meta($order_id, 'squalomail_woocommerce_landing_site', $landing_site);
        }

        $handler = new SqualoMail_WooCommerce_Single_Order($order_id, null, $campaign_id, $landing_site, $language, $gdpr_fields);
        $handler->is_update = $newOrder ? !$newOrder : null;
        $handler->is_admin_save = is_admin();
        
        squalomail_handle_or_queue($handler, 90);
    }

    /**
     * @param $order_id
     */
    public function onPartiallyRefunded($order_id)
    {
        if (!squalomail_is_configured()) return;

        $handler = new SqualoMail_WooCommerce_Single_Order($order_id, null, null, null);
        $handler->partially_refunded = true;
        squalomail_handle_or_queue($handler);
    }

    /**
     * Clear the card data for a user.
     */
    public function clearCartData()
    {
        if ($user_email = $this->getCurrentUserEmail()) {
            $id = squalomail_hash_trim_lower($user_email);
            $this->deleteCart($id);
            $this->api()->deleteCartByID($this->getUniqueStoreID(), $id);
        }
    }

    /**
     * @param null $updated
     *
     * @return bool|mixed|null
     */
    public function handleCartUpdated($updated = null)
    {
        if ($updated === false || $this->is_admin || $this->cart_was_submitted || !squalomail_is_configured()) {
            return !is_null($updated) ? $updated : false;
        }

        $this->cart = $this->getCartItems();

        if (($user_email = $this->getCurrentUserEmail())) {

            // let's skip this right here - no need to go any further.
            if (squalomail_email_is_privacy_protected($user_email)) {
                return !is_null($updated) ? $updated : false;
            }

            $uid = squalomail_hash_trim_lower($user_email);

            // track the cart locally so we can repopulate things for cross device compatibility.
            $this->trackCart($uid, $user_email);

            if ($this->cart && !empty($this->cart)) {

                $this->cart_was_submitted = true;

                // grab the cookie data that could play important roles in the submission
                $campaign = $this->getCampaignTrackingID();

                // get user language or default to admin main language
                $language = $this->user_language ?: substr( get_locale(), 0, 2 );

                // fire up the job handler
                $handler = new SqualoMail_WooCommerce_Cart_Update($uid, $user_email, $campaign, $this->cart, $language);
                squalomail_handle_or_queue($handler);
            } else {
                squalomail_unqueue(SqualoMail_WooCommerce_Cart_Update::class, $uid);
            }

            return !is_null($updated) ? $updated : true;
        }

        return !is_null($updated) ? $updated : false;
    }

    /**
     * @param $post_id
     */
    public function handleNewCoupon($post_id)
    {
        $this->handleCouponSaved($post_id, new WC_Coupon($post_id));
    }

    /**
     * @param $post_id
     * @param null $coupon
     */
    public function handleCouponSaved($post_id, $coupon = null)
    {
        if (!squalomail_is_configured()) return;

        if ($coupon instanceof WC_Coupon) {
            squalomail_handle_or_queue(new SqualoMail_WooCommerce_SingleCoupon($post_id));
        }
    }

    /**
     * @param $post_id
     */
    public function handleCouponRestored($post_id)
    {
        $this->handleCouponSaved($post_id, new WC_Coupon($post_id));
    }

    /**
     * @param WC_Data          $object   The deleted or trashed object.
	 * @param WP_REST_Response $response The response data.
     * @param WP_REST_Request  $request  The request sent to the API.
     */
    public function handleAPICouponTrashed($object, $response, $request)
    {
        try {
            $deleted = squalomail_get_api()->deletePromoRule(squalomail_get_store_id(), $request['id']);
            if ($deleted) squalomail_log('api.promo_code.deleted', "deleted promo code {$request['id']}");
            else squalomail_log('api.promo_code.delete_fail', "Unable to delete promo code {$request['id']}");
        } catch (\Exception $e) {
            squalomail_error('delete promo code', $e->getMessage());
        }
    }

    /**
     * Save post metadata when a post is saved.
     *
     * @param int $post_id The post ID.
     * @param WP_Post $post The post object.
     * @param bool $update Whether this is an existing post being updated or not.
     */
    public function handlePostSaved($post_id, $post, $update)
    {
        if (!squalomail_is_configured()) return;

        // don't handle any of these statuses because they're not ready for the show
        if (!in_array($post->post_status, array('trash', 'auto-draft', 'draft', 'pending', 'checkout-draft', 'wc-checkout-draft'))) {
            if ('product' == $post->post_type) {
                squalomail_handle_or_queue(new SqualoMail_WooCommerce_Single_Product($post_id), 5);
            } elseif ('shop_order' == $post->post_type) {
                $tracking = $this->onNewOrder($post_id);
                $this->onOrderSave($post_id, $tracking, !$update);
            }
        }
    }

    /**
     * Edit or create product category.
     *
     * @param int $term_id Term ID.
     * @param int $tt_id Term taxonomy ID.
     */
    public function handleCategorySaved($term_id, $tt_id)
    {
        if (!squalomail_is_configured()) return;
        
        squalomail_handle_or_queue(new SqualoMail_WooCommerce_Product_Category($term_id), 7);
    }

    /**
     * After product terms change.
     *
     * @param int $object_id Object ID.
     * @param array $terms An array of object term IDs or slugs.
     * @param array $tt_ids An array of term taxonomy IDs.
     * @param string $taxonomy Taxonomy slug.
     * @param bool $append Whether to append new terms to the old terms.
     * @param array $old_tt_ids Old array of term taxonomy IDs.
     */
    public function handlePostCategoryUpdate($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids)
    {
        if (!squalomail_is_configured()) return;

        if ($taxonomy == 'product_cat') {
            $terms_diff = array_merge(array_diff($tt_ids, $old_tt_ids), array_diff($old_tt_ids, $tt_ids));

            if(!empty($terms_diff)) {
                foreach($terms_diff as $term_id) {
                    squalomail_handle_or_queue(new SqualoMail_WooCommerce_Product_Category($term_id), 7);
                }
            }
        }
    }

    /**
     * Delete product category.
     *
     * @param int $term_id Term ID.
     * @param int $tt_id Term taxonomy ID.
     * @param int $deleted_term Deleted term ID.
     * @param int $object_ids
     */
    public function handleCategoryDeleted($term_id, $tt_id, $deleted_term, $object_ids)
    {
        if (!squalomail_is_configured()) return;

        try {
            $deleted = squalomail_get_api()->deleteStoreCategory(squalomail_get_store_id(), $term_id);
            if ($deleted) squalomail_log('category.deleted', "deleted category {$term_id}");
            else squalomail_log('category.delete_fail', "Unable to deleted category {$term_id}");
        } catch (\Exception $e) {
            squalomail_error('delete category', $e->getMessage());
        }
    }

    /**
     * @param $post_id
     */
    public function handlePostTrashed($post_id)
    {
        if (!squalomail_is_configured()) return;

        switch (get_post_type($post_id)) {
            case 'shop_coupon':
                try {
                    $deleted = squalomail_get_api()->deletePromoRule(squalomail_get_store_id(), $post_id);
                    if ($deleted) squalomail_log('promo_code.deleted', "deleted promo code {$post_id}");
                    else squalomail_log('promo_code.delete_fail', "Unable to delete promo code {$post_id}");
                } catch (\Exception $e) {
                    squalomail_error('delete promo code', $e->getMessage());
                }
                break;
            case 'product':
                try {
                    $deleted = squalomail_get_api()->deleteStoreProduct(squalomail_get_store_id(), $post_id);
                    if ($deleted) squalomail_log('product.deleted', "deleted product {$post_id}");
                    else squalomail_log('product.delete_fail', "Unable to deleted product {$post_id}");
                } catch (\Exception $e) {
                    squalomail_error('delete product', $e->getMessage());
                }
                break;
        }
    }

    /**
     * @param $post_id
     */
    public function handlePostRestored($post_id)
    {
        if (!squalomail_is_configured() || !($post = get_post($post_id))) return;

        // don't handle any of these statuses because they're not ready for the show
        if (in_array($post->post_status, array('trash', 'auto-draft', 'draft', 'pending', 'checkout-draft', 'wc-checkout-draft'))) {
            return;
        }

        switch(get_post_type($post_id)) {
            case 'shop_coupon':
                return $this->handleCouponRestored($post_id);
                break;

            case 'product':
                squalomail_handle_or_queue(new SqualoMail_WooCommerce_Single_Product($post_id), 5);
                break;
        }
    }

    /**
     * @param $user_id
     */
    public function handleUserRegistration($user_id)
    {
        if (!squalomail_is_configured()) return;

        $subscribed = (bool) isset($_POST['squalomail_woocommerce_newsletter']) && $_POST['squalomail_woocommerce_newsletter'] ? true : false;

        if (isset($_POST['squalomail_woocommerce_newsletter']) && $_POST['squalomail_woocommerce_newsletter']) {
            $gdpr_fields = isset($_POST['squalomail_woocommerce_gdpr']) ? 
                $_POST['squalomail_woocommerce_gdpr'] : false;
        }

        // update the user meta with the 'is_subscribed' form element
        update_user_meta($user_id, 'squalomail_woocommerce_is_subscribed', $subscribed);

        if ($subscribed) {
            $job = new SqualoMail_WooCommerce_User_Submit($user_id, $subscribed, null, null, $gdpr_fields);
            squalomail_handle_or_queue($job);
        }
    }

    /**
     * @param $user_id
     * @param $old_user_data
     */
    function handleUserUpdated($user_id, $old_user_data)
    {
        if (!squalomail_is_configured()) return;

        // only update this person if they were marked as subscribed before
        $is_subscribed = get_user_meta($user_id, 'squalomail_woocommerce_is_subscribed', true);

        // if they don't have a meta set for is_subscribed, we will get a blank string, so just ignore this.
        if ($is_subscribed === '' || $is_subscribed === null) return;

        // only send this update if the user actually has a boolean value.
        squalomail_handle_or_queue(new SqualoMail_WooCommerce_User_Submit($user_id, (bool) $is_subscribed, $old_user_data));
    }

    /**
     * Delete all the options pointing to the pages, and re-start the sync process.
     * @param bool $only_products
     * @return bool
     */
    protected function syncProducts($only_products = false)
    {
        if (!$this->isAdmin()) return false;
        $this->removePointers(true, ($only_products ? false : true));
        update_option('squalomail-woocommerce-sync.orders.prevent', $only_products);
        SqualoMail_WooCommerce_Process_Products::push();
        return true;
    }

    /**
     * Delete all the options pointing to the pages, and re-start the sync process.
     * @return bool
     */
    protected function syncOrders()
    {
        if (!$this->isAdmin()) return false;
        $this->removePointers(false, true);
        // since the products are all good, let's sync up the orders now.
        squalomail_handle_or_queue(new SqualoMail_WooCommerce_Process_Orders());
        return true;
    }

    /**
     * @return bool|string
     */
    public function getCurrentUserEmail()
    {
        if (isset($this->user_email) && !empty($this->user_email)) {
            return $this->user_email = strtolower($this->user_email);
        }

        $user = wp_get_current_user();
        $email = ($user->ID > 0 && isset($user->user_email)) ? $user->user_email : $this->getEmailFromSession();

        return $this->user_email = strtolower($email);
    }

    /**
     * @return bool|array
     */
    public function getCartItems()
    {
        if (function_exists('WC')) {
            $this->cart = WC()->cart->get_cart();
        } else {
            if (!($this->cart = $this->getWooSession('cart', false))) {
                $cart_session = array();
                foreach ( $this->cart as $key => $values ) {
                    $cart_session[$key] = $values;
                    unset($cart_session[$key]['data']); // Unset product object
                }
                return $this->cart = $cart_session;
            }
        }

        return is_array($this->cart) ? $this->cart : false;
    }

    /**
     * Set the cookie of the Squalo campaigns if we have one.
     */
    public function handleCampaignTracking()
    {
        // set the landing site cookie if we don't have one.
        $this->setLandingSiteCookie();

        $cookie_duration = $this->getCookieDuration();

        // if we have a query string of the sqm_cart_id in the URL, that means we are sending a campaign from SQM
        if (isset($_GET['sqm_cart_id']) && !isset($_GET['removed_item'])) {

            // try to pull the cart from the database.
            if (($cart = $this->getCart($_GET['sqm_cart_id'])) && !empty($cart)) {

                // set the current user email
                $this->user_email = trim(str_replace(' ','+', $cart->email));

                if (($current_email = $this->getEmailFromSession()) && $current_email !== $this->user_email) {
                    $this->previous_email = $current_email;
                    squalomail_set_cookie('squalomail_user_previous_email',$this->user_email, $cookie_duration, '/');
                }

                // cookie the current email
                squalomail_set_cookie('squalomail_user_email', $this->user_email, $cookie_duration, '/' );

                $cart_data = unserialize($cart->cart);

                if (!empty($cart_data)) {
                    // set the cart data.
                    $this->setWooSession('cart', unserialize($cart->cart));

                    squalomail_debug('carts', "manually setting cart data for {$this->user_email}", array(
                        'cart_id' => $_GET['sqm_cart_id'],
                        'cart' => $cart->cart,
                    ));
                }
            }
        }

        if (isset($_GET['sqm_cid'])) {
            $this->setCampaignTrackingID($_GET['sqm_cid'], $cookie_duration);
        }

        if (isset($_GET['sqm_eid'])) {
            squalomail_set_cookie('squalomail_email_id', trim($_GET['sqm_eid']), $cookie_duration, '/' );
        }
    }

    /**
     * @return mixed|null
     */
    public function getCampaignTrackingID()
    {
        $cookie = $this->cookie('squalomail_campaign_id', false);

        if (empty($cookie)) {
            $cookie = $this->getWooSession('squalomail_campaign_id', false);
        }

        // we must follow a pattern at minimum in order to think this is possibly a valid campaign ID.
        if (!$this->campaignIdMatchesFormat($cookie)) {
            return false;
        }

        return $cookie;
    }

    /**
     * @param $id
     * @param $cookie_duration
     * @return $this
     */
    public function setCampaignTrackingID($id, $cookie_duration)
    {
        if (!squalomail_is_configured()) {
            return $this;
        }

        $cid = trim($id);

        // we must follow a pattern at minimum in order to think this is possibly a valid campaign ID.
        if (!$this->campaignIdMatchesFormat($cid)) {
            return $this;
        }

        // don't throw the error if it's not found.
        if (!$this->api()->getCampaign($cid, false)) {
            $cid = null;
        }
        
        squalomail_set_cookie('squalomail_campaign_id', $cid, $cookie_duration, '/' );
        $this->setWooSession('squalomail_campaign_id', $cid);

        return $this;
    }

    /**
     * @param $cid
     * @return bool
     */
    public function campaignIdMatchesFormat($cid)
    {
        if (!is_string($cid) || empty($cid)) return false;
        return (bool) preg_match("/^[a-zA-Z0-9]{10,12}$/", $cid, $matches);
    }

    /**
     * @return mixed|null
     */
    public function getLandingSiteCookie()
    {
        $cookie = $this->cookie('squalomail_landing_site', false);

        if (empty($cookie)) {
            $cookie = $this->getWooSession('squalomail_landing_site', false);
        }

        return $cookie;
    }

    /**
     * @return $this
     */
    public function setLandingSiteCookie()
    {
        if (isset($_GET['expire_landing_site'])) $this->expireLandingSiteCookie();

        // if we already have a cookie here, we need to skip it.
        if ($this->getLandingSiteCookie() != false) return $this;

        $http_referer = $this->getReferer();

        if (!empty($http_referer)) {

            // grab the current landing url since it's a referral.
            $landing_site = home_url(wp_unslash($_SERVER['REQUEST_URI']));

            $compare_refer = str_replace(array('http://', 'https://'), '', $http_referer);
            $compare_local = str_replace(array('http://', 'https://'), '', $landing_site);

            if (strpos($compare_local, $compare_refer) === 0) return $this;

            // set the cookie
            squalomail_set_cookie('squalomail_landing_site', $landing_site, $this->getCookieDuration(), '/' );

            $this->setWooSession('squalomail_landing_site', $landing_site);
        }

        return $this;
    }

    /**
     * @return array|bool|string
     */
    public function getReferer()
    {
        if (!empty($_REQUEST['_wp_http_referer'])) {
            return wp_unslash($_REQUEST['_wp_http_referer']);
        } elseif (!empty($_SERVER['HTTP_REFERER'])) {
            return wp_unslash( $_SERVER['HTTP_REFERER']);
        }
        return false;
    }

    /**
     * @return $this
     */
    public function expireLandingSiteCookie()
    {
        squalomail_set_cookie('squalomail_landing_site', false, $this->getCookieDuration(), '/' );
        $this->setWooSession('squalomail_landing_site', false);

        return $this;
    }

    /**
     * @return bool
     */
    protected function getEmailFromSession()
    {
        return $this->cookie('squalomail_user_email', false);
    }

    /**
     * @return bool
     */
    protected function getPreviousEmailFromSession()
    {
        if ($this->previous_email) {
            return $this->previous_email = strtolower($this->previous_email);
        }
        $email = $this->cookie('squalomail_user_previous_email', false);
        return $email ? strtolower($email) : false;
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function getWooSession($key, $default = null)
    {
        if (!function_exists('WC')) return $default;

        if (!($woo = WC()) || empty($woo->session)) {
            return $default;
        }

        // not really sure why this would be the case, but if there is no session we can't get it anyway.
        if (!is_object($woo->session) || !method_exists($woo->session, 'get')) {
            return $default;
        }

        return $woo->session->get($key, $default);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setWooSession($key, $value)
    {
        if (!function_exists('WC')) return $this;

        if (!($woo = WC()) || empty($woo->session)) {
            return $this;
        }

        $woo->session->set($key, $value);

        return $this;
    }

    /**
     * @param $key
     * @return $this
     */
    public function removeWooSession($key)
    {
        if (!function_exists('WC')) return $this;

        if (!($woo = WC()) || empty($woo->session)) {
            return $this;
        }

        $woo->session->__unset($key);
        return $this;
    }

    /**
     *
     */
    public function get_user_by_hash()
    {
        if ($this->doingAjax() && isset($_GET['hash'])) {
            if (($cart = $this->getCart($_GET['hash']))) {
                $this->respondJSON(array('success' => true, 'email' => $cart->email));
            }
        }
        $this->respondJSON(array('success' => false, 'email' => false));
    }

    /**
     *
     */
    public function set_user_by_email()
    {
        if ($this->is_admin) {
            $this->respondJSON(array('success' => false));
        }

        if ($this->doingAjax() && isset($_GET['email'])) {

            $cookie_duration = $this->getCookieDuration();

            $this->user_email = trim(str_replace(' ','+', $_GET['email']));

            if (($current_email = $this->getEmailFromSession()) && $current_email !== $this->user_email) {
                $this->previous_email = $current_email;
                $this->force_cart_post = true;
                squalomail_set_cookie('squalomail_user_previous_email',$this->user_email, $cookie_duration, '/' );
            }

            squalomail_set_cookie('squalomail_user_email', $this->user_email, $cookie_duration, '/' );

            $this->getCartItems();

            if (isset($_GET['sqm_language'])) {
                $this->user_language = $_GET['sqm_language'];
            }

            $this->handleCartUpdated();

            $this->respondJSON(array(
                'success' => true,
                'email' => $this->user_email,
                'previous' => $this->previous_email,
                'cart' => $this->cart,
            ));
        }

        $this->respondJSON(array('success' => false, 'email' => false));
    }

    /**
     * @param string $time
     * @return int
     */
    protected function getCookieDuration($time = 'thirty_days')
    {
        $durations = array(
            'one_day' => 86400, 'seven_days' => 604800, 'fourteen_days' => 1209600, 'thirty_days' => 2419200,
        );

        if (!array_key_exists($time, $durations)) {
            $time = 'thirty_days';
        }

        return time() + $durations[$time];
    }

    /**
     * @param $key
     * @param bool $default
     * @return bool
     */
    protected function get($key, $default = false)
    {
        if (!isset($_REQUEST['squalomail-woocommerce']) || !isset($_REQUEST['squalomail-woocommerce'][$key])) {
            return $default;
        }
        return $_REQUEST['squalomail-woocommerce'][$key];
    }

    /**
     * @param $uid
     * @return array|bool|null|object|void
     */
    protected function getCart($uid)
    {
        if (!$this->validated_cart_db) return false;

        global $wpdb;

        $table = "{$wpdb->prefix}squalomail_carts";
        $statement = "SELECT * FROM $table WHERE id = %s";
        $sql = $wpdb->prepare($statement, $uid);

        if (($saved_cart = $wpdb->get_row($sql)) && !empty($saved_cart)) {
            return $saved_cart;
        }

        return false;
    }

    /**
     * @param $uid
     * @return true
     */
    protected function deleteCart($uid)
    {
        if (!$this->validated_cart_db) return false;

        global $wpdb;
        $table = "{$wpdb->prefix}squalomail_carts";
        $sql = $wpdb->prepare("DELETE FROM $table WHERE id = %s", $uid);
        $wpdb->query($sql);

        return true;
    }

    /**
     * @param $uid
     * @param $email
     * @return bool
     */
    protected function trackCart($uid, $email)
    {
        if (!$this->validated_cart_db) return false;

        global $wpdb;

        $table = "{$wpdb->prefix}squalomail_carts";

        $statement = "SELECT * FROM $table WHERE id = %s";
        $sql = $wpdb->prepare($statement, $uid);

        $user_id = get_current_user_id();

        if (($saved_cart = $wpdb->get_row($sql)) && is_object($saved_cart)) {
            $statement = "UPDATE {$table} SET `cart` = '%s', `email` = '%s', `user_id` = %s WHERE `id` = '%s'";
            $sql = $wpdb->prepare($statement, array(maybe_serialize($this->cart), $email, $user_id, $uid));
            try {
                $wpdb->query($sql);
            } catch (\Exception $e) {
                return false;
            }
        } else {
            try {
                $wpdb->insert("{$wpdb->prefix}squalomail_carts", array(
                    'id' => $uid,
                    'email' => $email,
                    'user_id' => (int) $user_id,
                    'cart'  => maybe_serialize($this->cart),
                    'created_at'   => gmdate('Y-m-d H:i:s', time()),
                ));
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $data
     */
    protected function respondJSON($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * @param null $obj_id
     * @return bool
     */
    public function squalomail_process_single_job($obj_id = null) {
        try {
            // not sure why this is happening - but we need to prepare for it and return false when it does.
            if (empty($obj_id)) {
                return false;
            }
            // get job row from db
            global $wpdb;
            $sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}squalomail_jobs	WHERE obj_id = %s", $obj_id );
            $job_row = $wpdb->get_row( $sql );
            
            if (is_null($job_row) || !is_object($job_row)) {
                squalomail_error('action_scheduler.process_job.fail','Job '.current_action().' not found at '.$wpdb->prefix.'_squalomail_jobs database table :: obj_id '.$obj_id);
                return false;
            }
            // get variables
            $job = unserialize($job_row->job);
            
            $job_id =$job_row->id;

            // process job
            $job->handle();
            
            // delete processed job
            $sql = $wpdb->prepare("DELETE FROM {$wpdb->prefix}squalomail_jobs WHERE id = %s AND obj_id = %s", array($job_id, $obj_id));
            $wpdb->query($sql);
            
            return true;
        } catch (\Exception $e) {
            $message = !empty($e->getMessage()) ? ' - ' . $e->getMessage() :'';
            squalomail_debug('action_scheduler.process_job.fail', get_class($job) . ' :: obj_id '.$obj_id . ' :: ' .get_class($e) . $message);
        }
        return false;
    }

    public function squalomail_process_sync_manager () {
        $sync_stats_manager = new SqualoMail_WooCommerce_Process_Full_Sync_Manager();
        $sync_stats_manager->handle();
    }

}
