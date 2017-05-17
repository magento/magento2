<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\BatchDataMapper;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Elasticsearch\Model\Adapter\Container\Attribute as AttributeContainer;
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
     * @var AttributeContainer
     */
    private $attributeContainer;

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
     * @param AttributeContainer $attributeContainer
     * @param FieldMapperInterface $fieldMapper
     * @param DateFieldType $dateFieldType
     * @param AdditionalFieldsProviderInterface $additionalFieldsProvider
     * @param array $excludedAttributes
     */
    public function __construct(
        Builder $builder,
        AttributeContainer $attributeContainer,
        FieldMapperInterface $fieldMapper,
        DateFieldType $dateFieldType,
        AdditionalFieldsProviderInterface $additionalFieldsProvider,
        array $excludedAttributes = []
    ) {
        $this->builder = $builder;
        $this->attributeContainer = $attributeContainer;
        $this->fieldMapper = $fieldMapper;
        $this->dateFieldType = $dateFieldType;
        $this->excludedAttributes = array_merge($this->defaultExcludedAttributes, $excludedAttributes);
        $this->additionalFieldsProvider = $additionalFieldsProvider;
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
            $attributeCode = $this->attributeContainer->getAttributeCodeById($attributeId);

            if (is_array($attributeValue)) {
                if (isset($attributeValue[$productId])) {
                    $value = $attributeValue[$productId];
                } else {
                    $value = implode(' ', $attributeValue);
                }
            } else {
                $value = $attributeValue;
            }

            if ($value) {
                $attributeData = $this->getAttributeData($attributeCode);
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
     * Get product attribute data by attribute code
     *
     * @param string $attributeCode
     * @return array
     */
    private function getAttributeData($attributeCode)
    {
        if (!array_key_exists($attributeCode, $this->attributeData)) {
            $attribute = $this->attributeContainer->getSearchableAttribute($attributeCode);
            if ($attribute) {
                $options = [];
                if ($attribute->getFrontendInput() === 'select') {
                    foreach ($attribute->getOptions() as $option) {
                        $options[$option->getValue()] = $option->getLabel();
                    }
                }
                $this->attributeData[$attributeCode] = [
                    AttributeInterface::FRONTEND_INPUT => $attribute->getFrontendInput(),
                    AttributeInterface::BACKEND_TYPE => $attribute->getBackendType(),
                    AttributeInterface::OPTIONS => $options,
                ];
            } else {
                $this->attributeData[$attributeCode] = null;
            }
        }

        return $this->attributeData[$attributeCode];
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
