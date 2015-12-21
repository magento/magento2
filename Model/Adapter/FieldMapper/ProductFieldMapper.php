<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Model\Adapter\FieldType;

/**
 * Class ProductFieldMapper
 */
class ProductFieldMapper implements FieldMapperInterface
{
    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var FieldType
     */
    protected $fieldType;

    /**
     * @param Config $eavConfig
     * @param FieldType $fieldType
     */
    public function __construct(
        Config $eavConfig,
        FieldType $fieldType
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldType = $fieldType;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getFieldName($attributeCode, $context = [])
    {
        if (in_array($attributeCode, ['id', 'sku', 'store_id', 'visibility'], true)) {
            return $attributeCode;
        }
        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
        if (!$attribute) {
            return $attributeCode;
        }
        $fieldType = $this->fieldType->getFieldType($attribute);
        $frontendInput = $attribute->getFrontendInput();
        if (empty($context['type'])) {
            $fieldName = $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_FILTER) {
            if ($fieldType === 'string') {
                return $this->getFieldName(
                    $attributeCode,
                    array_merge($context, ['type' => FieldMapperInterface::TYPE_QUERY])
                );
            }
            $fieldName = in_array($frontendInput, ['select', 'boolean'], true) ? $attributeCode . '_value' :
                $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_QUERY) {
            if ($attributeCode === '*') {
                $fieldName = '_all';
            } else {
                $fieldName = in_array($frontendInput, ['select', 'boolean'], true) ? $attributeCode . '_value' :
                    $attributeCode;
            }
        } else {
            $fieldName = 'sort_' . $attributeCode;
        }

        return $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllAttributesTypes($context = [])
    {
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $allAttributes = [];

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
            $frontendInput = $attribute->getFrontendInput();
            $notUsedInSearch = [];
            if ($this->isAttributeUsedInAdvancedSearch($attribute) === false && $attributeCode !=='media_gallery'
                && $attributeCode !=='quantity_and_stock_status' && $attributeCode !=='tier_price'
                && $attributeCode !=='category_ids') {
                $notUsedInSearch = ['index' => 'no'];
            }
            $allAttributes[$attributeCode] = [
                'type' => $this->fieldType->getFieldType($attribute)
            ];
            if ($notUsedInSearch) {
                $allAttributes[$attributeCode] = array_merge(
                    $allAttributes[$attributeCode],
                    $notUsedInSearch
                );
            }
            if ($attributeCode == 'category_ids') {
                $allAttributes['category'] = [
                    'type' => FieldType::ES_DATA_TYPE_NESTED,
                ];
            }
            if ($frontendInput == 'select') {
                $allAttributes[$attributeCode . '_value'] = [
                    'type' => FieldType::ES_DATA_TYPE_STRING,
                ];
            }
        }

        return $allAttributes;
    }

    /**
     * @param Object $attribute
     * @return bool
     */
    protected function isAttributeUsedInAdvancedSearch($attribute)
    {
        return $attribute->getIsVisibleInAdvancedSearch()
        || $attribute->getIsFilterable()
        || $attribute->getIsFilterableInSearch();
    }
}
