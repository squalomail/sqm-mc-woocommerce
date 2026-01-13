<?php

/**
 * Created by Vextras.
 *
 * Name: Ryan Hungate
 * Email: ryan@vextras.com
 * Date: 7/14/16
 * Time: 10:57 AM
 */
class SqualoMail_WooCommerce_Process_Categories extends SqualoMail_WooCommerce_Abstract_Sync
{
    /**
     * @var string
     */
    protected $action = 'squalomail_woocommerce_process_categories';

    public static function push()
    {
        $job = new SqualoMail_WooCommerce_Process_Categories();
        $job->flagStartSync();
        squalomail_handle_or_queue($job, 0);
    }


    /**
     * @return string
     */
    public function getResourceType()
    {
        return 'categories';
    }

    /**
     * Called after all the categories have been iterated and processed into Squalo
     */
    protected function complete()
    {
        squalomail_log('categories_sync.completed', 'Done with the categories queuing');

        // add a timestamp for the categories sync completion
        $this->setResourceCompleteTime();
    }
}
