<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\ViewModel\Order;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Resolves the canceled quantity to display for an order line item in the Admin order view.
 */
class CanceledItemQty implements ArgumentInterface
{
    /**
     * Retrieve the canceled quantity to display for the order line item.
     *
     * @param OrderItemInterface $item
     * @return float
     */
    public function getDisplayCanceledQty(OrderItemInterface $item): float
    {
        $order = $item->getOrder();
        if ($order
            && (float) $order->getTotalCanceled() > 0.0
            && (float) $item->getQtyShipped() >= (float) $item->getQtyOrdered()
        ) {
            $canceled = (float) $item->getQtyOrdered() - (float) $item->getQtyInvoiced();
            if ($canceled > 0) {
                return $canceled;
            }
        }

        return (float) $item->getQtyCanceled();
    }
}
