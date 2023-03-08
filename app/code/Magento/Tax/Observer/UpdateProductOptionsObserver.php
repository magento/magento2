<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Magento\Tax\Helper\Data as TaxHelper;

class UpdateProductOptionsObserver implements ObserverInterface
{
    /**
     * @param TaxHelper $taxData Tax data
     * @param Registry $registry
     */
    public function __construct(
        protected readonly TaxHelper $taxData,
        protected readonly Registry $registry
    ) {
    }

    /**
     * Change default JavaScript templates for options rendering
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
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
