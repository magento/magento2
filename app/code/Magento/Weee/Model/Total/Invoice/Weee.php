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
 * @package     Magento_Weee
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Magento\Weee\Model\Total\Invoice;

class Weee extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    /**
     * Weee data
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData = null;

    /**
     * Constructor
     *
     * By default is looking for first argument as array and assigns it as object
     * attributes This behavior may change in child classes
     *
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param array $data
     */
    public function __construct(
        \Magento\Weee\Helper\Data $weeeData,
        array $data = array()
    ) {
        $this->_weeeData = $weeeData;
        parent::__construct($data);
    }

    /**
     * Weee tax collector
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Weee\Model\Total\Invoice\Weee
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $store = $invoice->getStore();

        $totalTax = 0;
        $baseTotalTax = 0;

        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemQty = $orderItem->getQtyOrdered();

            if (!$orderItemQty || $orderItem->isDummy()) {
                continue;
            }

            $weeeTaxAmount = $item->getWeeeTaxAppliedAmount() * $item->getQty();
            $baseWeeeTaxAmount = $item->getBaseWeeeTaxAppliedAmount() * $item->getQty();

            $item->setWeeeTaxAppliedRowAmount($weeeTaxAmount);
            $item->setBaseWeeeTaxAppliedRowAmount($baseWeeeTaxAmount);
            $newApplied = array();
            $applied = $this->_weeeData->getApplied($item);
            foreach ($applied as $one) {
                $one['base_row_amount'] = $one['base_amount'] * $item->getQty();
                $one['row_amount'] = $one['amount'] * $item->getQty();
                $one['base_row_amount_incl_tax'] = $one['base_amount_incl_tax'] * $item->getQty();
                $one['row_amount_incl_tax'] = $one['amount_incl_tax'] * $item->getQty();

                $newApplied[] = $one;
            }
            $this->_weeeData->setApplied($item, $newApplied);

            $item->setWeeeTaxRowDisposition($item->getWeeeTaxDisposition() * $item->getQty());
            $item->setBaseWeeeTaxRowDisposition($item->getBaseWeeeTaxDisposition() * $item->getQty());

            $totalTax += $weeeTaxAmount;
            $baseTotalTax += $baseWeeeTaxAmount;
        }

        /*
         * Add FPT to totals
         * Notice that we check restriction on allowed tax, because
         * a) for last invoice we don't need to collect FPT - it is automatically collected by subtotal/tax collector,
         * that adds whole remaining (not invoiced) subtotal/tax value, so fpt is automatically included into it
         * b) FPT tax is included into order subtotal/tax value, so after multiple invoices with partial item quantities
         * it can happen that other collector will take some FPT value from shared subtotal/tax order value
         */
        $order = $invoice->getOrder();
        if ($this->_weeeData->includeInSubtotal($store)) {
            $allowedSubtotal = $order->getSubtotal() - $order->getSubtotalInvoiced() - $invoice->getSubtotal();
            $allowedBaseSubtotal = $order->getBaseSubtotal() - $order->getBaseSubtotalInvoiced()
                - $invoice->getBaseSubtotal();
            $totalTax = min($allowedSubtotal, $totalTax);
            $baseTotalTax = min($allowedBaseSubtotal, $baseTotalTax);

            $invoice->setSubtotal($invoice->getSubtotal() + $totalTax);
            $invoice->setBaseSubtotal($invoice->getBaseSubtotal() + $baseTotalTax);
        } else {
            $allowedTax = $order->getTaxAmount() - $order->getTaxInvoiced() - $invoice->getTaxAmount();
            $allowedBaseTax = $order->getBaseTaxAmount() - $order->getBaseTaxInvoiced() - $invoice->getBaseTaxAmount();
            $totalTax = min($allowedTax, $totalTax);
            $baseTotalTax = min($allowedBaseTax, $baseTotalTax);

            $invoice->setTaxAmount($invoice->getTaxAmount() + $totalTax);
            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseTotalTax);
        }

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalTax);

        return $this;
    }
}
