<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\Sales\Pdf;

/**
 * Sales order total for PDF, taking into account WEEE tax
 */
class Weee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{
    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $_weeeData;

    /**
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Weee\Helper\Data $_weeeData
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Weee\Helper\Data $_weeeData,
        array $data = []
    ) {
        $this->_weeeData = $_weeeData;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Check if weee total amount should be included
     *
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
        /** @var $items \Magento\Sales\Model\Order\Item[] */
        $items = $this->getSource()->getAllItems();
        $store = $this->getSource()->getStore();

        $weeeTotal = $this->_weeeData->getTotalAmounts($items, $store);

        // If we have no Weee, check if we still need to display it
        if (!$weeeTotal && !filter_var($this->getDisplayZero(), FILTER_VALIDATE_BOOLEAN)) {
            return [];
        }

        // Display the Weee total amount
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $totals = [
            [
                'amount' => $this->getOrder()->formatPriceTxt($weeeTotal),
                'label' => __($this->getTitle()) . ':',
                'font_size' => $fontSize,
            ],
        ];

        return $totals;
    }

    /**
     * Check if we can display Weee total information in PDF
     *
     * @return bool
     */
    public function canDisplay()
    {
        $items = $this->getSource()->getAllItems();
        $store = $this->getSource()->getStore();
        $amount = $this->_weeeData->getTotalAmounts($items, $store);
        return $this->getDisplayZero() === 'true' || $amount != 0;
    }
}
