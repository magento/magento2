<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Fixture\DownloadableProductInjectable;

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
            'with_two_separately_links' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_1',
                            'value' => 'Yes',
                        ],
                    ],
                ],
                'cartItem' => [
                    'price' => 23,
                    'subtotal' => 23,
                ],
            ],
            'with_two_bought_links' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_1',
                            'value' => 'Yes',
                        ],
                        [
                            'label' => 'link_2',
                            'value' => 'Yes'
                        ],
                    ],
                    'cartItem' => [
                        'price' => 23,
                        'subtotal' => 23,
                    ],
                ],
            ],
            'forUpdateMiniShoppingCart' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_0',
                            'value' => 'Yes',
                        ],
                    ],
                ],
                'cartItem' => [
                    'price' => 23,
                    'subtotal' => 22.43,
                ],
            ],
            'default' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_1',
                            'value' => 'Yes',
                        ],
                    ],
                ],
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : [];
    }
}
