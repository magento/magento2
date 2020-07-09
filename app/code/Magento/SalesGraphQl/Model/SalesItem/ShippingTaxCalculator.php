<?php

namespace Magento\SalesGraphQl\Model\SalesItem;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\EntityInterface;
use Magento\Tax\Api\Data\OrderTaxDetailsItemInterface;
use Magento\Tax\Api\OrderTaxManagementInterface;

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
        $taxClassAmount = [];
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
                if ($itemTaxDetail->getType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING) {
                    $taxClassAmount = $this->_aggregateTaxes($taxClassAmount, $itemTaxDetail, $shippingRatio);
                }
            }
        }

        return $taxClassAmount;
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
     * @param  array                        $taxClassAmount
     * @param  OrderTaxDetailsItemInterface $itemTaxDetail
     * @param  float                        $ratio
     * @return array
     */
    private function _aggregateTaxes($taxClassAmount, OrderTaxDetailsItemInterface $itemTaxDetail, $ratio)
    {
        $itemAppliedTaxes = $itemTaxDetail->getAppliedTaxes();
        foreach ($itemAppliedTaxes as $itemAppliedTax) {
            $taxAmount = $itemAppliedTax->getAmount() * $ratio;
            $baseTaxAmount = $itemAppliedTax->getBaseAmount() * $ratio;
            if (0 == $taxAmount && 0 == $baseTaxAmount) {
                continue;
            }
            $taxCode = $itemAppliedTax->getCode();
            if (!isset($taxClassAmount[$taxCode])) {
                $taxClassAmount[$taxCode]['title'] = $itemAppliedTax->getTitle();
                $taxClassAmount[$taxCode]['percent'] = $itemAppliedTax->getPercent();
                $taxClassAmount[$taxCode]['tax_amount'] = $taxAmount;
                $taxClassAmount[$taxCode]['base_tax_amount'] = $baseTaxAmount;
            } else {
                $taxClassAmount[$taxCode]['tax_amount'] += $taxAmount;
                $taxClassAmount[$taxCode]['base_tax_amount'] += $baseTaxAmount;
            }
        }
        return $taxClassAmount;
    }
}
