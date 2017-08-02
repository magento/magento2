<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

/**
 * Class \Magento\Sales\Model\Order\Creditmemo\Total\Tax
 *
 * @since 2.0.0
 */
class Tax extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @since 2.0.0
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $shippingTaxAmount = 0;
        $baseShippingTaxAmount = 0;
        $totalTax = 0;
        $baseTotalTax = 0;
        $totalDiscountTaxCompensation = 0;
        $baseTotalDiscountTaxCompensation = 0;

        $order = $creditmemo->getOrder();

        /** @var $item \Magento\Sales\Model\Order\Creditmemo\Item */
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy() || $item->getQty() <= 0) {
                continue;
            }
            $orderItemTax = (double)$orderItem->getTaxInvoiced();
            $baseOrderItemTax = (double)$orderItem->getBaseTaxInvoiced();
            $orderItemQty = (double)$orderItem->getQtyInvoiced();

            if ($orderItemTax && $orderItemQty) {
                /**
                 * Check item tax amount
                 */

                $tax = $orderItemTax - $orderItem->getTaxRefunded();
                $baseTax = $baseOrderItemTax - $orderItem->getBaseTaxRefunded();
                $discountTaxCompensation = $orderItem->getDiscountTaxCompensationInvoiced() -
                    $orderItem->getDiscountTaxCompensationRefunded();
                $baseDiscountTaxCompensation = $orderItem->getBaseDiscountTaxCompensationInvoiced() -
                    $orderItem->getBaseDiscountTaxCompensationRefunded();
                if (!$item->isLast()) {
                    $availableQty = $orderItemQty - $orderItem->getQtyRefunded();
                    $tax = $creditmemo->roundPrice($tax / $availableQty * $item->getQty());
                    $baseTax = $creditmemo->roundPrice($baseTax / $availableQty * $item->getQty(), 'base');
                    $discountTaxCompensation =
                        $creditmemo->roundPrice($discountTaxCompensation / $availableQty * $item->getQty());
                    $baseDiscountTaxCompensation =
                        $creditmemo->roundPrice($baseDiscountTaxCompensation / $availableQty * $item->getQty(), 'base');
                }

                $item->setTaxAmount($tax);
                $item->setBaseTaxAmount($baseTax);
                $item->setDiscountTaxCompensationAmount($discountTaxCompensation);
                $item->setBaseDiscountTaxCompensationAmount($baseDiscountTaxCompensation);

                $totalTax += $tax;
                $baseTotalTax += $baseTax;
                $totalDiscountTaxCompensation += $discountTaxCompensation;
                $baseTotalDiscountTaxCompensation += $baseDiscountTaxCompensation;
            }
        }

        $isPartialShippingRefunded = false;
        if ($invoice = $creditmemo->getInvoice()) {
            //recalculate tax amounts in case if refund shipping value was changed
            if ($order->getBaseShippingAmount() && $creditmemo->getBaseShippingAmount()) {
                $taxFactor = $creditmemo->getBaseShippingAmount() / $order->getBaseShippingAmount();
                $shippingTaxAmount = $invoice->getShippingTaxAmount() * $taxFactor;
                $baseShippingTaxAmount = $invoice->getBaseShippingTaxAmount() * $taxFactor;
                $totalDiscountTaxCompensation += $invoice->getShippingDiscountTaxCompensationAmount() * $taxFactor;
                $baseTotalDiscountTaxCompensation +=
                    $invoice->getBaseShippingDiscountTaxCompensationAmnt() * $taxFactor;
                $shippingDiscountTaxCompensationAmount =
                    $invoice->getShippingDiscountTaxCompensationAmount() * $taxFactor;
                $baseShippingDiscountTaxCompensationAmount =
                    $invoice->getBaseShippingDiscountTaxCompensationAmnt() * $taxFactor;
                $shippingTaxAmount = $creditmemo->roundPrice($shippingTaxAmount);
                $baseShippingTaxAmount = $creditmemo->roundPrice($baseShippingTaxAmount, 'base');
                $totalDiscountTaxCompensation = $creditmemo->roundPrice($totalDiscountTaxCompensation);
                $baseTotalDiscountTaxCompensation = $creditmemo->roundPrice($baseTotalDiscountTaxCompensation, 'base');
                $shippingDiscountTaxCompensationAmount =
                    $creditmemo->roundPrice($shippingDiscountTaxCompensationAmount);
                $baseShippingDiscountTaxCompensationAmount =
                    $creditmemo->roundPrice($baseShippingDiscountTaxCompensationAmount, 'base');
                if ($taxFactor < 1 && $invoice->getShippingTaxAmount() > 0) {
                    $isPartialShippingRefunded = true;
                }
                $totalTax += $shippingTaxAmount;
                $baseTotalTax += $baseShippingTaxAmount;
            }
        } else {
            $orderShippingAmount = $order->getShippingAmount();
            $baseOrderShippingAmount = $order->getBaseShippingAmount();

            $baseOrderShippingRefundedAmount = $order->getBaseShippingRefunded();

            $shippingTaxAmount = 0;
            $baseShippingTaxAmount = 0;
            $shippingDiscountTaxCompensationAmount = 0;
            $baseShippingDiscountTaxCompensationAmount = 0;

            $shippingDelta = $baseOrderShippingAmount - $baseOrderShippingRefundedAmount;

            if ($shippingDelta > $creditmemo->getBaseShippingAmount()) {
                $part = $creditmemo->getShippingAmount() / $orderShippingAmount;
                $basePart = $creditmemo->getBaseShippingAmount() / $baseOrderShippingAmount;
                $shippingTaxAmount = $order->getShippingTaxAmount() * $part;
                $baseShippingTaxAmount = $order->getBaseShippingTaxAmount() * $basePart;
                $shippingDiscountTaxCompensationAmount = $order->getShippingDiscountTaxCompensationAmount() * $part;
                $baseShippingDiscountTaxCompensationAmount =
                    $order->getBaseShippingDiscountTaxCompensationAmnt() * $basePart;
                $shippingTaxAmount = $creditmemo->roundPrice($shippingTaxAmount);
                $baseShippingTaxAmount = $creditmemo->roundPrice($baseShippingTaxAmount, 'base');
                $shippingDiscountTaxCompensationAmount =
                    $creditmemo->roundPrice($shippingDiscountTaxCompensationAmount);
                $baseShippingDiscountTaxCompensationAmount =
                    $creditmemo->roundPrice($baseShippingDiscountTaxCompensationAmount, 'base');
                if ($part < 1 && $order->getShippingTaxAmount() > 0) {
                    $isPartialShippingRefunded = true;
                }
            } elseif ($shippingDelta == $creditmemo->getBaseShippingAmount()) {
                $shippingTaxAmount = $order->getShippingTaxAmount() - $order->getShippingTaxRefunded();
                $baseShippingTaxAmount = $order->getBaseShippingTaxAmount() - $order->getBaseShippingTaxRefunded();
                $shippingDiscountTaxCompensationAmount = $order->getShippingDiscountTaxCompensationAmount() -
                    $order->getShippingDiscountTaxCompensationRefunded();
                $baseShippingDiscountTaxCompensationAmount = $order->getBaseShippingDiscountTaxCompensationAmnt() -
                    $order->getBaseShippingDiscountTaxCompensationRefunded();
            }
            $totalTax += $shippingTaxAmount;
            $baseTotalTax += $baseShippingTaxAmount;
            $totalDiscountTaxCompensation += $shippingDiscountTaxCompensationAmount;
            $baseTotalDiscountTaxCompensation += $baseShippingDiscountTaxCompensationAmount;
        }

        $allowedTax = $order->getTaxInvoiced() - $order->getTaxRefunded() - $creditmemo->getTaxAmount();
        $allowedBaseTax = $order->getBaseTaxInvoiced() - $order->getBaseTaxRefunded() - $creditmemo->getBaseTaxAmount();
        $allowedDiscountTaxCompensation = $order->getDiscountTaxCompensationInvoiced() +
            $order->getShippingDiscountTaxCompensationAmount() -
            $order->getDiscountTaxCompensationRefunded() -
            $order->getShippingDiscountTaxCompensationRefunded() -
            $creditmemo->getDiscountTaxCompensationAmount() -
            $creditmemo->getShippingDiscountTaxCompensationAmount();
        $allowedBaseDiscountTaxCompensation = $order->getBaseDiscountTaxCompensationInvoiced() +
            $order->getBaseShippingDiscountTaxCompensationAmnt() -
            $order->getBaseDiscountTaxCompensationRefunded() -
            $order->getBaseShippingDiscountTaxCompensationRefunded() -
            $creditmemo->getBaseShippingDiscountTaxCompensationAmnt() -
            $creditmemo->getBaseDiscountTaxCompensationAmount();

        if ($creditmemo->isLast() && !$isPartialShippingRefunded) {
            $totalTax = $allowedTax;
            $baseTotalTax = $allowedBaseTax;
            $totalDiscountTaxCompensation = $allowedDiscountTaxCompensation;
            $baseTotalDiscountTaxCompensation = $allowedBaseDiscountTaxCompensation;
        } else {
            $totalTax = min($allowedTax, $totalTax);
            $baseTotalTax = min($allowedBaseTax, $baseTotalTax);
            $totalDiscountTaxCompensation =
                min($allowedDiscountTaxCompensation, $totalDiscountTaxCompensation);
            $baseTotalDiscountTaxCompensation =
                min($allowedBaseDiscountTaxCompensation, $baseTotalDiscountTaxCompensation);
        }

        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $totalTax);
        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTotalTax);
        $creditmemo->setDiscountTaxCompensationAmount($totalDiscountTaxCompensation);
        $creditmemo->setBaseDiscountTaxCompensationAmount($baseTotalDiscountTaxCompensation);

        $creditmemo->setShippingTaxAmount($shippingTaxAmount);
        $creditmemo->setBaseShippingTaxAmount($baseShippingTaxAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalTax + $totalDiscountTaxCompensation);
        $creditmemo->setBaseGrandTotal(
            $creditmemo->getBaseGrandTotal() +
            $baseTotalTax + $baseTotalDiscountTaxCompensation
        );
        return $this;
    }
}
