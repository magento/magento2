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
            $totals = array(
                array(
                    'amount' => $this->getAmountPrefix() . $amount,
                    'label' => __('Subtotal (Excl. Tax)') . ':',
                    'font_size' => $fontSize
                ),
                array(
                    'amount' => $this->getAmountPrefix() . $amountInclTax,
                    'label' => __('Subtotal (Incl. Tax)') . ':',
                    'font_size' => $fontSize
                )
            );
        } elseif ($helper->displaySalesSubtotalInclTax($store)) {
            $totals = array(
                array(
                    'amount' => $this->getAmountPrefix() . $amountInclTax,
                    'label' => __($this->getTitle()) . ':',
                    'font_size' => $fontSize
                )
            );
        } else {
            $totals = array(
                array(
                    'amount' => $this->getAmountPrefix() . $amount,
                    'label' => __($this->getTitle()) . ':',
                    'font_size' => $fontSize
                )
            );
        }

        return $totals;
    }
}
