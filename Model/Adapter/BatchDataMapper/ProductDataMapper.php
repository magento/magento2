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
     * @var array
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
        $productIds = array_keys($documentData);
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
            if (is_array($attributeValue)) {
                if (isset($attributeValue[$productId])) {
                    $value = $attributeValue[$productId];
                } else {
                    $value = implode(' ', $attributeValue);
                }
            } else {
                $value = $attributeValue;
            }

            // cover case with "options"
            // see \Magento\CatalogSearch\Model\Indexer\Fulltext\Action\DataProvider::prepareProductIndex
            if ($value && $attributeId === 'options') {
                $productAttributes[$attributeId] = $value;
            } elseif ($value) {
                $attributeData = $this->getAttributeData($attributeId);
                if (!$attributeData) {
                    continue;
                }
                $attributeCode = $attributeData[AttributeInterface::ATTRIBUTE_CODE];
                if (isset($attributeData[AttributeInterface::OPTIONS][$value])) {
                    $productAttributes[$attributeCode . '_value'] = $attributeData[AttributeInterface::OPTIONS][$value];
                }
                $value = $this->formatProductAttributeValue($value, $attributeData, $storeId);
                $productAttributes[$attributeCode] = $value;
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
                if ($attribute->getFrontendInput() === 'select') {
                    foreach ($attribute->getOptions() as $option) {
                        $options[$option->getValue()] = $option->getLabel();
                    }
                }
                $this->attributeData[$attributeId] = [
                    AttributeInterface::ATTRIBUTE_CODE => $attribute->getAttributeCode(),
                    AttributeInterface::FRONTEND_INPUT => $attribute->getFrontendInput(),
                    AttributeInterface::BACKEND_TYPE => $attribute->getBackendType(),
                    AttributeInterface::OPTIONS => $options,
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
}
