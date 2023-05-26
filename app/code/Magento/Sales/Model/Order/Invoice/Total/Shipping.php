<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice\Total;

/**
 * Order invoice shipping total calculation model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Shipping extends AbstractTotal
{
    /**
     * Collect shipping total
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $orderShippingAmount = $invoice->getOrder()->getShippingAmount();
        $baseOrderShippingAmount = $invoice->getOrder()->getBaseShippingAmount();
        $shippingInclTax = $invoice->getOrder()->getShippingInclTax();
        $baseShippingInclTax = $invoice->getOrder()->getBaseShippingInclTax();
        if ($orderShippingAmount) {
            /**
             * Check shipping amount in previous invoices
             */
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                if ($previousInvoice->getShippingAmount() !== null && !$previousInvoice->isCanceled()) {
                    return $this;
                }
            }
            $invoice->setShippingAmount($orderShippingAmount);
            $invoice->setBaseShippingAmount($baseOrderShippingAmount);
            $invoice->setShippingInclTax($shippingInclTax);
            $invoice->setBaseShippingInclTax($baseShippingInclTax);

            $invoice->setGrandTotal($invoice->getGrandTotal() + $orderShippingAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseOrderShippingAmount);
        }
        return $this;
    }
}
