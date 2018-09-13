<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapper
    as Elasticsearch5ProductFieldMapper;
use Magento\Elasticsearch\Model\Adapter\FieldType;

/**
 * Class ProductFieldMapper
 */
class ProductFieldMapper extends Elasticsearch5ProductFieldMapper implements FieldMapperInterface
{
    /**
     * @return array
     */
    private function getAllStaticAttributesTypes()
    {
        $allAttributes = [];
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        // List of attributes which are required to be indexable
        $alwaysIndexableAttributes = [
            'category_ids',
            'visibility',
        ];

        foreach ($attributeCodes as $attributeCode) {
            $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);

            $allAttributes[$attributeCode] = [
                'type' => $this->fieldType->getFieldType($attribute)
            ];

            if (!$attribute->getIsSearchable() && !$this->isAttributeUsedInAdvancedSearch($attribute)
                && !in_array($attributeCode, $alwaysIndexableAttributes, true)
            ) {
                $allAttributes[$attributeCode] = array_merge(
                    $allAttributes[$attributeCode],
                    ['index' => 'no']
                );
            }

            if ($attribute->getFrontendInput() === 'select' || $attribute->getFrontendInput() === 'multiselect') {
                $allAttributes[$attributeCode . '_value'] = [
                    'type' => FieldType::ES_DATA_TYPE_STRING,
                ];
            }
        }

        return $allAttributes;
    }
}
