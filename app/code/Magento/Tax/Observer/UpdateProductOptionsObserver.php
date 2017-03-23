<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateProductOptionsObserver implements ObserverInterface
{
    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Registry $registry
    ) {
        $this->taxData = $taxData;
        $this->registry = $registry;
    }

    /**
     * Change default JavaScript templates for options rendering
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponseObject();
        $options = $response->getAdditionalOptions();

        $_product = $this->registry->registry('current_product');
        if (!$_product) {
            return $this;
        }

        $algorithm = $this->taxData->getCalculationAlgorithm();
        $options['calculationAlgorithm'] = $algorithm;
        // prepare correct template for options render
        if ($this->taxData->displayBothPrices()) {
            $options['optionTemplate'] = sprintf(
                '<%%= data.label %%>'
                . '<%% if (data.finalPrice.value) { %%>'
                . ' +<%%= data.finalPrice.formatted %%> (%1$s <%%= data.basePrice.formatted %%>)'
                . '<%% } %%>',
                __('Excl. tax:')
            );
        } elseif ($this->taxData->priceIncludesTax() && $this->taxData->displayPriceExcludingTax()) {
            $options['optionTemplate'] = sprintf(
                '<%%= data.label %%>'
                . '<%% if (data.basePrice.value) { %%>'
                . ' +<%%= data.basePrice.formatted %%>'
                . '<%% } %%>'
            );
        }

        $response->setAdditionalOptions($options);
        return $this;
    }
}
