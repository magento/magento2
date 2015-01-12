<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Fixture\Cart;

use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Item
 * Data for verify cart item block on checkout page
 *
 * Data keys:
 *  - product (fixture data for verify)
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
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

        /** @var ConfigurableProductInjectable $product */
        $checkoutData = $product->getCheckoutData();
        $cartItem = isset($checkoutData['cartItem']) ? $checkoutData['cartItem'] : [];
        $attributesData = $product->getConfigurableAttributesData()['attributes_data'];
        $checkoutConfigurableOptions = isset($checkoutData['options']['configurable_options'])
            ? $checkoutData['options']['configurable_options']
            : [];

        foreach ($checkoutConfigurableOptions as $key => $checkoutConfigurableOption) {
            $attribute = $checkoutConfigurableOption['title'];
            $option = $checkoutConfigurableOption['value'];

            $checkoutConfigurableOptions[$key] = [
                'title' => isset($attributesData[$attribute]['label'])
                        ? $attributesData[$attribute]['label']
                        : $attribute,
                'value' => isset($attributesData[$attribute]['options'][$option]['label'])
                        ? $attributesData[$attribute]['options'][$option]['label']
                        : $option,
            ];
        }

        $cartItem['options'] = isset($cartItem['options'])
            ? $cartItem['options'] + $checkoutConfigurableOptions
            : $checkoutConfigurableOptions;
        $this->data = $cartItem;
    }

    /**
     * Persist fixture
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return string
     */
    public function getDataConfig()
    {
        //
    }
}
