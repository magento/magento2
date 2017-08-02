<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter Queue statuses option array
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Newsletter\Model\Queue\Options;

/**
 * Class \Magento\Newsletter\Model\Queue\Options\Status
 *
 * @since 2.0.0
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return statuses option array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        return [
            \Magento\Newsletter\Model\Queue::STATUS_SENT => __('Sent'),
            \Magento\Newsletter\Model\Queue::STATUS_CANCEL => __('Cancelled'),
            \Magento\Newsletter\Model\Queue::STATUS_NEVER => __('Not Sent'),
            \Magento\Newsletter\Model\Queue::STATUS_SENDING => __('Sending'),
            \Magento\Newsletter\Model\Queue::STATUS_PAUSE => __('Paused')
        ];
    }
}
