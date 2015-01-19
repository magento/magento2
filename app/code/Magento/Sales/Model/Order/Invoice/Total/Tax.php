<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Total;

class Tax extends AbstractTotal
{
    /**
     * Collect invoice tax amount
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $totalTax = 0;
        $baseTotalTax = 0;
        $totalHiddenTax = 0;
        $baseTotalHiddenTax = 0;

        $order = $invoice->getOrder();

        /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemQty = $orderItem->getQtyOrdered();

            if (($orderItem->getTaxAmount() || $orderItem->getHiddenTaxAmount()) && $orderItemQty) {
                if ($item->getOrderItem()->isDummy() || $item->getQty() <= 0) {
                    continue;
                }

                /**
                 * Resolve rounding problems
                 */
                $tax = $orderItem->getTaxAmount() - $orderItem->getTaxInvoiced();
                $baseTax = $orderItem->getBaseTaxAmount() - $orderItem->getBaseTaxInvoiced();
                $hiddenTax = $orderItem->getHiddenTaxAmount() - $orderItem->getHiddenTaxInvoiced();
                $baseHiddenTax = $orderItem->getBaseHiddenTaxAmount() - $orderItem->getBaseHiddenTaxInvoiced();
                if (!$item->isLast()) {
                    $availableQty = $orderItemQty - $orderItem->getQtyInvoiced();
                    $tax = $invoice->roundPrice($tax / $availableQty * $item->getQty());
                    $baseTax = $invoice->roundPrice($baseTax / $availableQty * $item->getQty(), 'base');
                    $hiddenTax = $invoice->roundPrice($hiddenTax / $availableQty * $item->getQty());
                    $baseHiddenTax = $invoice->roundPrice($baseHiddenTax / $availableQty * $item->getQty(), 'base');
                }

                $item->setTaxAmount($tax);
                $item->setBaseTaxAmount($baseTax);
                $item->setHiddenTaxAmount($hiddenTax);
                $item->setBaseHiddenTaxAmount($baseHiddenTax);

                $totalTax += $tax;
                $baseTotalTax += $baseTax;
                $totalHiddenTax += $hiddenTax;
                $baseTotalHiddenTax += $baseHiddenTax;
            }
        }

        if ($this->_canIncludeShipping($invoice)) {
            $totalTax += $order->getShippingTaxAmount();
            $baseTotalTax += $order->getBaseShippingTaxAmount();
            $totalHiddenTax += $order->getShippingHiddenTaxAmount();
            $baseTotalHiddenTax += $order->getBaseShippingHiddenTaxAmnt();
            $invoice->setShippingTaxAmount($order->getShippingTaxAmount());
            $invoice->setBaseShippingTaxAmount($order->getBaseShippingTaxAmount());
            $invoice->setShippingHiddenTaxAmount($order->getShippingHiddenTaxAmount());
            $invoice->setBaseShippingHiddenTaxAmnt($order->getBaseShippingHiddenTaxAmnt());
        }
        $allowedTax = $order->getTaxAmount() - $order->getTaxInvoiced();
        $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced();
        $allowedHiddenTax = $order->getHiddenTaxAmount() +
            $order->getShippingHiddenTaxAmount() -
            $order->getHiddenTaxInvoiced() -
            $order->getShippingHiddenTaxInvoiced();
        $allowedBaseHiddenTax = $order->getBaseHiddenTaxAmount() +
            $order->getBaseShippingHiddenTaxAmnt() -
            $order->getBaseHiddenTaxInvoiced() -
            $order->getBaseShippingHiddenTaxInvoiced();

        if ($invoice->isLast()) {
            $totalTax = $allowedTax;
            $baseTotalTax = $allowedBaseTax;
            $totalHiddenTax = $allowedHiddenTax;
            $baseTotalHiddenTax = $allowedBaseHiddenTax;
        } else {
            $totalTax = min($allowedTax, $totalTax);
            $baseTotalTax = min($allowedBaseTax, $baseTotalTax);
            $totalHiddenTax = min($allowedHiddenTax, $totalHiddenTax);
            $baseTotalHiddenTax = min($allowedBaseHiddenTax, $baseTotalHiddenTax);
        }

        $invoice->setTaxAmount($totalTax);
        $invoice->setBaseTaxAmount($baseTotalTax);
        $invoice->setHiddenTaxAmount($totalHiddenTax);
        $invoice->setBaseHiddenTaxAmount($baseTotalHiddenTax);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalTax + $totalHiddenTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalTax + $baseTotalHiddenTax);

        return $this;
    }

    /**
     * Check if shipping tax calculation can be included to current invoice
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return bool
     */
    protected function _canIncludeShipping($invoice)
    {
        $includeShippingTax = true;
        /**
         * Check shipping amount in previous invoices
         */
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ($previousInvoice->getShippingAmount() && !$previousInvoice->isCanceled()) {
                $includeShippingTax = false;
            }
        }
        return $includeShippingTax;
    }
}
