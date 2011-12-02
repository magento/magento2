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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Sales_Model_Order_Creditmemo_Total_Tax extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $shippingTaxAmount     = 0;
        $baseShippingTaxAmount = 0;
        $totalTax              = 0;
        $baseTotalTax          = 0;
        $totalHiddenTax        = 0;
        $baseTotalHiddenTax    = 0;

        $order = $creditmemo->getOrder();

        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }
            $orderItem        = $item->getOrderItem();
            $orderItemTax     = $item->getOrderItem()->getTaxAmount();
            $baseOrderItemTax = $item->getOrderItem()->getBaseTaxAmount();
            $orderItemQty     = $item->getOrderItem()->getQtyOrdered();

            if ($orderItemTax && $orderItemQty) {
                /**
                 * Check item tax amount
                 */


                if ($item->isLast()) {
                    $tax            = $orderItemTax - $item->getOrderItem()->getTaxRefunded()
                        - $item->getOrderItem()->getTaxCanceled();
                    $baseTax        = $baseOrderItemTax - $item->getOrderItem()->getTaxRefunded()
                        - $item->getOrderItem()->getTaxCanceled();
                    $hiddenTax      = $orderItem->getHiddenTaxAmount() - $orderItem->getHiddenTaxRefunded()
                        - $item->getOrderItem()->getHiddenTaxCanceled();
                    $baseHiddenTax  = $orderItem->getBaseHiddenTaxAmount() - $orderItem->getBaseHiddenTaxRefunded()
                        - $item->getOrderItem()->getHiddenTaxCanceled();

                }
                else {
                    $tax            = $orderItemTax*$item->getQty()/$orderItemQty;
                    $baseTax        = $baseOrderItemTax*$item->getQty()/$orderItemQty;
                    $hiddenTax      = $orderItem->getHiddenTaxAmount()*$item->getQty()/$orderItemQty;
                    $baseHiddenTax  = $orderItem->getBaseHiddenTaxAmount()*$item->getQty()/$orderItemQty;

                    $tax            = $creditmemo->getStore()->roundPrice($tax);
                    $baseTax        = $creditmemo->getStore()->roundPrice($baseTax);
                    $hiddenTax      = $creditmemo->getStore()->roundPrice($hiddenTax);
                    $baseHiddenTax  = $creditmemo->getStore()->roundPrice($baseHiddenTax);
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
                $taxFactor = $creditmemo->getBaseShippingAmount()/$order->getBaseShippingAmount();
                $shippingTaxAmount           = $invoice->getShippingTaxAmount()*$taxFactor;
                $baseShippingTaxAmount       = $invoice->getBaseShippingTaxAmount()*$taxFactor;
                $totalHiddenTax             += $invoice->getShippingHiddenTaxAmount()*$taxFactor;
                $baseTotalHiddenTax         += $invoice->getBaseShippingHiddenTaxAmnt()*$taxFactor;
                $shippingHiddenTaxAmount     = $invoice->getShippingHiddenTaxAmount()*$taxFactor;
                $baseShippingHiddenTaxAmount = $invoice->getBaseShippingHiddenTaxAmnt()*$taxFactor;
                $shippingTaxAmount           = $creditmemo->getStore()->roundPrice($shippingTaxAmount);
                $baseShippingTaxAmount       = $creditmemo->getStore()->roundPrice($baseShippingTaxAmount);
                $totalHiddenTax              = $creditmemo->getStore()->roundPrice($totalHiddenTax);
                $baseTotalHiddenTax          = $creditmemo->getStore()->roundPrice($baseTotalHiddenTax);
                $shippingHiddenTaxAmount     = $creditmemo->getStore()->roundPrice($shippingHiddenTaxAmount);
                $baseShippingHiddenTaxAmount = $creditmemo->getStore()->roundPrice($baseShippingHiddenTaxAmount);
                $totalTax                   += $shippingTaxAmount;
                $baseTotalTax               += $baseShippingTaxAmount;
            }
        } else {
            $orderShippingAmount = $order->getShippingAmount();
            $baseOrderShippingAmount = $order->getBaseShippingAmount();
            $orderShippingHiddenTaxAmount = $order->getShippingHiddenTaxAmount();
            $baseOrderShippingHiddenTaxAmount = $order->getBaseShippingHiddenTaxAmnt();

            $baseOrderShippingRefundedAmount = $order->getBaseShippingRefunded();
            $baseOrderShippingHiddenTaxRefunded = $order->getBaseShippingHiddenTaxRefunded();

            $shippingTaxAmount = 0;
            $baseShippingTaxAmount = 0;
            $shippingHiddenTaxAmount = 0;
            $baseShippingHiddenTaxAmount = 0;

            $shippingDelta = $baseOrderShippingAmount - $baseOrderShippingRefundedAmount;

            if ($shippingDelta > $creditmemo->getBaseShippingAmount()) {
                $part       = $creditmemo->getShippingAmount()/$orderShippingAmount;
                $basePart   = $creditmemo->getBaseShippingAmount()/$baseOrderShippingAmount;
                $shippingTaxAmount          = $order->getShippingTaxAmount()*$part;
                $baseShippingTaxAmount      = $order->getBaseShippingTaxAmount()*$basePart;
                $shippingHiddenTaxAmount    = $order->getShippingHiddenTaxAmount()*$part;
                $baseShippingHiddenTaxAmount= $order->getBaseShippingHiddenTaxAmnt()*$basePart;
                $shippingTaxAmount          = $creditmemo->getStore()->roundPrice($shippingTaxAmount);
                $baseShippingTaxAmount      = $creditmemo->getStore()->roundPrice($baseShippingTaxAmount);
                $shippingHiddenTaxAmount    = $creditmemo->getStore()->roundPrice($shippingHiddenTaxAmount);
                $baseShippingHiddenTaxAmount= $creditmemo->getStore()->roundPrice($baseShippingHiddenTaxAmount);
            } elseif ($shippingDelta == $creditmemo->getBaseShippingAmount()) {
                $shippingTaxAmount          = $order->getShippingTaxAmount() - $order->getShippingTaxRefunded();
                $baseShippingTaxAmount      = $order->getBaseShippingTaxAmount() - $order->getBaseShippingTaxRefunded();
                $shippingHiddenTaxAmount    = $order->getShippingHiddenTaxAmount()
                        - $order->getShippingHiddenTaxRefunded();
                $baseShippingHiddenTaxAmount= $order->getBaseShippingHiddenTaxAmnt()
                        - $order->getBaseShippingHiddenTaxRefunded();
            }
            $totalTax           += $shippingTaxAmount;
            $baseTotalTax       += $baseShippingTaxAmount;
            $totalHiddenTax     += $shippingHiddenTaxAmount;
            $baseTotalHiddenTax += $baseShippingHiddenTaxAmount;
        }

        $allowedTax     = $order->getTaxAmount() - $order->getTaxRefunded();
        $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxRefunded();
        $allowedHiddenTax     = $order->getHiddenTaxAmount() + $order->getShippingHiddenTaxAmount()
            - $order->getHiddenTaxRefunded() - $order->getShippingHiddenTaxRefunded();
        $allowedBaseHiddenTax = $order->getBaseHiddenTaxAmount() + $order->getBaseShippingHiddenTaxAmnt()
            - $order->getBaseHiddenTaxRefunded() - $order->getBaseShippingHiddenTaxRefunded();


        $totalTax           = min($allowedTax, $totalTax);
        $baseTotalTax       = min($allowedBaseTax, $baseTotalTax);
        $totalHiddenTax     = min($allowedHiddenTax, $totalHiddenTax);
        $baseTotalHiddenTax = min($allowedBaseHiddenTax, $baseTotalHiddenTax);

        $creditmemo->setTaxAmount($totalTax);
        $creditmemo->setBaseTaxAmount($baseTotalTax);
        $creditmemo->setHiddenTaxAmount($totalHiddenTax);
        $creditmemo->setBaseHiddenTaxAmount($baseTotalHiddenTax);


        $creditmemo->setShippingTaxAmount($shippingTaxAmount);
        $creditmemo->setBaseShippingTaxAmount($baseShippingTaxAmount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalTax + $totalHiddenTax);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalTax + $baseTotalHiddenTax);
        return $this;
    }
}
