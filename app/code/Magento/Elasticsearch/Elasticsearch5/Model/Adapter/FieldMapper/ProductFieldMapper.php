<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Elasticsearch\Model\Adapter\FieldMapperInterface;
use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldType;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use \Magento\Customer\Model\Session as CustomerSession;

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
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Store manager
     *
     * @var StoreManager
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

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
            if ($fieldType === FieldType::ES_DATA_TYPE_TEXT) {
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
                'type' => $this->fieldType->getFieldType($attribute),
            ];

            if (!$attribute->getIsSearchable() && !$this->isAttributeUsedInAdvancedSearch($attribute)
                && !in_array($attributeCode, $alwaysIndexableAttributes, true)
            ) {
                if ($attribute->getIsFilterable() || $attribute->getIsFilterableInSearch()) {
                    $allAttributes[$attributeCode]['type'] = FieldType::ES_DATA_TYPE_KEYWORD;
                } else if ($allAttributes[$attributeCode]['type'] === FieldType::ES_DATA_TYPE_TEXT) {
                    $allAttributes[$attributeCode]['index'] = false;
                }
            } else if ($attributeCode == "category_ids") {
                $allAttributes[$attributeCode] = [
                    'type' => FieldType::ES_DATA_TYPE_INT,
                ];
            }

            if ($attribute->usesSource()
                || $attribute->getFrontendInput() === 'select'
                || $attribute->getFrontendInput() === 'multiselect'
            ) {
                $allAttributes[$attributeCode]['type'] = FieldType::ES_DATA_TYPE_KEYWORD;

                $allAttributes[$attributeCode . '_value'] = [
                    'type' => FieldType::ES_DATA_TYPE_TEXT,
                ];
            }
        }

        return $allAttributes;
    }

    /**
     * Is attribute used in advanced search.
     *
     * @param Object $attribute
     * @return bool
     */
    protected function isAttributeUsedInAdvancedSearch($attribute)
    {
        return $attribute->getIsVisibleInAdvancedSearch()
        || $attribute->getIsFilterable()
        || $attribute->getIsFilterableInSearch();
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
                return in_array($fieldType, ['text','integer'], true) ? $attributeCode . '_value' : $attributeCode;
            case 'boolean':
                return $fieldType === 'integer' ? $attributeCode . '_value' : $attributeCode;
            default:
                return $attributeCode;
        }
    }

    /**
     * Get query type field name.
     *
     * @param string $frontendInput
     * @param string $fieldType
     * @param string $attributeCode
     * @return string
     */
    protected function getQueryTypeFieldName($frontendInput, $fieldType, $attributeCode)
    {
        if ($attributeCode === '*') {
            $fieldName = '_all';
        } else {
            $fieldName = $this->getRefinedFieldName($frontendInput, $fieldType, $attributeCode);
        }
        return $fieldName;
    }

    /**
     * Get "position" field name
     *
     * @param array $context
     * @return string
     */
    protected function getPositionFiledName($context)
    {
        if (isset($context['categoryId'])) {
            $category = $context['categoryId'];
        } else {
            $category = $this->coreRegistry->registry('current_category')
                ? $this->coreRegistry->registry('current_category')->getId()
                : $this->storeManager->getStore()->getRootCategoryId();
        }
        return 'position_category_' . $category;
    }

    /**
     * Prepare price field name for search engine
     *
     * @param array $context
     * @return string
     */
    protected function getPriceFieldName($context)
    {
        $customerGroupId = !empty($context['customerGroupId'])
            ? $context['customerGroupId']
            : $this->customerSession->getCustomerGroupId();
        $websiteId = !empty($context['websiteId'])
            ? $context['websiteId']
            : $this->storeManager->getStore()->getWebsiteId();
        return 'price_' . $customerGroupId . '_' . $websiteId;
    }
}
