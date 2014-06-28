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

namespace Magento\Bundle\Test\Fixture\Bundle;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Bundle
 *
 * Data keys:
 *  - preset (bundle options preset name)
 *  - products (comma separated sku identifiers)
 *
 */
class Selections implements FixtureInterface
{
    /**
     * @var \Mtf\Fixture\FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * @var string
     */
    protected $currentPreset;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param $data
     * @param array $params
     * @param bool $persist
     */
    public function __construct(
        FixtureFactory $fixtureFactory,
        $data,
        array $params = [],
        $persist = false
    ) {
        $this->fixtureFactory = $fixtureFactory;

        $this->data = $data;

        if (isset($this->data['products'])) {
            $products = explode(',', $this->data['products']);
            $this->data['products'] = [];
            foreach ($products as $key => $product) {
                list($fixture, $dataSet) = explode('::', $product);
                $this->data['products'][$key] = $this->fixtureFactory
                    ->createByCode($fixture, ['dataSet' => $dataSet]);
            }
        }
        $this->currentPreset = $this->data['preset'];
        $this->data['preset'] = $this->getPreset($this->data['preset']);

        $this->params = $params;
        if ($persist) {
            $this->persist();
        }
    }

    /**
     * Persist bundle selections products
     *
     * @return void
     */
    public function persist()
    {
        if (isset($this->data['products'])) {
            foreach ($this->data['products'] as $product) {
                /** @var $product FixtureInterface */
                $product->persist();
            }
        }
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
     * Get selection for performing checkout
     *
     * @return array|null
     */
    public function getSelectionForCheckout()
    {
        /** @var \Magento\Catalog\Test\Fixture\CatalogProductSimple $product */
        $product = $this->data['products'][0];
        $selectionsForCheckout = [
            'default' => [
                0 => [
                    'value' => $product->getName(),
                    'type' => 'select',
                    'qty' => 1
                ]
            ],
            'second' => [
                0 => [
                    'value' => $product->getName(),
                    'type' => 'select',
                    'qty' => 1
                ]
            ],
        ];
        if (!isset($selectionsForCheckout[$this->currentPreset])) {
            return null;
        }
        return $selectionsForCheckout[$this->currentPreset];
    }

    /**
     * @param $name
     * @return mixed
     * @throws \InvalidArgumentException
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                'name' => 'Bundle Selections Default Preset',
                'items' => [
                    'bundle_item_0' => [
                        'title' => [
                            'value' => 'Drop-down Option'
                        ],
                        'type' => [
                            'value' => 'Drop-down',
                            'input_value' => 'select'
                        ],
                        'required' => [
                            'value' => 'Yes',
                            'input_value' => '1'
                        ],
                        'assigned_products' => [
                            0 => [
                                'search_data' => [
                                    'name' => '%item1::getProductName%',
                                ],
                                'data' => [
                                    'selection_qty' => [
                                        'value' => 1
                                    ],
                                    'product_id' => [
                                        'value' => '%item1::getProductId%'
                                    ]
                                ]
                            ],
                            1 => [
                                'search_data' => [
                                    'name' => '%item2::getProductName%',
                                ],
                                'data' => [
                                    'selection_qty' => [
                                        'value' => 1
                                    ],
                                    'product_id' => [
                                        'value' => '%item2::getProductId%'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'second' => [
                'name' => 'Bundle Selections Default Preset',
                'items' => [
                    'bundle_item_0' => [
                        'title' => [
                            'value' => 'Drop-down Second Option'
                        ],
                        'type' => [
                            'value' => 'Drop-down',
                            'input_value' => 'select'
                        ],
                        'required' => [
                            'value' => 'Yes',
                            'input_value' => '1'
                        ],
                        'assigned_products' => [
                            0 => [
                                'search_data' => [
                                    'name' => '%item1::getProductName%',
                                ],
                                'data' => [
                                    'selection_qty' => [
                                        'value' => 1
                                    ],
                                    'product_id' => [
                                        'value' => '%item1::getProductId%'
                                    ],
                                    'selection_price_value' => [
                                        'value' => '5'
                                    ]
                                ]
                            ],
                            1 => [
                                'search_data' => [
                                    'name' => '%item2::getProductName%',
                                ],
                                'data' => [
                                    'selection_qty' => [
                                        'value' => 1
                                    ],
                                    'product_id' => [
                                        'value' => '%item2::getProductId%'
                                    ],
                                    'selection_price_value' => [
                                        'value' => '10'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        if (!isset($presets[$name])) {
            throw new \InvalidArgumentException(
                sprintf('Wrong Bundle Selections preset name: %s', $name)
            );
        }
        return $presets[$name];
    }
}
