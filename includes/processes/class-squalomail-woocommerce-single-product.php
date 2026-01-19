<?php

/**
 * Created by Vextras.
 *
 * Name: Ryan Hungate
 * Email: ryan@vextras.com
 * Date: 7/15/16
 * Time: 11:42 AM
 */
class SqualoMail_WooCommerce_Single_Product extends Squalomail_Woocommerce_Job
{
    public $id;
    public $fallback_title;
    protected $store_id;
    protected $api;
    protected $service;
    protected $mode = 'update_or_create';
    protected $order_item = null;

    /**
     * SqualoMail_WooCommerce_Single_product constructor.
     * @param null|int $id
     */
    public function __construct($id = null, $fallback_title = null)
    {
        $this->setId($id);
        $this->setFallbackTitle($fallback_title);
    }

    /**
     * @param null $id
     * @return SqualoMail_WooCommerce_Single_Product
     */
    public function setId($id)
    {
        if (!empty($id)) {
            $this->id = $id instanceof WP_Post ? $id->ID : $id;
        }
    }

    /**
     * @param $title
     * @return $this
     */
    public function setFallbackTitle($title)
    {
        $this->fallback_title = $title;

        return $this;
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
     * @param SqualoMail_WooCommerce_LineItem $item
     * @return $this
     */
    public function fromOrderItem(SqualoMail_WooCommerce_LineItem $item)
    {
        $this->order_item = $item;
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

            if (!($product_post = get_post($this->id))) {
                return false;
            }

            try {
                // pull the product from Squalo first to see what method we need to call next.
                $squalomail_product = $this->api()->getStoreProduct($this->store_id, $this->id, true);
            } catch (\Exception $e) {
                if ($e instanceof SqualoMail_WooCommerce_RateLimitError) {
                    throw $e;
                }
                $squalomail_product = false;
            }

            // depending on if it's existing or not - we change the method call
            $method = $squalomail_product ? 'updateStoreProduct' : 'addStoreProduct';

            // if the mode set is "create" and the product is in Squalo - just return the product.
            if ($this->mode === 'create' && !empty($squalomail_product)) {
                return $squalomail_product;
            }

            // if the mode is set to "update" and the product is not currently in Squalo - skip it.
            if ($this->mode === 'update' && empty($squalomail_product)) {
                return false;
            }

            // if qe instructed this job to build from the order item, let's do that instead of the product post.
            if ($this->order_item) {
                squalomail_debug('product_submit.debug', 'using order item', array('item' => $this->order_item));
                $product = $this->transformer()->fromOrderItem($this->order_item);
            } else {
                $product = $this->transformer()->transform($product_post, $this->fallback_title);
            }

            if (empty($product->getTitle()) && !empty($this->fallback_title)) {
                $product->setTitle($this->fallback_title);
            }

            squalomail_debug('product_submit.debug', "#{$this->id}", $product->toArray());

            if (!$product->getId() || !$product->getTitle()) {
                squalomail_log('product_submit.warning', "{$method} :: post #{$this->id} was invalid.");
                return false;
            }

            // either updating or creating the product
            $this->api()->{$method}($this->store_id, $product, false);

            squalomail_log('product_submit.success', "{$method} :: #{$product->getId()}");

            update_option('squalomail-woocommerce-last_product_updated', $product->getId());

            return $product;

        } catch (SqualoMail_WooCommerce_RateLimitError $e) {
            sleep(3);
            squalomail_error('product_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            $this->applyRateLimitedScenario();
            throw $e;
        } catch (SqualoMail_WooCommerce_ServerError $e) {
            squalomail_error('product_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            throw $e;
        } catch (SqualoMail_WooCommerce_Error $e) {
            squalomail_log('product_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            throw $e;
        } catch (Exception $e) {
            squalomail_log('product_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
            throw $e;
        }
        catch (\Error $e) {
            squalomail_log('product_submit.error', squalomail_error_trace($e, "{$method} :: #{$this->id}"));
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
     * @return SqualoMail_WooCommerce_Transform_Products
     */
    public function transformer()
    {
        if (is_null($this->service)) {
            return $this->service = new SqualoMail_WooCommerce_Transform_Products();
        }

        return $this->service;
    }
}
