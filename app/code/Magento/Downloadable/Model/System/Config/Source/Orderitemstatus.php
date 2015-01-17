<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\System\Config\Source;

/**
 * Downloadable Order Item Status Source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Orderitemstatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
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
