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

namespace Magento\ConfigurableProduct\Test\Constraint;

use Mtf\Fixture\FixtureInterface;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;
use Magento\Wishlist\Test\Constraint\AssertProductInCustomerWishlistOnBackendGrid;

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
        /** @var ConfigurableProductInjectable $product */
        $productOptions = parent::prepareOptions($product);
        $checkoutData = $product->getCheckoutData()['options'];
        if (!empty($checkoutData['configurable_options'])) {
            $configurableAttributesData = $product->getConfigurableAttributesData()['attributes_data'];
            foreach ($checkoutData['configurable_options'] as $optionData) {
                $attribute = $configurableAttributesData[$optionData['title']];
                $productOptions[] = [
                    'option_name' => $attribute['label'],
                    'value' => $attribute['options'][$optionData['value']]['label']
                ];
            }
        }

        return $productOptions;
    }
}
