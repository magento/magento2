<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Pdf;

class Subtotal extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * Get array of arrays with totals information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        $helper = $this->_taxHelper;
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getSource()->getSubtotalInclTax()) {
            $amountInclTax = $this->getSource()->getSubtotalInclTax();
        } else {
            $amountInclTax = $this->getAmount() +
                $this->getSource()->getTaxAmount() -
                $this->getSource()->getShippingTaxAmount();
        }

        $amountInclTax = $this->getOrder()->formatPriceTxt($amountInclTax);
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        if ($helper->displaySalesSubtotalBoth($store)) {
            $totals = [
                [
                    'amount' => $this->getAmountPrefix() . $amount,
                    'label' => __('Subtotal (Excl. Tax)') . ':',
                    'font_size' => $fontSize,
                ],
                [
                    'amount' => $this->getAmountPrefix() . $amountInclTax,
                    'label' => __('Subtotal (Incl. Tax)') . ':',
                    'font_size' => $fontSize
                ],
            ];
        } elseif ($helper->displaySalesSubtotalInclTax($store)) {
            $totals = [
                [
                    'amount' => $this->getAmountPrefix() . $amountInclTax,
                    'label' => __($this->getTitle()) . ':',
                    'font_size' => $fontSize,
                ],
            ];
        } else {
            $totals = [
                [
                    'amount' => $this->getAmountPrefix() . $amount,
                    'label' => __($this->getTitle()) . ':',
                    'font_size' => $fontSize,
                ],
            ];
        }

        return $totals;
    }
}
