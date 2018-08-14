<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Constraint;

use Magento\Downloadable\Test\Fixture\DownloadableProduct;
use Magento\Wishlist\Test\Constraint\AssertProductInCustomerWishlistOnBackendGrid;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class AssertDownloadableProductInCustomerWishlistOnBackendGrid
 * Assert that downloadable product is present in grid on customer's wish list tab with configure option and qty
 */
class AssertDownloadableProductInCustomerWishlistOnBackendGrid extends AssertProductInCustomerWishlistOnBackendGrid
{
    /**
     * Prepare options
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareOptions(FixtureInterface $product)
    {
        /** @var DownloadableProduct $product */
        $productOptions = parent::prepareOptions($product);
        $checkoutData = $product->getCheckoutData()['options'];
        if (!empty($checkoutData['links'])) {
            $downloadableLinks = $product->getDownloadableLinks();
            foreach ($checkoutData['links'] as $optionData) {
                $linkKey = str_replace('link_', '', $optionData['label']);
                $productOptions[] = [
                    'option_name' => 'Links',
                    'value' => $downloadableLinks['downloadable']['link'][$linkKey]['title'],
                ];
            }
        }

        return $productOptions;
    }
}
