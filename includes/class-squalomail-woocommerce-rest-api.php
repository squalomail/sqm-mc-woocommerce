<?php

class SqualoMail_WooCommerce_Rest_Api
{
    protected static $namespace = 'squalomail-for-woocommerce/v1';

    /**
     * @param $path
     * @return string
     */
    public static function url($path)
    {
        return esc_url_raw(rest_url(static::$namespace.'/'.ltrim($path, '/')));
    }
    /**
     * Register all Squalo API routes.
     */
    public function register_routes()
    {
        // ping
        register_rest_route(static::$namespace, '/ping', array(
            'methods' => 'GET',
            'callback' => array($this, 'ping'),
            'permission_callback' => '__return_true',
        ));

        // Right now we only have a survey disconnect endpoint.
        register_rest_route(static::$namespace, "/survey/disconnect", array(
            'methods' => 'POST',
            'callback' => array($this, 'post_disconnect_survey'),
            'permission_callback' => array($this, 'permission_callback'),
        ));

        // Sync Stats
        register_rest_route(static::$namespace, '/sync/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_sync_stats'),
            'permission_callback' => array($this, 'permission_callback'),
        ));

        // remove review banner
        register_rest_route(static::$namespace, "/review-banner", array(
            'methods' => 'GET',
            'callback' => array($this, 'dismiss_review_banner'),
            'permission_callback' => array($this, 'permission_callback'),
        ));
    }

    /**
     * @return bool
     */
    public function permission_callback()
    {
        $cap = squalomail_get_allowed_capability();
        return ($cap === 'manage_woocommerce' || $cap === 'manage_options' );
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function ping(WP_REST_Request $request)
    {
        return $this->squalomail_rest_response(array('success' => true));
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function post_disconnect_survey(WP_REST_Request $request)
    {
        // need to send a post request to
        $host = squalomail_environment_variables()->environment === 'staging' ?
            'https://staging.conduit.vextras.com' : 'https://conduit.squalomailapp.com';

        $route = "{$host}/survey/woocommerce";

        $result = wp_remote_post(esc_url_raw($route), array(
            'timeout'   => 12,
            'blocking'  => true,
            'method'      => 'POST',
            'data_format' => 'body',
            'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
            'body'        => json_encode($request->get_params()),
        ));

        return $this->squalomail_rest_response($result);
    }

    /**
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function get_sync_stats(WP_REST_Request $request)
    {
        // if the queue is running in the console - we need to say tell the response why it's not going to fire this way.
        if (!squalomail_is_configured() || !($api = squalomail_get_api())) {
            return $this->squalomail_rest_response(array('success' => false, 'reason' => 'not configured'));
        }

        $store_id = squalomail_get_store_id();
        
        $complete = array(
            'coupons' => get_option('squalomail-woocommerce-sync.coupons.completed_at'),
            'products' => get_option('squalomail-woocommerce-sync.products.completed_at'),
            'categories' => get_option('squalomail-woocommerce-sync.categories.completed_at'),
            'orders' => get_option('squalomail-woocommerce-sync.orders.completed_at')
        );

        $promo_rules_count = squalomail_get_coupons_count();
        $product_count = squalomail_get_product_count();
        $category_count = squalomail_get_category_count();
        $order_count = squalomail_get_order_count();

        $squalomail_total_promo_rules = $complete['coupons'] ? $promo_rules_count - squalomail_get_remaining_jobs_count('SqualoMail_WooCommerce_SingleCoupon') : 0;
        $squalomail_total_products = $complete['products'] ? $product_count - squalomail_get_remaining_jobs_count('SqualoMail_WooCommerce_Single_Product') : 0;
        $squalomail_total_categories = $complete['categories'] ? $category_count - squalomail_get_remaining_jobs_count('SqualoMail_WooCommerce_Product_Category') : 0;
        $squalomail_total_orders = $complete['orders'] ? $order_count - squalomail_get_remaining_jobs_count('SqualoMail_WooCommerce_Single_Order') : 0;

        // try {
        //     $promo_rules = $api->getPromoRules($store_id, 1, 1, 1);
        //     $squalomail_total_promo_rules = $promo_rules['total_items'];
        //     if (isset($promo_rules_count['publish']) && $squalomail_total_promo_rules > $promo_rules_count['publish']) $squalomail_total_promo_rules = $promo_rules_count['publish'];
        // } catch (\Exception $e) { $squalomail_total_promo_rules = 0; }
        // try {
        //     $products = $api->products($store_id, 1, 1);
        //     $squalomail_total_products = $products['total_items'];
        //     if ($squalomail_total_products > $product_count) $squalomail_total_products = $product_count;
        // } catch (\Exception $e) { $squalomail_total_products = 0; }
        // try {
        //     $orders = $api->orders($store_id, 1, 1);
        //     $squalomail_total_orders = $orders['total_items'];
        //     if ($squalomail_total_orders > $order_count) $squalomail_total_orders = $order_count;
        // } catch (\Exception $e) { $squalomail_total_orders = 0; }

        $date = squalomail_date_local('now');

        // but we need to do it just in case.
        return $this->squalomail_rest_response(array(
            'success' => true,
            'promo_rules_in_store' => $promo_rules_count,
            'promo_rules_in_squalomail' => $squalomail_total_promo_rules,
            
            'products_in_store' => $product_count,
            'products_in_squalomail' => $squalomail_total_products,
            'categories_in_store' => $category_count,
            'categories_in_squalomail' => $squalomail_total_categories,
            
            'orders_in_store' => $order_count,
            'orders_in_squalomail' => $squalomail_total_orders,
            
            // 'promo_rules_page' => get_option('squalomail-woocommerce-sync.coupons.current_page'),
            // 'products_page' => get_option('squalomail-woocommerce-sync.products.current_page'),
            // 'orders_page' => get_option('squalomail-woocommerce-sync.orders.current_page'),
            
            'date' => $date->format( __('D, M j, Y g:i A', 'squalomail-for-woocommerce')),
            'has_started' => squalomail_has_started_syncing() || ($order_count != $squalomail_total_orders),
            'has_finished' => squalomail_is_done_syncing() && ($order_count == $squalomail_total_orders),
        ));
    }
    
    /**
     * @param WP_REST_Request $request
     * @return WP_Error|WP_REST_Response
     */
    public function dismiss_review_banner(WP_REST_Request $request)
    {
        return $this->squalomail_rest_response(array('success' => delete_option('squalomail-woocommerce-sync.initial_sync')));
    }


    /**
     * @param array $data
     * @param int $status
     * @return WP_REST_Response
     */
    private function squalomail_rest_response($data, $status = 200) {
        if (!is_array($data)) $data = array();
        $response = new WP_REST_Response($data);
        $response->set_status($status);
        return $response;
    }
}