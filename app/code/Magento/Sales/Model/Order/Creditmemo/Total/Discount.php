<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

class Discount extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setDiscountAmount(0);
        $creditmemo->setBaseDiscountAmount(0);

        $order = $creditmemo->getOrder();

        $totalDiscountAmount = 0;
        $baseTotalDiscountAmount = 0;

        /**
         * Calculate how much shipping discount should be applied
         * basing on how much shipping should be refunded.
         */
        $baseShippingAmount = $this->getBaseShippingAmount($creditmemo);
        if ($baseShippingAmount) {
            $baseShippingDiscount = $baseShippingAmount *
                $order->getBaseShippingDiscountAmount() /
                $order->getBaseShippingAmount();
            $shippingDiscount = $order->getShippingAmount() * $baseShippingDiscount / $order->getBaseShippingAmount();
            $totalDiscountAmount = $totalDiscountAmount + $shippingDiscount;
            $baseTotalDiscountAmount = $baseTotalDiscountAmount + $baseShippingDiscount;
        }

        /** @var $item \Magento\Sales\Model\Order\Invoice\Item */
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();

            if ($orderItem->isDummy()) {
                continue;
            }

            $orderItemDiscount = (double)$orderItem->getDiscountInvoiced();
            $baseOrderItemDiscount = (double)$orderItem->getBaseDiscountInvoiced();
            $orderItemQty = $orderItem->getQtyInvoiced();

            if ($orderItemDiscount && $orderItemQty) {
                $discount = $orderItemDiscount - $orderItem->getDiscountRefunded();
                $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountRefunded();
                if (!$item->isLast()) {
                    $availableQty = $orderItemQty - $orderItem->getQtyRefunded();
                    $discount = $creditmemo->roundPrice($discount / $availableQty * $item->getQty(), 'regular', true);
                    $baseDiscount = $creditmemo->roundPrice(
                        $baseDiscount / $availableQty * $item->getQty(),
                        'base',
                        true
                    );
                }

                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($baseDiscount);

                $totalDiscountAmount += $discount;
                $baseTotalDiscountAmount += $baseDiscount;
            }
        }

        $creditmemo->setDiscountAmount(-$totalDiscountAmount);
        $creditmemo->setBaseDiscountAmount(-$baseTotalDiscountAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() - $totalDiscountAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseTotalDiscountAmount);
        return $this;
    }

    /**
     * Get base shipping amount.
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return float
     */
    private function getBaseShippingAmount(\Magento\Sales\Model\Order\Creditmemo $creditmemo): float
    {
        $baseShippingAmount = (float)$creditmemo->getBaseShippingAmount();

        if (!$baseShippingAmount) {
            $baseShippingInclTax = (float)$creditmemo->getBaseShippingInclTax();
            $baseShippingTaxAmount = (float)$creditmemo->getBaseShippingTaxAmount();
            $baseShippingAmount = $baseShippingInclTax - $baseShippingTaxAmount;
        }

        return $baseShippingAmount;
    }
}
