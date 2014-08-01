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

namespace Magento\ConfigurableProduct\Test\Fixture\CatalogProductConfigurable;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class ConfigurableAttributesData
 * Source configurable attributes data of the configurable products
 */
class ConfigurableAttributesData implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data = [
        'products' => [],
        'attributes' => []
    ];

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Current preset
     *
     * @var string
     */
    protected $currentPreset;

    /**
     * Fixture factory
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Source constructor
     *
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __construct(FixtureFactory $fixtureFactory, array $data, array $params = [])
    {
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
        $data['products'] = empty($data['products']) ? [] : $data['products'];
        $data['attributes'] = empty($data['attributes']) ? [] : $data['attributes'];

        if (isset($data['preset']) && $data['preset'] !== '-') {
            $this->currentPreset = $data['preset'];
            $this->data = $this->getPreset($this->currentPreset);
            unset($data['preset']);
        }

        foreach ($data['products'] as $key => $product) {
            $this->data['products'][$key] = $product;
        }
        foreach ($data['attributes'] as $key => $attribute) {
            $this->data['attributes'][$key] = $attribute;
        }

        $this->prepareProducts();
        $this->prepareAttributes();
    }

    /**
     * Persist configurable attribute data
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Preparation of products fixture
     *
     * @return void
     */
    protected function prepareProducts()
    {
        if (!empty($this->data['products'])) {
            foreach ($this->data['products'] as $key => $product) {
                if (is_string($product)) {
                    list($fixture, $dataSet) = explode('::', $product);
                    /** @var $product InjectableFixture */
                    $product = $this->fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
                    if (!$product->hasData('id')) {
                        $product->persist();
                    }
                }
                $this->data['products'][$key] = $product;
            }
        }
    }

    /**
     * Preparation of attributes fixture
     *
     * @return void
     */
    protected function prepareAttributes()
    {
        if (!empty($this->data['attributes'])) {
            foreach ($this->data['attributes'] as $key => $attribute) {
                if (is_string($attribute)) {
                    list($fixture, $dataSet) = explode('::', $attribute);
                    /** @var $attribute InjectableFixture */
                    $attribute = $this->fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
                    if (!$attribute->hasData('id')) {
                        $attribute->persist();
                    }
                }
                $this->data['attributes'][$key] = $attribute;
            }
            // Set options used.
            $this->setOptions();
            // Initialization data matrix
            $this->matrixInit();
            // Assigning products
            $this->assigningProducts();
        }
    }

    /**
     * Set options used
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function setOptions()
    {
        $fixtures = $this->data['attributes'];
        foreach (array_keys($this->data['attributes_data']) as $key) {
            $fixtureData = $fixtures[$key]->getData();
            $this->data['attributes_data'][$key]['id'] = isset($fixtureData['attribute_id'])
                ? $fixtureData['attribute_id']
                : $key;
            $this->data['attributes_data'][$key]['title'] = $fixtureData['frontend_label'];
            $this->data['attributes_data'][$key]['code'] = $fixtureData['frontend_label'];
            foreach ($this->data['attributes_data'][$key]['options'] as $optionKey => &$value) {
                if (!isset($fixtureData['options'][$optionKey])) {
                    unset($this->data['attributes_data'][$key]['options'][$optionKey]);
                    continue;
                }
                $value['id'] = isset($fixtureData['options'][$optionKey]['id'])
                    ? $fixtureData['options'][$optionKey]['id']
                    : $optionKey;
                $value['name'] = empty($fixtureData['options'][$optionKey]['view'])
                    ? $fixtureData['options'][$optionKey]['admin']
                    : $fixtureData['options'][$optionKey]['view'];
            }
            unset($value);
        }
    }

    /**
     * Prepare data for matrix
     *
     * @return array
     */
    protected function prepareDataMatrix()
    {
        $attributes = [];
        foreach ($this->data['attributes_data'] as $attributeKey => $attribute) {
            $options = [];
            foreach ($attribute['options'] as $optionKey => $option) {
                if ($option['include'] === 'Yes') {
                    $option['key'] = $optionKey;
                    $options[] = $option;
                }
            }
            $attributes[] = [
                'key' => $attributeKey,
                'id' => $attribute['id'],
                'code' => $attribute['code'],
                'options' => $options
            ];
        }

        return $attributes;
    }

    /**
     * Generation variants
     *
     * @return array
     */
    protected function generationVariants()
    {
        $attributes = array_reverse($this->prepareDataMatrix());
        $variations = [];
        $attributesCount = count($attributes);
        $currentVariation = array_fill(0, $attributesCount, 0);
        $lastAttribute = $attributesCount - 1;
        do {
            for ($attributeIndex = 0; $attributeIndex < $attributesCount - 1; ++$attributeIndex) {
                if ($currentVariation[$attributeIndex] >= count($attributes[$attributeIndex]['options'])) {
                    $currentVariation[$attributeIndex] = 0;
                    ++$currentVariation[$attributeIndex + 1];
                }
            }
            if ($currentVariation[$lastAttribute] >= count($attributes[$lastAttribute]['options'])) {
                break;
            }

            $filledVariation = [];
            for ($attributeIndex = $attributesCount; $attributeIndex--;) {
                $currentAttribute = $attributes[$attributeIndex];
                $currentVariationValue = $currentVariation[$attributeIndex];
                $filledVariation[$currentAttribute['key']] = $currentAttribute['options'][$currentVariationValue];
                $filledVariation[$currentAttribute['key']]['code'] = $currentAttribute['code'];
            }

            $variationsKeys = [];
            $placeholder = [];
            $optionsNames = [];
            foreach ($filledVariation as $key => $option) {
                $variationKey = sprintf('%%attribute_%d-option_%d%%', $key, $option['key']);
                $variationsKeys[] = $variationKey;
                $keyName = sprintf('%%attribute_%d-option_%d_name%%', $key, $option['key']);
                $keyId = sprintf('%%attribute_%d-option_%d_id%%', $key, $option['key']);
                $attributeCode = sprintf('%%attribute_%d_code%%', $key);
                $optionsNames[] = $option['name'];
                $placeholder += [
                    $keyName => $option['name'],
                    $keyId => $option['id'],
                    $variationKey => $option['id'],
                    $attributeCode => $option['code']
                ];
            }

            $variationsKey = implode('-', $variationsKeys);
            $variations[$variationsKey]['placeholder'] = $placeholder;
            $variations[$variationsKey]['options_names'] = $optionsNames;
            $currentVariation[0]++;
        } while (true);

        return $variations;
    }

    /**
     * Initialization data matrix
     *
     * @return void
     */
    protected function matrixInit()
    {
        // Generation variants
        $variations = $this->generationVariants();

        foreach (array_keys($this->data['matrix']) as $key) {
            if (isset($variations[$key])) {
                foreach ($this->data['matrix'][$key] as $innerKey => &$value) {
                    if ($innerKey === 'configurable_attribute') {
                        $value = strtr(json_encode($value), $variations[$key]['placeholder']);
                    } elseif (is_string($value)) {
                        $value = strtr($value, $variations[$key]['placeholder']);
                    }
                }
                unset($value);
                $newKey = strtr($key, $variations[$key]['placeholder']);
                $this->data['matrix'][$newKey] = $this->data['matrix'][$key];
                $this->data['matrix'][$newKey]['options_names'] = $variations[$key]['options_names'];
                unset($this->data['matrix'][$key]);
            }
        }
    }

    /**
     * Assigning products
     *
     * @return void
     */
    protected function assigningProducts()
    {
        foreach ($this->data['products'] as $key => $product) {
            foreach ($this->data['matrix'] as &$value) {
                if (isset($value['associated_product_ids'][$key])) {
                    unset($value['associated_product_ids'][$key]);
                    /** @var $attribute InjectableFixture */
                    $value['associated_product_ids'][] = $product->getId();
                    $value['name'] = $product->getName();
                    $value['sku'] = $product->getSku();
                }
            }
            unset($value);
        }
    }

    /**
     * Return prepared data set
     *
     * @param string|null $key
     * @return mixed
     */
    public function getData($key = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Preset array
     *
     * @param string $name
     * @return mixed|null
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function getPreset($name)
    {
        $presets = [
            'default' => [
                'attributes_data' => [
                    [
                        'id' => '%id%',
                        'title' => '%title%',
                        'label' => 'Test variation1 label',
                        'options' => [
                            [
                                'id' => '%id%',
                                'name' => '%name%',
                                'pricing_value' => 12.00,
                                'include' => 'Yes',
                                'is_percent' => 'No'
                            ],
                            [
                                'id' => '%id%',
                                'name' => '%name%',
                                'pricing_value' => 20.00,
                                'include' => 'Yes',
                                'is_percent' => 'No'
                            ],
                            [
                                'id' => '%id%',
                                'name' => '%name%',
                                'pricing_value' => 18.00,
                                'include' => 'Yes',
                                'is_percent' => 'No'
                            ],
                        ]
                    ],
                    [
                        'id' => '%id%',
                        'title' => '%title%',
                        'label' => 'Test variation2 label',
                        'options' => [
                            [
                                'id' => '%id%',
                                'name' => '%name%',
                                'pricing_value' => 42.00,
                                'include' => 'Yes',
                                'is_percent' => 'No'
                            ],
                            [
                                'id' => '%id%',
                                'name' => '%name%',
                                'pricing_value' => 40.00,
                                'include' => 'Yes',
                                'is_percent' => 'No'
                            ],
                            [
                                'id' => '%id%',
                                'name' => '%name%',
                                'pricing_value' => 48.00,
                                'include' => 'Yes',
                                'is_percent' => 'No'
                            ],
                        ]
                    ]
                ],
                'products' => [

                ],
                'attributes' => [
                    'catalogProductAttribute::attribute_type_dropdown',
                    'catalogProductAttribute::attribute_type_dropdown'
                ],
                'matrix' => [
                    '%attribute_0-option_0%-%attribute_1-option_0%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_0%',
                            '%attribute_1_code%' => '%attribute_1-option_0%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_0_name% %attribute_1-option_0_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_0_id%_%attribute_1-option_0_id%',
                        'qty' => 10,
                        'weight' => 1
                    ],
                    '%attribute_0-option_0%-%attribute_1-option_1%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_0%',
                            '%attribute_1_code%' => '%attribute_1-option_1%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_0_name% %attribute_1-option_1_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_0_id%_%attribute_1-option_1_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
                    ],
                    '%attribute_0-option_0%-%attribute_1-option_2%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_0%',
                            '%attribute_1_code%' => '%attribute_1-option_2%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_0_name% %attribute_1-option_2_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_0_id%_%attribute_1-option_2_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
                    ],
                    '%attribute_0-option_1%-%attribute_1-option_0%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_1%',
                            '%attribute_1_code%' => '%attribute_1-option_0%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_1_name% %attribute_1-option_0_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_1_id%_%attribute_1-option_0_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
                    ],
                    '%attribute_0-option_1%-%attribute_1-option_1%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_1%',
                            '%attribute_1_code%' => '%attribute_1-option_1%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_1_name% %attribute_1-option_1_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_1_id%_%attribute_1-option_1_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
                    ],
                    '%attribute_0-option_1%-%attribute_1-option_2%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_1%',
                            '%attribute_1_code%' => '%attribute_1-option_2%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_1_name% %attribute_1-option_2_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_1_id%_%attribute_1-option_2_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
                    ],
                    '%attribute_0-option_2%-%attribute_1-option_0%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_2%',
                            '%attribute_1_code%' => '%attribute_1-option_0%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_2_name% %attribute_1-option_0_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_2_id%_%attribute_1-option_0_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
                    ],
                    '%attribute_0-option_2%-%attribute_1-option_1%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_2%',
                            '%attribute_1_code%' => '%attribute_1-option_1%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_2_name% %attribute_1-option_1_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_2_id%_%attribute_1-option_1_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
                    ],
                    '%attribute_0-option_2%-%attribute_1-option_2%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_2%',
                            '%attribute_1_code%' => '%attribute_1-option_2%'
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_2_name% %attribute_1-option_2_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_2_id%_%attribute_1-option_2_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
                    ]
                ]
            ],
            'one_variation' => [
                'attributes_data' => [
                    [
                        'id' => '%id%',
                        'title' => '%title%',
                        'label' => 'Test variation1 label',
                        'options' => [
                            [
                                'id' => '%id%',
                                'name' => '%name%',
                                'pricing_value' => 12.00,
                                'include' => 'Yes',
                                'is_percent' => 'No'
                            ]
                        ]
                    ]
                ],
                'products' => [

                ],
                'attributes' => [
                    'catalogProductAttribute::attribute_type_dropdown'
                ],
                'matrix' => [
                    '%attribute_0-option_0%' => [
                        'configurable_attribute' => [
                            '%attribute_0_code%' => '%attribute_0-option_0%',
                        ],
                        'associated_product_ids' => [],
                        'name' => 'In configurable %isolation% %attribute_0-option_0_name%',
                        'sku' => 'sku_configurable_%isolation%_%attribute_0-option_0_id%',
                        'qty' => 10,
                        'weight' => 1,
                        'options_names' => []
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
