<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    /** @var \Magento\Framework\Registry */
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

        if ($this->weeeData->isEnabled() &&
            !$this->weeeData->geDisplayIncl($product->getStoreId()) &&
            !$this->weeeData->geDisplayExcl($product->getStoreId())
        ) {
            // only do processing on bundle product
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                if (!array_key_exists('optionTemplate', $options)) {
                    $calcPrice = $this->getWhichCalcPriceToUse($product->getStoreId());
                    $options['optionTemplate'] = '<%- data.label %>'
                        . '<% if (data.' . $calcPrice . '.value) { %>'
                        . ' +<%- data.' . $calcPrice . '.formatted %>'
                        . '<% } %>';
                }

                foreach ($this->weeeData->getWeeeAttributesForBundle($product) as $weeeAttributes) {
                    foreach ($weeeAttributes as $weeeAttribute) {
                        if (!preg_match('/'.$weeeAttribute->getCode().'/', $options['optionTemplate'])) {
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

                if ($this->weeeData->geDisplayExlDescIncl($product->getStoreId())) {
                    $options['optionTemplate'] .= sprintf(
                        ' <%% if (data.weeePrice) { %%>'
                        . '<%%- data.weeePrice.formatted %%>'
                        . '<%% } %%>'
                    );
                }

            }
        }
        $response->setAdditionalOptions($options);
        return $this;
    }

    /**
     * Returns which product price to use as a basis for the Weee's final price
     *
     * @param  int|null $storeId
     * @return string
     */
    protected function getWhichCalcPriceToUse($storeId = null)
    {
        $calcPrice = 'finalPrice';
        if ($this->weeeData->geDisplayExcl($storeId) ||
            $this->weeeData->geDisplayExlDescIncl($storeId) ||
            ($this->taxData->priceIncludesTax() && $this->taxData->displayPriceExcludingTax())
        ) {
            $calcPrice = 'basePrice';
        }
        return $calcPrice;
    }
}
