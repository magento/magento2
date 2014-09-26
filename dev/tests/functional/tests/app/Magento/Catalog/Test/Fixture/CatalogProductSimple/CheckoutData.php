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
        $this->data = isset($data['preset']) ? $this->getPreset($data['preset']) : [];

        if (isset($data['data'])) {
            $this->data = array_replace_recursive($this->data, $data['data']);
        }
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
     */
    protected function getPreset($name)
    {
        $presets = [
            'with_two_custom_option' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0'
                        ],
                        [
                            'title' => 'attribute_key_1',
                            'value' => 'Content option %isolation%',
                        ]
                    ]
                ],
                'cartItem' => [
                    'price' => 340,
                    'subtotal' => 340
                ]
            ],
            'options-suite' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'Field value 1 %isolation%'
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
                        ]
                    ]
                ]
            ],
            'drop_down_with_one_option_fixed_price' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0'
                        ]
                    ]
                ]
            ],
            'drop_down_with_one_option_percent_price' => [
                'options' => [
                    'custom_options' => [
                        [
                            'title' => 'attribute_key_0',
                            'value' => 'option_key_0'
                        ]
                    ]
                ]
            ],
            'order_default' => [
                'options' => [
                    'qty' => 1
                ],
                'cartItem' => []
            ],
            'two_products' => [
                'options' => [
                    'qty' => 2
                ],
                'cartItem' => [
                    'price' => 100,
                    'subtotal' => 200
                ]
            ],
            'order_big_qty' => [
                'options' => [
                    'qty' => 2
                ],
                'cartItem' => []
            ]
        ];
        return isset($presets[$name]) ? $presets[$name] : [];
    }
}
