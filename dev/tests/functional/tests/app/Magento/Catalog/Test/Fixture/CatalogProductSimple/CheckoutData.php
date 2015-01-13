<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Mtf\Fixture\FixtureInterface;

/**
 * Class CheckoutData
 * Data for fill product form on frontend
 *
 * Data keys:
 *  - preset (Checkout data verification preset name)
 */
class CheckoutData implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data;

    /**
     * @constructor
     * @param array $params
     * @param array $data
     */
    public function __construct(array $params, array $data = [])
    {
        $this->params = $params;
        $preset = [];
        if (isset($data['preset'])) {
            $preset = $this->getPreset($data['preset']);
            unset($data['preset']);
        }
        $this->data = empty($preset) ? $data : array_replace_recursive($preset, $data);
    }

    /**
     * Persist custom selections products
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
        return $this->params;
    }

    /**
     * Return array preset
     *
     * @param string $name
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getPreset($name)
    {
        $presets = [
            'with_two_custom_option' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'Content option %isolation%',
                        ],
                    ],
                ],
                'qty' => 1,
                'cartItem' => [
                    'price' => 340,
                    'subtotal' => 340,
                ],
            ],
            'forUpdateMiniShoppingCart' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_1',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'Content option %isolation%',
                        ],
                    ],
                ],
                'qty' => 2,
                'cartItem' => [
                    'price' => 370,
                    'subtotal' => 740,
                ],
            ],
            'options-suite' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'Field value 1 %isolation%',
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'Field value 2 %isolation%'
                        ],
                        [
                            'title' => 'attribute_key_2',
                            'value' => 'option_key_1'
                        ],
                        [
                            'title' => 'attribute_key_3',
                            'value' => 'option_key_0'
                        ],
                    ],
                ],
            ],
            'drop_down_with_one_option_fixed_price' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                    ],
                ],
            ],
            'drop_down_with_one_option_percent_price' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0',
                        ],
                    ],
                ],
            ],
            'order_default' => [
                'qty' => 1,
                'cartItem' => [],
            ],
            'two_products' => [
                'qty' => 2,
                'cartItem' => [
                    'price' => 100,
                    'subtotal' => 200,
                ],
            ],
            'order_big_qty' => [
                'qty' => 900,
            ],
            'order_custom_price' => [
                'qty' => 3,
                'checkout_data' => [
                    'use_custom_price' => "Yes",
                    'custom_price' => 100,
                ],
                'cartItem' => [],
            ],
        ];
        return isset($presets[$name]) ? $presets[$name] : [];
    }
}
