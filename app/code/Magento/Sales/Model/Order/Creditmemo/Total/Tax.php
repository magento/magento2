<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Order\Creditmemo\Total;

class Tax extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $shippingTaxAmount = 0;
        $baseShippingTaxAmount = 0;
        $totalTax = 0;
        $baseTotalTax = 0;
        $totalHiddenTax = 0;
        $baseTotalHiddenTax = 0;

        $order = $creditmemo->getOrder();

        /** @var $item \Magento\Sales\Model\Order\Creditmemo\Item */
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
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
                $baseTax = $baseOrderItemTax - $orderItem->getTaxRefunded();
                $hiddenTax = $orderItem->getHiddenTaxAmount() - $orderItem->getHiddenTaxRefunded();
                $baseHiddenTax = $orderItem->getBaseHiddenTaxAmount() - $orderItem->getBaseHiddenTaxRefunded();
                if (!$item->isLast()) {
                    $availableQty = $orderItemQty - $orderItem->getQtyRefunded();
                    $tax = $creditmemo->roundPrice($tax / $availableQty * $item->getQty());
                    $baseTax = $creditmemo->roundPrice($baseTax / $availableQty * $item->getQty(), 'base');
                    $hiddenTax = $creditmemo->roundPrice($hiddenTax / $availableQty * $item->getQty());
                    $baseHiddenTax = $creditmemo->roundPrice($baseHiddenTax / $availableQty * $item->getQty(), 'base');
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

        if ($invoice = $creditmemo->getInvoice()) {
            //recalculate tax amounts in case if refund shipping value was changed
            if ($order->getBaseShippingAmount() && $creditmemo->getBaseShippingAmount()) {
                $taxFactor = $creditmemo->getBaseShippingAmount() / $order->getBaseShippingAmount();
                $shippingTaxAmount = $invoice->getShippingTaxAmount() * $taxFactor;
                $baseShippingTaxAmount = $invoice->getBaseShippingTaxAmount() * $taxFactor;
                $totalHiddenTax += $invoice->getShippingHiddenTaxAmount() * $taxFactor;
                $baseTotalHiddenTax += $invoice->getBaseShippingHiddenTaxAmnt() * $taxFactor;
                $shippingHiddenTaxAmount = $invoice->getShippingHiddenTaxAmount() * $taxFactor;
                $baseShippingHiddenTaxAmount = $invoice->getBaseShippingHiddenTaxAmnt() * $taxFactor;
                $shippingTaxAmount = $creditmemo->roundPrice($shippingTaxAmount);
                $baseShippingTaxAmount = $creditmemo->roundPrice($baseShippingTaxAmount, 'base');
                $totalHiddenTax = $creditmemo->roundPrice($totalHiddenTax);
                $baseTotalHiddenTax = $creditmemo->roundPrice($baseTotalHiddenTax, 'base');
                $shippingHiddenTaxAmount = $creditmemo->roundPrice($shippingHiddenTaxAmount);
                $baseShippingHiddenTaxAmount = $creditmemo->roundPrice($baseShippingHiddenTaxAmount, 'base');
                $totalTax += $shippingTaxAmount;
                $baseTotalTax += $baseShippingTaxAmount;
            }
        } else {
            $orderShippingAmount = $order->getShippingAmount();
            $baseOrderShippingAmount = $order->getBaseShippingAmount();

            $baseOrderShippingRefundedAmount = $order->getBaseShippingRefunded();

            $shippingTaxAmount = 0;
            $baseShippingTaxAmount = 0;
            $shippingHiddenTaxAmount = 0;
            $baseShippingHiddenTaxAmount = 0;

            $shippingDelta = $baseOrderShippingAmount - $baseOrderShippingRefundedAmount;

            if ($shippingDelta > $creditmemo->getBaseShippingAmount()) {
                $part = $creditmemo->getShippingAmount() / $orderShippingAmount;
                $basePart = $creditmemo->getBaseShippingAmount() / $baseOrderShippingAmount;
                $shippingTaxAmount = $order->getShippingTaxAmount() * $part;
                $baseShippingTaxAmount = $order->getBaseShippingTaxAmount() * $basePart;
                $shippingHiddenTaxAmount = $order->getShippingHiddenTaxAmount() * $part;
                $baseShippingHiddenTaxAmount = $order->getBaseShippingHiddenTaxAmnt() * $basePart;
                $shippingTaxAmount = $creditmemo->roundPrice($shippingTaxAmount);
                $baseShippingTaxAmount = $creditmemo->roundPrice($baseShippingTaxAmount, 'base');
                $shippingHiddenTaxAmount = $creditmemo->roundPrice($shippingHiddenTaxAmount);
                $baseShippingHiddenTaxAmount = $creditmemo->roundPrice($baseShippingHiddenTaxAmount, 'base');
            } elseif ($shippingDelta == $creditmemo->getBaseShippingAmount()) {
                $shippingTaxAmount = $order->getShippingTaxAmount() - $order->getShippingTaxRefunded();
                $baseShippingTaxAmount = $order->getBaseShippingTaxAmount() - $order->getBaseShippingTaxRefunded();
                $shippingHiddenTaxAmount = $order->getShippingHiddenTaxAmount() -
                    $order->getShippingHiddenTaxRefunded();
                $baseShippingHiddenTaxAmount = $order->getBaseShippingHiddenTaxAmnt() -
                    $order->getBaseShippingHiddenTaxRefunded();
            }
            $totalTax += $shippingTaxAmount;
            $baseTotalTax += $baseShippingTaxAmount;
            $totalHiddenTax += $shippingHiddenTaxAmount;
            $baseTotalHiddenTax += $baseShippingHiddenTaxAmount;
        }

        $allowedTax = $order->getTaxAmount() - $order->getTaxRefunded();
        $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxRefunded();
        $allowedHiddenTax = $order->getHiddenTaxAmount() +
            $order->getShippingHiddenTaxAmount() -
            $order->getHiddenTaxRefunded() -
            $order->getShippingHiddenTaxRefunded();
        $allowedBaseHiddenTax = $order->getBaseHiddenTaxAmount() +
            $order->getBaseShippingHiddenTaxAmnt() -
            $order->getBaseHiddenTaxRefunded() -
            $order->getBaseShippingHiddenTaxRefunded();


        $totalTax = min($allowedTax, $totalTax);
        $baseTotalTax = min($allowedBaseTax, $baseTotalTax);
        $totalHiddenTax = min($allowedHiddenTax, $totalHiddenTax);
        $baseTotalHiddenTax = min($allowedBaseHiddenTax, $baseTotalHiddenTax);

        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $totalTax);
        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTotalTax);
        $creditmemo->setHiddenTaxAmount($totalHiddenTax);
        $creditmemo->setBaseHiddenTaxAmount($baseTotalHiddenTax);

        $creditmemo->setShippingTaxAmount($shippingTaxAmount);
        $creditmemo->setBaseShippingTaxAmount($baseShippingTaxAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalTax + $totalHiddenTax);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalTax + $baseTotalHiddenTax);
        return $this;
    }
}
