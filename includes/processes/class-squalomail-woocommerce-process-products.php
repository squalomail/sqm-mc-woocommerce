<?php

/**
 * Created by Vextras.
 *
 * Name: Ryan Hungate
 * Email: ryan@vextras.com
 * Date: 7/14/16
 * Time: 10:57 AM
 */
class SqualoMail_WooCommerce_Process_Products extends SqualoMail_WooCommerce_Abstract_Sync
{
    /**
     * @var string
     */
    protected $action = 'squalomail_woocommerce_process_products';

    public static function push()
    {
        $job = new SqualoMail_WooCommerce_Process_Products();
        $job->flagStartSync();
        squalomail_handle_or_queue($job, 0);
    }


    /**
     * @return string
     */
    public function getResourceType()
    {
        return 'products';
    }

    /**
     * Called after all the products have been iterated and processed into Squalo
     */
    protected function complete()
    {
        squalomail_log('product_sync.completed', 'Done with the product queuing');

        // add a timestamp for the product sync completion
        $this->setResourceCompleteTime();
    }
}
