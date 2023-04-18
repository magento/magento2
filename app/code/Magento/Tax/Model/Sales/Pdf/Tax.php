<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Sales\Pdf;

use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;

class Tax extends DefaultTotal
{
    /**
     * @var Config
     */
    protected $_taxConfig;

    /**
     * @param TaxHelper $taxHelper
     * @param Calculation $taxCalculation
     * @param CollectionFactory $ordersFactory
     * @param Config $taxConfig
     * @param array $data
     */
    public function __construct(
        TaxHelper $taxHelper,
        Calculation $taxCalculation,
        CollectionFactory $ordersFactory,
        Config $taxConfig,
        array $data = []
    ) {
        $this->_taxConfig = $taxConfig;
        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }

    /**
     * Check if tax amount should be included to grandtotal block
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
        if ($this->_taxConfig->displaySalesTaxWithGrandTotal($store)) {
            return [];
        }

        $totals = [];

        if ($this->_taxConfig->displaySalesFullSummary($store)) {
            $totals = $this->getFullTaxInfo();
        }

        $totals = array_merge($totals, parent::getTotalsForDisplay());

        return $totals;
    }
}
