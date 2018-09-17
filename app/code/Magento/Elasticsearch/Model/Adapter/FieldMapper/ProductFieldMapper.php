<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use \Magento\Customer\Model\Session as CustomerSession;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapper
    as Elasticsearch5ProductFieldMapper;
use Magento\Elasticsearch\Model\Adapter\FieldType;

/**
 * Class ProductFieldMapper
 */
class ProductFieldMapper extends Elasticsearch5ProductFieldMapper implements FieldMapperInterface
{
    /**
     * @param Config $eavConfig
     * @param FieldType $fieldType
     * @param CustomerSession $customerSession
     * @param StoreManager $storeManager
     * @param Registry $coreRegistry
     */
    public function __construct(
        Config $eavConfig,
        FieldType $fieldType,
        CustomerSession $customerSession,
        StoreManager $storeManager,
        Registry $coreRegistry
    ) {
        $this->eavConfig = $eavConfig;
        $this->fieldType = $fieldType;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get field name.
     *
     * @param string $attributeCode
     * @param array $context
     * @return string
     */
    public function getFieldName($attributeCode, $context = [])
    {
        $attribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, $attributeCode);
        if (!$attribute || in_array($attributeCode, ['id', 'sku', 'store_id', 'visibility'], true)) {
            return $attributeCode;
        }
        if ($attributeCode === 'price') {
            return $this->getPriceFieldName($context);
        }
        if ($attributeCode === 'position') {
            return $this->getPositionFiledName($context);
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
            $fieldName = $attributeCode;
        } elseif ($context['type'] === FieldMapperInterface::TYPE_QUERY) {
            $fieldName = $this->getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode);
        } else {
            $fieldName = 'sort_' . $attributeCode;
        }

        return $fieldName;
    }

    /**
     * Get all attributes types.
     *
     * @param array $context
     * @return array
     */
    public function getAllAttributesTypes($context = [])
    {
        $attributeCodes = $this->eavConfig->getEntityAttributeCodes(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $allAttributes = [];
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

    /**
     * Get refined field name.
     *
     * @param string $frontendInput
     * @param string $fieldType
     * @param string $attributeCode
     * @return string
     */
    protected function getRefinedFieldName($frontendInput, $fieldType, $attributeCode)
    {
        switch ($frontendInput) {
            case 'select':
            case 'multiselect':
                return in_array($fieldType, ['string','integer'], true) ? $attributeCode . '_value' : $attributeCode;
            case 'boolean':
                return $fieldType === 'integer' ? $attributeCode . '_value' : $attributeCode;
            default:
                return $attributeCode;
        }
    }
}
