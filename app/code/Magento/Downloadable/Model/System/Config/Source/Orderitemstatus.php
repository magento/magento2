<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\System\Config\Source;

/**
 * Downloadable Order Item Status Source
 */
class Orderitemstatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => \Magento\Sales\Model\Order\Item::STATUS_PENDING, 'label' => __('Pending')],
            ['value' => \Magento\Sales\Model\Order\Item::STATUS_INVOICED, 'label' => __('Invoiced')]
        ];
    }
}
