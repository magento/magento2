<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductVirtual;

/**
 * Class CheckoutData
 * Data for fill product form on frontend
 *
 * Data keys:
 *  - preset (Checkout data verification preset name)
 */
class CheckoutData extends \Magento\Catalog\Test\Fixture\CatalogProductSimple\CheckoutData
{
    /**
     * Get preset array
     *
     * @param $name
     * @return array|null
     */
    protected function getPreset($name)
    {
        $presets = [
            'order_default' => [
                'qty' => 1,
            ],
            '50_dollar_product' => [
                'qty' => 1,
                'cartItem' => [
                    'price' => 50,
                    'qty' => 1,
                    'subtotal' => 50,
                ],
            ],
            'order_custom_price' => [
                'qty' => 3,
                'checkout_data' => [
                    'use_custom_price' => "Yes",
                    'custom_price' => 100,
                ],
            ],
            'order_big_qty' => [
                'qty' => 900,
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : null;
    }
}
