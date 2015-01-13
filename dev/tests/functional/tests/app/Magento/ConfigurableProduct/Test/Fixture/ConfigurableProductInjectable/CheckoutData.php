<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;

/**
 * Data for fill product form on frontend.
 *
 * Data keys:
 *  - preset (Checkout data verification preset name).
 */
class CheckoutData extends \Magento\Catalog\Test\Fixture\CatalogProductSimple\CheckoutData
{
    /**
     * Get preset array
     *
     * @param $name
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'option_key_1',
                        ],
                    ],
                ],
                'qty' => 3,
                'cartItem' => [
                    'price' => 172,
                    'qty' => 3,
                    'subtotal' => 516,
                ],
            ],
            'forUpdateMiniShoppingCart' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_1',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'option_key_0',
                        ],
                    ],
                ],
                'qty' => 1,
                'cartItem' => [
                    'price' => 172,
                    'qty' => 1,
                    'subtotal' => 172,
                ],
            ],
            'two_options' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                    ],
                ],
                'cartItem' => [
                    'price' => 101,
                ],
            ],
            'two_new_options' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_1',
                        ],
                    ],
                ],
                'cartItem' => [
                    'price' => 102,
                ],
            ],
            'two_attributes' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'option_key_0',
                        ],
                    ],
                ],
                'cartItem' => [
                    'price' => 112,
                ],
            ],
            'three_attributes' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_2',
                            'value' => 'option_key_0',
                        ],
                    ],
                ],
                'cartItem' => [
                    'price' => 112,
                ],
            ],
            'two_new_options_with_special_price' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_1',
                        ],
                    ],
                ],
                'cartItem' => [
                    'price' => 12,
                ],
            ],
            'two_options_with_assigned_product' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                    ],
                ],
                'cartItem' => [
                    'price' => 101,
                ],
            ],
            'with_one_option' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                    ],
                ],
                'qty' => 1,
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : null;
    }
}
