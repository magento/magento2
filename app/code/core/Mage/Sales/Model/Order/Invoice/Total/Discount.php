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


class Mage_Sales_Model_Order_Invoice_Total_Discount extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $invoice->setDiscountAmount(0);
        $invoice->setBaseDiscountAmount(0);

        $totalDiscountAmount     = 0;
        $baseTotalDiscountAmount = 0;

        /**
         * Checking if shipping discount was added in previous invoices.
         * So basically if we have invoice with positive discount and it
         * was not canceled we don't add shipping discount to this one.
         */
        $addShippingDicount = true;
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
            if ($previusInvoice->getDiscountAmount()) {
                $addShippingDicount = false;
            }
        }

        if ($addShippingDicount) {
            $totalDiscountAmount     = $totalDiscountAmount + $invoice->getOrder()->getShippingDiscountAmount();
            $baseTotalDiscountAmount = $baseTotalDiscountAmount + $invoice->getOrder()->getBaseShippingDiscountAmount();
        }

        foreach ($invoice->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }
            $orderItem = $item->getOrderItem();
            $orderItemDiscount      = (float) $orderItem->getDiscountAmount();
            $baseOrderItemDiscount  = (float) $orderItem->getBaseDiscountAmount();
            $orderItemQty       = $orderItem->getQtyOrdered();

            if ($orderItemDiscount && $orderItemQty) {
                /**
                 * Resolve rounding problems
                 */
                if ($item->isLast()) {
                    $discount = $orderItemDiscount - $orderItem->getDiscountInvoiced();
                    $baseDiscount = $baseOrderItemDiscount - $orderItem->getBaseDiscountInvoiced();
                }
                else {
                    $discount = $orderItemDiscount*$item->getQty()/$orderItemQty;
                    $baseDiscount = $baseOrderItemDiscount*$item->getQty()/$orderItemQty;

                    $discount = $invoice->getStore()->roundPrice($discount);
                    $baseDiscount = $invoice->getStore()->roundPrice($baseDiscount);
                }

                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($baseDiscount);

                $totalDiscountAmount += $discount;
                $baseTotalDiscountAmount += $baseDiscount;
            }
        }


        $invoice->setDiscountAmount($totalDiscountAmount);
        $invoice->setBaseDiscountAmount($baseTotalDiscountAmount);

        $invoice->setGrandTotal($invoice->getGrandTotal() - $totalDiscountAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseTotalDiscountAmount);
        return $this;
    }
}
