<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Constraint;

use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Wishlist\Test\Constraint\AssertProductInCustomerWishlistOnBackendGrid;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertConfigurableProductInCustomerWishlistOnBackendGrid
 * Assert that configurable product is present in grid on customer's wish list tab with configure option and qty
 */
class AssertConfigurableProductInCustomerWishlistOnBackendGrid extends AssertProductInCustomerWishlistOnBackendGrid
{
    /**
     * Prepare options
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareOptions(FixtureInterface $product)
    {
        /** @var ConfigurableProduct $product */
        $productOptions = parent::prepareOptions($product);
        $checkoutData = $product->getCheckoutData()['options'];
        if (!empty($checkoutData['configurable_options'])) {
            $configurableAttributesData = $product->getConfigurableAttributesData()['attributes_data'];
            foreach ($checkoutData['configurable_options'] as $optionData) {
                $attribute = $configurableAttributesData[$optionData['title']];
                $productOptions[] = [
                    'option_name' => $attribute['label'],
                    'value' => $attribute['options'][$optionData['value']]['label'],
                ];
            }
        }

        return $productOptions;
    }
}
