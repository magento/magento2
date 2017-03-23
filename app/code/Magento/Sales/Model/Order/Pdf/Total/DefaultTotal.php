<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Sales\Model\Order\Pdf\Total;

/**
 * Sales Order Total PDF model
 *
 * @method \Magento\Sales\Model\Order getOrder()
 */
class DefaultTotal extends \Magento\Framework\DataObject
{
    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $_taxHelper;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $_taxCalculation;

    /**
     * @var \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory
     */
    protected $_taxOrdersFactory;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        array $data = []
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_taxCalculation = $taxCalculation;
        $this->_taxOrdersFactory = $ordersFactory;
        parent::__construct($data);
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
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $amount = $this->getOrder()->formatPriceTxt($this->getAmount());
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $title = __($this->getTitle());
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        return [$total];
    }

    /**
     * Get array of arrays with tax information for display in PDF
     * array(
     *  $index => array(
     *      'amount'   => $amount,
     *      'label'    => $label,
     *      'font_size'=> $font_size
     *  )
     * )
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getFullTaxInfo()
    {
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $taxClassAmount = $this->_taxHelper->getCalculatedTaxes($this->getOrder());
        if (!empty($taxClassAmount)) {
            foreach ($taxClassAmount as &$tax) {
                $percent = $tax['percent'] ? ' (' . $tax['percent'] . '%)' : '';
                $tax['amount'] = $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($tax['tax_amount']);
                $tax['label'] = __($tax['title']) . $percent . ':';
                $tax['font_size'] = $fontSize;
            }
        } else {
            /** @var $orders \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\Collection */
            $orders = $this->_taxOrdersFactory->create();
            $rates = $orders->loadByOrder($this->getOrder())->toArray();
            $fullInfo = $this->_taxCalculation->reproduceProcess($rates['items']);
            $tax_info = [];

            if ($fullInfo) {
                foreach ($fullInfo as $info) {
                    if (isset($info['hidden']) && $info['hidden']) {
                        continue;
                    }

                    $_amount = $info['amount'];

                    foreach ($info['rates'] as $rate) {
                        $percent = $rate['percent'] ? ' (' . $rate['percent'] . '%)' : '';

                        $tax_info[] = [
                            'amount' => $this->getAmountPrefix() . $this->getOrder()->formatPriceTxt($_amount),
                            'label' => __($rate['title']) . $percent . ':',
                            'font_size' => $fontSize,
                        ];
                    }
                }
            }
            $taxClassAmount = $tax_info;
        }

        return $taxClassAmount;
    }

    /**
     * Check if we can display total information in PDF
     *
     * @return bool
     */
    public function canDisplay()
    {
        $amount = $this->getAmount();
        return $this->getDisplayZero() === 'true' || $amount != 0;
    }

    /**
     * Get Total amount from source
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->getSource()->getDataUsingMethod($this->getSourceField());
    }

    /**
     * Get title description from source
     *
     * @return mixed
     */
    public function getTitleDescription()
    {
        return $this->getSource()->getDataUsingMethod($this->getTitleSourceField());
    }
}
