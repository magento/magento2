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
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order invoice shipping total calculation model
 *
 * @category   Magento
 * @package    Magento_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Model\Order\Invoice\Total;

class Shipping extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setShippingAmount(0);
        $invoice->setBaseShippingAmount(0);
        $orderShippingAmount        = $invoice->getOrder()->getShippingAmount();
        $baseOrderShippingAmount    = $invoice->getOrder()->getBaseShippingAmount();
        $shippingInclTax            = $invoice->getOrder()->getShippingInclTax();
        $baseShippingInclTax        = $invoice->getOrder()->getBaseShippingInclTax();
        if ($orderShippingAmount) {
            /**
             * Check shipping amount in previous invoices
             */
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
                if ((float)$previousInvoice->getShippingAmount() && !$previousInvoice->isCanceled()) {
                    return $this;
                }
            }
            $invoice->setShippingAmount($orderShippingAmount);
            $invoice->setBaseShippingAmount($baseOrderShippingAmount);
            $invoice->setShippingInclTax($shippingInclTax);
            $invoice->setBaseShippingInclTax($baseShippingInclTax);

            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderShippingAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$baseOrderShippingAmount);
        }
        return $this;
    }
}
