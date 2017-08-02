<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Pdf;

/**
 * Class \Magento\Tax\Model\Sales\Pdf\Shipping
 *
 * @since 2.0.0
 */
class Shipping extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * @var \Magento\Tax\Model\Config
     * @since 2.0.0
     */
    protected $_taxConfig;

    /**
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

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
     * @since 2.0.0
     */
    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        $amountInclTax = $this->getSource()->getShippingInclTax();
        if (!$amountInclTax) {
            $amountInclTax = $this->getAmount() + $this->getSource()->getShippingTaxAmount();
        }
        $amountInclTax = $this->getOrder()->formatPriceTxt($amountInclTax);
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        if ($this->_taxConfig->displaySalesShippingBoth($store)) {
            $totals = [
                [
                    'amount' => $this->getAmountPrefix() . $amount,
                    'label' => __('Shipping (Excl. Tax)') . ':',
                    'font_size' => $fontSize,
                ],
                [
                    'amount' => $this->getAmountPrefix() . $amountInclTax,
                    'label' => __('Shipping (Incl. Tax)') . ':',
                    'font_size' => $fontSize
                ],
            ];
        } elseif ($this->_taxConfig->displaySalesShippingInclTax($store)) {
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
