<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Cart;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Data for verify cart item block on checkout page.
 *
 * Data keys:
 *  - product (fixture data for verify)
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Item extends DataSource
{
    /**
     * @constructor
     * @param FixtureInterface $product
     */
    public function __construct(FixtureInterface $product)
    {
        /** @var CatalogProductSimple $product */
        $checkoutData = $product->getCheckoutData();
        $cartItem = isset($checkoutData['cartItem']) ? $checkoutData['cartItem'] : [];
        $customOptions = $product->hasData('custom_options') ? $product->getCustomOptions() : [];
        $checkoutCustomOptions = isset($checkoutData['options']['custom_options'])
            ? $checkoutData['options']['custom_options']
            : [];

        foreach ($checkoutCustomOptions as $key => $checkoutCustomOption) {
            $attribute = str_replace('attribute_key_', '', $checkoutCustomOption['title']);
            $option = str_replace('option_key_', '', $checkoutCustomOption['value']);

            $checkoutCustomOptions[$key] = [
                'title' => isset($customOptions[$attribute]['title'])
                    ? $customOptions[$attribute]['title']
                    : $attribute,
                'value' => isset($customOptions[$attribute]['options'][$option]['title'])
                    ? $customOptions[$attribute]['options'][$option]['title']
                    : $option,
            ];
        }

        $cartItem['options'] = isset($cartItem['options'])
            ? $cartItem['options'] + $checkoutCustomOptions
            : $checkoutCustomOptions;
        $cartItem['qty'] = isset($checkoutData['qty'])
                ? $checkoutData['qty']
                : 1;

        $this->data = $cartItem;
    }
}
