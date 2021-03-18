<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Elasticsearch\Model\Adapter\Document\Builder;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\BatchDataMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldType\Date as DateFieldType;
use Magento\AdvancedSearch\Model\Adapter\DataMapper\AdditionalFieldsProviderInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;

/**
 * Map product index data to search engine metadata
 */
class ProductDataMapper implements BatchDataMapperInterface
{
    /**
     * @var AttributeOptionInterface[]
     */
    private $attributeOptionsCache;

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
        'tax_class_id',
    ];

    /**
     * @var string[]
     */
    private $sortableAttributesValuesToImplode = [
        'name',
    ];

    /**
     * @var string[]
     */
    private $filterableAttributeTypes;

    /**
     * @var string[]
     */
    private $sortableCaseSensitiveAttributes = [
        'name',
    ];

    /**
     * @param Builder $builder
     * @param FieldMapperInterface $fieldMapper
     * @param DateFieldType $dateFieldType
     * @param AdditionalFieldsProviderInterface $additionalFieldsProvider
     * @param DataProvider $dataProvider
     * @param array $excludedAttributes
     * @param array $sortableAttributesValuesToImplode
     * @param array $filterableAttributeTypes
     * @param array $sortableCaseSensitiveAttributes
     */
    public function __construct(
        Builder $builder,
        FieldMapperInterface $fieldMapper,
        DateFieldType $dateFieldType,
        AdditionalFieldsProviderInterface $additionalFieldsProvider,
        DataProvider $dataProvider,
        array $excludedAttributes = [],
        array $sortableAttributesValuesToImplode = [],
        array $filterableAttributeTypes = [],
        array $sortableCaseSensitiveAttributes = []
    ) {
        $this->builder = $builder;
        $this->fieldMapper = $fieldMapper;
        $this->dateFieldType = $dateFieldType;
        $this->excludedAttributes = array_merge($this->defaultExcludedAttributes, $excludedAttributes);
        $this->sortableAttributesValuesToImplode = array_merge(
            $this->sortableAttributesValuesToImplode,
            $sortableAttributesValuesToImplode
        );
        $this->additionalFieldsProvider = $additionalFieldsProvider;
        $this->dataProvider = $dataProvider;
        $this->attributeOptionsCache = [];
        $this->filterableAttributeTypes = $filterableAttributeTypes;
        $this->sortableCaseSensitiveAttributes = array_merge(
            $this->sortableCaseSensitiveAttributes,
            $sortableCaseSensitiveAttributes
        );
    }

    /**
     * Map index data for using in search engine metadata
     *
     * @param array $documentData
     * @param int $storeId
     * @param array $context
     * @return array
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
     * Convert raw data retrieved from source tables to human-readable format.
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
            $attribute = $this->dataProvider->getSearchableAttribute($attributeId);
            if (in_array($attribute->getAttributeCode(), $this->excludedAttributes, true)) {
                continue;
            }

            if (!\is_array($attributeValues)) {
                $attributeValues = [$productId => $attributeValues];
            }
            $attributeValues = $this->prepareAttributeValues($productId, $attribute, $attributeValues, $storeId);
            $productAttributes += $this->convertAttribute($attribute, $attributeValues, $storeId);
        }

        return $productAttributes;
    }

    /**
     * Convert data for attribute, add {attribute_code}_value for searchable attributes, that contain actual value.
     *
     * @param Attribute $attribute
     * @param array $attributeValues
     * @param int $storeId
     * @return array
     */
    private function convertAttribute(Attribute $attribute, array $attributeValues, int $storeId): array
    {
        $productAttributes = [];

        $retrievedValue = $this->retrieveFieldValue($attributeValues);
        if ($retrievedValue !== null) {
            $productAttributes[$attribute->getAttributeCode()] = $retrievedValue;

            if ($this->isAttributeLabelsShouldBeMapped($attribute)) {
                $attributeLabels = $this->getValuesLabels($attribute, $attributeValues, $storeId);
                $retrievedLabel = $this->retrieveFieldValue($attributeLabels);
                if ($retrievedLabel) {
                    $productAttributes[$attribute->getAttributeCode() . '_value'] = $retrievedLabel;
                }
            }
        }

        return $productAttributes;
    }

    /**
     * Check if an attribute has one of the next storefront properties enabled for mapping labels:
     * - "Use in Search" (is_searchable)
     * - "Visible in Advanced Search" (is_visible_in_advanced_search)
     * - "Use in Layered Navigation" (is_filterable)
     * - "Use in Search Results Layered Navigation" (is_filterable_in_search)
     *
     * @param Attribute $attribute
     * @return bool
     */
    private function isAttributeLabelsShouldBeMapped(Attribute $attribute): bool
    {
        return (
            $attribute->getIsSearchable()
            || $attribute->getIsVisibleInAdvancedSearch()
            || $attribute->getIsFilterable()
            || $attribute->getIsFilterableInSearch()
        );
    }

    /**
     * Prepare attribute values.
     *
     * @param int $productId
     * @param Attribute $attribute
     * @param array $attributeValues
     * @param int $storeId
     * @return array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function prepareAttributeValues(
        int $productId,
        Attribute $attribute,
        array $attributeValues,
        int $storeId
    ): array {
        if (in_array($attribute->getAttributeCode(), $this->attributesExcludedFromMerge, true)) {
            $attributeValues = [
                $productId => $attributeValues[$productId] ?? '',
            ];
        }

        if ($attribute->getFrontendInput() === 'multiselect') {
            $attributeValues = $this->prepareMultiselectValues($attributeValues);
        }

        if (in_array($attribute->getFrontendInput(), $this->filterableAttributeTypes)) {
            $attributeValues = array_map(
                function (string $valueId) {
                    return (int)$valueId;
                },
                $attributeValues
            );
        }

        if ($this->isAttributeDate($attribute)) {
            foreach ($attributeValues as $key => $attributeValue) {
                $attributeValues[$key] = $this->dateFieldType->formatDate($storeId, $attributeValue);
            }
        }

        if ($attribute->getUsedForSortBy()
            && in_array($attribute->getAttributeCode(), $this->sortableAttributesValuesToImplode)
            && count($attributeValues) > 1
        ) {
            $attributeValues = [$productId => implode(' ', $attributeValues)];
        }

        if (in_array($attribute->getAttributeCode(), $this->sortableCaseSensitiveAttributes)) {
            foreach ($attributeValues as $key => $attributeValue) {
                $attributeValues[$key] = strtolower($attributeValue);
            }
        }

        return $attributeValues;
    }

    /**
     * Prepare multiselect values.
     *
     * @param array $values
     * @return array
     */
    private function prepareMultiselectValues(array $values): array
    {
        return \array_merge(
            ...\array_map(
                function (string $value) {
                    return \explode(',', $value);
                },
                $values
            )
        );
    }

    /**
     * Is attribute date.
     *
     * @param Attribute $attribute
     * @return bool
     */
    private function isAttributeDate(Attribute $attribute): bool
    {
        return $attribute->getFrontendInput() === 'date'
            || in_array($attribute->getBackendType(), ['datetime', 'timestamp'], true);
    }

    /**
     * Get values labels.
     *
     * @param Attribute $attribute
     * @param array $attributeValues
     * @param int $storeId
     * @return array
     */
    private function getValuesLabels(Attribute $attribute, array $attributeValues, int $storeId): array
    {
        $attributeLabels = [];

        $options = $this->getAttributeOptions($attribute, $storeId);
        if (empty($options)) {
            return $attributeLabels;
        }

        foreach ($options as $option) {
            if (\in_array($option['value'], $attributeValues)) {
                $attributeLabels[] = $option['label'];
            }
        }

        return $attributeLabels;
    }

    /**
     * Retrieve options for attribute
     *
     * @param Attribute $attribute
     * @param int $storeId
     * @return array
     */
    private function getAttributeOptions(Attribute $attribute, int $storeId): array
    {
        if (!isset($this->attributeOptionsCache[$storeId][$attribute->getId()])) {
            $attributeStoreId = $attribute->getStoreId();
            /**
             * Load array format of options.
             * $attribute->getOptions() loads options into data objects which can be costly.
             */
            $options = $attribute->usesSource() ? $attribute->setStoreId($storeId)->getSource()->getAllOptions() : [];
            $this->attributeOptionsCache[$storeId][$attribute->getId()] = $options;
            $attribute->setStoreId($attributeStoreId);
        }

        return $this->attributeOptionsCache[$storeId][$attribute->getId()];
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
        $values = \array_unique($values);

        return count($values) === 1 ? \array_shift($values) : \array_values($values);
    }
}
