<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\GroupedProduct\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Magento\GroupedProduct\Test\Fixture\GroupedProductInjectable;
use Magento\Wishlist\Test\Constraint\AssertProductInCustomerWishlistOnBackendGrid;

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

        return ['product_name' => $product->getName(), 'qty_from' => 1, 'qty_to' => 1, 'options' => $options];
    }

    /**
     * Prepare options
     *
     * @param FixtureInterface $product
     * @return array
     */
    protected function prepareOptions(FixtureInterface $product)
    {
        /** @var GroupedProductInjectable $product */
        $productOptions = [];
        $checkoutData = $product->getCheckoutData()['options'];
        if (count($checkoutData)) {
            $associated = $product->getAssociated();
            foreach ($checkoutData as $optionData) {
                $productKey = str_replace('product_key_', '', $optionData['name']);
                $productOptions[] = [
                    'option_name' => $associated['assigned_products'][$productKey]['name'],
                    'value' => $optionData['qty']
                ];
            }
        }

        return $productOptions;
    }
}
