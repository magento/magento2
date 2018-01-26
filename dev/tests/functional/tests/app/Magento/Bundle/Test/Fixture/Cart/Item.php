<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Fixture\Cart;

use Magento\Bundle\Test\Fixture\BundleProduct;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Data for verify cart item block on checkout page.
 *
 * Data keys:
 *  - product (fixture data for verify)
 */
class Item extends \Magento\Catalog\Test\Fixture\Cart\Item
{
    /**
     * @constructor
     * @param FixtureInterface $product
     */
    public function __construct(FixtureInterface $product)
    {
        parent::__construct($product);

        /** @var BundleProduct $product */
        $bundleSelection = $product->getBundleSelections();
        $checkoutData = $product->getCheckoutData();
        $checkoutBundleOptions = isset($checkoutData['options']['bundle_options'])
            ? $checkoutData['options']['bundle_options']
            : [];

        foreach ($checkoutBundleOptions as $checkoutOptionKey => $checkoutOption) {
            // Find option and value keys
            $attributeKey = null;
            $optionKey = null;
            foreach ($bundleSelection['bundle_options'] as $key => $option) {
                if ($option['title'] == $checkoutOption['title']) {
                    $attributeKey = $key;

                    foreach ($option['assigned_products'] as $valueKey => $value) {
                        if (false !== strpos($value['search_data']['name'], $checkoutOption['value']['name'])) {
                            $optionKey = $valueKey;
                        }
                    }
                }
            }

            // Prepare option data
            $bundleSelectionAttribute = $bundleSelection['products'][$attributeKey];
            $bundleOptions = $bundleSelection['bundle_options'][$attributeKey];
            $value = $bundleSelectionAttribute[$optionKey]->getName();
            $qty = $bundleOptions['assigned_products'][$optionKey]['data']['selection_qty'];
            $price = $product->getPriceType() == 'Yes'
                ? number_format($bundleSelectionAttribute[$optionKey]->getPrice(), 2)
                : number_format($bundleOptions['assigned_products'][$optionKey]['data']['selection_price_value'], 2);
            $optionData = [
                'title' => $checkoutOption['title'],
                'value' => "{$qty} x {$value} {$price}",
            ];

            $checkoutBundleOptions[$checkoutOptionKey] = $optionData;
        }

        $this->data['options'] += $checkoutBundleOptions;
    }
}
