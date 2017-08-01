<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Total;

/**
 * Class \Magento\Sales\Model\Order\Invoice\Total\Cost
 *
 * @since 2.0.0
 */
class Cost extends AbstractTotal
{
    /**
     * Collect total cost of invoiced items
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     * @since 2.0.0
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $baseInvoiceTotalCost = 0;
        foreach ($invoice->getAllItems() as $item) {
            if (!$item->getHasChildren()) {
                $baseInvoiceTotalCost += $item->getBaseCost() * $item->getQty();
            }
        }
        $invoice->setBaseCost($baseInvoiceTotalCost);
        return $this;
    }
}
