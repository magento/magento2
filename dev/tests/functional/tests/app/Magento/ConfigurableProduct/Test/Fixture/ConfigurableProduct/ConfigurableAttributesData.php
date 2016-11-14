<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ConfigurableProduct\Test\Fixture\ConfigurableProduct;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\InjectableFixture;
use Magento\Mtf\Repository\RepositoryFactory;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;

/**
 * Source configurable attributes data of the configurable products.
 */
class ConfigurableAttributesData extends DataSource
{
    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Prepared attributes data.
     *
     * @var array
     */
    protected $attributesData = [];

    /**
     * Prepared variation matrix.
     *
     * @var array
     */
    protected $variationsMatrix = [];

    /**
     * Prepared attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Prepared Attribute Set.
     *
     * @var CatalogAttributeSet
     */
    protected $attributeSet;

    /**
     * Prepared products.
     *
     * @var array
     */
    protected $products = [];

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $data
     * @param array $params [optional]
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $data,
        array $params = []
    ) {
        $this->fixtureFactory = $fixtureFactory;
        $this->params = $params;
        $dataset = [];
        if (isset($data['dataset']) && isset($this->params['repository'])) {
            $dataset = $repositoryFactory->get($this->params['repository'])->get($data['dataset']);
            unset($data['dataset']);
        }

        $data = array_replace_recursive($data, $dataset);

        if (!empty($data)) {
            $this->prepareAttributes($data);
            $this->prepareAttributesData($data);
            $this->prepareProducts($data);
            $this->prepareVariationsMatrix($data);
            $this->prepareData();
        }
    }

    /**
     * Prepare attributes.
     *
     * @param array $data
     * @return void
     */
    protected function prepareAttributes(array $data)
    {
        if (!isset($data['attributes'])) {
            return;
        }

        foreach ($data['attributes'] as $key => $attribute) {
            if (is_string($attribute)) {
                list($fixture, $dataset) = explode('::', $attribute);
                /** @var InjectableFixture $attribute */
                $attribute = $this->fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
            }
            if (!$attribute->hasData('attribute_id')) {
                $attribute->persist();
            }
            $this->attributes[$key] = $attribute;
        }
    }

    /**
     * Prepare attributes data.
     *
     * @param array $data
     * @return void
     */
    protected function prepareAttributesData(array $data)
    {
        foreach ($this->attributes as $attributeKey => $attribute) {
            $attributeData = $attribute->getData();
            $options = [];
            foreach ($attributeData['options'] as $key => $option) {
                $options['option_key_' . $key] = $option;
            }
            $attributeData['options'] = $options;

            $this->attributesData[$attributeKey] = $attributeData;
        }

        $this->attributesData = array_replace_recursive(
            isset($data['attributes_data']) ? $data['attributes_data'] : [],
            $this->attributesData
        );
    }

    /**
     * Create and assign products.
     *
     * @return void
     */
    public function generateProducts()
    {
        $assignedProducts = ['products' => []];
        foreach (array_keys($this->variationsMatrix) as $variation) {
            $assignedProducts['products'][$variation] = 'catalogProductSimple::default';
        }

        $this->prepareProducts($assignedProducts);
    }

    /**
     * Prepare products.
     *
     * @param array $data
     * @return void
     */
    protected function prepareProducts(array $data)
    {
        if (!isset($data['products'])) {
            return;
        }

        $attributeSetData = [];
        if (!empty($this->attributes)) {
            $attributeSetData['attribute_set_id'] = ['attribute_set' => $this->createAttributeSet()];
        }

        foreach ($data['products'] as $key => $product) {
            if (is_string($product)) {
                list($fixture, $dataset) = explode('::', $product);
                $attributeData = ['attributes' => $this->getProductAttributeData($key)];
                $productData = isset($this->variationsMatrix[$key]) ? $this->variationsMatrix[$key] : [];

                $product = $this->fixtureFactory->createByCode(
                    $fixture,
                    [
                        'dataset' => $dataset,
                        'data' => array_merge($attributeSetData, $attributeData, $productData)
                    ]
                );
            }
            if (!$product->hasData('id')) {
                $product->persist();
            }

            $this->products[$key] = $product;
        }
    }

    /**
     * Create attribute set.
     *
     * @return FixtureInterface
     */
    protected function createAttributeSet()
    {
        if (!$this->attributeSet) {
            $this->attributeSet = $this->fixtureFactory->createByCode(
                'catalogAttributeSet',
                [
                    'dataset' => 'custom_attribute_set',
                    'data' => [
                        'assigned_attributes' => [
                            'attributes' => array_values($this->attributes),
                        ],
                    ]
                ]
            );
            $this->attributeSet->persist();
        }

        return $this->attributeSet;
    }

    /**
     * Get prepared attribute data for persist product.
     *
     * @param string $key
     * @return array
     */
    protected function getProductAttributeData($key)
    {
        $compositeKeys = explode(' ', $key);
        $data = [];

        foreach ($compositeKeys as $compositeKey) {
            $attributeId = $this->getAttributeOptionId($compositeKey);
            if ($attributeId) {
                $compositeKey = explode(':', $compositeKey);
                $attributeKey = $compositeKey[0];
                $data[$this->attributesData[$attributeKey]['attribute_code']] = $attributeId;
            }
        }

        return $data;
    }

    /**
     * Get id of attribute option by composite key.
     *
     * @param string $compositeKey
     * @return int|null
     */
    protected function getAttributeOptionId($compositeKey)
    {
        list($attributeKey, $optionKey) = explode(':', $compositeKey);
        return isset($this->attributesData[$attributeKey]['options'][$optionKey]['id'])
            ? $this->attributesData[$attributeKey]['options'][$optionKey]['id']
            : null;
    }

    /**
     * Prepare data for matrix.
     *
     * @param array $data
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function prepareVariationsMatrix(array $data)
    {
        $variationsMatrix = [];

        // generate matrix
        foreach ($this->attributesData as $attributeKey => $attribute) {
            $variationsMatrix = $this->addVariationMatrix($variationsMatrix, $attribute, $attributeKey);
        }

        foreach ($data['matrix'] as $key => $value) {
            if (isset($value['sku']) && $value['sku'] === '') {
                unset($variationsMatrix[$key]['sku']);
                unset($data['matrix'][$key]['sku']);
            }
        }

        $this->variationsMatrix = isset($data['matrix'])
            ? array_replace_recursive($variationsMatrix, $data['matrix'])
            : $variationsMatrix;

        // assigned products
        foreach ($this->variationsMatrix as $key => $row) {
            if (isset($this->products[$key])) {
                /** @var CatalogProductSimple $product */
                $product = $this->products[$key];
                $quantityAndStockStatus = $product->getQuantityAndStockStatus();
                $productData = [
                    'configurable_attribute' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'qty' => $quantityAndStockStatus['qty'],
                    'weight' => $product->getWeight(),
                    'price' => $product->getPrice()
                ];
                $this->variationsMatrix[$key] = array_replace_recursive($this->variationsMatrix[$key], $productData);
            } else {
                $this->variationsMatrix[$key] = array_replace_recursive(
                    $this->variationsMatrix[$key],
                    [
                        'weight' => 1,
                        'qty' => 10,
                    ],
                    $row
                );
            }
        }
    }

    /**
     * Add matrix variation.
     *
     * @param array $variationsMatrix
     * @param array $attribute
     * @param string $attributeKey
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function addVariationMatrix(array $variationsMatrix, array $attribute, $attributeKey)
    {
        $result = [];

        /* If empty matrix add one empty row */
        if (empty($variationsMatrix)) {
            $variationIsolation = mt_rand(10000, 70000);
            $variationsMatrix = [
                [
                    'name' => "In configurable product {$variationIsolation}",
                    'sku' => "in_configurable_product_{$variationIsolation}",
                ],
            ];
        }

        foreach ($variationsMatrix as $rowKey => $row) {
            $randIsolation = mt_rand(1, 100);
            $rowName = $row['name'];
            $rowSku = $row['sku'];
            $index = 1;
            foreach ($attribute['options'] as $optionKey => $option) {
                $compositeKey = "{$attributeKey}:{$optionKey}";
                $row['name'] = $rowName . ' ' . $randIsolation . ' ' . $index;
                $row['sku'] = $rowSku . '_' . $randIsolation . '_' . $index;
                $row['price'] = $option['pricing_value'];
                $newRowKey = $rowKey ? "{$rowKey} {$compositeKey}" : $compositeKey;
                $result[$newRowKey] = $row;
                $index++;
            }
        }

        return $result;
    }

    /**
     * Prepare data from source.
     *
     * @return void
     */
    protected function prepareData()
    {
        $attributeFields = [
            'frontend_label',
            'label',
            'frontend_input',
            'attribute_code',
            'attribute_id',
            'is_required',
            'options',
        ];
        $optionFields = [
            'admin',
            'label',
            'pricing_value',
            'include',
        ];
        $variationMatrixFields = [
            'configurable_attribute',
            'name',
            'sku',
            'price',
            'qty',
            'weight',
        ];

        $this->data = [
            'matrix' => [],
            'attributes_data' => [],
        ];

        foreach ($this->attributesData as $attributeKey => $attribute) {
            foreach ($attribute['options'] as $optionKey => $option) {
                $option['label'] = isset($option['view']) ? $option['view'] : $option['label'];
                $attribute['options'][$optionKey] = array_intersect_key($option, array_flip($optionFields));
            }
            $attribute['label'] = isset($attribute['label'])
                ? $attribute['label']
                : (isset($attribute['frontend_label']) ? $attribute['frontend_label'] : null);
            $attribute = array_intersect_key($attribute, array_flip($attributeFields));

            $this->data['attributes_data'][$attributeKey] = $attribute;
        }
        foreach ($this->variationsMatrix as $key => $variationMatrix) {
            $this->data['matrix'][$key] = array_intersect_key($variationMatrix, array_flip($variationMatrixFields));
        }
    }

    /**
     * Get prepared attributes data.
     *
     * @return array
     */
    public function getAttributesData()
    {
        return $this->attributesData;
    }

    /**
     * Get prepared variations matrix.
     *
     * @return array
     */
    public function getVariationsMatrix()
    {
        return $this->variationsMatrix;
    }

    /**
     * Get prepared attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get created attribute set.
     *
     * @return CatalogAttributeSet
     */
    public function getAttributeSet()
    {
        return $this->attributeSet;
    }

    /**
     * Get prepared products.
     *
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }
}
