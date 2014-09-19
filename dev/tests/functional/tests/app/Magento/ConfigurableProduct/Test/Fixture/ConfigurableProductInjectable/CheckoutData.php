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

namespace Magento\ConfigurableProduct\Test\Fixture\ConfigurableProductInjectable;

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
                        ]
                    ],
                    'qty' => 3
                ],
                'cartItem' => [
                    'price' => 172,
                    'qty' => 3,
                    'subtotal' => 516
                ]
            ],
            'two_options' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ]
                    ]
                ],
                'cartItem' => [
                    'price' => 101,
                ]
            ],
            'two_new_options' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_1',
                        ]
                    ]
                ],
                'cartItem' => [
                    'price' => 102,
                ]
            ],
            'two_new_options_with_special_price' =>[
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_1',
                        ]
                    ]
                ],
                'cartItem' => [
                    'price' => 12,
                ]
            ],
            'two_options_with_assigned_product' => [
                'options' => [
                    'configurable_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ]
                    ]
                ],
                'cartItem' => [
                    'price' => 101,
                ]
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : null;
    }
}
