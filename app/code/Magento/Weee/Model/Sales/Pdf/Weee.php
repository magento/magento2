<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;
use Magento\Weee\Helper\Data as WeeeHelper;

/**
 * Sales order total for PDF, taking into account WEEE tax
 */
class Weee extends DefaultTotal
{
    /**
     * @var WeeeHelper
     */
    protected $_weeeData;

    /**
     * @param TaxHelper $taxHelper
     * @param Calculation $taxCalculation
     * @param CollectionFactory $ordersFactory
     * @param WeeeHelper $_weeeData
     * @param array $data
     */
    public function __construct(
        TaxHelper $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        WeeeHelper $_weeeData,
        array $data = []
    ) {
        $this->_weeeData = $_weeeData;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Check if weee total amount should be included
     *
     * Example:
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        /** @var $items OrderItem[] */
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
