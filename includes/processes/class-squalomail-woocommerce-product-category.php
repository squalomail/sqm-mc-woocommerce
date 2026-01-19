<?php

/**
 * Created by Vextras.
 *
 * Name: Ryan Hungate
 * Email: ryan@vextras.com
 * Date: 7/15/16
 * Time: 11:42 AM
 */
class SqualoMail_WooCommerce_Product_Category extends Squalomail_Woocommerce_Job
{
    public $id;
    protected $store_id;
    protected $api;
    protected $service;
    protected $mode = 'update_or_create';

    /**
     * SqualoMail_WooCommerce_Category constructor.
     * @param null|int $id
     */
    public function __construct($id = null)
    {
        $this->setId($id);
    }

    /**
     * @param null $id
     * @return SqualoMail_WooCommerce_Category
     */
    public function setId($id)
    {
        if (!empty($id)) {
            $this->id = $id;
        }
    }

    /**
     * @return $this
     */
    public function createModeOnly()
    {
        $this->mode = 'create';
        return $this;
    }

    /**
     * @return $this
     */
    public function updateModeOnly()
    {
        $this->mode = 'update';

        return $this;
    }

    /**
     * @return $this
     */
    public function updateOrCreateMode()
    {
        $this->mode = 'update_or_create';

        return $this;
    }

    /**
     * @return bool
     */
    public function handle()
    {
        $this->process();

        return false;
    }

    /**
     * @return bool|SqualoMail_WooCommerce_Product
     */
    public function process()
    {
        if (empty($this->id)) {
            return false;
        }

        if (!squalomail_is_configured()) {
            squalomail_debug(get_called_class(), 'Squalo is not configured properly');
            return false;
        }

        $method = "no action";

        try {

            if (!($product_category = get_term($this->id))) {
                return false;
            }

            try {
                // pull the category from Squalo first to see what method we need to call next.
                $squalomail_category = $this->api()->getStoreCategory($this->store_id, $this->id, true);
            } catch (\Exception $e) {
                if ($e instanceof SqualoMail_WooCommerce_RateLimitError) {
                    throw $e;
                }
                $squalomail_category = false;
            }

            // depending on if it's existing or not - we change the method call
            $method = $squalomail_category ? 'updateStoreCategory' : 'addStoreCategory';

            // if the mode set is "create" and the category is in Squalo - just return the category.
            if ($this->mode === 'create' && !empty($squalomail_category)) {
                return $squalomail_category;
            }

            // if the mode is set to "update" and the category is not currently in Squalo - skip it.
            if ($this->mode === 'update' && empty($squalomail_category)) {
                return false;
            }

            $category = $this->transformer()->transform($product_category);
            
            squalomail_debug('product_category_submit.debug', "#{$this->id}", $category);

            if (!$category->getId()) {
                squalomail_log('product_category_submit.warning', "{$method} :: category #{$this->id} was invalid.");
                return false;
            }

            // either updating or creating the category
            $this->api()->{$method}($this->store_id, $category, false);

            squalomail_log('product_category_submit.success', "{$method} :: #{$category->getId()}");

            update_option('squalomail-woocommerce-last_product_category_updated', $category->getId());

            return $category;

        } catch (SqualoMail_WooCommerce_RateLimitError $e) {
            sleep(3);
            squalomail_error('product_category_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            $this->applyRateLimitedScenario();
            throw $e;
        } catch (SqualoMail_WooCommerce_ServerError $e) {
            squalomail_error('product_category_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            throw $e;
        } catch (SqualoMail_WooCommerce_Error $e) {
            squalomail_log('product_category_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            throw $e;
        } catch (Exception $e) {
            squalomail_log('product_category_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            throw $e;
        }
        catch (\Error $e) {
            squalomail_log('product_category_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            throw $e;
        }

        return false;
    }

    /**
     * @return SqualoMail_WooCommerce_SqualoMailApi
     */
    public function api()
    {
        if (is_null($this->api)) {

            $this->store_id = squalomail_get_store_id();
            $options = get_option('squalomail-woocommerce', array());

            if (!empty($this->store_id) && is_array($options) && isset($options['squalomail_api_key'])) {
                return $this->api = new SqualoMail_WooCommerce_SqualoMailApi($options['squalomail_api_key']);
            }

            throw new \RuntimeException('The Squalo API is not currently configured!');
        }

        return $this->api;
    }

    /**
     * @return SqualoMail_WooCommerce_Transform_Categories
     */
    public function transformer()
    {
        if (is_null($this->service)) {
            return $this->service = new SqualoMail_WooCommerce_Transform_Categories();
        }

        return $this->service;
    }
}
