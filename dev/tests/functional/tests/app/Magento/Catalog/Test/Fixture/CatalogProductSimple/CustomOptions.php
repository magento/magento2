<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class CustomOptions
 * Custom options fixture
 *
 * Data keys:
 *  - preset (Custom options preset name)
 *  - import_products (comma separated data set name)
 */
class CustomOptions implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data;

    /**
     * Custom options data
     *
     * @var array
     */
    protected $customOptions;

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * @constructor
     * @param array $params
     * @param array $data
     * @param FixtureFactory|null $fixtureFactory
     */
    public function __construct(array $params, array $data, FixtureFactory $fixtureFactory)
    {
        $this->params = $params;
        $this->data = (!isset($data['preset']) && !isset($data['import_products'])) ? $data : [];
        $this->customOptions = $this->data;

        if (isset($data['preset'])) {
            $this->data = $this->replaceData($this->getPreset($data['preset']), mt_rand());
            $this->customOptions = $this->data;
        }
        if (isset($data['import_products'])) {
            $importData = explode(',', $data['import_products']);
            $importCustomOptions = [];
            $importProducts = [];
            foreach ($importData as $item) {
                list($fixture, $dataSet) = explode('::', $item);
                $product = $fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
                if ($product->hasData('id') !== null) {
                    $product->persist();
                }
                $importCustomOptions = array_merge($importCustomOptions, $product->getCustomOptions());
                $importProducts[] = $product->getSku();
            }
            $this->customOptions = array_merge($this->data, $importCustomOptions);
            $this->data['import'] = ['options' => $importCustomOptions, 'products' => $importProducts];
        }
    }

    /**
     * Replace custom options data
     *
     * @param array $data
     * @param int $replace
     * @return array
     */
    protected function replaceData(array $data, $replace)
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->replaceData($value, $replace);
            }
            $result[$key] = str_replace('%isolation%', $replace, $value);
        }

        return $result;
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
     * Return all custom options
     *
     * @return array
     */
    public function getCustomOptions()
    {
        return $this->customOptions;
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
            'drop_down_with_one_option_fixed_price' => [
                [
                    'title' => 'custom option drop down %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '30 bucks',
                            'price' => 30,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_drop_down_row_1',
                        ],
                    ],
                ],
            ],
            'drop_down_with_one_option_percent_price' => [
                [
                    'title' => 'custom option drop down %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '40 bucks',
                            'price' => 40,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_1',
                        ],
                    ],
                ],
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
                            'max_characters' => 45,
                        ],
                    ],
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
                            'max_characters' => 45,
                        ],
                    ]
                ],
                [
                    'title' => 'Test3 option %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => 'Test3-1 %isolation%',
                            'price' => 110.01,
                            'price_type' => 'Percent',
                            'sku' => 'sku2_%isolation%',
                        ],
                        [
                            'title' => 'Test3-2 %isolation%',
                            'price' => 210.02,
                            'price_type' => 'Fixed',
                            'sku' => 'sku3_%isolation%'
                        ],
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
                            'sku' => 'sku2_%isolation%',
                        ],
                        [
                            'title' => 'Test2 %isolation%',
                            'price' => 20.02,
                            'price_type' => 'Fixed',
                            'sku' => 'sku3_%isolation%'
                        ],
                    ]
                ],
            ],
            'default' => [
                [
                    'title' => 'custom option drop down %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '10 percent',
                            'price' => 10,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_1',
                        ],
                    ],
                ],
                [
                    'title' => 'custom option drop down2 %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '20 percent',
                            'price' => 20,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_2',
                        ],
                    ]
                ],
            ],
            'two_options' => [
                [
                    'title' => 'custom option drop down %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '10 percent',
                            'price' => 10,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_1',
                        ],
                        [
                            'title' => '20 percent',
                            'price' => 20,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_2'
                        ],
                    ],
                ],
                [
                    'title' => 'custom option field %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Field',
                    'options' => [
                        [
                            'price' => 10,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_field_option_%isolation%',
                            'max_characters' => 1024,
                        ],
                    ]
                ],
            ],
            'all_types' => [
                [
                    'title' => 'custom option field %isolation%',
                    'type' => 'Field',
                    'is_require' => 'Yes',
                    'options' => [
                        [
                            'price' => 10,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_field_option_%isolation%',
                            'max_characters' => 1024,
                        ],
                    ],
                ],
                [
                    'title' => 'custom option Area %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Area',
                    'options' => [
                        [
                            'price' => 10,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_area_row_%isolation%',
                            'max_characters' => '10',
                        ],
                    ]
                ],
                [
                    'title' => 'custom option File %isolation%',
                    'is_require' => 'No',
                    'type' => 'File',
                    'options' => [
                        [
                            'price' => 10,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_file_row_%isolation%',
                            'file_extension' => 'jpg',
                            'image_size_x' => '100',
                            'image_size_y' => '100',
                        ],
                    ]
                ],
                [
                    'title' => 'custom option drop down %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Drop-down',
                    'options' => [
                        [
                            'title' => '10 percent',
                            'price' => 10,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_1_%isolation%',
                        ],
                        [
                            'title' => '20 percent',
                            'price' => 20,
                            'price_type' => 'Percent',
                            'sku' => 'sku_drop_down_row_2_%isolation%'
                        ],
                        [
                            'title' => '30 fixed',
                            'price' => 30,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_drop_down_row_3_%isolation%'
                        ],
                    ]
                ],
                [
                    'title' => 'custom option Radio Buttons %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Radio Buttons',
                    'options' => [
                        [
                            'title' => '20 fixed',
                            'price' => 20,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_radio_buttons_row%isolation%',
                        ],
                    ]
                ],
                [
                    'title' => 'custom option Checkbox %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Checkbox',
                    'options' => [
                        [
                            'title' => '20 fixed',
                            'price' => 20,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_checkbox_row%isolation%',
                        ],
                    ]
                ],
                [
                    'title' => 'custom option Multiple Select %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Multiple Select',
                    'options' => [
                        [
                            'title' => '20 fixed',
                            'price' => 20,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_multiple_select_row%isolation%',
                        ],
                    ]
                ],
                [
                    'title' => 'custom option Date %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Date',
                    'options' => [
                        [
                            'price' => 20,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_date_row%isolation%',
                        ],
                    ]
                ],
                [
                    'title' => 'custom option Date & Time %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Date & Time',
                    'options' => [
                        [
                            'price' => 20,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_date_and_time_row%isolation%',
                        ],
                    ]
                ],
                [
                    'title' => 'custom option Time %isolation%',
                    'is_require' => 'Yes',
                    'type' => 'Time',
                    'options' => [
                        [
                            'price' => 20,
                            'price_type' => 'Fixed',
                            'sku' => 'sku_time_row%isolation%',
                        ],
                    ]
                ],
            ],
        ];
        if (!isset($presets[$name])) {
            return null;
        }
        return $presets[$name];
    }
}
