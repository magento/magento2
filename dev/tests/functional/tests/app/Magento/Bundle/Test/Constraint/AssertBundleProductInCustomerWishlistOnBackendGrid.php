<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Constraint;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Wishlist\Test\Constraint\AssertProductInCustomerWishlistOnBackendGrid;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertBundleProductInCustomerWishlistOnBackendGrid
 * Assert that bundle product is present in grid on customer's wish list tab with configure option and qty
 */
class AssertBundleProductInCustomerWishlistOnBackendGrid extends AssertProductInCustomerWishlistOnBackendGrid
{
    /**
     * Prepare options
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareOptions(FixtureInterface $product)
    {
        /** @var BundleProduct $product */
        $productOptions = parent::prepareOptions($product);
        $checkoutData = $product->getCheckoutData()['options'];
        if (!empty($checkoutData['bundle_options'])) {
            foreach ($checkoutData['bundle_options'] as $optionData) {
                $productOptions[] = [
                    'option_name' => $optionData['title'],
                    'value' => $optionData['value']['name'],
                ];
            }
        }

        return $productOptions;
    }
}
