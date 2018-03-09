<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider;
use Magento\Eav\Api\Data\AttributeInterface;
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
    private $attributeData = [];

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
        // reset attribute data for new store
        $this->attributeData = [];
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
                if (in_array($attributeCode, $this->excludedAttributes, true)) {
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
    private function convertToProductData($productId, array $indexData, $storeId)
    {
        $productAttributes = [];
        foreach ($indexData as $attributeId => $attributeValue) {
            $attributeData = $this->getAttributeData($attributeId);
            if (!$attributeData) {
                continue;
            }
            $productAttributes = array_merge(
                $productAttributes,
                $this->convertAttribute(
                    $productId,
                    $attributeId,
                    $attributeValue,
                    $attributeData,
                    $storeId
                )
            );
        }
        return $productAttributes;
    }

    /**
     * Convert data for attribute: 1) add new value {attribute_code}_value for select and multiselect searchable
     * attributes, that will contain actual value 2) add child products data to composite products
     *
     * @param int $productId
     * @param int $attributeId
     * @param mixed $attributeValue
     * @param array $attributeData
     * @param int $storeId
     * @return array
     */
    private function convertAttribute($productId, $attributeId, $attributeValue, array $attributeData, $storeId)
    {
        $productAttributes = [];
        $attributeCode = $attributeData[AttributeInterface::ATTRIBUTE_CODE];
        $attributeFrontendInput = $attributeData[AttributeInterface::FRONTEND_INPUT];
        if (is_array($attributeValue)) {
            if (!$attributeData['is_searchable']) {
                $value = $this->getValueForAttribute(
                    $productId,
                    $attributeCode,
                    $attributeValue,
                    $attributeData['is_searchable']
                );
            } else {
                if (($attributeFrontendInput == 'select' || $attributeFrontendInput == 'multiselect')
                    && !in_array($attributeCode, $this->excludedAttributes)
                ) {
                    $value = $this->getValueForAttribute(
                        $productId,
                        $attributeCode,
                        $attributeValue,
                        $attributeData['is_searchable']
                    );
                    $productAttributes[$attributeCode . '_value'] = $this->getValueForAttributeOptions(
                        $attributeData,
                        $attributeValue
                    );
                } else {
                    $value = implode(' ', $attributeValue);
                }
            }
        } else {
            $value = $attributeValue;
        }

        // cover case with "options"
        // see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::prepareProductIndex
        if ($value) {
            if ($attributeId === 'options') {
                $productAttributes[$attributeId] = $value;
            } else {
                if (isset($attributeData[AttributeInterface::OPTIONS][$value])) {
                    $productAttributes[$attributeCode . '_value'] = $attributeData[AttributeInterface::OPTIONS][$value];
                }
                $productAttributes[$attributeCode] = $this->formatProductAttributeValue(
                    $value,
                    $attributeData,
                    $storeId
                );
            }
        }
        return $productAttributes;
    }

    /**
     * Get product attribute data by attribute id
     *
     * @param int $attributeId
     * @return array
     */
    private function getAttributeData($attributeId)
    {
        if (!array_key_exists($attributeId, $this->attributeData)) {
            $attribute = $this->dataProvider->getSearchableAttribute($attributeId);
            if ($attribute) {
                $options = [];
                if ($attribute->getFrontendInput() === 'select' || $attribute->getFrontendInput() === 'multiselect') {
                    foreach ($attribute->getOptions() as $option) {
                        $options[$option->getValue()] = $option->getLabel();
                    }
                }
                $this->attributeData[$attributeId] = [
                    AttributeInterface::ATTRIBUTE_CODE => $attribute->getAttributeCode(),
                    AttributeInterface::FRONTEND_INPUT => $attribute->getFrontendInput(),
                    AttributeInterface::BACKEND_TYPE => $attribute->getBackendType(),
                    AttributeInterface::OPTIONS => $options,
                    'is_searchable' => $attribute->getIsSearchable(),
                ];
            } else {
                $this->attributeData[$attributeId] = null;
            }
        }

        return $this->attributeData[$attributeId];
    }

    /**
     * Format product attribute value for search engine
     *
     * @param mixed $value
     * @param array $attributeData
     * @param string $storeId
     * @return string
     */
    private function formatProductAttributeValue($value, $attributeData, $storeId)
    {
        if ($attributeData[AttributeInterface::FRONTEND_INPUT] === 'date'
            || in_array($attributeData[AttributeInterface::BACKEND_TYPE], ['datetime', 'timestamp'])) {
            return $this->dateFieldType->formatDate($storeId, $value);
        } elseif ($attributeData[AttributeInterface::FRONTEND_INPUT] === 'multiselect') {
            return str_replace(',', ' ', $value);
        } else {
            return $value;
        }
    }

    /**
     * Return single value if value exists for the productId in array, otherwise return concatenated array values
     *
     * @param int $productId
     * @param string $attributeCode
     * @param array $attributeValue
     * @param bool $isSearchable
     * @return mixed
     */
    private function getValueForAttribute($productId, $attributeCode, array $attributeValue, $isSearchable)
    {
        if ((!$isSearchable || in_array($attributeCode, $this->attributesExcludedFromMerge))
            && isset($attributeValue[$productId])
        ) {
            $value = $attributeValue[$productId];
        } elseif (in_array($attributeCode, $this->attributesExcludedFromMerge) && !isset($attributeValue[$productId])) {
            $value = '';
        } else {
            $value = implode(' ', $attributeValue);
        }
        return $value;
    }

    /**
     * Concatenate select and multiselect attribute values
     *
     * @param array $attributeData
     * @param array $attributeValue
     * @return string
     */
    private function getValueForAttributeOptions(array $attributeData, array $attributeValue)
    {
        $result = null;
        $selectedValues = [];
        if ($attributeData[AttributeInterface::FRONTEND_INPUT] == 'select') {
            foreach ($attributeValue as $selectedValue) {
                if (isset($attributeData[AttributeInterface::OPTIONS][$selectedValue])) {
                    $selectedValues[] = $attributeData[AttributeInterface::OPTIONS][$selectedValue];
                }
            }
        }
        if ($attributeData[AttributeInterface::FRONTEND_INPUT] == 'multiselect') {
            foreach ($attributeValue as $selectedAttributeValues) {
                $selectedAttributeValues = explode(',', $selectedAttributeValues);
                foreach ($selectedAttributeValues as $selectedValue) {
                    if (isset($attributeData[AttributeInterface::OPTIONS][$selectedValue])) {
                        $selectedValues[] = $attributeData[AttributeInterface::OPTIONS][$selectedValue];
                    }
                }
            }
        }
        $selectedValues = array_unique($selectedValues);
        if (!empty($selectedValues)) {
            $result = implode(' ', $selectedValues);
        }
        return $result;
    }
}
