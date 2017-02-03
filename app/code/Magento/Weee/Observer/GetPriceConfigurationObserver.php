<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Observer;

use Magento\Framework\Event\ObserverInterface;

class GetPriceConfigurationObserver implements ObserverInterface
{
    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * Weee data
     *
     * @var \Magento\Weee\Helper\Data
     */
    protected $weeeData;

    /** @var \Magento\Framework\Registry */
    protected $registry;

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
        $this->registry = $registry;
        $this->taxData = $taxData;
        $this->weeeData = $weeeData;
    }

    /**
     * Modify the options config for the front end to resemble the weee final price
     *
     * @param   \Magento\Framework\Event\Observer $observer
     * @return  $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->weeeData->isEnabled()) {
            $priceConfigObj = $observer->getData('configObj');
            try {
                /** @var \Magento\Catalog\Model\Product $product */
                $product = $this->registry->registry('current_product');
                $weeeAttributesForBundle = $this->weeeData->getWeeeAttributesForBundle($product);
                $priceConfig = $this->recurConfigAndInsertWeeePrice(
                    $priceConfigObj->getConfig(),
                    'prices',
                    $this->getWhichCalcPriceToUse($product->getStoreId(), $weeeAttributesForBundle),
                    $weeeAttributesForBundle
                );
                $priceConfigObj->setConfig($priceConfig);
            } catch (\Exception $e) {
                return $this;
            }
        }
        return $this;
    }

    /**
     * Recurse through the config array and insert the weee price
     *
     * @param  array $input
     * @param  string $searchKey
     * @param  string $calcPrice
     * @param  array $weeeAttributesForBundle
     * @return array
     */
    private function recurConfigAndInsertWeeePrice($input, $searchKey, $calcPrice, $weeeAttributesForBundle = null)
    {
        $holder = [];
        if (is_array($input)) {
            foreach ($input as $key => $el) {
                if (is_array($el)) {
                    $holder[$key] =
                        $this->recurConfigAndInsertWeeePrice($el, $searchKey, $calcPrice, $weeeAttributesForBundle);
                    if ($key === $searchKey) {
                        if ((!array_key_exists('weeePrice', $holder[$key])) &&
                            (array_key_exists($calcPrice, $holder[$key]))
                        ) {
                            //this is required for product options && bundle
                            $holder[$key]['weeePrice'] = $holder[$key][$calcPrice];
                            // only do processing on product options
                            if (array_key_exists('optionId', $input) && $weeeAttributesForBundle) {
                                $holder = $this->insertWeeePrice($holder, $key, $weeeAttributesForBundle);
                            }
                        }
                    }
                } else {
                    $holder[$key] = $el;
                }
            }
        }
        return $holder;
    }

    /**
     * Insert the weee price for bundle product
     *
     * @param  array $holder
     * @param  int|string $key
     * @param  array $weeeAttributesForBundle
     * @return array
     */
    private function insertWeeePrice($holder, $key, $weeeAttributesForBundle)
    {
        if (array_key_exists($holder['optionId'], $weeeAttributesForBundle)) {
            if (count($weeeAttributesForBundle[$holder['optionId']]) > 0 &&
                is_array($weeeAttributesForBundle[$holder['optionId']])
            ) {
                $weeeSum = 0;
                foreach ($weeeAttributesForBundle[$holder['optionId']] as $weeeAttribute) {
                    $holder[$key]['weeePrice' . $weeeAttribute->getCode()] =
                        ['amount' => (float)$weeeAttribute->getAmountExclTax()];
                    $weeeSum += (float)$weeeAttribute->getAmountExclTax();
                }
                $holder[$key]['weeePrice']['amount'] += (float)$weeeSum;
            } else {
                //there were no Weee attributes for this option
                unset($holder[$key]['weeePrice']);
            }
        }
        return $holder;
    }

    /**
     * Returns which product price to use as a basis for the Weee's final price
     *
     * @param  int|null $storeId
     * @param  array|null $weeeAttributesForBundle
     * @return string
     */
    protected function getWhichCalcPriceToUse($storeId = null, $weeeAttributesForBundle = null)
    {
        $calcPrice = 'finalPrice';
        if (!empty($weeeAttributesForBundle)) {
            if ($this->weeeData->isDisplayExcl($storeId) ||
                $this->weeeData->isDisplayExclDescIncl($storeId) ||
                ($this->taxData->priceIncludesTax() && $this->taxData->displayPriceExcludingTax())
            ) {
                $calcPrice = 'basePrice';
            }
        }
        return $calcPrice;
    }
}
