<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesInventory\Model\Order\Creditmemo;


use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\CreditmemoItemInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Interface Qty Value Interface
 */
interface QtyValueInterface
{
    /**
     * @param CreditmemoItemInterface $creditmemoItem
     * @param CreditmemoInterface $creditmemo
     * @param OrderItemInterface|null $parentOrderItem
     * @param string|null $priceType
     * @return float
     */
    public function get(
        CreditmemoItemInterface $creditmemoItem,
        CreditmemoInterface $creditmemo,
        OrderItemInterface $parentOrderItem = null,
        $priceType = null
    );
}
