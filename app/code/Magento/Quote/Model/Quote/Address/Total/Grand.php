<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\Total;

/**
 * Class Grand
 *
 * @package Magento\Quote\Model\Quote\Address\Total
 */
class Grand extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote                          $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total            $total
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $grandTotal = $total->getGrandTotal();
        $baseGrandTotal = $total->getBaseGrandTotal();
        $totals = array_sum($total->getAllTotalAmounts());
        $baseTotals = array_sum($total->getAllBaseTotalAmounts());

        $total->setGrandTotal($grandTotal + $totals);
        $total->setBaseGrandTotal($baseGrandTotal + $baseTotals);

        return $this;
    }

    /**
     * Add grand total information to address
     *
     * @param \Magento\Quote\Model\Quote               $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        return [
            'code' => $this->getCode(),
            'title' => __('Grand Total'),
            'value' => $total->getGrandTotal(),
            'area' => 'footer',
        ];
    }
}
