<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Fixture\Cart;

use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Data for verify cart item block on checkout page.
 *
 * Data keys:
 *  - product (fixture data for verify)
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Item extends \Magento\Catalog\Test\Fixture\Cart\Item
{
    /**
     * @param FixtureInterface $product
     */
    public function __construct(FixtureInterface $product)
    {
        parent::__construct($product);

        /** @var ConfigurableProduct $product */
        $productData = $product->getData();
        $checkoutData = $product->getCheckoutData();
        $cartItem = isset($checkoutData['cartItem']) ? $checkoutData['cartItem'] : [];
        $attributesData = $product->getConfigurableAttributesData()['attributes_data'];
        $checkoutConfigurableOptions = isset($checkoutData['options']['configurable_options'])
            ? $checkoutData['options']['configurable_options']
            : [];

        $attributeKey = [];
        foreach ($checkoutConfigurableOptions as $key => $checkoutConfigurableOption) {
            $attribute = $checkoutConfigurableOption['title'];
            $option = $checkoutConfigurableOption['value'];
            $attributeKey[] = "$attribute:$option";
            $checkoutConfigurableOptions[$key] = [
                'title' => isset($attributesData[$attribute]['label'])
                    ? $attributesData[$attribute]['label']
                    : $attribute,
                'value' => isset($attributesData[$attribute]['options'][$option]['label'])
                    ? $attributesData[$attribute]['options'][$option]['label']
                    : $option,
            ];
        }
        $attributeKey = implode(' ', $attributeKey);
        $cartItem['sku'] = $productData['configurable_attributes_data']['matrix'][$attributeKey]['sku'];
        $cartItem['name'] = $productData['name'];

        $cartItem['options'] = isset($cartItem['options'])
            ? $cartItem['options'] + $checkoutConfigurableOptions
            : $checkoutConfigurableOptions;
        $this->data = $cartItem;
    }
}
