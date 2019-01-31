<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Fixture\Cart;

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
     * Return prepared dataset.
     *
     * @param null|string $key
     * @return array
     */
    public function getData($key = null)
    {
        parent::getData($key);
        $productData = $this->product->getData();
        $checkoutData = $this->product->getCheckoutData();
        $cartItem = isset($checkoutData['cartItem']) ? $checkoutData['cartItem'] : [];
        $attributesData = $this->product->getConfigurableAttributesData()['attributes_data'];
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
        if (isset($productData['configurable_attributes_data']['matrix'][$attributeKey])) {
            $cartItem['sku'] = $productData['configurable_attributes_data']['matrix'][$attributeKey]['sku'];
        } else {
            $cartItem['sku'] = $productData['sku'];
        }
        $cartItem['name'] = $productData['name'];

        $cartItem['options'] = isset($cartItem['options'])
            ? $cartItem['options'] + $checkoutConfigurableOptions
            : $checkoutConfigurableOptions;
        $this->data = $cartItem;

        return $this->data;
    }
}
