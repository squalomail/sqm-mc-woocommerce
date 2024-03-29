<?php

class SqualoMail_WooCommerce_Privacy
{
    /**
     * Privacy policy
     */
    public function privacy_policy()
    {
        if (function_exists( 'wp_add_privacy_policy_content')) {
            $content = sprintf(/* translators: %s - Squalomail Privacy Policy URL. */
                __( 'When shopping, we keep a record of your email and the cart contents for up to 30 days on our server. This record is kept to repopulate the contents of your cart if you switch devices or needed to come back another day. Read our privacy policy <a href="%s">here</a>.', 'squalomail-for-woocommerce' ),
                'https://squalomail.com/legal/privacy/'
                
            );
            wp_add_privacy_policy_content('SqualoMail for WooCommerce', wp_kses_post(wpautop($content, false)));
        }
    }

    /**
     * @param array $exporters
     * @return mixed
     */
    public function register_exporter($exporters)
    {
        $exporters['squalomail-woocommerce'] = array(
            'exporter_friendly_name' => __('SqualoMail for WooCommerce'),
            'callback'               => array($this, 'export'),
        );
        return $exporters;
    }

    /**
     * @param array $erasers
     * @return mixed
     */
    public function register_eraser($erasers)
    {
        $erasers['squalomail-woocommerce'] = array(
            'eraser_friendly_name' => __('SqualoMail for WooCommerce'),
            'callback'               => array($this, 'erase'),
        );
        return $erasers;
    }

    /**
     * @param $email_address
     * @param int $page
     * @return array
     */
    public function export($email_address, $page = 1)
    {
        global $wpdb;

        $uid = squalomail_hash_trim_lower($email_address);

        $data = array();

        if (get_site_option('squalomail_woocommerce_db_squalomail_carts', false)) {
            $table = "{$wpdb->prefix}squalomail_carts";
            $statement = "SELECT * FROM $table WHERE id = %s";
            $sql = $wpdb->prepare($statement, $uid);

            if (($saved_cart = $wpdb->get_row($sql)) && !empty($saved_cart)) {
                $data = array('name' => __('Email Address'), 'value' => $email_address);
            }
        }

        // If nothing found, return nothing
        if (is_array($data) && (count($data) < 1)) {
            return (array('data' => array(), 'done' => true));
        }

        return array(
            'data' => array(
                array(
                    'group_id'    => 'squalomail_cart',
                    'group_label' => __( 'SqualoMail Shopping Cart Data', 'squalomail-for-woocommerce' ),
                    'item_id'     => 'mailing-shopping-cart-1',
                    'data'        => array(
                        array(
                            'name'  => __( 'User ID', 'squalomail-for-woocommerce' ),
                            'value' => $uid,
                        ),
                        $data, // this is already an associative array with name and value keys
                    )
                )
            ),
            'done' => true,
        );
    }

    public function erase($email_address, $page = 1)
    {
        global $wpdb;

        $uid = squalomail_hash_trim_lower($email_address);
        $count = 0;

        if (get_site_option('squalomail_woocommerce_db_squalomail_carts', false)) {
            $table = "{$wpdb->prefix}squalomail_carts";
            $sql = $wpdb->prepare("DELETE FROM $table WHERE id = %s", $uid);
            $count = $wpdb->query($sql);
        }

        return array(
            'items_removed' => (int) $count,
            'items_retained' => false,
            'messages' => array(),
            'done' => true,
        );
    }
}
