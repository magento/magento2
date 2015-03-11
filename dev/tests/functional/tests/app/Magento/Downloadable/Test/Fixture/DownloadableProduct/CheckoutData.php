<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Test\Fixture\DownloadableProduct;

/**
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
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getPreset($name)
    {
        $presets = [
            'with_two_separately_links' => [
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
                    'subtotal' => 23,
                ],
            ],
            'with_two_bought_links' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_0',
                            'value' => 'Yes',
                        ],
                        [
                            'label' => 'link_1',
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
                            'label' => 'link_0',
                            'value' => 'Yes',
                        ],
                    ],
                ],
            ],

            'one_custom_option_and_downloadable_link' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0'
                        ],
                    ],
                    'links' => [
                        [
                            'label' => 'link_0',
                            'value' => 'Yes'
                        ]
                    ],
                ]
            ],

            'one_dollar_product_with_separated_link' => [
                'options' => [
                    'links' => [
                        [
                            'label' => 'link_0',
                            'value' => 'Yes'
                        ]
                    ],
                ],
                'cartItem' => [
                    'price' => 3,
                    'subtotal' => 3,
                ],
            ],

            'one_dollar_product_with_no_separated_link' => [
                'cartItem' => [
                    'price' => 1,
                    'subtotal' => 1,
                ],
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : [];
    }
}
