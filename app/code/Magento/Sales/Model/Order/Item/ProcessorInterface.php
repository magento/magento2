<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model\Order\Item;

use Magento\Sales\Api\Data\OrderItemInterface;

interface ProcessorInterface
{
    /**
     * Convert order item to buy request object
     *
     * @param OrderItemInterface $orderItem
     * @return \Magento\Framework\DataObject
     */
    public function convertToBuyRequest(OrderItemInterface $orderItem);

    /**
     * Process order item product options
     *
     * @param OrderItemInterface $orderItem
     * @return OrderItemInterface
     */
    public function processOptions(OrderItemInterface $orderItem);
}
