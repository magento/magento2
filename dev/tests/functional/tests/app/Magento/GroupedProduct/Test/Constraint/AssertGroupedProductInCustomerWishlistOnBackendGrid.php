<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Magento\GroupedProduct\Test\Fixture\GroupedProduct;
use Magento\Wishlist\Test\Constraint\AssertProductInCustomerWishlistOnBackendGrid;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertGroupedProductInCustomerWishlistOnBackendGrid
 * Assert that grouped product is present in grid on customer's wish list tab with configure option and qty
 */
class AssertGroupedProductInCustomerWishlistOnBackendGrid extends AssertProductInCustomerWishlistOnBackendGrid
{
    /**
     * Prepare filter
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareFilter(FixtureInterface $product)
    {
        $options = $this->prepareOptions($product);

        return ['product_name' => $product->getName(), 'options' => $options];
    }

    /**
     * Prepare options
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareOptions(FixtureInterface $product)
    {
        /** @var GroupedProduct $product */
        $productOptions = [];
        $checkoutData = $product->getCheckoutData()['options'];
        if (count($checkoutData)) {
            $associated = $product->getAssociated();
            foreach ($checkoutData as $optionData) {
                $productKey = str_replace('product_key_', '', $optionData['name']);
                $productOptions[] = [
                    'option_name' => $associated['assigned_products'][$productKey]['name'],
                    'value' => $optionData['qty'],
                ];
            }
        }

        return $productOptions;
    }
}
