<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldType\Date as DateFieldType;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;

/**
 * Map product index data to search engine metadata
 */
class ProductDataMapper implements BatchDataMapperInterface
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var FieldMapperInterface
     */
    private $fieldMapper;

    /**
     * @var DateFieldType
     */
    private $dateFieldType;

    /**
     * @var array
     */
    private $excludedAttributes;

    /**
     * @var AdditionalFieldsProviderInterface
     */
    private $additionalFieldsProvider;

    /**
     * @var DataProvider
     */
    private $dataProvider;

    /**
     * List of attributes which will be skipped during mapping
     *
     * @var string[]
     */
    private $defaultExcludedAttributes = [
        'price',
        'media_gallery',
        'tier_price',
        'quantity_and_stock_status',
        'media_gallery',
        'giftcard_amounts',
    ];

    /**
     * @var string[]
     */
    private $attributesExcludedFromMerge = [
        'status',
        'visibility',
        'tax_class_id'
    ];

    /**
     * Construction for DocumentDataMapper
     *
     * @param Builder $builder
     * @param FieldMapperInterface $fieldMapper
     * @param DateFieldType $dateFieldType
     * @param AdditionalFieldsProviderInterface $additionalFieldsProvider
     * @param DataProvider $dataProvider
     * @param array $excludedAttributes
     */
    public function __construct(
        Builder $builder,
        FieldMapperInterface $fieldMapper,
        DateFieldType $dateFieldType,
        AdditionalFieldsProviderInterface $additionalFieldsProvider,
        DataProvider $dataProvider,
        array $excludedAttributes = []
    ) {
        $this->builder = $builder;
        $this->fieldMapper = $fieldMapper;
        $this->dateFieldType = $dateFieldType;
        $this->excludedAttributes = array_merge($this->defaultExcludedAttributes, $excludedAttributes);
        $this->additionalFieldsProvider = $additionalFieldsProvider;
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function map(array $documentData, $storeId, array $context = [])
    {
        $documents = [];

        foreach ($documentData as $productId => $indexData) {
            $this->builder->addField('store_id', $storeId);
            $productIndexData = $this->convertToProductData($productId, $indexData, $storeId);
            foreach ($productIndexData as $attributeCode => $value) {
                // Prepare processing attribute info
                if (strpos($attributeCode, '_value') !== false) {
                    $this->builder->addField($attributeCode, $value);
                    continue;
                }

                $this->builder->addField(
                    $this->fieldMapper->getFieldName(
                        $attributeCode,
                        $context
                    ),
                    $value
                );
            }
            $documents[$productId] = $this->builder->build();
        }

        $productIds = array_keys($documentData);
        foreach ($this->additionalFieldsProvider->getFields($productIds, $storeId) as $productId => $fields) {
            $documents[$productId] = array_merge_recursive(
                $documents[$productId],
                $this->builder->addFields($fields)->build()
            );
        }

        return $documents;
    }

    /**
     * Convert raw data retrieved from source tables to human-readable format
     * E.g. [42 => [1 => 2]] will be converted to ['color' => '2', 'color_value' => 'red']
     *
     * @param int $productId
     * @param array $indexData
     * @param int $storeId
     * @return array
     */
    private function convertToProductData(int $productId, array $indexData, int $storeId): array
    {
        $productAttributes = [];

        if (isset($indexData['options'])) {
            // cover case with "options"
            // see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::prepareProductIndex
            $productAttributes['options'] = $indexData['options'];
            unset($indexData['options']);
        }

        foreach ($indexData as $attributeId => $attributeValues) {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
            $attribute = $this->dataProvider->getSearchableAttribute($attributeId);
            if (in_array($attribute->getAttributeCode(), $this->excludedAttributes, true)) {
                continue;
            }

            if (!\is_array($attributeValues)) {
                $attributeValues = [$productId => $attributeValues];
            }
            $attributeValues = $this->prepareAttributeValues($productId, $attribute, $attributeValues, $storeId);
            $productAttributes += $this->convertAttribute($attribute, $attributeValues);
        }

        return $productAttributes;
    }

    /**
     * Convert data for attribute: 1) add new value {attribute_code}_value for select and multiselect searchable
     * attributes, that will contain actual value 2) add child products data to composite products
     *
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param array $attributeValues
     * @return array
     */
    private function convertAttribute($attribute, array $attributeValues): array
    {
        $productAttributes = [];

        $retrievedValue = $this->retrieveFieldValue($attributeValues);
        if ($retrievedValue) {
            $productAttributes[$attribute->getAttributeCode()] = $retrievedValue;

            if ($attribute->getIsSearchable()) {
                $attributeLabels = $this->getValuesLabels($attribute, $attributeValues);
                $retrievedLabel = $this->retrieveFieldValue($attributeLabels);
                if ($retrievedLabel) {
                    $productAttributes[$attribute->getAttributeCode() . '_value'] = $retrievedLabel;
                }
            }
        }

        return $productAttributes;
    }

    /**
     * @param int $productId
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param array $attributeValues
     * @param int $storeId
     * @return array
     */
    private function prepareAttributeValues(int $productId, $attribute, array $attributeValues, int $storeId): array
    {
        if (in_array($attribute->getAttributeCode(), $this->attributesExcludedFromMerge, true)) {
            $attributeValues = [
                $productId => $attributeValues[$productId] ?? '',
            ];
        }

        if ($attribute->getFrontendInput() === 'multiselect') {
            $attributeValues = $this->prepareMultiselectValues($attributeValues);
        }

        if ($this->isAttributeDate($attribute)) {
            foreach ($attributeValues as $key => $attributeValue) {
                $attributeValues[$key] = $this->dateFieldType->formatDate($storeId, $attributeValue);
            }
        }

        return $attributeValues;
    }

    /**
     * @param array $values
     * @return array
     */
    private function prepareMultiselectValues(array $values): array
    {
        return \array_merge(...\array_map(function (string $value) {
            return \explode(',', $value);
        }, $values));
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return bool
     */
    private function isAttributeDate($attribute): bool
    {
        return $attribute->getFrontendInput() === 'date'
            || in_array($attribute->getBackendType(), ['datetime', 'timestamp'], true);
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @param array $attributeValues
     * @return array
     */
    private function getValuesLabels($attribute, array $attributeValues): array
    {
        $attributeLabels = [];
        foreach ($attribute->getOptions() as $option) {
            if (\in_array($option->getValue(), $attributeValues)) {
                $attributeLabels[] = $option->getLabel();
            }
        }

        return $attributeLabels;
    }

    /**
     * Retrieve value for field. If field have only one value this method return it.
     * Otherwise will be returned array of these values.
     * Note: array of values must have index keys, not as associative array.
     *
     * @param array $values
     * @return array|string
     */
    private function retrieveFieldValue(array $values)
    {
        $values = \array_filter(\array_unique($values));

        return count($values) === 1 ? \array_shift($values) : \array_values($values);
    }
}
