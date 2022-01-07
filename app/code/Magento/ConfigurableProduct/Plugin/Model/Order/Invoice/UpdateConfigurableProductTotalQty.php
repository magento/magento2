<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Model\Order\Invoice;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Sales\Model\Order\Invoice;

/**
 * Update total quantity for configurable product invoice
 */
class UpdateConfigurableProductTotalQty
{
    /**
     * Set total quantity for configurable product invoice
     *
     * @param Invoice $invoice
     * @param float $totalQty
     * @return float
     */
    public function beforeSetTotalQty(
        Invoice $invoice,
        float $totalQty
    ): float {
        $order = $invoice->getOrder();
        $productTotalQty = 0;
        $hasConfigurableProduct = false;
        foreach ($order->getAllItems() as $orderItem) {
            if ($orderItem->getParentItemId() === null &&
                $orderItem->getProductType() == Configurable::TYPE_CODE
            ) {
                $hasConfigurableProduct =  true;
                continue;
            }
            $productTotalQty += (float) $orderItem->getQtyOrdered();
        }
        return $hasConfigurableProduct ? $productTotalQty : $totalQty;
    }
}
