<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Repository;

use Magento\Catalog\Test\Repository\Product;
use Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct as ConfigurableProductFixture;

/**
 * Class Configurable Product Repository
 *
 */
class ConfigurableProduct extends Product
{
    const CONFIGURABLE = 'configurable';

    const CONFIGURABLE_ADVANCED_PRICING = 'configurable_advanced_pricing';

    const CONFIGURABLE_MAP = 'configurable_applied_map';

    const PRODUCT_VARIATIONS = 'product_variations';

    /**
     * Construct
     *
     * @param array $defaultConfig
     * @param array $defaultData
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        parent::__construct($defaultConfig, $defaultData);
        $this->_data[self::CONFIGURABLE]['data']['affect_configurable_product_attributes'] = 'Template %isolation%';
        $this->_data[self::CONFIGURABLE_ADVANCED_PRICING] = $this->getConfigurableAdvancedPricing();
        $this->_data[self::CONFIGURABLE_MAP] = $this->addMapToConfigurable($this->_data[self::CONFIGURABLE]);
        $this->_data[self::PRODUCT_VARIATIONS] = [
            'config' => $defaultConfig,
            'data' => $this->buildProductVariations($defaultData),
        ];
        $this->_data['edit_configurable'] = $this->editConfigurable();
    }

    /**
     * Get configurable product with advanced pricing
     *
     * @return array
     */
    protected function getConfigurableAdvancedPricing()
    {
        $pricing = [
            'data' => [
                'fields' => [
                    'special_price' => [
                        'value' => '9', 'group' => ConfigurableProductFixture::GROUP_PRODUCT_PRICING,
                    ],
                ],
            ],
        ];
        $product = array_replace_recursive($this->_data[self::CONFIGURABLE], $pricing);

        return $product;
    }

    /**
     * Add advanced pricing (MAP) to configurable product
     *
     * @param array $data
     * @return array
     */
    protected function addMapToConfigurable(array $data)
    {
        $pricing = [
            'data' => [
                'fields' => [
                    'msrp_display_actual_price_type' => [
                        'value' => 'On Gesture',
                        'input_value' => '1',
                        'group' => ConfigurableProductFixture::GROUP_PRODUCT_PRICING,
                        'input' => 'select',
                    ],
                    'msrp' => ['value' => '15', 'group' => ConfigurableProductFixture::GROUP_PRODUCT_PRICING],
                ],
            ],
        ];
        $product = array_replace_recursive($data, $pricing);

        return $product;
    }

    /**
     * Build product variations data set
     *
     * @param array $defaultData
     * @return array
     */
    protected function buildProductVariations(array $defaultData)
    {
        $data = $defaultData;
        $data['affected_attribute_set'] = 'Template %isolation%';
        $data['fields'] = [
            'configurable_attributes_data' => [
                'value' => [
                    '0' => [
                        'label' => ['value' => '%new_attribute_label%'],
                        '0' => [
                            'option_label' => ['value' => '%new_attribute_option_1_label%'],
                            'include' => ['value' => 'Yes'],
                        ],
                        '1' => [
                            'option_label' => ['value' => '%new_attribute_option_2_label%'],
                            'include' => ['value' => 'Yes'],
                        ],
                    ],
                ],
                'group' => ConfigurableProductFixture::GROUP,
            ],
            'variations-matrix' => [
                'value' => [
                    '0' => [
                        'configurable_attribute' => [
                            '0' => ['attribute_option' => '%new_attribute_option_1_label%'],
                        ],
                        'value' => ['qty' => ['value' => 100]],
                    ],
                    '1' => [
                        'configurable_attribute' => [
                            '0' => ['attribute_option' => '%new_attribute_option_2_label%'],
                        ],
                        'value' => ['qty' => ['value' => 100]],
                    ],
                ],
                'group' => ConfigurableProductFixture::GROUP,
            ],
        ];
        return $data;
    }

    /**
     * Build product edit data set
     *
     * @return array
     */
    protected function editConfigurable()
    {
        $editData = [
            'config' => [
                'type_id' => 'configurable',
            ],
            'data' => [
                'fields' => [
                    'name' => [
                        'value' => substr(get_class($this), strrpos(get_class($this), '\\') + 1) . ' %isolation%_edit',
                    ],
                    'sku' => [
                        'value' =>
                            substr(get_class($this), strrpos(get_class($this), '\\') + 1) . '_sku_%isolation%_edit',
                    ],
                    'price' => [
                        'value' => '15',
                    ],
                    'configurable_attributes_data' => [
                        'value' => [
                            '0' => [
                                '2' => [
                                    'option_label' => [
                                        'value' => 'Option3_%isolation%',
                                    ],
                                    'pricing_value' => [
                                        'value' => '4',
                                    ],
                                    'is_percent' => [
                                        'value' => 'No',
                                    ],
                                    'include' => [
                                        'value' => 'Yes',
                                    ],
                                ],
                            ],
                        ],
                        'group' => ConfigurableProductFixture::GROUP,
                    ],
                    'variations-matrix' => [
                        'value' => [
                            '0' => [
                                'value' => [
                                    'display' => [
                                        'value' => 'Yes',
                                        'input' => 'checkbox',
                                    ],
                                ],
                            ],
                            '1' => [
                                'value' => [
                                    'display' => [
                                        'value' => 'Yes',
                                        'input' => 'checkbox',
                                    ],
                                ],
                            ],
                            '2' => [
                                'configurable_attribute' => [
                                    '0' => [
                                        'attribute_option' => 'Option3_%isolation%',
                                    ],
                                ],
                                'value' => [
                                    'display' => [
                                        'value' => 'Yes',
                                        'input' => 'checkbox',
                                    ],
                                    'name' => [
                                        'value' => 'Variation 2-%isolation%',
                                    ],
                                    'sku' => [
                                        'value' => 'Variation 2-%isolation%',
                                    ],
                                    'qty' => [
                                        'value' => '100',
                                    ],
                                ],
                            ],
                        ],
                        'group' => ConfigurableProductFixture::GROUP,
                    ],
                ],
            ],
        ];

        return $editData;
    }
}
