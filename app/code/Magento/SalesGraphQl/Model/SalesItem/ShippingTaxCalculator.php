<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesGraphQl\Model\SalesItem;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\EntityInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;
use \Magento\Quote\Model\Quote\Address;

class ShippingTaxCalculator
{
    /**
     * @var OrderTaxManagementInterface
     */
    private $orderTaxManagement;

    /**
     * @param OrderTaxManagementInterface $orderTaxManagement
     */
    public function __construct(
        OrderTaxManagementInterface $orderTaxManagement
    ) {
        $this->orderTaxManagement = $orderTaxManagement;
    }

    /**
     * Calculate shipping taxes for sales item
     *
     * @param OrderInterface $order
     * @param EntityInterface $salesItem
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function calculateShippingTaxes(
        OrderInterface $order,
        EntityInterface $salesItem
    ) {
        $orderTaxDetails = $this->orderTaxManagement->getOrderTaxDetails($order->getId());
        $taxClassBreakdown = [];
        // Apply any taxes for shipping
        $shippingTaxAmount = $salesItem->getShippingTaxAmount();
        $originalShippingTaxAmount = $order->getShippingTaxAmount();
        if ($shippingTaxAmount && $originalShippingTaxAmount &&
            $shippingTaxAmount != 0 && (float)$originalShippingTaxAmount
        ) {
            //An invoice or credit memo can have a different qty than its order
            $shippingRatio = $shippingTaxAmount / $originalShippingTaxAmount;
            $itemTaxDetails = $orderTaxDetails->getItems();
            foreach ($itemTaxDetails as $itemTaxDetail) {
                //Aggregate taxable items associated with shipping
                if ($itemTaxDetail->getType() == Address::TYPE_SHIPPING) {
                    $taxClassBreakdown = $this->aggregateTaxes($taxClassBreakdown, $itemTaxDetail, $shippingRatio);
                }
            }
        }
        return $taxClassBreakdown;
    }

    /**
     * Accumulates the pre-calculated taxes for each tax class
     *
     * This method accepts and returns the 'taxClassAmount' array with format:
     * array(
     *  $index => array(
     *      'tax_amount'        => $taxAmount,
     *      'base_tax_amount'   => $baseTaxAmount,
     *      'title'             => $title,
     *      'percent'           => $percent
     *  )
     * )
     *
     * @param  array                        $taxClassBreakdown
     * @param  OrderTaxDetailsItemInterface $itemTaxDetail
     * @param  float                        $taxRatio
     * @return array
     */
    private function aggregateTaxes($taxClassBreakdown, OrderTaxDetailsItemInterface $itemTaxDetail, $taxRatio)
    {
        $itemAppliedTaxes = $itemTaxDetail->getAppliedTaxes();
        foreach ($itemAppliedTaxes as $itemAppliedTax) {
            $taxAmount = $itemAppliedTax->getAmount() * $taxRatio;
            $baseTaxAmount = $itemAppliedTax->getBaseAmount() * $taxRatio;
            if (0 == $taxAmount && 0 == $baseTaxAmount) {
                continue;
            }
            $taxCode = $itemAppliedTax->getCode();
            if (!isset($taxClassBreakdown[$taxCode])) {
                $taxClassBreakdown[$taxCode]['title'] = $itemAppliedTax->getTitle();
                $taxClassBreakdown[$taxCode]['percent'] = $itemAppliedTax->getPercent();
                $taxClassBreakdown[$taxCode]['tax_amount'] = $taxAmount;
                $taxClassBreakdown[$taxCode]['base_tax_amount'] = $baseTaxAmount;
            } else {
                $taxClassBreakdown[$taxCode]['tax_amount'] += $taxAmount;
                $taxClassBreakdown[$taxCode]['base_tax_amount'] += $baseTaxAmount;
            }
        }
        return $taxClassBreakdown;
    }
}
