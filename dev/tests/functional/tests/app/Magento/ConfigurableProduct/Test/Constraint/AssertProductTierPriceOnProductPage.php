<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\Catalog\Test\Constraint\AssertProductPage;
use Magento\ConfigurableProduct\Test\Block\Product\View\ConfigurableOptions;

/**
 * Open created configurble product on frontend and choose variation with tier price
 */
class AssertProductTierPriceOnProductPage extends AssertProductPage
{
    /**
     * Verify that tier prices configured for all variations of configured product displayed as expected.
     *
     * @return array
     */
    public function verify()
    {
        $errors = [];
        /** @var ConfigurableOptions $optionsBlock */
        $optionsBlock = $this->pageView->getConfigurableAttributesBlock();
        $formTierPrices = $optionsBlock->getOptionsPrices($this->product);
        $products = ($this->product->getDataFieldConfig('configurable_attributes_data')['source'])->getProducts();
        foreach ($products as $key => $product) {
            $configuredTierPrice = [];
            $actualTierPrices = isset($formTierPrices[$key]['tierPrices']) ? $formTierPrices[$key]['tierPrices'] : [];
            $tierPrices = $product->getTierPrice() ?: [];
            foreach ($tierPrices as $tierPrice) {
                $configuredTierPrice[] = [
                    'qty' => $tierPrice['price_qty'],
                    'price_qty' => $tierPrice['price'],
                ];
            }

            if ($configuredTierPrice != $actualTierPrices) {
                $errors[] = sprintf('Tier prices for variation %s doesn\'t equals to configured.', $key);
            }
        }

        return $errors;
    }
}
