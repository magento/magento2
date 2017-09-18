<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateProductOptionsObserver implements ObserverInterface
{
    /**
     * Weee data
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeData = null;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param \Magento\Tax\Helper\Data $taxData
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Weee\Helper\Data $weeeData,
        \Magento\Tax\Helper\Data $taxData
    ) {
        $this->weeeData = $weeeData;
        $this->registry = $registry;
        $this->taxData = $taxData;
    }

    /**
     * Change default JavaScript templates for options rendering
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getEvent()->getResponseObject();
        $options = $response->getAdditionalOptions();

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->registry->registry('current_product');
        if (!$product) {
            return $this;
        }

        // if the Weee module is enabled, then only do processing on bundle products
        if ($this->weeeData->isEnabled() && $product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            if ($this->taxData->priceIncludesTax() && $this->taxData->displayPriceExcludingTax()) {
                // the Tax module might have set up a default, but we will re-decide which calcPrice field to use
                unset($options['optionTemplate']);
            }

            if (!array_key_exists('optionTemplate', $options)) {
                $calcPrice = $this->getWhichCalcPriceToUse($product->getStoreId());
                $options['optionTemplate'] = '<%- data.label %>'
                    . '<% if (data.' . $calcPrice . '.value) { %>'
                    . ' +<%- data.' . $calcPrice . '.formatted %>'
                    . '<% } %>';
            }

            if (!$this->weeeData->isDisplayIncl($product->getStoreId()) &&
                !$this->weeeData->isDisplayExcl($product->getStoreId())) {
                // we need to display the individual Weee amounts
                foreach ($this->weeeData->getWeeeAttributesForBundle($product) as $weeeAttributes) {
                    foreach ($weeeAttributes as $weeeAttribute) {
                        if (!preg_match('/' . $weeeAttribute->getCode() . '/', $options['optionTemplate'])) {
                            $options['optionTemplate'] .= sprintf(
                                ' <%% if (data.weeePrice' . $weeeAttribute->getCode() . ') { %%>'
                                . '  (' . $weeeAttribute->getName()
                                . ': <%%- data.weeePrice' . $weeeAttribute->getCode()
                                . '.formatted %%>)'
                                . '<%% } %%>'
                            );
                        }
                    }
                }
            }

            if ($this->weeeData->isDisplayExclDescIncl($product->getStoreId())) {
                $options['optionTemplate'] .= sprintf(
                    ' <%% if (data.weeePrice) { %%>'
                    . '<%%- data.weeePrice.formatted %%>'
                    . '<%% } %%>'
                );
            }
        }
        $response->setAdditionalOptions($options);
        return $this;
    }

    /**
     * Returns which product price to show (before listing the individual Weee amounts, if applicable)
     *
     * @param  int|null $storeId
     * @return string
     */
    protected function getWhichCalcPriceToUse($storeId = null)
    {
        $calcPrice = 'finalPrice';

        if ($this->weeeData->isDisplayExclDescIncl($storeId) ||
            ($this->weeeData->isDisplayExcl($storeId) && $this->taxData->displayPriceExcludingTax())) {
            $calcPrice = 'basePrice';
        }
        return $calcPrice;
    }
}
