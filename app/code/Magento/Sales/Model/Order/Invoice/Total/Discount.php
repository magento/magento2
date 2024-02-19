<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Invoice;

/**
 * Discount invoice
 */
class Discount extends AbstractTotal
{
    /**
     * Collect invoice
     *
     * @param Invoice $invoice
     * @return $this
     */
    public function collect(Invoice $invoice)
    {
        $invoice->setDiscountAmount(0);
        $invoice->setBaseDiscountAmount(0);

        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;

        /**
         * Checking if shipping discount was added in previous invoices.
         * So basically if we have invoice with positive discount and it
         * was not canceled we don't add shipping discount to this one.
         */
        if ($this->isShippingDiscount($invoice)) {
            $totalDiscountAmount = $totalDiscountAmount + $invoice->getOrder()->getShippingDiscountAmount();
            $baseTotalDiscountAmount = $baseTotalDiscountAmount +
                $invoice->getOrder()->getBaseShippingDiscountAmount();
        }

        /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemDiscount = (double)$orderItem->getDiscountAmount();
            $baseOrderItemDiscount = (double)$orderItem->getBaseDiscountAmount();
            $orderItemQty = $orderItem->getQtyOrdered();

            if ($orderItemDiscount && $orderItemQty) {
                /**
                 * Resolve rounding problems
                 */
                $discount = $orderItemDiscount - $orderItem->getDiscountInvoiced();
                $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountInvoiced();
                if (!$item->isLast()) {
                    $activeQty = $orderItemQty - $orderItem->getQtyInvoiced();
                    $discount = $invoice->roundPrice($discount / $activeQty * $item->getQty(), 'regular', true);
                    $baseDiscount = $invoice->roundPrice($baseDiscount / $activeQty * $item->getQty(), 'base', true);
                }

                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($baseDiscount);

                $totalDiscountAmount += $discount;
                $baseTotalDiscountAmount += $baseDiscount;
            }
        }

        $invoice->setDiscountAmount(-$totalDiscountAmount);
        $invoice->setBaseDiscountAmount(-$baseTotalDiscountAmount);

        $grandTotal = abs($invoice->getGrandTotal() - $totalDiscountAmount) < 0.0001
            ? 0 : $invoice->getGrandTotal() - $totalDiscountAmount;
        $baseGrandTotal = abs($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount) < 0.0001
            ? 0 : $invoice->getBaseGrandTotal() - $baseTotalDiscountAmount;
        $invoice->setGrandTotal($grandTotal);
        $invoice->setBaseGrandTotal($baseGrandTotal);
        return $this;
    }

    /**
     * Checking if shipping discount was added in previous invoices.
     *
     * @param Invoice $invoice
     * @return bool
     */
    private function isShippingDiscount(Invoice $invoice): bool
    {
        $addShippingDiscount = true;
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getDiscountAmount()) {
                $addShippingDiscount = false;
            }
        }
        return $addShippingDiscount;
    }
}
