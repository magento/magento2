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
 * Class CustomOptions
 * Custom options fixture
 *
 * Data keys:
 *  - preset (Custom options preset name)
 *  - products (comma separated sku identifiers)
 */
class CustomOptions implements FixtureInterface
{
    /**
     * @var \Mtf\Fixture\FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @param array $params
     * @param array $data
     */
    public function __construct(array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['preset'])) {
            $this->data = $this->getPreset($data['preset']);
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
     * @param $key [optional]
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
     * @param string $name
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getPreset($name)
    {
        $presets = [
            'MAGETWO-23062' => [
                [
                    'title' => 'custom option drop down',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '30 bucks',
                            'price' => 30,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_drop_down_row_1'
                        ]
                    ]
                ]
            ],
            'MAGETWO-23063' => [
                [
                    'title' => 'custom option drop down',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '40 bucks',
                            'price' => 40,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_1'
                        ]
                    ]
                ]
            ],
            'MAGETWO-23066' => [
                [
                    'title' => 'custom option drop down',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '30 bucks',
                            'price' => 30,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_drop_down_row_1'
                        ]
                    ]
                ]
            ],
            'MAGETWO-23069' => [
                [
                    'title' => 'custom option drop down',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '10 percent',
                            'price' => 10,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_1'
                        ]
                    ]
                ]
            ],
            'options-suite' => [
                [
                    'title' => 'Test1 option %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Field',
                    'options' => [
                        [
                            'price' => 120.03,
                            'price_type' => 'Fixed',
                            'sku' => 'sku1_%isolation%',
                            'max_characters' => 45
                        ]
                    ]
                ],
                [
                    'title' => 'Test2 option %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Field',
                    'options' => [
                        [
                            'price' => 120.03,
                            'price_type' => 'Fixed',
                            'sku' => 'sku1_%isolation%',
                            'max_characters' => 45
                        ]
                    ]
                ],
                [
                    'title' => 'Test3 option %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => 'Test1 %isolation%',
                            'price' => 110.01,
                            'price_type' => 'Percent',
                            'sku' => 'sku2_%isolation%'
                        ],
                        [
                            'title' => 'Test2 %isolation%',
                            'price' => 210.02,
                            'price_type' => 'Fixed',
                            'sku' => 'sku3_%isolation%'
                        ]
                    ]
                ],
                [
                    'title' => 'Test4 option %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => 'Test1 %isolation%',
                            'price' => 10.01,
                            'price_type' => 'Percent',
                            'sku' => 'sku2_%isolation%'
                        ],
                        [
                            'title' => 'Test2 %isolation%',
                            'price' => 20.02,
                            'price_type' => 'Fixed',
                            'sku' => 'sku3_%isolation%'
                        ]
                    ]
                ]
            ],
            'default' => [
                [
                    'title' => 'custom option drop down',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '10 percent',
                            'price' => 10,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_1'
                        ]
                    ]
                ],
                [
                    'title' => 'custom option drop down2',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '20 percent',
                            'price' => 20,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_2'
                        ]
                    ]
                ]
            ],
            'two_options' => [
                [
                    'title' => 'custom option drop down',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '10 percent',
                            'price' => 10,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_1'
                        ]
                    ]
                ],
                [
                    'title' => 'custom option drop down2',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '20 percent',
                            'price' => 20,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_2'
                        ]
                    ]
                ]
            ]
        ];
        if (!isset($presets[$name])) {
            return null;
        }
        return $presets[$name];
    }
}
