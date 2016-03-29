<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Weee\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Weee\Helper\Data as WeeeHelper;

class Weee extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
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
     * @param array                     $data
     */
    public function __construct(\Magento\Weee\Helper\Data $weeeData, array $data = [])
    {
        $this->_weeeData = $weeeData;
        parent::__construct($data);
    }

    /**
     * Collect Weee amounts for the credit memo
     *
     * @param  Creditmemo $creditmemo
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function collect(Creditmemo $creditmemo)
    {
        $store = $creditmemo->getStore();

        $totalWeeeAmount = 0;
        $baseTotalWeeeAmount = 0;
        $totalWeeeAmountInclTax = 0;
        $baseTotalWeeeAmountInclTax = 0;
        $totalTaxAmount = 0;
        $baseTotalTaxAmount = 0;

        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            $orderItemQty = $orderItem->getQtyOrdered();

            if (!$orderItemQty || $orderItem->isDummy() || $item->getQty() < 0) {
                continue;
            }

            $ratio = $item->getQty() / $orderItemQty;

            $orderItemWeeeAmountExclTax = $orderItem->getWeeeTaxAppliedRowAmount();
            $orderItemBaseWeeeAmountExclTax = $orderItem->getBaseWeeeTaxAppliedRowAmnt();
            $weeeAmountExclTax = $creditmemo->roundPrice($orderItemWeeeAmountExclTax * $ratio);
            $baseWeeeAmountExclTax = $creditmemo->roundPrice($orderItemBaseWeeeAmountExclTax * $ratio, 'base');

            $orderItemWeeeAmountInclTax = $this->_weeeData->getRowWeeeTaxInclTax($orderItem);
            $orderItemBaseWeeeAmountInclTax = $this->_weeeData->getBaseRowWeeeTaxInclTax($orderItem);
            $weeeAmountInclTax = $creditmemo->roundPrice($orderItemWeeeAmountInclTax * $ratio);
            $baseWeeeAmountInclTax = $creditmemo->roundPrice($orderItemBaseWeeeAmountInclTax * $ratio, 'base');

            $itemTaxAmount = $weeeAmountInclTax - $weeeAmountExclTax;
            $itemBaseTaxAmount = $baseWeeeAmountInclTax - $baseWeeeAmountExclTax;

            $weeeAmountAvailable = $this->_weeeData->getWeeeAmountInvoiced($orderItem) -
                $this->_weeeData->getWeeeAmountRefunded($orderItem);
            $baseWeeeAmountAvailable = $this->_weeeData->getBaseWeeeAmountInvoiced($orderItem) -
                $this->_weeeData->getBaseWeeeAmountRefunded($orderItem);
            $weeeTaxAmountAvailable = $this->_weeeData->getWeeeTaxAmountInvoiced($orderItem) -
                $this->_weeeData->getWeeeTaxAmountRefunded($orderItem);
            $baseWeeeTaxAmountAvailable = $this->_weeeData->getBaseWeeeTaxAmountInvoiced($orderItem) -
                $this->_weeeData->getBaseWeeeTaxAmountRefunded($orderItem);

            if ($item->isLast()) {
                $weeeAmountExclTax = $weeeAmountAvailable;
                $baseWeeeAmountExclTax = $baseWeeeAmountAvailable;
                $itemTaxAmount = $weeeTaxAmountAvailable;
                $itemBaseTaxAmount = $baseWeeeTaxAmountAvailable;
            } else {
                $weeeAmountExclTax = min($weeeAmountExclTax, $weeeAmountAvailable);
                $baseWeeeAmountExclTax = min($baseWeeeAmountExclTax, $baseWeeeAmountAvailable);
                $itemTaxAmount = min($itemTaxAmount, $weeeTaxAmountAvailable);
                $itemBaseTaxAmount = min($itemBaseTaxAmount, $baseWeeeTaxAmountAvailable);
            }

            $totalWeeeAmount += $weeeAmountExclTax;
            $baseTotalWeeeAmount += $baseWeeeAmountExclTax;

            $item->setWeeeTaxAppliedRowAmount($weeeAmountExclTax);
            $item->setBaseWeeeTaxAppliedRowAmount($baseWeeeAmountExclTax);

            $totalTaxAmount += $itemTaxAmount;
            $baseTotalTaxAmount += $itemBaseTaxAmount;

            //Set the ratio of the tax amount in invoice item compared to tax amount in order item
            //This information is needed to calculate tax per tax rate later
            $orderItemTaxAmount = $orderItemWeeeAmountInclTax - $orderItemWeeeAmountExclTax;
            if ($orderItemTaxAmount != 0) {
                $taxRatio = [];
                if ($item->getTaxRatio()) {
                    $taxRatio = unserialize($item->getTaxRatio());
                }
                $taxRatio[\Magento\Weee\Model\Total\Quote\Weee::ITEM_TYPE] = $itemTaxAmount / $orderItemTaxAmount;
                $item->setTaxRatio(serialize($taxRatio));
            }

            $totalWeeeAmountInclTax += $weeeAmountInclTax;
            $baseTotalWeeeAmountInclTax += $baseWeeeAmountInclTax;

            $newApplied = [];
            $applied = $this->_weeeData->getApplied($orderItem);
            foreach ($applied as $one) {
                $title = (string)$one['title'];
                $one['base_row_amount'] = $creditmemo->roundPrice($one['base_row_amount'] * $ratio, $title.'_base');
                $one['row_amount'] = $creditmemo->roundPrice($one['row_amount'] * $ratio, $title);
                $one['base_row_amount_incl_tax'] = $creditmemo->roundPrice(
                    $one['base_row_amount_incl_tax'] * $ratio,
                    $title.'_base'
                );
                $one['row_amount_incl_tax'] = $creditmemo->roundPrice($one['row_amount_incl_tax'] * $ratio, $title);

                $newApplied[] = $one;
            }
            $this->_weeeData->setApplied($item, $newApplied);

            // Update order item
            $newApplied = [];
            $applied = $this->_weeeData->getApplied($orderItem);
            foreach ($applied as $one) {
                if (isset($one[WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED])) {
                    $one[WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED] =
                        $one[WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED] + $baseWeeeAmountExclTax;
                } else {
                    $one[WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED] = $baseWeeeAmountExclTax;
                }
                if (isset($one[WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED])) {
                    $one[WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED] =
                        $one[WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED] + $weeeAmountExclTax;
                } else {
                    $one[WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED] = $weeeAmountExclTax;
                }
                if (isset($one[WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED])) {
                    $one[WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED] =
                        $one[WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED] + $itemBaseTaxAmount;
                } else {
                    $one[WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED] = $itemBaseTaxAmount;
                }
                if (isset($one[WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED])) {
                    $one[WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED] =
                        $one[WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED] + $itemTaxAmount;
                } else {
                    $one[WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED] = $itemTaxAmount;
                }

                $newApplied[] = $one;
            }
            $this->_weeeData->setApplied($orderItem, $newApplied);

            $item->setWeeeTaxRowDisposition($item->getWeeeTaxDisposition() * $item->getQty());
            $item->setBaseWeeeTaxRowDisposition($item->getBaseWeeeTaxDisposition() * $item->getQty());
        }

        if ($this->_weeeData->includeInSubtotal($store)) {
            $creditmemo->setSubtotal($creditmemo->getSubtotal() + $totalWeeeAmount);
            $creditmemo->setBaseSubtotal($creditmemo->getBaseSubtotal() + $baseTotalWeeeAmount);
        }

        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $totalTaxAmount);
        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $baseTotalTaxAmount);

        $creditmemo->setSubtotalInclTax(
            $creditmemo->getSubtotalInclTax() + $totalWeeeAmountInclTax
        );
        $creditmemo->setBaseSubtotalInclTax(
            $creditmemo->getBaseSubtotalInclTax() + $baseTotalWeeeAmountInclTax
        );

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalWeeeAmount + $totalTaxAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseTotalWeeeAmount + $baseTotalTaxAmount);

        return $this;
    }
}
