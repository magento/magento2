<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Fixture\BundleProduct;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Class BundleSelections
 * Bundle selections preset
 */
class BundleSelections implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data;

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->params = $params;
        $this->data = !isset($data['preset']) ? $data : [];

        if (isset($data['preset'])) {
            $this->data = $this->getPreset($data['preset']);
            if (!empty($data['products'])) {
                $this->data['products'] = [];
                $this->data['products'] = explode('|', $data['products']);
                foreach ($this->data['products'] as $key => $products) {
                    $this->data['products'][$key] = explode(',', $products);
                }
            }
        }

        if (!empty($this->data['products'])) {
            $productsSelections = $this->data['products'];
            $this->data['products'] = [];
            foreach ($productsSelections as $index => $products) {
                $productSelection = [];
                foreach ($products as $key => $product) {
                    if ($product instanceof FixtureInterface) {
                        $productSelection[$key] = $product;
                        continue;
                    }
                    list($fixture, $dataSet) = explode('::', $product);
                    $productSelection[$key] = $fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
                    $productSelection[$key]->persist();
                    $this->data['bundle_options'][$index]['assigned_products'][$key]['search_data']['name'] =
                        $productSelection[$key]->getName();
                }
                $this->data['products'][] = $productSelection;
            }
        }
    }

    /**
     * Persist bundle selections products
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
     * Preset array
     *
     * @param string $name
     * @return mixed
     * @throws \InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getPreset($name)
    {
        $presets = [
            'default_dynamic' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar',
                    ],
                ],
            ],

            'default_fixed' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 5.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 6.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar',
                    ],
                ],
            ],
            
            'fixed_100_dollar' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 10.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 560.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::product_10_dollar',
                        'catalogProductSimple::default',
                    ],
                ],
            ],

            'second' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 5.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 10.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar',
                    ],
                ],
            ],

            'all_types_fixed' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 5.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 6.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                    [
                        'title' => 'Radio Button Option',
                        'type' => 'Radio Buttons',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 5.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 6.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ]
                    ],
                    [
                        'title' => 'Checkbox Option',
                        'type' => 'Checkbox',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 5.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 6.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ]
                    ],
                    [
                        'title' => 'Multiple Select Option',
                        'type' => 'Multiple Select',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 5.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 6.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ]
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar',
                    ],
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar'
                    ],
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar'
                    ],
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar'
                    ],
                ],
            ],

            'all_types_dynamic' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                    [
                        'title' => 'Radio Button Option',
                        'type' => 'Radio Buttons',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ]
                            ],
                        ]
                    ],
                    [
                        'title' => 'Checkbox Option',
                        'type' => 'Checkbox',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ]
                            ],
                        ]
                    ],
                    [
                        'title' => 'Multiple Select Option',
                        'type' => 'Multiple Select',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ]
                            ],
                        ]
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar',
                    ],
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar'
                    ],
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar'
                    ],
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar'
                    ],
                ],
            ],

            'with_not_required_options' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'No',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                    'selection_price_value' => 45,
                                    'selection_price_type' => 'Fixed',
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                    'selection_price_value' => 43,
                                    'selection_price_type' => 'Fixed',
                                ]
                            ],
                        ],
                    ],
                    [
                        'title' => 'Radio Button Option',
                        'type' => 'Radio Buttons',
                        'required' => 'No',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                    'selection_price_value' => 45,
                                    'selection_price_type' => 'Fixed',
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                    'selection_price_value' => 43,
                                    'selection_price_type' => 'Fixed',
                                ]
                            ],
                        ]
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar',
                    ],
                    [
                        'catalogProductSimple::default',
                        'catalogProductSimple::product_100_dollar'
                    ],
                ],
            ],

            'two_options_with_fixed_and_percent_prices' => [
                'bundle_options' => [
                    [
                        'title' => 'BundleOption1',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 10.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 20.00,
                                    'selection_price_type' => 'Percent',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::product_without_category',
                        'catalogProductSimple::product_without_category',
                    ],
                ],
            ],

            'two_options_assigned_products_without_category' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::product_without_category',
                        'catalogProductSimple::product_without_category',
                    ],
                ],
            ],

            'one_options_assigned_simple_big_qty' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_qty' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::simple_big_qty',
                    ],
                ],
            ],

            'required_two_fixed_options' => [
                'bundle_options' => [
                    [
                        'title' => 'Drop-down Option',
                        'type' => 'Drop-down',
                        'required' => 'Yes',
                        'assigned_products' => [
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 10.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ],
                            ],
                            [
                                'search_data' => [
                                    'name' => '%product_name%',
                                ],
                                'data' => [
                                    'selection_price_value' => 20.00,
                                    'selection_price_type' => 'Fixed',
                                    'selection_qty' => 1,
                                ]
                            ],
                        ],
                    ],
                ],
                'products' => [
                    [
                        'catalogProductSimple::simple',
                        'catalogProductVirtual::product_15_dollar',
                    ],
                ],
            ],
        ];
        if (!isset($presets[$name])) {
            throw new \InvalidArgumentException(
                sprintf('Wrong Bundle Selections preset name: %s', $name)
            );
        }
        return $presets[$name];
    }
}
